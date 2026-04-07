// Swal toast
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: true,
    timerProgressBar: true,
    timer: 6000,
});

// Daterange options
const dateRangeOption = {
    format: 'dd/mm/yyyy',
    autoclose: true,
    orientation: 'bottom',
    todayBtn: 'linked',
    todayHighlight: true,
    clearBtn: true,
    disableTouchKeyboard: true,
};

const dateOption = dateRangeOption;

// Global option post
let options = {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    },
    body: JSON.stringify({}),
};

let optPOST = {
    ...options,
    method: 'POST',
};

let optGET = {
    ...options,
    method: 'GET',
};

let optDEL = {
    ...options,
    method: 'DELETE',
};

const baseDtTableConfig = {
    stateSave: true,
    processing: true,
    responsive: true,
    serverSide: true,
    autoWidth: false,
    searching: false,
    destroy: true,
    language: {
        paginate: {
            previous: "<i class='mdi mdi-chevron-left'>",
            next: "<i class='mdi mdi-chevron-right'>",
        },
    },
    drawCallback: () => {
        $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
    },
}

// Http post
const cbmsPost = async (url, options) => {
    let result = await fetch(url, options)
        .then(response => {
            if (!response.ok) throw new Error(response.statusText);

            return response.json();
        })
        .catch(error => {
            console.log(`cbmsPost: Request failed: ${error}`);

            return false;
    });

    return result;
}

const showEmptyIDToast = (message = null) => {
    showNoSelectionToast(message ?? 'You must select at least one or more item in the list.');
}

const showNoSelectionToast = (message = null) => {
    Toast.fire({
        icon: 'warning',
        title: message ?? 'There\'s no items have been selected.',
    });
}

const sendMultiple = (selectedclients = [], form) => {
    $("input[name='selectedclients[]']").remove();

    if (!selectedclients.length) {
        showEmptyIDToast();
        return;
    }
    
    $.each(selectedclients, (key, value) => {
        form.append(`<input type="text" name="selectedclients[]" id="mailselect${value}" value="${value}" hidden/>`);
    });

    form.submit();
}