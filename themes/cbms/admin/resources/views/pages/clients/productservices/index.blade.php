@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Product/Service</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- MAIN CARD -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">{{ $pageTitle }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
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
                                                                    <i
                                                                        class="mdi mdi-minus float-right accor-plus-icon"></i>
                                                                </h6>
                                                            </div>
                                                        </a>
                                                        <div id="collapseOne" class="collapse hide"
                                                            aria-labelledby="headingOne" data-parent="#accordion">
                                                            <form action="" method="POST" id="form-filters"
                                                                enctype="multipart/form-data"
                                                                onsubmit="return filterTable(this)">
                                                                @csrf
                                                                <div class="card-body p-0 mt-3">
                                                                    <div class="row">
                                                                        <div class="col-lg-6">
                                                                            {{-- <div class="form-group row">
                                                                                <label for="type"
                                                                                    class="col-sm-4 col-form-label">Product
                                                                                    Type</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control"
                                                                                        name="type" id="type">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $productsType !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div> --}}
                                                                            <div class="form-group row">
                                                                                <label for="package"
                                                                                    class="col-sm-4 col-form-label">Product/Service</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control"
                                                                                        name="package" id="package">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $products !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="billingcycle"
                                                                                    class="col-sm-4 col-form-label">Billing
                                                                                    Cycle</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control"
                                                                                        name="billingcycle"
                                                                                        id="billingcycle">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $cycles !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="domain"
                                                                                    class="col-sm-4 col-form-label">Name</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control"
                                                                                        name="domain" id="domain"
                                                                                        placeholder="Name"
                                                                                        autocomplete="off" />
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="clientname"
                                                                                    class="col-sm-4 col-form-label">Client</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control"
                                                                                        name="clientname" id="clientname"
                                                                                        placeholder="Client Name"
                                                                                        autocomplete="off" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="server"
                                                                                    class="col-sm-4 col-form-label">Server</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control"
                                                                                        name="server" id="server">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $servers !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="paymentmethod"
                                                                                    class="col-sm-4 col-form-label">Payment
                                                                                    Methods</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control"
                                                                                        name="paymentmethod"
                                                                                        id="paymentmethod">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $paymentMethods !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="domainstatus"
                                                                                    class="col-sm-4 col-form-label">Status</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control"
                                                                                        name="domainstatus"
                                                                                        id="domainstatus">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $domainstatus !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="customfield"
                                                                                    class="col-sm-4 col-form-label">Custom
                                                                                    Field</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control"
                                                                                        name="customfield"
                                                                                        id="customfield">
                                                                                        <option value="Any">Any</option>
                                                                                        @foreach ($customFields as $value)
                                                                                            <option
                                                                                                value="{{ $value->id }}">
                                                                                                {{ $value->fieldname }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="customfieldvalue"
                                                                                    class="col-sm-4 col-form-label">Custom
                                                                                    Field Value</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control"
                                                                                        name="customfieldvalue"
                                                                                        id="customfieldvalue"
                                                                                        placeholder="Custom Field Value" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <button
                                                                                class="btn btn-primary px-5 d-flex align-items-center ml-auto">
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
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="dt-product-services"
                                                        class="table table-bordered dt-responsive nowrap w-100">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="cb-select-all-services"
                                                                            class="custom-control-input"
                                                                            id="cb-select-all-services">
                                                                        <label class="custom-control-label"
                                                                            for="cb-select-all-services">&nbsp;</label>
                                                                    </div>
                                                                </th>
                                                                <th>ID</th>
                                                                <th>Product/Service</th>
                                                                <th>Name</th>
                                                                <th>Client Name</th>
                                                                <th>Price</th>
                                                                <th>Billing Cycle</th>
                                                                <th>Next Due Date</th>
                                                                <th>Status</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <form action="{{ route('admin.pages.clients.massmail.sendmessage') }}"
                                                    method="POST" id="form-sendmultiple" enctype="multipart/form-data"
                                                    autocomplete="off" hidden>
                                                    @csrf
                                                    <input type="text" name="type" value="product" required hidden>
                                                    <input type="text" name="multiple" value="true" required hidden>
                                                </form>
                                                <div class="form-group row mt-3">
                                                    <label class="col-sm-2 col-form-label">With Selected: </label>
                                                    <div class="col-sm-10">
                                                        <button type="button" class="btn btn-light px-5"
                                                            onclick="sendMultiple(selectedServicesId, $('#form-sendmultiple'))">Send
                                                            Message</button>
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
                <!-- End MAIN CARD -->
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailProductService" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="mySmallModalLabel">Service Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label id="fieldsordernum"></label><br>
                    <label id="fieldsregdate"></label><br>
                    <label id="fieldsserver"></label><br>
                    <label id="fieldsdedicatedip"></label><br>
                    <label id="fieldsusername"></label><br>
                    <label id="fieldspaymentmethod"></label><br>
                    <label id="fieldspromocode"></label><br>
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


    <!-- Moment JS -->
    <script src="{{ Theme::asset('assets/libs/moment/min/moment.min.js') }}"></script>

    <!-- JQuery Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    <script>
        // Datatable
        let dtTableServices;

        // Checkbox selected
        let selectedServicesId = [];

        // Detail result cache
        let detailResult = {};

        $(() => {

            dtProductServices();

            // Select all checkbox (Services)
            $('body').on('change', '#cb-select-all-services', function() {
                let checked = $(this).is(':checked');

                $('.select-checkbox-services').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedServicesId.includes(id)) selectedServicesId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedServicesId = [];
                    }
                });

                // console.log(selectedServicesId);
            });

            // Select individual checkbox (Services)
            $('body').on('change', '.select-checkbox-services', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());

                if (checked) {
                    if (!selectedServicesId.includes(id)) selectedServicesId.push(id);
                } else {
                    let idx = selectedServicesId.indexOf(id);

                    if (idx > -1) selectedServicesId.splice(idx, 1);
                }

                // console.log(selectedServicesId);
            });

        });

        const dtProductServices = () => {
            dtTableServices = $('#dt-product-services').DataTable({
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
                    url: "{!! route('admin.pages.clients.productservices.dtProductService') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        width: '2%',
                        className: 'text-center',
                        visible: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'id',
                        name: 'id',
                        width: '2%',
                        className: 'text-center',
                        orderable: false,
                        render: (data, type, row) => {
                            let checked = selectedServicesId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-checkbox-services[]" id="select-checkbox-services-${data}" ${checked} class="custom-control-input select-checkbox-services" value="${data}">
                                        <label class="custom-control-label" for="select-checkbox-services-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    {
                        data: 'raw_id',
                        name: 'raw_id',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'name',
                        name: 'name',
                        width: '10%',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'domain',
                        name: 'domain',
                        width: '10%',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'clientname',
                        name: 'clientname',
                        width: '15%',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        width: '10%',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'billingcycle',
                        name: 'billingcycle',
                        width: '10%',
                        className: 'text-center',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'nextduedate',
                        name: 'nextduedate',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'domstatus',
                        name: 'domstatus',
                        width: '5%',
                        className: 'text-center',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        width: '5%',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                ],
            });
        }

        const detail = async (el) => {
            const id = $(el).attr('data-id');

            if (detailResult[id] !== undefined) {
                detailField(detailResult[id]);
                return true;
            }

            const url = "{!! route('admin.pages.clients.productservices.detail') !!}" + "?serviceid=" + id;
            delete options.body;
            options.method = 'GET';

            const response = await cbmsPost(url, options);
            if (response) {
                const {
                    result,
                    message,
                    data = null
                } = response;

                if (result == 'error') {
                    Toast.fire({
                        icon: result,
                        title: message,
                    });

                    return false;
                }

                if (data) {
                    detailField(data);
                    detailResult[id] = data;
                }

                return true;
            }

            console.log("Failed to fetch data. Response: " + response);
        }

        const detailField = (data) => {
            $("#fieldsordernum").text(data.fieldsordernum ?? "-");
            $("#fieldsregdate").text(data.fieldsregdate ?? "-");
            $("#fieldsserver").text(data.fieldsserver ?? "-");
            $("#fieldsdedicatedip").text(data.fieldsdedicatedip ?? "-");
            $("#fieldspaymentmethod").text(data.fieldspaymentmethod ?? "-");
            $("#fieldsusername").text(data.fieldsusername ?? "-");
            $("#fieldspromocode").text(data.fieldspromocode ?? "-");

            $('#detailProductService').modal({
                show: true,
                backdrop: 'static'
            });
        }

        const filterTable = (form) => {

            selectedServicesId = [];
            dtTableServices.ajax.reload();

            return false;
        }
    </script>
@endsection
