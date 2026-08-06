@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Editar usuario</h1>
        <p class="page-subtitle">{{ $user->name }}</p>
    </div>
</div>

<article class="card">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('users._form')
    </form>
</article>
@endsection
