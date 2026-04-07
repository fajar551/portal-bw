<form action="" id="step3-form">
    <div class="form-group">
        <label for="">
            <h4>Pemilik Bisnis</h4>
        </label>
        <div class="mt-3">
            <div class="font-weight-bold">Jabatan dalam perusahaan</div>
            <select name="position" class="form-control">
                @php
                    $selectitems = ["Founder / Owner / Director", "Finance", "IT, Product Management, Development", "Other"];
                @endphp
                @foreach ($selectitems as $item)
                    <option value="{{$item}}">{{$item}}</option>
                @endforeach
            </select>
        </div>
    
        <div class="mt-3">
            <div class="font-weight-bold">Nama lengkap sesuai KTP</div>
            <small class="mb-0">Harus sesuai dengan KTP dan Rekening</small>
            <input name="id_card_name" type="text" class="form-control">
        </div>
    
        <div class="mt-3">
            <div class="font-weight-bold">Alamat email</div>
            <input name="email_address" type="email" class="form-control">
        </div>
    
        <div class="mt-3">
            <div class="font-weight-bold">Nomor handphone</div>
            <input name="phone_number" type="text" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block submit">Lanjutkan ke tahap selanjutnya</button>
    </div>
</form>
