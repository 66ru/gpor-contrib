$(document).ready(function() {
    $.fn.gazprom_map_items = function (opts) {
        opts = $.extend({}, $.fn.gazprom_map_items.defaults, opts);
        return this.each(function () {
            $.fn.gazprom_map_items.instances[$(this).attr('id')] = new GazpromMapItems(this, opts, $(this).attr('id'));
            return $.fn.gazprom_map_items.instances[$(this).attr('id')];
        });
    };

    $.fn.gazprom_map_items.instances = new Object();
    $.fn.gazprom_map_items_refresh = function () {
    };

    // default options
    $.fn.gazprom_map_items.defaults = {
        'mapId':'ya-map-search',
        'listClass':'found_body_left',
        'filterClass': 'gazprom-search-form',
        'fuelFilterClass': '',
        'serviceFilterClass': '',
        'containerClass':'found_body',
        'itemClass':'item_offer',
        'activeItemClass':'item_offer_active',
        'footerId':'l-footer',
        'lat':$.data(document, 'yandexDefaultLatitude'),
        'lng':$.data(document, 'yandexDefaultLongitude'),
        'pmap':false,
        'enableScroll':true,
        'scale':$.data(document, 'yandexDefaultZoom'),
        'height':800,
        'listFeed': '',
        'infoFeed': ''
    };

    var GazpromMapItems = function (obj, o, instance_id) {
        var stationListGlobal = [];
        $("#" + o.mapId).each(function () {
            var left_height = 0;
            if (o.enableScroll)
                left_height = $('.' + o.listClass).attr("offsetHeight");
            else
                left_height = o.height;
            $(this).css({height:left_height + "px"});
        });
        YMaps.ready(function () {
            var mapType = "yandex#map";
            if ($.data(document, 'yandexPeopleMaps'))
                mapType = "yandex#publicMap";

            var map = new YMaps.Map(o.mapId, {
                center:[o.lat, o.lng],
                zoom:o.scale,
                type:mapType
            });
            map.behaviors.enable("scrollZoom");
            map.controls.add('zoomControl').add('typeSelector').add('mapTools').add('miniMap').add('scaleLine');

            var points = new Array();
            var titlePoints = new Array();

            var items = [];
            var itemsCounter = 0;

            var fuelTypes = [];
            var serviceTypes = [];

            var type = false;
            $.getJSON(o.infoFeed, function(data) {
                fuelTypes = data.fuelTypes;
                serviceTypes = data.serviceTypes;
                $.each(fuelTypes, function(key, type) {
                    var obj = $('<div>');
                    obj.addClass('gazprom-filter_item');
                    obj.addClass('filter-item_fuel');
                    obj.addClass('fuel-type_' + type['id']);
                    obj.html('<div style="text-align: center; margin-top: 5px">' + type['title'] + '</div>');
                    obj.attr('rel', type['id']);
                    $('.' + o.fuelFilterClass).append(obj);
                });
                var i = 1;
                $.each(serviceTypes, function(key, type) {
                    var obj = $('<div>');
                    obj.addClass('gazprom-filter_item');
                    obj.addClass('filter-item_service');
                    obj.addClass('service-type_' + type['id']);
                    obj.html('<img style="vertical-align: middle" width="24" height="24" src="' + type['img'] + '" /><span style="display: inline-block; width: 95px; vertical-align: middle;">' + type['title'] + '</span>');
                    obj.attr('rel', type['id']);
                    $('.' + o.serviceFilterClass).append(obj);
                    if (i % 5 == 0) {
                        $('.' + o.serviceFilterClass).append('<br />');
                    }
                    i++;
                    scroll();
                    bindFilterLinks();
                    $('.' + o.filterClass).show();

                });
            });

            $.getJSON(o.listFeed, function(stations) {
                $.each(stations, function(key, station) {
                    stationListGlobal.push(station);
                });
                // Дать время 100% получить все типы топлива и услуги
                window.setTimeout(function() {
                    updateStationList(stationListGlobal)
                }, 300);
                $("#" + o.mapId).show();
                scroll();
            })

            var setBounds = function () {
                minLng = false;
                maxLng = false;
                minLat = false;
                maxLat = false;
                for (i in points) {
                    x = points[i].geometry.getCoordinates()[0];
                    y = points[i].geometry.getCoordinates()[1];
                    if (minLng == false) minLng = x;
                    if (maxLng == false) maxLng = x;
                    if (minLat == false) minLat = y;
                    if (maxLat == false) maxLat = y;

                    if (minLng > x) minLng = x;
                    if (maxLng < x) maxLng = x;
                    if (minLat > y) minLat = y;
                    if (maxLat < y) maxLat = y;
                }
                if (minLng) {
                    map.setBounds([
                        [minLng, minLat],
                        [maxLng, maxLat]
                    ], {checkZoomRange:true});
                }
                return false;
            };

            // Устанавливает действия на кнопки фильтра
            var bindFilterLinks = function() {
                $('.gazprom-filter_item').unbind('click');
                $('.gazprom-filter_item').click(function() {
                    $(this).toggleClass('filter-selected');
                    stationList = [];
                    var fuelFilters = $.map($('.' + o.fuelFilterClass + ' div.filter-selected'), function(el) {
                        return $(el).attr('rel')
                    });
                    var serviceFilters = $.map($('.' + o.serviceFilterClass + ' div.filter-selected'), function(el) {
                        return $(el).attr('rel')
                    });
                    var flag = false;
                    $.each(stationListGlobal, function(key, station) {
                        flag = true;
                        for(var i = 0; i < fuelFilters.length; i++) {
                            if ($.inArray(fuelFilters[i], station.fuelTypes) == -1) {
                                flag = false;
                                break;
                            }
                        }
                        for(var i = 0; i < serviceFilters.length; i++) {
                            if ($.inArray(serviceFilters[i], station.services) == -1) {
                                flag = false
                                break;
                            }
                        }
                        if (flag) {
                            stationList.push(station);
                        }
                    });
                    updateStationList(stationList);
                })
            }

            // Обновляет список заправок и саму карту
            var updateStationList = function(stationList) {
                $('.' + o.listClass).html(' ');
                if (stationList.length == 0) {
                    $("#" + o.mapId).hide();
                    return false;
                }

                $("#" + o.mapId).show();
                map.geoObjects.each(function (geoObject) {
                    map.geoObjects.remove(geoObject);
                });
                $.each(stationList, function(key, station) {
                    var lat = parseFloat(station['gps'][0]);
                    var lng = parseFloat(station['gps'][1]);
                    var title = station['title'];

                    if (lat != "" && lng != "") {
                        var title = station['title']
                        var address = station['address'];

                        var fuelTypesString = '<span style="float: left; margin-top: 3px;">Топливо:</span>';
                        for (var i = 0; i < station['fuelTypes'].length; i++) {
                            type = fuelTypes[station['fuelTypes'][i]];
                            if (type == undefined) continue;
                            fuelTypesString += '<div style="background-color: #A0BC60; color: #FFFFFF" class="gazprom-filter_item filter-item_fuel fuel-type_' + type['id'] + '"><div style="text-align: center; margin-top: 5px">' + type['title'] +'</div></div>'
                        }

                        var servicesString = '<br /><br /><span style="float: left; margin-top: 3px;">Услуги:</span>';
                        for (var i = 0; i < station['services'].length; i++) {
                            type = serviceTypes[station['services'][i]];
                            if (type == undefined) continue;
                            servicesString += '<div style="background-color: #A0BC60; width: 24px; height: 24px; padding-top: 0; margin-left: 5px;" class="gazprom-filter_item filter-item_service service-type_' + type['id'] + '"><img title="' + type['title'] + '" width="24" height="24" src="' + type['img'] + '" style="vertical-align: middle" /></div>'
                        }

                        var placemark = new YMaps.Placemark([lat, lng], {
                            balloonContent: '<div style="fond-size: 13px;"><h3 style="font-size: 13px">' + title + '</h3></div><p style="margin-bottom: 10px;">' + address + '</p>' + fuelTypesString + servicesString
                        }, {
                            iconImageHref: 'http://gpnbonus.ru/bitrix/templates/blue_fixed/images/maps/azs_yamap_mark.png',
                            iconImageSize: [14, 24],
                            iconImageOffset: [-7, -12]
                        });
                        map.geoObjects.add(placemark);

                        var pointId = lat + "_" + lng;

                        itemsCounter++;
                        var obj = $('<div>');
                        obj.attr('id', 'point' + itemsCounter);
                        obj.addClass(o.itemClass);

                        obj.html('<a name="point' + itemsCounter + '"></a><table class="context item_offer_description"><tbody><tr><td class="item_offer_right"><h3><a href="#" onclick="return false;">' + title + '</a></h3></td></tr></tbody><tr><td class="item_offer_right-bot" valign="bottom"><p class="item_offer_address">' + address + '</p></td></tr></table>');

                        $(obj).attr('pointId', pointId);
                        points[pointId] = placemark;
                        titlePoints[pointId] = station['title'];
                        $('.' + o.listClass).append(obj);
                    }
                });
                scroll();
            }

            var $items = $('.' + o.listClass).find('.' + o.itemClass);

            $('.' + o.listClass).delegate('.'+o.itemClass, 'mouseenter', function (e) {
                pointId = $(this).attr("pointId");
                $(this).closest('.'+o.listClass).find('.'+o.itemClass).removeClass(o.activeItemClass);
                $(this).addClass(o.activeItemClass);

                if (pointId) {
                    placemark = points[pointId];
                    map.panTo(placemark.geometry.getCoordinates());
                    placemark.balloon.open();
                }
                else {
                    for (i in points)
                        points[i].balloon.close();
                }
                e.stopPropagation();
            });

            // map scroll
            if (!o.enableScroll) {
                setBounds();
            }
            else {
                var ya_map = $("#" + o.mapId);
                var $document = $(document);
                var $body = $('body');
                var $searchContent = $("." + o.containerClass);
                var $foot_wrap = $('.' + o.footerId);

                var scroll = function () {
                    var current_scroll = $document.scrollTop();
                    var offset = $searchContent.offset();
                    var new_top = offset.top - current_scroll;
                    var new_foot = $foot_wrap.height() + current_scroll - $document.height() + $body.height();
                    var right_block = $("." + o.containerClass);
                    if (new_top < 10) {
                        new_top = 10;
                    }
                    var newheight = document.body.clientHeight - new_top - 10;
                    var rightHeight = right_block.attr("offsetHeight");
                    var offset = rightHeight + parseInt(right_block.offset().top);
                    if (current_scroll + newheight + new_top > offset) {
                        newheight = offset - current_scroll - new_top - 10;
                    }

                    if (new_foot > 0)
                        newheight -= new_foot;

                    ya_map.css({
                        'height': newheight,
                        'top': new_top
                    });
                    map.container.fitToViewport();
                };

                var parent_map = ya_map.parent();
                ya_map.css("width", parent_map.width() - 10 + "px");
                var clientHeight = $(document.body).attr("clientHeight");
                var mapTop = parseInt(ya_map.offset().top);
                var startHeight = clientHeight;

                ya_map.css({height: startHeight - 10 + "px"});
                YMaps.load(function () {
                    ya_map.css({height: startHeight - mapTop + "px"});
                });

                rightBlock = $(o.listClass);
                if (rightBlock.attr("offsetHeight") < 535) {
                    rightBlock.css("height", "535px");
                }

                $(window).resize(function () {
                    ya_map.css("width", parent_map.width() - 10 + "px");
                    scroll();
                });
                $(document).ready(function () {
                    scroll();
                    setBounds();
                });
                window.onmousewheel = document.onmousewheel = scroll;
                window.onscroll = scroll;

                scroll();
            }
        });
    };
});