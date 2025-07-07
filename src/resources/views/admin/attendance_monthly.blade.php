@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h2 class="page-title">{{ $user->name }}さんの勤怠</h2>

    <div class="attendance-month">
        <a href="{{ route('admin.attendance.monthly', ['user' => $user->id, 'month' => $prevMonth]) }}" class="month-nav">← 前月</a>
        <span class="month-current">{{ $currentMonth->format('Y/m') }}</span>
        <a href="{{ route('admin.attendance.monthly', ['user' => $user->id, 'month' => $nextMonth]) }}" class="month-nav">翌月 →</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $att)
                <tr>
                    <td>{{ $att['display_date'] }}</td>
                    <td>{{ $att['clock_in'] }}</td>
                    <td>{{ $att['clock_out'] }}</td>
                    <td>{{ $att['break_time'] }}</td>
                    <td>{{ $att['work_time'] }}</td>
                    <td>
                    @if (!empty($att['id']))
                        <a href="{{ route('admin.attendance.detail', ['user_id' => $user->id, 'work_date' => $att['date']]) }}" class="detail-link">詳細</a>
                    @else
                        <span class="no-link">詳細</span>
                    @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <form method="GET" action="{{ route('admin.attendance.csv') }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">
        <button type="submit" class="csv-button">CSV出力</button>
    </form>
</div>
@endsection
