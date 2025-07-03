@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h2>勤怠詳細</h2>

    <form method="POST" action="{{ route('admin.attendance.update', ['attendance' => $attendance->id ?? 0]) }}">
    @csrf
    @method('PUT')

    {{-- 新規作成時に必要な情報 --}}
    <input type="hidden" name="user_id" value="{{ $attendance->user_id }}">
    <input type="hidden" name="work_date" value="{{ $attendance->work_date }}">


        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</></td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                    〜
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">

                    @error('clock_in')
                    <div class="form__error">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                    <div class="form__error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            @for ($i = 0; $i < 2; $i++)
            <tr>
            <th>休憩{{ $i + 1 }}</th>
            <td>
            <input type="time" name="breaks[{{ $i }}][start_time]" 
                   value="{{ old("breaks.$i.start_time", isset($attendance->breaks[$i]) ? \Carbon\Carbon::parse($attendance->breaks[$i]->start_time)->format('H:i') : '') }}">
                    〜
            <input type="time" name="breaks[{{ $i }}][end_time]" 
                   value="{{ old("breaks.$i.end_time", isset($attendance->breaks[$i]) ? \Carbon\Carbon::parse($attendance->breaks[$i]->end_time)->format('H:i') : '') }}">

                   {{-- エラーメッセージ表示 --}}
                @error("breaks.$i.start_time")
                <div class="form__error">{{ $message }}</div>
                @enderror
                @error("breaks.$i.end_time")
                <div class="form__error">{{ $message }}</div>
                @enderror
            </td>
            </tr>
            @endfor

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" rows="3">{{ old('note', $attendance->note) }}</textarea>

                    @error('note')
                    <div class="form__error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

                    <div class="attendance-detail-button">
                        <button type="submit" class="btn-black">修正</button>
                    </div>
    </form>
</div>
@endsection
