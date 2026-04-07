@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} - Edit Group</title>
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
                              <h4 class="mb-3">Products/Services</h4>
                           </div>
                        </div>
                     </div>
                     @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                           <h5>Successfully Created!</h5>
                           <small>{{ $message }}</small>
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                           </button>
                        </div>
                     @endif
                     @if (session('message'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                           <h5>Something Went Wrong!</h5>
                           <small>{!! session('message') !!}</small>
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                           </button>
                        </div>
                     @endif
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <div class="card p-3">
                              <div class="row mb-3">
                                 <div class="col-lg-12">
                                    <h5>Edit Group</h5>
                                 </div>
                              </div>
                              <form
                                 action="{{ route('admin.pages.setup.prodsservices.productservices.updategroup', $selectedGroup->id) }}"
                                 method="POST" id="productGroupForm">
                                 @csrf
                                 <div class="row">
                                    <div class="col-lg-12">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Product Group
                                             Name</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="name" type="text" placeholder="eg. Shared Hosting"
                                                class="form-control" value="{{ $selectedGroup->name }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Product Group Headline
                                          </label>
                                          <div class="col-sm-12 col-lg-6">
                                             <input name="headline" type="text" placeholder="eg. Your Perfect Plan"
                                                class="form-control" value="{{ $selectedGroup->headline }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Product Group Tagline
                                          </label>
                                          <div class="col-sm-12 col-lg-6">
                                             <input name="tagline" type="text"
                                                placeholder="eg. With our 30 Day Money Back Guarantee You Can't Go Wrong!"
                                                class="form-control" value="{{ $selectedGroup->tagline }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Group
                                             Features</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <p class="p-0 mb-2">Features added here will be available in a
                                                product group for display.</p>
                                             <div id="storedFeature" class="d-flex w-100">
                                                @if ($productFeature)
                                                   @foreach ($productFeature as $key => $value)
                                                      <div class="feature-badge px-3 py-2 mr-3 mb-3 rounded"
                                                         onclick="remove(this)" title="Remove Feature">
                                                         <input id="{{ $value }}" type="checkbox" name="featured[]"
                                                            class="d-none custom-control-input" value="{{ $value }}"
                                                            checked> {{ $value }} <i class="fas fa-times ml-2"></i>
                                                      </div>
                                                   @endforeach
                                                @endif
                                             </div>
                                             <div class="input-group mb-3 w-50">
                                                <input id="featureInput" type="text" class="form-control"
                                                   placeholder="Add New Feature" aria-label="Add New Feature"
                                                   aria-describedby="basic-addon2">
                                                <div class="input-group-append">
                                                   <button id="btnAddFeature" onclick="add_feature()"
                                                      class="btn btn-warning" type="button">Add New</button>
                                                </div>
                                             </div>
                                          </div>
                                       </div>

                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Order Form
                                             Template</label>
                                          <div class="col-sm-12 col-lg-10 col-form-label">
                                             <div class="custom-control custom-radio custom-control-inline">
                                                @php
                                                   $defaultOrderForm = Cfg::getValue('OrderFormTemplate');
                                                @endphp
                                                <input type="radio" id="customRadioInline1" name="orderfrmtpl"
                                                   class="custom-control-input"
                                                   value="{{ $defaultOrderForm }}"
                                                   {{ $selectedGroup->orderfrmtpl == $defaultOrderForm || $selectedGroup->orderfrmtpl == '' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="customRadioInline1">Use System
                                                   Default</label>
                                             </div>
                                             <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="customRadioInline2" name="orderfrmtpl"
                                                   class="custom-control-input" value="custom"
                                                   {{ $selectedGroup->orderfrmtpl != $defaultOrderForm ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="customRadioInline2">Use
                                                   Specific
                                                   Template</label>
                                             </div>
                                          </div>
                                       </div>

                                       <div class="collapse" id="spec-temp">
                                          <div class="form-group row">
                                             <label for="" class="col-sm-12 col-lg-2"></label>
                                             @foreach ($orderformThemes as $key => $orderformTheme)
                                                <div class="col-sm-12 col-lg-3 p-3" style="background-color: #f0f0f0">
                                                   <div class="label-thumb">
                                                      <label for="{{ $orderformTheme['name'] }}">
                                                         <img src="{{ $orderformTheme['thumbnail_url'] }}"
                                                            alt="thumbs.jpg">
                                                      </label>
                                                   </div>
                                                   <div class="custom-control custom-radio custom-control-inline">
                                                      <input type="radio" id="{{ $orderformTheme['name'] }}"
                                                         name="orderfrmtplcustom" class="custom-control-input"
                                                         value="{{ $orderformTheme['name'] }}"
                                                         {{ $orderFormTemplate == $orderformTheme['name'] ? 'checked' : '' }}>
                                                      <label class="custom-control-label"
                                                         for="{{ $orderformTheme['name'] }}">
                                                         {{ Str::ucfirst($orderformTheme['name']) }}
                                                         {{ $orderformTheme['name'] == \App\Helpers\ThemeManager::orderformThemeDefault() ? '(Default)' : '' }}
                                                      </label>
                                                   </div>
                                                </div>
                                             @endforeach
                                          </div>
                                       </div>

                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2">Active Payment Gateways</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="row" id="gatewayCheck">
                                                @foreach ($gateways as $key => $gateway)
                                                   <div class="col-lg-4">
                                                      <div class="custom-control custom-checkbox">
                                                         <input type="hidden" name="disabledgatewaysHidden[]"
                                                            id="{{ $key }}Hidden" value="{{ $key }}">
                                                         <input name="disabledgateways[]" value="{{ $key }}"
                                                            type="checkbox" class="custom-control-input"
                                                            id="{{ $key }}"
                                                            @foreach ($selectedGatewayArr as $disabled => $r) {{ $disabled === $key ? 'checked' : '' }} @endforeach>
                                                         <label class="custom-control-label"
                                                            for="{{ $key }}">{{ $gateway }}</label>
                                                      </div>
                                                   </div>
                                                @endforeach
                                             </div>
                                          </div>
                                       </div>

                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Hidden</label>
                                          <div class="col-sm-12 col-lg-10 pt-2">
                                             <div class="custom-control custom-checkbox">
                                                <input name="hidden" type="hidden" value="0" class="custom-control-input"
                                                   id="customCheck18Hidden">
                                                <input name="hidden" type="checkbox" value="1" class="custom-control-input"
                                                   id="customCheck18"
                                                   {{ $selectedGroup->hidden == 1 ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="customCheck18">Check
                                                   this box if this is a hidden group</label>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">
                                             Direct Link
                                          </label>
                                          <div class="col-sm-12 col-lg-6">
                                             <input type="text" class="form-control"
                                                value="http://127.0.0.1:8000/cart?gid={{ $selectedGroup->id }}"
                                                disabled>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-lg-12 d-flex justify-content-center">
                                       <button type="submit" id="btnCreateProdGroups"
                                          class="btn btn-success px-3 mx-1">Save
                                          Changes</button>
                                       <a href="{{ route('admin.pages.setup.prodsservices.productservices.index') }}">
                                          <button type="button" class="btn btn-light px-3 mx-1">Cancel Changes</button>
                                       </a>
                                    </div>
                                 </div>
                              </form>
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

@section('scripts')
   <script src="{{ Theme::asset('assets/js/submit-btn.js') }}"></script>
   <script type="text/javascript">
      $(document).ready(function() {
         $("#gatewayCheck input[type=checkbox]").each(function() {
            this.checked = !this.checked;
         });

         if ($('#featureInput').val('')) {
            $('#btnAddFeature').prop('disabled', true)
         }

         if ($("input[name='orderfrmtpl']:checked").val() == 'custom') {
            $('#spec-temp').collapse('show')
         }

         if ($("input[name='orderfrmtpl']:checked").val() == 'default') {
            $('#spec-temp').collapse('hide')
         }

         $("input[name='orderfrmtpl']").on('click', function() {
            let checkedPay = $("input[name='orderfrmtpl']:checked").val();
            if (checkedPay == 'default') {
               $('#spec-temp').collapse('hide')
            } else if (checkedPay == 'custom') {
               $('#spec-temp').collapse('show')
            } else {
               $('#spec-temp').collapse('hide')
            }
         })
      });

      $(document).on('keyup', "#featureInput", function() {
         var val = $(this).val();
         if (val.length != 0) {
            $("#btnAddFeature").prop('disabled', false);
         } else {
            $("#btnAddFeature").prop('disabled', true);
         }
      })

      function add_feature() {
         let inputSelect = $('#featureInput');
         let valueInput = inputSelect.val();
         let elementInput = `
            <div class="feature-badge px-3 py-2 mr-3 mb-3 rounded" onclick="remove(this)" title="Remove Feature">
            <input id="${valueInput}" type="checkbox" name="featured[]" class="d-none custom-control-input" value="${valueInput}" checked> ${valueInput} <i class="fas fa-times ml-2"></i>
            </div>
            `
         $('#storedFeature').append(elementInput);
         $('#featureInput').val('');
         $("#btnAddFeature").prop('disabled', true);
      }

      function remove(el) {
         var element = el;
         element.remove();
      }
   </script>
@endsection
