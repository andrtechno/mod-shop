// Products controller. Variations tab.

$(document).ready(function(){
    $("#addAttribute").click(function(){
        if($('#variantAttribute'+$('#variantAttribute').val()).length == 0)
        {
            $.ajax({
                url: "render-variant-table",
                cache: false,
                data: {attr_id: $('#variantAttribute').val()},
                dataType: "html",
                success: function(data){
                    $('#variantsData').append(data);
                }
            });
        }
    });
});

/**
 * @param el clicked link
 */
function cloneVariantRow(el){
    var tableId = $(el).attr('rel');
    var baseRow = $(tableId + ' tbody tr')[0];
    $(baseRow).clone().removeClass('baseRow').show().appendTo($(tableId + ' tbody'));
    return false;
}

/**
 * @param el clicked link
 */
function deleteVariantRow(el){
    var table = el.parent().parent().parent().parent();
    el.parent().parent().remove();

    // Check if table has any rows and remove table.
    if($(table).find('tbody tr').length == 0)
    {
        $(table).remove();
    }
    return false;
}

/**
 * Add option to attribut
 * @param link_clicked
 */
function addNewOption(link_clicked){
    var attr_id = $(link_clicked).attr('rel');
    var name    = prompt("Укажите значение опции");

    if(name != null){
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: common.url('/admin/shop/product/add-option-to-attribute'),
            data: {
                attr_id: attr_id,
                value: name
            },
            success: function(data){
                $('#variantAttribute'+attr_id+' select.options_list, #'+$(link_clicked).data('name')).each(function(i,el){
                    $(el).prepend($("<option/>", {
                        value: data.id,
                        text: name
                    }));

                    //var newOption = new Option(name, data.id, false, false);
                    //$(el).prepend(newOption).trigger('change');
                });



                common.notify(data.message,'success');
                if( $(link_clicked).data('name') ){

                  //  $('#' + $(link_clicked).data('name')).trigger("liszt:updated");


                }
            }
        });
    }

    return false;
}
