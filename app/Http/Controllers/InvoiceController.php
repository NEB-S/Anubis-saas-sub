<?php

namespace App\Http\Controllers;

use App\Models\License;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function generate(License $license)
    {
        $license->load(['customer', 'plan']);

        $pdf = Pdf::loadView('invoices.license', compact('license'));

        return $pdf->download("invoice-{$license->license_key}.pdf");
    }
}
