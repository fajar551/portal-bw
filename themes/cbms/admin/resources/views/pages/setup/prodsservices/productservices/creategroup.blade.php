@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Create Group</title>
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
                                    <h5>Create Group</h5>
                                 </div>
                              </div>
                              <form
                                 action="{{ route('admin.pages.setup.prodsservices.productservices.creategroup.add') }}"
                                 method="POST" id="productGroupForm">
                                 @csrf
                                 <div class="row">
                                    <div class="col-lg-12">
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Product Group
                                             Name</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="name" type="text" placeholder="eg. Shared Hosting"
                                                class="form-control">
                                          </div>
                                       </div>

                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Product Group Headline
                                          </label>
                                          <div class="col-sm-12 col-lg-6">
                                             <input name="headline" type="text" placeholder="eg. Your Perfect Plan"
                                                class="form-control">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Product Group Tagline
                                          </label>
                                          <div class="col-sm-12 col-lg-6">
                                             <input name="tagline" type="text"
                                                placeholder="eg. With our 30 Day Money Back Guarantee You Can't Go Wrong!"
                                                class="form-control">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Group
                                             Features</label>
                                          <div class="col-sm-12 col-lg-6 pt-2">
                                             <em>You must save the product group for the first time before you
                                                can add features</em>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-lg-2 col-form-label">Order Form
                                             Template</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="row">
                                                @foreach ($templateList as $id => $template)
                                                   <div class="col-sm-12 col-lg-4 mb-3">
                                                      <div>
                                                         <div class="border rounded p-2" style="min-height: 200px">
                                                            Thumbnail
                                                         </div>
                                                         <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="orderfrmtpl"
                                                               id="exampleRadios1" value="{{ $template }}">
                                                            <label class="form-check-label" for="exampleRadios1">
                                                               {{ $template }}
                                                            </label>
                                                         </div>
                                                      </div>
                                                   </div>
                                                @endforeach
                                                {{-- <div class="col-sm-12 col-lg-4 mb-3">
                                                   <div>
                                                      <div class="border rounded p-2" style="min-height: 200px">
                                                         Thumbnail
                                                      </div>
                                                      <div class="form-check">
                                                         <input class="form-check-input" type="radio" name="orderfrmtpl"
                                                            id="exampleRadios2" value="option2" >
                                                         <label class="form-check-label" for="exampleRadios2">
                                                            Premium Comparison
                                                         </label>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-4 mb-3">
                                                   <div>
                                                      <div class="border rounded p-2" style="min-height: 200px">
                                                         Thumbnail
                                                      </div>
                                                      <div class="form-check">
                                                         <input class="form-check-input" type="radio" name="orderfrmtpl"
                                                            id="exampleRadios3" value="option3" >
                                                         <label class="form-check-label" for="exampleRadios3">
                                                            Universal Slider
                                                         </label>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-4 mb-3">
                                                   <div>
                                                      <div class="border rounded p-2" style="min-height: 200px">
                                                         Thumbnail
                                                      </div>
                                                      <div class="form-check">
                                                         <input class="form-check-input" type="radio" name="orderfrmtpl"
                                                            id="exampleRadios4" value="option4" >
                                                         <label class="form-check-label" for="exampleRadios4">
                                                            Supreme Comparison
                                                         </label>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-4 mb-3">
                                                   <div>
                                                      <div class="border rounded p-2" style="min-height: 200px">
                                                         Thumbnail
                                                      </div>
                                                      <div class="form-check">
                                                         <input class="form-check-input" type="radio" name="orderfrmtpl"
                                                            id="exampleRadios5" value="option5" >
                                                         <label class="form-check-label" for="exampleRadios5">
                                                            Pure Comparison
                                                         </label>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-4 mb-3">
                                                   <div>
                                                      <div class="border rounded p-2" style="min-height: 200px">
                                                         Thumbnail
                                                      </div>
                                                      <div class="form-check">
                                                         <input class="form-check-input" type="radio" name="orderfrmtpl"
                                                            id="exampleRadios6" value="option6" >
                                                         <label class="form-check-label" for="exampleRadios6">
                                                            Cloud Slider
                                                         </label>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-4 mb-3">
                                                   <div>
                                                      <div class="border rounded p-2" style="min-height: 200px">
                                                         Thumbnail
                                                      </div>
                                                      <div class="form-check">
                                                         <input class="form-check-input" type="radio" name="orderfrmtpl"
                                                            id="exampleRadios7" value="option7" >
                                                         <label class="form-check-label" for="exampleRadios7">
                                                            Boxes
                                                         </label>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-sm-12 col-lg-4 mb-3">
                                                   <div>
                                                      <div class="border rounded p-2" style="min-height: 200px">
                                                         Thumbnail
                                                      </div>
                                                      <div class="form-check">
                                                         <input class="form-check-input" type="radio" name="orderfrmtpl"
                                                            id="exampleRadios8" value="option8">
                                                         <label class="form-check-label" for="exampleRadios8">
                                                            Modern
                                                         </label>
                                                      </div>
                                                   </div>
                                                </div> --}}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2">Available Payment Gateways</label>
                                          <div class="col-sm-12 col-lg-10">
                                             <div class="row">
                                                @foreach ($gateway as $key => $value)
                                                   <div class="col-lg-3">
                                                      <div class="custom-control custom-checkbox">
                                                         <input name="disabledgateways[]" value="{{ $key }}"
                                                            type="checkbox" class="custom-control-input"
                                                            id="{{ $key }}">
                                                         <label class="custom-control-label"
                                                            for="{{ $key }}">{{ $value }}</label>
                                                      </div>
                                                   </div>
                                                @endforeach
                                                {{-- <div class="col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="disabledgateways[]" value="xenditdana" type="checkbox"
                                                         class="custom-control-input" id="customCheck2">
                                                      <label class="custom-control-label" for="customCheck2">Xendit Via
                                                         Dana</label>
                                                   </div>
                                                </div>
                                                <div class="col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="disabledgateways[]" value="xenditbni" type="checkbox"
                                                         class="custom-control-input" id="customCheck3">
                                                      <label class="custom-control-label" for="customCheck3">Xendit BNI
                                                         VA</label>
                                                   </div>
                                                </div>
                                                <div class="col-lg-3">
                                                   <div class="custom-control custom-checkbox">
                                                      <input name="disabledgateways[]" value="indodana" type="checkbox"
                                                         class="custom-control-input" id="customCheck4">
                                                      <label class="custom-control-label" for="customCheck4">Indodana
                                                         Paylater</label>
                                                   </div>
                                                </div> --}}
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
                                                   id="customCheck18">
                                                <label class="custom-control-label" for="customCheck18">Check
                                                   this box if this is a hidden group</label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-lg-12 d-flex justify-content-center">
                                       <button type="submit" id="btnCreateProdGroups"
                                          class="btn btn-success px-3 mx-1">Save Changes</button>
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
@endsection
