(function ( $ ) {
    $.fn.filterShop = function( options ) {
        var settings = $.extend({
            color: "#556b2f",
            backgroundColor: "white"
        }, options );

        console.log('init filter sop');

        $.fn.filterCallback = function(e, objects, target) {
            console.log('$.fn.filterCallback');

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
            return;
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
            return xhrCallback;
        };

        return this;
    };
}( jQuery ));