@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Ticket Statuses</title>
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
                                        <h4 class="mb-3">Ticket Statuses</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Aspernatur similique
                                            nulla illum neque officia fugit modi commodi, adipisci voluptatibus perferendis
                                            unde libero temporibus maxime hic mollitia atque quo repellat minima?</p>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button class="btn btn-outline-success px-3"><i
                                                        class="fa fa-plus-square mr-2" aria-hidden="true"></i> Add
                                                    New</button>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-12">
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
                                                <div class="msg-alert"></div>
                                                <div class="table-responsive">
                                                    <table id="tblticketstatus" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Title</th>
                                                                <th>Include in Active Tickets</th>
                                                                <th>Include in Awaiting Reply</th>
                                                                <th>Auto Close?</th>
                                                                <th>Sort Order</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                       
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <form id="formaddstatus" action="{{ url(Request::segment(1).'/setup/support/ticketstatuses/store') }}" method="post" enctype="multipart/form-data">
                                        <div class="row mt-3">
                                            <div class="col-lg-12">
                                                <h4 class="card-title mb-3">Add Ticket Status</h4>
                                                <div class="form-group row">
                                                    <div class="col-sm-12 col-lg-2 col-form-label">
                                                        Title
                                                    </div>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="text" name="title" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-sm-12 col-lg-2 col-form-label">
                                                        Status Color
                                                    </div>
                                                    <div class="col-sm-12 col-lg-2">
                                                        <div class="input-group colorpicker-default" title="Using format option">
                                                            <input type="text" name="color" class="form-control input-lg" value="#4667cc" />
                                                            <span class="input-group-append">
                                                                <span  class="input-group-text colorpicker-input-addon"><i></i></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Include in  Active Tickets</label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="showactive" value="1" id="includeActive">
                                                            <label class="custom-control-label" for="includeActive"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Include in
                                                        Awaiting Reply</label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="showawaiting"  value="1" id="includeAwaitingReply">
                                                            <label class="custom-control-label" for="includeAwaitingReply"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Auto  Close?</label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="autoclose" value="1" id="autoClose">
                                                            <label class="custom-control-label" for="autoClose"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Sort
                                                        Order</label>
                                                    <div class="col-sm-12 col-lg-3">
                                                        <input type="text" class="form-control" name="sortorder" value="">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-lg-12 text-center">
                                                {{ csrf_field() }}
                                                <button  type="submit" class="btn btn-success px-3">Save Changes</button>
                                            </div>
                                            </div>
                                        </form>
                                        
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

    <div class="modal fade" id="formeditstatus" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            
                <div class="modal-content">
                <form id="formedittatus" action="" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Ticket Statuses</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                    </div>
                    <div class="modal-body">
                        <div class="container">    


                        <div class="form-group row">
                            <div class="col-sm-12 col-lg-2 col-form-label">
                                Title
                            </div>
                            <div class="col-sm-12 col-lg-5">
                                <input type="text" name="title" id="edittitle" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 col-lg-2 col-form-label">
                                Status Color
                            </div>
                            <div class="col-sm-12 col-lg-4">
                                <div class="input-group colorpicker-default" title="Using format option">
                                    <input type="text" name="color"  id="editcolor" class="form-control input-lg" value="#4667cc" />
                                    <span class="input-group-append">
                                        <span  class="input-group-text colorpicker-input-addon"><i></i></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="" class="col-sm-12 col-lg-2 col-form-label">Include in  Active Tickets</label>
                            <div class="col-sm-12 col-lg-5 pt-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input"  id="editshowactive" name="showactive" value="1" id="includeActive">
                                    <label class="custom-control-label" for="includeActive"></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="" class="col-sm-12 col-lg-2 col-form-label">Include in Awaiting Reply</label>
                            <div class="col-sm-12 col-lg-5 pt-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="editshowawaiting" name="showawaiting"  value="1" id="includeAwaitingReply">
                                    <label class="custom-control-label" for="includeAwaitingReply"></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="" class="col-sm-12 col-lg-2 col-form-label">Auto  Close?</label>
                            <div class="col-sm-12 col-lg-5 pt-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="editautoclose" name="autoclose" value="1" id="autoClose">
                                    <label class="custom-control-label" for="autoClose"></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="" class="col-sm-12 col-lg-2 col-form-label">Sort
                                Order</label>
                            <div class="col-sm-12 col-lg-3">
                                <input type="text" class="form-control" name="sortorder" id="editsortorder" value="">
                            </div>
                        </div>



                        </div>
                    </div>
                    <div class="modal-footer">
                        {{ csrf_field() }}
                        @method('PUT')
                        <input type="hidden" name="id" id="editid" value="">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success ">Save changes</button>
                    </div>
                    </form>
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
    <script src="{{ Theme::asset('assets/js/colorpicker.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var dt=function(){
                if (! $.fn.dataTable.isDataTable('#tblticketstatus') ) {
                    var tbl =$('#tblticketstatus').DataTable({
                        paging: true,
                        processing: true,
                        serverSide: true,
                        ordering:false,
                        ajax:{
                            url : '{{ url(Request::segment(1).'/setup/support/ticketstatuses') }}',
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
                            
                            //{data: 'title', name: 'title'},
                            {data: 'title', name: 'acttitleion',orderable: false, searchable: false,
                                render: (data, type, row) => {
                                        return `<span style="font-weight:bold; color:`+row.color+`;">`+row.title+`</span>`;
                                    }
                            }, 
                            {data: 'showactive', name: 'showactive',orderable: false, searchable: false,
                                render: (data, type, row) => {
                                        if(row.showactive == 1){
                                            return `<span class="text-success"> <i class="fas fa-check-circle"></i></span>`;
                                        }else{
                                            return `<span class="" ><i class="fas fa-ban"></i></span>`
                                        }
                                    }
                            }, 
                            {data: 'showawaiting', name: 'showawaiting',orderable: false, searchable: false,
                                render: (data, type, row) => {
                                        if(row.showawaiting == 1){
                                            return `<span class="text-success"> <i class="fas fa-check-circle"></i></span>`;
                                        }else{
                                            return `<span class="" ><i class="fas fa-ban"></i></span>`
                                        }
                                    }
                            },  
                            {data: 'autoclose', name: 'autoclose',orderable: false, searchable: false,
                                render: (data, type, row) => {
                                        if(row.autoclose == 1){
                                            return `<span class="text-success"> <i class="fas fa-check-circle"></i></span>`;
                                        }else{
                                            return `<span class="" ><i class="fas fa-ban"></i></span>`
                                        }
                                    }
                            }, 
                            {data: 'sortorder', name: 'sortorder'},
                            {data: 'id', name: 'action',orderable: false, searchable: false,width:'80px',
                                render: (data, type, row) => {
                                        var htmldelete='';
                                        if(4 < row.id){
                                            htmldelete=`<button type="button" data-id="`+data+`" data-title="`+row.name+`" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>`;
                                        }else{
                                            htmldelete='';
                                        }


                                        return `
                                                 
                                                <form id="fd`+data+`" action="{{ url(Request::segment(1).'/setup/support/ticketstatuses/destroy') }}" method="POST">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="id" value="`+data+`">
                                                <button type="button" title="Edit `+type.title+`" 
                                                        data-id=`+row.id+`
                                                        data-title="`+row.title+`"
                                                        data-color="`+row.color+`" 
                                                        data-sortorder="`+row.sortorder+`" 
                                                        data-showactive="`+row.showactive+`" 
                                                        data-showawaiting="`+row.showawaiting+`" 
                                                        data-autoclose="`+row.autoclose+`"
                                                        class="btn btn-info btn-xs editteble"><i class="far fa-edit"></i></button>
                                                `+htmldelete+`
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
            };
            //kahjsha
            
            dt();

            $("#formaddstatus").submit(function(){
                $('.msg-alert').html('');
                 $("#formaddstatus button[type='submit']").prop('disabled',true);

                $.ajax({
                    type: 'POST',
                    url:  "{{ url(Request::segment(1).'/setup/support/ticketstatuses/store') }}",
                    data: $("#formaddstatus").serialize(),
                    dataType: 'json',
                    success: function(data){
                        if(!data.error){
                            $(":input","#formaddstatus")
                            .not(":button, :submit, :reset, :hidden")
                            .val("")
                            .removeAttr("checked")
                            .removeAttr("selected");
                            $("#formaddstatus button[type='submit']").removeAttr('disabled');
                            $('#tblticketstatus').dataTable().fnDestroy();
                            dt();
                            Swal.fire(
                                    'Status Added Successfully',
                                    'The new status has now been added',
                                    'success'
                                    );
                        }
                        else{
                            $("#formaddstatus button[type='submit']").removeAttr('disabled');
                            Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: data.alert,
                                        footer: ''
                                    });
                        }
                    
                    }
                });
                return false;
             });


            $("#formedittatus").submit(function(){
                $('.msg-alert').html('');
                 $("#formedittatus button[type='submit']").prop('disabled',true);

                $.ajax({
                    type: 'POST',
                    url:  "{{ url(Request::segment(1).'/setup/support/ticketstatuses/update') }}",
                    data: $("#formedittatus").serialize(),
                    dataType: 'json',
                    success: function(data){
                        if(!data.error){
                            $(":input","#formedittatus")
                            .not(":button, :submit, :reset, :hidden")
                            .val("")
                            .removeAttr("checked")
                            .removeAttr("selected");
                            $("#formedittatus button[type='submit']").removeAttr('disabled');
                            $('#tblticketstatus').dataTable().fnDestroy();
                            dt();
                            $('#formeditstatus').modal('toggle'); 
                            Swal.fire(
                                    'Status Updated Successfully',
                                    'The new status has now been added',
                                    'success'
                                    );
                        }
                        else{
                            $('#formeditstatus').modal('toggle');
                            $("#formedittatus button[type='submit']").removeAttr('disabled');
                            Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: data.alert,
                                        footer: ''
                                    });
                        }
                    
                    }
                });
                return false;
             });

         //editteble
            $('#tblticketstatus').on('click', '.editteble', function (){
               //formeditstatus
                var atribute =$(this).data();
                $('#edittitle').val(atribute.title);
                $('#editcolor').val(atribute.color);
                if(atribute.showactive == 1){
                    $('#editshowactive').attr('checked','checked');
                }
                if(atribute.showawaiting == 1){
                    $('#editshowawaiting').attr('checked','checked');
                }
                if(atribute.autoclose == 1){
                    $('#editautoclose').attr('checked','checked');
                }
                $('#editsortorder').val(atribute.sortorder);
                $('#editid').val(atribute.id);

                



               $('#formeditstatus').modal('show');
               return false;
            });

            $('#tblticketstatus').on('click', '.delete', function (){
            // e.preventDefault();
                Swal.fire({
                        title: "Warning..!",
                        text: "Are you sure you want to delete this ticket status? Doing so will change all tickets assigned to this status to Closed.",
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
