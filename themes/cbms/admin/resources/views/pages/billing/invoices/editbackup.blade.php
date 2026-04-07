@extends('layouts.basecbms')

@section('title')
   <title>CBMS - Invoice #{{ $invoice->id }}</title>
@endsection

@section('content')
<style>
  .modal.fade .modal-dialog {
    transform: scale(0.8);
    transition: transform 0.3s ease-in-out;
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

.modal.show .modal-dialog {
    transform: scale(1);
}

.modal-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#sendCustomWAModal .modal-dialog {
    margin: 1.75rem auto;
}

@media (min-width: 576px) {
    #sendCustomWAModal .modal-dialog {
        max-width: 500px;
        margin: 1.75rem auto;
    }
}
</style>
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">
            <div class="row">

               <div class="col-xl-12">
                  <div class="view-client-wrapper">
                     <div class="row">
                        <div class="col-12">
                           <div class="card-title mb-3">
                              <h4 class="mb-3">Invoices #{{ $invoice->id }}</h4>
                           </div>
                           <div class="row">
                              <div class="col-lg-12">
                                 @if (Session::has('success'))
                                    <div class="alert alert-success">
                                       {!! Session::get('success') !!}
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
                                 @if ($invoice->status == 'Draft')
                                    <div class="p-3">
                                       <div class="alert alert-info row align-items-center" role="alert">
                                          <div class="col-1">
                                             <i class="ri-information-line ml-3" style="font-size: 48px;"></i>
                                          </div>
                                          <div class="col-10">
                                             <p class="mb-0 font-size-18">
                                                {{ __('admin.invoicesdraftInvoiceNotice') }}
                                             </p>
                                          </div>
                                       </div>
                                    </div>

                                 @endif
                                 <div class="card p-3">
                                    <div class="row">
                                       <div class="col-lg-8">
                                          <nav>
                                             <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                   <a class="nav-link active" id="nav-summary-tab" data-toggle="tab"
                                                      href="#nav-summary" role="tab" aria-controls="nav-summary"
                                                      aria-selected="true">Summary</a>
                                                </li>
                                                <li class="nav-item">
                                                   <a class="nav-link" id="nav-add-payment-tab" data-toggle="tab"
                                                      href="#nav-add-payment" role="tab" aria-controls="nav-add-payment"
                                                      aria-selected="false">Add
                                                      Payment</a>
                                                </li>
                                                <li class="nav-item">
                                                   <a class="nav-link" id="nav-options-tab" data-toggle="tab"
                                                      href="#nav-options" role="tab" aria-controls="nav-options"
                                                      aria-selected="false">Options</a>
                                                </li>
                                                <li class="nav-item">
                                                   <a class="nav-link" id="nav-credit-tab" data-toggle="tab"
                                                      href="#nav-credit" role="tab" aria-controls="nav-credit"
                                                      aria-selected="false">Credit</a>
                                                </li>
                                                <li class="nav-item">
                                                   <a class="nav-link" id="nav-refund-tab" data-toggle="tab"
                                                      href="#nav-refund" role="tab" aria-controls="nav-refund"
                                                      aria-selected="false">Refund</a>
                                                </li>
                                                <li class="nav-item">
                                                   <a class="nav-link" id="nav-notes-tab" data-toggle="tab"
                                                      href="#nav-notes" role="tab" aria-controls="nav-notes"
                                                      aria-selected="false">Notes</a>
                                                </li>
                                             </ul>
                                          </nav>
                                       </div>
                                       <div class="col-lg-4">
                                          <div class="float-right">
                                             <div class="btn-group mb-2" role="group" aria-label="Action Button Group">
                                                <a target="_blank"
                                                   href="{{ url($baseURL . 'invoices/view/' . $invoice->id) }}"
                                                   class="btn btn-light d-flex align-items-center text-nowrap"><i
                                                      class="ri-article-fill mr-2"></i>View as Client</button>
                                                   <a href="{{ url($baseURL . 'invoices/view/' . $invoice->id . '?print=1') }}"
                                                      class="btn btn-light d-flex align-items-center"><i
                                                         class="ri-printer-fill mr-2"></i>Print</a>
                                                   <a href="{{ url($baseURL . 'invoices/download/' . $invoice->id) }}"
                                                      class="btn btn-light d-flex align-items-center"><i
                                                         class="ri-download-cloud-fill mr-2"></i>Download</a>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- ============================================== -->
                                    <!-- InvoiceTab - Summary -->
                                    <!-- ============================================== -->
                                    <div class="tab-content" id="nav-tabContent">
                                       <div class="tab-pane fade show active" id="nav-summary" role="tabpanel"
                                          aria-labelledby="nav-summary-tab">
                                          <div class="card px-3 pb-3 pt-1">
                                             <div class="row">
                                                @if ($data['status'] == 'Draft')
                                                   <div class="col-lg-12 bg-light p-2">
                                                      <div class="d-flex flex-row-reverse">
                                                         <form action="" class="mr-3" method="POST">
                                                            {{ csrf_field() }}
                                                            @method('PUT')
                                                            <input type="hidden" name="id" value="{{ $data['id'] }}">
                                                            <input type="hidden" name="publish"
                                                               value="publish & sent email">
                                                            <input type="hidden" name="userid"
                                                               value="{{ $invoice->userid }}">
                                                            <button type="submit" class="btn btn-warning px-3"> Publish and
                                                               Send Email </button>
                                                         </form>


                                                         <form action="" class="mr-3" method="POST">
                                                            {{ csrf_field() }}
                                                            @method('PUT')
                                                            <input type="hidden" name="id" value="{{ $data['id'] }}">
                                                            <input type="hidden" name="publish" value="publish">
                                                            <input type="hidden" name="userid"
                                                               value="{{ $invoice->userid }}">
                                                            <button type="submit" class="btn btn-primary px-3">Publish
                                                            </button>
                                                         </form>


                                                      </div>
                                                   </div>
                                                @endif
                                                <div class="col-lg-6 p-2">
                                                   <div class="table-responsive">
                                                      <table class="table table-sm table-bordered">
                                                         <tbody>
                                                            <tr>
                                                               <td>Client Name</td>
                                                               <td><a
                                                                     href="{{route('admin.pages.clients.viewclients.clientsummary.index')}}?userid={{$invoice->userid}}">{{ ucwords($invoice->firstname . ' ' . $invoice->lastname) }}</a>
                                                                  (<a
                                                                     href="{{route('admin.pages.clients.viewclients.clientinvoices.index')}}?userid={{$invoice->userid}}">View
                                                                     Invoices</a> ) </td>
                                                            </tr>
                                                            <tr>
                                                               <td>Invoice Date</td>
                                                               <td>{{ $invoice->date }}</td>
                                                            </tr>
                                                            <tr>
                                                               <td>Due Date</td>
                                                               <td>{{ $invoice->duedate }}</td>
                                                            </tr>
                                                            <tr>
                                                               <td>Total Due</td>
                                                               <td>{{ $data['total'] }}</td>
                                                            </tr>
                                                            <tr>
                                                               <td>Balance</td>
                                                               @if (0 < $balance)
                                                                  <td class="text-danger"> {{ $data['balance'] }}
                                                                  @else
                                                                  <td class="text-success"> {{ $data['balance'] }}
                                                               @endif
                                                               </td>
                                                            </tr>
                                                         </tbody>
                                                      </table>
                                                   </div>
                                                </div>
                                                <div class="col-lg-6 p-2">
                                                   <div class="text-center">

                                                      <h3
                                                         class="font-weight-bold
                                                      @if ($data['status'] == 'Paid')
                                                        text-success
                                                      @elseif ($data['status'] == 'Unpaid')
                                                        text-danger
                                                      @else
                                                        text-secondary
                                                      @endif
                                                      ">
                                                         {{ $data['status'] }}
                                                      </h3>

                                                      @if ($data['status'] == 'Paid')
                                                         <p><b> {{ $data['datepaid'] }} </b></p>
                                                      @endif
                                                      @if ($data['status'] == 'Draft')
                                                         <p>Payment Method: <strong>No Transactions Applied</strong></p>
                                                      @else
                                                         <p>Payment Method:
                                                            <strong>{{ $data['paymentGatewayName'] }}</strong>
                                                         </p>
                                                      @endif
                                                   </div>
                                                   <div class="row justify-content-center">

                                                      <div class="col-sm-12">
                                                         <form action="" method="POST">
                                                            {{ csrf_field() }}
                                                            @method('PUT')
                                                            <div
                                                               class="form-group row justify-content-center align-items-center mb-0">
                                                               <div class="col-sm-7 my-1">
                                                                  <select name="invoice_stats" id="invoice-stats"
                                                                     class="form-control">
                                                                     @foreach ($tempalte as $r)
                                                                        <option value="{{ $r }}">
                                                                           {{ $r }}</option>
                                                                     @endforeach
                                                                  </select>
                                                               </div>
                                                               <div class="col-sm-3 my-1 pl-lg-0">
                                                                  <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                                  <input type="hidden" name="SendEmail" value="Send Email">
                                                                  <button type="submit"
                                                                     class="btn btn-block btn-info m-1 px-3 text-nowrap">Send
                                                                     Email</button>

                                                               </div>
                                                            </div>
                                                         </form>
                                                      </div>


                                                   </div>
                                                   
                                                   <!--BUTTON RESEND WA-->
                                                   <div class="row justify-content-center">
                                                      <div class="col-lg-12 col-sm-12  d-flex justify-content-around">
                                                         <form action="" method="POST">
                                                            {{ csrf_field() }}
                                                            @method('PUT')

                                                            <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                            <input type="hidden" name="userid"
                                                               value="{{ $invoice->userid }}">

                                                            <input type="submit" name="AttemptCapture"
                                                               class="btn btn-success m-1 px-3" value="Attempt Capture">
                                                            <input type="submit" name="MarkCancelled"
                                                               class="btn btn-light m-1 px-3" value="Mark Cancelled">
                                                            @if ($invoice->status != 'Unpaid')
                                                               <input type="submit" name="MarkUnpaid"
                                                                  class="btn btn-light m-1 px-3" value="Mark Unpaid">
                                                            @else
                                                               <input type="submit" name="zeroPaid"
                                                                  class="btn btn-info m-1 px-3" value="Mark paid">
                                                            @endif

                                                         </form>
                                                      </div>
                                                      <!--BUTTON RESEND WA-->
                                                      <div class="mt-3">
                                                        <!-- Untuk single Resend WA -->
                                                        <button onclick="resendWA({{ $invoice->id }})" class="btn btn-success m-1 px-3">
                                                          Resend WA
                                                        </button>

                                                        <!-- Untuk Resend WA By GroupClients -->
                                                        <button onclick="resendWAByGroup({{ $client->groupid }})" class="btn btn-light m-1 px-3">
                                                          Resend WA By GroupClients
                                                        </button>

                                                         {{-- <a href="/admin/clients/viewclients/resendWA?invoiceid={{ $invoice->id }}" class="btn btn-success m-1 px-3" target="_blank" onclick="return confirm('Are you sure you want to resend WA?');"> Resend WA </a>
                                                         <a href="/admin/clients/viewclients/resendWABulk?groupid={{ $client->groupid }}" class="btn btn-light m-1 px-3" target="_blank" onclick="return confirm('Are you sure you want to resend WA to all unpaid invoices in this group?');"> Resend WA By GroupCLients </a> --}}
                                                         <a href="/admin/clients/viewclients/resendWA?invoiceid={{ $invoice->id }}&preview=true" class="btn btn-info m-1 px-3" target="_blank"> Preview Data </a>
                                                        </div>
                                                        <div class="mt-3">
                                                          <button type="button" class="btn btn-success m-1 px-3" data-toggle="modal" data-target="#sendCustomWAModal">
                                                            Send WA to All Clients
                                                        </button>
                                                      </div>
                                                   </div>
                                                   
                                                   
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <!-- ============================================== -->
                                       <!-- InvoiceTab - Add Payment -->
                                       <!-- ============================================== -->
                                       <div class="tab-pane fade" id="nav-add-payment" role="tabpanel"
                                          aria-labelledby="nav-add-payment-tab">
                                          <div class="row">
                                             <div class="col-lg-12 p-3">
                                                @if ($invoice->status == 'Draft')
                                                   <div class="alert alert-warning row" role="alert">
                                                      <div class="col-1">
                                                         <i class="ri-information-line ml-3" style="font-size: 48px;"></i>
                                                      </div>
                                                      <div class="col-10">
                                                         <p style="font-size: 32px; margin-bottom: 0;">
                                                            {{ __('admin.invoicesinvoiceIsDraft') }}
                                                         </p>
                                                         <p class="mb-0">
                                                            {{ __('admin.invoicespublishAndSendEmail') }} to apply a
                                                            payment
                                                         </p>
                                                      </div>
                                                   </div>
                                                @elseif ($invoice->status == 'Cancelled')
                                                   <div class="alert alert-warning row" role="alert">
                                                      <div class="col-1">
                                                         <i class="ri-information-line ml-3" style="font-size: 48px;"></i>
                                                      </div>
                                                      <div class="col-10">
                                                         <p style="font-size: 32px; margin-bottom: 0;">
                                                            {{ __('admin.invoicesinvoiceIsCancelled') }}
                                                         </p>
                                                         <p class="mb-0">
                                                            {{ __('admin.invoicesinvoiceIsCancelledDescription') }}
                                                         </p>
                                                      </div>
                                                   </div>
                                                @elseif ($invoice->status == 'Paid')
                                                   <div class="alert alert-warning d-flex align-items-center" role="alert">
                                                      <div class="col-1">
                                                         <i class="ri-information-line ml-3" style="font-size: 48px;"></i>
                                                      </div>
                                                      <div class="col-10">
                                                         <p style="font-size: 32px; margin-bottom: 0;">
                                                            Invoice in Paid Status
                                                         </p>
                                                         <p class="mb-0">
                                                            As this invoice is already marked paid, any further payments
                                                            applied will result in a credit to the client
                                                         </p>
                                                      </div>
                                                   </div>
                                                @else
                                                {{-- HOTFIX: --}}
                                                   <form action="" method="POST">
                                                      <div class="row">
                                                         <div class="col-md-6">
                                                            <div class="form-group row">
                                                               <label for="addPaymentDate"
                                                                  class="col-sm-4 col-form-label">Date</label>
                                                               <div class="col-sm-8">
                                                                  <div class="input-daterange input-group inputdate">
                                                                     <input type="text" name="date" class="form-control"
                                                                        id="inputDate" autocomplete="off">
                                                                  </div>
                                                               </div>
                                                            </div>
                                                            <div class="form-group row">
                                                               <label for="paymentMethodOptions"
                                                                  class="col-sm-4 col-form-label">Payment Method</label>
                                                               <div class="col-sm-8">
                                                                  <select class="form-control" name="paymentmethod">
                                                                     @foreach ($gateway as $k => $v)
                                                                        <option value="{{ $k }}">
                                                                           {{ $v }}</option>
                                                                     @endforeach
                                                                  </select>
                                                               </div>
                                                            </div>
                                                            <div class="form-group row">
                                                               <label for="invoiceOptions"
                                                                  class="col-sm-4 col-form-label">Transaction</label>
                                                               <div class="col-sm-8">
                                                                  <input type="text" name="transid" class="form-control"
                                                                     id="transidINput">
                                                               </div>
                                                            </div>

                                                         </div>
                                                         <div class="col-md-6">
                                                            <div class="form-group row">
                                                               <label for="invoiceOptions"
                                                                  class="col-sm-4 col-form-label">Amount</label>
                                                               <div class="col-sm-8">
                                                                  <input type="text" name="amount" class="form-control"
                                                                     value="0.00" id="amount">
                                                               </div>
                                                            </div>
                                                            <div class="form-group row">
                                                               <label for="invoiceOptions"
                                                                  class="col-sm-4 col-form-label">Transaction Fees</label>
                                                               <div class="col-sm-8">
                                                                  <input type="text" name="fees" class="form-control"
                                                                     value="0.00" id="feesid">
                                                               </div>
                                                            </div>
                                                            <div class="form-group row">
                                                               <label for="invoiceOptions"
                                                                  class="col-sm-4 col-form-label">Send Email</label>
                                                               <div class="form-check text-center mt-2 col-sm-1">
                                                                  <input class="form-check-input" name="sendconfirmation"
                                                                     type="checkbox" value="1" checked>
                                                               </div>
                                                               <label class="col-sm-7 col-form-label">
                                                                  Tick to Send Confirmation Email
                                                               </label>
                                                            </div>


                                                         </div>
                                                      </div>
                                                      <div class="col-lg-12 d-flex justify-content-center">
                                                         {{ csrf_field() }}
                                                         @method('PUT')
                                                         <input type="hidden" name="addpayment" value="add payment">
                                                         <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                         <input type="hidden" name="userid"
                                                            value="{{ $invoice->userid }}">
                                                         <button type="submit" class="btn btn-success px-3 mt-3">Add
                                                            Payment</button>
                                                      </div>
                                                   </form>
                                                @endif
                                                {{-- @if ($invoice->status == 'Paid')
                                                   <div class="alert alert-warning d-flex align-items-center" role="alert">
                                                      <div class="col-1">
                                                         <i class="ri-information-line ml-3" style="font-size: 48px;"></i>
                                                      </div>
                                                      <div class="col-10">
                                                         <p style="font-size: 32px; margin-bottom: 0;">
                                                            Invoice in Paid Status
                                                         </p>
                                                         <p class="mb-0">
                                                            As this invoice is already marked paid, any further payments
                                                            applied will result in a credit to the client
                                                         </p>
                                                      </div>
                                                   </div>
                                                @endif --}}
                                             </div>
                                          </div>
                                       </div>
                                       <!-- ============================================== -->
                                       <!-- InvoiceTab - Options -->
                                       <!-- ============================================== -->
                                       <div class="tab-pane fade" id="nav-options" role="tabpanel"
                                          aria-labelledby="nav-options-tab">
                                          <form action="" method="POST">
                                             <div class="row py-3">
                                                <div class="col-lg-6">
                                                   <div class="form-group row">
                                                      <label for="invoiceDate" class="col-sm-2 col-form-label">Invoice
                                                         Date</label>
                                                      <div class="col-sm-10">
                                                         <div class="input-daterange input-group inputdate">
                                                            <input type="text" name="invoicedate"
                                                               value="{{ $invoice->date }}" class="form-control"
                                                               id="inputInvoiceDate" autocomplete="off">
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="form-group row">
                                                      <label for="paymentMethodOptions"
                                                         class="col-sm-2 col-form-label">Payment Method</label>
                                                      <div class="col-sm-10">
                                                         <select class="form-control" name="paymentmethod">
                                                            @foreach ($gateway as $k => $v)
                                                               <option value="{{ $k }}" @if ($invoice->paymentmethod == $k) selected @endif>{{ $v }}
                                                               </option>
                                                            @endforeach
                                                         </select>
                                                      </div>
                                                   </div>
                                                   <div class="form-group row">
                                                      <label for="invoiceOptions" class="col-sm-2 col-form-label">Invoice
                                                         #</label>
                                                      <div class="col-sm-10">
                                                         <input type="text" class="form-control" name="invoicenum"
                                                            id="invoiceOptions">
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-lg-6">
                                                   <div class="form-group row">
                                                      <label for="dueDateOption" class="col-sm-2 col-form-label">Due
                                                         Date</label>
                                                      <div class="col-sm-10">
                                                         <div class="input-daterange input-group inputdate">
                                                            <input type="text" name="datedue"
                                                               value="{{ $invoice->duedate }}" class="form-control"
                                                               id="dueDateOption" autocomplete="off">
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="form-group row">
                                                      <label for="taxRate" id="taxRate" class="col-sm-2 col-form-label">
                                                         Tax Rate
                                                      </label>
                                                      <div class="col-sm-5">
                                                         <div class="input-group">
                                                            <div class="input-group-prepend">
                                                               <span class="input-group-text">1</span>
                                                            </div>
                                                            <input type="text" name="taxrate" class="form-control"
                                                               value="{{ $invoice->taxrate }}" placeholder="0.00">
                                                            <div class="input-group-append">
                                                               <span class="input-group-text">%</span>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-sm-5">
                                                         <div class="input-group">
                                                            <div class="input-group-prepend">
                                                               <span class="input-group-text">2</span>
                                                            </div>
                                                            <input type="text" class="form-control" name="taxrate2"
                                                               value="{{ $invoice->taxrate2 }}" placeholder="0.00">
                                                            <div class="input-group-append">
                                                               <span class="input-group-text">%</span>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="form-group row">
                                                      <label for="statusInvoiceOptions"
                                                         class="col-sm-2 col-form-label">Status</label>
                                                      <div class="col-sm-10">
                                                         <select class="form-control" name="status">
                                                            <option value="Draft"
                                                               {{ $invoice->status == 'Draft' ? 'selected' : '' }}>
                                                               <div class="text-secondary">Draft</div>
                                                            </option>
                                                            <option value="Unpaid"
                                                               {{ $invoice->status == 'Unpaid' ? 'selected' : '' }}>
                                                               <div class="text-danger">Unpaid
                                                            </option>
                                                            <option value="Paid"
                                                               {{ $invoice->status == 'Paid' ? 'selected' : '' }}>
                                                               <div class="text-success">Paid
                                                            </option>
                                                            <option value="Cancelled"
                                                               {{ $invoice->status == 'Cancelled' ? 'selected' : '' }}>
                                                               Cancelled</option>
                                                            <option value="Refunded"
                                                               {{ $invoice->status == 'Refunded' ? 'selected' : '' }}>
                                                               Refunded</option>
                                                            <option value="Collections"
                                                               {{ $invoice->status == 'Collections' ? 'selected' : '' }}>
                                                               Collections</option>
                                                            <option value="Payment Pending"
                                                               {{ $invoice->status == 'Payment Pending' ? 'selected' : '' }}>
                                                               Payment Pending</option>
                                                         </select>
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-lg-12 d-flex justify-content-center">
                                                   {{ csrf_field() }}
                                                   @method('PUT')
                                                   <input type="hidden" name="option" value="option">
                                                   <input type="hidden" name="saveoptions" value="true">
                                                   <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                   <input type="hidden" name="userid" value="{{ $invoice->userid }}">
                                                   <button type="submit" class="btn btn-success px-3 mt-3">Save
                                                      Changes</button>
                                                </div>
                                             </div>

                                          </form>
                                          <hr>
                                       </div>
                                       <!-- ============================================== -->
                                       <!-- InvoiceTab - Credit -->
                                       <!-- ============================================== -->
                                       <div class="tab-pane fade" id="nav-credit" role="tabpanel"
                                          aria-labelledby="nav-credit-tab">
                                          <div class="row py-3 justify-content-center">
                                             <div class="col-lg-5">
                                                <h4 class="card-title">Add Credit to Invoice</h4>
                                                @if ($data['clientsdetails']['credit'] > 0)
                                                   <p class="mb-3 text-success">{{ $data['clientsdetails']['credit'] }}
                                                      Available</p>
                                                @else
                                                   <p class="mb-3 text-danger">{{ $data['clientsdetails']['credit'] }}
                                                      Available</p>
                                                @endif
                                                <form action="" method="POST">
                                                   {{ csrf_field() }}
                                                   @method('PUT')
                                                   <div class="form-group row">

                                                      <div class="col-sm-6">
                                                         <input type="text" name="addcredit" id="addCredit"
                                                            class="form-control" value="{{  $balance }}" placeholder="0.00"
                                                            {{  $balance > 0.00 ? '' : 'disabled readonly' }}>
                                                      </div>
                                                      <div class="col-sm-6">
                                                         <input type="hidden" name="optionaddcredit" value="addcredit">
                                                         <input type="hidden" name="saveoptions" value="true">
                                                         <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                         <input type="hidden" name="userid"
                                                            value="{{ $invoice->userid }}">
                                                         <button type="submit" class="btn btn-success px-5"
                                                         {{  $balance > 0.00 ? '' : 'disabled readonly' }}>
                                                            Go</button>
                                                      </div>

                                                   </div>
                                                </form>
                                             </div>
                                             <div class="col-lg-5">
                                                <form action="" method="POST">
                                                   {{ csrf_field() }}
                                                   @method('PUT')
                                                   <h4 class="card-title">Remove Credit to Invoice</h4>
                                                   <p class="mb-3 @if($data['credit']->toNumeric() > 0.00 ) text-success @else text-danger @endif">{{ ($data['credit']->toNumeric() > 0.00 )?$data['credit'] :'0.00' }} Available</p>
                                                   <div class="form-group row">
                                                      <div class="col-sm-6">
                                                         <input type="text" name="removeCredit" id="removeCredit"
                                                            class="form-control" value="{{ $data['credit']->toNumeric() }}"
                                                            {{ $data['credit']->toNumeric() > 0.00 ? '' : 'disabled readonly' }}>
                                                      </div>
                                                      <div class="col-sm-6">
                                                         <input type="hidden" name="Optionremovecredit" value="removecredit">
                                                         <input type="hidden" name="saveoptions" value="true">
                                                         <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                         <input type="hidden" name="userid"
                                                            value="{{ $invoice->userid }}">
                                                         <button class="btn btn-success px-5" {{ $data['credit']->toNumeric() > 0.00 ? '' : 'disabled readonly' }}>Go</button>
                                                      </div>
                                                   </div>
                                                </form>
                                             </div>
                                          </div>
                                       </div>
                                       <!-- ============================================== -->
                                       <!-- InvoiceTab - Refund -->
                                       <!-- ============================================== -->
                                       <div class="tab-pane fade" id="nav-refund" role="tabpanel"
                                          aria-labelledby="nav-refund-tab">
                                          <form action="" method="POST">
                                             <div class="row py-3">
                                                <div class="col-lg-12">
                                                   <div class="form-group row">
                                                      <label for="transactionRefund" class="col-sm-2 col-form-label">
                                                         Transactions
                                                      </label>
                                                      <div class="col-sm-5">
                                                         <select name="transid" id="transRefund" class="form-control">
                                                            @if (empty($conttrans))
                                                               <option value="">No Transactions Applied To This Invoice Yet
                                                               </option>
                                                            @else
                                                               @foreach ($transaction as $r)
                                                                  <option value="{{ $r['id'] }}">{{ $r['date'] }}
                                                                     | {{ $r['transid'] }} | {{ $r['amountin'] }}
                                                                  </option>
                                                               @endforeach
                                                            @endif
                                                         </select>
                                                      </div>
                                                   </div>
                                                   <div class="form-group row">
                                                      <label for="amountRefund" class="col-sm-2 col-form-label">Amount
                                                         Refund</label>
                                                      <div class="col-sm-3">
                                                         <input type="number" name="amount" id="amount"
                                                            class="form-control" value="0.00">
                                                      </div>
                                                      <div class="col-sm-3 d-flex align-items-center">
                                                         <h6>Leave blank for full refund</h6>
                                                      </div>
                                                   </div>
                                                   <div class="form-group row">
                                                      <label for="refundType" class="col-sm-2 col-form-label">Refund
                                                         Type</label>
                                                      <div class="col-sm-5">
                                                         <select name="refundtype" id="refundtype" class="form-control">
                                                            <option value="sendtogateway">Refund through Gateway (If
                                                               supported by module)</option>
                                                            <option value="" type="">Manual Refund Processed Externally
                                                            </option>
                                                            <option value="addascredit">Add to Client's Credit Balance
                                                            </option>
                                                         </select>
                                                      </div>
                                                   </div>
                                                   <div id="TransactionID" class="form-group row" style="display:none;">
                                                      <label for="refundtransid"
                                                         class="col-sm-2 col-form-label">Transaction ID</label>
                                                      <div class="col-sm-5">
                                                         <input type="text" name="refundtransid" id="refundtransid"
                                                            class="form-control">
                                                      </div>
                                                   </div>
                                                   <div class="form-group row">
                                                      <label for="reversePayment" class="col-sm-2 col-form-label">Reverse
                                                         Payment</label>
                                                      <div class="col-sm-6">
                                                         <div class="form-check mt-2">
                                                            <input class="form-check-input" name="reverse" type="checkbox"
                                                               value="1" id="defaultCheck1">
                                                            <label class="form-check-label" for="defaultCheck1">
                                                               Undo automated actions triggered by this transaction - where
                                                               possible.
                                                            </label>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="form-group row">
                                                      <label for="sendEmail" class="col-sm-2 col-form-label">Send
                                                         Email</label>
                                                      <div class="col-sm-6">
                                                         <div class="form-check mt-2">
                                                            <input class="form-check-input" name="sendemail"
                                                               type="checkbox" value="1" id="defaultCheck2" checked>
                                                            <label class="form-check-label" for="defaultCheck2">
                                                               Tick to Send Confirmation Email
                                                            </label>
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-lg-12 d-flex justify-content-center">
                                                   {{ csrf_field() }}
                                                   @method('PUT')
                                                   <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                   <input type="hidden" name="userid" value="{{ $invoice->userid }}">
                                                   <input type="hidden" name="refund" value="Refund">
                                                   <button type="submit" class="btn btn-light px-5"
                                                      disabled>Refund</button>
                                                </div>
                                             </div>
                                          </form>
                                       </div>
                                       <!-- ============================================== -->
                                       <!-- InvoiceTab - Notes -->
                                       <!-- ============================================== -->
                                       <div class="tab-pane fade" id="nav-notes" role="tabpanel"
                                          aria-labelledby="nav-notes-tab">
                                          <form action="" method="post">
                                             {{ csrf_field() }}
                                             @method('PUT')
                                             <div class="row py-3">
                                                <div class="col-lg-12">
                                                   <textarea name="notes" id="notesInvoice" cols="30"
                                                      class="form-control" rows="10">{{ $data['notes'] }}</textarea>
                                                </div>
                                                <div class="col-lg-12 d-flex justify-content-center">
                                                   <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                   <input type="hidden" name="userid" value="{{ $invoice->userid }}">
                                                   <input type="hidden" name="addnotes" value="addnotes">
                                                   <button type="submit" class="btn btn-success px-5 my-3">
                                                      Save Changes
                                                   </button>
                                                </div>
                                             </div>
                                          </form>
                                       </div>
                                    </div>

                                    <form action="" method="post">
                                       {{ csrf_field() }}
                                       @method('PUT')
                                       <input type="hidden" name="id" value="{{ $invoice->id }}">
                                       <input type="hidden" name="userid" value="{{ $invoice->userid }}">
                                       <div class="row">
                                          <div class="col-lg-12">
                                             <h5>Invoice Items</h5>
                                             <div class="table-responsive">
                                                <table id="" class="table table-bordered dt-responsive w-100">
                                                   <thead>
                                                      <tr class="text-center">
                                                         <th style="width: 50px;"></th>
                                                         <th style="width: 800px;">
                                                            Description
                                                         </th>
                                                         <th style="width: 200px;">Amount
                                                         </th>
                                                         <th style="width: 100px;">Taxed
                                                         </th>
                                                         <th style="width: 100px;">Action
                                                         </th>
                                                      </tr>
                                                   </thead>
                                                   <tbody>

                                                      @foreach ($invoiceitems as $r)
                                                         <tr>
                                                            <td>
                                                               <div class="custom-control custom-checkbox">
                                                                  <input type="checkbox" name="itemids[]"
                                                                     id="select{{ $r->id }}"
                                                                     class="custom-control-input select-invoice"
                                                                     value="{{ $r->id }}">
                                                                  <label class="custom-control-label"
                                                                     for="select{{ $r->id }}">&nbsp;</label>
                                                               </div>
                                                            </td>
                                                            <td>
                                                               <textarea class="form-control lineitem"
                                                                  name="item[{{ $r->id }}][description]"
                                                                  id="descriptionInvoice" cols="3"
                                                                  rows="1">{{ $r->description }}</textarea>
                                                            </td>
                                                            <td>
                                                               <input type="text" name="item[{{ $r->id }}][amount]"
                                                                  id="amount-invoice{{ $r->invoiceid }}"
                                                                  class="form-control text-center"
                                                                  value="{{ $r->amount }}">
                                                            </td>
                                                            <td>
                                                               <div class="form-check text-center mt-2">
                                                                  <input class="form-check-input"
                                                                     name="item[{{ $r->id }}][taxed]"
                                                                     type="checkbox" value="1"
                                                                     id="defaultCheck1{{ $r->invoiceid }}"
                                                                     {{ $r->taxed == 1 ? 'checked' : '' }}>
                                                               </div>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                               <button type="button" class="btn btn-link text-danger"
                                                                  onclick="deleteInvoiceItemById({{ $r->id }})"><i class="fas fa-trash"></i></button>
                                                            </td>
                                                         </tr>
                                                      @endforeach
                                                      <tr>
                                                         <td></td>
                                                         <td>
                                                            <textarea class="form-control" name="adddescription"
                                                               id="descriptionInvoice" cols="3" rows="1"></textarea>
                                                         </td>
                                                         <td>
                                                            <input type="text" name="addamount" id="amount-invoice"
                                                               class="form-control">
                                                         </td>
                                                         <td>
                                                            <div class="form-check text-center mt-2">
                                                               <input class="form-check-input" name="addtaxed"
                                                                  type="checkbox" value="1" id="defaultCheck1">
                                                            </div>
                                                         </td>
                                                         <td></td>
                                                      <tr>
                                                   </tbody>
                                                   <tfoot>
                                                      <tr class="bg-light">
                                                         <td colspan="2">
                                                            <div class="row align-items-center">
                                                               <div class="col-lg-6">
                                                                  <select type="select"
                                                                     class="form-control form-control-sm w-25"
                                                                     id="mass-input">
                                                                     <option value="none">With Selected</option>
                                                                     <option value="split">Split To New Invoice</option>
                                                                     <option value="delete">Delete</option>
                                                                  </select>
                                                               </div>
                                                               <div class="col-lg-6 text-right">
                                                                  <p class="m-0 font-weight-bold">
                                                                     Sub Total
                                                                  </p>
                                                               </div>
                                                            </div>
                                                         </td>
                                                         <td class="text-center">
                                                            {{-- {{ $invoice->subtotal }} --}}
                                                            {{ $data['subtotal'] }}
                                                         </td>
                                                         <td></td>
                                                         <td></td>
                                                      </tr>
                                                      {{-- <tr>
                                                         <td>
                                                            <div class="row align-items-center">
                                                               <div class="col-lg-6">
                                                                  <select type="select"
                                                                     class="form-control form-control-sm w-50"
                                                                     id="mass-input">
                                                                     <option value="none">With Selected</option>
                                                                     <option value="split">Split To New Invoice</option>
                                                                     <option value="delete">Delete</option>
                                                                  </select>
                                                               </div>
                                                               <div class="col-lg-6 text-right">
                                                                  <p class="m-0 font-weight-bold">
                                                                     Sub Total
                                                                  </p>
                                                               </div>
                                                            </div>
                                                         </td>
                                                         <td class="align-middle" style="text-align: right;">
                                                            {{ $invoice->subtotal }}</td>
                                                      </tr> --}}
                                                      @if ($data['taxname'])
                                                         <tr class="bg-light">
                                                            <td></td>
                                                            <td class="text-right">{{ $data['taxrate'] }}% {{ $data['taxname'] }}</td>
                                                            <td class="text-center">{{ $data['tax'] }}</td>
                                                            <td></td>
                                                            <td></td>
                                                         </tr>
                                                      @endif
                                                      @if ($data['taxname2'])
                                                         <tr class="bg-light">
                                                            <td></td>
                                                            <td class="text-right">{{ $data['taxrate2'] }}% {{ $data['taxname2'] }}</td>
                                                            <td class="text-center">{{ $data['tax2'] }}</td>
                                                            <td></td>
                                                            <td></td>
                                                         </tr>
                                                      @endif
                                                      <tr class="bg-light">
                                                         <td></td>
                                                         <td class="text-right">Credit</td>
                                                         {{-- <td class="text-center">{{ $invoice->credit }}</td> --}}
                                                         <td class="text-center">{{ $data['credit'] }}</td>
                                                         <td></td>
                                                         <td></td>
                                                      </tr>
                                                      <tr class="bg-primary text-white">
                                                         <th></th>
                                                         <th class="text-right">Total Due</th>
                                                         {{-- <th class="text-center">{{ $invoice->alltotal }}</th> --}}
                                                         <th class="text-center">{{ $data['total'] }}</th>
                                                         <th></th>
                                                         <th></th>
                                                      </tr>
                                                   </tfoot>
                                                </table>
                                             </div>
                                          </div>
                                          <div class="col-lg-12 d-flex justify-content-center">
                                             <input type="hidden" name="updateinvoice" value="Update Invoice">
                                             <button class="btn btn-success mx-1" type="submit">
                                                Save Changes
                                             </button>
                                             <a href="{{ url($baseURL . 'invoices') }}"
                                                class="btn btn-light mx-1">Cancel
                                                Changes</a>
                                          </div>
                                       </div>
                                    </form>

                                    <hr>
                                    <div class="row">
                                       <div class="col-lg-12 my-2">
                                          <h5>Transaction</h5>
                                          <div class="table-responsive">
                                             {{-- {!!$aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("fields", "paymentmethod"), $aInt->lang("fields", "transid"), $aInt->lang("fields", "amount"), $aInt->lang("fields", "fees"), ""), $transactions)!!} --}}
                                             <table id="datatable2"
                                                class="display table table-borderless dt-responsive w-100">
                                                <thead>
                                                   <tr>
                                                      <th>Date</th>
                                                      <th>Payment Method</th>
                                                      <th>Transaction ID</th>
                                                      <th>Amount</th>
                                                      <th>Transaction Fees</th>
                                                      <th>Action</th>
                                                      <th></th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   @foreach ($transactions as $transaction)
                                                      <tr>
                                                         <td>{{$transaction[0]}}</td>
                                                         <td>{{$transaction[1]}}</td>
                                                         <td>{{$transaction[2]}}</td>
                                                         <td>{{$transaction[3]}}</td>
                                                         <td>{{$transaction[4]}}</td>
                                                         {{-- <td>{!!$transaction[5]!!}</td> --}}
                                                      </tr>
                                                   @endforeach
                                                </tbody>
                                                {{-- @foreach ($transaction as $r)
                                                   <tr>
                                                      <td>{{ $r['date'] }}</td>
                                                      <td>{{ $r['gateway'] }}</td>
                                                      <td>{{ $r['transid'] }}</td>
                                                      <td>{{ $r['amountin'] }}</td>
                                                      <td>{{ $r['fees'] }}</td>
                                                   </tr>
                                                @endforeach --}}
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                       <div class="col-lg-12 my-2">
                                          <h5>Transaction History</h5>
                                          <div class="table-responsive">
                                             <table id="datatable3" class="display table dt-responsive w-100">
                                                <thead>
                                                   <tr>
                                                      <th>Date</th>
                                                      <th>Payment Method</th>
                                                      <th>Transaction ID</th>
                                                      <th>Status</th>
                                                      <th>Description</th>
                                                   </tr>
                                                </thead>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                    
                                    <!--WA POPUP-->
                                    <div class="modal fade" id="sendCustomWAModal" tabindex="-1" role="dialog" aria-labelledby="sendCustomWAModalLabel" aria-hidden="true">
                                        {{-- <div class="modal-dialog" role="document"> --}}
                                          <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="sendCustomWAModalLabel">Send WA Message To All Active Clients</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form id="customWAForm" action="/admin/clients/viewclients/sendCustomWA" method="GET" >
                                          <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="customMessage">Message:</label>
                                                    <textarea class="form-control" id="customMessage" name="message" rows="4" required></textarea>
                                                </div>
                                                <div class="form-group">
                                                {{-- <label for="delaySeconds">Delay between messages (seconds):</label>
                                                <input type="number" class="form-control" id="delaySeconds" name="delay" value="15" min="1" required> --}}
                                                <label for="delaySeconds">Delay between messages (seconds):</label>
                                                    <select class="form-control" id="delaySeconds" name="delay" required>
                                                        <option value="15">15 seconds</option>
                                                        <option value="30">30 seconds</option>
                                                        <option value="60">60 seconds</option>
                                                        <option value="90">90 seconds</option>
                                                    </select>
                                                </div>
                                            </div>
                                            {{-- <div class="modal-footer">
                                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                              <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to send this message to all active clients?');">Send Message</button>
                                            </div> --}}
                                            <div class="modal-footer">
                                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                              <button type="button" class="btn btn-primary" onclick="sendWAMessage(this.form)">Send Message</button>
                                          </div>
                                    </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!--WA POPUP-->
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection


