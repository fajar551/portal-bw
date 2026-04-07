<form id="step2-form" action="">
    <div class="row mb-3">
        <div class="col-md-2">
            <div class="avatar avatar-xl rounded-circle">
                <img id="logo-preview" src="{{asset('placeholder.png')}}" alt="logo" class="">
            </div>
        </div>
        <div class="col-md-10">
            <div class="form-group">
                <label for="exampleInputEmail1">
                    <h4 class="mb-0">Logo Bisnis</h4>
                </label>
                <p>Harus setidaknya 200px by 200px dan lebih kecil dari 1MB dan JPG atau PNG format.</p>
                <input onchange="loadFile(event)" name="logo" id="logo" type="file" class="form-control" accept="image/*">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1">
            <h4>Nama Bisnis</h4>
        </label>
        <input name="company" placeholder="contoh: PT Qwords Company International" value="{{\App\Helpers\Cfg::get('CompanyName')}}" type="text" class="form-control">
    </div>

    <div class="form-group">
        <label for="">
            <h4 class="mb-0">Deskripsi usaha</h4>
        </label>
        <p class="text-muted">Mohon jelaskan apa yang Anda jual, kepada siapa Anda menjual, dan waktu saat Anda menagih pelanggan Anda.</p>
        <textarea placeholder="contoh: Kami menjual berbagai macam produk digital" name="description" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label for="">
            <h4>Alamat Bisnis</h4>
        </label>
        <p class="text-muted">Alamat legal</p>
        <textarea placeholder="contoh: Jl. Sukajadi, Kota Bandung, 4444" name="legal_address" class="form-control"></textarea>
        <p class="text-muted mt-4">Preferensi alamat surat-menyurat</p>
        <div class="form-check mb-3">
            <input onclick="toggle('#legal_address1', this)" name="isLegalAddress" class="form-check-input" type="checkbox" value="" id="defaultCheck1" checked>
            <label class="form-check-label text-muted font-weight-normal" for="defaultCheck1">
                Alamat pengiriman saya sama dengan alamat resmi diatas
            </label>
        </div>
        <textarea placeholder="contoh: Jl. Sukajadi, Kota Bandung, 4444" name="legal_address1" id="legal_address1" class="form-control" style="display: none"></textarea>
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1">
            <h4 class="mb-0">Bagaimana renacana Anda menggunakan CBMS Auto?</h4>
        </label>
        <p class="text-muted">Mohon pilih opsi yang sesuai dengan tujuan Anda menggunakan CBMS Auto</p>
        @php
            $checklist = [
                'Mengintegrasikan CBMS Auto ke website atau aplikasi kami melalui API',
                'Mengintegrasikan CBMS Auto ke toko online kami yang berbasis Shopify, WooCommerce atau Wix',
                'Menerima pembayaran melalui media sosial, chat, sms dan lainnya',
                'Mengirim uang melalui unggahan excel atau tautan untuk pengembalian uang (refund)',
                'Lainnya',
            ];
        @endphp
        @foreach ($checklist as $item)
            <div class="form-check mb-2">
                <input
                    name="plan_to_use[]"
                    class="form-check-input"
                    type="checkbox"
                    value=""
                    @if ($loop->last)
                        onclick="toggle2('#other_plan_to_use, #other_plan_to_use-error', this)"
                        id="plan_to_use-last"
                    @else
                        id="{{$loop->index}}"
                    @endif
                    >
                <label 
                    class="form-check-label" 
                    for="{{$loop->last ? "plan_to_use-last" : $loop->index}}">
                    {{$item}}
                </label>
            </div>
        @endforeach
        <small id="plan_to_use-error" class="is-invalid"></small>
        <div>
            <input name="other_plan_to_use" id="other_plan_to_use" placeholder="Katakan bagaimana Anda ingin menggunakan CBMS Auto" type="text" class="form-control mt-3" style="display: none">
        </div>
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1">
            <h4 class="mb-0">Bukti Bisnis</h4>
        </label>
        <p class="text-muted">Apakah Anda memiliki situs web/aplikasi?</p>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="weborapp" id="weborapp1" value="yes">
            <label class="form-check-label" for="weborapp1">
                Ya, sudah live dan dapat diakses
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="weborapp" id="weborapp" value="no">
            <label class="form-check-label" for="weborapp">
                Tidak, saya tidak memiliki situs web (atau sedang dikerjakan)
            </label>
        </div>
        <small id="weborapp-error" class="is-invalid"></small>

        <div class="mt-4" id="section1" style="display: none">
            <strong>Tautan ke website Anda</strong>
            <p class="mb-0">Sediakan tautan Situs Web atau Aplikasi dalam kriteria kami</p>
            <button type="button" class="btn btn- btn-link" data-toggle="modal" data-target="#exampleModal">
                Lihat Panduan
            </button>
            <input name="website" type="url" placeholder="contoh: https://yourwebsite.com" class="form-control mt-3">
        </div>

        <div class="mt-4" id="section2" style="display: none">
            <strong class="font-weight-bold">Silakan pilih alternatif untuk link situs web/aplikasi</strong>
            <select name="alternative" class="form-control">
                <option value=""></option>
                @php
                    $selectitems = ["Media Sosial", "Marketplace/E-commerce", "Situs Web Staging"];
                @endphp
                @foreach ($selectitems as $item)
                    <option value="{{$item}}">{{$item}}</option>
                @endforeach
            </select>

            <div class="mt-3">
                <div id="sosmed" style="display: none">
                    <strong class="font-weight-bold">Link ke media sosial bisnis Anda</strong>
                    <input name="sosial_media_url" type="url" class="form-control" placeholder="contoh: https://yourwebsite.com">
                </div>
                <div id="website_staging" style="display: none">
                    <strong class="font-weight-bold">Situs Web Staging</strong>
                    <input name="website_staging" type="url" placeholder="contoh: https://yourwebsite.com" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block submit">Lanjutkan ke tahap selanjutnya</button>
    </div>
</form>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLabel">Panduan Situs Web dan Aplikasi</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
              <div class="col-md-6 mr-0">
                  <img class="img-fluid" src="https://dashboard.xendit.co/images/WMA-thumbnail.svg" alt="panduan">
                  <div class="alert alert-warning mt-5" role="alert">
                    Untuk menghindari penolakan, pastikan Anda mengirimkan Situs Web atau Aplikasi dalam kriteria kami.
                  </div>
              </div>
              <div class="col-md-6 ml-0">
                  <ol>
                      <li>Situs web atau Aplikasi harus siap, aktif, dan dapat diakses. Dan harus sesuai dengan aktivitas bisnis Anda</li>
                      <li>Kami lebih memilih Situs Web dan Aplikasi dengan katalog atau produk yang dapat dibeli melalui alur pembayaran.</li>
                      <li>Jika tidak Ada, berikan tautan relevan yang mewakili bisnis Anda (Situs perusahaan, Media sosial, atau Marketplace)</li>
                      <li>Jika Anda menjalankan bisnis melalui aplikasi, harap cantumkan tautan App Store atau Play Store Anda</li>
                  </ol>
              </div>
          </div>
        </div>
      </div>
    </div>
</div>
  