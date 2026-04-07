let onLoadWidth = window.innerWidth;

if (onLoadWidth == 1366) {
    $(".card-link-contact").removeClass("col-lg-6");
} else {
    $(".card-link-contact").addClass("col-lg-6");
}

$(window).on("resize", function () {
    let resizeWidth = window.innerWidth;

    if (resizeWidth <= 1366) {
        $(".card-link-contact").removeClass("col-lg-6");
    } else {
        $(".card-link-contact").addClass("col-lg-6");
    }
});
