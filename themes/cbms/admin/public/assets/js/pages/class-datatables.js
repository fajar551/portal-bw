$.extend($.fn.dataTable.defaults, {
    searching: false,
});
$(document).ready(function () {
    $(".display").DataTable({
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'",
                next: "<i class='mdi mdi-chevron-right'",
            },
            searching: false,
        },
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass(
                "pagination-rounded"
            );
        },
    });
});