@section('scripts')
   <!-- Required datatable js -->
   <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
   <!-- Buttons examples -->
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
   <!-- Responsive examples -->
   <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/accordion-radio.js') }}"></script>
   <script src="{{ Theme::asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/pages/create-invoice.js') }}"></script>
   <script src="{{ Theme::asset('js/notify.min.js') }}"></script>
   <script>
     // Untuk single Resend WA
    function resendWA(invoiceId) {
        Swal.fire({
            title: 'Kirim Ulang WA?',
            text: "Pesan WA akan dikirim ulang ke client",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-success mx-2',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Menggunakan route yang sudah ada
                window.open(`/admin/clients/viewclients/resendWA?show_loading=1&invoiceid=${invoiceId}`, '_blank');
            }
        });
    }
    
    // Untuk Resend WA By GroupClients
    function resendWAByGroup(groupId) {
        // Get group name dulu via AJAX
        $.get(`/admin/clients/viewclients/getGroupName/${groupId}`, function(groupName) {
            Swal.fire({
                title: `Kirim WA ke Group "<strong>${groupName}</strong>"?`,
                text: "Pesan WA akan dikirim ke semua client dalam group ini",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-success mx-2',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(`/admin/clients/viewclients/resendWABulk?show_loading=1&groupid=${groupId}`, '_blank');
                }
            });
        });
    }
    
    function sendWAMessage(form) {
        Swal.fire({
            title: 'Kirim Pesan WhatsApp?',
            text: "Pesan akan dikirim ke semua klien yang aktif",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-success mx-2',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Ambil nilai form
                let message = form.querySelector('[name="message"]').value;
                const delay = form.querySelector('[name="delay"]').value;
                
                // Format line breaks untuk URL
                message = message.replace(/\n/g, '%0A');
                
                // Buka halaman loading di tab baru dengan format line break yang benar
                window.open(`/admin/clients/viewclients/sendCustomWA?show_loading=1&message=${encodeURIComponent(message)}&delay=${delay}`, '_blank');
                
                // Tutup modal
                $('#sendCustomWAModal').modal('hide');
            }
        });
    }
    
    </script>
   <script type="text/javascript">
      const Toast = Swal.mixin({
         toast: true,
         position: 'top-right',
         iconColor: 'white',
         customClass: {
            popup: 'colored-toast'
         },
         showConfirmButton: false,
         timer: 1500,
         timerProgressBar: true
      })

      $(document).ready(function() {
         $('.lineitem').each(function(i, obj) {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
         });

         $('.inputdate').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            orientation: 'bottom',
            todayBtn: 'linked',
            todayHighlight: true,
            clearBtn: false,
            disableTouchKeyboard: true,
         });
         $("#refundtype").change(function() {
            if ($(this).val() === '') {
               $('#TransactionID').show();
            } else {
               $('#TransactionID').hide();
            }
            return false;
         });

         $("#mass-input").on("change", function() {
            var action = $(this).val();
            var itemids = [];
            $.each($("input[name='itemids[]']:checked"), function(){
               itemids.push($(this).val());
            });
            $.ajax({
               url: route('apiconsumer.admin.billing.mass-action-invoice-items'),
               type: 'POST',
               data: {action: action, itemids: itemids, invoiceid: '{{$invoice->id}}'},
               success: function(res) {
                  setSelectedOption();
                  if (res.result == 'success') {
                        $.notify(res.message, "success");
                        setTimeout(() => {
                           location.reload();
                        }, 1000);
                    } else {
                        $.notify(res.message, "error");
                    }
               },
               error: function(xhr, status, error) {
                  setSelectedOption();
                  var e = JSON.parse(xhr.responseText);
                  $.notify(e.message, "error");
               }
            });
         });
      });

      function setSelectedOption(val = 'none') {
         $("#mass-input").val(val);
      }

      const deleteInvoiceItemById = async (id) => {
         const url = route('admin.pages.billing.invoiceitem.delete');
         const invId = '{{ $invoice->id }}'
         const data = {
            iid: id,
            invoiceId: invId
         }
         //  console.log(invoiceId);

         Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#179A73',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
         }).then(async result => {
            if (result.isConfirmed) {
               fetch(url, {
                     method: "POST",
                     headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '{{ csrf_token() }}'
                     },
                     body: JSON.stringify(data)
                  })
                  .then(response => response.json())
                  .then(async res => {
                     await Toast.fire({
                        icon: 'success',
                        text: res.text,

                     })
                     await location.reload();
                  })
                  .catch(err => {
                     console.log(err);
                  })
            }
         })
      }
   </script>
   <script>
    function doDeleteTransaction(id) {
        if (confirm("{{Lang::get('admin.invoicesdeletetransaction')}}")) {
            window.location = "{{route('admin.pages.billing.invoices.deletetrans')}}?ide="+id;
        }
        return false;
    }
   </script>
@endsection

