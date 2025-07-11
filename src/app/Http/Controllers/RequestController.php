<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RequestApplication;

class RequestController extends Controller
{
    public function index(Request $request)
{
    $status = $request->get('status', 'waiting');

    $requests = RequestApplication::with(['attendance', 'user'])
        ->where('user_id', auth()->id()) 
        ->where('status', $status === 'waiting' ? '承認待ち' : '承認済み')
        ->orderBy('applied_at', 'desc')
        ->get();

    return view('request.list', compact('requests'));
}
}
