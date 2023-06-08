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
var showReset = false;
var showApply = false;
var showButtons = true;
var deferreds = [];

//const url = new URL('/catalog/clothes/tip_odagu/276', 'http://example.com');

//console.log(url.searchParams.get('tip_odagu'));


$(function () {
    var selector = $('#filters .card-collapse');
    selector.collapse({
        toggle: false
    });
    if ($.fn.cookie) {
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
    }


    $(document).on('click', '.sorting .radio_item', function () {
        $('.sorting').removeClass('open');
    });
});


var form = $('#filter-form');
var sortForm = $('#sorting-form');
var ajaxSelector = $('#listview-ajax');
var containerFilterCurrent = $('#ajax_filter_current');
var xhrFilters;
var xhrCallback;
var flagDeletePrices = false;
var global_url;
var newFunction = true;
var resultUrl = '';


function filterCallbackAjax(e, objects, target) {

    xhrCallback = $.ajax({
        dataType: "json",
        //url: common.url('/api/shop/elastic'),
        url: '/api/shop/elastic?lang='+common.language,
        //url: '/api/shop/elastic',
        type: 'POST',
        headers: {
            "filter-callback-ajax": true
        },
        data: objects,
        error: function (error) {
            $('.sidebar').removeClass('loading');
            common.notify(error.statusText, 'error');
        },
        success: function (response) {

            resultUrl = response.url;
            form.attr('action', resultUrl);

            $.each(response.filters, function (name, filter) {
                var hideCounter = 0;
                $.each(filter.filters, function (index, data) {
                    var selector = $('#filter_' + filter.key + '_' + data.id);
                    $('#filter-count-' + filter.key + '-' + data.id).html(data.count_text);

                    if (data.count) {
                        $('#filter-' + filter.key + ' #li-' + data.id + ' input[type="checkbox"]:not(:checked)').attr('disabled', false);
                        $('#filter-' + filter.key + ' #li-' + data.id).removeClass('disabled');
                        // $('#filter-' + name + ' #li-' + data.id).show();
                        hideCounter++;
                    } else {
                        $('#filter-' + filter.key + ' #li-' + data.id + ' input[type="checkbox"]:not(:checked)').attr('disabled', 'disabled');
                        $('#filter-' + filter.key + ' #li-' + data.id).addClass('disabled');
                        if (!selector.prop('checked')) {
                            //    $('#filter-' + name + ' #li-' + data.id).hide();
                        }else{

                            //    $('#filter-' + name + ' #li-' + data.id).show();
                        }
                    }
                });
                var container = $('#filter-container-' + filter.key);

                if(hideCounter == 0){
                    //container.hide();
                }else{
                    container.show();
                    if (hideCounter < 10) {
                        //container.find('input[type="text"]').hide();
                    } else {
                        container.find('input[type="text"]').show();
                    }
                }

            });
            responseData = response;

            $(document).trigger('filter:ajaxSuccess', {target: target, response: response});
        },
        complete: function () {
            showApply = true;
            showReset = true;
            if ($(e.currentTarget).data('type') !== 'checkbox' || $(e.currentTarget).data('type') !== 'slider') {
                $('.filter-buttons').trigger('filter:buttons:toggle', {response: responseData}); //показывать кнопку

            }

            if(e.type === 'slidestop'){
                $(document).trigger('filter:apply'); //сразу применять
            }

            $('.sidebar').removeClass('loading');
        },
        beforeSend: function () {
            //ajaxSelector.toggleClass('loading');
            showApply = false;
            showReset = true;
            $('.sidebar').addClass('loading');
            //$('.filter-buttons').trigger('filter:buttons:toggle');
        }

    });
    deferreds.push(xhrCallback);
    return xhrCallback;

}
function filterCallback(e, objects, target) {
    if (xhrCallback && xhrCallback.readyState !== 4) {
        xhrCallback.onreadystatechange = null;
        xhrCallback.abort();
        //ajaxSelector.removeClass('loading');
    }

    delete objects['search-filter'];

    var max = $("#slider-price").slider("option", "max");
    var min = $("#slider-price").slider("option", "min");
    var values = $("#slider-price").slider("option", "values");
    //if($(e.currentTarget).data('type') == 'checkbox'){
    if (min == values[0] && max == values[1]) {
        delete objects['slide[price][]'];
    }
    //}
    if ($(e.currentTarget).data('type') == 'slider' || e.type == 'filter:click:checkbox') {


        //$("#slider-price").slider("option", "values", [min, max]);
        $("#slider-price").slider("option", "max", max);
        $("#slider-price").slider("option", "min", min);
        console.log(min, max, values);
        if (min != values[0] && max != values[1]) {
            //$('#min_price').val(min);
            //$('#max_price').val(max);
            delete objects['slide[price][]'];
        } else {
            if (e.type == 'click') {
                $('#min_price').val(min);
                $('#max_price').val(max);
                $("#slider-price").slider("option", "values", [min, max]);
                delete objects['slide[price][]'];
            }
        }
        //if(min == values[0] && max == values[1]){
        //    delete objects['slide[price][]'];
        //}

    }

    //delete objects.route;
    //delete objects.category_id;
    //objects = $.extend(objects, {});

    var responseData;

    //objects = $.extend(objects, {'selected[slide][price]': 1});



    return filterCallbackAjax(e,objects,target);

}

