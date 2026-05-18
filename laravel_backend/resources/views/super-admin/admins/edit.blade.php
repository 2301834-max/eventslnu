@extends('super-admin.layout')

@section('title', 'Edit Admin')

@section('content')
@include('super-admin.admins.form', [
    'action' => route('super-admin.admins.update', $admin),
    'method' => 'PUT',
    'admin' => $admin,
    'submitLabel' => 'Update Admin',
])
@endsection
