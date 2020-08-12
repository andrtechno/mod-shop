// Init tree
/*
 $('#CategoryTreeFilter').bind('loaded.jstree', function (event, data) {
 //data.inst.open_all(0);
 }).delegate("a", "click", function (event) {
 try {
 var id = $(this).parent("li").attr('id').replace('CategoryTreeFilterNode_', '');
 } catch (err) {
 // 'All Categories' clicked
 var id = 0;
 }
 var obj = $('#product-grid .filters td')[0];
 $(obj).append('<input name="category" type="hidden" value="' + id + '">');
 $('#productsListGrid .filters :input').first().trigger('change');
 });
 */

var grid = $('#grid-product');
var pjax = '#pjax-grid-product';
var uiDialog = $('.ui-dialog');
/**
 * Update selected comments status
 * @param status_id
 */
function setProductsStatus(status_id, el) {
    $.ajax(common.url('/admin/shop/product/update-is-active'), {
        type: "post",
        dataType: "json",
        data: {
            _csrf: yii.getCsrfToken(),
            ids: grid.yiiGridView('getSelectedRows'),
            'switch': status_id
        },
        success: function (data) {
            common.notify(data.message, 'success');
            grid.yiiGridView('applyFilter');
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

function showCategoryAssignWindow2(el_clicked) {
    var modalContainer = $('#exampleModal');
    var modalBody = modalContainer.find('.modal-body');
    $.ajax({
        url: '/admin/shop/product/render-category-assign-window',
        success: function (data) {
            modalBody.html(data);
            modalContainer.modal('show');
        }
    });
    modalContainer.on('hidden.bs.modal', function (e) {
        modalBody.html('');
    })

}
/**
 * Display window with all categories list.
 *
 * @param el_clicked
 */
function showCategoryAssignWindow(el_clicked) {
    var selection = grid.yiiGridView('getSelectedRows');
    if (selection.length > 0) {
        var dialogSelector = "#set_categories_dialog";
        if ($(dialogSelector).length === 0) {
            var div = $('<div id="set_categories_dialog"/>');
            $(div).css('max-height', $(window).height() - 110 + 'px');
            $(div).attr('title', 'Назначить категории');
            $('body').append(div);
        }

        $(dialogSelector).load('/admin/shop/product/render-category-assign-window', {}, function () {
            uiDialog.position({my: 'center', at: 'center', of: window});
        });

        $(dialogSelector).dialog({
            dialogClass: 'assign-categories-modal',
            modal: true,
            resizable: false,
            responsive: true,
            width: 'auto',
            close: function () {
                $(this).remove();
                $('.assign-categories-modal').remove();
            },
            open: function () {
                $('.ui-widget-overlay').bind('click', function () {
                    uiDialog.remove();
                });
            },
            buttons: [{
                text: 'Назначить',
                'class': 'btn2 btn-primary2',
                click: function () {
                    var checked = $("#CategoryAssignTreeDialog .jstree-checked");
                    var ids = [];

                    checked.each(function (key, el) {
                        var id = $(el).attr('id').replace('node_', '').replace('_anchor', '');
                        ids.push(id);

                    });

                    if (checked.parent().length === 0) {
                        //$('#alert-s').html('<div class="alert alert-warning">На выбрана \'главная\' категория. Кликните на название категории, чтобы сделать ее главной.</div>');
                        //return;
                    }

                    //if (confirm($(el_clicked).data('confirm'))) {
                    $.ajax(common.url('/admin/shop/product/assign-categories'), {
                        type: "POST",
                        dataType: "json",
                        data: {
                            _csrf: yii.getCsrfToken(),
                            category_ids: ids,
                            main_category: checked.parent().attr('id').replace('node_', '').replace('_anchor', ''),
                            product_ids: selection
                        },
                        success: function (data) {
                            dialog.dialog('destroy').remove();
                            $.pjax.reload(pjax, {timeout: false});
                            common.notify(data.message, 'success');
                        },
                        error: function () {
                            $('#alert-s').html('<div class="alert alert-danger">Ошибка</div>');
                        }
                    });
                    //}
                },
            }, {
                text: 'Отмена',
                'class': 'btn2 btn-secondary2',
                click: function () {
                    $(this).dialog("close");
                }
            }]
        });
        uiDialog.position({my: 'center', at: 'center', of: window});
    } else {
        common.notify('Не выбрано не одного элемента!', 'warning');
    }
}

function showDuplicateProductsWindow() {
    var selection = grid.yiiGridView('getSelectedRows');
    if (selection.length > 0) {
        var dialogSelector = "#duplicate_products_dialog";

        if ($(dialogSelector).length === 0) {
            var div = $('<div id="duplicate_products_dialog"/>');
            $(div).attr('title', 'Копировать');
            $('body').append(div);
        }

        $(dialogSelector).load(common.url('/admin/shop/product/render-duplicate-products-window'), {}, function () {
            uiDialog.position({my: 'center', at: 'center', of: window});
        });
        var test;
        $(dialogSelector).dialog({
            modal: true,
            resizable: false,
            dialogClass: 'duplicate-modal',
            close: function () {
                $(this).remove();
                $('.duplicate-modal').remove();
            },
            open: function () {
                $('.ui-widget-overlay').bind('click', function () {
                    uiDialog.remove();
                });
            },
            buttons: [{
                text: 'Копировать',
                'class': 'btn btn-primary',
                click: function () {
                    console.log('getSelectedRows',grid.yiiGridView('getSelectedRows'));
                    $.ajax(common.url('/admin/shop/product/duplicate-products'), {
                        type: "post",
                        dataType: 'json',
                        data: {
                            _csrf: yii.getCsrfToken(),
                            products: selection,
                            duplicate: $("#duplicate_products_dialog form").serialize()
                        },
                        success: function (data) {

                            if (data.success) {
                                //uiDialog.remove();
                                uiDialog.dialog('close');
                                common.notify(data.message, 'success');
                                $.pjax.reload(pjax, {timeout: false});
                            } else {
                                common.notify(data.message, 'error');
                            }
                        },
                        error: function () {
                            common.notify("Ошибка", 'error');
                        }
                    });
                }
            },
                {
                    text: 'Отмена',
                    'class': 'btn btn-secondary',
                    click: function () {
                        $(this).dialog("close");
                    }
                }]
        });
        uiDialog.position({my: 'center', at: 'center', of: window});
    } else {
        common.notify('Не выбрано не одного элемента!', 'warning');
    }
}


function setProductsPrice() {
    var selection = grid.yiiGridView('getSelectedRows');
    console.log(selection);
    if (selection.length > 0) {
        var dialogSelector = "#prices_products_dialog";
        if ($(dialogSelector).length === 0) {
            var div = $('<div id="prices_products_dialog"/>');
            $(div).attr('title', 'Установить цену');
            $('body', document).append(div);
        } else {
            console.log('already dialog data');
            return;
        }

        $(dialogSelector).load(common.url('/admin/shop/product/render-products-price-window'), {}, function () {
            uiDialog.position({my: 'center', at: 'center', of: window});
        });

        $(dialogSelector).dialog({
            modal: true,
            dialogClass: 'set-prices-modal',
            //appendTo: grid,
            resizable: false,
            responsive: true,
            draggable: false,
            close: function () {
                $(this).remove();
                $('.set-prices-modal').remove();
            },
            open: function () {
                $('.ui-widget-overlay').bind('click', function () {
                    uiDialog.remove();
                });
            },
            buttons: [{
                text: 'Установить',
                //'class': 'btn btn-primary',
                click: function () {

                    $.ajax(common.url('/admin/shop/product/set-products'), {
                        type: "POST",
                        dataType: 'json',
                        data: {
                            products: selection,
                            data: $("#prices_products_dialog form").serialize()
                        },
                        success: function (data) {
                            if (data.success) {
                                //$(dialogSelector).dialog('close');
                                $.pjax.reload('#pjax-grid-product', {timeout: false});
                                //$(dialogSelector).dialog('close').remove();
                                //$(dialogSelector).dialog('destroy').remove();
                                //grid.yiiGridView('applyFilter');
                                common.notify(data.message, 'success');
                            } else {
                                common.notify(data.message, 'error');
                            }

                        },
                        error: function () {
                            common.notify("Ошибка", 'error');
                        }
                    });

                }
            }, {
                text: 'Отмена',
                //'class': 'btn btn-secondary',
                click: function () {
                    $(dialogSelector).dialog("close");
                }
            }]
        });
        uiDialog.position({my: 'center', at: 'center', of: window});
    } else {
        common.notify('Не выбрано не одного элемента!', 'warning');
    }
}

// Хак для отправки с диалогового окна формы через ENTER
// Оправка происходит для первый кнопки.
/*$(function () {
 $.extend($.ui.dialog.prototype.options, {
 create: function () {
 var $this = $(this);
 // focus first button and bind enter to it
 $this.parent().find('.ui-dialog-buttonpane button:first').focus();
 $this.keypress(function (e) {
 if (e.keyCode === $.ui.keyCode.ENTER) {
 $this.parent().find('.ui-dialog-buttonpane button:first').click();
 return false;
 }
 });
 }
 });
 });*/
