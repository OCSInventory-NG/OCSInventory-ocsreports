/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
function convertToUpper(v_string) {
    v_string.value = v_string.value.toUpperCase();
}

function codeTouche(evenement) {
    for (var prop in evenement) {
        if (prop == 'which')
            return(evenement.which);
    }
    return(evenement.keyCode);
}

function pressePapierNS6(evenement, touche) {
    var rePressePapierNS = /[cvxz]/i;

    for (var prop in evenement)
        if (prop == 'ctrlKey')
            isModifiers = true;
    if (isModifiers)
        return evenement.ctrlKey && rePressePapierNS.test(touche);
    else
        return false;
}

function scanTouche(evenement, exReguliere) {
    var reCarSpeciaux = /[\x00\x08\x0D\x03\x16\x18\x1A]/;
    var reCarValides = exReguliere;
    var codeDecimal = codeTouche(evenement);
    var car = String.fromCharCode(codeDecimal);
    var autorisation = reCarValides.test(car) || reCarSpeciaux.test(car) || pressePapierNS6(evenement, car);
    return autorisation;
}


function scrollHeaders() {
    var monSpan = document.getElementById("headers");
    if (monSpan) {
        if (document.body.scrollTop > 200) {
            monSpan.style.top = ((Math.ceil(document.body.scrollTop / 27)) * 27) + 3;
            monSpan.style.visibility = 'visible';
            // 15 Netsc 8ie
        } else
            monSpan.style.visibility = 'hidden';
    }
}

function wait(sens) {
    var mstyle = document.getElementById('wait').style.display = (sens != 0 ? "block" : "none");
}

function ruSure(pageDest) {
    if (confirm("?"))
        window.location = pageDest;
}

function post(form_name) {
    document.getElementById(form_name).submit();
}

function tri(did, hidden_name, did2, hidden_name2, form_name) {
    document.getElementById(hidden_name).value = did;
    document.getElementById(hidden_name2).value = did2;
    post(form_name);
}
function confirme(aff, did, form_name, hidden_name, lbl) {
    if (confirm(lbl + aff + '?')) {
        garde_valeur(did, hidden_name);
        post(form_name);
    }
}
function garde_valeur(did, hidden_name) {
    document.getElementById(hidden_name).value = did;

}
function pag(did, hidden_name, form_name) {
    garde_valeur(did, hidden_name);
    post(form_name);
}

function verif_field(field_name_verif, field_submit, form_name) {
    if (document.getElementById(field_name_verif).value == '') {
        document.getElementById(field_name_verif).style.backgroundColor = 'RED';
    } else {
        pag(field_submit, field_submit, form_name);
    }
}

function clic(id, val) {
    document.getElementById('ACTION_CLIC').action = id;
    document.getElementById('RESET').value = val;
    document.forms['ACTION_CLIC'].submit();
}

$.extend($.fn.dataTableExt.oStdClasses, {
    "sFilterInput": "form-control input-sm",
    "sLengthSelect": "form-control input-sm"
});

$.extend(true, $.fn.dataTable.defaults, {
    "sDom":
            "<'row'<'dataTables_length_container'l><'dataTables_filter_container'f>r>" +
            "t" +
            "<'row'<'col-xs-6'i><'col-xs-6'p>>",
});


function changerCouleur(obj, state) {
    if (state == true) {
        bcolor = obj.style.backgroundColor;
        fcolor = obj.style.color;
        obj.style.backgroundColor = '#FFDAB9';
        obj.style.color = 'red';
        return true;
    } else {
        obj.style.backgroundColor = bcolor;
        obj.style.color = fcolor;
        return true;
    }
    return false;
}

function delete_cookie(name) {
    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    name = name.slice(0, -4);
    var table = $('table#'+name).DataTable();
    table.state.clear();
}

function reload(){
    location.reload();
}

