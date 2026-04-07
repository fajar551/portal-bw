<div class="alert alert-success">
    Silahkan melakukan pembayaran ke nomor Permata Bank Virtual Account Anda berikut: <strong>{{$nomor}}</strong>.
    {{-- <a href="#" data-toggle="modal" data-target="#VAModal">Lihat Cara Bayar</a> --}}
</div>

<div class="modal fade" id="VAModal" tabindex="-1" role="dialog" aria-labelledby="VAModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content text-left">
        <div class="modal-header">
          <h5 class="modal-title" id="VAModalLabel">Cara Bayar</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            Kami sangat menyarankan agar Anda membayar ke Virtual Account menggunakan bank yang sama, yaitu jika Anda memiliki BNI, silakan gunakan Virtual Account BNI.
          </div>

          {{-- Cara Bayar --}}
          <div id="accordion">
            <div class="card mb-0">
              <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                  <button class="btn btn-link btn-block" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    ATM Terdekat
                  </button>
                </h5>
              </div>
          
              <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    <ol>
                        <li>Masukkan Kartu Anda.</li>
                        <li>Pilih Bahasa.</li>
                        <li>Masukkan PIN ATM Anda.</li>
                        <li>Pilih "Menu Lainnya".</li>
                        <li>Pilih "Transfer".</li>
                        <li>Pilih Jenis rekening yang akan Anda gunakan (Contoh; "Dari Rekening Tabungan").</li>
                        <li>Pilih “Virtual Account Billing”</li>
                        <li>Masukkan nomor Virtual Account Anda (contoh: 88089999XXXXXX).</li>
                        <li>Konfirmasi, apabila telah sesuai, lanjutkan transaksi.</li>
                        <li> Transaksi Anda telah selesai</li>
                    </ol>
                </div>
              </div>
            </div>

            <div class="card mb-0">
              <div class="card-header" id="headingTwo">
                <h5 class="mb-0">
                  <button class="btn btn-link collapsed btn-block" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Internet Banking BNI
                  </button>
                </h5>
              </div>
              <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                <div class="card-body">
                  <ol>
                    <li>Ketik alamat https://ibank.bni.co.id kemudian klik “Enter”.</li>
                    <li>Masukkan User ID dan Password.</li>
                    <li>Pilih menu “Transfer”</li>
                    <li>Pilih “Virtual Account Billing”.</li>
                    <li>Kemudian masukan nomor Virtual Account Anda (contoh: 88089999XXXXXX) yang hendak dibayarkan. Lalu pilih rekening debet yang akan digunakan. Kemudian tekan ‘’lanjut’’</li>
                    <li>Periksa ulang mengenai transaksi yang anda ingin lakukan</li>
                    <li>Masukkan Kode Otentikasi Token.</li>
                    <li>Pembayaran Anda berhasil</li>
                  </ol>
                </div>
              </div>
            </div>
            <div class="card mb-0">
              <div class="card-header" id="headingThree">
                <h5 class="mb-0">
                  <button class="btn btn-link collapsed btn-block" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Mobile Banking BNI
                  </button>
                </h5>
              </div>
              <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                <div class="card-body">
                  <ol>
                    <li>Akses BNI Mobile Banking dari handphone kemudian masukkan user ID dan password.</li>
                    <li>Pilih menu “Transfer”.</li>
                    <li>Pilih menu “Virtual Account Billing” kemudian pilih rekening debet.</li>
                    <li>Masukkan nomor Virtual Account Anda (contoh: 88089999XXXXXX) pada menu “input baru”.</li>
                    <li>Konfirmasi transaksi anda</li>
                    <li>Masukkan Password Transaksi.</li>
                    <li>Pembayaran Anda Telah Berhasil.</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        setInterval(update, 5000);
    });
    function update() {
        $.ajax({
            type : 'POST',
            url : "{{url('permatabankva/callback/ajax')}}",
            data: {id: "{{$params['invoiceid']}}", _token: "{{csrf_token()}}"},
            success : function(data){
                if(data.status){
                    location.reload();
                }	
            },
        });
    };
</script>
