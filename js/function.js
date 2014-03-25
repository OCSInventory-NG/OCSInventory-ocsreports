function convertToUpper(v_string){
         v_string.value=v_string.value.toUpperCase();
}

function codeTouche(evenement) {
        for (prop in evenement) {
                if(prop == 'which') return(evenement.which);
        }
        return(evenement.keyCode);
}
		
function pressePapierNS6(evenement,touche){
        var rePressePapierNS = /[cvxz]/i;

        for (prop in evenement) if (prop == 'ctrlKey') isModifiers = true;
        if (isModifiers) return evenement.ctrlKey && rePressePapierNS.test(touche);
        else return false;
}
			
function scanTouche(evenement,exReguliere) {
        var reCarSpeciaux = /[\x00\x08\x0D\x03\x16\x18\x1A]/;
        var reCarValides = exReguliere;
        var codeDecimal  = codeTouche(evenement);
        var car = String.fromCharCode(codeDecimal);
        var autorisation = reCarValides.test(car) || reCarSpeciaux.test(car) || pressePapierNS6(evenement,car);
        var toto = autorisation;
        return autorisation;
}		

		
function scrollHeaders() {
		var monSpan = document.getElementById("headers");
		if( monSpan ) {
			if( document.body.scrollTop > 200) {
				monSpan.style.top = (( Math.ceil(document.body.scrollTop / 27)) * 27) + 3;		
				monSpan.style.visibility = 'visible';
				// 15 Netsc 8ie
			}
			else
				monSpan.style.visibility = 'hidden';
		}
	}
	
function wait( sens ) {	
	var mstyle = document.getElementById('wait').style.display	= (sens!=0?"block" :"none");	
}

function ruSure( pageDest ) {
	if( confirm("?") )
		window.location = pageDest;
}

function post(form_name){	
	document.getElementById(form_name).submit();
}
	
function tri(did,hidden_name,did2,hidden_name2,form_name){
		document.getElementById(hidden_name).value=did;
		document.getElementById(hidden_name2).value=did2;
		post(form_name);
}
function confirme(aff,did,form_name,hidden_name,lbl){
	if(confirm(lbl+aff+'?')){
		garde_valeur(did,hidden_name);
		post(form_name);
	}
}
function garde_valeur(did,hidden_name){
		document.getElementById(hidden_name).value=did;
		
}
function pag(did,hidden_name,form_name){
		garde_valeur(did,hidden_name);
		post(form_name);
}

function verif_field(field_name_verif,field_submit,form_name) {
	if (document.getElementById(field_name_verif).value == '')	{
		document.getElementById(field_name_verif).style.backgroundColor = 'RED';
	}else {
		pag(field_submit,field_submit,form_name);
	}
}

function show_menu(id,all_menus) {
	var menus_array=all_menus.split('|');

	for (var i=0;i<menus_array.length;i++) { 		//Hidding all menus by default
		if (document.getElementById(menus_array[i]) != null)
		document.getElementById(menus_array[i]).style.display='none'; 
	}
 
	if (id != "nomenu") { 
		document.getElementById(id).style.display='block'; 
	}
}
		
function clic(id,val) {
	document.getElementById('ACTION_CLIC').action = id;
	document.getElementById('RESET').value=val;
	document.forms['ACTION_CLIC'].submit();
}

function ajaxtable(table_name,form_name){
	var table_id = "table#"+table_name;
	var table = $(table_id).DataTable();
	var info_page = table.page.info();
	var length = table.page.len();
	var data = $( form_name ).serializeArray();
	data.push({'name':table_name+'_page','value':info_page.page});
	$('#'+table_name+'_processing').css( "visibility","inline" );
	$(form_name).load(window.location.search+" "+form_name+">*",data,function(){
		page = info_page.page * length;
		table = $(table_id).dataTable({
			"processing": true,
			'pageLength' : length,
			 "displayStart": page,
		});
	console.log(table_id+"a ete rechargee en ajax");
		
	});
}				
