<form name="frmApiCredentialManage" action="{{route("admin.admin-setup-authz-api-devices-update")}}">
    @csrf
    <input type="hidden" name="id" value="{{$device->id}}">

    @include('authentication.partials.attributes-api-credentials')
</form>
