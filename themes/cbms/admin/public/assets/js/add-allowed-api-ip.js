const csrfToken2 = $('meta[name="csrf-token"]').attr("content");
function addAllowedAPI(url) {
   event.preventDefault();
   let ip2Value = $("#AllowedIP").val();
   let note2Value = $("#noteAllowedIp").val();
   $("#AllowedIP").val("");
   $("#noteAllowedIp").val("");
   $("#allowed-ip-api").append(
       "<option>" + ip2Value + " - " + note2Value + "</option>"
   );

   $.ajax({
       method: "POST",
       url: url,
       data: {
           ip: ip2Value,
           note: note2Value,
       },
       headers: {
           "X-CSRF-TOKEN": csrfToken2,
       },
   })
       .done((response) => {
           $("#toast-success").text(response);
       })
       .fail((error) => {
           console.log(error);
       });
   
   $("#close-modal-api").click();
   $("#liveToast").toast("show");
}

function removeAllowedIP(url) {
    event.preventDefault();
    let selectedIP = $("#allowed-ip-api option:selected").val();
    $("#allowed-ip-api option:selected").remove();

    $.ajax({
        method: "POST",
        url: url,
        data: {
            ip: selectedIP,
        },
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
    })
        .done((response) => {
            $("#toast-delete").text(response);
            console.log(response);
        })
        .fail((err) => {
            console.log(err);
        });
    $("#liveToastDeleted").toast("show");
}