@extends('layouts.clientbase')

@section('title')
Error
@endsection

@section('page-title')
{{ Lang::get('client.domaincontactinfo') }}
@endsection
@section('content')

<div class="page-content">
    <div class="container-fluid">
        {{ $error }}
    </div>
</div>
@endsection
