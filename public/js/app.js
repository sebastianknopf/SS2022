$(function () {
    // init map view
    let osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    });

    let map = L.map('sectionMapView', {
        center: [49.0068901,  	8.4036527],
        zoom: 13,
        layers: [osm]
    });

    let lineLayerGroup = new L.layerGroup([]).addTo(map);
    let stopLayerGroup = new L.layerGroup([]).addTo(map);

    let stopIcon = L.icon({
        iconUrl: 'public/images/sign224.png',
        iconSize: [30, 30]
    });

    // data loading functions
    function extractRequestParams() {
        return {
            dateFrom: $('#inputDateFrom').val(),
            dateUntil: $('#inputDateUntil').val(),
            routeName: $('#selectRouteName').val(),
            dayType: $('#selectDayType').val(),
            direction: $('#selectDirection').val()
        };
    }

    function loadTripDemandData() {
        // prepare filter params for this request
        let requestData = extractRequestParams();

        // run request and display results
        $.ajax({
            type: 'GET',
            url: 'tripDemandData',
            data: requestData,
            success: function (data) {

                $('.spanRouteName').text(requestData.routeName);
                $('#divTripsDirection1').html('');
                $('#divTripsDirection2').html('');

                $(data).each(function (i, element) {
                    let tripDiv = $('<div>');
                    tripDiv.addClass('w3-bar-item');
                    tripDiv.addClass('w3-center');
                    tripDiv.css({
                        'width': '100px',
                        'display': 'inline-block',
                        'float': 'none'
                    });

                    if ($('.active').attr('id') == 'buttonFilterOccupationHigh' && element.occupationLevel > 0.4) {
                        tripDiv.css('background-color', '#ff9ea3');
                    } else if ($('.active').attr('id') == 'buttonFilterOccupationMedium' && element.occupationLevel <= 0.4 && element.occupationLevel >= 0.1) {
                        tripDiv.css('background-color', '#ff9ea3');
                    } else if ($('.active').attr('id') == 'buttonFilterOccupationLow' && element.occupationLevel < 0.1) {
                        tripDiv.css('background-color', '#ff9ea3');
                    }

                    tripDiv.html(element.startTime + '<br />' + element.stopCode);

                    if (element.direction == 1) {
                        $('#divTripsDirection1').append(tripDiv);
                    } else {
                        $('#divTripsDirection2').append(tripDiv);
                    }
                });
            }
        });
    }

    function loadMapDemandData() {
        // prepare filter params for this request
        let requestData = extractRequestParams();

        // run request and display results
        $.ajax({
            type: 'GET',
            url: 'mapDemandData',
            data: requestData,
            success: function (data) {
                // display lines according to their occupation level
                lineLayerGroup.eachLayer(function (l) { l.removeFrom(lineLayerGroup); });
                $(data).each(function (i, element) {
                    let pathLine = L.polyline([
                        [element.fromStop.latitude, element.fromStop.longitude],
                        [element.toStop.latitude, element.toStop.longitude]
                    ], {color: '#1d6e90', dashArray: [25, 30]});

                    if ($('.active').attr('id') == 'buttonFilterOccupationHigh' && element.occupationLevel > 0.4) {
                        pathLine.setStyle({
                            color: '#ff2f3e',
                            dashArray: null
                        });
                    } else if ($('.active').attr('id') == 'buttonFilterOccupationMedium' && element.occupationLevel <= 0.4 && element.occupationLevel >= 0.1) {
                        pathLine.setStyle({
                            color: '#ff2f3e',
                            dashArray: null
                        });
                    } else if ($('.active').attr('id') == 'buttonFilterOccupationLow' && element.occupationLevel < 0.1) {
                        pathLine.setStyle({
                            color: '#ff2f3e',
                            dashArray: null
                        });
                    }

                    lineLayerGroup.addLayer(pathLine);
                });

                // display stops on top
                stopLayerGroup.eachLayer(function (l) { l.removeFrom(stopLayerGroup); });
                $(data).each(function (i, element) {
                    let stopMarker = new L.marker([element.fromStop.latitude, element.fromStop.longitude], {icon: stopIcon}).bindPopup(element.fromStop.name);
                    stopLayerGroup.addLayer(stopMarker);

                    if (i == data.length - 1) {
                        let stopMarker = new L.marker([element.toStop.latitude, element.toStop.longitude], {icon: stopIcon}).bindPopup(element.toStop.name);
                        stopLayerGroup.addLayer(stopMarker);
                    }
                });
            }
        });
    }

    // initial data loading
    loadTripDemandData();
    loadMapDemandData();

    // UI related
    let filterTimeout = null;
    $('.inputFilter').on('change', function () {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function () {
            loadTripDemandData();
            loadMapDemandData();
        }, 1000);
    });

    $('.buttonFilterOccupation').on('click', function (e) {
        // change button state
        $('.buttonFilterOccupation').removeClass('active').removeClass('w3-gray').addClass('w3-light-gray');
        $(this).removeClass('w3-light-gray').addClass('w3-gray').addClass('active');

        // reload and reprocess data
        loadTripDemandData();
        loadMapDemandData();

        // prevent form from being submitted
        e.preventDefault();
    });
});