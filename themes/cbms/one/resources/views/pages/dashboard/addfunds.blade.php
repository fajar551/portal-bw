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
                  <h6 class="header-pretitle d-none d-md-block mb-3"><a href="{{url('/')}}">Dashboard</a> <span
                        class="text-muted"> / Add Funds</span></h6>
               </div>
            </div>
         </div>
         <div class="row section-payment">
            <div class="col-sm-12 col-lg-8">
               @if ($addfundsdisabled)
                  @include('includes.alert', [
                     'type' => 'error',
                     'msg' => Lang::get("client.clientareaaddfundsdisabled"),
                     'textcenter' => true,
                  ])
               @elseif ($notallowed)
                  @include('includes.alert', [
                     'type' => 'error',
                     'msg' => Lang::get("client.clientareaaddfundsnotallowed"),
                     'textcenter' => true,
                  ])
               @elseif ($errormessage)
                  @include('includes.alert', [
                     'type' => 'error',
                     'errorshtml' => $errormessage,
                     'textcenter' => true,
                  ])
               @endif

               @if (!$addfundsdisabled)
                  <div class="card p-3" id="deposit-card">
                     <div class="title-form">
                        <h5 class="text-qw">Add Deposit To Your Account</h5>
                     </div>
                     <form method="post" action="">
                        @csrf
                           <div class="form-group row">
                              <label for="amount" class="col-sm-12 col-lg-3 col-form-label">{{Lang::get('client.addfundsamount')}}:</label>
                              <div class="col-sm-12 col-lg-9">
                                 <input type="text" name="amount" id="amount"
                                          value="{{$amount}}" class="form-control" required />
                              </div>
                           </div>
                           <div class="form-group row">
                              <label for="paymentmethod" class="col-sm-12 col-lg-3 col-form-label">{{Lang::get('client.orderpaymentmethod')}}:</label><br/>
                              <div class="col-sm-12 col-lg-9">
                                 <select name="paymentmethod" id="paymentmethod" class="form-control">
                                    @foreach ($gateways as $gateway)
                                       <option value="{{$gateway['sysname']}}">{{$gateway['name']}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="form-group text-right">
                              <button type="submit" class="btn btn-success-qw px-5">
                                 <i class="fas fa-plus mr-2"></i> {{Lang::get('client.addfunds')}}
                              </button>
                           </div>
                     </form>
                     <div class="tac mt-3 alert alert-warning">
                        <ul class="mb-0">
                           <li>{{Lang::get('client.addfundsnonrefundable')}}</li>
                           <li>
                              <div class="font-weight-bold text-info">Paypal (Verified Account only) : 1 USD = IDR 11.904
                              </div>
                           </li>
                        </ul>
                     </div>
                  </div>
               @endif

            </div>
            <div class="col-sm-12 col-lg-4">
               <div class="card">
                  <div class="card-body">
                     <h4 class="card-title">Information</h4>
                     <div class="table-responsive">
                        <table class="table table-striped">
                           <tbody>
                              <tr>
                                    <td class="textright"><strong>{{Lang::get('client.addfundsminimum')}}</strong></td>
                                    <td>{{$minimumamount}}</td>
                              </tr>
                              <tr>
                                    <td class="textright"><strong>{{Lang::get('client.addfundsmaximum')}}</strong></td>
                                    <td>{{$maximumamount}}</td>
                              </tr>
                              <tr>
                                    <td class="textright"><strong>{{Lang::get('client.addfundsmaximumbalance')}}</strong></td>
                                    <td>{{$maximumbalance}}</td>
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
