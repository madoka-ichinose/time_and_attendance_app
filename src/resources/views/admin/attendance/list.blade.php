@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('title', '勤怠一覧（管理者）')

@section('content')
<div class="attendance-list">
    <h2> {{ $date->format('Y年n月j日') }}の勤怠</h2>

    <div class="attendance-day">
        <form method="GET" action="{{ route('admin.attendance.list') }}">
        <button type="submit" class="day-nav" name="date" value="{{ $date->copy()->subDay()->toDateString() }}">← 前日</button>
        </form>

        <div class="day-current">
        <i class="fa-regular fa-calendar"></i>
        <span>{{ $date->format('Y/m/d') }}</span>
        </div>

        <form method="GET" action="{{ route('admin.attendance.list') }}">
        <button type="submit" class="day-nav" name="date" value="{{ $date->copy()->addDay()->toDateString() }}">翌日 →</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($attendances as $attendance)
<tr>
    <td>{{ $attendance['user_name'] ?? '―' }}</td>
    <td>{{ $attendance['clock_in'] !== '--:--' ? $attendance['clock_in'] : '―' }}</td>
    <td>{{ $attendance['clock_out'] !== '--:--' ? $attendance['clock_out'] : '―' }}</td>
    <td>{{ $attendance['break_time'] ?? '―' }}</td>
    <td>{{ $attendance['work_time'] ?? '―' }}</td>
    <td>
        @if (!empty($attendance['id']))
            <a href="{{ route('admin.attendance.detail', ['attendance' => $attendance['id']]) }}">詳細</a>
        @else
            <span class="no-link">詳細</span>
        @endif
    </td>
</tr>
@endforeach

        </tbody>
    </table>
</div>
@endsection
