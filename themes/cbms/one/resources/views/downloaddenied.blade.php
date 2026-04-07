@extends('layouts.clientbase')

@section('page-title')
   Downloads
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @if ($reason == "supportandupdates")
                @include('includes.alert', [
                    'type' => 'error',
                    'msg' => Lang::get('client.supportAndUpdatesExpiredLicense').($licensekey ? $licensekey : '.'),
                    'textcenter' => true,
                ])
            @endif

            @if ($reason == "supportandupdates")
                <p>{{Lang::get('client.supportAndUpdatesRenewalRequired')}}</p>

                <form action="{{route('cart')}}?a=add" method="post">
                    <input type="hidden" name="productid" value="{{$serviceid}}" />
                    <input type="hidden" name="aid" value="{{$addonid}}" />
                    <p align="center"><input type="submit" value="{{Lang::get('client.supportAndUpdatesClickHereToRenew')}} &raquo;" class="btn btn-primary" /></p>
                </form>
            @else
                <p>{{Lang::get('client.downloadproductrequired')}}</p>

                @if ($prodname)
                    @include('includes.alert', [
                        'type' => 'info',
                        'msg' => $prodname,
                        'textcenter' => true,
                    ])
                @else
                    @include('includes.alert', [
                        'type' => 'info',
                        'msg' => $addonname,
                        'textcenter' => true,
                    ])
                @endif
                
                @if ($pid || $aid)
                    <form action="{{route('cart')}}" method="post">
                        @if ($pid)
                            <input type="hidden" name="a" value="add" />
                            <input type="hidden" name="pid" value="{{$pid}}" />
                        @elseif ($aid)
                            <input type="hidden" name="gid" value="addons" />
                        @endif
                        <p align="center"><input type="submit" value="{{Lang::get('client.ordernowbutton')}} &raquo;" class="btn btn-primary" /></p>
                    </form>
                @endif
            @endif

        </div>
    </div>
@endsection
