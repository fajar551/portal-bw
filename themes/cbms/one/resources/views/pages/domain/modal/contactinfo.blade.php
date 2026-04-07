<div class="modal fade" id="ubah-informasi-kontak" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Ubah informasi kontak</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    It is important to keep your domain WHOIS contact information up-to-date at all times to avoid losing control of your domain. 
                </div>

                <div class="mt-3">
                    <div class="custom-control custom-radio">
                        <input type="radio" id="customRadio16" name="customRadio" class="custom-control-input">
                        <label class="custom-control-label" for="customRadio16">Gunakan Kontak Akun Client Goldenfast.net yang sudah ada</label>
                        <div class="form-group mt-2">
                            <label>Pilih kontak</label>
                            <select class="form-control">
                                <option value="u1718">Profil Kontak Utama</option>
                                @foreach ($contactList as $contact)
                                    <option value="{{$contact->id}}">{{ " $contact->firstname " . " $contact->lastname " }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="customRadio17" name="customRadio" class="custom-control-input">
                        <label class="custom-control-label" for="customRadio17">Specify custom information di bawah ini</label>
                        <div class="row m-2">
                            <div class="col-lg-12">
                                <h5 class="mt-2 text-primary">Data diri</h5>
                                <hr>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">ID Number</label>
                                    <input type="text" id="namadepan" class="form-control" value="3403010710900004">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Nama depan</label>
                                    <input type="text" id="namadepan" class="form-control" value="Oki">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Nama belakang</label>
                                    <input type="text" id="namabelakang" class="form-control" value="T Wibowo">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Nama Perusahaan</label>
                                    <input type="text" id="namaperusahaan" class="form-control" value="PT. Konsep Digital Indonesia">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Email</label>
                                    <input type="email" id="email" class="form-control" value="okitwibowo@gmail.com">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">No Telepon</label>
                                    <input type="text" id="telepon" class="form-control" value="087754320087">
                                </div>
                            </div>
                        </div>
                        <div class="row m-1">
                            <div class="col-lg-12">
                                <h5 class="mt-2 text-primary">Alamat</h5>
                                <hr>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Alamat</label>
                                    <input type="text" id="alamat" class="form-control" value="Jl. Kusumanegara No.260">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Alamat 2</label>
                                    <input type="text" id="alamat2" class="form-control" value="">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Kota</label>
                                    <input type="text" id="kota" class="form-control" value="Yogyakarta">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Propinsi/Wilayah</label>
                                    <input type="text" id="profinsi" class="form-control" value="Daerah Istimewa Yogyakarta">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Kode Pos</label>
                                    <input type="text" id="kodepos" class="form-control" value="55171">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="simpleinput">Negara</label>
                                    <select class="form-control">
                                        <option>India</option>
                                        <option>Indonesia</option>
                                        <option>Japan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save change</button>
            </div>
        </div>
    </div>
</div>