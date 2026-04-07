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

                <input type="text" value="{{ $myid }}" id="myId" />
                <input type="text" value="{{ $email }}" id="myEmail" />

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
                                class="btn btn-lg btn-outline-success w-100 active"
                                style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Daftar Pembelian
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/pencairan_dana') . '?page=pencairan_dana' }}"
                                class="btn btn-lg btn-outline-success w-100" style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Pencairan Dana
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end w-100 mt-3">
                    <p class="mb-0 mr-2 align-self-center" style="font-size: 14px; width: fit-content;">
                        Filter Data:
                    </p>
                    <button class="btn btn-success" data-toggle="modal" data-target="#filterSales">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="table-responsive mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>My Table Backlink</h5>
                            <table id="dtable-daftar-pembelian" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>URL Blog</th>
                                        <th>Link URL</th>
                                        <th>Harga</th>
                                        <th>Status Penjual</th>
                                        <th>Status Pembeli</th>
                                        <th>Notes</th>
                                        <th>Invoice ID</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="data-daftar-pembelian">
                                </tbody>
                            </table>
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

<div class="modal fade" id="filterSales" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Filter Penjualan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <div class="mb-3">
                        <p>Tanggal:</p>
                        <input type="date" name="tanggal" id="tanggal_filter" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <p>Domain:</p>
                        <input type="text" name="domain" id="domain_filter" class="form-control" placeholder="Cari berdasarkan Domain" />
                    </div>
                    <div class="mb-3">
                        <p>Harga Minimal:</p>
                        <input type="number" name="harga_minimal" id="harga_minimal_filter" class="form-control" placeholder="Masukkan harga minimal" />
                    </div>
                    <div class="mb-3">
                        <p>Harga Maksimal:</p>
                        <input type="number" name="harga_maksimal" id="harga_maksimal_filter" class="form-control" placeholder="Masukkan harga maksimal" />
                    </div>
                    <div class="mb-3">
                        <p>Status:</p>
                        <select class="form-control" id="status_filter" name="status">
                            <option value="">Semua</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button class="btn btn-primary" onclick="applySalesFilter()" type="button" data-dismiss="modal">Simpan Filter</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalApprove" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Approve Backlink</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda ingin approve backlink ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="approveBacklink()" id="approve_transaction" type="submit"
                    data-dismiss="modal">Ya</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPesan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Komentar Backlink</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h3 class="font-weight-bold text-center mb-4">Backlink <span id="url_blog">url_blog</span></h3>
                <div class="d-flex justify-content-between">
                    <div class="penjual-chat">
                        <p id="nama_penjual">Penjual: -</p>
                        <p id="status_penjual" class="mb-0">Status Penjual: Need Action</p>
                    </div>
                    <div class="pembeli-chat">
                        <p id="nama_pembeli">Pembeli: -</p>
                        <p id="status_pembeli" class="mb-0">Status Pembeli: Need Action</p>
                    </div>
                </div>
                <hr>
                <div id="message_container">
                    <p>Tidak ada komentar</p>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="w-100">
                    <textarea name="message" id="message" class="form-control" placeholder="Masukkan message Anda untuk Penjual" required></textarea>
                    <div class="invalid-feedback">
                        Tolong masukkan pesan Anda.
                    </div>
                </div>
                <button class="btn btn-primary" style="width:30%" onclick="kirimPesan()" id="kirim_pesan" type="button">Kirim Pesan</button>
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
        $(document).ready(function() {
            let id = $('#myId').val();
            const table = $('#dtable-daftar-pembelian').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/backlink/action?action=dataTransactionPembeli&id=' + id,
                    type: 'GET',
                    dataSrc: 'data',
                    data: function(d) {
                        d.tanggal = $('#tanggal_filter').val();
                        d.domain = $('#domain_filter').val();
                        d.harga = $('#harga_filter').val();
                        d.status = $('#status_filter').val();
                    }
                },
                columns: [
                    {
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        orderable: false
                    },
                    {
                        data: 'blog_url',
                        render: function(data) {
                            return data ? `<a href="${data}">${data}</a>` : '-';
                        }
                    },
                    {
                        data: 'link_url',
                        render: function(data, type, row) {
                            return row.status_admin !== 'Approve' ? '' : `<a href="${data}">${data}</a>`;
                        }
                    },
                    {
                        data: 'price',
                        render: function(data) {
                            return data ? `Rp${data}` : '-';
                        },
                        type: 'num'
                    },
                    {
                        render: function(data, type, row) {
                            return row.status_admin === "Reject" ? "Revisions" :
                                   row.status_penjual === "Submitted" && row.status_admin === "Approve" && row.status_pembeli !== "Done" ? "Submitted" :
                                   row.status_penjual === "Submitted" && row.status_admin === "Need Action" ? "Need Action" :
                                   row.status_pembeli === "Done" ? "Success" :
                                   row.status_penjual ? row.status_penjual : "-";
                        }
                    },
                    {
                        render: function(data, type, row) {
                            return row.status_penjual === "Approved" || row.status_penjual === "Need Action" ? '-' :
                                   row.status_pembeli && (row.status_penjual !== "Approved" || row.status_penjual !== "Need Action") ? `${row.status_pembeli}` : '-';
                        }
                    },
                    {
                        data: 'notes',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'invoiceid',
                        render: function(data) {
                            return `<a href="https://portal.qwords.com/viewinvoice.php?id=${data}">#${data}</a>`;
                        }
                    },
                    {
                        render: function(data, type, row) {
                            let buttons = '';
                            if (row.status_admin === "Approve") {
                                buttons += `<button class="btn btn-primary" onclick="modalApprove('${row.uuid ? row.uuid : row.id}')" style="border-radius:8px; margin:5px;" data-toggle="modal" data-target="#modalApprove">Approve</button>`;
                            }
                            if (row.status_pembeli !== 'Done') {
                                buttons += `<button class="btn btn-danger" onclick="modalKirimPesan('${row.uuid ? row.uuid : row.id}', '${row.status_penjual}', '${row.status_pembeli}', '${row.blog_url}')" style="border-radius:8px; padding: 8px 10px; margin:5px;" data-toggle="modal" data-target="#modalPesan">Chat Penjual</button>`;
                            }
                            return buttons;
                        },
                        orderable: false
                    }
                ],
                order: [
                    [1, 'asc']
                ]
            });

            // Function to apply filters
            window.applySalesFilter = function() {
                table.ajax.reload();
            };

            // Initialize Select2 for any filter fields if needed
            $("#category_filter").select2({
                placeholder: "Pilih Kategori",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script>
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

        function modalEditLinkUrl(id_blog) {
            let loader = $("#modal_loader_backlink");
            let content = $("#content-modal");
            let editElement = $("#edit_link_url");

            editElement.attr('onclick', 'editLinkUrl("' + id_blog + '")');
            content.text("Sedang mengambil data, mohon tunggu...");
            //loader.css("display", "flex");

            $.get('/modules/addons/sellBacklink/ajax/detailTransaction.php', {
                    id: id_blog
                })
                .done(function(data) {
                    if (data.code === 'success') {
                        Toast.fire({
                            icon: 'success',
                            text: data.message
                        });
                        $("#link_url_edit").val(data.link_url);
                        // loader.css("display", "none");
                    } else {
                        Toast.fire({
                            icon: 'error',
                            text: data.message
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    loader.css("display", "none");
                    console.error('Fetch error:', errorThrown);
                });
        }

        function modalApproveTransaksi(id_blog) {
            $("#approve_transaksi").attr('onclick', 'approveTransaksi("' + id_blog + '")');
        }

        function modalRejectTransakasi(id_blog) {
            $("#reject_transaksi").attr('onclick', 'rejectTransaksi("' + id_blog + '")');
        }

        function approveTransaksi(id_blog) {
            let loader = $("#modal_loader_backlink");
            let content = $("#content-modal");

            content.text("Sedang mengupdate data, mohon tunggu...");
            loader.css("display", "flex");

            $.get('/backlink/action?action=editStatusPenjual', {
                    id: id_blog,
                    status: 'Approved'
                })
                .done(function(data) {
                    if (data.code === 'success') {
                        Toast.fire({
                            icon: 'success',
                            text: data.message
                        });
                        location.reload();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            text: data.message
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    loader.css("display", "none");
                    console.error('Fetch error:', errorThrown);
                });
        }

        function rejectTransaksi(id_blog) {
            let loader = $("#modal_loader_backlink");
            let content = $("#content-modal");
            let notes = $("#notes_reject").val();

            if (!notes) {
                alert(`Alasan Reject Anda tidak boleh kosong!.`);
                return;
            }

            content.text("Sedang mengupdate data, mohon tunggu...");
            loader.css("display", "flex");

            $.get('/backlink/action?action=editStatusPenjual', {
                    id: id_blog,
                    status: 'Rejected',
                    notes: notes
                })
                .done(function(data) {
                    if (data.code === 'success') {
                        Toast.fire({
                            icon: 'success',
                            text: data.message
                        });
                        location.reload();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            text: data.message
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    loader.css("display", "none");
                    console.error('Fetch error:', errorThrown);
                });
        }

        function editLinkUrl(id_blog) {
            let link_url = $("#link_url_edit");
            let emailAdmin = $("#emailAdmin").val();
            let loader = $("#modal_loader_backlink");
            let content = $("#content-modal");

            if (!link_url.val()) {
                link_url.addClass('is-invalid');
                return;
            } else {
                link_url.removeClass('is-invalid');
            }

            content.text("Sedang mengedit link url Anda, mohon tunggu...");
            //loader.css("display", "flex");

            $.get('/backlink/action?action=editLinkUrl&id=' + id_blog, {
                    link_url: link_url.val(),
                    emailAdmin: emailAdmin
                })
                .done(function(data) {
                    Toast.fire({
                        icon: 'success',
                        text: 'Link url berhasil diubah.'
                    });
                    location.reload();
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    loader.css("display", "none");
                    console.error('Fetch error:', errorThrown);
                });
        }

        function modalKirimPesan(transaksi_id, status_penjual, status_pembeli, url) {
            $("#kirim_pesan").attr('onclick', 'kirimPesan("' + transaksi_id + '")');
            $("#status_penjual").text('Status Penjual: ' + status_penjual);
            $("#status_pembeli").text('Status Pembeli: ' + status_pembeli);
            $("#url_blog").text(url);

            $.get('/backlink/action?action=getPesan', {
                    transaksi_id: transaksi_id,
                    userid: id
                })
                .done(function(data) {
                    if (data.code === 'success') {
                        Toast.fire({
                            icon: 'success',
                            text: data.message
                        });
                        $("#nama_penjual").text("Penjual: " + data.penjual);
                        $("#nama_pembeli").text("Pembeli: " + data.pembeli);
                        renderMessages(data.data_chat);
                    } else {
                        Toast.fire({
                            icon: 'error',
                            text: data.message
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    Toast.fire({
                        icon: 'error',
                        text: 'Pesan gagal dikirim.'
                    });
                    console.error('Fetch error:', errorThrown);
                });
        }

        function renderMessages(messages) {
            const messageContainer = $("#message_container");
            messageContainer.empty();

            messages.forEach((message, index) => {
                const messageDiv = $("<div>").addClass(`chatId-${index + 1} chat-container`);
                const nameDiv = $("<div>").html(
                    `<h4 style="font-weight:bold" class="${message.role === 'Penjual' ? 'chat-penjual' : 'chat-pembeli'}">${message.nama.charAt(0)}</h4>`
                    );
                const messageContentDiv = $("<div>").addClass(`message-${index + 1}`).css("margin-left", "20px")
                    .html(`
                    <p><span style="font-weight:bold">${message.nama} (${message.role}) -</span> ${message.created_at}</p>
                    <p style="margin-bottom:0px">${message.message}</p>
                `);

                messageDiv.append(nameDiv).append(messageContentDiv);
                messageContainer.append(messageDiv);
            });

            if (messages.length === 0) {
                messageContainer.html(`<p>Tidak ada komentar</p>`);
            }
        }

        function kirimPesan(transaksi_id) {
            let message = $("#message");
            let id = $("#myId").val();
            let email = $("#myEmail").val();
            
            if (!message.val()) {
                message.addClass('is-invalid');
                message.focus(); // Focus on the message input
                return;
            } else {
                message.removeClass('is-invalid');
            }

            let content = $("#content-modal");
            content.text("Sedang mengirim pesan, mohon tunggu...");

            $.get('/backlink/action?action=kirimPesan', {
                    message: message.val(),
                    transaksi_id: transaksi_id,
                    userid: id,
                    email: email
                })
                .done(function(data) {
                    if (data.code === 'success') {
                        Toast.fire({
                            icon: 'success',
                            text: data.message
                        });
                        location.reload();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            text: data.message
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Fetch error:', errorThrown);
                    Toast.fire({
                        icon: 'error',
                        text: 'Pesan gagal dikirim.'
                    });
                });
        }
    </script>

    <script>
        function modalApprove(id_blog) {
            $("#approve_transaction").attr('onclick', 'approveBacklink("' + id_blog + '")');
        }

        function approveBacklink(id_blog) {
            //let loader = $("#modal_loader_backlink");
            //let content = $("#content-modal");
            //content.text("Sedang mengganti status backlink, mohon tunggu...");
            //loader.css("display", "flex");
            let email = $("#myEmail").val();

            $.get('/backlink/action?action=statusTransaction', {
                id: id_blog,
                status: "Approve",
                type: "pembeli",
                email: email
            })
            .done(function(data) {
                if (data.code === 'success') {
                    Toast.fire({
                        icon: 'success',
                        text: data.message
                    });
                    location.reload();
                } else {
                    Toast.fire({
                        icon: 'error',
                        text: data.message
                    });
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                //loader.css("display", "none");
                console.error('Fetch error:', errorThrown);
            });
        }

        function modalLaporAdmin(invoiceid, transaksi_id) {
            $("#lapor_transaction").attr('onclick', 'laporAdmin(' + invoiceid + ',"' + transaksi_id + '")');
        }

        function laporAdmin(invoiceid, transaksi_id) {
            let notes = $("#notes").val();
            let loader = $("#modal_loader_backlink");
            let content = $("#content-modal");

            if (!notes) {
                alert(`Tolong masukkan catatan Anda.`);
                return;
            }

            content.text("Sedang melaporkan backlink, mohon tunggu...");
            loader.css("display", "flex");

            $.get('/modules/addons/sellBacklink/ajax/laporAdmin.php', {
                notes: notes,
                transaksi_id: transaksi_id,
                userid: id,
                email: email
            })
            .done(function(data) {
                if (data.code === 'success') {
                    Toast.fire({
                        icon: 'success',
                        text: data.message
                    });
                    window.location.href = `https://portal.qwords.com/viewticket.php?tid=${data.tid}&c=${data.c}`;
                } else {
                    Toast.fire({
                        icon: 'error',
                        text: data.message
                    });
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                //loader.css("display", "none");
                console.error('Fetch error:', errorThrown);
            });
        }
    </script>
@endsection
