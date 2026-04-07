</head>
@php
    $companyLogo = Cfg::getValue('LogoURL');
    $defaultLogo = Theme::asset('assets/images/WHMCEPS.png');
@endphp

<body data-topbar="dark" data-layout="horizontal">

    <!-- Vertical navbar -->
    <header id="page-topbar">
        <div class="navbar-header">
            <div class="d-flex">
                <button type="button" class="btn btn-sm px-4 font-size-24 header-item waves-effect"
                    id="vertical-menu-btn">
                    <i class="ri-menu-2-line align-middle"></i>
                </button>
                <!-- LOGO -->
                <div class="navbar-brand-box">
                    {{-- LOGO DARK --}}
                    <a href="{{ route('admin.pages.dashboard.index') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            @if (empty($companyLogo))
                                <img src="{{ $defaultLogo }}" alt="Logo Dashboard" height="50" width="70">
                            @else
                                <img src="{{ $companyLogo }}" alt="Logo Dashboard" height="50" width="70">
                            @endif
                        </span>
                        <span class="logo-lg">
                            @if (empty($companyLogo))
                                <img src="{{ $defaultLogo }}" alt="Logo Dashboard" height="50" width="70">
                            @else
                                <img src="{{ $companyLogo }}" alt="Logo Dashboard" height="50" width="70">
                            @endif
                        </span>
                    </a>
                    {{-- LOGO LIGHT --}}
                    <a href="{{ route('admin.pages.dashboard.index') }}" class="logo logo-light">
                        <span class="logo-sm">
                            @if (empty($companyLogo))
                                <img src="{{ $defaultLogo }}" alt="Logo Dashboard" style="height: 50px; max-width: 150px; object-fit: contain;">
                            @else
                                <img src="{{ $companyLogo }}" alt="Logo Dashboard" style="height: 50px; max-width: 150px; object-fit: contain;">
                            @endif
                        </span>
                        <span class="logo-lg">
                            @if (empty($companyLogo))
                                <img src="{{ $defaultLogo }}" alt="Logo Dashboard" style="height: 50px; max-width: 150px; object-fit: contain;">
                            @else
                                <img src="{{ $companyLogo }}" alt="Logo Dashboard" style="height: 50px; max-width: 150px; object-fit: contain;">
                            @endif
                        </span>
                    </a>
                </div>
            </div>

            <div class="d-flex">
                <!-- App Search-->
                <form id="frmIntelligentSearch" action="" method="post" class="app-search d-none d-lg-block"
                    autocomplete="off">
                    <div class="position-relative">
                        <input type="text" id="searchkey" name="searchterm" class="form-control"
                            placeholder="Search...">
                        <span class="ri-search-line"></span>
                    </div>
                </form>

                <div class="dropdown d-inline-block d-lg-none ml-2">
                    <button type="button" class="btn header-item noti-icon waves-effect"
                        id="page-header-search-dropdown" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="ri-search-line"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
                        aria-labelledby="page-header-search-dropdown">

                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i
                                                class="ri-search-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="dropdown d-none d-lg-inline-block ml-1">
                    <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                </div>

                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item noti-icon waves-effect"
                        id="page-header-notifications-dropdown" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="ri-notification-3-line"></i>
                        {{-- <span class="noti-dot"></span> --}}
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
                        aria-labelledby="page-header-notifications-dropdown">
                        <div class="p-3">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0"> Notifications </h6>
                                </div>
                                <div class="col-auto">
                                    <a href="#!" class="small"> View All</a>
                                </div>
                            </div>
                        </div>
                        <div data-simplebar="init" style="max-height: 230px;">
                            <div class="simplebar-wrapper" style="margin: 0px;">
                                <div class="simplebar-height-auto-observer-wrapper">
                                    <div class="simplebar-height-auto-observer"></div>
                                </div>
                                <div class="simplebar-mask">
                                    <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                        <div class="simplebar-content-wrapper"
                                            style="height: auto; overflow: hidden;">
                                            <div class="simplebar-content" style="padding: 0px;">
                                                <h5 class="text-muted text-center">Notification Empty</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="simplebar-placeholder" style="width: 0px; height: 0px;"></div>
                            </div>
                            <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                                <div class="simplebar-scrollbar"
                                    style="transform: translate3d(0px, 0px, 0px); display: none;">
                                </div>
                            </div>
                            <div class="simplebar-track simplebar-vertical" style="visibility: hidden;">
                                <div class="simplebar-scrollbar"
                                    style="transform: translate3d(0px, 0px, 0px); display: none;">
                                </div>
                            </div>
                        </div>
                        <div class="p-2 border-top">
                            <a class="btn btn-sm btn-link font-size-14 btn-block text-center"
                                href="javascript:void(0)">
                                <i class="mdi mdi-arrow-right-circle mr-1"></i> View More..
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown d-inline-block user-dropdown">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-circle header-profile-user"
                            src="{{ Theme::asset('assets/images/users/avatar-2.jpg') }}" alt="Header Avatar">
                        <span
                            class="d-none d-xl-inline-block ml-1">{{ Auth::guard('admin')->user()->firstname }}</span>
                        <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <!-- item-->
                        <a class="dropdown-item" href="{{ url('/home') }}" target="_blank"><i
                                class="ri-user-line align-middle mr-1"></i>
                            {{ __('admin.clientarea') }}</a>
                        <a class="dropdown-item" href="#"><i class="ri-wallet-2-line align-middle mr-1"></i>
                            {{ __('admin.mynotes') }}</a>
                        <a class="dropdown-item d-block" href="{{ url('admin/myaccount') }}"><i
                                class="ri-settings-2-line align-middle mr-1"></i> {{ __('admin.myaccount') }}</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" role="button" href="{{ route('admin.logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                class="ri-shut-down-line align-middle mr-1 text-danger"></i>
                            {{ __('admin.logout') }}</a>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- End Vertical navbar -->

    <!-- Side Vertical Navbar -->
    <div class="vertical-menu mm-active">

        <div data-simplebar="init" class="h-100 mm-show">
            <div class="simplebar-wrapper" style="margin: 0px;">
                <div class="simplebar-height-auto-observer-wrapper">
                    <div class="simplebar-height-auto-observer"></div>
                </div>
                <div class="simplebar-mask">
                    <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                        <div class="simplebar-content-wrapper" style="height: 100%; overflow: hidden;">
                            <div class="simplebar-content" style="padding: 0px;">

                                <!--- Sidemenu -->
                                <div id="sidebar-menu" class="mm-active">
                                    <!-- Left Menu Start -->
                                    <ul class="metismenu list-unstyled mm-show" id="side-menu">
                                        <li class="menu-title">Menu</li>

                                        <li class="mm-active">
                                            <a href="{{ url('admin/dashboard') }}" class="waves-effect">
                                                <i class="ri-dashboard-line"></i>
                                                <span>Dashboard</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-group-line"></i>
                                                <span>Clients</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a
                                                        href="{{ url('admin/clients/addnewclient') }}">{{ __('admin.clientsaddnew') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/clients/cancellationrequests') }}">{{ __('admin.clientscancelrequests') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/clients/domainregistrations') }}">{{ __('admin.serviceslistdomains') }}</a>
                                                </li> 
                                                <li><a
                                                        href="{{ url('admin/clients/viewclients') }}">{{ __('admin.clientsviewsearchalt') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/clients/manageaffiliates') }}">{{ __('admin.affiiatesmanage') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/clients/massmail') }}">{{ __('admin.permissions21') }}</a>
                                                </li>
                                                <li><a href="{{ url('admin/clients/productservices/product/index') }}">{{ __('admin.clientsummaryproductsalt') }}</a>
                                                    <!--<ul class="sub-menu mm-collapse" aria-expanded="true">-->
                                                    <!--    <li>-->
                                                    <!--        <a-->
                                                    <!--            href="{{ url('admin/clients/productservices/product/index') }}">Service-->
                                                    <!--            List</a>-->
                                                    <!--    </li>-->
                                                        <!--<li><a-->
                                                        <!--        href="{{ url('admin/clients/productservices/product/sharedhosting') }}">{{ __('admin.serviceslisthosting') }}</a>-->
                                                        <!--</li>-->
                                                        <!--<li><a-->
                                                        <!--        href="{{ url('admin/clients/productservices/product/reselleraccount') }}">-->
                                                        <!--        {{ __('admin.serviceslistreseller') }}</a>-->
                                                        <!--</li>-->
                                                        <!--<li><a-->
                                                        <!--        href="{{ url('admin/clients/productservices/product/vpsservers') }}">-->
                                                        <!--        {{ __('admin.serviceslistservers') }}</a>-->
                                                        <!--</li>-->
                                                        <!--<li><a-->
                                                        <!--        href="{{ url('admin/clients/productservices/product/otherservices') }}">{{ __('admin.serviceslistother') }}</a>-->
                                                        <!--</li> -->
                                                    <!--</ul>-->
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/clients/serviceaddons') }}">{{ __('admin.serviceslistaddons') }}</a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-shopping-cart-line"></i>
                                                <span>{{ __('admin.orderstitle') }}</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a
                                                        href="{{ url('admin/orders/list-allorder') }}">{{ __('admin.orderslistall') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/orders/add-order') }}">{{ __('admin.ordersaddnew') }}</a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-money-dollar-circle-line"></i>
                                                <span>{{ __('admin.billingtitle') }}</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li> <a
                                                        href="{{ url('admin/billing/transactionlist') }}">{{ __('admin.billingtransactionslist') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/billing/invoices') }}">{{ __('admin.invoicestitle') }}</a>
                                                </li>
                                                <li>
                                                    <a href="javascript: void(0);"
                                                        class="has-arrow waves-effect">{{ __('admin.clientsummarybillableitems') }}</a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/billing/billableitemlist/add') }}">{{ __('admin.billableitemsaddnew') }}</a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ url('admin/billing/billableitemlist') }}">{{ __('admin.clientsummarybillableitems') }}
                                                                List</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                {{-- <li>
                                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                        {{ __('admin.clientsummaryquotes') }}</a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/billing/quotes') }}">{{ __('admin.quoteslistall') }}</a>
                                                        </li>
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/billing/quotes/add') }}">{{ __('admin.quotescreatenew') }}</a>
                                                        </li>
                                                        <li>
                                                            <a href="#">{{ __('admin.statusvalid') }}</a>
                                                        </li>
                                                        <li><a href="#">{{ __('admin.statusexpired') }}</a></li>
                                                    </ul>
                                                </li> --}}
                                                <li>
                                                    <a
                                                        href="{{ url('admin/billing/offlineccprocessing') }}">{{ __('admin.offlineccptitle') }}</a>
                                                </li>
                                                <li>
                                                    <a
                                                        href="{{ url('admin/billing/gatewaylog') }}">{{ __('admin.billinggatewaylog') }}</a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-book-open-line"></i>
                                                <span>{{ __('admin.supporttitle') }}</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a
                                                        href="{{ url('admin/support/supportoverview') }}">{{ __('admin.supportsupportoverview') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/support/announcements') }}">{{ __('admin.supportannouncements') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/support/downloads') }}">{{ __('admin.supportdownloads') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/support/knowledgebase') }}">{{ __('admin.supportknowledgebase') }}</a>
                                                </li>
                                                <li>
                                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                        {{ __('admin.supportsupporttickets') }}
                                                    </a>
                                                    {{-- <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li> <a
                                                                href="{{ url('admin/support/supporttickets') }}">{{ __('admin.supportsupportticketsall') }}</a>
                                                        </li>
                                                        <li> <a
                                                                href="{{ url('admin/support/supporttickets/open') }}">{{ __('admin.supportsupportticketsopen') }}</a>
                                                        </li>
                                                        <li> <a
                                                                href="{{ url('admin/support/supporttickets/answered') }}">{{ __('admin.supportsupportticketsanswered') }}</a>
                                                        </li>
                                                        <li> <a
                                                                href="{{ url('admin/support/supporttickets/onhold') }}">{{ __('admin.supportsupportticketsonhold') }}</a>
                                                        </li>
                                                        <li> <a
                                                                href="{{ url('admin/support/supporttickets/inprogress') }}">{{ __('admin.supportsupportticketsinprogress') }}</a>
                                                        </li>
                                                        <li> <a
                                                                href="{{ url('admin/support/supporttickets/customerreply') }}">{{ __('admin.supportsupportticketscustomerreply') }}</a>
                                                        </li>
                                                        <li> <a
                                                                href="{{ url('admin/support/supporttickets/closed') }}">{{ __('admin.supportsupportticketsclosed') }}</a>
                                                        </li>
                                                    </ul> --}}

                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                      <li>
                                                          <a href="{{ route('admin.pages.support.supporttickets.index') }}">
                                                              {{ __('admin.supportsupportticketsall') }}
                                                          </a>
                                                      </li>
                                                      <li>
                                                          <a href="{{ route('admin.pages.support.supporttickets.index', ['status' => 'open']) }}">
                                                              {{ __('admin.supportsupportticketsopen') }}
                                                          </a>
                                                      </li>
                                                      <li>
                                                          <a href="{{ route('admin.pages.support.supporttickets.index', ['status' => 'answered']) }}">
                                                              {{ __('admin.supportsupportticketsanswered') }}
                                                          </a>
                                                      </li>
                                                      <li>
                                                          <a href="{{ route('admin.pages.support.supporttickets.index', ['status' => 'onhold']) }}">
                                                              {{ __('admin.supportsupportticketsonhold') }}
                                                          </a>
                                                      </li>
                                                      <li>
                                                          <a href="{{ route('admin.pages.support.supporttickets.index', ['status' => 'inprogress']) }}">
                                                              {{ __('admin.supportsupportticketsinprogress') }}
                                                          </a>
                                                      </li>
                                                      <li>
                                                          <a href="{{ route('admin.pages.support.supporttickets.index', ['status' => 'customerreply']) }}">
                                                              {{ __('admin.supportsupportticketscustomerreply') }}
                                                          </a>
                                                      </li>
                                                      <li>
                                                          <a href="{{ route('admin.pages.support.supporttickets.index', ['status' => 'closed']) }}">
                                                              {{ __('admin.supportsupportticketsclosed') }}
                                                          </a>
                                                      </li>
                                                  </ul>

                                                </li>
                                                <li><a
                                                        href="{{ url('admin/support/opennewtickets') }}">{{ __('admin.supportopennewticket') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/support/predefinedreplies') }}">{{ __('admin.supportpredefreplies') }}</a>
                                                </li>
                                                {{-- <li>
                                       <a href="javascript: void(0);" class="has-arrow waves-effect">
                                          {{ __('admin.networkissuestitle') }}
                                       </a>
                                       <ul class="sub-menu mm-collapse" aria-expanded="false">
                                          <li>
                                             <a
                                                href="{{ url('admin/support/networkissues') }}">{{ __('admin.networkissuesopen') }}</a>
                                          </li>
                                          <li>
                                             <a
                                                href="{{ url('admin/support/networkissues/add') }}">{{ __('admin.networkissuesaddnew') }}</a>
                                          </li>
                                       </ul>
                                    </li> --}}
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-settings-line"></i>
                                                <span>{{ __('admin.setuptitle') }}</span>
                                            </a>
                                            <ul class="sub-menu" aria-expanded="false">
                                                <li>
                                                    <a
                                                        href="{{ url('admin/setup/generalsettings') }}">{{ __('admin.setupgeneral') }}</a>
                                                </li>
                                                {{-- <li>
                                                    <a
                                                        href="{{ url('admin/setup/appsintegrations') }}">{{ __('admin.setupappsAndIntegrations') }}</a>
                                                </li> --}}
                                                {{-- <li>
                                                    <a
                                                        href="{{ url('admin/setup/signinintegrations') }}">{{ __('admin.setupsignInIntegrations') }}</a>
                                                </li> --}}
                                                <li>
                                                    <a
                                                        href="{{ route('admin.configauto') }}">{{ __('admin.setupautomation') }}</a>
                                                </li>
                                                {{-- <li>
                                                    <a
                                                        href="{{ url('admin/setup/marketconnect') }}">{{ __('admin.setupmarketconnect') }}</a>
                                                </li> --}}
                                                {{-- <li>
                                                    <a
                                                        href="{{ url('admin/setup/notifications') }}">{{ __('admin.setupnotifications') }}</a>
                                                </li> --}}
                                                <li>
                                                    <a
                                                        href="{{ url('admin/setup/storagesettings') }}">{{ __('admin.setupstorage') }}</a>
                                                </li>
                                                <li>
                                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                        {{ __('admin.setupstaff') }}
                                                    </a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/setup/staffmanagement/administratorusers') }}">{{ __('admin.setupadmins') }}</a>
                                                        </li>
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/setup/staffmanagement/administratorroles') }}">{{ __('admin.setupadminroles') }}</a>
                                                        </li>
                                                        {{-- <li>
                                                            <a
                                                                href="{{ url('admin/setup/staffmanagement/2fa') }}">{{ __('admin.setuptwofa') }}</a>
                                                        </li> --}}
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/setup/staffmanagement/api', []) }}">{{ __('admin.setupapicredentials') }}</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li>
                                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                        {{ __('admin.setuppayments') }}
                                                    </a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/setup/payments/currencies') }}">{{ __('admin.setupcurrencies') }}</a>
                                                        </li>
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/setup/payments/paymentgateways') }}">{{ __('admin.setupgateways') }}</a>
                                                        </li>
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/setup/payments/taxconfiguration') }}">{{ __('admin.setuptax') }}</a>
                                                        </li>
                                                        <li>
                                                            <a
                                                                href="{{ url('admin/setup/payments/promotions') }}">{{ __('admin.setuppromos') }}</a>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li>
                                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                        Products Config
                                                    </a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li><a
                                                                href="{{ url('admin/setup/productservices') }}">{{ __('admin.setupproducts') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/productservices/configurableoptions') }}">{{ __('admin.setupconfigoptions') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/productservices/productaddons') }}">{{ __('admin.setupaddons') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/productservices/productbundles') }}">{{ __('admin.setupbundles') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/productservices/domainpricing') }}">{{ __('admin.setupdomainpricing') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/productservices/domainregistrars', []) }}">{{ __('admin.setupregistrars') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/productservices/serverconfig') }}">{{ __('admin.setupservers') }}</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li>
                                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                        {{ __('admin.supporttitle') }}
                                                    </a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li><a
                                                                href="{{ url('admin/setup/support/configticketdepartments') }}">{{ __('admin.setupsupportdepartments') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/support/ticketstatuses') }}">{{ __('admin.setupticketstatuses') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/support/escalationrules') }}">{{ __('admin.setupescalationrules') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/support/spamcontrol') }}">{{ __('admin.setupspam') }}</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                {{-- <li><a
                                                        href="{{ url('admin/setup/applicationlinks') }}">{{ __('admin.setupapplicationLinks') }}</a>
                                                </li> --}}
                                                {{-- <li><a
                                                        href="{{ url('admin/setup/openidconnect') }}">{{ __('admin.setupopenIdConnect') }}</a>
                                                </li> --}}
                                                <li><a
                                                        href="{{ url('admin/setup/emailtemplates') }}">{{ __('admin.emailtplstitle') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/setup/addonsmodule') }}">{{ __('admin.setupaddonmodules') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/setup/clientgroups') }}">{{ __('admin.setupclientgroups') }}</a>
                                                </li>
                                                <li><a
                                                        href="{{ url('admin/setup/customclientfields') }}">{{ __('admin.setupcustomclientfields') }}</a>
                                                </li>
                                                {{-- <li><a
                                                        href="{{ url('admin/setup/fraudprotection') }}">{{ __('admin.setupfraud') }}</a>
                                                </li> --}}

                                                <li>
                                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                        {{ __('admin.setupother') }}
                                                    </a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li><a
                                                                href="{{ url('admin/setup/other/orderstatuses') }}">{{ __('admin.setuporderstatuses') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/other/securityquestions') }}">{{ __('admin.setupsecurityqs') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/other/bannedips') }}">{{ __('admin.setupbannedips') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/other/bannedemails') }}">{{ __('admin.setupbannedemails') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/other/databasebackups') }}">{{ __('admin.setupbackups') }}</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li>
                                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                        {{ __('admin.logs') }}
                                                    </a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                        <li><a
                                                                href="{{ url('admin/setup/log/activitylog') }}">{{ __('admin.activitylog') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/log/adminlog') }}">{{ __('admin.adminlog') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/log/modulelog') }}">{{ __('admin.modulelog') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/log/emailmessagelog') }}">{{ __('admin.emailmessagelog') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/log/ticketmailimportlog') }}">{{ __('admin.ticketmailimportlog') }}</a>
                                                        </li>
                                                        <li><a
                                                                href="{{ url('admin/setup/log/whoislookuplog') }}">{{ __('admin.whoislookuplog') }}</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-rocket-line"></i>
                                                <span>{{ __('Addons') }}</span>
                                            </a>
                                            @php
                                                $addonmodules = \App\Helpers\AddonModule::adminSidebarOutput();
                                            @endphp
                                            @if (count($addonmodules))
                                                <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                    @foreach ($addonmodules as $addonmodule)
                                                        @php
                                                            $activeClass = Request::get('module') == $addonmodule['module'] ? 'mm-active' : '';
                                                        @endphp
                                                        <li class="{{ $activeClass }}">
                                                            <a href="{{ $addonmodule['link'] }}"
                                                                class="{{ $activeClass }}">{!! $addonmodule['name'] !!}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>

                                        {{-- <li class="menu-title">Pages</li> --}}

                                        {{-- <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-account-circle-line"></i>
                                                <span>Authentication</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a href="auth-login.html">Login</a></li>
                                                <li><a href="auth-register.html">Register</a></li>
                                                <li><a href="auth-recoverpw.html">Recover Password</a></li>
                                                <li><a href="auth-lock-screen.html">Lock Screen</a></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-profile-line"></i>
                                                <span>Utility</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a href="pages-starter.html">Starter Page</a></li>
                                                <li><a href="pages-maintenance.html">Maintenance</a></li>
                                                <li><a href="pages-comingsoon.html">Coming Soon</a></li>
                                                <li><a href="pages-timeline.html">Timeline</a></li>
                                                <li><a href="pages-faqs.html">FAQs</a></li>
                                                <li><a href="pages-pricing.html">Pricing</a></li>
                                                <li><a href="pages-404.html">Error 404</a></li>
                                                <li><a href="pages-500.html">Error 500</a></li>
                                            </ul>
                                        </li>

                                        <li class="menu-title">Components</li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-pencil-ruler-2-line"></i>
                                                <span>UI Elements</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a href="ui-alerts.html">Alerts</a></li>
                                                <li><a href="ui-buttons.html">Buttons</a></li>
                                                <li><a href="ui-cards.html">Cards</a></li>
                                                <li><a href="ui-carousel.html">Carousel</a></li>
                                                <li><a href="ui-dropdowns.html">Dropdowns</a></li>
                                                <li><a href="ui-grid.html">Grid</a></li>
                                                <li><a href="ui-images.html">Images</a></li>
                                                <li><a href="ui-lightbox.html">Lightbox</a></li>
                                                <li><a href="ui-modals.html">Modals</a></li>
                                                <li><a href="ui-rangeslider.html">Range Slider</a></li>
                                                <li><a href="ui-roundslider.html">Round Slider</a></li>
                                                <li><a href="ui-session-timeout.html">Session Timeout</a></li>
                                                <li><a href="ui-progressbars.html">Progress Bars</a></li>
                                                <li><a href="ui-sweet-alert.html">Sweet Alerts</a></li>
                                                <li><a href="ui-tabs-accordions.html">Tabs &amp; Accordions</a></li>
                                                <li><a href="ui-typography.html">Typography</a></li>
                                                <li><a href="ui-video.html">Video</a></li>
                                                <li><a href="ui-general.html">General</a></li>
                                                <li><a href="ui-rating.html">Rating</a></li>
                                                <li><a href="ui-notifications.html">Notifications</a></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="waves-effect">
                                                <i class="ri-eraser-fill"></i>
                                                <span class="badge badge-pill badge-danger float-right">6</span>
                                                <span>Forms</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a href="form-elements.html">Elements</a></li>
                                                <li><a href="form-validation.html">Validation</a></li>
                                                <li><a href="form-advanced.html">Advanced Plugins</a></li>
                                                <li><a href="form-editors.html">Editors</a></li>
                                                <li><a href="form-uploads.html">File Upload</a></li>
                                                <li><a href="form-xeditable.html">X-editable</a></li>
                                                <li><a href="form-wizard.html">Wizard</a></li>
                                                <li><a href="form-mask.html">Mask</a></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-table-2"></i>
                                                <span>Tables</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a href="tables-basic.html">Basic Tables</a></li>
                                                <li><a href="tables-datatable.html">Data Tables</a></li>
                                                <li><a href="tables-responsive.html">Responsive Table</a></li>
                                                <li><a href="tables-editable.html">Editable Table</a></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-bar-chart-line"></i>
                                                <span>Charts</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a href="charts-apex.html">Apexcharts</a></li>
                                                <li><a href="charts-chartjs.html">Chartjs</a></li>
                                                <li><a href="charts-flot.html">Flot</a></li>
                                                <li><a href="charts-knob.html">Jquery Knob</a></li>
                                                <li><a href="charts-sparkline.html">Sparkline</a></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-brush-line"></i>
                                                <span>Icons</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a href="icons-remix.html">Remix Icons</a></li>
                                                <li><a href="icons-materialdesign.html">Material Design</a></li>
                                                <li><a href="icons-dripicons.html">Dripicons</a></li>
                                                <li><a href="icons-fontawesome.html">Font awesome 5</a></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-map-pin-line"></i>
                                                <span>Maps</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="false">
                                                <li><a href="maps-google.html">Google Maps</a></li>
                                                <li><a href="maps-vector.html">Vector Maps</a></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="ri-share-line"></i>
                                                <span>Multi Level</span>
                                            </a>
                                            <ul class="sub-menu mm-collapse" aria-expanded="true">
                                                <li><a href="javascript: void(0);">Level 1.1</a></li>
                                                <li><a href="javascript: void(0);" class="has-arrow">Level 1.2</a>
                                                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                                                        <li><a href="javascript: void(0);">Level 2.1</a></li>
                                                        <li><a href="javascript: void(0);">Level 2.2</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li> --}}

                                    </ul>
                                </div>
                                <!-- Sidebar -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="simplebar-placeholder" style="width: auto; height: 867px;"></div>
            </div>
            <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                <div class="simplebar-scrollbar" style="transform: translate3d(0px, 0px, 0px); display: none;"></div>
            </div>
            <div class="simplebar-track simplebar-vertical" style="visibility: hidden;">
                <div class="simplebar-scrollbar"
                    style="transform: translate3d(0px, 0px, 0px); display: none; height: 797px;"></div>
            </div>
        </div>
    </div>
    <!-- Side Vertical Navbar -->
