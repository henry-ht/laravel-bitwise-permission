@extends("bwp::layout")
@section("title","Editar Menú")
@section("content")
@livewire("bwp-menus-form",["menuId"=>$menuId])
@endsection
