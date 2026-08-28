@extends('inventory.layouts.app')

@section('header', 'Log Login/Logout')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Log Login/Logout</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.admin.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>IP Address</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $index => $log)
                        @php
                            $duration = 'Active';
                            if ($log->logout_time) {
                                $diff = strtotime($log->logout_time) - strtotime($log->login_time);
                                $duration = sprintf('%02d:%02d:%02d', floor($diff / 3600), floor(($diff % 3600) / 60), $diff % 60);
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $log->username }}</td>
                            <td>{{ $log->login_time }}</td>
                            <td>{{ $log->logout_time ?? 'Still Active' }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $duration }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
