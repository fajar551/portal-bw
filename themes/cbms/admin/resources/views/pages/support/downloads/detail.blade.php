@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Downloads</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <!-- <div class="row">
                                <div class="col-12 p-3">
                                    <div class="page-title-box d-flex align-items-center justify-content-between">
                                        <h4 class="mb-0">Dashboard</h4>
                                    </div>
                                </div>
                            </div> -->
                <!-- end page title -->
                <div class="row">
                     


                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Downloads</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="form-group row">
                                            <label for="catName" class="col-sm-2 col-form-label">Category</label>
                                            <div class="col-sm-10">
                                                <select name="catName" id="catName" class="form-control">
                                                    <option value="0">Invoice</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="fileType" class="col-sm-2 col-form-label">File Type</label>
                                            <div class="col-sm-10">
                                                <select name="fileType" id="fileType" class="form-control">
                                                    <option value="0">ZIP</option>
                                                    <option value="1">Executable Files</option>
                                                    <option value="2">PDF File</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="titleName" class="col-sm-2 col-form-label">Title</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="titleName" id="titleName" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="descriptionName" class="col-sm-2 col-form-label">Description</label>
                                            <div class="col-sm-10">
                                                <textarea name="description" id="descripion" cols="20" class="form-control"
                                                    rows="5"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="fileName" class="col-sm-2 col-form-label">Filename</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="fileName" id="fileName" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="downloadCount" class="col-sm-2 col-form-label">Downloads</label>
                                            <div class="col-sm-10">
                                                <input type="number" name="downloadCount" id="downloadCount"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="clientOnly" class="col-sm-2 col-form-label">Clients Only</label>
                                            <div class="col-sm-10 d-flex align-items-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="customCheck1">
                                                    <label class="custom-control-label" for="customCheck1">Tick this box
                                                        to only allow logged in clients permission to download
                                                        it</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="productDownload" class="col-sm-2 col-form-label">Product
                                                Download</label>
                                            <div class="col-sm-10 d-flex align-items-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="customCheck2">
                                                    <label class="custom-control-label" for="customCheck2">Tick this box
                                                        if this download should only be available after a product or
                                                        addon purchase</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="hiddenClientArea" class="col-sm-2 col-form-label">Hidden</label>
                                            <div class="col-sm-10 d-flex align-items-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="customCheck3">
                                                    <label class="custom-control-label" for="customCheck3">Tick this box
                                                        to hide from client area
                                                        it</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="" class="col-sm-2 col-form-labe">Download Link</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="linkDownload" id="link-download"
                                                    class="form-control"
                                                    placeholder="https://proto.qwords.com/dl.php?type=d&id=1" disabled>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 d-flex justify-content-center">
                                            <button class="btn btn-success px-3 mx-1">Save Changes</button>
                                            <button class="btn btn-light px-3 mx-1">Cancel Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
