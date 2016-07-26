var postGisGeoJSON;
var selectedshape;
var editlayer;
var drawnstuff = L.featureGroup();
var layer;
var shapesyntax;
var type;
var drawlayer;
var editon = 0;
var togglebtn = 0;
var shapename;
var shapetype;
var shapedescription;
var geometrytype;
var marker;
var markerimg; // temporary marker for coordinate capture
var capture = false; // var to store whether control is active or not
var coordcapture = false;
var headingtext;





var polygonbtn = L.easyButton('topright',
        'fa-pencil-square-o',
        function () {
            shapetype = "shape";
            helptext = 'Draw the shape of a physical thing if the precise extend is known';
            headingtext = 'Shape';
            drawpolygon();
        },
        'Draw the shape of a physical thing if the precise extend is known'
        );

var areabutton = L.easyButton('topright',
        'fa-circle-o-notch',
        function () {
            shapetype = "area";
            helptext = 'Draw the area in which the physical thing is located. E.g. if its precise shape is not known but known to be within a certain area';
            headingtext = 'Area';
            drawpolygon();
        },
        'Draw the area in which the physical thing is located. E.g. if its precise shape is not known but known to be within a certain area'
        );

var pointbutton = L.easyButton('topright',
        'fa-map-marker',
        function () {
            helptext = 'Set a marker/point at the position where the physical thing is located';
            headingtext = 'Point';
            capture = true;
            coordcapture = true;
            map.removeLayer(postGisGeoJSON);
            setgeojsonwopopup();
            drawmarker();
        },
        'Set a marker/point at the position where the physical thing is located'
        );


var datainput = L.control();
datainput.onAdd = function (map) {
    var div = L.DomUtil.create('div', 'shapeinput');
    div.innerHTML += "<div id='insertform' style='display:block'>\
            <form id='shapeform' onmouseover='interoff()' onmouseout='interon()'>\
            <i id='headingtext'>  </i>\
                <i id='closebtn' title='close without saving' onclick='closemyformx()' class='fa'>X</i>\
                <i id='editclosebtn' title='close without saving' onclick='editclosemyform()' class='fa'>X</i>\
                <i id='markerclosebtn' title='close without saving' onclick='closemymarkerformx()' class='fa'>X</i>\
<br>\
                <p id='p1'>Hello World!</p>\
                <div style='display: none'>\
                    <label> Parent:</label>\
                    <span><input type='text' id='shapeparent' value='NULL'/></span> </div>\
                    <div id='namefield' style='display: block'>\
                    <span><input type='text' id='shapename' placeholder='enter name if desired'/></span> </div>\
                <span><textarea rows='3' cols='70' id='shapedescription' placeholder='here you can enter a description if necessary'/></textarea></span>\
                <span><input type='text' id='shapename' value='NULL'/></span>\
                <span><input type='text' id='shapetype' value='NULL'/></span>\
                <span><label id='eastinglabel' style='display: none'> Easting: </label>\<input type='text' id='popupeasting' placeholder='decimal degrees' /></span>\
                <span><label id='northinglabel' style='display: none'> Northing:</label>\<input type='text' id='popupnorthing' placeholder='decimal degrees' /></span>\
                <div style='display: none'>\
                    <label> Coordinates: </label>\
                    <span><textarea rows='4' cols='50' id='shapecoords'/></textarea></span>\
                    <label> Geometrytype: </label>\
                    <span><input type='text' id='geometrytype'/></span></div>\
            </form>\
            <input type='button' title='Reset values and shape' id='resetbtn' disabled value='Clear' onclick='resetmyform()'/>\
            <input type='button' title='Save shape' id='savebtn' disabled value='Save' onclick='savetodb()'/>\
            <input type='button' title='Save edits' id='editsavebtn' disabled value='Save' onclick='editsavetodb()'/>\
            <input type='button' title='Save marker' id='markersavebtn' disabled value='Save' onclick='savemarkertodb()'/>\
         </div>";

    return div;
    document.getElementById("headingtext").innerHTML = headingtext;
};
setgeojson();




function setpopup(feature, layer) {
    layer.bindPopup('<div id="popup"><b>' + feature.properties.parentname + '</b> <br>' +
        '<div id="popup"><b>' + feature.properties.title + '</b> <br>' +
                    '<i>' + feature.properties.type + '</i> <br> <br>' +
            '<div style="max-height:140px; overflow-y: auto">' + feature.properties.description + '<br> </div>' +
            '<button onclick="editshape()"/> Edit </button> <button onclick="deleteshape()"/>Delete</button></div>');
}


