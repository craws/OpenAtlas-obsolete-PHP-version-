
var coordcapture;
var coordcaptureimg;
var gispoints;
var editon = 0;

function interoff() {
    capture = false;
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
    if (coordcapture) {
        document.getElementById('map').style.cursor = 'crosshair';
        capture = true;
    }
    if (coordcaptureimg) {
        document.getElementById('map').style.cursor = 'crosshair';
    }
}
var togglebtn;



function togglebtns() {
    if (togglebtn === 0) {
        if (typeof (polygonbtn) == 'object') {
            polygonbtn.removeFrom(map);
        }
        if (typeof (polylinebtn) == 'object') {
            polylinebtn.removeFrom(map);
        }
        if (typeof (imageloadbtn) == 'object') {
            imageloadbtn.removeFrom(map);
        }
        if (typeof (areabutton) == 'object') {
            areabutton.removeFrom(map);
        }
        if (typeof (pointbutton) == 'object') {
            pointbutton.removeFrom(map);
        }
        if (typeof (admunitbutton) == 'object') {
            admunitbutton.removeFrom(map);
        }
        if (typeof (histregbutton) == 'object') {
            histregbutton.removeFrom(map);
        }
        togglebtn = 1;
    } else {
        coordcapture = false;
        if (typeof (polygonbtn) == 'object') {
            map.addControl(polygonbtn);
        }
        if (typeof (polylinebtn) == 'object') {
            map.addControl(polylinebtn);
        }
        if (typeof (imageloadbtn) == 'object') {
            map.addControl(imageloadbtn);
        }
        if (typeof (areabutton) == 'object') {
            map.addControl(areabutton);
        }
        if (typeof (pointbutton) == 'object') {
            map.addControl(pointbutton);
        }
        if (typeof (admunitbutton) == 'object') {
            map.addControl(admunitbutton);
        }
        if (typeof (histregbutton) == 'object') {
            map.addControl(histregbutton);
        }
        //document.getElementById('selectunit').style.display = 'none';
        $("#jstree").jstree("close_all");
        $('#jstree').jstree("deselect_all");
        //document.getElementById('saveadmbtn').disabled = true;
        togglebtn = 0;
        capture = false;
        coordcapture = false;
        coordcaptureimg = false;
        marker = '';
        updategeojson();
        interon();
    }
}


function interonoff(element) { //disable map dragging when cursor is e.g. in search input field.
    $(element).hover(function () {
        interoff();
    }, function () {
        interon();
    });
}



function setSitesInfo(e) { //set Popup Information of existing sites
    var marker = e.layer;
    marker.bindPopup(
        '<div id="mypopup"><div id="popuptitle">' + marker.toGeoJSON().properties.title + '</strong> <br> </div>' +
        '<div id="popuptype"><i>' + marker.toGeoJSON().properties.siteType + '</i> <br> <br></div>' +
        '<div style="max-height:100px; max-width:200px; overflow-y: auto">' + marker.toGeoJSON().properties.objectDescription + '<br></div></div><br>' +
        '<div style="max-height:100px; max-width:200px; overflow-y: auto">' + marker.toGeoJSON().properties.shapeType + '<br></div></div>' +
        '<a href="/admin/place/view/id/' + marker.feature.properties.objectId + '">Details</a>',
        {autoPanPaddingTopLeft: new L.Point(40, 10), autoPanPaddingBottomRight: new L.Point(50, 10)}
    );
}

L.mapbox.accessToken = 'pk.eyJ1Ijoib3BlbmF0bGFzbWFwYm94IiwiYSI6ImNpbHRlYzc3ZDAwMmR3MW02Z3FsYWxwNXcifQ.rwXGRavf1bh9ZW6zQn9cMg';
var map = L.map('map', {fullscreenControl: true}, null).setView([48.61, 16.93], 5);
var baseMaps = {
    Landscape: L.tileLayer('http://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png', {attribution: '&copy; <a href="http://www.thunderforest.com">Thunderforest Landscape '}),
    Openstreetmap: L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap </a> '}),
    Opencyclemap: L.tileLayer('http://{s}.tile.opencyclemap.org/cycle/{z}/{x}/{y}.png', {attribution: '&copy; <a href="http://www.opencyclemap.org/">OpenCycleMap '}),
    GoogleSattelite: L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {subdomains: ['mt0', 'mt1', 'mt2', 'mt3'], attribution: '&copy; Google Maps '}),
};

var marker; // temporary marker for coordinate capture
var capture = false; // var to store whether control is active or not

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
if (gisPointAll != "") {
    var sitesmarkers = L.mapbox.featureLayer(); // define a layer for sitedata
    sitesmarkers.on('layeradd', setSitesInfo); // trigger popup info creation when layer is added
    sitesmarkers.on('layeradd', function (e) {
        var marker = e.layer;
        marker.setIcon(L.icon({iconUrl: "/js/leaflet/images/marker-icon_all.png", iconAnchor: [12, 41], popupAnchor: [0, -34]}));
    });
    sitesmarkers.setGeoJSON(gisPointAll); //set layer content to geojson
    map.addLayer(sitesmarkers);
    ;
}

if (gisPolygonAll != "") {
    var sitesPolygons = L.mapbox.featureLayer(); // define a layer for sitedata
    sitesPolygons.on('layeradd', setSitesInfo); // trigger popup info creation when layer is added
    sitesPolygons.setGeoJSON(gisPolygonAll); //set layer content to geojson
}

