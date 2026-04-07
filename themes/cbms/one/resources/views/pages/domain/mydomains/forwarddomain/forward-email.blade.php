@extends('layouts.clientbase')

@section('title')
DNS Manager/ Forward Domain
@endsection

@section('page-title')
Domain Forwarder/ URL Masking
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


    .alert-success-custom {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
        padding: 15px;
        border-radius: 5px;
    }

    .nav-tabs .nav-link.active,
    .nav-tabs .nav-item.show .nav-link {
        color: #0273e5;
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
        <div class="row mb-3">
            <div class="col-xl-8 col-lg-8">
                <div class="header-breadcumb">
                    <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <a
                            href="{{ route('pages.domain.mydomains.index') }}"> / My Domains</a> <span
                            class="text-muted"> / Email Forwarder</span></h6>
                </div>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'dns']) }}">DNS Manager</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'domain_fwd']) }}">Domain Forwarder/URL Masking</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'email_fwd']) }}">Email Forwarder</a>
            </li>
        </ul>
        <br>

        @if($enabled)

        <div style="padding: 1em;border: 1px solid #ddd;border-radius: 5px;">
            <form id="setForward">
                <input type="hidden" name="domain" value="{{ $domain }}">
                <input type="hidden" name="action" value="settingforwardemail">
                <div class="form-group">
                    <label>Forward semua Email masuk ke {{ $domain }} menuju Email berikut:</label>
                    <input type="text" class="form-control" name="email" value="{{ $email }}" id="email" placeholder="example@mail.com">
                    <small class="form-text text-muted">
                        Limit maksimal 10 Email/Jam. Kami menyediakan layanan Cloud Hosting/Email Hosting untuk kebutuhan email yang lebih lengkap
                    </small>
                </div>
            </form>

            <form id="unsetForward">
                <input type="hidden" name="domain" value="{{ $domain }}">
                <input type="hidden" name="action" value="unsetforwardemail">
            </form>

            <div class="form-group text-center">
                <button class="btn btn-primary" id="btnSetForward">Enable Email Forwarder</button>
                <button class="btn btn-danger" id="btnUnsetForward" onclick="confirmRemove(event)">Disable Email Forwarder</button>
            </div>
        </div>

        <br>
        <form style="padding: 1em;border: 1px solid #ddd;border-radius: 5px;" id="addEmailForm">
            <input type="hidden" name="domain" value="{{ $domain }}">
            <input type="hidden" name="action" value="addforwardemail">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Alias</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Example" aria-describedby="basic-addon2" name="alias">
                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon2">@<div>{{ $domain }}</div></span>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="inputEmail4">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="test@gmail.com">
                </div>
            </div>
            <div class="form-group text-center">
                <button class="btn btn-primary" type="submit" id="addEmailButton"><i class="fas fa-plus mr-2"></i>Add Email Forwarding</button>
            </div>
        </form>
        <br>
        <table class="table">
            <thead>
                <tr>
                    <th style="text-align:left;">Alias</th>
                    <th style="text-align:left;">Email</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @if($datas && count($datas) > 0)
                @foreach($datas as $data)
                <tr>
                    <td style="text-align:left;"><span>{{ $data->alias }}</span></td>
                    <td style="text-align:left;"><span>{{ $data->email }}</span></td>
                    <td style="text-align:center;">
                        <button class="btn btn-outline-danger" type="button" onclick="deleteEmail(event, '{{ $data->id }}', '{{ $data->alias }}', '{{ $data->email }}', '{{ $domain }}')">Delete</button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="3" class="text-center"><strong>Record not available</strong></td>
                </tr>
                @endif
            </tbody>
        </table>


        @else
        <div class="alert alert-danger-custom">
            <p>
                Domain ini belum bisa menggunakan Email forwarder karena DNS Zone domain ini dibuat dengan sistem lain/Hosting.
                Jika anda bersikeras ingin menggunakan fitur ini anda harus menghapus DNS zone sekarang di Menu DNS Manager terlebih dahulu
                (Do it with your own Risk!, website anda akan down setelah menghapus DNS, hanya disarankan untuk website/domain baru akan dibuat) </p>
        </div>
        @endif

    </div>
</div>


<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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


