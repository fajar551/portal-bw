<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="{{$charset}}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$companyname}} - {{$pagetitle}}</title>

    <link href="{{ Theme::asset('css/all.css') }}" rel="stylesheet">
    <link href="{{ Theme::asset('css/fontawesome-all.min.css') }}" rel="stylesheet">
    <link href="{{ Theme::asset('css/invoice.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ Theme::asset('assets/images/favicon.ico') }}" />

</head>
<body>

    <div class="container-fluid invoice-container">

      @if ($invalidInvoiceIdRequested)
         @include('includes.panel', [
            'type' => 'danger',
            'headerTitle' => Lang::get("client.error"),
            'bodyContent' => Lang::get("client.invoiceserror"),
            'bodyTextCenter' => true,
         ])
      @else
         <div class="row invoice-header">
            <div class="invoice-col">

               @if ($logo)
                  <p><img src="{{$logo}}" title="{{$companyname}}" /></p>
               @else
                  <h2>{{$companyname}}</h2>
               @endif
               <h3>{{$pagetitle}}</h3>

            </div>
            <div class="invoice-col text-center">

               <div class="invoice-status">
                  @if ($status == "Draft")
                     <span class="draft">{{Lang::get("client.invoicesdraft")}}</span>
                  @elseif ($status == "Unpaid")
                     <span class="unpaid">{{Lang::get("client.invoicesunpaid")}}</span>
                  @elseif ($status == "Paid")
                     <span class="paid">{{Lang::get("client.invoicespaid")}}</span>
                  @elseif ($status == "Refunded")
                     <span class="refunded">{{Lang::get("client.invoicesrefunded")}}</span>
                  @elseif ($status == "Cancelled")
                     <span class="cancelled">{{Lang::get("client.invoicescancelled")}}</span>
                  @elseif ($status == "Collections")
                     <span class="collections">{{Lang::get("client.invoicescollections")}}</span>
                  @elseif ($status == "Payment Pending")
                     <span class="paid">{{Lang::get("client.invoicesPaymentPending")}}</span>
                  @endif
               </div>

               @if ($status == "Unpaid" || $status == "Draft")
                  <div class="small-text">
                     {{Lang::get("client.invoicesdatedue")}}: {{$datedue}}
                  </div>
                  <div class="payment-btn-container hidden-print" align="center">
                        {!!$paymentbutton!!}
                  </div>
               @endif

            </div>
         </div>

         <hr>

         @if ($paymentSuccessAwaitingNotification)
            @include('includes.panel', [
               'type' => 'success',
               'headerTitle' => Lang::get("client.success"),
               'bodyContent' => Lang::get("client.invoicePaymentSuccessAwaitingNotify"),
               'bodyTextCenter' => true,
            ])
         @elseif ($paymentSuccess)
            @include('includes.panel', [
               'type' => 'success',
               'headerTitle' => Lang::get("client.success"),
               'bodyContent' => Lang::get("client.invoicepaymentsuccessconfirmation"),
               'bodyTextCenter' => true,
            ])
         @elseif ($pendingReview)
            @include('includes.info', [
               'type' => 'success',
               'headerTitle' => Lang::get("client.success"),
               'bodyContent' => Lang::get("client.invoicepaymentpendingreview"),
               'bodyTextCenter' => true,
            ])
         @elseif ($paymentFailed)
            @include('includes.danger', [
               'type' => 'success',
               'headerTitle' => Lang::get("client.error"),
               'bodyContent' => Lang::get("client.invoicepaymentfailedconfirmation"),
               'bodyTextCenter' => true,
            ])
         @elseif ($offlineReview)
            @include('includes.info', [
               'type' => 'success',
               'headerTitle' => Lang::get("client.success"),
               'bodyContent' => Lang::get("client.invoiceofflinepaid"),
               'bodyTextCenter' => true,
            ])
         @endif

         <div class="row">
               <div class="invoice-col right">
                  <strong>{{Lang::get("client.invoicespayto")}}</strong>
                  <address class="small-text">
                     {!!$payto!!}
                     @if ($taxCode)
                        <br />{{$taxIdLabel}}: {{$taxCode}}
                     @endif
                  </address>
               </div>
               <div class="invoice-col">
                  <strong>{{Lang::get("client.invoicesinvoicedto")}}</strong>
                  <address class="small-text">
                     @if ($clientsdetails['companyname']) {{$clientsdetails['companyname']}}<br> @endif
                     {{$clientsdetails['firstname']}} {{$clientsdetails['lastname']}}<br />
                     {{$clientsdetails['address1']}}, {{$clientsdetails['address2']}}<br />
                     {{$clientsdetails['city']}}, {{$clientsdetails['state']}}, {{$clientsdetails['postcode']}}<br />
                     {{$clientsdetails['country']}}
                     @if ($clientsdetails['tax_id'])
                        <br />{{$taxIdLabel}}: {{$clientsdetails['tax_id']}}
                     @endif
                     @if ($customfields)
                        <br /><br />
                        @foreach ($customfields as $customfield)
                           {{$customfield['fieldname']}}: {{$customfield['value']}}<br />
                        @endforeach
                     @endif
                  </address>
               </div>
         </div>

         <div class="row">
               <div class="invoice-col right">
                  <strong>{{Lang::get("client.paymentmethod")}}</strong><br>
                  <span class="small-text">
                     @if ($status == "Unpaid" && $allowchangegateway)
                        <form method="post" action="" class="form-inline">
                           @csrf
                           {!!$gatewaydropdown!!}
                        </form>
                     @else
                        {{$paymentmethod}}@if ($paymethoddisplayname) ({{$paymethoddisplayname}})@endif
                     @endif
                  </span>
                  <br /><br />
               </div>
               <div class="invoice-col">
                  <strong>{{Lang::get("client.invoicesdatecreated")}}</strong><br>
                  <span class="small-text">
                     {{$date}}<br><br>
                  </span>
               </div>
         </div>

         <br />

         @if ($manualapplycredit)
            <div class="panel panel-success">
               <div class="panel-heading">
                  <h3 class="panel-title"><strong>{{Lang::get("client.invoiceaddcreditapply")}}</strong></h3>
               </div>
               <div class="panel-body">
                  <form method="post" action="">
                     @csrf
                     <input type="hidden" name="applycredit" value="true" />
                     {{Lang::get("client.invoiceaddcreditdesc1")}} <strong>{{$totalcredit}}</strong>. {{Lang::get("client.invoiceaddcreditdesc2")}}. {{Lang::get("client.invoiceaddcreditamount")}}:
                     <div class="row">
                        <div class="col-xs-8 col-xs-offset-2 col-sm-4 col-sm-offset-4">
                           <div class="input-group">
                                 <input type="text" name="creditamount" value="{{$creditamount}}" class="form-control" />
                                 <span class="input-group-btn">
                                    <input type="submit" value="{{Lang::get("client.invoiceaddcreditapply")}}" class="btn btn-success" />
                                 </span>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         @endif

         @if ($notes)
            @include('includes.panel', [
               'type' => 'info',
               'headerTitle' => Lang::get("client.invoicesnotes"),
               'bodyContent' => $notes,
            ])
         @endif

         <div class="panel panel-default">
               <div class="panel-heading">
                  <h3 class="panel-title"><strong>{{Lang::get("client.invoicelineitems")}}</strong></h3>
               </div>
               <div class="panel-body">
                  <div class="table-responsive">
                     <table class="table table-condensed">
                           <thead>
                              <tr>
                                 <td><strong>{{Lang::get("client.invoicesdescription")}}</strong></td>
                                 <td width="20%" class="text-center"><strong>{{Lang::get("client.invoicesamount")}}</strong></td>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($invoiceitems as $item)
                                 <tr>
                                    <td>{{$item['description']}}@if ($item['taxed'] == "true") *@endif</td>
                                    <td class="text-center">{{$item['amount']}}</td>
                                 </tr>
                              @endforeach
                              <tr>
                                 <td class="total-row text-right"><strong>{{Lang::get("client.invoicessubtotal")}}</strong></td>
                                 <td class="total-row text-center">{{$subtotal}}</td>
                              </tr>
                              @if ($taxname)
                                 <tr>
                                    <td class="total-row text-right"><strong>{{$taxrate}}% {{$taxname}}</strong></td>
                                    <td class="total-row text-center">{{$tax}}</td>
                                 </tr>
                              @endif
                              @if ($taxname2)
                                 <tr>
                                    <td class="total-row text-right"><strong>{{$taxrate2}}% {{$taxname2}}</strong></td>
                                    <td class="total-row text-center">{{$tax2}}</td>
                                 </tr>
                              @endif
                              <tr>
                                 <td class="total-row text-right"><strong>{{Lang::get("client.invoicescredit")}}</strong></td>
                                 <td class="total-row text-center">{{$credit}}</td>
                              </tr>
                              <tr>
                                 <td class="total-row text-right"><strong>{{Lang::get("client.invoicestotal")}}</strong></td>
                                 <td class="total-row text-center">{{$total}}</td>
                              </tr>
                           </tbody>
                     </table>
                  </div>
               </div>
         </div>

         @if ($taxrate)
            <p>* {{Lang::get("client.invoicestaxindicator")}}</p>
         @endif

         <div class="transactions-container small-text">
               <div class="table-responsive">
                  <table class="table table-condensed">
                     <thead>
                           <tr>
                              <td class="text-center"><strong>{{Lang::get("client.invoicestransdate")}}</strong></td>
                              <td class="text-center"><strong>{{Lang::get("client.invoicestransgateway")}}</strong></td>
                              <td class="text-center"><strong>{{Lang::get("client.invoicestransid")}}</strong></td>
                              <td class="text-center"><strong>{{Lang::get("client.invoicestransamount")}}</strong></td>
                           </tr>
                     </thead>
                     <tbody>
                        @forelse ($transactions as $transaction)
                           <tr>
                              <td class="text-center">{{$transaction['date']}}</td>
                              <td class="text-center">{{$transaction['gateway']}}</td>
                              <td class="text-center">{{$transaction['transid']}}</td>
                              <td class="text-center">{{$transaction['amount']}}</td>
                           </tr>
                        @empty
                           <tr>
                              <td class="text-center" colspan="4">{{Lang::get("client.invoicestransnonefound")}}</td>
                           </tr>
                        @endforelse
                           <tr>
                              <td class="text-right" colspan="3"><strong>{{Lang::get("client.invoicesbalance")}}</strong></td>
                              <td class="text-center">{{$balance}}</td>
                           </tr>
                     </tbody>
                  </table>
               </div>
         </div>

         <div class="pull-right btn-group btn-group-sm hidden-print">
               <a href="javascript:window.print()" class="btn btn-default"><i class="fas fa-print"></i> {{Lang::get("client.print")}}</a>
               <a href="{{route('dl')}}?type=i&amp;id={{$invoiceid}}" class="btn btn-default"><i class="fas fa-download"></i> {{Lang::get("client.invoicesdownload")}}</a>
         </div>
      @endif

    </div>

    <p class="text-center hidden-print"><a href="{{url('/')}}">{!!Lang::get("client.invoicesbacktoclientarea")!!}</a></a></p>

</body>
</html>
