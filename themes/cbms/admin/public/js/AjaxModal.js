var ajaxModalSubmitEvents = [];
$(document).ready(function(){

    $(document).on('click', '.open-modal', function(e) {
        e.preventDefault();
        var url = $(this).attr('href'),
            modalSize = $(this).data('modal-size'),
            modalClass = $(this).data('modal-class'),
            modalTitle = $(this).data('modal-title'),
            submitId = $(this).data('btn-submit-id'),
            submitLabel = $(this).data('btn-submit-label'),
            hideClose = $(this).data('btn-close-hide'),
            disabled = $(this).attr('disabled'),
            successDataTable = $(this).data('datatable-reload-success');

        if (!disabled) {
            openModal(url, '', modalTitle, modalSize, modalClass, submitLabel, submitId, hideClose, successDataTable);
        }
    });

    // define modal close reset action
    $('#modalAjax').on('hidden.bs.modal', function (e) {
        $('#modalAjax').find('.modal-body').empty();
        $('#modalAjax').children('div.modal-dialog').removeClass('modal-lg');
        $('#modalAjax').removeClass().addClass('modal whmcs-modal fade');
        $('#modalAjax .modal-title').html('Title');
        $('#modalAjax .modal-submit').html('Submit')
            .removeClass()
            .addClass('btn btn-primary modal-submit')
            .removeAttr('id')
            .removeAttr('disabled');
        $('#modalAjax .loader').show();
    });
});

function openModal(url, postData, modalTitle, modalSize, modalClass, submitLabel, submitId, hideClose, successDataTable) {
    //set the text of the modal title
    $('#modalAjax .modal-title').html(modalTitle);

    // set the modal size via a class attribute
    if (modalSize) {
        $('#modalAjax').children('div[class="modal-dialog"]').addClass(modalSize);
    }
    // set the modal class
    if (modalClass) {
        $('#modalAjax').addClass(modalClass);
    }

    // set the modal class
    if (modalClass) {
        $('#modalAjax').addClass(modalClass);
    }

    // set the text of the submit button
    if(!submitLabel){
       $('#modalAjax .modal-submit').hide();
    } else {
        $('#modalAjax .modal-submit').show().html(submitLabel);
        // set the button id so we can target the click function of it.
        if (submitId) {
            $('#modalAjax .modal-submit').attr('id', submitId);
        }
    }

    if (hideClose) {
        $('#modalAjaxClose').hide();
    }

    $('#modalAjax .modal-body').html('');

    $('#modalSkip').hide();
    $('#modalAjax .modal-submit').prop('disabled', true);

    // show modal
    $('#modalAjax').modal('show');

    // fetch modal content
    $.ajax({
        url: url,
        type: 'POST',
        data: postData || {_token: $('meta[name="csrf-token"]').attr('content')},
        success: function (data) {
            updateAjaxModal(data);
        },
        error: function(xhr, status, error) {
            var e = JSON.parse(xhr.responseText);
        },
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        $('#modalAjax .modal-body').html('An error occurred while communicating with the server. Please try again.');
        $('#modalAjax .loader').fadeOut();
    })
    .always(function (dataOrjqXHR, textStatus, jqXHRorErrorThrown) {
        if (successDataTable) {
            var modalForm = $('#modalAjax').find('form');
            modalForm.data('successDataTable', successDataTable);
        }
     });

    // WHMCS.http.jqClient.post(url, postData, function(data) {
    //     updateAjaxModal(data);
    // }, 'json').fail(function() {
    //     $('#modalAjax .modal-body').html('An error occurred while communicating with the server. Please try again.');
    //     $('#modalAjax .loader').fadeOut();
    // }).always(function () {
    //     if (successDataTable) {
    //         var modalForm = $('#modalAjax').find('form');
    //         modalForm.data('successDataTable', successDataTable);
    //     }
    // });

    //define modal submit button click
    if (submitId) {
        var submitButton = $('#' + submitId);
        submitButton.off('click');
        // $('#modalAjax .modal-submit').prop('disabled', false);
        submitButton.on('click', submitIdAjaxModalClickEvent);
    }
}

function submitIdAjaxModalClickEvent ()
{
    if ($(this).hasClass('disabled')) {
        return;
    }
    var canContinue = true,
        btn = $(this);
    btn.addClass('disabled');
    $('#modalAjax .loader').show();
    if (ajaxModalSubmitEvents.length) {
        $.each(ajaxModalSubmitEvents, function (index, value) {
            var fn = window[value];
            if (canContinue && typeof fn === 'function') {
                canContinue = fn();
            }
        });
    }
    if (!canContinue) {
        btn.removeClass('disabled');
        return;
    }
    var modalForm = $('#modalAjax').find('form');
    var modalBody = $('#modalAjax .modal-body');
    var modalErrorContainer = $(modalBody).find('.admin-modal-error');

    $(modalErrorContainer).slideUp();

    var modalPost = $.ajax({
        url: modalForm.attr('action'),
        type: 'POST',
        data: modalForm.serialize(),
        success: function (data) {
            if (modalForm.data('successDataTable')) {
                data.successDataTable = modalForm.data('successDataTable');
            }
            updateAjaxModal(data);
        },
        error: function(xhr, status, error) {
            var e = JSON.parse(xhr.responseText);
        },
    }).fail(function(xhr) {
        var data = xhr.responseJSON;
        var genericErrorMsg = 'An error occurred while communicating with the server. Please try again.';
        if (data && data.data) {
            data = data.data;
            if (data.errorMsg) {
                if (modalErrorContainer.length > 0) {
                    $(modalErrorContainer)
                        .html(data.errorMsg)
                        .slideDown();
                } else {
                    // $.growl.warning({title: data.errorMsgTitle, message: data.errorMsg});
                    $.notify(data.errorMsg, "error");
                }
            } else if (data.data.body) {
                $(modalBody).html(data.body);
            } else {
                $(modalBody).html(genericErrorMsg);
            }
        } else {
            $(modalBody).html(genericErrorMsg);
        }
        $('#modalAjax .loader').fadeOut();
    }).always(function () {
        btn.removeClass('disabled');
    });
}

