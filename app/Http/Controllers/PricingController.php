<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        // Every active plan shows here now, including Trial — the customer
        // picks whichever one fits, right alongside the paid plans.
        $plans = Plan::where('is_active', true)->orderBy('price_monthly_cents')->get();

        return view('public.pricing', compact('plans'));
    }

    /**
     * Kept for any old links pointing here, but the pricing page's buttons
     * now link straight to registration with the plan preselected — see
     * PortalAuthController::register() for how a paid plan picked at signup
     * gets issued as a real active subscription (not a trial) with no
     * payment yet, until Paddle is wired in.
     */
    public function subscribe(Request $request, Plan $plan)
    {
        return redirect()->route('portal.register', ['plan' => $plan->slug]);
    }
}
