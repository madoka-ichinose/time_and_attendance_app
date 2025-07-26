@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h2>勤怠詳細</h2>
    <div class="attendance-detail">
    <table class="attendance-detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $request->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($request->work_date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ $request->clock_in ? \Carbon\Carbon::parse($request->clock_in)->format('H:i') : '--:--' }}
                ～
                {{ $request->clock_out ? \Carbon\Carbon::parse($request->clock_out)->format('H:i') : '--:--' }}
            </td>
        </tr>
        @foreach ($request->breaks as $index => $break)
        <tr>
            <th>休憩{{ $index + 1 }}</th>
            <td>
                {{ \Carbon\Carbon::parse($break->start_time)->format('H:i') }} ～
                {{ \Carbon\Carbon::parse($break->end_time)->format('H:i') }}
            </td>
        </tr>
        @endforeach
        <tr>
            <th>備考</th>
            <td>{{ $request->reason }}</td>
        </tr>
    </table>
</div>
    @if ($request->status === '承認待ち')
    <div class="approve-container">
        <form method="POST" action="{{ route('admin.requests.approve', ['request' => $request->id]) }}">
            @csrf
            @method('PUT')
            <button type="submit" class="approve-button">承認</button>
        </form>
    <div>
    @else
    <div class="approve-container">
        <button class="approve-button" disabled>承認済み</button>
    </div>
    @endif
</div>
@endsection
