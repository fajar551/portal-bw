<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <title>{{ $CompanyName }} - Invoice #{{ $invoice['id'] }}</title>
   <!-- App favicon -->
   <link rel="shortcut icon" href="{{ Theme::asset('assets/images/favicon.ico') }}" />

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
   <div class="container invoice-container">
      @if (Session::has('success'))
         <div class="alert alert-success">
            {{ Session::get('success') }}
            @php
               Session::forget('success');
            @endphp
         </div>
      @endif
      @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif
      <div class="row header">
         <div class="col-md-7">
            <p><img src="{{ $logo }}" title="{{ $CompanyName }}" width="100"></p>
            <h3 class="mt-5">Invoice #{{ $invoice['id'] }}</h3>
         </div>
         <div class="col-sm-5 text-right">
            <div class="invoice-status">
               <span class="h3 {{ strtolower($invoice['status']) }}">{{ $invoice['status'] }}</span>
            </div>
            <div class="small-text"> Due Date: {{ $invoice['duedate'] }}</div>
            @if ($invoice['status'] == 'Unpaid')
               <div class="payment-btn-container my-3" align="right">
                  {{-- <button class="btn btn-success">Process to Payment</button> --}}
                  {!! $paymentbutton !!}
               </div>
            @endif
         </div>
      </div>
      @php
         //Set From Default Address company in general settings admin area
         $invoicePayTo = Cfg::getValue('InvoicePayTo');
      @endphp
      <div class="row">
         <div class="col-sm-6">
            <strong>Pay To:</strong>
            <address class="small-text">
               {{-- PT Qwords Company International<br>
               Gedung Cyber 1 Lantai 3<br>
               Jl.Kuningan Barat no.8, Jakarta 12710 - Indonesia<br>
               Call Center : 08041808888 / +62 21 3970-8800<br>
               Jakarta - Bandung - Yogyakarta - Surabaya<br>
               www.Qwords.co.id --}}
               {!! $invoicePayTo !!}
            </address>
         </div>
         <div class="col-sm-6 text-lg-right">
            <strong>Invoiced To</strong>
            <address class="small-text">
               {{ $client->companyname }}<br> {{ $client->firstname . ' ' . $client->lastname }}<br>
               {{ $client->address1 }}, {{ $client->city }}, {{ $client->state }}, <br>
            </address>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-6">
            <div class="mb-3">
               @if ($invoice['status'] == 'Paid')
                  <i class="fa fa-check-circle"></i>
               @else
                  <div class="font-weight-bolder">Current Payment Method: <span id="current-gate"
                        class="text-info">{{ $invoice['paymentGatewayName'] }}</span>
                  </div>
               @endif
            </div>
            <div class="form-group row">
               <label
                  class="small-text col-sm-12 col-lg-5 col-form-label">{{ $invoice['status'] == 'Paid' ? 'Payment Method' : 'Change Payment Method To : ' }}</label>
               <div class="col-sm-12 col-lg-7 d-flex">
                  @if ($invoice['status'] == 'Unpaid' && $allowchangegateway == 'on')
                     <form method="POST" action="" class="w-100" id="paymentmethods">
                        {{ csrf_field() }}
                        {{-- @method('PUT') --}}
                        <input type="hidden" name="id" value="{{ $invoice['id'] }}">
                        <div class="d-flex align-items-center">
                           <select id="drop-payment" name="paymentmethod"
                              onchange="updatePaymentMethods('drop-payment', event)" class="form-control w-100">
                              <option value="none">Choose Payment Method</option>
                              @foreach ($gateway as $k => $v)
                                 <option value="{{ $k }}">{{ $v }}</option>
                              @endforeach
                           </select>

                           <div class="spinner-border spinner-border-sm ml-3" id="loading-spin" role="status" hidden>
                              <span class="sr-only">Loading...</span>
                           </div>
                        </div>
                        <div class="mt-2" id="error-payment-change" hidden>

                        </div>
                     </form>
                  @else
                     @if ($invoice['paymentmethod'])
                        <p class="pt-2">{{ $invoice['paymentGatewayName'] }}</p>
                     @endif
                  @endif
               </div>
            </div>
         </div>
         <div class="col-sm-6 text-lg-right">
            <strong>Invoice Date:</strong><br>
            <span class="small-text">{{ $invoice['date'] }}<br><br></span>
         </div>
      </div>

      @if ($invoice['status'] == 'Unpaid' && $manualapplycredit)
         <div class="card">
            <div class="card-header" id="deposit">
               <h5 class="card-title text-white mb-0">Apply Deposit</h5>
            </div>
            <div class="card-body">
               <form method="post"
                  action="{{ route('pages.services.mydomains.viewinvoiceweb.applycredit', $invoice['id']) }}">
                  {{ csrf_field() }}
                  <input type="hidden" name="id" value="{{ $invoice['id'] }}">
                  <input type="hidden" name="option" value="option">
                  <input type="hidden" name="saveoptions" value="true">
                  <input type="hidden" name="userid" value="{{ $invoice['userid'] }}">
                  <input type="hidden" name="paymentmethod" value="{{ $invoice['paymentmethod'] }}">
                  Your credit balance is <strong>{{ $invoice['clientdepositbalance'] }}</strong>. This can be applied
                  to
                  the invoice using the form below.. Enter the amount to apply:
                  <div class="row d-flex justify-content-center mt-3">
                     <div class="col-12">
                        <div class="input-group">
                           <input type="number" name="creditamount" class="form-control">
                           <span class="input-group-btn">
                              <input type="submit" value="Apply Credit" class="btn btn-outline-success">
                           </span>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      @endif

      <div class="card">
         <div class="card-header">
            <h5 class="card-title">Invoice Items</h5>
         </div>
         <div class="card-body">
            <div class="panel-body">
               <div class="table-responsive">
                  <table class="table table-condensed">
                     <thead>
                        <tr>
                           <td><strong>Description</strong></td>
                           <td width="20%" class="text-right"><strong>Amount</strong></td>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach ($item as $r)
                           <tr>
                              <td>
                                 {!! $r['description'] !!}
                                 @if ($r['taxed'])
                                    *
                                 @endif
                              </td>
                              <td class="text-right">{{ $r['rawamount'] }}</td>
                           </tr>
                           {{-- @if ($r['taxed'])
                              <tr>
                                 <td class="total-row text-right"><strong>Tax {{ $r['taxrate'] }}%</strong></td>
                                 <td class="total-row text-right">{{ Format::formatCurrency($r['taxamount']) }}</td>
                              </tr>
                           @endif --}}
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
                           <td class="total-row text-right"><strong>Credit</strong></td>
                           <td class="total-row text-right">{{ $invoice['credit'] }}</td>
                        </tr>
                        <tr>
                           <td class="total-row text-right"><strong>Total</strong></td>
                           <td class="total-row text-right">{{ $invoice['total'] }}</td>
                        </tr>
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
      {{-- Total Tagihan:<br>IDR <input class="form-control" type="text" value="{{ $invoice['clientbalancedue'] }}"
         id="myInput2"> <button class="btn btn-success" onclick="copytext2()"><i
            class="fas fa-paste"></i></button><br></strong></p> --}}
      <div class="row">
         <div class="col-sm-12 col-lg-6" {{ $invoice['status'] == 'Paid' ? 'hidden' : '' }}>
            <h5>Total Pay:</h5>
            <div class="d-flex">
               <input type="text" class="form-control w-75" value="{{ $invoice['total'] }}" id="myInput2">
               <button class="btn btn-success-qw px-3"><i class="fas fa-paste mr-2"></i> Copy</button>
            </div>
         </div>
         {{-- <div class="col-sm-12 col-lg-6">
            <h6 class="font-weight-bold">Metode ini bukan Untuk Pembayaran via Teller/Transfer Antar Bank</h6>
            <div id="accordion" class="custom-accordion mb-4">

               <div class="card mb-0">
                  <div class="card-header" id="headingOne">
                     <h5 class="m-0 font-size-15">
                        <a class="d-block pt-2 pb-2 text-dark" data-toggle="collapse" href="#collapseOne"
                           aria-expanded="true" aria-controls="collapseOne">
                           ATM Mandiri <span class="float-right"><i
                                 class="mdi mdi-chevron-down accordion-arrow"></i></span>
                        </a>
                     </h5>
                  </div>
                  <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion"
                     style="">
                     <div class="card-body">
                        <ol>
                           <li>Pilih Menu Bayar/Beli</li>
                           <li>Pilih Lainnya</li>
                           <li>Pilih Multi Payment</li>
                           <li>Input 88049 sebagai Kode Institusi</li>
                           <li>Input Virtual Account Number 8804960800045244</li>
                           <li>Input Nominal 555521</li>
                           <li>Pilih Benar</li>
                           <li>Pilih Ya</li>
                           <li>Pilih Ya</li>
                           <li>Ambil bukti bayar anda</li>
                           <li>Selesai</li>
                        </ol>
                     </div>
                  </div>
               </div> <!-- end card-->

               <div class="card mb-0">
                  <div class="card-header" id="headingTwo">
                     <h5 class="m-0 font-size-15">
                        <a class="collapsed d-block pt-2 pb-2 text-dark" data-toggle="collapse" href="#collapseTwo"
                           aria-expanded="false" aria-controls="collapseTwo">
                           Mobile Banking Mandiri <span class="float-right"><i
                                 class="mdi mdi-chevron-down accordion-arrow"></i></span>
                        </a>
                     </h5>
                  </div>
                  <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                     <div class="card-body">
                        <ol>
                           <li>Login Mobile Banking (Pastikan Anda menggunakan aplikasi Mandiri Online terbaru)</li>
                           <li>Pilih Bayar</li>
                           <li>Pilih Multi Payment</li>
                           <li> Input Nicepay sebagai Penyedia Jasa</li>
                           <li>Input Nomor Virtual Account 8804960800045244</li>
                           <li>Pilih Lanjut</li>
                           <li>Input Nominal 555521</li>
                           <li>Input OTP and PIN</li>
                           <li>Pilih OK</li>
                           <li>Bukti bayar ditampilkan</li>
                           <li>Selesai</li>
                        </ol>
                     </div>
                  </div>
               </div> <!-- end card-->

               <div class="card mb-0">
                  <div class="card-header" id="headingThree">
                     <h5 class="m-0 font-size-15">
                        <a class="collapsed d-block pt-2 pb-2 text-dark" data-toggle="collapse" href="#collapseThree"
                           aria-expanded="false" aria-controls="collapseThree">
                           Internet Banking Mandiri <span class="float-right"><i
                                 class="mdi mdi-chevron-down accordion-arrow"></i></span>
                        </a>
                     </h5>
                  </div>
                  <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                     data-parent="#accordion">
                     <div class="card-body">
                        <ol>
                           <li>Login Internet Banking</li>
                           <li>Pilih Bayar</li>
                           <li>Pilih Multi Payment</li>
                           <li>Input Nicepay sebagai Penyedia Jasa</li>
                           <li>Input Nomor Virtual Account 8804960800045244 sebagai Kode Bayar</li>
                           <li>Ceklis IDR</li>
                           <li>Input Nominal 555521</li>
                           <li>Klik Lanjutkan</li>
                           <li>Bukti bayar ditampilkan</li>
                           <li>Selesai</li>
                        </ol>
                     </div>
                  </div>
               </div> <!-- end card-->

            </div>
         </div> --}}
      </div>
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
               @if (!empty($transactions))
                  @foreach ($transactions as $transaction => $inv)
                     <tr>
                        <td>{{ $inv['date'] }}</td>
                        <td>{{ $inv['gateway'] }}</td>
                        <td>{{ $inv['transid'] }}</td>
                        <td>{{ $inv['amount'] }}</td>
                     </tr>
                  @endforeach
               @else
                  <tr class="text-center">
                     <td colspan="4">Tidak ada transaksi terkait ditemukan</td>
                  </tr>
               @endif
               <tr>
                  <td class="text-right font-weight-bold" colspan="3">Sisa Tagihan</td>
                  <td>{{ $balance }}</td>
               </tr>
            </tbody>
         </table>
      </div>

      <div class="text-center">
         <a href="{{ route('dl') }}?type=i&amp;id={{ $invoiceid }}">
            <button class="btn btn-success px-3"><i class="fas fa-cloud-download-alt mr-2"></i>Download</button>
         </a>
         <button class="btn btn-warning px-3" onclick="javascript:window.print()"><i
               class="fas fa-print mr-2"></i>Print</button>
      </div>


   </div>


   <script type="text/javascript">
      function copytext2() {
         /* Get the text field */
         var copyText = document.getElementById("myInput2");

         /* Select the text field */
         copyText.select();
         copyText.setSelectionRange(0, 99999); /* For mobile devices */

         /* Copy the text inside the text field */
         navigator.clipboard.writeText(copyText.value);
      }


      function updatePaymentMethods(id, event) {
         event.preventDefault();
         $('#loading-spin').removeAttr('hidden', true);
         $('#error-payment-change').attr('hidden', true);
         $('#error-msg').remove();
         $.ajax({
            url: "{!! route('pages.services.mydomains.viewinvoiceweb.updatepayment') !!}",
            headers: {
               'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            type: 'post',
            data: $('form#paymentmethods')
               .serialize(), // Remember that you need to have your csrf token included
            dataType: 'json',
            success: function(_response) {
               //    console.log(_response);
               $('#current-gate').html(_response);
               $('#drop-payment').val('none');
               $('#loading-spin').attr('hidden', true);
               location.reload();
            },
            error: function(_response) {
               console.log(_response);
               $('#error-payment-change').removeAttr('hidden', true).prepend(
                  `<div class="text-danger" id="error-msg">
                    <strong>` + _response.statusText + `</strong> Error Code (` + _response.status + `) dolor sit amet consectetur, adipisicing elit. Quaerat, earum.
               </div>`);
               $('#loading-spin').attr('hidden', true);
            }

         })
      }
   </script>
</body>

</html>
