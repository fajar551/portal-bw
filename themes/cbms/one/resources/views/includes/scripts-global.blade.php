<script src="{{ Theme::asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ Theme::asset('assets/js/metismenu.min.js') }}"></script>
<script src="{{ Theme::asset('assets/js/waves.js') }}"></script>
<script src="{{ Theme::asset('assets/js/simplebar.min.js') }}"></script>
<script src="{{ Theme::asset('assets/plugins/bootstrap-dark/dark-mode-switch.min.js') }}"></script>
<script src="{{ Theme::asset('assets/plugins/katex/katex.min.js') }}"></script>
<!--<script src="{{ Theme::asset('assets/plugins/quill/quill.min.js') }}"></script>-->
   <script>
       document.addEventListener("DOMContentLoaded", function() {
           var snowEditor = document.getElementById('snow-editor');
           var bubbleEditor = document.getElementById('bubble-editor');

           if (snowEditor || bubbleEditor) {
               var script = document.createElement('script');
               script.src = "{{ url('clientarea/assets/plugins/quill/quill.min.js') }}";
               document.head.appendChild(script);

               script.onload = function() {
                   if (snowEditor) {
                       new Quill('#snow-editor', { theme: 'snow' });
                   }
                   if (bubbleEditor) {
                       new Quill('#bubble-editor', { theme: 'bubble' });
                   }
               };
           }
       });
   </script>
<script src="{{ Theme::asset('assets/plugins/loader/loadingoverlay.min.js') }}"></script>
<!--<script src="{{ Theme::asset('assets/pages/quilljs-demo.js') }}"></script>-->
@include('includes.datatables')
<!-- Sparkline Js-->
{{-- <script src="{{ asset('assets/plugins/jquery-sparkline/jquery.sparkline.min.js') }}"></script> --}}

{{-- <!-- Morris Js-->
<script src="{{ asset('assets/plugins/morris-js/morris.min.js') }}"></script>

<!-- Raphael Js-->
<script src="{{ asset('assets/plugins/raphael/raphael.min.js') }}"></script> --}}
<script src="{{ Theme::asset('assets/js/login-form.js') }}"></script>

<!-- Custom Js -->
<script src="{{ Theme::asset('assets/pages/dashboard-demo.js') }}"></script>

<!-- App js -->
<script src="{{ Theme::asset('assets/js/theme.js') }}"></script>
