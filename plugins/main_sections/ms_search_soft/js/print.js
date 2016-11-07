function imprime_zone(titre, obj)

{
// Définie la zone à imprimer
	var zi = document.getElementById(obj).innerHTML;

// Ouvre une nouvelle fenetre
	var f = window.open("", "ZoneImpr", "height=800, width=800, toolbar=0, menubar=0, scrollbars=1, resizable=1, status=0, location=0, left=10, top=10");

// Définit le Style de la page
	f.document.body.style.color = '#000000';
	f.document.body.style.backgroundColor = '#FFFFFF';
	f.document.body.style.padding = "10px";

// Ajoute les Données
	f.document.title = titre;
	f.document.body.innerHTML += "" + zi + "";

// Imprime et ferme la fenetre
	f.window.print();
	f.window.close();
	return true;
}

