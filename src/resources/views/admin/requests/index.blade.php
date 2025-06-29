@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="request-form">
    <h2>申請一覧</h2>

    <div class="tab">
        <a href="?status=承認待ち" class="{{ $status == '承認待ち' ? 'active' : '' }}">承認待ち</a>
        <a href="?status=承認済み" class="{{ $status == '承認済み' ? 'active' : '' }}">承認済み</a>
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
                <td>{{ $request->work_date }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ \Carbon\Carbon::parse($request->applied_at)->format('Y/m/d H:i') }}</td>
                <td>
                <a href="{{ route('admin.requests.detail', ['request' => $request->id]) }}">詳細</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6">申請がありません。</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