function setuid(e) {
    preventpopup();
    if (editon === 0) {
        var layer = e.layer;
        var feature = layer.feature;
        var uid = feature.properties.uid;
        geometrytype = feature.geometry.type;
        selectedshape = uid;
        editlayer = e.layer;
        shapename = feature.properties.title;
        shapetype = feature.properties.category;
        shapedescription = feature.properties.description;
        helptext = 'Draw the shape of a physical thing if the precise extend is known';
        headingtext = 'Shape';
        if (shapetype == "area") {
            helptext = "Draw the area in which the physical thing is located. E.g. if its precise shape is not known but known to be within a certain area"
            headingtext = 'Area';
        }
        ;
    }
}

function preventpopup(event) {
    if (editon === 1) {
        map.closePopup();
    }
}


function editshape()
{
    togglebtns();
    if (editon === 0)
    {

        map.addControl(datainput);
        document.getElementById("p1").innerHTML = helptext;
        document.getElementById("headingtext").innerHTML = headingtext;
        document.getElementById("shapecoords").value = 'empty';
        document.getElementById('savebtn').style.display = 'none';
        document.getElementById('resetbtn').style.display = 'none';
        document.getElementById('closebtn').style.display = 'none';
        document.getElementById('editclosebtn').style.display = 'block';
        document.getElementById('editsavebtn').style.display = 'block';
        //document.getElementById('shapename').value = shapename;
        document.getElementById('shapetype').value = shapetype;
        document.getElementById('shapedescription').value = shapedescription;
        $("#shapeform").on("input", function () {
            document.getElementById('editsavebtn').disabled = false;
        });
        map.closePopup();
        editon = 1;
        var mylayer = L.polygon(editlayer.getLatLngs()).addTo(map);
        map.removeLayer(editlayer);
        //alert(editlayer.getLatLngs());
        mylayer.options.editing || (mylayer.options.editing = {});
        mylayer.editing.enable();
        
        
        
        document.getElementById('geometrytype').value = geometrytype;
        mylayer.on('edit', function () {
            var latLngs = mylayer.getLatLngs();
            var latLngs; //to store coordinates of vertices
            var newvector = []; // array to store coordinates as numbers
            var type = geometrytype.toLowerCase();
            document.getElementById('editsavebtn').disabled = false;
            if (type != 'marker') {  //if other type than point then store array of coordinates as variable
                latLngs = mylayer.getLatLngs();
                for (i = 0; i < (latLngs.length); i++) {
                    newvector.push(' ' + latLngs[i].lng + ' ' + latLngs[i].lat);
                }
                
                ;
                if (type === 'polygon') {
                    newvector.push(' ' + latLngs[0].lng + ' ' + latLngs[0].lat); //if polygon add first xy again as last xy to close polygon
                    shapesyntax = '(' + newvector + ')';
                    returndata();
                }
                ;
                if (type === 'linestring') {
                    shapesyntax = newvector;
                    returndata();
                }
            }
            ;
            if (type === 'marker') {
                latLngs = layer.getLatLng();
                newvector = (' ' + latLngs.lng + ' ' + latLngs.lat);
                shapesyntax = 'ST_GeomFromText(\'POINT(' + newvector + ')\',4326);'

            }
            ;
        });
    }
}



//what happens, after stuff is drawn
map.on('draw:created', function (e)
{
    document.getElementById('savebtn').disabled = false;
    document.getElementById('resetbtn').disabled = false;
    drawnstuff.addLayer(e.layer); //add new geometry to layer
    type = e.layerType; //whatever geometry
    layer = e.layer;
    var latLngs; //to store coordinates of vertices
    var newvector = []; // array to store coordinates as numbers
    if (type != 'marker') {  //if other type than point then store array of coordinates as variable
        latLngs = layer.getLatLngs();
        for (i = 0; i < (latLngs.length); i++) {
            newvector.push(' ' + latLngs[i].lng + ' ' + latLngs[i].lat);
        }
        ;
        if (type === 'polygon') {
            newvector.push(' ' + latLngs[0].lng + ' ' + latLngs[0].lat); //if polygon add first xy again as last xy to close polygon
            shapesyntax = '(' + newvector + ')';
            returndata();
        }
        ;
        if (type === 'polyline') {
            shapesyntax = newvector;
            returndata();
        }
    }
    ;
    if (type === 'marker') {
        latLngs = layer.getLatLng();
        newvector = (' ' + latLngs.lng + ' ' + latLngs.lat);
        shapesyntax = 'ST_GeomFromText(\'POINT(' + newvector + ')\',4326);'
    }
    ;
});