function updateAjaxModal(data) {
    if (data.reloadPage) {
        if (typeof data.reloadPage === 'string') {
            window.location = data.reloadPage;
        } else {
            window.location.reload();
        }
        return;
    }
    // console.log(data);
    if (data.successDataTable) {
        // WHMCS.ui.dataTable.getTableById(data.successDataTable, undefined).ajax.reload();
        $("#"+data.successDataTable).DataTable().ajax.reload();
    }
    if (data.redirect) {
        window.location = data.redirect;
        dialogClose();
    }
    if (data.successWindow && typeof window[data.successWindow] === "function") {
        window[data.successWindow]();
    }
    if (data.dismiss) {
        dialogClose();
    }
    if (data.successMsg) {
        // $.growl.notice({ title: data.successMsgTitle, message: data.successMsg });
        $.notify(data.successMsg, "success");
    }
    console.log(data);
    if (data.errorMsg) {
        var inModalErrorContainer = $('#modalAjax .modal-body .admin-modal-error');

        if (inModalErrorContainer.length > 0 && !data.dismiss) {
            $(inModalErrorContainer)
                .html(data.errorMsg)
                .slideDown();
        } else {
            // $.growl.warning({title: data.errorMsgTitle, message: data.errorMsg});
            $.notify(data.errorMsg, "error");
        }
    }
    if (data.title) {
        $('#modalAjax .modal-title').html(data.title);
    }
    if (data.body) {
        $('#modalAjax .modal-body').html(data.body);
    } else {
        if (data.url) {
            $.ajax({
                url: data.url,
                type: 'POST',
                success: function (data2) {
                    $('#modalAjax').find('.modal-body').html(data2.body);
                },
                error: function(xhr, status, error) {
                    var e = JSON.parse(xhr.responseText);
                },
            }).fail(function() {
                $('#modalAjax').find('.modal-body').html('An error occurred while communicating with the server. Please try again.');
                $('#modalAjax').find('.loader').fadeOut();
            });
        }
    }
    if (data.submitlabel) {
        $('#modalAjax .modal-submit').html(data.submitlabel).show();
        if (data.submitId) {
            $('#modalAjax').find('.modal-submit').attr('id', data.submitId);
        }
    }

    if (data.submitId) {
        var submitButton = $('#' + data.submitId);
        submitButton.off('click');
        submitButton.on('click', function() {
            var modalForm = $('#modalAjax').find('form');
            $('#modalAjax .loader').show();
            var modalPost = $.ajax({
                url: modalForm.attr('action'),
                type: 'POST',
                data: modalForm.serialize(),
                success: function (data) {
                    updateAjaxModal(data);
                },
                error: function(xhr, status, error) {
                    var e = JSON.parse(xhr.responseText);
                },
            }).fail(function() {
                $('#modalAjax .modal-body').html('An error occurred while communicating with the server. Please try again.');
                $('#modalAjax .loader').fadeOut();
            });
        })
    }

    $('#modalAjax .loader').fadeOut();
    // $('#modalAjax .modal-submit').removeProp('disabled');
    $('#modalAjax .modal-submit').prop('disabled', false);
}

// backwards compat for older dialog implementations

function dialogSubmit() {
    $('#modalAjax .modal-submit').prop("disabled", true);
    $('#modalAjax .loader').show();
    var postUrl = $('#modalAjax').find('form').attr('action');

    $.ajax({
        url: postUrl,
        type: 'POST',
        data: $('#modalAjax').find('form').serialize(),
        success: function (data) {
            updateAjaxModal(data);
        },
        error: function(xhr, status, error) {
            var e = JSON.parse(xhr.responseText);
        },
    }).fail(function() {
        $('#modalAjax .modal-body').html('An error occurred while communicating with the server. Please try again.');
        $('#modalAjax .loader').fadeOut();
    });
}

function dialogClose() {
    $('#modalAjax').modal('hide');
}

function addAjaxModalSubmitEvents(functionName) {
    if (functionName) {
        ajaxModalSubmitEvents.push(functionName);
    }
}

function removeAjaxModalSubmitEvents(functionName) {
    if (functionName) {
        var index = ajaxModalSubmitEvents.indexOf(functionName);
        if (index >= 0) {
            ajaxModalSubmitEvents.splice(index, 1);
        }
    }
}
