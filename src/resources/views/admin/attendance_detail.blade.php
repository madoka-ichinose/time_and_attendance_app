@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    <form method="POST" action="{{ route('admin.attendance.update', ['attendance' => $attendance->id]) }}">
        @csrf
        @method('PUT')

        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}">
                    〜
                    <input type="time" name="clock_out" value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}">
                </td>
            </tr>

            @foreach($attendance->breaks as $index => $break)
            <tr>
            <th>休憩{{ $index + 1 }}</th>
            <td>
                <input type="time" name="breaks[{{ $index }}][start_time]" value="{{ old("breaks.$index.start_time", \Carbon\Carbon::parse($break->start_time)->format('H:i')) }}">
                〜
                <input type="time" name="breaks[{{ $index }}][end_time]" value="{{ old("breaks.$index.end_time", \Carbon\Carbon::parse($break->end_time)->format('H:i')) }}">
            </td>
            </tr>
            @endforeach

            @if(count($attendance->breaks) < 2)
            <tr>
            <th>休憩2</th>
            <td>
                <input type="time" name="breaks[1][start_time]">
                〜
                <input type="time" name="breaks[1][end_time]">
            </td>
            </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                </td>
            </tr>
        </table>

        <div class="submit-button">
            <button type="submit">修正</button>
        </div>
    </form>
</div>
@endsection