function isnull(selectid, fieldid, fieldtype = null) {
    // operator value
    var selectvalue = $("#"+selectid+" :selected").val();
    if(selectvalue == 'MORETHANXDAY' || selectvalue == 'LESSTHANXDAY') {
        var parentElement = $("#"+fieldid).parent();
        parentElement.empty();
        parentElement.replaceWith('<input class="form-control" type="number" name="'+fieldid+'" id="'+fieldid+'" value="">');
    } else if((selectvalue != 'MORETHANXDAY' && selectvalue != 'LESSTHANXDAY')) {
        if($(".form_datetime").length == 0) {
            $.ajax({
                url: "ajax/calendarfield.php",
                type : "GET",
                data : "fieldid="+fieldid,
                success : function(data, status) {
                    $("#"+fieldid).replaceWith(data);
                }
            });
        }
    }

    if(selectvalue == 'ISNULL' || selectvalue == 'ISNOTEMPTY') {
        $("#"+fieldid).prop('disabled', true);
    } else {
        $("#"+fieldid).prop('disabled', false);
    }
}

/// show/hide
function hide(id, preview, perso){
	document.getElementById(id).style.display='none';
    document.getElementById(preview).style.display='';
    document.getElementById(perso).style.display='none';
}

function show(id, preview, perso){
	document.getElementById(id).style.display='';//'block'
    document.getElementById(preview).style.display='none';
    document.getElementById(perso).style.display='';
}

/* Set the width of the sidebar to 250px (show it) */
function openNav() {
    document.getElementById("mySidepanel").style.width = "25%";
    for(var i = 0; document.getElementById("news"+i) != null; i++){
      document.getElementById("news"+i).style.display='';
      document.getElementById("news"+i).style.textAlign='left';
      document.getElementById("news"+i).style.paddingLeft = '30px';
      document.getElementById("news"+i).style.paddingRight = '30px';
      document.getElementById("imagenews"+i).style.display = 'none';
      document.getElementById("linknews"+i).style.display = 'none';
      if(document.getElementById("contentmodifnews"+i) != null){
        document.getElementById("contentmodifnews"+i).style.display = '';
        document.getElementById("contentnews"+i).style.display = 'none';
      }
    }
    document.getElementById("return").style.display = 'none';
}

/* Set the width of the sidebar to 0 (hide it) */
function closeNav() {
    document.getElementById("mySidepanel").style.width = "0";
    for(var i = 0; document.getElementById("news"+i) != null; i++){
      document.getElementById("news"+i).style.display='';
      document.getElementById("news"+i).style.textAlign='left';
      document.getElementById("news"+i).style.paddingLeft = '30px';
      document.getElementById("news"+i).style.paddingRight = '30px';
      document.getElementById("imagenews"+i).style.display = 'none';
      document.getElementById("linknews"+i).style.display = 'none';
      if(document.getElementById("contentmodifnews"+i) != null){
        document.getElementById("contentmodifnews"+i).style.display = '';
        document.getElementById("contentnews"+i).style.display = 'none';
      }
    }
}

/* Open the sidenav */
function openfullNav(div) {
    document.getElementById("mySidepanel").style.width = "100%";

    for(var i = 0; document.getElementById("news"+i) != null; i++){
      if(document.getElementById("news"+i) != document.getElementById(div)){
        document.getElementById("news"+i).style.display='none';
      }
    }
    if(document.getElementById("contentmodif"+div) != null){
      document.getElementById("contentmodif"+div).style.display = 'none';
      document.getElementById("content"+div).style.display = '';
    }
    document.getElementById(div).style.textAlign = 'center';
    document.getElementById(div).style.paddingLeft = '30%';
    document.getElementById(div).style.paddingRight = '30%';
    document.getElementById("image"+div).style.display = '';
    document.getElementById("link"+div).style.display = '';
    document.getElementById("return").style.display = '';
}

