<?php

use App\Http\Controllers\LicenseController;
use App\Http\Controllers\PaddleWebhookController;
use Illuminate\Support\Facades\Route;

// Public — called by every customer's sro-cms installation. No auth token here
// on purpose: the license_key itself IS the credential, same as an API key.
// Rate-limited so someone can't hammer it trying to guess keys.
Route::post('/license/check', [LicenseController::class, 'check'])->middleware('throttle:20,1');

// Called by the customer's SERVER (not their browser) right after the
// activation wizard redirects back — see LicenseController::exchange().
Route::post('/license/exchange', [LicenseController::class, 'exchange'])->middleware('throttle:10,1');

// Called by Paddle, not by anyone's browser. Signature verification lives inside
// the controller — see the TODO there before going live.
Route::post('/webhooks/paddle', [PaddleWebhookController::class, 'handle']);
