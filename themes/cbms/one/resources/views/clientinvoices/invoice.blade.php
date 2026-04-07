<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="{{$charset}}">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <title>{{$companyname}} - {{$pagetitle}}</title>
   <!-- App favicon -->
   <link rel="shortcut icon" href="{{ Theme::asset('assets/images/favicon.ico') }}" />

       <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


   <!-- Bootstrap Css -->
   <link href="{{ Theme::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet"
      type="text/css" />

   <!-- Icons Css -->
   <link href="{{ Theme::asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
   <link href="{{ Theme::asset('assets/css/invoiceweb.css') }}" rel="stylesheet" type="text/css" />

   <script src="{{ Theme::asset('assets/js/jquery.min.js') }}"></script>
   @include('includes.scripts-global')
</head>

<body>
   <div class="text-center py-3">
      <a href="{{ route('pages.billing.myinvoices.index') }}"><u>Back to Invoice List Page</u></a>
   </div>
   @if ($invalidInvoiceIdRequested)
      @include('includes.panel', [
         'type' => 'danger',
         'headerTitle' => Lang::get("client.error"),
         'bodyContent' => Lang::get("client.invoiceserror"),
         'bodyTextCenter' => true,
      ])
   @else
      <div class="container invoice-container">
         <div class="row header">
            <div class="col-md-7">
               @if ($logo)
                  <p><img src="{{$logo}}" title="{{$companyname}}" width="100"/></p>
               @else
                  <h3 class="mt-5">{{$companyname}}</h3>
               @endif
               <h3 class="mt-5">{{$pagetitle}}</h3>
            </div>
            <div class="col-sm-5 text-right">
               <div class="invoice-status">
                  @if ($status == "Draft")
                     <span class="h3 draft">{{Lang::get("client.invoicesdraft")}}</span>
                  @elseif ($status == "Unpaid")
                     <span class="h3 unpaid">{{Lang::get("client.invoicesunpaid")}}</span>
                  @elseif ($status == "Paid")
                     <span class="h3 paid">{{Lang::get("client.invoicespaid")}}</span>
                  @elseif ($status == "Refunded")
                     <span class="h3 refunded">{{Lang::get("client.invoicesrefunded")}}</span>
                  @elseif ($status == "Cancelled")
                     <span class="h3 cancelled">{{Lang::get("client.invoicescancelled")}}</span>
                  @elseif ($status == "Collections")
                     <span class="h3 collections">{{Lang::get("client.invoicescollections")}}</span>
                  @elseif ($status == "Payment Pending")
                     <span class="h3 paid">{{Lang::get("client.invoicesPaymentPending")}}</span>
                  @endif
               </div>
               @if ($status == "Unpaid" || $status == "Draft")
                  <div class="small-text">
                     {{Lang::get("client.invoicesdatedue")}}: {{$datedue}}
                  </div>
                  <div class="payment-btn-container hidden-print my-3" align="right">
                        {!!$paymentbutton!!}
                  </div>
               @endif
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
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
                  @include('includes.panel', [
                     'type' => 'info',
                     'headerTitle' => Lang::get("client.success"),
                     'bodyContent' => Lang::get("client.invoicepaymentpendingreview"),
                     'bodyTextCenter' => true,
                  ])
               @elseif ($paymentFailed)
                  @include('includes.panel', [
                     'type' => 'danger',
                     'headerTitle' => Lang::get("client.error"),
                     'bodyContent' => Lang::get("client.invoicepaymentfailedconfirmation"),
                     'bodyTextCenter' => true,
                  ])
               @elseif ($offlineReview)
                  @include('includes.panel', [
                     'type' => 'info',
                     'headerTitle' => Lang::get("client.success"),
                     'bodyContent' => Lang::get("client.invoiceofflinepaid"),
                     'bodyTextCenter' => true,
                  ])
               @endif
            </div>
            <div class="col-sm-6">
               <strong>{{Lang::get("client.invoicespayto")}}</strong>
               <address class="small-text">
                  {!!$payto!!}
                  @if ($taxCode)
                     <br />{{$taxIdLabel}}: {{$taxCode}}
                  @endif
               </address>
            </div>
            <div class="col-sm-6 text-lg-right">
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
            <div class="col-sm-6">
               <div class="form-group row">
                  <label
                     class="small-text col-sm-12 col-lg-5 col-form-label">{{Lang::get("client.paymentmethod")}}</label>
                  <div class="col-sm-12 col-lg-7 d-flex">
                     @if ($status == "Unpaid" && $allowchangegateway)
                        <form method="post" action="" class="form-inline">
                           @csrf
                           {!!$gatewaydropdown!!}
                        </form>
                     @else
                        <p class="pt-2">
                           {{$paymentmethod}}@if ($paymethoddisplayname) ({{$paymethoddisplayname}})@endif
                        </p>
                     @endif
                  </div>
               </div>
            </div>
            <div class="col-sm-6 text-lg-right">
               <strong>{{Lang::get("client.invoicesdatecreated")}}</strong><br>
               <span class="small-text">{{$date}}<br><br></span>
            </div>
         </div>

         @if ($manualapplycredit)
            <div class="card">
               <div class="card-header" id="deposit">
                  <h5 class="card-title text-white mb-0">{{Lang::get("client.invoiceaddcreditapply")}}</h5>
               </div>
               <div class="card-body">
                  <form method="post" action="">
                     @csrf
                     <input type="hidden" name="applycredit" value="true" />
                     {{Lang::get("client.invoiceaddcreditdesc1")}} <strong>{{$totalcredit}}</strong>. {{Lang::get("client.invoiceaddcreditdesc2")}} {{Lang::get("client.invoiceaddcreditamount")}}:
                     <div class="row d-flex justify-content-center mt-3">
                        <div class="col-12">
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

         <div class="card">
            <div class="card-header">
               <h5 class="card-title">{{Lang::get("client.invoicelineitems")}}</h5>
            </div>
            <div class="card-body">
               <div class="panel-body">
                  <div class="table-responsive">
                     <table class="table table-condensed">
                        <thead>
                           <tr>
                              <td><strong>{{Lang::get("client.invoicesdescription")}}</strong></td>
                              <td width="20%" class="text-right"><strong>{{Lang::get("client.invoicesamount")}}</strong></td>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($invoiceitems as $item)
                              <tr>
                                 <td>{!!$item['description']!!}@if ($item['taxed'] == "true") *@endif</td>
                                 <td class="text-right">{{$item['amount']}}</td>
                              </tr>
                           @endforeach
                           <tr>
                              <td class="total-row text-right"><strong>{{Lang::get("client.invoicessubtotal")}}</strong></td>
                              <td class="total-row text-right">{{$subtotal}}</td>
                           </tr>
                           @if ($taxname)
                              <tr>
                                 <td class="total-row text-right"><strong>{{ $taxrate }}%
                                       {{ $taxname }}</strong></td>
                                 <td class="total-row text-right">{{ $tax }}</td>
                              </tr>
                           @endif
                           @if ($taxname2)
                              <tr>
                                 <td class="total-row text-right"><strong>{{ $taxrate2 }}%
                                       {{ $taxname2 }}</strong></td>
                                 <td class="total-row text-right">{{ $tax2 }}</td>
                              </tr>
                           @endif
                           <tr>
                              <td class="total-row text-right"><strong>{{Lang::get("client.invoicescredit")}}</strong></td>
                              <td class="total-row text-right">{{$credit}}</td>
                           </tr>
                           <tr>
                               <td class="total-row text-right"><strong>{{Lang::get("client.invoicestotal")}}</strong></td>
                              <td class="total-row text-right">
                                 <div class="d-flex justify-content-end align-items-center">
                                    <span>{{$total}}</span>
                                    <button class="btn btn-link ml-2" onclick="copyAmount('{{ preg_replace('/[^0-9,]/', '', $total) }}')" title="Copy Amount">
                                       <i class="fas fa-copy"></i>
                                    </button>
                                 </div>
                              </td>
                           </tr>
                             @if ($totalDeposit ?? 0)
                              <tr>
                                 <td class="total-row text-right"><strong>Invoice Deposit</strong></td>
                                 <td class="total-row text-right">{{ \App\Helpers\Format::Price($totalDeposit) }}</td>
                              </tr>
                           @endif
                        </tbody>
                     </table>
                  </div>
               </div>

            </div>
         </div>
         @if ($taxrate)
            <p class="text-right font-weight-bold"><span class="font-weight-bold">(*) </span>
               {{ __('client.invoicestaxindicator') }}</p>
         @endif
         
         <br>
         <br>
         <br>
         @if ($taxrate)
            <h5>Tax Information</h5>
            <p>Faktur pajak hanya dapat diminta melalui layanan support tiket dengan melampirkan NPWP dan SPPKP perusahaan
               anda. Maksimal 7 Hari terhitung sejak tanggal tagihan ini tercatat dibayar pada sistem. Tanggal faktur pajak
               adalah tanggal pembayaran dan/atau tanggal layanan selesai diproses. Apabila memotong PPh pasal 23, wajib
               mengirimkan bukti potong ke Finance paling lambat 20 Hari setelah konfirmasi pembayaran. Apabila tidak
               mengirimkan bukti potong, maka pembayaran anda akan dianggap kurang bayar Dan dapat mengakibatkan layanan
               anda
               dihentikan sementara.</p>

            <div class="text-danger">
               <p>Pemotongan PPH23 tidak beserta dengan Nominal Kode Unik</p>
            </div>
         @endif

         <div class="table-responsive">
            <table class="table table-striped mt-4">
               <thead>
                  <tr>
                     <th>{{ __('client.invoicetransdate') }}</th>
                     <th>{{ __('client.orderpaymentmethod') }}</th>
                     <th>{{ __('client.invoicetransid') }}</th>
                     <th>{{ __('client.invoicetransamount') }}</th>
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

         <div class="text-center">
            <a href="{{ route('dl') }}?type=i&amp;id={{ $invoiceid }}">
               <button class="btn btn-success px-3"><i class="fas fa-cloud-download-alt mr-2"></i>{{Lang::get("client.invoicesdownload")}}</button>
            </a>
            <button class="btn btn-warning px-3" onclick="javascript:window.print()"><i
                  class="fas fa-print mr-2"></i>{{Lang::get("client.print")}}</button>
         </div>


      </div>
   @endif

   <script>
function copyAmount(amount) {
    // Buat element textarea temporary
    const textarea = document.createElement('textarea');
    textarea.value = amount;
    document.body.appendChild(textarea);
    
    // Select dan copy text
    textarea.select();
    document.execCommand('copy');
    
    // Hapus element temporary
    document.body.removeChild(textarea);
    
    // Tampilkan sweet alert dengan harga yang disalin
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: `Jumlah Rp ${amount} berhasil disalin ke clipboard`,
        showConfirmButton: false,
        timer: 1500
    });
}
</script>
</body>

</html>
