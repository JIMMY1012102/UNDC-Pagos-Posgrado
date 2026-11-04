@extends('layouts.market')
@section('marketPage', 'catalog')
@section('heroes')
@include('modules.tienda.partials.heroes')
@endsection
@section('content')
@include('modules.tienda.partials.catalog')
@endsection
