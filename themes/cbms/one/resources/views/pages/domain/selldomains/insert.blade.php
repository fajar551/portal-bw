@extends('layouts.clientbase')

@section('title')
    <title>Insert Sell & Rent Domain Page</title>
@endsection

@section('content')
    <div class="page-content" id="lelang-domain">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="mb-0">Public Insert Sell / Rent Domain</h3>
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

                <div class="col-md-12 mt-3">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="POST" name="myform" id="shell-domain-form"
                                action="{{ route('pages.domain.selldomains.action', ['action' => request()->get('page') == 'insert' ? 'sell' : 'edit']) }}"
                                onsubmit="validateForm(this); return false;">
                                @csrf
                                <input type="hidden" id="input-disable-lelang" name="disabled_lelang" value="0">
                                <div class="form-group">
                                    <label for="domain" class="form-label">Domain:</label>
                                    @if (request()->get('page') == 'insert')
                                        @if (request()->get('isqword'))
                                            <select class="form-control" name="domain" id="domain" required>
                                                @foreach ($qwordsdomains as $val)
                                                    <option value="{{ $val['domainname'] }}">{{ $val['domainname'] }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            @if (request()->get('domain'))
                                                <input class="form-control" type="text" value="{{ request()->get('domain') }}"
                                                    placeholder="example.com" disabled>
                                                <input type="hidden" name="domain" value="{{ request()->get('domain') }}">
                                            @else
                                                <input class="form-control" type="text" name="domain" id="domain"
                                                    value="{{ old('domain', request()->get('frm_domain')) }}"
                                                    placeholder="example.com" required>
                                            @endif
                                            <div id="domain_alerts" class="mt-2">
                                                <div id="domain_available" class="alert alert-danger" style="display:none;">
                                                    <strong>Domain tidak dapat dijual!</strong> Silahkan cari nama domain lain.
                                                </div>
                                                <div id="domain_unavailable" class="alert alert-success" style="display:none;">
                                                    <strong>Domain dapat dijual!</strong>
                                                </div>
                                                <div id="domain_not_found" class="alert alert-danger" style="display:none;">
                                                    <strong>Domain tidak ditemukan!</strong> Silahkan cari nama domain lain.
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <input class="form-control" type="text" value="{{ $domain }}"
                                            placeholder="example.com" disabled>
                                        <input type="hidden" name="domain" value="{{ $domain }}">
                                    @endif
                                </div>

                                @if (request()->get('page') == 'insert')
                                    <div class="form-group d-none">
                                        <label for="type" class="form-label">Tipe Harga:</label>
                                        <select class="form-control" name="type" required>
                                            <option value="FIX_PRICE">Harga Fix</option>
                                            <option value="AUCTION_PRICE">Harga Lelang</option>
                                            <option value="RENT_PRICE" @if (request()->get('type') == 'sewa') selected @endif>
                                                Harga Sewa / Bulan</option>
                                        </select>
                                    </div>

                                    <div class="form-group d-none">
                                        <p>Masukan dalam auto-suggest pencarian?</p>
                                        <input type="checkbox" id="suggest" name="is_suggest" value="true">
                                        <label for="suggest" class="form-label"> Rp 500.000 </label>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label for="pilih_jenis" class="form-label">Pilih Tipe</label>
                                    <select class="form-control" id="pilih_jenis">
                                        <option value="jual">Jual Domain</option>
                                        <option value="sewa" @if (request()->get('type') == 'sewa') selected @endif>Sewa Domain</option>
                                    </select>
                                </div>

                                <div id="section-jual">
                                    <div class="form-group">
                                        <label for="harga_awal" class="form-label">Harga Awal (Apabila anda ingin memasukan domain ini kedalam Lelang):</label>
                                        <div class="d-flex align-items-center">
                                            <label for="harga_awal" class="mr-2">Rp</label>
                                            <input class="form-control w-25" id="harga_awal" name="price_awal" type="number" value=""
                                                @if ($is_disabled_lelang ?? '') disabled @endif>
                                        </div>
                                        <small class="form-text text-muted" @if ($is_disabled_lelang ?? '') style="display:none" @endif>
                                            <label><input type="checkbox" onchange="handleDisable(this)"
                                                    @if ($is_disabled_lelang ?? '') checked @endif> Non aktifkan harga lelang (hanya harga kontan)</label>
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label for="price_kontan" class="form-label">Harga Kontan (Buy it Now Price (Fixed), anda dapat mengeset harga yang anda inginkan lebih tinggi)</label>
                                        <div class="d-flex align-items-center">
                                            <label for="price_kontan" class="mr-2">Rp</label>
                                            <input type="number" class="form-control w-25" id="price_kontan" name="price_kontan" placeholder="250000" min="0" step="any">
                                        </div>
                                        <input type="hidden" class="form-control" id="price_kontan_hidden" name="price_kontan" value="">
                                        <small class="form-text text-muted">Dana yang bisa dicairkan: Rp <span class="font-weight-bold" id="calculated"> - </span></small>
                                    </div>
                                </div>

                                <div id="section-sewa" style="display: none;">
                                    <div class="form-group">
                                        <label for="price_sewa" class="form-label">Harga Sewa:</label>
                                        <input type="text" class="form-control w-25" id="price_sewa" name="price_sewa" type="number" placeholder="250000">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center">
                                    <div class="pr-3">
                                        <a class="btn btn-secondary" href="{{ route('pages.domain.selldomains.index') }}">Kembali</a>
                                    </div>
                                    <div>
                                        <button class="btn btn-primary" id="submit_button" type="submit">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_verification" tabindex="-1" role="dialog"
        aria-labelledby="modalVerificationLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light text-dark">
                    <h5 class="modal-title" id="modalVerificationLabel">Verifikasi Domain</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-4">Pilih salah satu cara untuk memverifikasi domain Anda:</p>
                    <div class="accordion" id="verificationAccordion">
                        <!-- HTML File Verification -->
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link text-primary" type="button" data-toggle="collapse"
                                        data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        HTML File
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                data-parent="#verificationAccordion">
                                <div class="card-body">
                                    <ol class="mb-3">
                                        <li>Download file berikut:
                                            <form method="POST" action="{{ route('pages.domain.selldomains.action') }}">
                                                @csrf
                                                <input type="hidden" name="action" value="download_html">
                                                <input type="hidden" name="domain" value="{{ $domain }}">
                                                <button type="submit" class="btn btn-sm btn-link">
                                                    <i class="fa fa-download"></i> {{ $htmlfile }}
                                                </button>
                                            </form>
                                        </li>
                                        <li>Rename file menjadi: <br>
                                            <code style="color:black;"><strong>{{ $htmlfile }}</strong></code>
                                            <button class="btn btn-sm btn-primary ml-2" onclick="copyToClipboard('{{ $htmlfile }}')"><i class="fa fa-copy"></i></button>
                                        </li>
                                        <li>Upload ke: <code>https://{{ $domain }}/</code></li>
                                    </ol>
                                    <form method="POST" action="{{ route('pages.domain.selldomains.action') }}">
                                        @csrf
                                        <input type="hidden" name="action" value="verify">
                                        <input type="hidden" name="domain" value="{{ $domain }}" />
                                        <input type="hidden" name="type" value="html" />
                                        <button type="submit" class="btn btn-primary float-right mb-3">Verifikasi HTML</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Domain Name Provider Verification -->
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="btn btn-link text-primary collapsed" type="button"
                                        data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false"
                                        aria-controls="collapseTwo">
                                        Domain Name Provider
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                data-parent="#verificationAccordion">
                                <div class="card-body">
                                    <ol class="mb-3">
                                        <li>Login ke Penyedia/Registrar dimana domain didaftarkan.</li>
                                        <li>Salin TXT record di bawah ini ke DNS Config domain:</li>
                                        <input class="form-control my-2" readonly value="{{ $nameserver_uniq }}">
                                        <li>Klik <strong>Verifikasi</strong> setelah DNS resolve.</li>
                                    </ol>
                                    <p class="text-muted">Catatan: Perubahan DNS memerlukan waktu hingga 1-2 hari. Jika verifikasi gagal, coba kembali setelah beberapa waktu.</p>
                                    <form method="POST" action="{{ route('pages.domain.selldomains.action', ['action' => 'verify']) }}">
                                        @csrf
                                        <input type="hidden" name="domain" value="{{ $domain }}" />
                                        <input type="hidden" name="type" value="dns" />
                                        <button type="submit" class="btn btn-primary float-right mb-3">Verifikasi DNS</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Nameserver Verification -->
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="btn btn-link text-primary collapsed" type="button"
                                        data-toggle="collapse" data-target="#collapseThree" aria-expanded="false"
                                        aria-controls="collapseThree">
                                        Nameserver
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                                data-parent="#verificationAccordion">
                                <div class="card-body">
                                    <ol class="mb-3">
                                        <li>Login ke Penyedia/Registrar domain.</li>
                                        <li>Salin Nameserver 1 berikut ke konfigurasi Nameserver:</li>
                                        <input class="form-control my-2" readonly value="ns1.qwords.io">
                                        <li>Salin Nameserver 2 berikut ke konfigurasi Nameserver:</li>
                                        <input class="form-control my-2" readonly value="ns2.qwords.io">
                                        <li>Klik <strong>Verifikasi</strong> setelah set Nameserver.</li>
                                    </ol>
                                    <p class="text-muted">Catatan: Perubahan Nameserver biasanya memerlukan waktu untuk terpasang sempurna. Jika sistem kami tidak dapat langsung berhasil memverifikasi, silakan menunggu 1-2 Hari kemudian mencoba Verifikasi kembali</p>
                                    <label class="text-muted">Seller Note (optional)</label>
                                    <div class="form-group">
                                        <div id="editor-container" class="form-control" style="color:black; margin-bottom:10px;"></div>
                                    </div>
                                    
                                    <p class="text-muted">Catatan: Perubahan Nameserver memerlukan waktu hingga 1-2 hari. Jika verifikasi gagal, coba kembali setelah beberapa waktu.</p>
                                    <form method="POST" id="verif-nameserver" action="{{ route('pages.domain.selldomains.action', ['action' => 'verify']) }}">
                                        @csrf
                                        <input type="hidden" name="domain" value="{{ $domain }}" />
                                        <input type="hidden" name="type" value="nameserver" />
                                        <input type="hidden" id="seller_note" name="seller_note" value="">
                                        <button type="submit" class="btn btn-primary float-right mb-3">Verifikasi Nameserver</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End of accordion -->
                </div> <!-- End of modal-body -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDomainAge" tabindex="-1" role="dialog" aria-labelledby="confirmDomainAgeLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="confirmDomainAgeLabel">Konfirmasi Domain</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        Pastikan anda bersedia melakukan penyerahan/akuisisi akun panel yang berisi domain ke pembeli domain
                        karena domain belum berumur 60 hari.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="location.reload()">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitData()">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div class="modal fade" id="modal_loader_selldomain" tabindex="-1" role="dialog"
        aria-labelledby="modalLoaderLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <h5 class="ml-3 mb-0" id="content-modal">Domain sedang diperiksa, mohon tunggu...</h5>
                </div>
            </div>
        </div>
    </div>
    <!-- Loader -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <script src="https://cdn.ckeditor.com/ckeditor5/35.0.1/classic/ckeditor.js"></script>

    <script>
        $(document).ready(function() {
            ClassicEditor
                .create(document.querySelector('#editor-container'))
                .then(editor => {
                    window.editor = editor;

                    $('#verif-nameserver').on('submit', function(e) {
                        var sellerNoteContent = editor.getData();
                        $('#seller_note').val(sellerNoteContent);
                    });
                })
                .catch(error => {
                    console.error('There was a problem initializing the editor:', error);
                });

            $('#domain').on('input', debounce(handleInputChange, 1000));
            $('#price_kontan').on('input', debounce(getCairkanDana, 1000));
            console.log('Event listener attached to #domain');

            $('#pilih_jenis').on('change', handleJenisChange);
            
            handleJenisChange();
            $('#modal_loader_selldomain').modal('hide');

        });
        // Initialize Toast
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

        let section = '{{ $section }}';
        if (section === 'modal') {
            $('#modal_verification').modal('show');
        }

        function submitData() {
            startLoader($('#submit_button'));
            $('#shell-domain-form')[0].submit();
        }

        async function getCairkanDana(clientid) {
            const price = $('#price_kontan').val();

            // Melakukan fetch dengan jQuery
            $.ajax({
                url: `{{ route('pages.domain.selldomains.action', ['action' => 'calculate_price']) }}`,
                method: 'POST',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: JSON.stringify({ 
                    price: price,
                }),
                success: function(data) {
                    if (data.error) {
                        Toast.fire({
                            icon: 'error',
                            title: data.error
                        });
                    } else {
                        const formattedWithdrawal = formatNumber(data.withdraw_no_fee);
                        $('#calculated').text(formattedWithdrawal);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error in response:', xhr.responseText);
                    Toast.fire({
                        icon: 'error',
                        title: 'An unexpected error occurred.'
                    });
                }
            });
        }

        function formatNumber(number) {
            const parts = number.toString().split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            return parts.join('.');
        }

        function handleDisable(el) {
            if (el.checked) {
                $('#harga_awal').prop('disabled', true);
            } else {
                $('#harga_awal').prop('disabled', false);
            }
        }

        function debounce(func, delay) {
            let timeoutId;

            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeoutId);
                timeoutId = setTimeout(function() {
                    func.apply(context, args);
                }, delay);
            };
        }

        // Function to validate form
        async function validateForm(form) {
            try {
                // Prevent default form submission
                event.preventDefault();
                
                var domain = $("form[name='myform'] input[name='domain']").val();
                var price_kontan = $("form[name='myform'] input[name='price_kontan']").val();
                var price_sewa = $("form[name='myform'] input[name='price_sewa']").val();
                var pilih_jenis = $('#pilih_jenis').val();

                console.log('domain: ' + domain);
                console.log('price_kontan: ' + price_kontan);
                console.log('price_sewa: ' + price_sewa);
                console.log('pilih_jenis: ' + pilih_jenis);

                if (domain == '') {
                    Toast.fire({
                        icon: 'error',
                        title: 'Domain tidak boleh kosong'
                    });
                    return false;
                }

                if (pilih_jenis == 'jual' && price_kontan < 250000) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Harga kontan minimal Rp250.000'
                    });
                    return false;
                }

                if (pilih_jenis == 'sewa' && price_sewa < 250000) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Harga Sewa minimal Rp250.000'
                    });
                    return false;
                }

                if (/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/.test(domain)) {
                    startLoader($('#submit_button'));
                    const url = `{{ route('pages.domain.selldomains.action', ['domain' => '__DOMAIN__', 'action' => 'check_domain_age']) }}`
                        .replace('__DOMAIN__', encodeURIComponent(domain));
                    const formattedUrl = url.replace(/&amp;/g, '&');
                    const response = await fetch(formattedUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            domain: domain
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const text = await response.text();
                    if (text) {
                        const responseData = JSON.parse(text);
                        const data = responseData.results;

                        if (data.result && data.whois_status === 'unavailable') {
                            $('#confirmDomainAge').modal('show');
                            stopLoader($('#submit_button'));
                        } else {
                            form.submit();
                        }
                    } else {
                        throw new Error('Empty response from server');
                    }
                    return false;
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Domain not valid'
                    });
                    return false;
                }
            } catch (err) {
                Toast.fire({
                    icon: 'error',
                    title: err.message
                });
                stopLoader($('#submit_button'));
                return false;
            }
        }

        // Function to start loader
        function startLoader(el) {
            el.attr({
                disabled: true
            }).text('processing..');
            $('#modal_loader_selldomain').modal('show');
        }

        // Function to stop loader
        function stopLoader(el) {
            el.attr({
                disabled: false
            }).text('Submit');
            $('#modal_loader_selldomain').modal('hide');
        }

        function handleInputChange(event) {
            let loader = $("#modal_loader_selldomain");
            let domain_available = $("#domain_available");
            let domain_unavailable = $("#domain_unavailable");
            let domain_not_found = $("#domain_not_found");
            let button = $("#submit_button");
            const domainWhois = event.target.value;

            if (domainWhois) {
                let domainParts = domainWhois.split(".");
                if (!domainParts[0]) {
                    alert("Nama domain Anda kosong.");
                    return;
                }

                if (!domainParts[1]) {
                    alert("Ekstensi Anda kosong.");
                    return;
                }
                
                domain_available.hide();
                domain_unavailable.hide();
                domain_not_found.hide();
                button.prop('disabled', true);

                loader.modal('show');
                
                $.getJSON(`https://www.qwords.com/apis/find.php?domain=${domainWhois}`, function(data) {
                    if (data.status === "available") {
                        loader.modal('hide');
                        domain_available.show();
                        button.prop('disabled', true);
                    } else if (data.status === "unavailable") {
                        loader.modal('hide');
                        domain_unavailable.show();
                        button.prop('disabled', false);
                    } else if (data.status === "" || data.status === null) {
                        loader.modal('hide');
                        domain_not_found.show();
                        button.prop('disabled', false);
                    } else if (data.code === "error") {
                        loader.modal('hide');
                        button.prop('disabled', true);
                        alert(data.msg);
                        return;
                    }
                }).fail(function(error) {
                    loader.modal('hide');
                    console.error('Error:', error);
                    button.prop('disabled', false);
                });
            }
        }

        function debounce(func, delay) {
            let timeoutId;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeoutId);
                timeoutId = setTimeout(function() {
                    func.apply(context, args);
                }, delay);
            };
        }

        function handleJenisChange() {
            const pilihJenis = $('#pilih_jenis').val();
            const hargaAwal = $('#price_sewa');
            const hargaKontan = $('#price_kontan');

            if (pilihJenis === 'sewa') {
                $('#section-jual').hide();
                $('#section-sewa').show();
                hargaKontan.prop('disabled', true);
                hargaAwal.prop('disabled', false);
            } else if (pilihJenis === 'jual') {
                $('#section-jual').show();
                $('#section-sewa').hide();
                hargaKontan.prop('disabled', false);
                hargaAwal.prop('disabled', true);
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                Toast.fire({
                    icon: 'success',
                    title: 'Nama file telah disalin ke clipboard!'
                });
            }, function(err) {
                console.error('Error copying text: ', err);
                Toast.fire({
                    icon: 'error',
                    title: 'Gagal menyalin nama file.'
                });
            });
        }

    </script>
@endsection

