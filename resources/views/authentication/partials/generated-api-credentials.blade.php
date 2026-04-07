<div id="createCredentialsSuccess">
    <p>{{Lang::get("admin.apicredscredSuccessSummary")}}        <span class="alert-warning">{{Lang::get("admin.apicredsmustCopySecret")}}            </span></p>
    <div class="form-group">
        <label for="inputDeviceIdentifier">{{Lang::get("admin.apicredsidentifier")}}</label>
        <div class="input-group">
            <input id="inputDeviceIdentifier" name="inputDeviceIdentifier" value="{{$identifier}}" class="form-control" />
            <div class="input-group-append">
                <button class="btn btn-outline-secondary copy-to-clipboard" data-clipboard-target="#inputDeviceIdentifier" type="button"><img src="{{asset('assets/images/clippy.svg')}}" alt="Copy to clipboard" width="15"></button>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="inputDeviceIdentifier">{{Lang::get("admin.apicredssecret")}}</label>
        <div class="input-group">
            <input id="inputDeviceSecret" name="inputDeviceSecret" value="{{$secret}}" class="form-control" />
            <div class="input-group-append">
                <button class="btn btn-outline-secondary copy-to-clipboard" data-clipboard-target="#inputDeviceSecret" type="button"><img src="{{asset('assets/images/clippy.svg')}}" alt="Copy to clipboard" width="15"></button>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function() {
        window.tblDevice.ajax.reload();
        jQuery('#NewAPICredentials-Generate').hide();
    });
</script>
