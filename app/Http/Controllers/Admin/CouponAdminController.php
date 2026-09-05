<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponAdminController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(25);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'extra_trial_days' => ['required', 'integer', 'min:0'],
            'discount_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));

        Coupon::create($data);
        AdminActivity::log('coupon_created', "Created coupon \"{$data['code']}\".");

        return redirect()->route('admin.coupons.index')->with('success', "Coupon {$data['code']} created.");
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        AdminActivity::log('coupon_toggled', "Coupon \"{$coupon->code}\" " . ($coupon->is_active ? 'activated' : 'deactivated') . '.');

        return back()->with('success', 'Coupon ' . ($coupon->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();
        AdminActivity::log('coupon_deleted', "Deleted coupon \"{$code}\".");

        return back()->with('success', 'Coupon deleted.');
    }
}
