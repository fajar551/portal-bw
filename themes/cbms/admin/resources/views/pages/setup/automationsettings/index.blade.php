@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Automation Settings</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Sidebar Shortcut -->

                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Automation Settings</h4>
                                    </div>
                                    @if (Request::get('success'))
                                        {!! \App\Helpers\AdminFunctions::infoBox(
                                            $aInt->lang('automation', 'changesuccess'),
                                            $aInt->lang('automation', 'changesuccessinfo'),
                                        ) !!}
                                    @endif
                                    @if (Request::get('cronhourchanged'))
                                        {!! \App\Helpers\ViewHelper::alert(
                                            \Lang::get('admin.automationchangeOfDailyCronHourHelpText') .
                                                " <a href=\"https://docs.whmcs.com/Crons#Change_of_Daily_Cron_Hour\" target=\"_blank\" class=\"alert-link\">" .
                                                \Lang::get('admin.global.learnMore') .
                                                ' &raquo;</a>',
                                            'info',
                                        ) !!}
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <form action="{{ route('admin.configauto') }}?sub=save" method="post">
                                        @csrf
                                        <!-- START HERE -->
                                        <div class="card p-3">
                                            <div class="row">
                                                <!--<div class="col-lg-2 col-sm-12">-->
                                                <!--    <div class="automation-cron-status">-->
                                                <!--        @if ($cron->hasCronBeenInvokedIn24Hours())
    -->
                                                <!--            <div class="alert alert-success" role="alert">-->
                                                <!--                <div class="row">-->
                                                <!--                    <div class="col-12 text-center">-->
                                                <!--                        <i class="fa fa-check mr-2" aria-hidden="true"></i><strong>Cron-->
                                                <!--                            Status-->
                                                <!--                            OK</strong>-->
                                                <!--                    </div>-->
                                                <!--                    <div class="col-12 text-center">-->
                                                <!--                        <small>Last Run: {{ $lastInvocationTime }}</small>-->
                                                <!--                    </div>-->
                                                <!--                </div>-->
                                                <!--            </div>-->
                                            <!--        @else-->
                                                <!--            @if ($cron->hasCronEverBeenInvoked())
    -->
                                                <!--                <div class="alert alert-danger" role="alert">-->
                                                <!--                    <div class="row">-->
                                                <!--                        <div class="col-12 text-center">-->
                                                <!--                            <i class="fa fa-times mr-2" aria-hidden="true"></i><strong>Cron-->
                                                <!--                                Status-->
                                                <!--                                Error</strong>-->
                                                <!--                        </div>-->
                                                <!--                        <div class="col-12 text-center">-->
                                                <!--                            <small>Last Run: {{ $lastInvocationTime }}</small>-->
                                                <!--                        </div>-->
                                                <!--                    </div>-->
                                                <!--                </div>-->
                                            <!--            @else-->
                                                <!--                <div class="alert alert-warning" role="alert">-->
                                                <!--                    <div class="row">-->
                                                <!--                        <div class="col-12 text-center">-->
                                                <!--                            <i class="fa fa-exclamation-triangle mr-2" aria-hidden="true"></i><strong>No Cron Records</strong>-->
                                                <!--                        </div>-->
                                                <!--                        <div class="col-12 text-center">-->
                                                <!--                        </div>-->
                                                <!--                    </div>-->
                                                <!--                </div>-->
                                                <!--
    @endif-->
                                                <!--
    @endif-->
                                                <!--    </div>-->
                                                <!--</div>-->
                                                <div class="col-lg-10 col-sm-12">
                                                    <p class="font-size-20 pt-1">The System Cron is responsible for
                                                        automating
                                                        tasks
                                                        within
                                                        CBMS and must be configured to run periodically by the server
                                                        environment that hosts the CBMS installation.</h6>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="alert alert-primary" role="alert">
                                                        <div class="row">
                                                            <div
                                                                class="col-1 d-flex justify-content-center align-items-center">
                                                                <i class="fa fa-exclamation-circle" aria-hidden="true"
                                                                    style="font-size: 32px;"></i>
                                                            </div>
                                                            <div class="col">
                                                                <strong>
                                                                    The cron command below is provided for convenience. You
                                                                    should configure a cron task to run every 5 minutes
                                                                    using
                                                                    the command provided below within your server cron
                                                                    utility
                                                                    or web hosting control panel.
                                                                </strong>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"
                                                                id="inputGroup-sizing-default">Cron
                                                                Command</span>
                                                        </div>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"
                                                                id="inputGroup-sizing-default">*/5 *
                                                                * * *</span>
                                                        </div>
                                                        <input id="cronPhp" type="text" class="form-control"
                                                            aria-label="Default"
                                                            aria-describedby="inputGroup-sizing-default"
                                                            value="{{ \App\Helpers\Php::getPreferredCliBinary() }} {{ $artisan_path }} schedule:run"
                                                            disabled>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-secondary" type="button"><i
                                                                    class="fas fa-copy"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>Scheduling</h4>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Time of Day</label>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <select name="dailycronexecutionhour" id=""
                                                                class="form-control">
                                                                @php
                                                                    $label = 'am';
                                                                    $dailyCronExecutionHour = \App\Helpers\Cron::getDailyCronExecutionHour()->format('H');
                                                                    for ($hour = 0; $hour <= 23; $hour++) {
                                                                        $friendlyHour = $hour;
                                                                        if ($friendlyHour == 12) {
                                                                            $label = 'pm';
                                                                        } else {
                                                                            if (12 < $friendlyHour) {
                                                                                $friendlyHour -= 12;
                                                                            }
                                                                        }
                                                                        echo "<option value=\"" . $hour . "\"" . ($dailyCronExecutionHour == $hour ? ' selected' : '') . '>' . $friendlyHour . ':00' . $label . '</option>';
                                                                    }
                                                                @endphp
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-8">
                                                            <p class="m-0 pt-2">
                                                                The hour of the day you wish for the daily automated actions
                                                                to
                                                                be executed
                                                                <a href="#" data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    title="For this setting to take effect, your cron must be configured to run at least once every hour. We recommend setting it to run every 5 minutes to allow for other system processes to take place."><i
                                                                        class="fas fa-info-circle"></i> Important Note</a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>
                                                        Automatic Module Functions
                                                    </h4>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Enable
                                                            Suspension</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck1" name="autosuspend"
                                                                    @if (Cfg::get('AutoSuspension') == 'on') checked @endif>
                                                                <label class="custom-control-label" for="customCheck1">Tick
                                                                    this box to enable automatic suspension</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Suspend
                                                            Days</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control" name="days"
                                                                value="{{ Cfg::get('AutoSuspensionDays') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of days after the due payment date you want
                                                                to
                                                                wait before suspending the account
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Send Suspension
                                                            Email</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck2" name="autoSuspendEmail"
                                                                    {{ $autoSuspendChecked }}>
                                                                <label class="custom-control-label"
                                                                    for="customCheck2">Tick this
                                                                    box to send the Service Suspension Notification email on
                                                                    successful Suspend.</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Enable
                                                            Unsuspension</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck3" name="autounsuspend"
                                                                    @if (Cfg::get('AutoUnsuspend') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck3">Tick this
                                                                    box to enable automatic unsuspension on payment</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Send
                                                            Unsuspension
                                                            Email</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck4" name="autoUnsuspendEmail"
                                                                    {{ $autoUnsuspendChecked }}>
                                                                <label class="custom-control-label"
                                                                    for="customCheck4">Tick this
                                                                    box to send the Service Unsuspension Notification email
                                                                    on
                                                                    successful Unsuspend.</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Enable
                                                            Termination</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck5" name="autotermination"
                                                                    @if (Cfg::get('AutoTermination') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck5">Tick this
                                                                    box to enable automatic termination.</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Termination
                                                            Days</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="autoterminationdays"
                                                                value="{{ Cfg::get('AutoTerminationDays') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of days after the due payment date you want
                                                                to
                                                                wait before terminating the account
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>Billing Settings</h4>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Invoice
                                                            Generation</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="createinvoicedays"
                                                                value="{{ Cfg::get('CreateInvoiceDaysBefore') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-8">
                                                            <p class="m-0 pt-2">
                                                                Enter the default number of days before the due payment date
                                                                to
                                                                generate invoices (<a href="#"
                                                                    onclick="showadvinvoice();return false">Advanced
                                                                    Settings</a>)
                                                            </p>
                                                            <div id="advinvoicesettings" class="text-center"
                                                                style="display: none;">
                                                                <b>Per Billing Cycle Settings</b>
                                                                <p>This allows you to specify for certain cycles to generate
                                                                    further or less in advance of the due date than the
                                                                    default specified above:</p>
                                                                <div class="row">
                                                                    <div class="col-md-2 text-center">
                                                                        <b>Monthly</b>
                                                                        <input type="text" class="form-control"
                                                                            name="invoicegenmonthly"
                                                                            value="{{ Cfg::get('CreateInvoiceDaysBeforeMonthly') }}">
                                                                    </div>
                                                                    <div class="col-md-2 text-center">
                                                                        <b>Quarterly</b>
                                                                        <input type="text" class="form-control"
                                                                            name="invoicegenquarterly"
                                                                            value="{{ Cfg::get('CreateInvoiceDaysBeforeQuarterly') }}">
                                                                    </div>
                                                                    <div class="col-md-2 text-center">
                                                                        <b>Semi-Annually</b>
                                                                        <input type="text" class="form-control"
                                                                            name="invoicegensemiannually"
                                                                            value="{{ Cfg::get('CreateInvoiceDaysBeforeSemiAnnually') }}">
                                                                    </div>
                                                                    <div class="col-md-2 text-center">
                                                                        <b>Annually</b>
                                                                        <input type="text" class="form-control"
                                                                            name="invoicegenannually"
                                                                            value="{{ Cfg::get('CreateInvoiceDaysBeforeAnnually') }}">
                                                                    </div>
                                                                    <div class="col-md-2 text-center">
                                                                        <b>Biennially</b>
                                                                        <input type="text" class="form-control"
                                                                            name="invoicegenbiennially"
                                                                            value="{{ Cfg::get('CreateInvoiceDaysBeforeBiennially') }}">
                                                                    </div>
                                                                    <div class="col-md-2 text-center">
                                                                        <b>Triennially</b>
                                                                        <input type="text" class="form-control"
                                                                            name="invoicegentriennially"
                                                                            value="{{ Cfg::get('CreateInvoiceDaysBeforeTriennially') }}">
                                                                    </div>
                                                                </div>
                                                                <p>(Leave blank to use default setting for a cycle)</p>
                                                                <b class="mt-3">Domain Settings</b>
                                                                <p>Enter the number of days before the renewal date to
                                                                    generate invoices for domain renewals below:</p>
                                                                <div class="row justify-content-center">
                                                                    <div class="col-md-1">
                                                                        <input type="text" class="form-control"
                                                                            name="createdomaininvoicedays"
                                                                            value="{{ Cfg::get('CreateDomainInvoiceDaysBefore') }}">
                                                                    </div>
                                                                    <div class="col-md-6 text-left">
                                                                        (Leave blank to use default setting)
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Payment Reminder
                                                            Emails</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck6" name="invoicesendreminder"
                                                                    @if (Cfg::get('SendReminder') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck6">Tick this
                                                                    box to enable automatic termination.</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Invoice Unpaid
                                                            Reminder</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="invoicesendreminderdays"
                                                                value="{{ Cfg::get('SendInvoiceReminderDays') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of days before the invoice due date you
                                                                would
                                                                like to send a reminder (0 to disable)
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">First Overdue
                                                            Reminder</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="invoicefirstoverduereminder"
                                                                value="{{ Cfg::get('SendFirstOverdueInvoiceReminder') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of days after the invoice due date you
                                                                would
                                                                like to send the first overdue notice (0 to disable)
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Second Overdue
                                                            Reminder</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="invoicesecondoverduereminder"
                                                                value="{{ Cfg::get('SendSecondOverdueInvoiceReminder') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of days after the invoice due date you
                                                                would
                                                                like to send the second overdue notice (0 to disable)
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Third Overdue
                                                            Reminder</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="invoicethirdoverduereminder"
                                                                value="{{ Cfg::get('SendThirdOverdueInvoiceReminder') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of days after the invoice due date you
                                                                would
                                                                like to send the third overdue notice (0 to disable)
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Add Late Fee
                                                            Days</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="addlatefeedays"
                                                                value="{{ Cfg::get('AddLateFeeDays') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of days after the due payment date you want
                                                                to
                                                                add the late fee
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Overage Billing
                                                            Charge</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="overagebillingmethod" id="exampleRadios1"
                                                                    value="1"
                                                                    @if (Cfg::get('OverageBillingMethod') == '1') checked @endif>
                                                                <label class="form-check-label" for="exampleRadios1">
                                                                    Calculate & invoice on the last day of the month
                                                                    independently from the related product
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="overagebillingmethod" id="exampleRadios2"
                                                                    value="2"
                                                                    @if (Cfg::get('OverageBillingMethod') == '2') checked @endif>
                                                                <label class="form-check-label" for="exampleRadios2">
                                                                    Calculate on the last day of the month but include on
                                                                    the
                                                                    next invoice to generate for the client
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Change Invoice
                                                            Status</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck7" name="revchangeinvoicestatus"
                                                                    value="1"
                                                                    @if (Cfg::get('ReversalChangeInvoiceStatus') == '1') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck7">Allow
                                                                    payment reversals to change invoice status</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Change Due
                                                            Dates</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck8" value="1"
                                                                    name="revchangeduedates"
                                                                    @if (Cfg::get('ReversalChangeDueDates') == '1') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck8">Allow
                                                                    payment reversals to change service status</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>
                                                        Credit Card Charging Settings
                                                    </h4>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Process Days Before Due
                                                        </label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="ccprocessdaysbefore"
                                                                value="{{ Cfg::get('CCProcessDaysBefore') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of days before the due payment date you
                                                                want to
                                                                attempt to capture the payment
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Attempt Only
                                                            Once</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck9" name="ccattemptonlyonce"
                                                                    @if (Cfg::get('CCAttemptOnlyOnce') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck9">Tick this
                                                                    box to only attempt the payment automatically once and
                                                                    if it
                                                                    fails, don't attempt it again</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Retry Every Week For
                                                        </label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="ccretryeveryweekfor"
                                                                value="{{ Cfg::get('CCRetryEveryWeekFor') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the number of weeks to retry failed CC processing
                                                                attempts
                                                                for weekly
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            CC Expiry Notices Date
                                                        </label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control"
                                                                name="ccdaysendexpirynotices"
                                                                value="{{ Cfg::get('CCDaySendExpiryNotices') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-7">
                                                            <p class="m-0 pt-2">
                                                                Enter the day of the month that you want to send card expiry
                                                                notices for credit cards expiring at the end of the month
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2 col-form-label">Do
                                                            Not Remove CC
                                                            on Expiry</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck10" name="donotremovecconexpiry"
                                                                    @if (Cfg::get('CCDoNotRemoveOnExpiry') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck10">Tick
                                                                    this
                                                                    box to not remove credit card details when the expiry
                                                                    date
                                                                    passes</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>
                                                        Currency Auto Update Settings
                                                    </h4>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Exchange
                                                            Rates</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck11"
                                                                    name="currencyautoupdateexchangerates"
                                                                    @if (Cfg::get('CurrencyAutoUpdateExchangeRates') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck11">Tick
                                                                    this
                                                                    box to not remove credit card details when the expiry
                                                                    date
                                                                    passes</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Product
                                                            Prices</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck12"
                                                                    name="currencyautoupdateproductprices"
                                                                    @if (Cfg::get('CurrencyAutoUpdateProductPrices') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck12">Tick
                                                                    this box to update product prices using the current
                                                                    exchange
                                                                    rate daily</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>
                                                        Domain Reminder Settings
                                                    </h4>
                                                    <hr>
                                                    @php
                                                        $renewals = explode(",", Cfg::get("DomainRenewalNotices"), 5);
                                                        for ($i = count($renewals); $i < 5; $i++) {
                                                            $renewals[] = 0;
                                                        }
                                                        $languageStrings = array("firstrenewal", "secondrenewal", "thirdrenewal", "fourthrenewal", "fifthrenewal");
                                                        $renewalData = array();
                                                        foreach ($renewals as $count => $renewal) {
                                                            $selectData = "<select name=\"renewalWhen[" . $count . "]\" class=\"form-control w-25 select-inline\">" . "<option value=\"before\"" . (0 <= $renewal ? " selected=\"selected\"" : "") . ">" . $aInt->lang("", "before") . "</option>" . "<option value=\"after\"" . ($renewal < 0 ? " selected=\"selected\"" : "") . ">" . $aInt->lang("", "after") . "</option>" . "</select>";
                                                            $renewalData[] = array("name" => $languageStrings[$count], "fieldName" => "renewals[" . $count . "]", "value" => $renewal < 0 ? (int) ($renewal * -1) : (int) $renewal, "info" => sprintf($aInt->lang("automation", $languageStrings[$count] . "info"), $selectData));
                                                        }
                                                    @endphp
                                                    @foreach ($renewalData as $count => $renewal)
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">
                                                                {{$aInt->lang("automation", $renewal["name"])}}
                                                            </label>
                                                            <div class="col-sm-12 col-lg-10">
                                                                <div class="d-flex align-items-center">
                                                                    <input type="text" class="form-control" style="max-width: 50px;" name="{{$renewal["fieldName"]}}" value="{{$renewal["value"]}}">
                                                                    &nbsp;{!!$renewal["info"]!!} ({{$aInt->lang("automation", "todisable")}})
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div> --}}
                                            {{-- <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>
                                                        Domain Sync Settings
                                                    </h4>
                                                    <hr>
                                                    @php
                                                        $domainSyncEnabled = $domainSyncDate = $domainSyncNotify = "";
                                                        if (Cfg::getValue("DomainSyncEnabled")) {
                                                            $domainSyncEnabled = " checked=\"checked\"";
                                                        }
                                                        if (Cfg::getValue("DomainSyncNextDueDate")) {
                                                            $domainSyncDate = " checked=\"checked\"";
                                                        }
                                                        if (Cfg::getValue("DomainSyncNotifyOnly")) {
                                                            $domainSyncNotify = " checked=\"checked\"";
                                                        }
                                                    @endphp
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Domain Sync
                                                            Enabled</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck13" name="domainsyncenabled" {{$domainSyncEnabled}}>
                                                                <label class="custom-control-label" for="customCheck13">Tick
                                                                    this box to enable automated domain syncing with supported
                                                                    registrars via cron</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Sync Next Due
                                                            Date</label>
                                                        <div class="col-sm-12 col-lg-4 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck14" name="domainsyncnextduedate" {{$domainSyncDate}}>
                                                                <label class="custom-control-label" for="customCheck14">Enable -
                                                                    Number of Days to Set Due Date in Advance of Expiry:</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control" name="domainsyncnextduedatedays" value="{{(int)Cfg::getValue("DomainSyncNextDueDateDays")}}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Domain Sync Notify
                                                            Only</label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck15" name="domainsyncnotifyonly" {{$domainSyncNotify}}>
                                                                <label class="custom-control-label" for="customCheck15">Tick
                                                                    this box to not auto update any domain dates - just send
                                                                    email notification to admins</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Domain Status Sync
                                                            Frequency</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control" name="domain_status_sync_frequency" value="{{(int) Cfg::getValue("DomainStatusSyncFrequency")}}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-9">
                                                            <p class="m-0 pt-2">
                                                                (hours) How often the domain status sync will run as part of the
                                                                cron. 0 will use default value of 4 Hours
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Pending Transfer Sync
                                                            Frequency</label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control" name="domain_transfer_sync_frequency" value="{{(int) Cfg::getValue("DomainTransferStatusCheckFrequency")}}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-9">
                                                            <p class="m-0 pt-2">
                                                                (hours) How often the domain status sync will run as part of the
                                                                cron. 0 will use default value of 4 Hours
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> --}}
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>
                                                        Support Ticket Settings
                                                    </h4>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Close Inactive Tickets
                                                        </label>
                                                        <div class="col-sm-12 col-lg-1">
                                                            <input type="text" class="form-control" min="0"
                                                                name="closeinactivetickets"
                                                                value="{{ Cfg::getValue('CloseInactiveTickets') }}">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-9">
                                                            <p class="m-0 pt-2">
                                                                Time (in hours) of inactivity after which ticket is closed
                                                                (0 to
                                                                disable)
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Prune Ticket Attachments
                                                        </label>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <select name=""remove_inactive_attachments
                                                                id="" class="form-control">
                                                                @php
                                                                    foreach (range(0, 24) as $monthValue) {
                                                                        $selectedValue = (int) Cfg::getValue('PruneTicketAttachmentsMonths');
                                                                        $replacements = [];
                                                                        $description = 'disabled';
                                                                        $selected = '';
                                                                        if ($monthValue) {
                                                                            $description = 'someMonths';
                                                                            if ($monthValue === 1) {
                                                                                $description = 'aMonth';
                                                                            }
                                                                            $replacements = ['months' => $monthValue];
                                                                        }
                                                                        if ($selectedValue === $monthValue) {
                                                                            $selected = " selected=\"selected\"";
                                                                        }
                                                                        $description = LAng::get('admin.' . $description, $replacements);
                                                                        echo "<option value=\"" . $monthValue . "\"" . $selected . '>' . $description . '</option>';
                                                                    }
                                                                @endphp
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-8">
                                                            <p class="m-0 pt-2">
                                                                The length of time ticket attachments should be retained
                                                                after
                                                                the last activity within a closed ticket.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>
                                                        Data Retention Settings
                                                    </h4>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Automatically Delete Inactive Clients
                                                        </label>
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input"
                                                                        name="autodeleteinactiveclients"
                                                                        id="deleteInactive1" value="0"
                                                                        {{ !Cfg::getValue('DRAutoDeleteInactiveClients') ? ' checked' : '' }}>
                                                                    Never
                                                                </label>
                                                            </div>
                                                            <div class="d-flex">
                                                                <div class="form-check">
                                                                    <label class="form-check-label">
                                                                        <input type="radio" class="form-check-input"
                                                                            name="autodeleteinactiveclients"
                                                                            id="deleteInactive2" value="1"
                                                                            {{ Cfg::getValue('DRAutoDeleteInactiveClients') ? ' checked' : '' }}>
                                                                        After no invoice or transaction activity has
                                                                        occurred
                                                                        for
                                                                        the
                                                                        following number of months:
                                                                    </label>
                                                                </div>
                                                                <input type="text" class="form-control ml-2 mb-2"
                                                                    name="autodeleteinactiveclientsmonths"
                                                                    value="{{ (int) Cfg::getValue('DRAutoDeleteInactiveClientsMonths') }}"
                                                                    style="width: 4em;">
                                                            </div>
                                                            <div class="alert alert-primary" role="alert">
                                                                <i class="fa fa-exclamation-triangle mr-2"
                                                                    aria-hidden="true"></i>
                                                                Warning:<em> This will irrevocably erase all customer
                                                                    data.</em><br>
                                                                An inactive client is defined as a client with no active
                                                                products, services or addons.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h4>
                                                        Miscellaneous
                                                    </h4>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Cancellation Requests
                                                        </label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck16" name="autocancellationrequests"
                                                                    @if (Cfg::get('AutoCancellationRequests') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck16">Tick
                                                                    this box to automatically terminate accounts with
                                                                    cancellation requests when due</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Update Usage Statistics
                                                        </label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="customCheck17" name="updatestatusauto"
                                                                    @if (Cfg::get('UpdateStatsAuto') == 'on') checked @endif>
                                                                <label class="custom-control-label"
                                                                    for="customCheck17">Tick
                                                                    this box to update automatically when the cron
                                                                    runs</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Client Status Update
                                                        </label>
                                                        <div class="col-sm-12 col-lg-10 pt-2">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input"
                                                                        name="autoclientstatuschange"
                                                                        id="clientStatusUpdate1" value="1"
                                                                        @if (Cfg::get('AutoClientStatusChange') == '1') checked @endif>
                                                                    Disabled - never auto change client status
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input"
                                                                        name="autoclientstatuschange"
                                                                        id="clientStatusUpdate2" value="2"
                                                                        @if (Cfg::get('AutoClientStatusChange') == '2') checked @endif>
                                                                    Change client status based on active/inactive products
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input"
                                                                        name="autoclientstatuschange"
                                                                        id="clientStatusUpdate2" value="3"
                                                                        @if (Cfg::get('AutoClientStatusChange') == '3') checked @endif>
                                                                    Change client status based on active/inactive products
                                                                    and
                                                                    not logged in for longer than 3 months
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card p-3">
                                            <div class="row">
                                                <div class="col-lg-12 d-flex justify-content-center">
                                                    <button type="submit" class="btn btn-success px-3 mx-1">Save
                                                        Changes</button>
                                                    <button type="reset" class="btn btn-light px-3 mx-1">Cancel
                                                        Changes</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End MAIN CARD -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        {!! $jscode !!}
    </script>
@endsection
