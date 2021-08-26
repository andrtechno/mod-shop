$(function () {
    var tr = $(".optionsEditTable tbody tr");
    // Add new row
    $(document).on('click','#add-option-attribute',function () {

        var option_name = Math.random();
        var row = $(".copyMe").clone().removeClass('copyMe');

        //if (tr.length === 1) {
        //    tr.addClass('d-none');
        //}

        console.log('clicked',row);
        row.prependTo(".optionsEditTable > tbody");
        row.find(".value").each(function (i, el) {
            $(el).attr('name', 'options[' + option_name + '][]');
        });
        /*$.ajax({
            type:'GET',
            url:'/admin/shop/attribute/test',
            success:function (data) {
                console.log(row.find('td:nth-child(3)'));
                //row.find('td:nth-child(3)').html(data);
            }
        });*/

        return false;
    });





    // Delete row
    $(".optionsEditTable").delegate(".delete-option-attribute", "click", function () {
        $(this).parent().parent().remove();

        if (tr.length === 1) {
            tr.removeClass('d-none');
        }
        return false;
    });

    // On change type toggle options tab
    $("#attribute-type").change(function () {
        toggleOptionsTab($(this));
    });
    $("#attribute-type").change();


    $("form#Attribute").submit(function () {
        var el = $("#attribute-type");
        var array = [3, 4, 5,6];
        //if(array.indexOf(parseInt($(el).val())) != -1){
        if ($(el).val() !== 3 && $(el).val() !== 4 && $(el).val() !== 5 && $(el).val() !== 6) {
            $(".optionsEditTable").remove();
        }
        return true;
    });

    /**
     * Show/hide options tab on type change
     * @param el
     */
    function toggleOptionsTab(el) {
        var array = [3, 4, 5,6,9];
        var optionsTab = $("#attributes-tabs li")[1];
        console.log($(el).val());
        // Show options tab when type is dropdown or select
        if(array.indexOf(parseInt($(el).val())) != -1){
            $(optionsTab).show();
            $(".field-attribute-use_in_filter").show();
            $(".field-attribute-select_many").show();
        } else {
            $(optionsTab).hide();
            $(".field-attribute-use_in_filter").hide();
            $(".field-attribute-select_many").hide();
        }
    }

});