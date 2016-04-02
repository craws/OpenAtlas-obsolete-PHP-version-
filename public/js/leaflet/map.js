function interonoff(element) { //disable map dragging when cursor is e.g. in search input field.
    $(element).hover(function () {
        interoff();
    }, function () {
        interon();
    });
}

function interon() {
    map.dragging.enable();
    map.touchZoom.enable();
    map.doubleClickZoom.enable();
    map.scrollWheelZoom.enable();
    map.boxZoom.enable();
    map.keyboard.enable();
    if (map.tap) {
        map.tap.enable();
    }
    $('#map').css('cursor', '');
}

//functions to enable/disable dragging etc. (E.g. when cursor is in an text input field
function interoff() {
    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();
    map.boxZoom.disable();
    map.keyboard.disable();
    if (map.tap) {
        map.tap.disable();
    }
}

function setSitesInfo(e) { //set Popup Information of existing sites
    var marker = e.layer;
    marker.bindPopup(
      '<div id="mypopup"><div id="popuptitle">' + marker.toGeoJSON().properties.title + '</b> <br> </div>' +
      '<div id="popuptype"><i>' + marker.toGeoJSON().properties.sitetype + '</i> <br> <br></div>' +
      '<div style="max-height:100px; max-width:200px; overflow-y: auto">' + marker.toGeoJSON().properties.description + '<br></div></div>' +
      '<a href="/admin/place/view/id/' + marker.feature.properties.uid + '">Details</a>',
      {autoPanPaddingTopLeft: new L.Point(40, 10), autoPanPaddingBottomRight: new L.Point(50, 10)});
}

function switchInput() {
    if (capture) {
        $('#map').css('cursor', '');
        capture = false;
        uncaptureButton.removeFrom(map);
        captureButton.addTo(map);
    } else {
        $('#map').css('cursor', 'crosshair');
        capture = true;
        captureButton.removeFrom(map);
        uncaptureButton.addTo(map);
    }
}
L.mapbox.accessToken = 'pk.eyJ1Ijoib3BlbmF0bGFzbWFwYm94IiwiYSI6ImNpbHRlYzc3ZDAwMmR3MW02Z3FsYWxwNXcifQ.rwXGRavf1bh9ZW6zQn9cMg';

var map = L.map('map', {fullscreenControl: true}, null).setView([48.61, 16.93], 5);
// OpenAtlas uses free basemaps from openstreetmap and mapbox.com, change username,
var baseMaps = {
    Landscape: L.tileLayer('http://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png', {attribution: '&copy; <a href="http://www.thunderforest.com">Thunderforest Landscape '}),
    Openstreetmap: L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap </a> '}),
    Opencyclemap: L.tileLayer('http://{s}.tile.opencyclemap.org/cycle/{z}/{x}/{y}.png', {attribution: '&copy; <a href="http://www.opencyclemap.org/">OpenCycleMap ' }),
    GoogleSattelite: L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{subdomains:['mt0','mt1','mt2','mt3'], attribution: '&copy; Google Maps '}),
};
baseMaps.Landscape.addTo(map);
L.control.layers(baseMaps).addTo(map);
L.control.scale().addTo(map);
var marker; // temporary marker for coordinate capture
var capture = false; // var to store whether control is active or not
var captureButton = L.easyButton('topright', 'fa-plus-square-o', function () {
    switchInput();
}, 'capture coordinates');
var uncaptureButton = L.easyButton('topright', 'fa-minus-square-o', function () {
    switchInput();
}, 'uncapture coordinates');
uncaptureButton.removeFrom(map);

// variable to determine if captureButton is added or not (E.g. only added in place edit or insert mode, not in place list)
var coordcaptureon = false;
var myurl = window.location.href;
var parts = myurl.split("/");
var result = parts[parts.length - 1];
if (myurl.indexOf('update') >= 0) {
    var coordcaptureon = true;
}
if (myurl.indexOf('insert') >= 0) {
    var coordcaptureon = true;
}
if (!coordcaptureon) {
    captureButton.removeFrom(map);
}

map.on('click', function (e) {
    if (capture) {
        if (typeof (marker) !== 'object') {
            marker = new L.marker(e.latlng, {draggable: true});
            marker.addTo(map);
            var wgs84 = (marker.getLatLng());
            document.getElementById('northing').value = wgs84.lat;
            document.getElementById('easting').value = wgs84.lng;
        } else {
            marker.setLatLng(e.latlng);
            marker.on('dragend', function (event) {
                var marker = event.target;
                var position = marker.getLatLng();
                document.getElementById('northing').value = position.lat;
                document.getElementById('easting').value = position.lng;
            });
        }
        var wgs84 = marker.getLatLng();
        marker.on('dragend', function (event) {
            var marker = event.target;
            var position = marker.getLatLng();
            document.getElementById('northing').value = position.lat;
            document.getElementById('easting').value = position.lng;
        });
        document.getElementById('northing').value = wgs84.lat;
        document.getElementById('easting').value = wgs84.lng;
    }
});

if (jsonMarker != "") {
    var sitesmarkers = L.mapbox.featureLayer(); // define a layer for sitedata
    sitesmarkers.on('layeradd', setSitesInfo); // trigger popup info creation when layer is added
    sitesmarkers.setGeoJSON(jsonMarker); //set layer content to geojson
    map.addLayer(sitesmarkers);

    if (!(myurl.indexOf('place/') >= 0)) {
        map.fitBounds(sitesmarkers)
    }
    if (myurl.indexOf('insert') >= 0) {
        map.fitBounds(sitesmarkers)
    }
    map.panBy(new L.Point(0,-20));
}

var namecontrol = L.control.geonames({ // add geosearch
    username: 'openatlas', // Geonames account username.  Must be provided
    zoomLevel: 12, // Max zoom level to zoom to for location.  If null, will use the map's max zoom level.
    maxresults: 8, // Maximum number of results to display per search
    className: 'fa fa-globe', // class for icon
    workingClass: 'fa-spin', // class for search underway
});

var searchsites = L.control.Sitesearch(); //add sitesearch element
map.addControl(searchsites);
map.addControl(namecontrol);

sitesmarkers.eachLayer(function (marker) {
    if (marker.feature.properties.uid === result) {
        coords = marker.getLatLng();
        map.setView(coords, 14);
        map.panBy(new L.Point(0, -150));
        marker.openPopup();
    }
});

