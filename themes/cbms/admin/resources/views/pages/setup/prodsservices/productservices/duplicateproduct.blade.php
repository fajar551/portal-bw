@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Create New Product</title>
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
                              <form
                                 action="{{ route('admin.pages.setup.prodsservices.productservices.createproduct.duplicate_post') }}"
                                 method="POST">
                                 @csrf
                                 {{-- <input type="hidden" value="{{ $prodId + 1 }}" name="id"> --}}
                                 <div class="form-group row">
                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Existing Product</label>
                                    <div class="col-sm-12 col-lg-4">
                                       <select name="existingproduct" class="form-control">
                                          @foreach ($products as $key => $product)
                                             <option value="{{ $product['id'] }}">{{ $product['groupname'] }} -
                                                {{ $product['name'] }}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                       New Product Name
                                    </label>
                                    <div class="col-sm-12 col-lg-6">
                                       <input type="text" class="form-control" name="name">
                                    </div>
                                 </div>
                                 <div class="text-center">
                                    <button type="submit" class="btn btn-success px-5">Continue</button>
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
   <script src="{{ Theme::asset('assets/libs/bootstrap-switch-custom/bootstrap4-toggle.min.js') }}"></script>
@endsection
