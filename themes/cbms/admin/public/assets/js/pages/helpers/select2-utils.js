// Search the client using select2 plugin
const searchClient = (el, url, addParams = {}, callback = null) => {
    el.select2({
        // theme: "classic"
        placeholder: 'Start Typing to Search Clients',
        width: 'resolve',
        allowClear: true,
        closeOnSelect: true,
        cache: true,
        minimumInputLength: 3,
        templateResult: searchClientTemplateResult,
        templateSelection: searchClientTemplateResult,
        ajax: {
            url,
            type: 'GET',
            dataType: 'json',
            delay: 1000, // Wait 1 seconds before triggering the request
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function (params) {
                // You can add more params here
                let query = {
                    ...addParams,
                    search: params.term,
                }

                return query;
            }
        },
    });

    /**
    * Add select2 event onSelected
    * e.g:
    $('#search_client').on('select2:select', function (e) {
        let data = e.params.data;

        $('#newuserid').val(data.id);
    });
    */

    if (callback) {
        el.on('select2:select', callback); 
    }
}

const searchClientTemplateResult = (result) => {
    if (!result.data) return result.text;

    return $(`<div class="customselect" style="line-height: 1.4; font-size: 12px;" >   
                <span class="d-block" > ${result.data.firstname} ${result.data.lastname} ${result.data.companyname} #${result.data.id}</span>
                <span class="d-block" > <small>${result.data.email}</small></span>
            </div>`);
}