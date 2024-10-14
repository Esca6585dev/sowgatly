<?php

namespace App\Http\Controllers\AdminControllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function dashboard($categoryType)
    {
        $users = User::count();

        return view('admin-panel.dashboard.dashboard', compact('categoryType', 'users'));
    }
}
