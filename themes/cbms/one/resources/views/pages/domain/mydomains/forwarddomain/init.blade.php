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
            <h3>DNS Manager</h3>
            <div class="alert alert-info-custom">
                <p>Untuk menggunakan fitur DNS Manager, pastikan domain yang digunakan mengarah ke name server berikut:</p>
                <ul>
                    <li>dnsiix1.qwords.net</li>
                    <li>dnsiix2.qwords.net</li>
                </ul>
                <p>Jika membutuhkan bantuan silakan hubungi kami melalui support ticket <a href="https://s.id/16Akz" target="_blank">https://s.id/16Akz</a></p>
            </div>
            <h3>Qwords DNS Manager</h3>
            <br>
            <!-- Form -->
            <form id="br-dnsrecord-manager" name="br-dnsrecord-manager">
                @csrf
                <input type="hidden" name="domain" value="{{ $domain }}" />
                <input type="hidden" name="action" value="createdns" />
                <p class="alert alert-danger-custom">Module could not find any existing DNS zone for this domain name. Do you want to create an empty DNS Zone now? (this process takes a long time ± 5 minutes)</p>
                <a href="{{ route('pages.domain.mydomains.details', ['id' => $domainId]) }}" class="btn btn-outline-warning btn-responsive">No, Go back</a>
                <button id="btn-create" type="button" class="btn btn-danger btn-responsive">
                    Yes, Create
                    <span class="spinner-border spinner-border-sm ml-2 d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>
            <!-- End Form -->
        </div>
    </div>
    
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        document.getElementById('btn-create').addEventListener('click', async function () {
            const button = this;
            const spinner = button.querySelector('.spinner-border');
            const form = document.getElementById('br-dnsrecord-manager');
            const formData = new FormData(form);

            spinner.classList.remove('d-none');
            button.disabled = true;

            try {
                const response = await fetch("{{ route('pages.domain.mydomains.details.forwarddomain.create-dns') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    console.error('Error:', response.statusText);
                    alert('An error occurred. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            } finally {
                spinner.classList.add('d-none');
                button.disabled = false;
            }
        });
</script>
@endsection