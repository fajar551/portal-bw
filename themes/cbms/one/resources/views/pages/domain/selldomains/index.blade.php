@extends('layouts.clientbase')

@section('title')
    <title>Sell & Rent Domain</title>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('modules/addons/sell_domain/assets/custom.css') }}">

    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 col-md-8">
                    <h2 class="mb-0">Sell & Rent Domain</h2>
                    <small class="text-muted">By CBMS</small>
                </div>

                {{-- Message alert --}}
                <div class="col-12">
                    @if (Session::get('alert-message'))
                        <div class="alert alert-{{ Session::get('alert-type') }} alert-dismissible fade show" role="alert">
                            {!! nl2br(Session::get('alert-message')) !!}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Error:</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                {{-- Message alert --}}

                <div class="col-12 d-flex justify-content-between align-items-center my-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="me-3 mr-2">
                            <a href="{{ route('pages.domain.selldomains.index', ['page' => 'rent_all']) }}"
                                class="text-dark d-flex align-items-center" data-toggle="tooltip" data-placement="top"
                                title="Public Rent Domain" aria-label="Settings">
                                <i class="fas fa-university fa-2x"></i>
                            </a>
                        </div>
                        <div class="me-3 mr-2">
                            <a href="{{ route('pages.domain.selldomains.index', ['page' => 'my_rent']) }}"
                                class="btn btn-primary font-weight-bold d-flex align-items-center" data-toggle="tooltip"
                                data-placement="top" title="My Rent Domain">
                                My Rent Domain
                            </a>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-center">
                        <div class="me-3 mr-2">
                            <a href="{{ route('pages.domain.selldomains.index', ['page' => 'insert']) }}"
                                class="btn btn-primary font-weight-bold d-flex align-items-center"
                                data-toggle="tooltip" data-placement="top" title="Tambah Katalog Domain">
                                <i class="fas fa-plus"> </i> Tambah Katalog Domain
                            </a>
                        </div>
                        <div class="me-3 mr-2">
                            <a href="{{ route('pages.domain.selldomains.index', ['page' => 'setting']) }}"
                                class="text-dark d-flex align-items-center" aria-label="Settings" data-toggle="tooltip"
                                data-placement="top" title="Settings">
                                <i class="fas fa-cog fa-2x"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Jual Domain</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Domain</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Harga Awal</th>
                                    <th scope="col">Harga Beli Langsung</th>
                                    <th scope="col">Enable</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($domains as $data)
                                    <tr>
                                        <td>{{ $data->domain }}</td>
                                        <td>
                                            @if ($data->status == 'VERIFIED')
                                                <span class="badge badge-success">{{ __('ON_SELLING') }}</span>
                                            @elseif($data->status == 'NEED_VERIFY')
                                                <span class="badge badge-warning">{{ __('NEED_VERIFY') }}</span>
                                            @elseif($data->status == 'INVOICE_PAID')
                                                <span class="badge badge-info">{{ __('INVOICE_PAID') }}</span>
                                            @elseif($data->status == 'PROCESS_TRANSFER')
                                                <span class="badge badge-info">{{ __('PROCESS_TRANSFER') }}</span>
                                            @elseif($data->status == 'SETTLED')
                                                <span class="badge badge-info">{{ __('SETTLED') }}</span>
                                            @elseif($data->status == 'REJECTED')
                                                <span class="badge badge-danger">{{ __('REJECTED') }}</span>
                                                @if ($data->notes)
                                                    <p class="mt-2">[Notes: {{ $data->notes }}]</p>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            Rp {{ is_numeric($data->auction_price) ? number_format($data->auction_price, 0, '.', '.') : '0' }}
                                        </td>
                                        <td>
                                            Rp {{ is_numeric($data->fix_price) ? number_format($data->fix_price, 0, '.', '.') : '0' }}
                                        </td>
                                        <td>
                                            @if ($data->status == 'VERIFIED')
                                                <form method="POST" action="{{ route('pages.domain.selldomains.action', ['domain' => $data->domain, 'action' => 'toggle']) }}">
                                                    @csrf
                                                    <label class="switch">
                                                        <input type="checkbox" onclick="handleEnableDomain(this,'{{ $data->domain }}')"
                                                        @if ($data->enabled) checked @endif />
                                                    <span class="slider round"></span>
                                                    </label>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($data->status == 'NEED_VERIFY')
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <a class="btn btn-primary mr-2" data-toggle="tooltip" data-placement="top"
                                                    title="Verifikasi Domain" href="{{ route('pages.domain.selldomains.index', ['section' => 'modal', 'page' => 'insert', 'domain' => $data->domain]) }}">
                                                    Verifikasi
                                                    </a>
                                                    <form method="POST" action="{{ route('pages.domain.selldomains.action', ['domain' => $data->domain, 'action' => 'delete']) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger" data-confirm="true" data-toggle="tooltip" data-placement="top" title="Hapus Domain">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($data->status == 'INVOICE_PAID')
                                                @if ($data->qword_status == 'Active')
                                                    <p> transfer otomatis </p>
                                                @else
                                                    <a class="btn btn-primary" data-toggle="tooltip" data-placement="top"
                                                        title="Kirim EPP" href="{{ route('pages.domain.selldomains.index', ['page' => 'epp', 'domain' => $data->domain]) }}">
                                                        Kirim EPP
                                                    </a>
                                                @endif
                                            @elseif($data->status == 'VERIFIED')
                                                <div class="d-flex flex-wrap align-items-center">
                                                    <a class="btn btn-primary mr-2" data-toggle="tooltip" title="Atur Harga" href="{{ route('pages.domain.selldomains.index', ['page' => 'edit', 'domain' => $data->domain]) }}">
                                                        Atur Harga
                                                    </a>
                                                    <form method="POST" action="{{ route('pages.domain.selldomains.action', ['domain' => $data->domain, 'action' => 'delete']) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger" data-confirm="true" data-toggle="tooltip" data-placement="top" title="Hapus Domain">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                                @if ($data->nameserver)
                                                    <button class="btn btn-danger"
                                                        data-toggle="tooltip" data-placement="top" title="Seller Note"
                                                        onclick="updateNote('{{ $data->domain }}', '{{ $data->seller_note }}')">
                                                        Seller Note
                                                    </button>
                                                @endif
                                            @endif

                                            @if ($data->type == 'LELANG' && $data->status == 'VERIFIED')
                                                <a class="btn btn-danger" data-toggle="tooltip" title="Lihat History Bid"
                                                    href="{{ route('pages.domain.lelangdomains.index', ['page' => 'history', 'domain' => $data->domain]) }}">
                                                    Lihat History Bid
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Sewa Domain</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Domain</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Harga</th>
                                    <th scope="col">EPP</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($domain_rents as $data)
                                    <tr>
                                        <td>{{ $data->domain }}</td>
                                        <td>
                                            @if ($data->status == 'NEED_VERIFY')
                                                <span class="badge badge-warning">{{ __('NEED_VERIFY') }}</span>
                                            @elseif($data->status == 'VERIFIED')
                                                <span class="badge badge-success">{{ __('VERIFIED') }}</span>
                                            @elseif($data->status == 'INVOICE_PAID')
                                                <span class="badge badge-info">{{ __('INVOICE_PAID') }}</span>
                                            @elseif($data->status == 'PROCESS_TRANSFER')
                                                <span class="badge badge-info">{{ __('PROCESS_TRANSFER') }}</span>
                                            @elseif($data->status == 'SETTLED')
                                                <span class="badge badge-info">{{ __('SETTLED') }}</span>
                                            @elseif($data->status == 'REJECTED')
                                                <span class="badge badge-danger">{{ __('REJECTED') }}</span>
                                                @if ($data->notes)
                                                    <p class="mt-2">[Notes: {{ $data->notes }}]</p>
                                                @endif
                                            @elseif($data->status == 'RENT_ACTIVE')
                                                <span class="badge badge-success">{{ __('RENT_ACTIVE') }}</span>
                                            @endif
                                        </td>
                                        <td>Rp {{ number_format($data->price, 0, '.', '.') }}</td>
                                        <td>{{ $data->epp }}</td>
                                        <td>
                                            @if ($data->status == 'PROCESS_TRANSFER')
                                                <button class="btn btn-info" onclick="rentEPP('{{ $data->domain }}')">Setting EPP</button>
                                            @endif

                                            @if ($data->status == 'NEED_VERIFY' || $data->status == 'VERIFIED')
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <a class="btn btn-primary mr-2" data-toggle="tooltip" data-placement="top"
                                                        title="Verifikasi Domain" href="{{ route('pages.domain.selldomains.index', ['section' => 'modal', 'page' => 'insert', 'domain' => $data->domain]) }}">
                                                        Verifikasi
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('pages.domain.selldomains.action', ['domain' => $data->domain, 'action' => 'delete_rent']) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger"
                                                            data-confirm="true" data-toggle="tooltip" data-placement="top" title="Hapus Domain">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($data->status == 'RENT_ACTIVE')
                                                {{-- <button class="btn btn-success"
                                                    data-toggle="tooltip" data-placement="top" title="Seller Note"
                                                    onclick="updateNote('{{ $data->domain }}', '{{ $data->seller_note }}')">
                                                    Seller Note
                                                </button> --}}

                                            @endif
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

    <div class="modal fade" id="modal_epp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Set up EPP</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="setupEPPForm" method="POST" action="{{ route('pages.domain.selldomains.action') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Masukan EPP Domain Lease</label>
                            <input type="text" class="form-control" name="epp" id="input_epp_rent" required />
                        </div>
                        <input type="hidden" name="action" value="setup_epp_rent">
                        <input type="hidden" name="domain" id="input_domain_rent">
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_seller_note" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h4 class="modal-title mb-0" id="myModalLabel">Seller Note</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Masukan Description Domain</label>
                        <div id="editor-container" class="text-dark mb-3"></div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <form method="POST" action="{{ route('pages.domain.selldomains.action') }}">
                        @csrf
                        <input type="hidden" name="action" value="update_note">
                        <input type="hidden" name="domain" id="seller_domain">
                        <input type="hidden" name="seller_note" id="seller_note">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to proceed?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the CKEditor library -->
    <script src="https://cdn.ckeditor.com/ckeditor5/35.0.1/classic/ckeditor.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize CKEditor
            ClassicEditor
                .create(document.querySelector('#editor-container'))
                .then(editor => {
                    window.editor = editor;
                })
                .catch(error => {
                    console.error('There was a problem initializing the editor:', error);
                });

            // Function to update the note
            window.updateNote = function(domain, note) {
                var decodedNote = $('<textarea/>').html(note).val(); // Decode HTML entities
                $('#seller_domain').val(domain);
                window.editor.setData(decodedNote);
                $('#modal_seller_note').modal('show');
            };

            // Ensure the editor data is set to the hidden input before form submission
            $('#modal_seller_note form').on('submit', function() {
                $('#seller_note').val(window.editor.getData());
            });

            $('.table').DataTable({
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });

            window.handleEnableDomain = function(el, domain) {
                var value = $(el).is(':checked');
                $(el).closest('form').submit();
            };

            window.rentEPP = function(domain) {
                $('#input_domain_rent').val(domain);
                $('#modal_epp').modal('show');
            };

            let formToSubmit;

            // Handle form submissions that require confirmation
            $('form button[data-confirm]').on('click', function(e) {
                e.preventDefault();
                formToSubmit = $(this).closest('form');
                $('#confirmationModal').modal('show');
            });

            // Confirm action
            $('#confirmAction').on('click', function() {
                if (formToSubmit) {
                    formToSubmit.submit();
                }
            });
        });
    </script>
@endsection
