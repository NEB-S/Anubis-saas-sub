<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Plan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PortalDashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $licenses = $customer->licenses()->with(['plan', 'pendingPlan'])->latest()->get();
        $allPlans = Plan::where('is_active', true)->get();

        return view('portal.dashboard', compact('customer', 'licenses', 'allPlans'));
    }

    /**
     * TODO (Phase H): replace this stub with a real Paddle Checkout redirect
     * once you have a live Paddle account and Price IDs set on each Plan
     * (see the "paddle_price_id" column already on the plans table).
     * Until then, this is intentionally honest rather than fake — it does
     * NOT pretend to process a payment.
     */
    public function renew(License $license)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($license->customer_id === $customer->id, 403);

        return back()->with('info', 'Online renewal is coming soon. Please contact us to renew this license for now.');
    }

    public function invoice(License $license)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($license->customer_id === $customer->id, 403);

        $license->load(['customer', 'plan']);
        $pdf = Pdf::loadView('invoices.license', compact('license'));

        return $pdf->download("invoice-{$license->license_key}.pdf");
    }

    /** The "download + setup" page — the whole point of this is that the
     *  customer never has to type or even see their key manually. The
     *  activation command shown here already has THEIR key baked in;
     *  they only ever copy-paste it. */
    public function getStarted(License $license)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($license->customer_id === $customer->id, 403);

        $downloadUrl = config('services.product_download_url');
        $activateCommand = "php artisan license:activate {$license->license_key}";

        return view('portal.get-started', compact('license', 'downloadUrl', 'activateCommand'));
    }
}
