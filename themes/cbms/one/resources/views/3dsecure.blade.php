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

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('includes.alert', [
                'type' => 'info',
                'msg' => Lang::get("client.creditcard3dsecure"),
                'textcenter' => true,
            ])
            <br /><br />

            <div class="text-center">

                <div id="frmThreeDAuth" class="hidden">
                    {!!$code!!}
                </div>

                <iframe name="3dauth" height="500" scrolling="auto" src="about:blank" class="submit-3d"></iframe>

                <br /><br />

            </div>

            <script type="text/javascript" src="{{Theme::asset('js/scripts.js')}}"></script>
            <script language="javascript">
                jQuery("#frmThreeDAuth").find("form:first").attr('target', '3dauth');
                setTimeout("autoSubmitFormByContainer('frmThreeDAuth')", 1000);
            </script>
        </div>
    </div>
@endsection
