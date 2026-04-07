<div class="modal fade" id="form-dns-manager" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">DNS Manager</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <p>Kelola DNS records Khusus untuk domain tanpa Hosting/Cpanel Pastikan Domain sudah menggunakan Nameserver: dnsiix1.qwords.net & dnsiix2.qwords.net</p>
                    <p>Nama Domain: <b>argawibowo.my.id</b></p>
                </div>
                <div class="table-responsive table-striped">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Select</th>
                                <th>No</th>
                                <th>Host Name</th>
                                <th>TTL</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Hapus Record</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customCheck31">
                                        <label class="custom-control-label" for="customCheck31"></label>
                                    </div>
                                </td>
                                <td>1</td>
                                <td>
                                    <input type="text" name="" class="form-control" value="localhost">
                                </td>
                                <td>
                                    <input type="text" name="" class="form-control" value="14400">
                                </td>
                                <td>A</td>
                                <td>
                                    <div class="form-group row">
                                        <label for="colFormLabel" class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" id="colFormLabel" placeholder="127.0.0.1">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-danger"><i class="feather-trash"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customCheck32">
                                        <label class="custom-control-label" for="customCheck32"></label>
                                    </div>
                                </td>
                                <td>2</td>
                                <td>
                                    <input type="text" name="" class="form-control" value="argawibowo.my.id.">
                                </td>
                                <td>
                                    <input type="text" name="" class="form-control" value="14400">
                                </td>
                                <td>A</td>
                                <td>
                                    <div class="form-group row">
                                        <label for="colFormLabel" class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" id="colFormLabel" placeholder="103.28.14.14">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-danger"><i class="feather-trash"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customCheck33">
                                        <label class="custom-control-label" for="customCheck33"></label>
                                    </div>
                                </td>
                                <td>3</td>
                                <td>
                                    <input type="text" name="" class="form-control" value="mail">
                                </td>
                                <td>
                                    <input type="text" name="" class="form-control" value="14400">
                                </td>
                                <td>CNAME</td>
                                <td>
                                    <div class="form-group row">
                                        <label for="colFormLabel" class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" id="colFormLabel" placeholder="argawibowo.my.id">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-danger"><i class="feather-trash"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-group mt-2">
                    <a href="#" class="btn btn-primary">Simpan perubahan</a>
                    <a href="#" class="btn btn-primary">Kembalikan DNS Zone ke Default</a>
                    <a href="#" class="btn btn-danger" data-toggle="modal" data-dismiss="modal">Hapus Semua DNS Record</a>
                </div>
                <div class="mt-2">
                    <h5 class="modal-title" id="#">Tambah DNS record Baru</h5>
                    <hr>
                    <div class="table-responsive table-striped">
                        <table class="table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Host Name</th>
                                    <th>TTL</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <input type="email" class="form-control" id="colFormLabel" placeholder="">
                                            </div>
                                            <label for="colFormLabel" class="col-sm-6 col-form-label">.argawibowo.my.id.</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="" class="form-control" value="14400">
                                    </td>
                                    <td>
                                        <select class="form-control">
                                            <option value="A" selected="selected">A</option>
                                            <option value="AAAA">AAAA</option>
                                            <option value="MX">MX</option>
                                            <option value="CNAME">CNAME</option>
                                            <option value="DNAME">DNAME</option>
                                            <option value="NS">NS</option>
                                            <option value="TXT">TXT</option>
                                            <option value="SRV">SRV</option>
                                            <option value="PTR">PTR</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="form-group row">
                                            <label for="colFormLabel" class="col-sm-3 col-form-label">Address</label>
                                            <div class="col-sm-9">
                                                <input type="email" class="form-control" id="colFormLabel" placeholder="">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group mt-2">
                    <a href="#" class="btn btn-success" data-toggle="modal" data-dismiss="modal">Simpan perubahan</a>
                </div>
            </div>
        </div>
    </div>
</div>