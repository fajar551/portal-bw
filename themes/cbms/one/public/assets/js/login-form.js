$(document).ready(function () {
    $("#login-form").on('submit', function () {
        $("#alert-invalid").toggle();
        $("#email, #password").removeClass("is-invalid");
        $("#login-text").hide();
        $("#login-btn").append(`
            <div class="spinner-border spinner-border-sm text-light" role="status">
            <span class="sr-only">Loading...</span>
            </div>`);
         $("#login-btn").attr('disabled', true);
      });
   //  $("#login-btn").on('click', function () {
   //      
   //  });
});
