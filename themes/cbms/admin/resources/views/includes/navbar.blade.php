</head>

<body data-topbar="dark" data-layout="horizontal">
    <!-- Horizontal navbar -->
    <header id="page-topbar">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box">


                    <a href="{{ url('admin/dashboard') }}" class="logo logo-dark">
                        <span class="logo-xs">
                            <img src="{{ Theme::asset('assets/images/WHMCEPS.png') }}" alt="" height="20" />
                        </span>{{ url('') }}
                        <span class="logo-sm">
                            <img src="{{ Theme::asset('assets/images/WHMCEPS.png') }}" alt="" height="20" />
                        </span>{{ url('') }}
                        <span class="logo-lg">
                            <img src="{{ Theme::asset('assets/images/WHMCEPS.png') }}" alt="" height="20" />
                        </span>
                    </a>

                    <a href="{{ url('admin/dashboard') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ Theme::asset('assets/images/WHMCEPS.png') }}" alt="" height="20" />
                        </span>
                        <span class="logo-lg">
                            <img src="{{ Theme::asset('assets/images/WHMCEPS.png') }}" alt="" height="20" />
                        </span>
                    </a>
                </div>
                <button type="button" class="btn btn-sm px-3 font-size-24 d-lg-none header-item" data-toggle="collapse"
                    data-target="#topnav-menu-content">
                    <i class="ri-menu-2-line align-middle"></i>
                </button>
            </div>

            <div class="d-flex">
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
                                    <input type="text" class="form-control" placeholder="Search ..." />
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="ri-search-line"></i> Search...
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- App Search-->
                <form class="app-search d-none d-lg-block">
                    <div class="position-relative">
                        <input type="text" class="form-control text-dark"
                            placeholder="{{ __('admin.appsnavsearch') }}" />
                        <span class="ri-search-line"></span>
                    </div>
                </form>
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
                                    <h6 class="m-0">{{ __('admin.notificationstitle') }}</h6>
                                </div>
                                <div class="col-auto">
                                    <a href="#!" class="small"> View All</a>
                                </div>
                            </div>
                        </div>

                        <div data-simplebar style="max-height: 230px">
                           <h3>No new notifications</h3>
                        </div>

                        <div class="p-2 border-top">
                            <a class="btn btn-sm btn-link font-size-14 btn-block text-center" href="javascript:void(0)">
                                <i class="mdi mdi-arrow-right-circle mr-1"></i> View More..
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown d-inline-block user-dropdown">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-circle header-profile-user"
                            src="{{ Theme::asset('assets/images/users/avatar-2.jpg') }}" alt="Header Avatar" />
                        <span
                            class="d-none d-xl-inline-block ml-1">{{ Auth::guard('admin')->user()->firstname }}</span>
                        <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <!-- item-->
                        <a class="dropdown-item" href="{{url('/')}}" target="_blank"><i class="ri-user-line align-middle mr-1"></i>
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
    <!-- End Horizontal navbar -->

    <!-- NAVBAR -->
    <div class="topnav">
        <div class="container-fluid d-flex align-items-center">
            <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
                <div class="collapse navbar-collapse" id="topnav-menu-content">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <button class="btn btn-outline-success btn-sm d-none" id="show-btn">
                                Show Sidebar
                            </button>
                        </li>
                        {{-- NavigateLink --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('admin/dashboard') }}">
                                <i class="ri-dashboard-line"></i> {{ __('admin.hometitle') }}
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none"
                                href="{{ url('admin/clients/viewclients') }}" id="topnav-uielement" role="button"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="ri-user-3-line mr-2"></i>{{ __('admin.clientstitle') }}
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-uielement">
                                <a href="{{ url('admin/clients/addnewclient') }}"
                                    class="dropdown-item">{{ __('admin.clientsaddnew') }}</a>
                                <a href="{{ url('admin/clients/cancellationrequests') }}"
                                    class="dropdown-item">{{ __('admin.clientscancelrequests') }}</a>
                                <a href="{{ url('admin/clients/domainregistrations') }}"
                                    class="dropdown-item">{{ __('admin.serviceslistdomains') }}</a>
                                <a href="{{ url('admin/clients/viewclients') }}"
                                    class="dropdown-item">{{ __('admin.clientsviewsearchalt') }}</a>
                                <a href="{{ url('admin/clients/manageaffiliates') }}"
                                    class="dropdown-item">{{ __('admin.affiiatesmanage') }}</a>
                                <a href="{{ url('admin/clients/massmail') }}"
                                    class="dropdown-item">{{ __('admin.permissions21') }}</a>
                                {{-- For Desktop --}}
                                <div class="dropdown" id="desktop-topnav-serv">
                                    <a href="{{ url('admin/clients/productservices') }}">
                                        <div class=" dropdown-item dropdown-toggle arrow-none" role="button"
                                            id="topnav-serv" aria-haspopup="true" aria-expanded="false">
                                            {{ __('admin.clientsummaryproductsalt') }}
                                            <div class="arrow-down p-0 m-0"></div>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-serv">
                                        <a href="{{ url('admin/clients/productservices/sharedhosting') }}"
                                            class="dropdown-item">{{ __('admin.serviceslisthosting') }}</a>
                                        <a href="{{ url('admin/clients/productservices/reselleraccount') }}"
                                            class="dropdown-item">{{ __('admin.serviceslistreseller') }}</a>
                                        <a href="{{ url('admin/clients/productservices/vpsservers') }}"
                                            class="dropdown-item">{{ __('admin.serviceslistservers') }}</a>
                                        <a href="{{ url('admin/clients/productservices/otherservices') }}"
                                            class="dropdown-item">{{ __('admin.serviceslistother') }}</a>
                                    </div>
                                </div>
                                {{-- For Mobile --}}
                                <div class="dropdown" id="mobile-topnav-serv">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-serv-2"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.clientsummaryproductsalt') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-serv-2">
                                        <a href="{{ url('admin/clients/productservices/sharedhosting') }}"
                                            class="dropdown-item">{{ __('admin.serviceslisthosting') }}</a>
                                        <a href="{{ url('admin/clients/productservices/reselleraccount') }}"
                                            class="dropdown-item">{{ __('admin.serviceslistreseller') }}</a>
                                        <a href="{{ url('admin/clients/productservices/vpsservers') }}"
                                            class="dropdown-item">{{ __('admin.serviceslistservers') }}</a>
                                        <a href="{{ url('admin/clients/productservices/otherservices') }}"
                                            class="dropdown-item">{{ __('admin.serviceslistother') }}</a>
                                    </div>
                                </div>
                                <a href="{{ url('admin/clients/serviceaddons') }}"
                                    class="dropdown-item">{{ __('admin.serviceslistaddons') }}</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-order" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-apps-2-line mr-2"></i>{{ __('admin.orderstitle') }}
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-order">
                                <a href="{{ url('admin/orders/add') }}"
                                    class="dropdown-item">{{ __('admin.ordersaddnew') }}</a>
                                <a href="{{ url('admin/orders/') }}"
                                    class="dropdown-item">{{ __('admin.orderslistall') }}</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-components" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-bill-line mr-2"></i>{{ __('admin.billingtitle') }}
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-components">
                                <a href="{{ url('admin/billing/transactionlist') }}"
                                    class="dropdown-item">{{ __('admin.billingtransactionslist') }}</a>

                                <div class="dropdown">
                                    <a href="{{ url('admin/billing/invoices') }}">
                                        <div class="dropdown-item dropdown-toggle arrow-none" id=" topnav-invoice"
                                            role="button" aria-haspopup="true" aria-expanded="false">
                                            {{ __('admin.invoicestitle') }}
                                            {{-- <div class="arrow-down"></div> --}}
                                        </div>
                                    </a>
                                    {{-- <div class="dropdown-menu" aria-labelledby="topnav-invoice">
                                        <a href="invoices-all-list.html"
                                            class="dropdown-item">{{ __('admin.invoicestitle') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.statusdraft') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.statusunpaid') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.statusoverdue') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.statuscancelled') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.statusrefunded') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.statuscollections') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.statuspaymentpending') }}</a>
                                    </div> --}}
                                </div>

                                <div class="dropdown">
                                    <a href="{{ url('admin/billing/billableitemlist') }}">
                                        <div class="dropdown-item dropdown-toggle arrow-none" href="#"
                                            id="topnav-billable" role="button" aria-haspopup="true"
                                            aria-expanded="false">
                                            {{ __('admin.clientsummarybillableitems') }}
                                            <div class="arrow-down"></div>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-billable">
                                        <a href="#"
                                            class="dropdown-item">{{ __('admin.billableitemsuninvoiced') }}</a>
                                        <a href="#"
                                            class="dropdown-item">{{ __('admin.billableitemsrecurring') }}</a>
                                        <a href="{{ url('admin/billing/billableitemlist/add') }}"
                                            class="dropdown-item">{{ __('admin.billableitemsaddnew') }}</a>
                                    </div>
                                </div>

                                <div class="dropdown">
                                    <a href="{{ url('admin/billing/quotes') }}">
                                        <div class="dropdown-item dropdown-toggle arrow-none" id="topnav-quotes"
                                            role="button" aria-haspopup="true" aria-expanded="false">
                                            {{ __('admin.clientsummaryquotes') }}
                                            <div class="arrow-down"></div>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-quotes">
                                        <a href="#" class="dropdown-item">{{ __('admin.statusvalid') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.statusexpired') }}</a>
                                        <a href="{{ url('admin/billing/quotes/add') }}"
                                            class="dropdown-item">{{ __('admin.quotescreatenew') }}</a>
                                    </div>
                                </div>

                                <a href="{{ url('admin/billing/offlineccprocessing') }}"
                                    class="dropdown-item">{{ __('admin.offlineccptitle') }}</a>
                                <a href="{{ url('admin/billing/gatewaylog') }}"
                                    class="dropdown-item">{{ __('admin.billinggatewaylog') }}</a>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-support" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-chat-smile-line mr-2"></i>{{ __('admin.supporttitle') }}
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-support">
                                <a href="{{ url('admin/support/supportoverview') }}"
                                    class="dropdown-item">{{ __('admin.supportsupportoverview') }}</a>
                                <a href="{{ url('admin/support/announcements') }}"
                                    class="dropdown-item">{{ __('admin.supportannouncements') }}</a>
                                <a href="{{ url('admin/support/downloads') }}"
                                    class="dropdown-item">{{ __('admin.supportdownloads') }}</a>
                                <a href="{{ url('admin/support/knowledgebase') }}"
                                    class="dropdown-item">{{ __('admin.supportknowledgebase') }}</a>
                                <a href="{{ url('admin/support/supporttickets') }}"
                                    class="dropdown-item">{{ __('admin.supportsupporttickets') }}</a>
                                {{-- <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="support-tickets-list.html"
                                        id="topnav-supp-ticket" role="button" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.supportsupporttickets') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-supp-ticket">
                                        <a href="#" class="dropdown-item">{{ __('admin.supportflagged') }}</a>
                                        <a href="support-tickets-list.html"
                                            class="dropdown-item">{{ __('admin.supportallactive') }}</a>
                                        <a href="#" class="dropdown-item">Open</a>
                                        <a href="#" class="dropdown-item">Answered</a>
                                        <a href="#" class="dropdown-item">Customer-Reply</a>
                                        <a href="#" class="dropdown-item">On Hold</a>
                                        <a href="#" class="dropdown-item">In Progress</a>
                                        <a href="#" class="dropdown-item">Closed</a>
                                    </div>
                                </div> --}}
                                <a href="{{ url('admin/support/opennewtickets') }}"
                                    class="dropdown-item">{{ __('admin.supportopennewticket') }}</a>
                                <a href="{{ url('admin/support/predefinedreplies') }}"
                                    class="dropdown-item">{{ __('admin.supportpredefreplies') }}</a>

                                <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-net-issues"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.networkissuestitle') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-net-issues">
                                        <a href="{{ url('admin/support/networkissues') }}"
                                            class="dropdown-item">{{ __('admin.networkissuesopen') }}</a>
                                        <a href="{{ url('admin/support/networkissues/add') }}"
                                            class="dropdown-item">{{ __('admin.networkissuesaddnew') }}</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        {{-- <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-reports" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-alarm-warning-line mr-2"></i>{{ __('admin.reportstitle') }}
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-reports">
                                <a href="#" class="dropdown-item">Daily Performance</a>
                                <a href="#" class="dropdown-item">{{ __('admin.homeincomeforecast') }}</a>
                                <a href="#" class="dropdown-item">Annual Income Report</a>
                                <a href="#" class="dropdown-item">New Customers</a>
                                <a href="#" class="dropdown-item">Ticket Feedback Scores</a>
                                <a href="#" class="dropdown-item">Batch Invoice PDF Export</a>
                                <a href="#" class="dropdown-item text-info">More...</a>
                            </div>
                        </li> --}}

                        {{-- <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-utilities" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-sound-module-line mr-2"></i>{{ __('admin.utilitiestitle') }}
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-utilities">
                                <a href="#" class="dropdown-item">{{ __('admin.updatetitle') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.whmcsConnectwhmcsConnectName') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitiesmoduleQueue') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitiestldImport') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitiesemailmarketer') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitieslinktracking') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitiescalendar') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitiestodolist') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitieswhois') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitiesdomainresolver') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.utilitiesintegrationcode') }}</a>
                                <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-system"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.utilitiessystem') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-system" id="system-child">
                                        <a href="#"
                                            class="dropdown-item">{{ __('admin.utilitiesautomationStatus') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitiesdbstatus') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitiessyscleanup') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitiesphpinfo') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitiesphpcompat') }}</a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-logs"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.utilitieslogs') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-logs" id="logs-child">
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitiesactivitylog') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitiesadminlog') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitiesmodulelog') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitiesemaillog') }}</a>
                                        <a href="#" class="dropdown-item">{{ __('admin.utilitieswhoislog') }}<a>
                                    </div>
                                </div>
                            </div>
                        </li> --}}

                        {{-- <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle arrow-non" id="topnav-addons" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-external-link-line mr-2"></i>Addons
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-addons" id="addons-parent">
                                <a href="#" class="dropdown-item">Apps & Integration</a>
                                <a href="#" class="dropdown-item">Boost Power</a>
                                <a href="#" class="dropdown-item">Bulk Change Addon Price</a>
                                <a href="#" class="dropdown-item">Bulk Change Product Price</a>
                                <a href="#" class="dropdown-item">Activity Log</a>
                                <a href="#" class="dropdown-item">FDS recurring credit card</a>
                                <a href="#" class="dropdown-item">GSuite Manage User</a>
                                <a href="#" class="dropdown-item">Migrasi Hosting</a>
                                <a href="#" class="dropdown-item">Point QA</a>
                                <a href="#" class="dropdown-item">DNSSEC Manager</a>
                                <a href="#" class="dropdown-item">PrivateNS Registrar</a>
                                <a href="#" class="dropdown-item">PrivateNS DNS Manager</a>
                                <a href="#" class="dropdown-item">PrivateNS Document</a>
                                <a href="#" class="dropdown-item">Product Monitoring</a>
                                <a href="#" class="dropdown-item">Project Management</a>
                                <a href="#" class="dropdown-item">Reward Points</a>
                                <a href="#" class="dropdown-item">Voucher QA</a>
                            </div>
                        </li> --}}


                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle arrow-non" id="topnav-setup" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-settings-line mr-2"></i>{{ __('admin.setuptitle') }}
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-setup" id="setup-parent">
                                <a href="{{ url('admin/setup/generalsettings') }}"
                                    class="dropdown-item">{{ __('admin.setupgeneral') }}</a>
                                <a href="{{ url('admin/setup/appsintegrations') }}"
                                    class="dropdown-item">{{ __('admin.setupappsAndIntegrations') }}</a>
                                <a href="{{ url('admin/setup/signinintegrations') }}"
                                    class="dropdown-item">{{ __('admin.setupsignInIntegrations') }}</a>
                                <a href="{{ url('admin/setup/automationsettings') }}"
                                    class="dropdown-item">{{ __('admin.setupautomation') }}</a>
                                <a href="{{ url('admin/setup/marketconnect') }}"
                                    class="dropdown-item">{{ __('admin.setupmarketconnect') }}</a>
                                <a href="{{ url('admin/setup/notifications') }}"
                                    class="dropdown-item">{{ __('admin.setupnotifications') }}</a>
                                <a href="{{ url('admin/setup/storagesettings') }}"
                                    class="dropdown-item">{{ __('admin.setupstorage') }}</a>

                                <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-staff"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.setupstaff') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-staff">
                                        <a href="{{ url('admin/setup/staffmanagement/administratorusers') }}"
                                            class="dropdown-item">{{ __('admin.setupadmins') }}</a>
                                        <a href="{{ url('admin/setup/staffmanagement/administratorroles') }}"
                                            class="dropdown-item">{{ __('admin.setupadminroles') }}</a>
                                        <a href="{{ url('admin/setup/staffmanagement/2fa') }}"
                                            class="dropdown-item">{{ __('admin.setuptwofa') }}</a>
                                        <a href="{{ url('admin/setup/staffmanagement/api', []) }}"
                                            class="dropdown-item">{{ __('admin.setupapicredentials') }}</a>
                                    </div>
                                </div>

                                <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-pay"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.setuppayments') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-pay">
                                        <a href="{{ url('admin/setup/payments/currencies') }}"
                                            class="dropdown-item">{{ __('admin.setupcurrencies') }}</a>
                                        <a href="{{ url('admin/setup/payments/paymentgateways') }}"
                                            class="dropdown-item">{{ __('admin.setupgateways') }}</a>
                                        <a href="{{ url('admin/setup/payments/taxconfiguration') }}"
                                            class="dropdown-item">{{ __('admin.setuptax') }}</a>
                                        <a href="{{ url('admin/setup/payments/promotions') }}"
                                            class="dropdown-item">{{ __('admin.setuppromos') }}</a>
                                    </div>
                                </div>

                                <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-prodserv"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Products/Services
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-prodserv">
                                        <a href="{{ url('admin/setup/prodsservices/productservices') }}"
                                            class="dropdown-item">{{ __('admin.setupproducts') }}</a>
                                        <a href="{{ url('admin/setup/prodsservices/configurableoptions') }}"
                                            class="dropdown-item">{{ __('admin.setupconfigoptions') }}</a>
                                        <a href="{{ url('admin/setup/prodsservices/productaddons') }}"
                                            class="dropdown-item">{{ __('admin.setupaddons') }}</a>
                                        <a href="{{ url('admin/setup/prodsservices/productbundles') }}"
                                            class="dropdown-item">{{ __('admin.setupbundles') }}</a>
                                        <a href="{{ url('admin/setup/prodsservices/domainpricing') }}"
                                            class="dropdown-item">{{ __('admin.setupdomainpricing') }}</a>
                                        <a href="{{ url('admin/setup/prodsservices/domainregistrars', []) }}"
                                            class="dropdown-item">{{ __('admin.setupregistrars') }}</a>
                                        <a href="{{ url('admin/setup/prodsservices/serverconfig') }}"
                                            class="dropdown-item">{{ __('admin.setupservers') }}</a>
                                    </div>
                                </div>

                                <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-supp-2"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.supporttitle') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-logs">
                                        <a href="{{ url('admin/setup/support/configticketdepartments') }}"
                                            class="dropdown-item">{{ __('admin.setupsupportdepartments') }}</a>
                                        <a href="{{ url('admin/setup/support/ticketstatuses') }}"
                                            class="dropdown-item">{{ __('admin.setupticketstatuses') }}</a>
                                        <a href="{{ url('admin/setup/support/escalationrules') }}"
                                            class="dropdown-item">{{ __('admin.setupescalationrules') }}</a>
                                        <a href="{{ url('admin/setup/support/spamcontrol') }}"
                                            class="dropdown-item">{{ __('admin.setupspam') }}</a>
                                    </div>
                                </div>

                                <a href="{{ url('admin/setup/applicationlinks') }}"
                                    class="dropdown-item">{{ __('admin.setupapplicationLinks') }}</a>
                                <a href="{{ url('admin/setup/openidconnect') }}"
                                    class="dropdown-item">{{ __('admin.setupopenIdConnect') }}</a>
                                <a href="{{ url('admin/setup/emailtemplates') }}"
                                    class="dropdown-item">{{ __('admin.emailtplstitle') }}</a>
                                <a href="{{ url('admin/setup/addonsmodule') }}"
                                    class="dropdown-item">{{ __('admin.setupaddonmodules') }}</a>
                                <a href="{{ url('admin/setup/clientgroups') }}"
                                    class="dropdown-item">{{ __('admin.setupclientgroups') }}</a>
                                <a href="{{ url('admin/setup/customclientfields') }}"
                                    class="dropdown-item">{{ __('admin.setupcustomclientfields') }}</a>
                                <a href="{{ url('admin/setup/fraudprotection') }}"
                                    class="dropdown-item">{{ __('admin.setupfraud') }}</a>

                                <div class="dropdown">
                                    <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-oth"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('admin.setupother') }}
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-oth" id="other-child">
                                        <a href="{{ url('admin/setup/other/orderstatuses') }}"
                                            class="dropdown-item">{{ __('admin.setuporderstatuses') }}</a>
                                        <a href="{{ url('admin/setup/other/securityquestions') }}"
                                            class="dropdown-item">{{ __('admin.setupsecurityqs') }}</a>
                                        <a href="{{ url('admin/setup/other/bannedips') }}"
                                            class="dropdown-item">{{ __('admin.setupbannedips') }}</a>
                                        <a href="{{ url('admin/setup/other/bannedemails') }}"
                                            class="dropdown-item">{{ __('admin.setupbannedemails') }}</a>
                                        <a href="{{ url('admin/setup/other/databasebackups') }}"
                                            class="dropdown-item">{{ __('admin.setupbackups') }}</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle arrow-non" id="topnav-help" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-question-line mr-2"></i>{{ __('admin.helptitle') }}
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-help">
                                <a href="#" class="dropdown-item">{{ __('admin.helpdocs') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.helplicenseinfo') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.helpchangelicense') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.healthCheckmenuTitle') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.whatsNewmenuTitle') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.helpsetupWizard') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.helpsupport') }}</a>
                                <a href="#" class="dropdown-item">{{ __('admin.helpforums') }}</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="dropdown mini-md-info mt-1" id="mini-info">
                <a class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="ri-more-fill font-size-16"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left"
                    aria-labelledby=" dropdownMenuButton" style="min-width: 300px;">
                    <div class="row">
                        <div class="col-lg-12">
                            <a href="{{ url('admin/orders') }}">
                                <div class="mr-2 text-dark d-flex my-2 centering-items drop-item-custom">
                                    <div href="{{ url('admin/orders') }}" class="p-2">
                                        <div class="text-warning font-weight-bold d-flex align-items-center">
                                            <i class="ri-shopping-cart-fill mx-1"></i>533
                                        </div>
                                    </div>
                                    {{ __('admin.statspendingorders') }}
                                </div>
                            </a>
                            <a href="{{ url('admin/billing/invoices') }}">
                                <div class="mr-2 text-dark d-flex my-2 centering-items drop-item-custom">
                                    <div class="p-2">
                                        <div class="text-warning font-weight-bold d-flex align-items-center">
                                            <i class="ri-chat-3-fill mx-1"></i>316
                                        </div>
                                    </div>
                                    {{ __('admin.statsoverdueinvoices') }}
                                </div>
                            </a>
                            <a href="{{ url('admin/support/supporttickets') }}">
                                <div class="mr-2 text-dark d-flex my-2 centering-items drop-item-custom">
                                    <div href="#" class="p-2">
                                        <div class="text-warning font-weight-bold d-flex align-items-center">
                                            <i class="ri-chat-check-fill mx-1"></i>66
                                        </div>
                                    </div>
                                    {{ __('admin.statsticketsawaitingreply') }}
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mini-info ml-auto p-2">
                <div class="row">
                    <div class="col-lg-12 d-flex">
                        <div class="mr-2 text-dark d-flex align-items-center">
                            <a href="{{ url('admin/orders') }}" class="p-2">
                                <div class="text-warning font-weight-bold d-flex align-items-center">
                                    <i class="ri-shopping-cart-fill mx-1"></i>533
                                </div>
                            </a>
                            {{ __('admin.statspendingorders') }}
                        </div>
                        <div class="mr-2 text-dark d-flex align-items-center ">
                            <a href="{{ url('admin/billing/invoices') }}" class="p-2">
                                <div class="text-warning font-weight-bold d-flex align-items-center">
                                    <i class="ri-chat-3-fill mx-1"></i>316
                                </div>
                            </a>
                            {{ __('admin.statsoverdueinvoices') }}
                        </div>
                        <div class="mr-2 text-dark d-flex align-items-center">
                            <a href="{{ url('admin/support/supporttickets') }}" class="p-2">
                                <div class="text-warning font-weight-bold d-flex align-items-center">
                                    <i class="ri-chat-check-fill mx-1"></i>66
                                </div>
                            </a>
                            {{ __('admin.statsticketsawaitingreply') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END OF NAVBAR -->
