@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Network Issues</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Sidebar Shortcut -->
                     
                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Network Issues</h4>
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
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="form-group row">
                                            <label for="option-status" class="col-sm-2 col-form-label">Options</label>
                                            <div class="col-sm-12 col-lg-3">
                                                <select name="option" id="option-status" class="form-control">
                                                    <option value="">All</option>
                                                    <option value="Reported">Reported</option>
                                                    <option value="Investigating">Investigating</option>
                                                    <option value="In Progress">In Progress</option>
                                                    <option value="Outage">Outage</option>
                                                    <option value="Scheduled">Scheduled</option>
                                                    <option value="Resolved">Resolved</option>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <a href="{{ url('admin/support/networkissues/add') }}">
                                                    <button class="btn btn-outline-success px-5 float-lg-right">
                                                        <span class="align-middle"><i class="ri-add-fill"></i></span> Create
                                                        New
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                        <hr>
                                        <h4 class="card-title">Open Issues</h4>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="network" class="table table-bordered dt-responsive w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>Title</th>
                                                                <th>Type</th>
                                                                <th>Priority</th>
                                                                <th>Status</th>
                                                                <th>Start Date</th>
                                                                <th>End Date</th>
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
                    <!-- End MAIN CARD -->
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
    <script type="text/javascript">
    $(document).ready(function () {
        var data_network=function(filter=[]){
            if (! $.fn.dataTable.isDataTable('#network')){
                var option=$('#option-status').val();
                var tbl = $('#network').DataTable({
                    paging: true,
                    processing: true,
                    serverSide: true,
                    ajax:{
                        url : '{{ url('admin/support/networkissues') }}',
                        type: 'POST',
                        headers : {
                            'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                        },
                        data : {
                            option: option
                        },
                        dataType: 'json',
                },
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                    searching: false,
                },
                columns : [
                    {data: 'title', name: 'title'},
                    {data: 'type', name: 'type'},
                    {data: 'priority', name: 'priority'},
                    {data: 'status', name: 'status'},
                    {data: 'startdate', name: 'startdate'},
                    {data: 'enddate', name: 'enddate'},
                    {data: 'action', name: 'action', orderable: false, searchable: false,width:'100px'},
                    /* {data: 'delete', name: 'delete', orderable: false, searchable: false}, */
                ],
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
            
            });
                
            }
            
        };

        /* $('#network').DataTable({
            paging: true,
            processing: true,
            serverSide: true,
            ajax:{
                url : '{{ url('admin/support/networkissues') }}',
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
                {data: 'title', name: 'title'},
                {data: 'type', name: 'type'},
                {data: 'priority', name: 'priority'},
                {data: 'status', name: 'status'},
                {data: 'startdate', name: 'startdate'},
                {data: 'enddate', name: 'enddate'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
                //{data: 'delete', name: 'delete', orderable: false, searchable: false}, 
            ],
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
            },
        }) */

        data_network();
        //option-status
        
        $('#option-status').change(function() {
            $('#network').dataTable().fnDestroy();
            data_network();
            return false;
        });



        $('#network').on('click', '.delete', function (){
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
