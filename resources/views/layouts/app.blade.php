<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Products') | BestWeb</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('head')
</head>
<body>
<header class="site-header">
    <nav class="container nav" aria-label="Main navigation">
        <a class="brand" href="{{ route('catalog.index') }}">BestWeb Products</a>
        <div class="nav-links">
            <a href="{{ route('catalog.index') }}">Catalog</a>
            <a href="{{ route('api.documentation') }}">API docs</a>
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.products.index') }}">Manage products</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="link-button" type="submit">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}">Admin sign in</a>
            @endauth
        </div>
    </nav>
</header>
<main class="container">
    @if(session('status'))
        <div class="notice success" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="notice error" role="alert">
            <strong>Please check the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
