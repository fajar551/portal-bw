@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Announcements</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                     

                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Announcements</h4>
                                    </div>
                                </div>
                            </div>
                            @if(Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                                @php
                                    Session::forget('success');
                                @endphp
                            </div>
                            @endif
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12 d-flex justify-content-center">
                                                <a href="{{ url('admin/support/announcements/add') }}">
                                                    <button class="btn btn-success px-5">Add New
                                                        Announcements</button>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="announcements" class="table dt-responsive w-100">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th style="width: 30em;">Date</th>
                                                                <th style="width: 70em;">Title</th>
                                                                <th>Published</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
@endsection

@section('scripts')
    <!-- Required datatable js -->
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <!-- data -->
    <script type="text/javascript">
    $(document).ready(function () {
        $('#announcements').DataTable({
            paging: true,
            processing: true,
            serverSide: true,
            ajax:{
                url : '{{ url('admin/support/announcements') }}',
                type: 'POST',
                headers : {
                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                }
            },
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>",
                },
                searching: false,
            },
            columns : [
                {data: 'date', name: 'date'},
                {data: 'title', name: 'title'},
                {data: 'published', name: 'published'},
                {data: 'action', name: 'edit', orderable: false, searchable: false,width:'100px'},
               /*  {data: 'delete', name: 'delete', orderable: false, searchable: false}, */
            ],
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
            },
        })


        //delete announcements

        $('#announcements').on('click', '.delete', function (){
           // e.preventDefault();
            swal({
				title: "Warning..!",
				text: "Do you want to delete  "+$(this).data('title')+" ?",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then((value) => {
				if(value){
					window.location.href = $(this).attr('href');
				}else{
				
					return false;
				}
				
			});
			return false;


        });



    });
</script> 
@endsection
