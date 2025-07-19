<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->get();

        return view('admin.staff_list', compact('users'));
    }
}
