<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1"
          name="viewport">
    <meta content="{{ csrf_token() }}"
          name="csrf-token" />

    <title> {{ config('app.name') }}</title>

    <link href="{{ asset('img/favicon.ico') }}"
          rel="icon"
          type="image/x-icon">

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
          rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
          rel="stylesheet" />

    @vite('resources/scss/adminlte.scss')
</head>

<body>

    <div class="vh-100 d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6">
                    <div class="text-center mb-3">
                        <img alt="Logo"
                             class="w-75"
                             src="{{ asset('img/logo.png') }}">
                    </div>
                    <a class="btn btn-primary btn-block btn-lg mb-2"
                       href="{{ route('auth.login') }}">Go to Admin Dashboard</a>
                    <a class="btn btn-outline-primary btn-block btn-lg"
                       href="https://jaf.zenby.fun">Go to Shop</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