<div class="modal fade" id="disableConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirmation</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to disable email forwarder?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDisableButton">Disable</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let recordToDelete = null;

    function deleteEmail(e, id, alias, email, domain) {
        recordToDelete = {
            id,
            alias,
            email,
            domain
        };
        $('#deleteConfirmModal').modal('show');
    }

    document.getElementById('confirmDeleteButton').addEventListener('click', async function() {
        if (recordToDelete) {
            const {
                id,
                alias,
                email,
                domain
            } = recordToDelete;
            const submitButton = this;
            const cancelButton = document.querySelector('#deleteConfirmModal .btn-secondary');

            try {
                const csrfToken = '{{ csrf_token() }}';
                submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...`;
                submitButton.disabled = true;
                cancelButton.disabled = true;

                const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.removeforwardemail") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id,
                        alias,
                        email,
                        domain
                    }),
                });

                const result = await response.json();

                if (response.ok && result.data.status.result === 1) {

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
                } else if (response.ok && result.data.status.result === 0) {
                    let errors = [];
                    response.data.forEach(item => {
                        if (item.errors && item.errors.length > 0) {
                            errors.push(...item.errors);
                        }
                    });

                    let readableErrors = errors
                        .map(error => error.replace(/\\u([\dA-Fa-f]{4})/g, (match, group) => {
                            return String.fromCharCode(parseInt(group, 16));
                        }))
                        .join('\n');

                    Swal.fire({
                        title: 'Warning!',
                        text: readableErrors,
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

    function confirmRemove(e) {
        e.preventDefault();
        $('#disableConfirmModal').modal('show');
    }

    document.getElementById('addEmailButton').addEventListener('click', async function() {
        const form = document.getElementById('addEmailForm');
        const formData = new FormData(form);
        const submitButton = document.getElementById('addEmailButton');

        try {

            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`;
            submitButton.disabled = true;

            const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.addforwardemail") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: formData,
            });

            const result = await response.json();

            if (response.ok && result.data.status.result === 1) {

                Swal.fire({
                    title: 'Success!',
                    text: 'Email Forwarding successfully created!',
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

            } else if (response.ok && result.data.status.resultt === 0) {
                let errorText = result.data.errors;
                Swal.fire({
                    title: 'Warning!',
                    text: errorText,
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
                let errorText = result.data.errors;
                Swal.fire({
                    title: 'Error!',
                    text: errorText,
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
            submitButton.innerHTML = "<i class='fas fa-plus mr-2'></i>Add Email Forwarding";
            submitButton.disabled = false;
        }
    });

    document.getElementById('btnSetForward').addEventListener('click', async function() {
        const form = document.getElementById('setForward');
        const formData = new FormData(form);
        const submitButton = document.getElementById('btnSetForward');

        try {

            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`;
            submitButton.disabled = true;

            const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.setforwardemail") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: formData,
            });

            const result = await response.json();

            if (response.ok && result.data.status === 1) {

                Swal.fire({
                    title: 'Success!',
                    text: 'Email Forwarding successfully Enabled!',
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

            } else if (response.ok && result.data.status === 0) {
                let reasonText = result.data.errors;
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
                let reasonText = result.data.errors;
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
            console.log(error);
        } finally {
            submitButton.innerHTML = "Enable Email Forwarder";
            submitButton.disabled = false;
        }
    });

    document.getElementById('confirmDisableButton').addEventListener('click', async function() {
        const resetButton = this;
        const cancelButton = document.querySelector('#disableConfirmModal .btn-secondary');
        const domain = '{{ $domain }}';
        const email = $('#email').val();


        try {
            resetButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`;
            resetButton.disabled = true;

            const response = await fetch('{{ route("pages.domain.mydomains.details.forwarddomain.unsetforwardemail") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    domain,
                    email
                }),
            });

            const result = await response.json();

            if (response.ok && result.data.status === 1) {

                Swal.fire({
                    title: 'Success!',
                    text: 'Email Forwarding successfully disabled!',
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

            } else if (response.ok && result.data.status === 0) {
                let reasonText = result.data.errors;
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
                let reasonText = result.data.errors;
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
                    title: 'Error!',
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
            resetButton.innerHTML = `Disable`;
            resetButton.disabled = false;
            cancelButton.disabled = false;
            $('#disableConfirmModal').modal('hide');
        }
    });
</script>
@endsection