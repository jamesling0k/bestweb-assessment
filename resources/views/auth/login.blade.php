@extends('layouts.app')
@section('title', 'Admin sign in')
@section('content')
    <section class="panel login-panel">
        <h1>Admin sign in</h1>
        <p class="muted">Sign in to manage products.</p>
        <form method="POST" action="{{ route('login.store') }}" class="form-stack">
            @csrf
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" maxlength="255" required autofocus>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button class="button" type="submit">Sign in</button>
        </form>
    </section>
@endsection
