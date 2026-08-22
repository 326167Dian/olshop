@extends('backend.layouts.app')

@section('title', 'Detail Customer')
@section('header', 'Detail Data Customer')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-8 mx-auto">
                <div class="card card-primary shadow">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Customer</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered table-striped w-100">
                            <tr>
                                <th width="30%">Nama</th>
                                <td>{{ $customer->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $customer->email }}</td>
                            </tr>
                            <tr>
                                <th>Telepon</th>
                                <td>{{ $customer->no_tlp ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $customer->alamat ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('customer.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>
@endsection