function filter_ajax(e, objects, sort = false) {
    if (xhrFilters && xhrFilters.readyState !== 4) {
        xhrFilters.onreadystatechange = null;
        xhrFilters.abort();
        ajaxSelector.removeClass('loading');
    }

    delete objects.route;
    delete objects.param;
    delete objects.cache;
    delete objects['search-filter'];
    delete objects['attributes[]'];
    //if (url === undefined) {

    var url = (sort) ? formattedURL(e, objects) : resultUrl;
    //var url = current_url;
    // }

    // console.debug('Event: ' + e.type, objects);
    xhrFilters = $.ajax({
        dataType: "json",
        url: url,
        type: 'POST',
        headers: {
            "filter-ajax": true
        },
        data: $('#filter-form input[type="checkbox"], #filter-form input[type="radio"], #sorting-form select').serialize(),
        success: function (data) {
            ajaxSelector.html(data.items).toggleClass('loading');
            form.attr('action', data.currentUrl);
            containerFilterCurrent.html(data.currentFiltersData).removeClass('loading');
            history.pushState(null, $('title').text(), data.currentUrl);
            //console.log('success filter_ajax');
            $('#summary').html(data.totalCount);
            $('h1').html(data.pageName);

            if ((typeof $.fn.ias !== 'undefined')) {
                var ias = jQuery.ias();
                if (ias) {
                    console.debug('ias plugin > reinitialize:', ias);
                    ias.reinitialize();
                }
            } else if ((typeof $.fn.pjax !== 'undefined')) {
                // pjax.state = data.currentUrl;

                //console.log('PJAX!!!',$.fn.pjax);
                // $.pjax({url: url, container: '#pjax-catalog', timeout: false, state: data.currentUrl});
                // $.pjax.reload('#pjax-sales', {url: url,timeout:false,state:data.currentUrl})

            }
        },
        beforeSend: function () {
            ajaxSelector.toggleClass('loading');
        },
        //complete:function(){}
    });
    return xhrFilters;
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
    var form = form.serialize();
    return $.extend(form, sortForm.serialize())
}

