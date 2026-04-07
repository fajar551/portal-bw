@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Support Ticket Departments</title>
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
                                        <h4 class="mb-3">Support Ticket Departments</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Facilis porro
                                                    dolorum placeat repudiandae, optio molestias delectus, exercitationem
                                                    minima deserunt obcaecati hic! Quidem et nihil quasi ex modi, voluptates
                                                    perferendis fuga.</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="alert alert-warning w-100" role="alert">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"
                                                                        id="addon-wrapping">Ticket
                                                                        Importing using Email Forwarders</span>
                                                                </div>
                                                                <input type="text" class="form-control"
                                                                    placeholder="Username" aria-label="Username"
                                                                    aria-describedby="addon-wrapping"
                                                                    value=" | /usr/bin/php -q /home/protoqwords/public_html/crons/pipe.php">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-center">
                                                        <div class="col-lg-1">
                                                            <strong>OR</strong>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="basic-addon3">Ticket
                                                                        Importing using POP3 Import *</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="basic-url"
                                                                    aria-describedby="basic-addon3"
                                                                    value="*/5 * * * * /usr/bin/php -q /home/protoqwords/public_html/crons/pop.php">
                                                            </div>
                                                            <small>* (Requires IMAP installed on server)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box-info">
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
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-4">
                                                <a href="{{ url('admin/setup/support/configticketdepartments/add') }}">
                                                    <button class="btn btn-outline-success px-2"><i
                                                            class="fa fa-plus-square mr-2" aria-hidden="true"></i>Add New
                                                        Department</button>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-12">
                                                <div class="table-responsive table-support-ticket-departments">
                                                    <table id="supportdep" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Department Name</th>
                                                                <th>Description</th>
                                                                <th>Email Address</th>
                                                                <th>Hidden</th>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($data as $r)
                                                            <tr>
                                                                <td>{{ $r->name }}</td>
                                                                <td>{{ $r->description }}</td>
                                                                <td>{{ $r->email }}</td>
                                                                <td  width="80" >
                                                                    <div class="text-center">
                                                                        @if(!empty($r->hidden))<span class="badge badge-info">Yes</span> @else<span class="badge badge-danger">No</span>@endif
                                                                    </div>
                                                                </td>
                                                                <td width="100">
                                                                    <div class="d-flex justify-content-center">
                                                                        @if(!$loop->first)
                                                                        <form  action="{{ url(Request::segment(1).'/setup/support/configticketdepartments/order') }}" method="POST">
                                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                                            @method('PUT')
                                                                            <input type="hidden" name="id" value="{{ $r->id }}" >
                                                                            <input type="hidden" name="order" value="{{ $r->order }}" >
                                                                            <input type="hidden" name="type" value="up" >
                                                                            <button class="btn btn-link p-0 " type="submit" ><i class="fa fa-arrow-up" aria-hidden="true"></i></button>
                                                                        </form>
                                                                        @endif

                                                                        @if(!$loop->last)
                                                                        <form  action="{{ url(Request::segment(1).'/setup/support/configticketdepartments/order') }}" method="POST">
                                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                                            @method('PUT')
                                                                            <input type="hidden" name="id" value="{{ $r->id }}" >
                                                                            <input type="hidden" name="order" value="{{ $r->order }}" >
                                                                            <input type="hidden" name="type" value="down" >
                                                                            <button class="btn btn-link p-0" type="submit" ><i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                                                                        </form>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td width="100" >
                                                                    <div class="d-flex justify-content-center">  
                                                                        <form id="fd{{ $r->id }}" action="{{ url(Request::segment(1).'/setup/support/configticketdepartments/destroy') }}" method="POST">
                                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                            <input type="hidden" name="id" value="{{ $r->id }}">
                                                                            <a title="Edit {{ $r->name }}" href="{{ url(Request::segment(1).'/setup/support/configticketdepartments/edit/'.$r->id) }}" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                                                                            <button title="delete" type="button" data-id="{{ $r->id }}" data-title="{{ $r->name }}" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                                                        </form> 
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                            
                                                            
                                                          
                                                        </tbody>
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
       /*
        if (! $.fn.dataTable.isDataTable('#supportdep') ) {
            var tbl =$('#supportdep').DataTable({
                paging: true,
                processing: true,
                serverSide: true,
                ordering:false,
                ajax:{
                    url : '{{ url(Request::segment(1).'/setup/support/configticketdepartments') }}',
                    type: 'POST',
                    headers : {
                        'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                    },
                    /*  data:{filter:filter}, 
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
                    {data: 'email', name: 'email'},
                    {data: 'hidden', name: 'hidden',orderable: false, searchable: false,width:'80px',
                        render: (data, type, row) => {
                            if(row.hidden !=''){
                                return '<span class="badge badge-info">Yes</span>';
                            }else{
                                return '<span class="badge badge-danger">No</span>';
                            }
                            
                        }
                    },
                    
                    {data: 'order', name: 'order',orderable: false, searchable: false,width:'130px',
                        render: (data, type, row) => {
                               /*  console.log(row,'aaa');
                                console.log(data,'bb'); 
                                if(data == 1){
                                
                                }else{

                                }
                        
                            }
                    },
                    /* {data: 'id', name: 'action',orderable: false, searchable: false,width:'80px',
                        render: (data, type, row) => {

                                var html='';
                                if(type.delete == 0){
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
                /*
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                //order : [[ 3, "desc" ]] 
            });

        }; */

        $('#supportdep').on('click', '.delete', function (){
            // e.preventDefault();
                Swal.fire({
                        title: "Warning..!",
                        text: "Are you sure you want to delete this department "+$(this).data('title')+" ?",
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
