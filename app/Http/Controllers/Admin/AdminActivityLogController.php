<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;

class AdminActivityLogController extends Controller
{
    public function index()
    {
        $activities = AdminActivity::with('admin')->latest()->paginate(40);

        return view('admin.activity-log', compact('activities'));
    }
}
