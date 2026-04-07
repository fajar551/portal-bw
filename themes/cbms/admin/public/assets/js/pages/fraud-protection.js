$("select#fraudOpt").change(function() {
    // Two ways to get the current selected option
    // 1. Using JQUERY VAL
    let selected = $("#fraudOpt").val();

    if (selected == 1) {
        $("#fraudLabs").collapse("show");
        $("#maxMind").collapse("hide");
    } else if (selected == 2) {
        $("#maxMind").collapse("show");
        $("#fraudLabs").collapse("hide");
    } else {
        $("#maxMind").collapse("hide");
        $("#fraudLabs").collapse("hide");
    }
});
