<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <title>{{ $companyname }} - Invoice #{{ $id }}</title>
   <!--
    <link href="{{ Theme::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" media="all"  rel="stylesheet" type="text/css" />
    <link href="{{ Theme::asset('assets/css/invoice.css') }}" type="text/css" media="all"  rel="stylesheet" />
    -->
</head>
<style>
   body {
      background-image: url('https://images.unsplash.com/photo-1557683316-973673baf926?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=829&q=80');
      background-size: cover;
      margin: 0;
      padding: 10px;
      /* color: #fff; */
      /* font-family: 'Helvetica', sans-serif; */
   }

   .section-title {
      font-weight: 600;
      color: #fcb92b
   }

   .client-name {
      padding: 10px 10px 0;
      color: #4b79a1;
      font-weight: bold;
      font-size: 18px
   }

   .client-address {
      padding: 0 10px;
      color: #b4b4b4;
   }

   .invoice-date {
      padding: 10px 10px 0;
   }

   .invoice-container {
      margin: 15px auto;
      padding: 70px;
      max-width: 850px;
      background-color: #ffffff;
      border: 1px solid #ccc;
      -moz-border-radius: 6px;
      -webkit-border-radius: 6px;
      -o-border-radius: 6px;
      border-radius: 6px;
   }

   .row {
      margin-right: -15px;
      margin-left: -15px;
   }

   .container {
      /* padding-top: 30px;
       */
      margin: 0;
      padding: 0;
   }

   .company {
      color: #fff;
      font-size: 14px;
   }

   .company h4 {
      margin-top: -30px;
      margin-bottom: 0;
   }

   table.invoiceaddres p {
      line-height: 8px;
   }

   table {
      border-spacing: 0;
      border-collapse: collapse;
   }

   td,
   th {
      padding: 0;
   }

   table {
      border-collapse: collapse !important;
   }

   /* .table td,
   .table th {
      background-color: #fff !important;
   } */

   /* .table-bordered th,
   .table-bordered td {
      border: 1px solid #ddd !important;
   } */

   .table {
      width: 100%;
      max-width: 100%;
      margin-bottom: 20px;
   }

   .table>thead>tr>th,
   .table>tbody>tr>th,
   .table>tfoot>tr>th,
   .table>thead>tr>td,
   .table>tbody>tr>td,
   .table>tfoot>tr>td {
      padding: 8px;
      line-height: 1.42857143;
      vertical-align: top;
      border-top: 1px solid #ddd;
   }

   .table>thead>tr>th {
      vertical-align: bottom;
      border-bottom: 2px solid #ddd;
   }

   .table>caption+thead>tr:first-child>th,
   .table>colgroup+thead>tr:first-child>th,
   .table>thead:first-child>tr:first-child>th,
   .table>caption+thead>tr:first-child>td,
   .table>colgroup+thead>tr:first-child>td,
   .table>thead:first-child>tr:first-child>td {
      border-top: 0;
   }

   .table>tbody+tbody {
      border-top: 2px solid #ddd;
   }

   /* .table .table {
      background-color: #fff;
   } */

   .table-condensed>thead>tr>th,
   .table-condensed>tbody>tr>th,
   .table-condensed>tfoot>tr>th,
   .table-condensed>thead>tr>td,
   .table-condensed>tbody>tr>td,
   .table-condensed>tfoot>tr>td {
      padding: 5px;
   }

   .table-bordered {
      border: 1px solid #ddd;
   }

   .table-bordered>thead>tr>th,
   .table-bordered>tbody>tr>th,
   .table-bordered>tfoot>tr>th,
   .table-bordered>thead>tr>td,
   .table-bordered>tbody>tr>td,
   .table-bordered>tfoot>tr>td {
      border: 1px solid #ddd;
      margin: 0;
      padding: 0 5px;
   }

   .table-bordered>thead>tr>th,
   .table-bordered>thead>tr>td {
      border-bottom-width: 2px;
   }

   /* .table-striped>tbody>tr:nth-of-type(odd) {
      background-color: #f9f9f9;
   } */

   .table-hover>tbody>tr:hover {
      background-color: #f5f5f5;
   }

   /* table col[class*="col-"] {
      position: static;
      display: table-column;
      float: none;
   }

   table td[class*="col-"],
   table th[class*="col-"] {
      position: static;
      display: table-cell;
      float: none;
   } */

   /* .table>thead>tr>td.active,
   .table>tbody>tr>td.active,
   .table>tfoot>tr>td.active,
   .table>thead>tr>th.active,
   .table>tbody>tr>th.active,
   .table>tfoot>tr>th.active,
   .table>thead>tr.active>td,
   .table>tbody>tr.active>td,
   .table>tfoot>tr.active>td,
   .table>thead>tr.active>th,
   .table>tbody>tr.active>th,
   .table>tfoot>tr.active>th {
      background-color: #f5f5f5;
   }

   .table-hover>tbody>tr>td.active:hover,
   .table-hover>tbody>tr>th.active:hover,
   .table-hover>tbody>tr.active:hover>td,
   .table-hover>tbody>tr:hover>.active,
   .table-hover>tbody>tr.active:hover>th {
      background-color: #e8e8e8;
   }

   .table>thead>tr>td.success,
   .table>tbody>tr>td.success,
   .table>tfoot>tr>td.success,
   .table>thead>tr>th.success,
   .table>tbody>tr>th.success,
   .table>tfoot>tr>th.success,
   .table>thead>tr.success>td,
   .table>tbody>tr.success>td,
   .table>tfoot>tr.success>td,
   .table>thead>tr.success>th,
   .table>tbody>tr.success>th,
   .table>tfoot>tr.success>th {
      background-color: #dff0d8;
   }

   .table-hover>tbody>tr>td.success:hover,
   .table-hover>tbody>tr>th.success:hover,
   .table-hover>tbody>tr.success:hover>td,
   .table-hover>tbody>tr:hover>.success,
   .table-hover>tbody>tr.success:hover>th {
      background-color: #d0e9c6;
   }

   .table>thead>tr>td.info,
   .table>tbody>tr>td.info,
   .table>tfoot>tr>td.info,
   .table>thead>tr>th.info,
   .table>tbody>tr>th.info,
   .table>tfoot>tr>th.info,
   .table>thead>tr.info>td,
   .table>tbody>tr.info>td,
   .table>tfoot>tr.info>td,
   .table>thead>tr.info>th,
   .table>tbody>tr.info>th,
   .table>tfoot>tr.info>th {
      background-color: #d9edf7;
   }

   .table-hover>tbody>tr>td.info:hover,
   .table-hover>tbody>tr>th.info:hover,
   .table-hover>tbody>tr.info:hover>td,
   .table-hover>tbody>tr:hover>.info,
   .table-hover>tbody>tr.info:hover>th {
      background-color: #c4e3f3;
   }

   .table>thead>tr>td.warning,
   .table>tbody>tr>td.warning,
   .table>tfoot>tr>td.warning,
   .table>thead>tr>th.warning,
   .table>tbody>tr>th.warning,
   .table>tfoot>tr>th.warning,
   .table>thead>tr.warning>td,
   .table>tbody>tr.warning>td,
   .table>tfoot>tr.warning>td,
   .table>thead>tr.warning>th,
   .table>tbody>tr.warning>th,
   .table>tfoot>tr.warning>th {
      background-color: #fcf8e3;
   }

   .table-hover>tbody>tr>td.warning:hover,
   .table-hover>tbody>tr>th.warning:hover,
   .table-hover>tbody>tr.warning:hover>td,
   .table-hover>tbody>tr:hover>.warning,
   .table-hover>tbody>tr.warning:hover>th {
      background-color: #faf2cc;
   }

   .table>thead>tr>td.danger,
   .table>tbody>tr>td.danger,
   .table>tfoot>tr>td.danger,
   .table>thead>tr>th.danger,
   .table>tbody>tr>th.danger,
   .table>tfoot>tr>th.danger,
   .table>thead>tr.danger>td,
   .table>tbody>tr.danger>td,
   .table>tfoot>tr.danger>td,
   .table>thead>tr.danger>th,
   .table>tbody>tr.danger>th,
   .table>tfoot>tr.danger>th {
      background-color: #f2dede;
   }

   .table-hover>tbody>tr>td.danger:hover,
   .table-hover>tbody>tr>th.danger:hover,
   .table-hover>tbody>tr.danger:hover>td,
   .table-hover>tbody>tr:hover>.danger,
   .table-hover>tbody>tr.danger:hover>th {
      background-color: #ebcccc;
   }

   .table-responsive {
      min-height: .01%;
      overflow-x: auto;
   }

   @media screen and (max-width: 767px) {
      .table-responsive {
         width: 100%;
         margin-bottom: 15px;
         overflow-y: hidden;
         -ms-overflow-style: -ms-autohiding-scrollbar;
         border: 1px solid #ddd;
      }

      .table-responsive>.table {
         margin-bottom: 0;
      }

      .table-responsive>.table>thead>tr>th,
      .table-responsive>.table>tbody>tr>th,
      .table-responsive>.table>tfoot>tr>th,
      .table-responsive>.table>thead>tr>td,
      .table-responsive>.table>tbody>tr>td,
      .table-responsive>.table>tfoot>tr>td {
         white-space: nowrap;
      }

      .table-responsive>.table-bordered {
         border: 0;
      }

      .table-responsive>.table-bordered>thead>tr>th:first-child,
      .table-responsive>.table-bordered>tbody>tr>th:first-child,
      .table-responsive>.table-bordered>tfoot>tr>th:first-child,
      .table-responsive>.table-bordered>thead>tr>td:first-child,
      .table-responsive>.table-bordered>tbody>tr>td:first-child,
      .table-responsive>.table-bordered>tfoot>tr>td:first-child {
         border-left: 0;
      }

      .table-responsive>.table-bordered>thead>tr>th:last-child,
      .table-responsive>.table-bordered>tbody>tr>th:last-child,
      .table-responsive>.table-bordered>tfoot>tr>th:last-child,
      .table-responsive>.table-bordered>thead>tr>td:last-child,
      .table-responsive>.table-bordered>tbody>tr>td:last-child,
      .table-responsive>.table-bordered>tfoot>tr>td:last-child {
         border-right: 0;
      }

      .table-responsive>.table-bordered>tbody>tr:last-child>th,
      .table-responsive>.table-bordered>tfoot>tr:last-child>th,
      .table-responsive>.table-bordered>tbody>tr:last-child>td,
      .table-responsive>.table-bordered>tfoot>tr:last-child>td {
         border-bottom: 0;
      }
   } */

   .status {
      width: 402px;
      text-align: center;
      background: red;
      font-size: 30px;
      line-height: 61px;
      color: #ffff;
      position: absolute;
      right: -180px;
      transform: rotate(50deg);
      top: 20px;
   }

   .draft {
      background: #888;
   }

   .unpaid {
      background: #cc0000;
   }

   .paid {
      background: #779500;
   }

   .refunded {
      background: #224488;
   }

   .cancelled {
      background: #888;
   }

   .collections {
      background: #ffcc00;
   }

   .date {
      margin-top: 20px;
   }

   .card {
      background: #ffffff;
      width: 100%;
      height: 80px;
      border-radius: 8px;
      border-bottom: 2px solid #fcb92b;
      margin-top: 10px
   }

   .card-table {
      background: #ffffff;
      width: 98%;
      height: auto;
      border-radius: 8px;
      border-bottom: 2px solid #fcb92b;
      padding: 5px;
   }

   .card-information {
      background: #ffffff;
      width: 100%;
      height: auto;
      border-radius: 8px;
      border-bottom: 2px solid #fcb92b;
      padding: 5px;
   }

   .colored-hr {
      border: 1px solid #fcb92b;
      background: #fcb92b;
      min-height: 1px;
      margin-top: 10px;
   }

   .card-ads {
      position: relative;
      border-radius: 8px;
      background: #ffffff;
      width: 100%;
      height: 80px;
      margin-top: 10px;
      background-image: url('https://i.ibb.co/zhRdhMt/bg-ads.jpg');
      background-position: 50% 50%;
   }

   .overlay {
      background: #00000077;
      position: absolute;
      width: 99%;
      height: 100%;
      border-radius: 8px;
      padding-left: 5px;
   }

