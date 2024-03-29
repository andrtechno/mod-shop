/**
 * Script for configurable products and variants
 */

// Disable all dropdowns exclude first

$('.eavData:not(:first)').attr('disabled', 'disabled');

$(document).on('change','.eavData',function () {
    $('#configurable_id').val(0);
    if ($(this).val() === '---' || $(this).val() === '0') {
        recalculateProductPrice(this);
        // If selected empty - reset all next dropdowns
        $('.eavData').nextAllData(this).each(function () {
            $(this).find('option:first').attr('selected', 'selected');
            $(this).attr('disabled', 'disabled');
        });
        return;
    }

    var val = pconfPrepArray($(this).val().split('/'));

    // Disable all next
    $('.eavData').nextAllData(this).attr('disabled', 'disabled');
    // Activate first closest
    $('.eavData').nextAllData(this).first().removeAttr('disabled');

    $('.eavData').nextAllData(this).each(function () {
        // Reset current selection
        $(this).find('option:first').attr('selected', 'selected');

        $(this).find('option').each(function () {
            var optionVals = pconfPrepArray($(this).val().split('/'));
            var option = this;

            $(option).hide();
            // Check if one of previous values are present in current option
            $(val).each(function (i, el) {
                if (optionVals.contains(el) || $(option).val() === '0') {
                    $(option).show();
                }
            });

        });
    });
});

// Change price on last dropdown change
$('.eavData:last').change(function () {
    var temp = '';
    $('.eavData').each(function () {
        temp = temp + $(this).val();
    });

    temp = pconfPrepArray(temp.split('/'));

    if (temp.length > 1) {
        var productId = parseInt(find_duplicates(temp)[0]);
    } else {
        var productId = temp[0];
    }

    if (productPrices[productId] !== undefined) {
        $('#configurable_id').val(productId);
    }

    recalculateProductPrice(this);
});


// Process product variants.
// Calculate prices.
$(document).ready(function () {
    //$(document).on('change','.variantData',function () {
    //    recalculateProductPrice(this);
    //});
    $(document).on('click','.variantData input',function () {
        recalculateProductPrice(this);
    });

    $(document).on('click','.eavData2 input',function () {
        recalculateProductPrice2(this);
    });
});
function recalculateProductPrice2(el_clicked) {
    var id = $(el_clicked).val();
    var form = $(el_clicked).closest('form');
    var priceInput = form.find('input[name="product_price"]');
    var formData = form.serialize();
    var data = getFormData(form);
    var result = parseFloat(priceInput.val());

    form.find('input[name="configurable_id"]').val(id);

    $.ajax({
        url: common.url('/product/' + id + '/calculate-price'),
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function (response) {
            console.log(response);
            $('#productPrice').html(response.price);
        }
    });

    return false;
}
/**
 * Recalculate product price on change variant or configurable options.
 * Sum product price + variant prices + configurable prices.
 */
function recalculateProductPrice(el_clicked) {
    var id = $(el_clicked).data('product_id');
    console.log(el_clicked);
    var form = $(el_clicked).closest('form');
    //var form = $('#form-add-cart-'+$(el_clicked).data('product_id'));
    var priceInput = form.find('input[name="product_price"]');
    var formData = form.serialize();
    var data = getFormData(form);
    var result = parseFloat(priceInput.val());




    $.ajax({
        url:common.url('/product/'+id+'/calculate-price'),
        type:'POST',
        dataType:'json',
        data:formData,
        success:function(response){
            console.log(response);
            $('#productPrice').html(response.price);
        }
    });


//console.log('price: '+result);
return false;
    // Update price

    if (typeof(productPrices) !== "undefined" && productPrices[$('#configurable_id-'+id).val()] !== undefined) {
        result = result + parseFloat(productPrices[$('#configurable_id-'+id).val()]);
        console.log('use_configurations');
        if ($("#use_configurations-"+id).val() === '1') { // Pan, comment this.

            result = result - priceInput.val();
        }
    }

    $('.variantData').each(function () {

        //if($(this).is('input:radio')==true && $(this).attr('checked')!='checked'){
        //    return;
        //}

        var variant_id = $(this).val();
        if (jsVariantsData[variant_id]) {
            if (jsVariantsData[variant_id].price_type === "1") {
                // Price type is percent
                result = result + (result / 100 * parseFloat(jsVariantsData[variant_id].price));
            } else {
                result = result + parseFloat(jsVariantsData[variant_id].price);
            }
        }

    });

    // Apply current currency



   // result = result * parseFloat($('#currency_rate').val());
    console.log('variantData',result);
    $('#productPrice').html(price_format(result));
}

/**
 * Find all next object in DOM
 * Usage $('.selector').nextAllData(this).attr(...)
 * @param startFrom
 */
jQuery.fn.nextAllData = function (startFrom) {
    var selectedObjects = this;
    var result = [];
    selectedObjects.each(function (i) {
        if (this === startFrom) {
            result = selectedObjects.slice(i + 1);
        }
    });
    return result;
};
function getFormData($form){
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function(n, i){
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}
/**
 * Check if array contains value
 * @param obj
 */
Array.prototype.contains = function (obj) {
    var i = this.length;
    while (i--) {
        if (this[i] === obj) {
            return true;
        }
    }
    return false;
};

/**
 * Find duplicates in array
 * @param arr
 */
function find_duplicates(arr) {
    var len = arr.length,
        out = [],
        counts = {};

    for (var i = 0; i < len; i++) {
        var item = arr[i];
        var count = counts[item];
        counts[item] = counts[item] >= 1 ? counts[item] + 1 : 1;
    }

    for (var item in counts) {
        if (counts[item] > 1)
            out.push(item);
    }

    return out;
}

/**
 * Remove from array empty values and '---'
 * @param arr
 */
function pconfPrepArray(arr) {
    $.each(arr, function (i, v) {
        if (v === '' || v === '---' || v === '0') {
            arr.splice(i, 1);
        }
    });
    return arr;
}