function drawpolygon()
{
    drawlayer = new L.Draw.Polygon(map);
    geometrytype = "polygon";
    capture = false;
    startdrawing();
}

function drawpolyline()
{
    drawlayer = new L.Draw.Polyline(map);
    geometrytype = "linestring";
    startdrawing();
}


function startdrawing()
{
    map.addControl(datainput);
    resetmyform();
    map.addLayer(drawnstuff);
    drawlayer.enable();
    togglebtns();
    $("#shapeform").on("input", function () {
        document.getElementById('resetbtn').disabled = false;
    })
            ;
}



function returndata() {
    document.getElementById("shapecoords").value = shapesyntax;
}

function savetodb()
{
    document.getElementById('savebtn').style.display = 'none';
    var shapename = $('#shapename').val();
    var shapetype = $('#shapetype').val();
    var shapedescription = $('#shapedescription').val();
    var shapecoords = $('#shapecoords').val();
    var geometrytype = $('#geometrytype').val();
    var dataString = '&shapename=' + shapename + '&shapetype=' + shapetype + '&shapedescription=' + shapedescription + '&shapecoords=' + shapecoords + '&geometrytype=' + geometrytype;
    alert(dataString);
    $('#placeInfo').val($('#placeInfo').val() + dataString);
    //reloadgeojson();
    closemyform();
    ;
}

function editsavetodb()
{
    document.getElementById('editsavebtn').style.display = 'none';
    var uid = selectedshape;
    var shapename = $('#shapename').val();
    var shapetype = $('#shapetype').val();
    var shapedescription = $('#shapedescription').val();
    var shapecoords = $('#shapecoords').val();
    var geometrytype = $('#geometrytype').val();
    var dataString = 'shapename=' + shapename + '&shapetype=' + shapetype + '&shapedescription=' + shapedescription + '&shapecoords=' + shapecoords + '&geometrytype=' + geometrytype + '&uid=' + uid;
    alert(dataString);
    editclosemyform();
    }


function deleteshape()
{
    if (editon === 0) {
        var dataString = 'uid=' + selectedshape + '&geometrytype=' + geometrytype;
        $.ajax({
            type: "POST",
            url: "php/remove.php",
            data: dataString,
            success: function () {
                updategeojson();
            }
        });
    }
}

function reloadgeojson() {
    $.ajax({
        dataType: "json",
        url: "php/" + geometrytype + "_reload.php",
        success: function (data) {
            $(data.features).each(function (key, data) {
                postGisGeoJSON.addData(data);
                resetDrawLayer();
            });
        }
    }).error(function () {
    });
}

function updategeojson() {
    map.removeLayer(postGisGeoJSON);
    setgeojson();
}

function setgeojson()
{
    postGisGeoJSON = L.geoJson(undefined, {style: function (feature) {
            switch (feature.geometry.type) {
                case 'LineString':
                    return {color: "#000000", weight: 5}
                case 'Polygon':
                    return {fillColor: "#424242", color: "#000000", weight: 3}
            }
        },
        onEachFeature: setpopup}).addTo(map);

    postGisGeoJSON.on('click', setuid);

    $.ajax({
        dataType: "json",
        url: "php/linestring_update.php",
        success: function (data) {
            $(data.features).each(function (key, data) {
                postGisGeoJSON.addData(data);
            });
        }
    }).error(function () {
    });
    $.ajax({
        dataType: "json",
        url: "php/polygon_update.php",
        success: function (data) {
            $(data.features).each(function (key, data) {
                postGisGeoJSON.addData(data);
            });
        }
    }).error(function () {
    });
}


function setgeojsonwopopup()
{
    postGisGeoJSON = L.geoJson(undefined, {style: function (feature) {
            switch (feature.geometry.type) {
                case 'LineString':
                    return {color: "#000000", weight: 5}
                case 'Polygon':
                    return {fillColor: "#424242", color: "#000000", weight: 3, clickable: false}
            }
        },
        }).addTo(map);

    

    $.ajax({
        dataType: "json",
        url: "php/linestring_update.php",
        success: function (data) {
            $(data.features).each(function (key, data) {
                postGisGeoJSON.addData(data);
            });
        }
    }).error(function () {
    });
    $.ajax({
        dataType: "json",
        url: "php/polygon_update.php",
        success: function (data) {
            $(data.features).each(function (key, data) {
                postGisGeoJSON.addData(data);
            });
        }
    }).error(function () {
    });
}

