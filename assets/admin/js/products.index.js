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
var modal = $('#ProductSetModal');

/**
 * Update selected comments status
 * @param status_id
 * @param el object
 */
function setProductsStatus(status_id, el) {

    var selection = grid.yiiGridView('getSelectedRows');
    if (checkSelected()) {
        yii.confirm($(el).data('confirm-info'), function () {
            $.ajax({
                url: common.url('/admin/shop/product/update-is-active'),
                type: 'POST',
                dataType: 'json',
                data: {
                    ids: selection,
                    'switch': status_id
                },
                success: function (data) {
                    //if (data.success) {
                    common.notify(data.message, 'success');
                    $.pjax.reload(pjax, {timeout: false});
                    //} else {
                    //     common.notify(data.message, 'error');
                    // }
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
        }, function () {
            return false;
        });
    }
    return false;
}


function updateProductsViews(el) {

    //var selection = grid.yiiGridView('getSelectedRows');
    var selection = checkSelected();
    if (selection) {
        yii.confirm($(el).data('confirm-info'), function () {
            $.ajax({
                url: common.url('/admin/shop/product/update-views'),
                type: 'POST',
                dataType: 'json',
                data: {ids: selection},
                success: function (data) {
                    if (data.success) {
                        common.notify(data.message, 'success');
                        $.pjax.reload(pjax, {timeout: false});
                    } else {
                        common.notify(data.message, 'error');
                    }
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
        }, function () {
            return false;
        });
    }
    return false;
}


function checkSelected() {
    var selection = grid.yiiGridView('getSelectedRows');
    if (!selection.length) {
        common.notify('Не выбрано не одного элемента!', 'warning');
        return false;
    }
    return selection;
}

modal.on('hide.bs.modal', function (e) {
    $(this).find('.modal-body').html('');
});

/**
 * Display window with all categories list.
 *
 * @param el_clicked
 */
function showCategoryAssignWindow(el_clicked) {
    var modalContainer = $('#ProductSetModal');
    var modalBody = modalContainer.find('.modal-body');
    var button = modalContainer.find('.modal-footer button');
    modalContainer.find('.modal-title').html($(el_clicked).data('title'));

    if (checkSelected()) {
        $.ajax({
            url: common.url('/admin/shop/product/render-category-assign-window'),
            success: function (data) {
                button.text('Назначить');
                button.bind({
                    click: function () {
                        ajax_save_set_categories();
                        button.unbind('click');
                    }
                });
                modalBody.html(data);
                modalContainer.modal('show');
            },
            beforeSend: function () {
                common.addLoader('Opening...');
            },
            complete: function () {
                common.removeLoader();
            }
        });
    }

}

function ajax_save_set_categories() {
    var selection = grid.yiiGridView('getSelectedRows');
    var checked = $("#CategoryAssignTreeDialog .jstree-checked");
    var ids = [];

    checked.each(function (key, el) {
        var id = $(el).attr('id').replace('node_', '').replace('_anchor', '');
        ids.push(id);

    });

    var selected = $('#CategoryAssignTreeDialog').jstree('get_selected')[0];
    var main_category = 0;
    if (selected) {
        main_category = selected.replace('node_', '');
    }
    $.ajax(common.url('/admin/shop/product/assign-categories'), {
        type: "POST",
        dataType: "json",
        data: {
            category_ids: ids,
            main_category: main_category,
            product_ids: selection
        },
        success: function (data) {
            if (data.success) {
                $('#alert-s').html('');
                $.pjax.reload(pjax, {timeout: false});
                common.notify(data.message, 'success');
                modal.modal('hide');

            } else {
                common.notify(data.message, 'error');
                $('#alert-s').html('<div class="alert alert-danger">' + data.message + '</div>');
            }

        }
    });
}


function ajax_save_copy() {
    var selection = grid.yiiGridView('getSelectedRows');
    var button = modal.find('.modal-footer button');
    $.ajax(common.url('/admin/shop/product/duplicate-products'), {
        type: "POST",
        dataType: 'json',
        data: {
            products: selection,
            duplicate: $('#ProductSetModal form').serialize()
        },
        success: function (data) {
            if (data.success) {
                //$.pjax.reload('#pjax-grid-product', {timeout: false});

                common.notify(data.message, 'success');
                button.unbind('click');
                modal.modal('hide');
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

function showDuplicateProductsWindow(el_clicked) {
    var modalContainer = $('#ProductSetModal');
    var modalBody = modalContainer.find('.modal-body');
    var button = modalContainer.find('.modal-footer button');
    modalContainer.find('.modal-title').html($(el_clicked).data('title'));

    if (checkSelected()) {
        $.ajax({
            url: common.url('/admin/shop/product/render-duplicate-products-window'),
            success: function (data) {
                button.text('Назначить');
                button.bind({
                    click: function () {
                        ajax_save_copy();
                        button.unbind('click');
                    }
                });
                modalBody.html(data);
                modalContainer.modal('show');
            },
            beforeSend: function () {
                common.addLoader('Opening...');
            },
            complete: function () {
                common.removeLoader();
            }
        });
    }
}

function setProductsPrice(el_clicked) {
    var modalBody = modal.find('.modal-body');
    var button = modal.find('.modal-footer button');
    modal.find('.modal-title').html($(el_clicked).data('title'));

    if (checkSelected()) {
        $.ajax({
            url: common.url('/admin/shop/product/render-products-price-window'),
            success: function (data) {
                button.text('Установить');
                button.bind({
                    click: function () {
                        ajax_save_set_prices();
                    }
                });
                modalBody.html(data);
                modal.modal('show');
            },
            beforeSend: function () {
                common.addLoader('Opening...');
            },
            complete: function () {
                common.removeLoader();
            }
        });
    }
}

function ajax_save_set_prices() {
    var selection = grid.yiiGridView('getSelectedRows');
    var button = modal.find('.modal-footer button');
    $.ajax(common.url('/admin/shop/product/set-products'), {
        type: "POST",
        dataType: 'json',
        data: {
            products: selection,
            data: $('#ProductSetModal form').serialize()
        },
        success: function (data) {
            if (data.success) {
                //$.pjax.reload('#pjax-grid-product', {timeout: false});

                common.notify(data.message, 'success');
                button.unbind('click');
                modal.modal('hide');
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


$(document).on("click", "#collapse-grid-filter button", function (event, k) {
    var data = $("#grid-product").yiiGridView("data");
    console.log(data.settings.filterUrl, data.settings.filterSelector);
    $.pjax({
        //url: data.settings.filterUrl,
        url: "/admin/shop/product",
        container: '#pjax-grid-product',
        type: "GET",
        push: false,
        timeout: false,
        scrollTo: false,
        data: $("#collapse-grid-filter input, #collapse-grid-filter select").serialize()
    });
    return false;
});

$(document).on("change", "#collapse-grid-filter #productsearch-type_id", function (event, k) {

    $.getJSON(common.url("/admin/shop/product/load-attributes?type_id=" + $(this).val()), function (response) {
        if (Object.keys(response).length > 0) {
            $("#filter-grid-attributes").html("");
            $.each(response, function (key, items) {
                $("#filter-grid-attributes").append("<div class=\"col-sm-3\"><div class=\"form-group\"><label for=\"" + items.inputId + "\">" + items.label + "</label><select class=\"custom-select\" id=\"" + items.inputId + "\" name=\"" + items.inputName + "\"></select></div></div>");
                $("#" + items.inputId).append($("<option>", {
                    value: "",
                    text: "—"
                }));

                $.each(items.options, function (i, option) {
                    $("#" + items.inputId).append($("<option>", {
                        value: option.key,
                        text: option.value
                    }));
                });
            });
        } else {
            $("#filter-grid-attributes").html("");
        }
    });
    return false;
});