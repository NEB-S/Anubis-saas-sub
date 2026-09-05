<?php

namespace App\Http\Controllers;

use App\Models\ActivationCode;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivationWizardController extends Controller
{
    /**
     * GET /activate?return_url=...&state=...
     *
     * One license per customer now, so there's no plan/license selection
     * here anymore — registration already granted them their one Trial
     * license (or an admin already issued their one real license). This
     * page just confirms activation of whatever they have.
     */
    public function show(Request $request)
    {
        $request->validate([
            'return_url' => ['required', 'string', 'url'],
            'state' => ['required', 'string'],
        ]);

        $customer = Auth::guard('customer')->user();
        $license = $customer->licenses()->with('plan')->first();

        return view('activation-wizard', [
            'license' => $license,
            'returnUrl' => $request->query('return_url'),
            'state' => $request->query('state'),
        ]);
    }

    /**
     * POST /activate — the customer confirmed. With one license per
     * customer, there's nothing to choose between — this always issues an
     * activation code for their single existing license.
     */
    public function confirm(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'return_url' => ['required', 'string', 'url'],
            'state' => ['required', 'string'],
        ]);

        $license = $customer->licenses()->first();

        if (!$license) {
            return back()->withErrors(['license' => 'No license found on your account. Please contact support.']);
        }

        $code = ActivationCode::issueFor($license);

        // The browser only ever sees this one-time code — never the real
        // license_key. See LicenseController::exchange() for where the
        // client trades this code for the actual key, server-to-server.
        $separator = str_contains($data['return_url'], '?') ? '&' : '?';
        $redirectTo = $data['return_url'] . $separator . http_build_query([
            'code' => $code->code,
            'state' => $data['state'],
        ]);

        return redirect()->away($redirectTo);
    }
}
