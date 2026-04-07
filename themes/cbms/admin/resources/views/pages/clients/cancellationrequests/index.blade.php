@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Cancellation Request</title>
@endsection

@section('styles')
   
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <!-- <div class="row">
                                    <div class="col-12 p-3">
                                        <div class="page-title-box d-flex align-items-center justify-content-between">
                                            <h4 class="mb-0">Dashboard</h4>
                                        </div>
                                    </div>
                                </div> -->
                <!-- end page title -->
                <div class="row">
                     

                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Cancellation Request</h4>
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                    <div class="card mb-1 shadow-none">
                                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse"
                                                            aria-expanded="true" aria-controls="collapseOne">
                                                            <div class="card-header" id="headingOne">
                                                                <h6 class="m-0">
                                                                    Search & Filter
                                                                    <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                </h6>
                                                            </div>
                                                        </a>
                                                        <div id="collapseOne" class="collapse hide" aria-labelledby="headingOne" data-parent="#accordion">
                                                            <div class="card-body p-0 mt-3">
                                                                <form action="" method="POST" id="form-filters" enctype="multipart/form-data" onsubmit="return filterTable(this)">
                                                                    @csrf
                                                                    <input type="hidden" name="completed" id="completed" />
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="reason" class="col-sm-2 col-form-label">Reason</label>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" name="reason" class="form-control" placeholder="Reason"/>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="domain" class="col-sm-2 col-form-label">Domain</label>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" name="domain" class="form-control" placeholder="Domain" />
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="relid" class="col-sm-2 col-form-label">Service ID</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control" name="relid" placeholder="Service ID"/>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="userid" class="col-sm-2 col-form-label">Client ID</label>
                                                                                <div class="col-sm-10">
                                                                                    <input class="form-control" name="userid" placeholder="Client ID"/>
                                                                                    {{-- <select class="form-control" name="userid">
                                                                                        <option value="Any">Any</option>
                                                                                    </select> --}}
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="type" class="col-sm-2 col-form-label">Type</label>
                                                                                <div class="col-sm-10">
                                                                                    <select class="form-control" name="type">
                                                                                        <option value="Any">Any</option>
                                                                                        <option value="Immediate">Immediate</option>
                                                                                        <option value="End of Billing Period">End of Billing Period</option>
                                                                                        <option value="None Specified (API Submission)">None Specified (API Submission)</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <button type="submit" class="btn btn-primary px-5 d-flex align-items-center ml-auto">
                                                                                <i class="ri-search-line mr-2"></i>
                                                                                {{ __('admin.appsnavsearch') }}
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-12 pb-3">
                                                        <ul class="nav nav-pills nav-fill border rounded">
                                                            <li class="nav-item">
                                                                <a href="javascript:void(0)" class="nav-link active" id="open-request" data-id="open-request">Show Open Request</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a href="javascript:void(0)" class="nav-link" id="complete-request" data-id="complete-request">Show Completed Requests</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="cb-select-all" class="custom-control-input" id="cb-select-all">
                                                                        <label class="custom-control-label" for="cb-select-all">&nbsp;</label>
                                                                    </div>
                                                                </th>
                                                                <th class="text-center">Date</th>
                                                                <th class="text-center">Product/Service</th>
                                                                <th class="text-center">Reason</th>
                                                                <th class="text-center">Type</th>
                                                                <th class="text-center">Cancellation By End</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                                <div class="form-group row mt-3">
                                                    <label for="custom-field" class="col-sm-2 col-form-label">With Selected: </label>
                                                    <div class="col-sm-10">
                                                        <button type="button" class="btn btn-light px-5" id="btn-send-message">Send Message</button>
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

    <!-- Moment JS -->
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>

    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>
    
    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    <!-- Custom Js -->
    <script type="text/javascript">

        let dtTable;
        let selectedId = [];
        let currentActiveToggle = "open-request";

        $(() => {
            
            dtIndex();

            // Select all checkbox
            $('body').on('change', '#cb-select-all', function() {
                let checked = $(this).is(':checked');

                $('.select-clients').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedId.includes(id)) selectedId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedId = [];
                    }
                });

                // console.log(selectedId);
            });

            // Select individual checkbox
            $('body').on('change', '.select-clients', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());
                
                if (checked) {
                    if (!selectedId.includes(id)) selectedId.push(id);
                } else {
                    let idx = selectedId.indexOf(id);

                    if (idx > -1) selectedId.splice(idx, 1);
                }

                // console.log(selectedId);
            });

            $('body').on('click', '#open-request', function() {
                toggleCompleted(this);
            });

            $('body').on('click', '#complete-request', function() {
                toggleCompleted(this);
            });

            $('body').on('click', '.act-delete', function() {
                const url = "{!! route('admin.pages.clients.cancellationrequests.deleteCancellation') !!}";
                const payloads = {
                    id: $(this).attr('data-id'),
                };

                Swal.fire({
                    title: "Are you sure?",
                    html: `The <b>Data</b> will be deleted from database.`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, Delete!",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: async (data) => {
                        options.method = 'DELETE';
                        options.body = JSON.stringify(payloads);

                        const response = await cbmsPost(url, options);
                        if (!response) {
                            const error = "An error occured.";
                            return Swal.showValidationMessage(`Request failed: ${error}`);
                        }

                        return response;
                    },
                }).then((response) => {
                    if (response.value) {
                        const { result, message } = response.value;

                        Toast.fire({
                            icon: 'success',
                            title: `${message}`,
                        });

                        filterTable(null);
                    }
                }).catch(swal.noop);
            });

            $('body').on('click', '#btn-send-message', function() {
                if (selectedId.length) {
                    console.log("Do send message action here with selected id: " +selectedId.join(", "));
                } else {
                    Toast.fire({
                        icon: 'warning',
                        title: 'You must select at least one item in the list.',
                    });
                }
            });
        });

        const toggleCompleted = (param) => {
            let toggle = $(param).attr('data-id');

            if (toggle == "open-request") {
                $("#open-request").addClass("active");
                $("#complete-request").removeClass("active");

                $("#completed").val("");

                if (currentActiveToggle != "open-request") {
                    filterTable(null);
                }

                currentActiveToggle = "open-request";
            } else if (toggle == "complete-request") {
                $("#complete-request").addClass("active");
                $("#open-request").removeClass("active");

                $("#completed").val("true");

                if (currentActiveToggle != "complete-request") {
                    filterTable(null);
                }

                currentActiveToggle = "complete-request";
            }

        }

        const dtIndex = () => {
            dtTable = $('#datatable').DataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                serverSide: true,
                autoWidth: false,
                searching: false,
                destroy: true,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                },
                drawCallback: () => {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                },
                ajax: {
                    url: "{!! route('admin.pages.clients.cancellationrequests.dtCancellationRequest') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'id', name: 'id', width: '2%', className:'text-center', orderable: false, 
                        render: (data, type, row) => {
                            let checked = selectedId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-clients[]" id="select-clients-${data}" ${checked} class="custom-control-input select-clients" value="${data}">
                                        <label class="custom-control-label" for="select-clients-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    { data: 'date', name: 'date', width: '5%', defaultContent: 'N/A', className:'text-center',
                        render: (data, type, row) => {
                            return data ? moment(data).format('DD/MM/YYYY hh:mm') : 'N/A';
                        }
                    },
                    { data: 'product_service', name: 'product_service', width: '10%', orderable: false, searchable: false, },
                    { data: 'reason', name: 'reason', width: '15%', className:'text-center' },
                    { data: 'type', name: 'type', width: '10%', defaultContent: 'N/A'},
                    { data: 'nextduedate', name: 'nextduedate', width: '5%', defaultContent: 'N/A', className:'text-center', orderable: false,
                        render: (data, type, row) => {
                            return data ? moment(data).format('DD/MM/YYYY') : 'N/A';
                        }
                    },
                    { data: 'actions', name: 'actions', width: '5%', defaultContent: 'N/A', className:'text-center', orderable: false, searchable: false, },
                ],
            });
        }

        const filterTable = (form) => {
            
            selectedId = [];
            dtTable.ajax.reload();

            return false;
        }

    </script>
@endsection
