$("#continueProcess").click(function () {
    localStorage.setItem("hiddenForm", "opened");
    $("#simpleform").attr("hidden", true);
    $("#advancedForm").attr("hidden", false);
    $("#nameserverForm").attr("hidden", false);
    $("#serverForm").attr("hidden", false);
    $("#btnAddServer").attr("hidden", false);
    $("#btnCnlServer").attr("hidden", false);

    let hostname = $("#hostname").val();
    let username = $("#username").val();
    let password = $("#password").val();

    $("#module").prependTo("#module-post");
    $("#accessHash").prependTo("#accessHash-post");

    $("#hostname-post").val(hostname);
    $("#username-post").val(username);
    $("#password-post").val(password);
});

if (localStorage.getItem("hiddenForm")) {
    $("#simpleform").attr("hidden", true);
    $("#advancedForm").attr("hidden", false);
    $("#nameserverForm").attr("hidden", false);
    $("#serverForm").attr("hidden", false);
    $("#btnAddServer").attr("hidden", false);
    $("#btnCnlServer").attr("hidden", false);

    let hostname = $("#hostname").val();
    let username = $("#username").val();
    let password = $("#password").val();

    $("#module").prependTo("#module-post");
    $("#accessHash").prependTo("#accessHash-post");

    $("#hostname-post").val(hostname);
    $("#username-post").val(username);
    $("#password-post").val(password);
}
