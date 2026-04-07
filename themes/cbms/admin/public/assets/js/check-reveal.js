//OneT Time
$("#onlyOneTime1").change(function() {
    if (this.checked) {
        $("#onlyOneTime1").val("true");
        $("#OnlyOneTimeSetupIDR").collapse("show");
        $("#OnlyOneTimePriceIDR").collapse("show");
    } else {
        $("#onlyOneTime1").val("false");
        $("#OnlyOneTimeSetupIDR").collapse("hide");
        $("#OnlyOneTimePriceIDR").collapse("hide");
    }
});

//Recurring
$("#enableCheckIDR1").change(function() {
    if (this.checked) {
        $("#enableCheckIDR1").val("true");
        $("#oneTimeSetupIDR").collapse("show");
        $("#oneTimePriceIDR").collapse("show");
    } else {
        $("#enableCheckIDR1").val("false");
        $("#oneTimeSetupIDR").collapse("hide");
        $("#oneTimePriceIDR").collapse("hide");
    }
});

$("#enableCheckIDR2").change(function() {
    if (this.checked) {
        $("#enableCheckIDR2").val("true");
        $("#quarterlySetupIDR").collapse("show");
        $("#quarterlyPriceIDR").collapse("show");
    } else {
        $("#enableCheckIDR1").val("false");
        $("#quarterlySetupIDR").collapse("hide");
        $("#quarterlyPriceIDR").collapse("hide");
    }
});

$("#enableCheckIDR3").change(function() {
    if (this.checked) {
        $("#enableCheckIDR3").val("true");
        $("#semiAnnualSetupIDR").collapse("show");
        $("#semiAnnualPriceIDR").collapse("show");
    } else {
        $("#enableCheckIDR1").val("false");
        $("#semiAnnualSetupIDR").collapse("hide");
        $("#semiAnnualPriceIDR").collapse("hide");
    }
});

$("#enableCheckIDR4").change(function() {
    if (this.checked) {
        $("#enableCheckIDR4").val("true");
        $("#annualySetupIDR").collapse("show");
        $("#annualyPriceIDR").collapse("show");
    } else {
        $("#enableCheckIDR1").val("false");
        $("#annualySetupIDR").collapse("hide");
        $("#annualyPriceIDR").collapse("hide");
    }
});

$("#enableCheckIDR5").change(function() {
    if (this.checked) {
        $("#enableCheckIDR5").val("true");
        $("#bienniallySetupIDR").collapse("show");
        $("#bienniallyPriceIDR").collapse("show");
    } else {
        $("#enableCheckIDR1").val("false");
        $("#bienniallySetupIDR").collapse("hide");
        $("#bienniallyPriceIDR").collapse("hide");
    }
});

$("#enableCheckIDR6").change(function() {
    if (this.checked) {
        $("#enableCheckIDR6").val("true");
        $("#trienniallySetupIDR").collapse("show");
        $("#trienniallyPriceIDR").collapse("show");
    } else {
        $("#enableCheckIDR1").val("false");
        $("#trienniallySetupIDR").collapse("hide");
        $("#trienniallyPriceIDR").collapse("hide");
    }
});

//Create new product bundle pages
$("#showOrderForm").change(function() {
    if (this.checked) {
        $("#showOrderForm").val("true");
        $("#orderForm").collapse("show");
    } else {
        $("#showOrderForm").val("false");
        $("#orderForm").collapse("hide");
    }
});
