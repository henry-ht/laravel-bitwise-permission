@extends("bwp::layout")
@section("title","Editar Rol")
@section("content")
@livewire("bwp-roles-form",["roleId"=>$roleId])
@endsection
