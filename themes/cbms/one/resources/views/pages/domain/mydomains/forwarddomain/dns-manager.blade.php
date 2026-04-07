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
    .custom-alert {
        border-left: 4px solid #ccc;
        background-color: #f8f8f8;
        color: #333;
        padding: 10px 15px;
        border-radius: 0.25rem;
    }

    .alert-info-custom {
        background-color: #fff8e1;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 5px;
    }

    .alert-danger-custom {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
        padding: 15px;
        border-radius: 5px;
    }

    @media (max-width: 480px) {
        .action-buttons {
            flex-direction: row;
            display: flex;
            justify-content: center !important;
        }

        .action-buttons .btn {
            text-align: center !important;
        }

        .btn-yes {
            margin-left: 10px !important;
        }
    }

    @media (min-width: 300px) and (max-width: 480px) {
        .action-buttons {
            flex-direction: row;
            display: flex;
            justify-content: center !important;
        }

        .action-buttons .btn {
            margin-bottom: 50px !important;
            text-align: center !important;
        }

        .btn-yes {
            margin-left: 10px !important;
        }
    }

    .nav-tabs .nav-link.active,
    .nav-tabs .nav-item.show .nav-link {
        color: #0273e5;
    }

    .text-prime {
        color: #ffb444;
        /* Example color */
    }

    .confirm-swal {
        background-color: #ffb444 !important;
        /* Custom prime color */
        box-shadow: none !important;
        font-weight: bold;
        /* Optional: Emphasize text */
    }

    .confirm-swal:hover {
        background-color: #ffb444 !important;
        /* Add background color on hover */
        color: white !important;
        /* Contrast text color on hover */
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row mb-3">
            <div class="col-xl-8 col-lg-8">
                <div class="header-breadcumb">
                    <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <a
                            href="{{ route('pages.domain.mydomains.index') }}"> / My Domains</a> <span
                            class="text-muted"> / DNS Manager </span></h6>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'dns']) }}">DNS Manager</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'domain_fwd']) }}">Domain Forwarder/URL Masking</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'email_fwd']) }}">Email Forwarder</a>
            </li>
        </ul>
        <br>
        <div class="alert alert-info-custom" role="alert">
            <p>Untuk menggunakan fitur DNS Manager, pastikan domain yang digunakan mengarah ke name server berikut :</p>
            <ul>
                <li>dnsiix1.qwords.net</li>
                <li>dnsiix2.qwords.net</li>
            </ul>
            <p>Jika membutuhkan bantuan silahkan hubungi kami melalui support ticket
                <a href="https://s.id/16Akz">https://s.id/16Akz</a>
            </p>
        </div>
        <br>
        <h3>Qwords DNS Manager</h3>
        <span>Manage DNS records for domain name <strong class="text-prime">{{ $domain }}</strong></span>
        <br>
        <br>
        <table class="table table-striped table-framed">
            <thead>
                <tr>
                    <th style="text-align:left;">Host Name</th>
                    <th style="text-align:center;">TTL</th>
                    <th style="text-align:center;">Type</th>
                    <th style="text-align:center;">Value/Destination</th>
                    <th style="text-align:center;">Delete</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                <tr id="rc_{{ $record['Line'] }}">
                    <td style="text-align:left;">
                        <input class="form-control" style="display: inline;width:auto;" type="text" onchange="handleChange({{ $record['Line'] }}, this)" name="name" value="{{ str_replace('.'.$domain, '', $record['name']) }}">
                    </td>
                    <td style="text-align:center;">
                        <input class="form-control" style="display: inline;width:70px;" type="text" onchange="handleChange({{ $record['Line'] }}, this)" name="ttl" value="{{ $record['ttl'] }}">
                    </td>
                    <td style="text-align:center;">
                        <input type="hidden" name="type" value="{{ $record['type']}}">
                        <strong>{{ $record['type'] }}</strong>
                    </td>
                    <td class="row-values text-left">
                        @if(isset($record['address']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="address">Address :</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="address" onchange="handleChange({{ $record['Line'] }}, this)" name="address" value="{{ $record['address'] }}">
                            </div>
                        </div>
                        @endif

                        @if(isset($record['nsdname']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="nsdname">Nsdname :</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="nsdname" onchange="handleChange({{ $record['Line'] }}, this)" name="nsdname" value="{{ $record['nsdname'] }}">
                            </div>
                        </div>
                        @endif

                        @if(isset($record['exchange']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="exchange">Exchange :</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="exchange" onchange="handleChange({{ $record['Line'] }}, this)" name="exchange" value="{{ $record['exchange'] }}">
                            </div>
                        </div>
                        @endif

                        @if(isset($record['preference']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="preference">Priority :</label>
                            <div class="col-md-9">
                                <input class="form-control w-50" type="text" id="preference" onchange="handleChange({{ $record['Line'] }}, this)" name="preference" value="{{ $record['preference'] }}">
                            </div>
                        </div>
                        @endif

                        @if(isset($record['cname']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="cname">CNAME :</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="cname" onchange="handleChange({{ $record['Line'] }}, this)" name="cname" value="{{ $record['cname'] }}">
                            </div>
                        </div>
                        @endif

                        @if(isset($record['txtdata']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="txtdata">TXT :</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="txtdata" onchange="handleChange({{ $record['Line'] }}, this)" name="txtdata" value="{{ $record['txtdata'] }}">
                            </div>
                        </div>
                        @endif

                        @if(isset($record['flag']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="flag">Flag :</label>
                            <div class="col-md-9">
                                <input class="form-control w-50" type="text" id="flag" onchange="handleChange({{ $record['Line'] }}, this)" name="flag" value="{{ $record['flag'] }}">
                            </div>
                        </div>
                        @endif

                        @if(isset($record['tag']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="tag">Tag :</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="tag" onchange="handleChange({{ $record['Line'] }}, this)" name="tag" value="{{ $record['tag'] }}">
                            </div>
                        </div>
                        @endif

                        @if(isset($record['value']))
                        <div class="row mb-2">
                            <label class="col-md-3 col-form-label" for="value">Value :</label>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id="value" onchange="handleChange({{ $record['Line'] }}, this)" name="value" value="{{ $record['value'] }}">
                            </div>
                        </div>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <input type="hidden" name="line" value="{{ $record['Line'] }}">
                        <button type="button" class="btn btn-outline-danger btn-sm ld-ext-right" name="btnSave" value="Save Changes" onclick="deleteRecord({{ $record['Line'] }}, this)">
                            <i class="fas fa-times"></i>
                            <div class="ld ld-ring ld-spin-fast"></div>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="text-center">
            <button type="submit" class="btn btn-primary ld-ext-right" id="btnSave" name="btnSave" value="Save Changes"> Save Changes
                <div class="ld ld-ring ld-spin-fast"></div>
            </button>
            <button type="submit" class="btn btn-secondary ld-ext-right" id="btnRecreateZone" name="btnRecreateZone" value="Reset DNS Zone" onclick="resetDNS()"> Reset DNS Zone
                <div class="ld ld-ring ld-spin-fast"></div>
            </button>
            <button type="submit" class="btn btn-danger ld-ext-right" id="btnDeleteZone" name="btnDeleteZone" value="Delete DNS Zone" onclick="deletetDNS()"> Delete DNS Zone
                <div class="ld ld-ring ld-spin-fast"></div>
            </button>
        </p>
        <h3>Add a new DNS record</h3>
        <form id="dnsRecordForm">
            @csrf
            <table class="table">
                <thead class="thead">
                    <tr>
                        <th class="text-left">Host Name</th>
                        <th class="text-center">TTL</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Value/Destination</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <input type="hidden" name="action" value="addrecordwhm">
                        <input type="hidden" name="domain" value="{{ $domain }}">
                        <td class="align-middle text-left">
                            <div class="input-group mb-3">
                                <input class="form-control" type="text" name="name" value="">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">.{{ $domain }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle text-center">
                            <input class="form-control w-50 mx-auto" type="text" name="ttl" value="14440">
                        </td>
                        <td class="align-middle text-center">
                            <select class="form-control w-75 mx-auto" name="type" onchange="showDNSOption(this.value);">
                                @if(isset($type['data']) && is_array($type['data']))
                                @foreach ($type['data'] as $key => $val)
                                <option value="{{ $val['name'] }}" @if($key==0) selected="selected" @endif>{{ $val['name'] }}</option>
                                @endforeach
                                @else
                                <option value="" disabled>No types available</option>
                                @endif
                            </select>
                        </td>
                        <td>
                            <div id="add_address" class="row mb-2">
                                <label for="address" class="col-md-3 col-form-label">Address :</label>
                                <div class="col-md-9">
                                    <input id="address" class="form-control" type="text" name="values[address]" value="">
                                </div>
                            </div>
                            <div id="add_nsdname" class="row mb-2">
                                <label for="nsdname" class="col-md-3 col-form-label">Nsdname :</label>
                                <div class="col-md-9">
                                    <input id="nsdname" class="form-control" type="text" name="values[nsdname]" value="">
                                </div>
                            </div>
                            <div id="add_exchange" class="row mb-2">
                                <label for="exchange" class="col-md-3 col-form-label">Exchange :</label>
                                <div class="col-md-9">
                                    <input id="exchange" class="form-control" type="text" name="values[exchange]" value="">
                                </div>
                            </div>
                            <div id="add_preference" class="row mb-2">
                                <label for="preference" class="col-md-3 col-form-label">Priority :</label>
                                <div class="col-md-9">
                                    <input id="preference" class="form-control w-50" type="text" name="values[preference]" value="">
                                </div>
                            </div>
                            <div id="add_cname" class="row mb-2">
                                <label for="cname" class="col-md-3 col-form-label">CNAME :</label>
                                <div class="col-md-9">
                                    <input id="cname" class="form-control" type="text" name="values[cname]" value="">
                                </div>
                            </div>
                            <div id="add_txtdata" class="row mb-2">
                                <label for="txtdata" class="col-md-3 col-form-label">TXT :</label>
                                <div class="col-md-9">
                                    <input id="txtdata" class="form-control" type="text" name="values[txtdata]" value="">
                                </div>
                            </div>
                            <div id="add_target" class="row mb-2">
                                <label for="target" class="col-md-3 col-form-label">Target :</label>
                                <div class="col-md-9">
                                    <input id="target" class="form-control" type="text" name="values[target]" value="">
                                </div>
                            </div>
                            <div id="add_weight" class="row mb-2">
                                <label for="weight" class="col-md-3 col-form-label">Weight :</label>
                                <div class="col-md-9">
                                    <input id="weight" class="form-control" type="text" name="values[weight]" value="">
                                </div>
                            </div>
                            <div id="add_port" class="row mb-2">
                                <label for="port" class="col-md-3 col-form-label">Port :</label>
                                <div class="col-md-9">
                                    <input id="port" class="form-control" type="text" name="values[port]" value="">
                                </div>
                            </div>
                            <div id="add_priority" class="row mb-2">
                                <label for="priority" class="col-md-3 col-form-label">Priority :</label>
                                <div class="col-md-9">
                                    <input id="priority" class="form-control w-50" type="text" name="values[priority]" value="">
                                </div>
                            </div>
                            <div id="add_flag" class="row mb-2">
                                <label for="flag" class="col-md-3 col-form-label">Flag :</label>
                                <div class="col-md-9">
                                    <input id="flag" class="form-control" type="text" name="values[flag]" value="">
                                </div>
                            </div>
                            <div id="add_tag" class="row mb-2">
                                <label for="tag" class="col-md-3 col-form-label">Tag :</label>
                                <div class="col-md-9">
                                    <input id="tag" class="form-control" type="text" name="values[tag]" value="">
                                </div>
                            </div>
                            <div id="add_value" class="row mb-2">
                                <label for="value" class="col-md-3 col-form-label">Value :</label>
                                <div class="col-md-9">
                                    <input id="value" class="form-control" type="text" name="values[value]" value="">
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center">
                <button type="submit" class="btn btn-primary" id="submitFormButton">Submit</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this record?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resetConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Reset</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to Reset DNS Zone?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmResetButton">Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteDNSConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this DNS Zone?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteDNSButton">Delete DNS Zone</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
    let recordToDelete = null;

    var domain = "{{ $domain }}";
    var type = @json($type['data']);
    showDNSOption('A');

    window.changedRecord = {};

    function handleChange(line, element) {
        try {
            var $el = $("#rc_" + line);
            if ($el.length === 0) {
                console.error("Row element not found for line:", line);
                return;
            }

            const values = $el.find('.row-values input').map(function() {
                return {
                    [this.name]: this.value
                };
            }).toArray();

            if (!values || values.length === 0) {
                console.warn("No inputs found in .row-values for line:", line);
            }

            const values_obj = values.reduce((acc, v) => {
                acc[Object.keys(v)[0]] = Object.values(v)[0];
                return acc;
            }, {});

            if (!window.changedRecord) {
                window.changedRecord = {};
            }
            if (typeof domain === "undefined") {
                console.error("Global variable 'domain' is not defined");
                return;
            }

            window.changedRecord[line] = {
                ...window.changedRecord[line],
                domain: domain,
                name: $el.find('[name="name"]').val(),
                type: $el.find('[name="type"]').val(),
                ttl: $el.find('[name="ttl"]').val(),
                line: line,
                values: values_obj,
            };

            console.log("Updated window.changedRecord:", window.changedRecord);
        } catch (error) {
            console.error("Error in handleChange:", error);
        }
    }

    function showDNSOption(value) {
        $('#add_address,#add_nsdname,#add_exchange,#add_preference,#add_cname,#add_txtdata,#add_target,#add_weight,#add_port,#add_priority,#add_flag,#add_tag,#add_value').hide()
        const obj = type.find(obj => obj.name == value)
        if (obj) {
            obj.field.forEach(val => {
                $('#add_' + val.toLocaleLowerCase()).show();
            })
        }
    }

    function deleteRecord(line, el) {
        recordToDelete = {
            line,
            el
        };
        $('#deleteConfirmModal').modal('show');
    }

    function resetDNS() {
        $('#resetConfirmModal').modal('show');
    }

    function deletetDNS() {
        $('#deleteDNSConfirmModal').modal('show');
    }

    document.getElementById('submitFormButton').addEventListener('click', async function() {
        const form = document.getElementById('dnsRecordForm');
        const formData = new FormData(form);
        const submitButton = document.getElementById('submitFormButton');

        try {

            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`;
            submitButton.disabled = true;

            const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.addrecordwhm") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: formData,
            });

            const result = await response.json();

            if (response.ok && result.data.metadata.result === 1) {

                Swal.fire({
                    title: 'Success!',
                    text: 'DNS Record successfully created!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });

            } else if (response.ok && result.data.metadata.result === 0) {
                let reasonText = result.data.metadata.reason;
                Swal.fire({
                    title: 'Warning!',
                    text: reasonText,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                let reasonText = result.data.metadata.reason;
                Swal.fire({
                    title: 'Error!',
                    text: reasonText,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: 'Unexpected error',
                icon: 'error',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'confirm-swal',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        } finally {
            submitButton.innerHTML = `Submit`;
            submitButton.disabled = false;
        }
    });

    document.getElementById('confirmDeleteButton').addEventListener('click', async function() {
        if (recordToDelete) {
            const {
                line,
                el
            } = recordToDelete; // Retrieve stored record information
            const submitButton = this; // Reference to the Delete button
            const cancelButton = document.querySelector('#deleteConfirmModal .btn-secondary'); // Reference to the Cancel button
            const domain = '{{ $domain }}';

            try {
                const csrfToken = '{{ csrf_token() }}';
                submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...`;
                submitButton.disabled = true;
                cancelButton.disabled = true;

                const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.deleterecordwhm") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        domain,
                        line
                    }),
                });

                const result = await response.json();

                if (response.ok && result.data.metadata.result === 1) {

                    Swal.fire({
                        title: 'Success!',
                        text: 'Record successfully deleted!',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'confirm-swal',
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });

                } else if (response.ok && result.data.metadata.result === 0) {
                    let reasonText = result.data.metadata.reason;
                    Swal.fire({
                        title: 'Warning!',
                        text: reasonText,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'confirm-swal',
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    let reasonText = result.data.metadata.reason;
                    Swal.fire({
                        title: 'Error!',
                        text: reasonText,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'confirm-swal',
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                }
            } catch (error) {
                console.error('Error during record deletion:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: error.message || 'An unexpected error occurred.',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } finally {
                submitButton.innerHTML = `Delete`;
                submitButton.disabled = false;
                cancelButton.disabled = false;
                $('#deleteConfirmModal').modal('hide'); // Hide the modal
            }
        }
    });

    $('#btnSave').click(async function(e) {
        e.preventDefault();
        const values = Object.values(window.changedRecord);
        const btnSave = $(this);
        const saveButton = document.getElementById('btnSave');

        try {
            const csrfToken = '{{ csrf_token() }}';
            saveButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`;
            saveButton.disabled = true;

            let successCount = 0;
            let failureCount = 0;

            for (const val of values) {
                try {
                    const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.editrecordwhm") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(val),
                    });

                    const result = await response.json();

                    if (response.ok && result.data.metadata.result === 1) {
                        successCount++;
                        console.log(`Successfully updated record for ${val.domain}`);
                    } else if (response.ok && result.data.metadata.result === 0) {
                        failureCount++;
                        console.error(`Failed to update record for ${val.domain}:`, result.data.metadata.reason || 'Unknown error');
                    } else {
                        failureCount++;
                        console.error(`Failed to update record for ${val.domain}:`, result.data.metadata.reason || 'Unknown error');
                    }
                    console.log(result);
                } catch (error) {
                    console.log(error);
                }
            }

            Swal.fire({
                title: 'Update Completed',
                text: `${successCount} record(s) updated successfully, ${failureCount} record(s) failed.`,
                icon: failureCount > 0 ? 'warning' : 'success',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'confirm-swal',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });

        } catch (error) {
            console.error('Error during the save process:', error);
            Swal.fire({
                title: 'Error',
                text: error.message || 'An unexpected error occurred.',
                icon: 'error',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'confirm-swal',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        } finally {
            saveButton.innerHTML = `Save Changes`;
            saveButton.disabled = false;
        }
    });

    document.getElementById('confirmResetButton').addEventListener('click', async function() {
        const resetButton = this;
        const cancelButton = document.querySelector('#resetConfirmModal .btn-secondary');
        const domain = '{{ $domain }}';

        try {
            const csrfToken = '{{ csrf_token() }}';
            resetButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`;
            resetButton.disabled = true;

            const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.resetrecordwhm") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    domain
                }),
            });

            const result = await response.json();

            if (response.ok && result.data.metadata.result === 1) {

                Swal.fire({
                    title: 'Success!',
                    text: 'DNS Zone successfully reset!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });

            } else if (response.ok && result.data.metadata.result === 0) {
                let reasonText = result.data.metadata.reason;
                Swal.fire({
                    title: 'Warning!',
                    text: reasonText,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                let reasonText = result.data.metadata.reason;
                Swal.fire({
                    title: 'Error!',
                    text: reasonText,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
        } catch (error) {
            console.error('Error during record reset:', error);
            Swal.fire({
                icon: 'error',
                title: 'Unexpected Error',
                text: error.message || 'An unexpected error occurred.',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'confirm-swal',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        } finally {
            resetButton.innerHTML = `Reset`;
            resetButton.disabled = false;
            cancelButton.disabled = false;
            $('#resetConfirmModal').modal('hide');
        }
    });

    document.getElementById('confirmDeleteDNSButton').addEventListener('click', async function() {
        const deleteButton = this;
        const cancelButton = document.querySelector('#deleteDNSConfirmModal .btn-secondary');
        const domain = '{{ $domain }}';

        try {
            const csrfToken = '{{ csrf_token() }}';
            deleteButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`;
            deleteButton.disabled = true;

            const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.deletednsrecordwhm") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    domain
                }),
            });

            const result = await response.json();

            if (response.ok && result.data.metadata.result === 1) {

                Swal.fire({
                    title: 'Success!',
                    text: 'DNS Zone successfully deleted!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });

            } else if (response.ok && result.data.metadata.result === 0) {
                let reasonText = result.data.metadata.reason;
                Swal.fire({
                    title: 'Warning!',
                    text: reasonText,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                let reasonText = result.data.metadata.reason;
                Swal.fire({
                    title: 'Error!',
                    text: reasonText,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
        } catch (error) {
            console.error('Error during record deleted:', error);
            Swal.fire({
                icon: 'error',
                title: 'Unexpected Error',
                text: error.message || 'An unexpected error occurred.',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'confirm-swal',
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        } finally {
            deleteButton.innerHTML = `Delete`;
            deleteButton.disabled = false;
            cancelButton.disabled = false;
            $('#deleteDNSConfirmModal').modal('hide');
        }
    });
</script>
@endsection