@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Edit Contact Person</title>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        .form-group {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        .form-group label {
            flex: 0 0 200px;
            margin-bottom: 0;
        }
        .form-control-plaintext {
            flex: 1;
            padding: 0.375rem 0.75rem;
            margin-bottom: 0;
            line-height: 1.5;
            color: #495057;
            background-color: #e9ecef;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        .form-control[readonly] {
            background-color: #f8f9fa;
        }
        .form-control {
            border: none;
            border-bottom: 1px solid #ccc;
            border-radius: 0;
            box-shadow: none;
            flex: 1;
            width: 100%;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #000;
        }
        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="client-summary-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Edit contact person <strong>{{ $contact['name']['full_name'] }}</strong></h4>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-4">
                                        <h5 class="mb-4">Contact person data</h5>
                                        <form method="POST" action="{{ route('admin.pages.clients.viewclients.clientdomain.updateContact', $contact['id']) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group">
                                                <label for="username">Type of contact *</label>
                                                <div style="flex: 1;">
                                                    <input type="text" class="form-control" id="username" value="{{ $contact['role'] }}" readonly>
                                                    <small class="form-text">You can't change username</small>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="username">Username *</label>
                                                <div style="flex: 1;">
                                                    <input type="text" class="form-control" id="username" value="{{ $contact['username'] }}" readonly>
                                                    <small class="form-text">You can't change username</small>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="firstName">First name *</label>
                                                <input type="text" class="form-control" id="firstName" value="{{ $contact['name']['first_name'] }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="prefix">Prefix</label>
                                                <input type="text" class="form-control" id="prefix" value="{{ $contact['name']['prefix'] }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="lastName">Last name *</label>
                                                <input type="text" class="form-control" id="lastName" value="{{ $contact['name']['last_name'] }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="telephone">Telephone *</label>
                                                <div style="display: flex; gap: 10px;">
                                                    <input type="text" class="form-control" id="countryCode" value="{{ $contact['phone']['country_code'] }}" style="flex: 1;">
                                                    <input type="text" class="form-control" id="areaCode" value="{{ $contact['phone']['area_code'] }}" style="flex: 1;">
                                                    <input type="text" class="form-control" id="subscriberNumber" value="{{ $contact['phone']['subscriber_number'] }}" style="flex: 2;">
                                                </div>
                                                <small class="form-text">Example: +31 20 1234567</small>
                                            </div>

                                            <div class="form-group">
                                                <label for="email">E-mail address *</label>
                                                <input type="email" class="form-control" id="email" value="{{ $contact['email'] }}">
                                            </div>

                                            <h5 class="mt-4">Address details</h5>

                                            <div class="form-group">
                                                <label for="companyName">Company name *</label>
                                                <input type="text" class="form-control" id="companyName" value="{{ $contact['company_name'] ?? '' }}">
                                                <small class="form-text">Only enter company name if different from the reseller account, or if a different address is required.</small>
                                            </div>

                                            <div class="form-group">
                                                <label for="street">Street *</label>
                                                <input type="text" class="form-control" id="street" value="{{ $contact['address']['street'] ?? '' }} ">
                                            </div>

                                            <div class="form-group">
                                                <label for="number">Number * and suffix</label>
                                                <div style="display: flex; gap: 10px;">
                                                    <input type="text" class="form-control" id="number" value="{{ $contact['address']['number'] ?? '' }}" style="flex: 1;">
                                                    <input type="text" class="form-control" id="suffix" placeholder="The suffix is optional." style="flex: 2;">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="zipcode">Zipcode * and city *</label>
                                                <div style="display: flex; gap: 10px;">
                                                    <input type="text" class="form-control" id="zipcode" value="{{ $contact['address']['zipcode'] ?? '' }}" style="flex: 1;">
                                                    <input type="text" class="form-control" id="city" value="{{ $contact['address']['city'] ?? '' }}" style="flex: 2;">
                                                </div>
                                                <small class="form-text">Example: 1234 AB</small>
                                            </div>

                                            <div class="form-group">
                                                <label for="state">State/province</label>
                                                <input type="text" class="form-control" id="state" placeholder="Enter state or province">
                                            </div>

                                            <div class="form-group">
                                                <label for="country">Country *</label>
                                                <select class="form-control" id="country">
                                                    <option value="ID" selected>Indonesia</option>
                                                    <!-- Add more countries as needed -->
                                                </select>
                                            </div>

                                            <h5 class="mt-4">API access restrictions</h5>
                                            <p>If any IP addresses are entered in the white list, those are the only IP addresses from which the API accepts connections. All other connections will be refused. If any IP addresses are entered in the black list, connections from those IP addresses are refused. If neither white list nor black list contains any IP addresses, connections will be accepted from all sources.</p>

                                            <div class="form-group">
                                                <label for="whitelist">White list</label>
                                                <div id="whitelist" style="margin-top: 10px;">
                                                    @if(isset($contact['api_client_ip_list']['allow']) && is_array($contact['api_client_ip_list']['allow']))
                                                        @foreach($contact['api_client_ip_list']['allow'] as $ip)
                                                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                                                <input type="text" class="form-control" value="{{ $ip ?? '' }}" readonly style="flex: 1; border: none; border-bottom: 1px solid #ccc; margin-right: 10px;">
                                                                <button type="button" class="btn btn-danger" style="padding: 0.5rem; margin-right: 10px;">✖</button>
                                                                <button type="button" class="btn btn-success" style="padding: 0.5rem;">+ Add IP address</button>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                                            <input type="text" class="form-control" value="" readonly style="flex: 1; border: none; border-bottom: 1px solid #ccc; margin-right: 10px;">
                                                            <button type="button" class="btn btn-danger" style="padding: 0.5rem; margin-right: 10px;">✖</button>
                                                            <button type="button" class="btn btn-success" style="padding: 0.5rem;">+ Add IP address</button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                              <div class="modal-footer">
                                                
                                                    
                                                    <!-- Form fields here -->
                                                    <button type="submit" class="btn btn-primary" id="updateButton">Update</button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeBtn">Close</button>
                                            
                                                </div>

                                        </form>
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
    @stack('clientsearch')
    <!-- Tambahkan SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('updateButton').addEventListener('click', function(event) {
            event.preventDefault(); // Mencegah form dari pengiriman langsung
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda tidak dapat mengembalikan perubahan ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, perbarui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('updateForm').submit(); // Mengirimkan form jika dikonfirmasi
                }
            });
        });

        // Tampilkan SweetAlert jika ada session message
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#3085d6',
                timer: 3000
            });
        @endif
    </script>
@endsection