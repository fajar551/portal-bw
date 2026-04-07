<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('register-title') | {{ Cfg::getValue('CompanyName') }} Admin Area CBMS Auto</title>
    @include('includes.style')
</head>

<body>
    @yield('content')

    @include('includes.scripts-global')
</body>

</html>
