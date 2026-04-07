@extends('layouts.clientbase')

@section('title')
DNS Manager/ Forward Domain
@endsection

@section('page-title')
{{ Lang::get('client.domaincontactinfo') }}
@endsection
@section('content')

<div class="page-content">
    <div class="container-fluid">
        <div class="alert alert-warning" role="alert">
            <p>Untuk menggunakan fitur DNS manager, pastikan domain yang digunakan mengarah ke name server berikut:</p>
            <br>
            <ul>
                <li>dnsiix1.qwords.net</li>
                <li>dnsiix2.qwords.net</li>
            </ul>
            <br>
            <p>Domain anda belum mengarah ke nameserver di atas, silahkan update di halaman
                <a href="{{ url('https://portal.qwords.com/clientarea.php?action=domaindetails') }}">
                    berikut ini
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
