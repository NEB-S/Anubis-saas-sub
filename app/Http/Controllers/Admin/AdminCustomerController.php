<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Customer;
use App\Models\CustomerActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminCustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withCount('licenses')->latest()->paginate(25);

        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['licenses.plan', 'licenses.activities', 'activities']);

        return view('admin.customers.show', compact('customer'));
    }

    public function updateNotes(Request $request, Customer $customer)
    {
        $data = $request->validate(['admin_notes' => ['nullable', 'string', 'max:5000']]);

        $customer->update(['admin_notes' => $data['admin_notes']]);

        return back()->with('success', 'Note saved.');
    }

    /** Lets you set a customer's password directly — for when they've
     *  forgotten it and can't use the portal's own "forgot password" flow
     *  (there isn't one yet). You set it here and send it to them yourself
     *  through whatever channel you already use with them. */
    public function resetPassword(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        $customer->update(['password' => Hash::make($data['new_password'])]);

        CustomerActivity::log($customer, 'password_reset_by_admin', 'Password reset by admin.', 'admin');
        AdminActivity::log('customer_password_reset', "Reset the portal password for customer \"{$customer->name}\" ({$customer->email}).");

        return back()->with('success', "Password updated for {$customer->name}. Copy it now and send it to them — it won't be shown again.")
            ->with('new_password_plaintext', $data['new_password']);
    }

    /** Permanently deletes the customer AND every license they own — the
     *  licenses table's customer_id foreign key cascade-deletes on its own
     *  (set up back in Phase 1), so this one call cleans up everything:
     *  their licenses, and each license's own activity log, in one go. */
    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        $licenseCount = $customer->licenses()->count();
        $customer->delete();
        AdminActivity::log('customer_deleted', "Deleted customer \"{$name}\" and their {$licenseCount} license(s).");

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer \"{$name}\" and their {$licenseCount} license(s) deleted permanently.");
    }
}
