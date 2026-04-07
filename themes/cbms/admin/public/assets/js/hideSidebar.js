$(document).ready(function () {
    $("#hide-btn").click(function () {
        $("#sidebar-card").fadeOut(function () {
            $("#main-card").addClass("col-xl-12");
            $(".col-xl-10").addClass("col-xl-12");
            $("#show-btn").addClass("d-lg-block");
        });
    });

    $("#show-btn").click(function () {
        $("#sidebar-card").fadeIn(function () {
            $("#main-card").removeClass("col-xl-12");
            $(".col-xl-10").removeClass("col-xl-12");
            $("#show-btn").removeClass("d-lg-block");
        });
    });
});
