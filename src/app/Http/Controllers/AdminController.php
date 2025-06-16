<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['role'] = 'admin'; // 管理者のみ

        if (Auth::attempt($credentials)) {
            return redirect('/admin/attendance/list');
        }

        return back()->withErrors();
    }

    public function index(Request $request)
    {
        $date = $request->input('date') 
            ? Carbon::parse($request->input('date')) 
            : Carbon::today();

        // 当日の勤怠データを全ユーザー分取得
        $attendances = User::with(['attendances' => function ($query) use ($date) {
            $query->whereDate('work_date', $date->toDateString());
        }])->get();

        return view('admin.attendance.list', [
            'date' => $date,
            'attendances' => $attendances
        ]);
    }
}
