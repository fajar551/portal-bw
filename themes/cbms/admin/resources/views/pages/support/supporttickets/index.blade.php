@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Support Tickets</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">


                    <div class="col-xl-12">
                        {{-- <div class="view-client-wrapper">
                            <div class="row">
                                
                            </div>
                        </div> --}}
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card p-3">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card-title mb-3">
                                                <h4 class="mb-3">Support Tickets </h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                <div class="card mb-1 shadow-none">
                                                    <a href="#collapseOne" class="text-dark" data-toggle="collapse"
                                                        aria-expanded="true" aria-controls="collapseOne">
                                                        <div class="card-header" id="headingOne">
                                                            <h6 class="m-0">
                                                                Search & filter
                                                                <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                            </h6>
                                                        </div>
                                                    </a>
                                                    <div id="collapseOne" class="collapse hide" aria-labelledby="headingOne"
                                                        data-parent="#accordion">
                                                        <form action="" method="POST" id="filter">
                                                            <div class="card-body p-0 mt-3">
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="form-group row">
                                                                            <label for="client-name"
                                                                                class="col-sm-2 col-form-label">Client
                                                                                Name</label>
                                                                            <div class="col-sm-10">
                                                                                <select name="clientName" id="client"
                                                                                    class="form-control select2-placeholder"
                                                                                    style="width: 100%;">
                                                                                    @if (!empty($user))
                                                                                        <option value="{{ $user->id }}">
                                                                                            {{ $user->firstname }}
                                                                                            {{ $user->lastname }} |
                                                                                            {{ $user->email }} |
                                                                                            {{ $user->companyname }}
                                                                                        </option>
                                                                                    @endif
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="department-name"
                                                                                class="col-sm-2 col-form-label">Department</label>
                                                                            <div class="col-sm-10">
                                                                                <select name="department_name[]"
                                                                                    id="department-name"
                                                                                    class="form-control select2-limiting"
                                                                                    multiple="multiple">


                                                                                    @foreach ($dep as $d)
                                                                                        <option value="{{ $d->id }}">
                                                                                            {{ $d->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="status-tickets"
                                                                                class="col-sm-2 col-form-label">Status</label>
                                                                            <div class="col-sm-10">
                                                                                <select name="status_tickets[]"
                                                                                    id="status-tickets"
                                                                                    class="form-control select2-limiting"
                                                                                    multiple="multiple">

                                                                                    </option>
                                                                                    @foreach ($status as $s)
                                                                                        <option value="{{ $s->title }}">
                                                                                            {{ $s->title }}</option>
                                                                                    @endforeach

                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="tags-name"
                                                                                class="col-sm-2 col-form-label">Tags</label>
                                                                            <div class="col-sm-10">
                                                                                <select name="tags_name[]" id="tags-name"
                                                                                    class="form-control select2-limiting"
                                                                                    multiple="multiple">

                                                                                    </option>
                                                                                    @foreach ($tag as $s)
                                                                                        <option
                                                                                            value="{{ $s->id }}">
                                                                                            {{ $s->tag }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="priority-level"
                                                                                class="col-sm-2 col-form-label">Priority</label>
                                                                            <div class="col-sm-10">
                                                                                <select name="priority_level[]"
                                                                                    id="priority-level"
                                                                                    class="form-control select2-limiting"
                                                                                    multiple="multiple">

                                                                                    <option value="Low">Low</option>
                                                                                    <option value="Medium">Medium</option>
                                                                                    <option value="High">High</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="subject-message"
                                                                                class="col-sm-2 col-form-label">Subject/Message</label>
                                                                            <div class="col-sm-10">
                                                                                <input type="text" name="subject_message"
                                                                                    id="subject-message"
                                                                                    class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="email-address"
                                                                                class="col-sm-2 col col-form-label">Email
                                                                                Address</label>
                                                                            <div class="col-sm-10">
                                                                                <input type="email" name="email_address"
                                                                                    id="email-address" class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="ticket-id"
                                                                                class="col-sm-2 col-form-label">Ticket
                                                                                ID</label>
                                                                            <div class="col-sm-10">
                                                                                <input type="text" name="ticket_id"
                                                                                    id="ticket-id" class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for="assigned-to"
                                                                                class="col-sm-2 col-form-label">Assigned
                                                                                To</label>
                                                                            <div class="col-sm-10">
                                                                                <select name="assigned_to"
                                                                                    id="assigned-to"
                                                                                    class="form-control select2-search-disable"
                                                                                    style="width: 100%;">
                                                                                    <option value="0">Any</option>
                                                                                    <option value="1">Admin CBMSAuto
                                                                                    </option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-12 d-flex justify-content-center">
                                                                        <button type="submit"
                                                                            class="btn btn-primary px-5 d-flex align-items-center">
                                                                            <i class="ri-search-line mr-2"></i>
                                                                            Search
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group row">
                                                <label for="auto-refresh" class="col-lg-2 col-sm-2 col-form-label">Auto
                                                    Refresh:</label>
                                                <div class="col-lg-2 col-sm-6">
                                                    <select name="auto_refresh" id="auto-refresh" class="form-control"
                                                        style="width: 100%;">
                                                        <option value="">Never</option>
                                                        <option value="1">1 Minutes</option>
                                                        <option value="2">2 Minutes</option>
                                                        <option value="3">5 Minutes</option>
                                                        <option value="4">10 Minutes</option>
                                                        <option value="5">15 Minutes</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3 col-sm-6">
                                                    <button id="setINV" class="btn btn-success px-5">Set Auto
                                                        Refresh</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="table-responsive">
                                                <table class="table table-bordered dt-responsive w-100" id="tiket">
                                                    <thead>
                                                        <tr>
                                                            <th>
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                        id="ordercheck1">
                                                                    <label class="custom-control-label"
                                                                        for="ordercheck1">&nbsp;</label>
                                                                </div>
                                                            </th>
                                                            <th>Urgency</th>
                                                            <th>Department</th>
                                                            <th>Subject</th>
                                                            <th>Submitter</th>
                                                            <th>Status</th>
                                                            <th>Last Reply</th>
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
    <!-- data -->
    <script type="text/javascript">
       

        $(document).ready(function() {
            $('#client').select2({
                minimumInputLength: 2,
                placeholder: 'Client',
                ajax: {
                    type: "post",
                    url: '{{ url('admin/support/getClientselect2') }}',
                    dataType: 'json',
                    /*   delay: 250, */
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.firstname + ' ' + item.lastname + ' | ' + item
                                        .email + ' | ' + item.companyname,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });


            $('#auto-refresh').select2().val(getCookie('tickets_refresh')).trigger("change");

            $('#department-name').select2({
                placeholder: 'Any',
                allowClear: true
            });
            $('#status-tickets').select2({
                placeholder: 'Any',
                allowClear: true
            });
            $('#priority-level').select2({
                placeholder: 'Any',
                allowClear: true
            });



            var data_tiket = function(filter = []) {
                if (!$.fn.dataTable.isDataTable('#tiket')) {

                    var client = $('#client').val();
                    var dep = $('#department-name').val();
                    var status = $('#status-tickets').val();
                    var priority = $('#priority-level').val();
                    var tag = $('#tags-name').val();
                    var subject = $('#subject-message').val();
                    var email = $('#email-address').val();
                    var ticket = $('#ticket-id').val();
                    var assigned = $('#assigned-to').val();

                    var tbl = $('#tiket').DataTable({
                        paging: true,
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '{{ url('admin/support/supporttickets') }}',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: {
                                'department_name': dep,
                                'client': client,
                                'status': status,
                                'priority': priority,
                                'tag': tag,
                                'subject': subject,
                                'email': email,
                                'ticket': ticket,
                                'assigned': assigned,
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
                        columns: [{
                                data: 'checkbox',
                                name: 'id',
                                orderable: true,
                                searchable: false
                            },
                            {
                                data: 'urgency',
                                name: 'urgency'
                            },
                            {
                                data: 'departement',
                                name: 'departement'
                            },
                            {
                                data: 'subject',
                                name: 'subject'
                            },
                            {
                                data: 'submitter',
                                name: 'submitter'
                            },
                            {
                                data: 'status',
                                name: 'status'
                            },
                            {
                                data: 'lastreply',
                                name: 'lastreply',
                                searchable: false
                            },
                        ],
                        drawCallback: function() {
                            $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                            $("tbody tr").on("click", function() {
                                //loop through all td elements in the row
                                $(this).find("td").each(function(i) {
                                    //toggle between adding/removing the 'active' class
                                    $(this).addClass('visited');
                                });
                            });
                        },
                        order: [
                            [0, "desc"]
                        ],

                    });

                    var varAuto = getCookie('tickets_refresh');
                    if (varAuto) {
                        $('#auto-refresh').val(varAuto);
                        var time = (varAuto * 1000) * 60;
                        setInterval(function() {
                            tbl.ajax.reload();
                        }, time);
                    }

                }

            };

            $('#filter').on('submit', function(e) {
                e.preventDefault();
                $('#tiket').dataTable().fnDestroy();
                data_tiket();

                return false;
            });


            data_tiket();


            //auto-refresh
            $('#auto-refresh').change(function() {
                if ($(this).val()) {
                    setCookie('tickets_refresh', $(this).val());
                } else {
                    eraseCookie('tickets_refresh');
                }

                return false;
            });

            $("#setINV").click(function() {
                $('#tiket').dataTable().fnDestroy();
                data_tiket();
                return false;
            });


        });



        function setCookie(name, value) {
            var expires = "";
            var date = new Date();
            date.setTime(date.getTime() + (360 * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function eraseCookie(name) {
            var now = new Date();
            var time = now.getTime();
            var expireTime = time + 1000 * 36000;
            now.setTime(expireTime);
            document.cookie = name + '=; Path=/; Expires=' + now.toUTCString() + ';';
        }
    </script>
@endsection
