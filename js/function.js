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
    for (prop in evenement) {
        if (prop == 'which')
            return(evenement.which);
    }
    return(evenement.keyCode);
}

function pressePapierNS6(evenement, touche) {
    var rePressePapierNS = /[cvxz]/i;

    for (prop in evenement)
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
    var toto = autorisation;
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

function isnull(selectid, fieldid){
  var selectvalue = $("#"+selectid+" :selected").val();
  console.log(selectvalue);
  if(selectvalue == 'ISNULL'){
      $("#"+fieldid).prop('disabled', true);
  }else{
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
