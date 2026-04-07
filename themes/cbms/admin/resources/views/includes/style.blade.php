<!-- App favicon -->
<link rel="shortcut icon" href="{{ Theme::asset('assets/images/favicon.ico') }}" />
<!-- jquery.vectormap css -->
<link href="{{ Theme::asset('assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}"
   rel="stylesheet" type="text/css" />
<!-- Summernote css -->
<link href="{{ Theme::asset('assets/libs/summernote/summernote-bs4.min.css') }}" rel="stylesheet" type="text/css" />
<!-- SweetAlert2 -->
<link href="{{ Theme::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
<!-- Colorpicker BS -->
<link href="{{ Theme::asset('assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css') }}"
   rel="stylesheet">
<!-- DataTables -->
<link href="{{ Theme::asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
   type="text/css" />
<link href="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}"
   rel="stylesheet" type="text/css" />
<link href="{{ Theme::asset('assets/libs/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}"
   rel="stylesheet" type="text/css" />
<link href="{{ Theme::asset('assets/libs/datatables.net-rowGroup-1.1.3/css/rowGroup.bootstrap4.min.css') }}"
   rel="stylesheet" type="text/css" />
<!-- Responsive datatable examples -->
<link href="{{ Theme::asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
   rel="stylesheet" type="text/css" />
<!-- select2 -->
<link href="{{ Theme::asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

<!-- Bootstrap Css -->
<link href="{{ Theme::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet"
   type="text/css" />
<!-- Bootstrap Toggle -->
<link href="{{ Theme::asset('assets/css/bootstrap4-toggle.min.css') }}" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ Theme::asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ Theme::asset('assets/css/app.css') }}" id="app-style" rel="stylesheet" type="text/css" />

{{-- Custom Styles --}}
@yield('styles')

<!-- Swiper Slide Css -->
<link href="{{ Theme::asset('assets/css/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
<!-- Simplebar -->
<link href="{{ Theme::asset('assets/libs/simplebar/simplebar.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ Theme::asset('assets/libs/bootstrap-markdown/css/bootstrap-markdown.min.css') }}" rel="stylesheet"
   type="text/css" />
<!-- daterangepicker -->
<link href="{{ Theme::asset('assets/css/daterangepicker.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ Theme::asset('assets/libs/bootstrap-duallistbox/css/bootstrap-duallistbox.min.css') }}"
   rel="stylesheet" type="text/css" />
<link href="{{ Theme::asset('assets/libs/magnific-popup/magnific-popup.css') }}" rel="stylesheet" type="text/css" />


<!-- Custom CSS -->
<link href="{{ Theme::asset('assets/css/int.css') }}" type="text/css" rel="stylesheet" />
<link href="{{ Theme::asset('assets/css/style.css') }}" type="text/css" rel="stylesheet" />
@stack('styles')

<script src="{{ Theme::asset('assets/libs/jquery/jquery.min.js') }}"></script>
