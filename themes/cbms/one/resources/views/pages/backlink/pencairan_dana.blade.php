@extends('layouts.clientbase')

@section('title')
    Insert Sell & Rent Domain Page
@endsection

@section('content')
    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mb-0">Sell Backlink</h2>
                    <small class="text-muted">By CBMS</small>
                </div>

                {{-- Message alert --}}
                <div class="col-md-12">
                    @if (Session::get('alert-message'))
                        <div class="alert alert-{{ Session::get('alert-type') }}" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {!! nl2br(Session::get('alert-message')) !!}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <b>Error:</b>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                {{-- Message alert --}}

                <input type="hidden" value="{{ $clientsdetails['uuid'] }}" id="uuid" />
                <input type="hidden" value="{{ $clientsdetails['email'] }}" id="myEmail" />
                <input type="hidden" value="0" id="total_backlink_hidden" />
                <input type="hidden" value="0" id="total_whmcs_hidden" />

                <div class="col-md-12">
                    <div class="row mb-3 mt-3">
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/blog_saya') . '?page=index' }}"
                                class="btn btn-lg btn-outline-success w-100" style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Blog Saya
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/pesan_backlink') . '?page=pesan_backlink' }}"
                                class="btn btn-lg btn-outline-success w-100" style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Pesan Backlink
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/daftar_penjualan') . '?page=daftar_penjualan' }}"
                                class="btn btn-lg btn-outline-success w-100" style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Daftar Penjualan
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/daftar_pembelian') . '?page=daftar_pembelian' }}"
                                class="btn btn-lg btn-outline-success w-100" style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Daftar Pembelian
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/pencairan_dana') . '?page=pencairan_dana' }}"
                                class="btn btn-lg btn-outline-success w-100 active"
                                style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Pencairan Dana
                            </a>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end w-100 mt-3 mb-3">
                    <p class="mb-0 mr-2 align-self-center" style="font-size: 14px; width: fit-content;">
                        Filter Data:
                    </p>
                    <button class="btn btn-success" data-toggle="modal" data-target="#filterPencairanDana">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="col-md-6">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <h6 class="font-weight-bold mr-2">Total Deposit WHMCS:</h6>
                            <h6 id="total_whmcs">Rp0</h6>
                        </div>
                        <div class="d-flex align-items-center">
                            <h6 class="font-weight-bold mr-2">Deposit Backlink Terjual:</h6>
                            <h6 id="total_backlink">Rp0</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex flex-column align-items-end">
                        <div class="d-flex align-items-center mb-2">
                            <h6 class="font-weight-bold mb-0 mr-2">Rekening:</h6>
                            <select class="form-control" id="dataNorek">
                                <option>List Rekening Anda</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr />

                <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-end align-items-center mb-3">
                        {{-- <h3 class="font-weight-bold mb-0">Histori Transaksi</h3> --}}
                        <button class="btn btn-success btn-lg m-2" data-toggle="modal" data-target="#settingRekening"
                            style="font-size: 0.8rem;">Setting Rekening Bank</button>
                        <button class="btn btn-success btn-lg m-2" data-toggle="modal" data-target="#cairkanDana"
                            style="font-size: 0.8rem;">Cairkan Dana</button>
                    </div>
                </div>

                <div class="col-12">
                    <div class="table-responsive mt-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>Riwayat Pencairan Dana</h5>
                                <table id="dtable-pencairan-dana" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Pemasukan</th>
                                            <th>Pengeluaran</th>
                                            <th>Catatan</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="data">
                                        <!-- Data rows go here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

{{-- <div id="modal_loader_backlink" class="d-flex justify-content-center align-items-center position-fixed w-100 h-100 bg-dark bg-opacity-50" style="z-index: 109999; display: none;">
    <div id="loader" class="bg-white p-3 rounded d-flex">
        <img class="load" src="{{ $documentroot ?? '' }}/load.webp" width="40px" height="43px" />
        <h5 class="ml-3 align-self-center" id="content-modal">Sedang mendaftarkan blog Anda, mohon tunggu...</h5>
    </div>
</div> --}}

