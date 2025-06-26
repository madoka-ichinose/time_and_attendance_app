@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="request-form">
    <h2>申請一覧</h2>

    <div>
        <a href="?status=waiting" class="{{ $status == 'waiting' ? 'active' : '' }}">承認待ち</a>
        <a href="?status=approved" class="{{ $status == 'approved' ? 'active' : '' }}">承認済み</a>
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
            @forelse($requests as $request)
            <tr>
                <td>{{ $request->status }}</td>
                <td>{{ $request->user->name }}</td>
                <td>{{ optional($request->attendance)->work_date ? \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') : '-' }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ \Carbon\Carbon::parse($request->applied_at)->format('Y/m/d') }}</td>
                <td>
                    @if ($request->attendance)
                        <a href="{{ route('admin.attendance.detail', ['attendance' => $request->attendance->id]) }}">詳細</a>
                    @else
                        <span class="no-link">詳細</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6">申請がありません。</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
