@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Escalation Rules</title>
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
                                        <h4 class="mb-3">Support Ticket Escalations</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid ut
                                            exercitationem a neque? Ullam vel debitis repellendus eum mollitia saepe.</p>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="alert alert-warning mb-0" role="alert">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon3">Cron Command
                                                                Required for Running Escalation Rules (Run Every 5
                                                                Minutes)</span>
                                                        </div>
                                                        <input type="text" class="form-control" id="basic-url"
                                                            aria-describedby="basic-addon3"
                                                            value="/usr/bin/php -q /home/protoqwords/public_html/crons/cron.php do --TicketEscalations">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row my-3">
                                            <div class="col-lg-12">
                                                <a href="{{ url('admin/setup/support/escalationrules/add') }}">
                                                    <button class="btn btn-outline-success px-2">
                                                        <i class="fa fa-plus-square mr-2" aria-hidden="true"></i>Add New
                                                        Rule
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="configticketescalations" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            
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
        if (! $.fn.dataTable.isDataTable('#configticketescalations') ) {
            var tbl =$('#configticketescalations').DataTable({
                paging: true,
                processing: true,
                serverSide: true,
                ordering:false,
                ajax:{
                    url : '{{ url(Request::segment(1).'/setup/support/escalationrules') }}',
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
                    
                    {data: 'name', name: 'name'},
                    {data: 'id', name: 'action',orderable: false, searchable: false,width:'80px',
                        render: (data, type, row) => {
                                return `
                                        <form id="fd`+data+`" action="{{ url(Request::segment(1).'/setup/support/escalationrules/destroy') }}" method="POST">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="id" value="`+data+`">
                                        <a title="Edit `+type.name+`" href="{{ url(Request::segment(1).'/setup/support/escalationrules/edit') }}/`+data+`" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                                        <button type="button" data-id="`+data+`" data-title="`+row.name+`" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                        </form>    
                                
                                    `;
                            }
                    },  
                     
                ],  
        
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                //order : [[ 3, "desc" ]] 
            });

        };

        $('#configticketescalations').on('click', '.delete', function (){
            // e.preventDefault();
                Swal.fire({
                        title: "Warning..!",
                        text: "Are you sure you want to delete this escalation rule? "+$(this).data('title')+" ?",
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
