

// Init tree
$('#ShopCategoryTreeFilter').bind('loaded.jstree', function (event, data) {
    //data.inst.open_all(0);
}).delegate("a", "click", function (event) {
    try {
        var id = $(this).parent("li").attr('id').replace('ShopCategoryTreeFilterNode_', '');
    } catch (err) {
        // 'All Categories' clicked
        var id = 0;
    }
    var obj = $('#shopproduct-grid .filters td')[0];
    $(obj).append('<input name="category" type="hidden" value="' + id + '">');
    $('#productsListGrid .filters :input').first().trigger('change');
});



/**
 * Update selected comments status
 * @param status_id
 */
function setProductsStatus(status_id, el)
{
    $.ajax('/admin/shop/products/updateIsActive', {
        type: "post",
        data: {
            token: $(el).attr('data-token'),
            ids: $.fn.yiiGridView.getSelection('shopproduct-grid'),
            'switch': status_id
        },
        success: function (data) {
            $.fn.yiiGridView.update('shopproduct-grid');
            common.notify(data,'success');
        },
        error: function (XHR, textStatus, errorThrown) {
            var err = '';
            switch (textStatus) {
                case 'timeout':
                    err = 'The request timed out!';
                    break;
                case 'parsererror':
                    err = 'Parser error!';
                    break;
                case 'error':
                    if (XHR.status && !/^\s*$/.test(XHR.status))
                        err = 'Error ' + XHR.status;
                    else
                        err = 'Error';
                    if (XHR.responseText && !/^\s*$/.test(XHR.responseText))
                        err = err + ': ' + XHR.responseText;
                    break;
            }
            alert(err);
        }
    });
    return false;
}

/**
 * Display window with all categories list.
 *
 * @param el_clicked
 */
function showCategoryAssignWindow(el_clicked)
{
    if ($("#set_categories_dialog").length == 0)
    {
        var div = $('<div id="set_categories_dialog"/>');
        $(div).css('max-height', $(window).height() - 110 + 'px');
        $(div).attr('title', 'Назначить категории');
        $('body').append(div);
    }

    $('body').scrollTop(30);

    var dialog = $("#set_categories_dialog");
    dialog.load('/admin/shop/products/renderCategoryAssignWindow');

    dialog.dialog({
      //  position:'top',
        modal: true,
        resizable: false,
        responsive: true,
        width: 'auto',
        close: function () {
            $(this).remove();
        },
                open:function(){
        $('.ui-dialog').position({
                  my: 'center',
                  at: 'center',
                  of: window,
                  collision: 'fit'
            });

            $('.ui-widget-overlay').bind('click', function() {
                $('#set_categories_dialog').dialog('close');
            });

        },
        buttons: [{
                text: 'Назначить',
                'class': 'btn btn-primary',
                click: function () {
                    var checked = $("#CategoryAssignTreeDialog .jstree-checked");
                    var ids = [];

                    checked.each(function (key, el) {
                        var id = $(el).attr('id').replace('node_', '').replace('_anchor', '');
                        ids.push(id);

                    });

                    if ($("#CategoryAssignTreeDialog .jstree-clicked").parent().length == 0)
                    {
                        // $.jGrowl("На выбрана 'главная' категория. Кликните на название категории, чтобы сделать ее главной.", {
                        //     position: "bottom-right"
                        // });
                        $('#alert-s').html('<div class="alert alert-warning">На выбрана \'главная\' категория. Кликните на название категории, чтобы сделать ее главной.</div>');
                        return;
                    }

                    $.ajax('/admin/shop/products/assignCategories', {
                        type: "post",
                        data: {
                            token: common.token,
                            category_ids: ids,
                            main_category: $("#CategoryAssignTreeDialog .jstree-clicked").parent().attr('id').replace('node_', '').replace('_anchor', ''),
                            product_ids: $.fn.yiiGridView.getSelection('shopproduct-grid')
                        },
                        success: function () {
                            $(dialog).dialog("close");
                            $.fn.yiiGridView.update('shopproduct-grid');

                        },
                        error: function () {
                            $('#alert-s').html('<div class="alert alert-danger">Ошибка</div>');
                        }
                    });
                },
            }, {
                text: common.message.cancel,
                'class': 'btn btn-default',
                click: function () {
                    $(this).dialog("close");
                }
            }]
    });
}

function showDuplicateProductsWindow(link_clicked) {
    if ($("#duplicate_products_dialog").length == 0) {
        var div = $('<div id="duplicate_products_dialog"/>');
        $(div).attr('title', 'Копировать');
        $('body').append(div);
    }

    var dialog = $("#duplicate_products_dialog");
    dialog.load('/admin/shop/products/renderDuplicateProductsWindow');

    dialog.dialog({
        modal: true,
        resizable: false,
        buttons: [{
                text: 'Копировать',
                'class': 'btn btn-primary',
                click: function () {
                    $.ajax('/admin/shop/products/duplicateProducts', {
                        type: "post",
                        data: {
                            token: $(link_clicked).attr('data-token'),
                            products: $.fn.yiiGridView.getSelection('shopproduct-grid'),
                            duplicate: $("#duplicate_products_dialog form").serialize()
                        },
                        success: function (data) {
                            $(dialog).dialog("close");
                            common.notify("Изменения сохранены. <a href='" + data + "'>Просмотреть копии продуктов.</a>",'success');
                            $.fn.yiiGridView.update('shopproduct-grid');
                        },
                        error: function () {
                            common.notify("Ошибка",'error');
                        }
                    });
                }
            },
            {
                text: common.message.cancel,
                'class': 'btn btn-default',
                click: function () {
                    $(this).dialog("close");
                }
            }]
    });
}







function setProductsPrice(link_clicked) {
    if ($("#prices_products_dialog").length == 0) {
        var div = $('<div id="prices_products_dialog"/>');
        $(div).attr('title', 'Установить цену');
        $('body').append(div);
    }

    var dialog = $("#prices_products_dialog");
    dialog.load('/admin/shop/products/renderProductsPriceWindow');

    dialog.dialog({
        modal: true,
        resizable: false,
        buttons: [{
                text: 'Установить',
                'class': 'btn btn-primary',
                click: function () {

                    $.ajax('/admin/shop/products/setProducts', {
                        type: "post",
                        data: {
                            token: $(link_clicked).attr('data-token'),
                            products: $.fn.yiiGridView.getSelection('shopproduct-grid'),
                            data: $("#prices_products_dialog form").serialize()
                        },
                        success: function (data) {
                            $(dialog).dialog("close");
                            $.fn.yiiGridView.update('shopproduct-grid');

                        },
                        error: function () {
                            common.notify("Ошибка",'error');
                        }
                    });

                }
            }, {
                text: common.message.cancel,
                'class': 'btn btn-default',
                click: function () {
                    $(this).dialog("close");
                }
            }]
    });
}

// Хак для отправки с диалогового окна формы через ENTER
// Оправка происходит для первый кнопки.
$(function () {
    $.extend($.ui.dialog.prototype.options, {
        create: function () {
            var $this = $(this);
            // focus first button and bind enter to it
            $this.parent().find('.ui-dialog-buttonpane button:first').focus();
            $this.keypress(function (e) {
                if (e.keyCode == $.ui.keyCode.ENTER) {
                    $this.parent().find('.ui-dialog-buttonpane button:first').click();
                    return false;
                }
            });
        }
    });
});