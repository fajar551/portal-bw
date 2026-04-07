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

                <input type="hidden" value="{{ $myid }}" id="myId" />
                <input type="hidden" value="{{ $clientsdetails['uuid'] }}" id="uuid" />
                <input type="hidden" value="{{ $emailAdmin }}" id="emailAdmin" />

                <div class="col-md-12">
                    <div class="row mb-3 mt-3">
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/blog_saya') . '?page=index' }}"
                                class="btn btn-lg btn-outline-success w-100 active"
                                style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Blog Saya
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/pesan_backlink') . '?page=pesan_backlink' }}"
                                class="btn btn-lg btn-outline-success w-100"
                                style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Pesan Backlink
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/daftar_penjualan') . '?page=daftar_penjualan' }}"
                                class="btn btn-lg btn-outline-success w-100"
                                style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Daftar Penjualan
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/daftar_pembelian') . '?page=daftar_pembelian' }}"
                                class="btn btn-lg btn-outline-success w-100"
                                style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Daftar Pembelian
                            </a>
                        </div>
                        <div class="col-12 col-md-2 mt-1">
                            <a href="{{ url('/backlink/pencairan_dana') . '?page=pencairan_dana' }}"
                                class="btn btn-lg btn-outline-success w-100"
                                style="font-size: 0.8rem; padding: 1rem 2rem;">
                                Pencairan Dana
                            </a>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end w-100 mt-3">
                    <p class="mb-0 mr-2 align-self-center" style="font-size: 14px; width: fit-content;">
                        Filter Data:
                    </p>
                    <button class="btn btn-success" data-toggle="modal" data-target="#filterBlog">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <h2 class="font-weight-bold mb-0">Semua Blog Saya</h2>
                        <div>
                            @if ($isFreeze ?? false)
                                <button disabled class="btn btn-primary font-weight-bold d-flex align-items-center"
                                    data-toggle="modal" data-target="#createBlog">
                                    <i class="fa fa-plus mr-2"></i> Daftar Blog
                                </button>
                            @else
                                <button class="btn btn-primary font-weight-bold d-flex align-items-center"
                                    data-toggle="modal" data-target="#createBlog">
                                    <i class="fa fa-plus mr-2"></i> Daftar Blog
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Blog Backlink Saya</h5>
                            <table id="dtable-my-blog" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="text-center align-middle">No</th>
                                        <th rowspan="2" class="text-center align-middle">Nama Blog</th>
                                        <th rowspan="2" class="text-center align-middle">URL Blog</th>
                                        <th rowspan="2" class="text-center align-middle">Harga</th>
                                        <th rowspan="2" class="text-center align-middle">Kategori</th>
                                        <th rowspan="2" class="text-center align-middle">Bahasa</th>
                                        <th colspan="3" class="text-center">Metrik SEO</th>
                                        <th rowspan="2" class="text-center align-middle">Status</th>
                                        <th rowspan="2" class="text-center align-middle">Aksi</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">DA<sup><button id="button_da"
                                                    class="btn btn-info-qw">?</button></sup></th>
                                        <th class="text-center">PA<sup><button id="button_pa"
                                                    class="btn btn-info-qw">?</button></sup></th>
                                        <th class="text-center">Traffic<sup><button id="button_traffic"
                                                    class="btn btn-info-qw">?</button></sup></th>
                                    </tr>
                                </thead>
                                <tbody id="data-my-blog">
                                    <!-- Your data rows go here -->
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
        <img class="load" src="{{ $documentroot }}/load.webp" width="40px" height="43px" />
        <h5 class="ml-3 align-self-center" id="content-modal">Sedang mendaftarkan blog Anda, mohon tunggu...</h5>
    </div>
</div> --}}

