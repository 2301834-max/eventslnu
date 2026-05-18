@extends('super-admin.layout')

@section('title', 'Create Admin')

@section('content')
@include('super-admin.admins.form', [
    'action' => route('super-admin.admins.store'),
    'method' => 'POST',
    'admin' => null,
    'submitLabel' => 'Create Admin',
])
@endsection
