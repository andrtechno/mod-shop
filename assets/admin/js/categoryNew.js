/**
 * Scripts for js tree
 */

// Bind tree events
$('#ShopCategoryTree').bind('loaded.jstree', function (event, data) {
    // Open all nodes by default

}).delegate("a", "click", function (event) {
    // On link click get parent li ID and redirect to category update action
    var id = $(this).parent("li").attr('id').replace('ShopCategoryTreeNode_', '');
  alert(id);
   // window.location = '/admin/shop/category/update/id/' + id;
}).bind("move_node.jstree", function (e, data) {
console.log(data);
var findObjId = data.node.li_attr.id;
var ObjId = findObjId.replace('ShopCategoryTreeNode_','');
   // data.rslt.o.each(function (i) {
        $.ajax({
            async : false,
            type: 'GET',
            url: "/admin/shop/category/moveNode",
            data : {
             //   "id" : ObjId,
               // "ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace('ShopCategoryTreeNode_',''),
                "position" : data.position
            }
        });
   // });
});

function CategoryRedirectToFront(obj)
{
    var id = $(obj).attr("id").replace('ShopCategoryTreeNode_','');
    window.open('/admin/shop/category/redirect/id/'+id, '_blank');
}

function CategoryRedirectToAdminProducts(obj)
{
    var id = $(obj).attr("id").replace('ShopCategoryTreeNode_','');
    window.location = '/admin/shop/products/?category='+id;
}

function CategoryRedirectToParent(obj)
{
    var id = $(obj).attr("id").replace('ShopCategoryTreeNode_','');
    window.location = '/admin/shop/category/create?parent_id='+id;
}

function  CategoryRename(obj){
$('#ShopCategoryTree').bind("remove.jstree", function (e, data) {
$(e).click(function(){
   alert(';c'); 
});
        $.ajax({
            async : false,
            type: 'GET',
            url: "/admin/shop/category/deleteNode",
            data : {
                "id" : $(this).attr("id").replace('ShopCategoryTreeNode_',''),
                "ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace('ShopCategoryTreeNode_','')
               // "position" : data.rslt.cp + i
            }
        //            success : function (r) {
        //            }
        });

});
  //  $(obj).remove();
}