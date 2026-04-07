<div class="row">
    <div class="col-lg-6">
        <form>
            <div class="form-group">
                <select name="profile_name" id="select-prof-name" class="form-control select2-limiting" style="width: 100%">
                    @if (isset($clientsdetails))
                    <option value="{{ $clientsdetails["userid"] }}" selected>{{ $clientsdetails["fullname"] }} - #{{ $clientsdetails["userid"] }}</option>
                    @else
                    <option>{{ __("None") }}</option>
                    @endif
                </select>
            </div>
        </form>
    </div>
</div>

@push('clientsearch')
<script src="{{ Theme::asset('assets/js/pages/helpers/select2-utils.js') }}"></script>
<script>
    $(() => {
        searchClient($("#select-prof-name"), "{!! route('admin.pages.clients.viewclients.clientsummary.searchClient') !!}", {}, function (e) {
            let data = e.params.data;
            window.location.href = "{!! route('admin.pages.clients.viewclients.clientsummary.index'); !!}?userid=" +data.id;
        });
    });
</script>
@endpush
