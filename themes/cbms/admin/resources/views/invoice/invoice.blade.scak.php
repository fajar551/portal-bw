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
    <!-- jquery.vectormap css -->
    <link href="{{ Theme::asset('assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet" type="text/css" />

    <!-- select2 -->
    <link href="{{ Theme::asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    
  

    <!-- Bootstrap Css -->
    <link href="{{ Theme::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet"
        type="text/css" />
    <!-- Bootstrap Toggle -->
    <link href="{{ Theme::asset('assets/css/bootstrap4-toggle.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
   <!-- <link href="{{ Theme::asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" /> -->



    <!-- Custom CSS -->
    <link href="{{ Theme::asset('assets/css/style.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ Theme::asset('assets/css/invoice.css') }}" type="text/css" rel="stylesheet" />
    @if($print)
    <script>
        window.print();
    </script>
    @endif
</head>

<body style="background-color : #fff;" >
    <div class="container invoice-container">
    @if(Session::has('success'))
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
        <div class="row">
            <div class="col-md-7">
                <p><img src="{{ $logo }}" title="{{ $CompanyName }}"></p>
                <h3>Invoice #{{ $invoice['id'] }}</h3>
            </div>
            <div class="col-sm-5">
                <div class="invoice-status">
                    <span class="{{ strtolower($invoice['status']) }}">{{ $invoice['status'] }}</span>
                </div>
                <div class="small-text"> Due Date: {{ $invoice['duedate'] }}</div>
                <div class="payment-btn-container" align="center">

                </div>
            </div>
        </div>
        <hr>

        <div class="row">
            <div class="col-sm-6 pull-sm-right text-right-sm">
                <strong>Pay To:</strong>
                <address class="small-text">
                    PT Qwords Company International<br>
                    Gedung Cyber 1 Lantai 3<br>
                    Jl.Kuningan Barat no.8, Jakarta 12710 - Indonesia<br>
                    Call Center : 08041808888 / +62 21 3970-8800<br>
                    <br>
                    Jakarta - Bandung - Yogyakarta - Surabaya<br>
                    www.Qwords.co.id
                </address>
            </div>
            <div class="col-sm-6">
                <strong>Invoiced To</strong>
                <address class="small-text">
                    {{ $client->companyname  }}<br>  {{ $client->firstname .' '.$client->lastname  }}<br>
                    {{ $client->address1 }}, {{ $client->city }}, {{ $client->state }}, <br>
                </address>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <strong>Current Payment Method: {{ $gateway[$invoice['paymentmethod']] ??'' }}</strong><br><br>

                <span class="small-text">Change Payment Method To:
                    @if($invoice['status'] == 'Unpaid' && $allowchangegateway == 'On' )
                    <form method="post" action="" class="form-inline">
                        {{ csrf_field() }}
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $invoice['id'] }}">
                        <select name="gateway" onchange="submit()" class="form-control select-inlinenew">
                            <option value="">Choose</option>
                            @foreach($gateway as $k => $v )
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </span>
                <br><br>
            </div>
            <div class="col-sm-6 text-right-sm">
                <strong>Invoice Date:</strong><br>
                <span class="small-text">{{ $invoice['date'] }}<br><br></span>
            </div>
        </div>
        <!--
        <div class="card">
            <div class="card-header bg-success">
                <h5 class="card-title">Apply Credit</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    {{ csrf_field() }}
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $invoice['id'] }}">
                    <input type="hidden" name="option" value="option">
                    <input type="hidden" name="saveoptions" value="true">
                    <input type="hidden" name="userid" value="{{ $invoice['userid'] }}">
                    <input type="hidden" name="paymentmethod" value="{{ $invoice['paymentmethod'] }}">
                    Your credit balance is <strong>{{ $invoice['clienttotaldue'] }}

                    </strong>. This can be applied to the invoice using the form below.. Enter the amount to apply:
                    <div class="row d-flex justify-content-center">
                        <div class="col-xs-8">
                            <div class="input-group">
                                <input type="text" name="addcredit" value="-11000.00" class="form-control">
                                <span class="input-group-btn">
                                    <input type="submit" value="Apply Credit" class="btn btn-success">
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
         -->
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
                                    <td width="20%" class="text-center"><strong>Amount</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($item as $r)
                                    <tr>
                                        <td>{{ $r['description'] }}</td>
                                        <td class="text-center">{{  $r['rawamount'] }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="total-row text-right"><strong>Credit</strong></td>
                                    <td class="total-row">{{ $invoice['clientbalancedue'] }}</td>
                                </tr>
                                <tr>
                                    <td class="total-row text-right"><strong>Total</strong></td>
                                    <td class="total-row">{{ $invoice['clientbalanceduefomat'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <p>* Indicates a taxed item.</p>
        @if(!$print)
        <center><strong></strong>
            <p><strong>Total Tagihan:<br>IDR <input type="text" value="{{ $invoice['clientbalancedue'] }}" id="myInput2"> <button onclick="copytext2()"><i class="fas fa-paste"></i></button><br></strong></p>
        </center>
        <p>Mohon Lakukan Pembayaran secara PENUH ke:</p>
        @endif
        <br>
        <div class="table-responsive">
            <table class="table table-condensed">
                <tbody>
                    <tr>
                        <td class="total-row"><strong>PT Qwords Company International</strong></td>
                        <td class="total-row"><strong>No Rekening</strong></td>
                    </tr>
                    <tr>
                        <td class="total-row text-left">Bank Central Asia KC Wisma Mulia Jakarta</td>
                        <td class="total-row">503-5778-770</td>
                    </tr>
                    <tr>
                        <td class="total-row text-left">Bank Mandiri KC Suropati Bandung</td>
                        <td class="total-row">131-00-12210-888</td>
                    </tr>
                    <tr>
                        <td class="total-row text-left">Bank BRI KC Martadinata Bandung</td>
                        <td class="total-row">0389-01-000714-30-8</td>
                    </tr>
                    <tr>
                        <td class="total-row text-left">Bank BNI KCP POSINDO Bandung</td>
                        <td class="total-row">777-187-888-4</td>
                    </tr>
                </tbody>
            </table>
        </div>
    
        <br>
        <br>
        <p><b></b></p>
        <p>Faktur pajak hanya dapat diminta melalui layanan support tiket dengan melampirkan NPWP dan SPPKP perusahaan anda.</p>
        <p>Maksimal 7 Hari terhitung sejak tanggal tagihan ini tercatat dibayar pada sistem.</p>
        <p>Tanggal faktur pajak adalah tanggal pembayaran dan/atau tanggal layanan selesai diproses.</p>
        <p>Apabila memotong PPh pasal 23, wajib mengirimkan bukti potong ke Finance paling lambat 20 Hari setelah konfirmasi pembayaran.</p>
        <p>Apabila tidak mengirimkan bukti potong, maka pembayaran anda akan dianggap kurang bayar</p>
        <p>Dan dapat mengakibatkan layanan anda dihentikan sementara.</p>
        </br>
        <font color="red"><p>Pemotongan PPH23 tidak beserta dengan Nominal Kode Unik</p></font>
        </br>

        
        
    </div>

    @include('includes.scripts-global')
</body>

</html>
