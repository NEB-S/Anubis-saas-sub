<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\License;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $activeLicenses = License::where('status', 'active')->count();
        $trialLicenses = License::where('status', 'trial')->count();
        $canceledLicenses = License::where('status', 'canceled')->count();
        $pastDueLicenses = License::where('status', 'past_due')->count();
        $tamperedLicenses = License::whereNotNull('tamper_detected_at')->count();

        // Estimated recurring revenue, normalized to a monthly figure even
        // though plans have different cycle lengths (Basic=30d, Advanced=180d,
        // Premium=365d) — e.g. a $40 Advanced plan on a 180-day cycle counts
        // as roughly $6.67/month here, not $40/month.
        $monthlyRevenueCents = License::where('status', 'active')
            ->with('plan')
            ->get()
            ->sum(function (License $license) {
                if (!$license->plan || !$license->plan->default_period_days) {
                    return 0;
                }
                $months = $license->plan->default_period_days / 30;

                return $months > 0 ? $license->plan->price_monthly_cents / $months : 0;
            });

        $recentActivity = \App\Models\LicenseActivity::with('license.customer')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalCustomers', 'activeLicenses', 'trialLicenses',
            'canceledLicenses', 'pastDueLicenses', 'tamperedLicenses', 'monthlyRevenueCents', 'recentActivity'
        ));
    }
}
