@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Edit Currency</title>
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
                              <h4 class="mb-3">Currencies</h4>
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
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <div class="card p-3">
                              {{-- <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-reposive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Currency Code</th>
                                                                <th>Prefix</th>
                                                                <th>Suffix</th>
                                                                <th>Format</th>
                                                                <th>Base Conv. Rate</th>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($currenciesData as $currencies)
                                                                <tr>
                                                                    <td>{{ $currencies->code }}</td>
                                                                    <td>{{ $currencies->prefix }}</td>
                                                                    <td>{{ $currencies->suffix }}</td>
                                                                    <td>{{ $currencies->format }}</td>
                                                                    <td>{{ $currencies->rate }}</td>
                                                                    <td class="text-center"><i class="fas fa-edit"></i></td>
                                                                    <td class="text-center"><i class="fas fa-trash-alt"></i></td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12 d-flex justify-content-center">
                                                        <button class="btn btn-light px-3 mx-1">Update Exchange
                                                            Rates</button>
                                                        <button class="btn btn-light px-3 mx-1">Update Product
                                                            Prices</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}
                              <div class="row mt-3">
                                 <div class="col-lg-12">
                                    <h5 class="mb-3">Edit {{ $currency->code }} Currency</h5>
                                    <form class="w-100"
                                       action="{{ route('admin.pages.setup.payments.currencies.update', $currency->id) }}"
                                       method="POST">
                                       @method('PUT')
                                       @csrf
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Currency
                                             Code</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="code" type="text" class="form-control"
                                                value="{{ $currency->code }}">
                                             <small>eg. USD, GBP, etc...</small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Prefix</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="prefix" type="text" class="form-control"
                                                value="{{ $currency->prefix }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Suffix</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="suffix" type="text" class="form-control"
                                                value="{{ $currency->suffix }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Format</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <select name="format" id="" class="form-control">
                                                <option value="1" {{ $currency->format == 1 ? 'selected' : '' }}>1234.56</option>
                                                <option value="2" {{ $currency->format == 2 ? 'selected' : '' }}>1,234.56</option>
                                                <option value="3" {{ $currency->format == 3 ? 'selected' : '' }}>1.234.56</option>
                                                <option value="4" {{ $currency->format == 4 ? 'selected' : '' }}>1,234</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Base Conv.
                                             Rate</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="rate" type="text" class="form-control"
                                                value="{{ $currency->rate }}">
                                             <small>The current rate to convert to base currency</small>
                                          </div>
                                       </div>
                                       <div class="row">
                                          <div class="col-lg-12 d-flex justify-content-center">
                                             <button type="submit" class="btn btn-success px-3 mr-3">Update
                                                Currency</button>
                                             <a href="{{ route('admin.pages.setup.payments.currencies.index') }}">
                                                <button type="button" class="btn btn-light px-3">Back To
                                                   Currencies Page</button>
                                             </a>
                                          </div>
                                       </div>
                                    </form>
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
