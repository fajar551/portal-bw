<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $CompanyName }} - Invoice #{{ $invoice['id'] }}</title>
    <!--
    <link href="{{ Theme::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" media="all"  rel="stylesheet" type="text/css" />
    <link href="{{ Theme::asset('assets/css/invoice.css') }}" type="text/css" media="all"  rel="stylesheet" />
    -->
</head>
<style>
    .invoice-container {
        margin: 15px auto;
        padding: 70px;
        max-width: 850px;
        background-color: #fff;
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
        padding-top: 100px;
    }
    .company h4 {
        margin-top: 30px;
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
  .table td,
  .table th {
    background-color: #fff !important;
  }
  .table-bordered th,
  .table-bordered td {
    border: 1px solid #ddd !important;
  }
  .table {
  width: 100%;
  max-width: 100%;
  margin-bottom: 20px;
}
.table > thead > tr > th,
.table > tbody > tr > th,
.table > tfoot > tr > th,
.table > thead > tr > td,
.table > tbody > tr > td,
.table > tfoot > tr > td {
  padding: 8px;
  line-height: 1.42857143;
  vertical-align: top;
  border-top: 1px solid #ddd;
}
.table > thead > tr > th {
  vertical-align: bottom;
  border-bottom: 2px solid #ddd;
}
.table > caption + thead > tr:first-child > th,
.table > colgroup + thead > tr:first-child > th,
.table > thead:first-child > tr:first-child > th,
.table > caption + thead > tr:first-child > td,
.table > colgroup + thead > tr:first-child > td,
.table > thead:first-child > tr:first-child > td {
  border-top: 0;
}
.table > tbody + tbody {
  border-top: 2px solid #ddd;
}
.table .table {
  background-color: #fff;
}
.table-condensed > thead > tr > th,
.table-condensed > tbody > tr > th,
.table-condensed > tfoot > tr > th,
.table-condensed > thead > tr > td,
.table-condensed > tbody > tr > td,
.table-condensed > tfoot > tr > td {
  padding: 5px;
}
.table-bordered {
  border: 1px solid #ddd;
}
.table-bordered > thead > tr > th,
.table-bordered > tbody > tr > th,
.table-bordered > tfoot > tr > th,
.table-bordered > thead > tr > td,
.table-bordered > tbody > tr > td,
.table-bordered > tfoot > tr > td {
  border: 1px solid #ddd;
}
.table-bordered > thead > tr > th,
.table-bordered > thead > tr > td {
  border-bottom-width: 2px;
}
.table-striped > tbody > tr:nth-of-type(odd) {
  background-color: #f9f9f9;
}
.table-hover > tbody > tr:hover {
  background-color: #f5f5f5;
}
table col[class*="col-"] {
  position: static;
  display: table-column;
  float: none;
}
table td[class*="col-"],
table th[class*="col-"] {
  position: static;
  display: table-cell;
  float: none;
}
.table > thead > tr > td.active,
.table > tbody > tr > td.active,
.table > tfoot > tr > td.active,
.table > thead > tr > th.active,
.table > tbody > tr > th.active,
.table > tfoot > tr > th.active,
.table > thead > tr.active > td,
.table > tbody > tr.active > td,
.table > tfoot > tr.active > td,
.table > thead > tr.active > th,
.table > tbody > tr.active > th,
.table > tfoot > tr.active > th {
  background-color: #f5f5f5;
}
.table-hover > tbody > tr > td.active:hover,
.table-hover > tbody > tr > th.active:hover,
.table-hover > tbody > tr.active:hover > td,
.table-hover > tbody > tr:hover > .active,
.table-hover > tbody > tr.active:hover > th {
  background-color: #e8e8e8;
}
.table > thead > tr > td.success,
.table > tbody > tr > td.success,
.table > tfoot > tr > td.success,
.table > thead > tr > th.success,
.table > tbody > tr > th.success,
.table > tfoot > tr > th.success,
.table > thead > tr.success > td,
.table > tbody > tr.success > td,
.table > tfoot > tr.success > td,
.table > thead > tr.success > th,
.table > tbody > tr.success > th,
.table > tfoot > tr.success > th {
  background-color: #dff0d8;
}
.table-hover > tbody > tr > td.success:hover,
.table-hover > tbody > tr > th.success:hover,
.table-hover > tbody > tr.success:hover > td,
.table-hover > tbody > tr:hover > .success,
.table-hover > tbody > tr.success:hover > th {
  background-color: #d0e9c6;
}
.table > thead > tr > td.info,
.table > tbody > tr > td.info,
.table > tfoot > tr > td.info,
.table > thead > tr > th.info,
.table > tbody > tr > th.info,
.table > tfoot > tr > th.info,
.table > thead > tr.info > td,
.table > tbody > tr.info > td,
.table > tfoot > tr.info > td,
.table > thead > tr.info > th,
.table > tbody > tr.info > th,
.table > tfoot > tr.info > th {
  background-color: #d9edf7;
}
.table-hover > tbody > tr > td.info:hover,
.table-hover > tbody > tr > th.info:hover,
.table-hover > tbody > tr.info:hover > td,
.table-hover > tbody > tr:hover > .info,
.table-hover > tbody > tr.info:hover > th {
  background-color: #c4e3f3;
}
.table > thead > tr > td.warning,
.table > tbody > tr > td.warning,
.table > tfoot > tr > td.warning,
.table > thead > tr > th.warning,
.table > tbody > tr > th.warning,
.table > tfoot > tr > th.warning,
.table > thead > tr.warning > td,
.table > tbody > tr.warning > td,
.table > tfoot > tr.warning > td,
.table > thead > tr.warning > th,
.table > tbody > tr.warning > th,
.table > tfoot > tr.warning > th {
  background-color: #fcf8e3;
}
.table-hover > tbody > tr > td.warning:hover,
.table-hover > tbody > tr > th.warning:hover,
.table-hover > tbody > tr.warning:hover > td,
.table-hover > tbody > tr:hover > .warning,
.table-hover > tbody > tr.warning:hover > th {
  background-color: #faf2cc;
}
.table > thead > tr > td.danger,
.table > tbody > tr > td.danger,
.table > tfoot > tr > td.danger,
.table > thead > tr > th.danger,
.table > tbody > tr > th.danger,
.table > tfoot > tr > th.danger,
.table > thead > tr.danger > td,
.table > tbody > tr.danger > td,
.table > tfoot > tr.danger > td,
.table > thead > tr.danger > th,
.table > tbody > tr.danger > th,
.table > tfoot > tr.danger > th {
  background-color: #f2dede;
}
.table-hover > tbody > tr > td.danger:hover,
.table-hover > tbody > tr > th.danger:hover,
.table-hover > tbody > tr.danger:hover > td,
.table-hover > tbody > tr:hover > .danger,
.table-hover > tbody > tr.danger:hover > th {
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
  .table-responsive > .table {
    margin-bottom: 0;
  }
  .table-responsive > .table > thead > tr > th,
  .table-responsive > .table > tbody > tr > th,
  .table-responsive > .table > tfoot > tr > th,
  .table-responsive > .table > thead > tr > td,
  .table-responsive > .table > tbody > tr > td,
  .table-responsive > .table > tfoot > tr > td {
    white-space: nowrap;
  }
  .table-responsive > .table-bordered {
    border: 0;
  }
  .table-responsive > .table-bordered > thead > tr > th:first-child,
  .table-responsive > .table-bordered > tbody > tr > th:first-child,
  .table-responsive > .table-bordered > tfoot > tr > th:first-child,
  .table-responsive > .table-bordered > thead > tr > td:first-child,
  .table-responsive > .table-bordered > tbody > tr > td:first-child,
  .table-responsive > .table-bordered > tfoot > tr > td:first-child {
    border-left: 0;
  }
  .table-responsive > .table-bordered > thead > tr > th:last-child,
  .table-responsive > .table-bordered > tbody > tr > th:last-child,
  .table-responsive > .table-bordered > tfoot > tr > th:last-child,
  .table-responsive > .table-bordered > thead > tr > td:last-child,
  .table-responsive > .table-bordered > tbody > tr > td:last-child,
  .table-responsive > .table-bordered > tfoot > tr > td:last-child {
    border-right: 0;
  }
  .table-responsive > .table-bordered > tbody > tr:last-child > th,
  .table-responsive > .table-bordered > tfoot > tr:last-child > th,
  .table-responsive > .table-bordered > tbody > tr:last-child > td,
  .table-responsive > .table-bordered > tfoot > tr:last-child > td {
    border-bottom: 0;
  }
}
.status {
    width: 402px;
    text-align: center;
    background: red;
    font-size: 30px;
    line-height: 61px;
    color: #ffff;
    position: absolute;
    right: -152px;
    transform: rotate(36deg);
    top: 27px;
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
</style>
<body>
    <div class="container" style="position:relative;" >
        <div class="status {{ strtolower($invoice['status']) }}">{{ $invoice['status'] }}</div>
        <table border="0" width="100%">
            <tr>
                <td width="20%" style="border-top:0;">
                      <img id="logo" src="{{ $logo ?? '' }}" alt="logo" title="{{ $CompanyName ?? '' }}"
                          style="height: 40px; width: 300px; object-fit: contain; margin-top: -90px;">
                  </td>
                <td  width="50%" style="border-top:0;">
                    <div class="company" align="right" >
                        <h4> PT Qwords Company International</h4>
                        <p>Gedung Cyber 1 Lantai 3</p>
                        <p> Jl.Kuningan Barat no.8, Jakarta 12710 - Indonesia
                        Call Center : 08041808888 / +62 21 3970-8800
                        Jakarta - Bandung - Yogyakarta - Surabaya</p>
                        <p>  www.Qwords.co.id</p>
                    </div>
                </td>
            </tr>

        </table>
        <table border="0" width="100%" class="invoiceaddres table">
            <tr>
                <td width="70%" style="border-top:0;">
                    <p><b>Invoice To</b></p>
                    <p>Qwords</p>
                    <p>ATTN: {{ ucwords($client->firstname) }} {{ ucwords($client->lastname) }}</p>
                    <p>{{ $client->companyname }}, {{ $client->city }}, {{ $client->state }},
                        {{ $client->postcode }}.</p>

                </td>
                <td width="30%" style="border-top:0; text-align:right;">
                    <h4 align="right">
                        <div class="invoiceid" style="font-size: 21px; font-weight: bold;"><b>Invoice
                                #{{ $invoice['id'] }}</div></b>
                    </h4>
                    <p>Invoice Date : {{ $invoice['date'] }}</p>
                    <p>Due Date : {{ $invoice['duedate'] }}</p>
                </td>

            </tr>

        </table>

        <table width="100%" class="table table-condensed table-striped table-bordered" style="margin-bottom: 50px;">
            <thead>
                <tr>
                    <td><strong>Description</strong></td>
                    <td width="20%" class="text-center"><strong>Amount</strong></td>
                </tr>
            </thead>
            <tbody>
                @foreach ($item as $r)
                    <tr>
                        <td>{!! str_replace(['<br>', '<br/>', '<br />'], ' ', $r['description']) !!}</td>
                        <td class="text-center" align="right">{{ $r['rawamount'] }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td align="right" class="total-row text-right"><b>Subtotal</b></td>
                    <td align="right" class="total-row"><b>{{ $invoice['subtotal'] }}</b></td>
                </tr>
                <tr>
                    <td align="right" class="total-row text-right"><b>PPN 11%</b></td>
                    <td align="right" class="total-row"><b>{{ $invoice['tax'] }}</b></td>
                </tr>
                <tr>
                    <td align="right" class="total-row text-right"><b>Credit</b></td>
                    <td align="right" class="total-row"><b>{{ $invoice['credit'] }}</b></td>
                </tr>
                <tr>
                    <td align="right" class="total-row text-right"><b>Total</b></td>
                    <td align="right" class="total-row"><b>{{ $invoice['total'] }}</b></td>
                </tr>
            </tbody>
        </table>

        {{-- Instruksi pembayaran dinamis --}}
        @if ($paymentmethod == 'banktransfer')
            <div
                style="background-color: #c3e6cb; color: #155724; text-align: center; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                <p style="font-size: 16px; margin: 0;">
                    Silakan lakukan pembayaran ke rekening <strong>Mandiri</strong> berikut:<br>
                    <strong>PT Qwords Company International<br>
                        Bank Mandiri<br>
                        No. Rekening: 123-00-0000000-0</strong>
                </p>
            </div>
        @elseif(stripos($paymentmethod, 'va') !== false && isset($vaNumbers))
            @php
                $vaFields = [
                    'bcava' => 'BCA Virtual Account',
                    'mandiriva' => 'Mandiri Virtual Account',
                    'bniva' => 'BNI Virtual Account',
                    'briva' => 'BRI Virtual Account',
                    'permatabankva' => 'Permata Bank Virtual Account',
                    'biiva' => 'Bank International Indonesia Virtual Account',
                    'atmbersamava' => 'ATM Bersama Virtual Account',
                    'cimbva' => 'CIMB Virtual Account',
                    'danamonva' => 'Danamon Virtual Account',
                ];

                // Log semua VA yang tersedia
                \Log::info('Available VA Numbers:', [
                    'vaNumbers' => $vaNumbers,
                    'paymentmethod' => $paymentmethod,
                    'vaFields' => $vaFields,
                ]);

                // Tentukan VA yang akan ditampilkan berdasarkan payment method
                $activeVAs = [];
                if (stripos($paymentmethod, 'bca') !== false) {
                    if (!empty($vaNumbers->bcava)) {
                        $activeVAs[] = [
                            'label' => $vaFields['bcava'],
                            'number' => $vaNumbers->bcava,
                        ];
                    }
                } elseif (stripos($paymentmethod, 'mandiri') !== false) {
                    if (!empty($vaNumbers->mandiriva)) {
                        $activeVAs[] = [
                            'label' => $vaFields['mandiriva'],
                            'number' => $vaNumbers->mandiriva,
                        ];
                    }
                } elseif (stripos($paymentmethod, 'bni') !== false) {
                    if (!empty($vaNumbers->bniva)) {
                        $activeVAs[] = [
                            'label' => $vaFields['bniva'],
                            'number' => $vaNumbers->bniva,
                        ];
                    }
                } elseif (stripos($paymentmethod, 'bri') !== false) {
                    if (!empty($vaNumbers->briva)) {
                        $activeVAs[] = [
                            'label' => $vaFields['briva'],
                            'number' => $vaNumbers->briva,
                        ];
                    }
                } elseif (stripos($paymentmethod, 'permata') !== false) {
                    if (!empty($vaNumbers->permatabankva)) {
                        $activeVAs[] = [
                            'label' => $vaFields['permatabankva'],
                            'number' => $vaNumbers->permatabankva,
                        ];
                    }
                } elseif (stripos($paymentmethod, 'bii') !== false) {
                    if (!empty($vaNumbers->biiva)) {
                        $activeVAs[] = [
                            'label' => $vaFields['biiva'],
                            'number' => $vaNumbers->biiva,
                        ];
                    }
                } elseif (stripos($paymentmethod, 'atm') !== false) {
                    if (!empty($vaNumbers->atmbersamava)) {
                        $activeVAs[] = [
                            'label' => $vaFields['atmbersamava'],
                            'number' => $vaNumbers->atmbersamava,
                        ];
                    }
                } elseif (stripos($paymentmethod, 'cimb') !== false) {
                    if (!empty($vaNumbers->cimbva)) {
                        $activeVAs[] = [
                            'label' => $vaFields['cimbva'],
                            'number' => $vaNumbers->cimbva,
                        ];
                    }
                } elseif (stripos($paymentmethod, 'danamon') !== false) {
                    if (!empty($vaNumbers->danamonva)) {
                        $activeVAs[] = [
                            'label' => $vaFields['danamonva'],
                            'number' => $vaNumbers->danamonva,
                        ];
                    }
                }
            @endphp
            @if (!empty($activeVAs))
                <div
                    style="background-color: #c3e6cb; color: #155724; text-align: center; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <p style="font-size: 16px; margin: 0;">
                        Silahkan melakukan pembayaran ke nomor Virtual Account berikut:<br>
                        @foreach ($activeVAs as $va)
                            <strong>{{ $va['label'] }}: {{ $va['number'] }}</strong><br>
                        @endforeach
                    </p>
                </div>
            @else
                <div
                    style="background-color: #f8d7da; color: #721c24; text-align: center; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <p style="font-size: 16px; margin: 0;">
                        Tidak ada nomor Virtual Account yang tersedia untuk pembayaran ini.
                    </p>
                </div>
            @endif
        @endif

        <div class="diskripsi" style="font-size: 18px; margin-bottom:40px;">
            <p>Invoice yang melebihi masa due date akan diperbaharui isinya sesuai ketentuan yang berlaku</p>
            <p>
                Faktur pajak hanya dapat diminta melalui layanan support tiket dengan melampirkan NPWP dan SPPKP
                perusahaan anda.</p>
            <p>Maksimal 7 Hari terhitung sejak tanggal tagihan ini tercatat dibayar pada sistem.</p>
            <p>Tanggal faktur pajak adalah tanggal pembayaran dan/atau tanggal layanan selesai diproses.</p>
            <p>Apabila memotong PPh pasal 23, wajib mengirimkan bukti potong ke Finance paling lambat 20 Hari setelah
                konfirmasi pembayaran.</p>
            <p>Apabila tidak mengirimkan bukti potong, maka pembayaran anda akan dianggap kurang bayar</p>
            <p>Dan dapat mengakibatkan layanan anda dihentikan sementara.</p>
            <p style="color:red;">Pemotongan PPH23 tidak beserta dengan Nominal Kode Unik</p>
        </div>

        <h3>Transactions</h3>
        <table width="100%" class="table table-condensed table-striped table-bordered" style="margin-bottom: 40px;">
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
                    <tr>
                        <td class="text-right font-weight-bold" colspan="3"><b>Sisa Tagihan</b></td>
                        <td align="right">{{ $invoice['balance'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <center>PDF Generated on {{ date('Y-m-d') }}</center>
        </div>

</body>

</html>