if (gisPointSelected != "") {
    if (gisPolygonSelected == "") {
        var mypoints = L.geoJson(gisPointSelected, {onEachFeature: setpopup2}).addTo(map);
        mypoints.on('click', setObjectId);
        setTimeout(function () {
            map.fitBounds(mypoints, {maxZoom: 18});
        }, 1);
    } else
    {
        var mypoints = L.geoJson(gisPointSelected, {onEachFeature: setpopup2}).addTo(map);
        mypoints.on('click', setObjectId);
        var mysites = L.geoJson(gisPolygonSelected, {onEachFeature: setpopup2}).addTo(map);
        mysites.on('click', setObjectId);
        var myextend = L.featureGroup([mypoints, mysites]);
        setTimeout(function () {
            map.fitBounds(myextend, {maxZoom: 18});
        }, 1);
    }

} else {
    if (gisPointAll != "") {
        setTimeout(function () {
            map.fitBounds(sitesmarkers, {maxZoom: 18});
        }, 1);
    }
}

if (gisPointSelected == "") {
    console.log(gisPolygonSelected);
    if (gisPolygonSelected != "") {
        var mysites = L.geoJson(gisPolygonSelected, {onEachFeature: setpopup2}).addTo(map);
        mysites.on('click', setObjectId);
    } else {
        if (gisPointAll != "") {
            map.fitBounds(sitesmarkers, {maxZoom: 18});
        }
    }
}

if (myurl.indexOf('insert') >= 0) {
    $('#gisPoints').val('[]');
    $('#gisPolygons').val('[]');
    if (mypoints) {
        map.removeLayer(mypoints);
    }
}

if (myurl.indexOf('update') >= 0) {
    $('#gisPoints').val(JSON.stringify(gisPointSelected));
    $('#gisPolygons').val(JSON.stringify(gisPolygonSelected));
    if (mysites)
    {
    map.removeLayer(mysites);
    }
    if (mypoints)
    {
    map.removeLayer(mypoints);
    }
    var mysites = L.geoJson(gisPolygonSelected, {onEachFeature: setpopup}).addTo(map);
    mysites.on('click', setObjectId);
    var mypoints = L.geoJson(gisPointSelected, {onEachFeature: setpopup}).addTo(map);
    mypoints.on('click', setObjectId);
}

var namecontrol = L.control.geonames({// add geosearch
    username: 'openatlas', // Geonames account username.  Must be provided
    zoomLevel: 12, // Max zoom level to zoom to for location.  If null, will use the map's max zoom level.
    maxresults: 8, // Maximum number of results to display per search
    className: 'fa fa-globe', // class for icon
    workingClass: 'fa-spin', // class for search underway
});

//var searchsites = L.control.Sitesearch(); //add sitesearch element
//map.addControl(searchsites);
map.addControl(namecontrol);


if (gisPointAll != "") {
    sitesmarkers.eachLayer(function (marker) {
        if (marker.feature.properties.uid === result) {
            coords = marker.getLatLng();
            map.setView(coords, 14);
            map.panBy(new L.Point(0, -150));
            marker.openPopup();
        }
    });
}





var polyglayer = L.mapbox.featureLayer();
polyglayer.setGeoJSON(gisPolygonAll);

//features to choose in control menu




if (gisPointAll != "") {

    var overlayMaps = {
        Sites: sitesmarkers,
    }
    if (gisPolygonAll != "")
    {
        var overlayMaps = {
            Sites: sitesmarkers,
            Polygons: sitesPolygons
        }
    }
}


baseMaps.Landscape.addTo(map);
L.control.layers(baseMaps, overlayMaps).addTo(map);
L.control.scale().addTo(map);

function preventpopup(event) {
    if (editon === 1) {
        map.closePopup();
    }
}

function setObjectId(e) {
    preventpopup();
    if (editon === 0) {
        var layer = e.layer;
        var feature = layer.feature;
        var objectId = feature.properties.objectId;
        geometrytype = feature.geometry.type;
        if (geometrytype == 'Point') {
            position = (e.latlng);
        }
        selectedshape = feature.properties.id;
        editlayer = e.layer;
        editmarker = e.marker;
        shapename = feature.properties.name;
        shapetype = feature.properties.type;
        description = feature.properties.description;
        objectName = feature.properties.title;
        helptext = 'Draw the shape of a physical thing if the precise extend is known';
        headingtext = 'Shape';
        if (shapetype == "area") {
            helptext = "Draw the area in which the physical thing is located. E.g. if its precise shape is not known but known to be within a certain area"
            headingtext = 'Area';
        }
        if (geometrytype == "Point") {
            helptext = "Drag the marker to the new location"
            headingtext = 'Point';
        }
    }
}

function setpopup(feature, layer) {
    layer.bindPopup(
        '<div id="popup"><strong>' + feature.properties.title + '</strong><br/>' +
        '<div id="popup"><i>' + feature.properties.siteType + '</i><br/>' +
        '<div id="popup"><strong>' + feature.properties.name + '</strong><br/>' +
        '<div style="max-height:140px; overflow-y: auto;">' + feature.properties.description + '<br/></div>' +
        '<i>' + feature.properties.shapeType + '</i><br/><br/>' +
        '<button onclick="editshape()"/>Edit</button> <button onclick="deleteshape()"/>Delete</button></div>'
        );
}

function setpopup2(feature, layer) {
    layer.bindPopup(
        '<div id="popup"><strong>' + feature.properties.title + '</strong><br/>' +
        '<div id="popup"><i>' + feature.properties.siteType + '</i><br/>' +
        '<div id="popup"><strong>' + feature.properties.name + '</strong><br/>' +
        '<div style="max-height:140px; overflow-y: auto;">' + feature.properties.description + '<br/></div>' +
        '<i>' + feature.properties.shapeType + '</i><br/><br/>'
        );
}


