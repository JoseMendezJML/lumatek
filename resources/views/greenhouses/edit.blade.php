@extends('layouts.app')

@section('title', 'Editar invernadero')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Editar invernadero</h1>
        <p class="page-subtitle">{{ $greenhouse->name }}</p>
    </div>
</div>

<article class="card">
    <form method="POST" action="{{ route('greenhouses.update', $greenhouse) }}">
        @csrf
        @method('PUT')
        @include('greenhouses._form')
    </form>
</article>
@endsection
