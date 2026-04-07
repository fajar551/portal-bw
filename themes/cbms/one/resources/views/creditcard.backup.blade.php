@extends('layouts.clientbase')

@section('page-title')
   {{Lang::get("client.creditcard")}}
@endsection
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{Theme::asset('css/all.min.css')}}" />
    {{-- <link rel="stylesheet" type="text/css" href="{{Theme::asset('assets/css/theme.min.css')}}" /> --}}
    <style>
        .hidden {
            display: none;
        }
    </style>
@endsection
@section('scripts')
    {{-- <script type="text/javascript" src="{{Theme::asset('js/scripts.js')}}"></script> --}}
@endsection
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @if (!$remotecode)
            <script type="text/javascript" src="{{Theme::asset('js/scripts.js')}}"></script>
                <script type="text/javascript" src="{{Theme::asset('js/jquery.payment.js')}}"></script>
                <script type="text/javascript" src="{{Theme::asset('js/StatesDropdown.js')}}"></script>
                <script>
                    var stateNotRequired = true,
                        ccForm = '';

                    function validateCreditCardInput(e)
                    {
                        var newOrExisting = $('input[name="ccinfo"]:checked').val(),
                            submitButton = $('#btnSubmit'),
                            cardType = null,
                            submit = true;

                        ccForm.find('.form-group').removeClass('has-error');
                        ccForm.find('.field-error-msg').hide();

                        if (newOrExisting === 'new') {
                            cardType = jQuery.payment.cardType(ccForm.find('#inputCardNumber').val());
                            if (!jQuery.payment.validateCardNumber(ccForm.find('#inputCardNumber').val())) {
                                ccForm.find('#inputCardNumber').showInputError();
                                submit = false;
                            }
                            if (
                                !jQuery.payment.validateCardExpiry(
                                    ccForm.find('#inputCardExpiry').payment('cardExpiryVal')
                                )
                            ) {
                                ccForm.find('#inputCardExpiry').showInputError();
                                submit = false;
                            }
                        }
                        if (!jQuery.payment.validateCardCVC(ccForm.find('#inputCardCvv').val(), cardType)) {
                            ccForm.find('#inputCardCvv').showInputError();
                            submit = false;
                        }
                        if (!submit) {
                            submitButton.prop('disabled', false).removeClass('disabled')
                                .find('span').toggleClass('hidden');
                            e.preventDefault();
                        }
                    }

                    $(document).ready(function() {
                        ccForm = $('#frmPayment');
                        ccForm.on('submit', validateCreditCardInput);
                        $('.paymethod-info input[name="ccinfo"]').on('ifChecked', function() {
                            if ($(this).val() === 'new') {
                                showNewCardInputFields();
                            } else {
                                hideNewCardInputFields();
                            }
                        });

                        $('#billingAddressChoice input[name="billingcontact"]').on('ifChecked', function() {
                            if ($(this).val() === 'new') {
                                showNewBillingAddressFields();
                            } else {
                                hideNewBillingAddressFields();
                            }
                        });

                        ccForm.find('#inputCardNumber').payment('formatCardNumber');
                        ccForm.find('#inputCardStart').payment('formatCardExpiry');
                        ccForm.find('#inputCardExpiry').payment('formatCardExpiry');
                        ccForm.find('#inputCardCvv').payment('formatCardCVC');
                        ccForm.find('#ccissuenum').payment('restrictNumeric');
                    });
                </script>
            @endif
            <form id="frmPayment" method="post" action="{{url('creditcard.php?invoiceid='.$invoiceid)}}" class="form-horizontal" role="form">
                @csrf
                <input type="hidden" name="action" value="submit" />
                <input type="hidden" name="invoiceid" value="{{$invoiceid}}" />

                <div class="row">
                    <div class="col-md-7">

                        @if ($errormessage)
                            @include('includes.alert', [
                                'type' => 'error',
                                'errorshtml' => $errormessage
                            ])
                        @endif

                        <div class="alert alert-danger text-center gateway-errors hidden"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{Lang::get('client.paymentmethod')}}</label>
                            <div class="col-sm-8">
                                @if (count($existingCards) > 0)
                                    <div class="three-column-grid">
                                        @foreach ($existingCards as $cardInfo)
                                            @php
                                                $payMethodExpired = 0;
                                                $expiryDate = "";
                                                $payMethod = $cardInfo['payMethod'];
                                                if ($payMethod->payment->isExpired()) {
                                                    $payMethodExpired = 1;
                                                }
                                                if ($payMethod->payment->getExpiryDate()) {
                                                    $expiryDate = $payMethod->payment->getExpiryDate()->format('m/Y');
                                                }
                                            @endphp
                                            <div class="paymethod-info" data-paymethod-id="{{$cardInfo['paymethodid']}}">
                                                <input
                                                    id="existingCard{{$cardInfo['paymethodid']}}"
                                                    type="radio"
                                                    name="ccinfo"
                                                    class="existing-card icheck-button"
                                                    data-billing-contact-id="{{$cardInfo['billingcontactid']}}"
                                                    @if ($cardOnFile && !$payMethodExpired && $ccinfo == $cardInfo['paymethodid'])
                                                        @php
                                                            $preselectedBillingContactId = $cardInfo['billingcontactid'];
                                                        @endphp
                                                        checked="checked" data-loaded-paymethod="true"
                                                    @elseif (($cardOnFile && $payMethodExpired) || !$cardOnFile)
                                                        disabled="disabled"
                                                    @endif
                                                    onclick="@if ($remotecode)hideRemoteInputForm() @else hideNewCardInputFields() @endif;"
                                                    value="{{$cardInfo['paymethodid']}}"
                                                >
                                            </div>
                                            <div class="paymethod-info" data-paymethod-id="{{$cardInfo['paymethodid']}}">
                                                <label for="existingCard{{$cardInfo['paymethodid']}}">
                                                    <i class="{{$payMethod->getFontAwesomeIcon()}}"></i>
                                                </label>
                                            </div>
                                            <div class="paymethod-info" data-paymethod-id="{{$cardInfo['paymethodid']}}">
                                                <label for="existingCard{{$cardInfo['paymethodid']}}">
                                                    {{$payMethod->payment->getDisplayName()}}
                                                </label>
                                            </div>
                                            <div class="paymethod-info" data-paymethod-id="{{$cardInfo['paymethodid']}}">
                                                <label for="existingCard{{$cardInfo['paymethodid']}}">
                                                    {{$payMethod->getDescription()}}
                                                </label>
                                            </div>
                                            <div class="paymethod-info" data-paymethod-id="{{$cardInfo['paymethodid']}}">
                                                <label for="existingCard{{$cardInfo['paymethodid']}}">
                                                    {{$expiryDate}}
                                                    @if ($payMethodExpired)<br><small>{{Lang::get('client.clientareaexpired')}}</small>@endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="paymethod-info">
                                    <label>
                                        <input id="newCCInfo" type="radio" class="icheck-button" name="ccinfo" value="new" onclick="@if ($remotecode)hideRemoteInputForm() @else hideNewCardInputFields() @endif;"@if ($ccinfo == "new" || !$cardOnFile) checked @endif /> {{Lang::get('client.creditcardenternewcard')}}</label>
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if ($remotecode)
                            <div id="remoteInput" data-payment-module="{{$invoice['paymentmodule']}}" class="@if ($ccinfo != "new" || $cardOnFile) hidden  @endif">
                                <p><i class="fas fa-spinner fa-spin"></i> {{Lang::get('client.loading')}}</p>
                            </div>
                            <div id="remoteInputLoading" class="hidden"><p><i class="fas fa-spinner fa-spin"></i> {{Lang::get('client.loading')}}</p></div>
                            <div id="remoteInputError" class="hidden">
                                <div class="alert alert-danger"></div>
                            </div>
                            @if ($ccinfo == 'new')
                                <script>
                                    $(document).ready(function(){
                                        showRemoteInputForm();
                                    });
                                </script>
                            @endif
                        @else
                            <div class="form-group cc-details @if (!$addingNewCard) hidden  @endif">
                                <label for="inputCardNumber" class="col-sm-4 control-label">{{Lang::get('client.creditcardcardnumber')}}</label>
                                <div class="col-sm-7">
                                    <input type="tel" name="ccnumber" id="inputCardNumber" size="30" value="@if ($ccnumber){{$ccnumber}}@endif" autocomplete="off" class="form-control newccinfo cc-number-field" />
                                    <span class="field-error-msg">{{Lang::get('client.paymentMethodsManage.cardNumberNotValid')}}</span>
                                </div>
                            </div>
                            @if ($showccissuestart)
                                <div class="form-group cc-details @if (!$addingNewCard) hidden  @endif">
                                    <label for="inputCardStart" class="col-sm-4 control-label">{{Lang::get('client.creditcardcardstart')}}</label>
                                    <div class="col-sm-8">
                                        <input type="tel" name="ccstartdate" id="inputCardStart" class="form-control field input-inline input-inline-100" placeholder="MM / YY ({{Lang::get('client.creditcardcardstart')}})">
                                    </div>
                                </div>
                            @endif
                            <div class="form-group cc-details @if (!$addingNewCard) hidden  @endif">
                                <label for="inputCardExpiry" class="col-sm-4 control-label">{{Lang::get('client.creditcardcardexpires')}}</label>
                                <div class="col-sm-8">
                                    <input type="tel" name="ccexpirydate" id="inputCardExpiry" class="form-control field input-inline input-inline-100" placeholder="MM / YY @if ($showccissuestart) ({{Lang::get('client.creditcardcardexpires')}}) @endif" autocomplete="cc-exp">
                                    <span class="field-error-msg">{{Lang::get('client.paymentMethodsManage.expiryDateNotValid')}}</span>
                                </div>
                            </div>
                            @if ($showccissuestart)
                                <div class="form-group cc-details @if (!$addingNewCard) hidden  @endif">
                                    <label for="inputIssueNum" class="col-sm-4 control-label">{{Lang::get('client.creditcardcardissuenum')}}</label>
                                    <div class="col-xs-2">
                                        <input type="number" name="ccissuenum" id="inputIssueNum" value="{{$ccissuenum}}" class="form-control  input-inline input-inline-100" />
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <label for="cctype" class="col-sm-4 control-label">{{Lang::get('client.creditcardcvvnumber')}}</label>
                                <div class="col-sm-7">
                                    <input type="number" name="cccvv" id="inputCardCvv" value="{{$cccvv}}" autocomplete="off" class="form-control input-inline input-inline-100" />
                                    <button id="cvvWhereLink" type="button" class="btn btn-link" data-toggle="popover" data-content="<img src='{{Theme::asset('img/ccv.gif')}}' width='210'>">
                                        {{Lang::get('client.creditcardcvvwhere')}}
                                    </button>
                                    <br>
                                    <span class="field-error-msg">{{Lang::get('client.paymentMethodsManage.cvcNumberNotValid')}}</span>
                                </div>
                            </div>

                            <div class="form-group" id="billingAddressChoice" @if (!$addingNewCard)style="display: none"@endif>
                                <label for="cctype" class="col-sm-4 control-label">{{Lang::get('client.billingAddress')}}</label>
                                <div class="col-sm-8">
                                    <label class="radio-inline icheck-label billing-contact-0">
                                        <input
                                            type="radio"
                                            class="icheck-button"
                                            name="billingcontact"
                                            value="0"
                                            @if (!$billingcontact || $billingcontact != $client->billingContactId) checked @endif
                                        >

                                        <strong class="name">{{$client->fullName}}</strong>
                                        <span class="address1">{{$client->address1}}</span>,
                                        @if ($client->address2)<span class="address2">{{$client->address2}}</span>,@endif
                                        <span class="city">{{$client->city}}</span>,
                                        <span class="state">{{$client->state}}</span>,
                                        <span class="postcode">{{$client->postcode}}</span>,
                                        <span class="country">{{$client->country}}</span>
                                    </label>
                                    <br>
                                    @foreach ($client->contacts()->orderBy('firstname', 'asc')->orderBy('lastname', 'asc')->get() as $contact)
                                        <label class="radio-inline icheck-label billing-contact-{{{$contact->id}}}">
                                            <input
                                                type="radio"
                                                class="icheck-button"
                                                name="billingcontact"
                                                value="{{{$contact->id}}}"
                                                @if ($billingcontact == $contact->id || $contact->id == $client->billingContactId) checked @endif
                                            >

                                            <strong class="name">{{$contact->fullName}}</strong>
                                            <span class="address1">{{$contact->address1}}</span>,
                                            @if ($contact->address2)<span class="address2">{{$contact->address2}}</span>,@endif
                                            <span class="city">{{$contact->city}}</span>,
                                            <span class="state">{{$contact->state}}</span>,
                                            <span class="postcode">{{$contact->postcode}}</span>,
                                            <span class="country">{{$contact->country}}</span>
                                        </label>
                                        <br>
                                    @endforeach
                                    <label class="radio-inline icheck-label">
                                        <input
                                            type="radio"
                                            class="icheck-button"
                                            name="billingcontact"
                                            value="new"
                                            @if ($billingcontact == 'new') checked @endif
                                        >
                                        {{Lang::get('client.paymentMethodsManage.addNewBillingAddress')}}
                                    </label>
                                </div>
                            </div>
                            <div id="newBillingAddress" @if (!$userDetailsValidationError && $billingcontact != 'new') style="display: none"@endif>
                                <div class="form-group cc-billing-address">
                                    <label for="inputFirstName" class="col-sm-4 control-label">{{Lang::get('client.clientareafirstname')}}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="firstname" id="inputFirstName" value="{{$firstname}}" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group cc-billing-address">
                                    <label for="inputLastName" class="col-sm-4 control-label">{{Lang::get('client.clientarealastname')}}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="lastname" id="inputLastName" value="{{$lastname}}" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group cc-billing-address">
                                    <label for="inputAddress1" class="col-sm-4 control-label">{{Lang::get('client.clientareaaddress1')}}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="address1" id="inputAddress1" value="{{$address1}}" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group cc-billing-address">
                                    <label for="inputAddress2" class="col-sm-4 control-label">{{Lang::get('client.clientareaaddress2')}}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="address2" id="inputAddress2" value="{{$address2}}" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group cc-billing-address">
                                    <label for="inputCity" class="col-sm-4 control-label">{{Lang::get('client.clientareacity')}}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="city" id="inputCity" value="{{$city}}" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group cc-billing-address">
                                    <label for="inputState" class="col-sm-4 control-label">{{Lang::get('client.clientareastate')}}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="state" id="inputState" value="{{$state}}" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group cc-billing-address">
                                    <label for="inputPostcode" class="col-sm-4 control-label">{{Lang::get('client.clientareapostcode')}}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="postcode" id="inputPostcode" value="{{$postcode}}" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-group cc-billing-address">
                                    <label for="inputCountry" class="col-sm-4 control-label">{{Lang::get('client.clientareacountry')}}</label>
                                    <div class="col-sm-6">
                                        {!!$countriesdropdown!!}
                                    </div>
                                </div>
                                <div class="form-group cc-billing-address">
                                    <label for="inputPhone" class="col-sm-4 control-label">{{Lang::get('client.clientareaphonenumber')}}</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="phonenumber" id="inputPhone" value="{{$phonenumber}}" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            @if ($allowClientsToRemoveCards)
                                <div class="form-group cc-details @if (!$addingNewCard) hidden  @endif">
                                    <div class="col-sm-offset-4 col-sm-8">
                                        <input type="hidden" name="nostore" value="1">
                                        <input type="checkbox" class="toggle-switch-success" data-size="mini" checked="checked" name="nostore" id="inputNoStore" value="0" data-on-text="{{Lang::get('client.yes')}}" data-off-text="{{Lang::get('client.no')}}">
                                        <label class="checkbox-inline no-padding" for="inputNoStore">
                                            &nbsp;&nbsp;
                                            {{Lang::get('client.creditCardStore')}}
                                        </label>

                                    </div>
                                </div>
                            @endif
                            <div id="inputDescriptionContainer" class="form-group cc-details @if (!$addingNewCard) hidden  @endif">
                                <label for="inputDescription" class="col-sm-4 control-label">{{Lang::get('client.paymentMethods.cardDescription')}}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="inputDescription" name="ccdescription" autocomplete="off" value="" placeholder="{{Lang::get('client.paymentMethods.descriptionInput')}} {{Lang::get('client.paymentMethodsManage.optional')}}" />
                                </div>
                            </div>
                        @endif
                        <div id="btnSubmitContainer" class="form-group submit-container">
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg margin-top-5" id="btnSubmit" value="{{Lang::get('client.submitpayment')}}">
                                    <span class="pay-text">{{Lang::get('client.submitpayment')}}</span>
                                    <span class="click-text hidden">{{Lang::get('client.pleasewait')}}</span>
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-5">

                        <div id="invoiceIdSummary" class="invoice-summary">
                            <h2 class="text-center">
                                {{Lang::get('client.invoicenumber')}}@if ($invoicenum){{$invoicenum}}@else{{$invoiceid}}@endif
                            </h2>
                            <div class="invoice-summary-table">
                            <table class="table table-condensed">
                                <tr>
                                    <td class="text-center"><strong>{{Lang::get('client.invoicesdescription')}}</strong></td>
                                    <td width="150" class="text-center"><strong>{{Lang::get('client.invoicesamount')}}</strong></td>
                                </tr>
                                @foreach ($invoiceitems as $item)
                                    <tr>
                                        <td>{!!$item['description']!!}</td>
                                        <td class="text-center">{{$item['amount']}}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="total-row text-right">{{Lang::get('client.invoicessubtotal')}}</td>
                                    <td class="total-row text-center">{{$invoice['subtotal']}}</td>
                                </tr>
                                @if ($invoice['taxrate'])
                                    <tr>
                                        <td class="total-row text-right">{{$invoice['taxrate']}}% {{$invoice['taxname']}}</td>
                                        <td class="total-row text-center">{{$invoice['tax']}}</td>
                                    </tr>
                                @endif
                                @if ($invoice['taxrate2'])
                                    <tr>
                                        <td class="total-row text-right">{{$invoice['taxrate2']}}% {{$invoice['taxname2']}}</td>
                                        <td class="total-row text-center">{{$invoice['tax2']}}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="total-row text-right">{{Lang::get('client.invoicescredit')}}</td>
                                    <td class="total-row text-center">{{$invoice['credit']}}</td>
                                </tr>
                                <tr>
                                    <td class="total-row text-right">{{Lang::get('client.invoicestotaldue')}}</td>
                                    <td class="total-row text-center">{{$invoice['total']}}</td>
                                </tr>
                            </table>
                            </div>
                            <p class="text-center">
                                {{Lang::get('client.paymentstodate')}}: <strong>{{$invoice['amountpaid']}}</strong><br />
                                {{Lang::get('client.balancedue')}}: <strong>{{$balance}}</strong>
                            </p>
                        </div>

                    </div>
                </div>

                @if ($servedOverSsl)
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-lock"></i> &nbsp; {{Lang::get('client.creditcardsecuritynotice')}}
                    </div>
                @endif

            </form>
        </div>
    </div>
@endsection
