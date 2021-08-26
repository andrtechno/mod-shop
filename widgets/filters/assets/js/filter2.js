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
    var selector = $('.card .card-collapse');
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
function getSerializeObjects() {
    return $.extend($('#filter-form').serializeObject(), $('#sorting-form').serializeObject())
}

function formattedURL(objects) {
    var uri = current_url;
    console.log(yii.getCsrfParam());
    delete objects[yii.getCsrfParam()];
    //delete objects.min_price;
    //delete objects.max_price;

    $.each(objects, function (name, values) {
        if (values !== '') {
            var matches = name.match(/filter\[([a-zA-Z0-9-_]+)\]\[]/i);
            uri += (matches) ? '/' + matches[1] : '/' + name;


            if (values instanceof Array) {
                uri += '/' + values.join(',');
            } else {
                uri += '/' + values;
            }
        }
    });
    return uri;
}

var flagDeletePrices = false;
$(function () {

    var form = $('#filter-form');
//_csrf

    $(document).on('change', '#filter-form input[type="checkbox"]', function (e) {

        flagDeletePrices=true;
        var objects = getSerializeObjects();
        if (flagDeletePrices) {
            delete objects.min_price;
            delete objects.max_price;
        }

        //$.fn.yiiListView.update('shop-products', {url: formattedURL(objects)});
        $.get(formattedURL(objects), {}, function (data) {
            $('#listview-ajax').html(data);
            //console.log(data);
        });
        console.log('change', formattedURL(objects));
        //currentFilters(formattedURL(objects));
        //reload path by url
        //window.location.pathname = uri;

        history.pushState(null, $('title').text(), formattedURL(objects));
        e.preventDefault();
    });


    //for price inputs
    $('#filter-form input[type="text"]').change(function (e) {
        flagDeletePrices=false;
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


        $.fn.yiiListView.update('shop-products', {url: formattedURL(getSerializeObjects())});

        currentFilters(formattedURL(getSerializeObjects()));
        //reload path by url
        //window.location.pathname = uri;

        history.pushState(null, $('title').text(), formattedURL(getSerializeObjects()));
        e.preventDefault();
    });


    $('#sorting-form').change(function (e) {
        e.preventDefault();
        $.fn.yiiListView.update('shop-products', {url: formattedURL(getSerializeObjects())});
        history.pushState(null, $('title').text(), formattedURL(getSerializeObjects()));
    });

    $('#sorting-form a').click(function (e) {
        e.preventDefault();
        $.fn.yiiListView.update('shop-products', {url: $(this).attr('href')});
        history.pushState(null, $('title').text(), $(this).attr('href'));
    });


    //curret filter


});