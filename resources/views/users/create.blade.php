@extends('layouts.app')

@section('title', 'Registrar usuario')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Registrar usuario</h1>
        <p class="page-subtitle">Crea una cuenta y asigna sus permisos iniciales.</p>
    </div>
</div>

<article class="card">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        @include('users._form')
    </form>
</article>
@endsection
