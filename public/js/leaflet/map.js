
var coordcapture;
var coordcaptureimg;
var gispoints;

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
        '<div id="mypopup"><div id="popuptitle">' + marker.toGeoJSON().properties.title + '</b> <br> </div>' +
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
    if (!(myurl.indexOf('place/') >= 0)) {
        map.fitBounds(sitesmarkers)
    }
    if (myurl.indexOf('insert') >= 0) {
        map.fitBounds(sitesmarkers)
    }
    //map.panBy(new L.Point(0, -20));
}

var namecontrol = L.control.geonames({// add geosearch
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

var polygons = [{"type": "FeatureCollection", "features": [{"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.921749, 48.61195], [16.922881, 48.611925], [16.923533, 48.611902], [16.923899, 48.612708], [16.922325, 48.612788], [16.921957, 48.612428], [16.921749, 48.61195]]]}, "properties": {"title": "Hohenau Sst. 1 Gest\u00fctwiese", "description": "Ungef\u00e4hre Ausdehnung der Fundstreuung"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.896652, 48.603598], [16.896649, 48.603603], [16.896651, 48.603606], [16.896658, 48.603607], [16.896674, 48.603609], [16.89668, 48.60361], [16.896682, 48.603609], [16.896684, 48.603607], [16.896686, 48.603605], [16.896685, 48.603603], [16.896684, 48.603601], [16.896679, 48.603599], [16.896665, 48.603597], [16.896656, 48.603596], [16.896652, 48.603598]]]}, "properties": {"title": "Grab 131", "description": "Grabgrubenumriss"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.933717, 48.605862], [16.933866, 48.605941], [16.933981, 48.606012], [16.934054, 48.606055], [16.934107, 48.606099], [16.934158, 48.606148], [16.934245, 48.606223], [16.934335, 48.606275], [16.934417, 48.606319], [16.934486, 48.606361], [16.934529, 48.60639], [16.934542, 48.606428], [16.934574, 48.606451], [16.934609, 48.606453], [16.934641, 48.606442], [16.934666, 48.606424], [16.934668, 48.606387], [16.934661, 48.606327], [16.934675, 48.606267], [16.934675, 48.606226], [16.934678, 48.606193], [16.934696, 48.606138], [16.934712, 48.606095], [16.934734, 48.606045], [16.934754, 48.605988], [16.934782, 48.605946], [16.934805, 48.605892], [16.934807, 48.605852], [16.934761, 48.605795], [16.934686, 48.605734], [16.934565, 48.605631], [16.934365, 48.605441], [16.934215, 48.605287], [16.934096, 48.605145], [16.933898, 48.605007], [16.933811, 48.604966], [16.933732, 48.604973], [16.933624, 48.605007], [16.933526, 48.605039], [16.933489, 48.605071], [16.933428, 48.605146], [16.933388, 48.60521], [16.933372, 48.605302], [16.933329, 48.605397], [16.933717, 48.605862]]]}, "properties": {"title": "F\u00f6hrenh\u00fcgel", "description": "Ungef\u00e4hrer Umfang nach Lidar"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934356, 48.605617], [16.934399, 48.605603], [16.934389, 48.60559], [16.93435, 48.605606], [16.934356, 48.605617]]]}, "properties": {"title": "Schnitt 002", "description": "Mitscha-M\u00e4rheim\/Schultes 1953"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.93405, 48.605713], [16.934416, 48.605663], [16.934414, 48.605654], [16.934046, 48.605701], [16.93405, 48.605713]]]}, "properties": {"title": "Schnitt 009", "description": "Friesinger 1968"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.933791, 48.605876], [16.933818, 48.605866], [16.933783, 48.605833], [16.933762, 48.60584], [16.933791, 48.605876]]]}, "properties": {"title": "Schnitt 004", "description": "Mitscha-M\u00e4rheim\/Schultes 1953"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934499, 48.605813], [16.934553, 48.605795], [16.934539, 48.605779], [16.934487, 48.605799], [16.934499, 48.605813]]]}, "properties": {"title": "Schnitt 005", "description": "Mitscha-M\u00e4rheim\/Schultes 1953"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934429, 48.605744], [16.934472, 48.605729], [16.934463, 48.605715], [16.934418, 48.605731], [16.934429, 48.605744]]]}, "properties": {"title": "Schnitt 006", "description": "Mitscha-M\u00e4rheim\/Schultes 1953"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934457, 48.60592], [16.934666, 48.605951], [16.934669, 48.605939], [16.934461, 48.605906], [16.934457, 48.60592]]]}, "properties": {"title": "Schnitt 008", "description": "Friesinger 1968"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934431, 48.606111], [16.934581, 48.606127], [16.934583, 48.606116], [16.934437, 48.606099], [16.934431, 48.606111]]]}, "properties": {"title": "Schnitt 007", "description": "Friesinger 1968"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934242, 48.605711], [16.934289, 48.605702], [16.934282, 48.605685], [16.934237, 48.60569], [16.934242, 48.605711]]]}, "properties": {"title": "Schnitt 011", "description": "Friesinger 1968"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934235, 48.605711], [16.934227, 48.605694], [16.934172, 48.6057], [16.934178, 48.605719], [16.934235, 48.605711]]]}, "properties": {"title": "Schnitt 012", "description": "Friesinger 1968"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934393, 48.605826], [16.934419, 48.605828], [16.934421, 48.605816], [16.934395, 48.605815], [16.934393, 48.605826]]]}, "properties": {"title": "Schnitt 010", "description": "Friesinger 1968"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.934246, 48.605946], [16.93432, 48.605918], [16.93431, 48.605907], [16.934237, 48.605935], [16.934246, 48.605946]]]}, "properties": {"title": "Schnitt 001", "description": "Mitscha-M\u00e4rheim\/Schultes 1953"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.923684, 48.606174], [16.923037, 48.606133], [16.922801, 48.60663], [16.922743, 48.608201], [16.923155, 48.608233], [16.923566, 48.608226], [16.923684, 48.606174]]]}, "properties": {"title": "Hohenau Sst. 2", "description": "Ungef\u00e4hre Ausdehnung der Fundstreuung"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.919635, 48.605684], [16.918381, 48.60564], [16.918633, 48.605052], [16.919519, 48.605206], [16.919635, 48.605684]]]}, "properties": {"title": "Hohenau Sst. 3", "description": "Ungef\u00e4hre Ausdehnung der Fundstreuung"}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.903736, 48.623565], [16.903745, 48.623566], [16.903748, 48.623564], [16.903754, 48.623559], [16.903753, 48.623555], [16.903752, 48.623549], [16.903746, 48.623547], [16.903737, 48.623548], [16.903731, 48.623551], [16.903729, 48.62356], [16.903736, 48.623565]]]}, "properties": {"title": "Brandgrab 001", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.903735, 48.623088], [16.903764, 48.623096], [16.903768, 48.623084], [16.903739, 48.623076], [16.903735, 48.623088]]]}, "properties": {"title": "K\u00f6rpergrab 004", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.90377, 48.623098], [16.903797, 48.623106], [16.903802, 48.623095], [16.903775, 48.623087], [16.90377, 48.623098]]]}, "properties": {"title": "K\u00f6rpergrab 005", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.903398, 48.622908], [16.903414, 48.622932], [16.903428, 48.622929], [16.903411, 48.622903], [16.903398, 48.622908]]]}, "properties": {"title": "K\u00f6rpergrab 006", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.903564, 48.623063], [16.903597, 48.623072], [16.903601, 48.623058], [16.90357, 48.62305], [16.903564, 48.623063]]]}, "properties": {"title": "K\u00f6rpergrab 009", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.904262, 48.623271], [16.90427, 48.623274], [16.904284, 48.623247], [16.904275, 48.623245], [16.904262, 48.623271]]]}, "properties": {"title": "K\u00f6rpergrab 007", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.903731, 48.623113], [16.903752, 48.623119], [16.903756, 48.623108], [16.903737, 48.623103], [16.903731, 48.623113]]]}, "properties": {"title": "K\u00f6rpergrab 008", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.903702, 48.623053], [16.903738, 48.623063], [16.903746, 48.623052], [16.90371, 48.623041], [16.903702, 48.623053]]]}, "properties": {"title": "K\u00f6rpergrab 003", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.90368, 48.622994], [16.903709, 48.623001], [16.903714, 48.62299], [16.903684, 48.622983], [16.90368, 48.622994]]]}, "properties": {"title": "K\u00f6rpergrab 002", "description": null}}, {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[16.903683, 48.622973], [16.903716, 48.622981], [16.903719, 48.622973], [16.90372, 48.622969], [16.903687, 48.622961], [16.903687, 48.622961], [16.903683, 48.622973]]]}, "properties": {"title": "K\u00f6rpergrab 001", "description": null}}]}];

/*var polygons = [{
 "type": "FeatureCollection",
 "features":[
 {
 "type": "Feature",
 "geometry": {
 "type": "Polygon",
 "coordinates": [[[16.921749, 48.61195], [16.922881, 48.611925], [16.923533, 48.611902], [16.923899, 48.612708], [16.922325, 48.612788], [16.921957, 48.612428], [16.921749, 48.61195]]]
 },
 "properties": {
 "title": "Hohenau Sst. 1 Gest\u00fctwiese",
 "description": "Ungef\u00e4hre Ausdehnung der Fundstreuung"
 }
 },
 {
 "type": "Feature",
 "geometry": {
 "type": "Polygon",
 "coordinates": [[[16.896652, 48.603598], [16.896649, 48.603603], [16.896651, 48.603606], [16.896658, 48.603607], [16.896674, 48.603609], [16.89668, 48.60361], [16.896682, 48.603609], [16.896684, 48.603607], [16.896686, 48.603605], [16.896685, 48.603603], [16.896684, 48.603601], [16.896679, 48.603599], [16.896665, 48.603597], [16.896656, 48.603596], [16.896652, 48.603598]]]
 },
 "properties": {
 "title": "Grab 131",
 "description": "Grabgrubenumriss"
 }
 },*/


var polyglayer = L.mapbox.featureLayer();
polyglayer.setGeoJSON(polygons);

//features to choose in control menu
var overlayMaps = {
    Sites: sitesmarkers,
    Polygons: polyglayer,
}
baseMaps.Landscape.addTo(map);
L.control.layers(baseMaps, overlayMaps).addTo(map);
L.control.scale().addTo(map);

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
        selectedshape = objectId;
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
        '<div id="popup"><b>' + feature.properties.title + '</b> <br>' +
        '<div id="popup"><b>' + feature.properties.name + '</b> <br>' +
        '<i>' + feature.properties.siteType + '</i><br><br>' +
        '<div style="max-height:140px; overflow-y: auto">' + feature.properties.description + '</div>' +
        '<button onclick="editshape()"/>Edit</button> <button onclick="deleteshape()"/>Delete</button></div>'
        );
}

function setpopup2(feature, layer) {
    layer.bindPopup(
        '<div id="popup"><b>' + feature.properties.objectName + '</b> <br>' +
        '<div id="popup"><b>' + feature.properties.title + '</b> <br>' +
        '<i>' + feature.properties.type + '</i> <br> <br>' +
        '<div style="max-height:140px; overflow-y: auto">' + feature.properties.description + '<br> </div>'
        );
}

// bitte dynamisch generieren aus der Datenbank jeweils die Geometrien zu den Parent Places




if (myurl.indexOf('place/') >= 0) {
//    var mysites = L.geoJson(placepolygons, {onEachFeature: setpopup2}).addTo(map);
//    mysites.on('click', setObjectId);
    var mypoints = L.geoJson(gisPointSelected, {onEachFeature: setpopup2}).addTo(map);
    mypoints.on('click', setObjectId);
    var myextend = L.featureGroup([mypoints]);
    map.fitBounds(myextend);
    if (myurl.indexOf('insert') >= 0) {
        map.fitBounds(sitesmarkers)
//        map.removeLayer(mysites);
        map.removeLayer(mypoints);
    }
}

if (myurl.indexOf('update') >= 0) {
    $('#gisPoints').val(JSON.stringify(gisPointSelected));
//    map.removeLayer(mysites);
    map.removeLayer(mypoints);
//    var mysites = L.geoJson(placepolygons, {onEachFeature: setpopup}).addTo(map);
//    mysites.on('click', setObjectId);
    var mypoints = L.geoJson(gisPointSelected, {onEachFeature: setpopup}).addTo(map);
    mypoints.on('click', setObjectId);
    var myextend = L.featureGroup([mypoints]);
    map.fitBounds(myextend);
}
