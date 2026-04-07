@extends('layouts.clientbase')

@section('title')
    Domain Contact Info
@endsection

@section('page-title')
    {{ Lang::get('client.domaincontactinfo') }}
@endsection

@section('content')
    <link href="{{ Theme::asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        #documentUpload {
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 20px;
            max-width: 800px;
            /* Adjust width */
            margin: 0 auto;
            /* Center within container */
            background-color: #f9f9f9;
        }

        .custom-alert {
            border-left: 4px solid #ccc;
            /* Customize border color */
            background-color: #f8f8f8;
            /* Customize background color */
            color: #333;
            /* Customize text color */
            padding: 10px 15px;
            border-radius: 0.25rem;
        }

        .file-thumbnail {
            margin-bottom: 15px;
            /* Adds spacing below each thumbnail */
            padding: 5px;
            /* Adds padding for better structure */
        }

        .thumbnail-wrapper {
            border: 1px solid #ddd;
            /* Adds a border around the image */
            padding: 5px;
            /* Adds padding inside the border */
            background-color: #fff;
            /* Background color to match alert if needed */
        }

        .img-fluid {
            width: 100%;
            /* Ensures the image takes up the full width of the container */
            height: auto;
            /* Maintains aspect ratio */
            display: block;
            /* Centers the image */
        }

        /* Hide the 'X' overlay icon in the center */
        /* .dropzone .dz-preview.dz-error .dz-error-message {
                display: none;
            } */
        about:blank#blocked

        /* Remove tooltip (title attribute) */
        .dz-filename span {
            pointer-events: none;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <a
                                href="{{ route('pages.domain.mydomains.index') }}"> / My Domains</a> <span
                                class="text-muted"> / Domain Document</span></h6>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Header -->
            <h3>Domain Document</h3>
            <br>
            <!-- Buttons -->
            <div class="mb-4">
                <a href="{{ route('pages.domain.mydomains.details.document', array_merge(request()->query(), ['module' => 'PrivateNsRegistrar', 'page' => 'upload'])) }}"
                    class="btn btn-outline-primary">Upload Document</a>
                <a href="{{ route('pages.domain.mydomains.details.requirement', array_merge(request()->query(), ['module' => 'PrivateNsRegistrar', 'page' => 'requirement'])) }}"
                    class="btn btn-primary">Domain Document</a>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="mydomains" class="table table-bordered dt-responsive w-100">
                                    <thead>
                                        <tr>
                                            <th>Domain Name</th>
                                            <th>Document</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($table as $row)
                                            <tr>
                                                <td>{{ $row['domain'] }}</td>
                                                <td>{{ $row['count'] }} Files</td>
                                                <td class="text-center">{{ $row['status'] }}</td>
                                                <td class="text-center">
                                                    <a data-domain="{{ $row['domain'] }}"
                                                        data-userid="{{ $id }}" data-token="{{ csrf_token() }}"
                                                        class="btn btn-outline-warning btn-block detail" data-toggle="modal"
                                                        data-target="#detailDomain">
                                                        <span class="text-primary"><i class="fa fa-search"></i>
                                                            Details</span>
                                                    </a>
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

    <div class="modal fade" id="detailDomain" tabindex="-1" role="dialog" aria-labelledby="detailDomain"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Domain Document (<span id="domain_name"></span>)</h5>
                </div>
                <div class="modal-body">
                    <div id="loadingSpinner" class="text-center my-3" style="display: none;">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <table id="tbls" class="table table-bordered dt-responsive w-100">
                        <thead>
                            <th>Document Type</th>
                            <th class="text-center">Status</th>
                            <th>Reason</th>
                        </thead>
                        <tbody id="detail_document">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#mydomains').DataTable();
        });

        $('.detail').click(async function() {


            var token = $(this).attr('data-token');
            var domain = $(this).attr('data-domain');
            var userid = $(this).attr('data-userid');

            $('#domain_name').html('<strong>' + domain + '</strong>');
            $('#tbls').attr('hidden', true);

            $('#detail_document').html("");
            $('#loadingSpinner').show();

            try {
                const response = await fetch('/domain/mydomains/details/requirement/detail', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        _token: token,
                        domain: domain,
                        userid: userid,
                    })
                });
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                $('#detail_document').html("")
                $.each(data, function(i, v) {

                    let status;
                    if (v.status === 'Pending') {
                        status = "<a class='btn btn-primary'>Pending</a>";
                    } else if (v.status === 'Approved') {
                        status = "<a class='btn btn-success'>Approved</a>";
                    } else if (v.status === 'Rejected') {
                        status = "<a class='btn btn-danger'>Rejected</a>";
                    }

                    $('#detail_document').append("<tr><td>" + v.type + "</td><td class='text-center'>" +
                        status + "</td><td>" + v.ket + "</td></tr>")
                })
            } catch (error) {
                console.error('Error:', error);

            } finally {
                $('#loadingSpinner').hide();
                $('#tbls').removeAttr('hidden', true);
            }
        });
    </script>
@endsection
