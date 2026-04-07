@extends('layouts.clientbase')

@section('title')
   
@endsection

@section('page-title')
   Upgrade
@endsection

@section('content')
    <div class="page-content bg-white" id="service-detailsx">
        <div class="container-fluid">
            @if ($overdueinvoice)
                @include('includes.alert', [
                    'type' => 'warning',
                    'msg' => Lang::get('client.upgradeerroroverdueinvoice'),
                ])
            @elseif ($existingupgradeinvoice)
                @include('includes.alert', [
                    'type' => 'warning',
                    'msg' => Lang::get('client.upgradeexistingupgradeinvoice'),
                ])
            @elseif ($upgradenotavailable)
                @include('includes.alert', [
                    'type' => 'warning',
                    'msg' => Lang::get('client.upgradeNotPossible'),
                    'textcenter' => true,
                ])
            @endif

            @if ($overdueinvoice)
                <p>
                    <a href="clientarea.php?action=productdetails&id={{$id}}" class="btn btn-secondary">{{Lang::get('client.clientareabacklink')}}</a>
                </p>
            @elseif ($existingupgradeinvoice)
                <p>
                    <a href="clientarea.php?action=productdetails&id={{$id}}" class="btn btn-secondary btn-lg">{{Lang::get('client.clientareabacklink')}}</a>
                    <a href="submitticket.php" class="btn btn-secondary btn-lg">{{Lang::get('client.submitticketdescription')}}</a>
                </p>
            @elseif ($upgradenotavailable)
                <p>
                    <a href="clientarea.php?action=productdetails&id={{$id}}" class="btn btn-secondary btn-lg">{{Lang::get('client.clientareabacklink')}}</a>
                    <a href="submitticket.php" class="btn btn-secondary btn-lg">{{Lang::get('client.submitticketdescription')}}</a>
                </p>
            @else
                @if ($type == "package")
                    <p>{{Lang::get('client.upgradechoosepackage')}}</p>

                    <p>{{Lang::get('client.upgradecurrentconfig')}}:<br/><strong>{{$groupname}} - {{$productname}}</strong>@if ($domain) ({{$domain}}) @endif</p>

                    <p>{{Lang::get('client.upgradenewconfig')}}:</p>

                    <table class="table table-striped">
                        @foreach ($upgradepackages as $num => $upgradepackage)
                            <tr>
                                <td>
                                    <strong>
                                        {{$upgradepackage['groupname']}} - {{$upgradepackage['name']}}
                                    </strong>
                                    <br />
                                    {{$upgradepackage['description']}}
                                </td>
                                <td width="300" class="text-center">
                                    <form method="post" action="">
                                        @csrf
                                        <input type="hidden" name="step" value="2">
                                        <input type="hidden" name="type" value="{{$type}}">
                                        <input type="hidden" name="id" value="{{$id}}">
                                        <input type="hidden" name="pid" value="{{$upgradepackage['pid']}}">
                                        <div class="form-group">
                                            @if ($upgradepackage['pricing']['type'] == "free")
                                                {{Lang::get('client.orderfree')}}<br />
                                                <input type="hidden" name="billingcycle" value="free">
                                            @elseif ($upgradepackage['pricing']['type'] == "onetime")
                                                {{$upgradepackage['pricing']['onetime']}} {{Lang::get('client.orderpaymenttermonetime')}}
                                                <input type="hidden" name="billingcycle" value="onetime">
                                            @elseif ($upgradepackage['pricing']['type'] == "recurring")
                                                <select name="billingcycle" class="form-control">
                                                    @if ($upgradepackage['pricing']['monthly']) <option value="monthly">{{$upgradepackage['pricing']['monthly']}}</option> @endif
                                                    @if ($upgradepackage['pricing']['quarterly']) <option value="monthly">{{$upgradepackage['pricing']['quarterly']}}</option> @endif
                                                    @if ($upgradepackage['pricing']['semiannually']) <option value="monthly">{{$upgradepackage['pricing']['semiannually']}}</option> @endif
                                                    @if ($upgradepackage['pricing']['annually']) <option value="monthly">{{$upgradepackage['pricing']['annually']}}</option> @endif
                                                    @if ($upgradepackage['pricing']['biennially']) <option value="monthly">{{$upgradepackage['pricing']['biennially']}}</option> @endif
                                                    @if ($upgradepackage['pricing']['triennially']) <option value="monthly">{{$upgradepackage['pricing']['triennially']}}</option> @endif
                                                </select>
                                            @endif
                                        </div>
                                        <input type="submit" value="{{Lang::get('client.upgradedowngradechooseproduct')}}" class="btn btn-primary btn-block" />
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @elseif ($type == "configoptions")
                    <p>{{Lang::get('client.upgradechooseconfigoptions')}}</p>

                    @if ($errormessage)
                        @include('includes.alert', [
                            'type' => 'error',
                            'errorshtml' => $errormessage,
                        ])
                    @endif

                    <form method="post" action="">
                        @csrf
                        <input type="hidden" name="step" value="2" />
                        <input type="hidden" name="type" value="{{$type}}" />
                        <input type="hidden" name="id" value="{{$id}}" />

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{Lang::get('client.upgradecurrentconfig')}}</th>
                                    <th></th>
                                    <th>{{Lang::get('client.upgradenewconfig')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($configoptions as $num => $configoption)
                                    <tr>
                                        <td>{{$configoption['optionname']}}</td>
                                        <td>
                                            @if ($configoption['optiontype'] == 1 || $configoption['optiontype'] == 2)
                                                {!!$configoption['selectedname']!!}
                                            @elseif ($configoption['optiontype'] == 3)
                                                @if ($configoption['selectedqty'])
                                                    {{Lang::get('client.yes')}}
                                                @else
                                                    {{Lang::get('client.no')}}
                                                @endif
                                            @elseif ($configoption['optiontype'] == 4)
                                                {{$configoption['selectedqty']}} x {{$configoption['options'][0]['name']}}
                                            @endif
                                        </td>
                                        <td>=></td>
                                        <td>
                                            @if ($configoption['optiontype'] == 1 || $configoption['optiontype'] == 2)
                                                <select name="configoption[{{$configoption['id']}}]">
                                                    @foreach ($collection as $configoption['options'])
                                                        @if ($option['selected'])
                                                            <option value="{{$option['id']}}" selected>{{Lang::get('client.upgradenochange')}}</option>
                                                        @else
                                                            <option value="{{$option['id']}}">{{$option['nameonly']}} {{$option['price']}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            @elseif ($configoption['optiontype'] == 3)
                                                <input type="checkbox" name="configoption[{{$configoption['id']}}]" value="1" {{$configoption['selectedqty']? 'checked':''}}> {{$configoption['options'][0]['name']}}
                                            @elseif ($configoption['optiontype'] == 4)
                                                <input type="text" name="configoption[{{$configoption['id']}}]" value="{{$configoption['selectedqty']}}" size="5"> x {{$configoption['options'][0]['name']}}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p class="text-center">
                            <input type="submit" value="{{Lang::get('client.ordercontinuebutton')}}" class="btn btn-primary" />
                        </p>

                    </form>
                @endif
            @endif

        </div>
    </div>
@endsection
