@extends('layouts.app')

@section('title', 'Registrar invernadero')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Registrar invernadero</h1>
        <p class="page-subtitle">Configura su información y rangos de operación.</p>
    </div>
</div>

<article class="card">
    <form method="POST" action="{{ route('greenhouses.store') }}">
        @csrf
        @include('greenhouses._form')
    </form>
</article>
@endsection
