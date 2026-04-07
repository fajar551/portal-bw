<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

   @yield('title')

   @include('includes.style')

   @include('includes.navbar-vertical')

   @yield('content')

   @include('includes.footer')

   @routes
   @include('includes.scripts-global')
   
   @yield('scripts')
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @if(Session::has('alert'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '{{ Session::get("alert.type") }}',
                title: '{{ Session::get("alert.title") }}',
                text: '{{ Session::get("alert.message") }}',
                showConfirmButton: true,
                timer: 5000,
                timerProgressBar: true
            });
        });
    </script>
    @endif

   </body>

</html>
