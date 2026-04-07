@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Dashboard</title>
@endsection

@section('content')
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">
            <div class="row">
               {{-- This Sidebar --}}
               @if ($message = Session::get('success'))
                  <div class="col-lg-12">
                     <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h4 class="alert-heading">Your profile is updated!</h4>
                        <small>The profile data has been updated, you can change that anytime.</small>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div>
               @endif
               <!-- MAIN CARD -->
               <div class="col-xl-12" id="main-card">
                  <section class="dashboard-wrapper">
                     <div class="card-title mb-3">
                        <h4>{{ __('admin.hometitle') }}</h4>
                     </div>
                     <section class="card-status">
                        <div class="row">
                           <div class="col-md-6 col-lg-3">
                              <a href="{{ url('admin/orders') }}">
                                 <div class="card hover-card" id="">
                                    <div class="card-body text-white rounded pending-order">
                                       <div class="info-data">
                                          <i class="ri-shopping-cart-fill"></i>
                                          <h1 class="text-white mr-2">533</h1>
                                       </div>
                                       <small>{{ __('admin.orderslistpending') }}</small>
                                    </div>
                                 </div>
                              </a>
                           </div>
                           <div class="col-md-6 col-lg-3">
                              <a href="{{ url('admin/support/supporttickets') }}">
                                 <div class="card hover-card" id="">
                                    <div class="card-body text-white rounded tickets-waiting">
                                       <div class="info-data">
                                          <i class="ri-chat-3-fill"></i>
                                          <h1 class="text-white mr-2">52</h1>
                                       </div>
                                       <small>{{ __('admin.supportawaitingreply') }}</small>
                                    </div>
                                 </div>
                              </a>
                           </div>
                           <div class="col-md-6 col-lg-3">
                              <a href="{{ url('admin/clients/cancellationrequests') }}">
                                 <div class="card hover-card" id="">
                                    <div class="card-body text-white rounded pending-cancellation">
                                       <div class="info-data">
                                          <i class="ri-forbid-2-line"></i>
                                          <h1 class="text-white mr-2">0</h1>
                                       </div>
                                       <small>{{ __('admin.statspendingcancellations') }}</small>
                                    </div>
                                 </div>
                              </a>
                           </div>
                           <div class="col-md-6 col-lg-3">
                              <div class="card hover-card" id="">
                                 <div class="card-body text-white rounded pending-module">
                                    <div class="info-data">
                                       <i class="ri-error-warning-fill"></i>
                                       <h1 class="text-white mr-2">17</h1>
                                    </div>
                                    <small>{{ __('admin.statpendingmodule') }}</small>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </section>
                     <div class="chart-overview">
                        <div class="row">
                           <div class="col-lg-8 col-md-12 sm-mb-3">
                              <div class="card p-3">
                                 <h4 class="card-title mb-3">{{ __('admin.homesysoverview') }}</h4>
                                 <hr class="p-0 mt-2" />
                                 <div>
                                    <div id="spline_area" class="apex-charts mt-3" dir="ltr"></div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-lg-4 col-md-12">
                              <div class="card p-3">
                                 <h4 class="card-title mb-3">{{ __('admin.homeautomationoverview') }}</h4>
                                 <hr class="p-0 mt-2" />
                                 <div class="table-responsive thin-scrollbar mt-4 border rounded">
                                    <table class="table table-hover mb-0 table-centered table-nowrap">
                                       <tbody>
                                          <tr>
                                             <td>
                                                <p class="font-size-12 mb-0">
                                                   Invoice Created
                                                </p>
                                             </td>
                                             <td>
                                                <div id="spak-chart1"></div>
                                             </td>
                                             <td>
                                                <h3>0</h3>
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>
                                                <p class="font-size-12 mb-0">
                                                   Overdue Suspensions
                                                </p>
                                             </td>

                                             <td>
                                                <div id="spak-chart2"></div>
                                             </td>
                                             <td>
                                                <h3>0</h3>
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>
                                                <p class="font-size-12 mb-0">
                                                   Overdue Reminders
                                                </p>
                                             </td>
                                             <td>
                                                <div id="spak-chart3"></div>
                                             </td>
                                             <td>
                                                <h3>0</h3>
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>
                                                <p class="font-size-12 mb-0">
                                                   Credit Card Captures
                                                </p>
                                             </td>
                                             <td>
                                                <div id="spak-chart4"></div>
                                             </td>
                                             <td>
                                                <h3>0</h3>
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>
                                                <p class="font-size-12 mb-0">
                                                   Inactive Ticket Closed
                                                </p>
                                             </td>
                                             <td>
                                                <div id="spak-chart5"></div>
                                             </td>
                                             <td>
                                                <h3>0</h3>
                                             </td>
                                          </tr>
                                          <tr>
                                             <td>
                                                <p class="font-size-12 mb-0">
                                                   Cancellations Processed
                                                </p>
                                             </td>
                                             <td>
                                                <div id="spak-chart6"></div>
                                             </td>
                                             <td>
                                                <h3>0</h3>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <section class="section-3">
                        <div class="row">
                           <div class="col-lg-2 rounded">
                              <div class="card p-3">
                                 <h4 class="card-title m-0 p-0">{{ __('admin.billingtitle') }}</h4>
                                 <hr />
                                 <div class="row">
                                    <div class="col-12 pb-3">
                                       <h5 class="m-0 text-success">Rp 0.00</h5>
                                       <small>{{ __('admin.billingincometoday') }}</small>
                                    </div>
                                    <div class="col-12 pb-3">
                                       <h5 class="m-0 text-warning">Rp 0.00</h5>
                                       <small>{{ __('admin.calendarthisMonth') }}</small>
                                    </div>
                                    <div class="col-12 pb-3">
                                       <h5 class="m-0 text-danger">Rp 4996100</h5>
                                       <small>{{ __('admin.calendarthisYear') }}</small>
                                    </div>
                                    <div class="col-12">
                                       <h5 class="m-0">Rp 14690903</h5>
                                       <small>{{ __('admin.calendaralltime') }}</small>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-lg-5">
                              <div class="card p-3">
                                 <h4 class="card-title">{{ __('admin.wizardmarketConnect') }}
                                 </h4>
                                 <hr class="mt-2 p-0" />
                                 <div class="my-3">
                                    <h5 class="d-inline p-2">{{ __('admin.marketConnectsellingStatus') }}
                                    </h5>
                                    <a href="{{ url('admin/setup/marketconnect') }}">
                                       <button class="d-inline btn btn-sm btn-outline-primary float-right">
                                          {{ __('admin.manage') }}
                                       </button>
                                    </a>
                                 </div>
                                 <div class="swiper-container thin-scrollbar" id="market-bar">
                                    <!-- Additional required wrapper -->
                                    <div class="swiper-wrapper">
                                       <!-- Slides -->
                                       <div class="swiper-slide d-flex">
                                          <div class="d-flex align-content-center">
                                             <div class="col-lg-3">
                                                <div class="img-container">
                                                   <img src="{{ Theme::asset('assets/images/service/logo-sml.png') }}"
                                                      width="40px" alt="ssl-certi" />
                                                </div>
                                             </div>
                                             <div class="col-lg-8">
                                                <p class="mx-2">
                                                   SSL Certificates by DigiCert
                                                </p>
                                             </div>
                                          </div>
                                          <div class="d-flex align-content-center">
                                             <div class="col-lg-3">
                                                <div class="img-container">
                                                   <img
                                                      src="{{ Theme::asset('assets/images/service/web-builder.png') }}"
                                                      width="50px" alt="weebly" />
                                                </div>
                                             </div>
                                             <div class="col-lg-8">
                                                <p class="mx-2">Website Builder by Weebly</p>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="swiper-slide d-flex">
                                          <div class="d-flex align-content-center">
                                             <div class="col-lg-3">
                                                <div class="img-container">
                                                   <img src="{{ Theme::asset('assets/images/service/guard.png') }}"
                                                      width="40px" alt="codeguard" />
                                                </div>
                                             </div>
                                             <div class="col-lg-8">
                                                <p class="mx-2">
                                                   Website Backup by CodeGuard
                                                </p>
                                             </div>
                                          </div>
                                          <div class="d-flex align-content-center">
                                             <div class="col-lg-3">
                                                <div class="img-container">
                                                   <img src="{{ Theme::asset('assets/images/service/site-lock.png') }}"
                                                      width="35px" alt="sitelock" />
                                                </div>
                                             </div>
                                             <div class="col-lg-8">
                                                <p class="mx-2">
                                                   Website Security by SiteLock
                                                </p>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="swiper-slide d-flex">
                                          <div class="d-flex align-content-center">
                                             <div class="col-lg-2">
                                                <div class="img-container">
                                                   <img src="{{ Theme::asset('assets/images/service/vpn.png') }}"
                                                      width="40px" alt="" />
                                                </div>
                                             </div>
                                             <div class="col-lg-6">
                                                <p class="mx-2">VPN by SiteLock</p>
                                             </div>
                                          </div>
                                          <div class="d-flex align-content-center">
                                             <div class="col-lg-3">
                                                <div class="img-container">
                                                   <img src="{{ Theme::asset('assets/images/service/email.png') }}"
                                                      width="40px" alt="spamexperts" />
                                                </div>
                                             </div>
                                             <div class="col-lg-8">
                                                <p class="mx-2">
                                                   Email Security by SpamExperts
                                                </p>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="swiper-slide d-flex">
                                          <div class="d-flex align-content-center">
                                             <div class="col-sm-2">
                                                <div class="img-container">
                                                   <img src="{{ Theme::asset('assets/images/service/seo.svg') }}"
                                                      width="40px" alt="marketgoo" />
                                                </div>
                                             </div>
                                             <div class="col-lg-8">
                                                <p class="mx-2">Seo Tools by Marketgoo</p>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- If we need pagination -->
                                    <div class="swiper-scrollbar"></div>
                                 </div>

                                 <hr />
                                 <p class="text-center">
                                    MarketConnect gives you access to resell market
                                    leading services to your customers in minutes.
                                    <a href="#">Learn more »</a>
                                 </p>
                              </div>
                           </div>
                           <div class="col-lg-5">
                              <div class="card p-3">
                                 <h4 class="card-title">Support</h4>
                                 <hr class="p-0 mt-2" />
                                 <div class="row supp-section">
                                    <div class="col-6">
                                       <div class="d-lg-flex align-items-center">
                                          <i class="ri-price-tag-3-fill mr-3" style="color: #77cacd"></i>
                                          <div>
                                             <p>Awaiting Reply</p>
                                             <p>
                                                <span style="color: #77cacd; font-size: 20px">52
                                                </span>
                                                Tickets
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-6">
                                       <div class="d-lg-flex align-items-center">
                                          <i class="ri-flag-2-fill mr-3" style="color: #ef7895"></i>
                                          <div>
                                             <p>Assigned To You</p>
                                             <p>
                                                <span style="color: #ef7895; font-size: 20px">0
                                                </span>
                                                Tickets
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <table class="mt-3 table table-sm table-hover w-100">
                                       <tbody>
                                          <tr>
                                             <th scope="row">#488625</th>
                                             <td colspan="2">Mark</td>
                                             <td>@mdo</td>
                                          </tr>
                                          <tr>
                                             <th scope="row">#120591</th>
                                             <td colspan="2">Jacob</td>
                                             <td>@fat</td>
                                          </tr>
                                          <tr>
                                             <th scope="row">#399630</th>
                                             <td colspan="2">Larry the Bird</td>
                                             <td>@twitter</td>
                                          </tr>
                                       </tbody>
                                    </table>
                                 </div>
                                 <hr class="p-0 m-1" />
                                 <div class="row d-none d-sm-block mt-3">
                                    <div class="col-12">
                                       <div
                                          class="option-tickets d-flex justify-content-lg-between flex-sm-column flex-lg-row text-center">
                                          <a class="mr-2"
                                             href="{{ url('admin/support/supporttickets') }}">View All
                                             Tickets</a>
                                          <a class="mr-2" href="#">View My Tickets</a>
                                          <a class="mr-2" href="#">Open New Tickets</a>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </section>
                     <section class="section-4">
                        <div class="row">
                           <div class="col-lg-4">
                              <div class="card p-3">
                                 <h4 class="card-title">Staff Online</h4>
                                 <hr class="p-0 mt-2" />
                                 <div class="profile-online">
                                    <div class="row">
                                       <div class="col-lg-4 col-sm-12 d-flex flex-wrap justify-content-center">
                                          <i class="mdi mdi-account"></i>
                                          <h6 class="w-100 text-center">CBMS Admin</h6>
                                          <small class="text-success">Online</small>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-lg-12">
                                    <div class="card p-3">
                                       <h4 class="card-title">Network Status</h4>
                                       <hr class="p-0 mt-2" />
                                       <div class="row">
                                          <table class="table p-0">
                                             <thead>
                                                <tr>
                                                   <td>
                                                      Qwords.com
                                                      <br />
                                                      <small>109.23.123.1</small>
                                                   </td>
                                                   <td>
                                                      <div class="text-success">N/A</div>
                                                      <small>Status</small>
                                                   </td>
                                                   <td>
                                                      <div>N/A</div>
                                                      <small>Uptime</small>
                                                   </td>
                                                   <td>
                                                      <div>N/A</div>
                                                      <small>Avg. load</small>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td>
                                                      Qwords.com
                                                      <br />
                                                      <small>109.23.123.1</small>
                                                   </td>
                                                   <td>
                                                      <div class="text-success">N/A</div>
                                                      <small>Status</small>
                                                   </td>
                                                   <td>
                                                      <div>N/A</div>
                                                      <small>Uptime</small>
                                                   </td>
                                                   <td>
                                                      <div>N/A</div>
                                                      <small>Avg. load</small>
                                                   </td>
                                                </tr>
                                             </thead>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-lg-12">
                                    <div class="card p-3">
                                       <h4 class="card-title">Activity</h4>
                                       <hr class="p-0 mt-2" />
                                       <div class="activity-list thin-scrollbar" id="activity-bar">
                                          <div class="activity-item border-bottom">
                                             <div class="row">
                                                <div class="col-6">
                                                   <h6>Admin</h6>
                                                </div>
                                                <div class="col-6">
                                                   <small class="float-right">59 Seconds ago</small>
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   Hooks Debug: Hook Completed - Returned True
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   <small>202.80.212.117</small>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="activity-item border-bottom">
                                             <div class="row">
                                                <div class="col-6">
                                                   <h6>Admin</h6>
                                                </div>
                                                <div class="col-6">
                                                   <small class="float-right">59 Seconds ago</small>
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   Hooks Debug: Hook Point
                                                   AdminAreaFooterOutput - Calling Hook
                                                   Function (anonymous function)
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   <small>202.80.212.117</small>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="activity-item border-bottom">
                                             <div class="row">
                                                <div class="col-6">
                                                   <h6>Admin</h6>
                                                </div>
                                                <div class="col-6">
                                                   <small class="float-right">59 Seconds ago</small>
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   Hooks Debug: Called Hook Point
                                                   AdminAreaFooterOutput
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   <small>202.80.212.117</small>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="activity-item border-bottom">
                                             <div class="row">
                                                <div class="col-6">
                                                   <h6>Admin</h6>
                                                </div>
                                                <div class="col-6">
                                                   <small class="float-right">59 Seconds ago</small>
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   Hooks Debug: No Hook Functions Defined
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   <small>202.80.212.117</small>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="activity-item border-bottom">
                                             <div class="row">
                                                <div class="col-6">
                                                   <h6>Admin</h6>
                                                </div>
                                                <div class="col-6">
                                                   <small class="float-right">59 Seconds ago</small>
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   Hooks Debug: Called Hook Point AdminAreaPage
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-12">
                                                   <small>202.80.212.117</small>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-lg-4">
                              <div class="card p-3">
                                 <h4 class="card-title">Client Activity</h4>
                                 <hr class="p-0 mt-2" />
                                 <div class="mb-5 mb-lg-0">
                                    <div class="row">
                                       <div class="col-6">
                                          <div class="client-activity">
                                             <div class="d-lg-flex align-items-center">
                                                <i class="ri-user-fill mr-3" style="color: #eaae88"></i>
                                                <div>
                                                   <p>Active Clients</p>
                                                   <p>
                                                      <span style="color: #eaae88; font-size: 20px">28
                                                      </span>
                                                      Active
                                                   </p>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-6">
                                          <div class="user-activity">
                                             <div class="d-lg-flex align-items-center">
                                                <i class="ri-emotion-happy-fill mr-3" style="color: #3fa72a"></i>
                                                <div>
                                                   <p>Active Clients</p>
                                                   <p>
                                                      <span style="color: #3fa72a; font-size: 20px">28
                                                      </span>
                                                      Active
                                                   </p>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-12">
                                       <div class="table-scroll thin-scrollbar" id="tabs-bar">
                                          <table class="table table-sm table-striped bg-white">
                                             <thead>
                                                <tr>
                                                   <th scope="col">#</th>
                                                   <th scope="col">First</th>
                                                   <th scope="col">Last</th>
                                                   <th scope="col">Handle</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                                <tr>
                                                   <th scope="row">1</th>
                                                   <td>Mark</td>
                                                   <td>Otto</td>
                                                   <td>@mdo</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">3</th>
                                                   <td colspan="2">Larry the Bird</td>
                                                   <td>@twitter</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                                <tr>
                                                   <th scope="row">2</th>
                                                   <td>Jacob</td>
                                                   <td>Thornton</td>
                                                   <td>@fat</td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-lg-12">
                                    <div class="card p-3">
                                       <h4 class="card-title">System Health</h4>
                                       <hr class="mt-2 p-0" />
                                       <div class="status-health">
                                          <div class="row align-items-center">
                                             <div class="col-2" id="icon-web">
                                                <i class="mdi mdi-web text-success"></i>
                                             </div>
                                             <div class="col-4">
                                                <div>
                                                   Overall Rating
                                                   <h3 class="text-success">Good</h3>
                                                </div>
                                             </div>
                                             <div class="col-6">
                                                <button class="btn btn-outline-dark btn-sm float-right">
                                                   <i class="mdi mdi-arrow-right"></i> View
                                                   Issues
                                                </button>
                                             </div>
                                          </div>
                                          <div class="row">
                                             <div class="col-lg-12">
                                                <div class="progress" style="height: 20px">
                                                   <div class="progress-bar-striped bg-success" role="progressbar"
                                                      style="width: 75%" aria-valuenow="25" aria-valuemin="0"
                                                      aria-valuemax="100"></div>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="row text-center mt-3">
                                             <div class="col-lg-6">
                                                <i class="mdi mdi-alert"></i> 4 Warnings
                                             </div>
                                             <div class="col-lg-6 text-danger">
                                                <i class="mdi mdi-close"></i> 4 Needing
                                                Attention
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-lg-4">
                              <div class="card p-3">
                                 <h4 class="card-title">To-Do List</h4>
                                 <hr class="p-0 mt-2" />
                                 <div class="todo-wrap thin-scrollbar" id="todo-bar">
                                    <div class="todo-item col-12">
                                       <p class="text-right months">Due 6 months ago</p>
                                       <div class="form-group row">
                                          <div class="col-sm-12">
                                             <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="gridCheck1" />
                                                <label class="form-check-label" for="gridCheck1">
                                                   Manual Domain Registration
                                                   <span class="badge badge-warning">Pending</span>
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <i class="mdi mdi-account-circle mb-2"></i>
                                    </div>
                                    <div class="todo-item">
                                       <p class="text-right months">Due 6 months ago</p>
                                       <div class="form-group row">
                                          <div class="col-sm-12">
                                             <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="gridCheck1" />
                                                <label class="form-check-label" for="gridCheck1">
                                                   Manual Domain Registration
                                                   <span class="badge badge-warning">Pending</span>
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <i class="mdi mdi-account-circle mb-2"></i>
                                    </div>
                                    <div class="todo-item">
                                       <p class="text-right months">Due 6 months ago</p>
                                       <div class="form-group row">
                                          <div class="col-sm-12">
                                             <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="gridCheck1" />
                                                <label class="form-check-label" for="gridCheck1">
                                                   Manual Domain Registration
                                                   <span class="badge badge-warning">Pending</span>
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <i class="mdi mdi-account-circle mb-2"></i>
                                    </div>
                                    <div class="todo-item">
                                       <p class="text-right months">Due 6 months ago</p>
                                       <div class="form-group row">
                                          <div class="col-sm-12">
                                             <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="gridCheck1" />
                                                <label class="form-check-label" for="gridCheck1">
                                                   Manual Domain Registration
                                                   <span class="badge badge-warning">Pending</span>
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <i class="mdi mdi-account-circle mb-2"></i>
                                    </div>
                                    <div class="todo-item">
                                       <p class="text-right months">Due 6 months ago</p>
                                       <div class="form-group row">
                                          <div class="col-sm-12">
                                             <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="gridCheck1" />
                                                <label class="form-check-label" for="gridCheck1">
                                                   Manual Domain Registration
                                                   <span class="badge badge-warning">Pending</span>
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <i class="mdi mdi-account-circle mb-2"></i>
                                    </div>
                                    <div class="todo-item">
                                       <p class="text-right months">Due 6 months ago</p>
                                       <div class="form-group row">
                                          <div class="col-sm-12">
                                             <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="gridCheck1" />
                                                <label class="form-check-label" for="gridCheck1">
                                                   Manual Domain Registration
                                                   <span class="badge badge-warning">Pending</span>
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <i class="mdi mdi-account-circle mb-2"></i>
                                    </div>
                                    <div class="todo-item">
                                       <p class="text-right months">Due 6 months ago</p>
                                       <div class="form-group row">
                                          <div class="col-sm-12">
                                             <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="gridCheck1" />
                                                <label class="form-check-label" for="gridCheck1">
                                                   Manual Domain Registration
                                                   <span class="badge badge-warning">Pending</span>
                                                </label>
                                             </div>
                                          </div>
                                       </div>
                                       <i class="mdi mdi-account-circle mb-2"></i>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </section>
                     <section class="section-5">
                        <div class="row">
                           <div class="col-lg-12">
                              <div class="card p-3">
                                 <div class="h4 card-title">Project Management</div>
                                 <hr class="p-0 mt-2" />
                                 <nav>
                                    <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                                       <li class="nav-item">
                                          <a class="nav-link active" id="nav-assigned-tab" data-toggle="tab"
                                             href="#nav-assigned" role="tab" aria-controls="nav-assigned"
                                             aria-selected="true">My Assigned</a>
                                       </li>
                                       <li class="nav-item">
                                          <a class="nav-link" id="nav-due-project-tab" data-toggle="tab"
                                             href="#nav-due-project" role="tab" aria-controls="nav-due-project"
                                             aria-selected="false">Due
                                             Project</a>
                                       </li>
                                       <li class="nav-item">
                                          <a class="nav-link" id="nav-recent-activity-tab" data-toggle="tab"
                                             href="#nav-recent-activity" role="tab" aria-controls="nav-recent-activity"
                                             aria-selected="false">Recent
                                             Activity</a>
                                       </li>
                                    </ul>
                                 </nav>
                                 <div class="tab-content p-3" id="nav-tabContent">
                                    <div class="tab-pane fade show active" id="nav-assigned" role="tabpanel"
                                       aria-labelledby="nav-assigned-tab">
                                       <div class="table-responisve">
                                          <table id="datatable" class="table dt-responsive wrap w-100 mt-3">
                                             <thead style="ackground-color: #252b3b; color: #ffffff; text-align: center; ">
                                                <tr>
                                                   <th>Title</th>
                                                   <th>Due Date</th>
                                                   <th>Days Left / Due In</th>
                                                   <th>Status</th>
                                                </tr>
                                             </thead>
                                          </table>
                                       </div>
                                    </div>
                                    <div class="tab-pane fade" id="nav-due-project" role="tabpanel"
                                       aria-labelledby="nav-due-project-tab">
                                       <div class="table-responisve">
                                          <table id="scroll-vertical-datatable"
                                             class="table dt-responsive wrap w-100 mt-3">
                                             <thead style="background-color: #252b3b; color: #ffffff; text-align: center;">
                                                <tr>
                                                   <th>Title</th>
                                                   <th>Due Date</th>
                                                   <th>Days Left / Due In</th>
                                                   <th>Status</th>
                                                </tr>
                                             </thead>
                                          </table>
                                       </div>
                                    </div>
                                    <div class="tab-pane fade" id="nav-recent-activity" role="tabpanel"
                                       aria-labelledby="nav-recent-activity-tab">
                                       <div class="table-responisve">
                                          <table id="selection-datatable" class="table dt-responsive wrap w-100 mt-3">
                                             <thead style="background-color: #252b3b; color: #ffffff; text-align: center;">
                                                <tr>
                                                   <th>Date</th>
                                                   <th>Due Projects</th>
                                                   <th>Recent Activity</th>
                                                </tr>
                                             </thead>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </section>
                     <!-- </section> -->
                  </section>
               </div>
               <!-- END MAIN -->
            </div> <!-- END ROW -->
         </div>
      </div>
   </div>
@endsection

@section('scripts')
   <!-- Simplebar init -->
   <script src="{{ Theme::asset('assets/js/simplebarexec.js') }}"></script>
   <!-- Buttons examples -->
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
   <!-- apexcharts -->
   <script src="{{ Theme::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
   <!-- swiper slide -->
   <script src="{{ Theme::asset('assets/libs/swiper-slider/swiper-bundle.min.js') }}"></script>
   <!-- apexcharts init -->
   <script src="{{ Theme::asset('assets/js/pages/apexcharts.init.js') }}"></script>
   <!-- Required datatable js -->
   <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
   <!-- Responsive examples -->
   <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}">
   </script>
   <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/pages/dashboard.init.js') }}"></script>
@endsection
