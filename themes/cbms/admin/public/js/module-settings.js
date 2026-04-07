function updateServerGroups(requiredModule) {
    var optionServerTypes = '';
    var doShowOption = false;

    $('#inputServerGroup').find('option:not([value=0])').each(function() {
        optionServerTypes = $(this).attr('data-server-types');

        if (requiredModule) {
            doShowOption = (optionServerTypes.indexOf(',' + requiredModule + ',') > -1);
        } else {
            doShowOption = true;
        }

        if (doShowOption) {
            $(this).attr('disabled', false);
        } else {
            $(this).attr('disabled', true);

            if ($(this).is(':selected')) {
                $('#inputServerGroup').val('0');
            }
        }
    });
}
function fetchModuleSettings(productId, mode, type) {
    var gotValidResponse = false;
    var dataResponse = '';
    var switchLink = $('#mode-switch');
    var module = $('#inputModule').val();

    if (module === "") {
        $('#tblModuleSettings').find('tr').not(':first').remove();
        $('#noModuleSelectedRow').removeClass('d-none');
        $('#tblModuleAutomationSettings').find('input[type=radio]').attr('disabled', true);
        return;
    }

    mode = mode || 'simple';
    if (mode != 'simple' && mode != 'advanced') {
        mode = 'simple';
    }
    requestedMode = mode;
    $('#tblModuleSettings').addClass('module-settings-loading');
    $('#tblModuleAutomationSettings').addClass('module-settings-loading');
    $('#serverReturnedError').addClass('d-none');
    $('#moduleSettingsLoader').show();
    switchLink.attr('data-product-id', productId);
    switchLink.attr('data-type', type);
    $.ajax({
        url: route('apiconsumer.admin.setup.fetchModuleSettings'),
        type: 'post',
        data: {
            'module': module,
            'servergroup': $('#inputServerGroup').val(),
            'id': productId,
            'mode': mode,
            'type': type || null,
        },
        success: function(res) {
            // console.log(res);
            gotValidResponse = true;
            $('#tblModuleSettings').removeClass('module-settings-loading');
            $('#tblModuleAutomationSettings').removeClass('module-settings-loading');
            $('#tblModuleSettings tr').not(':first').remove();
            switchLink.addClass('d-none');
            if (module && res.result == 'error') {
                $('#serverReturnedErrorText').html(res.message);
                $('#serverReturnedError').removeClass('d-none');
            }
            if (module && res.result == 'success' && res.response.content) {
                $('#noModuleSelectedRow').addClass('d-none');
                $('#tblModuleSettings').append(res.response.content);
                $('#tblModuleAutomationSettings').find('input[type=radio]').removeAttr('disabled');
                if (res.response.mode == 'simple') {
                    switchLink.attr('data-mode', 'advanced').find('a').find('span').addClass('d-none').parent().find('.text-advanced').removeClass('d-none');
                    switchLink.removeClass('d-none');
                } else {
                    if (res.response.mode == 'advanced' && requestedMode == 'advanced') {
                        switchLink.attr('data-mode', 'simple').find('a').find('span').addClass('d-none').parent().find('.text-simple').removeClass('d-none');
                        switchLink.removeClass('d-none');
                    } else {
                        switchLink.addClass('d-none');
                    }
                }
            } else {
                $('#noModuleSelectedRow').removeClass('d-none');
                $('#tblModuleAutomationSettings').find('input[type=radio]').attr('disabled', true);
            }
            $('#moduleSettingsLoader').fadeOut();
            jQuery('[data-toggle="tooltip"]').tooltip();
        },
        error: function(xhr) {
            var e = JSON.parse(xhr.responseText);
            $.notify(e.message, "error");
            $('#serverReturnedErrorText').html(e.message);
            $('#serverReturnedError').removeClass('d-none');
        },
    }).always(function() {
        updateServerGroups(gotValidResponse ? module : '');

        if (!gotValidResponse) {
            // non json response, likely session expired
        }
    });
}
$('#mode-switch').on("click", function() {
    fetchModuleSettings($(this).data('product-id'), $(this).attr('data-mode'), $(this).attr('data-type'));
});
