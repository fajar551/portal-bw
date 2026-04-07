@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  MarketConnect</title>
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
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3 market-connect">
                                        <div class="row">
                                            <div class="col-sm-12 col-lg-8">
                                                <div class="card-title mb-3">
                                                    <img src="https://proto.qwords.com/assets/img/marketconnect/logo.png"
                                                        alt="market-connect.jpg" width="70%">
                                                    <p class="lead font-size-18 mt-2">Connecting you with Value Added
                                                        Services,
                                                        Upsells
                                                        and Additional
                                                        Revenue Streams.</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card border-dark">
                                                    <div class="card-body">
                                                        <h4 class="card-title">Your Account</h4>
                                                        <div class="row justify-content-center align-items-center"
                                                            style="height: 10vh">
                                                            <button type="button" class="btn btn-light btn-sm"
                                                                data-toggle="modal"
                                                                data-target="#modalAccountMarketConnect">
                                                                Login/Create Account
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- MODAL LOGIN/CREATE ACCOUNT --}}
                                                <!-- Modal -->
                                                <div class="modal fade" id="modalAccountMarketConnect" tabindex="-1"
                                                    role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            {{-- <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button> --}}
                                                            <div class="modal-body text-center">
                                                                <div class="img-modal-account-container">
                                                                    <img src="https://proto.qwords.com/assets/img/marketconnect/logo.png"
                                                                        alt="logo.png" width="260">
                                                                </div>
                                                                <p class="mt-3">You can login using either your <a
                                                                        href="#">WHMCS
                                                                        Marketplace</a> or <a href="#">WHMCS Members</a>
                                                                    Area login details.</p>
                                                                <div class="marketconnect-login p-3">
                                                                    <form action="">
                                                                        <div class="form-group row">
                                                                            <label for=""
                                                                                class="col-sm-12 col-lg-3 col-form-label">Email</label>
                                                                            <div class="col-sm-12 col-lg-9">
                                                                                <input type="text" class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label for=""
                                                                                class="col-sm-12 col-lg-3 col-form-label">Password</label>
                                                                            <div class="col-sm-12 col-lg-9">
                                                                                <input type="password" class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox"
                                                                                class="custom-control-input"
                                                                                id="marketConnectToS">
                                                                            <label class="custom-control-label"
                                                                                for="marketConnectToS">I agree to the <a
                                                                                    href="#">WHMCS Marketplace Terms of
                                                                                    Service</a></label>
                                                                        </div>
                                                                        <div class="my-3">
                                                                            <button type="submit"
                                                                                class="btn btn-light px-3">Login</button>
                                                                        </div>
                                                                        <p class="mt-5 mb-0">Not Registered? <a
                                                                                href="#">Create a
                                                                                free
                                                                                WHMCS
                                                                                Marketplace account</a></p>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row justify-content-center">
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="brand-container">
                                                        <img class="card-img-top"
                                                            src="https://proto.qwords.com/assets/img/marketconnect/symantec/logo.png"
                                                            alt="digicert.png">
                                                    </div>
                                                    <div class="card-body text-center mt-3">
                                                        <h2 class="lead">SSL Certificates</h2>
                                                        <p class="card-text text-muted">From DigiCert</p>
                                                        <p>Sell SSL's from DigiCert, the world's premier high-assurance
                                                            digital certificate provider.</p>
                                                        <div class="d-flex justify-content-around mt-5">
                                                            <button type="button" class="btn btn-light px-2">Learn
                                                                more</button>
                                                            <button type="button" data-toggle="modal"
                                                                class="btn btn-success px-2" data-target="#prodModal">Start
                                                                Selling</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal fade" id="prodModal" tabindex="-1" role="dialog"
                                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <div class="row col-sm-12 col-lg-4">
                                                                    <img src="https://proto.qwords.com/assets/img/marketconnect/symantec/logo.png"
                                                                        alt="logo.png" width="220px">
                                                                </div>
                                                                <div class="col-sm-12 col-lg-8 text-right">
                                                                    <div>
                                                                        <h3 class="lead">SSL Certificates</h3>
                                                                        <p class="lead text-muted">From DigiCert</p>
                                                                    </div>
                                                                </div>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <div class="pb-2 mr-3 pr-3" aria-hidden="true">&times;
                                                                    </div>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <nav>
                                                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                                        <a class="nav-item nav-link active"
                                                                            id="nav-about-tab" data-toggle="tab"
                                                                            href="#nav-about" role="tab"
                                                                            aria-controls="nav-about"
                                                                            aria-selected="true">About</a>
                                                                        <a class="nav-item nav-link" id="nav-automation-tab"
                                                                            data-toggle="tab" href="#nav-automation"
                                                                            role="tab" aria-controls="nav-automation"
                                                                            aria-selected="false">Automation</a>
                                                                        <a class="nav-item nav-link" id="nav-pricing-tab"
                                                                            data-toggle="tab" href="#nav-pricing" role="tab"
                                                                            aria-controls="nav-pricing"
                                                                            aria-selected="false">Pricing</a>
                                                                        <a class="nav-item nav-link" id="nav-faq-tab"
                                                                            data-toggle="tab" href="#nav-faq" role="tab"
                                                                            aria-controls="nav-faq"
                                                                            aria-selected="false">FAQ</a>
                                                                    </div>
                                                                </nav>
                                                                <div class="tab-content" id="nav-tabContent">
                                                                    <div class="tab-pane fade show active" id="nav-about"
                                                                        role="tabpanel" aria-labelledby="nav-about-tab">
                                                                        <div class="p-3">

                                                                            <h3>The World's #1 Security Solution</h3>
                                                                            <h6>DigiCert owns 3 of the most widely
                                                                                recognised
                                                                                and trusted SSL certificate brands in the
                                                                                world.
                                                                            </h6>
                                                                            <p>With their brands RapidSSL and GeoTrust,
                                                                                DigiCert provides the world's premier
                                                                                high-assurance digital certificates.</p>
                                                                            <div class="border rounded p-3">
                                                                                <div class="row">
                                                                                    <div class="col-sm-12 col-lg-6 ">
                                                                                        <div class="row">
                                                                                            <div
                                                                                                class="col-sm-12 col-lg-2 py-2 pl-4">
                                                                                                <i class="ri-file-text-line"
                                                                                                    style="font-size: 32px"
                                                                                                    aria-hidden="true"></i>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-sm-12 col-lg-10">
                                                                                                <h6>Full Suite of SSL
                                                                                                    Certificates</h6>
                                                                                                <p class="p-0">DigiCert
                                                                                                    offers the
                                                                                                    widest
                                                                                                    range of SSL
                                                                                                    Certificates to
                                                                                                    fit every need.</p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-6 col-sm-12">
                                                                                        <div class="row">
                                                                                            <div
                                                                                                class="col-sm-12 col-lg-2 py-2 pl-4">
                                                                                                <i class="ri-shield-user-line"
                                                                                                    style="font-size: 32px"
                                                                                                    aria-hidden="true"></i>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-sm-12 col-lg-10">
                                                                                                <h6>Full Suite of SSL
                                                                                                    Certificates</h6>
                                                                                                <p class="p-0">DigiCert
                                                                                                    offers the
                                                                                                    widest
                                                                                                    range of SSL
                                                                                                    Certificates to
                                                                                                    fit every need.</p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row mt-3">
                                                                                <div class="col-lg-12">
                                                                                    <h6>About DigiCert</h6>
                                                                                    <p>DigiCert is the world's premier
                                                                                        provider of high-assurance digital
                                                                                        certificates — providing trusted
                                                                                        SSL, private and managed PKI
                                                                                        deployments, and device
                                                                                        certificates.</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="tab-pane fade" id="nav-automation"
                                                                        role="tabpanel"
                                                                        aria-labelledby="nav-automation-tab">
                                                                        ...</div>
                                                                    <div class="tab-pane fade" id="nav-pricing"
                                                                        role="tabpanel" aria-labelledby="nav-pricing-tab">
                                                                        ...</div>
                                                                    <div class="tab-pane fade" id="nav-faq" role="tabpanel"
                                                                        aria-labelledby="nav-faq-tab">
                                                                        ...</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="brand-container">
                                                        <img class="card-img-top"
                                                            src="https://proto.qwords.com/assets/img/marketconnect/weebly/logo.png"
                                                            alt="weebly.png">
                                                    </div>
                                                    <div class="card-body text-center mt-3">
                                                        <h2 class="lead">Website Builder</h2>
                                                        <p class="card-text text-muted">From Weebly</p>
                                                        <p>Make it easier for customers to create a website with Weebly's
                                                            drag and drop site builder.</p>
                                                        <div class="d-flex justify-content-around mt-5">
                                                            <button class="btn btn-light px-2">Learn more</button>
                                                            <button class="btn btn-success px-2">Start Selling</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="brand-container">
                                                        <img class="card-img-top"
                                                            src="https://proto.qwords.com/assets/img/marketconnect/codeguard/logo.png"
                                                            alt="codguard.png">
                                                    </div>
                                                    <div class="card-body text-center mt-3">
                                                        <h2 class="lead">Website Backup</h2>
                                                        <p class="card-text text-muted">From CodeGuard</p>
                                                        <p>Automated website backup with one-click restores, malware
                                                            detection and WordPress management.</p>
                                                        <div class="d-flex justify-content-around mt-5">
                                                            <button class="btn btn-light px-2">Learn more</button>
                                                            <button class="btn btn-success px-2">Start Selling</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="brand-container">
                                                        <img class="card-img-top"
                                                            src="https://proto.qwords.com/assets/img/marketconnect/sitelock/logo.png"
                                                            alt="sitelock.png">
                                                    </div>
                                                    <div class="card-body text-center mt-3">
                                                        <h2 class="lead">Website Security</h2>
                                                        <p class="card-text text-muted">From SiteLock</p>
                                                        <p>Security and malware scanning, detection and removal plus WAF and
                                                            CDN services.</p>
                                                        <div class="d-flex justify-content-around mt-5">
                                                            <button class="btn btn-light px-2">Learn more</button>
                                                            <button class="btn btn-success px-2">Start Selling</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="brand-container">
                                                        <img class="card-img-top"
                                                            src="https://proto.qwords.com/assets/img/marketconnect/sitelockvpn/logo.png"
                                                            alt="sitelock.png">
                                                    </div>
                                                    <div class="card-body text-center mt-3">
                                                        <h2 class="lead">VPN</h2>
                                                        <p class="card-text text-muted">From SiteLock</p>
                                                        <p>Offer High Speed, Secure, and Easy to Use VPN Security and
                                                            Protection for Web Browsing.</p>
                                                        <div class="d-flex justify-content-around mt-5">
                                                            <button class="btn btn-light px-2">Learn more</button>
                                                            <button class="btn btn-success px-2">Start Selling</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <div class="card p-3">
                                                    <div class="brand-container">
                                                        <img class="card-img-top"
                                                            src="https://proto.qwords.com/assets/img/marketconnect/spamexperts/logo.png"
                                                            alt="email-security.png">
                                                    </div>
                                                    <div class="card-body text-center mt-3">
                                                        <h2 class="lead">Email Security</h2>
                                                        <p class="card-text text-muted">From SpamExperts</p>
                                                        <p>Offer professional email services including Anti-Spam, Virus
                                                            Protection and Email Archiving.</p>
                                                        <div class="d-flex justify-content-around mt-5">
                                                            <button class="btn btn-light px-2">Learn more</button>
                                                            <button class="btn btn-success px-2">Start Selling</button>
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
