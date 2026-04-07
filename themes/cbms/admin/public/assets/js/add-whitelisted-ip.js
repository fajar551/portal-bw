let csrfToken = $('meta[name="csrf-token"]').attr("content");

function addIP(url) {
    event.preventDefault();
    let ipValue = $("#newIP").val();
    let reasonValue = $("#reason").val();
    $("#newIP").val("");
    $("#reason").val("");
    $("#whitelist-ip").append(
        "<option>" + ipValue + " - " + reasonValue + "</option>"
    );

    $.ajax({
        method: "POST",
        url: url,
        data: {
            ip: ipValue,
            note: reasonValue,
        },
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
    })
        .done((response) => {
            $("#toast-success").text(response);
            console.log(response);
        })
        .fail((error) => {
            console.log(error);
        });

    $("#close-modal").click();
    $("#liveToast").toast("show");
}

function removeIP(url) {
    event.preventDefault();
    let selectedIP = $("#whitelist-ip option:selected").val();
    $("#whitelist-ip option:selected").remove();

    $.ajax({
        method: "POST",
        url: url,
        data: {
            ip: selectedIP,
        },
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
    }).done((response) => {
       $("#toast-delete").text(response);
    console.log(response);
    }).fail((err) => {
       console.log(err);
    });
     $("#liveToastDeleted").toast("show");
}