</style>

<body>
   <table border="0" width="100%">
      <tr>
         <td width="70%" style="border-top:0 ;">
            <img id="logo" src="{{ $logo ?? '' }}" alt="logo" title="{{ $companyname }}"
               style="width: 100px; max-width: 100px;">
            <div class="company">
               {!! $payto !!}
            </div>
         </td>
         <td width="30%" style="border-top:0; text-align: right;">
            <div style="margin-top: -90px">
               <div class="section-title">Subcription Summary</div>
               <div class="card">
                  <table border="0" width="100%" style="height:100%; ">
                     <tr>
                        <td width="50%">
                           <div style="margin-left: 10px">
                              <h5 style="margin-bottom: -20px;">Nomor Tagihan</h5>
                              <h6 style="font-weight: 400; font-style:italic">Invoice Number</h6>
                           </div>
                        </td>
                        <td width="50%" style="vertical-align: middle; text-align: center;">
                           <div style="font-weight: bold; color: #6C757D;">
                              #{{ $id }}
                           </div>
                        </td>
                     </tr>
                  </table>
               </div>
            </div>
         </td>
      </tr>
   </table>
   <div class="colored-hr"></div>
   <table border="0" width="100%" class="invoiceaddres">
      <tr>
         <td width="80%" style="border-top:0; padding-right: 5px;">
            <div class="   card">
               <div class="client-name">
                  {{ ucwords($clientsdetails['firstname']) }} {{ ucwords($clientsdetails['lastname']) }}
               </div>
               <div class="client-address">
                  {{ $clientsdetails['companyname'] }}, {{ $clientsdetails['city'] }}, {{ $clientsdetails['state']}},
                  {{ $clientsdetails['postcode'] }}.
               </div>
            </div>
         </td>
         <td width="20%" style="border-top:0; padding-left: 5px;">
            <div class="card">
               <div style="margin-left: 10px;">
                  <h5 style=" margin-bottom: -20px; margin-top: 8px;">ID Pelanggan</h5>
                  <h6 style="font-weight: 400; font-style:italic">Customer ID</h6>
                  <h3 style="margin-top: -15px; margin-left: -10px; text-align:center; color: #6C757D;">
                     {{ $clientsdetails['userid'] }}</h3>
               </div>

            </div>
         </td>
      </tr>
   </table>

   <table border="0" width="100%" class="invoiceaddres" style="margin-top: -5px">
      <tr>
         <td width="40%" style="padding: 5px">
            <div class="card">
               <div class="invoice-date">
                  <h5 style="margin-bottom: -20px; margin-top: -5px;">Tanggal Tagihan</h5>
                  <h6 style="font-weight: 400; font-style:italic; margin-bottom: 5px">Inovice Date</h6>
                  <hr>
                  <h4 style="margin-top: -5px; text-align: right; color: #09b363;">
                     {{ $date }}
                  </h4>
               </div>
            </div>
         </td>
         <td width="40%" style="padding: 5px">
            <div class="card">
               <div class="invoice-date">
                  <h5 style="margin-bottom: -20px; margin-top: -5px;">Tanggal Jatuh Tempo</h5>
                  <h6 style="font-weight: 400; font-style:italic; margin-bottom: 5px">Due Date</h6>
                  <hr>
                  <h4 style="margin-top: -5px; text-align: right; color: #f03c3c;">
                     {{ $duedate }}
                  </h4>
               </div>
            </div>
         </td>
         <td width="40%" style="padding: 5px">
            <div class="card">
               <div class="invoice-date">
                  <h5 style="margin-bottom: -20px; margin-top: -5px;">Jumlah Tagihan</h5>
                  <h6 style="font-weight: 400; font-style:italic; margin-bottom: 5px">Total Charge
                  </h6>
                  <hr>
                  <h4 style="margin-top: -5px; text-align: center">
                     {{ $total }}
                  </h4>
               </div>
            </div>
         </td>
      </tr>
   </table>

   <div class="card-table" style="margin-top: 5px">
      <div style="margin: 9px 3px">
         <h5 style="margin-bottom: -20px; margin-top: -5px;">Rincian Tagihan</h5>
         <h6 style="font-weight: 400; font-style:italic; margin-bottom: 5px">Payment Details
         </h6>
      </div>
      <table width="100%" class="table table-bordered">
         <thead>
            <tr>
               <td><span style="font-weight: bold;">Description</span></td>
               <td width="20%" class="text-center"><span style="font-weight: bold;">Amount</span></td>
            </tr>
         </thead>
         <tbody>
            @foreach ($invoiceitems as $r)
               <tr>
                  <td>
                     <p style="font-size: 12px">{!! $r['description'] !!}</p>
                  </td>
                  <td class="text-center" align="right">
                     <p style="font-size: 12px">{{ $r['rawamount'] }}</p>
                  </td>
               </tr>
            @endforeach
            <tr>
               <td align="right" class="total-row text-right"><span style="font-size: 12px;">Tax
                     {{ $taxrate }} %</span></td>
               <td align="right" class="total-row"><span
                     style="font-size: 12px; font-weight: bold;">{{ $tax }}</span></td>
            </tr>
            <tr>
               <td align="right" class="total-row text-right"><span style="font-size: 12px">Credit</span></td>
               <td align="right" class="total-row"><span
                     style="font-size: 12px; font-weight: bold;">{{ $credit }}</span></td>
            </tr>
            <tr>
               <td align="right" class="total-row text-right"><span style="font-size: 12px">Total</span></td>
               <td align="right" class="total-row"><span
                     style="font-size: 12px; font-weight: bold;">{{ $total }}</span></td>
            </tr>
         </tbody>
      </table>
   </div>
   <table border="0" width="100%" class="invoiceaddres" style="margin-top: 10px">
      <tr>
         <td width="50%" style="padding-right: 10px" align="justify">
            <div class="card-information">
               <div style="padding: 5px;">
                  <h5 style="margin-bottom: -20px; margin-top:0 ;">Informasi Penting</h5>
                  <h6 style="font-weight: 400; font-style:italic; margin-bottom: 5px">Important Information
                  </h6>
               </div>
               <hr>
               <div style="padding: 5px;">
                  <span style="font-size: 10px; font-weight: bold">
                     Jasa internet dikenakan PPh 23 sebesar 2% berdasarkan Peraturan Menteri Keuangan nomor
                     141/PMK.03/2015, berlaku mulai 26 Agustus 2015. Jika Anda terdaftar sebagai pelanggan Perusahaan,
                     pastikan nilai pembayaran Anda sudah dipotong PPh 23.
                  </span>
               </div>
               <div style="padding: 5px;">
                  <span style="font-size: 10px; font-weight: 400; font-style: italic;">
                     Internet services are subject to Income Tax Article 23 in accordance with Peraturan Menteri
                     Keuangan nomor 141/PMK.03/2015, valid from August 26, 2015. You are registered as a customer of the
                     Company, please make sure your payment amount already deducted Income Tax 23.
                  </span>
               </div>
            </div>
         </td>
         <td width="50%" style="padding-left: 10px; padding-right: 10px;">
            <div class="card-information">
               <div style="padding: 5px;">
                  <h5 style="margin-bottom: -20px; margin-top:0 ;">Informasi Faktur Pajak</h5>
                  <h6 style="font-weight: 400; font-style:italic; margin-bottom: 5px">Tax Receipt Information
                  </h6>
               </div>
               <hr>
               <div>
                  <table>
                     <tr>
                        <td style="width: 50%;">
                           <div style="font-size: 12px; font-weight: bold; margin-right: 12px;">Nama Perusahaan : </div>
                           <div style="font-size: 12px; font-style: italic margin-bottom: 20px;">Company Name</div>
                        </td>
                        <td style="width: 50%;">
                           <div style="font-size: 12px; text-align: left; margin-top: -10px;">PT. Relabs Net DayaCipta
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <td style="width: 70%;">
                           <div style="font-size: 12px; font-weight: bold; margin-right: 12px;">Alamat NPWP : </div>
                           <div style="font-size: 12px; font-style: italic margin-bottom: 20px;">Tax ID Address</div>
                        </td>
                        <td style="width: 30%;">
                           <div style="font-size: 12px; text-align: left; margin-top: 6px;">Jln. Blotan No. 18, Kayen
                              No.18 Wedomartani Ngemplak</div>
                        </td>
                     </tr>
                     <tr>
                        <td style="width: 50%;">
                           <div style="font-size: 12px; font-weight: bold; margin-right: 12px; margin-top: 10px;">NPWP :
                           </div>
                           <div style="font-size: 12px; font-style: italic margin-bottom: 20px;">Tax ID</div>
                        </td>
                        <td style="width: 50%;">
                           <div style="font-size: 12px; text-align: left; margin-top: -1px;">96.880.906.1-542.000</div>
                        </td>
                     </tr>
                  </table>
               </div>
            </div>
         </td>
      </tr>
   </table>
   {{-- <table width="100%" class="table table-condensed table-striped table-bordered" style="margin-bottom: 40px;">
      <thead>
         <tr>
            <td><strong>Transaction Date </strong></td>
            <td><strong>Gateway</strong></td>
            <td><strong>Transaction ID</strong></td>
            <td><strong>Amount</strong></td>
         </tr>
      </thead>
      <tbody>
         @foreach ($transactions as $r)
            <tr>
               <td>{{ $r['date'] }}</td>
               <td align="right">{{ $r['gateway'] }}</td>
               <td align="right">{{ $r['transid'] }}</td>
               <td align="right">{{ $r['amount'] }}</td>
            </tr>
         @endforeach
      </tbody>
   </table> --}}
   {{-- <div class="footer">
      <center>PDF Generated on {{ date('Y-m-d') }}</center>
   </div> --}}
   <div class="card-ads">
      <div class="overlay">
         <table>
            <tr>
               <td width="70%">
                  <img src="{{ $logo }}" width="30px">
               </td>
               <td width="30%">
                  <p style="font-size: 16px; font-weight: 400; color: #fff; position: absolute; left: 40%; top: 0; margin-top: 10px">
                     Browsing, Streaming, Komunikasi Jarak Jauh Tak Pernah
                     <span style="font-weight: bold;">Semudah Ini!</span>
                  </p>
                  <p style="font-size: 10px; font-style: italic; font-weight: 400; color: #fff; position: absolute; left: 40%; bottom: 0;">Dapatkan Kecepatan & Harga Sesuai yang Anda Inginkan</p>
               </td>
            </tr>
         </table>
      </div>
   </div>
</body>

</html>
