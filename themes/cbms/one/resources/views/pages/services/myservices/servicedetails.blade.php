@extends('layouts.clientbase')

@section('tab-title')
    Services Details
@endsection

@section('styles')
    <style>
        .product-status {
            margin: 0 0 20px 0;
            padding: 0;
            border-radius: 10px;
        }

        .product-status-suspended {
            background-color: #0768b8;
        }

        .product-status-active {
            background-color: green;
        }

        .product-status-pending {
            background-color: orange;
        }

        .product-status-cancelled,
        .product-status-terminated {
            background-color: #666;
        }

        div.product-details div.product-icon {
            background-color: #fff;
        }

        div.product-details div.product-icon {
            margin: 0;
            padding: 0;
            background-color: #efefef;
            border-radius: 10px;
            padding: 30px;
            font-size: 60px;
            line-height: 1em;
        }

        .product-status-text {
            padding: 5px;
            color: #fff;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
@endsection

@section('content')
    <div class="page-content bg-white" id="service-detailsx">
        <div class="container-fluid">
            @if ($modulecustombuttonresult)
                @if ($modulecustombuttonresult == 'success')
                    @include('includes.alert', [
                        'type' => 'success',
                        'msg' => Lang::get('client.moduleactionsuccess'),
                        'textcenter' => true,
                        'idname' => 'alertModuleCustomButtonSuccess',
                    ])
                @else
                    @include('includes.alert', [
                        'type' => 'error',
                        'msg' => Lang::get('client.moduleactionfailed') . ' ' . $modulecustombuttonresult,
                        'textcenter' => true,
                        'idname' => 'alertModuleCustomButtonFailed',
                    ])
                @endif
            @endif

            @if ($pendingcancellation)
                <div class="alert alert-danger" role="alert">
                    {{ Lang::get('client.cancellationrequestedexplanation') }}
                </div>
            @endif

            @if ($unpaidInvoice)
                <div class="alert alert-{{ $unpaidInvoiceOverdue ? 'danger' : 'warning' }}"
                    id="alert{{ $unpaidInvoiceOverdue ? 'Overdue' : 'Unpaid' }}Invoice">
                    <div class="pull-right">
                        <a href="{{ url('viewinvoice.php?id=' . $unpaidInvoice) }}" class="btn btn-sm btn-secondary">
                            {{ Lang::get('client.payInvoice') }}
                        </a>
                    </div>
                    {{ $unpaidInvoiceMessage }}
                </div>
            @endif

            <div class="tab-content margin-bottom">
                <div class="tab-pane fade in active show" id="tabOverview">
                    @if ($tplOverviewTabOutput)
                        {!! $tplOverviewTabOutput !!}
                    @else
                        <div class="product-details clearfix">
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="product-status product-status-{{ strtolower($rawstatus) }}">
                                        <div class="product-icon text-center">
                                            <span class="fa-stack fa-lg">
                                                <i class="fas fa-circle fa-stack-2x"></i>
                                                @php
                                                    if ($type == 'hostingaccount' || $type == 'reselleraccount') {
                                                        $fa = 'hdd';
                                                    } elseif ($type == 'server') {
                                                        $fa = 'database';
                                                    } else {
                                                        $fa = 'archive';
                                                    }

                                                @endphp
                                                <i class="fas fa-{{ $fa }} fa-stack-1x fa-inverse"></i>
                                            </span>
                                            {{-- nama product nya: WireBusiness Fiber 50 --}}
                                            <h3>{{ $product }}</h3>
                                            <h4>{{ $groupname }}</h4>
                                        </div>
                                        <div class="product-status-text">
                                            {{ $status }}
                                        </div>
                                    </div>

                                    {{-- cancel button dan upgrade button --}}
                                    @if ($showcancelbutton || $packagesupgrade)
                                        <div class="row px-3 mb-3">
                                            @if ($packagesupgrade)
                                                <div class="col-xs-{{ $showcancelbutton ? '6' : '12' }}">
                                                    <a href="{{ route('pages.services.upgrade') }}?type=package&amp;id={{ $id }}"
                                                        class="btn btn-block btn-success">{{ Lang::get('client.upgrade') }}</a>
                                                </div>
                                            @endif
                                            @if ($showcancelbutton)
                                                <div class="col-xs-{{ $packagesupgrade ? '6' : '12' }}">
                                                    <a href="clientarea.php?action=cancel&amp;id={{ $id }}"
                                                        class="btn btn-block btn-danger {{ $pendingcancellation ? 'disabled' : '' }}">{{ $pendingcancellation ? Lang::get('client.cancellationrequested') : Lang::get('client.clientareacancelrequestbutton') }}</a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                </div>
                                <div class="col-md-6 text-center">

                                    {{-- <h4 class="mb-0">{{ Lang::get('client.clientareahostingregdate') }}</h4> --}}
                                    <h4 class="mb-0">Registration Date</h4>
                                    <p>{{ $regdate }}</p>

                                    {{-- first payment amount --}}
                                    @if ($firstpaymentamount != $recurringamount)
                                        <h4 class="mb-0">{{ Lang::get('client.firstpaymentamount') }}</h4>
                                        <p>{{ $firstpaymentamount }}</p>
                                    @endif

                                    @if ($billingcycle != Lang::get('client.orderpaymenttermonetime') && $billingcycle != Lang::get('client.orderfree'))
                                        <h4 class="mb-0">{{ Lang::get('client.recurringamount') }}</h4>
                                        <p>{{ $recurringamount }}</p>
                                    @endif

                                    <h4 class="mb-0">{{ Lang::get('client.orderbillingcycle') }}</h4>
                                    <p>{{ $billingcycle }}</p>

                                    <h4 class="mb-0">{{ Lang::get('client.clientareahostingnextduedate') }}</h4>
                                    <p>{{ $nextduedate }}</p>

                                    <h4 class="mb-0">{{ Lang::get('client.orderpaymentmethod') }}</h4>
                                    <p>{{ $paymentmethod }}</p>

                                    @if ($suspendreason)
                                        <h4 class="mb-0">{{ Lang::get('client.suspendreason') }}</h4>
                                        <p>{{ $suspendreason }}</p>
                                    @endif

                                </div>
                            </div>
                        </div>
                        @foreach ($hookOutput as $output)
                            <div>
                                {!! $output !!}
                            </div>
                        @endforeach

                        {{-- tab domain, manage, config, additional info, resource usage --}}
                        @if ($domain || $moduleclientarea || $configurableoptions || $customfields || $lastupdate)
                            <div class="row clearfix px-3 pb-3">
                                <div class="col-xs-12">
                                    <ul class="nav nav-tabs nav-tabs-overflow" id="myTab" role="tablist">
                                        @if ($domain)
                                            <li class="nav-item">
                                                <a class="nav-link active" href="#domain" data-toggle="tab">
                                                    <i class="fas fa-globe fa-fw"></i>
                                                    @if ($type == 'server')
                                                        {{ Lang::get('client.sslserverinfo') }}
                                                    @elseif (($type == 'hostingaccount' || $type == 'reselleraccount') && $serverdata)
                                                        {{ Lang::get('client.hostingInfo') }}
                                                    @else
                                                        {{ Lang::get('client.clientareahostingdomain') }}
                                                    @endif
                                                </a>
                                            </li>
                                        @elseif ($moduleclientarea)
                                            <li class="nav-item">
                                                <a class="nav-link active" href="#manage" data-toggle="tab"><i
                                                        class="fas fa-globe fa-fw"></i>
                                                    {{ Lang::get('client.manage') }}</a>
                                            </li>
                                        @endif
                                        @if ($configurableoptions)
                                            <li class="nav-item">
                                                <a class="nav-link {{ !$domain && !$moduleclientarea ? 'active' : '' }}"
                                                    href="#configoptions" data-toggle="tab"><i
                                                        class="fas fa-cubes fa-fw"></i>
                                                    {{ Lang::get('client.orderconfigpackage') }}</a>
                                            </li>
                                        @endif
                                        @if ($customfields)
                                            <li class="nav-item">
                                                <a class="nav-link {{ !$domain && !$moduleclientarea && !$configurableoptions ? 'active' : '' }}"
                                                    href="#additionalinfo" data-toggle="tab"><i
                                                        class="fas fa-info fa-fw"></i>
                                                    {{-- {{ Lang::get('client.additionalInfo') }} --}}
                                                    Additional Information
                                                </a>
                                            </li>
                                        @endif
                                        @if ($lastupdate)
                                            <li class="nav-item">
                                                <a class="nav-link {{ !$domain && !$moduleclientarea && !$configurableoptions ? 'active' : '' }}"
                                                    href="#resourceusage" data-toggle="tab"><i
                                                        class="fas fa-inbox fa-fw"></i>
                                                    {{ Lang::get('client.resourceUsage') }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            {{-- tab domain --}}
                            <div class="tab-content product-details-tab-container pb-4">
                                @if ($domain)
                                    <div class="tab-pane fade show in active text-center" id="domain">
                                        @if ($type == 'server')
                                            <div class="row">
                                                <div class="col-sm-5 text-right">
                                                    <strong>{{ Lang::get('client.serverhostname') }}</strong>
                                                </div>
                                                <div class="col-sm-7 text-left">
                                                    {{ $domain }}
                                                </div>
                                            </div>
                                            @if ($dedicatedip)
                                                <div class="row">
                                                    <div class="col-sm-5 text-right">
                                                        <strong>{{ Lang::get('client.primaryIP') }}</strong>
                                                    </div>
                                                    <div class="col-sm-7 text-left">
                                                        {{ $dedicatedip }}
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($assignedips)
                                                <div class="row">
                                                    <div class="col-sm-5 text-right">
                                                        <strong>{{ Lang::get('client.assignedIPs') }}</strong>
                                                    </div>
                                                    <div class="col-sm-7 text-left">
                                                        {!! nl2br($assignedips) !!}
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($ns1 || $ns2)
                                                <div class="row">
                                                    <div class="col-sm-5 text-right">
                                                        <strong>{{ Lang::get('client.domainnameservers') }}</strong>
                                                    </div>
                                                    <div class="col-sm-7 text-left">
                                                        {{ $ns1 }}<br />{{ $ns2 }}
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            @if ($domain)
                                                <div class="row">
                                                    <div class="col-sm-5 text-right">
                                                        {{-- <strong>{{ Lang::get('client.orderdomain') }}</strong> --}}
                                                        <strong>halooo</strong>
                                                    </div>
                                                    <div class="col-sm-7 text-left">
                                                        {{ $domain }}
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($username)
                                                <div class="row">
                                                    <div class="col-sm-5 text-right">
                                                        <strong>{{ Lang::get('client.serverusername') }}</strong>
                                                    </div>
                                                    <div class="col-sm-7 text-left">
                                                        {{ $username }}
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($serverdata)
                                                <div class="row">
                                                    <div class="col-sm-5 text-right">
                                                        <strong>{{ Lang::get('client.servername') }}</strong>
                                                    </div>
                                                    <div class="col-sm-7 text-left">
                                                        {{ $serverdata['hostname'] }}
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-5 text-right">
                                                        {{-- <strong>{{ Lang::get('client.domainregisternsip') }}</strong> --}}
                                                        <strong>IP Address</strong>
                                                    </div>
                                                    <div class="col-sm-7 text-left">
                                                        {{ $serverdata['ipaddress'] }}
                                                    </div>
                                                </div>
                                                @if (
                                                    $serverdata['nameserver1'] ||
                                                        $serverdata['nameserver2'] ||
                                                        $serverdata['nameserver3'] ||
                                                        $serverdata['nameserver4'] ||
                                                        $serverdata['nameserver5']
                                                )
                                                    <div class="row">
                                                        <div class="col-sm-5 text-right">
                                                            <strong>{{ Lang::get('client.domainnameservers') }}</strong>
                                                        </div>
                                                        <div class="col-sm-7 text-left">
                                                            @if ($serverdata['nameserver1'])
                                                                {{ $serverdata['nameserver1'] }}
                                                                ({{ $serverdata['nameserver1ip'] }})<br />
                                                            @endif
                                                            @if ($serverdata['nameserver2'])
                                                                {{ $serverdata['nameserver2'] }}
                                                                ({{ $serverdata['nameserver2ip'] }})<br />
                                                            @endif
                                                            @if ($serverdata['nameserver3'])
                                                                {{ $serverdata['nameserver3'] }}
                                                                ({{ $serverdata['nameserver3ip'] }})<br />
                                                            @endif
                                                            @if ($serverdata['nameserver4'])
                                                                {{ $serverdata['nameserver4'] }}
                                                                ({{ $serverdata['nameserver4ip'] }})<br />
                                                            @endif
                                                            @if ($serverdata['nameserver5'])
                                                                {{ $serverdata['nameserver5'] }}
                                                                ({{ $serverdata['nameserver5ip'] }})<br />
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                            @if ($domain && $sslStatus)
                                                <div class="row">
                                                    <div class="col-sm-5 text-right">
                                                        <strong>{{ Lang::get('client.sslStatesslStatus') }}</strong>
                                                    </div>
                                                    <div
                                                        class="col-sm-7 text-left {{ $sslStatus->isInactive() ? 'ssl-inactive' : '' }}">
                                                        <img src="{{ $sslStatus->getImagePath() }}" width="12">
                                                        {{ $sslStatus->getStatusDisplayLabel() }}
                                                    </div>
                                                </div>
                                                @if ($sslStatus->isActive())
                                                    <div class="row">
                                                        <div class="col-sm-5 text-right">
                                                            <strong>{{ Lang::get('client.sslStatestartDate') }}</strong>
                                                        </div>
                                                        <div class="col-sm-7 text-left">
                                                            {{ $sslStatus->startDate->toClientDateFormat() }}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-5 text-right">
                                                            <strong>{{ Lang::get('client.sslStateexpiryDate') }}</strong>
                                                        </div>
                                                        <div class="col-sm-7 text-left">
                                                            {{ $sslStatus->expiryDate->toClientDateFormat() }}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-5 text-right">
                                                            <strong>{{ Lang::get('client.sslStateissuerName') }}</strong>
                                                        </div>
                                                        <div class="col-sm-7 text-left">
                                                            {{ $sslStatus->issuerName }}
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                            <br>
                                            <p>
                                                <a href="http://{{ $domain }}" class="btn btn-secondary"
                                                    target="_blank">{{ Lang::get('client.visitwebsite') }}</a>
                                                @if ($domainId)
                                                    <a href="clientarea.php?action=domaindetails&id={{ $domainId }}"
                                                        class="btn btn-secondary"
                                                        target="_blank">{{ Lang::get('client.managedomain') }}</a>
                                                @endif
                                                <input type="button"
                                                    onclick="popupWindow('whois.php?domain={{ $domain }}','whois',650,420);return false;"
                                                    value="{{ Lang::get('client.whoisinfo') }}"
                                                    class="btn btn-secondary" />
                                            </p>
                                        @endif
                                        @if ($moduleclientarea)
                                            <div class="text-center module-client-area">
                                                {!! $moduleclientarea !!}
                                            </div>
                                        @endif
                                    </div>
                                    @if ($sslStatus)
                                        <div class="tab-pane fade text-center" id="ssl-info">
                                            @if ($sslStatus->isActive())
                                                <div class="alert alert-success" role="alert">
                                                    {{ Lang::get('client.sslStatesslActive', ['expiry' => $sslStatus->expiryDate ? $sslStatus->expiryDate->toClientDateFormat() : '']) }}
                                                </div>
                                            @else
                                                <div class="alert alert-warning ssl-required" role="alert">
                                                    {{ Lang::get('client.sslRequired') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @elseif ($moduleclientarea)
                                    <div class="tab-pane fade {{ !$domain ? 'show in active' : '' }} text-center"
                                        id="manage">
                                        @if ($moduleclientarea)
                                            <div class="text-center module-client-area">
                                                {!! $moduleclientarea !!}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @if ($configurableoptions)
                                    <div class="tab-pane fade {{ !$domain && !$moduleclientarea ? 'show in active' : '' }} text-center"
                                        id="configoptions">
                                        @foreach ($configurableoptions as $configoption)
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <strong>{{ $configoption['optionname'] }}</strong>
                                                </div>
                                                <div class="col-sm-7 text-left">
                                                    @if ($configoption['optiontype'] == 3)
                                                        @if ($configoption['selectedqty'])
                                                            {{ Lang::get('client.yes') }}
                                                        @else
                                                            {{ Lang::get('client.no') }}
                                                        @endif
                                                    @elseif ($configoption['optiontype'] == 4)
                                                        {{ $configoption['selectedqty'] }} x
                                                        {{ $configoption['selectedoption'] }}
                                                    @else
                                                        {{ $configoption['selectedoption'] }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($customfields)
                                    <div class="tab-pane fade {{ !$domain && !$moduleclientarea && !$configurableoptions ? 'show in active' : '' }} text-center"
                                        id="additionalinfo">
                                        @foreach ($customfields as $field)
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <strong>{{ $field['name'] }}</strong>
                                                </div>
                                                <div class="col-sm-7 text-left">
                                                    {!! $field['value'] !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($lastupdate)
                                    <div class="tab-pane fade text-center" id="resourceusage">
                                        <div class="col-sm-10 col-sm-offset-1">
                                            <div class="col-sm-6">
                                                <h4>{{ Lang::get('client.diskSpace') }}</h4>
                                                <input type="text" value="{{ substr($diskpercent, 0, -1) }}"
                                                    class="dial-usage" data-width="100" data-height="100" data-min="0"
                                                    data-readOnly="true" />
                                                <p>{{ $diskusage }}MB / {{ $disklimit }}MB</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <h4>{{ Lang::get('client.bandwidth') }}</h4>
                                                <input type="text" value="{{ substr($bwpercent, 0, -1) }}"
                                                    class="dial-usage" data-width="100" data-height="100" data-min="0"
                                                    data-readOnly="true" />
                                                <p>{{ $bwusage }}MB / {{ $bwlimit }}MB</p>
                                            </div>
                                        </div>
                                        <div class="clearfix">
                                        </div>
                                        <p class="text-muted">{{ Lang::get('client.clientarealastupdated') }}:
                                            {{ $lastupdate }}</p>

                                        <script src="{{ Theme::asset('js/jquery.knob.js') }}"></script>
                                        <script type="text/javascript">
                                            $(function() {
                                                    ldelim
                                                }
                                                $(".dial-usage").knob({
                                                        ldelim
                                                    }
                                                    'format': function(v) {
                                                        ldelim
                                                    }
                                                    alert(v);
                                                    {
                                                        rdelim
                                                    } {
                                                        rdelim
                                                    });
                                                {
                                                    rdelim
                                                });
                                        </script>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <script src="{{ Theme::asset('js/bootstrap-tabdrop.js') }}"></script>
                        <script type="text/javascript">
                            // $('.nav-tabs-overflow').tabdrop();
                        </script>
                    @endif
                </div>
                {{-- tab download --}}
                <div class="tab-pane fade in" id="tabDownloads">

                    <h3>{{ Lang::get('client.downloadstitle') }}</h3>

                    @include('includes.alert', [
                        'type' => 'info',
                        'msg' => Lang::get('client.clientAreaProductDownloadsAvailable'),
                        'textcenter' => true,
                    ])

                    <div class="row">
                        @foreach ($downloads as $download)
                            <div class="col-xs-10 col-xs-offset-1">
                                <h4>{{ $download['title'] }}</h4>
                                <p>
                                    {{ $download['description'] }}
                                </p>
                                <p>
                                    <a href="{{ $download['link'] }}" class="btn btn-secondary"><i
                                            class="fas fa-download"></i> {{ Lang::get('client.downloadname') }}</a>
                                </p>
                            </div>
                        @endforeach
                    </div>

                </div>
                {{-- tab addons --}}
                <div class="tab-pane fade in" id="tabAddons">

                    <h3>{{ Lang::get('client.clientareahostingaddons') }}</h3>

                    @if ($addonsavailable)
                        @include('includes.alert', [
                            'type' => 'info',
                            'msg' => Lang::get('client.clientAreaProductAddonsAvailable'),
                            'textcenter' => true,
                        ])
                    @endif

                    <div class="row">
                        @foreach ($addons as $addon)
                            <div class="col-xs-10 col-xs-offset-1">
                                <div class="panel panel-default panel-accent-blue">
                                    <div class="panel-heading">
                                        {{ $addon['name'] }}
                                        <div class="pull-right status-{{ strtolower($addon['rawstatus']) }}">
                                            {{ $addon['status'] }}</div>
                                    </div>
                                    <div class="row panel-body">
                                        <div class="col-md-6">
                                            <p>
                                                {{ $addon['pricing'] }}
                                            </p>
                                            <p>
                                                {{ Lang::get('client.registered') }}: {{ $addon['regdate'] }}
                                            </p>
                                            <p>
                                                {{ Lang::get('client.clientareahostingnextduedate') }}:
                                                {{ $addon['nextduedate'] }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        {{ $addon['managementActions'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
                {{-- other tab --}}
                {{-- other tab --}}
            </div>

        </div> <!-- container-fluid -->
    </div>
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endsection
