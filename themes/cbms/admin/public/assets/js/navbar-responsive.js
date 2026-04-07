$(document).ready(function () {
    let winSize = $(this).width();
    if (winSize <= 1000) {
        $("#mobile-topnav-serv").show();
        $("#desktop-topnav-serv").hide();
    } else {
        $("#mobile-topnav-serv").hide();
        $("#desktop-topnav-serv").show();
    }

    if (winSize >= 1920 || winSize <= 990) {
        $("#mini-info").hide();
    } else {
        $("#mini-info").show();
    }

    if (winSize <= 1000) {
        $("#topnav-uielement").attr("data-toggle", "dropdown");
    }
});

$(window).on("resize", function (event) {
    let resizeWidth = $(this).width();
    if (resizeWidth <= 1000) {
        $("#mobile-topnav-serv").show();
        $("#desktop-topnav-serv").hide();
    } else {
        $("#mobile-topnav-serv").hide();
        $("#desktop-topnav-serv").show();
    }

    if (resizeWidth >= 1920 || resizeWidth <= 990) {
        $("#mini-info").hide();
    } else {
        $("#mini-info").show();
    }

    if (resizeWidth <= 1000) {
        $("#topnav-uielement").attr("data-toggle", "dropdown");
    } else {
        $("#topnav-uielement").removeAttr("data-toggle", "dropdown");
    }
});
