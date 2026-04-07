@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Automatic Backups</title>
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
                                        <h4 class="mb-3">Automatic Backups</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <p>We recommend taking regular backups to protect against data loss. WHMCS
                                                    can perform daily automated backups of the database via one or more of
                                                    the following methods. As a precautionary measure, you should make your
                                                    own backups as-well.</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="accordion" id="accordionExample">
                                                    <div class="card">
                                                        <div class="card-header" id="headingOne">
                                                            <div class="mb-0 d-flex align-items-center">
                                                                <button class="btn btn-link btn-block text-left"
                                                                    type="button" data-toggle="collapse"
                                                                    data-target="#collapseOne" aria-expanded="true"
                                                                    aria-controls="collapseOne">
                                                                    FTP/SFTP Backup
                                                                </button>
                                                                <div class="badge badge-secondary">
                                                                    Inactive
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                                                            data-parent="#accordionExample">
                                                            <div class="card-body">
                                                                <div class="alert alert-primary d-flex align-items-center"
                                                                    role="alert">
                                                                    <div style="font-size: 32px; margin-right: 16px">
                                                                        <i class="fa fa-exclamation-circle"
                                                                            aria-hidden="true"></i>
                                                                    </div>
                                                                    <p class="mb-0">Lorem, ipsum dolor sit amet
                                                                        consectetur
                                                                        adipisicing elit. Quam impedit exercitationem
                                                                        eius earum maxime nobis est fugit laudantium.
                                                                        Tenetur illum necessitatibus beatae. Libero
                                                                        deserunt rerum beatae? Asperiores, explicabo
                                                                        corporis quia placeat laudantium magnam omnis
                                                                        maxime aspernatur perferendis ipsam, repellat
                                                                        necessitatibus ea blanditiis! Quos consectetur
                                                                        odio quibusdam maxime quis repudiandae
                                                                        recusandae.</p>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-8 col-sm-12">
                                                                        <div class="form-group">
                                                                            <label>FTP Hostname</label>
                                                                            <input type="text" class="form-control"
                                                                                placeholder="www.example.com">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="form-group">
                                                                            <label>FTP Port</label>
                                                                            <input type="text" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-6 col-sm-12">
                                                                        <div class="form-group">
                                                                            <label>FTP Username</label>
                                                                            <input type="text" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 col-sm-12">
                                                                        <div class="form-group">
                                                                            <label>FTP Password</label>
                                                                            <input type="password" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <label>FTP Destination</label>
                                                                        <input type="text" class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-4">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox"
                                                                                class="custom-control-input" id="secureFTP">
                                                                            <label class="custom-control-label"
                                                                                for="secureFTP">Use Secure
                                                                                FTP/SFTP(Recommended)</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox"
                                                                                class="custom-control-input"
                                                                                id="passiveFTP">
                                                                            <label class="custom-control-label"
                                                                                for="passiveFTP">Use Secure
                                                                                FTP/SFTP(Recommended)</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row my-2">
                                                                    <div class="col-lg-12">
                                                                        <button class="btn btn-success px-2">Test
                                                                            Connection</button>
                                                                        <button class="btn btn-light px-2" disabled>Save &
                                                                            Active</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card">
                                                        <div class="card-header" id="headingTwo">
                                                            <div class="mb-0 d-flex align-items-center">
                                                                <button class="btn btn-link btn-block text-left collapsed"
                                                                    type="button" data-toggle="collapse"
                                                                    data-target="#collapseTwo" aria-expanded="false"
                                                                    aria-controls="collapseTwo">
                                                                    cPanel Backup
                                                                </button>
                                                                <div class="badge badge-secondary">
                                                                    Inactive
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                                            data-parent="#accordionExample">
                                                            <div class="card-body">
                                                                <div class="form-group">
                                                                    <label>cPanel/WHM Server Hostname</label>
                                                                    <input type="text" class="form-control"
                                                                        placeholder="www.example.com">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>WHM API Username</label>
                                                                    <input type="text" class="form-control"
                                                                        placeholder="Username">
                                                                    <small>Enter the WHM username for your server.</small>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>WHM API Token</label>
                                                                    <input type="password" class="form-control"
                                                                        placeholder="Password">
                                                                    <small>Create an API Token in WHM > Development > Manage
                                                                        API Tokens. We recommend generating a new API Token
                                                                        for backups.</small>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>cPanel Username</label>
                                                                    <input type="text" class="form-control"
                                                                        placeholder="Username">
                                                                    <small>Enter the cPanel username of the account that
                                                                        hosts the WHMCS installation.</small>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Backup Destination</label>
                                                                    <input type="text" class="form-control"
                                                                        placeholder="Remote FTP Server">
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-8">
                                                                        <div class="form-group">
                                                                            <label for="">Remote Destination
                                                                                Hostname</label>
                                                                            <input type="text" class="form-control"
                                                                                placeholder="www.example.com">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <div class="form-group">
                                                                            <label for="">Port</label>
                                                                            <input type="text" class="form-control"
                                                                                value="22">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group">
                                                                            <label for="">Remote Destination User</label>
                                                                            <input type="text" class="form-control"
                                                                                placeholder="youruser@example.com">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <label for="">Remote Destination Password</label>
                                                                        <input type="text" class="form-control"
                                                                            placeholder="FTP Password">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="">Remote Destination Directory</label>
                                                                    <input type="text" class="form-control"
                                                                        placeholder="/backups/whmcs/">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="">Email Address</label>
                                                                    <input type="text" class="form-control">
                                                                    <small>The email address that should receive a
                                                                        confirmation email when the backup
                                                                        completes.</small>
                                                                </div>
                                                                <div>
                                                                    <button class="btn btn-success px-2">Test
                                                                        Connection</button>
                                                                    <button class="btn btn-light px-2" disabled>Save &
                                                                        Active</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card">
                                                        <div class="card-header" id="headingThree">
                                                            <div class="mb-0 d-flex align-items-center">
                                                                <button class="btn btn-link btn-block text-left collapsed"
                                                                    type="button" data-toggle="collapse"
                                                                    data-target="#collapseThree" aria-expanded="false"
                                                                    aria-controls="collapseThree">
                                                                    Daily Email Backups
                                                                </button>
                                                                <div class="badge badge-secondary">
                                                                    Inactive
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="collapseThree" class="collapse"
                                                            aria-labelledby="headingThree" data-parent="#accordionExample">
                                                            <div class="card-body">
                                                                <div class="alert alert-primary d-flex align-items-center"
                                                                    role="alert">
                                                                    <div style="font-size: 32px; margin-right: 16px">
                                                                        <i class="fa fa-exclamation-circle"
                                                                            aria-hidden="true"></i>
                                                                    </div>
                                                                    <p class="mb-0">Lorem, ipsum dolor sit amet
                                                                        consectetur
                                                                        adipisicing elit. Quam impedit exercitationem
                                                                        eius earum maxime nobis est fugit laudantium.
                                                                        Tenetur illum necessitatibus beatae. Libero
                                                                        deserunt rerum beatae? Asperiores, explicabo
                                                                        corporis quia placeat laudantium magnam omnis
                                                                        maxime aspernatur perferendis ipsam, repellat
                                                                        necessitatibus ea blanditiis! Quos consectetur
                                                                        odio quibusdam maxime quis repudiandae
                                                                        recusandae.</p>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="">Email Address</label>
                                                                    <input type="text" class="form-control"
                                                                        placeholder="yourname@example.com">
                                                                    <small>Enter the email address where you would like
                                                                        backups to be delivered to.</small>
                                                                </div>
                                                                <button class="btn btn-light px-2">Save & Active</button>
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
        </div>
    </div>
@endsection
