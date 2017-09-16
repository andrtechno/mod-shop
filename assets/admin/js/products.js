
// Process checked categories

$("#Product").submit(function () {
    var checked = $("#jsTree_CategoryTree li a.jstree-checked");
    checked.each(function (i, el) {
        var id = $(el).attr("id").replace('node_', '').replace('_anchor', '');
        console.log(id);
        $("#Product").append('<input type="hidden" name="categories[]" value="' + id + '" />');
    });

});
//$('#ShopCategoryTree').delegate("a", "click", function (event) {
//	$('#ShopCategoryTree').jstree('checkbox').check_node($(this));
//	var id = $(this).parent("li").attr('id').replace('ShopCategoryTreeNode_', '');
//});

;
(function ($) {
    $.fn.checkNode = function (id) {

        $(this).bind('loaded.jstree', function () {
            $(this).jstree('check_node', 'node_' + id);
        });
    };
})(jQuery);

// On change `use configurations` select - load available attributes
$('#product-use_configurations, #product-type_id').change(function () {
    var attrs_block = $('#availableAttributes');
    var type_id = $('#shopproduct-type_id').val();
    attrs_block.html('');

    if ($('#product-use_configurations').val() == '0')
        return;

    $.getJSON('/admin/shop/default/load-configurable-options?type_id=' + type_id, function (data) {
        var items = [];
        if (data.status === 'success') {
            $.each(data.response, function (key, option) {
                items.push('<li><label class="control-label"><input type="checkbox" class="check" name="Product[configurable_attributes][]" value="' + option.id + '" name=""> ' + option.title + '</label></li>');
            });
            $('#availableAttributes').removeClass('hidden');
            $('<ul/>', {
                'class': 'list-unstyled',
                'style': 'margin-left:20px',
                html: items.join('')
            }).appendTo(attrs_block);
        } else {
            $('#availableAttributes').html('<div class="alert alert-danger">' + data.message + '</div>').removeClass('hidden');
        }
    });
});