function checkrequire(statut){
    if(statut == "OFF"){
      $('#NOTIF_MAIL_ADMIN').prop('required',false);
      $('#NOTIF_NAME_ADMIN').prop('required',false);
      $('#NOTIF_SMTP_HOST').prop('required',false);
      $('#NOTIF_PORT_SMTP').prop('required',false);
      $('#NOTIF_PROG_TIME').prop('required',false);
    }else{
      $('#NOTIF_MAIL_ADMIN').prop('required',true);
      $('#NOTIF_NAME_ADMIN').prop('required',true);
      $('#NOTIF_SMTP_HOST').prop('required',true);
      $('#NOTIF_PORT_SMTP').prop('required',true);
      $('#NOTIF_PROG_TIME').prop('required',true);
    }

}

function fuser_change(value) {
    var $fuserinput = $('.form-group-hidden');
    var $fuserinputtext = $('.form-group-debug option:selected').text();
    console.log($fuserinputtext);
    if (value === "5" && $fuserinputtext === "FUSER"){
        $fuserinput.removeClass("hidden");
    }
}

function show_hide_wol(id, check, button){
    checkbox = document.getElementById(check);
    wol = document.getElementById(id);
    send = document.getElementById(button)

    if(checkbox.checked){
        wol.style.display = '';
        send.style.display = 'none';
    } else {
        wol.style.display = 'none';
        send.style.display = '';
    }
}

function verif_champ_name(form, name){
    var champ = $('#'+name).val();
    if($.trim(champ) == ""){
        alert("Name can't be empty");
    }else{
        $('#'+form).submit();
    }
}

function searchInMIB() {
    // Declare variables
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("mib_info");
    tr = table.getElementsByTagName("tr");
  
    // Loop through all table rows, and hide those who don't match the search query
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[1];
        td2 = tr[i].getElementsByTagName("td")[3];
        if (td && td2) {
            txtValue = td.textContent || td.innerText;
            txtValue2 = td2.textContent || td2.innerText;

            if ((txtValue.toUpperCase().indexOf(filter) > -1) || (txtValue2.toUpperCase().indexOf(filter) > -1)) {
            tr[i].style.display = "";
            } else {
            tr[i].style.display = "none";
            }
        }
    }
}

function loadInteractions(os) {
    $('#interactions').removeClass("disabled ocs-disabled").addClass("active");
    $('#operatingsystem').hide();
    $('#os_selected').val(os);

    if(os == "windows") {
        $('#windowsInteractions').show();
    } else if(os == "linux") {
        $('#linuxInteractions').show();
    } else {
        $('#macosInteractions').show();
    }
}

function loadOptions(os, linkedoptions) {
    $.ajax({
        url: "ajax/teledeployoptions.php",
        type : "GET",
        data : "os="+os+"&linkedoptions="+linkedoptions,
        success : function(dataoptions, status) {
            $('#options').removeClass("disabled ocs-disabled").addClass("active");
            // If os == all test all os to hide
            if(os == "all") {
                $('#windowsInteractions').hide();
                $('#linuxInteractions').hide();
                $('#macosInteractions').hide();
            } else {
                $('#'+os+'Interactions').hide();
            }
            $('#deployment_options').append(dataoptions);
        }
    });
}

function verifPackageName(input) {
    var name = $(input).val();

    if(name.length >= 2) {
        $.ajax({
            url: "ajax/teledeployoptions.php",
            type : "GET",
            data : "name="+name,
            success : function(dataverif, status) {
                var result = jQuery.parseJSON(dataverif);
                if(result.file_exist == false) {
                    $(input).parent().parent().prepend("<p id='error_name' style='color:red;'>Name already exist</p>");
                    $('#valid').attr("disabled", true);
                } else {
                    $('#error_name').remove();
                    $('#valid').attr("disabled", false);
                }
            }
        });
    } 
}

function notifyUser() {
    active("NOTIFY_USER_div", $('#NOTIFY_USER').val());
}

function needDoneAction() {
    active("NEED_DONE_ACTION_div", $('#NEED_DONE_ACTION').val());
}

function disabled_checkbox(id) {
    var checkedValue = $('#'+id+':checked').val();
    if(checkedValue != undefined) {
        $('[id=selected_dupli]').each(function() {
            $(this).attr("disabled", true);
        });
    } else {
        $('[id=selected_dupli]').each(function() {
            $(this).attr("disabled", false);
        });
    }
}