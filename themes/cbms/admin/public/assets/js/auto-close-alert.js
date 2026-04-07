$(document).ready(function () {
    $("#success-alert")
        .fadeTo(5000, 1)
        .slideUp(100, function () {
            $("#success-alert").slideUp(100);
        });
});