<div class="modal fade" id="settingRekening" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Setting Rekening Bank</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="nama_bank">Nama Bank:</label>
                    <input type="text" name="nama_bank" id="nama_bank" class="form-control"
                        placeholder="Masukkan nama bank Anda" />
                </div>
                <div class="form-group">
                    <label for="no_rek">Nomor Rekening:</label>
                    <input type="number" name="no_rek" id="no_rek" class="form-control"
                        placeholder="Masukkan nomor rekening Anda" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="setting_rekening" onclick="settingRekening()" type="submit"
                    data-dismiss="modal">Submit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cairkanDana" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Cairkan Dana</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="dataCair" class="font-weight-bold">Pilih Rekening Anda:</label>
                    <select class="form-control" id="dataCair"></select>
                </div>
                <div class="form-group">
                    <label for="requested-price" class="font-weight-bold">Masukkan Dana:</label>
                    <input type="number" id="requested-price" class="form-control"
                        placeholder="Masukkan dana yang ingin Anda cairkan" />
                </div>
                <p><i>*dana yang dapat dicairkan minimal Rp200.000</i></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="cair_dana" onclick="cairkanDana()" type="submit">Cairkan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filterPencairanDana" tabindex="-1" role="dialog"
    aria-labelledby="filterPencairanDanaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterPencairanDanaLabel">Filter Pencairan Dana</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="form-group">
                        <label for="minPemasukan">Minimum Pemasukan</label>
                        <input type="number" class="form-control" id="minPemasukan"
                            placeholder="Enter minimum pemasukan">
                    </div>
                    <div class="form-group">
                        <label for="maxPemasukan">Maximum Pemasukan</label>
                        <input type="number" class="form-control" id="maxPemasukan"
                            placeholder="Enter maximum pemasukan">
                    </div>
                    <div class="form-group">
                        <label for="minPengeluaran">Minimum Pengeluaran</label>
                        <input type="number" class="form-control" id="minPengeluaran"
                            placeholder="Enter minimum pengeluaran">
                    </div>
                    <div class="form-group">
                        <label for="maxPengeluaran">Maximum Pengeluaran</label>
                        <input type="number" class="form-control" id="maxPengeluaran"
                            placeholder="Enter maximum pengeluaran">
                    </div>
                    <div class="form-group">
                        <label for="startDate">Start Date</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="form-group">
                        <label for="endDate">End Date</label>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="applyFilters">Apply Filters</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap4.min.js"></script>

    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <script type="text/javascript">
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        $(document).ready(function() {
            const table = $('#dtable-pencairan-dana').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/backlink/action?action=myHistory&id=' + uuid,
                    type: 'GET',
                    dataSrc: 'data',
                    data: function(d) {
                        d.minPemasukan = $('#minPemasukan').val();
                        d.maxPemasukan = $('#maxPemasukan').val();
                        d.minPengeluaran = $('#minPengeluaran').val();
                        d.maxPengeluaran = $('#maxPengeluaran').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    }
                },
                columns: [{
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        orderable: false
                    },
                    {
                        data: 'pemasukan',
                        render: function(data) {
                            return data ? `Rp${data}` : '-';
                        },
                        type: 'num'
                    },
                    {
                        data: 'pengeluaran',
                        render: function(data) {
                            return data ? `Rp${data}` : '-';
                        },
                        type: 'num'
                    },
                    {
                        data: 'catatan',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return data ? new Date(data).toLocaleDateString() : '-';
                        }
                    }
                ],
                order: [
                    [4, 'desc']
                ]
            });

            $('#applyFilters').on('click', function() {
                table.ajax.reload();
                $('#filterPencairanDana').modal('hide');
            });
        });
    </script>

    <script>
        let id = $("#uuid").val();
        let email = $("#myEmail").val();

        $.get('/backlink/action?action=getDataBank', {
            id: id
        }, function(data) {
            let selectElement = $("#dataNorek");
            let selectElement2 = $("#dataCair");

            data.forEach(val => {
                // Create a new option element for dataNorek
                let option = $('<option>', {
                    value: val.id,
                    text: `${val.name_rek} - ${val.no_rek}`
                });
                selectElement.append(option);

                // Create a new option element for dataCair
                let option2 = $('<option>', {
                    value: val.id,
                    text: `${val.name_rek} - ${val.no_rek}`
                });
                selectElement2.append(option2);
            });
        });

        $.get('/backlink/action?action=getTotalDeposit', {
            uuid: id
        }, function(data) {
            $("#total_whmcs").text(`Rp${data.credit}`);
            $("#total_backlink").text(`Rp${data.credit_backlink}`);
            $("#total_backlink_hidden").val(data.credit_backlink);
            $("#total_whmcs_hidden").val(data.credit);
        });

        function settingRekening() {
            let nama_bank = $("#nama_bank").val();
            let no_rek = $("#no_rek").val();
            //let loader = $("#modal_loader_backlink");
            let content = $("#content-modal");
            content.text("Setting rekening bank, please wait...");
            // loader.css("display", "flex");

            $.get('/backlink/action?action=settingRekening', {
                    uuid: id,
                    nama_bank: nama_bank,
                    no_rek: no_rek,
                    email: email
                })
                .done(function(data) {
                    if (data.code === 'success') {
                        Toast.fire({
                            icon: 'success',
                            title: data.message
                        });
                        location.reload();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    //loader.css("display", "none");
                    console.error('Fetch error:', errorThrown);
                });
        }

        function cairkanDana() {
            let valueRekening = $("#dataCair").val();
            let requestedPrice = $("#requested-price").val();
            let total_backlink_hidden = $("#total_backlink_hidden").val();
            let total_whmcs_hidden = $("#total_whmcs_hidden").val();

            // Function to display alert messages
            function showAlert(message) {
                // Remove any existing alert
                $(".modal-body .alert").remove();

                // Create and prepend the new alert
                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>`;
                $(".modal-body").prepend(alertHtml);
            }

            // Validation checks
            if (parseFloat(requestedPrice) < 200000) {
                showAlert('Dana yang ingin Anda cairkan kurang dari Rp200.000');
                return;
            }

            if (parseFloat(total_backlink_hidden) < 200000) {
                showAlert('Total deposit backlink terjual Anda kurang dari Rp200.000');
                return;
            }

            if (parseFloat(total_backlink_hidden) < parseFloat(requestedPrice)) {
                showAlert('Total deposit backlink terjual Anda kurang dari dana yang ingin dicairkan');
                return;
            }

            if (parseFloat(total_whmcs_hidden) < parseFloat(total_backlink_hidden)) {
                showAlert('Total deposit WHMCS lebih sedikit dari total deposit backlink! Mohon hubungi Admin untuk lebih lanjut');
                return;
            }

            // let loader = $("#modal_loader_backlink");
            // let content = $("#content-modal");
            content.text("Processing deposit, please wait...");
            // loader.css("display", "flex");

            $.get('/backlink/action?action=cairkanDana', {
                    uuid: id,
                    valueRekening: valueRekening,
                    email: email,
                    price: requestedPrice
                })
                .done(function(data) {
                    if (data.code === 'success') {
                        Toast.fire({
                            icon: 'success',
                            title: data.message
                        });
                        window.location.href =
                            `https://portal.qwords.com/viewticket.php?tid=${data.tid}&c=${data.c}`;
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    // loader.css("display", "none");
                    console.error('Fetch error:', errorThrown);
                });
        }
    </script>
@endsection
