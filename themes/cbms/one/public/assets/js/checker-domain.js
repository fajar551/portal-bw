async function CheckDomainWhois(el) {
    $(el).attr("disabled", true);
    $(el).text("");
    $(el)
        .append(`<div class="spinner-border spinner-border-sm text-light" role="status">
       <span class="sr-only">Loading...</span>
       </div>`);
    $("#result-check, #error-fetch").attr("hidden", true);
    $(
        "#domain-name, #availability, #error-text, #error-code, #status-icon, #price-domain"
    ).empty();
    $("#availability, #messagewhois").removeClass("text-danger");
    $("#availability, #messagewhois").removeClass("text-success");
    $("#wait-state").removeAttr("hidden", true);

    const regdropdown = $("#inputDomainRegPeriod0");
    const csrf_token = $('meta[name="csrf-token"]').attr("content");
    let tldId = $("#tldRegList").find(":selected").data("id");
    let domainName = $("#regDomain").val();
    let tld = $("#tldRegList").val();
    let domain_name = domainName + tld;
    const domain_reg = regdropdown.val() ?? 1;

    const Toast = Swal.mixin({
        toast: true,
        position: "top",
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
    });

    const datawhois = await {
        domainId: tldId,
        domain: domain_name,
        domainReg: domain_reg,
    };

    $.ajax({
        type: "POST",
        url: route("domaincheck.json"),
        data: datawhois,
        dataType: "json",
        headers: {
            "X-CSRF-Token": csrf_token,
        },
        success: async function (response) {
            console.log(response);
            $(el).empty();
            $(el).removeAttr("disabled", true);
            $(el).text("Check");
            $("#result-check, #register-add-cart").removeAttr("hidden", true);
            $("#wait-state").attr("hidden", true);
            let { domain, result } = response;
            $("#domain-name").append(domain);
            $("#availability")
                .addClass(`text-${result.type}`)
                .text(result.availability);
            $("#messagewhois")
                .addClass(`text-${result.type}`)
                .text(result.message);
            if (result.availability == "available") {
                $(regdropdown).removeAttr("hidden", true);
                $("#status-icon").append(`
                 <i class="far fa-check-circle text-success" style="font-size: 48px;"></i>
            `);
                $("#price-domain").append(`
                <p class="text-qw" id="tldPrice" data-price="${result.price}">${result.extension}</p>
                <h5 class="text-qw">${result.priceformatted}</h5>`);
            } else {
                $(regdropdown).attr("hidden", true);
                $("#status-icon").append(`
                 <i class="far fa-times-circle text-danger" style="font-size: 48px;"></i>
                `);
                $("#register-add-cart").attr("hidden", true);
            }
        },
        error: async function (error) {
            $(el).empty();
            $(el).removeAttr("disabled", true);
            $(el).text("Check");
            $("#wait-state").attr("hidden", true);
            await Toast.fire({
                icon: "error",
                title: error.statusText,
                text: `Error Code (${error.status})`,
            });
        },
    });
}

$("#inputDomainRegPeriod0").on('change', () => {
    CheckDomainWhois();
})

function CheckDomainStatus(el) {
    $(el).attr("disabled", true);
    $(el).text("");
    $(el).append(`
       <div class="spinner-border spinner-border-sm text-light" role="status">
       <span class="sr-only">Loading...</span>
       </div>
    `);

    const csrf_token = $('meta[name="csrf-token"]').attr("content");
    let domainName = $("#regTransfer").val();
    let tld = $("#tldTrf").val();
    let domain_name = domainName + tld;

    $("#result-trf-check").attr("hidden", true);
    $("#wait-trf").removeAttr("hidden", true);
    $("#message-trf").empty();

    const datadomain = {
        domain: domain_name,
        sld: domainName,
        tld: tld,
    };

    $.ajax({
        type: "POST",
        url: route("domainstatus.json"),
        data: datadomain,
        dataType: "json",
        headers: {
            "X-CSRF-Token": csrf_token,
        },
        success: function (response) {
            let { message, status } = response;
            $(el).empty();
            $(el).removeAttr("disabled", true);
            $(el).text("Check");
            $("#wait-trf").attr("hidden", true);
            $("#result-trf-check").removeAttr("hidden", true);

            if (status == "available") {
                $("#message-trf").append(`
             <div class="alert alert-success" role="alert">
                 <h3 class="mb-0"><i class="far fa-check-circle text-success mr-2"></i>${message}</h3>
             </div>
             <div class="input-group mb-0">
             <div class="input-group-prepend">
                 <span class="input-group-text" id="input-epp-label">EPP Code</span>
             </div>
                 <input type="text" class="form-control" aria-label="EPP input" aria-describedby="input-epp-label" id="input-epp">
             </div>
             <div class="text-left">
                 <small><i class="fas fa-exclamation-circle mr-2"></i>EPP code can be obtained from your previous registrar</small>
             </div>
             <button class="btn btn-success-qw px-5" id="btn-transfer-domain" disabled>
                 Continue
             </button>
          `);
            } else {
                $("#message-trf").append(`
             <div class="alert alert-danger" role="alert">
                 <h3 class="mb-0"><i class="far fa-times-circle text-danger mr-2"></i>${message}</h3>
             </div>
          `);
            }

            $("#input-epp").keydown(function (e) {
                if ($("#input-epp").val().length >= 6) {
                    $("#btn-transfer-domain").removeAttr("disabled", true);
                } else {
                    $("#btn-transfer-domain").attr("disabled", true);
                }
            });
        },
        error: function (err) {
            console.log(err);
            $("#message-trf").empty();
        },
    });
}
