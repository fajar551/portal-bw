@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Service Addons</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Service Addons</h4>
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                    <div class="card mb-1 shadow-none">
                                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne">
                                                            <div class="card-header" id="headingOne">
                                                                <h6 class="m-0">
                                                                    Search & Filter
                                                                    <i class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                </h6>
                                                            </div>
                                                        </a>
                                                        <div id="collapseOne" class="collapse hide" aria-labelledby="headingOne" data-parent="#accordion">
                                                            <form action="" method="POST" id="form-filters" enctype="multipart/form-data" onsubmit="return filterTable(this)">
                                                                @csrf
                                                                <div class="card-body p-0 mt-3">
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="addon" class="col-sm-4 col-form-label">Addon</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="addon" id="addon">
                                                                                        <option value="Any">Any</option>
                                                                                        @foreach ($addonsList as $addonReference => $addonName)
                                                                                            <option value="{{ $addonReference }}">{{ $addonName }}</option>;
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="package" class="col-sm-4 col-form-label">Product/Service</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="package" id="package">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $products !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="paymentmethod" class="col-sm-4 col-form-label">Payment Methods</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="paymentmethod" id="paymentmethod">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $paymentMethods !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="statusaddons" class="col-sm-4 col-form-label">Status</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="statusaddons" id="statusaddons">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $statusaddons !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="domain" class="col-sm-4 col-form-label">Domain</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control" name="domain" id="domain" placeholder="Domain Name" autocomplete="off"/>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="clientname" class="col-sm-4 col-form-label">Client</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control" name="clientname" id="clientname" placeholder="Client Name" autocomplete="off"/>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="type" class="col-sm-4 col-form-label">Product Type</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="type" id="type">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $productsType !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="server" class="col-sm-4 col-form-label">Server</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="server" id="server">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $servers !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="billingcycle" class="col-sm-4 col-form-label">Billing Cycle</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="billingcycle" id="billingcycle">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $cycles !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="customfield" class="col-sm-4 col-form-label">Custom Field</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="customfield" id="customfield">
                                                                                        <option value="Any">Any</option>
                                                                                        @foreach ($customFields as $value)
                                                                                        <option value="{{ $value->id }}">{{ $value->fieldname }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="customfieldvalue" class="col-sm-4 col-form-label">Custom Field Value</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control" name="customfieldvalue" id="customfieldvalue" placeholder="Custom Field Value"/>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <button type="submit" class="btn btn-primary px-5 d-flex align-items-center ml-auto">
                                                                                <i class="ri-search-line mr-2"></i> Search
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
                                                <div class="table-responsive">
                                                    <table id="dt-serive-addons" class="table table-bordered dt-responsive nowrap w-100">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="cb-select-all-addons" class="custom-control-input" id="cb-select-all-addons">
                                                                        <label class="custom-control-label" for="cb-select-all-addons">&nbsp;</label>
                                                                    </div>
                                                                </th>
                                                                <th>ID</th>
                                                                <th>Addons</th>
                                                                <th>Product Service</th>
                                                                <th>Client Name</th>
                                                                <th>Billing Cycle</th>
                                                                <th>Price</th>
                                                                <th>Next Due Date</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                                <form action="{{ route('admin.pages.clients.massmail.sendmessage') }}" method="POST" id="form-sendmultiple" enctype="multipart/form-data" autocomplete="off" hidden>
                                                    @csrf
                                                    <input type="text" name="type" value="product" required hidden>
                                                    <input type="text" name="multiple" value="true" required hidden>
                                                </form>
                                                <div class="form-group row mt-3">
                                                    <label class="col-sm-2 col-form-label">With Selected: </label>
                                                    <div class="col-sm-10">
                                                        <button type="button" class="btn btn-light px-5" onclick="sendMultiple(selectedAddonsId, $('#form-sendmultiple'))">Send Message</button>
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

    <!-- Modal Detail -->
    <div class="modal fade bs-example-modal-sm" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="mySmallModalLabel">Addons Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label id="fieldsordernum"></label><br>
                    <label id="fieldsregdate"></label><br>
                    <label id="fieldsserver"></label><br>
                    <label id="fieldsparentdomain"></label><br>
                    <label id="fieldspaymentmethod"></label><br>
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
    {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}

    <!-- Moment JS -->
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>

    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    <script>
        // Datatable
        let dtTableAddons;

        // Checkbox selected
        let selectedAddonsId = [];

        // Detail result cache
        let detailResult = {};

        $(() => {

            dtServiceAddons();

            // Select all checkbox (Domains)
            $('body').on('change', '#cb-select-all-addons', function() {
                let checked = $(this).is(':checked');

                $('.select-checkbox-addons').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedAddonsId.includes(id)) selectedAddonsId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedAddonsId = [];
                    }
                });

                // console.log(selectedAddonsId);
            });

            // Select individual checkbox (Domains)
            $('body').on('change', '.select-checkbox-addons', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());
                
                if (checked) {
                    if (!selectedAddonsId.includes(id)) selectedAddonsId.push(id);
                } else {
                    let idx = selectedAddonsId.indexOf(id);

                    if (idx > -1) selectedAddonsId.splice(idx, 1);
                }

                // console.log(selectedAddonsId);
            });

            $('body').on('click', '#btn-send-message', function() {
                if (selectedAddonsId.length) {
                    console.log("Do send message action here with selected id: " +selectedAddonsId.join(", "));
                } else {
                    Toast.fire({
                        icon: 'warning',
                        title: 'You must select at least one item in the list.',
                    });
                }
            });

        });

        const dtServiceAddons = () => {
            dtTableAddons = $('#dt-serive-addons').DataTable({
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
                    url: "{!! route('admin.pages.clients.domainregistrations.dtServiceAddons') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'hostingid', name: 'hostingid', width: '2%', className:'text-center', orderable: false, 
                        render: (data, type, row) => {
                            let checked = selectedAddonsId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-checkbox-addons[]" id="select-checkbox-addons-${data}" ${checked} class="custom-control-input select-checkbox-addons" value="${data}">
                                        <label class="custom-control-label" for="select-checkbox-addons-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    { data: 'raw_id', name: 'raw_id', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'addonname', name: 'addonname', width: '10%', defaultContent: 'N/A', },
                    { data: 'name', name: 'name', width: '10%', defaultContent: 'N/A', },
                    { data: 'clientname', name: 'clientname', width: '15%', defaultContent: 'N/A', },
                    { data: 'billingcycle', name: 'billingcycle', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'recurring', name: 'recurring', width: '10%', searchable: false, defaultContent: 'N/A', },
                    { data: 'nextduedate', name: 'nextduedate', width: '5%', className:'text-center', defaultContent: 'N/A'},
                    { data: 'status', name: 'status', width: '5%', className:'text-center', defaultContent: 'N/A'},
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const detail = async (el) => {
            const id = $(el).attr('data-id');

            if (detailResult[id] !== undefined) {
                detailField(detailResult[id]);
                return true;
            }
            
            const url = "{!! route('admin.pages.clients.serviceaddons.detail') !!}" +"?addonid="+id;
            delete options.body;
            options.method = 'GET';

            const response = await cbmsPost(url, options);
            if (response) {    
                const { result, message, data = null } = response;

                if (result == 'error') {
                    Toast.fire({ icon: result, title: message, });
                    
                    return false;
                }

                if (data) {
                    detailField(data);
                    detailResult[id] = data;
                }

                return true;
            }

            console.log("Failed to fetch data. Response: " +response);
        }

        const detailField = (data) => {
            $("#fieldsordernum").text(data.fieldsordernum ?? "-");
            $("#fieldsregdate").text(data.fieldsregdate ?? "-");
            $("#fieldsserver").text(data.fieldsserver ?? "-");
            $("#fieldsparentdomain").text(data.fieldsparentdomain ?? "-");
            $("#fieldspaymentmethod").text(data.fieldspaymentmethod ?? "-");

            $('#modal-detail').modal({show: true, backdrop: 'static'});
        }

        const filterTable = (form) => {

            selectedAddonsId = [];
            dtTableAddons.ajax.reload();

            return false;
        }
    </script>
@endsection
