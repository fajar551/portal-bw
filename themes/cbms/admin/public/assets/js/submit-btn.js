$(document).ready(function () {
    // submit button js 
    $(
        "#btnUpdateSettings, #btnCreateProdGroups, #btnCreateAdmin, #btnUpdateAdminRoles, #btnAddServer, #updateProds"
    ).click(function () {
        //
        $(this).attr("disabled", true);
        // add spinner to button
        setTimeout(() => {
            $(this).html(
                `<div class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></div>
            <div class="d-inline m-0">Please wait</div>
            `
            );
            $(this).css("cursor", "default");
        }, 800);
        setTimeout(() => {
            $('button:contains("Please wait")').html(
                `<div class="d-inline m-0">Redirecting...</div>`
            );
        }, 2000);
        setTimeout(() => {
            $(
                "#settingsForm, #productGroupForm, #AdminForm, #AdminRolesForm, #createNewServerForm, #updateProdsForm, #detailsTab, #pricingTabEditProduct, #upgradesTab"
            ).submit();
        }, 2000);
    });
});
