@extends("bwp::layout")
@section("title","Editar Acceso")
@section("content")
@livewire("bwp-accesses-form",["accessId"=>$accessId])
@endsection
