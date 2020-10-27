(function ($) {
    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
}(jQuery));

$(function () {
    var selector = $('#filters .card-collapse');
    selector.collapse({
        toggle: false
    });
    var panels = $.cookie();

    for (var panel in panels) {
        //console.log(panel);
        if (panel) {
            var panelSelector = $('#' + panel);
            if (panelSelector) {
                if (panelSelector.hasClass('card-collapse')) {
                    if ($.cookie(panel) === '1') {
                        panelSelector.collapse('show');
                    } else {
                        panelSelector.collapse('hide');
                    }
                }
            }
        }
    }

    selector.on('show.bs.collapse', function () {
        var active = $(this).attr('id');
        $.cookie(active, '1');

    });

    selector.on('hide.bs.collapse', function () {
        var active = $(this).attr('id');
        $.cookie(active, null);
    });
});


var form = $('#filter-form');
var sortForm = $('#sorting-form');
var ajaxSelector = $('#listview-ajax');
var containerFilterCurrent = $('#ajax_filter_current');
var xhrFilters;
var flagDeletePrices = false;
var global_url;


function filter_ajax(e, objects) {
    if (xhrFilters && xhrFilters.readyState !== 4) {
        xhrFilters.onreadystatechange = null;
        xhrFilters.abort();
        ajaxSelector.removeClass('loading');
    }

    //if (url === undefined) {
    var url = formattedURL(e, objects);
    //var url = current_url;
   // }

    xhrFilters = $.ajax({
        dataType: "json",
        url: url,
        type: 'POST',
        headers: {
            "filter-ajax": true
        },
        data: $('#filter-form input[type="checkbox"]').serialize(),
        success: function (data) {
            ajaxSelector.html(data.items).toggleClass('loading');
            form.attr('action', data.currentUrl);
            containerFilterCurrent.html(data.currentFiltersData).removeClass('loading');
            history.pushState(null, $('title').text(), data.currentUrl);
            console.log('success filter_ajax');
        },
        beforeSend: function () {
            ajaxSelector.toggleClass('loading');
        }
    });
}


/*
 var xhrCurrentFilter;
 function currentFilters(url) {
 var containerFilterCurrent = $('#ajax_filter_current');
 if (xhrCurrentFilter && xhrCurrentFilter.readyState !== 4) {
 xhrCurrentFilter.onreadystatechange = null;
 xhrCurrentFilter.abort();
 }

 xhrCurrentFilter = $.ajax({
 type: 'GET',
 url: url,
 data:{render:'active-filters'},
 beforeSend: function () {
 containerFilterCurrent.addClass('loading');
 },
 success: function (data) {
 containerFilterCurrent.html(data).removeClass('loading');
 $('#filter-form').attr('action',data.full_url);
 }
 });
 }
 */
function getSerializeObjects() {

    var formObject = form.serializeObject();
    return $.extend(formObject, sortForm.serializeObject())
}

function getSerialize() {

    return $.extend(form.serialize(), sortForm.serialize())
}

function formattedURL(e, objects) {
    var uri = current_url;
    var valuesList;
    delete objects[yii.getCsrfParam()];
    //delete objects.min_price;
    //delete objects.max_price;


    $.each(objects, function (name, values) {
        if (values !== '') {
            //var matches = name.match(/filter\[([a-zA-Z0-9-_]+)\]\[]|/i);
            var matches = name.match(/filter\[([a-zA-Z0-9-_]+)\]\[]/i);


            valuesList = (values instanceof Array) ? '/' + values.join(',') : '/' + values;
           // if (e.type !== 'slidestop') {
                uri += ((matches) ? '/' + matches[1] : '/' + name)+valuesList;
           // }


            /*if (values instanceof Array) {
             uri += '/' + values.join(',');
             } else {
             uri += '/' + values;

             }*/

        }
    });


   // console.log(uri, 'formattedURL', objects);
    return uri;
}


$(function () {

    $(document).on('click', '#sorter-block button', function (e) {
        // console.log($(this).val());
        if ($(this).val() !== '') {

        }
        e.preventDefault();
        return false;
    });
    $(document).on('click', '#filter-current a2', function (e) {

        console.log('click current filter close');
        e.preventDefault();
        return false;
    });

    $(document).on('change', '#filter-form input[type="checkbox"]', function (e) {

        flagDeletePrices = true;
        var objects = getSerializeObjects();
        if (flagDeletePrices) {
            //delete objects.min_price;
            // delete objects.max_price;
        }

        //$.fn.yiiListView.update('shop-products', {url: formattedURL(objects)});
        if(objects['filter[price][]'] == undefined){

        }


     //   console.log(formattedURL(e, getSerializeObjects()));



        //delete objects['filter[price][]'];
        //delete objects['sort'];
        filter_ajax(e,objects);

        e.preventDefault();
    });

    //for price inputs
    $('#filter-form #max_price,#filter-form #min_price').change(function (e) {
        flagDeletePrices = false;
        var slider = $("#filter-price-slider");
        var min = slider.slider("option", "min");
        var max = slider.slider("option", "max");

        var valueMin;
        var valueMax;

        if (parseInt($('#max_price').val()) > max) {
            valueMax = max;
            $('#max_price').val(valueMax);
        } else {
            valueMax = parseInt($('#max_price').val());
        }

        if (parseInt($('#min_price').val()) < min) {
            valueMin = min;
            $('#min_price').val(valueMin);
        } else {
            valueMin = parseInt($('#min_price').val());
        }

        slider.slider("values", [valueMin, valueMax]);


        // $.fn.yiiListView.update('shop-products', {url: formattedURL(getSerializeObjects())});
        filter_ajax(e,getSerializeObjects());
        //currentFilters(formattedURL(getSerializeObjects()));
        //reload path by url
        //window.location.pathname = uri;

        //history.pushState(null, $('title').text(), formattedURL(e, getSerializeObjects()));
        e.preventDefault();
        console.log('click #filter-form input[type="text"]');
    });


    $(document).on('click', '#sorting-form button2', function (e) {
        filter_ajax(e,getSerializeObjects());
        console.log('#sorting-form button');
        e.preventDefault();
        return false;
    });

    $(document).on('change', '#sorting-form', function (e) {
        filter_ajax(e,getSerializeObjects());
        console.log('#sorting-form');
        e.preventDefault();
        return false;
    });


    /*
     $('#sorting-form a').click(function (e) {
     e.preventDefault();
     $.fn.yiiListView.update('shop-products', {url: $(this).attr('href')});
     history.pushState(null, $('title').text(), $(this).attr('href'));
     console.log('click #sorting-form a');
     });
     */
});


function filterSearchInput(that, listId) {
    // Declare variables
    var value, ul, li, a, i, txtValue;

    value = $(that).val().toUpperCase();
    ul = document.getElementById(listId);
    li = ul.getElementsByTagName('li');

    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("label")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(value) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}