function formattedURL(e, objects) {
    var uri = current_url;
    var valuesList;
    delete objects[yii.getCsrfParam()];
    //delete objects.min_price;
    delete objects.default_price;

    $.each(objects, function (name, values) {
        if (values !== '') {

            //var matches = name.match(/filter\[([a-zA-Z0-9-_]+)\]\[]|/i);
            var matches = name.match(/filter\[([a-zA-Z0-9-_]+)\]\[]/i);
            var slides = name.match(/slide\[(?<name>[a-zA-Z0-9-_]+)\]\[]/i);


            //if (e.type == 'slidestop') {


            // } else {
            valuesList = (values instanceof Array) ? '/' + values.join(',') : '/' + values;
            if (name === 'sort') {

                uri += '/sort' + valuesList;

            } else {
                if (matches) {
                    uri += '/' + matches[1] + valuesList;
                } else {
                    if (slides) {

                        valuesList = (values instanceof Array) ? '/' + values.join('-') : '/' + values;
                        var defaultMin = $('#min_' + slides.groups.name).data('default');
                        var defaultMax = $('#max_' + slides.groups.name).data('default');

                        //No add to url default values
                        if ((Number.parseInt(values[0]) > defaultMin) || (Number.parseInt(values[1]) < defaultMax)) { // && (defaultMax < Number.parseInt(values[1]))
                            uri += '/' + slides.groups.name + '' + valuesList;
                        }

                    } else {
                        uri += '/' + name + valuesList;
                    }

                }
                // uri += ((matches) ? '/' + matches[1] : '/' + name) + valuesList;
            }
            //  }


        }
    });

    //console.debug(uri);
    // console.log(uri, 'formattedURL', objects);
    return uri;
}


function arrayRemove(arr, value) {
    return arr.filter(function (ele) {
        return ele != value;
    });
}

console.debug = function () {
    if (!console.debugging) return;
    var mainArguments = Array.prototype.slice.call(arguments);
    mainArguments.unshift("[Filter]");
    console.log.apply(this, mainArguments);
};


