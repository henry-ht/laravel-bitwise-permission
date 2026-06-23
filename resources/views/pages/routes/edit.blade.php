@extends("bwp::layout")
@section("title","Editar Ruta")
@section("content")
@livewire("bwp-routes-form",["routeId"=>$routeId])
@endsection
