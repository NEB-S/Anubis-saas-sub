<?php

namespace App\Http\Controllers;

use App\Models\ActivationCode;
use App\Models\License;
use App\Models\LicenseActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LicenseController extends Controller
{
    /**
     * POST /api/license/check
     * Body: { "license_key": "ANRX-XXXX-...", "domain": "customerdomain.com" }
     *
     * Called by the License Client (Phase 2) on every heartbeat. Deliberately returns
     * plain, predictable JSON — the client side only needs "valid" and "reason", the
     * rest (plan name, features) is there so the client can show a helpful message
     * or gate features without a second request.
     */
    public function check(Request $request)
    {
        $data = $request->validate([
            'license_key' => ['required', 'string'],
            'domain' => ['required', 'string'],
            // Optional — older client versions won't send this, and that's fine,
            // checkFileFingerprint() below just skips the comparison in that case.
            'file_hashes' => ['nullable', 'array'],
            'php_version' => ['nullable', 'string', 'max:20'],
            'laravel_version' => ['nullable', 'string', 'max:20'],
            'os' => ['nullable', 'string', 'max:50'],
            'installation_id' => ['nullable', 'string', 'max:64'],
            'visit_delta' => ['nullable', 'integer', 'min:0'],
        ]);

        $license = License::with('plan')
            ->where('license_key', $data['license_key'])
            ->first();

        if (!$license) {
            return response()->json([
                'valid' => false,
                'reason' => 'not_found',
            ], 404);
        }

        $result = $license->checkValidity($data['domain']);

        // First-ever successful check binds the key to whichever domain asked —
        // per your decision to lock each license to one domain. Stored lowercased
        // to match the case-insensitive comparison in License::checkValidity().
        if ($result['valid'] && $license->bound_domain === null) {
            $license->bound_domain = strtolower($data['domain']);
        }

        $license->last_checked_at = now();
        $license->last_checked_ip = $request->ip();
        $license->php_version = $data['php_version'] ?? $license->php_version;
        $license->laravel_version = $data['laravel_version'] ?? $license->laravel_version;
        $license->os = $data['os'] ?? $license->os;
        $license->installation_id = $data['installation_id'] ?? $license->installation_id;
        $license->visit_count = ($license->visit_count ?? 0) + ($data['visit_delta'] ?? 0);
        $license->save();

        $newlyTampered = $license->checkFileFingerprint($data['file_hashes'] ?? null);
        if ($newlyTampered) {
            LicenseActivity::log($license, 'tamper_detected', 'Licensing file hashes no longer match the trusted baseline — the site\'s licensing files may have been modified.', null);
        }

        return response()->json([
            'valid' => $result['valid'],
            'reason' => $result['reason'],
            'plan' => $license->plan->slug,
            'features' => $license->plan->features ?? [],
            'status' => $license->status,
            // The client uses this to know how long it can safely run offline
            // before treating the license as expired — see Phase 2's grace period logic.
            'current_period_ends_at' => optional($license->current_period_ends_at)->toIso8601String(),
        ], $result['valid'] ? 200 : 403);
    }

    /**
     * POST /api/license/exchange
     * Body: { "code": "..." }
     *
     * Called by the CUSTOMER'S SERVER (not their browser) right after the
     * activation wizard redirects back with a one-time code. This is the
     * only place the real license_key is ever returned during the wizard
     * flow — the browser itself never sees it, only this short-lived code.
     *
     * Known limitation, stated plainly: this endpoint trusts whoever holds
     * a valid, unused, unexpired code — there's no additional secret
     * binding it to a specific installation. That's an acceptable tradeoff
     * for now (the code is single-use and expires in 5 minutes), but if you
     * want to close that gap later, add a per-installation shared secret
     * exchanged out-of-band and require it here too.
     */
    public function exchange(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $activationCode = ActivationCode::where('code', $data['code'])->first();

        if (!$activationCode || !$activationCode->isValid()) {
            return response()->json(['error' => 'invalid_or_expired_code'], 400);
        }

        $activationCode->update(['used_at' => now()]);
        $license = $activationCode->license;

        LicenseActivity::log($license, 'activated_via_wizard', 'License key issued to the installation via the activation wizard exchange.', 'customer');

        return response()->json([
            'license_key' => $license->license_key,
            'server_url' => config('app.url'),
        ]);
    }
}
