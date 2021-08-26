var jsTree = $('#jsTree_TypeCategoryTree');

// Connect lists
$("#box2View").delegate('option', 'dblclick', function () {
    var clon = $(this).clone();
    $(this).remove();
    $(clon).appendTo($("#box1View"));
});
$("#box1View").delegate('option', 'dblclick', function () {
    var clon = $(this).clone();
    $(this).remove();
    $(clon).appendTo($("#box2View"));
});


// Process checked categories
$("#ProductTypeForm").submit(function () {

    $("#box2View option").prop('selected', true);
    var checked = $("#TypeCategoryTree li a.jstree-checked");
    checked.each(function (i, el) {
        var id = $(el).attr("id").replace('node_', '').replace('_anchor', '');
        $("#ProductTypeForm").append('<input type="hidden" name="categories[]" value="' + id + '" />');
    });
});


// Check node

;(function ($) {
    $.fn.checkNode = function (id) {
        $(this).bind('loaded.jstree', function () {
            $(this).jstree('check_node', 'node_' + id);
        });
    };
})(jQuery);


jsTree.on('loaded.jstree', function () {
    $(this).jstree('select_node', 'node_'+$('#main_category').val());
});
/*
 // Process main category
 $('#jsTree_TypeCategoryTree22').delegate("a", "click", function (event) {

 var node_id = $(this).attr('id').replace('_anchor', '');
 var id = node_id.replace('node_', '');

 $('#jsTree_TypeCategoryTree').jstree(true).check_node(node_id);
 //  $('#ShopTypeCategoryTree').jstree(true).select_node($(this).attr('id').replace('_anchor', ''));

 console.log(id);
 $('#main_category').val(id);
 });



 $('#jsTree_TypeCategoryTree').on("check_node.jstree", function (node, selected, event) {
 console.log(selected.node.id);

 console.log(node);
 });*/

jsTree.on("select_node.jstree", function (node, selected, event) {
    $(this).jstree('check_node', selected.node.id);
    $('#main_category').val(selected.node.id.replace('node_', ''));
});