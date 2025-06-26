@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <div class="attendance-detail-box">
        <h2 class="attendance-title">勤怠詳細</h2>

        @if ($pendingRequest)
                <p class="text-danger" style="color: red; margin-top: 10px;">
                    ※承認待ちのため修正はできません。
                </p>

        <div class="attendance-detail">
            <table class="attendance-detail-table">
                <tr>
                    <th>名前</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                    {{ optional($pendingRequest)->work_date 
        ? \Carbon\Carbon::parse($pendingRequest->work_date)->format('Y/m/d') 
        : '―' }}
                </td>
                </tr>
                
                <tr>
                    <th>出勤・退勤</th>
                    <td> 
                    {{ $pendingRequest && $pendingRequest->clock_in
    ? \Carbon\Carbon::parse($pendingRequest->clock_in)->format('H:i')
    : '―' }}
〜
{{ $pendingRequest && $pendingRequest->clock_out
    ? \Carbon\Carbon::parse($pendingRequest->clock_out)->format('H:i')
    : '―' }}    
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        @if ($pendingRequest && $pendingRequest->breaks->count() > 0)
                            @foreach ($pendingRequest->breaks as $break)
                                {{ \Carbon\Carbon::parse($break->start_time)->format('H:i') }}
                                〜
                                {{ \Carbon\Carbon::parse($break->end_time)->format('H:i') }}<br>
                            @endforeach
                        @else
                            @foreach ($attendance->breaks as $break)
                                {{ \Carbon\Carbon::parse($break->start_time)->format('H:i') }}
                                〜
                                {{ \Carbon\Carbon::parse($break->end_time)->format('H:i') }}<br>
                            @endforeach
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>{{ $pendingRequest ? $pendingRequest->reason : $attendance->note }}</td>
                </tr>
            </table>

           
            @else
            <table class="attendance-detail-table">
            <tr>
                    <th>名前</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
                </tr>
                
                <form 
                    action="{{ $attendance->exists 
                        ? route('attendance.request.submit', ['id' => $attendance->id]) 
                        : route('attendance.store') }}" 
                    method="POST">
                    @csrf
                    <input type="hidden" name="work_date" value="{{ $attendance->work_date }}">

                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                                ～ 
                                <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                            </td>
                        </tr>

                        @php
                            $breaks = $attendance->breaks->count() > 0 ? $attendance->breaks : collect([null]);
                        @endphp

                        @foreach($breaks as $index => $break)
                        <tr>
                            <th>休憩{{ $index + 1 }}</th>
                            <td>
                                <input type="time" name="breaks[{{ $break->id ?? 'new_'.$index }}][start]"
                                    value="{{ $break && $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '' }}">
                                ～
                                <input type="time" name="breaks[{{ $break->id ?? 'new_'.$index }}][end]"
                                    value="{{ $break && $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '' }}">
                            </td>
                        </tr>
                        @endforeach

                        <tr>
                            <th>備考</th>
                            <td><input type="text" name="note" value="{{ old('note', $attendance->note) }}" /></td>
                        </tr>
                    </table>

                    @if ($errors->any())
                        <div class="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button type="submit">
                        {{ $attendance->exists ? '修正申請する' : '新規申請する' }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
