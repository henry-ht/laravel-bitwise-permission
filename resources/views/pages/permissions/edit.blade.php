@extends('bwp::layout')
@section('title', 'Editar Permiso')
@section('content')
@livewire('bwp-permissions-form', ['permissionId' => $permissionId])
@endsection