function resetDrawLayer() {
    drawnstuff.removeLayer(layer);
}

function resetmyform() {
    document.getElementById('savebtn').style.display = 'block';
    map.closePopup();
    document.getElementById("shapeform").reset();
    document.getElementById("geometrytype").value = geometrytype;
    if (capture = false) {
        drawlayer.enable();
    }
    document.getElementById('savebtn').disabled = true;
    document.getElementById('resetbtn').disabled = true;
    document.getElementById("shapetype").value = shapetype;
    document.getElementById("p1").innerHTML = helptext;
    document.getElementById("headingtext").innerHTML = headingtext;
}

function closemyform() {
    datainput.removeFrom(map);
    togglebtns();
    drawlayer.disable();
    var coordcapture = false;
    interon();
    
}

function closemyformx() {
    datainput.removeFrom(map);
    drawnstuff.removeLayer(layer);
    togglebtns();
    drawlayer.disable();
    var coordcapture = false;
    interon();
    
}


function closemymarkerformx() {
    datainput.removeFrom(map);
    map.removeLayer(marker);
    togglebtns();
    coordcapture = false;
    capture = false;
    interon();
    }
    
 function closemymarkerform() {
    datainput.removeFrom(map);
    map.removeLayer(marker);
    togglebtns();
    coordcapture = false;
    capture = false;
    interon();
    }

function editclosemyform()
{
    editon = 0;
    datainput.removeFrom(map);
    togglebtns();
    //updategeojson();
    var coordcapture = false;
    interon();
}

map.on('click', function (e) {
    if (capture) {
        document.getElementById('markersavebtn').disabled = false;
        document.getElementById('geometrytype').value = 'point';
        if (typeof (marker) !== 'object') {
            marker = new L.marker(e.latlng, {draggable: true});
            marker.addTo(map);
            var wgs84 = (marker.getLatLng());
            document.getElementById('popupnorthing').value = wgs84.lat;
            document.getElementById('popupeasting').value = wgs84.lng;
        } else {
            marker.setLatLng(e.latlng);
            marker.on('dragend', function (event) {
                var marker = event.target;
                var position = marker.getLatLng();
                document.getElementById('popupnorthing').value = position.lat;
                document.getElementById('popupeasting').value = position.lng;
            });
        }
        var wgs84 = marker.getLatLng();
        marker.on('dragend', function (event) {
            var marker = event.target;
            var position = marker.getLatLng();
            document.getElementById('popupnorthing').value = position.lat;
            document.getElementById('popupeasting').value = position.lng;
        });
        document.getElementById('popupnorthing').value = wgs84.lat;
        document.getElementById('popupeasting').value = wgs84.lng;
    }
});

function drawmarker() {
    $('#map').css('cursor', 'crosshair');
    map.addControl(datainput);
    togglebtns();
    resetmyform();
    document.getElementById("p1").innerHTML = helptext;
    document.getElementById("headingtext").innerHTML = headingtext;
    document.getElementById('savebtn').style.display = 'none';
    document.getElementById('resetbtn').style.display = 'none';
    document.getElementById('closebtn').style.display = 'none';
    document.getElementById('markerclosebtn').style.display = 'block';
    document.getElementById('markersavebtn').style.display = 'block';
    document.getElementById('popupeasting').style.display = 'block';
    document.getElementById('popupnorthing').style.display = 'block';
    document.getElementById('eastinglabel').style.display = 'block';
    document.getElementById('northinglabel').style.display = 'block';
    ;
}


function savemarkertodb()
{
    capture = false;
    document.getElementById('savebtn').style.display = 'none';
    var shapename = $('#shapename').val();
    var shapetype = $('#shapetype').val();
    var shapedescription = $('#shapedescription').val();
    var shapecoords = $('#shapecoords').val();
    var geometrytype = $('#geometrytype').val();
    var northing = $('#popupnorthing').val();
    var easting = $('#popupeasting').val();
    var shapetype = 'centerpoint';
    var dataString = '&easting=' + easting + '&northing=' + northing + '&shapename=' + shapename + '&shapetype=' + shapetype + '&shapedescription=' + shapedescription + '&geometrytype=' + geometrytype;
    alert(dataString);
    L.marker([northing, easting]).addTo(map);
    closemymarkerform();
}

