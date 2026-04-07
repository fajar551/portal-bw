<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('title')

    @include('includes.style')

    <body data-topbar="dark" data-layout="horizontal">
    
    <!-- Begin page -->
    <div id="layout-wrapper">

        @yield('content')

        @include('includes.footer')

    </div>
    <!-- END layout-wrapper -->

    @include('includes.scripts-global')

    @yield('scripts')

    </body>

</html>