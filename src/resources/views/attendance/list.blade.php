@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <h1>勤怠一覧</h1>

    <div class="attendance-month">
        <a href="{{ $prevMonthUrl }}">← 前月</a>
        <span style="margin: 0 20px; font-weight: bold;">{{ $currentMonth }}</span>
        <a href="{{ $nextMonthUrl }}">翌月 →</a>
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
@endsection
