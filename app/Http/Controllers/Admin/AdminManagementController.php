<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = Admin::orderBy('name')->get();

        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_super_admin' => ['nullable', 'boolean'],
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_super_admin' => $request->boolean('is_super_admin'),
        ]);

        AdminActivity::log('admin_created', "Created admin account for {$admin->name} ({$admin->email}).");

        return redirect()->route('admin.admins.index')->with('success', "Admin account created for {$admin->name}.");
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->withErrors(['admin' => 'You cannot delete your own account while logged in as it.']);
        }

        $name = $admin->name;
        $admin->delete();

        AdminActivity::log('admin_deleted', "Deleted admin account for {$name}.");

        return back()->with('success', "Admin account for {$name} deleted.");
    }
}
