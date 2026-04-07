@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Product Addons</title>
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
                                        <h4 class="mb-3">Product Addons</h4>
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
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <p>Addons are primarily designed for one off fee items, whereas for
                                                    recurring items you would use configurable options. Addons can be
                                                    displayed during the initial order process but can also be ordered by
                                                    the client to add to an existing package at any time.</p>
                                            </div>
                                            <div class="col-lg-12 mb-3">
                                                <a href="{{ url(Request::segment(1).'/setup/productservices/productaddons/add') }}">
                                                    <button class="btn btn-outline-success px-3">
                                                        <i class="fa fa-plus mr-2" aria-hidden="true"></i>Add New Addon
                                                    </button>
                                                </a>
                                            </div>
                                            
                                           



                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="addonsdata" class="table table-bordere dt-responsive w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Description</th>
                                                                <th>Pay Type</th>
                                                                <th>Show on Order</th>
                                                                <th>Hidden</th>
                                                                <th>Addon Weighting</th>
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
           
            if (! $.fn.dataTable.isDataTable('#addonsdata') ) {
               
                var tbl =$('#addonsdata').DataTable({
                            paging: true,
                            processing: true,
                            serverSide: true,
                          
                            ajax:{
                                url : '{{ url(Request::segment(1).'/setup/productservices/productaddons') }}',
                                type: 'POST',
                                headers : {
                                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                                },
                               /*  data:{filter:filter}, */
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
                                {data: 'billingcycle', name: 'billingcycle'},
                                {data: 'showorder', name: 'showorder'},
                                {data: 'hidden', name: 'hidden'},
                                {data: 'weight', name: 'weight'},
                               /*  {data: 'expirationdate', name: 'expirationdate'}, */
                               /*  {data: 'id', name: 'duplicate',orderable: false, searchable: false,width:'80px',
                                    render: (data, type, row) => {
                                        return '<a href="{{ url(Request::segment(1).'/setup/payments/promotions/duplicate')}}/'+data+'" " ><i class="fas fa-plus-square"></i>Duplicate</a>';
                                    }
                                },
                                {data: 'id', name: 'expire',orderable: false, searchable: false,width:'130px',
                                    render: (data, type, row) => {
                                        
                                            return `
                                                 <form action="{{ url(Request::segment(1).'/setup/payments/promotions/expired') }}" method="POST">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="hidden" name="id" value="`+data+`">
                                                    <button type="submit" class="expired btn btn-success btn-xs"><i class="fas fa-stopwatch"></i> Expire Now</button>
                                                 </form>    
                                            
                                                `;
                                        }
                                }, */
                                {data: 'id', name: 'action',orderable: false, searchable: false,width:'80px',
                                    render: (data, type, row) => {

                                            var html='';
                                            if(!type.delete){
                                                html+=`<button type="button" data-id="`+data+`" data-title="`+row.name+`" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>`;
                                            }else{
                                                html+=`<button type="button" data-id="`+data+`" data-title="`+row.name+`" class="nodelete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>`;
                                            }


                                            return `
                                                 <form id="fd`+data+`" action="{{ url(Request::segment(1).'/setup/productservices/productaddons/destroy') }}" method="POST">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <input type="hidden" name="id" value="`+data+`">
                                                    <a title="Edit `+type.name+`" href="{{ url(Request::segment(1).'/setup/productservices/productaddons/edit') }}/`+data+`" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                                                    `+html+`
                                                 </form>    
                                            
                                                `;
                                        }
                                }, 
                                
                            ],  
                            
                            drawCallback: function () {
                                $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                            },
                          /*  order : [[ 0, "desc" ]] */
                        });
                
            };

           // datatable();

            
            $('#addonsdata').on('click', '.delete', function (){
                Swal.fire({
                        title: "Warning..!",
                        text: "Do you want to Product Addons "+$(this).data('id')+" ?",
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

            $('#addonsdata').on('click', '.nodelete', function (){

                Swal.fire({
                    icon: 'error',
                    title: 'Unable to Delete',
                    text: 'You cannot delete a product addon that is in use. To delete the addon, you need to first re-assign or remove the service addons using it.',
                    footer: ''
                });

                return false;

            });

        });
    </script> 
@endsection
