@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Domain Registrations</title>
@endsection

@section('styles')

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
                                    <h4>Domain Registrations</h4>
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="accordion" class="custom-accordion mt-1 pb-1">
                                                    <div class="card mb-1 shadow-none">
                                                        <a href="#collapseOne" class="text-dark" data-toggle="collapse" aria-expanded="true" aria-controls="collapseOne">
                                                            <div class="card-header" id="headingOne">
                                                                <h6 class="m-0">
                                                                    Search & Filter <i class="mdi mdi-minus float-right accor-plus-icon"></i>
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
                                                                                <label for="domain" class="col-sm-4 col-form-label">Domain</label>
                                                                                <div class="col-sm-8">
                                                                                    <input type="text" class="form-control" name="domain" id="domain" placeholder="Domain Name" />
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="registrar-select" class="col-sm-4 col-form-label">Registrar</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="select2-search-disable form-control" name="registrar" id="registrarsDropDown" style="width: 100%;">
                                                                                        <option value="">None</option>
                                                                                        @foreach ($registrars as $reg)
                                                                                            <option value="{{ $reg->getLowerName() }}">{{ ucwords($reg->getLowerName()) }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group row">
                                                                                <label for="statusdomain" class="col-sm-4 col-form-label">Status</label>
                                                                                <div class="col-sm-8">
                                                                                    <select class="form-control" name="statusdomain" id="statusdomain">
                                                                                        <option value="Any">Any</option>
                                                                                        {!! $statuses !!}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label for="clientname" class="col-sm-4 col-form-label">Client Name</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control" name="clientname" id="clientname" placeholder="Client Name" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <button type="submit" class="btn btn-primary px-5 d-flex align-items-center ml-auto">
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
                                                <div class="table-responsive">
                                                    <table id="dt-domain-registration" class="table table-bordered dt-responsive nowrap w-100">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="cb-select-all-domains" class="custom-control-input" id="cb-select-all-domains">
                                                                        <label class="custom-control-label" for="cb-select-all-domains">&nbsp;</label>
                                                                    </div>
                                                                </th>
                                                                <th class="text-center">ID</th>
                                                                <th class="text-center">Domain</th>
                                                                <th class="text-center">Client Name</th>
                                                                <th class="text-center">Reg Period</th>
                                                                <th class="text-center">Registrar</th>
                                                                <th class="text-center">Price</th>
                                                                <th class="text-center">Next Due Date</th>
                                                                <th class="text-center">Expiry Date</th>
                                                                <th class="text-center">Status</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <form action="{{ route('admin.pages.clients.massmail.sendmessage') }}" method="POST" id="form-sendmultiple" enctype="multipart/form-data" autocomplete="off" hidden>
                                                    @csrf
                                                    <input type="text" name="type" value="domain" required hidden>
                                                    <input type="text" name="multiple" value="true" required hidden>
                                                </form>
                                                <div class="form-group row mt-3">
                                                    <label class="col-sm-2 col-form-label">With Selected: </label>
                                                    <div class="col-sm-10">
                                                        <button type="button" class="btn btn-light px-5" onclick="sendMultiple(selectedDomainsId, $('#form-sendmultiple'));">Send Message</button>
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
    <div class="modal fade" id="detailProductService" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="mySmallModalLabel">Domain Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label id="fieldsordernum"></label><br>
                    <label id="fieldsregdate"></label><br>
                    <label id="fieldsordertype"></label><br>
                    <label id="fieldsdnsmanagement"></label><br>
                    <label id="fieldsemailforwarding"></label><br>
                    <label id="fieldsidprotection"></label><br>
                    <label id="fieldspremiumDomain"></label><br>
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
        let dtTableDomains;

        // Checkbox selected
        let selectedDomainsId = [];

        // Detail result cache
        let detailResult = {};

        $(() => {

            dtDomainRegistration();

            // Select all checkbox (Domains)
            $('body').on('change', '#cb-select-all-domains', function() {
                let checked = $(this).is(':checked');

                $('.select-checkbox-domains').each(function() {
                    if (checked) {
                        let id = parseInt($(this).val());

                        $(this).prop('checked', true);

                        if (!selectedDomainsId.includes(id)) selectedDomainsId.push(id);
                    } else {
                        $(this).prop('checked', false);

                        selectedDomainsId = [];
                    }
                });

                // console.log(selectedDomainsId);
            });

            // Select individual checkbox (Domains)
            $('body').on('change', '.select-checkbox-domains', function() {
                let checked = $(this).is(':checked');
                let id = parseInt($(this).val());
                
                if (checked) {
                    if (!selectedDomainsId.includes(id)) selectedDomainsId.push(id);
                } else {
                    let idx = selectedDomainsId.indexOf(id);

                    if (idx > -1) selectedDomainsId.splice(idx, 1);
                }

                // console.log(selectedDomainsId);
            });

        });

        const detail = async (el) => {
            const id = $(el).attr('data-id');

            if (detailResult[id] !== undefined) {
                detailField(detailResult[id]);
                return true;
            }
            
            const url = "{!! route('admin.pages.clients.domainregistrations.domainDetail') !!}" +"?domainid="+id;
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
            $("#fieldsordertype").text(data.fieldsordertype ?? "-");
            $("#fieldsdnsmanagement").text(data.fieldsdnsmanagement ?? "-");
            $("#fieldsemailforwarding").text(data.fieldsemailforwarding ?? "-");
            $("#fieldsidprotection").text(data.fieldsidprotection ?? "-");
            $("#fieldspremiumDomain").text(data.fieldspremiumDomain ?? "-");
            $("#fieldspaymentmethod").text(data.fieldspaymentmethod ?? "-");

            $('#detailProductService').modal({show: true, backdrop: 'static'});
        }

        const dtDomainRegistration = () => {
            dtTableDomains = $('#dt-domain-registration').DataTable({
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
                    url: "{!! route('admin.pages.clients.domainregistrations.dtDomainRegistration') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'id', name: 'id', width: '2%', className:'text-center', orderable: false, 
                        render: (data, type, row) => {
                            let checked = selectedDomainsId.includes(row.id) ? "checked" : "";

                            return `<div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="select-checkbox-domains[]" id="select-checkbox-domains-${data}" ${checked} class="custom-control-input select-checkbox-domains" value="${data}">
                                        <label class="custom-control-label" for="select-checkbox-domains-${data}">&nbsp;</label>
                                    </div>`;
                        }
                    },
                    { data: 'raw_id', name: 'raw_id', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'domain', name: 'domain', width: '15%', defaultContent: 'N/A', },
                    { data: 'clientname', name: 'clientname', width: '15%', defaultContent: 'N/A', },
                    { data: 'registrationperiod', name: 'registrationperiod', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'registrar', name: 'registrar', width: '10%', defaultContent: 'N/A', },
                    { data: 'recurringamount', name: 'recurringamount', width: '10%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'nextduedate', name: 'nextduedate', width: '5%', className:'text-center', defaultContent: 'N/A', },
                    { data: 'expirydate', name: 'expirydate', width: '5%', className:'text-center', defaultContent: 'N/A'},
                    { data: 'status', name: 'status', width: '5%', className:'text-center', defaultContent: 'N/A'},
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', orderable: false, searchable: false, defaultContent: 'N/A', },
                ],
            });
        }

        const filterTable = (form) => {

            selectedDomainsId = [];
            dtTableDomains.ajax.reload();

            return false;
        }
    </script>
    
@endsection
