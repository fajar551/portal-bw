@extends('layouts.clientbase')

@section('title')
    My Domains
@endsection

@section('page-title')
    My Domains
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row pb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <span
                                class="text-muted"> / My Domains</span></h6>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4">
                    <div class="pull-right">
                        <a href="" class="btn btn-success-qw"><i class="feather-plus"></i> Add Service</a>
                    </div>
                </div>
            </div>


            @if ($domainsCount === 0)
                <div class="row">
                    <div class="alert alert-warning" role="alert">
                        {{ Lang::get('client.nothavedomain') }}
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase font-size-12 text-muted mb-3">Active</h6>
                                        <span class="h3 mb-0 text-success">
                                            {{ $getDomain->where('status', 'Active')->count() }}
                                        </span>
                                    </div>
                                    <div class="col-auto ic-card">
                                        <i class="feather-check text-success opacity-1"></i>
                                    </div>
                                </div> <!-- end row -->

                                <div id="sparkline1" class="mt-3"></div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->

                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase font-size-12 text-muted mb-3">Expired</h6>
                                        <span class="h3 mb-0 text-danger">
                                            {{ $getDomain->where('status', 'Expired')->count() }}
                                        </span>
                                    </div>
                                    <div class="col-auto ic-card">
                                        <i class="feather-clock text-danger opacity-1"></i>
                                    </div>
                                </div> <!-- end row -->

                                <div id="sparkline1" class="mt-3"></div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->

                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase font-size-12 text-muted mb-3">Pending</h6>
                                        <span class="h3 mb-0 text-warning">
                                            {{ $getDomain->where('status', 'Pending')->count() }}
                                        </span>
                                    </div>
                                    <div class="col-auto ic-card">
                                        <i class="feather-clock text-warning opacity-1"></i>
                                    </div>
                                </div> <!-- end row -->

                                <div id="sparkline1" class="mt-3"></div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->

                    <div class="col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase font-size-12 text-muted mb-3">Grace</h6>
                                        <span class="h3 mb-0 text-secondary">
                                            {{ $getDomain->where('status', 'Grace')->count() }}
                                        </span>
                                    </div>
                                    <div class="col-auto ic-card">
                                        <i class="feather-clock text-secondary opacity-1"></i>
                                    </div>
                                </div> <!-- end row -->

                                <div id="sparkline1" class="mt-3"></div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div>
                <!-- end row-->

                <div class="row">

                    <div class="col-xl-12 col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        <button type="button" class="close" data-dismiss="alert">×</button>
                                        <h5>Succesfully Updated!</h5>
                                        <p class="m-0">{!! session('success') !!}</p>
                                    </div>
                                @endif
                                @if (session('warning'))
                                    <div class="alert alert-warning">
                                        <button type="button" class="close" data-dismiss="alert">×</button>
                                        <h5>Attention!</h5>
                                        <p class="m-0">{!! session('warning') !!}</p>
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        <button type="button" class="close" data-dismiss="alert">×</button>
                                        <h5>Something Went Wrong.</h5>
                                        <p class="m-0">{!! session('error') !!}</p>
                                    </div>
                                @endif
                                <h4 class="card-title mb-3">My Domain List</h4>
                                <div class="table-responsive">
                                    <table id="mydomains" class="table table-bordered dt-responsive w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Domain</th>
                                                <th>Registration Date</th>
                                                <th>Due Date</th>
                                                <th>Auto Renew</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row-->
            @endif
        </div> <!-- container-fluid -->
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        let dtTable;

        $(() => {
            dtIndex();
        })

        const dtIndex = () => {
            dtTable = $('#mydomains').DataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                serverSide: true,
                autoWidth: false,
                searching: false,
                //    bInfo: false, //used to hide the property 
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
                    url: "{!! route('dt_myDomains') !!}",
                    type: "GET",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        width: '2%',
                        className: 'text-center align-middle',
                        visible: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'name',
                        name: 'name',
                        width: '1%',
                        className: 'text-left align-middle',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'registrationdate',
                        name: 'registrationdate',
                        width: '2%',
                        className: 'text-center align-middle',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'nextduedate',
                        name: 'nextduedate',
                        width: '1%',
                        className: 'text-center align-middle',
                        defaultContent: 'N/A',
                    },
                    {
                        data: 'donotrenew',
                        name: 'donotrenew',
                        width: '1%',
                        className: 'text-center align-middle',
                        searchable: false,
                        defaultContent: 'Off',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        width: '1%',
                        className: 'text-center align-middle',
                        searchable: false,
                        defaultContent: 'Off',
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        width: '2%',
                        className: 'text-center align-middle',
                        orderable: false,
                        searchable: false,
                        defaultContent: 'N/A',
                    },
                ]
            })
        }

        const getDomainDetails = (domain) => {
            var domainId = domain.getAttribute('data-domain-id');
            var domainSelector = domain.getAttribute('data-domain-status');
            const token = '{{ csrf_token() }}';

            if (domainSelector == 'autorenew') {
                url = "{!! route('domainstatjson') !!}";
                $('#domain-res-stats').prepend(`
            <div class="spinner-border spinner-border-sm" id="spinner" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            `);
                fetch(url, {
                    method: 'POST',
                    credentials: "same-origin",
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-Token": token
                    },
                    body: JSON.stringify({
                        domainid: domainId
                    })
                }).then(response => {
                    return response.json();
                }).then(text => {
                    $('#spinner').remove();
                    if (text == 'Active') {
                        $('#domain-res-stats').prepend(
                            `<span class="badge badge-success" id="statsDomain">${text}</span>`);
                        $('#autorenew-check').attr('checked', true);
                    } else {
                        $('#domain-res-stats').prepend(
                            `<span class="badge badge-soft-danger" id="statsDomain">${text}</span>`);
                        $('#autorenew-check').attr('checked', false);
                    }
                    $('#clear-stats').on('click', function() {
                        $('#statsDomain').remove();
                        $('#autorenew-check').removeAttr('checked', true);
                    })
                }).catch(error => console.error(error));
            }
            $('.domain-id').prepend(`<input name="domainid" type="hidden" value="${domainId}">`)
        }

        const getNameServerChild = (domain) => {
            var domainId = domain.getAttribute('data-domain-id');
            var domainSelector = domain.getAttribute('data-domain-status');
            const token = '{{ csrf_token() }}';
            alert(domainId);
        }
    </script>
@endsection
