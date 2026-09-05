<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\License;
use App\Models\LicenseActivity;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalAuthController extends Controller
{
    public function showLogin()
    {
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those credentials don\'t match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $intended = $request->session()->pull('url.intended');

        return redirect()->to($intended ?? route('portal.dashboard'));
    }

    public function showRegister(Request $request)
    {
        $plans = Plan::where('is_active', true)->orderBy('price_monthly_cents')->get();

        // Lets the pricing page's buttons pre-select a plan by passing
        // ?plan=slug, instead of the customer having to find it again here.
        $preselectedPlan = Plan::where('slug', $request->query('plan'))->first();

        return view('portal.register', compact('plans', 'preselectedPlan'));
    }

    public function register(Request $request)
    {
        // A Customer row may already exist without a password — created by
        // you issuing their first license from the admin dashboard, before
        // they ever set up portal access. Treat that as "claiming" the
        // account rather than blocking on the unique-email rule.
        $existing = Customer::where('email', $request->input('email'))->first();

        if ($existing && $existing->password !== null) {
            return back()->withErrors(['email' => 'An account with this email already exists. Please log in instead.'])->onlyInput('email');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan_id' => ['required', 'exists:plans,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);

        // Coupon only ever applies to the Trial plan — extra_trial_days
        // wouldn't mean anything on a real active subscription.
        $coupon = null;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::findValid($data['coupon_code']);
            if (!$coupon) {
                return back()->withErrors(['coupon_code' => 'This coupon code is invalid or has expired.'])->withInput();
            }
        }

        if ($existing) {
            $existing->update(['name' => $data['name'], 'password' => Hash::make($data['password'])]);
            $customer = $existing;
            CustomerActivity::log($customer, 'account_claimed', "Set up portal access for the account at {$data['email']} (previously created by admin).", 'customer');
        } else {
            $customer = Customer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            CustomerActivity::log($customer, 'account_created', "New account registered: {$data['name']} ({$data['email']}).", 'customer');
        }

        // ONE LICENSE PER CUSTOMER: if they already have one, never issue a
        // second — they just log into the account they already have.
        if ($customer->licenses()->count() === 0) {
            $isTrial = $plan->slug === 'trial';

            if ($isTrial) {
                $extraDays = $coupon ? $coupon->extra_trial_days : 0;

                $license = License::create([
                    'customer_id' => $customer->id,
                    'plan_id' => $plan->id,
                    'license_key' => License::generateKey(),
                    'status' => 'trial',
                    'trial_ends_at' => now()->addDays(7 + $extraDays),
                ]);

                $note = $coupon
                    ? "Self-service trial started at signup (7 + {$extraDays} bonus days via coupon \"{$coupon->code}\")."
                    : 'Self-service trial started at signup (7 days).';
                LicenseActivity::log($license, 'issued', $note, 'customer');

                if ($coupon) {
                    $coupon->increment('used_count');
                }
            } else {
                // A real plan, chosen at signup, with no live payment yet —
                // issued as a genuinely ACTIVE subscription (not a trial)
                // for that plan's real billing period. This is a trust
                // arrangement until Paddle is wired in; it is deliberately
                // NOT labeled or treated as free/trial anywhere.
                $license = License::create([
                    'customer_id' => $customer->id,
                    'plan_id' => $plan->id,
                    'license_key' => License::generateKey(),
                    'status' => 'active',
                    'current_period_ends_at' => now()->addDays($plan->default_period_days),
                ]);

                LicenseActivity::log($license, 'issued', "Self-service {$plan->name} subscription started at signup ({$plan->default_period_days} days, pending payment integration).", 'customer');
            }
        }

        Auth::guard('customer')->login($customer);

        $intended = $request->session()->pull('url.intended');

        return redirect()->to($intended ?? route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
