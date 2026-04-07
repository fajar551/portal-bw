@extends('layouts.clientbase')

@section('title')
   Deposit Balance
@endsection

@section('page-title')
   Deposit Balance
@endsection

@section('content')
   <div class="page-content" id="add-deposit">
      <div class="container-fluid">
         <div class="row">
            <div class="col-xl-8 col-lg-8">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mb-3"><a href="index.html">Dashboard</a> <span
                        class="text-muted"> / Add Funds</span></h6>
               </div>
            </div>
         </div>
         <div class="row section-payment">
            <div class="col-sm-12 col-lg-8">
               <div class="card p-3" id="deposit-card">
                  <div class="title-form">
                     <h5 class="text-qw">Add Deposit To Your Account</h5>
                  </div>
                  <form action="{{ route('generate.invoice') }}" method="POST">
                     @csrf
                     <div class="form-group row">
                        <label class="col-sm-12 col-lg-3 col-form-label">Amount to Add</label>
                        <div class="col-sm-12 col-lg-9">
                           <input type="text" name="amount" class="form-control @error('amount') is-invalid @enderror"
                              placeholder="Min Rp. 50.000,00">
                           @if ($message = Session::get('error_funds'))
                              <div class="text-danger">{{ $message }}</div>
                           @endif
                           @error('amount')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="" class="col-sm-12 col-lg-3 col-form-label">Payment Method</label>
                        <div class="col-sm-12 col-lg-9">
                           <select name="paymentmethod" id=""
                              class="form-control @error('paymentmethod') is-invalid @enderror">
                              <option value="">Choose Payment Method</option>
                              @foreach ($gateways as $id => $gateway)
                                 <option value="{{ $id }}">{{ $gateway }}</option>
                              @endforeach
                              {{-- {!! $paymentMethodDropdown !!} --}}
                           </select>
                           @error('paymentmethod')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-12 mt-3">
                           <small>By Adding Funds, I agree that Funds/Deposit will be used automatically to renew my
                              expiring
                              active services/domains</small>
                        </div>
                     </div>
                     <button type="submit" class="btn btn-success-qw px-5">
                        <i class="fas fa-plus mr-2"></i>Add Funds
                     </button>
                  </form>
                  <div class="tac mt-3 alert alert-warning">
                     <ul class="mb-0">
                        <li>All deposits are non-refundable</li>
                        <li>
                           <div class="font-weight-bold text-info">Paypal (Verified Account only) : 1 USD = IDR 11.904
                           </div>
                        </li>
                     </ul>
                  </div>
               </div>
            </div>
            <div class="col-sm-12 col-lg-4">
               <div class="card">
                  <div class="card-body">
                     <h4 class="card-title">Information</h4>
                     <div class="table-responsive">
                        <table class="table table-bordered">
                           <tbody>
                              <tr>
                                 <td>Minimum Deposit</td>
                                 <td>{{ Format::formatCurrency($fundmin) }}</td>
                              </tr>
                              <tr>
                                 <td>Maximum Deposit</td>
                                 <td>{{ Format::formatCurrency($fundmax) }}</td>
                              </tr>
                              <tr>
                                 <td>Maximum Balance</td>
                                 <td>{{ Format::formatCurrency($maxbal) }}</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection
