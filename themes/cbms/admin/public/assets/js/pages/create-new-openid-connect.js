function disableRemoveBtn() {
    let count = document.getElementById("input-redirect").childElementCount;
    if (count == 1) {
        $("#btnRemoveInput").attr("disabled", "disabled");
    }
}

$(document).ready(function() {
    disableRemoveBtn();
});

$("#btnAddAnother").click(function() {
    $(".form-auth-redirect").append(`
      <input type="text" class="mb-2 w-75 form-control d-inline"
      placeholder="http://www.example.com/oauth">
    `);
    $("#btnRemoveInput").removeAttr("disabled");
});

$("#btnRemoveInput").click(function() {
    let lastItem = $(".form-auth-redirect").children();
    lastItem.last().remove();
    disableRemoveBtn();
});
