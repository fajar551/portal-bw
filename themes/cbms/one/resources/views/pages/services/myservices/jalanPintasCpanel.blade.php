@extends('layouts.clientbase')

@section('title')
    {{ Lang::get('client.jlnpntscpanel') }}
@endsection

@section('tab-title')
    {{ Lang::get('client.jlnpntscpanel') }}
@endsection

@section('content')
    <style>
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            background-color: #ffefe1;
            border-radius: 10px;
            padding: 15px;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .menu-item:hover {
            transform: translateY(-5px);
        }

        .menu-item.boost-power {
            background-color: #e3f2fd;
        }

        .menu-item i {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff7a3d;
        }

        .menu-item.boost-power i {
            color: #2196f3;
        }

        .login-button {
            background-color: #ff7a3d;
            color: white;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .info-card,
        .form-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .info-card h5,
        .form-card h5 {
            margin-bottom: 15px;
            font-weight: bold;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .submit-button {
            background-color: #ff7a3d;
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
        }

        .whois-button {
            background-color: #f5f5f5;
            color: #333;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
        }

        .btn-outline-orange {
            color: #ff7a3d;
            background-color: transparent;
            border: 2px solid #ff7a3d;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-orange:hover {
            color: #fff;
            background-color: #ff7a3d;
            border-color: #ff7a3d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 122, 61, 0.2);
        }

        .btn-outline-orange:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .btn-outline-orange.btn-block {
            width: 100%;
            display: block;
        }

        .loader-container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(255, 255, 255, 0.9) !important;
            display: none;
            justify-content: center !important;
            align-items: center !important;
            z-index: 999999 !important;
            pointer-events: all !important;
        }

        .loader {
            width: 60px !important;
            height: 60px !important;
            position: relative !important;
            z-index: 1000000 !important;
        }

        .loader:before {
            content: '';
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #3498db;
            border-bottom-color: #3498db;
            position: absolute;
            animation: spin 1s linear infinite;
        }

        .loader:after {
            content: '';
            width: 80%;
            height: 80%;
            border-radius: 50%;
            border: 3px solid transparent;
            border-left-color: #3498db;
            border-right-color: #3498db;
            position: absolute;
            top: 10%;
            left: 10%;
            animation: spin 0.8s linear infinite reverse;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .loader-visible {
            display: flex !important;
        }
    </style>

    <div id="pageLoader" class="loader-container">
        <div class="loader"></div>
    </div>

    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 card">
                    <h3 class="mt-2">Jalan Pintas cPanel</h3>
                    <div class="row mb-3">
                        <div class="col-xl-8 col-lg-8">
                            <div class="header-breadcumb">
                                <h6 class="header-pretitle d-none d-md-block mt-2">
                                    <a href="index.html">Services</a>
                                    <a href="{{ route('pages.domain.mydomains.index') }}"> / My Services</a>
                                    <span class="text-muted"> / {{ Lang::get('client.jlnpntscpanel') }} </span>
                                </h6>
                            </div>
                        </div>
                    </div>

                    @php
                        // Debug
                        \Log::info('View serverdata2:', $serverdata2 ?? []);
                    @endphp

                    @if ($serverdata2['debug'] == 'on')
                        <div class="alert alert-info">
                            <strong>Debug Info:</strong><br>
                            cPanel URL: {{ $serverdata2['cpanel_url'] ?? 'Not set' }}<br>
                            cPanel Username: {{ $serverdata2['cpanel_username'] ?? 'Not set' }}<br>
                            cPanel password: {{ $serverdata2['cpanel_password'] ?? 'Not set' }}<br>
                            Debug Mode: {{ $serverdata2['debug'] ? 'on' : 'Off' }}
                        </div>
                    @endif
                    <div class="menu-grid table-responsive">
                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/email_accounts/index.html#/list' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/email_accounts.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelemailAccounts') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/mail/fwds.html' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/forwarders.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelforwarders') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/mail/autores.html' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/autoresponders.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelautoresponders') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/filemanager/index.html' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/file_manager.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelfileManager') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/backup/index.html' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/backup.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelbackup') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/domains/index.html#' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/subdomains.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelsubdomains') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/site_publisher/index.html#/publish' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/addon_domains.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPaneladdonDomains') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/cron/index.html' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/cron_jobs.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelcronJobs') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/sql/index.html' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/mysql_databases.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelmysqlDatabases') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/3rdparty/phpMyAdmin/index.php' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/php_my_admin.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelphpMyAdmin') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/stats/awstats_landing.html' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/awstats.png"
                                      alt="service.png">
                                  <span>{{ Lang::get('client.cPanelawstats') }}</span>
                              </div>
                          </a>
                      </div>

                      <div class="menu-item boost-power justify-content-center">
                          <a href="{{ $serverdata2['cpanel_url'] . '/login/?user=' . urlencode($serverdata2['cpanel_username']) . '&pass=' . urlencode($serverdata2['cpanel_password']) . '&skiptoken=1&goto_uri=/frontend/jupiter/optimize/index.html' }}"
                              target="_blank" class="text-center">
                              <div class="d-flex flex-column align-items-center">
                                  <img class="mb-2"
                                      src="https://my.hostingnvme.id/assets/images/relabs/cPanel/fbp.png"
                                      height="60" width="60" alt="service.png">
                                  <span>{{ Lang::get('client.cPaneloptimizeWebsite') }}</span>
                              </div>
                          </a>
                      </div>

                  </div>

                  <a href="{{ route('pages.services.myservices.cpanellogin', ['id' => $id]) }}"
                      class="btn btn-outline-orange btn-block mb-4 text-center" target="_blank">
                      {{ Lang::get('client.cpanellogin2') }}
                  </a>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <h5 class="font-weight-bold text-center">{{ Lang::get('client.cPanelinfo') }}</h5>
                        <div class="info-row">
                            <span class="font-weight-bold">{{ Lang::get('client.cPanelusername') }}</span>
                            <span>{{ $username }}</span>
                        </div>
                        <div class="info-row">
                            <span class="font-weight-bold">{{ Lang::get('client.cPaneldomain') }}</span>
                            <span>{{ $domain }}</span>
                        </div>
                        <div class="info-row">
                            <span class="font-weight-bold">{{ Lang::get('client.cPanelproduct') }}</span>
                            <span>{{ $product }}</span>
                        </div>
                        <div class="info-row">
                            <span class="font-weight-bold">{{ Lang::get('client.cPanelipaddress') }}</span>
                            {{ $serverdata['ipaddress'] }}
                        </div>
                        @if ($ns1 || $ns2 || $assignedips)
                            <div class="info-row">
                                <span class="font-weight-bold">{{ Lang::get('client.cPanelnameservers') }}</span>
                                <div>
                                    @if ($ns1)
                                        <div>{{ $ns1 ?? 'Empty' }}</div>
                                    @endif
                                    @if ($ns2)
                                        <div>{{ $ns2 ?? 'Empty' }}</div>
                                    @endif
                                    @foreach (explode(',', $assignedips ?? '') as $ns)
                                        @if (trim($ns))
                                            <div>{{ trim($ns) ?? 'Empty' }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="text-center mt-2">
                            <a href="http://whois.domaintools.com/{{ $domain }}" target="_blank"
                                class="btn btn-outline-orange whois-button justify-content-center mt-4">WHOIS Info</a>
                        </div>
                    </div>

                    <div class="form-card">
                        <h5>{{ Lang::get('client.cPanelcreateEmailAccountTitle') }}</h5>
                        <form action="{{ $serverdata2['cpanel_url'] . $serverdata2['paths']['email'] }}" method="GET"
                            target="_blank">
                            @csrf
                            <input type="hidden" name="action" value="addform">
                            <input type="hidden" name="domain" value="{{ $domain }}">
                            <div class="mb-3">
                                <input type="text" class="form-input" name="email" placeholder="Email Address">
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-input" name="password" placeholder="Password">
                            </div>
                            <button type="submit" class="submit-button">Buat Email</button>
                        </form>
                    </div>

                    <div class="form-card">
                        <h5>{{ Lang::get('client.cPanelchangePassword') }}</h5>
                        <form action="{{ $serverdata2['cpanel_url'] . $serverdata2['paths']['password'] }}"
                            method="GET" target="_blank">
                            @csrf
                            <div class="mb-3">
                                <input type="text" class="form-input" value="{{ $username }}" disabled>
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-input" name="newpass" placeholder="Password Baru">
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-input" name="newpass2"
                                    placeholder="Ulangi Password Baru">
                            </div>
                            <button type="submit" class="submit-button">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const loader = $('#pageLoader');

            let isLoading = false;

            function showLoader() {
                if (isLoading) return;
                isLoading = true;
                $('#pageLoader').addClass('loader-visible');
            }

            function hideLoader() {
                isLoading = false;
                $('#pageLoader').removeClass('loader-visible');
            }

            console.log('Testing loader visibility');
            showLoader();
            setTimeout(hideLoader, 1000);

            $('.menu-item a').on('click', function(e) {
                console.log('Menu item clicked');
                showLoader();
                return true;
            });

            $('form').on('submit', function(e) {
                console.log('Form submitted');
                showLoader();
                return true;
            });

            $('.btn-outline-orange[target="_blank"]').on('click', function(e) {
                console.log('Login button clicked');
                showLoader();
                setTimeout(hideLoader, 2000);
                return true;
            });

            $('a[target="_blank"]').on('click', function(e) {
                console.log('External link clicked');
                showLoader();
                setTimeout(hideLoader, 2000);
                return true;
            });

            $(document).keypress(function(e) {
                if (e.which == 76) {
                    showLoader();
                    setTimeout(hideLoader, 2000);
                }
            });

            $('.menu-item.database-operation a').on('click', function(e) {
                showLoader();
                setTimeout(hideLoader, 6000);
            });
        });
    </script>
@endsection