<div class="modal fade" id="createBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Daftar Blog</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <div class="mb-3">
                        <label for="blog_name">Nama Blog:</label>
                        <input type="text" name="blog_name" id="blog_name" class="form-control"
                            placeholder="Masukkan nama blog" />
                        <div class="invalid-feedback">Nama blog harus diisi.</div>
                    </div>
                    <div class="mb-3">
                        <label for="blog_url">Url Blog:</label>
                        <input type="text" name="blog_url" id="blog_url" class="form-control"
                            placeholder="Masukkan url blog" />
                        <div class="invalid-feedback">URL blog harus diisi.</div>
                    </div>
                    <div class="mb-3">
                        <label for="category">Kategori:</label>
                        <select class="form-control" id="category" name="category[]"
                            multiple="multiple"></select>
                        <div class="invalid-feedback">Kategori harus dipilih.</div>
                    </div>
                    <div class="mb-3">
                        <label for="language">Bahasa:</label>
                        <select id="language" class="form-select form-control" required>
                            <option value="" selected disabled>Pilih Bahasa</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="English">English</option>
                        </select>
                        <div class="invalid-feedback">Bahasa harus dipilih.</div>
                    </div>
                    <div class="mb-3">
                        <label for="price">Harga: <button id="button_price"
                                class="btn btn-info-qw">?</button></label>
                        <input type="number" name="price" min="50000" value="50000" id="price"
                            class="form-control" placeholder="Masukkan harga backlink" />
                        <div class="invalid-feedback">Harga harus diisi dan minimal Rp50.000.</div>
                    </div>
                    <div class="mb-3">
                        <label for="kata_kunci">Kata Kunci:</label>
                        <input type="text" name="kata_kunci" id="kata_kunci" class="form-control"
                            placeholder="Masukkan kata kunci" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button class="btn btn-primary" onclick="validateForm()" type="button">Daftar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Edit Blog</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <div class="mb-3">
                        <label for="blog_name_edit">Nama Blog:</label>
                        <input type="text" name="blog_name" id="blog_name_edit" class="form-control"
                            placeholder="Masukkan nama blog" />
                    </div>
                    <div class="mb-3">
                        <label for="blog_url_edit">Url Blog:</label>
                        <input type="text" name="blog_url" id="blog_url_edit" class="form-control"
                            placeholder="Masukkan url blog" />
                    </div>
                    <div class="mb-3">
                        <label for="category_edit">Kategori:</label>
                        <select class="form-control" id="category_edit" name="category[]"
                            multiple="multiple"></select>
                    </div>
                    <div class="mb-3">
                        <label for="language_edit">Bahasa:</label>
                        <select id="language_edit" class="form-select form-control">
                            <option value="" selected disabled>Pilih Bahasa</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="English">English</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price_edit">Harga: <button id="button_price"
                                class="btn btn-info-qw">?</button></label>
                        <input type="number" name="price" id="price_edit" min="50000" value="50000"
                            class="form-control" placeholder="Masukkan harga backlink" />
                    </div>
                    <div class="mb-3">
                        <label for="kata_kunci_edit">Kata Kunci:</label>
                        <input type="text" name="kata_kunci_edit" id="kata_kunci_edit" class="form-control"
                            placeholder="Masukkan kata kunci" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="edit_blog" onclick="editBlog()" type="submit"
                    data-dismiss="modal">Edit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Delete Blog</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda ingin menghapus blog ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="deleteBlog()" id="delete_blog" type="submit"
                    data-dismiss="modal">Yes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resubmitBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Resubmit Blog</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda ingin resubmit blog ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="resubmitBlog()" id="resubmit_blog" type="submit"
                    data-dismiss="modal">Yes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="platformFee" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Informasi Platfom Fee</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Setiap penjualan backlink Anda akan ada potongan biaya platform sebesar 10%</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="informasiPlatformFee()" type="submit"
                    data-dismiss="modal">Yes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="informasiBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Informasi Blog</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Blog Anda akan di verifikasi oleh Admin maksimal 3 hari kerja, kami akan memberitahu Anda melalui
                    email ketika domain sudah siap di pesan</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="informasiBlog()" type="submit"
                    data-dismiss="modal">Yes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filterBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-weight-bold">Filter Backlink</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <input type="hidden" value="{$admin_id}" id="userid" />
                    <div class="mb-3">
                        <p>Kategori (Anda dapat memilih multiple):</p>
                        <select class="form-control" id="category_filter" name="category[]"
                            multiple="multiple"></select>
                    </div>
                    <div id="ranking_element" class="mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="flex-fill mr-2">
                                <p>DA<sup><button id="button_da_filter" class="btn btn-info-qw">?</button></sup>:</p>
                                <input type="number" name="ranking_da" id="ranking_da_filter" class="form-control"
                                    placeholder="Masukkan data DA" />
                            </div>
                            <div class="flex-fill mx-2">
                                <p>PA<sup><button id="button_pa_filter" class="btn btn-info-qw">?</button></sup>:</p>
                                <input type="number" name="ranking_pa" id="ranking_pa_filter" class="form-control"
                                    placeholder="Masukkan data PA" />
                            </div>
                            <div class="flex-fill ml-2">
                                <p>Traffic<sup><button id="button_traffic_filter"
                                            class="btn btn-info-qw">?</button></sup>:</p>
                                <input type="number" name="traffic" id="traffic_filter" class="form-control"
                                    placeholder="Masukkan data traffic" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="flex-fill mr-2">
                                <p>Harga Minimal:</p>
                                <input type="number" name="harga_minimal" id="harga_minimal_filter" class="form-control"
                                    placeholder="Masukkan harga minimal" />
                            </div>
                            <div class="flex-fill ml-2">
                                <p>Harga Maksimal:</p>
                                <input type="number" name="harga_maksimal" id="harga_maksimal_filter" class="form-control"
                                    placeholder="Masukkan harga maksimal" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p>Kata Kunci:</p>
                        <input type="text" name="kata_kunci" id="kata_kunci_filter" class="form-control"
                            placeholder="Cari berdasarkan Kata Kunci" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button class="btn btn-primary" id="pesan_blog" onclick="simpanFilter()" type="submit"
                    data-dismiss="modal">Simpan Filter</button>
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

    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <script>
        tippy('#button_da', {
            content: 'Domain Authority adalah metrik dari tool SEO Moz untuk mengukur seberapa bagus nilai domain untuk keperluan SEO.',
        });

        tippy('#button_pa', {
            content: 'Page Authority adalah metrik dari tool SEO Moz untuk mengukur seberapa bagus nilai halaman untuk keperluan SEO.',
        });

        tippy('#button_traffic', {
            content: 'Estimasi traffic bulanan yang diambil dari Similarweb.',
        });

        tippy('#button_price', {
            content: 'Harga jual backlink minimal Rp50.000 dan terdapat potongan biaya platform sebesar 10%',
        });

        tippy('#button_price_edit', {
            content: 'Harga jual backlink minimal Rp50.000 dan terdapat potongan biaya platform sebesar 10%',
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

        let urlParamBlog = new URLSearchParams(window.location.search);
        let backlink = urlParamBlog.get("backlink");

        if (backlink) {
            $("#blog_url").val(backlink);
            $("#createBlog").modal("show");
        }

        let id = $("#myId").val();
        let uuid = $("#uuid").val();

        $.get("/backlink/action?action=getDataCategory", function(data) {
            if (data.code === "error") {
                alert("Error: " + data.message);
                return;
            }

            let selectElement = $("#category");
            let selectElement2 = $("#category_edit");
            let selectElement3 = $("#category_filter");

            data.forEach((val) => {
                let option = $("<option>").val(val.category_name).text(val.category_name);
                selectElement.append(option);

                let option2 = $("<option>").val(val.category_name).text(val.category_name);
                selectElement2.append(option2);

                let option3 = $("<option>").val(val.category_name).text(val.category_name);
                selectElement3.append(option3);
            });
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Request failed: " + textStatus);
            console.log("Error: " + errorThrown);
            console.log("Response Text: " + jqXHR.responseText);
        });

        function validateForm() {
            let isValid = true;
            let requiredFields = ['#blog_name', '#blog_url', '#category', '#language', '#price', '#kata_kunci'];

            requiredFields.forEach(function(field) {
                if (!$(field).val() || ($(field).attr('id') === 'price' && $(field).val() < 50000)) {
                    $(field).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(field).removeClass('is-invalid');
                }
            });

            if (isValid) {
                $("#createBlog").modal("hide");
                $("#platformFee").modal("show");
            }
        }

        function createBlog() {
            $("#createBlog").modal("hide");
            $("#platformFee").modal("show");
        }

        function informasiPlatformFee() {
            let blog_name = $("#blog_name").val();
            let blog_url = $("#blog_url").val();
            let category = $("#category").val();
            let language = $("#language").val();
            let price = $("#price").val();
            let kata_kunci = $("#kata_kunci").val();
            let emailAdmin = $("#emailAdmin").val();

            $.get("/backlink/action?action=createBlog", {
                blog_name: blog_name,
                blog_url: blog_url,
                category: category,
                language: language,
                price: price,
                kata_kunci: kata_kunci,
                userid: id,
                emailAdmin: emailAdmin
            }).done(function(data) {
                $("#platformFee").modal("hide");
                $("#informasiBlog").modal("show");
                // loader.css("display", "none");
            }).fail(function(error) {
                $("#platformFee").modal("hide");
                $("#informasiBlog").modal("hide");
                // loader.css("display", "none");
                console.error("Fetch error:", error);
            });
        }

        function modalEditBlog(id_blog) {
            let loader = $("#modal_loader_backlink");
            let content = $("#content-modal");
            let editElement = $("#edit_blog");

            editElement.attr("onclick", 'editBlog("' + id_blog + '")');
            content.text("Sedang mengambil data, mohon tunggu...");
            loader.css("display", "flex");

            $.get("/backlink/action?action=detailBlog", {
                    id: id_blog
                })
                .done(function(data) {
                    $("#blog_name_edit").val(data.blog_name);
                    $("#blog_url_edit").val(data.blog_url);
                    $("#category_edit").val(data.category.split(",")).trigger("change");
                    $("#language_edit").val(data.language);
                    $("#price_edit").val(data.price);
                    $("#kata_kunci_edit").val(data.kata_kunci);
                    loader.css("display", "none");
                })
                .fail(function(error) {
                    loader.css("display", "none");
                    console.error("Fetch error:", error);
                });
        }

        function editBlog(id_blog) {
            let blog_name_edit = $("#blog_name_edit").val();
            let blog_url_edit = $("#blog_url_edit").val();
            let category_edit = $("#category_edit").val();
            let language_edit = $("#language_edit").val();
            let price_edit = $("#price_edit").val();
            let kata_kunci_edit = $("#kata_kunci_edit").val();
            // let emailAdmin = $("#emailAdmin").val();

            $.get("/backlink/action?action=editBlog", {
                blog_name: blog_name_edit,
                blog_url: blog_url_edit,
                category: category_edit,
                language: language_edit,
                price: price_edit,
                userid: id,
                id: id_blog,
                kata_kunci: kata_kunci_edit
            }).done(function(data) {
                $("#editBlog").modal("hide");
                Toast.fire({
                    icon: 'success',
                    text: 'Blog berhasil diubah'
                });
                location.reload();
            }).fail(function(error) {
                $("#editBlog").modal("hide");
                console.error("Fetch error:", error);
            });
        }

        function informasiBlog() {
            $("#informasiBlog").modal("hide");
            Toast.fire({
                icon: 'success',
                text: 'Blog berhasil dibuat'
            });
            window.location.href = "{{ url('/backlink/blog_saya') . '?page=index' }}";
        }

        function modalDeleteBlog(id_blog) {
            $("#delete_blog").attr("onclick", 'deleteBlog("' + id_blog + '")');
        }

        function deleteBlog(id_blog) {
            let loader = $("#modal_loader_backlink");
            let content = $("#content-modal");
            content.text("Sedang menghapus blog Anda, mohon tunggu...");
            loader.css("display", "flex");

            $.get("/backlink/action?action=deleteBlog", {
                    id: id_blog
                })
                .done(function(data) {
                    Toast.fire({
                        icon: 'success',
                        text: 'Blog berhasil dihapus'
                    });
                    location.reload();
                })
                .fail(function(error) {
                    Toast.fire({
                        icon: 'error',
                        text: 'Blog gagal dihapus'
                    });
                    loader.css("display", "none");
                    console.error("Fetch error:", error);
                });
        }

        function modalResubmit(id_blog) {
            $("#resubmit_blog").attr("onclick", 'resubmitBlog("' + id_blog + '")');
        }

        function resubmitBlog(id_blog) {
            let emailAdmin = $("#emailAdmin").val();
            // let loader = $("#modal_loader_backlink");
            // let content = $("#content-modal");
            
            // content.text("Sedang mendaftarkan kembali blog Anda, mohon tunggu...");
            // loader.css("display", "flex");

            $.get("/backlink/action?action=resubmitBlog", {
                id: id_blog,
                emailAdmin: emailAdmin
            }).done(function(data) {
                $("#resubmitBlog").modal("hide");   
                Toast.fire({
                    icon: 'success',
                    text: 'Blog berhasil diresubmit'
                });
                location.reload();
            }).fail(function(error) {
                $("#resubmitBlog").modal("hide");
                Toast.fire({
                    icon: 'error',
                    text: 'Blog gagal diresubmit'
                });
                console.error("Fetch error:", error);
            });
        }

    </script>

    <script type="text/javascript">
      $(document).ready(function () {
        const table = $('#dtable-my-blog').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
            url: '/backlink/action?action=dataMyBlog&id=' + uuid,
            type: 'GET',
            dataSrc: 'data',
            data: function (d) {
              d.category = $('#category_filter').val();
              d.ranking_da = $('#ranking_da_filter').val();
              d.ranking_pa = $('#ranking_pa_filter').val();
              d.traffic = $('#traffic_filter').val();
              d.harga_minimal = $('#harga_minimal_filter').val();
              d.harga_maksimal = $('#harga_maksimal_filter').val();
              d.kata_kunci = $('#kata_kunci_filter').val();
            }
          },
          columns: [
            {
              render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
              },
              orderable: false
            },
            {
              data: 'blog_name'
            },
            {
              data: 'blog_url',
              render: function(data, type, row) {
                return data ? `<a href="${data}">${data}</a>` : '-';
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
              data: 'category',
              render: function(data) {
                if (!data) return '-';
                return `<div class="d-flex flex-wrap">${data.split(',').map(category => `<span class="badge badge-secondary m-1">${category.trim()}</span>`).join('')}</div>`;
              }
            },
            {
              data: 'language'
            },
            {
              data: 'ranking_da',
              type: 'num'
            },
            {
              data: 'ranking_pa',
              type: 'num'
            },
            {
              data: 'traffic',
              type: 'num'
            },
            {
              data: 'status'
            },
            {
              render: function (data, type, row) {
                let buttons = '';
                
                if (row.status === "Reject") {
                    buttons += `
                        <button type="button" class="btn btn-warning mb-1" onclick="modalResubmit('${row.uuid ? row.uuid : row.id}')" data-toggle="modal" data-target="#resubmitBlog">
                            Resubmit Blog
                        </button>`;
                }
                
                buttons += `
                    <button type="button" class="btn btn-primary mb-1" onclick="modalEditBlog('${row.uuid ? row.uuid : row.id}')" data-toggle="modal" data-target="#editBlog">
                        Edit
                    </button>
                    <button type="button" class="btn btn-danger mb-1" onclick="modalDeleteBlog('${row.uuid ? row.uuid : row.id}')" data-toggle="modal" data-target="#deleteBlog">
                        Hapus
                    </button>`;
                
                return buttons;
              },
              orderable: false
            }
          ],
          order: [[1, 'asc']]
        });

        // Function to apply filters
        window.simpanFilter = function() {
          table.ajax.reload();
        };

        // Initialize Select2 for category fields
        $("#category").select2({
            placeholder: "Pilih Kategori",
            allowClear: true,
            width: '100%'
        });

        $("#category_edit").select2({
            placeholder: "Pilih Kategori",
            allowClear: true,
            width: '100%'
        });

        $("#category_filter").select2({
            placeholder: "Pilih Kategori",
            allowClear: true,
            width: '100%'
        });
      });
    </script>
@endsection