$(function () {
    var action;
    var width = $(window).width();
    var isMobile = (width < 768) ? true : false;


    $(document).on('filter:apply', function (e) {
        //var objects = getSerializeObjects();
        console.debug('filter:apply', $('#filter-form').serializeArray());
        filter_ajax(e, getSerializeObjects());
        // filter_ajax(e, objects);
    });
    $('#filter-form input[type="checkbox"], #filter-form input[type="radio"]').on('filter:click:checkbox', function (e, state) {
        this.checked = state;
        var id = $(this).attr('id');
        //  $('.tester').remove();


        //   $(this).parent().append('<div class="tester">123</div>');
        //  $('#example').popover({
        //     container: $(this).parent()
        // })

        console.debug('filter:click:checkbox', e.type, id, state);
        if (!isMobile) {
            if (newFunction) {
                var objects = getSerializeObjects();
                var target = $(this);
                filterCallback(e, objects, target);
            }

        } else {
            if (state) {
                checkedAll.push(id);
            } else {
                checkedAll = arrayRemove(checkedAll, id);
            }

            if (checkedAll.length > currentChackedByOpen.length) {
                changeItems = checkedAll.filter(n => currentChackedByOpen.indexOf(n) === -1);

            } else {
                changeItems = currentChackedByOpen.filter(n => checkedAll.indexOf(n) === -1);

            }
            console.log('> activeItems', changeItems);
            //showReset = (checkedAll.length) ? true : false; //not for chika
            showReset = true; //for chika
            showApply = true;
            if (newFunction) {
                var objects = getSerializeObjects();
                var target = $(this);
                filterCallback(e, objects, target);
            }
            $('.filter-buttons').trigger('filter:buttons:toggle');


        }

    });
    var currentChackedByOpen = []; //Определение после открытие фильтра чекнутых елементов.

    var checkedAll = [];
    var markedCheckbox = document.querySelectorAll('#filter-form input[type="checkbox"]:checked');
    var actibeFilterLog = [];
    for (var checkbox of markedCheckbox) {
        checkedAll.push($(checkbox).attr('id'));
        currentChackedByOpen.push($(checkbox).attr('id'));
        actibeFilterLog.push($(checkbox).attr('id'));
    }
    console.debug('Active filter:', actibeFilterLog.join(', '));
    //console.log('%c color text ', 'color: #0074cc');

    var objects = getSerializeObjects();
    var target = $(markedCheckbox[0]);
    var event = {type:'filter:click:checkbox'};
    filterCallbackAjax(event,objects,target);
    showButtons=false;


    var changeItems = [];
    var oldcheckedAll = checkedAll;


    $(document).on('filter:buttons:toggle', '.filter-buttons', function (e) {
        if (!showButtons) {
            return false;
        }
        /*if (Array.isArray(checkboxChacked)) {
            if (!checkboxChacked.length) {
                checkboxChacked = false;
            }
        }*/

        console.debug('filter:buttons:toggle', showReset, showApply);

        if (showReset) { //checkedAll ||
            $('#filter-reset').show();
        } else {
            $('#filter-reset').hide();
        }

        if (showApply) { //checkedAll ||
            $('#filter-apply').show();
        } else {
            $('#filter-apply').hide();
        }

        if (showReset || showApply) { //if (checkboxChacked) {
            //$(this).addClass('show');
            $('.sidebar').addClass('submitted');
            //console.debug(e.type, 'Show');
        } else {
            //showApply = false;
            //$(this).removeClass('show');
            $('.sidebar').removeClass('submitted');
            //console.debug(e.type, 'Hide');
        }

    });


    $('.sidebar').on('filter:open', function (e) {
        console.debug('Event: ' + e.type);
        $(this).addClass('active');
        $('body').addClass('noscroll');
        if ($('#filter-current ul li').length) {
            showReset = true;
            showApply = true; //not chika: set false
        }

        $('.filter-buttons').trigger('filter:buttons:toggle');

    });

    $('.sidebar').on('filter:close', function (e) {
        console.debug('Event: ' + e.type);
        $(this).removeClass('active');
        $('body').removeClass('noscroll');
    });


    var hovered = false;

    form.on({
        'mouseenter': function (e) {
            hovered = true;
        },
        'mouseleave': function (e) {
            hovered = false;

            $('[aria-describedby="' + $(this).attr('id') + '"]').popover('toggle');
        }
    }, '.popover').on('hide.bs.popover', '[aria-describedby^="popover"]', function (e) {
        setTimeout(function (element) {
            $(element).show();
        }, 0, e.target);

        if (hovered) {
            e.preventDefault();
        }
    });




    //Active filters mobile
    if (isMobile) {
        $(document).on('click', '#filter-current ul a', function (e) {
            var object = $(this).parent('li');
            var objectList = $(this).closest('ul');
            var checkboxId = $(object.data('target'));
            var slideId = $(object.data('slide-price-max'));
            var type = object.data('type');

            if (type == 'slider') {
                //remove params by slider
                console.debug('check', slideId);
                var slider = $("#slider-price");
                var min = slider.slider("option", "min");
                var max = slider.slider("option", "max");
                slider.slider("values", [min, max]);
                $('#min_price').val(min);
                $('#max_price').val(max);
                $('#filter-apply').show();

            } else {
                checkboxId.prop('checked', false);
                checkboxId.trigger('filter:click:checkbox', false);
            }


            object.remove();
            if (!objectList.find('li').length) {
                objectList.parent('li').remove();
            } else {
                //console.debug('ssssssssssssssss');
            }


            if ($('#filter-current ul li').length) { //checkedAll ||
                showReset = true;
                showApply = true;
            } else {
                showReset = false;
                showApply = true;
            }
            $(".filter-buttons").trigger("filter:buttons:toggle");
            console.debug('filter-current length',$('#filter-current ul li').length);

            return false;
        });
    }

    $(document).on('click', '#filter-apply', function (e) {
        console.debug("click filter-apply");
        // $('#filter-apply').hide();
        showApply = false;  //not chika: set false
        $(this).trigger('filter:apply');
        $('.sidebar').trigger('filter:close');
        //$(".filter-to-left").toggleClass("active");
        //$(".bg-minicart").toggleClass("active");
        e.preventDefault();
    });

    $(document).on('change', '#filter-form input[type="checkbox"], #filter-form input[type="radio"]', function (e) {
        showButtons=true;
        $(this).trigger('filter:click:checkbox', this.checked);
    });


    $("#slider-price").on("slidestop", function (e, ui) {
        console.log(e, ui);

        //return false;
        var flag = false;
        var objects = getSerializeObjects();
        if (!isMobile) {

            //filter_ajax(event, objects);
            if (newFunction) {
                var objects = getSerializeObjects();
                var target = $(this);
                filterCallback(e, objects, target);
            }
        } else {
            console.debug(e.type, ui.values);
            if (ui.values[0] !== parseInt($('#min_price').data('default'))) {
                flag = true;
            }

            if (ui.values[1] !== parseInt($('#max_price').data('default'))) {
                flag = true;
            }

            showApply = flag
            //$(".filter-buttons").trigger("filter:buttons:toggle");
            if (newFunction) {
                var objects = getSerializeObjects();
                var target = $(this);
                filterCallback(e, objects, target);
            }

        }
    });

    //for price inputs
    $(document).on('change', '#filter-form #max_price, #filter-form #min_price', function (e) {

        flagDeletePrices = false;
        var slider = $("#filters .ui-slider");
        var min = slider.slider("option", "min");
        var max = slider.slider("option", "max");

        var valueMin;
        var valueMax;

        if (parseInt($('#max_price').val()) > max) {
            valueMax = max;
            $('#max_price').val(valueMax);
        } else if (parseInt($('#max_price').val()) < min) {
            valueMax = min;
            $('#max_price').val(valueMax);
        } else {
            valueMax = parseInt($('#max_price').val());
        }

        if (parseInt($('#min_price').val()) < min) {
            valueMin = min;
            $('#min_price').val(valueMin);
        } else if (parseInt($('#min_price').val()) > max) {
            valueMin = max;
            $('#min_price').val(valueMin);
        } else {
            valueMin = parseInt($('#min_price').val());
        }


        slider.slider("values", [valueMin, valueMax]);

        //filter_ajax(e, getSerializeObjects());


        var objects = getSerializeObjects();
        var target = $(this);

        filterCallback(e, objects, target);

        $.when.apply($, deferreds).done(function () {
            filter_ajax(e, objects);
        });

        if (e.cancelable) {
            e.preventDefault();
        }
        e.preventDefault();
        console.debug('change filter price input');

    });


    /*$(document).on('click', '#filter-apply', function (e) {
        filter_ajax(e, getSerializeObjects());
        e.preventDefault();
        return false;
    });*/

    $(document).on('click', '#sorting-form button2', function (e) {
        filter_ajax(e, getSerializeObjects());
        console.debug('#sorting-form button');
        e.preventDefault();
        return false;
    });

    $(document).on('click', '#sorting-form input[type="radio"]', function (e) {
        filter_ajax(e, getSerializeObjects());
        console.debug('#sorting-form input[type="radio"]');
        e.preventDefault();
        return false;
    });

    $(document).on('change', '#sorting-form select', function (e) {
        filter_ajax(e, getSerializeObjects(), true);
        console.log('#sorting-form select');
        e.preventDefault();
        return false;
    });

    //currenct filter future
    $(document).on('click', 'a[data-target]', function (e) {
        var data = $($(this).data('target'));
        //e.preventDefault();
        data.prop('checked', false);

        var objects = getSerializeObjects();
        var target = $(this);
        showButtons = false;
        var filter = filterCallback(e, objects, target);
        filter.done(function () {
            filter_ajax(e, getSerializeObjects()).done(function () {
                showButtons = true;
            });
        })

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
        txtValue = a.getAttribute("data-search");
        // txtValue = a.textContent || a.innerText;
        //'data-search'
        //

        //for (var i = 0; i < test.length; i++) {
        //     console.log(i,test[i]); //second console output
        //}

        // test.forEach(function(currentValue, index, array) {
        //console.log(currentValue,index,array);
        // });
        //  a.parentNode.removeChild(a.getElementsByTagName("*"));
        //
        if (txtValue.toUpperCase().indexOf(value) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }

        if (li[i].classList.contains('disabled')) {
            li[i].style.display = "none";
        }

    }
}
