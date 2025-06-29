@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h2>勤怠一覧</h2>

    <div class="attendance-month">
    <a href="{{ $prevMonthUrl }}" class="month-nav">← 前月</a>

    <div class="month-current">
        <i class="fa-regular fa-calendar"></i> {{-- Font Awesome --}}
        <span>{{ $currentMonth }}</span>
    </div>

    <a href="{{ $nextMonthUrl }}" class="month-nav">翌月 →</a>
    </div>

    <table>
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
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance['display_date'] }}</td>
                    <td>{{ $attendance['clock_in'] === '--:--' ? '' : $attendance['clock_in'] }}</td>
                    <td>{{ $attendance['clock_out'] === '--:--' ? '' : $attendance['clock_out'] }}</td>
                    <td>{{ $attendance['break_time'] === '--:--' ? '' : $attendance['break_time'] }}</td>
                    <td>{{ $attendance['work_time'] === '--:--' ? '' : $attendance['work_time'] }}</td>
                    <td>
                    <a href="{{ route('attendance.createOrEdit', ['date' => $attendance['date']]) }}" class="attendance__detail">詳細</a>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
