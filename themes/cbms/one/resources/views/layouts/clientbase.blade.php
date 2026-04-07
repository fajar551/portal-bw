<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
$companyName = Cfg::getValue('CompanyName') ?? 'CBMS Auto';
// dd($companyName);
@endphp

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <title>Client Area {{ $companyName }} - @yield('title')</title>
   @include('includes.style')
   @yield('styles')
   @routes
   <script src="{{ Theme::asset('assets/js/jquery.min.js') }}"></script>
   @if (isset($headoutput))
    {!!$headoutput!!}
   @endif
</head>

<body>
   <div id="layout-wrapper">

      @include('includes.navbar')

      @include('includes.sidebar')

      <div class="main-content">
        @if (isset($headeroutput) && $headeroutput != "")
            <div class="page-content">
                <div class="container-fluid">
                    {!!$headeroutput!!}
                </div>
            </div>
        @endif

         @yield('content')

      </div>

   </div>

   @include('includes.footer')

   <div class="menu-overlay"></div>

   @include('includes.scripts-global')

   @yield('scripts')

   @include('includes.generate-password')

    @if (isset($footeroutput))
        {!!$footeroutput!!}
    @endif
</body>

</html>
