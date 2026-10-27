@extends('layouts.app')
@section('title', 'API documentation')
@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui-bundle.js" defer></script>
    <script src="{{ asset('js/docs.js') }}" defer></script>
@endpush
@section('content')
    <h1>Product API</h1>
    <p>Use <code>POST /api/tokens</code> to sign in, then enter the token using Authorize.</p>
    <p><a href="{{ asset('openapi.json') }}">Download the OpenAPI specification</a>. The interactive viewer requires access to jsDelivr.</p>
    <div id="swagger-ui" data-spec-url="{{ asset('openapi.json') }}"></div>
@endsection
