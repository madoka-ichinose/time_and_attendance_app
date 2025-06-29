@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">

@section('content')
<div class="request-form">
<h2>申請一覧</h2>

<div class="tab">
<a href="?status=waiting" class="{{ request('status') == 'waiting' ? 'active' : '' }}">承認待ち</a>
<a href="?status=approved" class="{{ request('status') == 'approved' ? 'active' : '' }}">承認済み</a>
</div>

<table>
    <thead>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach($requests as $request)
        <tr>
            <td>{{ $request->status }}</td>
            <td>{{ $request->user->name }}</td>
            <td>
            {{ $request->work_date ? \Carbon\Carbon::parse($request->work_date)->format('Y/m/d') : '―' }}
            </td>
            <td>{{ $request->reason }}</td>
            <td>{{ \Carbon\Carbon::parse($request->applied_at)->format('Y/m/d') }}</td>
            <td>
    @if ($request->attendance)
        <a href="{{ route('attendance.detail', ['id' => $request->attendance->id]) }}">詳細</a>
    @elseif ($request->work_date)
        <a href="{{ route('attendance.createOrEdit', ['date' => $request->work_date]) }}">詳細</a>
    @else
        ―
    @endif
</td>



        </tr>
        @endforeach
    </tbody>
</table>

</div>
@endsection
