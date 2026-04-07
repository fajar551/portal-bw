 <div class="vertical-menu">
    <div data-simplebar class="h-100">
       @php
          $companyLogo = Cfg::getValue('LogoURL');
          $defaultLogo = Theme::asset('assets/images/WHMCEPS.png');
       @endphp
       <!-- LOGO -->
       <div class="navbar-brand-box">
          <a href="{{ url('home') }}" class="logo">
             <span>
                @if (empty($companyLogo))
                   <img src="{{ $defaultLogo }}" alt="company-logo" width="100">
                @else
                   <img src="{{ $companyLogo }}" alt="company-logo" width="100">
                @endif
             </span>
             <i>
                @if (empty($companyLogo))
                   <img src="{{ $defaultLogo }}" alt="company-logo" width="100">
                @else
                   <img src="{{ $companyLogo }}" alt="company-logo" width="100">
                @endif
             </i>
          </a>
       </div>

       <!--- Sidemenu -->
       <div id="sidebar-menu">
          <!-- Left Menu Start -->
          <ul class="metismenu list-unstyled" id="side-menu">
             <li class="menu-title">Menu</li>

             <li>
                <a href="{{ url('/home') }}"><i
                      class="feather-home"></i><span>Beranda</span></a>
             </li>
             <li>
                <a href="javascript: void(0);" class="has-arrow"><i
                      class="feather-server"></i><span>Layanan</span></a>
                <ul class="sub-menu" aria-expanded="false">
                   <li><a href="{{ url('services/myservices') }}">Layanan Saya</a>
                   </li>
                   <li><a href="{{ route('cart') }}">Pesan Layanan Baru</a></li>
                   {{--<li><a href="https://client.qwords.com/cart?gid=addons">Pesan Layanan Tambahan</a></li>--}}
                   <li><a href="https://www.qwords.com/migrasi-hosting/">Migrasi Hosting ke Qwords</a></li>

                </ul>
             </li>
             <li>
               <a href="javascript: void(0);" class="has-arrow">
                  <i class="fa fa-globe-asia"></i><span> Domain</span></a>
               <ul class="sub-menu" aria-expanded="false">
                  
                  <li>
                     <a href="{{ url('domain/mydomains') }}">Domain Saya</a>
                  </li>
                  {{--<li>
                     <a href="{{ url('') }}">Perpanjangan Domain</a>
                  </li>--}}
                  <li class="has-sub">
                     <a href="javascript:void(0);">Daftar Domain Baru</a>
                     <ul class="sub-menu" aria-expanded="false">
                        <li>
                           <a href="https://client.bikin.website/cart?a=add&pid=2">Corporate</a>
                        </li>
                        <li>
                           <a href="https://client.bikin.website/cart?a=add&pid=9">DIY</a>
                        </li>
                        <li>
                           <a href="https://client.bikin.website/cart?a=add&pid=3">Ecommerce</a>
                        </li>
                        <li>
                           <a href="https://client.bikin.website/cart?a=add&pid=6">Entry</a>
                        </li>
                        <li>
                           <a href="https://client.bikin.website/cart?a=add&pid=7">Online</a>
                        </li>
                        <li>
                           <a href="https://client.bikin.website/cart?a=add&pid=8">Pilkada</a>
                        </li>
                        <li>
                           <a href="https://client.bikin.website/cart?a=add&pid=1">UKM</a>
                        </li>
                        <li>
                           <a href="https://client.bikin.website/cart?a=add&pid=5">Wedding</a>
                        </li>
                     </ul>
                  </li>
                  {{--<li>
                     <a href="{{ url('https://client.bikin.website/cart?a=add&domain=transfer') }}">Transfer Domain</a>
                  </li>--}}


               </ul>
            </li> 

             <li>
                <a href="javascript: void(0);" class="has-arrow"><i
                      class="feather-credit-card"></i><span>Finance/Billing</span></a>
                <ul class="sub-menu" aria-expanded="false">
                   <li><a href="{{ url('billinginfo/myinvoices') }}">Tagihan Saya</a></li>
                   <li><a href="{{ route('pages.support.openticket.index', ['step' => '2', 'deptid' => '9']) }}">Permintaan Tagihan Manual</a></li>
                   <li><a href="{{ route('pages.support.openticket.index', ['step' => '2', 'deptid' => '6']) }}">Permintaan Faktur Pajak</a></li>
                   {{-- <li><a href="{{ route('pages.support.openticket.index', ['step' => '2', 'deptid' => '6']) }}">Bukti Potong PPH23</a></li> --}}
                   <li><a href="{{ url('/addfunds') }}">Tambah Deposit</a></li>
                   <li><a href="{{ route('pages.support.openticket.index', ['step' => '2', 'deptid' => '5']) }}">Pengembalian Dana</a></li>
                   {{--<li><a href="{{ url('quotes') }}">Penawaran untuk saya</a></li>--}}
                </ul>
             </li>

             <li>
                <a href="javascript: void(0);" class="has-arrow"><i
                      class="feather-help-circle"></i><span>{{ __('client.navsupport') }}</span></a>
                <ul class="sub-menu" aria-expanded="false">
                   <li><a href="{{ url('support/openticket') }}">{{ __('client.opennewticket') }}</a></li>
                   <li><a href="{{ url('support/mytickets') }}">{{ __('client.navtickets') }}</a></li>
                   <li><a href="{{ url('https://kb.qwords.com/') }}">Knowledge Base</a></li>
                   <li><a href="{{ url('https://status.qwords.com/') }}">Server/Network Status</a></li>
                   <li><a href="{{ route('pages.support.openticket.index', ['step' => '2', 'deptid' => '18']) }}">Saran & Kritik</a></li>
                </ul>
             </li>

             {{-- <li>
                <a href="javascript: void(0);" class="has-arrow"><i
                      class="feather-help-circle"></i><span>Tools</span></a>
                <ul class="sub-menu" aria-expanded="false">
                   <li><a href="https://client.qwords.com/index.php?m=unblockip">Unblock IP Address</a></li>
                   <li><a href="https://wa.share.web.id/">WhatsApp Link Generator</a></li>
                   <li><a href="https://link.share.web.id/">LinkQ for Instagram</a></li>
                </ul>
             </li> --}}

             <li>
                <a href="{{ url('affiliate') }}"><i class="far fa-handshake"></i><span>Affiliate</span></a>
             </li>
             <li id="test-nav">


             </li>
             <li class="fix-bottom px-5 py-2">
                {{-- <a href="#" id="darkSwitch"><i class="feather-moon"></i><span>Dark Mode</span></a> --}}
                <div class="custom-control custom-switch">
                   <input type="checkbox" class="custom-control-input" id="darkSwitch">
                   <label class="custom-control-label sidebar-text" for="darkSwitch"><i
                         class="feather-moon mr-2"></i><span>Dark Mode</span></label>
                </div>
             </li>

          </ul>
       </div>
       <!-- Sidebar -->
    </div>
 </div>
