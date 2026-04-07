@extends('layouts.clientbase')

@section('page-title')
   Shopping Cart - Forward Page
@endsection

@section('content')
{{-- @include('common') --}}
<script src="{{Theme::asset('js/scripts.js')}}"></script>
<div class="page-content">
    <div class="container-fluid">
        <div id="order-standard_cart">

            <br />

            @include('includes.alert', [
                'type' => 'info',
                'msg' => $message,
                'textcenter' => true,
            ])

            <br />

            <div class="text-center">

                <img src="{{Theme::asset('img/loading.gif')}}" alt="Loading" border="0" />

                <br /><br /><br />

                <div id="frmPayment" align="center">

                    {!!$code!!}

                    <form method="post" action="{{$invoiceid?route('pages.services.mydomains.viewinvoiceweb', $invoiceid):route('home')}}">
                        @csrf
                    </form>

                </div>

            </div>

            <br /><br /><br />

            <script language="javascript">
                setTimeout("autoSubmitFormByContainer('frmPayment')", 5000);
            </script>

            
        </div>
    </div>
</div>
@endsection
