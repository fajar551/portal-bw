@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Configurable Option Groups</title>
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
                                        <h4 class="mb-3">Configurable Option Groups</h4>
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
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="row ">
                                            <div class="col-lg-12">
                                                <p>Configurable options allow you to offer addons and customisation options
                                                    with your products. Options are assigned to groups and groups can then
                                                    be applied to products.</p>
                                            </div>
                                            <div class="col-lg-12 d-lg-flex">
                                                <a href="{{ url('admin/setup/productservices/configurableoptions/add') }}">
                                                    <button class="btn btn-outline-success mx-1 mb-3 px-3"><i class="fa fa-plus mr-2" aria-hidden="true"></i>Create a New Group</button>
                                                </a>
                                                <a href="{{ url('admin/setup/productservices/configurableoptions/duplicategroup') }}">
                                                    <button class="btn btn-outline-success mx-1 mb-3 px-3"><i class="fa fa-plus mr-2" aria-hidden="true"></i>Duplicate group</button>
                                                </a>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="table-reponsive">
                                                    <table id="config" class="table table-bordered dt-responsive w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>Group Name</th>
                                                                <th>Description</th>
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
    <script type="text/javascript">
        $(document).ready(function () {
           
            var config=function(filter=[]){
            if (! $.fn.dataTable.isDataTable('#configID') ) {
               


                var tbl =$('#config').DataTable({
                            paging: true,
                            processing: true,
                            serverSide: true,
                          
                            ajax:{
                                url : '{{ url(Request::segment(1).'/setup/productservices/configurableoptions') }}',
                                type: 'POST',
                                headers : {
                                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
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
                                
                                {data: 'name', name: 'name'},
                                {data: 'description', name: 'description'},
                                {data: 'id', name: 'action',orderable: false, searchable: false,width:'80px',
                                    render: (data, type, row) => {
                                        
                                            return `
                                                 <form id="fd`+data+`" action="{{ url(Request::segment(1).'/setup/productservices/configurableoptions/destroy') }}" method="POST">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <input type="hidden" name="id" value="`+data+`">
                                                    <a title="Edit `+type.name+`" href="{{ url(Request::segment(1).'/setup/productservices/configurableoptions/edit') }}/`+data+`" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                                                    <button type="button" data-id="`+data+`" data-title="`+row.name+`" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                                 </form>    
                                            
                                                `;
                                        }
                                },
                                
                            ],  
                            
                            drawCallback: function () {
                                $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                            },
                           // order : [[ 3, "desc" ]]
                        });
                }
            };

            config();

            $('#config').on('click', '.delete', function (){
                Swal.fire({
                        title: "Warning..!",
                        text: "Do you want to delete Configurable Option Groups  "+$(this).data('title')+" ?",
                        icon: "warning",
                        showCancelButton:true,
                        cancelButtonColor: '#d33',
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((value) => {
                        if(value.isConfirmed){
                            $('#fd'+$(this).data('id')).submit();
                        }else{
                            return false;
                        }
                });
                return false;
            });


        });
    </script>
@endsection
