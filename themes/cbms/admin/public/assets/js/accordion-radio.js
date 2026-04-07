$(document).ready(function() {
    $("input[name='inlineRadioOptions']").click(function() {
        let checked = $("input[name='inlineRadioOptions']:checked").val();
        if (checked == "option1") {
            $("#productServiceRadio").collapse("hide");
            $("#generalRadio").collapse("show");
            $("#domainRadio").collapse("hide");
        } else if (checked == "option2") {
            $("#productServiceRadio").collapse("show");
            $("#domainRadio").collapse("hide");
        } else if (checked == "option3") {
            $("#productServiceRadio").collapse("hide");
            $("#domainRadio").collapse("show");
        }
    });
});

$(document).ready(function() {
    $("input[name='paymentType']").click(function() {
        let checked = $("input[name='paymentType']:checked").val();
        if (checked == "option0") {
            $("#oneTime").collapse("hide");
            $("#recurring").collapse("hide");
        } else if (checked == "option1") {
            $("#oneTime").collapse("show");
            $("#recurring").collapse("hide");
        } else if (checked == "option2") {
            $("#recurring").collapse("show");
            $("#oneTime").collapse("hide");
        }
    });
});
