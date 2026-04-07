<form method="post" action="{{route("admin.admin-setup-authz-api-devices-generate")}}" id="frmCreateCredentials">
    @csrf
    <div class="form-group">
        <label for="inputAdmin">{{Lang::get("admin.apicredsadminUser")}}</label>
        <select id="inputAdmin" name="admin_id" class="form-control enhanced" style="width:100%;">
            {!!$adminUserSelectOptions!!}
        </select>
    </div>
    @include('authentication.partials.attributes-api-credentials')
</form>

