/**
 * @param product_id ИД обэекта
 */
function notify(product_id) {
    $('body').append($('<div/>', {
        'id': 'dialog'
    }));
    $('#dialog').dialog({
        title: 'Сообщить о появлении',
        modal: true,
        resizable: false,
        draggable: false,
        responsive: true,
        open: function () {
            var that = this;
            $.ajax({
                url: common.url('/shop/notify'),
                data: {product_id: product_id},
                dataType: 'json',
                type: 'POST',
                success: function (data) {
                    $(that).html(data.data);
                }
            });
        },
        close: function () {
            $('#dialog').remove();
            $('a.btn-danger').removeClass(':focus');
        },
        buttons: [{
            text: common.message.cancel,
            'class': 'btn btn-link',
            click: function () {
                $(this).remove();
            }
        }, {
            text: common.message.send,
            'class': 'btn btn-primary',
            click: function () {

                $.ajax({
                    url: common.url('/shop/notify'),
                    data: $('#notify-form').serialize(),
                    dataType: 'json',
                    type: 'POST',
                    success: function (data) {
                        if (data.status === 'OK') {
                            $('#dialog').remove();
                            //common.report(data.message);
                            common.notify(data.message, 'success');
                        } else {
                            $('#dialog').html(data.data);
                        }
                    }
                });

                /*common.ajax('/notify', $('#notify-form').serialize(), function (data, textStatus, xhr) {
                    if (data.status === 'OK') {
                        $('#dialog').remove();
                        //common.report(data.message);
                        common.notify(data.message, 'success');
                    } else {
                        $('#dialog').html(data.data);
                    }
                }, 'json');*/
            }
        }]
    });
}