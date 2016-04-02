
function createOverlay(name, title, multi) {
    /* remove line below and use multiple = false as function param when netbeans fix for 257826 available */
    var multiple = typeof multi !== 'undefined' ?  multi : false;
    $("#" + name + "Overlay").click(function () {
        $("#" + name + "Dialog").dialog("close");
    });
    $("#" + name + "Button").click(function () {
        $("#" + name + "Overlay").height($(window).height());
        $("#" + name + "Overlay").width($(window).width());
        $("#" + name + "Overlay").fadeTo(1, 0.6);
        $("#" + name + "Dialog").dialog({
            position: {my: "center top", at: "center top+80", of: window},
            closeText: "X",
            title: title,
            closeOnEscape: true,
            width: "auto",
            height: "auto",
            close: function () {
                if(multiple) {
                    selectFromTableMulti(name);
                }
                $("#" + name + "Overlay").css("display", "none");
            }
        });
        $("#" + name + "Table").trigger('sorton', [[[0, 0]]]);
    });
}

function createTableOverlayMulti(name, title) {
    $("#" + name + "Overlay").click(function () {
        $("#" + name + "Dialog").dialog("close");
    });
    $("#" + name + "Button").click(function () {
        $("#" + name + "Overlay").height($(window).height());
        $("#" + name + "Overlay").width($(window).width());
        $("#" + name + "Overlay").fadeTo(1, 0.6);
        $("#" + name + "Dialog").dialog({
            position: {my: "center top", at: "center top+80", of: window},
            closeText: "X",
            title: title,
            closeOnEscape: true,
            width: "auto",
            height: "auto",
            close: function () {
                $("#" + name + "Overlay").css("display", "none");
            }
        });
        $("#" + name + "Table").trigger('sorton', [[[0, 0]]]);
    });
}

function createTreeOverlay(name, title, multi) {
    /* remove line below and use multiple = false as function param when netbeans fix for 257826 available */
    var multiple = typeof multi !== 'undefined' ?  multi : false;
    $("#" + name + "Overlay").click(function () {
        $("#" + name + "Dialog").dialog("close");
    });
    $("#" + name + "Button").click(function () {
        $("#" + name + "Overlay").height($(window).height());
        $("#" + name + "Overlay").width($(window).width());
        $("#" + name + "Overlay").fadeTo(1, 0.6);
        $("#" + name + "Dialog").dialog({
            position: {my: "center top", at: "center top+80", of: window},
            closeText: "X",
            title: title,
            closeOnEscape: true,
            width: "auto",
            height: "auto",
            close: function () {
                if(multiple) {
                    selectFromTreeMulti(name);
                }
                $("#" + name + "Overlay").css("display", "none");
            }
        });
    });
}

function selectFromTree(name, id, text) {
    $("#" + name + "Id").val(id);
    $("#" + name + "Button").val(text);
    $("#" + name + "Button").focus(); /* to refresh/fill button and remove validation errors */
    if ($('#' + name + 'Dialog').hasClass("ui-dialog-content") &&  $('#' + name + 'Dialog').dialog("isOpen")) {
        $('#' + name + 'Dialog').dialog('close');
    }
    $("#" + name + "Clear").show();
}

function selectFromTreeMulti(name) {
    var ids = $('#' + name + 'Tree').jstree('get_selected');
    var checkedNames = '';
    ids.forEach(function(item, index, array){
        var node = $('#' + name + 'Tree').jstree().get_node(item);
        checkedNames += node['text'] + "<br/>";
    });
    $("#" + name + "Id").val(ids);
    $("#" + name + "Selection").html(checkedNames);
}

function selectFromTable(element, table, id) {
    $("#" + table + "Button").rules('remove', 'required');
    $("#" + table + "Button").val(element.innerHTML);
    $("#" + table + "Id").attr('value', id);
    $("#" + table + "Button").focus(); /* to refresh/fill button and remove validation errors */
    $(".ui-dialog-titlebar-close").trigger('click');
    $("#" + table + "Clear").show();
}

function selectFromTableMulti(name) {
    var checkedNames = '';
    var ids = [];
    $(".multiTableSelect").each(function() {
        if ($(this).is(':checked')) {
            checkedNames += $(this).val() + "<br/>";
            ids.push($(this).attr('id'));
        }
    });
    $("#" + name + "Selection").html(checkedNames);
    $("#" + name + "Ids").val(ids.join(','));
    $("label.error").hide();
}

function clearSelect(table) {
    $("#" + table + "Button").val('');
    $("#" + table + "Id").val('');
    $("#" + table + "Tree").jstree("deselect_all");
    $("#" + table + "Clear").hide();
}

function ajaxAddField(fieldName, elementId) {
    $.ajax({
        type: "POST",
        url: "/admin/function/add-field",
        data: "name=" + fieldName + ",elementId=" + elementId,
        success: function (newElement) {
            $("#" + fieldName + "ElementAdd").before(newElement);
            $("#" + fieldName + "ElementId").val(++elementId);
        }
    });
}

function ajaxBookmark(entityId) {
    $.ajax({
        type: "POST",
        url: "/admin/function/bookmark",
        data: "entityId=" + entityId,
        success: function (label) {
            $('#bookmark' + entityId).html(label);
        }
    });
}

$(document).ready(function () {
    $("#dateSwitcher").click(function () {
        $(".dateSwitch").toggleClass('display-none');
    });
});

function resizeText(multiplier) {
    if (document.body.style.fontSize === "") {
        document.body.style.fontSize = "1.0em";
    }
    document.body.style.fontSize = parseFloat(document.body.style.fontSize) + (multiplier * 0.2) + "em";
}
