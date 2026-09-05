<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseActivity;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalSettingsController extends Controller
{
    public function edit()
    {
        $customer = Auth::guard('customer')->user();

        return view('portal.settings', compact('customer'));
    }

    public function updateName(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);

        $old = $customer->name;
        $customer->update(['name' => $data['name']]);

        // Logged against every license this customer owns, since the log lives
        // on the License model — a name change isn't license-specific, but this
        // keeps it visible wherever the admin is already looking.
        foreach ($customer->licenses as $license) {
            LicenseActivity::log($license, 'name_changed', "Customer changed their name from \"{$old}\" to \"{$data['name']}\".", 'customer');
        }

        return back()->with('success', 'Name updated.');
    }

    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->input('current_password'), $customer->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $customer->update(['password' => Hash::make($request->input('password'))]);

        foreach ($customer->licenses as $license) {
            LicenseActivity::log($license, 'password_changed', 'Customer changed their password.', 'customer');
        }

        return back()->with('success', 'Password updated.');
    }

    /** Customer asks to move their license to a new domain. Doesn't touch
     *  bound_domain directly — sits in pending_domain until an admin approves
     *  it from the dashboard, per your decision that this needs admin sign-off. */
    public function requestDomainChange(Request $request, License $license)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($license->customer_id === $customer->id, 403);

        $data = $request->validate(['domain' => ['required', 'string', 'max:255']]);

        $license->update(['pending_domain' => strtolower($data['domain'])]);
        LicenseActivity::log($license, 'domain_change_requested', "Customer requested domain change to \"{$data['domain']}\".", 'customer');

        return back()->with('info', 'Domain change requested. Waiting for admin approval.');
    }

    /** Customer asks to move their license to a different plan (upgrade or
     *  downgrade). Doesn't touch plan_id directly — sits in pending_plan_id
     *  until an admin approves it, same pattern as the domain change above.
     *  Needed because there's no live payment processing yet: an instant
     *  self-service upgrade would let someone claim a pricier plan's
     *  features without actually paying for them. */
    public function requestPlanChange(Request $request, License $license)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($license->customer_id === $customer->id, 403);

        $data = $request->validate(['plan_id' => ['required', 'exists:plans,id']]);

        if ((int) $data['plan_id'] === $license->plan_id) {
            return back()->withErrors(['plan_id' => 'That is already your current plan.']);
        }

        $newPlan = Plan::find($data['plan_id']);
        $license->update(['pending_plan_id' => $newPlan->id]);
        LicenseActivity::log($license, 'plan_change_requested', "Customer requested a change from {$license->plan->name} to {$newPlan->name}.", 'customer');

        return back()->with('info', "Plan change to {$newPlan->name} requested. Waiting for admin approval.");
    }
}
