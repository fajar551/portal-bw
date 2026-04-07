@extends('layouts.clientbase')

@section('title')
    Domain Details
@endsection

@section('page-title')
    Domain Details
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <a
                                href="{{ route('pages.domain.mydomains.index') }}"> / My Domains</a> <span class="text-muted">
                                / Domain Details</span></h6>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    @if ($registrarcustombuttonresult == 'success')
                        @include('includes.alert', [
                            'type' => 'success',
                            'msg' => Lang::get('client.moduleactionsuccess'),
                            'textcenter' => true,
                        ])
                    @elseif ($registrarcustombuttonresult)
                        @include('includes.alert', [
                            'type' => 'error',
                            'msg' => Lang::get('client.moduleactionfailed') . ' ' . $registrarcustombuttonresult,
                            'textcenter' => true,
                        ])
                    @endif

                    @if ($unpaidInvoice)
                        <div class="alert alert-{{ $unpaidInvoiceOverdue ? 'danger' : 'warning' }}"
                            id="alert{{ $unpaidInvoiceOverdue ? 'Overdue' : 'Unpaid' }}Invoice">
                            <div class="text-right">
                                <a href="{{ route('pages.domain.mydomains.viewinvoiceweb', $unpaidInvoice) }}?id={{ $unpaidInvoice }}"
                                    class="btn btn-xs btn-secondary">
                                    {{ Lang::get('client.payInvoice') }}
                                </a>
                            </div>
                            {!! $unpaidInvoiceMessage !!}
                        </div>
                    @endif

                    <div class="tab-content margin-bottom">
                        <div class="tab-pane fade in show active" id="tabOverview">

                            @if (isset($alerts))
                                @foreach ($alerts as $alert)
                                    @include('includes.alert', [
                                        'type' => $alert['type'],
                                        'msg' => "<strong>{$alert['title']}</strong><br>{$alert['description']}",
                                        'textcenter' => true,
                                    ])
                                @endforeach
                            @endif

                            @if ($systemStatus != 'Active')
                                <div class="alert alert-warning text-center" role="alert">
                                    {{ Lang::get('client.domainCannotBeManagedUnlessActive') }}
                                </div>
                            @endif

                            <h3>{{ Lang::get('client.overview') }}</h3>

                            @if (isset($lockstatus) && $lockstatus == 'unlocked')
                                @php
                                    $domainUnlockedMsg =
                                        '<strong>' .
                                        Lang::get('client.domaincurrentlyunlocked') .
                                        '</strong><br />' .
                                        Lang::get('client.domaincurrentlyunlockedexp');
                                @endphp
                                @include('includes.alert', [
                                    'type' => 'error',
                                    'msg' => $domainUnlockedMsg,
                                    'textcenter' => true,
                                ])
                            @endif
                            {{-- {if $lockstatus eq "unlocked"}
                           {capture name="domainUnlockedMsg"}<strong>{{Lang::get('client.domaincurrentlyunlocked')}}</strong><br />{{Lang::get('client.domaincurrentlyunlockedexp')}}{/capture}
                           {include file="$template/includes/alert.tpl" type="error" msg=$smarty.capture.domainUnlockedMsg}
                     {/if} --}}

                            <div class="row">
                                <div class="col-sm-offset-1 col-sm-5">
                                    <h4><strong>{{ Lang::get('client.clientareahostingdomain') }}:</strong></h4> <a
                                        href="http://{{ $domain }}" target="_blank">{{ $domain }}</a>
                                </div>
                                <div class="col-sm-5">
                                    <h4><strong>{{ Lang::get('client.firstpaymentamount') }}:</strong></h4>
                                    <span>{{ $firstpaymentamount }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-offset-1 col-sm-5">
                                    <h4><strong>{{ Lang::get('client.clientareahostingregdate') }}:</strong></h4>
                                    <span>{{ $registrationdate }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <h4><strong>{{ Lang::get('client.recurringamount') }}:</strong></h4>
                                    {{ $recurringamount }} {{ Lang::get('client.every') }} {{ $registrationperiod }}
                                    {{ Lang::get('client.orderyears') }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-offset-1 col-sm-5">
                                    <h4><strong>{{ Lang::get('client.clientareahostingnextduedate') }}:</strong></h4>
                                    {{ $nextduedate }}
                                </div>
                                <div class="col-sm-6">
                                    <h4><strong>{{ Lang::get('client.orderpaymentmethod') }}:</strong></h4>
                                    {{ $paymentmethod }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-offset-1 col-sm-5">
                                    <h4><strong>{{ Lang::get('client.clientareastatus') }}:</strong></h4>
                                    {{ $status }}
                                </div>
                            </div>
                            @if ($sslStatus)
                                <div class="row">
                                    <div class="col-sm-offset-1 col-sm-5{{ $sslStatus->isInactive() ? 'ssl-inactive' : '' }}">
                                        <h4><strong>{{ Lang::get('client.sslStatesslStatus') }}</strong></h4> <img
                                            src="{{ $sslStatus->getImagePath() }}" width="16">
                                        {{ $sslStatus->getStatusDisplayLabel() }}
                                    </div>
                                    @if ($sslStatus->isActive())
                                        <div class="col-sm-6">
                                            <h4><strong>{{ Lang::get('client.sslStatestartDate') }}</strong></h4>
                                            {{ $sslStatus->startDate ? $sslStatus->startDate->toClientDateFormat() : '' }}
                                        </div>
                                    @endif
                                </div>
                                @if ($sslStatus->isActive())
                                    <div class="row">
                                        <div class="col-sm-offset-1 col-sm-5">
                                            <h4><strong>{{ Lang::get('client.sslStateissuerName') }}</strong></h4>
                                            {{ $sslStatus->issuerName }}
                                        </div>
                                        <div class="col-sm-6">
                                            <h4><strong>{{ Lang::get('client.sslStateexpiryDate') }}</strong></h4>
                                            {{ $sslStatus->expiryDate ? $sslStatus->expiryDate->toClientDateFormat() : '' }}
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if (isset($registrarclientarea))
                                <div class="moduleoutput">
                                    {{-- {$registrarclientarea|replace:'modulebutton':'btn'} --}}
                                    {{-- {!!str_replace("modulebutton", "btn", $registrarclientarea)!!} --}}
                                    {!! $registrarclientarea !!}
                                </div>
                            @endif

                            @foreach ($hookOutput as $output)
                                <div>
                                    {!! $output !!}
                                </div>
                            @endforeach

                            <br />

                            @if (
                                $canDomainBeManaged &&
                                    ($managementoptions['nameservers'] ||
                                        $managementoptions['contacts'] ||
                                        $managementoptions['locking'] ||
                                        $renew))
                                {{-- {* No reason to show this section if nothing can be done here! *} --}}

                                <h4>{{ Lang::get('client.doToday') }}</h4>

                                <ul>
                                    @if ($systemStatus == 'Active' && $managementoptions['nameservers'])
                                        <li>
                                            <a class="tabControlLink" data-toggle="tab" href="#tabNameservers">
                                                {{ Lang::get('client.changeDomainNS') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if ($systemStatus == 'Active' && $managementoptions['contacts'])
                                        <li>
                                            <a
                                                href="{{ route('pages.domain.mydomains.domaindetails2') }}?action=domaincontacts&domainid={{ $domainid }}">
                                                {{ Lang::get('client.updateWhoisContact') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if ($systemStatus == 'Active' && $managementoptions['locking'])
                                        <li>
                                            <a class="tabControlLink" data-toggle="tab" href="#tabReglock">
                                                {{ Lang::get('client.changeRegLock') }}
                                            </a>
                                        </li>
                                    @endif
                                    {{-- HOTFIX: this --}}
                                    @if ($renew)
                                        <li>
                                            <a href="">
                                                {{ Lang::get('client.renewYourDomain') }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            @endif

                        </div>
                        <div class="tab-pane fade" id="tabAutorenew">

                            <h3>{{ Lang::get('client.domainsautorenew') }}</h3>

                            @if ($changeAutoRenewStatusSuccessful)
                                @include('includes.alert', [
                                    'type' => 'success',
                                    'msg' => Lang::get('client.changessavedsuccessfully'),
                                    'textcenter' => true,
                                ])
                            @endif

                            @include('includes.alert', [
                                'type' => 'info',
                                'msg' => Lang::get('client.domainrenewexp'),
                            ])

                            <br />

                            <h2 class="text-center">{{ Lang::get('client.domainautorenewstatus') }}: <span
                                    class="label label-{{ $autorenew ? 'success' : 'danger' }}">
                                    @if ($autorenew)
                                        {{ Lang::get('client.domainsautorenewenabled') }}
                                    @else
                                        {{ Lang::get('client.domainsautorenewdisabled') }}
                                    @endif
                                </span></h2>

                            <br />
                            <br />

                            <form method="post" action="?action=domaindetails#tabAutorenew">
                                @csrf
                                <input type="hidden" name="id" value="{{ $domainid }}">
                                <input type="hidden" name="sub" value="autorenew" />
                                @if ($autorenew)
                                    <input type="hidden" name="autorenew" value="disable">
                                    <p class="text-center">
                                        <input type="submit" class="btn btn-lg btn-danger"
                                            value="{{ Lang::get('client.domainsautorenewdisable') }}" />
                                    </p>
                                @else
                                    <input type="hidden" name="autorenew" value="enable">
                                    <p class="text-center">
                                        <input type="submit" class="btn btn-lg btn-success"
                                            value="{{ Lang::get('client.domainsautorenewenable') }}" />
                                    </p>
                                @endif
                            </form>

                        </div>
                        <div class="tab-pane fade" id="tabNameservers">

                            <h3>{{ Lang::get('client.domainnameservers') }}</h3>

                            @if (isset($nameservererror) && $nameservererror)
                                @include('includes.alert', [
                                    'type' => 'error',
                                    'msg' => $nameservererror,
                                    'textcenter' => true,
                                ])
                            @endif
                            @if ($subaction == 'savens')
                                @if ($updatesuccess)
                                    @include('includes.alert', [
                                        'type' => 'success',
                                        'msg' => Lang::get('client.changessavedsuccessfully'),
                                        'textcenter' => true,
                                    ])
                                @elseif ($error)
                                    @include('includes.alert', [
                                        'type' => 'error',
                                        'msg' => $error,
                                        'textcenter' => true,
                                    ])
                                @endif
                            @endif

                            @include('includes.alert', [
                                'type' => 'info',
                                'msg' => Lang::get('client.domainnsexp'),
                            ])

                            <form class="form-horizontal" role="form" method="post"
                                action="?action=domaindetails#tabNameservers">
                                @csrf
                                <input type="hidden" name="id" value="{{ $domainid }}" />
                                <input type="hidden" name="sub" value="savens" />
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="nschoice" value="default"
                                            onclick="disableFields('domnsinputs',true)"{{ isset($defaultns) && $defaultns ? 'checked' : '' }} />
                                        {{ Lang::get('client.nschoicedefault') }}
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="nschoice" value="custom"
                                            onclick="disableFields('domnsinputs',false)"{{ isset($defaultns) && !$defaultns ? 'checked' : '' }} />
                                        {{ Lang::get('client.nschoicecustom') }}
                                    </label>
                                </div>
                                <br />
                                @for ($num = 1; $num < 5; $num++)
                                    <div class="form-group">
                                        <label for="inputNs{{ $num }}"
                                            class="col-sm-4 control-label">{{ Lang::get('client.clientareanameserver') }}
                                            {{ $num }}</label>
                                        <div class="col-sm-7">
                                            <input type="text" name="ns{{ $num }}"
                                                class="form-control domnsinputs" id="inputNs{{ $num }}"
                                                value="{{ isset($nameservers) && array_key_exists($num, $nameservers) ? $nameservers[$num]['value'] : '' }}" />
                                        </div>
                                    </div>
                                @endfor
                                <p class="text-center">
                                    <input type="submit" class="btn btn-primary"
                                        value="{{ Lang::get('client.changenameservers') }}" />
                                </p>
                            </form>

                        </div>
                        <div class="tab-pane fade" id="tabReglock">

                            <h3>{{ Lang::get('client.domainregistrarlock') }}</h3>

                            @if ($subaction == 'savereglock')
                                @if ($updatesuccess)
                                    @include('includes.alert', [
                                        'type' => 'success',
                                        'msg' => Lang::get('client.changessavedsuccessfully'),
                                        'textcenter' => true,
                                    ])
                                @elseif ($error)
                                    @include('includes.alert', [
                                        'type' => 'error',
                                        'msg' => $error,
                                        'textcenter' => true,
                                    ])
                                @endif
                            @endif

                            @include('includes.alert', [
                                'type' => 'info',
                                'msg' => Lang::get('client.domainlockingexp'),
                            ])

                            <br />

                            <h2 class="text-center">{{ Lang::get('client.domainreglockstatus') }}: <span
                                    class="label label-{{ $lockstatus == 'locked' ? 'success' : 'danger' }}">
                                    @if ($lockstatus == 'locked')
                                        {{ Lang::get('client.domainsautorenewenabled') }}
                                    @else
                                        {{ Lang::get('client.domainsautorenewdisabled') }}
                                    @endif
                                </span></h2>

                            <br />
                            <br />

                            <form method="post" action="?action=domaindetails#tabReglock">
                                @csrf
                                <input type="hidden" name="id" value="{{ $domainid }}">
                                <input type="hidden" name="sub" value="savereglock" />
                                @if ($lockstatus == 'locked')
                                    <p class="text-center">
                                        <input type="submit" class="btn btn-lg btn-danger"
                                            value="{{ Lang::get('client.domainreglockdisable') }}" />
                                    </p>
                                @else
                                    <p class="text-center">
                                        <input type="submit" class="btn btn-lg btn-success" name="reglock"
                                            value="{{ Lang::get('client.domainreglockenable') }}" />
                                    </p>
                                @endif
                            </form>

                        </div>
                        <div class="tab-pane fade" id="tabRelease">

                            <h3>{{ Lang::get('client.domainrelease') }}</h3>

                            @include('includes.alert', [
                                'type' => 'info',
                                'msg' => Lang::get('client.domainreleasedescription'),
                            ])

                            <form class="form-horizontal" role="form" method="post" action="?action=domaindetails">
                                @csrf
                                <input type="hidden" name="sub" value="releasedomain">
                                <input type="hidden" name="id" value="{{ $domainid }}">

                                <div class="form-group">
                                    <label for="inputReleaseTag"
                                        class="col-xs-4 control-label">{{ Lang::get('client.domainreleasetag') }}</label>
                                    <div class="col-xs-6 col-sm-5">
                                        <input type="text" class="form-control" id="inputReleaseTag"
                                            name="transtag" />
                                    </div>
                                </div>

                                <p class="text-center">
                                    <input type="submit" value="{{ Lang::get('client.domainrelease') }}"
                                        class="btn btn-primary" />
                                </p>
                            </form>

                        </div>
                        <div class="tab-pane fade" id="tabAddons">

                            <h3>{{ Lang::get('client.domainaddons') }}</h3>

                            <p>
                                {{ Lang::get('client.domainaddonsinfo') }}
                            </p>

                            @if ($addons['idprotection'])
                                <div class="row margin-bottom">
                                    <div class="col-xs-3 col-md-2 text-center">
                                        <i class="fas fa-shield-alt fa-3x"></i>
                                    </div>
                                    <div class="col-xs-9 col-md-10">
                                        <strong>{{ Lang::get('client.domainidprotection') }}</strong><br />
                                        {{ Lang::get('client.domainaddonsidprotectioninfo') }}<br />
                                        <form action="clientarea.php?action=domainaddons" method="post">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $domainid }}" />
                                            @if ($addonstatus['idprotection'])
                                                <input type="hidden" name="disable" value="idprotect" />
                                                <input type="submit" value="{{ Lang::get('client.disable') }}"
                                                    class="btn btn-danger" />
                                            @else
                                                <input type="hidden" name="buy" value="idprotect" />
                                                <input type="submit"
                                                    value="{{ Lang::get('client.domainaddonsbuynow') }} {{ $addonspricing['idprotection'] }}"
                                                    class="btn btn-success" />
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            @endif
                            @if ($addons['dnsmanagement'])
                                <div class="row margin-bottom">
                                    <div class="col-xs-3 col-md-2 text-center">
                                        <i class="fas fa-cloud fa-3x"></i>
                                    </div>
                                    <div class="col-xs-9 col-md-10">
                                        <strong>{{ Lang::get('client.domainaddonsdnsmanagement') }}</strong><br />
                                        {{ Lang::get('client.domainaddonsdnsmanagementinfo') }}<br />
                                        <form action="clientarea.php?action=domainaddons" method="post">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $domainid }}" />
                                            @if ($addonstatus['dnsmanagement'])
                                                <input type="hidden" name="disable" value="dnsmanagement" />
                                                <a class="btn btn-success"
                                                    href="clientarea.php?action=domaindns&domainid={{ $domainid }}">{{ Lang::get('client.manage') }}</a>
                                                <input type="submit" value="{{ Lang::get('client.disable') }}"
                                                    class="btn btn-danger" />
                                            @else
                                                <input type="hidden" name="buy" value="dnsmanagement" />
                                                <input type="submit"
                                                    value="{{ Lang::get('client.domainaddonsbuynow') }} {{ $addonspricing['dnsmanagement'] }}"
                                                    class="btn btn-success" />
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            @endif
                            @if ($addons['emailforwarding'])
                                <div class="row margin-bottom">
                                    <div class="col-xs-3 col-md-2 text-center">
                                        <i class="fas fa-envelope fa-3x">&nbsp;</i><i class="fas fa-share fa-2x"></i>
                                    </div>
                                    <div class="col-xs-9 col-md-10">
                                        <strong>{{ Lang::get('client.domainemailforwarding') }}</strong><br />
                                        {{ Lang::get('client.domainaddonsemailforwardinginfo') }}<br />
                                        <form action="clientarea.php?action=domainaddons" method="post">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $domainid }}" />
                                            @if ($addonstatus['emailforwarding'])
                                                <input type="hidden" name="disable" value="emailfwd" />
                                                <a class="btn btn-success"
                                                    href="clientarea.php?action=domainemailforwarding&domainid={{ $domainid }}">{{ Lang::get('client.manage') }}</a>
                                                <input type="submit" value="{{ Lang::get('client.disable') }}"
                                                    class="btn btn-danger" />
                                            @else
                                                <input type="hidden" name="buy" value="emailfwd" />
                                                <input type="submit"
                                                    value="{{ Lang::get('client.domainaddonsbuynow') }} {{ $addonspricing['emailforwarding'] }}"
                                                    class="btn btn-success" />
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row-->

        </div> <!-- container-fluid -->
    </div>
@endsection
