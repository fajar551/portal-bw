<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .card-backlink{
        padding: 20px;
        background-color: #FFF;
        border: 1px solid #F7863B;
        border-radius: 8px;
        color: #F7863B;
        font-size: 14px;
        min-width: 200px;
        font-weight: bold;
    }

    .card-backlink:hover{
        background-color: #F7863B;
        color: #FFF;
    }
    
    .active-card{
        padding: 20px;
        background-color: #F7863B;
        border: 1px solid #F7863B;
        border-radius: 8px;
        color: #FFF;
        font-size: 14px;
        min-width: 200px;
        font-weight: bold;
    }
    
    .start-section{
        display: flex;
        gap:10px;
    }
    
    .start-section>a{
        text-decoration: none;
    }
    
    .sidebar-baclink{
        text-decoration: none !important;
    }
    
    @keyframes rotate {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
    
    .load{
    	animation: rotate 2s infinite;
    }
    
    div.dataTables_wrapper div.dataTables_info{
        background-color: transparent;
        padding: 0px;
        color: black;
    }
    
    .form-select{
        border-radius: 8px;
        padding: 10px;
        width:100%;
        margin-bottom:10px;
        border:1px solid #ced4da;
    }
    
    .btn-blue{
        padding: 8px 10px;
        border:1px solid #337ab7;
        color:white;
        background-color: #337ab7;
    }
    
    .btn-blue:hover{
        border:1px solid #1a4d80;
        color:white;
        background-color: #1a4d80;
    }
    
    .select2-selection--multiple{
        border-radius:8px !important; 
        border:1px solid #ced4da !important; 
        padding:8px !important;
    }
    
    .select2-search__field{
        margin:0px !important;
        font-family: "Plus Jakarta Sans" !important;
        height:20px !important;
    }
    
    .dataTables_wrapper .dataTables_length{
        background-color: transparent;
    }
    
    .input-sm{
    	background-color: rgba(0,0,0,.05) !important;
    }
    
    .table-striped tbody tr:nth-of-type(2n+1) {
    	background-color: rgba(0,0,0,.05) !important;
    }
    
    .card {
        border-radius: 15px;
    }
    
    .btn-info-qw{
        font-size:8px;
        font-weight:bold;
        border-radius:100%; 
        padding: 0px 3px;
        background-color:transparent;
        border: 2px solid #F7863B;
        color: #F7863B;
    }
    
    .btn-info-qw:hover{
        color: #FFF;
        background-color:#F7863B;
    }
</style>

<input type="hidden" value="{{ $myid ?? '' }}" id="myId" />
<input type="hidden" value="{{ $clientsdetails['uuid'] ?? '' }}" id="uuid" />
<input type="hidden" value="{{ $emailAdmin ?? '' }}" id="emailAdmin" />

<div class="start-section">
    
    <a href="{{ $modulelink ?? '' }}">
        <div class="card-backlink active-card">
                Blog Saya
        </div>
    </a>
    
    <a href="{{ $modulelink ?? '' }}&action=index">
        <div class="card-backlink">
                Pesan Backlink
        </div>
    </a>
    
    <a href="{{ $modulelink ?? '' }}&action=daftarPenjualan">
        <div class="card-backlink">
                Daftar Penjualan
        </div>
    </a>
    
    <a href="{{ $modulelink ?? '' }}&action=daftarPembelian">
        <div class="card-backlink">
                Daftar Pembelian
        </div>
    </a>
    
    <a href="{{ $modulelink ?? '' }}&action=pencairanDana">
        <div class="card-backlink">
                Pencairan Dana
        </div>
    </a>
</div>

<div style="display:flex; justify-content:space-between; width:100%; margin-top:30px">
    <h2 style="font-weight:bold; font-size: 24px">Semua Blog Saya</h2>
    @if($isFreeze ?? false)
        <button disabled class="btn btn-primary" style="font-weight:bold;border-radius:8px" data-toggle="modal" data-target="#createBlog"><i class="fa fa-plus" style="font-size:12px"></i> Daftar Blog</button>
    @else
        <button class="btn btn-primary" style="font-weight:bold;border-radius:8px" data-toggle="modal" data-target="#createBlog"><i class="fa fa-plus" style="font-size:12px"></i> Daftar Blog</button>
    @endif

</div>

<div class="card table-responsive" style="margin-top:30px">
    <table id="dtable-my-blog" class="table table-striped table-bordered">
          <thead class="thead-dark">
            <tr>
                <th rowspan="2" style="text-align: center;padding-bottom: 25px;">No</th>
                <th rowspan="2" style="text-align: center;padding-bottom: 25px;">Nama Blog</th>
                <th rowspan="2" style="text-align: center;padding-bottom: 25px;">URL Blog</th>
                <th rowspan="2" style="text-align: center;padding-bottom: 25px;">Harga</th>
                <th rowspan="2" style="text-align: center;padding-bottom: 25px;">Kategori</th>
                <th rowspan="2" style="text-align: center;padding-bottom: 25px;">Bahasa</th>
                <th colspan="3" style="text-align: center;">Metrik SEO</th>
                <th rowspan="2" style="text-align: center;padding-bottom: 25px;">Status</th>
                <th rowspan="2" style="text-align: center;padding-bottom: 25px;">Aksi</th>
            </tr>
            <tr>
                <th class="text-center custom-th">
                    DA<sup><button id="button_da" class="btn btn-info-qw">?</button></sup>
                </th>
                <th class="text-center custom-th">
                    PA<sup><button id="button_pa" class="btn btn-info-qw">?</button></sup>
                </th>
                <th class="text-center custom-th">
                    Traffic<sup><button id="button_traffic" class="btn btn-info-qw">?</button></sup>
                </th>
            </tr>
        </thead>
        <tbody id="data-my-blog">
            <!-- Your data rows go here -->
        </tbody>
    </table>
</div>
<div id="modal_loader_backlink" style="position: fixed;top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 109999;  display: none; justify-content: center; align-items: center;" >
    <div id="loader" style="background-color: #fff; padding: 20px; border-radius: 8px; z-index: 1000; width:fit-content; display:flex">
        <img class="load" src="{{ $documentroot ?? '' }}/load.webp" width="40px" height="43px"/>
        <h5 style="margin-left:15px; align-self: center" id="content-modal">Sedang mendaftarkan blog Anda, mohon tunggu...</h5>
    </div>
</div>

<div class="modal fade" id="createBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight:bold">Daftar Blog</h4>
          </div>
          <div class="modal-body">
            <div>
                <div style="width:100%">
                    <p>Nama Blog:</p>
                    <input type="text" name="blog_name" id="blog_name" placeholder="Masukkan nama blog" style="width:100%; border-radius:8px; border:1px solid #ced4da; padding:8px; margin-bottom:10px"/>
                </div>
                <div style="width:100%">
                    <p>Url Blog:</p>
                    <input type="text" name="blog_url" id="blog_url" placeholder="Masukkan url blog" style="width:100%; border-radius:8px; border:1px solid #ced4da; padding:8px; margin-bottom:10px"/>
                </div>
                <div style="width:100%; margin-bottom:10px;">
                    <p>Kategori:</p>
                    <select class="js-example-basic-multiple" id="category" name="category[]" multiple="multiple" style="width:100%;">
                    </select>
                </div>
                <div style="width:100%;">
                    <p>Bahasa:</p>
                    <select id="language" class="form-select">
                        <option value="Indonesia">Indonesia</option>
                        <option value="English">English</option>
                    </select>
                </div>
                <div style="width:100%">
                    <p>Harga: <button id="button_price" class="btn btn-info-qw">?</button></p>
                    <input type="number" name="price" min="50000" value="50000" id="price" placeholder="Masukkan harga backlink" style="width:100%; border-radius:8px; border:1px solid #ced4da; padding:8px; margin-bottom:10px"/>
                </div>
                <div style="width:100%">
                    <p>Kata Kunci:</p>
                    <input type="text" name="kata_kunci" id="kata_kunci" placeholder="Masukkan kata kunci" style="width:100%; border-radius:8px; border:1px solid #ced4da; padding:8px; margin-bottom:10px"/>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal" style="padding:8px">Batal</button>
            <button class="btn btn-primary" onclick="createBlog()" type="submit" data-dismiss="modal">Daftar</button>
          </div>
    </div>
 </div>
</div>

<div class="modal fade" id="editBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight:bold">Edit Blog</h4>
          </div>
          <div class="modal-body">
            <div>
                <div style="width:100%">
                    <p>Nama Blog:</p>
                    <input type="text" name="blog_name" id="blog_name_edit" placeholder="Masukkan nama blog" style="width:100%; border-radius:8px; border:1px solid #ced4da; padding:8px; margin-bottom:10px"/>
                </div>
                <div style="width:100%">
                    <p>Url Blog:</p>
                    <input type="text" name="blog_url" id="blog_url_edit" placeholder="Masukkan url blog" style="width:100%; border-radius:8px; border:1px solid #ced4da; padding:8px; margin-bottom:10px"/>
                </div>
                <div style="width:100%; margin-bottom:10px">
                    <p>Kategori:</p>
                    <select class="js-example-basic-multiple" id="category_edit" name="category[]" multiple="multiple" style="width:100%;">
                    </select>
                </div>
                <div style="width:100%">
                    <p>Bahasa:</p>
                    <select id="language_edit" class="form-select">
                        <option value="Indonesia">Indonesia</option>
                        <option value="English">English</option>
                    </select>
                </div>
                <div style="width:100%">
                    <p>Harga: <button id="button_price" class="btn btn-info-qw">?</button></p>
                    <input type="number" name="price" id="price_edit" min="50000" value="50000" placeholder="Masukkan harga backlink" style="width:100%; border-radius:8px; border:1px solid #ced4da; padding:8px; margin-bottom:10px"/>
                </div>
                <div style="width:100%">
                    <p>Kata Kunci:</p>
                    <input type="text" name="kata_kunci_edit" id="kata_kunci_edit" placeholder="Masukkan kata kunci" style="width:100%; border-radius:8px; border:1px solid #ced4da; padding:8px; margin-bottom:10px"/>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal" style="padding:8px">Close</button>
            <button class="btn btn-primary" id="edit_blog" onclick="editBlog()" type="submit" data-dismiss="modal">Edit</button>
          </div>
    </div>
 </div>
</div>

<div class="modal fade" id="deleteBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight:bold">Delete Blog</h4>
          </div>
          <div class="modal-body">
            <h3 style="font-size:16px">Apakah Anda ingin menghapus blog ini?</h3>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal" style="padding:8px">Cancel</button>
            <button class="btn btn-primary" onclick="deleteBlog()" id="delete_blog" type="submit" data-dismiss="modal">Yes</button>
          </div>
    </div>
 </div>
</div>

<div class="modal fade" id="resubmitBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight:bold">Resubmit Blog</h4>
          </div>
          <div class="modal-body">
            <h3 style="font-size:16px">Apakah Anda ingin resubmit blog ini?</h3>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal" style="padding:8px">Cancel</button>
            <button class="btn btn-primary" onclick="resubmitBlog()" id="resubmit_blog" type="submit" data-dismiss="modal">Yes</button>
          </div>
    </div>
 </div>
</div>

<div class="modal fade" id="platformFee" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight:bold">Informasi Platfom Fee</h4>
          </div>
          <div class="modal-body">
            <h3 style="font-size:16px">Setiap penjualan backlink Anda akan ada potongan biaya platform sebesar 10%</h3>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary" onclick="informasiPlatformFee()" type="submit" data-dismiss="modal">Yes</button>
          </div>
    </div>
 </div>
</div>

<div class="modal fade" id="informasiBlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" style="font-weight:bold">Informasi Blog</h4>
          </div>
          <div class="modal-body">
            <h3 style="font-size:16px">Blog Anda akan di verifikasi oleh Admin maksimal 3 hari kerja, kami akan memberitahu Anda melalui email ketika domain sudah siap di pesan</h3>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary" onclick="informasiBlog()" type="submit" data-dismiss="modal">Yes</button>
          </div>
    </div>
 </div>
</div>

<link href="https://cdn.datatables.net/v/bs/dt-1.13.6/date-1.5.1/sb-1.5.0/sp-2.2.0/datatables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/v/bs/dt-1.13.6/date-1.5.1/sb-1.5.0/sp-2.2.0/datatables.min.js"></script>
<script src="{{ $documentroot ?? '' }}/js/myBlog.js?v=1.4"></script>
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
