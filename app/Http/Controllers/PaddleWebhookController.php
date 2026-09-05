<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaddleWebhookController extends Controller
{
    /**
     * POST /webhooks/paddle
     *
     * SETUP (do this once in the Paddle dashboard, Developer Tools > Notifications):
     *   1. Add this URL as a webhook destination.
     *   2. Subscribe to: subscription.activated, subscription.updated,
     *      subscription.canceled, subscription.past_due.
     *   3. Copy the "Notification Signing Secret" it gives you into this
     *      project's .env as PADDLE_WEBHOOK_SECRET.
     *
     * IMPORTANT — signature verification is deliberately left as a clearly marked
     * TODO below rather than guessed at: Paddle's exact header name and signing
     * algorithm are specific to your Paddle account version (Classic vs Billing),
     * and getting this wrong silently accepts forged requests. Copy the verification
     * snippet straight from your Paddle dashboard's webhook docs page before this
     * goes live — do not skip it.
     */
    public function handle(Request $request)
    {
        // TODO: verify the webhook signature here before trusting the payload.
        // See: https://developer.paddle.com/webhooks/signature-verification
        // if (!$this->verifySignature($request)) {
        //     abort(401);
        // }

        $event = $request->input('event_type');
        $data = $request->input('data', []);

        Log::info('Paddle webhook received', ['event' => $event]);

        match ($event) {
            'subscription.activated', 'subscription.updated' => $this->activateOrRenew($data),
            'subscription.canceled' => $this->cancel($data),
            'subscription.past_due' => $this->markPastDue($data),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    protected function activateOrRenew(array $data): void
    {
        $subscriptionId = $data['id'] ?? null;
        $priceId = $data['items'][0]['price']['id'] ?? null;
        $customerEmail = $data['custom_data']['email'] ?? null;
        // The next_billed_at date tells us exactly when the current paid period ends —
        // this is what current_period_ends_at should be set to, not "now + 30 days",
        // so it stays accurate even if Paddle's billing cycle isn't exactly monthly.
        $periodEnd = $data['next_billed_at'] ?? null;

        $license = License::where('paddle_subscription_id', $subscriptionId)->first();

        if (!$license) {
            // First payment for this subscription — find the license by the customer
            // email you passed as custom_data when building the checkout link.
            // (See the pricing-page note further down for how that gets set.)
            $license = License::whereHas('customer', fn ($q) => $q->where('email', $customerEmail))
                ->whereNull('paddle_subscription_id')
                ->latest()
                ->first();
        }

        if (!$license) {
            Log::warning('Paddle webhook: no matching license found', ['subscription_id' => $subscriptionId]);
            return;
        }

        $plan = Plan::where('paddle_price_id', $priceId)->first();

        $license->update([
            'paddle_subscription_id' => $subscriptionId,
            'plan_id' => $plan?->id ?? $license->plan_id,
            'status' => 'active',
            'current_period_ends_at' => $periodEnd ?: now()->addMonth(),
        ]);
    }

    protected function cancel(array $data): void
    {
        License::where('paddle_subscription_id', $data['id'] ?? null)
            ->update(['status' => 'canceled']);
    }

    protected function markPastDue(array $data): void
    {
        // Payment failed but Paddle is retrying — don't cancel yet, just flag it.
        // The license's current_period_ends_at still governs access, so the site
        // keeps working until that date passes for real.
        License::where('paddle_subscription_id', $data['id'] ?? null)
            ->update(['status' => 'past_due']);
    }
}
