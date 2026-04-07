@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Currencies</title>
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
                           @if (session('message'))
                              <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Something Went Wrong!</h5>
                                 <small>{!! session('message') !!}</small>
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
                              <p>You can sell in different currencies concurrently by setting them up below.
                                 Customers who visit your site can then choose to shop in their local currency.
                              </p>
                              <div class="row">
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
                                                <th colspan="2">Action</th>
                                             </tr>
                                          </thead>
                                          <tbody>

                                             @foreach ($currenciesData as $currencies)
                                                <tr>
                                                   <td>{{ $currencies['code'] }}</td>
                                                   <td>{{ $currencies['prefix'] }}</td>
                                                   <td>{{ $currencies['suffix'] }}</td>
                                                   <td>
                                                      @foreach ($formattedCurr as $fId => $valFormat)
                                                         {{ $currencies['format'] == $fId ? $valFormat : '' }}
                                                      @endforeach
                                                   </td>
                                                   <td>{{ $currencies['rate'] }}</td>
                                                   <td class="text-center"><a title="Edit"
                                                         href="{{ route('admin.pages.setup.payments.currencies.edit', $currencies['id']) }}"><i
                                                            class="fas fa-edit"></i></a></td>
                                                   @if ($currencies['default'] == 1)
                                                      <td></td>
                                                   @else
                                                      <td class="text-center">
                                                         <a class="text-danger"
                                                            onclick="ConfirmDelete('{{ route('admin.pages.setup.payments.currencies.delete', $currencies['id']) }}')"
                                                            title="Delete" href="#">
                                                            <i class="fas fa-trash-alt"></i></a>
                                                      </td>
                                                   @endif
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
                              </div>
                              <div class="row mt-3">
                                 <div class="col-lg-12">
                                    <h5 class="mb-3">Add Additional Currency</h5>
                                    <form class="w-100"
                                       action="{{ route('admin.pages.setup.payments.currencies.create') }}"
                                       method="POST">
                                       @csrf
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Currency
                                             Code</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="code" type="text" class="form-control">
                                             <small>eg. USD, GBP, etc...</small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Prefix</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="prefix" type="text" class="form-control">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Suffix</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="suffix" type="text" class="form-control">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Format</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <select name="format" id="" class="form-control">
                                               <option value="1">1234.56</option>
                                               <option value="2">1,234.56</option>
                                               <option value="3">1.234.56</option>
                                               <option value="4">1,234</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-sm-12 col-lg-2 col-form-label">Base Conv.
                                             Rate</label>
                                          <div class="col-sm-12 col-lg-5">
                                             <input name="rate" type="text" class="form-control" value="1.00">
                                             <small>The current rate to convert to base currency</small>
                                          </div>
                                       </div>
                                       <div class="row justify-content-center">
                                          <div class="col-sm-12 col-md-2">
                                             <button type="submit" class="btn btn-success btn-block">Add
                                                Currency</button>
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

@section('scripts')
   <script>
      function ConfirmDelete(url) {
         const csrf = $('meta[name="csrf-token"]').attr("content");
         Swal.fire({
            title: "Are you sure?",
            html: `The <b>Data</b> will be deleted from database.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Delete!",
            showLoaderOnConfirm: true,
            preConfirm: () => {
               const options = {
                  method: "GET",
                  headers: {
                     "Content-Type": "application/json",
                     "X-CSRF-TOKEN": csrf,
                  },
               };
               return fetch(
                     url,
                     options
                  )
                  .then((response) => {
                     if (response) {
                        Swal.fire(
                           "Deleted!",
                           "Your file has been deleted.",
                           "success"
                        );
                     }
                  })
                  // .then(setTimeout(function () {
                  //     location.reload()
                  // }, 2000))
                  .then(location.reload())
                  .catch((error) => {
                     Swal.showValidationMessage(`Request failed: ${error}`);
                  });
            },
            allowOutsideClick: () => !Swal.isLoading(),
         });
      }
   </script>
@endsection
