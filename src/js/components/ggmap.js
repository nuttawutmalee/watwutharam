window.ggmap = {
    CONTAINER_SELECTOR: '.map__wrapper',
    MAP_SELECTOR: '.ggmap',
    key: 'AIzaSyD9jHSvd-BVWizdrYD5K2tqTueRXbN7ONk',
    srcPathPng: 'templates/littleriversidehoian/assets/images/icons/icon-location.png',
    srcPathSvg: 'templates/littleriversidehoian/assets/images/icons/icon-location.svg',
    isLoaded: false,
    markerSettings: {
        color: '#731932',
        innerRadius: 10,
        outerMaxRadius: 40,
        outerMaxOpacity: 0.5
    },
    onReady: function () {
        if (!jQuery(window.ggmap.CONTAINER_SELECTOR).length) return;

        
        window.ggmap.smoothScroll();
        window.ggmap.normalScroll();
    },
    init: function () {

        if (window.ggmap.isLoaded === true) return
        if (verge.inViewport(jQuery(window.ggmap.CONTAINER_SELECTOR), 200)) {
            var s = document.createElement('script');
            s.setAttribute('src', 'https://maps.googleapis.com/maps/api/js?callback=window.ggmap.createMap&key=' + window.ggmap.key);
            s.setAttribute("async", "");
            s.setAttribute("defer", "");
            document.body.appendChild(s);
            window.ggmap.isLoaded = true;

            // console.log('init');
            
        }
    },
    createMap: function () {

        var containers = jQuery(window.ggmap.CONTAINER_SELECTOR);
        containers.each(function () {
            var container = jQuery(this);
            var mapContainer = container.find('.ggmap');
            if (mapContainer.data('map') != undefined)
                return;

            var styles = [{
                    "featureType": "all",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "all",
                    "elementType": "labels.text.fill",
                    "stylers": [{
                        "color": "#967b68"
                    }]
                },
                {
                    "featureType": "all",
                    "elementType": "labels.text.stroke",
                    "stylers": [{
                        "color": "#fdf2de"
                    }]
                },
                {
                    "featureType": "all",
                    "elementType": "labels.icon",
                    "stylers": [{
                        "visibility": "off"
                    }]
                },
                {
                    "featureType": "poi",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#eeeeee"
                    }]
                },
                {
                    "featureType": "poi",
                    "elementType": "geometry.fill",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "poi",
                    "elementType": "labels.text.fill",
                    "stylers": [{
                        "color": "#b2a793"
                    }]
                },
                {
                    "featureType": "poi.attraction",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "poi.attraction",
                    "elementType": "labels.icon",
                    "stylers": [{
                        "color": "#ff0000"
                    }]
                },
                {
                    "featureType": "poi.business",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "poi.government",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "poi.medical",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "poi.place_of_worship",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "poi.school",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "poi.sports_complex",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry",
                    "stylers": [{
                            "color": "#ece1cf"
                        },
                        {
                            "visibility": "on"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry.fill",
                    "stylers": [{
                        "color": "#fdf2de"
                    }]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f3e7cd"
                    }]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "labels.text.fill",
                    "stylers": [{
                        "color": "#967b68"
                    }]
                },
                {
                    "featureType": "transit.station",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#f4e9d5"
                    }]
                },
                {
                    "featureType": "transit.station.bus",
                    "elementType": "all",
                    "stylers": [{
                        "color": "#ab2a2a"
                    }]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [{
                        "color": "#e7d6bc"
                    }]
                },
                {
                    "featureType": "water",
                    "elementType": "labels.text.fill",
                    "stylers": [{
                            "color": "#b8a88e"
                        },
                        {
                            "visibility": "on"
                        }
                    ]
                }
            ];

            //google map custom marker icon - .png fallback for IE11
            var marker_url = (window.Modernizr.ie) ? window.ggmap.srcPathPng : window.ggmap.srcPathSvg;

            // Options
            var mapCenter = new google.maps.LatLng(mapContainer.attr('data-lat'), mapContainer.attr('data-lng'));
            var zoom = parseInt(mapContainer.attr('data-zoom'));
            var title = mapContainer.attr('data-title');

            var mapOptions = {
                center: mapCenter,
                zoom: zoom,
                styles: styles,
                disableDefaultUI: true,
                disableDoubleClickZoom: true,
                draggable: true,
                scrollwheel: false,
                overviewMapControl: false,
                panControl: false,
                zoomControl: false,
                mapTypeControl: false,
                scaleControl: false,
                streetViewControl: false,
                rotateControl: false,
                fullscreenControl: false,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            // Map
            var map = new google.maps.Map(mapContainer.get(0), mapOptions);
            map.panBy(verge.viewportW() / 4, 0);

            var dotOuter = {
                path: google.maps.SymbolPath.CIRCLE,
                strokeWeight: 0,
                fillColor: window.ggmap.markerSettings.color,
            };

            var dotInner = {
                path: google.maps.SymbolPath.CIRCLE,
                scale: window.ggmap.markerSettings.innerRadius,
                strokeWeight: 0,
                fillColor: window.ggmap.markerSettings.color,
                fillOpacity: 1
            };

            // Marker
            var marker = new google.maps.Marker({
                position: mapCenter,
                map: map,
                // visible: true,
                // icon: marker_url
                icon: dotOuter
            });

            var marker2 = new google.maps.Marker({
                position: mapCenter,
                map: map,
                title: title,
                icon: dotInner
            });

            window.ggmap.pulseMarker(marker);

            marker.addListener('click', function () {
                window.open(mapContainer.attr('data-link'));
            });
            marker2.addListener('click', function () {
                window.open(mapContainer.attr('data-link'));
            });

            //add custom buttons for the zoom-in/zoom-out on the map
            function CustomZoomControl(controlDiv, map) {
                //grap the zoom elements from the DOM and insert them in the map
                var controlUIzoomIn = document.getElementById('cd-zoom-in'),
                    controlUIzoomOut = document.getElementById('cd-zoom-out');
                controlDiv.appendChild(controlUIzoomIn);
                controlDiv.appendChild(controlUIzoomOut);

                // Setup the click event listeners and zoom-in or out according to the clicked element
                google.maps.event.addDomListener(controlUIzoomIn, 'click', function () {
                    map.setZoom(map.getZoom() + 1)
                });
                google.maps.event.addDomListener(controlUIzoomOut, 'click', function () {
                    map.setZoom(map.getZoom() - 1)
                });
            }

            var zoomControlDiv = document.createElement('div');
            var zoomControl = new CustomZoomControl(zoomControlDiv, map);

            //insert the zoom div on the top left of the map
            map.controls[google.maps.ControlPosition.LEFT_TOP].push(zoomControlDiv);

            // Save datas
            mapContainer.data('map', map);
            mapContainer.data('mapCenter', mapCenter);
        });


        google.maps.event.addDomListener(window, 'resize', function () {
            window.ggmap.resizeMap();
        })
    },
    resizeMap: function () {

        // console.log('resizeMap');

        // Selectors
        var containers = jQuery(window.ggmap.CONTAINER_SELECTOR);
        if (!containers.length) return;

        containers.each(function () {
            var container = jQuery(this);
            var mapContainer = container.find('.ggmap');

            // Get map
            var map = mapContainer.data('map');
            if (typeof (map) == 'undefined') return;

            var mapCenter = mapContainer.data('mapCenter');
            map.setCenter(mapCenter);
            map.panBy(verge.viewportW() / 4, 0);

            google.maps.event.trigger(map, 'resize');
        });
    },
    pulseMarker: function (marker) {
        var counter = 0;
        var percentage = 0;
        var speed = 1;
        var icon = marker.getIcon();

        var loop = function () {
            if (counter > 90) {
                counter = 0;
            }
            percentage = Math.sin(counter * (Math.PI / 180));

            icon.scale = window.ggmap.markerSettings.innerRadius + percentage * (window.ggmap.markerSettings.outerMaxRadius - window.ggmap.markerSettings.innerRadius);
            icon.fillOpacity = window.ggmap.markerSettings.outerMaxOpacity - percentage * window.ggmap.markerSettings.outerMaxOpacity;
            marker.setIcon(icon);

            counter += speed;
            animationPulse = requestAnimationFrame(loop);
        };

        loop();
    },
    smoothScroll: function () {
        if (window.simplified.smoothscrollVersion()) {
            window.smoothscrollbar.scrollbar.addListener(function (status) {
                window.ggmap.init();
            });
        }
    },
    normalScroll: function () {
        if (!window.simplified.smoothscrollVersion()) {
            window.ggmap.init();
        }
    }
};

site.ready.push(function () {
    window.ggmap.onReady();
});
site.scroll.push(function () {
    window.ggmap.normalScroll();
});

