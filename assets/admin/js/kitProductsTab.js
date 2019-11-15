
/**
 * @param el
 * @return {Boolean}
 * @constructor
 */
function addKitProduct(el) {
    var img = $(el).parent().parent().parent().find('.image img').attr("src");
    var product = $(el).parent().parent().parent().find("a.product-name");
    var product_id = product.data('id');
    var product_name = product.text();
    var trclass = "kitProductLine" + product_id;
    if ($("." + trclass).length == 0)
    {
        $("#kitProductsTable").append("<tr class=" + trclass + "><td class=\"image text-center\"><img class=\"img-thumbnail\" src=\"" + img + "\" /></td><td>" + product_name + "</td><td class=\"text-center\">" +
                "<a href='#' class='btn btn-sm btn-danger' onclick='return $(this).parent().parent().remove();'>" + common.message.delete + "</a>" +
                "<input type='hidden' value='" + product_id + "' name='kitProductId[]'>" +
                "</td></tr>");
    }

    return false;
}
