@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Storage Settings</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Sidebar Shortcut -->

                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Storage Settings</h4>
                                    </div>
                                    <nav>
                                        <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="nav-settings-tab" data-toggle="tab"
                                                    href="#nav-settings" role="tab" aria-controls="nav-settings"
                                                    aria-selected="true">Settings</a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="nav-config-tab" data-toggle="tab" href="#nav-config"
                                                    role="tab" aria-controls="nav-config"
                                                    aria-selected="false">Configurations</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="tab-content" id="nav-tabContent">
                                        <div class="tab-pane fade show active" id="nav-settings" role="tabpanel"
                                            aria-labelledby="nav-settings-tab">
                                            <div class="card p-3">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="alert alert-primary d-flex align-items-center"
                                                            role="alert">
                                                            <i class="fa fa-exclamation-circle mr-3 " aria-hidden="true"
                                                                style="font-size: 32px"></i>
                                                            <p class="m-0">Changing an existing storage method will require
                                                                a migration process to run in the background. Depending on
                                                                the number of files, this can take some time to complete.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Settings Form --}}
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Client
                                                                Files</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <select name="" id="" class="form-control">
                                                                    <option value="0">Local Storage:
                                                                        /home/protoqwords/publice_html/attachments</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label
                                                                class="col-sm-12 col-lg-2 col-form-label">Downloads</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <select name="" id="" class="form-control">
                                                                    <option value="0">Local Storage:
                                                                        /home/protoqwords/publice_html/attachments</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Email
                                                                Attachment</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <select name="" id="" class="form-control">
                                                                    <option value="0">Local Storage:
                                                                        /home/protoqwords/publice_html/attachments</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Email
                                                                Images</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <select name="" id="" class="form-control">
                                                                    <option value="0">Local Storage:
                                                                        /home/protoqwords/publice_html/attachments</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Email Template
                                                                Attachments</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <select name="" id="" class="form-control">
                                                                    <option value="0">Local Storage:
                                                                        /home/protoqwords/publice_html/attachments</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Knowledgebase
                                                                Images</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <select name="" id="" class="form-control">
                                                                    <option value="0">Local Storage:
                                                                        /home/protoqwords/publice_html/attachments</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Project
                                                                Management Files</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <select name="" id="" class="form-control">
                                                                    <option value="0">Local Storage:
                                                                        /home/protoqwords/publice_html/attachments</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Ticket
                                                                Attachments </label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <select name="" id="" class="form-control">
                                                                    <option value="0">Local Storage:
                                                                        /home/protoqwords/publice_html/attachments</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="nav-config" role="tabpanel"
                                            aria-labelledby="nav-config-tab">
                                            <div class="card p-3">
                                                <div class="row config-card">
                                                    <div class="col-lg-3">
                                                        <div class="card pb-3">
                                                            <div class="card-header bg-success text-white">
                                                                <div class="row">
                                                                    <div class="col-lg-8">
                                                                        Local Storage
                                                                    </div>
                                                                    <div class="col-lg-4 d-flex align-items-center">
                                                                        <i class="fa fa-play-circle mr-2"
                                                                            aria-hidden="true"></i>
                                                                        <i class="fas fa-copy mr-2"></i>
                                                                        <i class="fa fa-cog mr-2" aria-hidden="true"></i>
                                                                        <i class="fa fa-window-close"
                                                                            aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row icon-hdd">
                                                                    <div class="col-lg-3">
                                                                        <i class="fas fa-hdd"></i>
                                                                    </div>
                                                                    <div class="col-lg-9 d-flex align-items-center">
                                                                        .../downloads
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="card pb-3">
                                                            <div class="card-header bg-success text-white">
                                                                <div class="row">
                                                                    <div class="col-lg-8">
                                                                        Local Storage
                                                                    </div>
                                                                    <div class="col-lg-4 d-flex align-items-center">
                                                                        <i class="fa fa-play-circle mr-2"
                                                                            aria-hidden="true"></i>
                                                                        <i class="fas fa-copy mr-2"></i>
                                                                        <i class="fa fa-cog mr-2" aria-hidden="true"></i>
                                                                        <i class="fa fa-window-close"
                                                                            aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row icon-hdd">
                                                                    <div class="col-lg-3">
                                                                        <i class="fas fa-hdd"></i>
                                                                    </div>
                                                                    <div class="col-lg-9 d-flex align-items-center">
                                                                        .../downloads
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="card pb-3">
                                                            <div class="card-header bg-success text-white">
                                                                <div class="row">
                                                                    <div class="col-lg-8">
                                                                        Local Storage
                                                                    </div>
                                                                    <div class="col-lg-4 d-flex align-items-center">
                                                                        <i class="fa fa-play-circle mr-2"
                                                                            aria-hidden="true"></i>
                                                                        <i class="fas fa-copy mr-2"></i>
                                                                        <i class="fa fa-cog mr-2" aria-hidden="true"></i>
                                                                        <i class="fa fa-window-close"
                                                                            aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row icon-hdd">
                                                                    <div class="col-lg-3">
                                                                        <i class="fas fa-hdd"></i>
                                                                    </div>
                                                                    <div class="col-lg-9 d-flex align-items-center">
                                                                        .../downloads
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End MAIN CARD -->
        </div>
    </div>
@endsection
