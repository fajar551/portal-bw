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
                            class="text-muted"> / Domain Forwarder / URL Masking </span></h6>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'dns']) }}">DNS Manager</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'domain_fwd']) }}">Domain Forwarder/URL Masking</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('pages.domain.mydomains.details.dnsmanager', ['id' => $domainId, 'module' => 'ForwardDomain', 'page' => 'email_fwd']) }}">Email Forwarder</a>
            </li>
        </ul>
        <br>

        @if (!$enabled)
        <div class="alert alert-danger-custom">
            <p>
                Domain ini belum bisa menggunakan domain forwarder karena DNS Zone domain ini dibuat dengan sistem lain/Hosting. Jika anda bersikeras ingin menggunakan fitur ini anda harus menghapus DNS zone sekarang di Menu DNS Manager terlebih dahulu (Do it with your own Risk!, website anda akan down setelah menghapus DNS, hanya disarankan untuk website/domain baru akan dibuat)
            </p>
        </div>
        @endif

        <form id="frm-add" style="padding: 1em;border: 1px solid #ddd;border-radius: 5px;" novalidate>
            @csrf
            <input type="hidden" id="action_add" name="action" value="forwarddomain">
            <input type="hidden" id="uid" value="{{ $id }}">
            <input type="hidden" name="domain" value="{{ $domain }}">

            <div class="form-group">
                <div class="col-md-12 mb-3">
                    <label for="exampleInputEmail1">Pilih tipe : </label>
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input class="form-check-input" type="radio" name="masked" id="forward" value="false" checked>
                        <label class="control-label" for="forward">Domain Forwarder</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input class="form-check-input" type="radio" name="masked" id="masked" value="true">
                        <label class="form-check-label" for="masked">URL Masking</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12 mb-3">
                    <label for="domain">Domain</label>
                    <select class="form-control" id="domain" disabled>
                        @foreach ($domains as $value)
                        <option value="{{ $value->domain }}" {{ $domain == $value->domain ? 'selected' : '' }}>
                            {{ $value->domain }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-12 mb-3">
                    <label for="target">Target</label>
                    <input type="url" class="form-control" id="target" name="redirect" placeholder="https://qwords.com" required>
                    <div class="invalid-feedback">
                        Please provide a valid URL.
                    </div>
                    <small class="form-text text-muted">Proses setting Domain Forwarder/URL Masking memerlukan waktu 10 - 60 detik.</small>
                </div>
            </div>

            <div class="form-group text-center">
                <div class="col-md-12 mb-3">
                    <a>
                        <button class="btn btn-primary px-3" type="button" id="sbm-button" {{ !$enabled ? 'disabled' : '' }}>
                            <i class="fas fa-check mr-2"></i>Submit</button>
                    </a>
                    <a>
                        <button class="btn btn-danger px-3" id="del-button" {{ !$enabled ? 'disabled' : '' }}>
                            <i class="fas fa-times mr-2"></i>Delete</button>
                    </a>
                </div>
            </div>

        </form>

        <form id="fr-delete" style="display:none">
            @csrf
            <input type="hidden" name="action" value="removeforward">
            <input type="hidden" name="masked" id="del-masked" value="false">
            <input type="hidden" name="domain" id="del-domain" value="">
            <input type="hidden" name="redirect" id="del-redirect" value="">
            <button type="submit" class="btn btn-primary">Delete</button>
        </form>
    </div>
</div>

<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
            </div>
            <div class="modal-body">
                Pastikan domain telah menggunakan nameserver qwords dan tidak digunakan di hosting manapun. Lanjutkan?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelSubmit" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSubmit">Yes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmationDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Delete Confirmation</h5>
            </div>
            <div class="modal-body">
                Apakah anda yakin ingin menghapus Record Domain Forwarder/URL Masking?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>
    $("#sbm-button").click(function(e) {
        e.preventDefault();
        $("#confirmationModal").modal("show");
    });

    document.getElementById('confirmSubmit').addEventListener('click', async function(e) {
        e.preventDefault();

        const form = document.getElementById('frm-add');
        const formData = new FormData(form);

        const confirmButton = document.getElementById('confirmSubmit');
        const cancelButton = document.getElementById('cancelSubmit');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            $("#confirmationModal").modal("hide");
            return;
        }

        confirmButton.disabled = true;
        cancelButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

        try {
            const csrfToken = '{{ csrf_token() }}';

            const response = await fetch("{{ route('pages.domain.mydomains.details.forwarddomain.addForwardDomain') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const result = await response.json();

            if (response.ok && result.data.status === true) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Domain Successfully Forwarded!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }

            if (result.errorgg) {
                console.log(result);
                Swal.fire({
                    title: 'Error!',
                    text: result.errorgg,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }

        } catch (error) {
            console.error('Error during forwarding record:', error);
            Swal.fire({
                icon: 'error',
                title: 'Unexpected Error',
                text: error.message || 'An unexpected error occurred.',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'confirm-swal',
                },
            });
        } finally {
            confirmButton.disabled = false;
            cancelButton.disabled = false;
            confirmButton.innerHTML = 'Yes';
            $("#confirmationModal").modal("hide");
        }
    });

    $("#del-button").click(function(e) {
        e.preventDefault();
        $("#confirmationDeleteModal").modal("show");
    })

    document.getElementById('confirmDelete').addEventListener('click', async function(e) {
        e.preventDefault();

        const form = document.getElementById('fr-delete');
        const formData = new FormData(form);

        const confirmButton = document.getElementById('confirmDelete');
        const cancelButton = document.getElementById('cancelDelete');

        confirmButton.disabled = true;
        cancelButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';

        try {
            const csrfToken = '{{ csrf_token() }}';

            const response = await fetch("{{ route('pages.domain.mydomains.details.forwarddomain.removeForwardDomain') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const result = await response.json();

            if (response.ok && result.data.status === true) {
                console.log(result);
                Swal.fire({
                    title: 'Success!',
                    text: 'Domain Forwarder/URL Masking Successfully Deleted!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }

            if (result.errorgg) {
                console.log(result);
                Swal.fire({
                    title: 'Error!',
                    text: result.errorgg,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'confirm-swal',
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }

        } catch (error) {
            console.error('Error during forwarding record:', error);
            Swal.fire({
                icon: 'error',
                title: 'Unexpected Error',
                text: error.message || 'An unexpected error occurred.',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'confirm-swal',
                },
            });
        } finally {
            confirmButton.disabled = false;
            cancelButton.disabled = false;
            confirmButton.innerHTML = 'Yes';
            $("#confirmationDeleteModal").modal("hide");
        }
    });

    $("#target").on('input', function(e) {
        $("#del-redirect").val(this.value)
    })

    $('[name="masked"]').change(function(e) {
        $("#del-masked").val(this.value)

        if ($('#masked')[0].checked) {
            $('#action_add').val('maskdomain')
        } else {
            $('#action_add').val('forwarddomain')
        }
    })

    $("#del-domain").val($('#domain').val());
    $("#del-redirect").val($('#target').val());
</script>
@endsection