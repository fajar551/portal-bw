@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Edit Product</title>
@endsection

@push('styles')
   <link href="{{ Theme::asset('css/app.css') }}" type="text/css" rel="stylesheet" />
@endpush
@section('content')
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">
            <div class="row">
               <!-- MAIN CARD -->
               <div class="col-xl-12">
                  <div class="view-client-wrapper">
                     <div class="row">
                        <div class="col-12">
                           <div class="card-title mb-3">
                              <h4 class="mb-3">Products/Services</h4>
                           </div>
                        </div>
                     </div>
                     @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                           <h5>Success!</h5>
                           <small>{{ $message }}</small>
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                           </button>
                        </div>
                     @endif
                     @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                           <h5>Error!</h5>
                           <small>{{ $message }}</small>
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                           </button>
                        </div>
                     @endif
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <div class="card p-3">
                              <h5 class="mb-4">Edit Product</h5>
                              {{-- Edit Product Tab --}}
                              <ul class="nav nav-tabs" id="myTab" role="tablist">
                                 <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == 1 || $activeTab == null ? 'active' : '' }}"
                                       id="details-tab" data-toggle="tab" href="#details" role="tab"
                                       aria-controls="details" aria-selected="true">Details</a>
                                 </li>
                                 <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == 2 ? 'active' : '' }}" id="pricing-tab"
                                       data-toggle="tab" href="#pricing" role="tab" aria-controls="pricing"
                                       aria-selected="false">Pricing</a>
                                 </li>
                                 <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == 3 ? 'active' : '' }}" id="module-tab"
                                       data-toggle="tab" href="#module" role="tab" aria-controls="module"
                                       aria-selected="false">Module Settings</a>
                                 </li>
                                 <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == 4 ? 'active' : '' }}" id="customfields-tab"
                                       data-toggle="tab" href="#customfields" role="tab" aria-controls="customfields"
                                       aria-selected="false">Custom Fields</a>
                                 </li>
                                 <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == 5 ? 'active' : '' }}"
                                       id="configurable-options-tab" data-toggle="tab" href="#configurableOptions"
                                       role="tab" aria-controls="configurable-options" aria-selected="false">Configurable
                                       Options</a>
                                 </li>
                                 <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == 6 ? 'active' : '' }}" id="upgrades-tab"
                                       data-toggle="tab" href="#upgrades" role="tab" aria-controls="upgrades-options"
                                       aria-selected="false">Upgrades</a>
                                 </li>
                                 <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == 7 ? 'active' : '' }}" id="free-domains-tab"
                                       data-toggle="tab" href="#freeDomains" role="tab" aria-controls="free-domains"
                                       aria-selected="false">Free Domain</a>
                                 </li>
                                 <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ $activeTab == 8 ? 'active' : '' }}" id="links-tab"
                                       data-toggle="tab" href="#links" role="tab" aria-controls="links"
                                       aria-selected="false">Links</a>
                                 </li>
                              </ul>

                              {{-- Edit Product Content --}}
                              <div class="tab-content p-3" id="myTabContent">

                                 {{-- Details Tab --}}
                                 <div
                                    class="tab-pane fade {{ $activeTab == 1 || $activeTab == null ? 'show active' : '' }}"
                                    id="details" role="tabpanel" aria-labelledby="details-tab">
                                    @include('pages.setup.prodsservices.productservices.tabcontent.details', [
                                    'newProds' => $newProds,
                                    'listTypeValue' => $listTypeValue,
                                    'customProductEmail' => $customProductEmail,
                                    'tab' => 1,
                                    'prodGroup' => $prodGroup
                                    ])
                                 </div>

                                 {{-- Pricing Tab --}}
                                 <div class="tab-pane fade {{ $activeTab == 2 ? 'show active' : '' }}" id="pricing"
                                    role="tabpanel" aria-labelledby="pricing-tab">
                                    @include('pages.setup.prodsservices.productservices.tabcontent.pricing', [
                                    'newProds' => $newProds,
                                    'tab' => 2,
                                    'id' => $newProds->id,
                                    'params' => $params,
                                    'price' => $price
                                    ])
                                 </div>

                                 {{-- Module --}}
                                 <div class="tab-pane fade {{ $activeTab == 3 ? 'show active' : '' }}" id="module"
                                    role="tabpanel" aria-labelledby="module-tab">
                                    @include('pages.setup.prodsservices.productservices.tabcontent.modulesettings', [
                                    'product' => $newProds,
                                    'serverModules' => $serverModules,
                                    'serverGroups' => $serverGroups,
                                    'tab' => 3,
                                    'id' => $newProds->id,
                                    ])
                                 </div>

                                 {{-- Customfields --}}
                                 <div class="tab-pane fade {{ $activeTab == 4 ? 'show active' : '' }}" id="customfields"
                                    role="tabpanel" aria-labelledby="customfields-tab">
                                    @include('pages.setup.prodsservices.productservices.tabcontent.customfields',
                                    ['customfields' => $customfields,
                                    'tab' => 4,
                                    'id' => $newProds->id])
                                 </div>

                                 {{-- Configurable Options --}}
                                 <div class="tab-pane fade {{ $activeTab == 5 ? 'show active' : '' }}"
                                    id="configurableOptions" role="tabpanel" aria-labelledby="configurable-options-tab">
                                    @include('pages.setup.prodsservices.productservices.tabcontent.configurableoptions', [
                                    'newProds' => $newProds,
                                    'configList' => $configList,
                                    'tab' => 5
                                    ])
                                 </div>

                                 {{-- Upgrades --}}
                                 <div class="tab-pane fade {{ $activeTab == 6 ? 'show active' : '' }}" id="upgrades"
                                    role="tabpanel" aria-labelledby="upgrades-tab">
                                    @include('pages.setup.prodsservices.productservices.tabcontent.upgrades', [
                                    'newProds' => $newProds,
                                    'upgradesPackageList' => $upgradesPackageList,
                                    'getUpgradePackageId' => $getUpgradePackageId,
                                    'customProductEmail' => $customProductEmail,
                                    'tab' => 6
                                    ])
                                 </div>

                                 {{-- Free Domain --}}
                                 <div class="tab-pane fade {{ $activeTab == 7 ? 'show active' : '' }}" id="freeDomains"
                                    role="tabpanel" aria-labelledby="free-domains-tab">
                                    @include('pages.setup.prodsservices.productservices.tabcontent.freedomains', [
                                    'newProds' => $newProds,
                                    'cyclesPricing' => $cyclesPricing,
                                    'paymentTerms' => $paymentTerms,
                                    'tldInProduct' => $tldInProduct,
                                    'tlds' => $tlds,
                                    'tab' => 7,
                                    ])
                                 </div>

                                 {{-- Links --}}
                                 <div class="tab-pane fade {{ $activeTab == 8 ? 'show active' : '' }}" id="links"
                                    role="tabpanel" aria-labelledby="links-tab">
                                    @include('pages.setup.prodsservices.productservices.tabcontent.links')
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
   @endsection

   @section('scripts')
      <script src="{{ Theme::asset('assets/js/submit-btn.js') }}"></script>
      <script type="text/javascript">
         if ($('#CheckStock:checked')) {
            $('#qty-input').removeAttr('disabled')
         }
      </script>
      <script src="{{ Theme::asset('js/notify.min.js') }}"></script>
      <script src="{{ Theme::asset('js/module-settings.js') }}"></script>
      <script src="{{ Theme::asset('js/customfields.js') }}"></script>
      <script src="{{ Theme::asset('assets/libs/bootstrap-duallistbox/js/jquery.bootstrap-duallistbox.min.js') }}">
      </script>
      <script>
         $(document).ready(function() {
            fetchModuleSettings('{{ $newProds->id }}', 'simple', 'configproducts');
         });
      </script>
      <script type="text/javascript">
         $(document).ready(function() {
            $(".pricingtgl").click(function() {
               var cycle = $(this).attr("cycle");
               var currency = $(this).attr("currency");

               if ($(this).is(":checked")) {
                  $("#pricing_" + currency + "_" + cycle).val("0.00").show();
                  $("#setup_" + currency + "_" + cycle).show();
               } else {
                  $("#pricing_" + currency + "_" + cycle).val("-1.00").hide();
                  $("#setup_" + currency + "_" + cycle).hide();
               }
            });

            var demo1 = $('#bootstrap-duallistbox-nonselected-list_packages').bootstrapDualListbox();
           
            $("#qty-input").prop('disabled', true)

            if ($("input[name='paytype']:checked").val() == 'onetime') {
                $('.recurring-cycles, .setup-column, .price-column, .checkbox-column').addClass('onetime-mode')
            }

            if ($("input[name='paytype']:checked").val() == 'recurring') {
                $('.recurring-cycles, .setup-column, .price-column, .checkbox-column').removeClass('onetime-mode')
                $('#tblpricing').removeClass('w-50');
            }

            if ($("input[name='paytype']:checked").val() == 'free') {
               $('#recurring-section').collapse('hide')
            }

            $("input[name='paytype']").on('click', function() {
               let checkedPay = $("input[name='paytype']:checked").val();
               if (checkedPay == 'onetime') {
                  $('#recurring-section').addClass('show')
                  $('.recurring-cycles, .setup-column, .price-column, .checkbox-column').addClass('onetime-mode')
                  $('#tblpricing').addClass('w-50');
               } else if (checkedPay == 'recurring') {
                  $('#recurring-section').addClass('show')
                  $('.recurring-cycles, .setup-column, .price-column, .checkbox-column').removeClass('onetime-mode')
                  $('#tblpricing').removeClass('w-50');
               } else {
                  $('#recurring-section').removeClass('hide')
                  $('#recurring-section').removeClass('show')
               }
            })

            if ($('input[name=\'stockcontrol\']:checked')) {
               $("#qty-input").removeAttr('disabled', true)
            }
         })
      </script>
   @endsection
