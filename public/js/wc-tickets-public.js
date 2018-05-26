jQuery(document).ready(function ($) {
    // Закрытие тикета
    $(document).on('click', '[data-action="close-ticket"]', function (event) {
        event.preventDefault();
        var button = $(this);
        var buttonData = {
            action: "wct_close_ticket",
            ticket: button.data('ticket'),
            nonce: button.data('nonce')
        }
        $.ajax({
            type: 'POST',
            url: wcTickets.ajaxurl,
            data: buttonData,
            dataType: 'json',
            cache: false,
            beforeSend: function () {
                $(".wctf-overlay").fadeIn();
            },
            success: function (data) {
                if (data.status) {
                    button.addClass('closed').text(data.message).removeAttr('data-toggle').removeAttr('data-original-title');
                    setTimeout(function () {
                        document.location.reload();
                    }, 2000);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            },
            complete: function () {
                $(".wctf-overlay").fadeOut();
            }
        });
    });

    // tooltips
    $('[data-toggle="tooltip"]').tooltip()


    $("#wct-parent-ticket").change(function (e) {
        if ($(this).val() != '') {
            $("#wct-subject").parent().hide();
            $("#wct-subject").val('New');
        } else {
            $("#wct-subject").parent().show();
        }
    });

    $(document).on('change', '#ticket-file-select', function (event) {
        $(this).parents('label').after("<span>" + $(this).prop('files')[0]['name'] + "</span>")
    });
    // форма тикетов
    $("#ticket-form").submit(function (event) {
        event.preventDefault();

        var el = $(this);
        // создадим объект данных формы
        var formData = new FormData(this);
        var file = $("#ticket-file-select").prop('files')[0];
        if (file != undefined) formData.append('file', file);

        $.ajax({
            type: 'POST',
            url: wcTickets.ajaxurl,
            //data: JSON.stringify(parameters),
            data: formData,
            contentType: false,
            dataType: 'json',
            cache: false,
            async: true,
            processData: false,
            enctype: 'multipart/form-data',
            beforeSend: function () {
                el.find('.preloader').fadeIn();
            },
            success: function (data) {
                try {
                    if (data.status) {
                        el.find(".message-box").html('<div class="alert alert-success" role="alert">' + data.message + '</div>')
                    }
                } catch (e) {
                    console.log('Ошибка ' + e.name + ":" + e.message + "\n" + e.stack);
                }


            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('error...', xhr);
                //error logging
            },
            complete: function () {
                el.find('.preloader').fadeOut();
                setTimeout(function () {
                    document.location.reload();
                }, 2000);
            }
        });

        return false;

    });
});
