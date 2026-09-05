<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseActivity;
use App\Models\Plan;
use Illuminate\Http\Request;

class LicenseAdminController extends Controller
{
    public function index()
    {
        $licenses = License::with(['customer', 'plan'])->latest()->paginate(25);

        return view('admin.licenses.index', compact('licenses'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $plans = Plan::where('is_active', true)->get();

        return view('admin.licenses.create', compact('customers', 'plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required_without:customer_id', 'string', 'nullable'],
            'customer_email' => ['required_without:customer_id', 'email', 'nullable'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => ['required', 'in:active,trial'],
            'trial_days' => ['nullable', 'integer', 'min:1'],
            'period_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $customer = $data['customer_id']
            ? Customer::findOrFail($data['customer_id'])
            // firstOrCreate instead of create(): if a customer with this email
            // already exists (repeat buyer, typo re-submit, etc.) reuse them
            // instead of crashing on the unique-email constraint.
            : Customer::firstOrCreate(
                ['email' => $data['customer_email']],
                ['name' => $data['customer_name']]
            );

        $plan = Plan::findOrFail($data['plan_id']);

        $license = License::create([
            'customer_id' => $customer->id,
            'plan_id' => $data['plan_id'],
            'license_key' => License::generateKey(),
            'status' => $data['status'],
            // Cast to (int): form values always arrive as strings, and Carbon 3
            // (used by Laravel 13) rejects non-numeric-typed arguments to addDays().
            // Falls back to the plan's own default_period_days (Basic=30, Advanced=180,
            // Premium=365) rather than assuming every plan is a flat 30-day cycle.
            'trial_ends_at' => $data['status'] === 'trial'
                ? now()->addDays((int) ($data['trial_days'] ?? 7))
                : null,
            'current_period_ends_at' => $data['status'] === 'active'
                ? now()->addDays((int) ($data['period_days'] ?? $plan->default_period_days))
                : null,
        ]);

        LicenseActivity::log($license, 'issued', "License issued ({$data['status']}, {$license->plan->name}).", 'admin');

        return redirect()->route('admin.licenses.index')
            ->with('success', "License issued: {$license->license_key}");
    }

    /** One-click cancel — instantly locks the customer's site out. */
    public function cancel(License $license)
    {
        $license->update(['status' => 'canceled']);
        LicenseActivity::log($license, 'canceled', 'License canceled by admin.', 'admin');

        return back()->with('success', "License {$license->license_key} canceled.");
    }

    public function reactivate(License $license)
    {
        $days = $license->plan->default_period_days;

        $license->update([
            'status' => 'active',
            'current_period_ends_at' => now()->addDays((int) $days),
            'reminder_sent_at' => null,
        ]);
        LicenseActivity::log($license, 'reactivated', "License reactivated by admin (+{$days} days, {$license->plan->name} plan).", 'admin');

        return back()->with('success', "License {$license->license_key} reactivated.");
    }

    /** Manually push the expiry date out — for one-off extensions, refunds-as-credit, etc. */
    public function extend(Request $request, License $license)
    {
        $days = (int) $request->validate(['days' => ['required', 'integer', 'min:1']])['days'];

        $base = $license->current_period_ends_at && $license->current_period_ends_at->isFuture()
            ? $license->current_period_ends_at
            : now();

        $license->update(['current_period_ends_at' => $base->addDays($days), 'reminder_sent_at' => null]);
        LicenseActivity::log($license, 'extended', "Extended by {$days} days by admin.", 'admin');

        return back()->with('success', "Extended by {$days} days.");
    }

    /** Permanently deletes the license record. The customer's site will treat
     *  this exactly like a canceled/not_found license the next time it checks in.
     *  This does NOT delete the Customer record itself, so their history/other
     *  licenses (if any) stay intact. Note: LicenseActivity rows for this license
     *  cascade-delete too (see the migration's onDelete), so nothing dangling is left. */
    public function destroy(License $license)
    {
        $key = $license->license_key;
        $license->delete();

        return redirect()->route('admin.licenses.index')
            ->with('success', "License {$key} deleted permanently.");
    }

    /** Approve a customer's pending domain-change request: swaps it into
     *  bound_domain and clears the pending flag. */
    public function approveDomainChange(License $license)
    {
        abort_if($license->pending_domain === null, 404);

        $old = $license->bound_domain;
        $license->update([
            'bound_domain' => $license->pending_domain,
            'pending_domain' => null,
        ]);
        LicenseActivity::log($license, 'domain_change_approved', "Domain changed from \"{$old}\" to \"{$license->bound_domain}\" (approved by admin).", 'admin');

        return back()->with('success', 'Domain change approved.');
    }

    public function rejectDomainChange(License $license)
    {
        $requested = $license->pending_domain;
        $license->update(['pending_domain' => null]);
        LicenseActivity::log($license, 'domain_change_rejected', "Domain change request to \"{$requested}\" rejected by admin.", 'admin');

        return back()->with('success', 'Domain change request rejected.');
    }

    /** Approve a customer's pending plan change: swaps it into plan_id and
     *  clears the pending flag. Deliberately does NOT touch billing/price —
     *  there's no live payment system yet, so approving this is purely
     *  "unlock the new plan's features", not "charge them the difference". */
    public function approvePlanChange(License $license)
    {
        abort_if($license->pending_plan_id === null, 404);

        $old = $license->plan->name;
        $license->load('pendingPlan');
        $newPlan = $license->pendingPlan;

        // Work out the remaining time on the CURRENT plan, then compare it
        // to a fresh cycle of the NEW plan. If what's left is less than a
        // full new-plan cycle (the common upgrade case — 3 days left on
        // Basic, approved for Premium), grant a full fresh cycle instead of
        // leaving them with only the old plan's leftover days. If what's
        // left is MORE than a full new-plan cycle (a downgrade while they
        // still have lots of paid time banked), leave it alone — they
        // already paid for that time, a downgrade shouldn't claw it back.
        $remainingDays = $license->current_period_ends_at && $license->current_period_ends_at->isFuture()
            ? now()->diffInDays($license->current_period_ends_at)
            : 0;

        $newPeriodEnd = $remainingDays < $newPlan->default_period_days
            ? now()->addDays($newPlan->default_period_days)
            : $license->current_period_ends_at;

        $license->update([
            'plan_id' => $license->pending_plan_id,
            'pending_plan_id' => null,
            'current_period_ends_at' => $newPeriodEnd,
            'reminder_sent_at' => null,
        ]);
        LicenseActivity::log($license, 'plan_change_approved', "Plan changed from {$old} to {$newPlan->name} (approved by admin). Period now ends " . $newPeriodEnd->format('M j, Y') . '.', 'admin');

        return back()->with('success', 'Plan change approved.');
    }

    public function rejectPlanChange(License $license)
    {
        $license->load('pendingPlan');
        $requested = $license->pendingPlan->name ?? 'unknown';
        $license->update(['pending_plan_id' => null]);
        LicenseActivity::log($license, 'plan_change_rejected', "Plan change request to {$requested} rejected by admin.", 'admin');

        return back()->with('success', 'Plan change request rejected.');
    }

    /** Full activity history for one license — the "history" the admin asked for. */
    public function activity(License $license)
    {
        $license->load('activities', 'customer', 'plan');

        return view('admin.licenses.activity', compact('license'));
    }

    /** Runs cancel/extend/delete against every license ID the admin checked
     *  in the table at once, instead of clicking each one individually. */
    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'license_ids' => ['required', 'array', 'min:1'],
            'license_ids.*' => ['exists:licenses,id'],
            'bulk_type' => ['required', 'in:cancel,extend,delete'],
        ]);

        $licenses = License::whereIn('id', $data['license_ids'])->get();
        $count = 0;

        foreach ($licenses as $license) {
            match ($data['bulk_type']) {
                'cancel' => (function () use ($license) {
                    $license->update(['status' => 'canceled']);
                    LicenseActivity::log($license, 'canceled', 'License canceled by admin (bulk action).', 'admin');
                })(),
                'extend' => (function () use ($license) {
                    $base = $license->current_period_ends_at && $license->current_period_ends_at->isFuture()
                        ? $license->current_period_ends_at
                        : now();
                    $license->update(['current_period_ends_at' => $base->addDays(30), 'reminder_sent_at' => null]);
                    LicenseActivity::log($license, 'extended', 'Extended by 30 days by admin (bulk action).', 'admin');
                })(),
                'delete' => $license->delete(),
            };
            $count++;
        }

        $label = ['cancel' => 'canceled', 'extend' => 'extended (+30 days)', 'delete' => 'deleted'][$data['bulk_type']];

        return back()->with('success', "{$count} license(s) {$label}.");
    }

    /** Exports every license as a CSV file — opens directly in Excel/Sheets.
     *  Streamed rather than built as one big string, so this stays fast even
     *  with thousands of licenses. */
    public function export()
    {
        $filename = 'licenses-export-' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'License Key', 'Customer Name', 'Customer Email', 'Plan', 'Status',
                'Bound Domain', 'Trial Ends', 'Current Period Ends', 'Last Checked At', 'Issued At',
            ]);

            License::with(['customer', 'plan'])->orderBy('created_at')->chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $license) {
                    fputcsv($handle, [
                        $license->license_key,
                        $license->customer->name ?? '',
                        $license->customer->email ?? '',
                        $license->plan->name ?? '',
                        $license->status,
                        $license->bound_domain ?? '',
                        optional($license->trial_ends_at)->format('Y-m-d'),
                        optional($license->current_period_ends_at)->format('Y-m-d'),
                        optional($license->last_checked_at)->format('Y-m-d H:i'),
                        $license->created_at->format('Y-m-d'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Clears a tamper flag after the admin confirms it was a false alarm
     *  (a legitimate product update, not real tampering). The NEXT check-in
     *  captures a fresh trusted baseline automatically. */
    public function resetFingerprint(License $license)
    {
        $license->resetFingerprintBaseline();
        LicenseActivity::log($license, 'fingerprint_reset', 'Tamper flag cleared by admin; a new trusted baseline will be captured on the next check-in.', 'admin');

        return back()->with('success', 'Fingerprint baseline reset.');
    }
}
