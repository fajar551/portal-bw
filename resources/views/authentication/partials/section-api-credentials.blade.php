<p>
        <a id="btnNewAPICredentials"
            href="{{route('admin.admin-setup-authz-api-device-new')}}"
            class="btn btn-success open-modal"
            data-modal-title="{{addslashes(Lang::get("admin.apicredentialscreate"))}}"
            data-btn-submit-id="NewAPICredentials-Generate"
            data-btn-submit-label="{{addslashes(Lang::get("admin.apicredentialsgenerate"))}}"
        >
            <i class="fas fa-plus fa-fw"></i>
            {{Lang::get("admin.apicredentialscreate")}}
        </a>
</p>

<table id="tblDevice" class="table table-responsive display data-driven table-themed"
       data-ajax='{"url": "{{route('admin.admin-setup-authz-api-devices-list')}}"}'
       data-on-draw-rebind-confirmation="true"
       data-lang-empty-table="{{Lang::get("admin.apicredentialsnoCredentials")}}"
       data-auto-width="false"
       data-columns='{{json_encode(array(array("width" => "27%"), array("width" => "20%"), array("width" => "16%"), array("width" => "17%"), array("width" => "12%"), array("width" => "8%", "orderable" => 0)))}}'
       >
    <thead>
        <tr>
        <th>Identifier</th>
        <th>Description</th>
        <th>Admin User</th>
        <th>Roles</th>
        <th>Last Access</th>
        <th></th>
    </tr>
    </thead>
</table>

<!-- successful form return body will want to use image; this cache in browser for smoother UX -->
{{-- <img class="hide" src="../assets/img/clippy.svg" alt="Copy to clipboard" width="15" /> --}}
