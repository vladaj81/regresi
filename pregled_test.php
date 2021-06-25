<?php

require "../common/no_cache.php";
if (session_id() == '') {
	session_start();
}

if (isset($_SESSION['radnik']) && $_SESSION['radnik']) {
	$radnik = $_SESSION['radnik'];
	foreach ($_REQUEST as $kljuc => $vrednost) {
		${$kljuc} = $vrednost;
	}

	$conn = pg_connect("host=localhost dbname=stete user=zoranp");
	if (!$conn) {
		echo "Gre¹ka otvaranja konekcije prema SQL serveru.";
		exit;
	}

	$sql_rad_na_daljinu = "SELECT rad_na_daljinu FROM procenitelji WHERE radnik = $radnik";
	$rez_rad_na_daljinu = pg_query($conn, $sql_rad_na_daljinu);
	$niz_rad_na_daljinu = pg_fetch_assoc($rez_rad_na_daljinu);
	$rad_na_daljinu	    = $niz_rad_na_daljinu['rad_na_daljinu'];
} else {
	session_destroy();
	echo <<<EOF
		<script type="text/javascript">
		window.top.location = '/';
		</script>
EOF;

	exit;
}

$sql_dozvola = "SELECT id FROM prevara_privilegije where radnik_id=" . $radnik;
$upit_dozvola = pg_query($conn, $sql_dozvola);
$rezultat_dozvola = pg_fetch_assoc($upit_dozvola);

//UKLJUCIVANJE MODALNOG PROZORA ZA PRIKAZ MEJLOVA - DODAO VLADIMIR JOVANOVIC
include 'mail_pregled.php';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
	<title>PREDMET OD©TETNOG ZAHTEVA</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<link rel="stylesheet" type="text/css" href="../common/menistil.css">
	<link rel='stylesheet' type='text/css' href='../css/pregled_unos_zarko.css' />
	<link rel="stylesheet" type="text/css" href="zapisnik_dodatno/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="../css/ui.dropdownchecklist.standalone.css" />
	<script type="text/javascript" language="JavaScript" src="../js/proveraUnosa.js"></script>
	<script type="text/javascript" language="javascript" src="../js/jquery.js"></script>
	<!-- Dodato kako bi radio autocomplite - MARIJA 24.02.2015 -->
	<script type="text/javascript" language="javascript" src="../js/jquery-ui.js"></script>
	<!-- Dodato kako bi radio autocomplite - MARIJA 24.02.2015 -->
	<script type="text/javascript" language="JavaScript" src="../js/jquery.maskedinput.js"></script>
	<script type="text/javascript" language="JavaScript" src="../js/ui.dropdownchecklist.js"></script>
	<script type="text/javascript" language="JavaScript" src="mail_forma.js"></script>
	<script type="text/javascript" language="JavaScript" src="sluzbena_beleska.js"></script>
	<script type="text/javascript" language="javascript" src="../js/jquery.ui.datepicker-sr-SR.js"></script>
	<script type="text/javascript" language="JavaScript" src="dodatna_napomena_forma.js"></script>
	<script type="text/javascript" src="../js/tiny_mce/jquery.tinymce.js"></script>
	<style>
		.modal {
			display: none;
			/* Hidden by default */
			position: fixed;
			/* Stay in place */
			z-index: 1;
			/* Sit on top */
			padding-top: 100px;
			/* Location of the box */
			left: 0;
			top: 0;
			width: 100%;
			/* Full width */
			height: 100%;
			/* Full height */
			overflow: auto;
			/* Enable scroll if needed */
			background-color: rgb(0, 0, 0);
			/* Fallback color */
			background-color: rgba(0, 0, 0, 0.4);
			/* Black w/ opacity */
		}

		/* Modal Content */
		.modal-content {
			background-color: #fefefe;
			margin: auto;
			padding: 20px;
			border: 1px solid #888;
			width: 80%;
		}

		/* The Close Button */
		.close {
			color: #aaaaaa;
			float: right;
			font-size: 28px;
			font-weight: bold;
		}

		.close:hover,
		.close:focus {
			color: #000;
			text-decoration: none;
			cursor: pointer;
		}

		.izmeni {
			background: url(../images/edit.png) no-repeat left;
			cursor: pointer;
			width: 23px;
			height: 21px;
			border-radius: 40px;
		}

		.odradjeno {
			background: url(../images/check4.png) no-repeat left;
			cursor: pointer;
			width: 23px;
			height: 21px;
			border-radius: 40px;
		}
	</style>
	<script type="text/javascript">
		// MARIJA 24.09.2015 - otvoreno predmet iy liste reaktiviranih predmeta
		function otvori_predmet_odstetnog_zahteva(id) {
			if (id != -1) {
				window.open("pregled.php?idstete=" + id + "&dugme=DA", "_blank");
				document.getElementById("lista_reaktiviranih").value = -1;
			}
		}
		// Nemanja Jovanovic 07.12.2017 
		function otvori_pregled_instrukcija(idstete) {
			var datum_kompletiranja = $("input[name='datumKompl']").val();
			var id_stete = idstete;

			$.ajax({
				type: 'post',
				url: 'potpisivanje_resenja_dodavanje_instrukcija_funkcije.php?funkcija=proveri_datum_kompletiranja',
				datatype: 'json',
				data: {
					id_stete: id_stete,
					datum_kompletiranja: datum_kompletiranja
				},
				success: function(ret) {
					var data = JSON.parse(ret);
					var poruka = data.poruka;
					var flag = data.flag;

					if (flag) {
						window.open("potpisivanje_resenja_dodavanje_instrukcija.php?id_stete=" + id_stete + "&uslov=potpis", "_self");
					} else {
						alert(poruka);
					}
				}
			});

		}

		// Marko Markovic --- dodato da se vrati na istu stranu
		function vrati_nazad(id) {
			window.open("pregled.php?idstete=" + id + "&dugme=DA", "_self");
		}


		// ----------- Marko Markovic 2020-04-29 ----- pravni osnov korice -----
		function pravni_osnov_korice(idstete) {
			// var idstete = $("[name='idstete']").val(); 
			var novi_broj_predmeta = $("[name='novi_broj_predmeta']").val();
			var prezimeOst = $("[name='prezimeOst']").val();
			var imeNazivOst = $("[name='imeNazivOst']").val();

			var adresaOst = $("[name='adresaOst']").val();
			var delimicnoProc = $("[name='delimicnoProc']").val();
			var dodatno = $('#dodatno').val();

			//var osnovan = $("[name='osnovan']").val(); 
			var osnovan;
			if ($('#osnovan_ceo').is(':checked')) {
				osnovan = "Osnovan u celosti";
			} else if ($('#osnovan_delimicno').is(':checked')) {
				osnovan = "Delimièno osnovan- " + delimicnoProc + "% " + dodatno;
			} else if ($('#odbijen').is(':checked')) {
				osnovan = "Odbijen";
			} else {
				alert('Odaberite da li je pravni osnov osnovan u celosti, delimicno ili odbijen.');
				exit;
			}

			var datumPravniOsnov = $('#datumPravniOsnov').val();
			var datum_prijave = $('#datum_prijave').val();
			var broj_polise = $('#broj_polise').val();

			window.open("pravni_osnov_korice.php?idstete=" + idstete + "&osnovan=" + osnovan + "&novi_broj_predmeta=" + novi_broj_predmeta + "&imeNazivOst=" + imeNazivOst + "&prezimeOst=" + prezimeOst + "&adresaOst=" + adresaOst + "&datumPravniOsnov=" + datumPravniOsnov + "&broj_polise=" + broj_polise + "&datum_prijave=" + datum_prijave, "_self");
		}
		// --------- Marko Markovic kraj pravni osnov 2020-04-29



		function kreiraj_odbijenicu(id_stete) {
			window.open("resenje_zahteva_odbijen_novo.php?id_stete=" + id_stete, "_self");
		}

		function kreiraj_odbijenicu_likvidacija(idstete) {
			window.open("kreiranje_odbijenice_likvidacija_novo.php?idstete=" + idstete, "_self");
		}



		$(document).ready(function() {

			var idstete = document.pregled.idstete.value;
			var url = "dohvatiPrevaru";
			$.ajax({
				type: 'POST',
				url: "funkcije.php",
				datatype: 'json',
				data: {
					funkcija: url,
					idstete: idstete
				},
				datatype: 'json',
				success: function(ret) {
					if (ret) {
						var data = JSON.parse(ret);
						if (data.podaci['sumnja'] == 't') {
							document.getElementById("sumnjaNaPrevaru").checked = true;
							document.getElementById("sumnjaNaPrevaru").value = true;
							document.getElementById("sumnjaNaPrevaru").disabled = true;
							for (i = 1; i <= 4; i++)
								document.getElementById("prevara_" + i).disabled = false;
							if (data.podaci['status_prevare'] != 0)
								document.getElementById("prevara_" + data.podaci['status_prevare']).checked = true;
						}
						if (typeof data.podaci['datum_sumnje'] !== 'undefined')
							if (data.podaci['datum_sumnje'] != 'NULL') {
								document.getElementById("datumPrev").value = data.podaci['datum_sumnje'];
								document.getElementById("datumPrev").disabled = true;
							}
						else
							document.getElementById("datumPrev").value = '';
						if (typeof data.podaci['ocekivana_suma'] !== 'undefined')
							if (data.podaci['ocekivana_suma'] != 0)
								document.getElementById("ocekivana_suma").value = data.podaci['ocekivana_suma'];
							else
								document.getElementById("ocekivana_suma").value = '';
						if (typeof data.podaci['status_prevare_datum'] !== 'undefined')
							if (data.podaci['status_prevare_datum'] != 'NULL')
								document.getElementById("datumPrevare").value = data.podaci['status_prevare_datum'];
							else
								document.getElementById("datumPrevare").value = '';
						if (typeof data.podaci['napomena'] !== 'undefined')
							document.getElementById("Napomena").value = data.podaci['napomena'];
						else
							document.getElementById("Napomena").value = '';
						if (data.podaci['nastaviti_saradnju_ak'] == 't') {
							document.getElementById("nastaviti_saradnju").checked = true;
							document.getElementById("nastaviti_saradnju").value = true;
						}
						var niz = [];
						if( data.podaci['osumnjiceni'] != 'NULL')
						{
							var duzina = data.podaci['osumnjiceni'];
							duzina = duzina.substring(1, duzina.length - 1);

							for (i = 0; i < duzina.length; i += 2)
								niz.push(duzina[i]);
							for (j = 0; j < niz.length; j++) 
							{
								if (niz[j] == 1) document.getElementById("osumnjiceni_1").checked = true;
								if (niz[j] == 2) document.getElementById("osumnjiceni_2").checked = true;
								if (niz[j] == 3) document.getElementById("osumnjiceni_3").checked = true;
								if (niz[j] == 4) document.getElementById("osumnjiceni_4").checked = true;
							} 
					    }  
						document.getElementById("broj_prevare").value = data.podaci['id'];
					} 
					else 
					{
						document.getElementById("sumnjaNaPrevaru").checked = false;
						for (i = 1; i <= 4; i++)
							document.getElementById("prevara_" + i).disabled = true;
						
						document.getElementById("datumPrev").value = '';
						document.getElementById("ocekivana_suma").value = '';
						document.getElementById("datumPrevare").value = '';
						document.getElementById("Napomena").value = '';
					}
				}
			});

			$('.prevara').attr('disabled', true);
			if ($("#sifra_lista")) {
				$("#sifra_lista").dropdownchecklist({
					icon: {
						placement: 'right',
						toOpen: 'ui-icon-arrowthick-1-s',
						toClose: 'ui-icon-arrowthick-1-n'
					},
					maxDropHeight: 200,
					width: 220,
					groupItemChecksWholeGroup: true,
					textFormatFunction: function(options) {
						//console.log("options: ", options);
						var selectedOptions = options.filter(":selected");
						//console.log("selected options: ", selectedOptions);
						var countOfSelected = selectedOptions.size();
						var sve_vrednosti = selectedOptions.val();
						for (var i = 1; i < countOfSelected; i++) {
							//alert($(selectedOptions[i]).val());
							sve_vrednosti += ',' + $(selectedOptions[i]).val();

						}
						$("#sifra").val('');
						$("#sifra").val(sve_vrednosti);
						$("#sifra").text(sve_vrednosti);
						//document.getElementById('vrednost_advokata').value = sve_vrednosti;

						switch (countOfSelected) {
							//case 0: return "<i>Izaberite ¹ifre</i>";
							case 0:
								return "<i>Izaberite ¹ifru</i>";
							case 1:
								return selectedOptions.text();
							case options.size():
								return "<span style='color:red;font-weight:bold'>Svi su selektovani</span>";
							default:
								return countOfSelected + " Selektovana";

						}
					},
					onItemClick: function(checkbox, selector) {
						//alert("value " + checkbox.val() + ", is checked: " + checkbox.prop("checked"));
					}
				});
			}



		});
		/* Nenad Puk¹ec */
		/*----------------------------------------------------	
		 *  Funkcija za citanje podataka          			 |
		 *  sa saobracajne dozvole				             |
		 *----------------------------------------------------
		 */

		function prikaziModal(number) {
			document.getElementById("citacSaobracajneModal").style.display = "block";
			document.getElementById("ost-osg").value = number;
		}

		function zatvoriModal() {
			document.getElementById("citacSaobracajneModal").style.display = "none";
		}

		function PodaciIzCitacaSD() {
			var ostOsg = document.getElementById("ost-osg").value;
			var i;
			var ime = document.eVRCard.getImeVlasnika();
			var prezime = document.eVRCard.getPrezimeIliNazivFirmeVlasnika();
			var jmbgVlasnika = document.eVRCard.getJmbgIliMbVlasnika();
			var adresa = document.eVRCard.getAdresaVlasnikaKompletna();
			var marka = document.eVRCard.getMarkaVozila();
			var tip = document.eVRCard.getTipVozila();
			var model = document.eVRCard.getKomercijalnaOznakaModelVozila();
			var godinaProizvodnje = document.eVRCard.getGodinaProizvodnjeVozila();
			var regBr = document.eVRCard.getRegistarskiBrojVozila();
			var sasija = document.eVRCard.getBrojSasijeVozila();
			var grad = document.eVRCard.getAdresaVlasnikaGrad();
			var opstinaApplet = document.eVRCard.getAdresaVlasnikaOpstina();

			if (ostOsg == 1) {
				var allOpstinaApplet = document.getElementById("osteceni_opstina_id").querySelectorAll("option");
			} else {
				var allOpstinaApplet = document.getElementById("osiguranik_krivac_opstina_id").querySelectorAll("option");
			}


			for (i = 0; i < allOpstinaApplet.length; i++) {
				if (opstinaApplet == "PALILULA") {
					if (allOpstinaApplet[i].text.indexOf(grad + "-" + opstinaApplet) > -1) {
						allOpstinaApplet[i].setAttribute('selected', 'selected');
						if (ostOsg == 1) {
							document.getElementById("osteceni_opstina_id").style.border = "2px solid green";
							setTimeout(function() {
								document.getElementById("osteceni_opstina_id").style.border = "1px solid black";
							}, 5000);
						} else {
							document.getElementById("osiguranik_krivac_opstina_id").style.border = "2px solid green";
							setTimeout(function() {
								document.getElementById("osiguranik_krivac_opstina_id").style.border = "1px solid black";
							}, 5000);
						}
					}
				} else {
					if (allOpstinaApplet[i].text.indexOf(opstinaApplet) > -1) {
						allOpstinaApplet[i].setAttribute('selected', 'selected');
						if (ostOsg == 1) {
							document.getElementById("osteceni_opstina_id").style.border = "2px solid green";
							setTimeout(function() {
								document.getElementById("osteceni_opstina_id").style.border = "1px solid black";
							}, 5000);
						} else {
							document.getElementById("osiguranik_krivac_opstina_id").style.border = "2px solid green";
							setTimeout(function() {
								document.getElementById("osiguranik_krivac_opstina_id").style.border = "1px solid black";
							}, 5000);
						}
					}
				}
			}

			if (ostOsg == 1) {
				document.getElementById("predlog-mesto").style.display = "block";
				//document.getElementById("predlog-mesto1").style.display = "block";
				document.getElementById("predlzeno-mesto-label").innerHTML = document.eVRCard.getAdresaVlasnikaOpstina();
				document.getElementById("predlzeno-mesto-label").value = document.eVRCard.getAdresaVlasnikaOpstina();
				//document.getElementById("predlzeno-mesto-label1").innerHTML = document.eVRCard.getAdresaKorisnikaOpstina();
				//document.getElementById("predlzeno-mesto-label1").value = document.eVRCard.getAdresaKorisnikaOpstina();
			} else {
				document.getElementById("predlog-mesto2").style.display = "block";
				//document.getElementById("predlog-mesto3").style.display = "block";
				document.getElementById("predlzeno-mesto-label2").innerHTML = document.eVRCard.getAdresaVlasnikaOpstina();
				document.getElementById("predlzeno-mesto-label2").value = document.eVRCard.getAdresaVlasnikaOpstina();
				//document.getElementById("predlzeno-mesto-label3").innerHTML = document.eVRCard.getAdresaKorisnikaOpstina();
				//document.getElementById("predlzeno-mesto-label3").value = document.eVRCard.getAdresaKorisnikaOpstina();
			}

			/*getMarkaVozila();
			getTipVozila();
			getKomercijalnaOznakaModelVozila();
			getBrojSasijeVozila();
			getRegistarskiBrojVozila();
			getJmbgIliMbVlasnika();
			getPrezimeIliNazivFirmeVlasnika();
			getImeVlasnika();

			getAdresaVlasnikaKompletna()
			getAdresaVlasnikaGrad()
			getAdresaVlasnikaOpstina()
			getAdresaVlasnikaUlica()
			getAdresaVlasnikaBroj()
			getAdresaVlasnikaSprat()
			getAdresaVlasnikaStan()
			
			getPrezimeIliNazivFirmeKorisnika()
			getImeKorisnika()
			getJmbgIliMbKorisnika()
			getAdresaKorisnikaKompletna()
			getAdresaKorisnikaGrad()
			getAdresaKorisnikaOpstina()
			getAdresaKorisnikaUlica()
			getAdresaKorisnikaBroj()
			getAdresaKorisnikaSprat()
			getAdresaKorisnikaStan()*/

			if (ostOsg == 1) {
				document.getElementById("prezimeOst").value = prezime;
				document.getElementById("imeNazivOst").value = ime;
				document.getElementById("jmbgPibOst").value = jmbgVlasnika;
				document.getElementById("adresaOst").value = adresa;
				document.getElementById("markaOst").value = marka;
				document.getElementById("tipOst").value = tip;
				document.getElementById("modelOst").value = model;
				document.getElementById("godOst").value = godinaProizvodnje;
				document.getElementById("regPodOst").value = regBr.substring(0, 2);
				document.getElementById("regOznakaOst").value = regBr.substring(2, regBr.length);
				document.getElementById("brsasOst").value = sasija;

				document.getElementById("prezimeOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("prezimeOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("jmbgPibOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("jmbgPibOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("imeNazivOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("imeNazivOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("adresaOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("adresaOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("markaOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("markaOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("tipOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("tipOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("modelOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("modelOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("godOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("godOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("regPodOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("regPodOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("regOznakaOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("regOznakaOst").style.border = "1px solid black";
				}, 5000);
				document.getElementById("brsasOst").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("brsasOst").style.border = "1px solid black";
				}, 5000);
			} else {
				document.getElementById("prezimeKriv").value = prezime;
				document.getElementById("jmbgPibKriv").value = jmbgVlasnika;
				document.getElementById("imeNazivKriv").value = ime;
				document.getElementById("osiguranik_krivac_adresa").value = adresa;
				document.getElementById("markaKriv").value = marka;
				document.getElementById("tipKriv").value = tip;
				document.getElementById("modelKriv").value = model;
				document.getElementById("godKriv").value = godinaProizvodnje;
				document.getElementById("regPodKriv").value = regBr.substring(0, 2);
				document.getElementById("regOznakaKriv").value = regBr.substring(2, regBr.length);
				document.getElementById("brsasKriv").value = sasija;

				document.getElementById("prezimeKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("prezimeKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("jmbgPibKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("jmbgPibKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("imeNazivKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("imeNazivKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("osiguranik_krivac_adresa").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("osiguranik_krivac_adresa").style.border = "1px solid black";
				}, 5000);
				document.getElementById("markaKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("markaKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("tipKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("tipKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("modelKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("modelKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("godKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("godKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("regPodKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("regPodKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("regOznakaKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("regOznakaKriv").style.border = "1px solid black";
				}, 5000);
				document.getElementById("brsasKriv").style.border = "2px solid green";
				setTimeout(function() {
					document.getElementById("brsasKriv").style.border = "1px solid black";
				}, 5000);
			}

			/* PODACI O VOZAÈU */

			/*var imeVozac = document.eVRCard.getImeKorisnika();
			var prezimeVozac = document.eVRCard.getPrezimeIliNazivFirmeKorisnika();
			var jmbgVozac = document.eVRCard.getJmbgIliMbKorisnika();
			var adresaVozac = document.eVRCard.getAdresaKorisnikaKompletna();
			var opstinaAppletVozac = document.eVRCard.getAdresaKorisnikaOpstina();
			var gradVozac = document.eVRCard.getAdresaKorisnikaGrad();

			if(ostOsg == 1)
			{
				var allOpstinaAppletVozac = document.getElementById("vozac_opstina_id").querySelectorAll("option");
			}
			else
			{
				var allOpstinaAppletVozac = document.getElementById("vozac_krivac_opstina_id").querySelectorAll("option");
			}
			
			for(i = 0; i < allOpstinaAppletVozac.length; i++)
			{
				if(opstinaAppletVozac == "PALILULA")
				{
					if(allOpstinaAppletVozac[i].text.indexOf(gradVozac+"-"+opstinaAppletVozac) > -1)
			        {
						allOpstinaAppletVozac[i].setAttribute('selected','selected');
						if(ostOsg == 1)
						{
							document.getElementById("vozac_opstina_id").style.border = "2px solid green";
							setTimeout(function(){ document.getElementById("vozac_opstina_id").style.border = "1px solid black"; }, 5000);
						}
						else
						{
							document.getElementById("vozac_krivac_opstina_id").style.border = "2px solid green";
							setTimeout(function(){ document.getElementById("vozac_krivac_opstina_id").style.border = "1px solid black"; }, 5000);
						}
			        }
				}
				else
				{
					if(allOpstinaAppletVozac[i].text.indexOf(opstinaAppletVozac) > -1)
			        {
						allOpstinaAppletVozac[i].setAttribute('selected','selected');
						if(ostOsg == 1)
						{
							document.getElementById("vozac_opstina_id").style.border = "2px solid green";
							setTimeout(function(){ document.getElementById("vozac_opstina_id").style.border = "1px solid black"; }, 5000);
						}
						else
						{
							document.getElementById("vozac_krivac_opstina_id").style.border = "2px solid green";
							setTimeout(function(){ document.getElementById("vozac_krivac_opstina_id").style.border = "1px solid black"; }, 5000);
						}
			        }
				}
			}*/

			/*if(ostOsg == 1)
			{
				document.getElementById("prezimeVoz").value = prezimeVozac;
				document.getElementById("jmbgVoz").value = jmbgVozac;
				document.getElementById("imeVoz").value = imeVozac;
				document.getElementById("vozac_adresa").value = adresaVozac;
				
				document.getElementById("prezimeVoz").style.border = "2px solid green";
				setTimeout(function(){ document.getElementById("prezimeVoz").style.border = "0px solid green"; }, 5000);
				document.getElementById("jmbgVoz").style.border = "2px solid green";
				setTimeout(function(){ document.getElementById("jmbgVoz").style.border = "0px solid green"; }, 5000);
				document.getElementById("imeVoz").style.border = "2px solid green";
				setTimeout(function(){ document.getElementById("imeVoz").style.border = "0px solid green"; }, 5000);
				document.getElementById("vozac_adresa").style.border = "2px solid green";
				setTimeout(function(){ document.getElementById("vozac_adresa").style.border = "0px solid green"; }, 5000);
				
				document.addEventListener('DOMContentLoaded', postaviMestaOpstine(document.getElementById("osteceni_opstina_id")), false);
				document.addEventListener('DOMContentLoaded', postaviMestaOpstine(document.getElementById("vozac_opstina_id")), false);
			}
			else
			{
				document.getElementById("prezimeVozKriv").value = prezimeVozac;
				document.getElementById("jmbgVozKriv").value = jmbgVozac;
				document.getElementById("imeVozKriv").value = imeVozac;
				document.getElementById("vozac_krivac_adresa").value = adresaVozac;
				
				document.getElementById("prezimeVozKriv").style.border = "2px solid green";
				setTimeout(function(){ document.getElementById("prezimeVozKriv").style.border = "0px solid green"; }, 5000);
				document.getElementById("jmbgVozKriv").style.border = "2px solid green";
				setTimeout(function(){ document.getElementById("jmbgVozKriv").style.border = "0px solid green"; }, 5000);
				document.getElementById("imeVozKriv").style.border = "2px solid green";
				setTimeout(function(){ document.getElementById("imeVozKriv").style.border = "0px solid green"; }, 5000);
				document.getElementById("vozac_krivac_adresa").style.border = "2px solid green";
				setTimeout(function(){ document.getElementById("vozac_krivac_adresa").style.border = "0px solid green"; }, 5000);
				
				document.addEventListener('DOMContentLoaded', postaviMestaOpstine(document.getElementById("osiguranik_krivac_opstina_id")), false);
				document.addEventListener('DOMContentLoaded', postaviMestaOpstine(document.getElementById("vozac_krivac_opstina_id")), false);
			}*/
			if (ostOsg == 1) {
				document.addEventListener('DOMContentLoaded', postaviMestaOpstine(document.getElementById("osteceni_opstina_id")), false);
				document.addEventListener('DOMContentLoaded', postaviMestaOpstine(document.getElementById("vozac_opstina_id")), false);
				//document.addEventListener('DOMContentLoaded', dohvatiJMBG(), false);
			} else {
				document.addEventListener('DOMContentLoaded', postaviMestaOpstine(document.getElementById("osiguranik_krivac_opstina_id")), false);
				document.addEventListener('DOMContentLoaded', postaviMestaOpstine(document.getElementById("vozac_krivac_opstina_id")), false);
				//document.addEventListener('DOMContentLoaded', dohvatiJMBG(), false);
			}

			if (jmbgVlasnika.length < 13) {
				dohvatiJMBG(ostOsg);
			}
		}

		function predloziMestoOsteceni(forma) {
			var i;
			if (forma == 1) {
				var allMestoAppletOsteceni = document.getElementById("osteceni_mesto_id").querySelectorAll("option");
				var mestoOsteceni = document.getElementById("predlzeno-mesto-label").value;
			} else {
				var allMestoAppletOsteceni = document.getElementById("osiguranik_krivac_mesto_id").querySelectorAll("option");
				var mestoOsteceni = document.getElementById("predlzeno-mesto-label2").value;
			}


			for (i = 0; i < allMestoAppletOsteceni.length; i++) {
				if (allMestoAppletOsteceni[i].text.indexOf(mestoOsteceni) > -1) {
					allMestoAppletOsteceni[i].setAttribute('selected', 'selected');
					if (forma == 1) {
						document.getElementById("osteceni_mesto_id").style.border = "2px solid green";
						setTimeout(function() {
							document.getElementById("osteceni_mesto_id").style.border = "0px solid green";
						}, 5000);
					} else {
						document.getElementById("osiguranik_krivac_mesto_id").style.border = "2px solid green";
						setTimeout(function() {
							document.getElementById("osiguranik_krivac_mesto_id").style.border = "0px solid green";
						}, 5000);
					}
				}
			}
		}

		/*function predloziMestoOsteceniVozac(forma)
		{
			var i;
			if(forma == 1)
			{
				var allMestoAppletOsteceni = document.getElementById("vozac_mesto_id").querySelectorAll("option");
				var mestoOsteceni = document.getElementById("predlzeno-mesto-label1").value;
			}
			else
			{
				var allMestoAppletOsteceni = document.getElementById("vozac_krivac_mesto_id").querySelectorAll("option");
				var mestoOsteceni = document.getElementById("predlzeno-mesto-label3").value;
			}
			
			for(i = 0; i < allMestoAppletOsteceni.length; i++)
			{
				if(allMestoAppletOsteceni[i].text.indexOf(mestoOsteceni) > -1)
			    {
					allMestoAppletOsteceni[i].setAttribute('selected','selected');
					if(forma == 1)
					{
						document.getElementById("vozac_mesto_id").style.border = "2px solid green";
						setTimeout(function(){ document.getElementById("vozac_mesto_id").style.border = "0px solid green"; }, 5000);
					}
					else
					{
						document.getElementById("vozac_krivac_mesto_id").style.border = "2px solid green";
						setTimeout(function(){ document.getElementById("vozac_krivac_mesto_id").style.border = "0px solid green"; }, 5000);
					}
			    }
			}
		}*/

		function dohvatiJMBG(broj) {
			var funkcija = "dohvatiJMBG";
			var maticni_broj = document.eVRCard.getJmbgIliMbVlasnika();
			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=dohvatiJMBG",
				datatype: 'json',
				data: {
					funkcija: funkcija,
					maticni_broj: maticni_broj
				},
				success: function(ret) {

					var data = JSON.parse(ret);
					if (broj == 1) {
						document.getElementById("jmbgPibOst").innerHTML = data.podaci;
						document.getElementById("jmbgPibOst").value = data.podaci;
					} else {
						document.getElementById("jmbgPibKriv").innerHTML = data.podaci;
						document.getElementById("jmbgPibKriv").value = data.podaci;
					}
				}
			});
		}

		function prikazi_listu_sifara(value) {
			var sifra_stara = document.getElementById("sifra").value;

			if (document.getElementById("izmeni_sifru").checked == true) {
				var broj_kliknuto = Number(document.getElementById("broj_kliknuto").value);
				var novi_broj = broj_kliknuto + 1;
				document.getElementById("broj_kliknuto").value = novi_broj;
				document.getElementById("div_sifre").style.display = 'inline';
				if (document.getElementById("broj_kliknuto").value == 1) {
					$("#sifra_lista1").dropdownchecklist({
						icon: {
							placement: 'right',
							toOpen: 'ui-icon-arrowthick-1-s',
							toClose: 'ui-icon-arrowthick-1-n'
						},
						maxDropHeight: 200,
						width: 220,
						groupItemChecksWholeGroup: true,
						textFormatFunction: function(options) {
							//console.log("options: ", options);
							var selectedOptions = options.filter(":selected");
							//console.log("selected options: ", selectedOptions);
							var countOfSelected = selectedOptions.size();
							var sve_vrednosti = selectedOptions.val();
							for (var i = 1; i < countOfSelected; i++) {
								//alert($(selectedOptions[i]).val());
								sve_vrednosti += ',' + $(selectedOptions[i]).val();

							}
							if (sve_vrednosti != undefined) {
								document.getElementById("sifra").value = sve_vrednosti;
								$("#sifra").text(sve_vrednosti);
							} else {
								document.getElementById("sifra").value = sifra_stara;
								$("#sifra").text(sifra_stara);

							}
							//	document.getElementById('vrednost_advokata').value = sve_vrednosti;
							switch (countOfSelected) {
								//case 0: return "<i>Izaberite ¹ifre</i>";
								case 0:
									return "<i>Izaberite ¹ifru</i>";
								case 1:
									return selectedOptions.text();
								case options.size():
									return "<span style='color:red;font-weight:bold'>Svi su selektovani</span>";
								default:
									return countOfSelected + " Selektovana";
							}
						},
						onItemClick: function(checkbox, selector) {
							//alert("value " + checkbox.val() + ", is checked: " + checkbox.prop("checked"));
						}
					});
				}
			} else {
				document.getElementById("div_sifre").style.display = 'none';
			}
		}


		function prikazi_polje_za_rentu(tip) {

			if (tip == '1001011') {
				document.getElementById('renta_check_box').style.display = 'inline';
				document.getElementById('renta_label').style.display = 'inline';

			} else {
				document.getElementById('renta_check_box').style.display = 'none';
				document.getElementById('renta_label').style.display = 'none';
				document.getElementById('renta_check_box').checked = false;
			}
		}

		function popuni_hidden_osnov(osnov_prigovora) {
			document.getElementById("osnov_prigovora").value = osnov_prigovora;
		}
		//Branka 06.05.2016.
		function kreiraj_stampaj_potvrdu(potvrda_id) {
			if (!potvrda_id) {
				potvrda_id = 'nov';
			}
			var idstete = document.pregled.idstete.value;

			var url = "snimi_potvrdu_prijema_prigovora";
			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=" + url + "&idstete=" + idstete,
				datatype: 'json',
				data: {
					potvrda_id: potvrda_id,
					idstete: idstete
				},
				success: function(ret) {
					var data = JSON.parse(ret);

					if (data.flag == true && potvrda_id == 'nov') {
						alert(data.poruka);

					}
					if (data.flag == false) {
						alert(data.poruka);
						location.reload();
					}
					if (data.flag == true) {

						var html = data.html;
						var potvrda = data.potvrda_id;
						var url = "stampaj_potvrdu";
						$.ajax({
							type: 'POST',
							url: "funkcije.php?funkcija=" + url,
							datatype: 'json',
							data: {
								html: html,
								potvrda_id: potvrda
							},
							success: function(ret) {
								var data = JSON.parse(ret);
								window.open("PDF_fajlovi/" + data.naziv_pdf, "_blank");
								location.reload();
							}
						});
					}



				}
			});


		}

		function stampaj_garanciju(garancija_id) {
			if (!garancija_id) {
				garancija_id = 'nov';
			}
			var idstete = document.pregled.idstete.value;

			var url = "snimi_garanciju_dpz";
			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=" + url,
				async: false,
				datatype: 'json',
				data: {
					garancija_id: garancija_id,
					idstete: idstete
				},
				success: function(ret) {

					var data = JSON.parse(ret);
					if (data.flag == true && garancija_id == 'nov') {
						alert(data.poruka);

					}
					if (data.flag == false) {
						alert(data.poruka);
						location.reload();
					}
					if (data.flag == true) {


						var garancija = data.garancija_id;
						var url = "stampaj_garanciju";
						$.ajax({
							type: 'POST',
							url: "funkcije.php?funkcija=" + url,
							datatype: 'json',
							async: false,
							data: {

								garancija_id: garancija
							},
							success: function(ret) {
								var data = JSON.parse(ret);
								var data = JSON.parse(ret);
								window.open("PDF_fajlovi/" + data.naziv_pdf, "_blank");
								location.reload();
							}
						});
					}

				}
			});




		}



		// MARIJA 01.04.2015 - POCETAK
		function stampajDopis() {
			var id_dopisa = document.getElementById("obavestenje_o_regresnom_potrazivanju").value;
			var url = "stampaj_dopis";
			var idstete = document.pregled.idstete.value;
			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=" + url + "&id_dopisa=" + id_dopisa,
				datatype: 'json',
				data: {
					idstete: idstete
				},
				success: function(ret) {
					var data = JSON.parse(ret);
					window.open("PDF_fajlovi/" + data.naziv_pdf, "_blank");
				}
			});
		}


		// MARIJA 16.03.2015 - POCETAK
		function obrisi_osnov_pravni_osnov() {
			var idstete = document.pregled.idstete.value;
			var izabran_iz_liste = $('#lista_za_pravni_osnov option:selected').val();

			if (izabran_iz_liste) {
				var url = "obrisi_osnov_za_pravni_osnov";
				$.ajax({
					type: 'POST',
					url: "funkcije.php?funkcija=" + url,
					datatype: 'json',
					data: {
						idstete: idstete,
						izabran_iz_liste: izabran_iz_liste
					},
					success: function(ret) {
						var data = JSON.parse(ret);
						if (data.flag) {
							$("#lista_za_pravni_osnov option[value='" + izabran_iz_liste + "']").remove();
							$('#pravni_osnov_izvestaj').append(data.opcije);
						}
						//alert(data.poruka);
					}
				});
			} else {
				alert("Morate oznaèiti osnov u listi");
			}
		}

		function dodaj_osnov_pravni_osnov() {
			var selektovan_osnov = document.getElementById("pravni_osnov_izvestaj").value;
			var idstete = document.pregled.idstete.value;
			if (selektovan_osnov != -1) {
				var url = "snimi_osnov_za_pravni_osnov";
				$.ajax({
					type: 'POST',
					url: "funkcije.php?funkcija=" + url,
					datatype: 'json',
					data: {
						selektovan_osnov: selektovan_osnov,
						idstete: idstete
					},
					success: function(ret) {
						var data = JSON.parse(ret);
						if (data.flag) {
							document.getElementById("pravni_osnov_izvestaj").value = '-1';
							$("#pravni_osnov_izvestaj option[value='" + selektovan_osnov + "']").remove();
							$('#lista_za_pravni_osnov').append("<option value='" + selektovan_osnov + "'>" + data.osnov_naziv + "</option>")
							document.getElementById("lista_za_pravni_osnov").style.display = "table-row";
							document.getElementById("obrisi_osnov_za_pravni_osnov").style.display = "table-row";

						}
					}
				});
			} else {
				alert("Morate izabrati osnov za pravni osnov");
			}
		}
		// MARIJA 05.03.2015 - dodata funkcija koja salje selektovan osnov na osnovu koga je dat pravni osnov i sacuvane prikazuje u listi - KRAJ

		// MARIJA 02.03.2015 - dodata funkcija za prikaz liste osiguravajucih drustva ukoliko se selektuje u regres_od osiguravajuce drustvo - POCETAK

		function potvrda_osnova_za_regres(id) {
			var potvrda = document.getElementById("potvrdjen_osnov_za_regres").checked;
			var obavestenje_o_regresnom_potrazivanju = document.getElementById("obavestenje_o_regresnom_potrazivanju").value;

			//	var trenutni_datum = document.getElementById("datum_evidentiranja_potvrde_za_regres").value;

			var datum_evidentiranja = new Date();
			var year = datum_evidentiranja.getFullYear().toString();
			var mesec = (datum_evidentiranja.getMonth() + 1).toString();
			var dan = datum_evidentiranja.getDate().toString();
			mesec = (mesec.length == 1) ? ('0' + mesec) : mesec;
			dan = (dan.length == 1) ? ('0' + dan) : dan;

			document.getElementById("radnik_evidentirao_potvrdu_za_regres").style.display = "inline";
			document.getElementById("radnik_evidentirao").style.display = "inline";
			document.getElementById("datum_evidentiranja_potvrde_za_regres").style.display = "inline";
			document.getElementById("datum_evidentiranja").style.display = "inline";
			document.getElementById("datum_evidentiranja_potvrde_za_regres").value = year + '-' + mesec + '-' + dan;
			document.getElementById("radnik_evidentirao_potvrdu_za_regres").value = document.getElementById("ulogovan_radnik").value;

			if (potvrda == false) {
				document.getElementById("razlog_regresa_id").value = '-1';
				$("#razlog_regresa_id").removeClass("disabled");
				document.getElementById("razlog_regresa_id").disabled = false;

				document.getElementById("regres_od").value = 'Izaberite';
				$("#regres_od").removeClass("disabled");
				document.getElementById("regres_od").disabled = false;

				document.getElementById("osiguravajuce_drustvo_id").value = '-1';
				$("#osiguravajuce_drustvo_id").removeClass("disabled");
				document.getElementById("osiguravajuce_drustvo_id").disabled = false;

				document.getElementById("red_osiguravajuca_drustva").style.display = "none";

				document.getElementById("vrstaRegPotr").value = '';
				$("#vrstaRegPotr").removeClass("disabled");
				document.getElementById("vrstaRegPotr").disabled = false;
				document.getElementById("vrstaRegPotr").readOnly = false;

				document.getElementById("osiguranjeRegPotr").value = '';
				$("#osiguranjeRegPotr").removeClass("disabled");
				document.getElementById("osiguranjeRegPotr").disabled = false;
				document.getElementById("osiguranjeRegPotr").readOnly = false;

				document.getElementById("drzavaRegPotr").value = '';
				$("#drzavaRegPotr").removeClass("disabled");
				document.getElementById("drzavaRegPotr").disabled = false;
				document.getElementById("drzavaRegPotr").readOnly = false;

				document.getElementById("regresno_potrazivanje_napomena").value = '';
				$("#regresno_potrazivanje_napomena").removeClass("disabled");
				document.getElementById("regresno_potrazivanje_napomena").disabled = false;
				document.getElementById("regresno_potrazivanje_napomena").readOnly = false;

			}

			// izmena 12.05.2015 - MARIJA
			if (obavestenje_o_regresnom_potrazivanju == '' && potvrda != false && document.getElementById("regres_od").value != 'Osiguravajuæe dru¹tvo' && document.getElementById("regres_od").value != 'Ostalo') {
				document.getElementById("vrsta_dopisa").value = 1;
				document.getElementById("dugme_kreiraj_dopis").click();
			}

		}

		function prikazi_podatke_osiguravajuceg_drustva(id) {

			//RESETOVANJE LISTE SA OPSTINAMA
			document.getElementById("opstina_reg").selectedIndex = "0";

			//DODAO VLADA - ZA RESET LISTE SA MESTIMA I OSTALIH INPUT POLJA NA PROMENU OSIGURANJA
			$('#mesto_reg').html('');
			$('#jmbg_pib').val('');
			$('#adresa_reg').val('');
			$('#telefon_reg').val('');
			$('#koliko_potrazivati').val('');

			//DODAO VLADA USLOV
			if (id != 26 && id != -1) {
				document.getElementById("osiguranjeRegPotr").value = document.getElementById('osiguravajuce_drustvo_id').options[document.getElementById('osiguravajuce_drustvo_id').selectedIndex].text;
			} else {
				document.getElementById("osiguranjeRegPotr").value = '';
			}

			//var lekarski_nalaz_id=document.getElementById('lekarski_nalazi').value;
			var url = "podaci_osiguravajuca_drustva";
			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=" + url,
				datatype: 'json',
				data: {
					id: id
				},
				success: function(ret) {
					var data = JSON.parse(ret);
					//console.log(data);

					//DOHVATANJE SELECT LISTE SA DRZAVAMA PO ID-JU - DODAO VLADA
					var select_drzave = document.getElementById('drzava_reg_id');
					var broj_stavki = select_drzave.options.length

					//AKO JE U PITANJU STRANO OSIGURANJE,RESETUJ SELECT
					if (data.zemlja == '') {

						//console.log('nema zemlje');
						select_drzave.selectedIndex = 0;
					}
					//U SUPROTNOM 
					else {

						//PROLAZAK KROZ LISTU SA DRZAVAMA
						for (var i = 0; i < broj_stavki; i++) {

							//AKO JE NAZIV DRZAVE OSIGURANJA ISTI KAO NAZIV IZ LISTE,SELEKTUJ GA
							if(select_drzave.options[i].text === data.zemlja) {

								select_drzave.options[i].selected = true;
								//select_drzave.options[i].setAttribute('selected', true);
								//select_drzave.disabled = true;
							}
						}	
					}
				
					//ENABLE-UJ SELECT LISTE
					$('#opstina_reg').attr('disabled', false);
					$('#mesto_reg').attr('disabled', false);
				}
			});
		}

		function prikazi_listu_podataka(value) {
			if (value == 'Osiguravajuæe dru¹tvo') {
				document.getElementById("red_osiguravajuca_drustva").style.display = "table-row";
				document.getElementById("drzavaRegPotr").value = '';
				document.getElementById("osiguranjeRegPotr").value = '';
			} else {
				document.getElementById("red_osiguravajuca_drustva").style.display = "none";
			}

			if (value == 'Krivac vlasnik vozila') {
				var vlasnik_krivac_prezime = document.getElementById("prezimeKriv").value;
				var vlasnik_krivac_ime = document.getElementById("imeNazivKriv").value;
				if (vlasnik_krivac_prezime == '' && vlasnik_krivac_ime == '') {
					document.getElementById("osiguranjeRegPotr").value = '';
				} else {
					document.getElementById("osiguranjeRegPotr").value = vlasnik_krivac_ime + ' ' + vlasnik_krivac_prezime;
				}

				if (document.getElementById('osiguranik_krivac_zemlja_id').value == '-1') {
					document.getElementById("drzavaRegPotr").value = '';
				} else {
					document.getElementById("drzavaRegPotr").value = document.getElementById('osiguranik_krivac_zemlja_id').options[document.getElementById('osiguranik_krivac_zemlja_id').selectedIndex].text;
				}

				var vlasnik_krivac_jmbg = document.getElementById("jmbgPibKriv").value;
				var vlasnik_krivac_broj_licne = document.getElementById("osiguranik_krivac_broj_licne_karte").value;
				var broj_evid_vlasnik;
				if (vlasnik_krivac_jmbg == '' && vlasnik_krivac_broj_licne == '') {
					broj_evid_vlasnik = '';
				} else {
					broj_evid_vlasnik = (vlasnik_krivac_jmbg) ? vlasnik_krivac_jmbg : vlasnik_krivac_broj_licne;
				}
				//document.getElementById("oznakaRegPotr").value = broj_evid_vlasnik ;
			} else if (value == 'Krivac vozaè vozila') {
				var vozac_krivac_prezime = document.getElementById("prezimeVozKriv").value;
				var vozac_krivac_ime = document.getElementById("imeVozKriv").value;
				if (vozac_krivac_prezime == '' && vozac_krivac_ime == '') {
					document.getElementById("osiguranjeRegPotr").value = '';
				} else {
					document.getElementById("osiguranjeRegPotr").value = vozac_krivac_ime + ' ' + vozac_krivac_prezime;
				}

				if (document.getElementById('vozac_krivac_zemlja_id').value == '-1') {
					document.getElementById("drzavaRegPotr").value = '';
				} else {
					document.getElementById("drzavaRegPotr").value = document.getElementById('vozac_krivac_zemlja_id').options[document.getElementById('vozac_krivac_zemlja_id').selectedIndex].text;
				}

				var vozac_krivac_jmbg = document.getElementById("jmbgVozKriv").value;
				var vozac_krivac_broj_licne = document.getElementById("vozac_krivac_broj_licne_karte").value;
				var broj_evid_vozac;
				if (vozac_krivac_jmbg == '' && vozac_krivac_broj_licne == '') {
					broj_evid_vozac = '';
				} else {
					if (vozac_krivac_jmbg) {
						broj_evid_vozac = vozac_krivac_jmbg;
					} else {
						broj_evid_vozac = vozac_krivac_broj_licne
					}
				}
				//document.getElementById("oznakaRegPotr").value = broj_evid_vozac;
			}

		}
		//MARIJA 02.03.2015 - dodata funkcija za prikaz liste osiguravajucih drustva ukoliko se selektuje u regres_od osiguravajuce drustvo - KRAJ

		// BRANKA 04.03.2015
		function otvori_formu_za_kreiranje_dokumenata() {

			var dokument = document.getElementById("lista_dokumenti").value;
			var predmet_id = document.pregled.idstete.value;

			if (dokument == -1) {
				alert("Morate izabrati dokument");
				exit;
			} else if (dokument == "obracun") {
				//window.open("obracun_visine_stete.php?predmet_odstetnog_zahteva_id="+predmet_id+"&dugme=DA", '_self');
				document.getElementById("obracun_visine_stete").click();

			} else if (dokument == "obracun_n_dpz") {
				document.getElementById("obracun_visine_stete_n_dpz").click();


			} else if (dokument == "obracun_0205_dpz") {
				document.getElementById("obracun_visine_stete_0205_dpz").click();
			} else if (dokument == "resenje_0903") {
				document.getElementById("resenje_IO_0903").click();

			}
		}
		//MARIJA 18.02.2015 - dodat script za autocomplite koji se koristi kod pravnog osnova koji je osnovan delimicno - POCETAK
		function samoBrojeviITacka(evt) {
			var kod = evt.which;
			if (kod == 0 || kod == 8 || kod == 13 || (kod > 47 && kod < 58) || kod == 46)
				return true;
			else
				return false;
		}

		function proveri_pib(b) {
			try {
				var a = b.value;
			}
			catch(err) {
				alert('Ne postoji ovaj objekat.');
				return false;
			}
			var cifra = 0;
			var i = 0;
			if (a.match(/^\d+$/)) {
			// PIB 
				if (a.length == 9) {
					for (i = 0; i < 8; i++) {
						cifra += parseInt(a.charAt(i));
						cifra -= (cifra > 10 ? 10 : 0);
						cifra *= 2;
						cifra -= (cifra > 11 ? 11 : 0);
					}
					cifra = (11 - cifra) > 9 ? 0 : 11 - cifra;
					if (cifra != parseInt(a.charAt(8))) {
						alert("Gre¹ka u PIB-u.\nNije dobar kontrolni broj.");
						b.value = '';
						b.style.border = '2px solid #007FFF';
						b.focus();
						return false;
					}
				}
				else
				{
					alert('Gre¹ka u PIB-u.\nMora imati taèno 9 cifara!');
					b.value = '';
					b.focus();
					return false;
				}
			}
			else {
				alert ("©ifra partnera sadr¾i iskljuèivo cifre.");
				b.value = '';
					b.focus();
				return false;
			}
			b.style.border = '1px solid #C4C4C4';
			return true;
		}

		(function($) {


			$.widget("ui.combobox", {
				_create: function() {
					var input, self = this,
						select = this.element,
						selected = select.children(":selected"),
						value = selected.val() ? selected.text() : "",
						wrapper = this.wrapper = $("<span>").addClass("ui-combobox").insertAfter(select);

					input = $("<input id='dodatno' style='margin-left:0px;width:150px;color:black;font-size:12px;height:24px;'>").appendTo(wrapper).val(value).addClass("ui-state-default ui-combobox-input").autocomplete({
						delay: 1000,
						minLength: 3,
						source: function(request, response) {
							var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");

							response(select.find("option").map(function() {
								var text = $(this).text();

								if (this.value && (!request.term || matcher.test(text))) return {
									label: text.replace(
										new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + $.ui.autocomplete.escapeRegex(request.term) + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "$1"),
									value: text,
									option: this,
									category: $(this).closest("optgroup").attr("label")
								};

							}).get());
						},
						select: function(event, ui) {
							ui.item.option.selected = true;
							self._trigger("selected", event, {
								item: ui.item.option
							});
						},
						change: function(event, ui) {
							if (!ui.item) {
								var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex($(this).val()) + "$", "i"),
									valid = false;
								select.children("option").each(function() {
									if ($(this).text().match(matcher)) {
										this.selected = valid = true;
										$(this).change();
										return false;
									}
								});
								if (!valid) {}
							}
						}
					}).addClass("ui-widget ui-widget-content ui-corner-left");

					if ($('#osnovan_ceo').is(':checked')) {

						$('#dodatno').val('');
					}

					input.data("autocomplete")._renderItem = function(ul, item) {
						return $("<li></li>").data("item.autocomplete", item).append("<a>" + item.label + "</a>").appendTo(ul);
					};

					input.data("autocomplete")._renderMenu = function(ul, items) {
						var self = this,
							currentCategory = "";
						$.each(items, function(index, item) {
							if (item.category != currentCategory) {
								if (item.category) {
									ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
								}
								currentCategory = item.category;
							}
							self._renderItem(ul, item);
						});
					};
				},

				destroy: function() {
					this.wrapper.remove();
					this.element.show();
					$.Widget.prototype.destroy.call(this);
				}
			});
		})(jQuery);

		$(function() {
			if ($("#osnovan_delimicno").is(':checked')) {
				$("#razlog_umanjenja_stete_id").combobox();
				$("#razlog_umanjenja_stete_id").toggle();
			}
		});


		//MARIJA 18.02.2015 - dodat script za autocomplite koji se koristi kod pravnog osnova koji je osnovan delimicno - KRAJ


		function stampaj_lekarski_nalaz() {
			var lekarski_nalaz_id = document.getElementById('lekarski_nalazi').value;
			if (lekarski_nalaz_id == -1) {
				alert("Morate izabrati lekarski_nalaz");
				exit;
			} else {
				var url = "kreiraj_pdf_lekarski_nalaz";
				$.ajax({
					type: 'POST',
					url: "funkcije.php?funkcija=" + url,
					datatype: 'json',
					data: {
						lekarski_nalaz_id: lekarski_nalaz_id
					},
					success: function(ret) {
						var data = JSON.parse(ret);
						window.open("PDF_fajlovi_lekarski_izvestaj/" + data.naziv_pdf, "_blank");
					}
				});

			}
		}
		//BRANKA 19.01.2015 POCETAK

		function otvori_polja_za_jmbg_i_tip(odstetni_zahtev_id) {


			if (document.getElementById('novi_predmet').checked == true) {
				document.getElementById('tip_predmeta_tarife').style.display = 'inline';
				document.getElementById('label_jmbg').style.display = 'inline';
				document.getElementById('jmbg_ostecenog').style.display = 'inline';
				document.getElementById('label_tip_predmeta').style.display = 'inline';
				document.getElementById('dugme_novi_predmet').style.display = 'inline';

			} else {
				document.getElementById('label_jmbg').style.display = 'none';
				document.getElementById('jmbg_ostecenog').style.display = 'none';
				document.getElementById('label_tip_predmeta').style.display = 'none';
				document.getElementById('tip_predmeta_tarife').style.display = 'none';
				document.getElementById('dugme_novi_predmet').style.display = 'none';


			}
		}

		function snimanje_odstetnog_zahteva(url, vrsta_odstetnog_zahteva, jmbg_pib_ostecenog, id_prijave, stetni_dogadjaj_id, renta) {
			//alert(renta);
			//Branka 21.09.2015. -Dodato
			var idsp = document.getElementById('sudski_postupak_id').value;
			$.ajax({
				type: 'POST',
				url: "funkcije_stetni_dogadjaj.php?funkcija=" + url,
				datatype: 'json',
				data: {
					vrsta_odstetnog_zahteva: vrsta_odstetnog_zahteva,
					jmbg_pib_ostecenog: jmbg_pib_ostecenog,
					id_prijave: id_prijave,
					answer: "da",
					stetni_dogadjaj_id: stetni_dogadjaj_id,
					// 					osnovni_predmet_id:osnovni_predmet_id,
					idsp: idsp,
					renta: renta

				},
				success: function(ret) {
					var data = JSON.parse(ret);
					if (data.flag == true) {
						var odstetni_zahtev_id = data.odstetni_zahtev_id;
						window.open("pregled.php?predmet_odstetnog_zahteva_id=" + odstetni_zahtev_id + "&dugme=DA", '_self');
					} else {
						alert(data.poruka);
					}
				}
			});

		}



		function provera_postojecih_predmeta1(id_prijave, stetni_dogadjaj_id, renta) {

			var tip_predmeta = document.getElementById('tip_predmeta_tarife').value;
			var jmbgpibost = document.getElementById('jmbg_ostecenog').value;
			var url = 'provera_postojecih_predmeta_visestruki_prolaz';
			//Branka 21.09.2015. dodato zbog zabrane da se kreira novi predmet na odstetnom yahtevu od prethodnih godina
			var idsp = document.getElementById('sudski_postupak_id').value;
			if (tip_predmeta == -1 || jmbgpibost == "") {
				alert("Morate izabrati tip od¹tetnog zahteva i uneti jmbg o¹teæenog!");
				exit;
			}
			if (!renta) {
				var renta = 0;
				var tip_duzina = tip_predmeta.length;
				var poslednji = tip_predmeta.charAt(tip_duzina - 1);
				//alert(document.getElementById('vrsta_obrasca').value);
				if (document.getElementById('vrsta_obrasca').value == 'AO' && poslednji == '1') {
					if (document.getElementById('renta_check_box').checked == true) {
						renta = 1;
					} else {
						renta = 0;
					}
				}
			}
			$.ajax({
				type: 'POST',
				url: "../common/funkcije.php?funkcija=" + url,
				datatype: 'json',
				data: {
					tip_predmeta: tip_predmeta,
					jmbgpibost: jmbgpibost,
					stetni_dogadjaj_id: stetni_dogadjaj_id,
					renta: renta
				},
				success: function(ret) {

					var data = JSON.parse(ret);
					var url = 'snimi_odstetni_zahtev';
					if (data.novi_da_ne == true) {
						if (data.predmet_id == false) {

							var trenutna_godina = new Date().getFullYear();
							var godina_odstetnog_zahteva = document.getElementById("datum_podnosenja_zahteva").value;
							godina_odstetnog_zahteva = godina_odstetnog_zahteva.substring(6, 10);
							if (!idsp || idsp == null || idsp == "") {
								if (godina_odstetnog_zahteva == trenutna_godina) {

									snimanje_odstetnog_zahteva(url, tip_predmeta, jmbgpibost, id_prijave, stetni_dogadjaj_id, renta);
								} else {
									alert('Ne mo¾ete kreirati novi predmet na od¹tetnom zahtevu iz prethodnih godina. Morate kreirati nov od¹tetni zahtev!');
									close();
								}
							} else {
								snimanje_odstetnog_zahteva(url, tip_predmeta, jmbgpibost, id_prijave, stetni_dogadjaj_id, renta);

							}

						}
						//PREDMET_ID -> TRUE
						else {
							//Reaktivacija
							alert("Predmet sa unetim jmbg/pib i tipom veæ postoji! Mo¾ete ga samo reaktivirati!");
							close();

						}

					}
					//Pronadjeno 1 ili vise rezervisanih predmeta za uneti jmbg i tip odstetnog zahteva
					else {
						var odgovor = data.odgovor;
						var predmet_id = data.predmet_id;
						if (data.postojeci_oz == true) { //Reaktivacija
							alert("Predmet sa unetim jmbg/pib i tipom veæ postoji! Mo¾ete ga samo reaktivirati!");
							close();
						} else {
							alert(odgovor);
							// 							 	jAlert(odgovor,"Obave¹tenje!!!",function(answer)
							// 									 	{
							// 											if (answer)
							// 											{
							window.open("pregled.php?predmet_odstetnog_zahteva_id=" + predmet_id + "&dugme=DA", '_self');
							// 											}
							// 									 	});

						}



					}


				}
			});
		}


		//BRANKA 19.01.2015 KRAJ

		//MARIJA 2.11.2014. funkcija koja  na promenu opstine menja spisak mesta, ukoliko se ovo izbaci prikayivace se samo mesto  koje je sacuvano u bazi bez mogucnosti promene
		function postaviMestaOpstine(opstine_selekt) {
			var vrsta_stete = document.pregled.vrstaSt[1].value;
			var osteceni_opstina = document.getElementById("osteceni_opstina_id").value;
			var osiguranik_krivac = document.getElementById("osiguranik_krivac_opstina_id").value;
			if (vrsta_stete != 'DPZ' && vrsta_stete != 'IO') {
				var vozac_krivac = document.getElementById("vozac_krivac_opstina_id").value;
				var vozac = document.getElementById("vozac_opstina_id").value;
			}
			//da se vrate sva mesta ostecenog na osnovu id ostine ostecenog
			if (opstine_selekt.value == osteceni_opstina && opstine_selekt.id == 'osteceni_opstina_id') {
				$.ajax({
					type: 'GET',
					url: '../common/funkcije.php?funkcija=vrati_sifarnik_mesta_opcije&opstina_id=' + opstine_selekt.value,
					datatype: 'json',
					success: function(ret) {
						var data = JSON.parse(ret);
						$('#osteceni_mesto_id').empty();
						$('#osteceni_mesto_id').append(data.opcije);
					}
				});
			} else if (opstine_selekt.value == vozac && opstine_selekt.id == 'vozac_opstina_id') {
				$.ajax({
					type: 'GET',
					url: '../common/funkcije.php?funkcija=vrati_sifarnik_mesta_opcije&opstina_id=' + opstine_selekt.value,
					datatype: 'json',
					success: function(ret) {
						var data = JSON.parse(ret);
						$('#vozac_mesto_id').empty();
						$('#vozac_mesto_id').append(data.opcije);

					}
				});
			} else if (opstine_selekt.value == osiguranik_krivac && opstine_selekt.id == 'osiguranik_krivac_opstina_id') {
				$.ajax({
					type: 'GET',
					url: '../common/funkcije.php?funkcija=vrati_sifarnik_mesta_opcije&opstina_id=' + opstine_selekt.value,
					datatype: 'json',
					success: function(ret) {
						var data = JSON.parse(ret);
						$('#osiguranik_krivac_mesto_id').empty();
						$('#osiguranik_krivac_mesto_id').append(data.opcije);
					}
				});
			} else if (opstine_selekt.value == vozac_krivac && opstine_selekt.id == 'vozac_krivac_opstina_id') {
				$.ajax({
					type: 'GET',
					url: '../common/funkcije.php?funkcija=vrati_sifarnik_mesta_opcije&opstina_id=' + opstine_selekt.value,
					datatype: 'json',
					success: function(ret) {
						var data = JSON.parse(ret);
						$('#vozac_krivac_mesto_id').empty();
						$('#vozac_krivac_mesto_id').append(data.opcije);
					}
				});
			}
		}
		//MARIJA KRAJ
		//BRANKA 14.11.2014. Funkcija za prikaz/sakrivanje dugmeta za kreiranje dopisa POCETAK
		function prikazi_sakrij_dugme(vrednost) {
			if (vrednost == -1) {
				document.getElementById("dugme_kreiraj_dopis").style.display = "none";
			} else {
				document.getElementById("dugme_kreiraj_dopis").style.display = "table-row";
			}
		}
		//BRANKA 14.11.2014. Funkcija za prikaz/sakrivanje dugmeta za kreiranje dopisa KRAJ
		// Branka - 2014-10-31 - Za DA/Ne odgovore u delu procene - PO¹ETAK
		jQuery(function($) {
			$('#slikao_vreme').mask('99:99');
		});

		function validate(id) {
			switch (id) {
				case 'imao_zapisnik':
					document.getElementById('nije_imao_zapisnik').checked = false;
					break;
				case 'nije_imao_zapisnik':
					document.getElementById('imao_zapisnik').checked = false;
					break;
				case 'imao_evropski_izvestaj':
					document.getElementById('nije_imao_evropski_izvestaj').checked = false;
					break;
				case 'nije_imao_evropski_izvestaj':
					document.getElementById('imao_evropski_izvestaj').checked = false;
					break;
				case 'izvrsio_uporedjivanje_vozila':
					document.getElementById('nije_izvrsio_uporedjivanje_vozila').checked = false;
					break;
				case 'nije_izvrsio_uporedjivanje_vozila':
					document.getElementById('izvrsio_uporedjivanje_vozila').checked = false;
					break;
				case 'slikao_drugo_vozilo_odvojeno':
					document.getElementById('nije_slikao_drugo_vozilo_odvojeno').checked = false;
					break;
				case 'nije_slikao_drugo_vozilo_odvojeno':
					document.getElementById('slikao_drugo_vozilo_odvojeno').checked = false;
					break;
				default:
					break;
			}
		}

		function prikazi_sakrij_polja(id) {
			if (id == "nije_imao_evropski_izvestaj" && document.getElementById("nije_imao_evropski_izvestaj").checked == true || (document.getElementById("imao_evropski_izvestaj").checked == false && document.getElementById("nije_imao_evropski_izvestaj").checked == false)) {
				document.getElementById("red_izvrsio_uporedjivanje_vozila").style.display = "none";
				document.getElementById("red_slikao_drugo_vozilo_odvojeno").style.display = "none";
				document.getElementById("red_slikao_gde_kada").style.display = "none";
				document.getElementById("red_slikao_drugo_vozilo_odvojeno").style.display = "none";
				document.getElementById("red_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen").style.display = "none";
				document.getElementById("izvrsio_uporedjivanje_vozila").checked = false;
				document.getElementById("nije_izvrsio_uporedjivanje_vozila").checked = false;
				document.getElementById("slikao_drugo_vozilo_odvojeno").checked = false;
				document.getElementById("nije_slikao_drugo_vozilo_odvojeno").checked = false;
				document.getElementById("slikao_gde").value = "";
				document.getElementById("slikao_kada").value = "";
				document.getElementById("slikao_vreme").value = "";
				document.getElementById("stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen").value = "";
			}
			if (id == "imao_evropski_izvestaj" && document.getElementById("imao_evropski_izvestaj").checked == true) {
				document.getElementById("red_izvrsio_uporedjivanje_vozila").style.display = "table-row";
				document.getElementById("red_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen").style.display = "table-row";
				if (document.getElementById("nije_izvrsio_uporedjivanje_vozila").checked == true) {
					document.getElementById("red_slikao_drugo_vozilo_odvojeno").style.display = "table-row";
					if (document.getElementById("slikao_drugo_vozilo_odvojeno").checked == true) {
						document.getElementById("red_slikao_gde_kada").style.display = "table-row";
					}
				}
			}

			if (id == "nije_izvrsio_uporedjivanje_vozila" && document.getElementById("nije_izvrsio_uporedjivanje_vozila").checked == true) {
				document.getElementById("red_slikao_drugo_vozilo_odvojeno").style.display = "table-row";
				if (document.getElementById("slikao_drugo_vozilo_odvojeno").checked == true) {
					document.getElementById("red_slikao_gde_kada").style.display = "table-row";
				}
			}

			if (id == "izvrsio_uporedjivanje_vozila" && document.getElementById("izvrsio_uporedjivanje_vozila").checked == true || (document.getElementById("nije_izvrsio_uporedjivanje_vozila").checked == false && document.getElementById("izvrsio_uporedjivanje_vozila").checked == false)) {
				document.getElementById("red_slikao_drugo_vozilo_odvojeno").style.display = "none";
				document.getElementById("red_slikao_gde_kada").style.display = "none";
				document.getElementById("slikao_drugo_vozilo_odvojeno").checked = false;
				document.getElementById("nije_slikao_drugo_vozilo_odvojeno").checked = false;
				document.getElementById("slikao_gde").value = "";
				document.getElementById("slikao_kada").value = "";
				document.getElementById("slikao_vreme").value = "";
			}
			if (id == "slikao_drugo_vozilo_odvojeno" && document.getElementById("slikao_drugo_vozilo_odvojeno").checked == true) {
				document.getElementById("red_slikao_gde_kada").style.display = "table-row";
			}
			if (id == "nije_slikao_drugo_vozilo_odvojeno" && document.getElementById("nije_slikao_drugo_vozilo_odvojeno").checked == true || (document.getElementById("nije_slikao_drugo_vozilo_odvojeno").checked == false && document.getElementById("slikao_drugo_vozilo_odvojeno").checked == false)) {
				document.getElementById("red_slikao_gde_kada").style.display = "none";
				document.getElementById("slikao_gde").value = "";
				document.getElementById("slikao_kada").value = "";
				document.getElementById("slikao_vreme").value = "";
			}
		}
		// Branka - 2014-10-31 - Za DA/Ne odgovore u delu procene - KRAJ


		/*
		 * Za otvaranje LIKVIDACIJA stranice
		 */
		function likvidacija() {
			var idstete = document.pregled.idstete.value;
			window.open("likvidacija.php?idstete=" + idstete, '_self');

		}

		function likvidacija_stete() {
			var idstete = document.pregled.idstete.value;
			window.open("likvidacija_stete.php?idstete=" + idstete, '_self');

		}

		// Branka - 2014-10-30 - Za toggle dugmeta 'dugme_resenje_odbijen'
		function toggle_dugme_resenje_odbijen(id) {

			//DODAO VLADA - DEO KODA ZA ODCEKIRANJE CHECKBOXA ODUSTAO I SAKRIVANJE DUGMETA RESENJE ODUSTAO
			$('#odustao_pravni_osnov').prop('checked', false);
			$('#resenje_odustao').hide();

			if (id == "odbijen") {
				document.getElementById("dugme_resenje_odbijen").style.display = "table-row";
			} else {
				document.getElementById("dugme_resenje_odbijen").style.display = "none";
			}
			// MARIJA 18.02.2015 - dodat uslo za checked u slucaju da je osnovan delimicno - POCETAK
			if (id != "" && id != "odbijen") {
				document.getElementById('labela_razlozi_umanjenja').style.display = "inline";
				$("#razlog_umanjenja_stete_id").combobox();
				$("#razlog_umanjenja_stete_id").toggle();
			}

			if (id == "osnovan_delimicno" && id != "" && id != "odbijen") {
				document.getElementById('labela_razlozi_umanjenja').style.display = "inline";
				document.getElementById('dodatno').style.display = "inline";
				$("#razlog_umanjenja_stete_id").combobox();
				$("#razlog_umanjenja_stete_id").toggle();
			} else {
				document.getElementById("labela_razlozi_umanjenja").style.display = "none";
				document.getElementById("dodatno").style.display = "none";
				document.getElementById("razlog_umanjenja_stete_id").style.display = "none";

				//DODAO VLADA - ZBOG RESETOVANJA POLJA KAD NIJE ODABRANO OSNOVAN DELIMICNO
				$("[name='delimicnoProc']").val('');
				$('#dodatno').val('');
				$('#dodatno').hide();
			}
			// MARIJA 18.02.2015 - dodat uslo za checked u slucaju da je osnovan delimicno - KRAJ
		}

		// MARIJA 24.10.2014. funkcija koja je dodata za uploads PDF fajla
		function upload() {
			var fd = new FormData(),
				myFile = document.getElementById("file").files[0];
			fd.append('file', myFile);
			var steta = document.pregled.idstete.value;
			var url = 'upload_dokumenti_knjiga_steta.php?steta=' + steta;
			$.ajax({
				url: url,
				type: 'POST',
				data: fd,
				processData: false,
				contentType: false,
				success: function(ret) {
					var data = JSON.parse(ret);
					alert(data.poruka);
					location.reload();
				}
			});
		}
		/* Copyright 2011, SPL 61 d.o.o. Sva prava zadr¾ana. */
		// Za storno i za storniranje od¹tetnog zahtevani // PO ZAHTEVU BEZ PROVERE!!!
		var jeStorno = 0;

		function StaroStanje() {
			if (document.pregled.storno.checked) {
				jeStorno = 1;
			}
		}

		function JesiSiguran() {
			if (jeStorno == 1) {
				alert("Storniranom od¹tetnom zahtevu se ne mo¾e promeniti status.\n\nMolimo vas obratite se administratoru baze podataka ako mislite da se radi o gre¹ci.");
				document.pregled.storno.checked = true;
			} 
			else {

				//DODAO VLADA - POCETAK

				//AKO JE CEKIRANO STORNO
				if ($('input[name="storno"]').is(':checked')) {

					//RESETOVANJE RADIO BUTTON GRUPE OSNOVAN
					$('input[name="osnovan"]').prop('checked', false);

					//RESETOVANJE I SAKRIVANJE INPUT POLJA UZ DELIMICNO OSNOVAN
					$("[name='delimicnoProc']").val('');
					$('#dodatno').val('');
					$('#dodatno').hide();
					$('#labela_razlozi_umanjenja').hide();

					//SAKRIVANJE BUTTONA RESENJE ODBIJEN I RESENJE ODUSTAO
					$('#dugme_resenje_odbijen').hide();
					$('#resenje_odustao').hide();
				}
				//DODAO VLADA - KRAJ

				var siguranSam = confirm("Da li ste sigurni da ¾elite da stornirate ovaj od¹tetni zahtev?\n\nStornirani zahtev se ne mo¾e ponovo aktivirati.");
				if (siguranSam == true) {
					document.pregled.storno.checked = true;
				} else {
					document.pregled.storno.checked = false;
				}
			}
		}

		function ProveriStorno() {
			if (jeStorno == 1) {
				alert("Ne mo¾e se ureðivati STORNIRANI od¹tetni zahtev.");
				return false;
			} else {
				return true;
			}
		}
		//Izmena zbog prigovora
		//Lazar Milossavljeviæ - 08-02-2013
		function otvoriZatvoriDodatnaPoljaPrigovor(element) {
			if (element.checked) {
				$('#datumPrigovor_div').show();
				$('#datumPrigovor_div_1').show();
				$('#osnovan_po_prigovoru_na_visinu_odstete_osnovan_div').show();
				$('#osnovan_po_prigovoru_na_visinu_odstete_delimicno_div').show();
				$('#osnovan_po_prigovoru_na_visinu_odstete_odbijen_div').show();
			} else {
				$('#datumPrigovor_div').hide();
				$('#datumPrigovor_div_1').hide();
				$('#datumPrigovor').val('');
				$('#osnovan_po_prigovoru_na_visinu_odstete_osnovan_div').hide();
				$('#osnovan_po_prigovoru_na_visinu_odstete_osnovan').attr('checked', false);
				$('#osnovan_po_prigovoru_na_visinu_odstete_delimicno_div').hide();
				$('#osnovan_po_prigovoru_na_visinu_odstete_delimicno').attr('checked', false);
				$('#delimicno_resen_po_prigovoru_procenat').val('');
				$('#osnovan_po_prigovoru_na_visinu_odstete_odbijen_div').hide();
				$('#osnovan_po_prigovoru_na_visinu_odstete_odbijen').attr('checked', false);
			}
		}

		function proveriUnosOdstetnogZahteva() {

			// MARIJA 20.02.2015 - provera da li postoji unet razlog umanjenja sstete za pravni osnov u tablei sifarnici.razlog_umanjenja_stete - ukoliko ne postoji, dodati deo u tabelu
			var idstete = document.pregled.idstete.value;

			//Nemanja Jovanovic
			var podnosioca_prijave_email_hidden = $("#podnosioca_prijave_email").val();
			$("#podnosioca_prijave_email_hidden").val(podnosioca_prijave_email_hidden);
			//


			if (idstete == null) {
				idstete = document.getElementById("idstete").value;
			}
			var vrsta_stete = document.pregled.vrstaSt[1].value;
			if (vrsta_stete != 'DPZ') {
				var dodatno_razlog_umenjenja_stete = document.getElementById("dodatno").value;
				var razlog_umanjenja_stete_id = document.getElementById("razlog_umanjenja_stete_id").value;
				var umenjenje_stete_id;
				umenjenje_stete_id = razlog_umanjenja_stete_id;
			}
			//var proba = document.getElementById("dodatno").id;

			if (vrsta_stete != 'DPZ' && dodatno_razlog_umenjenja_stete != null && dodatno_razlog_umenjenja_stete != '') {
				$.ajax({
					type: 'POST',
					url: 'funkcije.php',
					data: {
						funkcija: 'snimi_razlog_umanjenja_stete',
						dodatno_razlog_umenjenja_stete: dodatno_razlog_umenjenja_stete,
						umenjenje_stete_id: umenjenje_stete_id,
						idstete: idstete
					},
					datatype: 'json',
					async: false,
					success: function(ret) {
						var data = JSON.parse(ret);

						if (!data.flag) {
							alert("Nije unet nov razlog umanjenja stete");
							return false;
						} else {
							var id_razlog_umanjenja_stete = data.id;

							$("#razlog_umanjenja_stete_id").append(data.opcije);
							$("#razlog_umanjenja_stete_id").val(id_razlog_umanjenja_stete);

							//Dodat jos jedan ajax
							$.ajax({
								type: 'POST',
								url: 'funkcije.php',
								data: {
									funkcija: 'unesi_razlog_umanjenja_stete',
									id_razlog_umanjenja_stete: id_razlog_umanjenja_stete,
									idstete: idstete
								},
								datatype: 'json',
								async: false,
								success: function(ret) {
									var data = JSON.parse(ret);

									if (!data.flag) {
										alert("Nije unet razlog smanjenja ¹tete!");
										return false;
									}

								}
							});

						}
					}
				});
			}


			// Provera unetih vrednosti na osiguravajucim pokricima
			//var idstete = document.pregled.idstete.value;
			//var vrsta_stete = document.pregled.vrstaSt[1].value;

			//var tip_stete = document.pregled.tipSt[1].value;
			var tip_stete = document.getElementById('tip_predmeta').value;
			var vrsta_stete = document.getElementById('vrsta_osiguranja').value;

			if (vrsta_stete == 'DPZ' && tip_stete == '0205') {


				var radnik = document.getElementById('osiguravajuce_pokrice_radnik').value;
				var broj_osiguranih_pokrica = document.getElementById('broj_ostvarenih_osiguranih_pokrica').value;

				for (var i = 1; i <= broj_osiguranih_pokrica; i++) {
					// Proveri da li je uneto sve ¹to treba za svaki
					var id_osiguravajuceg_pokrica = document.getElementById('id_osiguravajuceg_pokrica_' + i);
					var iznos_osiguravajuceg_pokrica = document.getElementById('cena_osiguravajuceg_pokrica_' + i);
					var valuta_osiguravajuceg_pokrica = document.getElementById('valuta_osiguravajuceg_pokrica_' + i);
					var napomena_osiguravajuceg_pokrica = document.getElementById('napomena_osiguravajuceg_pokrica_' + i);
					if (id_osiguravajuceg_pokrica.value != -1 || iznos_osiguravajuceg_pokrica.value || valuta_osiguravajuceg_pokrica.value != -1) {
						if (id_osiguravajuceg_pokrica.value == -1) {
							alert("Odaberite osiguravajuæe pokriæe za redni broj: " + i + "!!!");
							id_osiguravajuceg_pokrica.focus();
							return false;
						}
						if (!iznos_osiguravajuceg_pokrica.value) {
							alert("Unesite iznos za redni broj: " + i + "!!!");
							iznos_osiguravajuceg_pokrica.focus();
							return false;
						}
						if (isNaN(iznos_osiguravajuceg_pokrica.value)) {
							alert("Unesite iznos za redni broj: " + i + "!!!");
							iznos_osiguravajuceg_pokrica.value = '';
							iznos_osiguravajuceg_pokrica.focus();
							return false;
						}
						if (valuta_osiguravajuceg_pokrica.value == -1) {
							alert("Odaberite valutu za redni broj: " + i + "!!!");
							id_osiguravajuceg_pokrica.focus();
							return false;
						}
					} else if (napomena_osiguravajuceg_pokrica.value) {
						alert("Zavr¹ite sa unosom podataka za osiguravajuæe pokriæe sa rednim brojem: " + i + "!!!");
						id_osiguravajuceg_pokrica.focus();
						return false;
					}
				}
				// Popuni JSON niz sa elementima dela za OSIGURANA POKRICA
				var jsonNiz = {};
				jsonNiz["broj_osiguranih_pokrica"] = broj_osiguranih_pokrica;
				jsonNiz["idstete"] = idstete;
				jsonNiz["radnik"] = radnik;
				for (var i = 1; i <= broj_osiguranih_pokrica; i++) {
					jsonNiz["id_osiguravajuceg_pokrica_" + i] = document.getElementById("id_osiguravajuceg_pokrica_" + i).value;
					jsonNiz["iznos_osiguravajuceg_pokrica_" + i] = document.getElementById("cena_osiguravajuceg_pokrica_" + i).value;
					jsonNiz["valuta_osiguravajuceg_pokrica_" + i] = document.getElementById("valuta_osiguravajuceg_pokrica_" + i).value;
					jsonNiz["napomena_osiguravajuceg_pokrica_" + i] = document.getElementById("napomena_osiguravajuceg_pokrica_" + i).value;
				}
				// Snimi sve
				var jsonNizstring = JSON.stringify(jsonNiz);
				$.ajax({
					type: 'POST',
					async: false,
					url: 'funkcije.php',
					data: {
						funkcija: 'snimi_ostvarena_pokrica_dpz_zp',
						podaci: jsonNizstring
					},
					datatype: 'json',
					success: function(ret) {
						//var data = JSON.parse(ret);
						//alert('Evo pocinje deo 3');
						var data = JSON.parse(ret);
						//alert(data.poruka); 
						exit;
					}
				});
			} // Zavr¹ava se za DPZ ZP

			// Provera za PRIGOVOR
			var prigovor_checkbox = document.getElementById('prigovor');
			var prigovor_datum = document.getElementById('datumPrigovor');
			var prigovor_radio_osnovan = document.getElementById('osnovan_po_prigovoru_na_visinu_odstete_osnovan');
			var prigovor_radio_delimicno = document.getElementById('osnovan_po_prigovoru_na_visinu_odstete_delimicno');
			var prigovor_radio_odbijen = document.getElementById('osnovan_po_prigovoru_na_visinu_odstete_odbijen');
			var prigovor_procenat = document.getElementById('delimicno_resen_po_prigovoru_procenat');
			if (prigovor_checkbox.checked) {
				if (prigovor_datum.value == '') {
					alert('Niste uneli datum prigovora!!!');
					prigovor_datum.focus();
					return false;
				}
				if (prigovor_procenat.value > 99 || prigovor_procenat.value < 0) {
					alert('Procenat treba biti u rasponu od 1 do 99 %!!!');
					prigovor_procenat.focus();
					return false;
				}
			}

			return true;
		}


		function vrati_osiguranike_sa_polise_putnog() {
			// povuci broj polise i vrstu osiguranja
			var vrsta_polise = document.pregled.vrPolise.value;
			var broj_polise = document.pregled.brPolise.value;
			var elements = document.getElementsByName('vrstaSt');
			var vrsta_stete = elements[1].value;
			var tip_stete = document.getElementsByName("tipSt");
			tip_stete = tip_stete[0].value;
			var id_stete = document.pregled.idstete.value;

			if (vrsta_polise != 'DPZ') {
				alert('Nije odabrana prava vrsta polise. Odaberie DPZ!!!');
				document.pregled.vrPolise.focus();
				return false;
			}

			if (broj_polise == '') {
				alert('Niste uneli broj polise!!!');
				document.pregled.brPolise.focus();
				return false;
			}
			// ajax provera da li ima podataka sa te polise
			// i prikaz tih podataka u listi
			$.ajax({
				type: 'GET',
				url: 'funkcije.php?funkcija=vrati_osiguranike_sa_polise_putnog&broj_polise=' + broj_polise + '&vrsta_polise=' + vrsta_polise + '&vrsta_stete=' + vrsta_stete + '&id_stete=' + id_stete,
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					// Vrati poruku ako je polisa neva¾eæa
					if (data.nevazeca == 1) {
						alert('Polisa je neva¾eæa (poni¹tena ili stornirana)!!!');
						document.pregled.brPolise.focus();
						return false;
					}
					// Izprazni podatke o O¹TEæENOM
					document.pregled.imeNazivOst.value = '';
					document.pregled.prezimeOst.value = '';
					document.pregled.jmbgPibOst.value = '';
					document.pregled.adresaOst.value = '';
					document.pregled.posbrOst.value = '';
					document.pregled.osteceni_broj_pasosa.value = '';
					document.pregled.osteceni_pol.value = '-1';
					document.pregled.osteceni_email.value = '';
					//MARIJA 19.11.2014 dodato da kada se prazne svi podaci o o¹teæenom
					document.pregled.osteceni_broj_licne_karte.value = '';
					//KRAJ MARIJA
					//document.pregled.sifra.value = '02.19.'+data.tarifna_grupa;
					$('#osiguranici_sa_putno_polise').hide();
					// Proveri da li ima podataka sa polise
					if (tip_stete != '0205') {
						// Samo pusti da se osve¹i stranica
						$.ajax({
							type: 'GET',
							url: 'funkcije.php?funkcija=vrati_redni_broj_stete_za_dpz_tb_ili_hi&broj_polise=' + broj_polise + '&vrsta_polise=' + vrsta_polise + '&vrsta_stete=' + vrsta_stete + '&id_stete=' + id_stete,
							datatype: 'json',
							success: function(ret) {
								var data = JSON.parse(ret);
								// Isprazni prethodno popunjene
								document.pregled.prezimeKriv.value = '';
								document.pregled.imeNazivKriv.value = '';
								document.pregled.jmbgPibKriv.value = '';
								//MARIJA 21.11.2014. dodato da se isprazne podaci i za licnu kartu osiguranik krivca
								document.pregled.osiguranik_krivac_broj_licne_karte.value = '';
								//KRAJ MARIJA
								// Popuni podatke o rednom broju ¹tete
								document.pregled.rbrSt.value = data.redni_broj_stete;
								// Ostali podaci
							}
						});
						return true;
					} else {
						// Otvori da se vidi select Osiguranici i popuni taj select
						$('#osiguranici_sa_putno_polise').show();
						$('#osiguranici_sa_putno_polise_select').empty();
						$('#osiguranici_sa_putno_polise_select').append(data.opcije);
						// Popuni podatke o UGOVARAèU
						document.pregled.prezimeKriv.value = data.prezime_ugovaraca;
						document.pregled.imeNazivKriv.value = data.ime_ugovaraca;
						document.pregled.jmbgPibKriv.value = data.jmbg_ili_pib_ugovaraca;
						// Redni broj ¹tete
						document.pregled.rbrSt.value = data.redni_broj_stete;
						document.pregled.razred_opasnosti_dpz_zp.value = data.razred_opasnosti;
						document.pregled.razred_opasnosti_dpz_zp_ispis.value = data.razred_opasnosti_ispis;
					}
				}
			});
			return false;

		}
		// Funkcija kojom se podaci iz select liste osiguranika sa polise putnog osiguranja popunjavaju na poljima za O©TEÆENOG
		function popuni_podatke_ostecenog_osiguranika_putno_polise(value) {
			var broj_polise = document.pregled.brPolise.value;
			$.ajax({
				type: 'GET',
				url: 'funkcije.php?funkcija=vrati_podatke_osiguranika_sa_polise_putnog&broj_polise=' + broj_polise + '&jmbg_osiguranika=' + value,
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					// popuni odgovarajuæa polja sa podacima o O¹teæenom
					document.pregled.imeNazivOst.value = data.ime_osiguranika;
					document.pregled.prezimeOst.value = data.prezime_osiguranika;
					document.pregled.jmbgPibOst.value = data.jmbg_osiguranika;
					document.pregled.adresaOst.value = data.adresa_osiguranika;
					document.pregled.posbrOst.value = data.postanski_broj_osiguranika;
					document.pregled.osteceni_broj_pasosa.value = data.broj_pasosa;
					document.pregled.osteceni_email.value = data.email_osiguranika;
				}
			});
			return false;
		}

		function otvoriZapisnik() {
			dopunski_select = document.getElementById('dopunski');

			var postavljen_zapisnik = document.getElementById('naziv_fajla_upload_hidden').value;
			if (dopunski_select.value == -1) {
				alert("Odaberite reviziju zapisnika!!!");
				dopunski_select.focus();
			} else if (postavljen_zapisnik != '') {
				idstete = document.pregled.idstete.value;
				vrati_adresu();
			} else {
				dopunski = $('#dopunski').val();
				trajno_niz = dopunski.split("_");
				trajno = trajno_niz[1];

				if (trajno == 0) {
					id_stete_zapisnik = trajno_niz[2];
					if (id_stete_zapisnik == document.pregled.idstete.value) {
						document.pregled.zapisnik.click();
					} else {
						alert("Postoji privremen zapisnik na prethodnoj reviziji predmeta!");
					}
				} else {
					//idstete = document.pregled.idstete.value;
					idstete = trajno_niz[1];
					dopunski = trajno_niz[0];
					window.open("zapisnik_kreiranje_pdf.php?id_stete=" + idstete + "&dopunski=" + dopunski, "_blank");
				}

			}
		}
		//Branka - 31.10.2016. funkcija za prikaz dugmeta za otkljucavanje zapisnika
		function prikazi_dugme_za_vracanje_u_privremen() {

			dopunski_select = document.getElementById('dopunski');
			var postavljen_zapisnik = document.getElementById('naziv_fajla_upload_hidden').value;
			if (dopunski_select.value == -1) {
				document.getElementById('otkljucaj_zapisnik').style.visibility = 'hidden';
				document.getElementById('obrisi_zapisnik').style.visibility = 'hidden';
			} else if (postavljen_zapisnik != '') {
				document.getElementById('otkljucaj_zapisnik').style.visibility = 'hidden';
				document.getElementById('obrisi_zapisnik').style.visibility = 'hidden';
			} else {
				dopunski = $('#dopunski').val();
				trajno_niz = dopunski.split("_");
				trajno = trajno_niz[1];
				dopunski = $('#dopunski').val();
				if (trajno == 0) {
					document.getElementById('otkljucaj_zapisnik').style.visibility = 'hidden';
					document.getElementById('obrisi_zapisnik').style.visibility = 'visible';
				} else {
					document.getElementById('otkljucaj_zapisnik').style.visibility = 'visible';
					document.getElementById('obrisi_zapisnik').style.visibility = 'visible';
				}

			}

		}

		function otkljucajObrisiZapisnik(id) {

			var dopunski = document.getElementById('dopunski').value;

			if (dopunski != '-1') {
				trajno_niz = dopunski.split("_");
				revizija = trajno_niz[0];

				var id_stete = document.pregled.idstete.value;
				var osnovni_predmet_id = document.getElementById('osnovni_predmet_id').value;

				if (id == 'obrisi_zapisnik') {
					var url = "obrisi_zapisnik";
					var siguranSam = confirm("Da li ste sigurni da ¾elite da obri¹ete zapisnik?");
				} else {
					var url = "otkljucaj_zapisnik";
					var siguranSam = true;
				}

				if (siguranSam) {
					$.ajax({
						type: 'POST',
						url: "funkcije.php?funkcija=" + url,
						datatype: 'json',
						data: {
							id_stete: id_stete,
							zapisnik: revizija,
							osnovni_predmet_id: osnovni_predmet_id
						},
						success: function(ret) {
							var data = JSON.parse(ret);
							alert(data.poruka);
							location.reload();
						}
					});
				}
			} else {
				alert("Odaberite zapisnik koji ¾elite da obri¹ete.");
				return;
			}
		}

		//MARIJA 28.10.2014. da se vraca adresa sa koje se preuzima selektovan fajl
		function vrati_adresu() {
			idstete = document.pregled.idstete.value;
			var potreban_fajl = document.getElementById('naziv_fajla_upload_hidden').value;
			document.getElementById('adresa').href = '../arhiva/stete/zapisnici_upload/' + idstete + '/' + potreban_fajl;
			var adresa = document.getElementById('adresa').href;
			window.open(adresa);
		}
		//ZAVRSENO

		function dodaj_ostvareno_osigurano_pokrice() {
			var broj_osiguranih_pokrica = parseInt(document.getElementById('broj_ostvarenih_osiguranih_pokrica').value) + 1;
			// Proveri da li je u prethodnom dodato sve ¹to treba
			var prethodni_red = broj_osiguranih_pokrica - 1;
			var id_osiguravajuceg_pokrica = document.getElementById('id_osiguravajuceg_pokrica_' + prethodni_red);
			var iznos_osiguravajuceg_pokrica = document.getElementById('cena_osiguravajuceg_pokrica_' + prethodni_red);
			var valuta_osiguravajuceg_pokrica = document.getElementById('valuta_osiguravajuceg_pokrica_' + prethodni_red);
			var napomena_osiguravajuceg_pokrica = document.getElementById('napomena_osiguravajuceg_pokrica_' + prethodni_red);
			if (id_osiguravajuceg_pokrica.value == -1) {
				alert("Odaberite osiguravajuæe pokriæe za redni broj: " + prethodni_red + "!!!");
				id_osiguravajuceg_pokrica.focus();
				return false;
			}
			if (!iznos_osiguravajuceg_pokrica.value) {
				alert("Unesite iznos za redni broj: " + prethodni_red + "!!!");
				iznos_osiguravajuceg_pokrica.focus();
				return false;
			}
			if (valuta_osiguravajuceg_pokrica.value == -1) {
				alert("Odaberite valutu za redni broj: " + prethodni_red + "!!!");
				id_osiguravajuceg_pokrica.focus();
				return false;
			}

			var broj_tr_tabele = $('table#tabela_osigurana_pokrica tr:last').index() - 2;
			$('#tabela_osigurana_pokrica tr:eq(' + broj_tr_tabele + ')').after(
				"<tr id='osiguravajuce_pokrice_podaci_" + broj_osiguranih_pokrica + "'>\n" +
				"<td class='uvucenRedTd' style='width:20px;'>\n" +
				"<div id='redni_broj_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "'><b>" + broj_osiguranih_pokrica + ".</b></div>\n" +
				"</td>\n" +
				"<td style='width:500px;'>\n" +
				"Predmet osiguravajuæeg pokriæa:\n" +
				"<select style='width:300px;' id='id_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "' name='id_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "' onkeypress='return handleEnter(this, event)'>\n" +
				"</select>" +
				"</td>\n" +
				"<td style='width:140px;'>\n" +
				"Iznos:\n" +
				"<input style='width:90px;' id='cena_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "' name='cena_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "' value='' size='20' height='15' onkeypress='return handleEnter(this, event)'>\n" +
				"</td>\n" +
				"<td style='width:140px;'>\n" +
				"Valuta:\n" +
				"<select style='width:90px;' id='valuta_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "' name='valuta_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "' onkeypress='return handleEnter(this, event)'>\n" +
				"<option value='-1'></option>" +
				"<option value='RSD' title='Dinar - RSD' >" +
				"Dinar - RSD" +
				"</option>" +
				"<option value='EUR' >" +
				"Evro - EUR" +
				"</option>" +
				"<option value='CHF' >" +
				"©vajcarski franak - CHF" +
				"</option>" +
				"<option value='USD' >" +
				"Amerièki dolar - USD" +
				"</option>" +
				"</select>" +
				"</td>\n" +
				"<td style='width:350px;'>\n" +
				"Napomena:\n" +
				"<input style='width:250px;' id='napomena_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "' name='napomena_osiguravajuceg_pokrica_" + broj_osiguranih_pokrica + "' value='' size='20' height='15' onkeypress='return handleEnter(this, event)'>\n" +
				"</td>\n" +
				"<td>\n" +
				"<input type='button' value='Obri¹i' id='obrisi_osiguravajuce_pokrice_" + broj_osiguranih_pokrica + "' name='obrisi_osiguravajuce_pokrice_" + broj_osiguranih_pokrica + "' onclick='obrisi_osiguravajuce_pokrice(" + broj_osiguranih_pokrica + ");' />" +
				"</td>\n" +
				"</tr>\n" +
				"<tr>\n");

			$.ajax({
				type: 'GET',
				url: 'funkcije.php?funkcija=vrati_osigurana_pokrica_opcije',
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					$('#id_osiguravajuceg_pokrica_' + broj_osiguranih_pokrica).append(data.opcije);
				}
			});

			document.getElementById('broj_ostvarenih_osiguranih_pokrica').value = broj_osiguranih_pokrica;
			return true;
		}

		function obrisi_osiguravajuce_pokrice(redni_broj_osiguravajuceg_pokrica) {
			var ukupno_pokrica = document.getElementById('broj_ostvarenih_osiguranih_pokrica').value;
			id_tr_obisati = "osiguravajuce_pokrice_podaci_" + redni_broj_osiguravajuceg_pokrica;
			document.getElementById('broj_ostvarenih_osiguranih_pokrica').value = parseInt(ukupno_pokrica) - 1;
			$('#' + id_tr_obisati).remove();
			//promeni sve ostale parametre
			var pocetak_i = parseInt(redni_broj_osiguravajuceg_pokrica) + 1;
			var kraj_i = parseInt(ukupno_pokrica) + 1;
			if (pocetak_i != kraj_i) {
				for (var i = pocetak_i; i < kraj_i; i++) {
					var obj_tr = document.getElementById('osiguravajuce_pokrice_podaci_' + i);
					var obj_redni_broj = document.getElementById('redni_broj_osiguravajuceg_pokrica_' + i);
					var obj_id_pokrica = document.getElementById('id_osiguravajuceg_pokrica_' + i);
					var obj_iznos_pokrica = document.getElementById('cena_osiguravajuceg_pokrica_' + i);
					var obj_valuta_pokrica = document.getElementById('valuta_osiguravajuceg_pokrica_' + i);
					var obj_napomena_pokrica = document.getElementById('napomena_osiguravajuceg_pokrica_' + i);
					var obj_obrisi_pokrice = document.getElementById('obrisi_osiguravajuce_pokrice_' + i);
					var novi_i = i - 1;
					obj_tr.setAttribute("id", 'osiguravajuce_pokrice_podaci_' + novi_i + '');
					obj_redni_broj.setAttribute("id", 'redni_broj_osiguravajuceg_pokrica_' + novi_i + '');
					obj_redni_broj.innerHTML = "<b>" + novi_i + ".</b>";
					obj_id_pokrica.setAttribute("id", 'id_osiguravajuceg_pokrica_' + novi_i);
					obj_id_pokrica.setAttribute("name", 'id_osiguravajuceg_pokrica_' + novi_i);
					obj_iznos_pokrica.setAttribute("id", 'cena_osiguravajuceg_pokrica_' + novi_i);
					obj_iznos_pokrica.setAttribute("name", 'cena_osiguravajuceg_pokrica_' + novi_i);
					obj_valuta_pokrica.setAttribute("id", 'valuta_osiguravajuceg_pokrica_' + novi_i);
					obj_valuta_pokrica.setAttribute("name", 'valuta_osiguravajuceg_pokrica_' + novi_i);
					obj_napomena_pokrica.setAttribute("id", 'napomena_osiguravajuceg_pokrica_' + novi_i);
					obj_napomena_pokrica.setAttribute("name", 'napomena_osiguravajuceg_pokrica_' + novi_i);
					obj_obrisi_pokrice.setAttribute("id", 'obrisi_osiguravajuce_pokrice_' + novi_i);
					obj_obrisi_pokrice.setAttribute("name", 'obrisi_osiguravajuce_pokrice_' + novi_i);
					obj_obrisi_pokrice.setAttribute("onclick", "obrisi_osiguravajuce_pokrice(" + novi_i + ");");
				}
			}
		}
		// Funkcija kojom se otvara reaktivirani od¹tetni zahtev iz liste od¹tetnih zahteva koji su nastali kao reaktivacija osnovnog zahteva
		function otvori_reaktivirani_odstetni_zahtev(idstete) {
			alert('Funkcija trenutno ne radi, obavestiti Sektor za IT!!!');
			// 	if (idstete != -1 && idstete != '') {
			// 		window.open('pregled.php?idstete='+idstete+'&dugme=DA', '_self');
			// 	}
		}

		function samoBrojeviITackaIMinus(evt) {
			var kod = evt.which;
			if (kod == 0 || kod == 8 || kod == 13 || (kod > 47 && kod < 58) || kod == 46 || kod == 45) {
				if (kod == 13) return false;
				return true;
			} else
				return false;
		}

		function zatvori_stranu() {
			var idstete = document.pregled.idstete.value;
			window.open("pregled.php?idstete=" + idstete + "&dugme=DA", "_self");
		}

		//Nevena 2017-07-03 POÈETAK
		//Funkcija koja vraæa uzroke za padajuæu listu na osnovu prosledjenog ID-ja 
		function vratiUzrokeZaRizike(id, preneti_uzrok) {

			var tipPredmeta = $("#tip_predmeta").val();
			var sifra = tipPredmeta.substr(0, 2);
			var tarifa = tipPredmeta.substr(2, 2);
			var uzrok_baza = $('#uzrok_baza').val();
			var datumkonac_baza = $('#datumkonac_baza').val();
			$.ajax({
				type: 'POST',
				url: 'funkcije.php?funkcija=vrati_uzroke_opcije',
				datatype: 'json',
				data: {
					sifra: sifra,
					tarifa: tarifa,
					id: id,
					uzrok_baza: uzrok_baza,
					preneti_uzrok: preneti_uzrok,
					datumkonac_baza: datumkonac_baza
				},
				success: function(ret) {
					var data = JSON.parse(ret);
					$('#uzrok').empty();
					document.getElementById('uzrok').disabled = false;
					$('#uzrok').append(data.opcije);
				}

			});
		}
		//Nevena 2017-07-03 KRAJ

		//Stajka Jeminovic 03.01.2018.
		function resenje_zahteva_sudski() {
			var datumkonac_baza = $('#datumkonac_baza').val();

			var idstete = document.pregled.idstete.value;
			window.open("resenje_zahteva_za_isplatu_sudski.php?idstete=" + idstete, '_self');

			// 	if(datumkonac_baza)
			// 	{
			// 		var idstete = document.pregled.idstete.value;
			// 		window.open("resenje_zahteva_za_isplatu_sudski.php?idstete="+idstete, '_self');

			// 	}
			// 	else
			// 	{
			// 		alert('Nije unet datum kompletiranja');	
			// 	}
		}


		// ------ Marko Markovic stetni dogadjaji datum mesto nastanka ------

		//Marko Stankovicdodao 15.03.2018.
		function omoguci_izmenu_sd(e) {
			if (e.checked) {
				document.getElementById("datum_nastanka").disabled = false;

				document.getElementById("opstina_nastanka").disabled = false;
				document.getElementById("mesto_nastanka").disabled = false;
				$('#opis_lokacije').attr("readonly", false);
				$('#izmeni_sd_dugme').show();

				$('#datum_nastanka').addClass('za_izmene');

				$("#opstina_nastanka").addClass('za_izmene');
				$("#mesto_nastanka").addClass('za_izmene');
				$("#opis_lokacije").addClass('za_izmene');

			} else {
				document.getElementById("datum_nastanka").disabled = true; // bilo je false...  
				document.getElementById("opstina_nastanka").disabled = true;
				document.getElementById("mesto_nastanka").disabled = true;
				$('#opis_lokacije').attr("readonly", true);
				$('#izmeni_sd_dugme').hide();

				$("#datum_nastanka").removeClass('za_izmene');

				$("#opstina_nastanka").removeClass('za_izmene');
				$("#mesto_nastanka").removeClass('za_izmene');
				$("#opis_lokacije").removeClass('za_izmene');


			}
		}
		//   ---------- Marko Markovic kraj ----------


		function vrati_mesta(id) {

			if (id == 0) {
				var option = "<option value ='-1'>Odaberite mesto</option>";
				$('#mesto_nastanka').html(option);
			} else {
				$.ajax({
					type: 'POST',
					url: 'funkcije.php?funkcija=puni_kombo_boks_mesto_sd',
					data: {
						id: id
					},
					success: function(ret) {
						var data = JSON.parse(ret);

						if (data.flag == false) {
							alert(data.poruka);
						} else {
							$('#mesto_nastanka').html(data.html_option);

						}
					}
				});
			}
		}

		//DODAO VLADA
		function vrati_mesta_reg(value,id) {

			$.ajax({

				type:'GET',
				url: '../../common/funkcije.php?funkcija=vrati_sifarnik_mesta_opcije&opstina_id='+value,
				datatype: 'json',

				success: function(ret) {

					var data = JSON.parse(ret);
					//console.log(data);

					$('#mesto_reg').html(data.opcije);
				}
			});
		}


		//FUNKCIJA NA PROMENU VREDNOSTI SELECTA SA DRZAVAMA - DODAO VLADA
		function izmeni_polja(id_drzave) {

			//AKO JE U PITANJU STRANA DRZAVA,DISABLE-UJ POLJA I RESETUJ SELECTE
			if(id_drzave != '199') {

				document.getElementById("opstina_reg").selectedIndex = "0";
				document.getElementById("mesto_reg").selectedIndex = "0";

				$('#opstina_reg').prop('disabled', true);
				$('#mesto_reg').prop('disabled', true);
			}
			//U SUPROTNOM,ENABLE-UJ POLJA
			else {

				$('#opstina_reg').prop('disabled', false);
				$('#mesto_reg').prop('disabled', false);
			}
		}


		//FUNKCIJA ZA PRIKAZ I SAKRIVANJE POLJA U ZAVISNOSTI OD TIPA SELEKTOVANOG REGRESNOG DUZNIKA - DODAO VLADA
		function promena_input_polja(regres_od) {

			var tip_lica;

			//AKO JE TIP DUZNIKA OSIGURAVAJUCE DRUSTVO
			if(regres_od == 'Osiguravajuæe dru¹tvo') {
				
				//SETOVANJE TIPA LICA NA PRAVNO
				$('#tip_lica1').prop('checked', true); 
				$('#tip_lica').prop('disabled', true); 

				//SAKRIJ INPUT POLJA ZA IME I PREZIME I RESETUJ IH
				$('#polje_ime_duznika').attr('hidden', true);
				$('#polje_prezime_duznika').attr('hidden', true);
				$('#ime_duznika').val('');
				$('#prezime_duznika').val('');

				//PRIKAZI SELECT I INPUT ZA OSIGURANJA
				$('#select_osig_drustvo').attr('hidden', false);
				$('#input_osig_drustvo').attr('hidden', false);

				
				//UPIS CEKIRANOG TIPA LICA U PROMENJIVU
				tip_lica = $("input[name='tip_lica']:checked").val();

				//AKO JE PRAVNO LICE,PROMENI MAXLENGTH I DODAJ BLUR FUNKCIJU ZA PROVERU PIBA
				if(tip_lica == 'pravno') {

					$("#jmbg_pib").attr('maxlength', 9);

					$("#jmbg_pib").off('blur');

					$("#jmbg_pib").blur(function(){

						proveri_pib(this);
					});
				}
			}
			//U SUPROTNOM
			else {

				//ODCEKIRANJE RADIO BUTTONA ZA TIP LICA
				$('#tip_lica1').prop('checked', false); 
				$('#tip_lica').prop('disabled', false); 

				$('#prezime_duznika').attr('readonly', false);
				$('#prezime_duznika').removeClass('disabled');

				//PRIKAZI INPUT POLJA ZA IME I PREZIME
				$('#polje_ime_duznika').attr('hidden', false);
				$('#polje_prezime_duznika').attr('hidden', false);
				
				//SAKRIJ SELECT I INPUT ZA OSIGURANJA
				$('#select_osig_drustvo').attr('hidden', true);
				$('#input_osig_drustvo').attr('hidden', true);
				
				//RESETUJ POLJE SA NAZIVOM OSIGURAVAJUCEG DRUSTVA
				$('#osiguranjeRegPotr').val('');

				//UPIS CEKIRANOG TIPA LICA U PROMENJIVU
				tip_lica = $("input[name='tip_lica']:checked").val();

				//AKO NIJE ODABRAN TIP LICA,SKINI SVE BLUR FUNKCIJE
				if (tip_lica === undefined) {

					$("#jmbg_pib").off('blur');
				}
			}

			//RESETOVANJE INPUT POLJA
			$('#ime_duznika').val('');
			$('#prezime_duznika').val('');
			$('#jmbg_pib').val('');
			$('#adresa_reg').val('');
			$('#telefon_reg').val('');
			$('#koliko_potrazivati').val('');
			$('#osiguranjeRegPotr').val('');

			//RESETOVANJE SELECT LISTA SA NAZIVOM OSIGURANJA,DRZAVOM,OPSTINOM I MESTOM
			$('option:selected', '#osiguravajuce_drustvo_id').removeAttr('selected');
			$("#osiguravajuce_drustvo_id option:first").attr('selected','selected');

			$('option:selected', '#drzava_reg_id').removeAttr('selected');
			$("#drzava_reg_id option:first").attr('selected','selected');

			$('option:selected', '#opstina_reg').removeAttr('selected');
			$("#opstina_reg option:first").attr('selected','selected');

			$('option:selected', '#mesto_reg').removeAttr('selected');
			$("#mesto_reg option:first").attr('selected','selected');
			$('#mesto_reg').html('');
		}


		// --------------------------------------------------------------------------  
		// ----------------- Marko Marokvic --------- izmena ucesca ---------------
		function izmeni_podatke_polisa_ucesce() {
			var ucesce = $("#ucesce").val();
			var stetni_dogadjaj_id = $('#stetni_dogadjaj_id').val();

			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=izmeni_ucesce",
				data: {
					ucesce: ucesce,
					stetni_dogadjaj_id: stetni_dogadjaj_id
				},
				success: function(ret) {
					var data = JSON.parse(ret);
					if (data.flag == true) {
						alert(data.poruka);
						window.location.reload(true);
					} else {
						alert(data.poruka);
					}
				}
			});
		}
		//---------------------------- Marko Markovic kraj -----------------------



		// ----------------------Marko Markovic datum mesto pocetak------------

		function izmeni_sd_mesta() {
			var odstetni_zahtev_id = $('#odstetni_zahtev_id').val();
			var datum_prijave = $('#datum_prijave').val();
			// var datum_nastanka = $().val('#datum_nastanka');
			var datum_nastanka = $('#datum_nastanka').val();

			var opstina_mesto = $('#opstina_nastanka').val();
			var mesto_nastanka = $('#mesto_nastanka').val();
			var opis_lokacije = $('#opis_lokacije').val();
			var stetni_dogadjaj_id = $('#stetni_dogadjaj_id').val();

			// Marko Markovic dodao zbog izmene datuma nezgode u o_zahtev
			var idstete = document.pregled.idstete.value;

			if (opstina_mesto == 0) {

				alert("Odaberite Mesto");
				return;
			}
			$.ajax({
				type: 'POST',
				url: 'funkcije.php?funkcija=izmeni_stetni_dogadjaj_mesto',
				datatype: 'json',
				data: {
					datum_nastanka: datum_nastanka,
					odstetni_zahtev_id: odstetni_zahtev_id,
					datum_prijave: datum_prijave,
					mesto_nastanka: mesto_nastanka,
					opis_lokacije: opis_lokacije,
					stetni_dogadjaj_id: stetni_dogadjaj_id,
					idstete: idstete
				},
				success: function(ret) {
					var data = JSON.parse(ret);
					if (data.flag == true) {
						alert(data.poruka);
						window.location.reload(true);
					} else {
						alert(data.poruka);
					}
				}
			});

		}
		//-----------------Marko Markovic kraj  -------------------   



		/*
		/*
		* Marko Stankovic16.03.2018 klikom na checkbox pojavljuje se dugme i select boks otkljucava
		 */
		function izmena_u_odstetnom_zahtevu(e) {
			if (e.checked) {
				$('#dugme_oz').show();
				document.getElementById("organizaciona_jedinica").disabled = false;
				$("#organizaciona_jedinica").addClass('za_izmene');
			} else {
				$('#dugme_oz').hide();
				document.getElementById("organizaciona_jedinica").disabled = true;
				$("#organizaciona_jedinica").removeClass('za_izmene');
			}
		}

		/*
		 * Prosledjuje id odstetnog yahteva selektovani vrednost organiyacione jedinice
		 * Ajaksom se prosledjuju podaci 
		 */


		function izmeni_u_oz_org_jed() {

			var organizaciona_jedinica = $('#organizaciona_jedinica').val();
			var odstetni_zahtev_id = $('#odstetni_zahtev_id').val();
			$.ajax({

				type: 'POST',
				url: "funkcije.php?funkcija=izmeni_oz_organizacionu_jeinicu",
				data: {
					organizaciona_jedinica: organizaciona_jedinica,
					odstetni_zahtev_id: odstetni_zahtev_id
				},
				success: function(ret) {
					var data = JSON.parse(ret);
					if (data.flag == true) {
						alert(data.poruka);
						window.location.reload(true);
					} else {
						alert(data.poruka);
					}
				}
			});
		}


		/*
		 * Izmena polisa  
		 */
		function dozvoli_izmene(e) {
			if (e.checked) {

				$('#izmeni_podatke_iz_polisa').show();

				$("#marka_vozila_osiguranik").attr("readonly", false);
				$("#tip_vozila_osiguranik").attr("readonly", false);
				$("#model_vozila_osiguranik").attr("readonly", false);
				$("#godiste_vozila_osiguranik").attr("readonly", false);
				$("#registarsko_podrucje_vozila_osiguranik").attr("readonly", false);
				$("#registarski_broj_vozila_osiguranik").attr("readonly", false);
				$("#broj_sasije_osiguranik").attr("readonly", false);


				$("#marka_vozila_osiguranik").addClass('za_izmene');
				$("#tip_vozila_osiguranik").addClass('za_izmene');
				$("#model_vozila_osiguranik").addClass('za_izmene');
				$("#godiste_vozila_osiguranik").addClass('za_izmene');
				$("#registarsko_podrucje_vozila_osiguranik").addClass('za_izmene');;
				$("#registarski_broj_vozila_osiguranik").addClass('za_izmene');
				$("#broj_sasije_osiguranik").addClass('za_izmene');


			} else {
				$('#izmeni_podatke_iz_polisa').hide();

				$("#marka_vozila_osiguranik").attr("readonly", true);
				$("#tip_vozila_osiguranik").attr("readonly", true);
				$("#model_vozila_osiguranik").attr("readonly", true);
				$("#godiste_vozila_osiguranik").attr("readonly", true);
				$("#registarsko_podrucje_vozila_osiguranik").attr("readonly", true);
				$("#registarski_broj_vozila_osiguranik").attr("readonly", true);
				$("#broj_sasije_osiguranik").attr("readonly", true);



				$("#marka_vozila_osiguranik").removeClass('za_izmene');
				$("#tip_vozila_osiguranik").removeClass('za_izmene');
				$("#model_vozila_osiguranik").removeClass('za_izmene');
				$("#godiste_vozila_osiguranik").removeClass('za_izmene');
				$("#registarsko_podrucje_vozila_osiguranik").removeClass('za_izmene');;
				$("#registarski_broj_vozila_osiguranik").removeClass('za_izmene');
				$("#broj_sasije_osiguranik").removeClass('za_izmene');


			}
		}


		/*
		 * Izmeni_podatke
		 */

		function izmeni_podatke_polisa_mup() {
			var broj_pilise = $("#broj_polise").val();
			var marka_vozila_osiguranik = $("#marka_vozila_osiguranik").val();
			var tip_vozila_osiguranik = $("#tip_vozila_osiguranik").val();
			var model_vozila_osiguranik = $("#model_vozila_osiguranik").val();
			var godiste_vozila_osiguranik = $("#godiste_vozila_osiguranik").val();
			var registarsko_podrucje_vozila_osiguranik = $("#registarsko_podrucje_vozila_osiguranik").val();
			var registarski_broj_vozila_osiguranik = $("#registarski_broj_vozila_osiguranik").val();
			var broj_sasije_osiguranik = $("#broj_sasije_osiguranik").val();
			var vrsta_obrasca = $('#vrsta_obrasca').val();
			var stetni_dogadjaj_id = $('#stetni_dogadjaj_id').val();

			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=izmeni_podatke_polisa_mup",
				data: {
					broj_pilise: broj_pilise,
					marka_vozila_osiguranik: marka_vozila_osiguranik,
					tip_vozila_osiguranik: tip_vozila_osiguranik,
					model_vozila_osiguranik: model_vozila_osiguranik,
					godiste_vozila_osiguranik: godiste_vozila_osiguranik,
					registarsko_podrucje_vozila_osiguranik: registarsko_podrucje_vozila_osiguranik,
					registarski_broj_vozila_osiguranik: registarski_broj_vozila_osiguranik,
					broj_sasije_osiguranik: broj_sasije_osiguranik,
					vrsta_obrasca: vrsta_obrasca,
					stetni_dogadjaj_id: stetni_dogadjaj_id
				},

				success: function(ret) {
					var data = JSON.parse(ret);
					if (data.flag == true) {
						alert(data.poruka);
						window.location.reload(true);
					} else {
						alert(data.poruka);
					}
				}
			});
		}

		function provera_radnik_sesija() {
			var rad_na_daljinu = '<?php echo $rad_na_daljinu; ?>';
			if (rad_na_daljinu) {
				$('#finansijsko_pravni_osnov').hide();
				$('#likvidacija').hide();
				$('.hr_presek').hide();
			}
		}

		function naziv_fajla_upload_hidden_fun() {

			//$("#dopunski").val($("#naziv_fajla_upload_hidden").val());

			$("#naziv_fajla_upload_hidden").val($("#dopunski").find("option:selected").attr("name"));
			//$('#dopunski option[value=-1]').attr('selected','selected').change();
		}

			  /************************************************************ */
	  // Kontrola popune polja za pravni osnov
	  // ®arko Petroviæ 17.3.2021.
	  function provera_advokata(pravniOsnovDao,pravniOsnovDao_1,pravniOsnovDao_2)
	  {
		
		// Jednaki, a nisu prazni!!!	    
		if ((pravniOsnovDao ==  pravniOsnovDao_1 && pravniOsnovDao != 0) || (pravniOsnovDao ==  pravniOsnovDao_2 && pravniOsnovDao != 0) || (pravniOsnovDao_1 ==  pravniOsnovDao_2 && pravniOsnovDao_1 != 0)) 
		       {
				alert('Advokati za pravni osnov moraju biti razlièita lica.');
				return false;
			   }  
	        return true;	    
	  }
	 
	  $(document).on('change','#pravniOsnovDao',function(){
		if($('#prigovor_indikator').val() == "1") 
		{
		   $pravniOsnovDao   = parseInt($('#pravniOsnovDao option:selected').val());
		   $pravniOsnovDao_1 = parseInt($('#pravniOsnovDao_1 option:selected').val());
		   $pravniOsnovDao_2 = parseInt($('#pravniOsnovDao_2 option:selected').val());
		   indikator         = provera_advokata($pravniOsnovDao,$pravniOsnovDao_1,$pravniOsnovDao_2);
		   if(!indikator)
		    $(this).val('0');
		}
	  });
	  $(document).on('change','#pravniOsnovDao_1',function(){
		if($('#prigovor_indikator').val() == "1") 
		{
		   $pravniOsnovDao   = parseInt($('#pravniOsnovDao option:selected').val());
		   $pravniOsnovDao_1 = parseInt($('#pravniOsnovDao_1 option:selected').val());
		   $pravniOsnovDao_2 = parseInt($('#pravniOsnovDao_2 option:selected').val());
		   indikator         = provera_advokata($pravniOsnovDao,$pravniOsnovDao_1,$pravniOsnovDao_2);
		   if(!indikator)
		    $(this).val('0');
		}
	  });
	  $(document).on('change','#pravniOsnovDao_2',function(){
		if($('#prigovor_indikator').val() == "1") 
		{
		   $pravniOsnovDao   = parseInt($('#pravniOsnovDao option:selected').val());
		   $pravniOsnovDao_1 = parseInt($('#pravniOsnovDao_1 option:selected').val());
		   $pravniOsnovDao_2 = parseInt($('#pravniOsnovDao_2 option:selected').val());
		   indikator         = provera_advokata($pravniOsnovDao,$pravniOsnovDao_1,$pravniOsnovDao_2);
		   if(!indikator)
		    $(this).val('0');
		}
	  });
	  

	/************************************************************ */
	</script>

	<style>
		.tborder {
			BORDER-TOP: black 2px dotted
		}

		.disabled {
			BACKGROUND: #00316C;
			font-weight: bold;
			color: white
		}

		.disabledbig {
			BACKGROUND: #00316C;
			font-weight: bold;
			color: white;
			height: 30;
			font-size: 18
		}

		#izmeni_sd_dugme {
			display: none;
		}

		#dugme_oz {
			display: none;
		}

		#izmeni_podatke_iz_polisa {
			display: none;
			margin-top: 20px;
		}

		#cekiraj_podaci_polisa {
			margin-top: 20px;
		}

		.za_izmene {
			background-color: white !important;
			color: black !important;
		}
	</style>
	<script language="javascript" src="../common/cal2.js">
		/*
Xin's Popup calendar script- Xin Yang (http://www.yxscripts.com/)
Script featured on/available at http://www.dynamicdrive.com/
This notice must stay intact for use
*/
	</script>
	<script language="javascript" src="../common/cal_stete/cal_stete.js"></script>
</head>

<!-- <body onLoad="StaroStanje();"> -->
<?php
if (!$prethodne && !$dokumentacija && !$zapisnik && !$dugme_kreiraj_dopis && !$dugme_pregledaj_dopise && !$dugme_resenje_odbijen && !$dugme_odluka && !$dugme_odluka_likvidacija && !$dugme_dopisi && !$odbijenica_likvidacija && !$lekarski_nalaz && !$obracun_visine_stete && !$obracun_visine_stete_n_dpz && !$obracun_visine_stete_0205_dpz && !$galerija && !$vozilo_dugme && !$nalozi && !$da && !$resenje_IO_0903) {
	$padding = "padding-top:190px;";
} else {
	$padding = "";
}

?>

<body style="<?php echo $padding; ?>" onload="provera_radnik_sesija();">

	<?php

	if (!$radnik or ($radnik > 999 and $radnik < 3000 and $radnik <> 2098 and $radnik <> 2195 and $radnik <> 2236 and $radnik <> 2212 and $radnik == 2103 and $radnik == 2121 and $radnik <> 2053)) {
		echo "<script type='text/javascript'>";
		echo "window.open ('../meni.php', 'contents')";
		echo "</script>";
		exit;
	}

	// Ovo je neophodno da bi skript radio i ako ne postoji RegisterGlobals (novi PHP)
	// 20.11.2014. ZP
	// Ako je ne¹to poslato GET-om, uradi prvo to
	foreach ($_GET as $kljuc => $vrednost) {
		${$kljuc} = $vrednost;
	}
	// Ako je ne¹to poslato i GET-om i POST-om, prepi¹i ono ¹to je poslato GET-om (bezbednost)
	foreach ($_POST as $kljuc => $vrednost) {
		${$kljuc} = $vrednost;
	}
	date_default_timezone_set('Europe/Belgrade');
	define('t', 't');

	$conn = pg_connect("host=localhost dbname=stete user=zoranp");
	if (!$conn) {
		echo "Gre¹ka otvaranja konekcije prema SQL serveru.";
		exit;
	}

	$conn2 = pg_connect("host=localhost dbname=amso user=zoranp");
	if (!$conn) {
		echo "Gre¹ka otvaranja konekcije prema SQL serveru.";
		exit;
	}
	require "../common/sifarnici_class.php";
	$sifarnici_class = new sifarnici_class();

	require "../common/funkcije_class.php";
	$funkcije_class = new funkcije_class();

	require "funkcije.php";
	define("KONSTANTA_PREGLED_ODSTETNOG_ZAHTEVA", "1");

	$idstete = (isset($_GET['predmet_odstetnog_zahteva_id'])) ? $_GET['predmet_odstetnog_zahteva_id'] : $_REQUEST['idstete'];
	//echo $idstete;
	$podaci_sa_poz = $funkcije_class->vrati_podatke_sa_predmeta_odstetnog_zahteva($idstete);
	$datum_otvaranja_predmeta = $podaci_sa_poz['datum_otvaranja_predmeta'];

	$pin_kod_za_web = $podaci_sa_poz['pin_kod'];  // Nemanja Jovanovic

	// Defini¹em promenljive koje koristim u sledeæem IF-u... Da izbegnem upozorenja...
	foreach (array('prethodne', 'dokumentacija', 'zapisnik', 'dugme_kreiraj_dopis', 'dugme_pregledaj_dopise', 'dugme_resenje_odbijen', 'lekarski_nalaz', 'obracun_visine_stete', 'galerija', 'vozilo_dugme', 'nalozi', 'da') as $a) {
		${$a} = isset(${$a}) && ${$a} ? ${$a} : '';
	}

	if (!$prethodne && !$dokumentacija && !$zapisnik && !$dugme_kreiraj_dopis && !$dugme_pregledaj_dopise && !$dugme_resenje_odbijen && !$dugme_odluka && !$dugme_odluka_likvidacija && !$dugme_dopisi && !$odbijenica_likvidacija  && !$lekarski_nalaz && !$obracun_visine_stete && !$obracun_visine_stete_n_dpz && !$obracun_visine_stete_0205_dpz && !$galerija && !$vozilo_dugme && !$nalozi && !$da && !$resenje_IO_0903) {
		//$idstete = (isset($_GET['predmet_odstetnog_zahteva_id'])) ? $_GET['predmet_odstetnog_zahteva_id'] : $_REQUEST['idstete'];
		$sql_odstetni_zahtev = "SELECT oz.id,oz.stetni_dogadjaj_id,tip_rente,osnov_rente, stetni_dogadjaj_id, oz.vrsta_osiguranja, oz.numericka_vrsta_osiguranja, poz.renta FROM odstetni_zahtev oz INNER JOIN predmet_odstetnog_zahteva poz ON (oz.id = poz.odstetni_zahtev_id) WHERE poz.id=$idstete";
		$rezultat_odstetni_zahtev = pg_query($conn, $sql_odstetni_zahtev);
		$niz_odstetni_zahtev = pg_fetch_assoc($rezultat_odstetni_zahtev);
		$id_stetnog_dogadjaja = $niz_odstetni_zahtev['stetni_dogadjaj_id'];
		$odstetni_zahtev_id = $niz_odstetni_zahtev['id'];
		$numericka_vrsta_osiguranja = $niz_odstetni_zahtev['numericka_vrsta_osiguranja'];
		$vrsta_osiguranja = $niz_odstetni_zahtev['vrsta_osiguranja'];
		$renta_lica = $niz_odstetni_zahtev['renta'];


		echo '<input type="hidden" id="stetni_dogadjaj_id" value="' . $id_stetnog_dogadjaja . '"/>';
		echo '<input type="hidden" id="odstetni_zahtev_id" value="' . $odstetni_zahtev_id . '" />';

		//	var_dump($niz_odstetni_zahtev);
		// 	exit;
		$stari_broj_predmeta = $funkcije_class->vrati_broj_predmeta($idstete, 1);
		$novi_broj_predmeta = $funkcije_class->vrati_broj_predmeta($idstete, 2);
		//ECHO "BROJ PREDMETA". $novi_broj_predmeta ."IDSTETE".$idstete ;

		$podaci_sa_stetnog_dogadjaja = $funkcije_class->vrati_podatke_sa_stetnog_dogadjaja($id_stetnog_dogadjaja);
		$broj_polise_sa_stetnog_dogadjaja = $podaci_sa_stetnog_dogadjaja['broj_polise'];
		$datum_nastanka_sa_stetnog_dogadjaja = $podaci_sa_stetnog_dogadjaja['datum_nastanka'];
		$vrsta_obrasca = $podaci_sa_stetnog_dogadjaja['vrsta_obrasca'];
		//BRANKA 01.07.2015 dodato za rentu
		$tip_rente_baza = $niz_odstetni_zahtev['tip_rente'];
		$osnov_rente_baza = $niz_odstetni_zahtev['osnov_rente'];
		//echo "$tip_rente,$osnov_rente";

		// 	$provera = $funkcije_class->provera_postojecih_predmeta_visestruki_prolaz(6, 'HI', '2410986782428');
		// 	var_dump($provera);
		// 	exit;


		require 'funkcije_stetni_dogadjaj.php';

		if (!$snimi_dok) {
			$margina_gornja = '50px';
			echo "<div style='margin-top:$margina_gornja;'>";
			//Marko Stankovicdodao Promenljivu radnik u funkciji 15.03.2018.
			pregled_stetnog_dogadjaja($id_stetnog_dogadjaja, 'pregled', $radnik);
			//Marko Stankovicdodao Promenljivu radnik u funkciji 16.03.2018.
			pregled_prijave_stete($odstetni_zahtev_id, 'pregled', $radnik);
			echo "</div>";
		}
	}
	// 24.09.2015 prebacena funkcija kako se ne bi kreirala dva upita za izvlacenje broja regresa posto je potrebno postaviti i na pocetku i pri kraju samog koda 
	function vrati_podatke_otvorenog_regresa($conn, $idstete)
	{
		$sql_regres = " SELECT idregres,brreg,datum_upisa FROM regresna WHERE idregres IN (SELECT idregres FROM steta_regres WHERE idstete = $idstete)";
		$rezultat_regres = pg_query($conn, $sql_regres);
		$niz_regres = pg_fetch_assoc($rezultat_regres);
		return $niz_regres;
	}



	// MARIJA 18.06.2015 - dodato da se izvuku podaci o ostecenom - POCETAK

	// dodato da bi se izvukao broj polise - 25.06.2015 - MARIJA
	$conn_amso = pg_connect("host=localhost dbname=amso user=zoranp");
	$sql_dpz = "SELECT sd.broj_polise AS broj_polise FROM stetni_dogadjaj sd
			INNER JOIN odstetni_zahtev oz
			ON sd.id = oz.stetni_dogadjaj_id
			INNER JOIN predmet_odstetnog_zahteva poz
			ON oz.id = poz.odstetni_zahtev_id
			WHERE poz.id = " . $idstete;

	$result_dpz = pg_query($conn, $sql_dpz);
	$niz_dpz = pg_fetch_assoc($result_dpz);

	$broj_polise = $niz_dpz['broj_polise'];

	echo "<form action=\"" . htmlentities($_SERVER['PHP_SELF']) . "\" method=\"post\" name=\"pregled\" onSubmit=\"return ProveriStorno();\">\n";

	//Nemanja Jovanovic - galerija
	if (isset($_POST['osnovni_zapisnik'])) {
		echo "<input type='hidden' name='tip_fotografije' value='osnovni'>\n";
	} else if (isset($_POST['dopunski_zapisnik'])) {
		echo "<input type='hidden' name='tip_fotografije' value='dopunski'>\n";
	} else {
		//echo "<input type='hidden' name='tip_fotografije' value='osnovni'>\n";
		$_SESSION['tip_fotografije'] = 'osnovni';
	}
	$glavna = 'g' . substr(date("Y-m-d"), 0, 4);

	$snimi_vozilo = isset($snimi_vozilo) ? $snimi_vozilo : '';
	$snimi_nalog = isset($snimi_nalog) ? $snimi_nalog : '';
	$zatvori_nalog = isset($zatvori_nalog) ? $zatvori_nalog : '';
	$snimi_dok = isset($snimi_dok) ? $snimi_dok : '';
	$izmeni = isset($izmeni) ? $izmeni : '';
	$prethodne = isset($prethodne) ? $prethodne : '';
	$dokumentacija = isset($dokumentacija) ? $dokumentacija : '';
	$zapisnik = isset($zapisnik) ? $zapisnik : '';
	$dugme_kreiraj_dopis = isset($dugme_kreiraj_dopis) ? $dugme_kreiraj_dopis : '';
	$dugme_pregledaj_dopise = isset($dugme_pregledaj_dopise) ? $dugme_pregledaj_dopise : '';
	$lekarski_nalaz = isset($lekarski_nalaz) ? $lekarski_nalaz : '';
	$obracun_visine_stete = isset($obracun_visine_stete) ? $obracun_visine_stete : '';
	$obracun_visine_stete_n_dpz = isset($obracun_visine_stete_n_dpz) ? $obracun_visine_stete_n_dpz : '';
	$obracun_visine_stete_0205_dpz = isset($obracun_visine_stete_0205_dpz) ? $obracun_visine_stete_0205_dpz : '';
	$resenje_IO_0903 = isset($resenje_IO_0903) ? $resenje_IO_0903 : '';
	$dugme_resenje_odbijen = isset($dugme_resenje_odbijen) ? $dugme_resenje_odbijen : '';
	$dugme_odluka = isset($dugme_odluka) ? $dugme_odluka : '';
	$dugme_odluka_likvidacija = isset($dugme_odluka_likvidacija) ? $dugme_odluka_likvidacija : '';
	$galerija = isset($galerija) ? $galerija : '';
	$vrati = isset($vrati) ? $vrati : '';
	$vozilo_dugme = isset($vozilo_dugme) ? $vozilo_dugme : '';
	$prepisi = isset($prepisi) ? $prepisi : '';
	$prepisiKriv = isset($prepisiKriv) ? $prepisiKriv : '';
	$calc = isset($calc) ? $calc : '';
	$submit = isset($submit) ? $submit : '';
	$submitk1 = isset($submitk1) ? $submitk1 : '';
	$submitk2 = isset($submitk2) ? $submitk2 : '';
	$submitk3 = isset($submitk3) ? $submitk3 : '';
	$submitk4 = isset($submitk4) ? $submitk4 : '';
	$submitk5 = isset($submitk5) ? $submitk5 : '';
	$odustani = isset($odustani) ? $odustani : '';
	$odustanik1 = isset($odustanik1) ? $odustanik1 : '';
	$odustanik2 = isset($odustanik2) ? $odustanik2 : '';
	$odustanik3 = isset($odustanik3) ? $odustanik3 : '';
	$odustanik4 = isset($odustanik4) ? $odustanik4 : '';
	$odustanik5 = isset($odustanik5) ? $odustanik5 : '';
	$pronadji_kat = isset($pronadji_kat) ? $pronadji_kat : '';
	$zatvori_vozilo = isset($zatvori_vozilo) ? $zatvori_vozilo : '';
	$nalozi = isset($nalozi) ? $nalozi : '';

	if (!$snimi_vozilo) {
		$davoz = 1;
	}

	if ($snimi_vozilo) {

		$davoz = 1;

		if ($prvaUpotreba && !je_datum($prvaUpotreba)  && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.prvaUpotreba.value='';\n";
			echo "alert(\"Neispravan datum prve upotrebe!\")\n";
			echo "document.pregled.prvaUpotreba.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}

		if ($cena && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $cena)  && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.cena.value='';\n";
			echo "alert(\"Neispravna cena vozila!\")\n";
			echo "document.pregled.cena.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}

		if ($procAmortizacije && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $procAmortizacije)  && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.procAmortizacije.value='';\n";
			echo "alert(\"Neispravan procenat amortizacije!\")\n";
			echo "document.pregled.procAmortizacije.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}

		if ($vrednost_vozilo && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $vrednost_vozilo)  && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.vrednost.value='';\n";
			echo "alert(\"Neispravna vrednost vozila!\")\n";
			echo "document.pregled.vrednost.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}

		if ($snagakw && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $snagakw)  && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.snagakw.value='';\n";
			echo "alert(\"Neispravna vrednost za snagu!\")\n";
			echo "document.pregled.snagakw.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}


		if ($ccm && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $ccm)  && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.ccm.value='';\n";
			echo "alert(\"Neispravna vrednost za zapreminu!\")\n";
			echo "document.pregled.ccm.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}

		if ($masa && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $masa)  && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.masa.value='';\n";
			echo "alert(\"Neispravna vrednost za masu!\")\n";
			echo "document.pregled.masa.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}

		if ($brVrata && !ereg("^[0-9]+$", $brVrata) && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.brVrata.value='';\n";
			echo "alert(\"Neispravan broj vrata!\")\n";
			echo "document.pregled.brVrata.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}

		if ($brRegMesta && !ereg("^[0-9]+$", $brRegMesta) && $davoz) {

			require "podaci_o_vozilu.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.brRegMesta.value='';\n";
			echo "alert(\"Neispravan broj registrovanih mesta!\")\n";
			echo "document.pregled.brRegMesta.focus();\n";
			echo "</script>\n";
			$davoz = 0;
		}
	}

	if (!$snimi_nalog) {
		$danal = 1;
	}
	if ($snimi_nalog) {

		$danal = 1;

		if ((!$datumKompl || !je_datum($datumKompl)) && $danal) {

			require "isplate.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumKompl.value='';\n";
			echo "alert(\"Ne mo¾ete uneti nalog za isplatu pre nego ¹to unesete datum kompletiranja!\")\n";
			echo "document.pregled.datumKompl.focus();\n";
			echo "</script>\n";
			$danal = 0;
		}

		if (!ereg("^[\-]?[0-9]{1,12}\.?[0-9]{0,2}$", $iznos)  && $danal) {

			require "isplate.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.iznos.value='';\n";
			echo "alert(\"Neispravan iznos!\")\n";
			echo "document.pregled.iznos.focus();\n";
			echo "</script>\n";
			$danal = 0;
		}

		if ((!$datum_naloga || !je_datum($datum_naloga)) && $danal) {

			require "isplate.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datum_naloga.value='';\n";
			echo "alert(\"Neispravan datum naloga!\")\n";
			echo "document.pregled.datum_naloga.focus();\n";
			echo "</script>\n";
			$danal = 0;
		}

		if (!$konacna  && $danal) {

			require "isplate.php";
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.konacna.value='';\n";
			echo "alert(\"Da li je isplata konaèna?\")\n";
			echo "document.pregled.konacna.focus();\n";
			echo "</script>\n";
			$danal = 0;
		}

		if ($danal) {


			$sql = "begin;";
			$rezultat = pg_query($conn, $sql);

			$sql = "select max(datum_naloga) as stari from isplate where idstete=$idstete ";
			$rezultat = pg_query($conn, $sql);
			$niz = pg_fetch_assoc($rezultat);

			$stari = $niz['stari'];
			/* Umesto samo provere $datum_naloga<$stari, ukoliko je iznos == 0, onda mo¾e unazad do datuma otvaranja predmeta od¹tetnog zahteva*/
			$provera_datum_manji_od_starog = ($datum_naloga < $stari) ? TRUE : FALSE;
			// $provera_datum_manji_od_starog = ($iznos == 0 && $datum_naloga>=$datum_otvaranja_predmeta) ? FALSE : TRUE ;
			if ($iznos == 0 && $datum_naloga >= $datum_otvaranja_predmeta) {
				$provera_datum_manji_od_starog = FALSE;
			}

			if ($provera_datum_manji_od_starog) {
				require "isplate.php";
				echo "<script type=\"text/javascript\">";
				if ($iznos == 0) {
					echo "alert(\"Datum naloga je mlaði od datuma otvaranja predmeta od¹tetnog zahteva!\")\n";
				} else {
					echo "alert(\"Datum naloga je mlaði od datuma poslednjeg naloga po toj ¹teti!\")\n";
				}
				echo "</script>";
				$danal = 0;
			} else {

				$sql = "select idstete from isplate where idstete=$idstete  and konacna='DA' ";
				$rezultat = pg_query($conn, $sql);
				$niz = pg_fetch_assoc($rezultat);

				$idst = $niz['idstete'];

				if ($idst && $konacna == 'DA') {
					require "isplate.php";
					echo "<script type=\"text/javascript\">";
					echo "alert(\"Imali ste ranije konaènu isplatu po ovoj ¹teti!\")\n";
					echo "</script>";
					$danal = 0;
				} else {
					$sql = "select max(rbr) as rbr from isplate where idstete=$idstete  ";
					$rezultat = pg_query($conn, $sql);
					$niz = pg_fetch_assoc($rezultat);

					$rbr = $niz['rbr'];

					if (!$rbr) {
						$rbr = 1;
					} else {
						$rbr = $rbr + 1;
					}

					// Branka - 2014-10-31 - DA/NE procena POÈETAK
					$imao_policijski_zapisnik = $_POST['imao_policijski_zapisnik'];

					// Marko Markovic 2020-05-13 zapisnici IO 
					$zapisnici_io = $_POST['zapisnici_io'];
					// zapisnici IO kraj 2020-05-13

					$imao_evropski_izvestaj = $_POST['imao_evropski_izvestaj'];
					$izvrsio_uporedjivanje_vozila = $_POST['izvrsio_uporedjivanje_vozila'];
					$slikao_drugo_vozilo_odvojeno = $_POST['slikao_drugo_vozilo_odvojeno'];
					$slikao_gde = $_POST['slikao_gde'];
					$slikao_kada = $_POST['slikao_kada'];
					$slikao_vreme = $_POST['slikao_vreme'];
					$tip_rente = $_POST['tip_rente'];
					$osnov_rente = $_POST['osnov_rente'];

					$stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen = $_POST['stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen'];
					// Branka - 2014-10-31 - DA/NE procena KRAJ

					// Promena zbog NALOGA ZA KNJIZENJE I ISPLATU STETA
					// Lazar Milosavljevic- januar 2013
					// Prvo se pokupe vrednosti svih promenljivih sa $_POST-@author html
					$kome_isplacujemo = $_POST['kome_isplacujemo'];
					$nalog_ime = $_POST['nalog_ime'];
					$nalog_prezime = $_POST['nalog_prezime'];
					$nalog_jmbg = $_POST['nalog_jmbg'];
					$nalog_naziv_firme = $_POST['nalog_naziv_firme'];
					$nalog_pib_firme = $_POST['nalog_pib_firme'];
					$nalog_naziv_servisa = $_POST['nalog_naziv_servisa'];
					$nalog_pib_servisa = $_POST['nalog_pib_servisa'];
					$nalog_adresa = $_POST['nalog_adresa'];
					$nalog_mesto = $_POST['nalog_mesto'];
					$nalog_tekuci_racun = $_POST['nalog_tekuci_racun'];
					$nalog_broj_fakture = $_POST['nalog_broj_fakture'];
					$nalog_poziv_na_broj = $_POST['nalog_poziv_na_broj'];
					$nalog_napomena = $_POST['nalog_napomena'];
					$iznos = $_POST['iznos'];
					$datum_naloga = $_POST['datum_naloga'];
					$idisp = $_POST['idisp'];
					$svrha = $_POST['svrha'];
					$konacna = $_POST['konacna'];
					$nalog_pravno_hidden = $_POST['nalog_pravno_hidden'];
					$nalog_fizicko_hidden = $_POST['nalog_fizicko_hidden'];
					$nalog_servisi = $_POST['nalog_servisi'];
					// Advokati
					$nalog_naziv_advokata = $_POST['nalog_naziv_advokata'];
					$nalog_jmbg_pib_advokata = $_POST['nalog_jmbg_pib_advokata'];
					$nalog_advokati = $_POST['nalog_advokati'];
					// Dodatna polja za inostrana plaæanja
					$placanje_u_inostranoj_valuti = ($_POST['placanje_u_inostranoj_valuti'] == 'on') ? 1 : 0;
					$nalog_iznos_strani = $_POST['nalog_iznos_strani'];
					$nalog_valuta = $_POST['nalog_valuta'];
					$nalog_ime_prezime_naziv_vlasnika_racuna = $_POST['nalog_ime_prezime_naziv_vlasnika_racuna'];
					$nalog_adresa_vlasnika_racuna = $_POST['nalog_adresa_vlasnika_racuna'];
					$nalog_iban = $_POST['nalog_iban'];
					$nalog_swift = $_POST['nalog_swift'];
					$nalog_account_name = $_POST['nalog_account_name'];
					$nalog_naziv_filijale_banke_bank_branch = $_POST['nalog_naziv_filijale_banke_bank_branch'];
					$nalog_lokacija_filijale_banke_bank_branch = $_POST['nalog_lokacija_filijale_banke_bank_branch'];
					$nalog_beneficiary = $_POST['nalog_beneficiary'];
					// Dodate su promenljive kojima se prenose sa isplate.php podaci koji se èuvaju a potrebni su za Nalog
					// $kome_isplacujemo, $nalog_ime, $nalog_prezime, $nalog_jmbg, $nalog_naziv_firme, itd...
					// U zavisnosti od toga kojme se isplaæuje od¹teta i koji je tip lica (fizièko ili pravno) imamo vi¹e sluèajeva
					//Branka dodato 02.07.2015
					// 		$tip_rente=$_POST['tip_rente'];
					// 		$osnov_rente=$_POST['osnov_rente'];
					if ($kome_isplacujemo == 1 || $kome_isplacujemo == 3 || $kome_isplacujemo == 5 || $kome_isplacujemo == 6) // Ako se isplaæuje O©TEÆENOM ili Ako se isplaæuje NEKOM DRUGOM ili ako se isplaæuje KOMPENZACIJA
					{
						if ($nalog_pravno_hidden == 'true') // PRAVNO lice
						{
							$fizicko_pravno_nalog = 'P';
							$ime_naziv_nalog = $nalog_naziv_firme;
							$prezime_nalog = '';
							$jmbg_pib_nalog = $nalog_pib_firme;
						} else if ($nalog_fizicko_hidden == 'true') // FIZIèKO lice
						{
							$fizicko_pravno_nalog = 'F';
							$ime_naziv_nalog = $nalog_ime;
							$prezime_nalog = $nalog_prezime;
							$jmbg_pib_nalog = $nalog_jmbg;
						}
					} else if ($kome_isplacujemo == 2) // Ako se isplaæuje SERVISU (servis je PRAVNO lice)
					{
						$fizicko_pravno_nalog = 'P';
						$ime_naziv_nalog = $nalog_naziv_servisa;
						$prezime_nalog = '';
						$jmbg_pib_nalog = $nalog_pib_servisa;
					} else if ($kome_isplacujemo == 4) // Ako se isplaæuje ADVOKATU (advokat je PRAVNO lice, a mo¾e biti i FIZIÈKO)
					{
						$fizicko_pravno_nalog = ($nalog_pravno_hidden == true) ? 'P' : 'F';
						$ime_naziv_nalog = $nalog_naziv_advokata;
						$prezime_nalog = '';
						$jmbg_pib_nalog = $nalog_jmbg_pib_advokata;
					}
					$kome_isplacujemo_nalog = $kome_isplacujemo;
					$adresa_nalog = $nalog_adresa;
					$postanski_broj_nalog = $nalog_mesto;
					if ($placanje_u_inostranoj_valuti == 1) {
						// Ukoliko je plaæanje u inostranoj valuti onda se defini¹u sledeæa polja
						$tekuci_racun_nalog = "";
						$broj_fakture_nalog = "";
						$poziv_na_broj_nalog = "";
						$iznos_strano = $nalog_iznos_strani;
						$valuta = $nalog_valuta;
						$ime_prezime_naziv_vlasnika_racuna = $nalog_ime_prezime_naziv_vlasnika_racuna;
						$adresa_vlasnika_racuna = $nalog_adresa_vlasnika_racuna;
						$iban = $nalog_iban;
						$swift = $nalog_swift;
						$account_name = $nalog_account_name;
						$naziv_banke_branch = $nalog_naziv_filijale_banke_bank_branch;
						$adresa_banke_branch = $nalog_lokacija_filijale_banke_bank_branch;
						$beneficiary = $nalog_beneficiary;
					} else {
						// Ukoliko je plaæanje na raèun u RSD, onda sledeæa
						$tekuci_racun_nalog = $nalog_tekuci_racun;
						$broj_fakture_nalog = $nalog_broj_fakture;
						$poziv_na_broj_nalog = $nalog_poziv_na_broj;
						$iznos_strano = "";
						$valuta = "RSD";
						$ime_prezime_naziv_vlasnika_racuna = "";
						$adresa_vlasnika_racuna = "";
						$iban = "";
						$swift = "";
						$account_name = "";
						$naziv_banke_branch = "";
						$adresa_banke_branch = "";
						$beneficiary = "";
					}

					// Ukoliko je izabrana opcija za kome_isplacujemo pri kojoj se bira servis (ili med.ustanova, ili advokat) iz liste
					switch ($kome_isplacujemo_nalog) {
						case 2:
							$odabrano_id_nalog = $nalog_servisi;
							break;
						case 4: // Za ADVOKATE
							$odabrano_id_nalog = $nalog_advokati;
							break;
						case 5: // Za Kompenzaciju
						case 6: // Za Podnosioca zahteva
							$odabrano_id_nalog = -1;
							break;
							//Pro¹iriti za MED.USTANOVE i LEKARE
						default:
							$odabrano_id_nalog = -1;
							break;
					}
					// Trimovanje stringova pre upisa
					$ime_naziv_nalog = trim($ime_naziv_nalog);
					$prezime_nalog = trim($prezime_nalog);
					$jmbg_pib_nalog = trim($jmbg_pib_nalog);
					$adresa_nalog = trim($adresa_nalog);
					$tekuci_racun_nalog = trim($tekuci_racun_nalog);
					$broj_fakture_nalog = trim($broj_fakture_nalog);
					$poziv_na_broj_nalog = trim($poziv_na_broj_nalog);
					$napomena_na_nalogu_nalog = trim($nalog_napomena);
					$odabrano_id = $odabrano_id_nalog;
					$iznos_strano_nalog = trim($iznos_strano);
					$valuta_nalog = trim($valuta);
					$ime_prezime_naziv_vlasnika_racuna_nalog = trim($ime_prezime_naziv_vlasnika_racuna);
					$adresa_vlasnika_racuna_nalog = trim($adresa_vlasnika_racuna);
					$iban_nalog = trim($iban);
					$swift_nalog = trim($swift);
					$account_name_nalog = trim($account_name);
					$naziv_banke_branch_nalog = trim($naziv_banke_branch);
					$adresa_banke_branch_nalog = trim($adresa_banke_branch);
					$beneficiary_nalog = trim($beneficiary);
					// Upis u tabele (privremena za AK i odmah u isplate za ostale) - EDIT (LAZAR) 2016-06-15 - Za sve vrste ¹teta ide u privremene
					// 		if ($vrstaSt == 'AK' || $vrstaSt == 'AO')
					// 		{
					$sql_redni_broj = "select max(rbr) as rbr from isplate_privremena where idstete=$idstete  ";
					$rezultat_redni_broj = pg_query($conn, $sql_redni_broj);
					$niz_redni_broj = pg_fetch_assoc($rezultat_redni_broj);
					$redni_broj_privremenog_naloga = $niz_redni_broj['rbr'];

					$redni_broj_privremenog_naloga = (!$redni_broj_privremenog_naloga) ? 1 : $redni_broj_privremenog_naloga + 1;
					$sifra_nalog = str_replace(".", '', $sifra_nalog);
					$sql = "insert into isplate_privremena
					(idstete, rbr, datum_naloga, iznos, svrha, konacna,
					kome_isplacujemo, fizicko_pravno, ime_naziv, prezime, jmbg_pib, adresa, postanski_broj,
					tekuci_racun, broj_fakture, poziv_na_broj, napomena_na_nalogu, odabrano_id, placanje_u_inostranoj_valuti,
				  ime_prezime_naziv_vlasnika_racuna, adresa_vlasnika_racuna, iban, swift, account_name,
				  beneficiary, naziv_banke_branch, adresa_banke_branch, status, iznos_strano, valuta,
				  datum_vreme_privremeno, radnik_privremeno, sifra)
					VALUES ( $idstete, $redni_broj_privremenog_naloga, '$datum_naloga', $iznos, ";

					if ($svrha) {
						$sql .= " '$svrha', ";
					} else {
						$sql .= " null ,";
					}
					if ($konacna) {
						$sql .= " '$konacna',";
					} else {
						$sql .= " null,";
					}
					$sql .= " $kome_isplacujemo_nalog,'$fizicko_pravno_nalog','$ime_naziv_nalog', '$prezime_nalog','$jmbg_pib_nalog','$adresa_nalog', $postanski_broj_nalog, ";
					if ($tekuci_racun_nalog != '')
						$sql .= " '$tekuci_racun_nalog',";
					else
						$sql .= " NULL,";
					if ($broj_fakture_nalog != '')
						$sql .= " '$broj_fakture_nalog',";
					else
						$sql .= " NULL,";
					if ($poziv_na_broj_nalog != '')
						$sql .= " '$poziv_na_broj_nalog', ";
					else
						$sql .= " NULL,";
					if ($napomena_na_nalogu_nalog != '')
						$sql .= " '$napomena_na_nalogu_nalog', ";
					else
						$sql .= " NULL,";
					$sql .= " $odabrano_id,$placanje_u_inostranoj_valuti::bit,'$ime_prezime_naziv_vlasnika_racuna_nalog', '$adresa_vlasnika_racuna_nalog', '$iban_nalog','$swift_nalog', ";
					if ($account_name_nalog != '')
						$sql .= " '$account_name_nalog', ";
					else
						$sql .= " NULL,";
					if ($beneficiary_nalog != '')
						$sql .= " '$beneficiary_nalog', ";
					else
						$sql .= " NULL,";
					$sql .= " '$naziv_banke_branch_nalog', '$adresa_banke_branch_nalog','PRIVREMEN', ";
					if ($iznos_strano_nalog != '')
						$sql .= " '$iznos_strano_nalog', ";
					else
						$sql .= " NULL,";
					$sql .= " '$valuta_nalog', current_timestamp, $radnik, '$sifra_nalog'";
					$sql .= " );";
					$rezultat1 = pg_query($conn, $sql);

					if ($rezultat1) {
						$sql = "select isplaceno,nalog from knjigas where idstete=$idstete";
						$rezultat = pg_query($conn, $sql);
						$niz = pg_fetch_assoc($rezultat);
						$isplaceno = $niz['isplaceno'];
						$nalog = $niz['nalog'];
						$sql = "commit;";
						$rezultat = pg_query($conn, $sql);
	?>
						<script>
							$(document).ready(function() {

								document.getElementById("nalozi_dugme").click();
							});
						</script>
		<?php
					} else {
						$sql = "rollback;";
						$rezultat = pg_query($conn, $sql);
					}
					// 		}
					// 		else
					// 		{
					// 			$sql="insert into isplate
					// 								(idstete, rbr, datum_naloga, iznos, svrha, konacna, radnik, dana , vreme,
					// 								kome_isplacujemo, fizicko_pravno, ime_naziv, prezime, jmbg_pib, adresa, postanski_broj,
					// 								tekuci_racun, broj_fakture, poziv_na_broj, napomena_na_nalogu, odabrano_id)
					// 						VALUES ( $idstete, $rbr, '$datum_naloga', $iznos, ";
					// 			if ($svrha)
					// 			{
					// 				$sql.=" '$svrha', ";
					// 			}
					// 			else
					// 			{
					// 				$sql.=" null ,";
					// 			}
					// 			if ($konacna)
					// 			{
					// 				$sql.=" '$konacna',";
					// 			}
					// 			else
					// 			{
					// 				$sql.=" null,";
					// 			}
					// 			$sql.=" $radnik, current_date, current_time, $kome_isplacujemo_nalog,'$fizicko_pravno_nalog','$ime_naziv_nalog', '$prezime_nalog','$jmbg_pib_nalog','$adresa_nalog', $postanski_broj_nalog,'$tekuci_racun_nalog', ";
					// 			if($broj_fakture_nalog != '')
					// 				$sql.=" '$broj_fakture_nalog',";
					// 			else
					// 				$sql.=" NULL,";
					// 			if($poziv_na_broj_nalog != '')
					// 				$sql.=" '$poziv_na_broj_nalog', ";
					// 			else
					// 				$sql.=" NULL,";
					// 			if($napomena_na_nalogu_nalog != '')
					// 				$sql.=" '$napomena_na_nalogu_nalog', ";
					// 			else
					// 				$sql.=" NULL,";
					// 			$sql.=" $odabrano_id);";
					// 			$rezultat1=pg_query($conn,$sql);

					// 			if ($konacna){$faza='FAZA 10 - LIKVIDACIJA'; $faza_id=10;}

					// 			// OLD - $sql="update knjigas set isplaceno=((select sum(iznos) as iznos from isplate where idstete=$idstete))  ";
					// 			$sql="update predmet_odstetnog_zahteva set isplaceno=((select sum(iznos) as iznos from isplate where idstete=$idstete))  ";
					// 			if ($faza){$sql.=" , faza='$faza'".", faza_id=$faza_id"; }
					// 			if ($konacna=='DA'){$sql.=" , nalog='$datum_naloga'"; }

					// 			$sql.=" where id=$idstete";
					// 			$rezultat2=pg_query($conn,$sql);

					// 			if ( $rezultat1 && $rezultat2)
					// 			{

					// 				$sql="select isplaceno,nalog from knjigas where idstete=$idstete";
					// 				$rezultat=pg_query($conn,$sql);
					// 				$niz=pg_fetch_assoc($rezultat);

					// 				$isplaceno= $niz['isplaceno'];
					// 				$nalog= $niz['nalog'];

					// 				$sql="commit;";
					// 				$rezultat=pg_query($conn,$sql);

					// 			}
					// 			else
					// 			{
					// 				$sql="rollback;";
					// 				$rezultat=pg_query($conn,$sql);
					// 			}
					// 		}
					// Kraj promena - januar 2013

				}
			}
		}
	}

	if ($zatvori_nalog) {

		$sql = "select isplaceno,nalog from knjigas where idstete=$idstete";
		$rezultat = pg_query($conn, $sql);
		$niz = pg_fetch_assoc($rezultat);

		$isplaceno = $niz['isplaceno'];
		$nalog = $niz['nalog'];
	}

	// ---------------------------- POCETAK DOK -------------------- UPDATE INSERT ------
	//unos dokumentacije
	if (!$snimi_dok) {
		$dadok = 1;
	}

	if ($snimi_dok) {

		$dadok = 1;

		for ($dok = 1; $dok <= $brojdok + 2; $dok++) {

			if ($iddok[$dok] && $dadok) {

				//provera datuma
				if (($datum_dost[$dok] && !je_datum($datum_dost[$dok])) && $dadok) {

					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Neispravan datum kada je dokument dostavljen!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}

				if (($datum_dost[$dok] &&  $datum_dost[$dok] > date("Y-m-d")) && $dadok) {

					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Datum dostavljanja dokumenta mora biti mlaði ili jednak dana¹njem!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}


				if ($datum_dost[$dok] && $dok_ned[$dok] && $dadok) {

					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Datum dostavljanja dokumenta se ne unosi za dokument koji nedostaje!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}

				//provera da u isto vreme ne mogu oba polja biti cekirana

				if ($dok_pri[$dok] && $dok_ned[$dok] && $dadok) {

					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Dokument ne mo¾e u isto vreme da bude i nedostajuæi i dostavljen!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}

				// //2015-10-29
				if ($datum_dost[$dok] && ($datum_dost[$dok] < $min_datum) && $dadok) {

					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Datum dostavljanja dokumenta ne sme biti manji od minimalnog datuma dostavljanja dokumenta!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}

				/*
// Marko Markovic zakomentarisao 2019-12-05 
if(!$opis[$dok] && ($dok_pri[$dok] || $dok_ned[$dok]) && $dadok){

require "dokumentacija.php";
echo "<script language=\"javascript\">\n";
echo "alert(\"Unesite opis dokumenta!\")\n";
echo "</script>\n";
$dadok=0;

}
// Marko Markovic kraj
*/

				//update radimo
				if (($dok_pri[$dok] || $dok_ned[$dok]) && $dadok == 1) {

					$sql = "update dokumentacija set ";
					if ($dok_pri[$dok] && !$dok_ned[$dok]) {
						$sql .= " prilozeno=true, nedostaje=null,";
					}
					if ($dok_ned[$dok] && !$dok_pri[$dok]) {
						$sql .= " nedostaje=true, datum_trazi=current_date, ";
					}

					if ($dok_pri[$dok]) {

						if ($datum_dost[$dok]) {
							$sql .= " datum_dost='$datum_dost[$dok]',";
						} else {
							$sql .= " datum_dost=current_date,";
						}
					}
					// Marko Markovic vidi da li se ovaj deo izbacuje ???? 9991 i 9992  
					if (($id_sif[$dok] == 9991 || $id_sif[$dok] == 9992) && $opis[$dok]) {
						$sql .= " dokument='$opis[$dok]',";
					}

					// FAZA - ne bi trebalo update
					$sql .= " radnik=$radnik, dana=current_date, vreme=current_time where idstete=$idstete and iddok=$iddok[$dok]";

					$rezultat1 = pg_query($conn, $sql);
				}

				if (!$dok_pri[$dok] && !$dok_ned[$dok] && $iddok[$dok] && $dadok == 1) {
					$sql = "UPDATE dokumentacija set nedostaje =NULL WHERE idstete=$idstete and iddok=$iddok[$dok]";
					$rezultat1 = pg_query($conn, $sql);
				}


				//ako ne prelazi iz satatusa nedostaje u status prilozeno ne sme da se unosi datum_dost
				//zabrana da se vraca iz statusa dostavljeno u status nedostaje nece moci da ga decekira - disable (ako je bilo u bazi)
			} else {

				//provera datuma

				if (($datum_dost[$dok] && !je_datum($datum_dost[$dok])) && $dadok) {

					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Neispravan datum kada je dokumentacija dostavljena!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}

				if (($datum_dost[$dok] &&  $datum_dost[$dok] > date("Y-m-d")) && $dadok) {

					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Datum dostavljanja dokumenta mora biti mlaði ili jednak dana¹njem!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}

				//provera da u isto vreme ne mogu oba polja biti cekirana
				if ($dok_pri[$dok] == true && $dok_ned[$dok] == true && $dadok) {

					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Dokument ne mo¾e u isto vreme da bude i nedostajuæi i dostavljen!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}

				//2015-10-29
				if ($datum_dost[$dok] && ($datum_dost[$dok] < $min_datum) && $dadok) {

					//$dadok=0;
					require "dokumentacija.php";
					echo "<script language=\"javascript\">\n";
					echo "alert(\"Datum ne mo¾e biti manji od datuma podno¹enja zahteva!\")\n";
					echo "</script>\n";
					$dadok = 0;
				}

				/*
// Marko Markovic zakomentarisao 2019-12-05 
if(!$opis[$dok] && ($dok_pri[$dok] || $dok_ned[$dok]) && $dadok){

require "dokumentacija.php";
echo "<script language=\"javascript\">\n";
echo "alert(\"Unesite opis dokumenta!\")\n";
echo "</script>\n";
$dadok=0;

}
// Marko Markovic kraj
*/

				//insert radimo
				if (($dok_pri[$dok] || $dok_ned[$dok]) && $dadok) {
					// -------- Marko Markovic dodavanje faze!!! 2019-12-06 
					$conn_amso = pg_connect("dbname=amso user=zoranp");
					$sql_radnik_faza = "SELECT radnik, faza_stete FROM radnik WHERE radnik = $radnik";      // WHERE faza_stete IS NOT NULL";
					$rez_sql = pg_query($conn_amso, $sql_radnik_faza);
					$niz_radnik_faza = pg_fetch_assoc($rez_sql);

					$faza_dok = $niz_radnik_faza['faza_stete'];

					// Marko Markovic kraj faze ---- faza se dalje upisuje u dokumentacija tabelu

					$sql = "insert into dokumentacija (idstete, id_sif, dokument, prilozeno, datum_trazi, nedostaje, datum_dost, radnik, dana, vreme, faza) values ($idstete, $id_sif[$dok], '$opis[$dok]',  ";

					if ($dok_pri[$dok]) {
						$sql .= " $dok_pri[$dok],";
					} else {
						$sql .= " null,";
					}
					if ($dok_ned[$dok]) {
						$sql .= " current_date,";
					} else {
						$sql .= " null,";
					}
					if ($dok_ned[$dok]) {
						$sql .= " true,";
					} else {
						$sql .= " null,";
					}
					if ($dok_pri[$dok]) {

						if ($datum_dost[$dok]) {
							$sql .= " '$datum_dost[$dok]',";
						} else {
							$sql .= "  current_date,";
						}
					} else {
						$sql .= " null,";
					}
					// Marko Markovic ubacena faza
					$sql .= " $radnik, current_date, current_time, $faza_dok )";

					$rezultat1 = pg_query($conn, $sql);
				}
			}
		}

		if ($dadok) {

			// OLD - $sql2="update knjigas set ";
			$sql2 = "update predmet_odstetnog_zahteva set ";

			if ($napomena_dok) {
				$sql2 .= " napomena_dok='$napomena_dok',";
			} else {
				$sql2 .= " napomena_dok=null,";
			}

			$sql2 .= " radnik=$radnik, datum=current_date, vreme=current_time where id=$idstete";
			$rezultat2 = pg_query($conn, $sql2);
		} else {
			$rezultat2 = true;
		}

		if ($rezultat1 && $rezultat2 && $dadok) {

			$sql = "commit;";
			$rezultat = pg_query($conn, $sql);

			echo "<script type=\"text/javascript\">";
			echo "alert(\"Podaci su uspe¹no upisani!\")\n";

			echo "</script>";
		} else {
			$sql = "rollback;";
			$rezultat = pg_query($conn, $sql);
		}

		//unos dokumentacije kraj
	}
	// BRANKA - Dodat uslov za dugme kreiraj dopis
	$sql_sudski = "select sudski_postupak_id, novi_broj_predmeta || ' (' || stari_broj_predmeta || ')' as broj, * from predmet_odstetnog_zahteva where id=$idstete";
	$rezultat_sudski = pg_query($conn, $sql_sudski);
	$niz_sud = pg_fetch_assoc($rezultat_sudski);
	$sudski_postupak_id = $niz_sud['sudski_postupak_id'];

	$osnovni_predmet_id_reaktiviranog = $niz_sud['osnovni_predmet_id'];

	echo "<input type='hidden' id='osnovni_predmet_id' value=$osnovni_predmet_id_reaktiviranog >";


	$sql_sudski_postupak = "SELECT * FROM sudski_postupak WHERE idsp=$sudski_postupak_id";
	$rezultat_sudski_postupak = pg_query($conn, $sql_sudski_postupak);
	$niz_sudski_postupak = pg_fetch_assoc($rezultat_sudski_postupak);
	$broj_sudskog_postupka = $niz_sudski_postupak['brsp'];
	//BOGDAN
	echo "<input type='hidden' id='broj_sudskog_postupka' value=$broj_sudskog_postupka >";
	//Branka 12.10.2015.
	$datum_kompletiranje_sudski = $niz_sudski_postupak['datum_kompletiranja'];

	//03.07.2017. Nevena - dodato za nova polja rizika i uzroka
	$sql = "select faza_id, osiguranik_krivac_tekuci_racun,sifra_niz,vas_broj, rizik_id, uzrok_id, datumkonac from predmet_odstetnog_zahteva where id=$idstete";
	$rezultat = pg_query($conn, $sql);
	$niz = pg_fetch_assoc($rezultat);
	$faza_id = $niz['faza_id'];
	$sifra_niz = $niz['sifra_niz'];
	$sifra_niz = substr($sifra_niz, 1, -1);
	$sifra_niz = (explode(",", $sifra_niz));
	$osiguranik_krivac_tekuci_racun_baza = $niz['osiguranik_krivac_tekuci_racun'];
	$osiguranik_krivac_tekuci_racun_ispis = ($osiguranik_krivac_tekuci_racun_baza) ? $osiguranik_krivac_tekuci_racun_baza : $osiguranik_krivac_tekuci_racun;
	//22.02.2017. Branka dodato novo polje
	$vas_broj_baza = $niz['vas_broj'];
	//03.07.2017. Nevena - dodato za nova polja rizika i uzroka 
	$rizik_baza = $niz['rizik_id'];
	$uzrok_baza = $niz['uzrok_id'];
	$datumkonac_baza = $niz['datumkonac'];

	// 2016-03-22
	//$prijaviti_u_reosiguranje = $niz['prijaviti_u_reosiguranje'];

	$osnovni_predmet_id_reaktiviranog = ($sudski_postupak_id || $osnovni_predmet_id_reaktiviranog) ? $osnovni_predmet_id_reaktiviranog : $idstete;
	$sql_brojevi_reaktivacija = "SELECT id AS predmet_id,novi_broj_predmeta AS osnovni FROM predmet_odstetnog_zahteva
WHERE coalesce(osnovni_predmet_id, id)=$osnovni_predmet_id_reaktiviranog ORDER BY id ASC";
	$upit_brojevi_reaktivacija = pg_query($conn, $sql_brojevi_reaktivacija);
	$niz_brojevi_reaktivacija = pg_fetch_all($upit_brojevi_reaktivacija);


	$osnovi_reaktivirani_broj =  $niz_brojevi_reaktivacija[0]['osnovni'];
	$osnovi_reaktivirani_id = $niz_brojevi_reaktivacija[0]['predmet_id'];
	// dodato za izvlacenje podataka regresni broj i datum otvaranja regresa
	$podaci_otvoren_regres = vrati_podatke_otvorenog_regresa($conn, $idstete);
	$regresni_broj = $podaci_otvoren_regres['brreg'];
	$datum_otvaranja_regresa =  $podaci_otvoren_regres['datum_upisa'];
	$idregres = $podaci_otvoren_regres['idregres'];
	if (!$izmeni && !$prethodne && !$dokumentacija && !$zapisnik && !$dugme_kreiraj_dopis && !$lekarski_nalaz && !$obracun_visine_stete && !$obracun_visine_stete_n_dpz && !$obracun_visine_stete_0205_dpz && !$resenje_IO_0903 && !$dugme_pregledaj_dopise && !$dugme_resenje_odbijen && !$dugme_odluka && !$dugme_odluka_likvidacija && !$odbijenica_likvidacija && !$dugme_dopisi && !$galerija && !$vrati && !$vozilo_dugme && !$prepisi && !$prepisiKriv && !$calc && !$submitk1 && !$submitk2 && !$submitk3 && !$submitk4 && !$submitk5 && !$odustanik1 && !$odustanik2 && !$odustanik3 && !$odustanik4 && !$odustanik5 && !$pronadji_kat && !$snimi_vozilo && !$zatvori_vozilo && !$nalozi && !$reak) {



		echo "<input type=\"hidden\" name=\"sudski_postupak_id\" id=\"sudski_postupak_id\"   value=\"$sudski_postupak_id\">\n";


		$sql = "select prijaviti_u_reosiguranje from predmet_odstetnog_zahteva where id=$idstete";
		$rezultat = pg_query($conn, $sql);
		$niz = pg_fetch_assoc($rezultat);
		// 2016-03-22
		$prijaviti_u_reosiguranje = $niz['prijaviti_u_reosiguranje'];

		$sql = "select * from knjigas where idstete=$idstete";

		$rezultat = pg_query($conn, $sql);
		$niz = pg_fetch_assoc($rezultat);

		/*Branka - 2014-10-31 - DA/NE procena*/
		$imao_evropski_izvestaj = $niz['snimanje_stete_procenitelj_imao_evropski_izvestaj'];

		// Marko Markovic 2020-05-13 IO zapisnici 
		$zapisnici_io = $niz['snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen'];
		// Marko Markovic kraj 2020-05-13

		$imao_policijski_zapisnik = $niz['snimanje_stete_procenitelj_imao_policijski_zapisnik'];
		$izvrsio_uporedjivanje_vozila = $niz['snimanje_stete_procenitelj_je_izvrsio_poredjenje_vozila'];
		$slikao_drugo_vozilo_odvojeno = $niz['snimanje_stete_procenitelj_je_slikao_vozila_odvojeno'];
		$slikao_gde = $niz['snimanje_stete_mesto_gde_je_procenitelj_slikao_drugo_vozilo'];
		$slikao_kada = $niz['snimanje_stete_datum_kada_je_procenitelj_slikao_drugo_vozilo'];
		$slikao_vreme = $niz['snimanje_stete_vreme_kada_je_procenitelj_slikao_drugo_vozilo'];
		$stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen = $niz['snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen'];


		$brSt = $niz['brst'];
		$vrstaSt = $niz['vrstast'];
		$tipSt = $niz['tipst'];
		$sifra = $niz['sifra'];

		// Dodato zbog automatskog ispisivanja ¹ifre
		// if ($sifra == '' && $vrstaSt == 'DPZ') {
		// 	switch ($tipSt) {
		// 		case 'HI':
		// 			$sifra = '02.12.02';
		// 		break;
		// 		case 'TB':
		// 			$sifra = '02.12.01';
		// 			break;
		// 		case '0205':
		// 			//$sifra = '02.19.01';
		// 			break;
		// 		default:
		// 		break;
		// 	}
		// }
		$faza = $niz['faza'];
		$rbrSt = $niz['rbrst'];
		$datumEvid = $niz['datumkompl'];
		// $datumEvid= $niz['datumevid'];
		$datumKompl = $niz['datumkonac'];

		if ($sudski_postupak_id && $datum_kompletiranje_sudski && $datum_otvaranja_predmeta < '2015-10-01') {
			$datumKompl = $datum_kompletiranje_sudski;
		}
		if ($datumKompl) {
			$komda = 1;
		}

		$premija = $niz['premija'];

		$prezimeOst = $niz['prezimeost'];
		$imeNazivOst = $niz['imenazivost'];
		$jmbgPibOst = $niz['jmbgpibost'];
		$jmbgPibOstBaza = ($niz['jmbgpibost'] != '' || $niz['jmbgpibost']) ? "readonly class='disabled'" : '';
		$jmbgPibOstBazaDisabled = ($jmbgPibOstBaza != '') ? 1 : 0;
		$telefon2 = $niz['telefon2'];
		$markaOst = $niz['markaost'];
		$tipOst = $niz['tipost'];
		$godOst = $niz['godost'];
		$regOznakaOst = $niz['regoznakaost'];
		$brsasOst = $niz['brsasost'];
		$nazivOsigOst = $niz['nazivosigost'];
		$brPoliseOst = $niz['brpoliseost'];
		$vaznostOdOst = $niz['vaznostodost'];
		$vaznostDoOst = $niz['vaznostdoost'];
		$predjenoKmOst = $niz['predjenokm_ost'];    //dodato

		$prezimeKriv = $niz['prezimekriv'];
		$imeNazivKriv = $niz['imenazivkriv'];
		$jmbgPibKriv = $niz['jmbgpibkriv'];
		$ovlLiceKriv = $niz['ovllicekriv'];
		$markaKriv = $niz['markakriv'];
		$tipKriv = $niz['tipkriv'];
		$godKriv = $niz['godkriv'];
		$regOznakaKriv = $niz['regoznakakriv'];
		$brsasKriv = $niz['brsaskriv'];



		$vrstaRegStet = $niz['vrstaregstet'];
		$oznakaRegStet = $niz['oznakaregstet'];
		$osiguranjeRegStet = $niz['osiguranjeregstet'];
		$drzavaRegStet = $niz['drzavaregstet'];

		$procenitelj1 = $niz['procenitelj1'];
		$procenitelj2 = $niz['procenitelj2'];
		$datumProc = $niz['datumproc'];

		$servis_upuceno_id = $niz['servis_upuceno_id'];
		$servis_fakturisano_id = $niz['servis_fakturisano_id'];
		// Dodato 13-11-2013
		$servis_fakturisano_datum = $niz['servis_fakturisano_datum'];

		$dana = $niz['dana'];


		$datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete = $niz['datum_kompletiranja_dokumentacije_utvrdjivanje_visine_stete'];
		//Branka 12.10.2015
		if ($sudski_postupak_id && $datum_kompletiranje_sudski && $datum_otvaranja_predmeta < '2015-10-01') {
			$datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete = $datum_kompletiranje_sudski;
		}
		$pocetak = $niz['pocetak'];
		$kraj = $niz['kraj'];
		$obradjivac1 = $niz['obradjivac1'];
		$obradjivac2 = $niz['obradjivac2'];
		$datumPonuda1 = $niz['datumponuda1'];
		$likvidatorPonuda1 = $niz['likvidatorponuda1'];
		$datumPrigovor = $niz['datumprigovor'];
		$komisija1 = $niz['komisija1'];
		$komisija2 = $niz['komisija2'];

		$datumPonuda2 = $niz['datumponuda2'];
		$likvidatorPonuda2 = $niz['likvidatorponuda2'];
		$zahtevano = $niz['zahtevano'];

		/* 
Dodeljivanje iznosa rezervacije Nemanja Jovanovic
*/
		/*
if(is_null($niz['rezervisano']) && $tipSt == '0301')
{
	$rezervisano = 80000.00;
}
else if (is_null($niz['rezervisano']) && $tipSt == '1001011')
{
	$rezervisano = 75000.00;
}
else if (is_null($niz['rezervisano']) && $tipSt == '1001012')
{
	$rezervisano = 85000.00;
}
else
{
	$rezervisano = $niz['rezervisano'];
}
*/
		/* 
Kraj
*/
		$isplaceno = $niz['isplaceno'];
		$nalog = $niz['nalog'];
		$isplata = $niz['isplata'];
		$odustao = $niz['odustao'];
		$sp = $niz['sp'];
		$arhivirano = $niz['arhivirano'];
		$napomena = $niz['napomena'];
		$slovo = $niz['slovo'];
		$nazivOsigKriv = $niz['nazivosigkriv'];
		$brPoliseKriv = $niz['brpolisekriv'];
		$vaznostOdKriv = $niz['vaznostodkriv'];
		$vaznostDoKriv = $niz['vaznostdokriv'];

		$dokNijeSt = $niz['doknijest'];
		$regPodOst = $niz['regpodost'];
		$regPodKriv = $niz['regpodkriv'];

		$modelOst = $niz['modelost'];
		$modelKriv = $niz['modelkriv'];
		$prihvacena = $niz['prihvacena'];
		$pocetak2 = $niz['pocetak2'];
		$kraj2 = $niz['kraj2'];
		$prihvacena2 = $niz['prihvacena2'];

		$gotovina = $niz['gotovina'];
		$virman = $niz['virman'];
		$doznaka = $niz['doznaka'];
		$kompenzacija = $niz['kompenzacija'];
		$fotoaparat = $niz['fotoaparat_id'];
		$teren = $niz['teren'];

		$tekRacun_ost = $niz['tekracun_ost'];
		$nacin_resavanja = $niz['nacin_resavanja'];
		$nacin_resavanja2 = $niz['nacin_resavanja2'];
		$naknadna_isplata = $niz['naknadna_isplata'];

		//Nemanja Jovanovic 18-12-2018
		$osteceni_mail = $niz['osteceni_mail'];
		$osiguranik_mail = $niz['osiguranik_mail'];


		//$datumPrijave= $niz['datumprijave'];
		$datumPrijave = $niz['datumevid'];
		if ($datumPrijave) {
			$prida = 1;
		}
		$struktura = $niz['struktura'];

		$adresaOst = $niz['adresaost'];
		$posbrOst = $niz['posbrost'];
		$posbrOvllice = $niz['posbrovllice'];

		//MARIJA 2.11.2014.
		//dodate nove promenljive koje se izvlaèe iz tabele knjigas ukoliko postoje
		$osteceni_mesto_id = $niz['osteceni_mesto_id'];
		$osiguranik_krivac_mesto_id = $niz['osiguranik_krivac_mesto_id'];

		$osteceni_mesto_opis = $niz['osteceni_mesto_opis'];
		$osiguranik_krivac_mesto_opis = $niz['osiguranik_krivac_mesto_opis'];

		$osiguranik_krivac_adresa = $niz['osiguranik_krivac_adresa'];

		$osteceni_zemlja_id = $niz['osteceni_zemlja_id'];
		$osiguranik_krivac_zemlja_id = $niz['osiguranik_krivac_zemlja_id'];
		$osiguranik_krivac_telefon1 = $niz['osiguranik_krivac_telefon1'];
		$osiguranik_krivac_telefon2 = $niz['osiguranik_krivac_telefon2'];
		$osteceni_broj_licne_karte = $niz['osteceni_broj_licne_karte'];
		$osiguranik_krivac_broj_licne_karte = $niz['osiguranik_krivac_broj_licne_karte'];
		//KRAJ DODATAK MARIJA

		// Nova polja... Bar neka od njih...
		$opisStete = $niz['opisstete'];

		// Marko Markovic 2020-05-28
		$napomenaSnimanje = $niz['napomenasnimanje'];

		$rbrReaktivirana = $niz['rbrreaktivirana'];
		$rbrSD = $niz['rbrsd'];
		$rbrSteta = $niz['rbrsteta'];
		$reaktivirana = $niz['reaktivirana'];
		//$reaktivirana = $brsp;


		$reaktiviranaBaza = ($reaktivirana != '' || $reaktivirana) ? "style='display:none;'" : '';

		// Dodato polje za RAZLOG REAKTIVACIJE
		$razlog_reaktivacije = $niz['razlog_reaktivacije'];

		// DODATO 11-09-2013 Lazar Milosavljeviæ
		// Zbog toga da li je ili nije AK ¹teta nastala u inostranstvu
		$steta_u_inostranstvu = $niz['steta_u_inostranstvu'];

		// 2016-03-22 dodato za evidentiranja reosiguranja 
		//$prijaviti_u_reosiguranje = $niz['prijaviti_u_reosiguranje'];

		$storno = $niz['storno'];
		$totalnaSteta = $niz['totalnasteta'];
		if ($odustao == t) {
			$odustao = true;
		} else {
			$odustao = false;
		}

		if ($dokNijeSt == t) {
			$dokNijeSt = true;
		} else {
			$dokNijeSt = false;
		}

		if ($datumPrigovor) {
			$prigovor = true;
		}

		// Izmena zbog prigovora
		// Lazar Milosavljevic - 08-02-2013
		if ($datumPrigovor != '') {
			$prikaz_prigovor_na_visinu_stete = 'block';
			$sql_vrati_prigovor = "SELECT * FROM prigovor WHERE idstete = $idstete AND vazi = 1";
			$rezultat_vrati_prigovor = pg_query($conn, $sql_vrati_prigovor);
			$niz_vrati_prigovor = pg_fetch_array($rezultat_vrati_prigovor);
			$prigovor_datum = $niz_vrati_prigovor['datum_prigovora'];
			$prigovor_osnovan = $niz_vrati_prigovor['osnovan'];
			$prigovor_procenat = $niz_vrati_prigovor['procenat'];
		} else {
			$prikaz_prigovor_na_visinu_stete = 'none';
		}

		if ($gotovina == t) {
			$gotovina = true;
		} else {
			$gotovina = false;
		}

		if ($virman == t) {
			$virman = true;
		} else {
			$virman = false;
		}

		if ($doznaka == t) {
			$doznaka = true;
		} else {
			$doznaka = false;
		}

		if ($kompenzacija == t) {
			$kompenzacija = true;
		} else {
			$kompenzacija = false;
		}

		if ($teren == t) {
			$teren = true;
		} else {
			$teren = false;
		}

		if ($vrstaSt == 'DPZ' && $tipSt == '0205') {
			// Pokupi sve podatke iz tabele 'knjigas_dpz_zp'
			$sql_knjigas_dpz_zp = "select * from knjigas_dpz_zp where idstete=$idstete";
			$rezultat_knjigas_dpz_zp = pg_query($conn, $sql_knjigas_dpz_zp);
			$niz_knjigas_dpz_zp = pg_fetch_assoc($rezultat_knjigas_dpz_zp);
			// Popuni promenljive vezane za vrstu stete DPZ - tip stete ZP
			$osteceni_broj_pasosa = $niz_knjigas_dpz_zp['osteceni_broj_pasosa'];
			$osteceni_pol = $niz_knjigas_dpz_zp['osteceni_pol'];
			$osteceni_email = $niz_knjigas_dpz_zp['osteceni_email'];
			$datum_ulaska_u_zemlju_destinacije = $niz_knjigas_dpz_zp['datum_ulaska_u_zemlju_destinacije'];
			$datum_izlaska_iz_zemlje_destinacije = $niz_knjigas_dpz_zp['datum_izlaska_iz_zemlje_destinacije'];
			$naziv_medicinske_ustanove = $niz_knjigas_dpz_zp['naziv_medicinske_ustanove'];
			$ime_lekara = $niz_knjigas_dpz_zp['ime_lekara'];
			$datum_prijema_medicinska_ustanova = $niz_knjigas_dpz_zp['datum_prijema_medicinska_ustanova'];
			$datum_otpustanja_medicinska_ustanova = $niz_knjigas_dpz_zp['datum_otpustanja_medicinska_ustanova'];
			$vrsta_povrede_ili_bolesti = $niz_knjigas_dpz_zp['vrsta_povrede_ili_bolesti'];
			$vrsta_lecenja = $niz_knjigas_dpz_zp['vrsta_lecenja'];
			$napomena_o_osiguranom_slucaju = $niz_knjigas_dpz_zp['napomena_o_osiguranom_slucaju'];

			if ($brPolise) {
				// Pokupi sve podatke iz tabele 'knjigas_dpz_zp'
				$sql_podaci_sa_polise = "SELECT * FROM putno WHERE brpolise=$brPolise ;";
				$rezultat_podaci_sa_polise = pg_query($conn2, $sql_podaci_sa_polise);
				$niz_podaci_sa_polise = pg_fetch_assoc($rezultat_podaci_sa_polise);

				$razred_opasnosti_dpz_zp = $niz_podaci_sa_polise['razred'];
				// Pokupi sipis iz ¹ifarnika za razred opasnosti
				$sql_razred_opasnosti = "SELECT * FROM sifarnici.razred_opasnosti WHERE razred_opasnosti='$razred_opasnosti_dpz_zp' ;";
				$rezultat_razred_opasnosti = pg_query($conn, $sql_razred_opasnosti);
				$niz_razred_opasnosti = pg_fetch_assoc($rezultat_razred_opasnosti);

				$razred_opasnosti_dpz_zp = $niz_razred_opasnosti['razred_opasnosti_kratko'];
				$razred_opasnosti_dpz_zp_ispis = $niz_razred_opasnosti['opis'];
			}
		}

		$sql = "select * from vozac where idstete=$idstete";
		$rezultat = pg_query($conn, $sql);
		$niz = pg_fetch_assoc($rezultat);

		$prezimeVoz = $niz['prezimevoz'];
		$imeVoz = $niz['imevoz'];
		$jmbgVoz = $niz['jmbgvoz'];
		$telefonv1 = $niz['telefonv1'];
		$telefonv2 = $niz['telefonv2'];
		/* dodato za vozaca krivca */
		$prezimeVozKriv = $niz['prezimevozkriv'];
		$imeVozKriv = $niz['imevozkriv'];
		$jmbgVozKriv = $niz['jmbgvozkriv'];
		//MARIJA 7.11.2014
		$vozac_mesto_id = $niz['vozac_mesto_id'];
		$vozac_krivac_mesto_id = $niz['vozac_krivac_mesto_id'];
		$vozac_mesto_opis = $niz['vozac_mesto_opis'];
		$vozac_krivac_mesto_opis = $niz['vozac_krivac_mesto_opis'];
		// dodato za vozaca osteceng i vozaca krivca adresa
		$vozac_adresa = $niz['vozac_adresa'];
		$vozac_krivac_adresa = $niz['vozac_krivac_adresa'];
		// dodato za vozaca osteceng i vozaca krivca zemlja
		$vozac_zemlja_id = $niz['vozac_zemlja_id'];
		$vozac_krivac_zemlja_id = $niz['vozac_krivac_zemlja_id'];
		//dodato za vozaca krivca telefoni
		$vozac_krivac_telefon1 = $niz['vozac_krivac_telefon1'];
		$vozac_krivac_telefon2 = $niz['vozac_krivac_telefon2'];
		//dodato za broj licne karte vozaca
		$vozac_broj_licne_karte = $niz['vozac_broj_licne_karte'];
		$vozac_krivac_broj_licne_karte = $niz['vozac_krivac_broj_licne_karte'];
		//ZAVRSENO

		$sql = "select * from pravni where idstete=$idstete";
		$rezultat = pg_query($conn, $sql);
		$niz = pg_fetch_assoc($rezultat);

		//DODAO VLADA - UZIMANJE VREDNOSTI IZ BAZE ZA UPIS U POLJA
		$tip_lica = $niz['tip_lica'];
		$ime_reg = $niz['ime_reg'];
		$prezime_reg = $niz['prezime_reg'];
		$jmbg_pib = $niz['jmbg_pib'];
		$opstina_reg_id = $niz['opstina_reg_id'];
		$mesto_reg_id = $niz['mesto_reg_id'];
		$adresa_reg = $niz['adresa_reg'];
		$telefon_reg = $niz['telefon_reg'];
		$koliko_potrazivati = $niz['koliko_potrazivati'] ? $niz['koliko_potrazivati'] : '';
		//DODAO VLADA - KRAJ

		$datumPravniOsnov = $niz['datumpravniosnov'];
		$osnovan = $niz['osnovan'];
		$delimicnoProc = $niz['delimicnoproc'];
		$vraceno = $niz['vraceno'];
		$vrstaRegPotr = $niz['vrstaregpotr'];
		$oznakaRegPotr = $niz['oznakaregpotr'];
		$osiguranjeRegPotr = $niz['osiguranjeregpotr'];
		$drzavaRegPotr = $niz['drzavaregpotr'];

		//DODAO VLADA
		$drzava_reg_id = $niz['drzava_reg_id'];

		$regPotr = $niz['regpotr'];
		$pravnaPredato = $niz['pravnapredato'];
		$pravniOsnovDao = $niz['pravni_osnov_dao'];
		$pravniOsnovDao_1 = $niz['pravni_osnov_dao_1'];
		$pravniOsnovDao_2 = $niz['pravni_osnov_dao_2'];
		$pravniOsnovNapomena = $niz['pravni_osnov_napomena'];
		$pravniOsnovObradjivac = $niz['pravni_osnov_obradjivac'];
		$datumPrijemaPredmetaPravnaSluzba = $niz['datum_prijema_predmeta_pravna_sluzba'];
		$pravniOsnovDatumKompletiranjaDokumentacije = $niz['pravni_osnov_datum_kompletiranja_dokumentacije'];
		//Marko Stankovicdodao 30.07.2018.
		$odustao_pravni = $niz['odustao'];

		//Branka 12.10.2015
		if ($sudski_postupak_id && $datum_kompletiranje_sudski && $datum_otvaranja_predmeta < '2015-10-01') {
			$pravniOsnovDatumKompletiranjaDokumentacije = $datum_kompletiranje_sudski;
		}
		// MARIJA 18.02.2015 - dodato za razlog umanjenja stete - POCETAK
		$razlog_umanjenja_stete_id = $niz['razlog_umanjenja_stete_id'];
		$osnov_pravnog_osnova  = $niz['osnov_pravnog_osnova'];
		$alkotest_osteceni = $niz['alkotest_osteceni'];
		$alkotest_krivac = $niz['alkotest_krivac'];
		// MARIJA 18.02.2015 - dodato za razlog umanjenja stete - KRAJ

		// MARIJA 27.02.2015 - polja za regres -  POCETAK
		$regres_od = $niz['regres_od'];
		$osiguravajuce_drustvo_id = $niz['osiguravajuce_drustvo_id'];
		$potvrdjen_osnov_za_regres = $niz['potvrdjen_osnov_za_regres'];
		$radnik_evidentirao_potvrdu_za_regres = $niz['radnik_evidentirao_potvrdu_za_regres'];
		$datum_evidentiranja_potvrde_za_regres = $niz['datum_evidentiranja_potvrde_za_regres'];
		$razlog_regresa_id = $niz['razlog_regresa_id'];
		$regresno_potrazivanje_napomena = $niz['regresno_potrazivanje_napomena'];
		// MARIJA 27.02.2015 - polja za regres -  KRAJ

		/*10.04.2018 Bogdan Golubovic
 * Doradio: Lazar Milosavljevic2018-10-16 
 * Inicijalizacija variabli $minDatumKompletiranjaIzmena i $maxDatumKompletiranjaIzmena
 *odredjuju granicne datume do kojih moze da se pomera datum konacnog resavanja
 * Takoðe, i promenljiva $mogucnostIzmeneDatumaKompletiranja koja u sluèaju da se radi izmena koja je van opsega datuma, tj. ako je MIN > MAX 
 */
		//Nevena datum kompletiranja 01.06.2021.
		//$zakljucaniDatum = zakljucani_datum($conn);
		//$minDatumKompletiranjaIzmena = minDateDatumKompletiranja($conn, $pravniOsnovDatumKompletiranjaDokumentacije, $datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete, $datum_otvaranja_predmeta, $zakljucaniDatum);
		$minDatumKompletiranjaIzmena = minDateDatumKompletiranja($conn, $pravniOsnovDatumKompletiranjaDokumentacije, $datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete, $datum_otvaranja_predmeta);
		$maxDatumKompletiranjaIzmena = maxDateDatumKompletiranja($conn, $idstete);
		

		$mogucnostIzmeneDatumaKompletiranja = ($minDatumKompletiranjaIzmena > $maxDatumKompletiranjaIzmena) ? FALSE : TRUE;
		// var_dump($mogucnostIzmeneDatumaKompletiranja,$minDatumKompletiranjaIzmena,$maxDatumKompletiranjaIzmena);exit;

		if ($regPotr == t) {
			$regPotr = true;
		} else {
			$regPotr = false;
		}

		if ($vrstaSt == 'AK' || $vrstaSt == 'AKs') {

			$sql = "select * from kf_osnov where idstete=$idstete";
			$rezultat = pg_query($conn, $sql);
			$niz = pg_fetch_assoc($rezultat);
			$datumKomOsnov = $niz['datum'];
			$kompenzovati = $niz['kompenzovati'];

			$kfPredato = $niz['kfpredato'];
			$vinkulirano = $niz['vinkulirano'];
		}

		$sql = "select * from vozilo where idstete=$idstete";
		$rezultat = pg_query($conn, $sql);
		$niz = pg_fetch_assoc($rezultat);

		$prvaUpotreba = $niz['prvaupotreba'];
		$vrstaVozila = $niz['vrstavozila'];
		$zemljaProizv = $niz['zemljaproizv'];
		$marka = $niz['marka'];
		$tip = $niz['tip'];
		$model = $niz['model'];
		$sifraVoz = $niz['sifravoz'];
		$cena = $niz['cena'];
		$procAmortizacije = $niz['procamortizacije'];
		$vrednost_vozilo = $niz['vrednost'];

		$brSasije = $niz['brsasije'];
		$brMotora = $niz['brmotora'];
		$snagakw = $niz['snagakw'];
		$ccm = $niz['ccm'];
		$masa = $niz['masa'];
		$vrGoriva = $niz['vrgoriva'];
		$boja = $niz['boja'];
		$karoserija = $niz['karoserija'];
		$brVrata = $niz['brvrata'];
		$brRegMesta = $niz['brregmesta'];

		$cb1 = $niz['cb1'];
		$cb2 = $niz['cb2'];
		$cb3 = $niz['cb3'];
		$cb4 = $niz['cb4'];
		$cb5 = $niz['cb5'];
		$cb6 = $niz['cb6'];
		$cb7 = $niz['cb7'];
		$cb8 = $niz['cb8'];
		$cb9 = $niz['cb9'];
		$cb10 = $niz['cb10'];
		$cb11 = $niz['cb11'];
		$cb12 = $niz['cb12'];

		$foto = $niz['foto'];
		$opisOst = $niz['opisost'];

		if ($cb1 == t) {
			$cb1 = true;
		} else {
			$cb1 = false;
		}
		if ($cb2 == t) {
			$cb2 = true;
		} else {
			$cb2 = false;
		}
		if ($cb3 == t) {
			$cb3 = true;
		} else {
			$cb3 = false;
		}
		if ($cb4 == t) {
			$cb4 = true;
		} else {
			$cb4 = false;
		}
		if ($cb5 == t) {
			$cb5 = true;
		} else {
			$cb5 = false;
		}
		if ($cb6 == t) {
			$cb6 = true;
		} else {
			$cb6 = false;
		}
		if ($cb7 == t) {
			$cb7 = true;
		} else {
			$cb7 = false;
		}
		if ($cb8 == t) {
			$cb8 = true;
		} else {
			$cb8 = false;
		}
		if ($cb9 == t) {
			$cb9 = true;
		} else {
			$cb9 = false;
		}
		if ($cb10 == t) {
			$cb10 = true;
		} else {
			$cb10 = false;
		}
		if ($cb11 == t) {
			$cb11 = true;
		} else {
			$cb11 = false;
		}
		if ($cb12 == t) {
			$cb12 = true;
		} else {
			$cb12 = false;
		}

		echo "<input type=\"hidden\" name=\"dugme\" value=\"$dugme\">\n";
	}

	// BRANKA - Dodat uslov za dugme kreiraj dopis
	//if ((!$prethodne || ($prethodne && !$brPolise)) && !$dokumentacija && !$galerija && !$dugme_resenje_odbijen && !$dugme_kreiraj_dopis && !$dugme_pregledaj_dopise && !$zapisnik && !$vozilo_dugme && !$submitk1 && !$submitk2 && !$submitk3 && !$submitk4 && !$submitk5 && !$odustanik1 && !$odustanik2 && !$odustanik3 && !$odustanik4 && !$odustanik5  && $davoz && !$pronadji_kat && !$nalozi && $danal && $dadok){
	if (!$prethodne && !$dokumentacija && !$galerija && !$dugme_resenje_odbijen && !$dugme_odluka && !$dugme_odluka_likvidacija && !$dugme_dopisi  && !$odbijenica_likvidacija && !$lekarski_nalaz && !$obracun_visine_stete && !$obracun_visine_stete_n_dpz && !$obracun_visine_stete_0205_dpz && !$resenje_IO_0903 && !$dugme_kreiraj_dopis && !$dugme_pregledaj_dopise && !$zapisnik && !$vozilo_dugme && !$submitk1 && !$submitk2 && !$submitk3 && !$submitk4 && !$submitk5 && !$odustanik1 && !$odustanik2 && !$odustanik3 && !$odustanik4 && !$odustanik5  && $davoz && !$pronadji_kat && !$nalozi && $danal && $dadok) {

		// DEO PRENET IZ ZARKO.PHP 29-05-2013
		// OVDE SE RADI PROVERA DA LI IMA NEKOMPLETIRANIH PREDMETA OD©TETNIH ZAHTEVA KOJI SU KREIRANI
		if ($vrstaSt == 'USL') {

			$sql = "select count(*) as koliko from knjigas  where vrstast='$vrstaSt' and brst<$brSt and  datumevid is null";
		} else {

			$sql = "select count(*) as koliko from knjigas  where vrstast='$vrstaSt' and brst<$brSt and  datumkompl is null";
		}

		$rezultat = pg_query($conn, $sql);
		$niz = pg_fetch_assoc($rezultat);
		$koliko = $niz['koliko'];

		// 	if ($koliko){

		// 		echo "<script type=\"text/javascript\">";
		// 		echo "nekompletirane = window.open('nekompletirane.php?vrstaSt=$vrstaSt&brSt=$brSt','nekompl', 'alwaysRaised=yes, dependent=yes, scrollbars=yes , resizable=yes , width=324, height=120, screenX=800')\n";
		// 		echo "</script>";

		// 	}

		//-----------------------------------------------------------------------------------------------------------------------------


		// DEO PRENET IZ ZARKO.PHP	 29-05-2013
		if ($datumEvid) {
			$god = substr($datumEvid, 2, 2);
			$godks = substr($datumEvid, 0, 4);
		} else {
			$god = substr(date("Y-m-d"), 2, 2);
			$godks = substr(date("Y-m-d"), 0, 4);
		}
		// OVDE TREBA DA SE POSTAVI DATUM IZ NUMERACIJE (koju dogovaramo)
		//}

		//ubacena drop down lista za  OPSTINE
		$rezultatOpstine = $sifarnici_class->vratiOpstine();
		$rezultatOkruzi = $sifarnici_class->vratiOkruge();
		//ubacena drop down lista za  ZEMLJE za DPZ ZP
		$sqlZemljeDPZ = "SELECT id,naziv,kontinent_ispis FROM sifarnici.zemlje_drzave ORDER BY naziv;";
		$rezultatZemljeDPZ = pg_query($conn, $sqlZemljeDPZ);
		$nizZemljeDPZ = pg_fetch_all($rezultatZemljeDPZ);
		// kontinenti
		$sqlKontinentiDPZ = "SELECT DISTINCT(kontinent_ispis) as kontinent FROM sifarnici.zemlje_drzave ORDER BY kontinent_ispis;";
		$rezultatKontinentiDPZ = pg_query($conn, $sqlKontinentiDPZ);
		$nizKontinentiDPZ = pg_fetch_all($rezultatKontinentiDPZ);
		//MARIJA DODATO -- Prebaciti u FUNKCIJE kad se stigne, ako se stigne
		// LAZAR Milosavljevic- Izbaèeno funkcija 2016-05-20
		// function vrati_zemlju_po_id($conn,$vrsta_lica_zemlja_id,$tabela,$idstete)
		// {
		// 	$sqlZemlja = "SELECT $vrsta_lica_zemlja_id FROM $tabela WHERE idstete = $idstete";
		// 	$rezultatZemlja = pg_query($conn,$sqlZemlja);
		// 	$nizZemlja = pg_fetch_array($rezultatZemlja);
		// 	return $nizZemlja;
		// }
		//KRAJ MARIJA DODATO

		// MARIJA 05.03.2015 - dodata funkcija da izvalci iz sifarnika naziv snimljenih osnova - POCETAK
		function vrati_sve_osnove_za_stetu($conn, $idstete)
		{
			$sql_osnov = "SELECT p.idstete,p.osnov_pravnog_osnova, opo.osnov_id, sopo.naziv FROM pravni p
			INNER JOIN osnov_za_pravni_osnov opo
			ON opo.idstete = p.idstete
			INNER JOIN sifarnici.osnov_za_pravni_osnov sopo
			ON opo.osnov_id = sopo.id
			WHERE p.idstete = $idstete";
			$rezultat_osnov = pg_query($conn, $sql_osnov);
			$niz_osnov = pg_fetch_all($rezultat_osnov);
			return $niz_osnov;
		}
		// MARIJA 05.03.2015 - dodata funkcija da izvalci iz sifarnika naziv snimljenih osnova - KRAJ

		$margina_forme = "position:absolute; top:0px; clear:both;";

		echo "<div id='gore'  style=' $margina_forme   width:99%;'><table width=\"100%\" height='200px;'   bgcolor=\"#CCCCCC\" cellspacing=\"0\"  class=\"\">\n";
		echo "<tr >\n";
		echo "<td width=\"250\" class=\"headerSivoGornja\" align=\"left\" colspan=\"3\">\n";
		echo "<p>\n";
		echo "<strong><font size=\"6\">\n";
		echo "PREDMET OD©TETNOG ZAHTEVA";
		echo "</strong></font>\n";
		echo "</p>\n";
		echo "</td>\n";

		//  Korisnik koji je otvorio stetu - POÈETAK - START
		echo "<td class=\"headerSivoGornja\" align=\"left\" colspan=\"2\"><strong>Predmet otvorio radnik: <u>";
		/* Za ko je otvorio predmet od¹tetnog zahteva*/
		$sql_radnik_otvorio__predmet = "SELECT radnik_evidentirao_predmet FROM predmet_odstetnog_zahteva WHERE id = $idstete";
		$rezultat_radnik_otvorio__predmet = pg_query($conn, $sql_radnik_otvorio__predmet);
		$predmet_otvorio_radnik = pg_fetch_result($rezultat_radnik_otvorio__predmet, 0, 'radnik_evidentirao_predmet');
		if ($predmet_otvorio_radnik) {
			$conn_zabrane = pg_connect("host=localhost dbname=zabrane user=zoranp");
			if (!$conn_zabrane) {
				echo "Gre¹ka otvaranja konekcije prema SQL serveru.";
				exit;
			}
			$sql_predmet_otvorio = "SELECT ime FROM unosivaci WHERE sifra = $predmet_otvorio_radnik";
			$rezultat_predmet_otvorio = pg_query($conn_zabrane, $sql_predmet_otvorio);
			$predmet_otvorio_ime = pg_fetch_result($rezultat_predmet_otvorio, 0, 'ime');
		} else {
			$predmet_otvorio_ime = "";
		}

		echo $predmet_otvorio_ime;
		echo "</u></strong><br>";
		//  Korisnik koji je otvorio stetu - KRAJ - END
		//Nemanja Jovanovic  
		//Stampa korica predmeta 2020-07-10
		$stampa_korica_prava_pristupa = array(138, 151, 3033, 3029);

		if (in_array($radnik, $stampa_korica_prava_pristupa)) {
			$sql_stampa = "	WITH 
				radnik AS
				(
					SELECT 
						radnik,
						ime
					FROM 
						dblink('host=localhost dbname=amso user=zoranp',
							'SELECT
								radnik,
								ime
							FROM 
								radnik'
						) AS t1 (radnik integer, ime character varying)
				)
				SELECT 
					ime, 
					dana,
					to_char(vreme,'HH:MM:SS') AS vreme
				FROM 
					korice_predmeta_stampa AS kps
						INNER JOIN radnik AS r ON (kps.radnik = r.radnik)
				WHERE  
					idstete = $idstete";

			$rez_stampa = pg_query($conn, $sql_stampa);
			$niz_stampa = pg_fetch_all($rez_stampa);
			$br_stampa	= count($niz_stampa);

			echo "<strong>©tampa korica: </strong>";
			echo "<select name='stampa_korica'>";

			if (!empty($niz_stampa)) {
				for ($i = 0; $i < $br_stampa; $i++) {
					$ime 	= $niz_stampa[$i]['ime'];
					$dan 	= $niz_stampa[$i]['dana'];
					$vreme 	= $niz_stampa[$i]['vreme'];

					echo "<option>$ime: $dan - $vreme</option>";
				}
			} else {
				echo "<option>©tampa predmeta nije izvr¹ena</option>";
			}

			echo "</select>";
		}
		echo "</td>\n";

		echo "<td colspan=\"2\" align=\"left\" class=\"headerSivoGornja\">\n";
		//echo "<center>\n";
		echo "<p>\n";
		echo "<strong><font size=\"5\">\n";
		echo "Godina&nbsp;" . $godks . ".\n";
		echo "</strong></font>\n";
		echo "</p>\n";
		//echo "</center>\n";
		echo "</td>\n";
		echo "<td class='headerSivoGornja'>";

		$sql_provera_online = "	SELECT 
							online_zahtev_id, 
							opoz.datum_vreme_evidentiranja_predmeta 
						FROM predmet_odstetnog_zahteva AS poz 
							INNER JOIN online.predmet_odstetnog_zahteva AS opoz ON (poz.online_zahtev_id = opoz.id) 
						WHERE 
							poz.id = $idstete";
		$rez_provera_online = pg_query($conn, $sql_provera_online);
		$niz_provera_online = pg_fetch_assoc($rez_provera_online);

		$datum_vreme_evidentiranja_predmeta_online = $niz_provera_online['datum_vreme_evidentiranja_predmeta'];
		if ($datum_vreme_evidentiranja_predmeta_online != '') {
			echo '<p><b>Online zahtev</b></p>';
		}

		echo "</td>";
		echo "</tr>";

		echo "<tr>\n";
		// echo "<td width=\"250\" height=\"50\" class=\"uvucenRedTd\" >\n";
		echo "<td class=\"uvucenRedTd\" colspan=\"2\">\n";
		/* UKLONITI - SKINUTI - 2014-12-30 - LAZAR Milosavljevic START  */
		/*
	echo "Vr.&nbsp;stete&nbsp;&nbsp;&nbsp;Br.&nbsp;stete\n";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tip&nbsp;stete\n";
	echo "<br>\n";
 	echo "<input name=\"vrstaSt\" value=\"$vrstaSt\" size=\"2\" height=\"20\" disabled=\"true\" class=\"disabledbig\"  onkeypress=\"return handleEnter(this, event)\">\n";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;\n";
	echo "<input name=\"brSt\" value=\"$brSt\" size=\"3\" height=\"20\"  disabled=\"true\"  class=\"disabledbig\" onkeypress=\"return handleEnter(this, event)\">\n";
	echo "<font size=\"5\"><b> / " . $god ."</b></font>";
	echo "<select name=\"tipSt\" disabled class='disabled' >\n";
	if ($vrstaSt=='AK' || $vrstaSt=='AKs')
	{
		echo "<option ";
		if ($tipSt == 'P') { echo "selected "; }
		echo "value=\"P\" title='Potpuni kasko'>P</option>";
		echo "<option ";
		if ($tipSt == 'D') { echo "selected "; }
		echo "value=\"D\" title='Delimièni kasko'>D</option>";
	}
	elseif ($vrstaSt=='USL')
	{
		echo "<option ";
		if ($tipSt == 'MS') { echo "selected "; }
		echo "value=\"MS\">MS</option>";
		echo "<option ";
		if ($tipSt == 'ML') { echo "selected "; }
		echo "value=\"ML\">ML</option>";
		echo "<option ";
		if ($tipSt == 'N') { echo "selected "; }
		echo "value=\"N\">N</option>";
		echo "<option ";
		if ($tipSt == 'P') { echo "selected "; }
		echo "value=\"P\">P</option>";
		echo "<option ";
		if ($tipSt == 'D') { echo "selected "; }
		echo "value=\"D\">D</option>";
		echo "<option ";
		if ($tipSt == 'RÃ¯Â¿Å“-MS') { echo "selected "; }
		echo "value=\"RÃ¯Â¿Å“-MS\">RÃ¯Â¿Å“-MS</option>";
		echo "<option ";
		if ($tipSt == 'RÃ¯Â¿Å“-ML') { echo "selected "; }
		echo "value=\"RÃ¯Â¿Å“-ML'\">RÃ¯Â¿Å“-ML</option>";
		echo "<option ";
		if ($tipSt == 'RÃ¯Â¿Å“-N') { echo "selected "; }
		echo "value=\"RÃ¯Â¿Å“-N\">RÃ¯Â¿Å“-N</option>";
	}
	// Dorada: Pri odabiru vrste stete DPZ i nekog od definisanih tipova (HI, TB, ZP) da se automatski popuni ¹ifra osiguranja
	// Lazar Milosavljevic18-03-2013
	elseif ($vrstaSt == 'DPZ')
	{
		echo "<option ";
		if ($tipSt == '') {	echo "selected ";	}
		echo "value=\"-1\" title=\"Odaberite tip stete\" onclick=\"document.pregled.sifra.value='';\">&nbsp;</option>";
		echo "<option ";
		if ($tipSt == 'HI') {echo "selected ";	}
		echo "value=\"HI\" title=\"HirurÃ¯Â¿Å“ke intervencije\" onclick=\"document.pregled.sifra.value='02.12.02';\">HI</option>";
		echo "<option ";
		if ($tipSt == 'TB') {echo "selected ";	}
		echo "value=\"TB\" title=\"TeÃ¯Â¿Å“e bolesti\" onclick=\"document.pregled.sifra.value='02.12.01';\">TB</option>";
		echo "<option ";
		if ($tipSt == '0205') {	echo "selected ";	}
		echo "value=\"ZP\" title=\"Putno zdravstveno\" onclick=\"document.pregled.sifra.value='02.19.01';\">ZP</option>";
	}
	else
	{
		echo "<option ";
		if ($tipSt == 'MS') { echo "selected "; }
		echo "value=\"MS\">MS</option>";
		echo "<option ";
		if ($tipSt == 'ML') { echo "selected "; }
		echo "value=\"ML\">ML</option>";
		echo "<option ";
		if ($tipSt == 'N') { echo "selected "; }
		echo "value=\"N\">N</option>";
		echo "<option ";
		if ($tipSt == 'RÃ¯Â¿Å“-MS') { echo "selected "; }
		echo "value=\"RÃ¯Â¿Å“-MS\">RÃ¯Â¿Å“-MS</option>";
		echo "<option ";
		if ($tipSt == 'RÃ¯Â¿Å“-ML') { echo "selected "; }
		echo "value=\"RÃ¯Â¿Å“-ML\">RÃ¯Â¿Å“-ML</option>";
		echo "<option ";
		if ($tipSt == 'RÃ¯Â¿Å“-N') { echo "selected "; }
		echo "value=\"RÃ¯Â¿Å“-N\">RÃ¯Â¿Å“-N</option>";
	}
	echo "</select>";
*/
		/* UKLONITI - SKINUTI - 2014-12-30 - LAZAR Milosavljevic END  */
		//echo "<input name='tipSt' value='$tipSt' type='hidden'>\n";
		echo "<input name='vrstaSt' value='$vrstaSt' type='hidden'>\n";
		echo "<label style='width: 270px; display: inline-block;'>BROJ PREDMETA OD¹TETNOG ZAHTEVA: </label>";
		echo "<input name='novi_broj_predmeta' value='$novi_broj_predmeta' size='14' height='20' disabled='true' class='disabledbig' >\n";
		//  Nemanja Jovanovic pin
		echo "<label style=' display: inline-block;'>PIN ZA WEB:&nbsp;</label>";
		echo "<input name='pin_za_web' value='$pin_kod_za_web' disabled='true' class='disabledbig'  style='width: 50px;'>\n";

		//echo "<br>\n";
		echo "</td>";
		echo "<td class=\"uvucenRedTd\" colspan=\"1\">\n";
		echo "<label  style='width: 170px; display: inline-block;'>TIP PREDMETA: </label>";

		$sql_nbs = "SELECT opis FROM sifarnici.vrste_osiguranja_nbs WHERE vazi_do is null and (tarifa='$tipSt' OR tarifna_grupa='$tipSt'  OR tarifna_oznaka_lica_imovina='$tipSt')";
		$upit_sql_nbs  = pg_query($conn2, $sql_nbs);
		$niz_sql_nbs = pg_fetch_assoc($upit_sql_nbs);
		$opis_tarife = $niz_sql_nbs['opis'];



		echo "<input  title='$opis_tarife' id='tip_predmeta' name='tip_predmeta' value='$tipSt' size='10' height='20' disabled='true' class='disabledbig' >\n";
		//$tipSt=1;

		//deo za rentnu
		if ($renta_lica == 1) {
			$renta_lica_prikaz = 'R';
			echo "<input  id='renta_lica' name='renta_lica' value='$renta_lica_prikaz' size='2' height='20' disabled='true' class='disabledbig' >\n";
			$osnovi_rente = array(1 => 'Trajna delimièna nesposobnost za rad', 2 => 'Trajna potpuna nesposobnost za rad', 3 => 'Izgubljeno izdr¾avanje', 4 => 'Tuða nega i pomoæ');
			echo "<br><label>Osnov rente:</label><select style='width:120px' id='osnov_rente' name='osnov_rente'>";
			echo "<option value='-1'>Izaberite</option>";
			foreach ($osnovi_rente as $osnov_r) {
				if ($osnov_rente == $osnov_r && $osnov_rente != $osnov_rente_baza) {
					echo "<option value='$osnov_r' selected=\"selected\">" . $osnov_r . "</option>\n";
				} else {
					$selected = ($osnov_rente_baza == $osnov_r) ? "selected='selected'" : "";
					echo "<option value='" . $osnov_r . "' $selected >$osnov_r</option>";
				}
			}
			echo "</select>&nbsp;&nbsp;&nbsp;";

			$tipovi_rente = array(1 => 'Privremena', 2 => 'Odlo¾ena', 3 => 'Do¾ivotna');
			echo "<br><label>Tip rente:</label><select id='tip_rente' name='tip_rente'>";

			echo "<option value='-1'>Izaberite</option>";
			foreach ($tipovi_rente as $tip_r) {

				if ($tip_rente == $tip_r && $tip_rente != $tip_rente_baza) {
					echo "<option value='$tip_r' selected=\"selected\">" . $tip_r . "</option>\n";
				} else {
					$selected = ($tip_rente_baza == $tip_r) ? "selected='selected'" : "";
					echo "<option value='" . $tip_r . "' $selected >$tip_r</option>";
				}
			}
			echo "</select>&nbsp;&nbsp;&nbsp;";
		}

		echo "<br>\n";
		echo "</td>";
		$border_sudski;
		$border_osnovni;
		$border_regres;

		if (!$faza) {
			$faza = 'FAZA 1 - PRIJAVA ©TETE';
			$faza_id = 1;
		}

		if ($broj_sudskog_postupka || ($osnovni_predmet_id_reaktiviranog && $osnovni_predmet_id_reaktiviranog != $idstete) || count($niz_brojevi_reaktivacija) > 1 || $regresni_broj) {
			$border_sudski = "style='border:2px solid black; border-bottom:grey;' margin-20px;";
			$border_osnovni = "style='border:2px solid black; border-bottom:grey;border-top:grey;'";
			$border_regres = "style='border:2px solid black;border-top:grey;'";
		} else {
			$border_sudski = "";
			$border_osnovni = "";
			$border_regres = "";
		}




		echo "<td colspan=\"2\" $border_sudski>&nbsp";
		if ($sudski_postupak_id) {
			echo "<label style='color:#8000FF;font-weight:bold'>BROJ SUDSKOG PREDMETA:&nbsp</label>"; //#F79F81
			echo '<a target="_blank" style="font-size:16px;" href="../evidencije/pravna/sudski_ispravka_novo.php?idsp=' . $sudski_postupak_id . '&dugme=DA">' . $broj_sudskog_postupka . '</a><br>';
		}
		echo "</td>";
		echo "<td rowspan='3'>";

		if ($vrstaSt == 'DPZ' && $tipSt == '0205') {
			echo "Pokrivena teritorija:<br/><textarea name='razred_opasnosti_dpz_zp_ispis' id='razred_opasnosti_dpz_zp_ispis' style='width:200px;height:50px;resize:none;' class='disabled' readonly='readonly' >$razred_opasnosti_dpz_zp_ispis</textarea>";
			echo "<br/>";
			echo "Razred opasnosti:&nbsp;\n<input name='razred_opasnosti_dpz_zp' id='razred_opasnosti_dpz_zp' style='width:40px;' class='disabled' readonly='readonly' value='$razred_opasnosti_dpz_zp' />";
		} else if ($vrstaSt == 'AK' && substr($tipSt, -1) == '1') {
			//  U textarea se unose podaci iz dodatnog fajla
			$broj_polise_za_dugovanje_na_dan_nastanka_stete = $broj_polise_sa_stetnog_dogadjaja;
			$datum_nastanka_za_dugovanje_na_dan_nastanka_stete = $datum_nastanka_sa_stetnog_dogadjaja;
			echo "Dugovanje na dan nastanka ¹tete:<br/>";
			echo "<textarea name='dugovanje_na_dan_nastanka_stete' id='dugovanje_na_dan_nastanka_stete' style='width:220px;height:100px;resize:none;text-align:right;overflow:hidden;' class='disabled' readonly='readonly' >";
			require_once 'dugovanje_na_dan_nastanka_stete.php';
			echo "</textarea>";
		}
		//  Novo za polise domacinstva (DOM) - iskljucivo tarifa 0903 i za brojeve between 900000 and 999999 (BC, 30.10.2020)
		else if ($vrstaSt == 'IO' && $tipSt == '0903' && (intval($broj_polise) >= 900000 && intval($broj_polise) <= 999999)) {
			//  U textarea se unose podaci iz dodatnog fajla
			$broj_polise_za_dugovanje_na_dan_nastanka_stete = $broj_polise_sa_stetnog_dogadjaja;
			$datum_nastanka_za_dugovanje_na_dan_nastanka_stete = $datum_nastanka_sa_stetnog_dogadjaja;
			echo "Dugovanje na dan nastanka ¹tete:<br/>";
			echo "<textarea name='dugovanje_na_dan_nastanka_stete' id='dugovanje_na_dan_nastanka_stete' style='width:220px;height:100px;resize:none;text-align:right;overflow:hidden;' class='disabled' readonly='readonly' >";
			require_once 'dugovanje_na_dan_nastanka_stete_domacinstvo.php';
			echo "</textarea>";
		}
		//  Novo za opste sistemske polise (NN, IO, DZO)
		else if ($vrstaSt == 'IO' || $vrstaSt == 'N' || ($vrstaSt == 'DPZ' && $tipSt != '0205')) {
			//  U textarea se unose podaci iz dodatnog fajla
			$broj_polise_za_dugovanje_na_dan_nastanka_stete = $broj_polise_sa_stetnog_dogadjaja;
			$datum_nastanka_za_dugovanje_na_dan_nastanka_stete = $datum_nastanka_sa_stetnog_dogadjaja;
			echo "Dugovanje na dan nastanka ¹tete:<br/>";
			echo "<textarea name='dugovanje_na_dan_nastanka_stete' id='dugovanje_na_dan_nastanka_stete' style='width:220px;height:100px;resize:none;text-align:right;overflow:hidden;' class='disabled' readonly='readonly' >";
			require_once 'dugovanje_na_dan_nastanka_stete_sistemske_opste.php';
			echo "</textarea>";
		} else {
			echo "&nbsp;\n";
		}
		echo "</td>";
		$upit_saosiguranja = "SELECT * FROM ugovor_saosiguranje AS s
	INNER JOIN ugovor_saosiguranje_ucesnici as u on (s.id=u.ugovor_id)
	INNER JOIN   ugovor_saosiguranje_polise as p on (s.id=p.ugovor_id)
	WHERE p.broj_polise=$broj_polise and p.storno=0::bit and u.storno=0::bit and s.nase_polise=1::bit";


		$result_saosiguranje = pg_query($conn2, $upit_saosiguranja);
		$niz_saosiguranje = pg_fetch_all($result_saosiguranje);
		if ($niz_saosiguranje) {
			echo "<td rowspan='2'>";
			//Branka 29.03.2016. Dodato da se prikazuju saosiguravaÃ¯Â¿Å“i i uÃ¯Â¿Å“eÃ¯Â¿Å“Ã¯Â¿Å“a u sluÃ¯Â¿Å“aju da je uneta polisa evidentirana u ugovoru o saosiguranju


			$broj_ugovora = ($niz_saosiguranje) ? $niz_saosiguranje[0]["broj_ugovora"] : "";
			$id_osiguravajuce_drustvo_vodeci = ($niz_saosiguranje) ? $niz_saosiguranje[0]['vodeci_saosiguravac_id'] : "";
			$ucesce_vodeci = ($niz_saosiguranje) ? $niz_saosiguranje[0]['ucesce_vodeceg_saosiguravaca'] : "";

			$sql_osiguravajuca_drustva = "select * from sifarnici.osiguravajuca_drustva where id=$id_osiguravajuce_drustvo_vodeci";
			$upit_osiguravajuca_drustva  = pg_query($conn, $sql_osiguravajuca_drustva);
			$niz_osiguravajuca_drustva = pg_fetch_assoc($upit_osiguravajuca_drustva);

			$naziv_vodeceg = $niz_osiguravajuca_drustva["osiguravajuce_drustvo_naziv"];

			$broj_saosiguravaca = count($niz_saosiguranje);
			//$scroll=($broj_saosiguravaca>2)?"class='tablescroll'":"";
			$saosiguravaci = "<table width='100%'  cellspacing='1' style='height:50px!important' ><tr style='width:100%;background-color:#E8E8E8 ' ><th colspan='3' style='width:50%;text-align:left;border-bottom:solid black 1px;border-bottom:solid black 1px;font-weight:bold;font-size:8pt' >BROJ UGOVORA O SAOSIGURANJU: $broj_ugovora</th></tr>";
			$saosiguravaci .= "<tr style='width:100%;font-size:8pt'><td style='text-align:left;border-bottom:solid black 1px;' colspan='2' ><table width='100%' cellspacing='0'><tr><td width='70%' style='font-size:8pt'>$naziv_vodeceg</td><td width='30%' style='font-size:8pt'> $ucesce_vodeci %</td></tr></table></td></tr>";


			for ($i = 0; $i < count($niz_saosiguranje); $i++) {
				$id_osiguravajuce_drustvo_prateci = $niz_saosiguranje[$i]['prateci_saosiguravac_id'];
				$ucesce_prateci = $niz_saosiguranje[$i]['ucesce_prateceg_saosiguravaca_procenat'];

				$sql_osiguravajuca_drustva = "select * from sifarnici.osiguravajuca_drustva where id=$id_osiguravajuce_drustvo_prateci";
				$upit_osiguravajuca_drustva  = pg_query($conn, $sql_osiguravajuca_drustva);
				$niz_osiguravajuca_drustva = pg_fetch_assoc($upit_osiguravajuca_drustva);

				$naziv_prateceg = $niz_osiguravajuca_drustva["osiguravajuce_drustvo_naziv"];
				$saosiguravaci .= "<tr style='width:100%'><td colspan='2' style='text-align:left;width:100%;border-bottom:solid black 1px;font-size:8pt' colspan='2'><table width='100%' cellspacing='0'><tr><td width='70%' style='font-size:8pt'>$naziv_prateceg</td><td width='30%' style='font-size:8pt'> $ucesce_prateci %</td></tr></table></td></tr>";
			}
			$saosiguravaci .= "</table>";
			echo $saosiguravaci;
			echo "</td>";
		}
		echo "</tr>";
		echo "<tr>";
		echo "<td class=\"uvucenRedTd\" colspan=\"1\">\n";

		echo "<label style='width: 270px; display: inline-block;'>Stari / arhivski broj predmeta: </label>";
		echo "<input name='stari_broj_predmeta' value='$stari_broj_predmeta' size='14' height='20' disabled='true' class='disabledbig' >\n";
		//echo "<br>\n";

		echo "</td>";
		echo "<td class=\"uvucenRedTd\" colspan=\"2\"> ";
		//echo "<div style='height:24px;vertical-align:center'>\n";
		echo "<label id='label_datumPrijave' for='datumPrijave' style='width: 170px; display: inline-block;'>\n";
		echo "Datum podno¹enja zahteva:";
		echo "</label>\n";
		//echo "</div>\n";
		if ($prida) {
			echo "<input name=\"datumPrijave\" id=\"datumPrijave\" value=\"$datumPrijave\" size=\"15\" height=\"20\" onclick=\"showCal('datumPrijave')\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\">\n";
		} else {
			echo "<input name=\"datumPrijave\" id=\"datumPrijave\" value=\"$datumPrijave\" size=\"15\" height=\"20\" onclick=\"showCal('datumPrijave')\" onkeypress=\"return handleEnter(this, event)\">\n";
		}
		echo "<td colspan=\"2\" $border_osnovni>&nbsp";
		//provera da li je ve' unet prigovor
		$provera_prigovori = "
		SELECT pi.prigovor_status_id,pr.id as id,('PO-' || redni_broj || '/' || extract(YEAR FROM dana)) AS broj_prigovora FROM prigovori.prigovori_registar AS pr
	INNER JOIN prigovori.prigovori_istorija_statusa AS pi
	ON (pi.prigovor_id=pr.id)
	WHERE pr.poz_id=$idstete  order by pi.id desc limit 1   ";
		$result_prigovori = pg_query($conn2, $provera_prigovori);
		$podaci_prigovori = pg_fetch_assoc($result_prigovori);

		$id_prigovora = $podaci_prigovori['id'];
		$broj_prigovora = $podaci_prigovori['broj_prigovora'];
		$prigovor_status_id = $podaci_prigovori['prigovor_status_id'];

		$disable_osnov = '';
		if ($osnovni_predmet_id_reaktiviranog && $osnovni_predmet_id_reaktiviranog != $idstete && !$sudski_postupak_id) {
			echo "<label style='color:#8000FF;font-weight:bold'>OSNOVNI PREDMET:&nbsp;</label>";
			echo '<a target="_blank" style="font-size:16px;" href="pregled.php?idstete=' . $osnovi_reaktivirani_id . '&dugme=DA">' . $osnovi_reaktivirani_broj . '</a>';
			// NOVO - VRAÃ¯Â¿Å“EN RAZLOG REAKTIVACIJE ZA aktivirane/reaktivirane predmete (LAZAR Milosavljevic- 2016-02-19)
			echo "<br/>";
			$disable_osnov = (!$razlog_reaktivacije || $razlog_reaktivacije == '') ? '' : 'disabled="disabled"';

			echo "Osnov prigovora:";

			//if (!$razlog_reaktivacije) $razlog_reaktivacije='';
			echo "<select $disable_osnov name=\"razlog_reaktivacije\" onchange='popuni_hidden_osnov(this.value)'>\n";
			//echo "<optgroup label='Ne postoji osnov za prigovor'>";
			echo "<option ";
			if ($razlog_reaktivacije == '') {
				echo "selected ";
			}
			echo "value=\"\">--Nije unet razlog--</option>";
			echo "<optgroup label='Ne postoji osnov za prigovor'>";
			echo "<option ";
			if ($razlog_reaktivacije == -1) {
				echo "selected ";
			}
			echo "value='-1'>Ne postoji osnov za prigovor</option>";
			echo "</optgroup>";
			echo "<optgroup label='Postoji osnov za prigovor'>";
			// 		echo "<option ";
			// 		if ($razlog_reaktivacije == '') {
			// 			echo "selected ";
			// 		}
			// 		echo "value=\"\">--Nije unet razlog--</option>";
			echo "<option ";
			if ($razlog_reaktivacije == '821 - Re¹avanje zahteva') {
				echo "selected ";
			}
			echo "value=\"821 - Re¹avanje zahteva\">821 - Re¹avanje zahteva</option>";
			echo "<option ";
			if ($razlog_reaktivacije == '822 - Uslovi osiguranja') {
				echo "selected ";
			}
			echo "value=\"822 - Uslovi osiguranja\">822 - Uslovi osiguranja</option>";
			echo "<option ";
			if ($razlog_reaktivacije == '823 - Izvr¹enje obaveza iz ugovora') {
				echo "selected ";
			}
			echo "value=\"823 - Izvr¹enje obaveza iz ugovora\">823 - Izvr¹enje obaveza iz ugovora</option>";

			echo "<option ";
			if ($razlog_reaktivacije == '824 - Visina i isplata ponuðene naknade') {
				echo "selected ";
			}
			echo "value=\"824 - Visina i isplata ponuðene naknade\">824 - Visina i isplata ponuðene naknade</option>";
			echo "</optgroup>";
			echo "</select>";
			echo "<input hidden value='$razlog_reaktivacije' name=\"razlog_reaktivacije\" id='osnov_prigovora'>";
			if ($podaci_prigovori && $prigovor_status_id != 4) {

				echo "<a href='../prigovori/prigovori_forma.php?id=$id_prigovora' target='_blank'>$broj_prigovora</a>";
				if ($radnik == 151 || $radnik == 2059 || $radnik == 138 || $radnik == 3068 || $radnik == 3044 || $radnik == 3042 || $radnik == 3067 || $radnik == 124 || $radnik == 3045 || $radnik == 3054 || $radnik == 3061 || $radnik == 3004 || $radnik == 3039 || $radnik == 3036 || $radnik == 3023 || $radnik == 3053 || $radnik == 3046 || $radnik == 122 || $radnik == 3016 || $radnik == 10 || $radnik == 3080 || $radnik == 3064 || $radnik == 3120 || $radnik == 3102 || $radnik == 3126 || $radnik == 2249) {
										
					//IZMENA STILA BUTTONA - DODAO VLADIMIR JOVANOVIC
					echo "<input type='submit' id='dugme_odluka' name='dugme_odluka' value='Kreiraj odluku' style='width: 35%; margin-left: 10%; margin-top: 10px;
					'>";
				}
				/*
		 * Dodao Marko Stankovic07.08.2017. FilipoviÃ¯Â¿Å“ Dragana radnik 3083
		 * Dodata 15.04.2019. Sanja RepanoviÃ¯Â¿Å“ radnik 3101
		 */
				if ($radnik == 151 || $radnik == 2059 || $radnik == 138 || $radnik == 3068 || $radnik == 3044 || $radnik == 3042 || $radnik == 3067 || $radnik == 124 || $radnik == 3045 || $radnik == 3054 || $radnik == 3061 || $radnik == 3004 || $radnik == 3039 || $radnik == 3036 || $radnik == 3023 || $radnik == 3053 || $radnik == 3046 || $radnik == 122 || $radnik == 3016 || $radnik == 3043 || $radnik == 3055 || $radnik == 3056 || $radnik == 3042 || $radnik == 3024 || $radnik == 3052 || $radnik == 3069 || $radnik == 3070 || $radnik == 3075 || $radnik == 3064 || $radnik == 3080 || $radnik == 3083 || $radnik == 3101 || $radnik == 3120 || $radnik == 3102 || $radnik == 3126 || $radnik == 2249) {
					$provera_potvrda_prigovori = "SELECT id from potvrda_prigovori WHERE id_stete=$idstete";
					$result_potvrda_prigovori = pg_query($conn, $provera_potvrda_prigovori);
					$podaci_potvrda_prigovori = pg_fetch_assoc($result_potvrda_prigovori);
					$potvrda_id = $podaci_potvrda_prigovori['id'];


					//IZMENA STILA BUTTONA - DODAO VLADIMIR JOVANOVIC
					echo "<input type='button' id='dugme_potvrda' name='dugme_potvrda' value='Kreiraj potvrdu' onclick='kreiraj_stampaj_potvrdu($potvrda_id)' style='width: 35%;'>";
					echo "<br>";
					
					//BUTTONI ZA SLANJE I PRIKAZ MEJLOVA SA ODLUKOM - DODAO VLADIMIR JOVANOVIC
					echo "<input type='button' id='dugme_potvrda_mejl' name='dugme_potvrda_mejl' value='Po¹alji potvrdu' onclick='posalji_mail_potvrda()' style='width: 35%; margin-left: 10%'>";
					echo "<input type='button' id='prikaz_mejlova' name='prikaz_mejlova' value='Prikaz mejlova potvrda' onclick='pregled_emailova(". '"potvrda_prigovori"' .")' style='width: 35%;'><br>";
				}
			}
		}

		if ($osnovni_predmet_id_reaktiviranog == $idstete && count($niz_brojevi_reaktivacija) > 1) {
			echo "<label style='color:#8000FF;font-weight:bold'>LISTA REAKTIVIRANIH PREDMETA:&nbsp;</label>";
			echo "<select value='lista_reaktiviranih' name='lista_reaktiviranih' id='lista_reaktiviranih' onchange='otvori_predmet_odstetnog_zahteva(this.value);'>"; //$niz_brojevi_reaktivacija
			echo "<option value='-1'>Izaberite</option>";
			for ($i = 1; $i < count($niz_brojevi_reaktivacija); $i++) {
				$predmet_id_reaktiviran = $niz_brojevi_reaktivacija[$i]['predmet_id'];
				$broj_reaktiviran = $niz_brojevi_reaktivacija[$i]['osnovni'];
				echo "<option value='$predmet_id_reaktiviran'>$broj_reaktiviran</option>";
			}
			echo "</select>";
		}

		echo "</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td class=\"uvucenRedTd\" id='faza_id'>";
		$inline = ($tip_predmeta == 'R') ? 'inline' : 'inline-block';
		echo "<label style='width: 270px; display: inline-block;'>Faza obrade</label>";
		echo "<input type='hidden' align='right' name=\"faza\" id=\"faza\" value=\"$faza\" size=\"20\" height=\"20\"
	onkeypress=\"return handleEnter(this, event)\">
	<select  onfocus='this.defaultIndex=this.selectedIndex;' onchange='this.selectedIndex=this.defaultIndex;' id='faza'>";

		// 	if($faza_id==NULL || !$faza_id)
		// 		$faza_id=1;
		$conn = pg_connect("host=localhost dbname=stete user=zoranp");
		$sql = "SELECT * FROM sifarnici.faze_obrade_predmeta_odstetnog_zahteva";
		$rezultat = pg_query($conn, $sql);
		$redova = pg_num_rows($rezultat);

		for ($i = 0; $i < $redova; $i++) {
			$selekt = "";
			$niz = pg_fetch_assoc($rezultat);
			if ($faza_id == $niz['id'])
				$selekt = " selected ";



			echo "<option " . $selekt . " id=" . $niz['id'] . ">FAZA " . $niz['id'] . " - " . $niz['naziv'] . "</option>";
		}

		if (!$faza_id)
			$faza_id = 1;
		echo "</select>
	\n";
		echo "</td>\n";


		echo "<td  colspan=\"2\" class=\"uvucenRedTd\">";

		echo "<label id='label_datumPrijave' for='datumPrijave' style='width: 170px; display: inline-block;'>¹IFRA</label>";
		//echo "<br/>\n";
		if (($vrstaSt == 'AO' || $vrstaSt == 'AK' || $vrstaSt == 'DPZ') && (isset($sifra) || $sifra == '')) {
			$sifra_polje_readonly = " readonly='readonly' class='disabled' ";
			//$sifra_polje_readonly = "";
		}
		if ($sifra && $sifra != "" && $sifra != "-1") {
			echo "<input type='hidden' value='0' id='broj_kliknuto' name='broj_kliknuto' />";
			//echo "<input  name=\"sifra_prikaz\" id=\"sifra_prikaz\" value=\"$sifra\"  onkeypress=\"return handleEnter(this, event)\"  readonly='readonly' class='disabled' >\n";	

			if (strpos($sifra, '.') == false) {
				$sifra_niz = explode(",", $sifra);
				foreach ($sifra_niz as $sifra_niz_clan) {
					$sifra_niz_clan = wordwrap($sifra_niz_clan, 2, '.', true);
					$sifra_prikaz .= $sifra_niz_clan . ',';
				}
				$sifra_prikaz = rtrim($sifra_prikaz, ",");
			} else {
				$sifra_prikaz = $sifra;
			}
			echo "<input   name=\"sifra\" id=\"sifra\"  onkeypress=\"return handleEnter(this, event)\"  readonly='readonly' class='disabled' value='$sifra_prikaz'>\n";
			if ($sifra_niz && !in_array('-1', $sifra_niz)) {
				$funkcije_klasa = new funkcije_class();
				$sifra_osiguranja = $funkcije_klasa->vrati_sifru_osiguranja_za_predmet_novo($vrsta_obrasca, $vrsta_osiguranja, $tipSt, $vrsta_vozila_sa_polise, $numericka_vrsta_osiguranja);
				$broj_mogucih_sifara = count($sifra_osiguranja);
				if ($broj_mogucih_sifara > 1 && !$nalog && $numericka_vrsta_osiguranja != '10' && $numericka_vrsta_osiguranja != '03') {
					echo "<input type='checkbox' id='izmeni_sifru' onclick='prikazi_listu_sifara(this.value)'>";
				}

				echo "<div id='div_sifre' style='display:none' >";
				//echo "<select id='sifra_lista1' name='sifra_lista1' multiple='true' tabindex='1'>";
				echo "<select id='sifra_lista1' name='sifra_lista1' tabindex='1'>";


				$niz_polise = $funkcije_klasa->vrati_podatke_sa_polise($vrsta_obrasca, $broj_polise_sa_stetnog_dogadjaja);
				$vrsta_vozila_sa_polise = $niz_polise['vrsta'];


				//echo "vroj sifara".$broj_mogucih_sifara;
				for ($i = 0; $i < count($sifra_osiguranja); $i++) {

					$sifra_za_listu = substr($sifra_osiguranja[$i]['konto'], 3);
					$opis1 = $sifra_osiguranja[$i]['opis1'];
					$select = "";
					for ($j = 0; $j < count($sifra_niz); $j++) {
						if ($sifra_niz[$j] == $sifra_za_listu) {
							$select = "selected";
						}
					}
					$sifra_za_listu_prikaz = wordwrap($sifra_za_listu, 2, '.', true);
					echo "<option $select  value='$sifra_za_listu_prikaz' title='$opis1' >$sifra_za_listu_prikaz - $opis1</option>";
				}
				echo "</select>";
				echo "</div>";
			}
		} else {
			//echo "<select  name=\"sifra\"  onkeypress=\"return handleEnter(this, event)\"  >\n";	
			//echo "<select id='sifra_lista' name='sifra_lista' multiple='true' tabindex='1'>";
			echo "<select id='sifra_lista' name='sifra_lista' tabindex='1'>";
			$funkcije_klasa = new funkcije_class();

			$niz_polise = $funkcije_klasa->vrati_podatke_sa_polise($vrsta_obrasca, $broj_polise_sa_stetnog_dogadjaja);
			$vrsta_vozila_sa_polise = $niz_polise['vrsta'];
			$sifra_osiguranja = $funkcije_klasa->vrati_sifru_osiguranja_za_predmet_novo($vrsta_obrasca, $vrsta_osiguranja, $tipSt, $vrsta_vozila_sa_polise, $numericka_vrsta_osiguranja);

			$broj_mogucih_sifara = count($sifra_osiguranja);
			//echo "<option value='-1'>Izaberite</option>";
			//echo "vroj sifara".$broj_mogucih_sifara;
			echo "<option  value='-1' title='--Izaberite ¹ifru--'><i>----Izaberite ¹ifru----</i></option>";
			for ($i = 0; $i < count($sifra_osiguranja); $i++) {
				$sifra_za_listu = substr($sifra_osiguranja[$i]['konto'], 3);
				$opis1 = $sifra_osiguranja[$i]['opis1'];

				$sifra_za_listu_prikaz = wordwrap($sifra_za_listu, 2, '.', true);
				echo "<option  value='$sifra_za_listu_prikaz' title='$opis1'>$sifra_za_listu_prikaz - $opis1</option>";
			}
			echo "</select>";
			echo "<input hidden  name=\"sifra\" id=\"sifra\" >\n";
		}
		echo "</td>";

		echo "<td colspan=\"2\" $border_regres> &nbsp;\n";
		if ($regresni_broj) {
			echo "<label style='color:#8000FF;font-weight:bold'>REGRESNI BROJ:&nbsp;</label>";
			echo '<a target="_blank" style="font-size:16px;" href="../evidencije/pravna/regresna.php?idregres=' . $idregres . '&status=izmeni">' . $regresni_broj . '</a>';
		}
		echo "</td>";
		//echo "<td colspan=\"2\">\n";
		/*
	echo "Reaktivirani iz ovog odÃ¯Â¿Å“tetnog zahteva:";
	echo "<br/>";
	// Upit kojim se vraÃ¯Â¿Å“aju svi odÃ¯Â¿Å“tetni zahtevi
	// koji su nastali reaktivacijom osnovnog (na kom smo trenutno)
	$sql_reaktivirani_iz_osnovnog = "SELECT
																			idstete, vrstast||'-'||brst||'/'||substring(extract(year from datumevid)::text,3,2) as broj_stete
																		FROM
																			knjigas
																		WHERE
																			reaktivirana = (SELECT vrstast||'-'||brst||'/'||substring(extract(year from datumevid)::text,3,2)
																		FROM knjigas
																		WHERE idstete = $idstete);";
	$upit_reaktivirani_iz_osnovnog = pg_query($conn,$sql_reaktivirani_iz_osnovnog);
	$niz_reaktivirani_iz_osnovnog = pg_fetch_all($upit_reaktivirani_iz_osnovnog);
	echo "<select name='reaktivirani_iz_osnovnog' id='reaktivirani_iz_osnovnog' onchange='otvori_reaktivirani_odstetni_zahtev(this.value);' style='width:140px' >";
	if($niz_reaktivirani_iz_osnovnog)
	{
		echo "<option value=''>";
		echo "--Izaberi reaktivirani--";
		echo "</option>";
		for ($i = 0; $i < count($niz_reaktivirani_iz_osnovnog); $i++)
		{
			echo "<option value='".$niz_reaktivirani_iz_osnovnog[$i]['idstete']."'>";
			echo $niz_reaktivirani_iz_osnovnog[$i]['broj_stete'];
			echo "</option>";
		}
	}
	else
	{
		echo "<option value=''>";
		echo "-Nema reaktiviranih-";
		echo "</option>";
	}
	echo "</select>";
//	echo "<br/>";
 * 
 */
		/*
	echo "Rbr.reaktivirane:";
	echo "<input name=\"rbrReaktivirana\" value=\"$rbrReaktivirana\" size=\"13\" height=\"20\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\">\n";

	echo "<br/>";
	if ($reaktivirana)
	{
		echo "<br/>";
		echo "<br/>";
		echo "Razlog reaktivacije:";
			if (!$razlog_reaktivacije) $razlog_reaktivacije='';
			echo "<select name=\"razlog_reaktivacije\">\n";
			echo "<option ";
			if ($razlog_reaktivacije == '') {
				echo "selected ";
			}
			echo "value=\"\">--Nije unet razlog--</option>";
			echo "<option ";
			if ($razlog_reaktivacije == 'Osnov') {
				echo "selected ";
			}
			echo "value=\"Osnov\">Osnov</option>";
			echo "<option ";
			if ($razlog_reaktivacije == 'Iznos') {
				echo "selected ";
			}
			echo "value=\"Iznos\">Visina stete</option>";
			echo "<option ";
			if ($razlog_reaktivacije == 'Osnov i iznos') {
				echo "selected ";
			}
			echo "value=\"Osnov i iznos\">I osnov i visina stete</option>";
			echo "</select>";
	}
	*/

		//echo "</td>\n";

		echo "<td width=\"120\" rowspan='3'>\n";
		// 	if ($vrstaSt == 'DPZ' && $tipSt == '0205')
		// 	{
		// 		echo "Pokrivena teritorija:<br/><textarea name='razred_opasnosti_dpz_zp_ispis' id='razred_opasnosti_dpz_zp_ispis' style='width:200px;height:50px;resize:none;' class='disabled' readonly='readonly' >$razred_opasnosti_dpz_zp_ispis</textarea>";
		// 		echo "<br/>";
		// 		echo "Razred opasnosti:&nbsp;\n<input name='razred_opasnosti_dpz_zp' id='razred_opasnosti_dpz_zp' style='width:40px;' class='disabled' readonly='readonly' value='$razred_opasnosti_dpz_zp' />";
		// 	}
		// 	else if ($vrstaSt == 'AK' && $tipSt == 'P')
		// 	{
		// 	//  U textarea se unose podaci iz dodatnog fajla
		// 		$broj_polise_za_dugovanje_na_dan_nastanka_stete = $broj_polise_sa_stetnog_dogadjaja;
		// 		$datum_nastanka_za_dugovanje_na_dan_nastanka_stete = $datum_nastanka_sa_stetnog_dogadjaja;
		// 		echo "Dugovanje na dan nastanka stete:<br/>";
		// 		echo "<textarea name='dugovanje_na_dan_nastanka_stete' id='dugovanje_na_dan_nastanka_stete' style='width:220px;height:100px;resize:none;text-align:right;overflow:hidden;' class='disabled' readonly='readonly' >";
		// 		require_once 'dugovanje_na_dan_nastanka_stete.php';
		// 		echo "</textarea>";
		// 	}
		// 	else
		// 	{
		// 		echo "&nbsp;\n";
		// 	}
		echo "<td align=\"right\" style=\"line-height: 1.5;\">\n";
		/*
	echo "<br>\n";
	echo "Rbr.po Ã¯Â¿Å“D:\n";
	echo "<br>\n";
	echo "<input name=\"rbrSD\" value=\"$rbrSD\" size=\"13\" height=\"20\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\">\n";
	echo "<br>\n";
	echo "¹teta:";
	echo "<br>\n";
	echo "<input name=\"rbrSteta\" value=\"$rbrSteta\" size=\"13\" height=\"20\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\">\n";
	echo "<br>\n";
	*/
		echo "</td>\n";

		echo "<td align=\"right\">\n";
		echo "</td>\n";
		// echo "<td>\n";
		// echo "¹ifra\n";
		// echo "<br/>\n";
		// if (($vrstaSt == 'AO' || $vrstaSt == 'AK' || $vrstaSt == 'DPZ') && (isset($sifra) || $sifra==''))
		// {
		// 	$sifra_polje_readonly = " readonly='readonly' class='disabled' ";
		// 	//$sifra_polje_readonly = "";
		// }
		// echo "<input name=\"sifra\" value=\"$sifra\" size=\"15\" height=\"20\" onkeypress=\"return handleEnter(this, event)\" $sifra_polje_readonly >\n";
		//echo "<br/>\n";
		/*
echo "R.br.stete:\n";
echo "<br/>\n";
echo "<input name=\"rbrSt\" value=\"$rbrSt\" size=\"15\" height=\"20\" onkeypress=\"return handleEnter(this, event)\">\n";
*/
		//echo "</td>\n";

		echo "</tr>\n";

		// drugi red - PRAZAN
		echo "<tr>\n";
		echo "<td  class=\"uvucenRedTd\">\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "</td>\n";
		echo "<td align=\"right\">\n";
		echo "</td>\n";
		echo "<td align=\"left\">\n";
		echo "</td>\n";
		echo "<td align=\"right\">\n";
		echo "</td>\n";
		echo "<td align=\"left\">\n";
		echo "</td>\n";
		echo "</tr>\n";

		// treci red - PRAZAN
		echo "<tr>\n";
		echo "<td align=\"left\"  class=\"uvucenRedTd\" style='vertical-align:top;'>\n";
		/* UKLONITI - SKINUTI - 2014-12-29 - LAZAR Milosavljevic START  */
		/*
		echo "<div style='height:24px;vertical-align:center'>\n";
			echo "<label id='label_datumEvid' for='datumEvid' >\n";
				echo "Datum prijave:\n";
			echo "</label>\n";
		echo "</div>\n";
	*/
		/* UKLONITI - SKINUTI - 2014-12-29 - LAZAR Milosavljevic END  */
		echo "</td>\n";
		echo "<td align=\"left\" style='vertical-align:top;'>\n";
		/* UKLONITI - SKINUTI - 2014-12-29 - LAZAR Milosavljevic START  */
		/*
		echo "<input name=\"datumEvid\" value=\"$datumEvid\" size=\"15\" height=\"20\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "<br/>\n";
		echo "<br/>\n";
	*/
		/* UKLONITI - SKINUTI - 2014-12-29 - LAZAR Milosavljevic END  */
		echo "</td>\n";
		echo "<td>\n";
		echo "</td>\n";
		echo "<td align=\"left\" style=\"line-height: 2;padding-right:10px;\">\n";
		echo "</td>\n";
		echo "<td align=\"left\">\n";
		echo "</td>\n";
		echo "<td align=\"left\">\n";
		echo "</td>\n";
		echo "</tr>\n";

		// cetvrti red
		echo "<tr>\n";
		echo "<td align=\"left\" style=\"line-height: 1.5;\"  class=\"uvucenRedTd\">\n";
		echo "</td>\n";
		echo "<td align=\"left\" colspan=\"2\">\n";
		echo "</td>\n";

		echo "<td width=\"65\" align=\"right\">\n";


		echo "</td>\n";
		echo "<td width=\"15%\" align=\"left\" colspan=\"1\">\n";



		echo "</td>\n";

		echo "<td width=\"65\" align=\"right\">\n";



		echo "</td>\n";
		echo "<td width=\"15%\" align=\"left\" colspan=\"2\">\n";


		echo "</td>\n";
		echo "</tr>\n";

		//Nevena - dodavanje rizika i uzroka - 2017-07-03 - POÈETAK
		echo "<tr>\n";
		//RIZIK - dodavanje padajuæe liste za rizik na predmetu od¹tetnog zahteva
		echo "<td colspan=\"2\" class=\"uvucenRedTd\" >";
		echo "<label style='width: 150px; display: inline-block;'>Prijavljeni rizik</label>";
		if ($datumkonac_baza && ($rizik_baza == null || $uzrok_baza == null)) {
			$disable_rizik = 'disabled';
			$disable_uzrok = 'disabled';
		}
		echo "<input type='hidden' value='$uzrok_baza' name='uzrok_baza' id='uzrok_baza'>";
		echo "<input type='hidden' value='$datumkonac_baza' name='datumkonac_baza' id='datumkonac_baza'>";
		echo "<select  name='rizik' id='rizik' style='width: 300px;position:relative;' onchange='vratiUzrokeZaRizike(this.value);' $disable_rizik >";
		$sifra_rizik_uzrok = substr($tipSt, 0, 4);
		$sql = "SELECT id, osnovni_dopunski, opis
		FROM sifarnici.aktuari_rizici
		WHERE id IN (SELECT aktuari_rizici_id FROM sifarnici.aktuari_uzroci WHERE sifra_osiguranja||''||tarifa_osiguranja = '$sifra_rizik_uzrok') AND vazi = 't'::boolean ";

		if ($datumkonac_baza && $rizik_baza) {
			$dodatak = " AND id = $rizik_baza";
			$sql .= "$dodatak ORDER BY id";
		} else {
			echo "<option value='-1'>Izaberite rizik:</option>";
		}

		$rezultat = pg_query($conn2, $sql);
		$redova = pg_num_rows($rezultat);
		for ($i = 0; $i < $redova; $i++) {
			$niz = pg_fetch_assoc($rezultat);
			echo "<option value='" . $niz['id'] . "'";
			if ($rizik_baza == $niz['id']) {
				echo 'selected';
			}
			echo ">" . $niz['opis'] . "</option>";
		}
		echo "</select>\n";
		echo "</td>\n";
		// kraj RIZIKA

		//UZROK - uÃ¯Â¿Å“itava se na osnovu izabranog rizika
		echo "<td  colspan=\"2\" style='width:300px;' class=\"uvucenRedTd\" >";
		echo "<label style='width: 150px; display: inline-block;'>Uzrok ¹tete po riziku</label>";
		echo "<select name='uzrok' id='uzrok' style='width: 300px;position:relative;' $disable_uzrok>";
		if ($rizik_baza != null) {
			echo "<script>
		vratiUzrokeZaRizike($rizik_baza);
		</script>";
		}
		echo "</select>\n";
		echo "</td>\n";
		echo "</tr>\n";
		// kraj UZROKA
		//Nevena - dodavanje rizika i uzroka - 2017-07-03 - KRAJ

		echo "<tr>\n";
		echo "<td align=\"left\"  colspan=\"2\">\n";
		echo "<input name=\"dokNijeSt\" type=\"checkbox\" value=\"true\" ";
		if ($dokNijeSt == true) {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "Dokumentacija&nbsp;nije&nbsp;stigla\n";
		echo "</td>\n";

		echo "<td>\n";
		if ($dugme == 'DA') {
			echo "<input type=\"submit\" value=\"Dokumentacija\" class=\"button\" name=\"dokumentacija\">\n";
		}
		echo "</td>\n";

		echo "<td>\n";
		// Marko Markovic dugme za dodatnu napomenu modal faza prijem 2019-12-05
		// $unosivaci = array(151,138,3093,3055,3052,3053,3045,3033,3039,3093,3042,3067,3083,3078,3044,3038,3090,3023,3081,3079,122,3029,3054,3024,3046,3085,3032,3070,2253,3116,3101,3080,2119,2224,3069,3043,3016,3004,3102,3106);
		$conn_amso = pg_connect("host=localhost dbname=amso user=zoranp");
		$sql_unosivaci = "SELECT radnik FROM radnik WHERE faza_stete is not null";
		$rezultat      = pg_query($conn_amso, $sql_unosivaci);
		$niz_unosivaci = pg_fetch_all($rezultat);
		$brunosivaca   = pg_num_rows($rezultat);
		for ($i = 0; $i < $brunosivaca; $i++) {
			$unosivaci[] = $niz_unosivaci[$i]['radnik'];
		}
		if (in_array($radnik, $unosivaci)) {
			echo "<input type='button' onclick='otvori_dodatne_napomen(1);' id='napomena_prijem_faza' name='napomena_prijem_faza' style='height:30px; width:200px; font-size:13px; margin:0px;' text-align='center' value='Napomena za sajt dru¹tva' />";
		}
		// echo "&nbsp;\n";
		// Marko Markovic kraj
		echo "</td>\n";

		echo "<td>\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td>\n";
		if ($dugme == 'DA') {
			echo "<input type=\"submit\" value=\"Prethodne ¹tete\" class=\"button\" name=\"prethodne\">\n";
		}
		echo "</td>\n";

		echo "</tr>\n";

		echo "<tr><td class=\"footerSivo\" colspan=\"8\"></td></td>";
		echo "</table>\n</div><br>";
		echo "<hr color=\"#000000\">\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td>\n";

		// drugi red   DRUGA  TABELA
		// echo "<table width=\"100%\" border=\"0\" bgcolor=\"#CCCCCC\" cellspacing=\"0\">\n";
		// echo "<tr>\n";

		// echo "<td class=\"headerSivo\" colspan=\"6\">\n";

		// echo "</tr>";
		// echo "</table>";


		// pregled_stetnog_dogadjaja($id_stetnog_dogadjaja,'pregled');
		//pregled_prijave_stete($odstetni_zahtev_id,'pregled');

		// echo "<table width=\"100%\" border=\"0\" bgcolor=\"#CCCCCC\" cellspacing=\"0\">\n";
		// echo "<tr>";
		// echo "<td>";
		// echo "<strong>\n";
		// echo "PODACI&nbsp;O&nbsp;OÃ¯Â¿Å“TEÃ¯Â¿Å“ENOM\n";
		// echo "</strong>\n";
		// echo "</td>\n";
		// echo "</tr>\n";

		echo "<table width=\"100%\" border=\"0\" bgcolor=\"#CCCCCC\" cellspacing=\"0\">\n";
		echo "<tr>";
		echo "<td width=\"100%\" class=\"headerSivoGornja\" align=\"left\" colspan=\"6\">\n";
		echo "<p>\n";
		echo "<strong><font size=\"6\">\n";
		echo "PODACI&nbsp;O&nbsp;O¹TEÆENOM\n";
		echo "</strong></font>\n";
		echo "</p>\n";
		echo "</td>\n";
		echo "</tr>\n";

		// Ne prikazuje se odmah, veÃ¯Â¿Å“ samo kad se na DPZ [tetama sa tipom PZ klikne na OK kod broja polise
		echo "<tr id=\"osiguranici_sa_putno_polise\" style=\"display:none;\">\n";
		echo "<td class=\"uvucenRedTd\" colspan='6'>\n";
		echo "Osiguranici:\n";
		echo "<select id=\"osiguranici_sa_putno_polise_select\" onchange=\"popuni_podatke_ostecenog_osiguranika_putno_polise(this.value);\"></select>";
		echo "</td>\n";
		echo "</tr>\n";


		$conn_amso = pg_connect("host=localhost  dbname=amso user=zoranp");
		$sql_osiguranik = " SELECT * FROM putno_osiguranici WHERE jmbg_osiguranika = '$jmbgPibOst' AND brpolise = $broj_polise ORDER BY id DESC";
		$result_osiguranik = pg_query($conn_amso, $sql_osiguranik);
		$niz_osiguranik = pg_fetch_assoc($result_osiguranik);

		if ($vrstaSt == 'DPZ') {
			$prezimeOst = ($prezimeOst) ? $prezimeOst : $niz_osiguranik['prezime'];
			$imeNazivOst = ($imeNazivOst) ? $imeNazivOst : $niz_osiguranik['ime'];
			$adresaOst = ($adresaOst) ? $adresaOst : $niz_osiguranik['ulica_osiguranika'] . ' ' . $niz_osiguranik['broj_ulice_osiguranika'];
			$osteceni_broj_pasosa = ($osteceni_broj_pasosa) ? $osteceni_broj_pasosa : $niz_osiguranik['br_pas'];
			$telefon2 = ($telefon2) ? $telefon2 : $niz_osiguranik['telefon_osiguranika'];
			$postanski_broj_osiguranika = ($postanski_broj_osiguranika) ? $postanski_broj_osiguranika : $niz_osiguranik['postanski_broj_osiguranika'];
		}
		// MARIJA 18.06.2015 - dodato da se izvuku podaci o ostecenom - KARJ

		echo "<tr>\n";
		echo "<td class=\"uvucenRedTd\" width='17%'>\n";
		echo "Prezime/Naziv:\n";
		echo "<br>\n";
		echo "<input name=\"prezimeOst\" id=\"prezimeOst\" value=\"$prezimeOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "<br>\n";
		echo "Ime:\n";
		echo "<br>\n";
		echo "<input name=\"imeNazivOst\" id=\"imeNazivOst\" value=\"$imeNazivOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";
		echo "<td width='17%'>\n";
		echo "JMBG/PIB:\n";
		echo "<br>\n";
		if ($jmbgPibOstBazaDisabled) $jmbgPibOstBaza = "readonly class='disabled'";
		echo "<input required style='display: inline;' id=\"jmbgPibOst\" onchange='VALID_JMBG(this);' name=\"jmbgPibOst\" value=\"$jmbgPibOst\" size=\"20\" height=\"15\" onkeypress=\" return samoBrojevi(this, event);\" $jmbgPibOstBaza>\n";
		if ($radnik == 2059 || $radnik == 3071 || $radnik == 151 || $radnik == 3064  || $radnik == 3085  || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125) {
			echo "<input style='display: inline:block;' type='checkbox' id='promeni_lib' name='promeni_lib' onclick='promeniLIB(this);' />";
		}
		echo "<input type='button' hidden id='sacuvaj_novi_lib' name='sacuvaj_novi_lib' onclick='provera_postojecih_predmeta();' value='Promeni' /><br>";
		//MARIJA 19.11.2014. dodato za broj licne karte osteæenog
		echo "Broj liène karte:\n";
		echo "<br>\n";
		echo "<input name=\"osteceni_broj_licne_karte\" value=\"$osteceni_broj_licne_karte\" size=\"20\" height=\"15\" onkeypress=\" return samoBrojevi(this, event);\" >\n";
		//KRAJ DODATKA
		echo "</td>\n";
		echo "<td width='17%'>\n";
		//MARIJA 10.11.2014

		echo "Zemlja:\n";
		echo "<br>\n";
		echo "<select name='osteceni_zemlja_id' id='osteceni_zemlja_id' onkeypress=\"return handleEnter(this, event)\" style='width:180px' >";
		echo "<option value='-1'>Izaberite zemlju</option>";
		// -> Lazar Milosavljevic2016-05-04 <- Izmena 10 na liniji 3764 pa nadalje nakon XDEBUG testiranja
		$indexKontinentiDPZ = count($nizKontinentiDPZ);
		$indexZemljeDPZ = count($nizZemljeDPZ);
		for ($i = 0; $i < $indexKontinentiDPZ; $i++) {
			$kontinent_iz_niza = $nizKontinentiDPZ[$i]['kontinent'];
			echo "<optgroup label='$kontinent_iz_niza'>";
			for ($j = 0; $j < $indexZemljeDPZ; $j++) {
				$id_zemlje_iz_niza = $nizZemljeDPZ[$j]['id'];
				$naziv_zemlje_iz_niza = $nizZemljeDPZ[$j]['naziv'];
				$kontinent_zemlje_iz_niza = $nizZemljeDPZ[$j]['kontinent_ispis'];
				if ($kontinent_zemlje_iz_niza == $kontinent_iz_niza) {
					if ($osteceni_mesto_opis) {
						$osteceni_zemlja_id_prikaz = $osteceni_zemlja_id;
						$selected = ($id_zemlje_iz_niza == $osteceni_zemlja_id_prikaz) ? "selected='selected'" : "";
						echo "<option value='" . $id_zemlje_iz_niza . "' $selected >" . $naziv_zemlje_iz_niza . "</option>";
					} else {
						echo "<option value='$id_zemlje_iz_niza' ";
						if ($id_zemlje_iz_niza == 199) echo "selected";
						echo ">";
						echo $naziv_zemlje_iz_niza;
						echo "</option>";
					}
				}
			}
			echo "</optgroup>";
		}
		echo "</select>";
		echo "<br>";
		//ZAVRSENO DODAVANJE 10.11.2014.
		echo "Adresa:\n";
		echo "<br>\n";
		echo "<input name=\"adresaOst\" id=\"adresaOst\" value=\"$adresaOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";
		echo "<td width='17%'>";
		// 	if($posbrOst && !$osteceni_mesto_id)
		// 	{
		// 		$osteceni_mesto_id_provera = vrati_mesto_po_broju_poste($conn2, $posbrOst);
		// 	}
		// 	else
		// 	{
		$osteceni_mesto_id_provera = $osteceni_mesto_id;
		//}
		$rezultatOpstine = $sifarnici_class->vratiOpstine();
		$rezultatOkruzi = $sifarnici_class->vratiOkruge();
		$rezultatMestoNaziv = $sifarnici_class->vratiNazivMesta($osteceni_mesto_id_provera);
		$rezultatOpstinePoMestu = $sifarnici_class->vratiOpstinuPoMestu($osteceni_mesto_id_provera);
		$osteceniOpstineId = $rezultatOpstinePoMestu['id'];
		echo "Op¹tina:\n";
		echo "<br>\n";
		$promeni_prikaz_osteceni_opstine = ($osteceni_mesto_opis) ? 'disabled=\"disabled\"' : '';
		$osteceni_zemlja_id_provera = $_POST['osteceni_zemlja_id'];
		echo "<select name='osteceni_opstina_id' id='osteceni_opstina_id' $promeni_prikaz_osteceni_opstine style='width:180px;' onkeypress=\"return handleEnter(this, event)\" onchange='postaviMestaOpstine(this);' >";
		echo "<option value='-1' >Izaberite op¹tinu</option>";
		// -> Lazar Milosavljevic2016-05-04 <- Izmena 11 na liniji 3834 pa nadalje nakon XDEBUG testiranja
		$nizOpstine = pg_fetch_all($rezultatOpstine);
		$nizOkruzi 	= pg_fetch_all($rezultatOkruzi);
		$indexOpstine = count($nizOpstine);
		$indexOkruzi = count($nizOkruzi);

		for ($j = 0; $j < $indexOkruzi; $j++) {
			echo "<optgroup label='" . $nizOkruzi[$i]['vrednost'] . "'>";
			for ($i = 0; $i < $indexOpstine; $i++) {
				if ($nizOkruzi[$j]['id'] == $nizOpstine[$i]['okrug_id']) {
					$selected = ($nizOpstine[$i]['id'] == $osteceniOpstineId) ? "selected='selected'" : "";
					echo "<option value='" . $nizOpstine[$i]['id'] . "' $selected >" . $nizOpstine[$i]['vrednost'] . "</option>";
				}
			}
			echo "</optgroup>";
		}

		echo "</select>";
		echo "<div id='predlog-mesto' style='display: none'><label id='predlzeno-mesto-label'>Predlog</label><input onClick='predloziMestoOsteceni(1)' type='checkbox' id='predlozeno-mesto'></div>";
		echo "<br>\n";
		echo "Mesto: \n";
		echo "<br>\n";
		$promeni_prikaz_osteceni_mesto_opis = ($osteceni_mesto_opis) ? '' : 'hidden';
		//MARIJA 10.11.2014
		echo "<input type='text' name='osteceni_mesto_opis' id='osteceni_mesto_opis' value='$osteceni_mesto_opis' $promeni_prikaz_osteceni_mesto_opis  size=\"20\" height=\"15\">";
		//MARIJA 2.11.2014.
		$promeni_prikaz_osteceni_mesto_id = ($osteceni_mesto_opis) ? 'hidden' : '';
		//MARIJA 17.11.2014
		if ($osteceni_mesto_id_provera && $osteceni_mesto_id_provera != -1) {
			$osteceni_mesto_id_disabled = "disabled='disabled'";
			$osteceni_mesto_id_class = "disabled";
		}
		//ZAVRSENO
		if ($vrstaSt != 'DPZ') {
			echo "<select name='osteceni_mesto_id' id='osteceni_mesto_id' $promeni_prikaz_osteceni_mesto_id style='width:180px;' onkeypress=\"return handleEnter(this, event)\" class='disabled'>";
		} else {
			echo "<select name='osteceni_mesto_id' id='osteceni_mesto_id' $promeni_prikaz_osteceni_mesto_id style='width:180px;' onkeypress=\"return handleEnter(this, event)\">";
		}
		echo "<option value='-1'>Izaberite mesto</option>";
		if ($osteceni_mesto_id_provera) {
			$nazivMesta = pg_fetch_all($rezultatMestoNaziv);
			echo "<option value='" . $osteceni_mesto_id_provera . "' selected='selected' >" . $nazivMesta[0]['vrednost'] . "</option>";
		}
		echo "</select>";
		echo "</td>\n";

		echo "<td width='16%'>\n";
		echo "&nbsp;Telefon:\n";
		echo "<br>\n";
		echo "<input name=\"telefon2\" value=\"$telefon2\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "<br>\n";
		echo "&nbsp;Tekuæi&nbsp;raèun:\n";
		echo "<br>\n";
		echo "<input name=\"tekRacun_ost\" value=\"$tekRacun_ost\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";
		echo "<td width='16%'>\n";
		echo "&nbsp;E-mail:\n";
		echo "<br>";
		echo "<input name=\"osteceni_mail\" value=\"$osteceni_mail\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n<br>";
		echo "&nbsp;Va¹ broj:\n";
		echo "<br>\n";

		echo "<input name=\"vas_broj\" value=\"$vas_broj_baza\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "<br>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		//MARIJA 31.10.2014.
		echo "<input name='posbrOst' value='$posbrOst' type='hidden' >\n";
		echo "<input name='posbrOvllice' value='$posbrOvllice' type='hidden' >\n";

		//ZAVRSENO
		echo "<td class='uvucenRedTd'>\n";

		echo "</td>";
		echo "<td>";
		echo "</td>\n";

		echo "<td></td>";
		echo "<td>\n";
		echo "</td>";
		echo "<td>";
		//ZAVRSENO
		echo "</tr>\n";

		//ubacujemo polja za adresu
		switch ($vrstaSt) {
			case 'DPZ':
				echo "<tr>\n";
				echo "<td class=\"uvucenRedTd\" colspan='2'>\n";
				echo "Broj paso¹a:\n";
				echo "<br>\n";
				echo "<input name=\"osteceni_broj_pasosa\" value=\"$osteceni_broj_pasosa\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td colspan='1'>\n";
				echo "Pol:\n";
				echo "<br>\n";
				echo "<select name='osteceni_pol'>";
				echo "<option value='-1'></option>";
				echo "<option value='M' ";
				if ($osteceni_pol == 'M') echo "selected";
				echo ">";
				echo "Mu¹ki";
				echo "</option>";
				echo "<option value='¾' ";
				if ($osteceni_pol == '¾') echo "selected";
				echo ">";
				echo "®enski";
				echo "</option>";
				echo "</select>";
				echo "</td>\n";

				echo "<td colspan='2'>\n";
				echo "E-mail:\n";
				echo "<br>\n";
				echo "<input name=\"osteceni_email\" value=\"$osteceni_email\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "</tr>\n";
				break;
			default:
				echo "<tr><td style='height:25px;'></td></tr>";
				echo "<tr>\n";
				echo "<td class=\"uvucenRedTd\">\n";
				echo "Marka\n";
				echo "<br>\n";
				echo "<input name=\"markaOst\" id=\"markaOst\" value=\"$markaOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "Tip\n";
				echo "<br>\n";
				echo "<input name=\"tipOst\" id=\"tipOst\" value=\"$tipOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "Model\n";
				echo "<br>\n";
				echo "<input name=\"modelOst\" id=\"modelOst\" value=\"$modelOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";


				// Marko Markovic 2020-05-13
				// Ako je IO registracija se sklanja tj postaje hidden
				if ($vrstaSt == 'IO') {
					echo "<td>\n";
					echo "God.:\n";
					echo "<br>\n";
					echo "<input name=\"godOst\" id=\"godOst\" value=\"$godOst\" size=\"5\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
					echo "<input type=\"hidden\" name=\"regPodOst\" id=\"regPodOst\" value=\"$regPodOst\" size=\"3\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">";
					echo "<input type=\"hidden\" name=\"regOznakaOst\" id=\"regOznakaOst\" value=\"$regOznakaOst\" size=\"5\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
					echo "</td>\n";
				} else {
					echo "<td>\n";
					echo "God.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Registarska&nbsp;oznaka:\n";
					echo "<br>\n";
					echo "<input name=\"godOst\" id=\"godOst\" value=\"$godOst\" size=\"5\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
					echo "&nbsp;";
					echo "<input name=\"regPodOst\" id=\"regPodOst\" value=\"$regPodOst\" size=\"3\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">";
					echo "*";
					echo "<input name=\"regOznakaOst\" id=\"regOznakaOst\" value=\"$regOznakaOst\" size=\"5\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
					echo "</td>\n";
				}
				// Marko Markovic 2020-05-13 kraj

				echo "<td>\n";
				// Marko Markovic 2020-05-13 ukoliko je IO umesto Sasije vozila upisuje se evidencioni broj 
				// inputi i value ostaju isti 				
				if ($vrstaSt == 'IO') {
					echo "&nbsp;Evidencioni broj:\n";
				} else {
					echo "&nbsp;Broj ¹asije vozila\n";
				}
				// Marko Markovic kraj 2020-05-13
				echo "<br>\n";

				echo "<input name=\"brsasOst\" id=\"brsasOst\" value=\"$brsasOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";

				echo "</td>\n";
				echo "<td>";
				if ($vrstaSt == 'IO') {
					echo "Radni sati:";
				} else {
					echo "Preðeno km:";
				}
				echo "<br>";
				echo "<input type=\"text\" name=\"predjenoKmOst\" value=\"$predjenoKmOst\" size=\"8\" onkeypress=\" return samoBrojevi(this, event);\" title=\"Mo¾ete uneti samo brojeve!\">&nbsp;\n";
				echo "&nbsp;";
				if ($dugme == 'DA') {
					echo "<input type=\"submit\" value=\"Podaci o vozilu\" class=\"button\" name=\"vozilo_dugme\" style='116px'>\n";
				}
				echo "</td>\n";
				echo "</tr>\n";
				break;
		}

		//struktura
		echo "<tr><td colspan='6'>\n";
		echo "&nbsp;\n";
		echo "</td></tr>\n";

		echo "<tr>";
		echo "<td><b>\n";
		echo "Sektorska struktura:\n";
		echo "</b></td>\n";

		echo "<td colspan=\"5\">\n";

		$sql = "select sifra || ' - ' || opis as sektor, sifra from sektorska_struktura  order by sifra";
		$tabela = 'sektorska_struktura';
		$polje = 'struktura';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'sektor', 'sifra', ${$polje}, 900);

		echo "</td>\n";
		echo "</tr>\n";

		//struktura
		echo "<tr>\n";
		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";
		switch ($vrstaSt) {
			case 'DPZ':
				break;

			default:
				echo "<tr>\n";
				echo "<td class=\"uvucenRedTd\">\n";
				echo "Naziv osiguranja\n";
				echo "<br>\n";
				echo "<input name=\"nazivOsigOst\" value=\"$nazivOsigOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "Broj&nbsp;polise\n";
				echo "<br>\n";
				echo "<input name=\"brPoliseOst\" value=\"$brPoliseOst\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td valign=\"bottom\">\n";
				echo "Va¾nost&nbsp;od:&nbsp;&nbsp;\n";
				echo "<br>";
				echo "<input name=\"vaznostOdOst\" value=\"$vaznostOdOst\" size=\"20\" height=\"15\" onclick=\"showCal('vaznostOdOst')\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td valign=\"bottom\" nowrap=\"nowrap\">\n";
				echo "do:&nbsp;&nbsp;\n";
				echo "<br>";
				echo "<input name=\"vaznostDoOst\" value=\"$vaznostDoOst\" size=\"20\" height=\"15\" onclick=\"showCal('vaznostDoOst')\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>";
				/* Nenad Puk¹ec  */
				echo "<td valign=\"bottom\" nowrap=\"nowrap\">\n";
				//echo "do:&nbsp;&nbsp;\n";
				echo "<br>";
				if ($vrstaSt == 'AO' || $vrstaSt == 'AK') {
					echo "<input type=\"button\" name='modal1' id='modal1' value=\"Prevuci sa saobraæajne\" size=\"20\" height=\"15\" onClick='prikaziModal(1)' >\n";
				}
				echo "</td>";

				echo "<div id='citacSaobracajneModal' class='modal'>
		  	<div class='modal-content' style='margin:0px auto;width:960px;height: 540px;margin-top: -120px !important'>
				<div id='boxes1'>
  					<div id='dialog1' class='window1'>
					<applet name='eVRCard' id='eVRCard'  code='eVRCardApplet.class' archive='../common/eVRC_vr11.jar' width='960' height='500' style='margin:0px auto !important;'>
						<PARAM name='printer' value='IBM'>
					</applet>
					<div style='float: left;margin: 0 auto !important;padding: 20px;width: 45%;'>
						<input type='button' class='close2' name='citac_preuzmi_podatke1' value='Preuzmi podatke' onClick='PodaciIzCitacaSD()' style='margin-left: 100px;'>
					</div>
					<div style='float: left;padding: 20px;width: 45%;'>
						<input type='button' name='zatvori_prozor1' value='Zatvori prozor' style='float: right;margin-right: 100px;' onClick='zatvoriModal()'>
					</div>
                    <input type='hidden' id='ost-osg' value=''>
				</div>
			</div>
  			</div>
		</div>";

				echo "<td  valign=\"bottom\" >\n";
				break;
		}

		if ($prepisi) {
			$prezimeVoz = $prezimeOst;
			$imeVoz  = $imeNazivOst;
			$jmbgVoz = $jmbgPibOst;
			$telefonv2 = $telefon2;
			$vozac_adresa = $adresaOst;
			$vozac_mesto_id = $osteceni_mesto_id;
			$vozac_mesto_opis = $osteceni_mesto_opis;
			$vozac_zemlja_id = $osteceni_zemlja_id;
			$vozac_broj_licne_karte = $osteceni_broj_licne_karte;
			echo "<input type=\"hidden\" name=\"idstete\" value=\"$idstete\">\n";
			echo "<input type=\"hidden\" name=\"datumEvid\" value=\"$datumEvid\">\n";
			if ($prida) {
				echo "<input type=\"hidden\" name=\"datumPrijave\" value=\"$datumPrijave\">\n";
			}
			if ($komda) {
				echo "<input type=\"hidden\" name=\"datumKompl\" value=\"$datumKompl\">\n";
			}
			echo "<input type=\"hidden\" name=\"brSt\" value=\"$brSt\">\n";
			echo "<input type=\"hidden\" name=\"vrstaSt\" value=\"$vrstaSt\">\n";
			echo "<input type=\"hidden\" name=\"nalog\" value=\"$nalog\">\n";
			echo "<input type=\"hidden\" name=\"isplaceno\" value=\"$isplaceno\">\n";
			echo "<input type=\"hidden\" name=\"isplata\" value=\"$isplata\">\n";
			echo "<input type=\"hidden\" name=\"prida\" value=\"$prida\">\n";
			echo "<input type=\"hidden\" name=\"komda\" value=\"$komda\">\n";
			echo "<input type=\"hidden\" name=\"idreak\" value=\"$idreak\">\n";
			echo "<input type=\"hidden\" name=\"rbrSD\" value=\"$rbrSD\">\n";
			echo "<input type=\"hidden\" name=\"rbrSteta\" value=\"$rbrSteta\">\n";
			echo "<input type=\"hidden\" name=\"rbrReaktivirana\" value=\"$rbrReaktivirana\">\n";
			echo "<input type=\"hidden\" name=\"reaktivirana\" value=\"$reaktivirana\">\n";
			echo "<input type=\"hidden\" name=\"reaktiviranaBaza\" value=\"$reaktiviranaBaza\">\n";
			if ($jmbgPibOstBaza) {
				echo "<input type=\"hidden\" name=\"jmbgPibOstBazaDisabled\" value=\"$jmbgPibOstBazaDisabled\">\n";
			}
		}

		if ($prepisiKriv) {
			$prezimeVozKriv =  $prezimeKriv;
			$imeVozKriv  = $imeNazivKriv;
			$jmbgVozKriv = $jmbgPibKriv;
			$vozac_krivac_adresa = $osiguranik_krivac_adresa;
			$vozac_krivac_mesto_id = $osiguranik_krivac_mesto_id;
			$vozac_krivac_mesto_opis = $osiguranik_krivac_mesto_opis;
			$vozac_krivac_zemlja_id = $osiguranik_krivac_zemlja_id;
			$vozac_krivac_telefon1 = $osiguranik_krivac_telefon1;
			$vozac_krivac_telefon2 = $osiguranik_krivac_telefon2;
			$vozac_krivac_broj_licne_karte = $osiguranik_krivac_broj_licne_karte;
			echo "<input type=\"hidden\" name=\"idstete\" value=\"$idstete\">\n";
			echo "<input type=\"hidden\" name=\"datumEvid\" value=\"$datumEvid\">\n";
			if ($prida) {
				echo "<input type=\"hidden\" name=\"datumPrijave\" value=\"$datumPrijave\">\n";
			}
			if ($komda) {
				echo "<input type=\"hidden\" name=\"datumKompl\" value=\"$datumKompl\">\n";
			}
			echo "<input type=\"hidden\" name=\"brSt\" value=\"$brSt\">\n";
			echo "<input type=\"hidden\" name=\"vrstaSt\" value=\"$vrstaSt\">\n";
			echo "<input type=\"hidden\" name=\"nalog\" value=\"$nalog\">\n";
			echo "<input type=\"hidden\" name=\"isplaceno\" value=\"$isplaceno\">\n";
			echo "<input type=\"hidden\" name=\"isplata\" value=\"$isplata\">\n";
			echo "<input type=\"hidden\" name=\"prida\" value=\"$prida\">\n";
			echo "<input type=\"hidden\" name=\"komda\" value=\"$komda\">\n";
			echo "<input type=\"hidden\" name=\"idreak\" value=\"$idreak\">\n";
			echo "<input type=\"hidden\" name=\"rbrSD\" value=\"$rbrSD\">\n";
			echo "<input type=\"hidden\" name=\"rbrSteta\" value=\"$rbrSteta\">\n";
			echo "<input type=\"hidden\" name=\"rbrReaktivirana\" value=\"$rbrReaktivirana\">\n";
			echo "<input type=\"hidden\" name=\"reaktivirana\" value=\"$reaktivirana\">\n";
			echo "<input type=\"hidden\" name=\"reaktiviranaBaza\" value=\"$reaktiviranaBaza\">\n";
			if ($jmbgPibOstBaza) {
				echo "<input type=\"hidden\" name=\"jmbgPibOstBazaDisabled\" value=\"$jmbgPibOstBazaDisabled\">\n";
			}
		}

		switch ($vrstaSt) {
			case 'DPZ':
				break;

				// Marko Markovic 2020-05-13 
			case 'IO';
				break;
				// Marko Markovic kraj 2020-05-13

			default:
				echo "<tr>\n";
				echo "<td class=\"headerSivo1\" colspan=\"6\" style='width:1000px'>\n";
				echo "<strong>\n";
				echo "PODACI&nbsp;O&nbsp;VOZAÈU\n";
				echo "</strong>\n";
				echo "</td>\n";
				echo "<tr>\n";
				echo "<td class=\"uvucenRedTd\">\n";
				echo "Prezime:\n";
				echo "<br>\n";
				echo "<input name=\"prezimeVoz\" id=\"prezimeVoz\" value=\"$prezimeVoz\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "<br>";
				echo "Ime:\n";
				echo "<br>\n";
				echo "<input name=\"imeVoz\" id=\"imeVoz\" value=\"$imeVoz\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";
				echo "<td>\n";
				echo "JMBG:\n";
				echo "<br>\n";
				echo "<input name=\"jmbgVoz\" id='jmbgVoz' value=\"$jmbgVoz\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				//MARIJA 19.11.2014
				echo "<br>";
				echo "Broj liène karte:\n";
				echo "<br>\n";
				echo "<input name=\"vozac_broj_licne_karte\" id='vozac_broj_licne_karte' value=\"$vozac_broj_licne_karte\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				//KRAJ DODATKA
				echo "</td>\n";
				//MARIJA 31.10.2014.
				// dodati parametri za opstine
				$rezultatOpstine = $sifarnici_class->vratiOpstine();
				$rezultatOkruzi = $sifarnici_class->vratiOkruge();
				$rezultatMestoNaziv = $sifarnici_class->vratiNazivMesta($vozac_mesto_id);
				$rezultatOpstinePoMestu = $sifarnici_class->vratiOpstinuPoMestu($vozac_mesto_id);
				$vozacOpstineId = $rezultatOpstinePoMestu['id'];

				echo "<td>\n";
				//	echo "<br>";
				echo "Zemlja:\n";
				echo "<br>\n";
				echo "<select name='vozac_zemlja_id' id='vozac_zemlja_id' onkeypress=\"return handleEnter(this, event)\" style='width:180px;'>";
				echo "<option value='-1'>Izaberite zemlju</option>";
				// -> Lazar Milosavljevic2016-05-04 <- Izmena 12 na liniji 4188 pa nadalje nakon XDEBUG testiranja
				$indexKontinentiDPZ = count($nizKontinentiDPZ);
				$indexZemljeDPZ = count($nizZemljeDPZ);
				for ($i = 0; $i < $indexKontinentiDPZ; $i++) {
					$kontinent_iz_niza = $nizKontinentiDPZ[$i]['kontinent'];
					echo "<optgroup label='$kontinent_iz_niza'>";
					for ($j = 0; $j < $indexZemljeDPZ; $j++) {
						$id_zemlje_iz_niza = $nizZemljeDPZ[$j]['id'];
						$naziv_zemlje_iz_niza = $nizZemljeDPZ[$j]['naziv'];
						$kontinent_zemlje_iz_niza = $nizZemljeDPZ[$j]['kontinent_ispis'];
						if ($kontinent_zemlje_iz_niza == $kontinent_iz_niza) {
							if ($vozac_mesto_opis) {
								$vozac_zemlja_id_prikaz = $vozac_zemlja_id;
								$selected = ($id_zemlje_iz_niza == $vozac_zemlja_id_prikaz) ? "selected='selected'" : "";
								echo "<option value='" . $id_zemlje_iz_niza . "' $selected >" . $naziv_zemlje_iz_niza . "</option>";
							} else {
								echo "<option value='$id_zemlje_iz_niza' ";
								if ($id_zemlje_iz_niza == 199)	echo "selected";
								echo ">";
								echo $naziv_zemlje_iz_niza;
								echo "</option>";
							}
						}
					}
					echo "</optgroup>";
				}
				echo "</select>";
				echo "<br>";
				echo "Adresa:\n";
				echo "<br>\n";
				echo "<input name=\"vozac_adresa\" id=\"vozac_adresa\" value=\"$vozac_adresa\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>";
				echo "<td>";
				echo "Op¹tina: \n";
				echo "<br>\n";
				$promeni_prikaz_vozac_opstine = ($vozac_mesto_opis) ? 'disabled=\"disabled\"' : '';
				echo "<select name='vozac_opstina_id' id='vozac_opstina_id' $promeni_prikaz_vozac_opstine style='width:180px;' onkeypress=\"return handleEnter(this, event)\" onchange='postaviMestaOpstine(this);'>";
				echo "<option value='-1' >Izaberite op¹tinu</option>";
				// -> Lazar Milosavljevic2016-05-04 <- Izmena 13 na liniji 4240 pa nadalje nakon XDEBUG testiranja
				$nizOpstine = pg_fetch_all($rezultatOpstine);
				$nizOkruzi = pg_fetch_all($rezultatOkruzi);
				$indexOpstine = count($nizOpstine);
				$indexOkruzi = count($nizOkruzi);
				for ($j = 0; $j < $indexOkruzi; $j++) {
					echo "<optgroup label='" . $nizOkruzi[$j]['vrednost'] . "'>";

					for ($i = 0; $i < $indexOpstine; $i++) {
						if ($nizOkruzi[$j]['id'] == $nizOpstine[$i]['okrug_id']) {
							$selected = ($nizOpstine[$i]['id'] == $vozacOpstineId) ? "selected='selected'" : "";
							echo "<option value='" . $nizOpstine[$i]['id'] . "' $selected >" . $nizOpstine[$i]['vrednost'] . "</option>";
						}
					}
					echo "</optgroup>";
				}
				echo "</select>";
				echo "<div id='predlog-mesto1' style='display: none'><label id='predlzeno-mesto-label1'>Predlog</label><input onClick='predloziMestoOsteceniVozac(1)' type='checkbox' id='predlozeno-mesto1'></div>";
				echo "<br>";
				echo "Mesto: \n";
				echo "<br>\n";
				$promeni_prikaz_vozac_mesto_opis = ($vozac_mesto_opis) ? '' : 'hidden';
				echo "<input type='text' name='vozac_mesto_opis' id='vozac_mesto_opis' value='$vozac_mesto_opis' $promeni_prikaz_vozac_mesto_opis size=\"20\" height=\"15\" >";
				// deo ubacen da kada se selektuje neka druga zemlja da ovo polje bude hidden a da se otvori text box za mesto
				$promeni_prikaz_vozac_mesto_id = ($vozac_mesto_opis) ? 'hidden' : '';

				echo "<select name='vozac_mesto_id' id='vozac_mesto_id' $promeni_prikaz_vozac_mesto_id style='width:180px;' onkeypress=\"return handleEnter(this, event)\" >";
				echo "<option value='-1'>Izaberite mesto</option>";
				if ($vozac_mesto_id) {
					$nazivMesta = pg_fetch_all($rezultatMestoNaziv);
					echo "<option value='" . $vozac_mesto_id . "' selected='selected' >" . $nazivMesta[0]['vrednost'] . "</option>";
				} else {
					echo "<option value='-1'>Izaberite mesto</option>";
				}
				echo "</select>";
				echo "</td>";
				//ZAVRSEN DODATAK
				echo "<td>";
				echo "&nbsp;Telefon:\n";
				echo "<br>\n";
				echo "<input name=\"telefonv1\" value=\"$telefonv1\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "<br>\n<br>";
				echo "<input name=\"telefonv2\" value=\"$telefonv2\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";
				echo "<td valign=\"bottom\" >\n";
				echo "<input type=\"submit\" value=\"Prepi¹i podatke o o¹teæenom\" class=\"button\" name=\"prepisi\" style='width:190px'>\n";
				echo "</td>\n";
				echo "</tr>\n";
				break;
		}
		echo "<tr><td class=\"footerSivo\" colspan=\"6\"></td>";
		echo "</tr>\n";
		echo "</table>\n";

		echo "<hr color=\"#000000\">\n";

		//3 red 				TRECA TABELA
		echo "<table width=\"100%\" border=\"0\" bgcolor=\"#FFFF99\" cellspacing=\"0\">\n";
		echo "<tr>\n";
		echo "<td class=\"headerZuto\" colspan=\"6\">\n";
		echo "<strong>\n";

		// dodat deo da se izvuku podaci o ugovaracu na osnovu polise -POCETAK 2015-06-02
		if ($vrstaSt == 'DPZ' && $tipSt == '0205') {
			$conn_amso = pg_connect("host=localhost  dbname=amso user=zoranp");
			$sql_dpz = "SELECT sd.broj_polise AS broj_polise FROM stetni_dogadjaj sd
			INNER JOIN odstetni_zahtev oz
			ON sd.id = oz.stetni_dogadjaj_id
			INNER JOIN predmet_odstetnog_zahteva poz
			ON oz.id = poz.odstetni_zahtev_id
			WHERE poz.id = " . $idstete;
			$result_dpz = pg_query($conn, $sql_dpz);
			$niz_dpz = pg_fetch_assoc($result_dpz);

			$broj_polise = $niz_dpz['broj_polise'];

			$sql_putno_dpz = "SELECT * FROM putno WHERE brpolise = $broj_polise";
			$result_putno_dpz = pg_query($conn_amso, $sql_putno_dpz);
			$niz_putno_dpz = pg_fetch_assoc($result_putno_dpz);

			$prezimeKriv = $niz_putno_dpz['prezime'];
			$zemlja_id_osiguranik = $niz_putno_dpz['id_zemlje'];
			$mesto_id_osiguranik = $niz_putno_dpz['mesto_id_osiguranik'];
			$osiguranik_krivac_telefon1 = $niz_putno_dpz['telefon'];

			$osiguranik_tip_lica = $niz_putno_dpz['fizpra'];
			if ($osiguranik_tip_lica == 'P') {
				$jmbgPibKriv = $niz_putno_dpz['pib'];
				$imeNazivKriv = $niz_putno_dpz['naziv_firme_ugovaraca'];
			} else {
				$jmbgPibKriv = $niz_putno_dpz['jmbg'];
				$imeNazivKriv = $niz_putno_dpz['ime'];
			}

			$osiguranik_krivac_adresa = $niz_putno_dpz['adresa'];
			$osiguranik_krivac_posbr = $niz_putno_dpz['posbr'];
		}
		// dodat deo da se izvuku podaci o ugovaracu na osnovu polise -KRAJ 2015-06-02
		switch ($vrstaSt) {
			case 'DPZ':
				echo "&nbsp;PODACI O UGOVARAÈU\n";
				break;
			default:
				echo "&nbsp;PODACI O OSIGURANIKU&nbsp;(&quot;Krivac&quot;)\n";
				break;
		}

		echo "</strong>\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td class=\"uvucenRedTd\" width='17%'>\n";
		echo "Prezime/Naziv:\n";
		echo "<br>\n";
		echo "<input name=\"prezimeKriv\" value=\"$prezimeKriv\" id='prezimeKriv' size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "<br>\n";
		echo "Ime:\n";
		echo "<br>\n";
		echo "<input name=\"imeNazivKriv\" value=\"$imeNazivKriv\" id='imeNazivKriv' size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td width='17%'>\n";
		echo "JMBG/PIB:\n";
		echo "<br>\n";
		echo "<input name=\"jmbgPibKriv\" id='jmbgPibKriv' value=\"$jmbgPibKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "<br>";
		echo "Broj liène karte:\n";
		echo "<br>\n";
		echo "<input name=\"osiguranik_krivac_broj_licne_karte\" id='osiguranik_krivac_broj_licne_karte' value=\"$osiguranik_krivac_broj_licne_karte\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";
		//MARIJA 3.11.2014.
		// parametri za opstine i mesta za osiguranika krivca
		$rezultatOpstine = $sifarnici_class->vratiOpstine();
		$rezultatOkruzi = $sifarnici_class->vratiOkruge();
		$rezultatMestoNaziv = $sifarnici_class->vratiNazivMesta($osiguranik_krivac_mesto_id);
		$rezultatOpstinePoMestu = $sifarnici_class->vratiOpstinuPoMestu($osiguranik_krivac_mesto_id);
		$osiguranikKrivacOpstineId = $rezultatOpstinePoMestu['id'];
		echo "<td   width='17%'>";
		//MARIJA 10.11.2014 deo koda vezan za prikaz zemalja za osiguranika krivca

		echo "Zemlja:\n";
		echo "<br>\n";
		echo "<select name='osiguranik_krivac_zemlja_id' id='osiguranik_krivac_zemlja_id' onkeypress=\"return handleEnter(this, event)\" style='width:180px;'>";
		echo "<option value='-1'>Izaberite zemlju</option>";
		// -> Lazar Milosavljevic2016-05-04 <- Izmena 14 na liniji 4394 pa nadalje nakon XDEBUG testiranja
		$indexKontinentiDPZ = count($nizKontinentiDPZ);
		$indexZemljeDPZ = count($nizZemljeDPZ);
		for ($i = 0; $i < $indexKontinentiDPZ; $i++) {
			$kontinent_iz_niza = $nizKontinentiDPZ[$i]['kontinent'];
			echo "<optgroup label='$kontinent_iz_niza'>";
			for ($j = 0; $j < $indexZemljeDPZ; $j++) {
				$id_zemlje_iz_niza = $nizZemljeDPZ[$j]['id'];
				$naziv_zemlje_iz_niza = $nizZemljeDPZ[$j]['naziv'];
				$kontinent_zemlje_iz_niza = $nizZemljeDPZ[$j]['kontinent_ispis'];
				if ($kontinent_zemlje_iz_niza == $kontinent_iz_niza) {
					if ($osiguranik_krivac_mesto_opis) {
						$osiguranik_krivac_zemlja_id_prikaz = $osiguranik_krivac_zemlja_id;
						$selected = ($id_zemlje_iz_niza == $osiguranik_krivac_zemlja_id_prikaz) ? "selected='selected'" : "";
						echo "<option value='" . $id_zemlje_iz_niza . "' $selected >" . $naziv_zemlje_iz_niza . "</option>";
					} else {
						echo "<option value='$id_zemlje_iz_niza' ";
						if ($id_zemlje_iz_niza == 199) echo "selected";
						echo ">";
						echo $naziv_zemlje_iz_niza;
						echo "</option>";
					}
				}
			}
			echo "</optgroup>";
		}

		echo "</select>";
		echo "<br>";
		echo "Adresa:\n";
		echo "<br>\n";
		echo "<input name=\"osiguranik_krivac_adresa\" id=\"osiguranik_krivac_adresa\" value=\"$osiguranik_krivac_adresa\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "<td  width='17%'>";

		echo "Op¹tina: \n";
		echo "<br>\n";
		$promeni_prikaz_osiguranik_krivac_opstine = ($osiguranik_krivac_mesto_opis) ? 'disabled=\"disabled\"' : '';
		echo "<select name='osiguranik_krivac_opstina_id' id='osiguranik_krivac_opstina_id' $promeni_prikaz_osiguranik_krivac_opstine style='width:180px;' onkeypress=\"return handleEnter(this, event)\" onchange='postaviMestaOpstine(this);'>";
		echo "<option value='-1' >Izaberite op¹tinu</option>";
		// -> Lazar Milosavljevic2016-05-04 <- Izmena 15 na liniji 4448 pa nadalje nakon XDEBUG testiranja
		$nizOpstine = pg_fetch_all($rezultatOpstine);
		$nizOkruzi = pg_fetch_all($rezultatOkruzi);
		$indexOpstine = count($nizOpstine);
		$indexOkruzi = count($nizOkruzi);
		for ($j = 0; $j < $indexOkruzi; $j++) {
			echo "<optgroup label='" . $nizOkruzi[$j]['vrednost'] . "'>";
			for ($i = 0; $i < $indexOpstine; $i++) {

				if ($nizOkruzi[$j]['id'] == $nizOpstine[$i]['okrug_id']) {
					if ($vrstaSt == 'DPZ' && $osiguranik_krivac_posbr) {
						$rezultatOpstinePoPostanskomBroju = $sifarnici_class->vrati_opstinu_po_broju_poste($osiguranik_krivac_posbr);
						$selected = ($nizOpstine[$i]['id'] == $rezultatOpstinePoPostanskomBroju['id']) ? "selected='selected'" : "";
						echo "<option value='" . $nizOpstine[$i]['id'] . "' $selected >" . $nizOpstine[$i]['vrednost'] . "</option>";
					} else {
						$selected = ($nizOpstine[$i]['id'] == $osiguranikKrivacOpstineId) ? "selected='selected'" : "";
						echo "<option value='" . $nizOpstine[$i]['id'] . "' $selected >" . $nizOpstine[$i]['vrednost'] . "</option>";
					}
				}
			}
			echo "</optgroup>";
		}

		echo "</select>";
		echo "<div id='predlog-mesto2' style='display: none'><label id='predlzeno-mesto-label2'>Predlog</label><input onClick='predloziMestoOsteceni(2)' type='checkbox' id='predlozeno-mesto2'></div>";
		echo "<br>";
		echo "Mesto:\n";
		echo "<br>\n";
		//MARIJA dodat deo za unos mesta ukoliko je u pitanju strano lice
		$promeni_prikaz_osiguranik_krivac_mesto_opis = ($osiguranik_krivac_mesto_opis) ? '' : 'hidden';
		echo "<input type='text' name='osiguranik_krivac_mesto_opis' id='osiguranik_krivac_mesto_opis' value='$osiguranik_krivac_mesto_opis' $promeni_prikaz_osiguranik_krivac_mesto_opis size=\"20\" height=\"15\">";
		//MARIJA 3.11.2014. deo ubacen da kada se selektuje neka druga zemlja da ovo polje bude hidden a da se otvori text box za mesto
		$promeni_prikaz_osiguranik_krivac_mesto_id = ($osiguranik_krivac_mesto_opis) ? 'hidden' : '';

		echo "<select name='osiguranik_krivac_mesto_id' id='osiguranik_krivac_mesto_id'  $promeni_prikaz_osiguranik_krivac_mesto_id style='width:180px;' onkeypress=\"return handleEnter(this, event)\" >";
		echo "<option value='-1'>Izaberite mesto</option>";

		if ($vrstaSt == 'DPZ' && $osiguranik_krivac_posbr) {
			$rezultatOpstinePoPostanskomBroju = $sifarnici_class->vrati_opstinu_po_broju_poste($osiguranik_krivac_posbr);
			$opstina_id = $rezultatOpstinePoPostanskomBroju['id'];
			$rezultat_mesto = $sifarnici_class->vrati_mesto_po_opstini($opstina_id);
			for ($i = 0; $i < count($rezultat_mesto); $i++) {
				echo "<option value='" . $rezultat_mesto[$i]['id'] . "'  >" . $rezultat_mesto[$i]['vrednost'] . "</option>";
			}
		}


		if ($osiguranik_krivac_mesto_id) {
			$nazivMesta = pg_fetch_all($rezultatMestoNaziv);
			echo "<option value='" . $osiguranik_krivac_mesto_id . "' selected='selected' >" . $nazivMesta[0]['vrednost'] . "</option>";
		}
		echo "</select>";

		echo "</td>";
		//MARIJA dodato zbog prikaza telefona ya osiguranika
		echo "<td  width='16%'>";
		echo "Telefon:\n";
		echo "<br>";
		echo "<input type='text' name='osiguranik_krivac_telefon1' id='osiguranik_krivac_telefon1' value='$osiguranik_krivac_telefon1' size=\"20\" height=\"15\">";
		echo "<br><br>";
		echo "<input type='text' name='osiguranik_krivac_telefon2' id='osiguranik_krivac_telefon2' value='$osiguranik_krivac_telefon2' size=\"20\" height=\"15\">";
		echo "</td>";
		//KRAJ dodatka za telefon
		//ZAVRSEN DODATAK za osiguranika krivca
		echo "<td  width='16%'>\n";
		echo "Ovla¹æeno lice:\n";
		echo "<br>";
		echo "<input name=\"ovlLiceKriv\" value=\"$ovlLiceKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		// dodato 2016-06-15
		echo "<br>";
		echo "Tekuæi raèun:\n";
		echo "<br>";
		echo "<input type='text' name='osiguranik_krivac_tekuci_racun' id='osiguranik_krivac_tekuci_racun' value='$osiguranik_krivac_tekuci_racun_ispis' size=\"20\" height=\"15\">";
		echo "</b></td>\n";
		echo "</tr>\n";

		//Nemanja Jovanovic
		echo "<tr>";
		echo "<td class='uvucenRedTd' width='16%'>";
		echo "Email:<br>";
		echo "<input type='text' name='osiguranik_mail' id='osiguranik_mail' value='$osiguranik_mail' size=\"20\" height=\"15\">";
		echo "</td>";
		echo "</tr>\n";

		switch ($vrstaSt) {
			case 'DPZ':
				break;

			default:
				echo "<tr><td style='height:25px;'></td><td></td><td></td><td></td><td></td></tr>";
				echo "<tr>\n";
				echo "<td class=\"uvucenRedTd\">\n";
				echo "Marka:\n";
				echo "<br>\n";
				echo "<input name=\"markaKriv\" id=\"markaKriv\" value=\"$markaKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "Tip:\n";
				echo "<br>\n";
				echo "<input name=\"tipKriv\" id=\"tipKriv\" value=\"$tipKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "Model:\n";
				echo "<br>\n";
				echo "<input name=\"modelKriv\" id=\"modelKriv\" value=\"$modelKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";


				// Marko Markovic 2020-05-13 
				// Ako je IO registracija se sklanja tj postaje hidden
				if ($vrstaSt == 'IO') {
					echo "<td>\n";
					echo "God.:\n";
					echo "<br>\n";
					echo "<input name=\"godKriv\" id=\"godKriv\" value=\"$godKriv\" size=\"5\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
					echo "<input type=\"hidden\" name=\"regPodOst\" id=\"regPodOst\" value=\"$regPodOst\" size=\"3\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">";
					echo "<input type=\"hidden\" name=\"regOznakaOst\" id=\"regOznakaOst\" value=\"$regOznakaOst\" size=\"5\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
					echo "</td>\n";
				} else {
					echo "<td>\n";
					echo "Godi¹te&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Registarska oznaka:\n";
					echo "<br>\n";
					echo "<input name=\"godKriv\" id=\"godKriv\" value=\"$godKriv\" size=\"5\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
					echo "&nbsp;\n";

					echo "<input name=\"regPodKriv\" id=\"regPodKriv\" value=\"$regPodKriv\" size=\"2\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">";
					echo "*";

					echo "<input name=\"regOznakaKriv\" id=\"regOznakaKriv\" value=\"$regOznakaKriv\" size=\"5\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
					echo "</td>\n";
				}
				// Marko Markovic 2020-05-13 kraj

				echo "<td>\n";
				// Marko Markovic 2020-05-13 ukoliko je IO umesto Sasije vozila upisuje se evidencioni broj 
				// inputi i value ostaju isti
				if ($vrstaSt == 'IO') {
					echo "&nbsp;Evidencioni broj:\n";
				} else {
					echo "&nbsp;Broj ¹asije vozila:\n";
				}
				// Marko Markovic kraj 2020-05-13

				echo "<br>\n";
				echo "<input name=\"brsasKriv\" id=\"brsasKriv\" value=\"$brsasKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "&nbsp;\n";
				echo "</td>\n";
				echo "</tr>\n";

				echo "<tr>\n";
				echo "<td class=\"uvucenRedTd\">\n";
				echo "Naziv&nbsp;osiguranja:\n";
				echo "<br>\n";
				echo "<input name=\"nazivOsigKriv\" value=\"$nazivOsigKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";
				echo "<td>\n";
				echo "Broj&nbsp;polise:\n";
				echo "<br>\n";
				echo "<input name=\"brPoliseKriv\" value=\"$brPoliseKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td valign=\"bottom\">\n";
				echo "Va¾nost od:&nbsp;&nbsp;";
				echo "<br>\n";
				echo "<input name=\"vaznostOdKriv\" value=\"$vaznostOdKriv\" size=\"20\" height=\"15\" onclick=\"showCal('vaznostOdKriv')\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td valign=\"bottom\">\n";
				echo "do:&nbsp;\n";
				echo "<br>\n";
				echo "<input name=\"vaznostDoKriv\" value=\"$vaznostDoKriv\" size=\"20\" height=\"15\" onclick=\"showCal('vaznostDoKriv')\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				/* Nenad Puk¹ec  */
				echo "<td valign=\"bottom\" nowrap=\"nowrap\">\n";
				//echo "do:&nbsp;&nbsp;\n";
				echo "<br>";
				if ($vrstaSt == 'AO' || $vrstaSt == 'AK') {
					echo "<input type=\"button\" id=\"modal1\" name=\"citajSaobracajnu\" value=\"Prevuci sa saobraæajne\" size=\"20\" height=\"15\" onclick=\"prikaziModal(2)\" >\n";
				}

				echo "</td>";
				echo "<td colspan=\"2\">\n";
				echo "&nbsp;\n";
				echo "</td>\n";

				break;
		}

		switch ($vrstaSt) {
			case 'DPZ':
				break;

				// Marko Markovic 2020-05-13 ----- izbacuje se deo forme kada je IO
			case 'IO';
				break;
				// Marko Markovic kraj 2020-05-13

			default:
				/*  DODATO    **** 7.7.11 **********************/
				echo "<tr><td></td></tr>";
				echo "</tr>\n";
				echo "<tr class=\"headerZuto1\">\n";
				echo "<td>\n";
				echo "<strong>\n";
				echo "&nbsp;&nbsp;PODACI&nbsp;O&nbsp;VOZAÈU\n";
				echo "</strong>\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "&nbsp;\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "&nbsp;\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "&nbsp;\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "&nbsp;\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "&nbsp;\n";
				echo "</td>\n";
				echo "</tr>\n";

				echo "<tr>\n";
				echo "<td class=\"uvucenRedTd\">\n";
				echo "Prezime:\n";
				echo "<br>\n";
				echo "<input name=\"prezimeVozKriv\" value=\"$prezimeVozKriv\" id='prezimeVozKriv' size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "<br>\n";
				echo "Ime:\n";
				echo "<br>\n";
				echo "<input name=\"imeVozKriv\" id='imeVozKriv' value=\"$imeVozKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				echo "<td>\n";
				echo "JMBG:\n";
				echo "<br>\n";
				echo "<input name=\"jmbgVozKriv\" id='jmbgVozKriv' value=\"$jmbgVozKriv\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				//MARIJA 19.11.2014.
				echo "<br>\n";
				echo "Broj liène karte:\n";
				echo "<br>\n";
				echo "<input name=\"vozac_krivac_broj_licne_karte\" id='vozac_krivac_broj_licne_karte' value=\"$vozac_krivac_broj_licne_karte\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>\n";

				$rezultatOpstine = $sifarnici_class->vratiOpstine();
				$rezultatOkruzi = $sifarnici_class->vratiOkruge();
				$rezultatMestoNaziv = $sifarnici_class->vratiNazivMesta($vozac_krivac_mesto_id);
				$rezultatOpstinePoMestu = $sifarnici_class->vratiOpstinuPoMestu($vozac_krivac_mesto_id);
				$vozacKrivacOpstineId = $rezultatOpstinePoMestu['id'];

				echo "<td>\n";
				//MARIJA 10.11.2014 deo koda vezan za prikaz zemalja

				echo "Zemlja:\n";
				echo "<br>\n";
				echo "<select name='vozac_krivac_zemlja_id' id='vozac_krivac_zemlja_id' onkeypress=\"return handleEnter(this, event)\" style='width:180px;'>";
				echo "<option value='-1'>Izaberite zemlju</option>";
				// -> Lazar Milosavljevic2016-05-04 <- Izmena 16 na liniji 4677 pa nadalje nakon XDEBUG testiranja
				$indexKontinentiDPZ = count($nizKontinentiDPZ);
				$indexZemljeDPZ = count($nizZemljeDPZ);

				for ($i = 0; $i < $indexKontinentiDPZ; $i++) {
					$kontinent_iz_niza = $nizKontinentiDPZ[$i]['kontinent'];
					echo "<optgroup label='$kontinent_iz_niza'>";
					for ($j = 0; $j < $indexZemljeDPZ; $j++) {
						$id_zemlje_iz_niza = $nizZemljeDPZ[$j]['id'];
						$naziv_zemlje_iz_niza = $nizZemljeDPZ[$j]['naziv'];
						$kontinent_zemlje_iz_niza = $nizZemljeDPZ[$j]['kontinent_ispis'];
						if ($kontinent_zemlje_iz_niza == $kontinent_iz_niza) {
							if ($vozac_krivac_mesto_opis) {
								$vozac_krivac_zemlja_id_prikaz = $vozac_krivac_zemlja_id;
								$selected = ($id_zemlje_iz_niza == $vozac_krivac_zemlja_id_prikaz) ? "selected='selected'" : "";
								echo "<option value='" . $id_zemlje_iz_niza . "' $selected >" . $naziv_zemlje_iz_niza . "</option>";
							} else {
								echo "<option value='$id_zemlje_iz_niza' ";
								if ($id_zemlje_iz_niza == 199) echo "selected";
								echo ">";
								echo $naziv_zemlje_iz_niza;
								echo "</option>";
							}
						}
					}
					echo "</optgroup>";
				}

				echo "</select>";
				echo "<br>";
				echo "Adresa:\n";
				echo "<br>\n";
				echo "<input name=\"vozac_krivac_adresa\" id=\"vozac_krivac_adresa\" value=\"$vozac_krivac_adresa\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
				echo "</td>";
				echo "<td>";
				echo "Op¹tina: \n";
				echo "<br>\n";
				$promeni_prikaz_vozac_krivac_opstine = ($vozac_krivac_mesto_opis) ? 'disabled=\"disabled\"' : '';
				echo "<select name='vozac_krivac_opstina_id' id='vozac_krivac_opstina_id' $promeni_prikaz_vozac_krivac_opstine style='width:180px;' onkeypress=\"return handleEnter(this, event)\" onchange='postaviMestaOpstine(this);'>";
				echo "<option value='-1' >Izaberite op¹tinu</option>";
				// -> Lazar Milosavljevic2016-05-04 <- Izmena 17 na liniji 4731 pa nadalje nakon XDEBUG testiranja
				$nizOpstine = pg_fetch_all($rezultatOpstine);
				$nizOkruzi = pg_fetch_all($rezultatOkruzi);
				$indexOpstine = count($nizOpstine);
				$indexOkruzi = count($nizOkruzi);
				for ($j = 0; $j < $indexOkruzi; $j++) {
					echo "<optgroup label='" . $nizOkruzi[$j]['vrednost'] . "'>";
					$nizOpstine = pg_fetch_all($rezultatOpstine);
					for ($i = 0; $i < $indexOpstine; $i++) {
						if ($nizOkruzi[$j]['id'] == $nizOpstine[$i]['okrug_id']) {
							$selected = ($nizOpstine[$i]['id'] == $vozacKrivacOpstineId) ? "selected='selected'" : "";
							echo "<option value='" . $nizOpstine[$i]['id'] . "' $selected >" . $nizOpstine[$i]['vrednost'] . "</option>";
						}
					}
					echo "</optgroup>";
				}

				echo "</select>";
				echo "<div id='predlog-mesto3' style='display: none'><label id='predlzeno-mesto-label3'>Predlog</label><input onClick='predloziMestoOsteceniVozac(2)' type='checkbox' id='predlozeno-mesto3'></div>";
				echo "<br>";
				echo "Mesto:\n";
				echo "<br>\n";
				//MARIJA dodat deo za unos mesta ukoliko je u pitanju strano lice
				$promeni_prikaz_vozac_krivac_mesto_opis = ($vozac_krivac_mesto_opis) ? '' : 'hidden';
				echo "<input type='text' name='vozac_krivac_mesto_opis' id='vozac_krivac_mesto_opis' value='$vozac_krivac_mesto_opis' $promeni_prikaz_vozac_krivac_mesto_opis  size=\"20\" height=\"15\">";
				//MARIJA 3.11.2014. deo ubacen da kada se selektuje neka druga zemlja da ovo polje bude hidden a da se otvori text box za mesto
				$promeni_prikaz_vozac_mesto_id = ($vozac_krivac_mesto_opis) ? 'hidden' : '';


				echo "<select name='vozac_krivac_mesto_id' id='vozac_krivac_mesto_id' $promeni_prikaz_vozac_mesto_id style='width:180px;'  onkeypress=\"return handleEnter(this, event)\" >";
				echo "<option value='-1'>Izaberite mesto</option>";
				if ($vozac_krivac_mesto_id) {
					$nazivMesta = pg_fetch_all($rezultatMestoNaziv);
					echo "<option value='" . $vozac_krivac_mesto_id . "' selected='selected' >" . $nazivMesta[0]['vrednost'] . "</option>";
				}
				echo "</select>";

				echo "</td>";
				//MARIJA dodato zbog prikaza telefona ya osiguranika
				echo "<td>";
				echo "Telefon:\n";
				echo "<br>";
				echo "<input type='text' name='vozac_krivac_telefon1' id='vozac_krivac_telefon1' value='$vozac_krivac_telefon1'  size=\"20\" height=\"15\">";
				echo "<br><br>";
				echo "<input type='text' name='vozac_krivac_telefon2' id='vozac_krivac_telefon2' value='$vozac_krivac_telefon2'  size=\"20\" height=\"15\">";
				echo "</td>";
				//KRAJ dodatka za telefon
				//ZAVRSENO

				echo "<td>\n";
				echo "<input type=\"submit\" value=\"Prepi¹i podatke o osiguraniku\" class=\"button\" name=\"prepisiKriv\" style='width:190px'>\n";
				echo "</td>\n";

				echo "</tr>\n";
				break;
		}
		echo "<tr><td class=\"footerZuto\" colspan=\"6\"></td></tr>";
		echo "</table>\n";

		echo "<hr color=\"#000000\">\n";
		echo "</td>\n";
		echo "</tr>\n";
		/*******NOVO**07.06.11*****/

		/* Novo za DPZ - ZP - Tabela sa podacima o osiguranom sluÃ¯Â¿Å“aju*/
		switch ($vrstaSt) {
			case 'DPZ':
				switch ($tipSt) {
					case '0205':
						echo "<table width=\"100%\" border=\"0\" bgcolor=\"#CC7070\" cellspacing=\"0\">\n";
						echo "<tr>\n";

						echo "<td class=\"headerCrveno\" colspan=\"4\">\n";
						echo "<strong>\n";
						echo "&nbsp;&nbsp;PODACI&nbsp;O&nbsp;OSIGURANOM&nbsp;SLUÈAJU\n";
						echo "</strong>\n";
						echo "</td>\n";

						echo "</tr>\n";

						echo "<tr>\n";

						echo "<td class=\"uvucenRedTd\">\n";
						echo "<div style='width:250px;float:left;'>";
						echo "Datum ulaska u zemlju destinacije:\n";
						echo "</div>";
						echo "<input name=\"datum_ulaska_u_zemlju_destinacije\" value=\"$datum_ulaska_u_zemlju_destinacije\" size=\"15\" height=\"15\" onclick=\"showCal('datum_ulaska_u_zemlju_destinacije')\" onkeypress=\"return handleEnter(this, event)\">\n";
						echo "</td>\n";

						echo "<td colspan='2'>\n";
						echo "<div style='width:250px;float:left;margin-left:50px;'>";
						echo "Naziv medicinske ustanove:\n";
						echo "</div>";
						echo "<input style='width:300px;' name=\"naziv_medicinske_ustanove\" value=\"$naziv_medicinske_ustanove\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
						echo "</td>\n";

						echo "</tr>\n";

						echo "<tr>\n";

						echo "<td class=\"uvucenRedTd\">\n";
						echo "<div style='width:250px;float:left;'>";
						echo "Datum izlaska iz zemlje destinacije:\n";
						echo "</div>";
						echo "<input name=\"datum_izlaska_iz_zemlje_destinacije\" value=\"$datum_izlaska_iz_zemlje_destinacije\" size=\"15\" height=\"15\" onclick=\"showCal('datum_izlaska_iz_zemlje_destinacije')\" onkeypress=\"return handleEnter(this, event)\">\n";
						echo "</td>\n";

						echo "<td colspan='2'>\n";
						echo "<div style='width:250px;float:left;margin-left:50px;'>";
						echo "Ime lekara:\n";
						echo "</div>";
						echo "<input style='width:300px;' name=\"ime_lekara\" value=\"$ime_lekara\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
						echo "</td>\n";

						echo "</tr>\n";

						echo "<tr>\n";

						echo "<td class=\"uvucenRedTd\">\n";

						echo "<div style='width:250px;float:left;'>";
						echo "Datum prijema u medicinsku ustanovu:\n";
						echo "</div>";
						echo "<input style='float:left;' name=\"datum_prijema_medicinska_ustanova\" value=\"$datum_prijema_medicinska_ustanova\" size=\"15\" height=\"15\" onclick=\"showCal('datum_prijema_medicinska_ustanova')\" onkeypress=\"return handleEnter(this, event)\">\n";

						echo "</td>\n";

						echo "<td>\n";
						echo "<div style='width:250px;float:left;margin-left:50px;'>";
						echo "Vrsta povrede / bolesti:\n";
						echo "</div>";

						// Pokupiti sve povrede ili bolesti, odnosno posledice
						$sql_uzroci_stete_dpz_zp = "SELECT distinct(uzrok) as uzrok FROM sifarnici.dpz_zp_posledice_stetnog_dogadjaja ORDER BY uzrok;";
						$upit_uzroci_stete_dpz_zp = pg_query($conn, $sql_uzroci_stete_dpz_zp);
						$niz_uzroci_stete_dpz_zp = pg_fetch_all($upit_uzroci_stete_dpz_zp);
						$sql_posledice_stete_dpz_zp = "SELECT * FROM sifarnici.dpz_zp_posledice_stetnog_dogadjaja ORDER BY posledica;";
						$upit_posledice_stete_dpz_zp = pg_query($conn, $sql_posledice_stete_dpz_zp);
						$niz_posledice_stete_dpz_zp = pg_fetch_all($upit_posledice_stete_dpz_zp);
						// Postaviti selekt sa podacima iz Ã¯Â¿Å“ifarnika
						echo "<select name='vrsta_povrede_ili_bolesti' style='width:300px;'>";
						echo "<option value='-1'></option>";
						foreach ($niz_uzroci_stete_dpz_zp as $uzrok2) {
							$uzrok2 = $uzrok2['uzrok'];
							echo "<optgroup label='$uzrok2' >";
							for ($i = 0; $i < count($niz_posledice_stete_dpz_zp); $i++) {
								if ($uzrok2 == $niz_posledice_stete_dpz_zp[$i]['uzrok']) {
									$selektovan_vrsta_povrede_ili_bolesti = ($niz_posledice_stete_dpz_zp[$i]['id'] == $vrsta_povrede_ili_bolesti ? 'selected' : '');
									$id_select_posledice_stete_dpz_zp = $niz_posledice_stete_dpz_zp[$i]['id'];
									echo "<option value='$id_select_posledice_stete_dpz_zp' $selektovan_vrsta_povrede_ili_bolesti>";
									echo $niz_posledice_stete_dpz_zp[$i]['posledica'];
									echo "</option>";
								}
							}
							echo "</optgroup>";
						}
						echo "</select>";

						echo "</td>\n";

						echo "</tr>\n";

						echo "<tr>\n";

						echo "<td class=\"uvucenRedTd\">\n";
						echo "<div style='width:250px;float:left;'>";
						echo "Datum otpu¹tanja iz medicinske ustanove:\n";
						echo "</div>";
						echo "<input style='float:left;' name=\"datum_otpustanja_medicinska_ustanova\" value=\"$datum_otpustanja_medicinska_ustanova\" size=\"15\" height=\"15\" onclick=\"showCal('datum_otpustanja_medicinska_ustanova')\" onkeypress=\"return handleEnter(this, event)\">\n";
						echo "</td>\n";

						echo "<td>\n";
						echo "<div style='width:250px;float:left;margin-left:50px;'>";
						echo "Vrsta leèenja:\n";
						echo "</div>";
						echo "<input  style='width:300px;' name=\"vrsta_lecenja\" value=\"$vrsta_lecenja\" size=\"50\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
						echo "</td>\n";

						echo "</tr>\n";

						echo "<tr>\n";

						echo "<td class=\"uvucenRedTd\">\n";
						echo "</td>\n";

						echo "<td style='vertical-align:middle;'>\n";
						echo "<div style='width:250px;float:left;margin-left:50px;'>";
						echo "Napomena o osiguranom sluèaju:\n";
						echo "</div>";
						echo "<textarea name=\"napomena_o_osiguranom_slucaju\" style='width:300px;height:50px;resize:none;float:left;' value=\"$napomena_o_osiguranom_slucaju\" onkeypress=\"return handleEnter(this, event)\">$napomena_o_osiguranom_slucaju</textarea>\n";
						echo "</td>\n";

						echo "</tr>\n";

						echo "<tr><td class=\"footerCrveno\" colspan=\"4\"></td>";
						echo "</tr>\n";
						echo "</table>\n";
						break;

					default:
						break;
				}
				break;

			default:
				break;
		}
		echo "<hr color=\"#000000\">\n";

		/* Novo za DPZ - ZP - Tabela sa podacima o ostvarenim osiguranim pokriÃ¯Â¿Å“ima*/
		switch ($vrstaSt) {
			case 'DPZ':
				switch ($tipSt) {
					case '0205':
						// Pokupi sve podatke iz tabele 'knjigas_dpz_zp_ostvarena_osigurana_pokrica' u bazi 'stete'
						$sql_ostvarena_pokrica = "SELECT * FROM knjigas_dpz_zp_ostvarena_osigurana_pokrica WHERE idstete=$idstete";
						$upit_ostvarena_pokrica = pg_query($conn, $sql_ostvarena_pokrica);
						$niz_ostvarena_pokrica = pg_fetch_all($upit_ostvarena_pokrica);
						$broj_ostvarenih_osiguranih_pokrica = count($niz_ostvarena_pokrica);
						if (count($niz_ostvarena_pokrica) > 0) {
							$id_osiguravajuceg_pokrica_1 = $niz_ostvarena_pokrica[0]['osigurana_pokrica_id'];
							$cena_osiguravajuceg_pokrica_1 = $niz_ostvarena_pokrica[0]['iznos'];
							$valuta_osiguravajuceg_pokrica_1 = $niz_ostvarena_pokrica[0]['valuta'];
							$napomena_osiguravajuceg_pokrica_1 = $niz_ostvarena_pokrica[0]['napomena'];
						}
						echo "<table id='tabela_osigurana_pokrica' name='tabela_osigurana_pokrica' width='100%' border='0' bgcolor='#F2A2E5' cellspacing='0'>\n";
						echo "<tr>\n";

						echo "<td class='headerRoze' colspan='6'>\n";
						echo "<strong>\n";
						echo "&nbsp;&nbsp;PODACI&nbsp;O&nbsp;OSTVARENIM&nbsp;OSIGURANIM&nbsp;POKRIÆIMA\n";
						echo "</strong>\n";
						echo "</td>\n";

						echo "</tr>\n";

						echo "<tr id='osiguravajuce_pokrice_podaci_1'>\n";
						// REDNI BROJ
						echo "<td class='uvucenRedTd' style='width:20px;'>\n";
						echo "<b>1.</b>\n";
						echo "</td>\n";
						echo "<td style='width:500px;'>\n";
						echo "Predmet osiguravajuæeg pokriæa:\n";
						// OSIGURAVAJUÃ¯Â¿Å“A POKRIÃ¯Â¿Å“A
						// Pokupi sva osiguravajuca pokriÃ¯Â¿Å“a iz baze i postavi ih u selekt
						$sql_osiguravajuca_pokrica = "SELECT * FROM sifarnici.dpz_zp_osigurana_pokrica ORDER BY naziv;";
						$upit_osiguravajuca_pokrica = pg_query($conn, $sql_osiguravajuca_pokrica);
						$niz_osiguravajuca_pokrica = pg_fetch_all($upit_osiguravajuca_pokrica);
						echo "<select style='width:300px;' id='id_osiguravajuceg_pokrica_1' name='id_osiguravajuceg_pokrica_1' onkeypress='return handleEnter(this, event)'>\n";
						echo "<option value='-1'></option>";
						for ($i = 0; $i < count($niz_osiguravajuca_pokrica); $i++) {
							echo "<option value='" . $niz_osiguravajuca_pokrica[$i]['id'] . "' title='" . $niz_osiguravajuca_pokrica[$i]['opis'] . "' ";
							if ($id_osiguravajuceg_pokrica_1 == $niz_osiguravajuca_pokrica[$i]['id']) echo "selected='selected'";
							echo ">";
							echo $niz_osiguravajuca_pokrica[$i]['naziv'];
							echo "</option>";
						}
						echo "</select>";
						echo "</td>\n";
						// IZNOS
						echo "<td style='width:140px;'>\n";
						echo "Iznos:\n";
						echo "<input style='width:90px;' id='cena_osiguravajuceg_pokrica_1' name='cena_osiguravajuceg_pokrica_1' value='$cena_osiguravajuceg_pokrica_1' size='20' height='15' onkeypress='return handleEnter(this, event)'>\n";
						echo "</td>\n";
						echo "<td style='width:140px;'>\n";
						echo "Valuta:\n";
						// VALUTA
						// Napravi listu nekoliko valuta koje su opticaju
						echo "<select style='width:90px;' id='valuta_osiguravajuceg_pokrica_1' name='valuta_osiguravajuceg_pokrica_1' onkeypress='return handleEnter(this, event)'>\n";
						echo "<option value='-1'></option>";
						echo "<option value='RSD' title='Dinar - RSD' ";
						if ($valuta_osiguravajuceg_pokrica_1 == 'RSD') echo "selected";
						echo ">";
						echo "Dinar - RSD";
						echo "</option>";
						echo "<option value='EUR' ";
						if ($valuta_osiguravajuceg_pokrica_1 == 'EUR') echo "selected";
						echo ">";
						echo "Evro - EUR";
						echo "</option>";
						echo "<option value='CHF' ";
						if ($valuta_osiguravajuceg_pokrica_1 == 'CHF') echo "selected";
						echo ">";
						echo "¹vajcarski franak - CHF";
						echo "</option>";
						echo "<option value='USD' ";
						if ($valuta_osiguravajuceg_pokrica_1 == 'USD') echo "selected";
						echo ">";
						echo "Amerièki dolar - USD";
						echo "</option>";
						echo "</select>";
						echo "</td>\n";
						// NAPOMENA
						echo "<td style='width:350px;'>\n";
						echo "Napomena:\n";
						echo "<input style='width:250px;' id='napomena_osiguravajuceg_pokrica_1' name='napomena_osiguravajuceg_pokrica_1' value='$napomena_osiguravajuceg_pokrica_1' size='20' height='15' onkeypress='return handleEnter(this, event)'>\n";
						echo "</td>\n";
						echo "<td>\n";
						if ($radnik == 151 || $radnik == 3044 || $radnik == 3042 || $radnik == 3067 || $radnik == 138) {
							$provera_garancija = "SELECT id from dpz_garancija WHERE id_stete=$idstete";
							$result_garancija = pg_query($conn, $provera_garancija);
							$podaci_garancija = pg_fetch_assoc($result_garancija);

							$garancija_id = $podaci_garancija['id'];
							if ($niz_ostvarena_pokrica) {
								echo "<input type='button' id='garancija_dokument' name='garancija_dokument' value='Kreiraj garanciju' onclick='stampaj_garanciju($garancija_id)' >";
							}
						}
						echo "</td>\n";

						echo "</tr>\n";
						// IspiÃ¯Â¿Å“i sve redove kada ime viÃ¯Â¿Å“e od jednog zapisa u bazi
						for ($j = 1; $j < count($niz_ostvarena_pokrica); $j++) {
							// ISPIÃ¯Â¿Å“I CEO RED
							$redni_broj_ostvarenog_pokrica_baza = $j + 1;
							$id_osiguravajuceg_pokrica_baza = $niz_ostvarena_pokrica[$j]['osigurana_pokrica_id'];
							$cena_osiguravajuceg_pokrica_baza = $niz_ostvarena_pokrica[$j]['iznos'];
							$valuta_osiguravajuceg_pokrica_baza = $niz_ostvarena_pokrica[$j]['valuta'];
							$napomena_osiguravajuceg_pokrica_baza = $niz_ostvarena_pokrica[$j]['napomena'];
							echo "<tr id='osiguravajuce_pokrice_podaci_$redni_broj_ostvarenog_pokrica_baza'>\n";
							// REDNI BROJ
							echo "<td class='uvucenRedTd' style='width:20px;'>\n";
							echo "<div id='redni_broj_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' ><b>$redni_broj_ostvarenog_pokrica_baza.</b></div>\n";
							echo "</td>\n";
							echo "<td style='width:500px;'>\n";
							echo "Predmet osiguravajuæeg pokriæa:\n";
							// OSIGURAVAJUÃ¯Â¿Å“A POKRIÃ¯Â¿Å“A
							// Pokupi sva osiguravajuca pokriÃ¯Â¿Å“a iz baze i postavi ih u selekt
							$sql_osiguravajuca_pokrica = "SELECT * FROM sifarnici.dpz_zp_osigurana_pokrica ORDER BY naziv;";
							$upit_osiguravajuca_pokrica = pg_query($conn, $sql_osiguravajuca_pokrica);
							$niz_osiguravajuca_pokrica = pg_fetch_all($upit_osiguravajuca_pokrica);
							echo "<select style='width:300px;' id='id_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' name='id_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' onkeypress='return handleEnter(this, event)'>\n";
							echo "<option value='-1'></option>";
							for ($i = 0; $i < count($niz_osiguravajuca_pokrica); $i++) {
								echo "<option value='" . $niz_osiguravajuca_pokrica[$i]['id'] . "' title='" . $niz_osiguravajuca_pokrica[$i]['opis'] . "' ";
								if ($id_osiguravajuceg_pokrica_baza == $niz_osiguravajuca_pokrica[$i]['id']) echo "selected='selected'";
								echo ">";
								echo $niz_osiguravajuca_pokrica[$i]['naziv'];
								echo "</option>";
							}
							echo "</select>";
							echo "</td>\n";
							// IZNOS
							echo "<td style='width:140px;'>\n";
							echo "Iznos:\n";
							echo "<input style='width:90px;' id='cena_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' name='cena_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' value='$cena_osiguravajuceg_pokrica_baza' size='20' height='15' onkeypress='return handleEnter(this, event)'>\n";
							echo "</td>\n";
							echo "<td style='width:140px;'>\n";
							echo "Valuta:\n";
							// VALUTA
							// Napravi listu nekoliko valuta koje su opticaju
							echo "<select style='width:90px;' id='valuta_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' name='valuta_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' onkeypress='return handleEnter(this, event)'>\n";
							echo "<option value='-1'></option>";
							echo "<option value='RSD' title='Dinar - RSD' ";
							if ($valuta_osiguravajuceg_pokrica_baza == 'RSD') echo "selected";
							echo ">";
							echo "Dinar - RSD";
							echo "</option>";
							echo "<option value='EUR' ";
							if ($valuta_osiguravajuceg_pokrica_baza == 'EUR') echo "selected";
							echo ">";
							echo "Evro - EUR";
							echo "</option>";
							echo "<option value='CHF' ";
							if ($valuta_osiguravajuceg_pokrica_baza == 'CHF') echo "selected";
							echo ">";
							echo "¹vajcarski franak - CHF";
							echo "</option>";
							echo "<option value='USD' ";
							if ($valuta_osiguravajuceg_pokrica_baza == 'USD') echo "selected";
							echo ">";
							echo "Amerièki dolar - USD";
							echo "</option>";
							echo "</select>";
							echo "</td>\n";
							// NAPOMENA
							echo "<td style='width:350px;'>\n";
							echo "Napomena:\n";
							echo "<input style='width:250px;' id='napomena_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' name='napomena_osiguravajuceg_pokrica_$redni_broj_ostvarenog_pokrica_baza' value='$napomena_osiguravajuceg_pokrica_baza' size='20' height='15' onkeypress='return handleEnter(this, event)'>\n";
							echo "</td>\n";
							echo "<td>\n";
							echo	"<input type='button' value='Obri¹i' id='obrisi_osiguravajuce_pokrice_$redni_broj_ostvarenog_pokrica_baza' name='obrisi_osiguravajuce_pokrice_$redni_broj_ostvarenog_pokrica_baza' onclick='obrisi_osiguravajuce_pokrice($redni_broj_ostvarenog_pokrica_baza);' />";
							echo "</td>\n";

							echo "</tr>\n";
						}


						echo "<tr>\n";
						// PROMENI OBAVEZNO

						echo "<td class='uvucenRedTd' colspan='5' style='text-align:center;' >\n";
						echo "<br/>";
						echo "<input type='button' id='unesi_osiguravajuce_pokrice' name='unesi_osiguravajuce_pokrice' value='Dodaj osiguravajuæe pokriæe' onkeypress='return handleEnter(this, event)' onclick='dodaj_ostvareno_osigurano_pokrice();'>\n";
						echo "<input type='hidden' id='broj_ostvarenih_osiguranih_pokrica' name='broj_ostvarenih_osiguranih_pokrica' value='$broj_ostvarenih_osiguranih_pokrica' />";
						echo "<input type='hidden' id='osiguravajuce_pokrice_radnik' name='osiguravajuce_pokrice_radnik' value='$radnik' />";
						echo "</td>\n";

						echo "</tr>\n";
						echo "<tr><td class='footerRoze' colspan='6'></td>";
						echo "</tr>\n";
						echo "</table>\n";
						break;

					default:
						break;
				}
				break;

			default:
				break;
		}
		echo "<hr color=\"#000000\">\n";

		//*************4 RED= TABELA(siva)********/ --- Zakomentarisano april 2013 - Lazar MilosavljeviÃ¯Â¿Å“

		//*************5 RED=TABELA(plava)********/
		echo "<table width=\"100%\"  border=\"0\" cellspacing=\"0\" rowspacing=\"1\" class=\"tabela\">\n";
		//1red
		echo "<tr>\n";
		echo "<td width=\"30%\"  bgcolor=\"#2BAEFF\" class=\"headerPlavo\" colspan=\"2\">\n";
		echo "<strong>\n";
		echo "&nbsp;&nbsp;REGRESNA ©TETA &nbsp;\n";
		echo "</strong>\n";
		echo "</td>\n";

		echo "<td  width=\"45%\" class=\"headerZeleno\" colspan=\"2\" bgcolor=\"#B1FEFC\"><b>\n";
		echo "&nbsp;SNIMANJE ©TETE- (utv.obima ¹tete)&nbsp;IV\n";
		echo "</b></td>\n";

		echo "</tr>\n";

		//3red
		echo "<tr>\n";
		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "&nbsp;&nbsp;Vrsta:&nbsp;\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "<input name=\"vrstaRegStet\" value=\"$vrstaRegStet\" size=\"15\" height=\"17\" onkeypress=\"return handleEnter(this, event)\" class=\"textBoxRegSteta\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td rowspan=\"2\" bgcolor=\"#B1FEFC\" align=\"left\" valign=\"midle\"><b>\n";
		echo "&nbsp;&nbsp;Procenitelji:\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#B1FEFC\" align=\"left\">\n";
		echo "<strong>\n";
		echo "1\n";
		echo "</strong>\n";
		echo "&nbsp;\n";


		// Lazar Milosavljevicapril 2013
		$sql = "SELECT
				prezime || ' ' || ime as pim,
				jmbg
			FROM
				procenitelji
			WHERE
				(aktivan='A' AND procenitelj = true)
					OR
				jmbg='$procenitelj1'
			ORDER BY
				prezime,ime;
			";
		$tabela = 'procenitelji';
		$polje = 'procenitelj1';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'pim', 'jmbg', ${$polje});

		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";		//4red
		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "&nbsp;&nbsp;Oznaka:&nbsp;\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "<input name=\"oznakaRegStet\" value=\"$oznakaRegStet\" size=\"15\" height=\"15\" onkeypress=\"return handleEnter(this, event)\" class=\"textBoxRegSteta\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#B1FEFC\" align=\"left\">\n";
		echo "<strong>\n";
		echo "2\n";
		echo "</strong>\n";
		echo "&nbsp;\n";

		// Lazar Milosavljevicapril 2013
		$sql = "SELECT
				prezime || ' ' || ime as pim,
				jmbg
			FROM
				procenitelji
			WHERE
				(aktivan='A' AND procenitelj = true)
					OR
				jmbg='$procenitelj2'
			ORDER BY
				prezime,ime;
			";
		$tabela = 'procenitelji';
		$polje = 'procenitelj2';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'pim', 'jmbg', ${$polje});

		echo "</tr>\n";

		echo "<tr>\n";		//5 red
		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "&nbsp;&nbsp;Osiguranje:&nbsp;\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "<input name=\"osiguranjeRegStet\" value=\"$osiguranjeRegStet\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\" class=\"textBoxRegSteta\">\n";
		echo "</td>\n";

		echo "<td  bgcolor=\"#B1FEFC\" align=\"left\" ><b>\n";
		echo "&nbsp;&nbsp;Datum:\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#B1FEFC\" align=\"left\">\n";
		echo "<input name=\"datumProc\" value=\"$datumProc\" size=\"27\" height=\"15\" onclick=\"showCal('datumProc')\" onkeypress=\"return handleEnter(this, event)\">\n";

		// 2020-05-29 prebaceno dugme iznad po zahtevu Slavenka
		// $unosivaci = array(151,138,3093,3055,3052,3053,3045,3033,3039,3093,3067,3083,3078,3044,3038,3090,3023,3081,3079,122,3029,3054,3024,3046,3085,3032,3070,2253,3116,3101,3080,2119,2224,3069,3043,3016,3004,3102,3106);
		/*
$conn_amso = pg_connect ("host=localhost dbname=amso user=zoranp");
$sql_unosivaci = "SELECT radnik FROM radnik WHERE faza_stete is not null";  
$rezultat      = pg_query($conn_amso, $sql_unosivaci);
$niz_unosivaci = pg_fetch_all($rezultat);
$brunosivaca   = pg_num_rows($rezultat);
for ($i=0; $i < $brunosivaca ; $i++)
{
  $unosivaci[] = $niz_unosivaci[$i]['radnik'];
}
if(in_array($radnik, $unosivaci))
{
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<input type='button' onclick='otvori_dodatne_napomen(2);' id='napomena_snimanje_faza' name='napomena_snimanje_faza' style='height:30px; width:200px; font-size:13px; margin:0px;' text-align='center' value='Napomena za sajt dru¹tva' />";	
}
*/
		// 2020-05-29 dugme kraj


		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";		//6 red
		echo "<td bgcolor=\"#2BAEFF\" rowspan=\"2\" valign=\"top\">\n";
		echo "&nbsp;&nbsp;Dr¾ava:&nbsp;\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#2BAEFF\" rowspan=\"2\" valign=\"top\">\n";
		echo "<input name=\"drzavaRegStet\" value=\"$drzavaRegStet\" size=\"20\" height=\"15\" onkeypress=\"return handleEnter(this, event)\" class=\"textBoxRegSteta\" $disable_selekti>\n";
		echo "</td>\n";

		echo "<td  bgcolor=\"#B1FEFC\" align=\"left\"><b>\n";
		echo "&nbsp;&nbsp;Opis ¹tete:\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#B1FEFC\" style=\"display:flex; align-items:center;\" align=\"left\">\n";
		echo "<textarea name=\"opisStete\" rows=\"4\" style=\"resize:none;\" cols=\"40\">\n";
		echo $opisStete;
		echo "</textarea>\n";

		// 2020-05-29 prebacivanje napomene na zahtev Slavenka ---- Marko Markovic
		echo "&nbsp;&nbsp;<b>Napomena:</b>&nbsp;&nbsp;";
		echo "<textarea name=\"napomenaSnimanje\" rows=\"4\" style=\"resize:none;\" cols=\"40\">\n";
		echo $napomenaSnimanje;
		echo "</textarea>\n";

		$conn_amso = pg_connect("host=localhost dbname=amso user=zoranp");
		$sql_unosivaci = "SELECT radnik FROM radnik WHERE faza_stete is not null";
		$rezultat      = pg_query($conn_amso, $sql_unosivaci);
		$niz_unosivaci = pg_fetch_all($rezultat);
		$brunosivaca   = pg_num_rows($rezultat);
		for ($i = 0; $i < $brunosivaca; $i++) {
			$unosivaci[] = $niz_unosivaci[$i]['radnik'];
		}
		if (in_array($radnik, $unosivaci)) {
			echo "&nbsp;&nbsp;";
			echo "<input type='button' onclick='otvori_dodatne_napomen(2);' id='napomena_snimanje_faza' name='napomena_snimanje_faza' style='height:30px; width:200px; font-size:13px; margin:0px;' text-align='center' value='Napomena za sajt dru¹tva' />";
		}

		// 2020-05-29 kraj napomene

		echo "</td>\n";
		echo "</tr>\n";


		// Marko Markovic 2020-05-29 prazni tr i td zbog boja na regresu i snimanju stete a dugme za sajt drustva se prebacuje iznad
		echo "<tr>";
		echo "<td bgcolor='#B1FEFC'></td>";
		echo "<td bgcolor='#B1FEFC'></td>";
		echo "</tr>";
		// Marko kraj 2020-05-29 dugme je bilo ispod zakomentarisano je prebaceno iznad

		/*
// ----------- Marko Markovic dugme za dodantnu napomenu faza snimanje modal 2019-12-06 ---------------
echo "<tr>";
echo "<td bgcolor='#B1FEFC'></td>";
echo "<td bgcolor='#B1FEFC'>";
// $unosivaci = array(151,138,3093,3055,3052,3053,3045,3033,3039,3093,3067,3083,3078,3044,3038,3090,3023,3081,3079,122,3029,3054,3024,3046,3085,3032,3070,2253,3116,3101,3080,2119,2224,3069,3043,3016,3004,3102,3106);
$conn_amso = pg_connect ("host=localhost dbname=amso user=zoranp");
$sql_unosivaci = "SELECT radnik FROM radnik WHERE faza_stete is not null";  
$rezultat      = pg_query($conn_amso, $sql_unosivaci);
$niz_unosivaci = pg_fetch_all($rezultat);
$brunosivaca   = pg_num_rows($rezultat);
for ($i=0; $i < $brunosivaca ; $i++)
{
  $unosivaci[] = $niz_unosivaci[$i]['radnik'];
}
if(in_array($radnik, $unosivaci))
{
	echo "<input type='button' onclick='otvori_dodatne_napomen(2);' id='napomena_snimanje_faza' name='napomena_snimanje_faza' style='height:30px; width:200px; font-size:13px; margin:0px;' text-align='center' value='Napomena za sajt dru¹tva' />";	
}
echo "</td>";
echo "</tr>";
//---------------- Marko Markovic kraj -----------------
*/

		/*
// ------------ Marko Markovic 2020-05-28 napomena snimanje -----
echo "<tr>";
echo "<td bgcolor='#2BAEFF'></td>";
echo "<td bgcolor='#2BAEFF'></td>";
echo "<td bgcolor='#B1FEFC'>";
echo "&nbsp;&nbsp;<b>Napomena:</b>\n";
echo "</td>";
echo "<td bgcolor='#B1FEFC' align=\"left\">";
echo "<textarea name=\"napomenaSnimanje\" rows=\"3\" style=\"resize:none;\" cols=\"40\">\n";
echo $napomenaSnimanje;
echo "</textarea>\n";
echo "</td>";
echo "</tr>";
// ----------- Marko Markovic kraj napomena snimanje 2020-05-28 ------
*/
		echo "<tr><td bgcolor=\"#2BAEFF\" class=\"headerPlavo\" colspan='2'><strong>&nbsp;&nbsp;PREVARA</strong></td><td bgcolor=\"#B1FEFC\" style='  border: solid 0em black;' colspan='2'></td></tr>";
		//7 red
		echo "<tr>";

		// Marko Markovic dodato 2 td zbog regresne stete jer je iznad ubacena dodatna napomena za fazu snimanje 2019-12-06 ------
		echo "<td bgcolor='#2BAEFF'>";
		echo "&nbsp;&nbsp;Sumnja na prevaru:";
		echo "</td>";
		echo "<td bgcolor='#2BAEFF'>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DA &nbsp;<input id=\"sumnjaNaPrevaru\" name=\"sumnjaNaPrevaru\" type=\"checkbox\" value=\"false\" >";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Datum:</strong>&nbsp;&nbsp;<input type='text' id='datumPrev' name=\"datumPrev\" value=\"$datumPrev\" size=\"10\" height=\"15\" onclick=\"showCal('datumPrev')\" onkeypress=\"return handleEnter(this, event)\">";
		echo "</td>";

		// Marko Markovic kraj dodataka td -----------

		echo "<td  bgcolor=\"#B1FEFC\" align=\"left\"><b>\n";
		echo "&nbsp;&nbsp;Servis upuæeno:\n";
		echo "</b></td>\n";
		echo "<td bgcolor=\"#B1FEFC\" align=\"left\">\n";

		$sqlSvi = "SELECT *
		   FROM servisi
		   ORDER BY naziv_prikaz";
		$rezultatSvi = pg_query($conn, $sqlSvi);

		$sqlAktivniUpucivanje = "SELECT *
					 	 FROM servisi
						 WHERE status = 'A' AND za_upucivanje = 'DA'
					     ORDER BY naziv_prikaz";

		$rezultatUpucivanje = pg_query($conn, $sqlAktivniUpucivanje);

		echo "<select name='servis_upuceno_id'  onkeypress=\"return handleEnter(this, event)\" class=\"dropdownServisi\">";
		echo "<option value='0' selected='selected'>Izaberite servis</option>";
		//brojac
		$a = 0;
		while ($row = pg_fetch_assoc($rezultatUpucivanje)) {
			if ($row['id'] == $servis_upuceno_id) {
				echo "<option value='" . $row['id'] . "' selected='selected'>" . strtoupper($row['naziv_prikaz']) . "</option>";
				$a = 1;
			} else
				echo "<option value='" . $row['id'] . "' >" . strtoupper($row['naziv_prikaz']) . "</option>";
		}
		//ako servis za izabranu stetu nije medju aktivnim i on se dodaje na listu
		if ($a == 0) {
			while ($row = pg_fetch_assoc($rezultatSvi)) {
				if ($row['id'] == $servis_upuceno_id) {
					echo "<option value='" . $row['id'] . "' selected='selected'>" . strtoupper($row['naziv_prikaz']) . "</option>";
				}
			}
		}
		echo "</select>";
		echo "</td>\n";
		echo "</tr>\n";

		//8 red
		echo "<tr bgcolor='#2BAEFF'><td>&nbsp;&nbsp;O&ccaron;ekivana suma:</td><td><input id='ocekivana_suma' name=\"ocekivana_suma\" type=\"text\" value=\"\" ></td><td bgcolor='#B1FEFC' colspan='2'></td></tr>";
		echo "<tr>";
		echo "<td bgcolor=\"#2BAEFF\">&nbsp;&nbsp;Osumnji&ccaron;eni:</td><td bgcolor=\"#2BAEFF\">Osiguranik&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' class='osumnjiceni' id='osumnjiceni_1' name='osumnjiceni' value='1'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O&#353;te&#263;eni&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' class='osumnjiceni' id='osumnjiceni_2' name='osumnjiceni' value='2'><br/><br/>Voza&#269;(osiguranik)&nbsp;<input type='checkbox' class='osumnjiceni' id='osumnjiceni_4' name='osumnjiceni' value='4'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Voza&#269;(o&#353;te&#263;eni)&nbsp;&nbsp;<input type='checkbox' class='osumnjiceni' id='osumnjiceni_3' name='osumnjiceni' value='3'></td>";
		echo "<td  bgcolor=\"#B1FEFC\" align=\"left\"><b>\n";
		echo "&nbsp;&nbsp;Servis fakturisano:\n";
		echo "</b></td>\n";
		echo "<td bgcolor=\"#B1FEFC\" align=\"left\">\n";

		$sqlAktivniFaktura = "SELECT *
				      FROM servisi
		  			  WHERE status = 'A' AND za_fakturisanje = 'DA'
					  ORDER BY pib,naziv_prikaz";

		$rezultatAktivniFaktura = pg_query($conn, $sqlAktivniFaktura);

		echo "<select name='servis_fakturisano_id'  onkeypress=\"return handleEnter(this, event)\" class=\"dropdownServisi\">";

		echo "<option value='0' selected='selected'>Izaberite servis</option>";
		//brojac
		$a = 0;
		while ($row = pg_fetch_assoc($rezultatAktivniFaktura)) {
			if ($row['id'] == $servis_fakturisano_id) {
				echo "<option value='" . $row['id'] . "' selected='selected'>" . $row['pib'] . " " . strtoupper($row['naziv_prikaz']) . "</option>";
				$a = 1;
			} else
				echo "<option value='" . $row['id'] . "' >" . $row['pib'] . " " . strtoupper($row['naziv_prikaz']) . "</option>";
		}
		$sqlSvi = "SELECT *
		   FROM servisi
		   ORDER BY pib,naziv_prikaz";
		$rezultatSvi = pg_query($conn, $sqlSvi);
		//ako servis za izabranu stetu nije medju aktivnim i on se dodaje na listu
		if ($a == 0) {
			while ($row = pg_fetch_assoc($rezultatSvi)) {
				if ($row['id'] == $servis_fakturisano_id) {
					echo "<option value='" . $row['id'] . "' selected='selected'>" . $row['pib'] . " " . strtoupper($row['naziv_prikaz']) . "</option>";
				}
			}
		}
		echo "</select>";
		echo "<label for='servis_fakturisano_datum' style='margin-left:10px;'>Datum prijema fakture servisa:</label>";
		echo "<input type='text' id='servis_fakturisano_datum' name='servis_fakturisano_datum' value='$servis_fakturisano_datum' style='margin-left:10px;' size='15' height='15' onclick=\"showCal('servis_fakturisano_datum');\" onkeypress='return handleEnter(this, event)'/>";
		echo "</td>\n";
		echo "</tr>";

		//8 red
		echo "<tr>\n";
		echo "<td bgcolor=\"#2BAEFF\">&nbsp;&nbsp;Napomena:</td><td bgcolor=\"#2BAEFF\"><textarea id='Napomena' name='Napomena' rows='3' cols='48'></textarea></td>";
		echo "<td  bgcolor=\"#B1FEFC\" align=\"left\"><b>\n";
		echo "&nbsp;&nbsp;Fotoaparat:\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#B1FEFC\" align=\"left\">\n";

		$sqlFotoaparati = " SELECT id, upper(fotoaparat) AS fotoaparat FROM fotoaparati ORDER BY fotoaparat ASC;";
		$rezultatFotoaparati = pg_query($conn, $sqlFotoaparati);
		echo "<select name=\"fotoaparat\" id=\"fotoaparat\" class=\"dropdownServisi\">\n";
		echo "<option value=\"0\">Izaberite fotoaprat</option>\n";

		while ($rowFotoaparat = pg_fetch_assoc($rezultatFotoaparati)) {
			if ($rowFotoaparat['id'] == $fotoaparat)
				echo "<option value='" . $rowFotoaparat['id'] . "' selected=\"selected\">" . $rowFotoaparat['fotoaparat'] . "</option>\n";
			else
				echo "<option value='" . $rowFotoaparat['id'] . "' >" . $rowFotoaparat['fotoaparat'] . "</option>";
		}
		echo "</select>\n";

		if ($dugme == 'DA')
			echo "<input type=\"submit\" value=\"Galerija fotografija\" class=\"button\" name=\"galerija\" style=\"margin-left:10px;\" id='galerija_dugme'>\n";

		/*
if ($dugme=='DA' && (($vrstaSt=='AO' && ($tipSt=='MS' || $tipSt=='RÃ¯Â¿Å“-MS')) || $vrstaSt=='AK')) {
	echo "<input type=\"submit\" value=\"Unesi Zapisnik\" class=\"button\" name=\"zapisnik\" style=\"margin-left:10px;\">\n";
}
*/
		if ($osnovni_predmet_id_reaktiviranog) {
			$sqlPrethodniZapisnici = "SELECT id FROM predmet_odstetnog_zahteva WHERE (osnovni_predmet_id = $osnovni_predmet_id_reaktiviranog OR id=$osnovni_predmet_id_reaktiviranog)";
			$rezultatPrethodniZapisnici = pg_query($conn, $sqlPrethodniZapisnici);
			$rezultatPrethodni = pg_fetch_all_columns($rezultatPrethodniZapisnici);
			$idZapisnici = implode(",", $rezultatPrethodni);
			$uslov_za_zapisnik = " id_stete IN ($idZapisnici) AND id_stete<=$idstete";
		}
		$idZapisniciNiz = explode(",", $idZapisnici);
		$max_id_stete = max($idZapisniciNiz);

		$upit_poslednji_zapisnik = "SELECT trajno, id_stete from zapisnik_o_ostecenju_vozila where id_stete in ($idZapisnici) order by id desc limit 1 ";
		$rezultat_poslednji_zapisnik = pg_query($conn, $upit_poslednji_zapisnik);
		$niz_poslednji_zapisnik = pg_fetch_assoc($rezultat_poslednji_zapisnik);
		$trajno_poslednji = $niz_poslednji_zapisnik['trajno'];
		$id_stete_poslednji = $niz_poslednji_zapisnik['id_stete'];


		if ($dugme == 'DA' && (($vrstaSt == 'AO' && (substr($sifra, -1) == '2'  || $tipSt == 'R¹-S')) || $vrstaSt == 'AK')) {
			$hidden_zapisnik = ($max_id_stete != $idstete || ($id_stete_poslednji && $id_stete_poslednji != $idstete && $trajno_poslednji == 0)) ? "hidden" : "";

			echo "<input $hidden_zapisnik type=\"submit\" value=\"Unesi Zapisnik\" class=\"button\" name=\"zapisnik\" style=\"margin-left:10px;\">\n";
		}
		/* MARIJA 24.20.2014.*/
		echo "<br/>";
		//uslov ubacen 30.10.2014.
		if ($dugme == 'DA' && (($vrstaSt == 'AO' && (substr($sifra, -1) == '2'  || $tipSt == 'R¹-S')) || $vrstaSt == 'AK')) {
			echo " <input type='file' id='file' name='file' multiple>";
			echo "<input type='button' onclick='upload()' value='Postavi zapisnik'/>";
		}
		echo "<input type='button' style='float:right;margin-right:80px;' class='button' value='Slu¾bena bele¹ka' onclick='otvoriModalZaSluzbenuBelesku(2)'>";

		echo "</td>\n";

		echo "</tr>\n";
		echo "<tr><td bgcolor='#2BAEFF'>&nbsp;&nbsp;Dokazana prevara:</td><td bgcolor='#2BAEFF'>&nbsp;Odustao<input class='prevara' id='prevara_1' name=\"Prevara\" type=\"radio\" value=\"false\" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Odbijen <input class='prevara' id='prevara_2' name=\"Prevara\" type=\"radio\" value=\"false\" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Datum:</strong>&nbsp;&nbsp;<input id='datumPrevare' name=\"datumPrevare\" value=\"$datumDokazPrev\" size=\"10\" height=\"15\" onclick=\"showCal('datumPrevare')\" onkeypress=\"return handleEnter(this, event)\"></td><td  bgcolor=\"#B1FEFC\" colspan='2'></td></tr>";
		echo "<tr><td bgcolor='#2BAEFF'>&nbsp;&nbsp;Nije prevara:</td><td bgcolor='#2BAEFF'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input class='prevara' id='prevara_3' name=\"Prevara\" type=\"radio\" value=\"false\" ></td><td bgcolor=\"#B1FEFC\" colspan='2'></td></tr>";
		echo "<tr><td bgcolor=\"#2BAEFF\">&nbsp;&nbsp;Nedokazana prevara:</td><td bgcolor=\"#2BAEFF\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input class='prevara' id='prevara_4' name=\"Prevara\" type=\"radio\" value=\"false\" ></td><td bgcolor=\"#B1FEFC\" colspan='2'></td></tr>";


		//MARIJA 27.10.2014.
		$sqlPostavljenZapisnik = "SELECT * FROM postavljen_zapisnik WHERE idstete = $idstete";
		$rezultatPostavljenZapisnik = pg_query($conn, $sqlPostavljenZapisnik);
		$rezultat_zapisnik = pg_num_rows($rezultatPostavljenZapisnik);


		//ZAVRSEN DODATAK

		$sqlZapisnik = "SELECT *
				      FROM zapisnik_o_ostecenju_vozila
		  			  WHERE $uslov_za_zapisnik
					  	ORDER BY id,dopunski";

		$rezultatZapisnik = pg_query($conn, $sqlZapisnik);
		$br_rez = pg_num_rows($rezultatZapisnik);

		//MARIJA 28.10.2014.  da li postoji dati zapisnik u tabeli postavljen_zapisnik
		//tj da li za dat idstete postoji postavljen zapisnik u bazi
		$sqlZapisnikPostavljen = "SELECT *
								FROM postavljen_zapisnik
								WHERE idstete = $idstete";

		$rezultatZapisnikPostavljen = pg_query($conn, $sqlZapisnikPostavljen);
		$broj_rezultata_upload_fajlova = pg_num_rows($rezultatZapisnikPostavljen);
		//ZAVRSEN DODATAK


		if ($br_rez) {
			echo "<tr>";
			echo "<td bgcolor=\"#2BAEFF\" colspan='2'></td>";
			echo "<td  bgcolor=\"#B1FEFC\" align=\"left\"><b>\n";
			echo "&nbsp;&nbsp;Izaberite reviziju zapisnika:\n";
			echo "</b></td>\n";

			echo "<td bgcolor=\"#B1FEFC\" align=\"left\">\n";

			$rezultatZapisnik = pg_query($conn, $sqlZapisnik);

			echo "<select name='dopunski' id='dopunski' onkeypress=\"return handleEnter(this, event)\" class=\"dropdownServisi\" onchange='prikazi_dugme_za_vracanje_u_privremen();naziv_fajla_upload_hidden_fun();'>";

			echo "<option value='-1' selected='selected'>Izaberite zapisnik</option>";
			//MARIJA 28.10.2014
			echo "<optgroup label='Zapisnici koji su kreirani iz aplikacije'>";
			//brojac
			while ($row = pg_fetch_assoc($rezultatZapisnik)) {
				$datum_zap = substr($row['datum_vreme'], 0, 10);
				$datum_zapisnika_baza = explode('-', $datum_zap);
				$datum_zapisnika = $datum_zapisnika_baza[2] . "." . $datum_zapisnika_baza[1] . "." . $datum_zapisnika_baza[0];

				$dopunski = $row['dopunski'];
				$id_predmet_zapisnik = $row['id_stete'];

				if ($dopunski != 0) {
					$naziv = "Zapisnik " . "dopunski br." . $dopunski . " datum:" . $datum_zapisnika;
				} else {
					$naziv = "Zapisnik datum: " . $datum_zapisnika;
				}

				// Upit za ko je uradio zapisnik
				$sqlUradioZapisnik = "SELECT *
													FROM unosivaci
													WHERE sifra = " . $row['procenitelj_uradio'] . ";";
				$rezultatUradioZapisnik = pg_query($conn_zabrane, $sqlUradioZapisnik);
				$nizUradioZapisnik = pg_fetch_array($rezultatUradioZapisnik);
				$imeUradioZapisnik = $nizUradioZapisnik['ime'];
				// Dodati ko je uradio Zapisnik
				$naziv .= " ($imeUradioZapisnik) ";
				// Dodati ko je uradio Zapisnik
				if ($row['trajno'] == 0)
					$naziv .= " (Nije zavr¹en)";

				if ($row['trajno'] == 0) {
					echo "<option value='" . $row['dopunski'] . "_" . $row['trajno'] . "_" . $id_predmet_zapisnik . "' selected='selected'>" . $naziv . "</option>";
				} else if ($row['dopunski'] == $dopunski) {
					echo "<option value='" . $row['dopunski'] . "_" . $id_predmet_zapisnik . "' selected='selected'>" . $naziv . "</option>";
				} else
					echo "<option value='" . $row['dopunski'] . "_" . $id_predmet_zapisnik . "' >" . $naziv . "</option>";
			}


			echo "</optgroup>";

			//MARIJA 28.10.2014.
			if (pg_num_rows($rezultatPostavljenZapisnik)) {
				echo "<optgroup label='Zapisnici koji su postavljeni (skenirani)'>";;
			}
			//ZAVRSEN KOD
			//MARIJA 27.10.2014.
			$naziv_fajla = '';
			while ($row = pg_fetch_assoc($rezultatPostavljenZapisnik)) {
				$naziv_fajla = $row['naziv_fajla'];
				$datum_postavljanja_zapisnika = $row['datum'];
				$datum_postavljanja_zapisnika_baza = explode('-', $datum_postavljanja_zapisnika);
				$datum_postavljanja_zapisnika = $datum_postavljanja_zapisnika_baza[2] . "." . $datum_postavljanja_zapisnika_baza[1] . "." . $datum_postavljanja_zapisnika_baza[0];
				$sqlPostavioZapisnik = "SELECT *
													FROM unosivaci
													WHERE sifra = " . $row['radnik'] . ";";
				$rezultatPostavioZapisnik = pg_query($conn_zabrane, $sqlPostavioZapisnik);
				$nizPostavioZapisnik = pg_fetch_array($rezultatPostavioZapisnik);
				$imePostavioZapisnik = $nizPostavioZapisnik['ime'];
				echo "<option value='" . $row['id'] . "' name='$naziv_fajla'>" . $naziv_fajla . ": " . $datum_postavljanja_zapisnika . " (" . $imePostavioZapisnik . ")</option>";
			}
			echo "</optgroup>";
			echo "</select>";
			//echo "</td>\n";
			echo "<input type='button' name='otvori_zapisnik' value='Otvori zapisnik' onclick='otvoriZapisnik()'/>";
			//Otkljuèavanje zapisnka dozvoljeno radnicima : Sa¹i Mandiæu, Simoviæ ®ikici i Braunoviæ Darku
			if ($radnik == 151 || $radnik == 138 || $radnik == 3029 || $radnik == 3033 || $radnik == 3035) {
				echo "<input type='button' style='visibility:hidden' name='obrisi_zapisnik' id='obrisi_zapisnik' value='Obri¹i zapisnik' onclick='otkljucajObrisiZapisnik(this.id)'/><input type='button' style='visibility:hidden' name='otkljucaj_zapisnik' id='otkljucaj_zapisnik' value='Otkljuèaj zapisnik' onclick='otkljucajObrisiZapisnik(this.id)'/>";
			}

			//Nemanja Jovanovic 2018-12-12
			$array_procena = array(151, 138, 3029, 3033, 3040, 3078, 3072, 3081, 3093, 3038, 3079, 2253, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25);
			if (in_array($radnik, $array_procena)) {
				echo "<input type='button' value='Pripremi email' class='button' id='pripremi_button' onClick='posalji_mail_procena();'>";
			}

			//MARIJA 28.10.2014.
			echo "<input type='hidden' id='naziv_fajla_upload_hidden' name='naziv_fajla_upload_hidden' value='' readonly='readonly'>";
			echo "<a id='adresa' href='#'  onclick='vrati_adresu()' target=_new></a></td>";
			echo "</tr>";
		}
		//MARIJA 28.10.2014. DODATO DA BI SE POJAVLJIVAO COMBO BOX I KADA NIJE KREIRAN ZAPISNIK
		else if ($broj_rezultata_upload_fajlova) {
			echo "<tr>";
			echo "<td bgcolor=\"#2BAEFF\" colspan='2'></td>";
			echo "<td  bgcolor=\"#B1FEFC\" align=\"left\"><b>\n";
			echo "&nbsp;&nbsp;Izaberite reviziju zapisnika:\n";
			echo "</b></td>\n";

			echo "<td bgcolor=\"#B1FEFC\" align=\"left\">\n";
			echo "<select name='dopunski' id='dopunski' onkeypress=\"return handleEnter(this, event)\" class=\"dropdownServisi\" onChange='naziv_fajla_upload_hidden_fun();'>";
			echo "<option value='-1' selected='selected'>Izaberite zapisnik</option>";

			$naziv_fajla = '';
			echo "<optgroup label='Zapisnici koji su postavljeni'>";
			while ($row = pg_fetch_assoc($rezultatPostavljenZapisnik)) {
				$naziv_fajla = $row['naziv_fajla'];
				echo "<option value='" . $row['id'] . "' name='$naziv_fajla'>" . $naziv_fajla . "</option>";
			}

			echo "</optgroup>";
			echo "</select>";
			if ($radnik == 151 || $radnik == 138 || $radnik == 3029 || $radnik == 3033 || $radnik == 3035) {
				echo "<input type='button' style='' name='obrisi_zapisnik' id='obrisi_zapisnik' value='Obri¹i zapisnik' onclick='otkljucajObrisiZapisnik(this.id)'/>";
			}
			echo "<input type='button' name='otvori_zapisnik' value='Otvori zapisnik' onclick='otvoriZapisnik()'/>";
			echo "<input type='hidden' id='naziv_fajla_upload_hidden' name='naziv_fajla_upload_hidden' value='' readonly='readonly'>";
			echo "<a id='adresa' href='#'  onclick='vrati_adresu()' target=_new></a></td>";
			echo "</tr>";
			echo "<tr><td bgcolor=\"#2BAEFF\" colspan='2'></td><td bgcolor=\"#B1FEFC\">&nbsp;&nbsp;Napomena:</td><td bgcolor=\"#B1FEFC\"><textarea name='Napomena' row='4' col='50'></textarea></td></tr>";
		}

		//ZAVRSEN DAODATAK
		?>
		<!-- MARIJA 28.10.2014. dodat jq za promenuu vrednosti u text box kada se menja vresdnost select boxa -->
		<script>
			//Izmeni Nemanja JOvanovic


			/*
			$(document).ready(function() {
					$("#dopunski").val($("#naziv_fajla_upload_hidden").val());
				  $("#dopunski").on("change", function() {
					  $("#naziv_fajla_upload_hidden").val($(this).find("option:selected").attr("name"));
					  
				    });
				    $('#dopunski option[value=-1]').attr('selected','selected').change();
				 });*/
		</script>
		<!-- zavrsetak dodatka -->

		<!-- MARIJA 13.12.2014.- POÈETAK -->
		<script>
			$('#osteceni_zemlja_id').on("change", function() {
				var value = $(this).find('option:selected').val();
				if (value != 199) {
					$("#osteceni_opstina_id").val('-1');
					$("#osteceni_opstina_id").attr("disabled", "disabled");
					$("#osteceni_mesto_id").val('-1');
					$("#osteceni_mesto_id").hide();
					$("#osteceni_mesto_opis").val('');
					$("#osteceni_mesto_opis").show();
					return false;
				} else if (value == 199) {
					$("#osteceni_opstina_id").removeAttr("disabled");
					$("#osteceni_mesto_id").show();
					$("#osteceni_mesto_opis").val('');
					$("#osteceni_mesto_opis").hide();
					return false;
				}
			});

			$('#osiguranik_krivac_zemlja_id').on("change", function() {
				var value = $(this).find('option:selected').val();
				if (value != 199) {
					$("#osiguranik_krivac_opstina_id").val('-1');
					$("#osiguranik_krivac_opstina_id").attr("disabled", "disabled");
					$("#osiguranik_krivac_mesto_id").val('-1');
					$("#osiguranik_krivac_mesto_id").hide();
					$("#osiguranik_krivac_mesto_opis").val('');
					$("#osiguranik_krivac_mesto_opis").show();
					return false;
				} else if (value == 199) {
					$("#osiguranik_krivac_opstina_id").removeAttr("disabled");
					$("#osiguranik_krivac_mesto_id").show();
					$("#osiguranik_krivac_mesto_opis").val('');
					$("#osiguranik_krivac_mesto_opis").hide();
					return false;
				}
			});

			$('#vozac_zemlja_id').on("change", function() {
				var value = $(this).find('option:selected').val();
				if (value != 199) {
					$("#vozac_opstina_id").val('-1');
					$("#vozac_opstina_id").attr("disabled", "disabled");
					$("#vozac_mesto_id").val('-1');
					$("#vozac_mesto_id").hide();
					$("#vozac_mesto_opis").val('');
					$("#vozac_mesto_opis").show();
					return false;
				} else if (value == 199) {
					$("#vozac_opstina_id").removeAttr("disabled");
					$("#vozac_mesto_id").show();
					$("#vozac_mesto_opis").val('');
					$("#vozac_mesto_opis").hide();
					return false;
				}
			});

			$('#vozac_krivac_zemlja_id').on("change", function() {
				var value = $(this).find('option:selected').val();
				if (value != 199) {
					$("#vozac_krivac_opstina_id").val('-1');
					$("#vozac_krivac_opstina_id").attr("disabled", "disabled");
					$("#vozac_krivac_mesto_id").val('-1');
					$("#vozac_krivac_mesto_id").hide();
					$("#vozac_krivac_mesto_opis").val('');
					$("#vozac_krivac_mesto_opis").show();
					return false;
				} else if (value == 199) {
					$("#vozac_krivac_opstina_id").removeAttr("disabled");
					$("#vozac_krivac_mesto_id").show();
					$("#vozac_krivac_mesto_opis").val('');
					$("#vozac_krivac_mesto_opis").hide();
					return false;
				}
			});

			$('#osteceni_opstina_id').on("change", function() {
				var value = $(this).find('option:selected').val();
				if (value != -1) {
					$("#osteceni_mesto_id").removeAttr("disabled");
					$("#osteceni_mesto_id").removeClass("disabled");
					return false;
				}
			});

			$('#vozac_opstina_id').on("change", function() {
				var value = $(this).find('option:selected').val();
				if (value != -1) {
					$("#vozac_mesto_id").removeAttr("disabled");
					$("#vozac_mesto_id").removeClass("disabled");
					return false;
				}
			});

			$('#osiguranik_krivac_opstina_id').on("change", function() {
				var value = $(this).find('option:selected').val();
				if (value != -1) {
					$("#osiguranik_krivac_mesto_id").removeAttr("disabled");
					$("#osiguranik_krivac_mesto_id").removeClass("disabled");
					return false;
				}
			});

			$('#vozac_krivac_opstina_id').on("change", function() {
				var value = $(this).find('option:selected').val();
				if (value != -1) {
					$("#vozac_krivac_mesto_id").removeAttr("disabled");
					$("#vozac_krivac_mesto_id").removeClass("disabled");
					return false;
				}
			});
		</script>
		<!-- MARIJA 13.12.2014.- KRAJ -->

		<?php
		//9 red

		echo "<tr>";
		echo "<td bgcolor=\"#2BAEFF\" colspan='2'>&nbsp;&nbsp;Nastaviti saradnju?&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NE&nbsp;<input id='nastaviti_saradnju' name=\"nastaviti_saradnju\" type=\"checkbox\" value=\"false\"></td>";
		echo "<td bgcolor=\"#B1FEFC\" align=\"left\"><b>\n";
		echo "&nbsp;&nbsp;Teren:\n";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"teren\" type=\"checkbox\" value=\"true\" ";
		if ($teren == true) {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#B1FEFC\">\n";

		if ($vrstaSt == 'DPZ' || $vrstaSt == 'N' || ($vrstaSt == 'AO' && substr($sifra, -1) == '1')) {
			//echo "<input type='button' value='Kreiraj lekarski nalaz' onclick='idi_na_lekarskI_nalaz($idstete);'>\n";
			echo "<input type='submit' value='Kreiraj lekarski nalaz' id='lekarski_nalaz' name='lekarski_nalaz'>\n";
			//06.02.2015 BRANKA - Dodato da bi se na pregledu prikazivali uradjeni lekarski nalazi
			$sql_lekarski_nalazi = "select * from lekarski_nalazi where predmet_odstetnog_zahteva_id=$idstete ORDER BY id DESC LIMIT 1 ";
			$result_lekarski_nalazi = pg_query($conn, $sql_lekarski_nalazi);
			$niz_lekarski_nalazi = pg_fetch_all($result_lekarski_nalazi);
			if ($niz_lekarski_nalazi) {
				echo "<select id='lekarski_nalazi' style='width:190px' name='lekarski_nalazi'  >\n";
				echo " <option value='-1'>Izaberite lekarski nalaz</option>";

				for ($n = 0; $n < count($niz_lekarski_nalazi); $n++) {
					$lekarski_nalaz_id = $niz_lekarski_nalazi[$n]['id'];
					$lekarski_nalaz_datum = $niz_lekarski_nalazi[$n]['datum_lekarskog_nalaza'];
					$lekarski_nalaz_datum = date("d.m.Y", strtotime($lekarski_nalaz_datum));
					$vreme = substr($niz_lekarski_nalazi[$n]['vreme'], 0, 5);

					echo "<option value='" . $lekarski_nalaz_id . "'";
					echo "> Lekarski nalaz datum: $lekarski_nalaz_datum, $vreme </option>";
				}

				echo "</select>\n";
				echo "<input type='button' value='©tampaj' onclick='stampaj_lekarski_nalaz();'>\n";
			}
		}
		// if($podaci_prigovori)
		// {

		if ($radnik == 151 || $radnik == 138 || $radnik == 3068 || $radnik == 3044 || $radnik == 3042 || $radnik == 3067 || $radnik == 124 || $radnik == 3045 || $radnik == 3054 || $radnik == 3061 || $radnik == 3004 || $radnik == 3039 || $radnik == 3036 || $radnik == 3023  ||  $radnik == 122 ||  $radnik == 2059 || $radnik == 3071 || $radnik == 3064 || $radnik == 3029 || $radnik == 3033 || $radnik == 3072 || $radnik == 3072 || $radnik == 3040 || $radnik == 3035 || $radnik == 3048 || $radnik == 3048 || $radnik == 3071 || $radnik == 3074 || $radnik == 3064 || $radnik == 3085 || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125 || $radnik == 2249) {
			echo "<input type='submit' id='dugme_odluka_likvidacija' name='dugme_odluka_likvidacija' value='Odluka komisije' >";
		}
		//}
		echo "\n";
		echo "</td>\n";

		echo "</tr>\n";
		echo "<tr>";
		echo "<td bgcolor=\"#2BAEFF\" >";

		$sql_osnovni = "SELECT
							sd.id
						FROM 
							stetni_dogadjaj AS sd
								INNER JOIN odstetni_zahtev 		AS oz 	ON (sd.id = oz.stetni_dogadjaj_id)
								INNER JOIN predmet_odstetnog_zahteva 	AS poz	ON (oz.id = poz.odstetni_zahtev_id)
						WHERE
							poz.id = $idstete";

		$upit_predmet 		= pg_query($conn, $sql_osnovni);
		$rezultat_predmet 	= pg_fetch_assoc($upit_predmet);
		$stetni_id 			= $rezultat_predmet['id'];

		$sql_sudski = "	SELECT
							textcat_all(poz.id::text) AS poz_id_niz,
							textcat_all(poz.novi_broj_predmeta::text) AS novi_broj_predmeta_niz,
							textcat_all(sp.idsp::text) AS sudski_postupak_id,
							textcat_all(sp.brsp::text) AS broj_sudskog_postupka
						FROM 
							stetni_dogadjaj AS sd
								INNER JOIN odstetni_zahtev 				AS oz 	ON (sd.id = oz.stetni_dogadjaj_id)
								INNER JOIN predmet_odstetnog_zahteva 	AS poz	ON (oz.id = poz.odstetni_zahtev_id)
								LEFT OUTER JOIN sudski_postupak			AS sp 	ON (sp.idsp = poz.sudski_postupak_id)
						WHERE
							sd.id = $stetni_id"; 

		$upit_sudski 					= pg_query($conn, $sql_sudski);
		$rezultat_sudski 				= pg_fetch_assoc($upit_sudski);
		$sudski_postupak_id_beleska 	= $rezultat_sudski['sudski_postupak_id'];
		$broj_sudskog_postupka_beleska 	= $rezultat_sudski['broj_sudskog_postupka'];
		
		if ($sudski_postupak_id_beleska != '')
		{
			echo '&nbsp;&nbsp;Broj sudskog postupka:';
		}
		echo "</td>";
		echo '<td bgcolor="#2BAEFF">';
		if ($sudski_postupak_id_beleska != '')
			echo '<a target="_blank" style="font-size:16px;" href="../evidencije/pravna/sudski_ispravka_novo.php?idsp=' . $sudski_postupak_id_beleska . '&dugme=DA"><b>' . $broj_sudskog_postupka_beleska . '</b></a>';
		if (!empty($rezultat_dozvola)) {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' class='button' value='Snimi' onclick='snimiPrevaru()'>";
			echo "<input type='button' style='float:right;margin-right:80px;' class='button' value='Slu¾bena bele¹ka' onclick='otvoriModalZaSluzbenuBelesku(1)'>";
		}
		echo "</td><td colspan='2' bgcolor=\"#B1FEFC\"></td>";
		echo "</tr>";
        echo "<input id='broj_sudskog_postupka_beleska' name='broj_sudskog_postupka_beleska' type='hidden' value='".$broj_sudskog_postupka_beleska."'>";
		// Marko Markovic 2020-05-13 ako je IO dragaciji prikaz na formi Nema evropskog izvestaja a dodaju se zapisnici
		if ($vrstaSt == 'IO') {
			prikazi_deo_za_procenitelje_snimanje_io($imao_policijski_zapisnik, $zapisnici_io);
		} else {
			prikazi_deo_za_procenitelje_snimanje($imao_policijski_zapisnik, $imao_evropski_izvestaj, $izvrsio_uporedjivanje_vozila, $slikao_drugo_vozilo_odvojeno, $slikao_gde, $slikao_kada, $slikao_vreme, $stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen);
		}
		// Marko Markovic kraj 2020-05-13

		echo "<tr><td colspan=\"2\" class=\"footerPlavo\"></td><td colspan=\"2\" class=\"footerZeleno\"></td></tr>";
		echo "</table>\n";

		//Kraj za procenitelje (Rad na daljinu) -- Nemanja Jovanovic 26-02-2020

		echo "<hr color=\"#000000\" class='hr_presek'>\n";
		//********************************** 6 red TABELA 6   ****************************************************
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" id='finansijsko_pravni_osnov'>\n";
		echo "<tr bgcolor=\"#CCCCCC\">\n";

		echo "<td colspan=\"4\" class=\"headerSivo\" > \n";
		echo "Dana:&nbsp; \n";
		echo "<input name=\"dana\" value=\"$dana\" size=\"15\" height=\"15\" onclick=\"showCal('dana')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;predato na dalju obradu\n";
		echo "</td>\n";

		//MARIJA 19.02.2015 - imenjeno zbog dodatog td
		$colspan_AK_kf_osnov_prvo = ($vrstaSt == 'AK') ? 5 : 7;
		echo "<td colspan='$colspan_AK_kf_osnov_prvo' class=\"headerSivo\" > \n";
		//echo "<td colspan=\"6\" class=\"headerSivo\" > \n";
		echo "Datum prijema predmeta u pravnu slu¾bu:&nbsp; \n";
		echo "<input name=\"datumPrijemaPredmetaPravnaSluzba\" value=\"$datumPrijemaPredmetaPravnaSluzba\" maxlength=\"10\" size=\"15\" height=\"15\" title=\"Datum kada je obraðivaè (pravnik) primio predmet na ocenu pravnog osnova\" onclick=\"showCal('datumPrijemaPredmetaPravnaSluzba')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "</tr>\n";

		echo "<tr>\n";
		if ($vrstaSt == 'AK') {
			echo "<td colspan=\"4\" bgcolor=\"#FFA56F\" class=\"headerNarandza\">\n";
			echo "<strong><font size=\"4\">\n";
			echo "&nbsp;KOMERCIJALNO-FINANSIJSKI OSNOV\n";
			echo "</font></strong>\n&nbsp;&nbsp;&nbsp;<b><font size=\"5\">II</font></b>";
			echo "</td>\n";
		}
		$colspan_AK_kf_osnov = ($vrstaSt == 'AK') ? 2 : 6;
		// MARIJA 19.02.2015 - izmenjeno posto je dodatajos jedna kolona kako bi se postavio select box za izvestaje
		echo "<td bgcolor=\"#2BAEFF\" colspan='" . ($colspan_AK_kf_osnov + 1) . "' class=\"headerPlavo\">\n"; // Za sve AK stete
		echo "<strong><font size=\"4\">\n";
		echo "PRAVNI OSNOV\n";
		echo "</font></strong>\n &nbsp;&nbsp;&nbsp;<b><font size=\"5\">III</font></b>";
		echo "</td>\n";

		echo "<td colspan=\"2\" bgcolor=\"#2BAEFF\"  class=\"headerPlavo\">\n";
		echo "<strong>\n";
		echo "Regresno potra¾ivanje\n";
		echo "</strong>\n";
		echo "</td>\n";
		echo "</tr>\n";

		// upit kojim se izvlace svi podaci iz tabele pravni
		$sql_postoji_pravni = " SELECT * FROM pravni WHERE idstete = $idstete ";
		$upit_postoji_pravni = pg_query($conn, $sql_postoji_pravni);
		$niz_postoji_pravni = pg_fetch_assoc($upit_postoji_pravni);
		$postoji_regres_od = $niz_postoji_pravni['regres_od'];
		$postoji_potvrda_tf = $niz_postoji_pravni['potvrdjen_osnov_za_regres'];
		$sirina_AK = ($vrstaSt == 'AK') ? '140px' : '200px';

		// MARIJA REGRES - 27.02.2015 - dodavanje jednog reda u kom se postavlja select box POCETAK
		$regres_od_lice = array(0 => 'Izaberite', 1 => 'Krivac vlasnik vozila', 2 => 'Krivac vozaè vozila', 3 => 'Osiguravajuæe dru¹tvo', 4 => 'Ostalo');

		// // dodato za izvlacenje podataka regresni broj i datum otvaranja regresa
		// $podaci_otvoren_regres = vrati_podatke_otvorenog_regresa($conn,$idstete);
		// $regresni_broj = $podaci_otvoren_regres['brreg'];
		// $datum_otvaranja_regresa =  $podaci_otvoren_regres['datum_upisa'];

		// MARIJA 18.03.2015 - dodata select lista za razlog umanjenja - POCETAK - ispravnljeno 02.04.2015.
		echo "<tr >";

		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\" colspan = '4'>&nbsp;&nbsp;</td>\n";
		}
		echo "<td bgcolor=\"#2BAEFF\" colspan='$colspan_AK_kf_osnov'/>";
		echo "</td>";

		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "<b>Regresni broj:</b>\n";
		echo "</br><input name=\"oznakaRegPotr\" id='oznakaRegPotr' value=\"$regresni_broj\" maxlength=\"15\" class='disabled' style=\"width: $sirina_AK;\" onkeypress=\"return handleEnter(this, event)\" readonly>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		$sirina_AK_nap_dugme = ($vrstaSt == 'AK') ? '210px' : '300px';
		echo "<td bgcolor=\"#2BAEFF\" colspan='2'/>";
		echo "Razlog regresa: ";
		echo "</br>";
		// MARIJA 03.04.2015 - dodato da bi se postavilo na polja koja se odnose na regresno potrazivanje i postavljapolja kao disabled i readonly
		//$regres_disabled = ($postoji_potvrda_tf == 't') ? "class='disabled'" : '';
		//$regres_readonly = ($postoji_potvrda_tf == 't') ? "readonly" : '';

		$sql_razlog_regresa = "SELECT * FROM sifarnici.razlog_regresa ORDER BY id";
		$result_razlog_regresa = pg_query($conn, $sql_razlog_regresa);

		$razlog_regresa_id_postoji = $niz_postoji_pravni['razlog_regresa_id'];
		echo "<select width='80px' class=\"disabled\"  style='font-size:12px; width:$sirina_AK_nap_dugme;' onkeypress='return handleEnter(this, event)' id='razlog_regresa_id' name='razlog_regresa_id' $regres_disabled  $regres_readonly>";
		echo "<option value = '-1'>Izaberite razlog regresa</option>";
		while ($niz_razlog_regresa = pg_fetch_assoc($result_razlog_regresa)) {
			// ukoliko je razlog regresa jednak slektovanom i ukoliko selektovan nije isti kao onaj sto je snimljen u bazi, neka taj ostane selektovan prilikom refresh strane
			if ($razlog_regresa_id == $niz_razlog_regresa['id'] && $razlog_regresa_id != $razlog_regresa_id_postoji) {
				echo "<option value='$razlog_regresa_id' selected=\"selected\">" . $niz_razlog_regresa['kratak_opis'] . "</option>\n";
			} else {
				$selected = ($razlog_regresa_id_postoji == $niz_razlog_regresa['id']) ? "selected='selected'" : "";
				echo "<option value='" . $niz_razlog_regresa['id'] . "' $selected>" . $niz_razlog_regresa['kratak_opis'] . "</option>";
			}
		}
		echo "</select>";
		echo "</td>";

		echo "</tr>";
		// MARIJA 18.03.2015 - dodata select lista za razlog umanjenja - KRAJ - ispravnljeno 02.04.2015.


		echo "<tr >";

		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\" colspan = '4'>&nbsp;&nbsp;</td>\n";
		}


		$obrazlozenje_osnov 	= '';
        $obrazlozenje 			= "SELECT pravni_osnov_obrazlozenje FROM utvrdjivanje_pravnog_osnova_odstetnog_zahteva where idstete =$idstete order by id desc LIMIT 1";
        $obrazlozenje_query 	= pg_query($conn, $obrazlozenje);
        $obrazlozenje_rezultat 	= pg_fetch_assoc($obrazlozenje_query);
		$obrazlozenje_osnov 	= $obrazlozenje_rezultat['pravni_osnov_obrazlozenje'];
		if($colspan_AK_kf_osnov==2)
		{
		 echo "<td bgcolor=\"#2BAEFF\" colspan='1'/>";
         echo "<div style='float:right; margin-left: 5px;'"; 
		 echo "<label>Pravni osnov - obrazlo¾enje:</label>"; 
         echo "<textarea name=\"obrazlozenje\" id=\"obrazlozenje\" rows=\"4\" cols=\"40\" style=\"resize:none;width: 450px;\" readonly>$obrazlozenje_osnov</textarea>\n";
		 echo "</div>";
		 echo "</td>";
		 echo "<td bgcolor=\"#2BAEFF\" colspan='1'/>";
		 echo "</td>";
		}
		if($colspan_AK_kf_osnov==6){
		 echo "<td bgcolor=\"#2BAEFF\"/>";
		 echo "</td>";
		 echo "<td bgcolor=\"#2BAEFF\"/>";
         echo "<div style='float:left;'"; 
		 echo "<label>Pravni osnov - obrazlo¾enje:</label>"; 
         echo "<textarea name=\"obrazlozenje\" id=\"obrazlozenje\" rows=\"4\" cols=\"40\" style=\"resize:none;width: 250px;\" readonly>$obrazlozenje_osnov</textarea>\n";
		 echo "</div>";
		 echo "</td>";
		 echo "<td bgcolor=\"#2BAEFF\" colspan='4'/>";
		 echo "</td>";
		}
		// MARIJA 02.04.2015 - POCETAK
		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "<b>Datum otvaranja regresa:</b>\n";
		echo "<input name='datum_otvaranja_regresa' id='datum_otvaranja_regresa' value='$datum_otvaranja_regresa' maxlength=\"15\" class='disabled' style=\"width: $sirina_AK;\" onkeypress=\"return handleEnter(this, event)\" readonly>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		// MARIJA 02.04.2015 - KRAJ

		//IZMENIO VLADA
		echo "<td bgcolor=\"#2BAEFF\" width=\"200\" />";
		echo "<label>Regres od:</label><br>";

		echo "<select width='80px' style='font-size:12px; width: 200px;' onkeypress='return handleEnter(this, event)' id='regres_od' class='disable_selekti' name='regres_od' onchange='promena_input_polja(this.value)' $regres_disabled $regres_readonly $disable_selekti>";


		foreach ($regres_od_lice as $reges_lice) {
			if ($regres_od == $reges_lice && ($postoji_regres_od == null || $postoji_regres_od != $regres_od)) {
				echo "<option value='$reges_lice' selected=\"selected\">" . $reges_lice . "</option>\n";
			} else {
				$selected = ($postoji_regres_od == $reges_lice) ? "selected='selected'" : "";
				echo "<option value='$reges_lice' $selected>$reges_lice</option>";
			}
		}
		echo "</select>";
		echo "</td>";

		//DODAO VLADA ZA RAZLICITE SIRINE INPUT I SELECT POLJA
		$sirina_polja = ($vrstaSt == 'AK') ? '180px' : '200px';


		//PREMESTIO VLADA
		echo "<td width=\"220\" bgcolor=\"#2BAEFF\">\n";
		echo "<label>Vrsta:</label><br>";
		echo "<input name=\"vrstaRegPotr\" id='vrstaRegPotr' value=\"$vrstaRegPotr\" maxlength=\"10\" style='width : $sirina_polja;' onkeypress=\"return handleEnter(this, event)\" $regres_disabled $regres_readonly readonly class=\"disabled\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "</tr>";

		//PREMESTIO VLADA

		// MARIJA REGRES - 27.02.2015 - dodavanje jednog reda u kom se postavlja select box KRAJ

		echo "<input type='hidden' id='osiguravajuca_drustva_hidden' name='osiguravajuca_drustva_hidden' value='$osiguravajuce_drustvo_id'>";

		// MARIJA 03.03.2015 - polja koja su potreba za prikaz u delu za regesno potrazivanje i odnose se na lica, smestamo ih u hiddden polja kako bi ih pozvali u okviru javascript - POCETAK
		echo "<tr>\n";
		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\" >&nbsp;&nbsp;</td>\n";
			echo "<td bgcolor=\"#FFA56F\" >DA&nbsp;&nbsp;&nbsp;&nbsp;NE</td>\n";
			echo "<td bgcolor=\"#FFA56F\" >&nbsp;&nbsp;</td>\n";
			echo "<td bgcolor=\"#FFA56F\" >DA&nbsp;&nbsp;&nbsp;&nbsp;NE</td>\n";
		}

		//IZMENIO VLADA
		echo "<td class=\"dodaj_padding\" bgcolor=\"#2BAEFF\" width=\"200\" colspan=\"1\">\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"osnovan\" id=\"osnovan_ceo\" value=\"C\"";   // Marko Markovic 2020-04-29 dodat id='osnovan_ceo'
		if ($osnovan == "C") {
			echo " checked ";
		}
		/* Branka - 2014-11-07 Dopuna za odbijenice PO?ETAK     - dodato da mo???e da kreira odbijenice i za IO */
		$odbijenica_javascript_toggle = (in_array($vrstaSt, array('AO', 'AK', 'N', 'IO', 'DPZ'))) ? "onclick='toggle_dugme_resenje_odbijen(this.id)'" : "";
		/* Branka - 2014-11-07 Dopuna za odbijenice KRAJ*/
		echo "onkeypress=\"return handleEnter(this, event)\" $odbijenica_javascript_toggle >\n";
		echo "Osnovan u celosti";
		echo "</td>\n";

		$sakrij_osiguranje = ($regres_od == 'Osiguravajuæe dru¹tvo') ? 'hidden' : '';
		$prikazi_osiguranje = ($regres_od != 'Osiguravajuæe dru¹tvo') ? 'hidden' : '';

		//VLADA IZMENIO COLSPAN
		if ($vrstaSt == 'AK') {

			echo "<td bgcolor=\"#2BAEFF\" colspan=\"1\">";
			echo "</td>";
		}

		//AKO JE UNET TIP LICA
		if ($tip_lica != '') {

			//PROVERA DA LI JE LICE FIZICKO ILI PRAVNO
			$fizicko_check = ($tip_lica == 'F') ? 'checked="true"' : '';
			$pravno_check = ($tip_lica == 'P') ? 'checked="true"' : '';
		}

		//VLADA IZMENIO COLSPAN
		if ($vrstaSt != 'AK') {

			echo "<td bgcolor=\"#2BAEFF\" colspan=\"5\">";
			echo "</td>";
		}

		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "<label>Tip lica</label><br>";
		echo "<label for=\"tip_lica\">Fizièko</label>";
		echo "<input type=\"radio\" id=\"tip_lica\" name=\"tip_lica\" value=\"fizicko\" $fizicko_check>";
		echo "<label for=\"tip_lica\">Pravno</label>";
		echo "<input type=\"radio\" id=\"tip_lica1\" name=\"tip_lica\" value=\"pravno\" $pravno_check>";
		echo "</td>\n";

		echo "<td id=\"polje_ime_duznika\" class=\"dodaj_padding\" bgcolor=\"#2BAEFF\" $sakrij_osiguranje>\n";
		echo "<label class=\"izmeni_tekst\">Ime regresnog du¾nika</label>";
		echo "<input name=\"ime_duznika\" id=\"ime_duznika\" value=\"$ime_reg\" style=\"width: 200px;\" onkeypress=\"return handleEnter(this, event)\" $regres_disabled $disable_ime>";
		echo "</td>\n";

		//DODAO VLADA
		echo "<td id=\"polje_prezime_duznika\" class=\"dodaj_padding\" bgcolor=\"#2BAEFF\" $sakrij_osiguranje>\n";
		echo "<label>Prezime regresnog du¾nika</label>";
		echo "<input name=\"prezime_duznika\" id=\"prezime_duznika\" value=\"$prezime_reg\" style=\"width: 200px;\" onkeypress=\"return handleEnter(this, event)\" $regres_disabled $disable_prezime>";
		echo "</td>\n";


		//MARIJA 02.03.2015 - dodato za kreiranje liste osig drustva - POCETAK
		$sql_osiguravajuca_drustva = " SELECT DISTINCT strano_drustvo FROM sifarnici.osiguravajuca_drustva ";
		$result_osiguravajuca_drustva = pg_query($conn, $sql_osiguravajuca_drustva);
		$niz_osiguravajuca_drustva = pg_fetch_all($result_osiguravajuca_drustva);


		// upit da se izvuce iz pravni
		$osiguravajuce_drustvo_id_postoji = $niz_postoji_pravni['osiguravajuce_drustvo_id'];

		//$osig_drustvo_disabled = ($postoji_potvrda_tf == 't') ? "class='disabled'" : '';
		//$osig_drustvo_readonly = ($postoji_potvrda_tf == 't') ? "readonly" : '';


		//DODAO VLADA KLASU PADDING
		echo "<td id=\"select_osig_drustvo\" class=\"dodaj_padding\" bgcolor=\"#2BAEFF\" width=\"200\" $prikazi_osiguranje/>";

		//PREMESTIO VLADA
		echo "<label>Osiguravajuæe dru¹tvo:</label><br>";
		echo "<select width='80px' style='font-size:12px; width: 200px;' onkeypress='return handleEnter(this, event)' id='osiguravajuce_drustvo_id' class='disable_selekti' name='osiguravajuce_drustvo_id' $osig_drustvo_disabled $osig_drustvo_readonly onchange='prikazi_podatke_osiguravajuceg_drustva(this.value);'>";
		echo "<option value = '-1'>Izaberite osiguravajuæe dru¹tvo</option>";

		for ($i = 0; $i < count($niz_osiguravajuca_drustva); $i++) {
			$group_strano_osiguravajuce_drustvo = $niz_osiguravajuca_drustva[$i]['strano_drustvo'];
			if ($group_strano_osiguravajuce_drustvo == '0') {
				$ispis_strano_osiguravajuce_drustvo = 'Domaæe osiguravajuæe dru¹tvo';
			} else {
				$ispis_strano_osiguravajuce_drustvo = 'Strano osiguravajuæe dru¹tvo';
			}
			echo "<optgroup label='$ispis_strano_osiguravajuce_drustvo'>";
			$sql_od = "SELECT id,osiguravajuce_drustvo_naziv FROM sifarnici.osiguravajuca_drustva WHERE strano_drustvo = '$group_strano_osiguravajuce_drustvo'";
			$result_od = pg_query($conn, $sql_od);

			while ($niz_od = pg_fetch_assoc($result_od)) {

				if ($osiguravajuce_drustvo_id == $niz_od['id'] && $osiguravajuce_drustvo_id != $osiguravajuce_drustvo_id_postoji) {
					echo "<option value='$osiguravajuce_drustvo_id' selected=\"selected\">" . $niz_od['osiguravajuce_drustvo_naziv'] . "</option>\n";
				} else {
					$selected = ($osiguravajuce_drustvo_id_postoji == $niz_od['id']) ? "selected='selected'" : "";
					echo "<option value='" . $niz_od['id'] . "' $selected>" . $niz_od['osiguravajuce_drustvo_naziv'] . "</option>";
				}
			}
			echo "</optgroup>";
		}
		echo "</select>";

		echo "</td>";

		//IZMENIO VLADA
		echo "<td id=\"input_osig_drustvo\" class=\"dodaj_padding\" bgcolor=\"#2BAEFF\" $prikazi_osiguranje>\n";
		echo "<label>Regresni du¾nik</label>";
		echo "<input name=\"osiguranjeRegPotr\" id='osiguranjeRegPotr' value=\"$osiguranjeRegPotr\" maxlength=\"25\" style='width : $sirina_polja;' onkeypress=\"return handleEnter(this, event)\" $regres_disabled >\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		//PREMESTIO VLADA - KRAJ

		//********************************************* NOVO********************************
		echo "<tr>";
		if ($vrstaSt == 'AK') {
			echo "<td align=\"left\" bgcolor=\"#FFA56F\" class=\"uvucenRedTd\" >Kompenzovati: </td>";
			echo "<td bgcolor=\"#FFA56F\" style='width:100px!important;'>\n";
			echo "<input type=\"radio\" name=\"kompenzovati\" value=\"DA\"";
			if ($kompenzovati == "DA") {
				echo " checked ";
			}
			echo "onkeypress=\"return handleEnter(this, event)\">\n";
			echo "&nbsp;";
			echo "<input type=\"radio\" name=\"kompenzovati\" value=\"NE\"";
			if ($kompenzovati == "NE") {
				echo " checked ";
			}
			echo "onkeypress=\"return handleEnter(this, event)\"></td>\n";
			echo "&nbsp;";
			echo "<td align=\"left\" bgcolor=\"#FFA56F\" >Vinkulirano: </td>";
			echo "<td bgcolor=\"#FFA56F\">\n";
			echo "<input type=\"radio\" name=\"vinkulirano\" value=\"DA\"";
			if ($vinkulirano == "DA") {
				echo " checked ";
			}
			echo "onkeypress=\"return handleEnter(this, event)\">\n";
			echo "&nbsp;";
			echo "<input type=\"radio\" name=\"vinkulirano\" value=\"NE\"";
			if ($vinkulirano == "NE") {
				echo " checked ";
			}
			echo "onkeypress=\"return handleEnter(this, event)\"></td>\n";
			echo "&nbsp;";
		}

		//VLADA IZMENIO COLSPAN
		echo "<td bgcolor=\"#2BAEFF\" colspan='" . ($colspan_AK_kf_osnov) . "'>\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"osnovan\" value=\"D\"";
		if ($osnovan == "D") {
			echo " checked ";
		}
		// MARIJA 18.02.2015. - dodat id i promenljiva za funkciju
		echo "onkeypress=\"return handleEnter(this, event)\" id='osnovan_delimicno' $odbijenica_javascript_toggle>\n";
		echo "Osnovan delimièno";
		echo "&nbsp;<input name=\"delimicnoProc\" if='delimicnoProc' value=\"$delimicnoProc\" size=\"3\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;%";

		// MARIJA 18.02.2015 - dodato za autocomplite za pravni osnov - POCETAK
		$sql_postoji_razlog_umenjenja = " SELECT * FROM pravni WHERE idstete = $idstete ";
		$upit_postoji_razlog_umenjenja = pg_query($conn, $sql_postoji_razlog_umenjenja);
		$niz_postoji_razlog_umenjenja = pg_fetch_assoc($upit_postoji_razlog_umenjenja);

		if ($dugme == 'DA' || $odbijenica_javascript_toggle != "") {
			$razlozi_umanjenja_postoji = $niz_postoji_pravni['razlog_umanjenja_stete_id'];

			$dugme_resenje_odbijen_display = ($osnovan == "D") ? "inline" : "hidden";

			echo "<label id='labela_razlozi_umanjenja' name='labela_razlozi_umanjenja' style='font-size:12px;' $dugme_resenje_odbijen_display >&nbsp;&nbsp;Razlog smanjenja ¹tete:&nbsp;$niz_razlog&nbsp;</label>";
			echo "<select width='80px' style='font-size:12px;' class='lista_podnaslov_option1'  name='razlog_umanjenja_stete_id' id='razlog_umanjenja_stete_id' hidden onkeypress='return handleEnter(this, event)'>";

			$sql_razlozi_umanjenja = " SELECT * FROM sifarnici.razlozi_umanjenja_stete ";
			$upit_razlozi_umanjenja = pg_query($conn, $sql_razlozi_umanjenja);
			$niz_razlozi_umanjenja = pg_fetch_all($upit_razlozi_umanjenja);

			echo "<option value='-1'></option>";

			foreach ($niz_razlozi_umanjenja as $razlozi_umanjenja) {
				$razlozi_umanjenja_id = $razlozi_umanjenja['id'];
				$razlozi_umanjenja_opis = $razlozi_umanjenja['naziv'];

				if ($razlog_umanjenja_stete_id == $razlozi_umanjenja_id && ($razlozi_umanjenja_postoji == null || $razlozi_umanjenja_postoji != $razlog_umanjenja_stete_id)) {
					echo "<option value='$razlozi_umanjenja_id' selected=\"selected\">" . $razlozi_umanjenja_opis . "</option>\n";
				} else {
					$selected = ($razlozi_umanjenja_postoji == $razlozi_umanjenja_id) ? "selected='selected'" : "";

					echo "<option value='$razlozi_umanjenja_id' $selected id='razlog_umanjenja_stete_id'>" . $razlozi_umanjenja_opis . "</option>";
				}
			}

			echo "</select> ";
		}
		// MARIJA 18.02.2015 - dodato za autocomplite za pravni osnov - KRAJ
		echo "</td>\n";

		
		//IZMENIO VLADA - DODAO VLADA ONKEYPRESS FUNKCIJU SAMO BROJEVI
		echo "<td class=\"dodaj_padding\" bgcolor=\"#2BAEFF\">\n";
		echo "<label>JMBG/PIB:</label><br>";
		echo "<input name=\"jmbg_pib\" id='jmbg_pib' value=\"$jmbg_pib\" maxlength=\"13\" style=\"width: 200px;\" onkeypress=\"return handleEnter(this, event) && samoBrojevi(this,event);\" $regres_disabled $disable_jmbg>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		//UPIT ZA DOBIJANJE DRZAVA IZ SIFARNIKA - DODAO VLADA
		$upit_drzave = "SELECT id,naziv FROM sifarnici.zemlje_drzave";
		$rezultat_drzave = pg_query($conn, $upit_drzave);
		$niz_drzave = pg_fetch_all($rezultat_drzave);

		//IZMENIO VLADA I PREMESTIO LABELU
		echo "<td class=\"dodaj_padding\" bgcolor=\"#2BAEFF\">\n";
		echo "<label>Dr¾ava:</label><br>";
		echo "<select style='font-size:12px; width:200;' onkeypress='return handleEnter(this, event)' onchange='izmeni_polja(this.value)' id='drzava_reg_id' class='disable_selekti' name='drzava_reg_id' $regres_disabled $regres_readonly>";
		echo "<option value='-1' >Izaberite dr¾avu</option>";

		//PROLAZAK KROZ NIZ SA DRZAVAMA - DODAO VLADA
		foreach($niz_drzave as $drzava) {

			if($drzava_reg_id) {

				//AKO SE ID DRZAVE POKLOPI SA ID-JEM IZ SIFARNIKA,SELEKTUJ STAVKU IZ LISTE
				if($drzava_reg_id == $drzava['id']) {

					echo "<option value=" .$drzava['id']. " selected>" .$drzava['naziv']. "</option>";
				}
			}

			if(!$drzava_reg_id) {

				//AKO SE NAZIV IZ BAZE POKLOPI SA NAZIVOM IZ SIFARNIKA,SELEKTUJ STAVKU IZ LISTE
				if($drzavaRegPotr ==  $drzava['naziv']) {

					echo "<option value=" .$drzava['id']. " selected>" .$drzava['naziv']. "</option>";
				}
			}
			
			echo "<option value=" .$drzava['id']. ">" .$drzava['naziv']. "</option>";
		}

		echo "</select>";
		echo "&nbsp;\n";
		echo "</td>";

		//IZMENIO VLADA I PREMESTIO LABELU
		echo "<td class=\"dodaj_padding\" bgcolor=\"#2BAEFF\">\n";
		echo "<label>Adresa:</label><br>";
		echo "<input name=\"adresa_reg\" id='adresa_reg' value=\"$adresa_reg\" maxlength=\"25\" style='width : $sirina_polja;' onkeypress=\"return handleEnter(this, event)\" $regres_disabled $disable_adresa>\n";
		echo "&nbsp;\n";
		echo "</td>";		

		echo "</tr>";

		echo "<tr>";
		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\" colspan='4'></td>\n";
		}

		//VLADA IZMENIO COLSPAN
		echo "<td bgcolor=\"#2BAEFF\" colspan='" . ($colspan_AK_kf_osnov - 1) . "'>\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"osnovan\" value=\"O\"";
		if ($osnovan == "O") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\" id='odbijen' $odbijenica_javascript_toggle>\n\n\n\n";
		echo "ODBIJEN";
		if ($dugme == 'DA' && $odbijenica_javascript_toggle != "") {
			$dugme_resenje_odbijen_display = ($osnovan == "O") ? "table-row" : "none";
			echo "  <input type='button' id='dugme_resenje_odbijen' name='dugme_resenje_odbijen' value='Kreiraj odbijenicu' style='display:$dugme_resenje_odbijen_display;' onclick='kreiraj_odbijenicu($idstete);'>";
		}
		echo "</td>\n";

		//PREMESTIO VLADA
		echo "<td width=\"120\" bgcolor=\"#2BAEFF\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		
		//IZMENIO VLADA I PREMESTIO LABELU
		echo "<td class=\"dodaj_padding\" bgcolor=\"#2BAEFF\">\n";
		echo "<label>Telefon:</label><br>";
		echo "<input name=\"telefon_reg\" id='telefon_reg' value=\"$telefon_reg\" maxlength=\"25\" style=\"width: 200px;\" onkeypress=\"return handleEnter(this, event)\" $regres_disabled $disable_telefon>\n";
		echo "&nbsp;\n";
		echo "</td>";

		//PREMESTIO VLADA
		echo "<td class=\"dodaj_padding\" bgcolor=\"#2BAEFF\"/>";
		echo "<label>Op¹tina regresnog du¾nika:</label><br>";
		echo "<select style='font-size:12px; width:200;' onkeypress='return handleEnter(this, event)' onchange='vrati_mesta_reg(this.value,id)' id='opstina_reg' class='disable_selekti' name='opstina_reg' $regres_disabled $regres_readonly $disable_strane_zemlje $disable_select>";
		echo "<option value='-1' selected>Izaberite op¹tinu</option>";
		
		//DOBIJANJE OPSTINA IZ SIFARNIKA - DODAO VLADA
		$niz_opstine = pg_fetch_all($rezultatOpstine);

		//PROLAZAK KROZ NIZ SA OPSTINAMA - DODAO VLADA
		foreach($niz_opstine as $opstina) {
		
			//AKO SE ID OPSTINE IZ BAZE POKLOPI SA ID-JEM IZ SIFARNIKA,SELEKTUJ STAVKU IZ LISTE
			if($opstina_reg_id == $opstina['id']) {

				echo "<option value=" .$opstina['id']. " selected>" .$opstina['vrednost']. "</option>";
			}
			else {

				echo "<option value=" .$opstina['id']. ">" .$opstina['vrednost']. "</option>";
			}
		}

		echo "</select>";
		echo "</td>";
				
		//PREMESTIO VLADA
		echo "<td class=\"dodaj_padding\" bgcolor=\"#2BAEFF\"/>";
		echo "<label>Mesto regresnog du¾nika:</label><br>";
		echo "<select style='font-size:12px; width:$sirina_polja;' onkeypress='return handleEnter(this, event)' id='mesto_reg' class='disable_selekti' name='mesto_reg' $regres_disabled $regres_readonly $disable_strane_zemlje $disable_select>";
		echo "<option value='-1' selected>Izaberite mesto</option>";

		//AKO U BAZI POSTOJI ID OPSTINE
		if($opstina_reg_id != '' && $mesto_reg_id != '') {

			//DOBIJANJE MESTA IZ SIFARNIKA PO ID-JU OPSTINE - DODAO VLADA
			$rezultatMesta = $sifarnici_class->vratiMesta($opstina_reg_id );
			$niz_mesta = pg_fetch_all($rezultatMesta);
		
			//PROLAZAK KROZ NIZ SA MESTIMA - DODAO VLADA
			foreach($niz_mesta as $mesto) {
				
				//AKO SE ID MESTA IZ BAZE POKLOPI SA ID-JEM IZ SIFARNIKA,SELEKTUJ STAVKU IZ LISTE
				if($mesto_reg_id == $mesto['id']) {

					echo "<option value=" .$mesto['id']. " selected>" .$mesto['vrednost']. "</option>";
				}
				else {

					echo "<option value=" .$mesto['id']. ">" .$mesto['vrednost']. "</option>";
				}
			}
		}
		
		echo "</select>";
		echo "</td>";

		echo "</tr>\n";

		//IZMENIO VLADA
		echo "<tr>";

		if ($vrstaSt == 'AK') {

			echo "<td width=\"120\" bgcolor=\"#FFA56F\" colspan=\"4\">\n";
			echo "&nbsp;\n";
			echo "</td>\n";

			echo "<td width=\"120\" bgcolor=\"#2BAEFF\" colspan=\"2\">\n";
			echo "&nbsp;\n";
			echo "</td>\n";
		}
		else {
			echo "<td width=\"120\" bgcolor=\"#2BAEFF\" colspan=\"6\">\n";
			echo "&nbsp;\n";
			echo "</td>\n";
		}

		
		//PRIKAZ INPUTA ZA VISINU POTRAZIVANJA KADA JE REGRES OD OSIGURAVAJUCEG DRUSTVA
		echo "<td class=\"dodaj_padding\" bgcolor=\"#2BAEFF\">\n";
		echo "<label>Koliko potra¾ivati</label>";
		echo "<input name=\"koliko_potrazivati\" id='koliko_potrazivati' min=\"1\" value=\"$koliko_potrazivati\"  style=\"width: 200px;\" onkeypress=\"return handleEnter(this, event) && samoBrojeviITacka(event);\" $regres_disabled $disable_potrazivanje>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td width=\"120\" bgcolor=\"#2BAEFF\" colspan=\"2\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "</tr>\n";
		//IZMENA VLADA - KRAJ

		//BRANKA- dodato za odabir vrste dopisa - POCETAK
		echo "<tr>";
		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\" colspan='4'></td>\n";
		}
		echo "<td bgcolor=\"#2BAEFF\" colspan='" . ($colspan_AK_kf_osnov - 2) . "' width='600px'>\n";
		echo "&nbsp;\n";
		echo "<label>Izaberite vrstu dopisa:</label><select name='vrsta_dopisa'  id='vrsta_dopisa' style='width:260px;' onchange='prikazi_sakrij_dugme(this.value)'>";
		echo "<option value='-1'>Izaberite</option>";
		$sql_vrste_dokumenata = "SELECT * from sifarnici.vrste_dokumenata_stete";
		$upit_vrste_dokumenata = pg_query($conn, $sql_vrste_dokumenata);
		$niz_vrste_dokumenata = pg_fetch_all($upit_vrste_dokumenata);
		foreach ($niz_vrste_dokumenata as $vrsta_dokumenta) {
			$id_dokumenta = $vrsta_dokumenta['id'];
			$naziv_doumenta = $vrsta_dokumenta['naziv'];
			echo "<option value='$id_dokumenta'>$naziv_doumenta</option>";
		}
		echo "</select> ";
		echo "<input type='submit' id='dugme_kreiraj_dopis' name='dugme_kreiraj_dopis' style='display:none'  value='Kreiraj dopis' >";
		//BRANKA- dodato za odabir vrste dopisa - KRAJ
		//BRANKA 18.11.2014 -dodato da se dugme za pregled svih dopisa ne prikazuje ukoliko nema nijedan dopisa
		$sql_broj_dopisa = "select count(*) as koliko from dopisi_stete  where id_stete=$idstete";
		$rezultat = pg_query($conn, $sql_broj_dopisa);
		$niz = pg_fetch_assoc($rezultat);
		$koliko = $niz['koliko'];
		if ($koliko != 0) {
			echo "<input type='submit' id='dugme_pregledaj_dopise' name='dugme_pregledaj_dopise'  value='Pregledaj dopise' >";
		}
		echo "\n\n\n\n";
		echo "</td>\n";

		// MARIJA 19.02.2015 - dodato kako bi se napravila select lista za izvestaje - POCETAK
		//$pravni_osnov_dat = array(0 => 'Izaberi vrstu izveÃ¯Â¿Å“taja', 1=>'Evropski izveÃ¯Â¿Å“taj', 2 => 'Policijski zapisnik', 3 => 'Evropski izveÃ¯Â¿Å“taj i izveÃ¯Â¿Å“taj o alkotestu i policijske potvrde, dnevnog izvestaja');
		$html_select = "<label id='labela_vrsta_izvestaja' name='labela_vrsta_izvestaja' style='margin-right:10px;font-size:12px;   float: left;' >Pravni osnov dat na osnovu: </label>
				<select name='pravni_osnov_izvestaj' id='pravni_osnov_izvestaj' style='margin-right:20px; width: 175px; font-size:12px;'>\n";

		$postoji_alkotest_izvestaj = $niz_postoji_pravni['osnov_pravnog_osnova'];
		$postoji_alkotest_osteceni = $niz_postoji_pravni['alkotest_osteceni'];
		$postoji_alkotest_krivac = $niz_postoji_pravni['alkotest_krivac'];

		if ($alkotest_osteceni && !$postoji_alkotest_osteceni) {
			$vrednost_alkotest_osteceni = $alkotest_osteceni;
		} else {
			$vrednost_alkotest_osteceni = ($postoji_alkotest_osteceni) ? $postoji_alkotest_osteceni : '0.00';
		}
		if ($alkotest_krivac && !$postoji_alkotest_krivac) {
			$vrednost_alkotest_krivac = $alkotest_krivac;
		} else {
			$vrednost_alkotest_krivac = ($postoji_alkotest_krivac) ? $postoji_alkotest_krivac : '0.00';
		}

		// MARIJA 04.03.2015 izmenjeno - POCETAK
		// upit kojim se izvlace svi osnovi u listu koji nisu vec izabrani za dati idstete
		$sql_osnov = " SELECT * FROM sifarnici.osnov_za_pravni_osnov WHERE id NOT IN(SELECT sopo.id FROM sifarnici.osnov_za_pravni_osnov sopo
		        INNER JOIN osnov_za_pravni_osnov opo
		    	ON sopo.id = opo.osnov_id
		    	WHERE idstete = $idstete) ORDER BY naziv ";
		$upit_osnov = pg_query($conn, $sql_osnov);
		$niz_osnov = pg_fetch_all($upit_osnov);

		$postoji_osnov_za_pravni_osnov = $niz_postoji_pravni['osnov_pravnog_osnova'];

		$html_select .=  "<option value='-1'>Izaberite</option>";

		foreach ($niz_osnov as $osnov_za_pravni_osnov) {
			$dat_pravni_osnov_id = $osnov_za_pravni_osnov['id'];
			$dat_pravni_osnov_naziv = $osnov_za_pravni_osnov['naziv'];

			$selected = ($postoji_osnov_za_pravni_osnov == $dat_pravni_osnov_id) ? "selected='selected'" : "";
			$html_select .=  "<option value='$dat_pravni_osnov_id' $selected id='razlog_umanjenja_stete_id'>" . $dat_pravni_osnov_naziv . "</option>";
		}

		$html_select .= "</select>";
		$vrednost_osnov_postoji = vrati_sve_osnove_za_stetu($conn, $idstete);
		$hidden_uslov_osnov = ($vrednost_osnov_postoji) ? '' : 'hidden';
		$html_select .= "</br>";
		$html_select .= "<input type='button' name='dodaj_osnov_za_pravni_osnov' id='dodaj_osnov_za_pravni_osnov' value='Dodaj' onclick='dodaj_osnov_pravni_osnov();'>";
		$html_select .= "<input type='button' name='obrisi_osnov_za_pravni_osnov' $hidden_uslov_osnov  id='obrisi_osnov_za_pravni_osnov' value='Obri¹i poslednji' onclick='obrisi_osnov_pravni_osnov();'>";


		$kasko_uslov_prikaz = ($vrstaSt == 'AK') ? 1 : 2;
		echo "<td bgcolor=\"#2BAEFF\" colspan='$kasko_uslov_prikaz'  class='uvucenRedTd' width='175px'>\n";
		echo $html_select;
		echo "</td>\n";

		// MARIJA 04.03.2015. - dodato da bi se prikazalo u istom redu i alkottest - POCETAK
		echo "<td bgcolor=\"#2BAEFF\" style='width:230px;' class=\"uvucenRedTd\">";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<table  bgcolor='#CCCCCC'  id='tabela_alkotest' name='tabela_alkotest' style='center'>";
		echo "<tr bgcolor='#CCCCCC'>";
		echo "<td colspan = 4 bgcolor='#CCCCCC' id='radi'>";
		echo "<label id='labela_alkotest' name='labela_alkotest' style='font-size:12px;' >Alkotest: </label>";
		echo "</br>";
		echo "</td>";
		echo "</tr>";
		echo "<tr bgcolor='#CCCCCC'>";
		echo "<td bgcolor='#CCCCCC'>";
		echo "<label id='labela_alkotest_osteceni' name='labela_alkotest_osteceni' style='font-size:10px;'  >Od¹tetni: </label>";
		echo "</td>";
		echo "<td bgcolor='#CCCCCC'>";
		echo "<input type='text' name='alkotest_osteceni' id='alkotest_osteceni'  style='font-size:12px; width:45px;' value='$vrednost_alkotest_osteceni'  onkeypress='return samoBrojeviITacka(event);'/>";
		echo "&nbsp;&#8240;";
		echo "</td>";
		echo "<td bgcolor='#CCCCCC'>";
		echo "<label id='labela_alkotest_krivac' name='labela_alkotest_krivac' style='font-size:10px;'  >Krivac: </label>";
		echo "</td>";
		echo "<td bgcolor='#CCCCCC'>";
		echo "<input type='text' name='alkotest_krivac' id='alkotest_krivac'  style='font-size:12px; width:45px;' value='$vrednost_alkotest_krivac'  onkeypress='return samoBrojeviITacka(event);'/>";
		echo "&nbsp;&#8240;&nbsp;";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "</td>";;
		// MARIJA 04.03.2015. - dodato da bi se prikazalo u istom redu i alkottest - KRAJ
		$sirina_AK_nap_dugme = ($vrstaSt == 'AK') ? '210px' : '300px';

		echo "<td bgcolor=\"#2BAEFF\" colspan='2'>";
		echo "Napomena:</br>";
		echo "<textarea id='regresno_potrazivanje_napomena' style='resize:none; width: $sirina_AK_nap_dugme; height: 80px;' name='regresno_potrazivanje_napomena' $regres_disabled $regres_readonly>$regresno_potrazivanje_napomena</textarea>";
		echo "</td>";
		// MARIJA 19.02.2015 - dodato kako bi se napravila select lista za izvestaje - KRAJ

		echo "</tr>\n";
		$lista_za_pravni_osnov = ($vrednost_osnov_postoji) ? $vrednost_osnov_postoji : $lista_za_pravni_osnov;
		// MARIJA 25.02.2015 - POCETAK
		$postoji_osnov_za_osnov = ($lista_za_pravni_osnov) ? 'inline' : 'hidden';

		$vrsta_stete_AK = ($vrstaSt == 'AK') ? 4 : 5;

		echo "<tr id='sakrij_red'>";
		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\" colspan='4' >";
			echo "</td>";
			echo "<td bgcolor=\"#2BAEFF\" >";
			echo "</td>";
		} else {
			echo "<td bgcolor=\"#2BAEFF\" colspan='5' >";
			echo "</td>";
		}

		echo "<td bgcolor=\"#2BAEFF\" style='width:180px;' class=\"uvucenRedTd\">";
		$prikaz_textarea = ($lista_za_pravni_osnov) ? 'none' : 'hidden';
		$prikaz_osnov = ($lista_za_pravni_osnov) ? "class='disabled'" : "";
		echo "<input type='hidden' name='osnov_pravnog_osnova' id='osnov_pravnog_osnova' value='$osnov_pravnog_osnova'>";
		echo "<select id='lista_za_pravni_osnov' name='lista_za_pravni_osnov[]' multiple='' style='width:200px; height:70px;' $postoji_osnov_za_osnov>";
		if ($vrednost_osnov_postoji) {
			for ($i = 0; $i < count($vrednost_osnov_postoji); $i++) {
				$option_id = $vrednost_osnov_postoji[$i]['osnov_id'];
				$option_naziv = $vrednost_osnov_postoji[$i]['naziv'];
				echo "<option value='" . $option_id . "'>$option_naziv</option>";
			}
		}
		echo "</select>";
		//echo "<textarea name='lista_za_pravni_osnov' id='lista_za_pravni_osnov' readonly='readonly' $prikaz_osnov>$lista_za_pravni_osnov</textarea>";
		echo "</td>";

		echo "<td bgcolor=\"#2BAEFF\">";
		echo "</td>";


		echo "<td bgcolor=\"#2BAEFF\" colspan='2'>";
		echo "</td>";


		echo "</tr>";

		// MARIJA 25.02.2015 - KRAJ
		echo "<tr>";
		if ($vrstaSt == 'AK'  && substr($sifra, -1) == '1') {
			$broj_polise_za_dugovanje_na_danasnji_dan = $broj_polise_sa_stetnog_dogadjaja;
			echo "<td bgcolor=\"#FFA56F\" colspan=\"4\" >";
			echo "<div style='float:left;width:180px;'>&nbsp;&nbsp;Dugovanje na dana¹nji dan:</div>";
			echo "<textarea name='dugovanje_na_danasnji_dan' id='dugovanje_na_danasnji_dan' style='float:left;width:220px;height:100px;resize:none;text-align:left;background-color:#CC865C;color:#404040;overflow:hidden;' class='disabled' readonly='readonly' >";
			require_once 'dugovanje_na_danasnji_dan.php';
			echo "</textarea>";
			echo "&nbsp;</td>\n";
		}
		//  Novo za opste sistemske polise (NN, IO, DZO) - KF OSNOV fali za IO, NN, DZO
		/*else if ($vrstaSt == 'IO' || $vrstaSt == 'N' || ($vrstaSt == 'DPZ' && $tipSt != '0205'))
{
	$broj_polise_za_dugovanje_na_danasnji_dan = $broj_polise_sa_stetnog_dogadjaja;
	echo "<td bgcolor=\"#FFA56F\" colspan=\"4\" >";
	echo "<div style='float:left;width:180px;'>&nbsp;&nbsp;Dugovanje na dana¹nji dan:</div>";
	echo "<textarea name='dugovanje_na_danasnji_dan' id='dugovanje_na_danasnji_dan' style='float:left;width:220px;height:100px;resize:none;text-align:left;background-color:#CC865C;color:#404040;overflow:hidden;' class='disabled' readonly='readonly' >";
	require_once 'dugovanje_na_danasnji_dan_sistemske_opste.php';
	echo "</textarea>";
	echo "&nbsp;</td>\n";
}*/ else if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\" colspan=\"4\" >";
			echo "&nbsp;</td>\n";
		} else {
			echo "&nbsp;";
		}

		//  Datum kompletiranja dokumentacije za davanje pravnog osnova
		echo "<td bgcolor=\"#2BAEFF\" colspan='" . ($colspan_AK_kf_osnov + 1) . "'>";
		echo "&nbsp;&nbsp;Datum kompletiranja dokumentacije za davanje pravnog osnova:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<input id='pravniOsnovDatumKompletiranjaDokumentacije' name=\"pravniOsnovDatumKompletiranjaDokumentacije\" value=\"$pravniOsnovDatumKompletiranjaDokumentacije\" maxlength=\"8\" size=\"8\" height=\"15\" title=\"Datum kada je dostavljen poslednji dokument koji je potreban i dovoljan za davanje pravnog osnova\" onclick=\"showCal('pravniOsnovDatumKompletiranjaDokumentacije')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		//MARIJA 01.01.2015 - POCETAK
		echo "<td width=\"120\" bgcolor=\"#2BAEFF\" colspan= '2'>\n";
		$sql_dokument_regresno_potrazivanje = "SELECT id,dana FROM dopisi_stete WHERE revizija IN(SELECT max(revizija) AS revizija FROM dopisi_stete WHERE id_stete = $idstete AND vrsta_dopisa = 1) AND id_stete = $idstete AND vrsta_dopisa = 1";
		$rezultat_dokument_regresno_potrazivanje = pg_query($conn, $sql_dokument_regresno_potrazivanje);
		$niz_dokumenta_regresno_potrazivanje = pg_fetch_assoc($rezultat_dokument_regresno_potrazivanje);
		$revizija_obavestenja_id = $niz_dokumenta_regresno_potrazivanje['id'];
		$revizija_obavestenja_datum = $niz_dokumenta_regresno_potrazivanje['dana'];
		echo "<input type='hidden' name='obavestenje_o_regresnom_potrazivanju' id='obavestenje_o_regresnom_potrazivanju' value='$revizija_obavestenja_id'>";
		if ($revizija_obavestenja_id != 0) {
			//echo "<a href='' onclick='stampajDopis();'>ObaveÃ¯Â¿Å“tenje o regresnom potraÃ¯Â¿Å“ivanju</a>";
			echo "<input type='button'  onclick='stampajDopis();' value='Obave¹tenje o regresnom potra¾ivanju' style='font-size:11px; width:$sirina_AK_nap_dugme;'>";
			echo "</br>";
			echo "<label name='datum_kreiranja_obavestenja' style='width:$sirina_AK_nap_dugme; font-size:11px;'>Datum kreiranja dokumenta: " . $revizija_obavestenja_datum . "</label>";
		}
		echo "</td>\n";
		//MARIJA 01.01.2015 - KRAJ

		//********************************************* NOVO********************************
		echo "<tr>\n";
		if ($vrstaSt == 'AK') {
			echo "<td width=\"100\" bgcolor=\"#FFA56F\" >\n";
			echo "</td>\n";
			echo "<td width=\"100\" bgcolor=\"#FFA56F\" colspan=\"2\" >\n";
			echo "</td>\n";
			echo "<td bgcolor=\"#FFA56F\">\n";
			echo "</td>\n";
		}

		echo "<td bgcolor=\"#2BAEFF\" colspan='" . ($colspan_AK_kf_osnov - 1) . "' align=\"left\" class=\"uvucenRedTd\">\n";
		echo "<label name=\"label_pravni_osnov_obradjivac\" id=\"label_pravni_osnov_obradjivac\" for=\"pravni_osnov_obradjivac\" style=\"width: 150px;\">Obraðivaè: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>";

		$sqlPravniciDodatno = ($pravniOsnovObradjivac) ? "OR id = $pravniOsnovObradjivac " : "";
		$sqlPravnici = " SELECT id, upper(ime) AS ime FROM pravnici WHERE vazi = TRUE $sqlPravniciDodatno ORDER BY ime ASC;";
		$rezultatPravnici = pg_query($conn, $sqlPravnici);
		echo "<select name=\"pravniOsnovObradjivac\" id=\"pravniOsnovObradjivac\" style=\"width: 250px;\">\n";
		echo "<option value=\"0\">&nbsp;</option>\n";

		while ($rowPravnik = pg_fetch_assoc($rezultatPravnici)) {
			if ($rowPravnik['id'] == $pravniOsnovObradjivac)
				echo "<option value=\"" . $rowPravnik['id'] . "\" selected=\"selected\">" . $rowPravnik['ime'] . "</option>\n";
			else
				echo "<option value=\"" . $rowPravnik['id'] . "\" >" . $rowPravnik['ime'] . "</option>\n";
		}
		echo "</select>\n";
		echo "</td>\n";



		echo "<td bgcolor=\"#2BAEFF\" align=\"left\" class=\"uvucenRedTd\" style=\"width: 450px;\" colspan=\"1\>";
		echo "<label name=\"label_pravni_osnov_dao\" id=\"label_pravni_osnov_dao\" for=\"pravni_osnov_dao\" style=\"width: 150px;\">Pravni osnov dao:&nbsp;&nbsp;&nbsp;</label>";

		$sqlPravniciOsnovDodatno = ($pravniOsnovDao) ? "OR id = $pravniOsnovDao " : "";
		$sqlPravniciOsnov = " SELECT id, upper(ime) AS ime FROM pravnici WHERE vazi = TRUE $sqlPravniciOsnovDodatno ORDER BY ime ASC;";
		$rezultatPravniciOsnov = pg_query($conn, $sqlPravniciOsnov);
		echo "<select name=\"pravniOsnovDao\" id=\"pravniOsnovDao\" style=\"width: 250px;\">\n";
		echo "<option value=\"0\">&nbsp;</option>\n";

		while ($rowPravnik = pg_fetch_assoc($rezultatPravniciOsnov)) {
			if ($rowPravnik['id'] == $pravniOsnovDao)
				echo "<option value=\"" . $rowPravnik['id'] . "\" selected=\"selected\">" . $rowPravnik['ime'] . "</option>\n";
			else
				echo "<option value=\"" . $rowPravnik['id'] . "\" >" . $rowPravnik['ime'] . "</option>\n";
		}
		echo "</select>\n";
		echo "</td>\n";
		echo "<td bgcolor=\"#2BAEFF\"  align=\"left\" class=\"uvucenRedTd\"></td>";
		// MARIJA 02.03.2015 - dodato za prikaz saglasnosti za otvaranje regresa i sva podesavanja - POCETAK
		//if($radnik == 122 || $radnik == 2059 || $radnik == 138 || $radnik == 3036 || $radnik == 3071 || $radnik == 151)
		//echo "<input type='text' name='potvrdjen_osnov_za_regres' id='potvrdjen_osnov_za_regres' value='$potvrdjen_osnov_za_regres'>";
		// if($radnik == 122 || $radnik == 2059 || $radnik == 138 || $radnik == 3036 || $radnik == 3041)
		// {
		$promenljiva_hidden_potvrdjen_osnov_za_regres = (($radnik == 122 || $radnik == 2059 || $radnik == 138 || $radnik == 3036 || $radnik == 3071 || $radnik == 151 || $radnik == 3064 || $radnik == 3085 || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125)) ? '' : 'hidden';
		$promenljiva_hidden_potvrdjen_osnov_za_regres_zabrana = (($radnik != 122 && $radnik != 2059 && $radnik != 138 && $radnik != 3036 && $radnik != 3071 && $radnik != 151 && $radnik != 3064 || $radnik == 3085 || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125)) ? '' : 'hidden';
		echo "<td bgcolor=\"#2BAEFF\" colspan='2' $promenljiva_hidden_potvrdjen_osnov_za_regres>\n";
		echo "Potvrðen osnov za regres:";
		echo "&nbsp;\n";
		if ($vrstaSt == 'AK') {
			echo "</br>";
		}
		echo "<input type='radio' name='potvrdjen_osnov_za_regres' value='true' id='potvrdjen_osnov_za_regres' onchange='potvrda_osnova_za_regres(this.value);'";
		if ($potvrdjen_osnov_za_regres == "true" || $potvrdjen_osnov_za_regres == "t") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">DA &nbsp;\n";

		echo "<input type='radio'name='potvrdjen_osnov_za_regres' value='false' id='potvrdjen_osnov_za_regres' onchange='potvrda_osnova_za_regres(this.value);'";
		if ($potvrdjen_osnov_za_regres == "false" || $potvrdjen_osnov_za_regres == "f") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">NE\n";


		echo "</td>\n";
		// }
		// else
		// {
		echo "<td bgcolor=\"#2BAEFF\" colspan='2' $promenljiva_hidden_potvrdjen_osnov_za_regres_zabrana>\n";
		echo "</td>\n";
		// }
		// MARIJA 02.03.2015 - dodato za prikaz saglasnosti za otvaranje regresa i sva podesavanja - KRAJ

		echo "</tr>\n";

		if ($vrstaSt == 'AK') {
			echo "<td colspan=\"3\" bgcolor=\"#FFA56F\" valign=\"center\" style='text-align:right;' class=\"uvucenRedTd\">\n";
			echo "Datum davanja KF osnova:&nbsp;\n";
			echo "</td>\n";
			echo "<td bgcolor=\"#FFA56F\">\n";
			echo "<input name=\"datumKomOsnov\" value=\"$datumKomOsnov\" size=\"15\" height=\"15\" onclick=\"showCal('datumKomOsnov')\" onkeypress=\"return handleEnter(this, event)\">\n";
			echo "</td>\n";
		}

		echo "<td bgcolor=\"#2BAEFF\" colspan=\"1\" rowspan=\"2\" align=\"left\" valign=\"top\" class=\"uvucenRedTd\">\n";
		echo "Napomena: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";   // dodatno Marko Markovic 2019-12-05 &nbsp;&nbsp;&nbsp;&nbsp;
		echo "<textarea name=\"pravniOsnovNapomena\" id=\"pravniOsnovNapomena\" rows=\"6\" cols=\"40\" style=\"resize:none; width: 250px;\">$pravniOsnovNapomena</textarea>\n";
		echo "</td>\n";

		if ($vrstaSt != 'AK') 
		{
			echo "<td colspan=\"4\" bgcolor=\"#2BAEFF\"></td>";  
		}
		$sql_prigovor           = "select id from prigovori.prigovori_registar where poz_id=$idstete";
		$rez_prigovor           = pg_query($conn_amso, $sql_prigovor );
		$niz_prigovor           = pg_fetch_assoc($rez_prigovor);
		$prigovor_indikator     = empty($niz_prigovor) ? 0 : 1;
		  
		if(!empty($niz_prigovor))
		{

		// Marko Markovic dodatna napomena faza pravna dugme za modal 2019-12-06
		echo "<td bgcolor=\"#2BAEFF\" align=\"left\" class=\"uvucenRedTd\" style=\"width: 450px;\" colspan=\"1\>\n";
		echo "<label name=\"label_pravni_osnov_dao\" id=\"label_pravni_osnov_dao\" for=\"pravni_osnov_dao\" style=\"margin-right:10px;font-size:12px;   float: left;\">Pravni osnov dao 1:</label>";

		$sqlPravniciOsnovDodatno = ($pravniOsnovDao_1) ? "OR id = $pravniOsnovDao_1 " : "";
		$sqlPravniciOsnov = " SELECT id, upper(ime) AS ime FROM pravnici WHERE vazi = TRUE $sqlPravniciOsnovDodatno ORDER BY ime ASC;";
		$rezultatPravniciOsnov = pg_query($conn, $sqlPravniciOsnov);
		echo "<select name=\"pravniOsnovDao_1\" id=\"pravniOsnovDao_1\" style=\"width: 250px;\">\n";
		echo "<option value=\"0\">&nbsp;</option>\n";

		while ($rowPravnik = pg_fetch_assoc($rezultatPravniciOsnov))  
		{
			if ($rowPravnik['id'] == $pravniOsnovDao_1)
				echo "<option value=\"" . $rowPravnik['id'] . "\" selected=\"selected\">" . $rowPravnik['ime'] . "</option>\n";
			else
				echo "<option value=\"" . $rowPravnik['id'] . "\" >" . $rowPravnik['ime'] . "</option>\n";
		}
		echo "</select>\n";
		echo "</td>\n";
	}
	else
	{
	echo "<td bgcolor=\"#2BAEFF\" align=\"left\" class=\"uvucenRedTd\" style=\"width: 450px;\" colspan=\"1\>\n";
	echo "<label name=\"label_pravni_osnov_dao\" id=\"label_pravni_osnov_dao\" for=\"pravni_osnov_dao\" style=\"margin-right:10px;font-size:12px;   float: left;\"></label>";
	echo "<select name=\"pravniOsnovDao_1\" id=\"pravniOsnovDao_1\" style=\"width: 250px;visibility:hidden;\">\n";
	echo "</select>\n";
	echo "</td>";	

	}
		echo "<td bgcolor='#2BAEFF'>";
		echo "</td>";
		echo "<td bgcolor='#2BAEFF'>";
		echo "</td>";
		echo "<td bgcolor='#2BAEFF'>";
		// $unosivaci = array(151,138,3093,3055,3052,3053,3045,3033,3039,3042,3067,3083,3078,3093,3044,3038,3090,3023,3081,3079,122,3029,3054,3024,3046,3085,3032,3070,2253,3116,3101,3080,2119,2224,3069,3043,3016,3004,3102,3106);
		$conn_amso = pg_connect("host=localhost dbname=amso user=zoranp");
		$sql_unosivaci = "SELECT radnik FROM radnik WHERE faza_stete is not null";
		$rezultat      = pg_query($conn_amso, $sql_unosivaci);
		$niz_unosivaci = pg_fetch_all($rezultat);
		$brunosivaca   = pg_num_rows($rezultat);
		for ($i = 0; $i < $brunosivaca; $i++) {
			$unosivaci[] = $niz_unosivaci[$i]['radnik'];
		}
		if (in_array($radnik, $unosivaci)) {
			echo "<input type='button' onclick='otvori_dodatne_napomen(3);' id='napomena_pravna_faza' name='napomena_pravna_faza' style='height:30px; width:200px; font-size:13px; margin:0px;' text-align='center' value='Napomena za sajt dru¹tva' />";
		}
		echo "</td>";
		// Marko Markovic kraj 

		/*
// Marko Markovic zakomentarisao 2019-12-05
echo "<td bgcolor=\"#2BAEFF\" colspan=\"2\">\n";
echo "&nbsp;\n";
echo "</td>\n";
// 18.02.2015 - dodato
echo "<td width=\"120\" bgcolor=\"#2BAEFF\">\n";
echo "&nbsp;\n";
echo "</td>\n";
// 18.02.2015 - dodato
// Marok Markovic kraj
*/

		echo "</tr>\n";

		if ($calc) {
			$preostaliDug = $dugZaPremiju - $kompenzovano;
		}

		echo "<tr>\n";
		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\" >\n";
			echo "</td>\n";
			echo "<td bgcolor=\"#FFA56F\">\n";
			echo "</td>\n";
			echo "<td colspan=\"2\" bgcolor=\"#FFA56F\">\n";
			echo "&nbsp;\n";
			echo "</td>\n";
		}
		else
		{
			echo "<td bgcolor=\"#2BAEFF\" colspan='4'></td>"; 
		}

		// ovde ispravitii za AK
		$ak_izmena_kolone = ($vrstaSt == 'AK') ? ($colspan_AK_kf_osnov + 1) : $colspan_AK_kf_osnov;

		if(!empty($niz_prigovor))
		{
        echo "<td bgcolor=\"#2BAEFF\" align=\"left\" class=\"uvucenRedTd\" style=\"width: 450px;\" colspan=\"1\">\n";
		echo "<label name=\"label_pravni_osnov_dao\" id=\"label_pravni_osnov_dao\" for=\"pravni_osnov_dao\" >Pravni osnov dao 2:</label>";

		$sqlPravniciOsnovDodatno = ($pravniOsnovDao_2) ? "OR id = $pravniOsnovDao_2 " : "";
		$sqlPravniciOsnov = " SELECT id, upper(ime) AS ime FROM pravnici WHERE vazi = TRUE $sqlPravniciOsnovDodatno ORDER BY ime ASC;";
		$rezultatPravniciOsnov = pg_query($conn, $sqlPravniciOsnov);
		echo "<select name=\"pravniOsnovDao_2\" id=\"pravniOsnovDao_2\" style=\"width: 250px;\">\n";
		echo "<option value=\"0\">&nbsp;</option>\n";

		while ($rowPravnik = pg_fetch_assoc($rezultatPravniciOsnov)) {
			if ($rowPravnik['id'] == $pravniOsnovDao_2)
				echo "<option value=\"" . $rowPravnik['id'] . "\" selected=\"selected\">" . $rowPravnik['ime'] . "</option>\n";
			else
				echo "<option value=\"" . $rowPravnik['id'] . "\" >" . $rowPravnik['ime'] . "</option>\n";
		}
		echo "</select>\n";
		echo "</td>\n";
	}
	else
	{
		echo "<td bgcolor=\"#2BAEFF\" align=\"left\" class=\"uvucenRedTd\" style=\"width: 450px;\" colspan=\"1\">\n";
		echo "<label name=\"label_pravni_osnov_dao\" id=\"label_pravni_osnov_dao\" for=\"pravni_osnov_dao\" style=\"margin-right:10px;font-size:12px;   float: left;\"></label>";
		echo "<select name=\"pravniOsnovDao_1\" id=\"pravniOsnovDao_1\" style=\"width: 250px;visibility:hidden;\">\n";
		echo "</select>\n";
		echo "</td>\n";
    }
		
		echo "<td bgcolor=\"#2BAEFF\" colspan=\"4\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";


		echo "</tr>\n";

		echo "<tr>\n";
		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\">\n";
			echo "</td>\n";
			echo "<td bgcolor=\"#FFA56F\">\n";
			echo "</td>\n";
			echo "<td bgcolor=\"#FFA56F\" width=\"100\" style='text-align:right;' class=\"uvucenRedTd\">\n";
			echo "Predato:&nbsp;\n";
			echo "</td>\n";
			echo "<td bgcolor=\"#FFA56F\" width=\"100\" >\n";
			echo "<input name=\"kfPredato\" value=\"$kfPredato\" size=\"15\" height=\"15\" onclick=\"showCal('kfPredato')\" onkeypress=\"return handleEnter(this, event)\">\n";
			echo "</td>\n";
		}

		echo "<td colspan='" . ($colspan_AK_kf_osnov - 1) . "' align=\"right\" bgcolor=\"#2BAEFF\" class=\"uvucenRedTd\">\n";
		echo "Datum davanja pravnog osnova:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<input name=\"datumPravniOsnov\" id=\"datumPravniOsnov\" value=\"$datumPravniOsnov\" size=\"15\" height=\"15\" onclick=\"showCal('datumPravniOsnov')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#2BAEFF\" colspan='2'>\n";
		echo "&nbsp;&nbsp;Predato:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<input name=\"pravnaPredato\" value=\"$pravnaPredato\" size=\"15\" height=\"15\" onclick=\"showCal('pravnaPredato')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";



		// ----------- Marko Markovic 2020-04-29 dodato dugme za otvaranje strane za stampu pravnog osnova obrazlozenja korice
		echo "<td bgcolor=\"#2BAEFF\">\n";
		echo "<input type='button' onclick='pravni_osnov_korice($idstete)' id='dugme_kreiraj_dopis' name='dugme_kreiraj_dopis' value='Utvrðivanje pravnog osnova' >";
		echo "</td>\n";
		// ----------- Marko Markovic kraj 2020-04-29


		// MARIJA 02.03.2015 - dodat radnik i trenutan datum - POCETAK
		$conn_zabrane = pg_connect("  host=localhost dbname=zabrane user=zoranp");
		if (!$conn_zabrane) {
			echo "Gre¹ka otvaranja konekcije prema SQL serveru.";
			exit;
		}

		$sql_radnik = " SELECT ime  FROM unosivaci WHERE sifra = $radnik;";
		$rezultat_radnik = pg_query($conn_zabrane, $sql_radnik);
		$niz_radnik = pg_fetch_assoc($rezultat_radnik);
		$ulogovan_radnik = $niz_radnik['ime'];
		echo "<input type='hidden' name='ulogovan_radnik' value='$ulogovan_radnik' id='ulogovan_radnik'>";

		$radnik_tabela_pravni = $niz_postoji_pravni['radnik_evidentirao_potvrdu_za_regres'];
		$datum_tabela_pravni = $niz_postoji_pravni['datum_evidentiranja_potvrde_za_regres'];
		//$potvrdjen_osnov_za_regres_postoji = $niz_postoji_pravni['potvrdjen_osnov_za_regres'];

		$radnik_reg_pot_disabled = ($radnik_tabela_pravni) ? "class='disabled'" : '';
		$radnik_reg_pot_readonly = ($radnik_tabela_pravni) ? "readonly='readonly'" : '';

		$datum_reg_pot_disabled = ($datum_tabela_pravni) ? "class='disabled'" : '';
		$datum_reg_pot_readonly = ($datum_tabela_pravni) ? "readonly='readonly'" : '';

		echo "<td width=\"120\" bgcolor=\"#2BAEFF\" colspan='2'>\n";

		// ukoliko je ulogovan radnik isti kao i onaj koji je sacuvan u tabeli pravni, onda je on odobrio ili odbiro zahtev za regres. Ukoliko nije, upisi radnika koji je trenutno ulogonam.
		//$radnik_evidentirao_potvrdu_za_regres = ($radnik_evidentirao_potvrdu_za_regres && $potvrdjen_osnov_za_regres == $potvrdjen_osnov_za_regres_postoji) ? $radnik_tabela_pravni : $niz_radnik['ime']; // $niz_radnik['ime']
		// ukoliko posotji datum za pravni osnov i ukoliko se ne menja potvrdjen osnov za regres onda je datum isti. Ukoliko se menja potvrdjen osnov za regres, upisi datum kada je izvrsena izmena
		//$datum_evidentiranja_potvrde_za_regres = ($datum_evidentiranja_potvrde_za_regres && $potvrdjen_osnov_za_regres == $potvrdjen_osnov_za_regres_postoji) ? $datum_tabela_pravni: date("Y-m-d") ;
		$sirina_radnik = ($vrstaSt == 'AK') ? '150px' : '125px';
		$sirina_datum = ($vrstaSt == 'AK') ? '146px' : '90px';

		$prikazi = ($postoji_potvrda_tf && $radnik) ? 'inline' : 'hidden';
		if ($radnik_evidentirao_potvrdu_za_regres && $potvrdjen_osnov_za_regres == $postoji_potvrda_tf) {
			$radnik_evidentirao_potvrdu_za_regres = $radnik_tabela_pravni;
		}
		if ($datum_evidentiranja_potvrde_za_regres && $potvrdjen_osnov_za_regres == $postoji_potvrda_tf) {
			$datum_evidentiranja_potvrde_za_regres = $datum_tabela_pravni;
		}

		// if($postoji_potvrda_tf && $radnik)
		// {

		echo "<label $prikazi id='radnik_evidentirao' name='radnik_evidentirao'>Radnik: </label>";
		echo "<input type='text' value='" .  $radnik_evidentirao_potvrdu_za_regres . "' id='radnik_evidentirao_potvrdu_za_regres' name='radnik_evidentirao_potvrdu_za_regres' style='width:$sirina_radnik;' $radnik_reg_pot_disabled $radnik_reg_pot_readonly  $prikazi/>";
		if ($vrstaSt == 'AK') {
			echo "</br>";
		}
		echo "<label $prikazi id='datum_evidentiranja' name='datum_evidentiranja'>Datum:&nbsp; </label>";
		echo "<input type='text' value='$datum_evidentiranja_potvrde_za_regres' id='datum_evidentiranja_potvrde_za_regres' name='datum_evidentiranja_potvrde_za_regres' style='width:$sirina_datum;' $datum_reg_pot_disabled $datum_reg_pot_readonly $prikazi/>";
		//}
		echo "</td>\n";
		// 18.02.2015 - dodato

		echo "</tr>\n";

		echo "<tr>\n";

		if ($vrstaSt == 'AK') {
			echo "<td bgcolor=\"#FFA56F\">\n";
			echo "&nbsp;\n";
			echo "</td>\n";
			echo "<td bgcolor=\"#FFA56F\">\n";
			echo "</td>\n";
			echo "<td bgcolor=\"#FFA56F\" colspan=\"2\">\n";
			echo "&nbsp;\n";
			echo "</td>\n";
		}

		// MARIJA 20.02.2015. - dodato za prikaz rezervisane sume za pravni osnov - POCETAK
		echo "<td width=\"250\" bgcolor=\"#2BAEFF\"  class='uvucenRedTd' valign='top'>\n";
		// echo "<strong>";
		// echo "&nbsp;\n";
		// echo "Rezervisano";
		// echo "</strong>";
		// echo "&nbsp; \n";
		// echo "<input name='pravni_osnov_rezervisano'  id='pravni_osnov_rezervisano' value=\"$pravni_osnov_rezervisano\" size=\"13\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";


		//23.07.2018 Dodao Marko Stankovicza kreiranje re¹enja odustao 30.07.2018.


		echo "Odustao &nbsp;&nbsp;<input type='checkbox' onclick='pravni_osnov_odustao(this);' name='odustao_pravni_osnov' id='odustao_pravni_osnov'  
onkeypress='return handleEnter(this, event)' value=\"true\"";
		//if($rez_pravni['odustao']=='t')
		if ($odustao_pravni == 't' || $odustao_pravni_osnov) {
			echo "checked";
		}
		echo ">";

		//dodavanje uslova za prikaz dugmeta 01.08.2018.
		$hidden_pravni = ($odustao_pravni == 't' || $odustao_pravni_osnov == true) ? null : 'hidden';
		echo "&nbsp;&nbsp; <input type='button' name ='kreiraj_resenje_odustao' id='resenje_odustao' onclick='prikazi_formu_za_kreiranje_resenja_odustao(0);' 
value='Re¹enje odustao' $hidden_pravni>";

		echo "</td>\n";
		//31.07.2018. Marko Stankovic
		$odustao_pravni_osnov = $_POST['odustao_pravni_osnov'];


		// MARIJA 20.02.2015. - dodato za prikaz rezervisane sume za pravni osnov - KRAJ

		echo "<td bgcolor=\"#2BAEFF\" colspan='" . ($colspan_AK_kf_osnov) . "'>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\" colspan=\"2\" align=\"right\">\n";
		echo "<strong>\n";
		echo "Vraæeno:\n";
		echo "</strong>\n";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<input name=\"vraceno\" value=\"$vraceno\" size=\"15\" height=\"15\" onclick=\"showCal('vraceno')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "</tr>\n";
		echo "<tr>";
		if ($vrstaSt == 'AK') {
			echo "<td class=\"footerNarandza\" colspan=\"4\"></td>";
		}
		// MARIJA 19.02.2015 - izmenjeno
		echo "<td class=\"footerPlavo\" colspan='" . ($colspan_AK_kf_osnov + 3) . "'></td>";
		echo "</tr>";
		echo "</table>\n";

		echo "<hr color=\"#000000\" class='hr_presek'>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>\n";

		/************************* 7 SEDMA TABELA ***************************/
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" bgcolor=\"#B1FEFC\"  id='likvidacija'> \n";
		echo "<tr>\n";
		echo "<td width=\"40%\" valign=\"bottom\">\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" >\n";
		echo "<tr>\n";

		echo "<td colspan=\"3\" class=\"headerZeleno\">\n";
		echo "<strong><font size=\"3\">\n";
		echo "UTVRDJIVANJE&nbsp;VISINE&nbsp;©TETE\n";
		echo "</font></strong>\n";
		echo "</td>\n";

		echo "<td>\n&nbsp;&nbsp;&nbsp;&nbsp;<b><u><font size=\"4\">V</font><u></b>";
		echo "</td>\n";

		echo "<td>";
		//14.06.2017 dodat Novakoviæ ®arko - 3071
		//14.02.2019 dodata Iliæ Gorana - 3044 

		if ($radnik == 151 || $radnik == 3045 || $radnik == 138 || $radnik == 3054 || $radnik == 3061 || $radnik == 3004 || $radnik == 3039 || $radnik == 3036 || $radnik == 3023 || $radnik == 3071  || $radnik == 3085 || $radnik == 2059 || $radnik == 3082 || $radnik == 2224 || $radnik == 3083  || $radnik == 2059 || $radnik == 3064 || $radnik == 3090 || $radnik == 3044 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 122 || $radnik == 2253 || $radnik == 3126 || $radnik == 3125 || $radnik == 3069 || $radnik == 3029 || $radnik == 3128 || $radnik == 3062) {
			if ($sudski_postupak_id == null) {
				//dodao Marko Stankovicuslove 31.05.2018 kad je IO vrsta 13 i i ima broj sasije da udje u likvidaciju
				if (($vrstaSt == 'AO' && substr($sifra, -1) == '2') || $vrstaSt == 'AK' || ($vrstaSt == 'IO' && substr($tipSt, 0, 4) != '0903') || ($vrstaSt == 'IO' && $brsasOst != '')) {
					$uslov_io = substr($tipSt, 0, 2);

					//Dodao Marko Stankovic 13.03.2018.
					if ($uslov_io != '13' || ($uslov_io == '13' && $brsasOst != '')) {
						echo "<input type='button'  value='Likvidacija' onclick='likvidacija_stete()';>";
					}
				}
			} else {
				echo "<input type='button'  value='Resenje zahteva sudski postupak' onclick='resenje_zahteva_sudski()';>";
			}
		}
		echo "</td>";

		echo "</tr>\n";

		//  Obraðivaci (likvidatori)
		echo "<tr>\n";
		echo "<td width=\"400\" colspan=\"4\">Obraðivaè (likvidator):</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td>\n";
		echo "1&nbsp;\n";
		// Lazar Milosavljevicapril 2013
		$sql = "SELECT
				prezime || ' ' || ime as pim,
				jmbg
			FROM
				procenitelji
			WHERE
					(aktivan='A' AND likvidator = true)
				OR
					jmbg='$obradjivac1'
			ORDER BY
				prezime,ime;
			";
		$tabela = 'procenitelji';
		$polje = 'obradjivac1';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'pim', 'jmbg', ${$polje});

		echo "</td>\n";

		echo "<td colspan=\"2\" align=\"right\">\n";
		echo "2&nbsp;\n";
		// Lazar Milosavljevicapril 2013
		$sql = "SELECT
				prezime || ' ' || ime as pim,
				jmbg
			FROM
				procenitelji
			WHERE
					(aktivan='A' AND likvidator = true)
				OR
					jmbg='$obradjivac1'
			ORDER BY
				prezime,ime;
			";
		$tabela = 'procenitelji';
		$polje = 'obradjivac2';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'pim', 'jmbg', ${$polje});
		echo "</td>\n";

		echo "<td></td>\n";
		echo "<td>";
		// if($podaci_prigovori)
		// {

		if ($radnik == 10 || $radnik == 151 || $radnik == 138 || $radnik == 3068 || $radnik == 3044 || $radnik == 3042 || $radnik == 3067 || $radnik == 124 || $radnik == 3045 || $radnik == 3054 || $radnik == 3061 || $radnik == 3004 || $radnik == 3039 || $radnik == 3036 || $radnik == 3023  ||  $radnik == 122 ||  $radnik == 2059 || $radnik == 3071 || $radnik == 3064 || $radnik == 3029 || $radnik == 3033 || $radnik == 3072 || $radnik == 3072 || $radnik == 3040 || $radnik == 3035 || $radnik == 3048 || $radnik == 3048 || $radnik == 3071 || $radnik == 3074 || $radnik == 3085  || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125 || $radnik == 3069 || $radnik == 2249) 
		{

			echo "<input type='submit' id='dugme_odluka_likvidacija' name='dugme_odluka_likvidacija' value='Odluka komisije' >";
		}
		//}
		echo "</td>\n";
		echo "</tr>\n";

		//  Datum prijema predmeta u likvidaciju
		echo "<tr>\n";
		echo "<td align=\"right\" colspan=\"2\">Datum prijema predmeta u likvidaciju:</td>\n";
		echo "<td width=\"100\">\n";
		echo "  <input name=\"pocetak\" value=\"$pocetak\" maxlength=\"10\" size=\"13\" height=\"15\" title=\"Datum kada je obraðivaè (likvidator) primio predmet na utvrðivanje visine ¹tete\" onclick=\"showCal('pocetak')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";
		echo "<td>";
		echo "</td>";
		echo "<td>";
		if ($vrstaSt == 'AO' || $vrstaSt == 'AK') {
			if ($radnik == 151 || $radnik == 138 || $radnik == 3004 || $radnik == 3023 || $radnik == 3039 || $radnik == 3036 || $radnik == 3045 || $radnik == 3054 || $radnik == 3061 || $radnik == 3071 || $radnik == 3085 || $radnik == 3082 || $radnik == 3083 || $radnik == 2224 || $radnik == 2059 || $radnik == 3064 || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125 || $radnik == 3069) 
			{
				echo "<input type='submit' id='dugme_dopisi' name='dugme_dopisi' value='Dopisi' >";
			}
		}
		//}
		echo "</td>\n";
		echo "</tr>\n";

		//  Datum kompletiranja dokumentacije za utvrðivanje visine ¹tete
		echo "<tr>\n";
		echo "<td align=\"right\" colspan=\"2\">Datum kompletiranja dokumentacije za utvrðivanje visine ¹tete:</td>\n";
		echo "<td width=\"100\">\n";
		echo "<input name=\"datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete\" id=\"datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete\" value=\"$datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete\" maxlength=\"10\" size=\"13\" height=\"15\" title=\"Datum kada je dostavljen poslednji dokument koji je potreban i dovoljan za utvrðivanje visine ¹tete\" onclick=\"showCal('datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";
		echo "<td>&nbsp;</td>\n";
		//Dodao Marko Stankovic13.03.2018.
		//$uslov_io_tip=substr($tipSt,0,2);

		if (($vrstaSt == 'AO' && substr($sifra, -1) == '2') || $vrstaSt == 'AK' || $vrstaSt == 'IO') {
			if ($radnik == 151 || $radnik == 138 || $radnik == 3004 || $radnik == 3023 || $radnik == 3039 || $radnik == 3036 || $radnik == 3045 || $radnik == 3054 || $radnik == 3061 || $radnik == 3071 || $radnik == 3085 || $radnik == 2059 || $radnik == 3082 || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125 || $radnik == 3064 || $radnik == 3069) 
			{
				// 		echo "<td><input type='submit' id='odbijenica_likvidacija' name='odbijenica_likvidacija' value='Kreiranje odbijenice' ></td>\n";
				echo "<td><input type='button' id='odbijenica_likvidacija_novo' name='odbijenica_likvidacija_novo' value='Kreiranje odbijenice' onclick='kreiraj_odbijenicu_likvidacija($idstete);' ></td>\n";
			}
		}
		echo "</tr>\n";

		//  Datum utvrðivanja visine ¹tete
		echo "<tr>\n";
		echo "<td align=\"right\" colspan=\"2\">Datum utvrðivanja visine ¹tete:</td>\n";
		echo "<td width=\"100\">\n";
		echo "  <input name=\"kraj\" value=\"$kraj\" maxlength=\"10\" size=\"13\" height=\"15\" title=\"Datum kada je obraðivaè (likvidator) utvrdio visinu ¹tete\" onclick=\"showCal('kraj')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";
		echo "<td>&nbsp;</td>\n";
		//marko Stankovic03.08.2018.
		echo "<td>";

		$sql_poz_za_resenje_odustao = "SELECT * FROM predmet_odstetnog_zahteva WHERE id =$idstete";
		$upit_poz_za_resenje_odustao = pg_query($conn, $sql_poz_za_resenje_odustao);
		$podaci_poz_zahteva_za_resenje_odustao = pg_fetch_assoc($upit_poz_za_resenje_odustao);
		$odustao_poz_za_resenje_odustao = $podaci_poz_zahteva_za_resenje_odustao['odustao'];
		if ($odustao_poz_za_resenje_odustao == 't') {
			if (($vrstaSt == 'AO' && substr($sifra, -1) == '2') || $vrstaSt == 'AK' || $vrstaSt == 'IO') {
				if ($radnik == 151 || $radnik == 138 || $radnik == 3004 || $radnik == 3023 || $radnik == 3039 || $radnik == 3036 || $radnik == 3045 || $radnik == 3054 || $radnik == 3061 || $radnik == 3071 || $radnik == 3085 || $radnik == 2059 || $radnik == 3082 || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3069) 
				{
					echo "<input type='button' id='likvidacija_odstao' name='likvidacija_odustao' value='Re¹enje odustao' onclick='prikazi_formu_resenje_odustao_u_likvidaciji(1)' ";
				}
			}
		}
		echo "</td>\n";

		echo "</tr>\n";

		echo "<tr><td colspan=\"2\">&nbsp; </td>\n";
		echo "<td>DA&nbsp;&nbsp;&nbsp;&nbsp;NE</td>\n";
		echo "<td>&nbsp;</td></tr>\n";

		echo "<tr>\n";
		echo "<td colspan=\"1\">\n";
		echo "<strong><font size=\"3\">\n";
		echo "PONUDA 1 - PO¹TA\n";
		echo "</font></strong>\n";
		echo "</td>\n";

		echo "<td align=\"right\" >Prihvaæena: </td>";
		echo "<td >\n";
		echo "<input type=\"radio\" name=\"prihvacena\" value=\"DA\"";
		if ($prihvacena == "DA") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;";

		echo "<input type=\"radio\" name=\"prihvacena\" value=\"NE\"";
		if ($prihvacena == "NE") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\"></td>\n";
		echo "&nbsp;";

		echo "<td>\n";
		echo "&nbsp;\n&nbsp;&nbsp;&nbsp;<b><u><font size=\"4\">VI</font><u></b>";
		echo "</td>\n";
		echo "</tr>\n";


		echo "<tr>\n";
		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td align=\"left\">\n";
		echo "Pregovaraè:\n";

		// Lazar Milosavljevicapril 2013
		$sql = "SELECT
				prezime || ' ' || ime as pim,
				jmbg
			FROM
				procenitelji
			WHERE
					(aktivan='A' AND likvidator = true)
				OR
					jmbg='$likvidatorPonuda1'
			ORDER BY
				prezime,ime;
			";
		$tabela = 'procenitelji';
		$polje = 'likvidatorPonuda1';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'pim', 'jmbg', ${$polje});
		echo "</td>\n";

		echo "<td align=\"right\">\n";
		echo "Datum:&nbsp;\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<input name=\"datumPonuda1\" value=\"$datumPonuda1\" size=\"13\" height=\"15\" onclick=\"showCal('datumPonuda1')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";


		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";


		echo "<tr>\n";
		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td bgcolor=\"#FFDEAD\">\n";
		echo "<input id=\"prigovor\" name=\"prigovor\" type=\"checkbox\" value=\"true\" ";
		if ($prigovor == true) {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\" onclick='otvoriZatvoriDodatnaPoljaPrigovor(this);'>\n";
		echo "&nbsp;&nbsp;Prigovor\n";
		echo "</td>\n";

		// Izmene zbog prigovora na visinu ¹tete
		// Lazar Milosavljevic- 08-02-2013
		if (isset($osnovan_po_prigovoru_na_visinu_odstete) && $osnovan_po_prigovoru_na_visinu_odstete != '')
			$prigovor_osnovan = $osnovan_po_prigovoru_na_visinu_odstete;
		if (isset($delimicno_resen_po_prigovoru_procenat) && $delimicno_resen_po_prigovoru_procenat != '')
			$prigovor_procenat = $delimicno_resen_po_prigovoru_procenat;
		echo "<td bgcolor=\"#FFDEAD\" align=\"right\">\n";
		echo "<div id='datumPrigovor_div_1' style='display:$prikaz_prigovor_na_visinu_stete'>";
		echo "Ulo¾en&nbsp;dana:\n";
		echo "</div>";
		echo "</td>\n";
		echo "<td bgcolor=\"#FFDEAD\">\n";
		echo "<div id='datumPrigovor_div' style='display:$prikaz_prigovor_na_visinu_stete'>";
		echo "<input id='datumPrigovor' name=\"datumPrigovor\" value=\"$datumPrigovor\" size=\"13\" height=\"15\" onclick=\"showCal('datumPrigovor')\" onkeypress=\"return handleEnter(this, event)\" >\n";
		echo "</div>";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<b><u><font size=\"4\">VII</font><u></b>\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td colspan=\"3\" bgcolor=\"#FFDEAD\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";


		echo "<tr>\n";
		echo "<td bgcolor=\"#FFDEAD\" colspan=\"3\">\n";
		echo "Drugostepena komisija\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";


		echo "<tr>\n";
		echo "<td bgcolor=\"#FFDEAD\">\n";
		echo "1&nbsp;&nbsp;\n";
		// Lazar Milosavljevicapril 2013
		$sql = "SELECT
				prezime || ' ' || ime as pim,
				jmbg
			FROM
				procenitelji
			WHERE
					(aktivan='A' AND likvidator = true)
				OR
					jmbg='$komisija1'
			ORDER BY
				prezime,ime;
			";
		$tabela = 'procenitelji';
		$polje = 'komisija1';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'pim', 'jmbg', ${$polje});
		echo "</td>\n";

		echo "<td align=\"right\" bgcolor=\"#FFDEAD\">\n";
		echo "Poèetak:&nbsp;\n";
		echo "</td>\n";
		echo "<td bgcolor=\"#FFDEAD\">\n";
		echo "<input name=\"pocetak2\" value=\"$pocetak2\" size=\"13\" height=\"15\" onclick=\"showCal('pocetak2')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tr bo>\n";
		echo "<td bgcolor=\"#FFDEAD\">\n";
		echo "2&nbsp;&nbsp;\n";
		// Lazar Milosavljevicapril 2013
		$sql = "SELECT
				prezime || ' ' || ime as pim,
				jmbg
			FROM
				procenitelji
			WHERE
					(aktivan='A' AND likvidator = true)
				OR
					jmbg='$komisija2'
			ORDER BY
				prezime,ime;
			";
		$tabela = 'procenitelji';
		$polje = 'komisija2';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'pim', 'jmbg', ${$polje});
		echo "</td>\n";

		echo "<td align=\"right\" bgcolor=\"#FFDEAD\">\n";
		echo "Kraj:&nbsp;\n";
		echo "</td>\n";
		echo "<td bgcolor=\"#FFDEAD\">\n";
		echo "<input name=\"kraj2\" value=\"$kraj2\" size=\"13\" height=\"15\" onclick=\"showCal('kraj2')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr><td colspan=\"2\" class=\"tborder\" bgcolor=\"#FFDEAD\">&nbsp;</td>\n";
		echo "<td class=\"tborder\" bgcolor=\"#FFDEAD\">DA&nbsp;&nbsp;&nbsp;&nbsp;NE</td>\n";
		echo "<td>&nbsp;</td></tr>\n";

		echo "<tr>\n";
		echo "<td bgcolor=\"#FFDEAD\" >\n";
		echo "<strong>\n";
		echo "Ponuda 2\n";
		echo "</strong>\n";
		echo "</td>\n";

		echo "<td align=\"right\"  bgcolor=\"#FFDEAD\" >Prihvaæena: </td>";
		echo "<td bgcolor=\"#FFDEAD\" >\n";

		echo "<input type=\"radio\" name=\"prihvacena2\" value=\"DA\"";
		if ($prihvacena2 == "DA") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;";

		echo "<input type=\"radio\" name=\"prihvacena2\" value=\"NE\"";
		if ($prihvacena2 == "NE") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\"></td>\n";
		echo "&nbsp;";

		echo "<td>\n";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<b><u><font size=\"4\">VIII</font><u></b>\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td bgcolor=\"#FFDEAD\" colspan=\"3\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td bgcolor=\"#FFDEAD\" align=\"left\">\n";
		echo "Pregovaraè:\n";
		// Lazar Milosavljevicapril 2013
		$sql = "SELECT
				prezime || ' ' || ime as pim,
				jmbg
			FROM
				procenitelji
			WHERE
					(aktivan='A' AND likvidator = true)
				OR
					jmbg='$likvidatorPonuda2'
			ORDER BY
				prezime,ime;
			";
		$tabela = 'procenitelji';
		$polje = 'likvidatorPonuda2';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'pim', 'jmbg', ${$polje});
		echo "</td>\n";

		echo "<td bgcolor=\"#FFDEAD\" align=\"right\">\n";
		echo "Datum:&nbsp;\n";
		echo "</td>\n";
		echo "<td bgcolor=\"#FFDEAD\">\n";
		echo "<input name=\"datumPonuda2\" value=\"$datumPonuda2\" size=\"13\" height=\"15\" onclick=\"showCal('datumPonuda2')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td bgcolor=\"#FFDEAD\" colspan=\"3\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "</tr>\n";
		echo "<tr><td class=\"footerZuto2\" colspan=\"3\"></td><td class=\"footerZeleno\" colspan=\"3\"></td></tr>";
		echo "</table>\n";
		echo "</td>\n";
		echo "<td width=\"60%\" valign=\"bottom\">\n";

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" height=\"100%\" >\n";

		echo "<tr>\n";

		echo "<td >\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td colspan=\"3\" align=\"center\" valign=\"bottom\">\n";
		echo "<strong>\n";
		echo "Napomena\n";
		echo "</strong>\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";

		

		echo "<td>";
		
		// Mare 2021-04-23
		// resenja
		$sql_resenja = "SELECT * FROM resenja WHERE poz_id = $idstete AND konacno = 't' ORDER BY id DESC LIMIT 1";
		$result = pg_query($conn, $sql_resenja);
		$podaci_resenja = pg_fetch_assoc($result);
		$html_resenja = $podaci_resenja["html"];
		$resenja_id   = $podaci_resenja["id"];
		if($html_resenja != null)
		{
			$za_stampu_resenja = '<td><input type="checkbox" id="modal_resenja" name="modal_resenja" value="1" checked /></td>'; 
		}
		else 
		{
			$za_stampu_resenja = '<td><input type="checkbox" id="modal_resenja" name="modal_resenja" value="1" disabled /></td>';
		}
		
		// instrukcije
		$sql_instrukcije = "SELECT * FROM instrukcije WHERE resenja_id = $resenja_id";
		$result = pg_query($conn, $sql_instrukcije);
		$podaci_instrukcije = pg_fetch_all($result);
		$broj_instrukcija = count($podaci_instrukcije);
			for($i = 0; $i < $broj_instrukcija; $i ++)
			{
				$html_instrukcije = $podaci_instrukcije[$i]["html"];
			}
		if($html_instrukcije != null)
		{
			$za_stampu_instrukcije = '<td><input type="checkbox" id="modal_instrukcije" name="modal_instrukcije" value="2" checked /></td>'; 
		}
		else 
		{
			$za_stampu_instrukcije = '<td><input type="checkbox" id="modal_instrukcije" name="modal_instrukcije" value="2" disabled /></td>';
		}
		
		// obracun visine stete stvari
		$sql_obracun_visine_stete = "SELECT * FROM obracun_visine_stete_stvari WHERE id_stete = $idstete";
		$result = pg_query($conn, $sql_obracun_visine_stete);
		$podaci_obracun = pg_fetch_assoc($result);
		$html_obracun = $podaci_obracun["html"];
		if($html_obracun != null)
		{
			$za_stampu_obracun = '<td><input type="checkbox" id="modal_obracun_visine_stete_stvari" name="modal_obracun_visine_stete_stvari" value="4" checked /></td>'; 
		}
		else 
		{
			$za_stampu_obracun = '<td><input type="checkbox" id="modal_obracun_visine_stete_stvari" name="modal_obracun_visine_stete_stvari" value="4" disabled /></td>';
		}
		// auto dani
		$sql_auto_dani = "SELECT * FROM likvidacija_stete WHERE id_stete = $idstete";
		$result = pg_query($conn, $sql_auto_dani);
		$podaci_likvidacija = pg_fetch_assoc($result);
		$html_auto_dani  = $podaci_likvidacija["html_auto_dani"];
		$html_saglasnost = $podaci_likvidacija["html_saglasnost"];
		$html_sporazum   = $podaci_likvidacija["html_sporazum"];
		$likividacija_id = $podaci_likvidacija["id"];

		if($html_auto_dani != null)
		{
			$za_stampu_auto_dani = '<td><input type="checkbox" id="modal_auto_dani" name="modal_auto_dani" value="3" checked /></td>'; 
		}
		else 
		{
			$za_stampu_auto_dani = '<td><input type="checkbox" id="modal_auto_dani" name="modal_auto_dani" value="3" disabled /></td>';
		}
		if($html_saglasnost != null)
		{
			$za_stampu_saglasnost = '<td><input type="checkbox" id="modal_saglasnost" name="modal_saglasnost" value="8" checked /></td>'; 
		}
		else 
		{
			$za_stampu_saglasnost = '<td><input type="checkbox" id="modal_saglasnost" name="modal_saglasnost" value="8" disabled /></td>';
		}
		if($html_sporazum != null)
		{
			$za_stampu_sporazum = '<td><input type="checkbox" id="modal_sporazum" name="modal_sporazum" value="7" checked /></td>'; 
		}
		else 
		{
			$za_stampu_sporazum = '<td><input type="checkbox" id="modal_sporazum" name="modal_sporazum" value="7" disabled /></td>';
		}
		// garantno pismo
		$sql_garantno_pismo = "SELECT * FROM likvidacija_racuni WHERE likvidacija_id = $likividacija_id";
		$result = pg_query($conn, $sql_garantno_pismo);
		$podaci_lr = pg_fetch_all($result);
		$broj_lr = count($podaci_lr);
			for($i = 0; $i < $broj_lr; $i ++)
			{
				$html_garantno_pismo = $podaci_lr[$i]["html"];
			}
		if($html_garantno_pismo != null)
		{
			$za_stampu_garantno_pismo = '<td><input type="checkbox" id="modal_garantno_pismo" name="modal_garantno_pismo" value="9" checked /></td>'; 
		}
		else 
		{
			$za_stampu_garantno_pismo = '<td><input type="checkbox" id="modal_garantno_pismo" name="modal_garantno_pismo" value="9" disabled /></td>';
		}
		// tehnoloski list
		$sql_tehnoloski_list = "SELECT * FROM tehnoloski_list WHERE id_stete = $idstete";
		$result = pg_query($conn, $sql_tehnoloski_list);
		$podaci_tl = pg_fetch_assoc($result);
		$html_tehnoloski_list = $podaci_tl["html"];
		if($html_tehnoloski_list != null)
		{
			$za_stampu_tehnoloski_list = '<td><input type="checkbox" id="modal_tehnoloski_list" name="modal_tehnoloski_list" value="5" checked /></td>'; 
		}
		else 
		{
			$za_stampu_tehnoloski_list = '<td><input type="checkbox" id="modal_tehnoloski_list" name="modal_tehnoloski_list" value="5" disabled /></td>';
		}
		// totalna steta
		$sql_totalna_steta = "SELECT * FROM totalna_steta WHERE id_stete = $idstete";
		$result = pg_query($conn, $sql_totalna_steta);
		$podaci_ts = pg_fetch_assoc($result);
		$html_totalna_steta = $podaci_ts["html"];
		if($html_totalna_steta != null)
		{
			$za_stampu_totalna_steta = '<td><input type="checkbox" id="modal_totalna_steta" name="modal_totalna_steta" value="6" checked /></td>'; 
		}
		else 
		{
			$za_stampu_totalna_steta = '<td><input type="checkbox" id="modal_totalna_steta" name="modal_totalna_steta" value="6" disabled /></td>';
		}

		// obracun ao lica
		$sql_obracun_ao_lica = "SELECT * FROM obracun_visine_stete_ao_lica WHERE predmet_odstetnog_zahteva_id = $idstete";
		$result = pg_query($conn, $sql_obracun_ao_lica);
		$podaci_obracun_ao_lica = pg_fetch_assoc($result);
		$html_obracun_ao_lica = $podaci_obracun_ao_lica["html"];
		if($html_obracun_ao_lica != null)
		{
			$za_stampu_obracun_ao_lica = '<td><input type="checkbox" id="modal_ao_lica" name="modal_ao_lica" value="6" checked /></td>'; 
		}
		else 
		{
			$za_stampu_obracun_ao_lica = '<td><input type="checkbox" id="modal_ao_lica" name="modal_ao_lica" value="6" disabled /></td>';
		}

		// obracun 0205 dpz
		$sql_obracun_0205_dpz = "SELECT * FROM obracun_visine_stete_0205_dpz WHERE idstete = $idstete";
		$result = pg_query($conn, $sql_obracun_0205_dpz);
		$podaci_0205_dpz = pg_fetch_assoc($result);
		$html_0205_dpz = $podaci_0205_dpz["html"];
		if($html_0205_dpz != null)
		{
			$za_stampu_obracun_0205_dpz = '<td><input type="checkbox" id="modal_0205_dpz" name="modal_0205_dpz" value="6" checked /></td>'; 
		}
		else 
		{
			$za_stampu_obracun_0205_dpz = '<td><input type="checkbox" id="modal_0205_dpz" name="modal_0205_dpz" value="6" disabled /></td>';
		}

		// obracun n dpz
		$sql_obracun_n_dpz = "SELECT * FROM obracun_visine_stete_n_dpz WHERE predmet_odstetnog_zahteva_id = $idstete";
		$result = pg_query($conn, $sql_obracun_n_dpz);
		$podaci_n_dpz = pg_fetch_assoc($result);
		$html_n_dpz = $podaci_n_dpz["html"];
		if($html_n_dpz != null)
		{
			$za_stampu_obracun_n_dpz = '<td><input type="checkbox" id="modal_n_dpz" name="modal_n_dpz" value="6" checked /></td>'; 
		}
		else 
		{
			$za_stampu_obracun_n_dpz = '<td><input type="checkbox" id="modal_n_dpz" name="modal_n_dpz" value="6" disabled /></td>';
		}

		echo "<table style='background-color: #66ffe0;'>";
// 2021-05-20 tipSt Mare
		echo "<tr>";
		if($tipSt == '0205')
		{
			echo "<td>$za_stampu_resenja</td><td>Re¹enja</td>";
			echo "<td>$za_stampu_instrukcije</td><td>Instrukcije</td>";
			echo "<td>$za_stampu_obracun_0205_dpz</td><td>Obraèun visine ¹tete 0205 dpz</td>";
		}
		elseif($tipSt == '1001011' || $tipSt == '1001021' || $tipSt == '1001031')
		{
			echo "<td>$za_stampu_resenja</td><td>Re¹enja</td>";
			echo "<td>$za_stampu_instrukcije</td><td>Instrukcije</td>";
			echo "<td>$za_stampu_obracun_ao_lica</td><td>Obraèun visine ¹tete ao lica $tipSt</td>";
		}
		elseif($tipSt == '0103' || $tipSt == '020201')
		{
			echo "<td>$za_stampu_resenja</td><td>Re¹enja</td>";
			echo "<td>$za_stampu_instrukcije</td><td>Instrukcije</td>";
			echo "<td>$za_stampu_obracun_n_dpz</td><td>Obraèun visine ¹tete n dpz</td>";
		}
		else 
		{
			echo "<td>$za_stampu_resenja</td><td>Re¹enja</td>";
			echo "<td>$za_stampu_instrukcije</td><td>Instrukcije</td>";
			echo "<td>$za_stampu_obracun</td><td>Obraèun visine ¹tete stvari</td>";
			echo "<td>$za_stampu_auto_dani</td><td>Auto dani</td>";
			echo "<td>$za_stampu_saglasnost</td><td>Saglasnost</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>$za_stampu_tehnoloski_list</td><td>Tehnoloski list</td>";
			echo "<td>$za_stampu_totalna_steta</td><td>Totalna ¹teta</td>";
			echo "<td>$za_stampu_sporazum</td><td>Sporazum</td>";
			echo "<td>$za_stampu_garantno_pismo</td><td>Garantno pismo</td>";
			echo "<td></td>";
		}
	
		
		echo "</tr>";
		echo "</table>";
		// echo "<input type='button' value='©tampaj dokumentaciju' id='stampa_dok_likvidacija' name='stampa_dok_likvidacija'  onclick='stampaj_generisana_dok()' ><br/>";  // prebaceno dugme ispod 

		
		echo "<br>Vrste dokumenata:\n";

		//echo "<td><br><br>Vrste dokumenata:\n";
		//Izmenio Marko Stankovic13.03.2018. dodat uslov i u select
		$io_sifra = substr($tipSt, 0, 2);

		echo "<select style='width:110px' id='lista_dokumenti' name='lista_dokumenti'>";
		echo "<option value='-1'>Izaberite</option>";
		echo '';
		if ($sudski_postupak_id == null) {
			//Marko Stankovoc dodao 31.05.2018 dodat uslov $brsasOst  da je prazan kako bi uso u obracun visine stete

			if ($vrstaSt == 'AO' && (substr($sifra, -1) == '1') || ($vrstaSt == 'IO' && $io_sifra == '13' && $brsasOst == '')) {
				echo "<option value='obracun'>Obraèun visine ¹tete na licima</option>";
			}
			$obracun_za = ($vrstaSt == 'N') ? 'nezgodu ¹tetu' : 'dobrovoljno zdravstveno osiguranje';
			if ($vrstaSt == 'N' || ($vrstaSt == 'DPZ' && substr($tipSt, 0, 4) == '0202')) {
				echo "<option value='obracun_n_dpz'>Obraèun visine ¹tete po osnovu zahteva za $obracun_za</option>";
			}
			if ($vrstaSt == 'DPZ' && $tipSt == '0205') {
				echo "<option value='obracun_0205_dpz'>Obraèun visine ¹tete po osnovu zahteva za putno zdravstveno osiguranje</option>";
			}
			if ($vrstaSt == 'IO' && $tipSt == '0903') {
				echo "<option value='resenje_0903'>Re¹enje zahteva za isplatu za domaæinstvo</option>";
			}
		} else {
			//echo "<input type='button'  value='Resenje zahteva sudski postupak' onclick='resenje_zahteva_sudski()';>";
		}
		echo "</select>";
		echo "<input type='button' value='Kreiraj' id='dokumenti' name='dokumenti' onclick='otvori_formu_za_kreiranje_dokumenata()'>\n";
		echo "<input type='submit' hidden value='Kreiraj obraèun' id='obracun_visine_stete' name='obracun_visine_stete'>\n";
		echo "<input type='submit' hidden value='Kreiraj obraèun' id='obracun_visine_stete_n_dpz' name='obracun_visine_stete_n_dpz'>\n";
		echo "<input type='submit' hidden value='Kreiraj obraèun' id='obracun_visine_stete_0205_dpz' name='obracun_visine_stete_0205_dpz'>\n";
		echo "<input type='submit' hidden value='Kreiraj obraèun' id='resenje_IO_0903' name='resenje_IO_0903'>\n";

		// 2021-04-23
		echo "<input type='button' value='©tampaj obele¾enu dokumentaciju' id='stampa_dok_likvidacija' name='stampa_dok_likvidacija' onclick='stampaj_generisana_dok()' ><br/>";

		echo "<td colspan=\"3\"  align=\"left\" valign=\"middle\">\n";
		echo "&nbsp;\n";
		$napomena = str_replace("\\", "", $napomena);
		echo "<textarea rows=\"4\" cols=\"60\"  name=\"napomena\" style=\"resize:none\">\n";
		echo $napomena . "</textarea>\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td >\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"nacin_resavanja\" value=\"S\"";
		if ($nacin_resavanja == "S") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "Sporazumno</td>\n";

		echo "<td colspan=\"3\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td >\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"nacin_resavanja\" value=\"N\"";
		if ($nacin_resavanja == "N") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "Nesporni deo</td>\n";

		echo "<td bgcolor=\"#CCCCCC\" width=\"150\">\n";
		echo "<strong>\n";
		echo "Podaci o isplati\n";
		echo "</strong>\n";

		echo "</td>\n";

		echo "<td width=\"140\" bgcolor=\"#CCCCCC\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td width=\"140\" bgcolor=\"#CCCCCC\">\n";
		// dodat check list za reosiguranje  2016-03-22
		// bane, slavenko, borko, vidak, vesna, nebojsa
		//$disabled = ($radnik != 151 || $radnik == 138 || $radnik == 3045 || $radnik ==3036 || $radnik == 2059 || $radnik ==3041) ? '' : 'disabled="disabled"' ;
		$disabled = ($radnik == 151 || $radnik == 138 || $radnik == 3045 || $radnik == 3036 || $radnik == 2059 || $radnik == 3071 || $radnik == 3064  || $radnik == 3085 || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125) ? '' : 'readonly="readonly"';
		echo "<input name='prijaviti_u_reosiguranje' id='prijaviti_u_reosiguranje' type='checkbox' value='1' ";
		if ($prijaviti_u_reosiguranje == 1) {
			echo " checked $disabled ";
		}
		echo "onkeypress='return handleEnter(this, event)'>";
		//echo "<strong>\n";
		echo "&nbsp;<label style=' font-weight: bold; font-size:12px;'>REOSIGURANJE</label>\n";
		//echo "<strong>\n";
		echo "<hr color='#000000'>";
		echo "</td>\n";

		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td >\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"nacin_resavanja\" value=\"A\"";
		if ($nacin_resavanja == "A") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "Akontacija</td>\n";

		echo "<td width=\"130\" bgcolor=\"#CCCCCC\">\n";
		echo "Zahtevano\n";
		echo "</td>\n";

		echo "<td width=\"200\" bgcolor=\"#CCCCCC\">\n";
		echo "&nbsp; \n";
		echo "<input name=\"zahtevano\" value=\"$zahtevano\" size=\"13\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "<input name=\"gotovina\" type=\"checkbox\" value=\"true\" ";
		if ($gotovina == true) {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;1.&nbsp;Gotovina\n";
		echo "</td>\n";
		echo "</tr>\n";
		//Branka 09.11.2015 - dodato pocetak

		echo "<tr>\n";
		echo "<td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "Rezervacije\n";
		echo "</td>\n";
		require_once '../common/stete_pravo_radnici.php';
		if (in_array($radnik, $radnici_koji_mogu_videti_rezervacije)) {
			$deo_upita = "";
		} else {
			$deo_upita = "LIMIT 1";
		}
		echo "&nbsp; \n";


		echo "<td bgcolor=\"#CCCCCC\" id='rezervacija_lista_id'>\n";

		$sql_rezervacije_unete = "SELECT rbr,rezervisano,datum_od FROM rezervacije  WHERE idstete=$idstete ORDER BY datum_od desc, rbr desc  $deo_upita";
		$result_rezervacije_unete = pg_query($conn, $sql_rezervacije_unete);
		$podaci_rezervacije_unete = pg_fetch_all($result_rezervacije_unete);

		echo "&nbsp;&nbsp;<select name=\"rezervisano_lista\"  id=\"rezervisano_lista\"  style='width:120px'>\n";
		echo "<option>Datum i iznos";
		echo "</option>";
		for ($j = 0; $j < count($podaci_rezervacije_unete); $j++) {
			$datum_od_lista = $podaci_rezervacije_unete[$j]['datum_od'];
			$datum_od_lista = date("d.m.Y", strtotime($datum_od_lista));
			$rezervisano_lista = $podaci_rezervacije_unete[$j]['rezervisano'];
			$rbr_lista = $podaci_rezervacije_unete[$j]['rbr'];
			if ($podaci_rezervacije_unete) {
				echo "<option>$datum_od_lista - $rezervisano_lista";
				echo "</option>";
			} else {
				echo "<option>Nema unetih rezervacija";
				echo "</option>";
			}
		}



		echo "</select>";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "</td>\n";
		echo "</tr>\n";


		// Marko Markovic
		echo "<tr>\n";
		echo "<td>";
		echo "</td>";
		echo "<td style='background-color:CCCCCC;' colspan='2'>\n";

		// Marko Markovic dugme koje ce otvoriti prozor za izmenu datuma i iznosa rezervacija
		// pristup omogucen radnicima iz liste
		// 138-Milanovic Slavenko, 151-CveticBranko, 2059-Milicevic Vesna, 3045-Bogdanovic Borko, 3064-Blagojevic Djordje
		//Dodati Mandic Sasa i Darko Braunovic - Nemanja Jovanovic 09-07-2020
		$niz_radnika = array(138, 151, 2059, 3045, 3064, 3029, 3033);
		if (in_array($radnik, $niz_radnika)) {
			if ($nalog == '') {
				echo "<input type='button' style='margin-left:20px;' value='Izmena rezervacije' name='izmeni_dat_izn_rez' id='izmeni_dat_izn_rez' onclick='izmeni_datum_iznos_rez()' />";
			}
		}
		echo "</td>\n";
		echo "<td style='background-color:CCCCCC;'>";
		echo "</td>";
		echo "<td style='background-color:CCCCCC;'>";
		echo "</td>";
		echo "</tr>\n";
		// Marko Markovic kraj

		//Branka 09.11.2015 - dodato kraj
		echo "<tr>\n";
		echo "<td>\n";
		echo "&nbsp;\n";
		// Marko Markovic dugme za modal faza likvidacija dodatna napomena 2019-12-06 
		// $unosivaci = array(151,138,3093,3055,3052,3053,3045,3033,3039,3093,3042, 3067,3083,3078,3044,3038,3090,3023,3081,3079,122,3029,3054,3024,3046,3085,3032,3070,2253,3116,3101,3080,2119,2224,3069,3043,3016,3004,3102,3106);
		$conn_amso = pg_connect("host=localhost dbname=amso user=zoranp");
		$sql_unosivaci = "SELECT radnik FROM radnik WHERE faza_stete is not null";
		$rezultat      = pg_query($conn_amso, $sql_unosivaci);
		$niz_unosivaci = pg_fetch_all($rezultat);
		$brunosivaca   = pg_num_rows($rezultat);
		for ($i = 0; $i < $brunosivaca; $i++) {
			$unosivaci[] = $niz_unosivaci[$i]['radnik'];
		}
		if (in_array($radnik, $unosivaci)) {
			echo "<input type='button' onclick='otvori_dodatne_napomen(4);' id='napomena_likvidacija_faza' name='napomena_likvidacija_faza' style='height:30px; width:200px; font-size:13px; margin:0px;' text-align='center' value='Napomena za sajt dru¹tva' />";
		}
		// Marko Markovic kraj
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";

		echo "Unesite novu rezervaciju\n";
		echo "</td>\n";
		echo "<td bgcolor=\"#CCCCCC\" id='rezervacija_id'>\n";
		echo "&nbsp;\n";

		/* 
Dodeljivanje iznosa rezervacije Nemanja Jovanovic
*/

		$sql_rezervacije	= "	SELECT 
							count(*)::integer AS broj_rezervacija 
						FROM 
							rezervacije 
						WHERE 
							idstete = $idstete";

		$rez_rezervacije	= pg_query($conn, $sql_rezervacije);
		$niz_rezervacije	= pg_fetch_assoc($rez_rezervacije);
		$broj_rezervacija  	= $niz_rezervacije['broj_rezervacija'];

		if ($broj_rezervacija == 0) {
			$ao_lica 	= array('1001011', '1001021', '1001031', '1001041', '1001051', '1001061', '1001071', '1001081', '1001091', '1001101', '1001111', '1001121', '1001131', '1001141');
			$ao_stvari 	= array('1001012', '1001022', '1001032', '1001042', '1001052', '1001062', '1001072', '1001082', '1001092', '1001102', '1001112', '1001122', '1001132', '1001142');


			if ($tipSt == '0301') {
				$rezervisano_predlog = 80000.00;
			} else if (in_array($tipSt, $ao_lica)) {
				$rezervisano_predlog = 200000.00;
			} else if (in_array($tipSt, $ao_stvari)) {
				$rezervisano_predlog = 100000.00;
			}
		}

		/* 
Kraj
*/

		echo "<input name=\"rezervisano\" size=\"13\" height=\"15\" onkeypress=\"return handleEnter(this, event)\" value='$rezervisano_predlog'>\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "<input name=\"virman\" type=\"checkbox\" value=\"true\" ";
		if ($virman == true) {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;2.&nbsp;Virman\n";
		echo "</td>\n";
		echo "</tr>\n";



		// Izmena zbog prigovora
		// Lazar Milossavljeviæ - 08-02-2013
		echo "<tr>\n";
		echo "<td  >\n";
		echo "<div id='osnovan_po_prigovoru_na_visinu_odstete_osnovan_div' style='display:$prikaz_prigovor_na_visinu_stete' >\n";
		echo "&nbsp;\n";
		echo "<input type='radio' id='osnovan_po_prigovoru_na_visinu_odstete_osnovan' name='osnovan_po_prigovoru_na_visinu_odstete' value='O' ";
		if ($prigovor_osnovan == "O") {
			echo " checked ";
		}
		echo "onkeypress='return handleEnter(this, event)'>\n";
		echo "Osnovan po prigovoru\n";
		echo "</div>\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\"><b>\n";
		echo "Re¹eno - fazno\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#CCCCCC\" id='reseno_fazno'>&nbsp;\n";
		$sql = "select rbr || '. - ' || iznos as stav, idisp from isplate where idstete=$idstete order by rbr desc";
		$tabela = 'isplate';
		$polje = 'nalogIznos';
		${$polje} = isset(${$polje}) ? ${$polje} : '';
		drop_kombo0('', $sql, $polje, $conn, $tabela, 'stav', 'idisp', ${$polje});
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "<input name=\"doznaka\" type=\"checkbox\" value=\"true\" ";
		if ($doznaka == true) {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;3.&nbsp;Doznaka\n";
		echo "</td>\n";

		echo "</tr>\n";

		// Izmena zbog prigovora
		// Lazar Milosavljevic - 08-02-2013
		echo "<tr>\n";
		echo "<td >\n";
		echo "<div id='osnovan_po_prigovoru_na_visinu_odstete_delimicno_div' style='display:$prikaz_prigovor_na_visinu_stete' >\n";
		echo "&nbsp;\n";
		echo "<input type='radio' id='osnovan_po_prigovoru_na_visinu_odstete_delimicno' name='osnovan_po_prigovoru_na_visinu_odstete' value='D' ";
		if ($prigovor_osnovan == "D") {
			echo " checked ";
		}
		echo "onkeypress='return handleEnter(this, event)'>\n";
		echo "Delimièno re¹en";
		echo "&nbsp;<input id='delimicno_resen_po_prigovoru_procenat' name='delimicno_resen_po_prigovoru_procenat' value=\"$prigovor_procenat\" size='3' height='15' onkeypress='return samoBrojevi(this, event);'>\n";
		echo "&nbsp;%";
		echo "</div>\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\"><b>\n";
		echo "Re¹eno - ukupno\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#CCCCCC\" id='isplaceno_id'>\n";
		echo "&nbsp; \n";
		echo "<input name=\"isplaceno\" id=\"isplaceno\" value=\"$isplaceno\" size=\"13\" height=\"15\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "<input name=\"kompenzacija\" type=\"checkbox\" value=\"true\" ";
		if ($kompenzacija == true) {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "&nbsp;4.&nbsp;Kompenzacija\n";
		echo "</td>\n";
		echo "</tr>\n";

		// Izmena zbog prigovora - zakomentarisano <tr> nekoliko redova iznad (14 redova)
		// Lazar Milosavljevic - 08-02-2013
		echo "<tr>\n";
		echo "<td >\n";
		echo "<div id='osnovan_po_prigovoru_na_visinu_odstete_odbijen_div' style='display:$prikaz_prigovor_na_visinu_stete' >\n";
		echo "&nbsp;\n";
		echo "<input type='radio' id='osnovan_po_prigovoru_na_visinu_odstete_odbijen' name='osnovan_po_prigovoru_na_visinu_odstete' value='S' ";
		if ($prigovor_osnovan == "S") {
			echo " checked ";
		}
		echo "onkeypress='return handleEnter(this, event)'>\n";
		echo "Odbijen po prigovoru";
		echo "</div>\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\"><b>\n";
		echo "Datum kon. re¹avanja\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#CCCCCC\" id='nalog_id'>\n";
		echo "&nbsp; \n";
		// echo "<input name=\"nalog\" id=\"nalog\" value=\"$nalog\" size=\"13\" height=\"15\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\">\n";
		// echo "<b><u><font size=\"4\">IX</font><u></b></td>\n";
		/*Dodao Bogdan Goluboviæ 29.03.2018*/
		/*Doradio: Lazar Milosavljevic2018-10-16*/
		echo "<input name=\"nalog\" id=\"nalog\" value=\"$nalog\" size=\"13\" maxlength=\"10\" height=\"15\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\" title=\"Datum kon. re¹avanja \" />\n";
		echo "<b><u><font size=\"4\">IX</font></u></b>";
		/*Dodao Bogdan Goluboviæ 29.03.2018*/
		/*Kraj Bogdan Goluboviæ 29.03.2018*/

		echo "<td bgcolor=\"#CCCCCC\">\n";
		if ($dugme == 'DA') {
			echo "<input type=\"submit\" value=\"Pregled naloga\" class=\"button\" name=\"nalozi\" id=\"nalozi_dugme\">\n";
		}
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr><td>\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\"><b>\n";
		echo "Datum kompletiranja\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "&nbsp; \n";
		echo "<div id=\"dat_kon\">";
		if ($komda) {
			echo "<input id='datumKompl' name=\"datumKompl\" value=\"$datumKompl\" size=\"15\" height=\"20\" onclick=\"showCal('datumKompl')\" disabled=\"true\" class=\"disabled\" onkeypress=\"return handleEnter(this, event)\">\n";
		} else {
			echo "<input id='datumKompl' name=\"datumKompl\" value=\"$datumKompl\" size=\"15\" height=\"20\" onclick=\"showCal('datumKompl')\" onkeypress=\"return handleEnter(this, event)\">\n";
		}
		$radnik_niz_izmena_datuma_kompletiranja = array(2059, 3071, 151, 3064, 3085, 3090, 3106, 2244, 2106, 3126, 3125);
		if (in_array($radnik, $radnik_niz_izmena_datuma_kompletiranja) && $mogucnostIzmeneDatumaKompletiranja && $datumKompl != null && ($datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete != null || $pravniOsnovDatumKompletiranjaDokumentacije != null)) {
			echo '<input id="izmeni_dat_kon" type="checkbox" onclick="prikaz_dugme_dat_kon()"></div></td>';
		} else {
			echo "</td>\n";
		}

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "<input type='button' value='Potpisivanje re¹enja' class=\"button\" name='pregled_instrukcija' id='pregled_instrukcija' onclick='otvori_pregled_instrukcija($idstete)'>";
		echo "</td></tr>\n";

		echo "<tr>\n";

		echo "<td >\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"nacin_resavanja2\" value=\"S\"";
		if ($nacin_resavanja2 == "S") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "Sporazumno</td>\n";


		echo "<td bgcolor=\"#CCCCCC\" colspan=\"3\">\n";
		echo "<hr color=\"#000000\">\n";
		echo "</td>\n";
		echo "</tr>\n";


		echo "<tr>\n";
		echo "<td >\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"nacin_resavanja2\" value=\"N\"";
		if ($nacin_resavanja2 == "N") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "Nesporni deo</td>\n";

		if ($isplaceno) {

			$sqlisp = "select rbr from isplate where idstete=$idstete and datum_isplate isnull";
			$rezultatisp = pg_query($conn, $sqlisp);
			$nizisp = pg_fetch_assoc($rezultatisp);

			$isp = $nizisp['rbr'];

			if ($isp) {
				$poruka = 'NE';
			} else {
				$poruka = 'DA';
			}
		} else {
			$poruka = 'NE';
		}

		$sqlisp = "select sum(isplaceni_iznos) as sumisp from isplate where idstete=$idstete and datum_isplate notnull";
		$rezultatisp = pg_query($conn, $sqlisp);
		$nizisp = pg_fetch_assoc($rezultatisp);

		$sumisp = $nizisp['sumisp'];


		echo "<td bgcolor=\"#CCCCCC\"><b>\n";
		echo "ISPLAÆENO\n";
		echo "</b></td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "&nbsp; \n";
		echo "<b><font size=\"4\">$poruka &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>X</u></font></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iznos</td>\n";


		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "<input name=\"sumisp\" value=\"$sumisp\" size=\"13\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td >\n";
		echo "&nbsp;\n";
		echo "<input type=\"radio\" name=\"nacin_resavanja2\" value=\"A\"";
		if ($nacin_resavanja2 == "A") {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "Akontacija</td>\n";

		echo "<td height=\"23\" bgcolor=\"#CCCCCC\" colspan=\"3\">\n";
		echo "<hr color=\"#000000\">\n";
		echo "</td>\n";
		echo "</tr>\n";


		echo "<tr>\n";
		echo "<td bgcolor=\"#CCCCCC\" align=\"left\">\n";
		echo "ODUSTAO\n";
		echo "<input name=\"odustao\" type=\"checkbox\" value=\"true\"  id='odustao'";
		if ($odustao == true) {
			echo " checked ";
		}
		echo "onkeypress=\"return handleEnter(this, event)\">\n";
		echo "SP- \n";
		echo "<input name=\"sp\" value=\"$sp\" size=\"6\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "Arhivirano\n";
		echo "</td>\n";
		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "&nbsp; \n";
		echo "<input name=\"arhivirano\" value=\"$arhivirano\" size=\"13\" height=\"15\" onclick=\"showCal('arhivirano')\" onkeypress=\"return handleEnter(this, event)\">\n";
		echo "<b><font size=\"4\">&nbsp;&nbsp;<u>XI</font><u></b></td>\n";

		echo "<td bgcolor=\"#CCCCCC\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";

		echo "<tr><td bgcolor=\"#CCCCCC\" align=\"left\">\n";

		echo "STORNO\n";
		echo "&nbsp;\n";
		echo "<input name=\"storno\" type=\"checkbox\" value=\"1\" onClick=\"JesiSiguran();\"";
		if ($storno == 1) {
			echo " checked";
		}
		echo "></td>\n";

		echo "<td bgcolor=\"#CCCCCC\" align=\"left\">\n";
		echo "TOTALNA ©TETA ";
		echo "<input name=\"totalnaSteta\" type=\"checkbox\" value=\"1\"";
		if ($totalnaSteta == 1) {
			echo " checked";
		}
		echo ">\n";

		echo "</td>";

		echo "<td bgcolor=\"#CCCCCC\" align=\"left\">\n";
		echo "PO RACUNU ";
		echo "<input name=\"totalnaSteta\" type=\"checkbox\" value=\"1\"";
		if ($totalnaSteta == 1) {
			echo " checked";
		}
		echo ">\n";

		echo "</td>";
		echo "<tr><td colspan=\"4\" class=\"footerSivo\"></td></tr>";
		echo "</tr>\n";
		echo "</table>\n";

		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";




		// Marko Markovic --- ispis u novom prozorcetu podataka za izmenu datuma i iznosa poslednje rezervacije 
		$conn_stete = pg_connect("host=localhost dbname=stete user=zoranp");
		$sql_ispis_dat_izn_rez = "	SELECT 
								datum_od, 
								rezervisano 
							FROM 
								rezervacije 
							WHERE 
								idstete= '$idstete' AND 
								rbr = (SELECT MAX(rbr) AS rbr FROM rezervacije WHERE idstete= '$idstete')";

		$rezultat_datum_izn_rez = pg_query($conn_stete, $sql_ispis_dat_izn_rez);
		$rezultat_rez_niz = pg_fetch_array($rezultat_datum_izn_rez);
		$datum_od_rez = $rezultat_rez_niz['datum_od'];
		$iznos_rez = $rezultat_rez_niz['rezervisano'];
		// Marko Markovic kraj ispisa posledenje rezervacije (datum i iznos)

		$sql_datum_zakljucan_period = " WITH provera_datuma AS (
WITH pocetak_meseca AS
(
SELECT CASE WHEN ((EXTRACT (DAY FROM current_date))) >'05' THEN  ((EXTRACT (YEAR FROM current_date))||'-'||(EXTRACT (MONTH FROM current_date))||'-01')::DATE
ELSE ((EXTRACT (YEAR FROM current_date))||'-'||(EXTRACT (MONTH FROM current_date)-1)||'-01')::DATE END  AS pocetak_meseca
)
SELECT CASE WHEN '$datum_od_rez' > (SELECT pocetak_meseca FROM pocetak_meseca) THEN '$datum_od_rez' ELSE (SELECT pocetak_meseca FROM pocetak_meseca) END  AS min_datum,
(SELECT pocetak_meseca FROM pocetak_meseca ) as minimalni_datum,
current_date AS max_datum
)
SELECT
CASE
WHEN '$datum_od_rez'::date between minimalni_datum AND max_datum THEN true ELSE false
END AS provera
FROM provera_datuma  ";

		$rezultat_provere_datuma_zakljucan_period = pg_query($conn_stete, $sql_datum_zakljucan_period);
		$rezultat_provere_niz_zakljucan_period = pg_fetch_array($rezultat_provere_datuma_zakljucan_period);

		$provera = $rezultat_provere_niz_zakljucan_period['provera'];

		//-----------------------------------------------------------------------------

		/*<form action="" style="width: 70%; padding: 10px; margin: 2px 0 22px 0; border: none; background: #f1f1f1;"></form>*/
		// Marko Markovic forma za izmenu...
		echo '	<div id="izmeni_datum_rez" style="display: none;  right: 540px; background-color:#ffbada;  position: fixed; bottom: 300; border: 3px solid #f1f1f1; z-index: 9;">
			
				<table>
					<tr>
						<td colspan="2"><label for="naslov"><b>Datum i iznos rezervaije</b></label><td>
					</tr>
					<tr>
						<td><label for="datum_rez">Datum rezervacije:</label></td>';

		if ($provera == 'f') {
			echo '<td style="width:50px;"><input type="text" name="datum_rez" id="datum_rez" value="' . $datum_od_rez . '" disabled style="width:120px;"/></td>';
		} else {
			echo '<td ><input type="text" name="datum_rez" id="datum_rez" value="' . $datum_od_rez . '" style="width:120px;" /></td>';
		}

		echo '	</tr>
		<tr>
			<td><label for="iznos_rez">Iznos rezervacije:</label></td>';

		if ($provera == 'f') {
			echo '<td><input type="text" name="iznos_rez" id="iznos_rez" style="text-align: right; width:120px;" value="' . $iznos_rez . '" disabled></td>';
		} else {
			echo '<td><input type="text" name="iznos_rez" id="iznos_rez" style="text-align: right; width:120px;" value="' . $iznos_rez . '" ></td>';
		}

		echo '	</tr>
		<tr>
			<td><button type="button" onclick="izmeni_dat_iznos_rez()" >Snimi izmenu</button></td>
			<td><button type="button" onclick="zatvori_prozor()" >Zatvori</button></td>
		</tr>
	</table>

</div>';
		// Marko Markovic kraj forme za izmenu...



		echo "<hr color=\"#000000\">\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tr bgcolor=\"#CCCCCC\">\n";
		echo "<td>\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\">\n";
		echo "<tr>\n";
		echo "<td align=\"left\" width=\"50%\">\n";
		echo "&nbsp;\n";

		if (in_array($radnik, $v)) {

			echo "<label>®elite da otvorite jo¹ jedan predmet na istom od¹tetnom zahtevu? </label>";
			echo "<input type=\"checkbox\" value=\"novi_predmet\"  name=\"novi_predmet\" id=\"novi_predmet\"  onclick='otvori_polja_za_jmbg_i_tip($odstetni_zahtev_id);' >\n";
			echo "</br>";
			echo "</br>";
		}
		echo "</td>\n";
		echo "<td align=\"right\" width=\"50%\">\n";

		echo "&nbsp;&nbsp;\n";

		if ($dugme == 'DA') {
			echo "<input type=\"submit\" value=\"Izmeni\" class=\"button\" id=\"izmeni\" name=\"izmeni\" onclick='return proveriUnosOdstetnogZahteva();'>\n";
		}

		echo "&nbsp;&nbsp;\n";
		echo "<input type=\"submit\" value=\"Odustani\" class=\"button\" name=\"odustani\">\n";
		echo "&nbsp;\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";

		echo "<label id='label_jmbg' style='display: none;'>JMBG/PIB o¹teæenog:</label>";
		echo "<input id='jmbg_ostecenog' style='display: none; width:130px' name='jmbg_ostecenog' maxlength='13' value=''/>";
		echo "&nbsp&nbsp&nbsp";
		echo "<label id='label_tip_predmeta' style='display: none;' >Tip predmeta od¹tetnog zahteva:</label>";




		//19.01.2015
		$sql_vrsta_osiguranja = "select vrsta_osiguranja, numericka_vrsta_osiguranja from odstetni_zahtev where id=$odstetni_zahtev_id";
		$rezultat_vrsta_osiguranja = pg_query($conn, $sql_vrsta_osiguranja);
		$niz_vrsta_osiguranja = pg_fetch_assoc($rezultat_vrsta_osiguranja);
		$vrsta_osiguranja = $niz_vrsta_osiguranja['vrsta_osiguranja'];
		$numericka_vrsta_osiguranja = $niz_vrsta_osiguranja['numericka_vrsta_osiguranja'];

		echo "<select name='tip_predmeta_tarife' id='tip_predmeta_tarife' style='display: none;' onclick='prikazi_polje_za_rentu(this.value)'>";
		echo "<option value='-1' >Izaberite</option>";

		//branka 23.12.2016.
		//foreach ($tip_osiguranja as $tip)
		//{
		//echo "<option value='" . $tip . "'>$vrsta_osiguranja</option>";
		//  	}
		echo $vrsta_osiguranja;
		$tipovi = vrati_tipove_odstetnih_zahteva($vrsta_osiguranja, $vrsta_obrasca, $numericka_vrsta_osiguranja, $broj_polise, false);
		echo $tipovi;
		echo "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<input style='display:none;' id='renta_check_box' type='checkbox'><label id='renta_label' style='font-size:10pt;display:none;'>Renta</label>";

		//}


		// if ($vrsta_osiguranja=='AK')
		// {
		// 	$tip_osiguranja = array(1 => 'P', 2 => 'D');
		// }
		// else if($vrsta_osiguranja=='DPZ')
		// {
		// 	$tip_osiguranja = array(1 => 'HI', 2 => 'TB', 3 => '0205');
		// }
		// else if($vrsta_osiguranja=='IO')
		// {
		// 	$tip_osiguranja = array(1 => 'S');
		// }
		// else if($vrsta_osiguranja=='N')
		// {
		// 	$tip_osiguranja = array(1 => 'N');
		// }
		// else
		// {
		// 	$tip_osiguranja = array(1 => 'L', 2 => 'S', 3 => 'R');
		// 	//$tip_osiguranja = array(1 => 'L', 2 => 'S', 3 => 'RÃ¯Â¿Å“-L', 4 => 'RÃ¯Â¿Å“-S');

		// }

		// echo "<select name='tip_predmeta' id='tip_predmeta'  style='width: 100px; font-size:12px; display: none; '>
		// <option value='-1' >Izaberite</option>";
		// foreach ($tip_osiguranja as $tip)
		// {
		// echo "<option value='" . $tip . "'>$tip</option>";
		// }

		// echo "</select>";
		// echo "&nbsp&nbsp&nbsp";
		echo "&nbsp;&nbsp;&nbsp;<input type=\"button\" style='display: none;  font-weight:bold;'  value=\"Otvori novi predmet\" id='dugme_novi_predmet'  class=\"button\" name=\"dugme_novi_predmet\" onclick='provera_postojecih_predmeta1($odstetni_zahtev_id,$id_stetnog_dogadjaja)'>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "<hr color=\"#000000\">\n";
	}

	//if ($prethodne && $brPolise){ require "prethodnes0.php";}
	if ($prethodne) {
		require "prethodnes0.php";
	}

	if ($dokumentacija) {
		require "dokumentacija.php";
	}
	if ($galerija) {
		require "galerija.php";
	}
	if ($dugme_resenje_odbijen) {
		require "resenje_zahteva_odbijen.php";
	}
	if ($dugme_odluka) {
		require "odluka_po_prigovoru.php";
	}
	if ($dugme_odluka_likvidacija) {
		require "odluka_o_prigovoru_likvidacija.php";
	}
	if ($dugme_dopisi) {
		require "zahtev_za_dostavu_dokumentacije.php";
	}
	if ($odbijenica_likvidacija) {
		require "kreiranje_odbijenice_likvidacija.php";
	}


	if ($lekarski_nalaz) {
		require "lekarski_nalazi.php";
	}
	if ($obracun_visine_stete) {
		require "obracun_visine_stete.php";
	}
	if ($obracun_visine_stete_n_dpz) {
		require "obracun_visine_stete_n_dpz.php";
	}
	if ($obracun_visine_stete_0205_dpz) {
		require "obracun_visine_stete_0205_dpz.php";
	}
	if ($resenje_IO_0903) {
		require "resenje_IO_0903.php";
	}
	// BRANKA - dodato za otvaranje forme za kreiranje dopisa
	if ($dugme_kreiraj_dopis) {
		require "dopisi.php";
	}
	if ($dugme_pregledaj_dopise) {
		require "pregled_dopisa.php";
	}
	if ($nalozi) {
		require "isplate.php";
	}
	if ($zapisnik) {
		if ($uzrok_baza) {
			require "zapisnik_o_ostecenju_vozila.php";
		} else {
			echo "<script>
		alert('Izaberite rizik i uzrok');
		vrati_nazad($idstete);
		</script>";
			// Marko Markovic - dodato da mora da se izabere rizik i uzrok
		}
	}
	//zapisnik_o_ostecenju_vozila

	echo "<input type=\"hidden\" name=\"prvaUpotreba\" value=\"$prvaUpotreba\">\n";
	echo "<input type=\"hidden\" name=\"vrstaVozila\" value=\"$vrstaVozila\">\n";
	echo "<input type=\"hidden\" name=\"zemljaProizv\" value=\"$zemljaProizv\">\n";
	echo "<input type=\"hidden\" name=\"marka\" value=\"$marka\">\n";
	echo "<input type=\"hidden\" name=\"tip\" value=\"$tip\">\n";
	echo "<input type=\"hidden\" name=\"model\" value=\"$model\">\n";
	echo "<input type=\"hidden\" name=\"sifraVoz\" value=\"$sifraVoz\">\n";
	echo "<input type=\"hidden\" name=\"cena\" value=\"$cena\">\n";
	echo "<input type=\"hidden\" name=\"procAmortizacije\" value=\"$procAmortizacije\">\n";
	echo "<input type=\"hidden\" name=\"vrednost\" value=\"$vrednost_vozilo\">\n";

	echo "<input type=\"hidden\" name=\"brSasije\" value=\"$brSasije\">\n";
	echo "<input type=\"hidden\" name=\"brMotora\" value=\"$brMotora\">\n";
	echo "<input type=\"hidden\" name=\"snagakw\" value=\"$snagakw\">\n";
	echo "<input type=\"hidden\" name=\"ccm\" value=\"$ccm\">\n";
	echo "<input type=\"hidden\" name=\"masa\" value=\"$masa\">\n";
	echo "<input type=\"hidden\" name=\"vrGoriva\" value=\"$vrGoriva\">\n";
	echo "<input type=\"hidden\" name=\"boja\" value=\"$boja\">\n";
	echo "<input type=\"hidden\" name=\"karoserija\" value=\"$karoserija\">\n";
	echo "<input type=\"hidden\" name=\"brVrata\" value=\"$brVrata\">\n";
	echo "<input type=\"hidden\" name=\"brRegMesta\" value=\"$brRegMesta\">\n";

	if (!$vozilo_dugme) {
		echo "<input type=\"hidden\" name=\"cb1\" value=\"$cb1\">\n";
		echo "<input type=\"hidden\" name=\"cb2\" value=\"$cb2\">\n";
		echo "<input type=\"hidden\" name=\"cb3\" value=\"$cb3\">\n";
		echo "<input type=\"hidden\" name=\"cb4\" value=\"$cb4\">\n";
		echo "<input type=\"hidden\" name=\"cb5\" value=\"$cb5\">\n";
		echo "<input type=\"hidden\" name=\"cb6\" value=\"$cb6\">\n";
		echo "<input type=\"hidden\" name=\"cb7\" value=\"$cb7\">\n";
		echo "<input type=\"hidden\" name=\"cb8\" value=\"$cb8\">\n";
		echo "<input type=\"hidden\" name=\"cb9\" value=\"$cb9\">\n";
		echo "<input type=\"hidden\" name=\"cb10\" value=\"$cb10\">\n";
		echo "<input type=\"hidden\" name=\"cb11\" value=\"$cb11\">\n";
		echo "<input type=\"hidden\" name=\"cb12\" value=\"$cb12\">\n";
	}

	echo "<input type=\"hidden\" name=\"foto\" value=\"$foto\">\n";
	echo "<input type=\"hidden\" name=\"opisOst\" value=\"$opisOst\">\n";


	if ($vozilo_dugme || $submitk1 || $submitk2 || $submitk3 || $submitk4 || $submitk5 || $odustanik1 || $odustanik2 || $odustanik3 || $odustanik4 || $odustanik5 || $pronadji_kat) {
		if ($pronadji_kat) {
			$izbor = 'DA';
		} // else{$izbor='NE';}
		if ($vozilo_dugme) {
			if ($prvaUpotreba  || $vrstaVozila || $zemljaProizv || $marka || $tip || $model || $sifraVoz || $cena || $procAmortizacije ||  $vrednost_vozilo || $brSasije || $brMotora || $snagakw || $ccm || $masa || $vrGoriva || $boja || $karoserija || $brVrata || $brRegMesta || $foto || $opisOst || $cb1 || $cb2 || $cb3 || $cb4 || $cb5 || $cb6 || $cb7 || $cb8 || $cb9 || $cb10 || $cb11 || $cb12) {
				$izbor = 'NE';
			} else {
				$izbor = 'DA';
			}
		}
		$izmeni == true;
		echo "<input type=\"hidden\" name=\"idstete\" value=\"$idstete\">\n";
		echo "<input type=\"hidden\" name=\"datumEvid\" value=\"$datumEvid\">\n";
		if ($prida) {
			echo "<input type=\"hidden\" name=\"datumPrijave\" value=\"$datumPrijave\">\n";
		}
		if ($komda) {
			echo "<input type=\"hidden\" name=\"datumKompl\" value=\"$datumKompl\">\n";
		}
		echo "<input type=\"hidden\" name=\"brSt\" value=\"$brSt\">\n";
		echo "<input type=\"hidden\" name=\"vrstaSt\" value=\"$vrstaSt\">\n";
		//echo "<input type=\"hidden\" name=\"tipSt\" value=\"$tipSt\">\n";
		echo "<input type=\"hidden\" name=\"dugme\" value=\"$dugme\">\n";
		echo "<input type=\"hidden\" name=\"nalog\" value=\"$nalog\">\n";
		echo "<input type=\"hidden\" name=\"isplaceno\" value=\"$isplaceno\">\n";
		echo "<input type=\"hidden\" name=\"isplata\" value=\"$isplata\">\n";
		echo "<input type=\"hidden\" name=\"prida\" value=\"$prida\">\n";
		echo "<input type=\"hidden\" name=\"komda\" value=\"$komda\">\n";
		echo "<input type=\"hidden\" name=\"idreak\" value=\"$idreak\">\n";
		echo "<input type=\"hidden\" name=\"rbrSD\" value=\"$rbrSD\">\n";
		echo "<input type=\"hidden\" name=\"rbrSteta\" value=\"$rbrSteta\">\n";
		echo "<input type=\"hidden\" name=\"rbrReaktivirana\" value=\"$rbrReaktivirana\">\n";
		echo "<input type=\"hidden\" name=\"reaktivirana\" value=\"$reaktivirana\">\n";
		echo "<input type=\"hidden\" name=\"reaktiviranaBaza\" value=\"$reaktiviranaBaza\">\n";
		if ($jmbgPibOstBaza) {
			echo "<input type=\"hidden\" name=\"jmbgPibOstBazaDisabled\" value=\"$jmbgPibOstBazaDisabled\">\n";
		}
		require "podaci_o_vozilu.php";
	}


	echo "<input type=\"hidden\" name=\"idstete\" value=\"$idstete\">\n";
	echo "<input type=\"hidden\" name=\"datumEvid\" value=\"$datumEvid\">\n";
	if ($prida) {
		echo "<input type=\"hidden\" name=\"datumPrijave\" value=\"$datumPrijave\">\n";
	}
	if ($komda) {
		echo "<input type=\"hidden\" name=\"datumKompl\" value=\"$datumKompl\">\n";
	}
	echo "<input type=\"hidden\" name=\"brSt\" value=\"$brSt\">\n";
	echo "<input type=\"hidden\" name=\"vrstaSt\" value=\"$vrstaSt\">\n";
	echo "<input type=\"hidden\" name=\"tipSt\" value=\"$tipSt\">\n";
	echo "<input type=\"hidden\" name=\"dugme\" value=\"$dugme\">\n";
	echo "<div id='nalog_id_hidden'>";
	echo "<input type=\"hidden\" name=\"nalog\"  value=\"$nalog\">\n";
	echo "<input type=\"hidden\" name=\"isplaceno\" value=\"$isplaceno\">\n";
	echo "</div>";
	echo "<input type=\"hidden\" name=\"isplata\" value=\"$isplata\">\n";
	echo "<input type=\"hidden\" name=\"prida\" value=\"$prida\">\n";
	echo "<input type=\"hidden\" name=\"komda\" value=\"$komda\">\n";
	echo "<input type=\"hidden\" name=\"idreak\" value=\"$idreak\">\n";
	echo "<input type=\"hidden\" name=\"rbrSD\" value=\"$rbrSD\">\n";
	echo "<input type=\"hidden\" name=\"rbrSteta\" value=\"$rbrSteta\">\n";
	echo "<input type=\"hidden\" name=\"rbrReaktivirana\" value=\"$rbrReaktivirana\">\n";
	echo "<input type=\"hidden\" name=\"reaktivirana\" value=\"$reaktivirana\">\n";
	echo "<input type=\"hidden\" name=\"reaktiviranaBaza\" value=\"$reaktiviranaBaza\">\n";
	if ($jmbgPibOstBaza) {
		echo "<input type=\"hidden\" name=\"jmbgPibOstBazaDisabled\" value=\"$jmbgPibOstBazaDisabled\">\n";
	}

	//Nemanja Jovanovic

	echo "<input type='hidden' id='podnosioca_prijave_email_hidden' name='podnosioca_prijave_email_hidden' value=''>\n";
	echo "<input type='hidden'  id='broj_prevare' name='broj_prevare' />";
	if ($submit) {
		$sql = "begin;";
		$rezultat = pg_query($conn, $sql);

		// OLD - $sql="update knjigas set ";
		$sql = "update predmet_odstetnog_zahteva set ";
		if ($od_zaht) {
			$sql .= " od_zaht=$od_zaht,";
		} else {
			$sql .= " od_zaht=false,";
		}
		if ($polisa_pl) {
			$sql .= " polisa_pl=$polisa_pl,";
		} else {
			$sql .= " polisa_pl=false,";
		}
		if ($zapisnik_mup) {
			$sql .= " zapisnik_mup=$zapisnik_mup,";
		} else {
			$sql .= " zapisnik_mup=false,";
		}
		if ($saobracajna) {
			$sql .= " saobracajna=$saobracajna,";
		} else {
			$sql .= " saobracajna=false ,";
		}
		if ($licnak) {
			$sql .= " licnak=$licnak,";
		} else {
			$sql .= " licnak=false,";
		}
		if ($ovlascenje) {
			$sql .= " ovlascenje=$ovlascenje,";
		} else {
			$sql .= " ovlascenje=false,";
		}
		if ($polisa_ost) {
			$sql .= " polisa_ost=$polisa_ost,";
		} else {
			$sql .= " polisa_ost=false,";
		}
		if ($vozilo) {
			$sql .= " vozilo=$vozilo,";
		} else {
			$sql .= " vozilo=false,";
		}

		$sql .= " radnik=$radnik, datum=current_date, vreme=current_time where id=$idstete";
		$rezultat1 = pg_query($conn, $sql);

		if ($rezultat1) {
			$sql = "commit;";
			$rezultat = pg_query($conn, $sql);
		} else {
			$sql = "rollback;";
			$rezultat = pg_query($conn, $sql);
		}
	}

	if ($odustani) {
		echo "<script type=\"text/javascript\">";
		echo "window.close()\n";
		echo "</script>";
	}

	if ($izmeni) {
		$da = 1;
		// NEVENA PERIÆ (Uzroci i rizici) 2017-07-03 - POÈETAK
		$preneti_rizik = $_POST['rizik'];
		$preneti_uzrok = $_POST['uzrok'];
		// Dodato za nova polja rizik i uzrok
		echo "<script language=\"javascript\">\n";
		echo "$('#rizik').find('option').filter(function(){return ($(this).val() == " . $preneti_rizik . ");}).attr('selected', true);";
		echo "</script>\n";
		if ($datumkonac_baza == NULL && $datumKompl != null) {

			if ($rizik == '-1') {
				echo "<script language=\"javascript\">\n";
				echo "alert(\"Morate izabrati prijavljeni rizik!\")\n";
				echo "document.pregled.rizik.focus();\n";
				echo "</script>\n";
				$da = 0;
			} else if ((!$uzrok || $uzrok == '-1') && $da = 1 && $rizik) {
				echo "<script language=\"javascript\">\n";
				echo "alert(\"Morate izabrati uzrok ¹tete po riziku!\");\n";
				echo "vratiUzrokeZaRizike($preneti_rizik);";
				echo "document.pregled.uzrok.focus();\n";
				echo "</script>\n";
				$da = 0;
			}
		}
		// NEVENA PERIÆ (Uzroci i rizici) 2017-07-03 - KRAJ

		if (!$tipSt  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.tipSt.value='';\n";
			echo "alert(\"Unesite tip ¹tete!\")\n";
			echo "document.pregled.tipSt.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		// 	if ((!ereg("^[0-9.]+$",$sifra)) && $da)
		// 	{
		if ((!$sifra || $sifra == '-1') && $da && $datumKompl) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.sifra.value='';\n";
			echo "alert(\"Unesite ¹ifru osiguranja!\")\n";
			echo "document.pregled.sifra.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($vrstaSt == 'CMR' && $da && $sifra <> '10.02') {
			echo "<script language=\"javascript\">\n";
			echo "alert(\"Nije dobra ¹ifra osiguranja CMR!\")\n";
			echo "document.pregled.sifra.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		// Dodata provera da mora biti izabran tip stete pri vrsti stete 'DPZ'
		// 	if ($vrstaSt == 'DPZ' && $tipSt == '-1' && $da)
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "alert(\"Potrebno je odabrati tip stete!\")\n";
		// 		echo "document.pregled.tipSt.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		// 	if ($vrstaSt == 'DPZ' && $tipSt!='0205' && $da && !ereg("^02.1[1-9].0[12]$", $sifra))
		// 	{
		// 	echo "<script language=\"javascript\">\n";
		// 	echo "alert(\"Nije dobra ¹ifra osiguranja DPZ!\")\n";
		// 	echo "document.pregled.sifra.focus();\n";
		// 	echo "</script>\n";
		// 	$da=0;
		// 	}

		// 	if ($vrstaSt == 'DPZ' && $tipSt == 'TB' && $da && $sifra != '02.12.01')
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "alert(\"Pogre¹an unos ¹ifre osiguranja! Za te¾e bolesti ¹ifra osiguranja je 02.12.01 !\")\n";
		// 		echo "document.pregled.sifra.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		// 	if ($vrstaSt == 'DPZ' && $tipSt == 'HI' && $da && $sifra != '02.12.02')
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "alert(\"Pogre¹an unos ¹ifre osiguranja! Za hirur¹ke intervencije ¹ifra osiguranja je 02.12.02 !\")\n";
		// 		echo "document.pregled.sifra.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		// 	if ((!ereg("^[0-9]+$",$rbrSt)) && $da)
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "document.pregled.rbrSt.value='';\n";
		// 		echo "alert(\"Unesite redni broj stete!\")\n";
		// 		echo "document.pregled.rbrSt.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		/* Branka - 2014-11-04 DOPUNA DA/Ne procena - provere i alerti - POÈETAK*/
		if (($slikao_kada && !je_datum($slikao_kada)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.slikao_kada.value='';\n";
			echo "alert(\"Neispravan datum slikanja vozila!\")\n";
			echo "document.pregled.slikao_kada.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if ($slikao_kada && $slikao_kada > date("Y-m-d") && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.slikao_kada.value='';\n";
			echo "alert(\"Datum slikanja vozila ne mo¾e biti u buduænosti!\")\n";
			echo "document.pregled.slikao_kada.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if ($slikao_kada && $slikao_kada < $datumPrijave && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.slikao_kada.value='';\n";
			echo "alert(\"Datum slikanja vozila ne mo¾e biti ranije od datuma prijave ¹tete!\")\n";
			echo "document.pregled.slikao_kada.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if (($slikao_vreme && !je_vreme($slikao_vreme)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.slikao_vreme.value='';\n";
			echo "alert(\"Neispravano vreme slikanja vozila!\")\n";
			echo "document.pregled.slikao_vreme.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		/* Branka - 2014-11-04 DOPUNA DA/Ne procena - provere i alerti - KRAJ*/

		//MARIJA dodato kako bi se nova polja postavila kao obavezana
		if ($osteceni_mesto_id == null || $osteceni_mesto_id == -1) {
			if ($osteceni_mesto_opis == null) {
				echo "<script language=\"javascript\">\n";
				echo "alert(\"Morate odabrati mesto od¹tetnog!\")\n";
				echo "document.pregled.osteceni_mesto_id.focus();\n";
				echo "</script>\n";
				$da = 0;
			}
		}
		//Branka 28.03.2016. dodato da je osnov prigovora obavezno polje ukoliko je u pitanju reaktivacija

		if ($osnovni_predmet_id_reaktiviranog && $osnovni_predmet_id_reaktiviranog != $idstete && $razlog_reaktivacije == "" && !$sudski_postupak_id) {

			echo "<script language=\"javascript\">\n";
			echo "alert(\"Morate odabrati osnov prigovora!\")\n";
			echo "document.pregled.razlog_reaktivacije.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($adresaOst == null) {
			echo "<script language=\"javascript\">\n";
			echo "alert(\"Morate upisati adresu od¹tetnog!\")\n";
			echo "document.pregled.adresaOst.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($datumKompl && !je_datum($datumKompl)  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumKompl.value='';\n";
			echo "alert(\"Neispravan datum kompletiranja!\")\n";
			echo "document.pregled.datumKompl.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($datumKompl > $nalog)  && $da && $nalog && $datumKompl) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumKompl.value='';\n";
			echo "alert(\"Datum kompletiranja ne mo¾e biti nakon datuma konaènog re¹avanja!\")\n";
			echo "document.pregled.datumKompl.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		// 	if ( (!je_datum($datumEvid) || !$datumEvid) && $da)
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "document.pregled.datumEvid.value='';\n";
		// 		echo "alert(\"Neispravan datum evidentiranja!\")\n";
		// 		echo "document.pregled.datumEvid.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		// 	if ($datumEvid && $datumKompl && ($datumKompl < $datumEvid ) && $da)
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "document.pregled.datumKompl.value='';\n";
		// 		echo "alert(\"©teta je kompletirana pre nego ¹to je evidentirana!\")\n";
		// 		echo "document.pregled.datumKompl.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		//datum prijave
		// 	if (($datumPrijave && !je_datum($datumPrijave) ) && $da)
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "document.pregled.datumPrijave.value='';\n";
		// 		echo "alert(\"Neispravan datum prijave stete!\")\n";
		// 		echo "document.pregled.datumPrijave.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		// 	if ($datumPrijave &&  $datumPrijave < $datumEvid && $da)
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "document.pregled.datumPrijave.value='';\n";
		// 		echo "alert(\"©teta ne mo¾e biti prijavljena posle podno¹enja zahteva!\")\n";
		// 		echo "document.pregled.datumPrijave.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		if ($datumPrijave && $datumKompl && $datumPrijave > $datumKompl && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumKompl.value='';\n";
			echo "alert(\"¹teta je kompletirana pre nego ¹to je prijavljena!\")\n";
			echo "document.pregled.datumKompl.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		//datum prijave
		if ($premija && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $premija)  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.premija.value='';\n";
			echo "alert(\"Neispravan iznos premije!\")\n";
			echo "document.pregled.premija.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($tekRacun_ost && !ereg("^[0-9-]+$", $tekRacun_ost)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.tekRacun_ost.value='';\n";
			echo "alert(\"Unesite tekuæi raèun!\")\n";
			echo "document.pregled.tekRacun_ost.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($vaznostOdKriv && !je_datum($vaznostOdKriv)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.zarko.vaznostOdKriv.value='';\n";
			echo "alert(\"Neispravan datum poèetka va¾enja polise krivca!\")\n";
			echo "document.zarko.vaznostOdKriv.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($vaznostDoKriv && !je_datum($vaznostDoKriv)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.zarko.vaznostDoKriv.value='';\n";
			echo "alert(\"Neispravan datum prestanka va¾enja polise krivca!\")\n";
			echo "document.zarko.vaznostDoKriv.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($datumProc && !je_datum($datumProc)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumProc.value='';\n";
			echo "alert(\"Neispravan datum procene ¹tete!\")\n";
			echo "document.pregled.datumProc.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($dana && !je_datum($dana)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.dana.value='';\n";
			echo "alert(\"Neispravan datum predaje na dalju obradu!\")\n";
			echo "document.pregled.dana.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($vrstaSt == 'AK' || $vrstaSt == 'AKs') {
			if ($malusProc && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $malusProc)  && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.malusProc.value='';\n";
				echo "alert(\"Unesite procenat malusa!\")\n";
				echo "document.pregled.malusProc.focus();\n";
				echo "</script>\n";
				$da = 0;
			}

			if (((!$malusIznos || !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $malusIznos)) && $malusProc)  && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.malusIznos.value='';\n";
				echo "alert(\"Unesite iznos malusa!\")\n";
				echo "document.pregled.malusIznos.focus();\n";
				echo "</script>\n";
				$da = 0;
			}

			if ($dugZaPremiju && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $dugZaPremiju)  && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.dugZaPremiju.value='';\n";
				echo "alert(\"Unesite dug za premiju!\")\n";
				echo "document.pregled.dugZaPremiju.focus();\n";
				echo "</script>\n";
				$da = 0;
			}

			if ($kompenzovano && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $kompenzovano)  && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.kompenzovano.value='';\n";
				echo "alert(\"Unesite kompenzovani iznos!\")\n";
				echo "document.pregled.kompenzovano.focus();\n";
				echo "</script>\n";
				$da = 0;
			}

			if ($preostaliDug && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $preostaliDug)  && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.preostaliDug.value='';\n";
				echo "alert(\"Unesite iznos preostalog duga!\")\n";
				echo "document.pregled.preostaliDug.focus();\n";
				echo "</script>\n";
				$da = 0;
			}

			if (($datumKomOsnov && !je_datum($datumKomOsnov)) && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.datumKomOsnov.value='';\n";
				echo "alert(\"Neispravan datum obrade KF osnova!\")\n";
				echo "document.pregled.datumKomOsnov.focus();\n";
				echo "</script>\n";
				$da = 0;
			}

			if (($kfPredato && !je_datum($kfPredato)) && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.kfPredato.value='';\n";
				echo "alert(\"Neispravan datum predaje KF osnova!\")\n";
				echo "document.pregled.kfPredato.focus();\n";
				echo "</script>\n";
				$da = 0;
			}
		}

		if ($datumPrijemaPredmetaPravnaSluzba && (!je_datum($datumPrijemaPredmetaPravnaSluzba) || ($datumPrijemaPredmetaPravnaSluzba > date("Y-m-d"))) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumPrijemaPredmetaPravnaSluzba.value='';\n";
			echo "alert(\"Neispravan datum prijema predmeta u pravnu slu¾bu!\")\n";
			echo "document.pregled.datumPrijemaPredmetaPravnaSluzba.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($pravniOsnovDatumKompletiranjaDokumentacije && (!je_datum($pravniOsnovDatumKompletiranjaDokumentacije) || ($pravniOsnovDatumKompletiranjaDokumentacije > date("Y-m-d"))) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.pravniOsnovDatumKompletiranjaDokumentacije.value='';\n";
			echo "alert(\"Neispravan datum kompletiranja dokumentacije za davanje pravnog osnova!\")\n";
			echo "document.pregled.pravniOsnovDatumKompletiranjaDokumentacije.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($pravniOsnovDatumKompletiranjaDokumentacije && (!je_datum($pravniOsnovDatumKompletiranjaDokumentacije) || ($pravniOsnovDatumKompletiranjaDokumentacije < $datum_otvaranja_predmeta)) && $da) {
			echo "<script language=\"javascript\">\n";
			//echo "document.pregled.pravniOsnovDatumKompletiranjaDokumentacije.value='';\n";
			echo "alert(\"Neispravan datum kompletiranja dokumentacije za davanje pravnog osnova! Datum '$pravniOsnovDatumKompletiranjaDokumentacije' ne mo¾e biti ranije od datuma otvaranja predmeta od¹tetnog zahteva '$datum_otvaranja_predmeta'!\")\n";
			echo "document.pregled.pravniOsnovDatumKompletiranjaDokumentacije.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($datumPravniOsnov && !je_datum($datumPravniOsnov)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumPravniOsnov.value='';\n";
			echo "alert(\"Neispravan datum obrade pravnog osnova!\")\n";
			echo "document.pregled.datumPravniOsnov.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($pravnaPredato && !je_datum($pravnaPredato)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.pravnaPredato.value='';\n";
			echo "alert(\"Neispravan datum predaje pravnog osnova!\")\n";
			echo "document.pregled.pravnaPredato.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (((!$delimicnoProc || !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $delimicnoProc)) && $osnovan == 'D')  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.delimicnoProc.value='';\n";
			echo "alert(\"Unesite procenat za delimiènu osnovanost!\")\n";
			echo "document.pregled.delimicnoProc.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		// MARIJA - 19.02.2015 dodato razlog umanjenja bude obavezno polje ukoliko se stiklira OSNOVAN DELIMICNo - POCETAk
		if (($razlog_umanjenja_stete_id == -1 && $osnovan == 'D')  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.razlog_umanjenja_stete_id.value='-1';\n";
			echo "alert(\"Izaberite razlog umanjenja ¹tete!\")\n";
			echo "document.pregled.razlog_umanjenja_stete_id.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		// MARIJA - 19.02.2015 dodato razlog umanjenja bude obavezno polje ukoliko se stiklira OSNOVAN DELIMICNo - KRAJ
		/*
		// MARIJA 03.03.2015. - dodato za obavezno polje za osiguravajuca drustva - POCETAK
		if ($regres_od == 'Osiguravajuæe dru¹tvo' && $osiguravajuce_drustvo_id == -1  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.osiguravajuce_drustvo_id.value='-1';\n";
			echo "alert(\"Izaberite osiguravajuce drustvo!!\")\n";
			echo "document.pregled.osiguravajuce_drustvo_id.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		*/
		// MARIJA 03.03.2015. - dodato za obavezno polje za osiguravajuca drustva - KRAJ

		if (($vraceno && !je_datum($vraceno)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.vraceno.value='';\n";
			echo "alert(\"Neispravan datum vraæanja iz Pravne slu¾be!\")\n";
			echo "document.pregled.vraceno.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete && (!je_datum($datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete) || ($datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete > date("Y-m-d"))) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete.value='';\n";
			echo "alert(\"Neispravan datum kompletiranja dokumentacije za utvrðivanje visine ¹tete!\")\n";
			echo "document.pregled.datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete && (!je_datum($datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete) || ($datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete < $datum_otvaranja_predmeta)) && $da) {
			echo "<script language=\"javascript\">\n";
			//echo "document.pregled.datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete.value='';\n";
			echo "alert(\"Neispravan datum kompletiranja dokumentacije za utvrðivanje visine ¹tete! Datum '$datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete' ne mo¾e biti ranije od datuma otvaranja predmeta od¹tetnog zahteva '$datum_otvaranja_predmeta'!\")\n";
			echo "document.pregled.datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($pocetak && !je_datum($pocetak)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.pocetak.value='';\n";
			echo "alert(\"Neispravan datum poèetka utvrðivanja visine ¹tete!\")\n";
			echo "document.pregled.pocetak.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($kraj && !je_datum($kraj)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.kraj.value='';\n";
			echo "alert(\"Neispravan datum zavr¹etka utvrðivanja visine ¹tete!\")\n";
			echo "document.pregled.kraj.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($datumPonuda1 && !je_datum($datumPonuda1)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumPonuda1.value='';\n";
			echo "alert(\"Neispravan datum prve ponude!\")\n";
			echo "document.pregled.datumPonuda1.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($datumPrigovor && !je_datum($datumPrigovor)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumPrigovor.value='';\n";
			echo "alert(\"Neispravan datum prigovora!\")\n";
			echo "document.pregled.datumPrigovor.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($pocetak2 && !je_datum($pocetak2)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.pocetak2.value='';\n";
			echo "alert(\"Neispravan datum poèetka rada drugostepene komisije!\")\n";
			echo "document.pregled.pocetak2.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($kraj2 && !je_datum($kraj2)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.kraj2.value='';\n";
			echo "alert(\"Neispravan datum zavr¹etka rada drugostepene komisije!\")\n";
			echo "document.pregled.kraj2.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($datumPonuda2 && !je_datum($datumPonuda2)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datumPonuda2.value='';\n";
			echo "alert(\"Neispravan datum druge ponude!\")\n";
			echo "document.pregled.datumPonuda2.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($zahtevano && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $zahtevano)  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.zahtevano.value='';\n";
			echo "alert(\"Unesite zahtevani iznos!\")\n";
			echo "document.pregled.zahtevano.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ((!$rezervisano || !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $rezervisano))  && $da && !$podaci_rezervacije_unete) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.rezervisano.value='';\n";
			echo "alert(\"Unesite rezervisani iznos!\")\n";
			echo "document.pregled.rezervisano.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if ($vrstaSt == 'USL') {
			if ($isplaceno && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.isplaceno.value='';\n";
				echo "alert(\"Isplaæeni iznos se ne unosi za USL!\")\n";
				echo "document.pregled.isplaceno.focus();\n";
				echo "</script>\n";
				$da = 0;
			}

			if ($nalog  && $da) {
				echo "<script language=\"javascript\">\n";
				echo "document.pregled.nalog.value='';\n";
				echo "alert(\"Datum naloga za isplatu se ne unosi za USL!\")\n";
				echo "document.pregled.nalog.focus();\n";
				echo "</script>\n";
				$da = 0;
			}
		}

		if ($isplaceno && !ereg("^[0-9]{1,12}\.?[0-9]{0,2}$", $isplaceno)  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.isplaceno.value='';\n";
			echo "alert(\"Unesite isplaæeni iznos!\")\n";
			echo "document.pregled.isplaceno.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		// Lazar Milosavljevic28-02-2013
		// Dodat uslov za proveru kada je od¹tetni zahtev odbijen po prigovoru na visinu odstete, da tada mo¾e isplaæeni iznos da bude 0  --  $prigovor_osnovan<>'S'
		if ($isplaceno && $isplaceno == 0.00 && (!$sp && $osnovan <> 'O' && $prigovor_osnovan <> 'S' && $odustao == false) && !$storno) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.isplaceno.value='';\n";
			echo "alert(\"Unesite isplaæeni iznos <> 0.00!\")\n";
			echo "document.pregled.isplaceno.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($sp && $osnovan == 'O') || ($sp && $odustao == true) || ($osnovan == 'O' && $odustao == true)) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.sp.value='';\n";
			echo "alert(\"Predmet ne mo¾e istovremeno biti i SP i odbijen i odustao!\")\n";
			echo "document.pregled.sp.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($nalog && !je_datum($nalog)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.nalog.value='';\n";
			echo "alert(\"Neispravan datum naloga za isplatu!\")\n";
			echo "document.pregled.nalog.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($isplata && !je_datum($isplata)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.isplata.value='';\n";
			echo "alert(\"Neispravan datum isplate!\")\n";
			echo "document.pregled.isplata.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		if (($naknadna_isplata && !je_datum($naknadna_isplata)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.naknadna_isplata.value='';\n";
			echo "alert(\"Neispravan datum naknadne isplate!\")\n";
			echo "document.pregled.naknadna_isplata.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if (($arhivirano && !je_datum($arhivirano)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.arhivirano.value='';\n";
			echo "alert(\"Neispravan datum arhiviranja!\")\n";
			echo "document.pregled.arhivirano.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if ($struktura == '0' && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.struktura.value='';\n";
			echo "alert(\"Morate izabrati sektorsku strukturu!\")\n";
			echo "document.pregled.struktura.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		//Branka 01.07.2015. dodato za tip i osnov rente
		$tip_rente = $_POST['tip_rente'];
		$osnov_rente = $_POST['osnov_rente'];
		if ($tip_rente == -1) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.struktura.value='';\n";
			echo "alert(\"Morate izabrati tip rente!\")\n";
			echo "document.pregled.tip_rente.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if ($renta_lica == '1' && $da  && $osnov_rente == -1) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.struktura.value='';\n";
			echo "alert(\"Morate izabrati osnov rente!\")\n";
			echo "document.pregled.osnov_rente.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		// 	if ($reaktivirana && $razlog_reaktivacije=='' && $da)
		// 	{
		// 		echo "<script language=\"javascript\">\n";
		// 		echo "document.pregled.razlog_reaktivacije.value='';\n";
		// 		echo "alert(\"Morate izabrati razlog reaktivacije!\")\n";
		// 		echo "document.pregled.razlog_reaktivacije.focus();\n";
		// 		echo "</script>\n";
		// 		$da=0;
		// 	}

		// Ovde æu da stavim kontrole za NOVA POLJA...
		// if ((!ereg("^[0-9]+$",$rbrSD)) && $da){
		// echo "<script language=\"javascript\">\n";
		// echo "document.pregled.rbrSD.value='';\n";
		// echo "alert(\"Pritisnite dugme reaktiviraj!\")\n";
		// echo "document.pregled.rbrSD.focus();\n";
		// echo "</script>\n";
		// $da=0;
		// }

		// if ((!ereg("^[0-9]+$",$rbrSteta)) && $da){


		// echo "<script language=\"javascript\">\n";
		// echo "document.pregled.rbrSteta.value='';\n";
		// echo "alert(\"Pritisnite dugme reaktiviraj!\")\n";
		// echo "document.pregled.rbrSteta.focus();\n";
		// echo "</script>\n";
		// $da=0;
		// }

		//Provere vezane za DPZ i ZP, polja u delu za OSIGURANI SLUÈAJ
		if (($datum_ulaska_u_zemlju_destinacije && !je_datum($datum_ulaska_u_zemlju_destinacije)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datum_ulaska_u_zemlju_destinacije.value='';\n";
			echo "alert(\"Neispravan datum ulaska u zemlju destinacije!\")\n";
			echo "document.pregled.datum_ulaska_u_zemlju_destinacije.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if (($datum_izlaska_iz_zemlje_destinacije && !je_datum($datum_izlaska_iz_zemlje_destinacije)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datum_izlaska_iz_zemlje_destinacije.value='';\n";
			echo "alert(\"Neispravan datum izlaska iz zemlje destinacije!\")\n";
			echo "document.pregled.datum_izlaska_iz_zemlje_destinacije.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if (($datum_prijema_medicinska_ustanova && !je_datum($datum_prijema_medicinska_ustanova)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datum_prijema_medicinska_ustanova.value='';\n";
			echo "alert(\"Neispravan datum prijema u medicinsku ustanovu!\")\n";
			echo "document.pregled.datum_prijema_medicinska_ustanova.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if (($datum_otpustanja_medicinska_ustanova && !je_datum($datum_otpustanja_medicinska_ustanova)) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.datum_otpustanja_medicinska_ustanova.value='';\n";
			echo "alert(\"Neispravan datum otpu¹tanja iz medicinske ustanove!\")\n";
			echo "document.pregled.datum_otpustanja_medicinska_ustanova.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if (($datum_ulaska_u_zemlju_destinacije > $datum_izlaska_iz_zemlje_destinacije) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "alert(\"Datum izlaska iz zemlje destinacije mora biti pre datuma izlaska iz zemlje destinacije!\")\n";
			echo "document.pregled.datum_ulaska_u_zemlju_destinacije.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
		if (($datum_prijema_medicinska_ustanova > $datum_otpustanja_medicinska_ustanova) && $da) {
			echo "<script language=\"javascript\">\n";
			echo "alert(\"Datum prijema u medicinsku ustanovu mora biti pre datuma otpu¹tanja iz medicinske ustanove!\")\n";
			echo "document.pregled.datum_prijema_medicinska_ustanova.focus();\n";
			echo "</script>\n";
			$da = 0;
		}

		//Dodao Marko Stankovic31.07.2018.
		if ($odustao == true && $odustao_pravni_osnov == true && $da) {
			echo "<script language=\"javascript\">\n";
			echo "alert(\"Ne mo¾ete odabrati 'Odustao' pravni osnov i 'Odustao' u Likvidaciji \")\n";

			echo "</script>\n";
			$da = 0;
		}
		//Dodao Marko Stankovic06.08.2018.
		if ($odustao_poz_za_resenje_odustao == true  && $odustao_pravni == true  && $da) {
			echo "<script language=\"javascript\">\n";
			echo "alert(\"Ne mo¾ete odabrati 'Odustao' pravni osnov i 'Odustao' u Likvidaciji \")\n";

			echo "</script>\n";
			$da = 0;
		}

		$sifra = str_replace(".", "", $sifra);
		$sifra_niz = "'{" . $sifra . "}'";

		//ne treba za sada
		/*
if ($osnovan=='O' && $odustao_pravni_osnov==true)
{
    echo "<script language=\"javascript\">\n";
    echo "document.pregled.sp.value='';\n";
    echo "alert(\"Predmet ne mo¾e istovremeno biti odustao i odbijen!\")\n";
    echo "document.pregled.sp.focus();\n";
    echo "</script>\n";
    $da=0;
    
}
*/
		//dodao Marko Stankovic02.08.2018.
		$sql_resenja = "SELECT * FROM resenja WHERE poz_id =$idstete AND status = 'POTPISAN' AND konacno = true";
		$upit_sql_resenja = pg_query($conn, $sql_resenja);
		$podaci_resenja = pg_fetch_assoc($upit_sql_resenja);
		$prebroj_resenja = pg_num_rows($upit_sql_resenja);
		if ($prebroj_resenja > 0 && ($odustao_pravni_osnov == true || $odustao == true)) {
			echo "<script language=\"javascript\">\n";
			echo "document.pregled.sp.value='';\n";
			echo "alert(\"Nalog je potpisan  sa konaèno DA ne mo¾ete kreirati re¹enje Odustao!\")\n";
			echo "document.pregled.sp.focus();\n";
			echo "</script>\n";
			$da = 0;
		}


		if ($da) {

			//     $odustao_pravni =isset($_POST['odustao_pravni_osnov']);
			//     $odustao =isset($_POST['odustao']);
			//     if($odustao_pravni == 1 && $odustao==1){
			//     //30.07.2018. dodao Marko Stankovic
			//     echo "<script type=\"text/javascript\">";
			//     echo "alert('Pravni=$odustao_pravni, odustao=$odustao Ne mo¾ete oba')";

			//     echo "</script>";
			// exit();
			//}
			$sql = "begin;";
			$rezultat = pg_query($conn, $sql);

			if ($dana) {
				$faza = 'FAZA 2 - KF OSNOV';
				$faza_id = 2;
			}
			if (($dana && ($vrstaSt <> 'AK' && $vrstaSt <> 'AKs' && $vrstaSt <> 'IO' && $vrstaSt <> 'N')) || ($dana && $kfPredato && ($vrstaSt == 'AK' || $vrstaSt == 'AKs' || $vrstaSt == 'IO' || $vrstaSt == 'N'))) {
				$faza = 'FAZA 3 - PRAVNI OSNOV';
				$faza_id = 3;
			}
			if ($vraceno) {
				$faza = 'FAZA 5 - OBRADA';
				$faza_id = 5;
			}
			if ($kraj) {
				$faza = 'FAZA 6 - PONUDA1';
				$faza_id = 6;
			}
			if ($prigovor) {
				$faza = 'FAZA 7 - PRIGOVOR';
				$faza_id = 7;
			}
			if ($kraj2) {
				$faza = 'FAZA 8 - PONUDA2';
				$faza_id = 8;
			}
			if ($nalog) {
				$faza = 'FAZA 9 - NALOG ZA ISPLATU';
				$faza_id = 9;
			}
			if ($isplata) {
				$faza = 'FAZA 10 - LIKVIDACIJA';
				$faza_id = 10;
			}
			if ($arhivirano) {
				$faza = 'FAZA 11 - ARHIVIRANJE';
				$faza_id = 11;
			}

			if ($vrstaSt == 'AK' && substr($sifra, -1) == '2') {
				$vrstaSt = 'AKs';
			}
			if ($vrstaSt == 'AKs' && substr($sifra, -1) == '1') {
				$vrstaSt = 'AK';
			}

			if ($nalog && ($sp || $odustao || $osnovan == 'O')) {
				$faza = 'FAZA 10 - LIKVIDACIJA';
				$faza_id = 10;
				$isplata = $nalog;
			}

			$sifra = str_replace(".", "", $sifra);
			$sifra_niz = "'{" . $sifra . "}'";

			// OLD - $sql="update knjigas set  tipst='$tipSt', ";
			$sql = "update predmet_odstetnog_zahteva set ";
			$sql .= " sifra='$sifra',sifra_niz=$sifra_niz, ";

			if ($faza && $faza_id) {
				$sql .= "faza='$faza'," . "faza_id=$faza_id, ";
			} else {
				$sql .= "faza=null, faza_id=null, ";
			}
			//Branka dodato 01.07.2015

			if ($renta_lica == 1) {

				if ($tip_rente != -1) {
					$sql .= "tip_rente='$tip_rente',";
				} else {
					$sql .= "tip_rente=null,";
				}
				if ($osnov_rente != -1) {
					$sql .= "osnov_rente='$osnov_rente',";
				} else {
					$sql .= "osnov_rente=null,";
				}
			}

			if ($rbrSt) {
				$sql .= "rbrst=$rbrSt,";
			} else {
				$sql .= "rbrst=null,";
			}
			if ($datumEvid) {
				$sql .= "datumevid='$datumEvid', datumkompl='$datumEvid',";
			} else {
				$sql .= "datumevid=null, datumkompl=null, ";
			}
			if ($datumKompl) {
				$sql .= "datumkonac='$datumKompl',";
			} else {
				$sql .= "datumkonac=null,";
			}
			if ($premija) {
				$sql .= "premija=$premija,";
			} else {
				$sql .= "premija=null,";
			}
			if ($prezimeOst) {
				$sql .= "prezimeost='$prezimeOst',";
			} else {
				$sql .= "prezimeost=null,";
			}
			if ($imeNazivOst) {
				$sql .= "imenazivost='$imeNazivOst',";
			} else {
				$sql .= "imenazivost=null,";
			}
			if ($jmbgPibOst) {
				$sql .= "jmbgpibost='$jmbgPibOst',";
			} else {
				$sql .= "jmbgpibost=null,";
			}
			if ($telefon2) {
				$sql .= "telefon2='$telefon2',";
			} else {
				$sql .= "telefon2=null,";
			}
			if ($markaOst) {
				$sql .= "markaost='$markaOst',";
			} else {
				$sql .= "markaost=null,";
			}
			if ($tipOst) {
				$sql .= "tipost='$tipOst',";
			} else {
				$sql .= "tipost=null,";
			}
			if ($godOst) {
				$sql .= "godost='$godOst',";
			} else {
				$sql .= "godost=null,";
			}
			if ($regPodOst) {
				$sql .= "regpodost='$regPodOst',";
			} else {
				$sql .= "regpodost=null,";
			}
			if ($regOznakaOst) {
				$sql .= "regoznakaost='$regOznakaOst',";
			} else {
				$sql .= "regoznakaost=null,";
			}
			if ($brsasOst) {
				$sql .= "brsasost='$brsasOst',";
			} else {
				$sql .= "brsasost=NULL,";
			}
			if ($nazivOsigOst) {
				$sql .= "nazivosigost='$nazivOsigOst',";
			} else {
				$sql .= "nazivosigost=null,";
			}
			if ($brPoliseOst) {
				$sql .= "brpoliseost='$brPoliseOst',";
			} else {
				$sql .= "brpoliseost=null,";
			}
			if ($vaznostOdOst) {
				$sql .= "vaznostodost='$vaznostOdOst',";
			} else {
				$sql .= "vaznostodost=null,";
			}
			if ($vaznostDoOst) {
				$sql .= "vaznostdoost='$vaznostDoOst',";
			} else {
				$sql .= "vaznostdoost=null,";
			}
			if ($predjenoKmOst) {
				$sql .= "predjenokm_ost=$predjenoKmOst,";
			} else {
				$sql .= "predjenokm_ost=null,";
			}
			if ($prezimeKriv) {
				$sql .= "prezimekriv='$prezimeKriv',";
			} else {
				$sql .= "prezimekriv=null,";
			}
			if ($imeNazivKriv) {
				$sql .= "imenazivkriv='$imeNazivKriv',";
			} else {
				$sql .= "imenazivkriv=null,";
			}
			if ($jmbgPibKriv) {
				$sql .= "jmbgpibkriv='$jmbgPibKriv',";
			} else {
				$sql .= "jmbgpibkriv=null,";
			}
			if ($ovlLiceKriv) {
				$sql .= "ovllicekriv='$ovlLiceKriv',";
			} else {
				$sql .= "ovllicekriv=null,";
			}
			if ($markaKriv) {
				$sql .= "markakriv='$markaKriv',";
			} else {
				$sql .= "markakriv=null,";
			}
			if ($tipKriv) {
				$sql .= "tipkriv='$tipKriv',";
			} else {
				$sql .= "tipkriv=null,";
			}
			if ($godKriv) {
				$sql .= "godkriv='$godKriv',";
			} else {
				$sql .= "godkriv=null,";
			}
			if ($regPodKriv) {
				$sql .= "regpodkriv='$regPodKriv',";
			} else {
				$sql .= "regpodkriv=null,";
			}
			if ($regOznakaKriv) {
				$sql .= "regoznakakriv='$regOznakaKriv',";
			} else {
				$sql .= "regoznakakriv=null,";
			}
			if ($brsasKriv) {
				$sql .= "brsaskriv='$brsasKriv',";
			} else {
				$sql .= "brsaskriv=NULL,";
			}
			if ($vrstaRegStet) {
				$sql .= "vrstaregstet='$vrstaRegStet',";
			} else {
				$sql .= "vrstaregstet=null,";
			}
			if ($oznakaRegStet) {
				$sql .= "oznakaregstet='$oznakaRegStet',";
			} else {
				$sql .= "oznakaregstet=null,";
			}
			if ($osiguranjeRegStet) {
				$sql .= "osiguranjeregstet='$osiguranjeRegStet',";
			} else {
				$sql .= "osiguranjeregstet=null,";
			}
			if ($drzavaRegStet) {
				$sql .= "drzavaregstet='$drzavaRegStet',";
			} else {
				$sql .= "drzavaregstet=null,";
			}
			if ($procenitelj1) {
				$sql .= "procenitelj1='$procenitelj1',";
			} else {
				$sql .= "procenitelj1=null,";
			}
			if ($procenitelj2) {
				$sql .= "procenitelj2='$procenitelj2',";
			} else {
				$sql .= "procenitelj2=null,";
			}
			if ($datumProc) {
				$sql .= "datumproc='$datumProc',";
			} else {
				$sql .= "datumproc=null,";
			}
			if ($servis_upuceno_id != 0) {
				$sql .= "servis_upuceno_id = $servis_upuceno_id,";
			} else if ($servis_upuceno_id == 0) {
				$sql .= "servis_upuceno_id = null,";
			}
			if ($servis_fakturisano_id != 0) {
				$sql .= "servis_fakturisano_id = $servis_fakturisano_id,";
			} else if ($servis_fakturisano_id == 0) {
				$sql .= "servis_fakturisano_id = null,";
			}
			// Dodato 13-11-2013 Lazar Milosavljeviæ
			if ($servis_fakturisano_datum != '') {
				$sql .= "servis_fakturisano_datum = '$servis_fakturisano_datum',";
			} else if ($servis_fakturisano_datum == '') {
				$sql .= "servis_fakturisano_datum = null,";
			}

			if ($dana) {
				$sql .= "dana='$dana',";
			} else {
				$sql .= "dana=null,";
			}
			if ($datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete) {
				$sql .= "datum_kompletiranja_dokumentacije_utvrdjivanje_visine_stete='$datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete',";
			} else {
				$sql .= "datum_kompletiranja_dokumentacije_utvrdjivanje_visine_stete=null,";
			}
			if ($pocetak) {
				$sql .= "pocetak='$pocetak',";
			} else {
				$sql .= "pocetak=null,";
			}
			if ($kraj) {
				$sql .= "kraj='$kraj',";
			} else {
				$sql .= "kraj=null,";
			}

			if ($obradjivac1) {
				$sql .= "obradjivac1='$obradjivac1',";
			} else {
				$sql .= "obradjivac1=null,";
			}
			if ($obradjivac2) {
				$sql .= "obradjivac2='$obradjivac2',";
			} else {
				$sql .= "obradjivac2=null,";
			}
			if ($datumPonuda1) {
				$sql .= "datumponuda1='$datumPonuda1',";
			} else {
				$sql .= "datumponuda1=null,";
			}
			if ($likvidatorPonuda1) {
				$sql .= "likvidatorponuda1='$likvidatorPonuda1',";
			} else {
				$sql .= "likvidatorponuda1=null,";
			}
			//Branka 22.02.2017.
			if ($vas_broj) {
				$sql .= "vas_broj='$vas_broj',";
			} else {
				$sql .= "vas_broj=null,";
			}

			// NEVENA PERIÆ (Uzroci i rizici) 2017-07-03 - POÈETAK
			// Dodato za update uzroka i rizika
			if ($rizik && $rizik != '-1') {
				$sql .= "rizik_id = $rizik, ";
			} else {
				$sql .= "rizik_id = NULL,";
			}
			if ($rizik != '-1' && $uzrok && $uzrok != '-1') {
				$sql .= "uzrok_id=$uzrok,";
			} else {
				$sql .= "uzrok_id=null,";
			}

			//Nemanja Jovanovic 2018-12-20
			if ($osteceni_mail) {
				$sql .= "osteceni_mail='$osteceni_mail',";
			} else {
				$sql .= "osteceni_mail=null,";
			}
			if ($osiguranik_mail) {
				$sql .= "osiguranik_mail='$osiguranik_mail',";
			} else {
				$sql .= "osiguranik_mail=null,";
			}


			//

			// NEVENA PERIÆ (Uzroci i rizici) 2017-07-03 - KRAJ

			if ($datumPrigovor) {
				// Izmena zbog prigovora
				// Lazar Milosavljevic- 08-02-2013
				$delimicno_resen_po_prigovoru_procenat = ($delimicno_resen_po_prigovoru_procenat == '' ? 'NULL' : $delimicno_resen_po_prigovoru_procenat);
				// Setuj prethodni prigovor na vazi=0
				$sql_update_prigovor = "
												UPDATE
													prigovor
												SET
													vazi=0,
													radnik=$radnik,
													dana=current_date,
													vreme=current_time
												WHERE
													idstete = $idstete;
												";
				$rezultat_update_prigovor = pg_query($conn, $sql_update_prigovor);
				$sql_upisi_prigovor = "INSERT INTO prigovor(
														idstete, datum_prigovora,
														osnovan, procenat, vazi,
														radnik, dana, vreme)
													VALUES
														($idstete, '$datumPrigovor',
														'$osnovan_po_prigovoru_na_visinu_odstete', $delimicno_resen_po_prigovoru_procenat, 1,
														$radnik, current_date, current_time);
													";
				$rezultat_upisi_prigovor = pg_query($conn, $sql_upisi_prigovor);
				$sql .= "datumprigovor='$datumPrigovor',";
			} else {
				// Izmena zbog prigovora
				// Lazar Milosavljevic- 08-02-2013
				$sql_ne_vazi_prigovor = "UPDATE
														prigovor
   												SET
   													vazi=0,
   													radnik=$radnik,
   													dana=current_date,
   													vreme=current_time
													WHERE
														idstete=$idstete;
													";
				$rezultat_ne_vazi_prigovor = pg_query($conn, $sql_ne_vazi_prigovor);
				$sql .= "datumprigovor=null,";
			}








			if ($komisija1) {
				$sql .= "komisija1='$komisija1',";
			} else {
				$sql .= "komisija1=null,";
			}
			if ($komisija2) {
				$sql .= "komisija2='$komisija2',";
			} else {
				$sql .= "komisija2=null,";
			}
			if ($datumPonuda2) {
				$sql .= "datumponuda2='$datumPonuda2',";
			} else {
				$sql .= "datumponuda2=null,";
			}
			if ($likvidatorPonuda2) {
				$sql .= "likvidatorponuda2='$likvidatorPonuda2',";
			} else {
				$sql .= "likvidatorponuda2=null,";
			}
			if ($zahtevano) {
				$sql .= "zahtevano=$zahtevano,";
			} else {
				$sql .= "zahtevano=null,";
			}
			if ($rezervisano) {
				$sql .= "rezervisano=$rezervisano,";
			} else {
				$sql .= "rezervisano=null,";
			}
			if ($isplaceno) {
				$sql .= "isplaceno=$isplaceno,";
			} else {
				$sql .= "isplaceno=null,";
			}
			if ($nalog) {
				$sql .= "nalog='$nalog',";
			} else {
				$sql .= "nalog=null,";
			}
			if ($odustao) {
				$sql .= "odustao=$odustao,";
			} else {
				$sql .= "odustao=false,";
			}
			if ($dokNijeSt) {
				$sql .= "doknijest=$dokNijeSt,";
			} else {
				$sql .= "doknijest=false,";
			}
			if ($sp) {
				$sql .= "sp='$sp',";
			} else {
				$sql .= "sp=null,";
			}
			if ($arhivirano) {
				$sql .= "arhivirano='$arhivirano',";
			} else {
				$sql .= "arhivirano=null,";
			}
			if ($napomena) {
				$sql .= "napomena='$napomena',";
			} else {
				$sql .= "napomena=null,";
			}
			$sql .= " radnik=$radnik, datum=current_date, vreme=current_time ,";
			if ($slovo == 'H') {
				$sql .= "slovo='$slovo',";
			} else {
				$sql .= "slovo=null,";
			}
			if ($nazivOsigKriv) {
				$sql .= "nazivosigkriv='$nazivOsigKriv',";
			} else {
				$sql .= "nazivosigkriv=null,";
			}
			if ($brPoliseKriv) {
				$sql .= "brpolisekriv='$brPoliseKriv',";
			} else {
				$sql .= "brpolisekriv=null,";
			}
			if ($vaznostOdKriv) {
				$sql .= "vaznostodkriv='$vaznostOdKriv',";
			} else {
				$sql .= "vaznostodkriv=null,";
			}
			if ($vaznostDoKriv) {
				$sql .= "vaznostdokriv='$vaznostDoKriv', ";
			} else {
				$sql .= "vaznostdokriv=null, ";
			}
			if ($modelOst) {
				$sql .= "modelost='$modelOst', ";
			} else {
				$sql .= "modelost=null, ";
			}
			if ($modelKriv) {
				$sql .= "modelkriv='$modelKriv', ";
			} else {
				$sql .= "modelkriv=null, ";
			}
			if ($prihvacena) {
				$sql .= "prihvacena='$prihvacena', ";
			} else {
				$sql .= "prihvacena=null, ";
			}
			if ($pocetak2) {
				$sql .= "pocetak2='$pocetak2' ,";
			} else {
				$sql .= "pocetak2=null ,";
			}
			if ($kraj2) {
				$sql .= "kraj2='$kraj2' ,";
			} else {
				$sql .= "kraj2=null, ";
			}
			if ($prihvacena2) {
				$sql .= "prihvacena2='$prihvacena2' ,";
			} else {
				$sql .= "prihvacena2=null, ";
			}
			if ($gotovina) {
				$sql .= "gotovina=$gotovina,";
			} else {
				$sql .= "gotovina=false,";
			}
			if ($virman) {
				$sql .= " virman=$virman,";
			} else {
				$sql .= " virman=false,";
			}
			if ($doznaka) {
				$sql .= "doznaka=$doznaka,";
			} else {
				$sql .= "doznaka=false,";
			}
			if ($kompenzacija) {
				$sql .= "kompenzacija=$kompenzacija,";
			} else {
				$sql .= "kompenzacija=false,";
			}
			if ($fotoaparat) {
				$sql .= "fotoaparat_id=$fotoaparat,";
			} else {
				$sql .= "fotoaparat_id=null,";
			}
			if ($teren) {
				$sql .= "teren=$teren,";
			} else {
				$sql .= "teren=false,";
			}
			if ($tekRacun_ost) {
				$sql .= "tekracun_ost='$tekRacun_ost',";
			} else {
				$sql .= "tekracun_ost=null,";
			}
			if ($nacin_resavanja) {
				$sql .= "nacin_resavanja='$nacin_resavanja',";
			} else {
				$sql .= "nacin_resavanja=null,";
			}
			if ($nacin_resavanja2) {
				$sql .= "nacin_resavanja2='$nacin_resavanja2',";
			} else {
				$sql .= "nacin_resavanja2=null,";
			}
			if ($naknadna_isplata) {
				$sql .= "naknadna_isplata='$naknadna_isplata', ";
			} else {
				$sql .= "naknadna_isplata=null, ";
			}
			if ($datumPrijave) {
				$sql .= "datumprijave='$datumPrijave',";
			} else {
				$sql .= "datumprijave=null,";
			}
			if ($struktura) {
				$sql .= "struktura='$struktura', ";
			} else {
				$sql .= "struktura=null, ";
			}
			if ($adresaOst) {
				$sql .= "adresaost=upper('$adresaOst'), ";
			} else {
				$sql .= "adresaost=null, ";
			}
			if ($posbrOst) {
				$sql .= "posbrost=$posbrOst, ";
			} else {
				$sql .= "posbrost=null, ";
			}
			if ($posbrOvllice) {
				$sql .= "posbrovllice=$posbrOvllice, ";
			} else {
				$sql .= "posbrovllice=null, ";
			}
			// MARIJA update za knjigas - 2014-12-13 - POÈETAK
			if ($osteceni_mesto_id) {
				$sql .= "osteceni_mesto_id='$osteceni_mesto_id', ";
			} else {
				$sql .= "osteceni_mesto_id=null,";
			}
			if ($osiguranik_krivac_mesto_id) {
				$sql .= "osiguranik_krivac_mesto_id='$osiguranik_krivac_mesto_id', ";
			} else {
				$sql .= "osiguranik_krivac_mesto_id=null,";
			}
			if ($osteceni_mesto_opis) {
				$sql .= "osteceni_mesto_opis=upper('$osteceni_mesto_opis'), ";
			} else {
				$sql .= "osteceni_mesto_opis=null,";
			}
			if ($osiguranik_krivac_mesto_opis) {
				$sql .= "osiguranik_krivac_mesto_opis=upper('$osiguranik_krivac_mesto_opis'), ";
			} else {
				$sql .= "osiguranik_krivac_mesto_opis=null,";
			}
			//dodato za adresu za osiguranika krivca
			if ($osiguranik_krivac_adresa) {
				$sql .= "osiguranik_krivac_adresa=upper('$osiguranik_krivac_adresa'), ";
			} else {
				$sql .= "osiguranik_krivac_adresa=null, ";
			}
			//dodato za zemlje lica sve sem vozaca
			if ($osteceni_zemlja_id) {
				$sql .= "osteceni_zemlja_id='$osteceni_zemlja_id', ";
			} else {
				$sql .= "osteceni_zemlja_id=null,";
			}
			if ($osiguranik_krivac_zemlja_id) {
				$sql .= "osiguranik_krivac_zemlja_id='$osiguranik_krivac_zemlja_id', ";
			} else {
				$sql .= "osiguranik_krivac_zemlja_id=null,";
			}
			if ($osiguranik_krivac_telefon1) {
				$sql .= "osiguranik_krivac_telefon1='$osiguranik_krivac_telefon1', ";
			} else {
				$sql .= "osiguranik_krivac_telefon1=null,";
			}
			if ($osiguranik_krivac_telefon2) {
				$sql .= "osiguranik_krivac_telefon2='$osiguranik_krivac_telefon2', ";
			} else {
				$sql .= "osiguranik_krivac_telefon2=null,";
			}
			//dodat broj licne karte za sva lica osim za vozace
			if ($osteceni_broj_licne_karte) {
				$sql .= "osteceni_broj_licne_karte='$osteceni_broj_licne_karte', ";
			} else {
				$sql .= "osteceni_broj_licne_karte=null,";
			}
			if ($osiguranik_krivac_broj_licne_karte) {
				$sql .= "osiguranik_krivac_broj_licne_karte='$osiguranik_krivac_broj_licne_karte', ";
			} else {
				$sql .= "osiguranik_krivac_broj_licne_karte=null,";
			}
			// dodato 2016-06-15
			if ($osiguranik_krivac_tekuci_racun) {
				$sql .= "osiguranik_krivac_tekuci_racun='$osiguranik_krivac_tekuci_racun', ";
			} else {
				$sql .= "osiguranik_krivac_tekuci_racun=null,";
			}
			// MARIJA update za knjigas - 2014-12-13 - POÈETAK
			// Ostala nova polja, osim brojeva ¹asije koja su promenjena za tg%
			$sql .= "rbrreaktivirana = " . ($rbrReaktivirana ? "'$rbrReaktivirana'" : 'NULL') . ", ";
			$sql .= "rbrsd = " . ($rbrSD ? $rbrSD : 'NULL') . ", ";
			$sql .= "opisstete = " . ($opisStete ? "'$opisStete'" : 'NULL') . ", ";

			// Marko Markovic 2020-05-28
			$sql .= "napomenasnimanje = " . ($napomenaSnimanje ? "'$napomenaSnimanje'" : 'NULL') . ", ";

			$sql .= "storno = " . ($storno ? $storno : 0) . ", ";
			$sql .= "totalnasteta = " . ($totalnaSteta ? $totalnaSteta :  0) . ", ";
			// Novo za RAZLOG REAKTIVACIJE
			$sql .= "razlog_reaktivacije = '$razlog_reaktivacije', ";
			if ($rbrSteta) {
				$sql .= "rbrsteta=$rbrSteta, ";
			} else {
				$sql .= "rbrsteta=null, ";
			}
			if ($reaktivirana) {
				$sql .= "reaktivirana='$reaktivirana', ";
			} else {
				$sql .= "reaktivirana=null, ";
			}
			// 2016-03-22 dodato za evidenciju polja reosiguranja
			if ($radnik == 151 || $radnik == 138 || $radnik == 3045 || $radnik == 3036 || $radnik == 2059 || $radnik == 3071 || $radnik == 3064 || $radnik == 3085 || $radnik == 3090 || $radnik == 3106 || $radnik == 2244 || $radnik == 2106 || $radnik == 3126 || $radnik == 3125) {
				if ($prijaviti_u_reosiguranje) {
					$sql .= "prijaviti_u_reosiguranje=$prijaviti_u_reosiguranje, ";
				} else {
					$sql .= "prijaviti_u_reosiguranje=null,";
				}
			}
			/*Branka - 2014-10-31 - DA/NE procena - POÈETAK*/
			if ($imao_policijski_zapisnik == 'DA') {
				$sql .= "snimanje_stete_procenitelj_imao_policijski_zapisnik=1::bit,";
			} else if ($imao_policijski_zapisnik == 'NE') {
				$sql .= "snimanje_stete_procenitelj_imao_policijski_zapisnik=0::bit,";
			} else {
				$sql .= "snimanje_stete_procenitelj_imao_policijski_zapisnik=null,";
			}

			// --------- Marko Markovic IO zapisnici 2020-05-13 Evropski izvestaj se ne upisuje -----------
			if ($vrstaSt == 'IO') {
				if ($zapisnici_io == 'zapisnik_pozar') {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=4";
				} else if ($zapisnici_io == 'zapisnik_kradja') {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=5";
				} else if ($zapisnici_io == 'zapisnik_lom_masina') {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=6";
				} else if ($zapisnici_io == 'zapisnik_lom_stakla') {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=7";
				} else if ($zapisnici_io == 'zapisnik_odgovornost') {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=8";
				} else {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=-1";
				}
			} else {
				if ($imao_evropski_izvestaj == 'DA') {
					$sql .= "snimanje_stete_procenitelj_imao_evropski_izvestaj=1::bit,";
				} else if ($imao_evropski_izvestaj == 'NE') {
					$sql .= "snimanje_stete_procenitelj_imao_evropski_izvestaj=0::bit,";
				} else {
					$sql .= "snimanje_stete_procenitelj_imao_evropski_izvestaj=null,";
				}
				if ($izvrsio_uporedjivanje_vozila == 'DA') {
					$sql .= "snimanje_stete_procenitelj_je_izvrsio_poredjenje_vozila=1::bit,";
				} else if ($izvrsio_uporedjivanje_vozila == 'NE') {
					$sql .= "snimanje_stete_procenitelj_je_izvrsio_poredjenje_vozila=0::bit,";
				} else {
					$sql .= "snimanje_stete_procenitelj_je_izvrsio_poredjenje_vozila=null,";
				}
				if ($slikao_drugo_vozilo_odvojeno == 'DA') {
					$sql .= "snimanje_stete_procenitelj_je_slikao_vozila_odvojeno=1::bit,";
				} else if ($slikao_drugo_vozilo_odvojeno == 'NE') {
					$sql .= "snimanje_stete_procenitelj_je_slikao_vozila_odvojeno=0::bit,";
				} else {
					$sql .= "snimanje_stete_procenitelj_je_slikao_vozila_odvojeno=null,";
				}
				$slikao_gde_baza = pg_escape_string($slikao_gde);
				if ($slikao_gde) {
					$sql .= "snimanje_stete_mesto_gde_je_procenitelj_slikao_drugo_vozilo='$slikao_gde_baza',";
				} else {
					$sql .= "snimanje_stete_mesto_gde_je_procenitelj_slikao_drugo_vozilo=null, ";
				}
				if ($slikao_kada) {
					$sql .= "snimanje_stete_datum_kada_je_procenitelj_slikao_drugo_vozilo='$slikao_kada',";
				} else {
					$sql .= "snimanje_stete_datum_kada_je_procenitelj_slikao_drugo_vozilo=null,";
				}
				if ($slikao_vreme) {
					$sql .= "snimanje_stete_vreme_kada_je_procenitelj_slikao_drugo_vozilo='$slikao_vreme',";
				} else {
					$sql .= "snimanje_stete_vreme_kada_je_procenitelj_slikao_drugo_vozilo=null,";
				}
				if ($stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen == 'DA') {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=1";
				} else if ($stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen == 'NE') {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=2";
				} else if ($stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen == 'IZABERITE') {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=-1";
				} else {
					$sql .= "snimanje_stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen=3";
				}
			}
			// Marko Markovic kraj 2020-05-13

			/*Branka - 2014-10-31 - DA/NE procena - KRAJ*/
			$sql .= " where  id=$idstete";
			$rezultat1 = pg_query($conn, $sql);

			//Nemanja Jovanovic - update email-a podnosioca zahteva
			$sql_id_odstetni_zahtev = "SELECT odstetni_zahtev_id FROM predmet_odstetnog_zahteva WHERE id = $idstete";
			$rez_id_odstetni_zahtev = pg_query($conn, $sql_id_odstetni_zahtev);
			$niz_id_odstetni_zahtev = pg_fetch_assoc($rez_id_odstetni_zahtev);
			$odstetni_zahtev_id_niz 		 = $niz_id_odstetni_zahtev['odstetni_zahtev_id'];
			$podnosioca_prijave_email_hidden = $_POST['podnosioca_prijave_email_hidden'];

			$sql_update_podnosilac_mail = "	UPDATE odstetni_zahtev 
								SET podnosilac_zahteva_email = '$podnosioca_prijave_email_hidden' 
								WHERE id = $odstetni_zahtev_id_niz";
			$rez_update_podnosilac_mail = pg_query($conn, $sql_update_podnosilac_mail);



			// Dodat upit za izmenu podataka za vrste ¹teta DPZ && tip stete ZP
			if ($vrstaSt == 'DPZ' && $tipSt == '0205') {
				// Sreðivanje stringova za upite
				$osteceni_broj_pasosa_update = pg_escape_string(trim($osteceni_broj_pasosa));
				$osteceni_pol_update = pg_escape_string(trim($osteceni_pol));
				$osteceni_email_update = pg_escape_string(trim($osteceni_email));
				$datum_ulaska_u_zemlju_destinacije_update = pg_escape_string(trim($datum_ulaska_u_zemlju_destinacije));
				$datum_izlaska_iz_zemlje_destinacije_update = pg_escape_string(trim($datum_izlaska_iz_zemlje_destinacije));
				$datum_prijema_medicinska_ustanova_update = pg_escape_string(trim($datum_prijema_medicinska_ustanova));
				$datum_otpustanja_medicinska_ustanova_update = pg_escape_string(trim($datum_otpustanja_medicinska_ustanova));
				$naziv_medicinske_ustanove_update = pg_escape_string(trim($naziv_medicinske_ustanove));
				$ime_lekara_update = pg_escape_string(trim($ime_lekara));
				$vrsta_povrede_ili_bolesti_update = pg_escape_string(trim($vrsta_povrede_ili_bolesti));
				$vrsta_lecenja_update = pg_escape_string(trim($vrsta_lecenja));
				$napomena_o_osiguranom_slucaju_update = pg_escape_string(trim($napomena_o_osiguranom_slucaju));
				// POÈETAK upita
				$sql_dpz_zp = "UPDATE knjigas_dpz_zp SET ";
				// Deo podataka o o¹tetenom
				$sql_dpz_zp .= ($osteceni_broj_pasosa ? "osteceni_broj_pasosa='$osteceni_broj_pasosa_update', " : "osteceni_broj_pasosa=NULL, ");
				$sql_dpz_zp .= ($osteceni_pol == '-1' ? "osteceni_pol='$osteceni_pol_update', " : "osteceni_pol='-1', ");
				$sql_dpz_zp .= ($osteceni_email ? "osteceni_email='$osteceni_email_update', " : "osteceni_email=NULL, ");
				// Datumi
				$sql_dpz_zp .= ($datum_ulaska_u_zemlju_destinacije ? "datum_ulaska_u_zemlju_destinacije='$datum_ulaska_u_zemlju_destinacije_update', " : "datum_ulaska_u_zemlju_destinacije=NULL, ");
				$sql_dpz_zp .= ($datum_izlaska_iz_zemlje_destinacije ? "datum_izlaska_iz_zemlje_destinacije='$datum_izlaska_iz_zemlje_destinacije_update', " : "datum_izlaska_iz_zemlje_destinacije=NULL, ");
				$sql_dpz_zp .= ($datum_prijema_medicinska_ustanova ? "datum_prijema_medicinska_ustanova='$datum_prijema_medicinska_ustanova_update', " : "datum_prijema_medicinska_ustanova=NULL, ");
				$sql_dpz_zp .= ($datum_otpustanja_medicinska_ustanova ? "datum_otpustanja_medicinska_ustanova='$datum_otpustanja_medicinska_ustanova_update', " : "datum_otpustanja_medicinska_ustanova=NULL, ");
				// Med.ustanova, lekar, leèenje, povreda i napomena
				$sql_dpz_zp .= ($naziv_medicinske_ustanove ? "naziv_medicinske_ustanove='$naziv_medicinske_ustanove_update', " : "naziv_medicinske_ustanove=NULL, ");
				$sql_dpz_zp .= ($ime_lekara ? "ime_lekara='$ime_lekara_update', " : "ime_lekara=NULL, ");
				$sql_dpz_zp .= ($vrsta_povrede_ili_bolesti ? "vrsta_povrede_ili_bolesti=$vrsta_povrede_ili_bolesti_update, " : "vrsta_povrede_ili_bolesti=NULL, ");
				$sql_dpz_zp .= ($vrsta_lecenja ? "vrsta_lecenja='$vrsta_lecenja_update', " : "vrsta_lecenja=NULL, ");
				$sql_dpz_zp .= ($napomena_o_osiguranom_slucaju ? "napomena_o_osiguranom_slucaju='$napomena_o_osiguranom_slucaju_update' " : "napomena_o_osiguranom_slucaju=NULL ");
				// Uslov po kome se radi  a¾uriranje tabele
				$sql_dpz_zp .= "WHERE	idstete=$idstete;";
				$rezultat_dpz_zp = pg_query($conn, $sql_dpz_zp);
				if (pg_affected_rows($rezultat_dpz_zp) == 0) {
					$sql_dpz_zp_insert = "INSERT INTO public.knjigas_dpz_zp(
            idstete, osteceni_broj_pasosa, osteceni_pol, osteceni_email,
            datum_ulaska_u_zemlju_destinacije, datum_izlaska_iz_zemlje_destinacije,
            naziv_medicinske_ustanove, ime_lekara, datum_prijema_medicinska_ustanova,
            datum_otpustanja_medicinska_ustanova, vrsta_povrede_ili_bolesti,
            vrsta_lecenja, napomena_o_osiguranom_slucaju)
    VALUES ( $idstete, ";
					$sql_dpz_zp_insert .= ($osteceni_broj_pasosa ? "'$osteceni_broj_pasosa_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($osteceni_pol ? "'$osteceni_pol_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($osteceni_email ? "'$osteceni_email_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($datum_ulaska_u_zemlju_destinacije ? "'$datum_ulaska_u_zemlju_destinacije_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($datum_izlaska_iz_zemlje_destinacije ? "'$datum_izlaska_iz_zemlje_destinacije_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($naziv_medicinske_ustanove ? "'$naziv_medicinske_ustanove_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($ime_lekara ? "'$ime_lekara_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($datum_prijema_medicinska_ustanova ? "'$datum_prijema_medicinska_ustanova_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($datum_otpustanja_medicinska_ustanova ? "'$datum_otpustanja_medicinska_ustanova_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($vrsta_povrede_ili_bolesti ? "$vrsta_povrede_ili_bolesti_update, " : "NULL, ");
					$sql_dpz_zp_insert .= ($vrsta_lecenja ? "'$vrsta_lecenja_update', " : "NULL, ");
					$sql_dpz_zp_insert .= ($napomena_o_osiguranom_slucaju ? "'$napomena_o_osiguranom_slucaju_update' " : "NULL ");
					$sql_dpz_zp_insert .= ");";
					$rezultat_dpz_zp_insert = pg_query($conn, $sql_dpz_zp_insert);
				}
			}

			$sql = "select * from pravni where idstete=$idstete";
			$rezultat = pg_query($conn, $sql);
			$niz = pg_fetch_assoc($rezultat);


			//DODAO VLADA
			$nastavi = true;

			//PREMESTIO VLADA
			$br = $niz['idstete'];


			//AKO NIJE POTVRDJEN OSNOV ZA REGRES,POKRENI VALIDACIJU
			if($potvrdjen_osnov_za_regres !== true) {

				//AKO POSTOJI POTVRDJEN OSNOV ZA REGRES
				if (isset($_POST['potvrdjen_osnov_za_regres']) && $_POST['potvrdjen_osnov_za_regres'] == 'true') {


					//KREIRANJE PROMENJIVE ZA GRESKU I FLAGA ZA STATUS VALIDACIJE
					$tekst = '';

					//AKO NIJE ODABRANO OD KOGA JE REGRES
					if ($_POST['regres_od'] == 'Izaberite') {

						$tekst .= 'Morate odabrati od koga je regres!\n';

						echo "<script language=\"javascript\">\n";
						echo "document.pregled.regres_od.focus();\n";
						echo "</script>\n";
						$nastavi = false;
					}

					//AKO NIJE ODABRAN TIP LICA
					if (!isset($_POST['tip_lica'])) {

						$tekst .= 'Morate odabrati tip lica!\n';

						echo "<script language=\"javascript\">\n";
						echo "document.pregled.tip_lica.focus();\n";
						echo "</script>\n";
						$nastavi = false;
					}
					//AKO JE ODABRAN TIP LICA
					else {

						//AKO JE TIP LICA FIZICKO
						if ($_POST['tip_lica'] == 'fizicko') {

							$tip_lica = 'F';

							$osiguravajuce_drustvo_id = '';

							//AKO JE PRAZNO POLJE IME DUZNIKA
							if ($_POST['ime_duznika'] == '') {

								$tekst .= 'Morate uneti ime regresnog du¾nika!\n';

								echo "<script language=\"javascript\">\n";
								echo "document.pregled.ime_duznika.focus();\n";
								echo "</script>\n";
								$nastavi = false;
							}

							//AKO JE PRAZNO POLJE PREZIME DUZNIKA
							if ($_POST['prezime_duznika'] == '') {

								$tekst .= 'Morate uneti prezime regresnog du¾nika!\n';

								echo "<script language=\"javascript\">\n";
								echo "document.pregled.prezime_duznika.focus();\n";
								echo "</script>\n";
								$nastavi = false;
							}
			
							//AKO NIJE UNET JMBG DUZNIKA
							if ($_POST['jmbg_pib'] == '') {

								$tekst .= 'Morate uneti JMBG regresnog du¾nika!\n';

								echo "<script language=\"javascript\">\n";
								echo "document.pregled.jmbg_pib.focus();\n";
								echo "</script>\n";
								$nastavi = false;
							}
						}
						//AKO JE TIP LICA PRAVNO
						else {

							$tip_lica = 'P';
			
							//AKO JE REGRES OD OSIGURAVAJUCEG DRUSTVA
							if($_POST['regres_od'] == 'Osiguravajuæe dru¹tvo') {

								//AKO NIJE ODABRANO OSIGURAVAJUCE DRUSTVO
								if($_POST['osiguravajuce_drustvo_id'] == '' || $_POST['osiguravajuce_drustvo_id'] == '-1') {

									$tekst .= 'Morate odabrati osiguravajuæe dru¹tvo!\n';

									echo "<script language=\"javascript\">\n";
									echo "document.pregled.osiguravajuce_drustvo_id.focus();\n";
									echo "</script>\n";
									$nastavi = false;
								}

								//AKO NIJE UNET NAZIV OSIGURAVAJUCEG DRUSTVA
								if($_POST['osiguranjeRegPotr'] == '') {

									$tekst .= 'Morate uneti naziv osiguravajuæeg dru¹tva!\n';

									echo "<script language=\"javascript\">\n";
									echo "document.pregled.osiguranjeRegPotr.focus();\n";
									echo "</script>\n";
									$nastavi = false;
								}
							}
							//U SUPROTNOM
							else {

								$osiguravajuce_drustvo_id = '';

								//AKO NIJE UNET NAZIV DUZNIKA
								if ($_POST['ime_duznika'] == '') {

									$tekst .= 'Morate uneti naziv regresnog du¾nika!\n';

									echo "<script language=\"javascript\">\n";
									echo "document.pregled.ime_duznika.focus();\n";
									echo "</script>\n";
									$nastavi = false;
								}
							}
			
							//AKO NIJE UNET PIB DUZNIKA
							if ($_POST['jmbg_pib'] == '') {

								$tekst .= 'Morate uneti pib regresnog du¾nika!\n';

								echo "<script language=\"javascript\">\n";
								echo "document.pregled.jmbg_pib.focus();\n";
								echo "</script>\n";
								$nastavi = false;
							}
						}
					}

					//AKO NIJE ODABRANA DRZAVA
					if ($_POST['drzava_reg_id'] == '' || $_POST['drzava_reg_id'] == '-1') {

						$tekst .= 'Morate odabrati dr¾avu regresnog du¾nika!\n';

						echo "<script language=\"javascript\">\n";
						echo "document.pregled.drzava_reg_id.focus();\n";
						echo "</script>\n";
						$nastavi = false;

						//AKO NIJE ODABRANA OPSTINA
						if ($_POST['opstina_reg'] == '' || $_POST['opstina_reg'] == '-1') {

							$tekst .= 'Morate odabrati op¹tinu regresnog du¾nika!\n';

							echo "<script language=\"javascript\">\n";
							echo "document.pregled.opstina_reg.focus();\n";
							echo "</script>\n";
							$nastavi = false;
						}

						//AKO NIJE ODABRANO MESTO
						if ($_POST['mesto_reg'] == '' || $_POST['mesto_reg'] == '-1') {

							$tekst .= 'Morate odabrati mesto regresnog du¾nika!\n';

							echo "<script language=\"javascript\">\n";
							echo "document.pregled.mesto_reg.focus();\n";
							echo "</script>\n";
							$nastavi = false;
						}
					}
			
					//AKO JE ODABRANA DRZAVA SRBIJA
					if ($_POST['drzava_reg_id'] == '199') {

						//AKO NIJE ODABRANA OPSTINA
						if ($_POST['opstina_reg'] == '' || $_POST['opstina_reg'] == '-1') {

							$tekst .= 'Morate odabrati op¹tinu regresnog du¾nika!\n';

							echo "<script language=\"javascript\">\n";
							echo "document.pregled.opstina_reg.focus();\n";
							echo "</script>\n";
							$nastavi = false;
						}

						//AKO NIJE ODABRANO MESTO
						if ($_POST['mesto_reg'] == '' || $_POST['mesto_reg'] == '-1') {

							$tekst .= 'Morate odabrati mesto regresnog du¾nika!\n';

							echo "<script language=\"javascript\">\n";
							echo "document.pregled.mesto_reg.focus();\n";
							echo "</script>\n";
							$nastavi = false;
						}
					}

					//AKO NIJE UNETA ADRESA
					if ($_POST['adresa_reg'] == '') {

						$tekst .= 'Morate uneti adresu regresnog du¾nika!\n';

						echo "<script language=\"javascript\">\n";
						echo "document.pregled.adresa_reg.focus();\n";
						echo "</script>\n";
						$nastavi = false;
					}

					//AKO NIJE UNET TELEFON
					if ($_POST['telefon_reg'] == '') {

						$tekst .= 'Morate uneti telefon regresnog du¾nika!\n';

						echo "<script language=\"javascript\">\n";
						echo "document.pregled.telefon_reg.focus();\n";
						echo "</script>\n";
						$nastavi = false;
					}
			
					//AKO NIJE UNET IZNOS POTRAZIVANJA
					if ($_POST['koliko_potrazivati'] == '') {

						$tekst .= 'Morate uneti iznos regresnog potra¾ivanja!\n';

						echo "<script language=\"javascript\">\n";
						echo "document.pregled.koliko_potrazivati.focus();\n";
						echo "</script>\n";
						$nastavi = false;
					}
					//U SUPROTNOM
					else {

						//AKO IZNOS POTRAZIVANJA NIJE U DOBROM FORMATU
						if ($_POST['koliko_potrazivati'] && !preg_match("/^[0-9]{1,14}\.?[0-9]{0,2}$/", $_POST['koliko_potrazivati'])) {

							$tekst .= 'Visina potra¾ivanja nije u dobrom formatu!\n';

							echo "<script language=\"javascript\">\n";
							echo "document.pregled.koliko_potrazivati.value='';\n";
							echo "document.pregled.koliko_potrazivati.focus();\n";
							echo "</script>\n";
							$nastavi = false;
						}
					}
			
					//AKO BILO KOJE POLJE SA REGRESNIM PODACIMA NIJE DOBRO POPUNJENO
					if ($br && $nastavi === false) {

						echo "<script language=\"javascript\">\n";
						echo "alert('$tekst');";
						echo "location.href = 'http://10.101.50.212/stete/pregled.php?idstete=" .$br. "&dugme=DA';";
						echo "</script>\n";
					}
				}
			}

			//SETOVANJE TIPA LICA ZA UNOS U BAZU
			if (isset($_POST['tip_lica'])) {
				
				if ($_POST['tip_lica'] == 'fizicko') {

					$tip_lica = 'F';
				}
				else {
					$tip_lica = 'P';
				}
			}
			
			//AKO JE CEKIRANO POLJE ODUSTAO ILI STORNO, SETUJ VREDNOSTI OSNOVAN I DELIMICNOPROC NA PRAZNO
			if ($odustao_pravni_osnov || $storno) {

				$osnovan = '';
				$delimicnoProc = '';
			}

			if ($br) {

				if ($nastavi === true) {

					if ($datumPravniOsnov || $osnovan || $delimicnoProc || $vraceno || $vrstaRegPotr || $osiguranjeRegPotr || $drzavaRegPotr || $regPotr || $pravnaPredato || $pravniOsnovDao != '0' || $pravniOsnovDao_1 != '0' || $pravniOsnovDao_2 != '0'  ||$pravniOsnovNapomena || $pravniOsnovObradjivac != '0' || $datumPrijemaPredmetaPravnaSluzba || $pravniOsnovDatumKompletiranjaDokumentacije || $razlog_umanjenja_stete_id || $lista_za_pravni_osnov || $alkotest_osteceni || $alkotest_krivac || $regres_od || $osiguravajuce_drustvo_id || $potvrdjen_osnov_za_regres || $radnik_evidentirao_potvrdu_za_regres || $datum_evidentiranja_potvrde_za_regres || $razlog_regresa_id || $regresno_potrazivanje_napomena || $odustao_pravni_osnov) {

						$sql = "update pravni set ";
						if ($datumPravniOsnov) {
							$sql .= "datumpravniosnov='$datumPravniOsnov',";
						} else {
							$sql .= "datumpravniosnov=null,";
						}
						if ($osnovan) {
							$sql .= "osnovan='$osnovan',";
						} else {
							$sql .= "osnovan=null,";
						}
						if ($delimicnoProc) {
							$sql .= "delimicnoproc=$delimicnoProc,";
						} else {
							$sql .= "delimicnoproc=null,";
						}
						if ($vraceno) {
							$sql .= "vraceno='$vraceno',";
						} else {
							$sql .= "vraceno=null,";
						}
						if ($vrstaRegPotr) {
							$sql .= "vrstaregpotr='$vrstaRegPotr',";
						} else {
							$sql .= "vrstaregpotr=null,";
						}
						if ($oznakaRegPotr) {
							$sql .= "oznakaregpotr='$oznakaRegPotr',";
						} else {
							$sql .= "oznakaregpotr=null,";
						}
						if ($osiguranjeRegPotr) {
							$sql .= "osiguranjeregpotr='$osiguranjeRegPotr',";
						} else {
							$sql .= "osiguranjeregpotr=null,";
						}
						/*ZAKOMENTARISAO VLADA - ZBOG NOVOG NACINA UPISA DRZAVE
						if ($drzavaRegPotr) {
							$sql .= "drzavaregpotr='$drzavaRegPotr',";
						} else {
							$sql .= "drzavaregpotr=null,";
						}
						*/	

						//DODAO VLADA
						if ($drzava_reg_id) {
							$sql .= "drzava_reg_id = '$drzava_reg_id',";
						} else {
							$sql .= "drzava_reg_id = null,";
						}

						$sql .= " radnik=$radnik, dana=current_date, vreme=current_time,  ";
						if ($regPotr) {
							$sql .= "regpotr=$regPotr, ";
						} else {
							$sql .= "regpotr=null, ";
						}
						if ($pravnaPredato) {
							$sql .= "pravnapredato='$pravnaPredato', ";
						} else {
							$sql .= "pravnapredato=null, ";
						}
						if ($pravniOsnovDao != '0') {
							$sql .= "pravni_osnov_dao=$pravniOsnovDao, ";
						} else {
							$sql .= "pravni_osnov_dao=null, ";
						}
					
						if ($pravniOsnovNapomena) {
							$sql .= "pravni_osnov_napomena='$pravniOsnovNapomena', ";
						} else {
							$sql .= "pravni_osnov_napomena=null, ";
						}
						if ($pravniOsnovObradjivac != '0') {
							$sql .= "pravni_osnov_obradjivac=$pravniOsnovObradjivac, ";
						} else {
							$sql .= "pravni_osnov_obradjivac=null, ";
						}
						if ($datumPrijemaPredmetaPravnaSluzba) {
							$sql .= "datum_prijema_predmeta_pravna_sluzba='$datumPrijemaPredmetaPravnaSluzba', ";
						} else {
							$sql .= "datum_prijema_predmeta_pravna_sluzba=null, ";
						}
						if ($pravniOsnovDatumKompletiranjaDokumentacije) {
							$sql .= "pravni_osnov_datum_kompletiranja_dokumentacije='$pravniOsnovDatumKompletiranjaDokumentacije', ";
						} else {
							$sql .= "pravni_osnov_datum_kompletiranja_dokumentacije=null, ";
						}


						// MARIJA 18.02.2015 dodato za razlog smanjenja stete - POCETAK
						if ($razlog_umanjenja_stete_id) {
							$sql .= "razlog_umanjenja_stete_id=$razlog_umanjenja_stete_id, ";
						} else {
							$sql .= "razlog_umanjenja_stete_id=null, ";
						}

						//$pravni_osnov_izvestaj = ($pravni_osnov_izvestaj != 1) ? $pravni_osnov_izvestaj: null;

						// if ($pravni_osnov_izvestaj){$sql.="pravni_osnov_izvestaj='$pravni_osnov_izvestaj', "; }
						// else{$sql.="pravni_osnov_izvestaj=null, ";}
						if ($lista_za_pravni_osnov) {
							$sql .= "osnov_pravnog_osnova=TRUE, ";
						} else {
							$sql .= "osnov_pravnog_osnova=FALSE, ";
						}
						$alkotest_osteceni = ($alkotest_osteceni) ? $alkotest_osteceni : '0.00';
						if ($alkotest_osteceni) {
							$sql .= "alkotest_osteceni=$alkotest_osteceni, ";
						} else {
							$sql .= "alkotest_osteceni=null, ";
						}
						$alkotest_krivac = ($alkotest_krivac) ? $alkotest_krivac : '0.00';
						if ($alkotest_krivac) {
							$sql .= "alkotest_krivac=$alkotest_krivac, ";
						} else {
							$sql .= "alkotest_krivac=null, ";
						}
						//MARIJA 19.02.2015 - dodato za snimanje izvestaja na osnovu koga je dat pravni osnov - KRAJ

						// MARIJA 27.02.2015 _ REGRES polja - POCETAK
						if ($regres_od) {
							$sql .= "regres_od='$regres_od', ";
						} else {
							$sql .= "regres_od=null, ";
						}
						// MARIJA 27.02.2015 _ REGRES polja - KRAJ

						// MARIJA 02.03.2015 - POCETAK
						if ($osiguravajuce_drustvo_id) {
							$sql .= "osiguravajuce_drustvo_id=$osiguravajuce_drustvo_id, ";
						} else {
							$sql .= "osiguravajuce_drustvo_id=null, ";
						}

						if ($potvrdjen_osnov_za_regres) {
							$sql .= "potvrdjen_osnov_za_regres='$potvrdjen_osnov_za_regres', ";
						} else {
							$sql .= "potvrdjen_osnov_za_regres=null, ";
						}

						if ($radnik_evidentirao_potvrdu_za_regres && $potvrdjen_osnov_za_regres) {
							$sql .= "radnik_evidentirao_potvrdu_za_regres='$radnik_evidentirao_potvrdu_za_regres', ";
						} else {
							$sql .= "radnik_evidentirao_potvrdu_za_regres=null, ";
						}

						if ($datum_evidentiranja_potvrde_za_regres && $potvrdjen_osnov_za_regres) {
							$sql .= "datum_evidentiranja_potvrde_za_regres='$datum_evidentiranja_potvrde_za_regres', ";
						} else {
							$sql .= "datum_evidentiranja_potvrde_za_regres=null, ";
						}

						if ($razlog_regresa_id) {
							$sql .= "razlog_regresa_id=$razlog_regresa_id, ";
						} else {
							$sql .= "razlog_regresa_id=null, ";
						}

						if ($regresno_potrazivanje_napomena) {
							$sql .= "regresno_potrazivanje_napomena='$regresno_potrazivanje_napomena', ";
						} else {
							$sql .= "regresno_potrazivanje_napomena=null, ";
						}

						//Dodao Marko Stankovic31.07.2018.
						if ($odustao_pravni_osnov == true) {
							$sql .= "odustao=true,";
						} else {
							$sql .= "odustao=false,";
						}
						// MARIJA 02.03.2015 - KRAJ
						if ($pravniOsnovDao_1 != '0' && $pravniOsnovDao_1 != '') {
							$sql .= "pravni_osnov_dao_1 = $pravniOsnovDao_1, ";
						} 
						if ($pravniOsnovDao_1 == '0' || $pravniOsnovDao_1 == '')
						{
							$sql .= " pravni_osnov_dao_1 = null,";
						}
						if ($pravniOsnovDao_2 != '0' && $pravniOsnovDao_2 != '') {
							$sql .= "pravni_osnov_dao_2 = $pravniOsnovDao_2, ";
						} 
						if ($pravniOsnovDao_2 == '0' || $pravniOsnovDao_2 == '')
						{
							$sql .= " pravni_osnov_dao_2 = null, ";
						}

						//DODAO VLADA PROSIRENJE UPITA ZA NOVE KOLONE U TABELI PRAVNI - POCETAK
						if($tip_lica) {
							$sql .= "tip_lica = '$tip_lica', ";
						}
						else {
							$sql .= "tip_lica = null, ";
						}

						if($ime_duznika) {
							$sql .= "ime_reg = '$ime_duznika', ";
						}
						else {
							$sql .= "ime_reg = null, ";
						}

						if($prezime_duznika) {
							$sql .= "prezime_reg = '$prezime_duznika', ";
						}
						else {
							$sql .= "prezime_reg = null, ";
						}

						if($jmbg_pib) {
							$sql .= "jmbg_pib = '$jmbg_pib', ";
						}
						else {
							$sql .= "jmbg_pib = null, ";
						}

						if($mesto_reg) {
							$sql .= "mesto_reg_id = '$mesto_reg', ";
						}
						else {
							$sql .= "mesto_reg_id = -1, ";
						}

						if($adresa_reg) {
							$sql .= "adresa_reg = '$adresa_reg', ";
						}
						else {
							$sql .= "adresa_reg = null, ";
						}

						if($telefon_reg) {
							$sql .= "telefon_reg = '$telefon_reg', ";
						}
						else {
							$sql .= "telefon_reg = null, ";
						}


						if($opstina_reg) {
							$sql .= "opstina_reg_id = '$opstina_reg', ";
						}
						else {
							$sql .= "opstina_reg_id = -1, ";
						}

						if($koliko_potrazivati) {
							$sql .= "koliko_potrazivati = '$koliko_potrazivati' ";
						}
						else {
							$sql .= "koliko_potrazivati = null ";
						}
						//DODAO VLADA - KRAJ
		
						$sql .= " where idstete=$idstete";
						
						$rezultat2 = pg_query($conn, $sql);
					} else {
						$sql = "delete from pravni where idstete=$idstete";
						$rezultat2 = pg_query($conn, $sql);
					}
				}
			} else {
				if ($datumPravniOsnov || $osnovan || $delimicnoProc || $vraceno || $vrstaRegPotr || $osiguranjeRegPotr || $drzavaRegPotr || $regPotr || $pravnaPredato || $pravniOsnovDao != '0'  || $pravniOsnovDao_1 != '0' || $pravniOsnovDao_2 != '0' || $pravniOsnovNapomena || $pravniOsnovObradjivac != '0' || $datumPrijemaPredmetaPravnaSluzba || $pravniOsnovDatumKompletiranjaDokumentacije || $razlog_umanjenja_stete_id || $lista_za_pravni_osnov || $alkotest_osteceni || $alkotest_krivac || $regres_od || $osiguravajuce_drustvo_id || $potvrdjen_osnov_za_regres || $radnik_evidentirao_potvrdu_za_regres || $datum_evidentiranja_potvrde_za_regres || $razlog_regresa_id || $regresno_potrazivanje_napomena) {
					$sql = "insert into pravni (idstete,datumpravniosnov,osnovan,delimicnoproc,vraceno,vrstaregpotr,oznakaregpotr,osiguranjeregpotr,
					drzavaregpotr,radnik,dana,vreme,regpotr,pravnapredato,pravni_osnov_dao,pravni_osnov_napomena,pravni_osnov_obradjivac,
					datum_prijema_predmeta_pravna_sluzba,pravni_osnov_datum_kompletiranja_dokumentacije,razlog_umanjenja_stete_id,pravni_osnov_izvestaj,
					alkotest_osteceni,alkotest_krivac,regres_od,osiguravajuce_drustvo_id,potvrdjen_osnov_za_regres,radnik_evidentirao_potvrdu_za_regres,
					datum_evidentiranja_potvrde_za_regres,razlog_regresa_id,regresno_potrazivanje_napomena,pravni_osnov_dao_1,pravni_osnov_dao_2,tip_lica,ime_reg,prezime_reg,jmbg_pib,
					mesto_reg_id,adresa_reg,telefon_reg,opstina_reg_id,koliko_potrazivati,drzava_reg_id) values ($idstete, ";

					if ($datumPravniOsnov) {
						$sql .= "'$datumPravniOsnov',";
					} else {
						$sql .= "null,";
					}
					if ($osnovan) {
						$sql .= " '$osnovan',";
					} else {
						$sql .= "null,";
					}
					if ($delimicnoProc) {
						$sql .= "$delimicnoProc,";
					} else {
						$sql .= "null,";
					}
					if ($vraceno) {
						$sql .= "'$vraceno',";
					} else {
						$sql .= "null,";
					}
					if ($vrstaRegPotr) {
						$sql .= " '$vrstaRegPotr',";
					} else {
						$sql .= "null,";
					}
					if ($oznakaRegPotr) {
						$sql .= " '$oznakaRegPotr',";
					} else {
						$sql .= "null,";
					}
					if ($osiguranjeRegPotr) {
						$sql .= "'$osiguranjeRegPotr',";
					} else {
						$sql .= "null,";
					}
					if ($drzavaRegPotr) {
						$sql .= "'$drzavaRegPotr',";
					} else {
						$sql .= "null,";
					}
					$sql .= " $radnik, current_date, current_time, ";
					if ($regPotr) {
						$sql .= "'$regPotr',";
					} else {
						$sql .= " null,";
					}
					if ($pravnaPredato) {
						$sql .= "'$pravnaPredato', ";
					} else {
						$sql .= " null,";
					}
					if ($pravniOsnovDao != '0') {
						$sql .= "$pravniOsnovDao, ";
					} else {
						$sql .= " null,";
					}
					// MARIJA 02.03.2015 - KRAJ
			
					if ($pravniOsnovNapomena) {
						$sql .= "'$pravniOsnovNapomena', ";
					} else {
						$sql .= " null,";
					}
					if ($pravniOsnovObradjivac != '0') {
						$sql .= "$pravniOsnovObradjivac, ";
					} else {
						$sql .= " null,";
					}
					if ($datumPrijemaPredmetaPravnaSluzba) {
						$sql .= "'$datumPrijemaPredmetaPravnaSluzba',";
					} else {
						$sql .= "null,";
					}
					if ($pravniOsnovDatumKompletiranjaDokumentacije) {
						$sql .= "'$pravniOsnovDatumKompletiranjaDokumentacije',";
					} else {
						$sql .= "null,";
					}
					// MARIJA 18.02.2015 - dodato za umanjenje - POCETAk
					if ($razlog_umanjenja_stete_id) {
						$sql .= "$razlog_umanjenja_stete_id, ";
					} else {
						$sql .= "null,";
					}
					//   if ($pravni_osnov_izvestaj){$sql.="'$pravni_osnov_izvestaj',"; }
					//   else{$sql.="null,";}
					if ($lista_za_pravni_osnov) {
						$sql .= "TRUE,";
					} else {
						$sql .= "FALSE,";
					}

					if ($alkotest_osteceni) {
						$sql .= "$alkotest_osteceni,";
					} else {
						$sql .= "null, ";
					}
					
					if ($alkotest_krivac) {
						//var_dump($alkotest_krivac);
						$sql .= "$alkotest_krivac, ";
					} else {
						$sql .= "null, ";
					}
					
					// MARIJA 19.02.2015 - dodato za pravni izvestaj- KRAJ

					// MARIJA 27.02.2015 - REGES polja dodata - POCETAK
					if ($regres_od) {
						$sql .= "'$regres_od', ";
					} else {
						$sql .= "null, ";
					}
					// MARIJA 27.02.2015 - REGES polja dodata - KRAJ

					// MARIJA 02.03.2015 - POCETAK
					if ($osiguravajuce_drustvo_id) {
						$sql .= "$osiguravajuce_drustvo_id, ";
					} else {
						$sql .= "null, ";
					}

					if ($potvrdjen_osnov_za_regres) {
						$sql .= "'$potvrdjen_osnov_za_regres', ";
					} else {
						$sql .= "null, ";
					}

					if ($radnik_evidentirao_potvrdu_za_regres && $potvrdjen_osnov_za_regres) {
						$sql .= "'$radnik_evidentirao_potvrdu_za_regres', ";
					} else {
						$sql .= "null, ";
					}

					if ($datum_evidentiranja_potvrde_za_regres && $potvrdjen_osnov_za_regres) {
						$sql .= "'$datum_evidentiranja_potvrde_za_regres', ";
					} else {
						$sql .= "null, ";
					}

					if ($razlog_regresa_id) {
						$sql .= "$razlog_regresa_id, ";
					} else {
						$sql .= "null, ";
					}

					if ($regresno_potrazivanje_napomena) {
						$sql .= "'$regresno_potrazivanje_napomena', ";
					} else {
						$sql .= "null, ";
					}
					// MARIJA 02.03.2015 - KRAJ
					if ($pravniOsnovDao_1 != '0' && $pravniOsnovDao_1 != '') {
						$sql .= "$pravniOsnovDao_1, ";
					} 
					if ($pravniOsnovDao_1 == '0' || $pravniOsnovDao_1 == '')
					{
						$sql .= " null,";
					}
					if ($pravniOsnovDao_2 != '0' && $pravniOsnovDao_2 != '') {
						$sql .= "$pravniOsnovDao_2, ";
					} 
					if ($pravniOsnovDao_2 == '0' || $pravniOsnovDao_2 == '')
					{
					    $sql .= " null,";
					}

					//DODAO VLADA PROSIRENJE UPITA ZA NOVE KOLONE U TABELI PRAVNI - POCETAK
					if($tip_lica) {
						$sql .= "'$tip_lica', ";
					}
					else {
						$sql .= "null, ";
					}

					if($ime_duznika) {
						$sql .= "'$ime_duznika', ";
					}
					else {
						$sql .= "null, ";
					}

					if($prezime_duznika) {
						$sql .= "'$prezime_duznika', ";
					}
					else {
						$sql .= "null, ";
					}

					if($jmbg_pib) {
						$sql .= "'$jmbg_pib', ";
					}
					else {
						$sql .= "null, ";
					}

					if($mesto_reg) {
						$sql .= "'$mesto_reg', ";
					}
					else {
						$sql .= "-1, ";
					}

					if($adresa_reg) {
						$sql .= "'$adresa_reg', ";
					}
					else {
						$sql .= "null, ";
					}

					if($telefon_reg) {
						$sql .= "'$telefon_reg', ";
					}
					else {
						$sql .= "null, ";
					}

					if($opstina_reg) {
						$sql .= "'$opstina_reg', ";
					}
					else {
						$sql .= "-1, ";
					}

					if($koliko_potrazivati) {
						$sql .= "'$koliko_potrazivati', ";
					}
					else {
						$sql .= "null, ";
					}

					if ($drzava_reg_id) {
						$sql .= "'$drzava_reg_id') ";
					} else {
						$sql .= "null) ";
					}
					//DODAO VLADA - KRAJ
					
					$rezultat2 = pg_query($conn, $sql);

					//var_dump($alkotest_krivac);
					//var_dump($sql);
					//var_dump(pg_last_error($conn));

				} else {
					$rezultat2 = true;
				}
			}

			//kf_osnov
			if ($vrstaSt == 'AK' || $vrstaSt == 'AKs' || $vrstaSt == 'IO' || $vrstaSt == 'N') {

				$sql = "select * from kf_osnov where idstete=$idstete";
				$rezultat = pg_query($conn, $sql);
				$niz = pg_fetch_assoc($rezultat);

				$br = $niz['idstete'];

				if ($br) {

					if ($malusProc || $malusIznos || $dugZaPremiju || $kompenzovano || $preostaliDug || $datumKomOsnov || $kompenzovati || $kfPredato || $vinkulirano) {

						$sql = "update kf_osnov set ";
						if ($datumKomOsnov) {
							$sql .= "datum='$datumKomOsnov',";
						} else {
							$sql .= "datum=null,";
						}
						$sql .= " radnik=$radnik, dana=current_date, vreme=current_time, ";
						if ($kompenzovati) {
							$sql .= "kompenzovati='$kompenzovati',";
						} else {
							$sql .= "kompenzovati=null, ";
						}

						if ($kfPredato) {
							$sql .= "kfpredato='$kfPredato',";
						} else {
							$sql .= "kfpredato=null, ";
						}
						if ($vinkulirano) {
							$sql .= "vinkulirano='$vinkulirano' ";
						} else {
							$sql .= "vinkulirano=null ";
						}

						$sql .= " where idstete=$idstete";

						$rezultat3 = pg_query($conn, $sql);
					} else {

						$sql = "delete from kf_osnov where idstete=$idstete";
						$rezultat3 = pg_query($conn, $sql);
					}
				} else {
					if ($malusProc || $malusIznos || $dugZaPremiju || $kompenzovano || $preostaliDug || $datumKomOsnov || $kompenzovati || $kfPredato || $vinkulirano) {

						$sql = "insert into kf_osnov values ( $idstete, ";
						if ($datumKomOsnov) {
							$sql .= " '$datumKomOsnov',";
						} else {
							$sql .= "null,";
						}
						$sql .= " $radnik,current_date, current_time,";
						if ($kompenzovati) {
							$sql .= " '$kompenzovati',";
						} else {
							$sql .= "null ,";
						}

						if ($kfPredato) {
							$sql .= " '$kfPredato',";
						} else {
							$sql .= "null ,";
						}
						if ($vinkulirano) {
							$sql .= " '$vinkulirano')";
						} else {
							$sql .= "null )";
						}

						$rezultat3 = pg_query($conn, $sql);
					} else {
						$rezultat3 = true;
					}
				}
			} else {
				$rezultat3 = true;
			}

			//tabela VOZAC --- POÈETAK START
			$sql = "select * from vozac where idstete=$idstete";
			$rezultat = pg_query($conn, $sql);
			$niz = pg_fetch_assoc($rezultat);

			$br = $niz['idstete'];

			if ($br) {
				if ($prezimeVoz || $imeVoz || $jmbgVoz || $telefonv1 || $telefonv2 || $prezimeVozKriv || $imeVozKriv || $jmbgVozKriv) {
					$sql = "update vozac set ";
					if ($prezimeVoz) {
						$sql .= "prezimevoz='$prezimeVoz',";
					} else {
						$sql .= "prezimevoz=null,";
					}
					if ($imeVoz) {
						$sql .= "imevoz='$imeVoz',";
					} else {
						$sql .= "imevoz=null,";
					}
					if ($jmbgVoz) {
						$sql .= "jmbgvoz='$jmbgVoz',";
					} else {
						$sql .= "jmbgvoz=null,";
					}
					if ($telefonv1) {
						$sql .= "telefonv1='$telefonv1',";
					} else {
						$sql .= "telefonv1=null,";
					}
					if ($telefonv2) {
						$sql .= "telefonv2='$telefonv2',";
					} else {
						$sql .= "telefonv2=null,";
					}
					if ($prezimeVozKriv) {
						$sql .= "prezimevozkriv='$prezimeVozKriv',";
					} else {
						$sql .= "prezimevozkriv=null,";
					}
					if ($imeVozKriv) {
						$sql .= "imevozkriv='$imeVozKriv',";
					} else {
						$sql .= "imevozkriv=null,";
					}
					if ($jmbgVozKriv) {
						$sql .= "jmbgvozkriv='$jmbgVozKriv',";
					} else {
						$sql .= "jmbgvozkriv=null,";
					}
					//MARIJA 7.11.2014.
					if ($vozac_mesto_id) {
						$sql .= "vozac_mesto_id='$vozac_mesto_id', ";
					} else {
						$sql .= "vozac_mesto_id=null,";
					}
					if ($vozac_krivac_mesto_id) {
						$sql .= "vozac_krivac_mesto_id='$vozac_krivac_mesto_id',";
					} else {
						$sql .= "vozac_krivac_mesto_id=null,";
					}
					if ($vozac_mesto_opis) {
						$sql .= "vozac_mesto_opis=upper('$vozac_mesto_opis'), ";
					} else {
						$sql .= "vozac_mesto_opis=null,";
					}
					if ($vozac_krivac_mesto_opis) {
						$sql .= "vozac_krivac_mesto_opis = upper('$vozac_krivac_mesto_opis'), ";
					} else {
						$sql .= "vozac_krivac_mesto_opis = null,";
					}
					//dodato za cuvanje adrese vozaca
					if ($vozac_adresa) {
						$sql .= "vozac_adresa=upper('$vozac_adresa'),";
					} else {
						$sql .= "vozac_adresa=null,";
					}
					if ($vozac_krivac_adresa) {
						$sql .= "vozac_krivac_adresa=upper('$vozac_krivac_adresa'),";
					} else {
						$sql .= "vozac_krivac_adresa=null,";
					}
					//dodato za cuvanje zemlje vozaca
					if ($vozac_zemlja_id) {
						$sql .= "vozac_zemlja_id=$vozac_zemlja_id,";
					} else {
						$sql .= "vozac_zemlja_id=null,";
					}
					if ($vozac_krivac_zemlja_id) {
						$sql .= "vozac_krivac_zemlja_id='$vozac_krivac_zemlja_id',";
					} else {
						$sql .= "vozac_krivac_zemlja_id=null,";
					}
					//dodato za cuvanje telefona vozaca krivca
					if ($vozac_krivac_telefon1) {
						$sql .= "vozac_krivac_telefon1='$vozac_krivac_telefon1',";
					} else {
						$sql .= "vozac_krivac_telefon1=null,";
					}
					if ($vozac_krivac_telefon2) {
						$sql .= "vozac_krivac_telefon2='$vozac_krivac_telefon2',";
					} else {
						$sql .= "vozac_krivac_telefon2=null,";
					}
					//dodato za cuvanje broja licne karte vozaca
					if ($vozac_broj_licne_karte) {
						$sql .= "vozac_broj_licne_karte='$vozac_broj_licne_karte',";
					} else {
						$sql .= "vozac_broj_licne_karte=null,";
					}
					if ($vozac_krivac_broj_licne_karte) {
						$sql .= "vozac_krivac_broj_licne_karte='$vozac_krivac_broj_licne_karte',";
					} else {
						$sql .= "vozac_krivac_broj_licne_karte=null,";
					}
					//ZAVRSENO
					$sql .= " radnik=$radnik, dana=current_date, vreme=current_time where idstete=$idstete";
					$rezultat5 = pg_query($conn, $sql);
				} else {
					$sql = "delete from vozac where idstete=$idstete";
					$rezultat5 = pg_query($conn, $sql);
				}
			} else {
				if ($prezimeVoz || $imeVoz || $jmbgVoz || $telefonv1 || $telefonv2 || $prezimeVozKriv || $imeVozKriv || $jmbgVozKriv) {
					$sql = "insert into vozac values ( $idstete, ";
					if ($prezimeVoz) {
						$sql .= "'$prezimeVoz',";
					} else {
						$sql .= "null,";
					}
					if ($imeVoz) {
						$sql .= "'$imeVoz',";
					} else {
						$sql .= "null,";
					}
					if ($jmbgVoz) {
						$sql .= "'$jmbgVoz',";
					} else {
						$sql .= "null,";
					}
					if ($telefonv1) {
						$sql .= "'$telefonv1',";
					} else {
						$sql .= "null,";
					}
					if ($telefonv2) {
						$sql .= "'$telefonv2',";
					} else {
						$sql .= "null,";
					}
					$sql .= "$radnik, current_date, current_time,";
					if ($prezimeVozKriv) {
						$sql .= "'$prezimeVozKriv',";
					} else {
						$sql .= "null,";
					}
					if ($imeVozKriv) {
						$sql .= "'$imeVozKriv',";
					} else {
						$sql .= "null,";
					}
					if ($jmbgVozKriv) {
						$sql .= "'$jmbgVozKriv',";
					} else {
						$sql .= "null,";
					}
					//MARIJA 7.11.2014 i 19.11.2014 - POÈETAK DODATOG
					if ($vozac_mesto_id) {
						$sql .= "'$vozac_mesto_id', ";
					} else {
						$sql .= "null,";
					}
					if ($vozac_krivac_mesto_id) {
						$sql .= "'$vozac_krivac_mesto_id',";
					} else {
						$sql .= "null,";
					}
					if ($vozac_mesto_opis) {
						$sql .= "upper('$vozac_mesto_opis'), ";
					} else {
						$sql .= "null,";
					}
					if ($vozac_krivac_mesto_opis) {
						$sql .= "upper('$vozac_krivac_mesto_opis'), ";
					} else {
						$sql .= "null,";
					}
					if ($vozac_adresa) {
						$sql .= "upper('$vozac_adresa'),";
					} else {
						$sql .= "null,";
					}
					if ($vozac_krivac_adresa) {
						$sql .= "upper('$vozac_krivac_adresa'),";
					} else {
						$sql .= "null,";
					}
					if ($vozac_zemlja_id) {
						$sql .= "$vozac_zemlja_id,";
					} else {
						$sql .= "null,";
					}
					if ($vozac_krivac_zemlja_id) {
						$sql .= "'$vozac_krivac_zemlja_id',";
					} else {
						$sql .= "null,";
					}
					if ($vozac_krivac_telefon1) {
						$sql .= "'$vozac_krivac_telefon1',";
					} else {
						$sql .= "null,";
					}
					if ($vozac_krivac_telefon2) {
						$sql .= "'$vozac_krivac_telefon2',";
					} else {
						$sql .= "null,";
					}
					if ($vozac_broj_licne_karte) {
						$sql .= "'$vozac_broj_licne_karte',";
					} else {
						$sql .= "null,";
					}
					if ($vozac_krivac_broj_licne_karte) {
						$sql .= "'$vozac_krivac_broj_licne_karte')";
					} else {
						$sql .= "null)";
					}
					// MARIJA 7.11.2014 i 19.11.2014 KRAJ DODATOG
					$rezultat5 = pg_query($conn, $sql);
				} else {
					$rezultat5 = true;
				}
			}
			//tabela VOZAC --- KRAJ END

			$sql = "select * from vozilo where idstete=$idstete";
			$rezultat = pg_query($conn, $sql);
			$niz = pg_fetch_assoc($rezultat);

			$br = $niz['idstete'];

			if ($br) {

				if ($prvaUpotreba  || $vrstaVozila || $zemljaProizv || $marka || $tip || $model || $sifraVoz || $cena || $procAmortizacije ||  $vrednost_vozilo || $brSasije || $brMotora || $snagakw || $ccm || $masa || $vrGoriva || $boja || $karoserija || $brVrata || $brRegMesta || $foto || $opisOst || $cb1 || $cb2 || $cb3 || $cb4 || $cb5 || $cb6 || $cb7 || $cb8 || $cb9 || $cb10 || $cb11 || $cb12) {

					$sql = "update vozilo set ";
					if ($prvaUpotreba) {
						$sql .= "prvaupotreba='$prvaUpotreba',";
					} else {
						$sql .= "prvaupotreba=null,";
					}
					if ($vrstaVozila) {
						$sql .= " vrstavozila='$vrstaVozila',";
					} else {
						$sql .= "vrstavozila=null,";
					}
					if ($zemljaProizv) {
						$sql .= " zemljaproizv='$zemljaProizv',";
					} else {
						$sql .= "zemljaproizv=null,";
					}
					if ($marka) {
						$sql .= " marka='$marka',";
					} else {
						$sql .= "marka=null,";
					}
					if ($tip) {
						$sql .= " tip='$tip',";
					} else {
						$sql .= "tip=null,";
					}
					if ($model) {
						$sql .= " model='$model',";
					} else {
						$sql .= "model=null,";
					}
					if ($sifraVoz) {
						$sql .= " sifravoz='$sifraVoz',";
					} else {
						$sql .= "sifravoz=null,";
					}
					if ($cena) {
						$sql .= " cena=$cena,";
					} else {
						$sql .= "cena=null,";
					}
					if ($procAmortizacije) {
						$sql .= " procamortizacije=$procAmortizacije,";
					} else {
						$sql .= "procamortizacije=null,";
					}
					if ($vrednost_vozilo) {
						$sql .= " vrednost=$vrednost_vozilo,";
					} else {
						$sql .= "vrednost=null,";
					}

					if ($brSasije) {
						$sql .= "brsasije='$brSasije',";
					} else {
						$sql .= "brsasije=null,";
					}
					if ($brMotora) {
						$sql .= " brmotora='$brMotora',";
					} else {
						$sql .= "brmotora=null,";
					}
					if ($snagakw) {
						$sql .= " snagakw=$snagakw,";
					} else {
						$sql .= "snagakw=null,";
					}
					if ($ccm) {
						$sql .= " ccm=$ccm,";
					} else {
						$sql .= "ccm=null,";
					}
					if ($masa) {
						$sql .= " masa=$masa,";
					} else {
						$sql .= "masa=null,";
					}
					if ($vrGoriva) {
						$sql .= " vrgoriva='$vrGoriva',";
					} else {
						$sql .= "vrgoriva=null,";
					}
					if ($boja) {
						$sql .= " boja='$boja',";
					} else {
						$sql .= "boja=null,";
					}
					if ($karoserija) {
						$sql .= " karoserija='$karoserija',";
					} else {
						$sql .= "karoserija=null,";
					}
					if ($brVrata) {
						$sql .= " brvrata=$brVrata,";
					} else {
						$sql .= "brvrata=null,";
					}
					if ($brRegMesta) {
						$sql .= " brregmesta=$brRegMesta,";
					} else {
						$sql .= "brregmesta=null,";
					}
					if ($foto) {
						$sql .= " foto='$foto',";
					} else {
						$sql .= "foto=null,";
					}
					if ($opisOst) {
						$sql .= " opisost='$opisOst',";
					} else {
						$sql .= "opisost=null,";
					}

					if ($cb1) {
						$sql .= " cb1=true,";
					} else {
						$sql .= "cb1=false,";
					}
					if ($cb2) {
						$sql .= " cb2=true,";
					} else {
						$sql .= "cb2=false,";
					}
					if ($cb3) {
						$sql .= " cb3=true,";
					} else {
						$sql .= "cb3=false,";
					}
					if ($cb4) {
						$sql .= " cb4=true,";
					} else {
						$sql .= "cb4=false,";
					}
					if ($cb5) {
						$sql .= " cb5=true,";
					} else {
						$sql .= "cb5=false,";
					}
					if ($cb6) {
						$sql .= " cb6=true,";
					} else {
						$sql .= "cb6=false,";
					}
					if ($cb7) {
						$sql .= " cb7=true,";
					} else {
						$sql .= "cb7=false,";
					}
					if ($cb8) {
						$sql .= " cb8=true,";
					} else {
						$sql .= "cb8=false,";
					}
					if ($cb9) {
						$sql .= " cb9=true,";
					} else {
						$sql .= "cb9=false,";
					}
					if ($cb10) {
						$sql .= " cb10=true,";
					} else {
						$sql .= "cb10=false,";
					}
					if ($cb11) {
						$sql .= " cb11=true,";
					} else {
						$sql .= "cb11=false,";
					}
					if ($cb12) {
						$sql .= " cb12=true,";
					} else {
						$sql .= "cb12=false,";
					}

					$sql .= " radnik=$radnik, dana=current_date, vreme=current_time where idstete=$idstete";

					$rezultat6 = pg_query($conn, $sql);
				} else {

					$sql = "delete from vozilo where idstete=$idstete";
					$rezultat6 = pg_query($conn, $sql);
				}
			} else {

				if ($prvaUpotreba  || $vrstaVozila || $zemljaProizv || $marka || $tip || $model || $sifraVoz || $cena || $procAmortizacije ||  $vrednost_vozilo || $brSasije || $brMotora || $snagakw || $ccm || $masa || $vrGoriva || $boja || $karoserija || $brVrata || $brRegMesta || $foto || $opisOst || $cb1 || $cb2 || $cb3 || $cb4 || $cb5 || $cb6 || $cb7 || $cb8 || $cb9 || $cb10 || $cb11 || $cb12) {

					$sql = "insert into vozilo (idstete, prvaupotreba  ,vrstavozila ,zemljaproizv ,marka ,tip ,model ,sifravoz ,cena ,procamortizacije ,vrednost ,brsasije ,brmotora ,snagakw ,ccm ,masa ,vrgoriva ,boja ,karoserija ,brvrata ,brregmesta ,foto ,opisost ,cb1 ,cb2 ,cb3 ,cb4 ,cb5 ,cb6 ,cb7 ,cb8 ,cb9 ,cb10 ,cb11 ,cb12, radnik, dana, vreme) values ( $idstete, ";
					if ($prvaUpotreba) {
						$sql .= "'$prvaUpotreba',";
					} else {
						$sql .= "null,";
					}
					if ($vrstaVozila) {
						$sql .= " '$vrstaVozila',";
					} else {
						$sql .= "null,";
					}
					if ($zemljaProizv) {
						$sql .= " '$zemljaProizv',";
					} else {
						$sql .= "null,";
					}
					if ($marka) {
						$sql .= " '$marka',";
					} else {
						$sql .= "null,";
					}
					if ($tip) {
						$sql .= " '$tip',";
					} else {
						$sql .= "null,";
					}
					if ($model) {
						$sql .= " '$model',";
					} else {
						$sql .= "null,";
					}
					if ($sifraVoz) {
						$sql .= " '$sifraVoz',";
					} else {
						$sql .= "null,";
					}
					if ($cena) {
						$sql .= " $cena,";
					} else {
						$sql .= "null,";
					}
					if ($procAmortizacije) {
						$sql .= " $procAmortizacije,";
					} else {
						$sql .= "null,";
					}
					if ($vrednost_vozilo) {
						$sql .= " $vrednost_vozilo,";
					} else {
						$sql .= "null,";
					}
					if ($brSasije) {
						$sql .= "'$brSasije',";
					} else {
						$sql .= "null,";
					}
					if ($brMotora) {
						$sql .= " '$brMotora',";
					} else {
						$sql .= "null,";
					}
					if ($snagakw) {
						$sql .= " $snagakw,";
					} else {
						$sql .= "null,";
					}
					if ($ccm) {
						$sql .= " $ccm,";
					} else {
						$sql .= "null,";
					}
					if ($masa) {
						$sql .= " $masa,";
					} else {
						$sql .= "null,";
					}
					if ($vrGoriva) {
						$sql .= " '$vrGoriva',";
					} else {
						$sql .= "null,";
					}
					if ($boja) {
						$sql .= " '$boja',";
					} else {
						$sql .= "null,";
					}
					if ($karoserija) {
						$sql .= " '$karoserija',";
					} else {
						$sql .= "null,";
					}
					if ($brVrata) {
						$sql .= " $brVrata,";
					} else {
						$sql .= "null,";
					}
					if ($brRegMesta) {
						$sql .= " $brRegMesta,";
					} else {
						$sql .= "null,";
					}
					if ($foto) {
						$sql .= " '$foto',";
					} else {
						$sql .= "null,";
					}
					if ($opisOst) {
						$sql .= " '$opisOst',";
					} else {
						$sql .= "null,";
					}
					if ($cb1) {
						$sql .= " $cb1,";
					} else {
						$sql .= "null,";
					}
					if ($cb2) {
						$sql .= " $cb2,";
					} else {
						$sql .= "null,";
					}
					if ($cb3) {
						$sql .= " $cb3,";
					} else {
						$sql .= "null,";
					}
					if ($cb4) {
						$sql .= " $cb4,";
					} else {
						$sql .= "null,";
					}
					if ($cb5) {
						$sql .= " $cb5,";
					} else {
						$sql .= "null,";
					}
					if ($cb6) {
						$sql .= " $cb6,";
					} else {
						$sql .= "null,";
					}
					if ($cb7) {
						$sql .= " $cb7,";
					} else {
						$sql .= "null,";
					}
					if ($cb8) {
						$sql .= " $cb8,";
					} else {
						$sql .= "null,";
					}
					if ($cb9) {
						$sql .= " $cb9,";
					} else {
						$sql .= "null,";
					}
					if ($cb10) {
						$sql .= " $cb10,";
					} else {
						$sql .= "null,";
					}
					if ($cb11) {
						$sql .= " $cb11,";
					} else {
						$sql .= "null,";
					}
					if ($cb12) {
						$sql .= " $cb12,";
					} else {
						$sql .= "null,";
					}
					$sql .= " $radnik, current_date, current_time)";

					$rezultat6 = pg_query($conn, $sql);
				} else {
					$rezultat6 = true;
				}
			}

			// //reaktiviranje --- POÈETAK START
			// $sqlreak="select * from reaktivirane where id_reak=$idstete and vrsta_reak='$vrstaSt'";
			// $rezultatreak=pg_query($conn,$sqlreak);
			// $nizreak=pg_fetch_assoc($rezultatreak);
			// $brreak= $nizreak['id_reak'];
			// if ($brreak)
			// {
			// 	if ($rbrReaktivirana && $reaktivirana && $idreak)
			// 	{
			// 		$sql="update reaktivirane set idstete_prva=$idreak, datum_reakt='$datumEvid', id_reak=$idstete, vrsta_prva='$vrstaSt', radnik=$radnik, dana=current_date, vreme=current_time where id_reak=$idstete and vrsta_reak='$vrstaSt'";
			// 		$rezultat8=pg_query($conn,$sql);
			// 	}
			// 	else
			// 	{
			// 		$sql="delete from reaktivirane where id_reak=$idstete and vrsta_reak='$vrstaSt'";
			// 		$rezultat8=pg_query($conn,$sql);
			// 	}
			// }
			// else
			// {
			// 	if ($rbrReaktivirana && $reaktivirana && $idreak){

			// 		$sql="insert into reaktivirane values ($idreak,'$datumEvid', $idstete, '$vrstaSt', $radnik, current_date, current_time, '$vrstaSt') ";
			// 		$rezultat8=pg_query($conn,$sql);
			// 	}
			// 	else{$rezultat8=true;}
			// }
			// //reaktiviranje --- KRAJ END

			// if ($rezultat1 && $rezultat2 && $rezultat3 && $rezultat4 && $rezultat5 && $rezultat6 && $rezultat8)
			// if ($rezultat1 && $rezultat2 && $rezultat3 && $rezultat5 && $rezultat6 && $rezultat8)

			//Nemanja Jovanovic 

			if ($rezultat1 && $rezultat2 && $rezultat3 && $rezultat5 && $rezultat6 && $rez_update_podnosilac_mail) {

				$sql = "commit;";
				$rezultat = pg_query($conn, $sql);

				$sql = "begin;";
				$rezultat = pg_query($conn, $sql);

				$sql = "select max(rbr) as rbr from rezervacije where idstete=$idstete  ";
				$rezultat = pg_query($conn, $sql);
				$niz = pg_fetch_assoc($rezultat);

				$rbr = $niz['rbr'];
				if (!$rbr) {
					$rbr = 1;
				}

				$sql = "select rbr, rezervisano, datum_od from rezervacije where idstete=$idstete and rbr=$rbr  ";
				$rezultat = pg_query($conn, $sql);
				$niz = pg_fetch_assoc($rezultat);

				$rbr = $niz['rbr'];
				$rez = $niz['rezervisano'];
				$datum_od = $niz['datum_od'];

				if ($rezervisano <> $rez && $rezervisano && $rezervisano != '' && $rezervisano != null) {

					if ($datum_od == date("Y-m-d")) {

						$sql = "update rezervacije set  ";
						if ($rezervisano) {
							$sql .= " rezervisano=$rezervisano,";
						} else {
							$sql .= " rezervisano=0.00, ";
						}
						$sql .= " radnik=$radnik, dana=current_date, vreme=current_time where idstete=$idstete and rbr=$rbr ";

						$rezultat7 = pg_query($conn, $sql);
					} else {

						$rbr = $rbr + 1;

						$sql = "insert into rezervacije (idstete, rbr, datum_od, rezervisano , radnik, dana, vreme) values ( $idstete, $rbr , current_date, ";
						if ($rezervisano) {
							$sql .= " $rezervisano,";
						} else {
							$sql .= " 0.00, ";
						}
						$sql .= " $radnik, current_date, current_time)";

						$rezultat7 = pg_query($conn, $sql);
					}
				} else {
					$rezultat7 = true;
				}
			}

			if ($rezultat7) {
				$sql = "commit;";
				$rezultat = pg_query($conn, $sql);
				echo "<script type=\"text/javascript\">";
				echo "alert(\"Podaci su uspe¹no promenjeni!\")\n";

				echo "zatvori_stranu();";
				//echo "window.close()\n";
				echo "</script>";
			} else {

				$sql = "rollback;";
				$rezultat = pg_query($conn, $sql);
			}

			//&& $idstete >103840 
			// 10840 je poslednji rucno prenet predmet u prigovorima 
			if ($razlog_reaktivacije != -1 && $idstete > 103840  && !$podaci_prigovori && $osnovni_predmet_id_reaktiviranog && $osnovni_predmet_id_reaktiviranog != $idstete && !$sudski_postupak_id) {

				$sql_amso = "BEGIN;";
				$rezultat_amso = pg_query($conn2, $sql_amso);

				$sql_oz_sd = "

	SELECT
	poz.id,
	poz.osnovni_predmet_id,
	poz.novi_broj_predmeta,
	poz.stari_broj_predmeta,
	poz.radnik_evidentirao_predmet AS radnik,
	poz.datum_vreme_evidentiranja_predmeta::date AS dana_poz,
	poz.datum_vreme_evidentiranja_predmeta::time AS vreme_poz,
	CASE WHEN character_length(jmbgpibost) = 13 THEN 2 ELSE 1 END AS tip_lica,
	CASE WHEN character_length(jmbgpibost) = 13 THEN NULL ELSE concat_ws(' ', poz.imenazivost, poz.prezimeost) END AS poslovno_ime,
	CASE WHEN character_length(jmbgpibost) = 13 THEN poz.prezimeost ELSE NULL END AS prezime,
	CASE WHEN character_length(jmbgpibost) = 13 THEN poz.imenazivost ELSE NULL END AS ime,
	poz.osteceni_mesto_id AS mesto_id,
	concat_ws (', ',poz.osteceni_mesto_opis, poz.adresaost) AS ulica,
	NULL AS broj,
	NULL AS ulaz,
	NULL AS stan,
	CASE WHEN character_length(jmbgpibost) = 13 THEN poz.jmbgpibost ELSE NULL END AS jmbg,

	CASE WHEN character_length(jmbgpibost) = 13
	THEN NULL
	ELSE
	(SELECT DISTINCT aaa.matbr FROM tmp.ak_matbr_pib AS aaa WHERE aaa.pib = poz.jmbgpibost)
	END AS mb,

	CASE WHEN character_length(jmbgpibost) = 13 THEN NULL ELSE poz.jmbgpibost END AS pib,

	concat_ws(', ',poz.markaost,poz.tipost,poz.godost,poz.regoznakaost,poz.nazivosigost,poz.brpoliseost,poz.vaznostodost,poz.vaznostdoost,poz.regpodost,poz.modelost,poz.tekracun_ost,poz.brsasost) AS napomena,

	poz.razlog_reaktivacije,
	poz.osteceni_zemlja_id AS zemlja_id,
	poz.osteceni_broj_licne_karte AS broj_licne_karte,
	oz.podnosilac_odstetnog_zahteva_vrsta::text,
	CASE WHEN oz.podnosilac_zahteva_tip_lica = 'F' THEN oz.podnosilac_zahteva_prezime ELSE NULL END AS ovlasceno_lice_prezime,
	CASE WHEN oz.podnosilac_zahteva_tip_lica = 'F' THEN oz.podnosilac_zahteva_ime ELSE oz.podnosilac_zahteva_naziv END AS ovlasceno_lice_ime,

	oz.podnosilac_zahteva_mesto_id AS ovlasceno_lice_mesto_id,
	concat_ws(', ',oz.podnosilac_zahteva_adresa,oz.podnosilac_zahteva_mesto_txt) AS ovlasceno_lice_ulica,
	CASE WHEN oz.podnosilac_zahteva_tip_lica = 'F' THEN oz.podnosilac_zahteva_jmbg ELSE concat_ws(', ',oz.podnosilac_zahteva_pib,oz.podnosilac_zahteva_maticni_broj) END AS ovlasceno_lice_broj,
	concat_ws(', ',oz.podnosilac_zahteva_telefon,oz.podnosilac_zahteva_tekuci_racun) AS ovlasceno_lice_ulaz,
	concat_ws(', ',oz.strano_lice,oz.podnosilac_zahteva_zemlja_id) AS ovlasceno_lice_stan,
	oz.vrsta_osiguranja,
	concat_ws(', ', oj.opis,oj.naziv,organizaciona_jedinica_id) AS lokacija_prijema,

	CASE WHEN oz.vrsta_osiguranja = 'DPZ' THEN 209 ELSE
	CASE WHEN oz.vrsta_osiguranja = 'AO' THEN 257 ELSE
	CASE WHEN oz.vrsta_osiguranja = 'AK' THEN 216 ELSE
	CASE WHEN oz.vrsta_osiguranja = 'N' THEN 182 ELSE
	CASE WHEN oz.vrsta_osiguranja = 'IO' THEN
	CASE WHEN substring(sifra FROM 1 FOR 2) = '08' THEN 236
	ELSE 241
	END END END END END END AS prigovori_vrsta_osiguranja_nbs_id,

	sd.broj_polise,
	sd.vrsta_obrasca,
	poz.tip_predmeta,


	poz.datum_otvaranja_predmeta AS datum_prijema,
	oz.datum_podnosenja_zahteva,
	poz.datum_otvaranja_predmeta,
	oz.organizaciona_jedinica_id,
	oj.naziv,
	oj.opis,
	'PISANO, U PROSTORIJAMA DRU©TVA'
	AS nacin_podnosenja_prigovora,
	CASE
	WHEN storno = 1 OR p.osnovan = 'O'
	THEN 'Neosnovan'
	ELSE 'Osnovan' END
	AS status_prigovora,

	poz.nalog AS datum_resavanja_prigovora,
	poz.nalog AS datum_dostavljanja_odgovora,
	poz.nalog AS datum_izvrsenja_obaveze_drustva,
	NULL::text AS opis_prigovora

	FROM
	predmet_odstetnog_zahteva AS poz
	INNER JOIN odstetni_zahtev AS oz ON (oz.id=poz.odstetni_zahtev_id)
	INNER JOIN stetni_dogadjaj AS sd ON (sd.id=oz.stetni_dogadjaj_id)
	INNER JOIN sifarnici.organizacione_jedinice AS oj ON (oj.id = oz.organizaciona_jedinica_id)
	INNER JOIN pravni AS p ON (poz.id = p.idstete)
	WHERE
	poz.id=$idstete";
				$result_oz_sd = pg_query($conn, $sql_oz_sd);
				$podaci_oz_sd = pg_fetch_assoc($result_oz_sd);

				$tip_lica_prigovori_registar = $podaci_oz_sd['tip_lica'];
				$poslovno_ime_prigovori_registar = ($podaci_oz_sd['poslovno_ime'] != '') ? "'" . $podaci_oz_sd['poslovno_ime'] . "'" : 'NULL';
				$ime_prigovori_registar = "'" . $podaci_oz_sd['ime'] . "'";
				$prezime_prigovori_registar = "'" . $podaci_oz_sd['prezime'] . "'";
				$mesto_id_prigovori_registar = $podaci_oz_sd['mesto_id'];
				$ulica_prigovori_registar = "'" . $podaci_oz_sd['ulica'] . "'";
				$broj_prigovori_registar = "'" . $podaci_oz_sd['broj'] . "'";
				$ulaz_prigovori_registar = "'" . $podaci_oz_sd['ulaz'] . "'";
				$stan_prigovori_registar = "'" . $podaci_oz_sd['stan'] . "'";
				$jmbg_prigovori_registar = ($podaci_oz_sd['jmbg'] == '') ? 'NULL' : "'" . $podaci_oz_sd['jmbg'] . "'";
				$maticni_prigovori_registar = ($podaci_oz_sd['mb'] == '') ? 'NULL' : "'" . $podaci_oz_sd['mb'] . "'";
				$pib_prigovori_registar = ($podaci_oz_sd['pib'] == '') ? 'NULL' : "'" . $podaci_oz_sd['pib'] . "'";

				$podnosilac_tip_lica = $podaci_oz_sd['podnosilac_zahteva_tip_lica'];

				$ovlasceno_lice_ime_prigovori_registar = "'" . $podaci_oz_sd['ovlasceno_lice_ime'] . "'";
				$ovlasceno_lice_prezime_prigovori_registar = "'" . $podaci_oz_sd['ovlasceno_lice_prezime'] . "'";
				$ovlasceno_lice_mesto_id_prigovori_registar = $podaci_oz_sd['ovlasceno_lice_mesto_id'];
				if (!$ovlasceno_lice_mesto_id_prigovori_registar) {
					$ovlasceno_lice_mesto_id_prigovori_registar = 'null';
				}
				$ovlasceno_lice_ulica_prigovori_registar = "'" . $podaci_oz_sd['ovlasceno_lice_ulica'] . "'";
				$ovlasceno_lice_broj_prigovori_registar = "'" . $podaci_oz_sd['ovlasceno_lice_broj'] . "'";
				$ovlasceno_lice_ulaz_prigovori_registar = "'" . $podaci_oz_sd['ovlasceno_lice_ulaz'] . "'";

				$broj_polise_prigovori_registar = "'" . $podaci_oz_sd['broj_polise'] . "'";
				$vrsta_obrasca_naziv_prigovori_registar = $podaci_oz_sd['vrsta_obrasca'];

				$sql_vrsta_obrasca = "SELECT id FROM sifarnici.vrsta_obrasca_vrste_osiguranja  WHERE vrsta_obrasca ='$vrsta_obrasca_naziv_prigovori_registar'";
				$result_vrsta_obrasca = pg_query($conn2, $sql_vrsta_obrasca);
				$niz_vrsta_obrasca = pg_fetch_assoc($result_vrsta_obrasca);
				$vrsta_obrasca_id_prigovori_registar = $niz_vrsta_obrasca['id'];


				$pokriveni_rizik_prigovori_registar = 'NULL';
				$prigovori_vrsta_osiguranja_nbs_id = $podaci_oz_sd['prigovori_vrsta_osiguranja_nbs_id'];
				$sadrzaj_prigovora_prigovori_registar = 'NULL';
				$napomena_prigovori_registar = "'" . $podaci_oz_sd['napomena'] . "'";

				$prigovori_nacin_podnosenja_id_prigovori_registar = 1;
				$datum_prijema_prigovori_registar = "'" . $podaci_oz_sd['datum_prijema'] . "'";
				$lokacija_prijema_prigovori_registar = "'" . $podaci_oz_sd['lokacija_prijema'] . "'";
				$prigovori_osnov_nbs_id_prigovori_registar = 11;
				switch ($razlog_reaktivacije) {
					case '821 - Re¹avanje zahteva':
						$prigovori_osnov_nbs_id_prigovori_registar = 8;
						break;
					case '822 - Uslovi osiguranja':
						$prigovori_osnov_nbs_id_prigovori_registar = 9;
						break;
					case '823 - Izvr¹enje obaveza iz ugovora':
						$prigovori_osnov_nbs_id_prigovori_registar = 10;
						break;
					case '824 - Visina i isplata ponuðene naknade':
						$prigovori_osnov_nbs_id_prigovori_registar = 11;
						break;
					default:
						$prigovori_osnov_nbs_id_prigovori_registar = 11;
						break;
				}
				$lice_objekat_prigovora_prigovori_registar = 'NULL';
				//Radnik je Goran Popovic
				$radnik_prigovori_registar = 3068;

				$upitUpisRegistar = "
	INSERT INTO prigovori.prigovori_registar (
		

	redni_broj, tip_lica, poslovno_ime, ime, prezime,
	mesto_id , ulica, broj, ulaz, stan, jmbg, maticni,  pib,


	ovlasceno_lice_ime,  ovlasceno_lice_prezime,
	ovlasceno_lice_mesto_id, ovlasceno_lice_ulica,
	ovlasceno_lice_broj, ovlasceno_lice_ulaz,
	ovlasceno_lice_stan,


	broj_polise, vrsta_obrasca_id, pokriveni_rizik,
	prigovori_vrsta_osiguranja_nbs_id, sadrzaj_prigovora, napomena,


	prigovori_nacin_podnosenja_id, datum_prijema,
	lokacija_prijema, prigovori_osnov_nbs_id,
	lice_objekat_prigovora, radnik , poz_id
	)
	VALUES (

	(SELECT (COALESCE(max(redni_broj), 0) + 1)
	FROM prigovori.prigovori_registar
	WHERE extract(YEAR FROM dana) = extract(YEAR FROM now())),
	$tip_lica_prigovori_registar, $poslovno_ime_prigovori_registar, $ime_prigovori_registar, $prezime_prigovori_registar,
	$mesto_id_prigovori_registar , $ulica_prigovori_registar, NULL, NULL, NULL, $jmbg_prigovori_registar, $maticni_prigovori_registar, $pib_prigovori_registar,

		
	$ovlasceno_lice_ime_prigovori_registar, $ovlasceno_lice_prezime_prigovori_registar,
	$ovlasceno_lice_mesto_id_prigovori_registar, $ovlasceno_lice_ulica_prigovori_registar,
	$ovlasceno_lice_broj_prigovori_registar, $ovlasceno_lice_ulaz_prigovori_registar,
	NULL,


	$broj_polise_prigovori_registar, $vrsta_obrasca_id_prigovori_registar, $pokriveni_rizik_prigovori_registar,
	$prigovori_vrsta_osiguranja_nbs_id, $sadrzaj_prigovora_prigovori_registar, $napomena_prigovori_registar,


	$prigovori_nacin_podnosenja_id_prigovori_registar, $datum_prijema_prigovori_registar,
	$lokacija_prijema_prigovori_registar, $prigovori_osnov_nbs_id_prigovori_registar,
	$lice_objekat_prigovora_prigovori_registar, $radnik_prigovori_registar , $idstete
	)
	RETURNING id;
	";

				$rezultat_insert_prigovori = pg_query($conn2, $upitUpisRegistar);
				$row = pg_fetch_row($rezultat_insert_prigovori);
				$prigovor_id = $row['0'];

				$upit_prigovor_istorija = " INSERT INTO prigovori.prigovori_istorija_statusa
	(prigovor_id,datum_promene,prigovor_status_id)
	VALUES
	($prigovor_id,current_timestamp,NULL)";
				$rezultat_istorija_statusa = pg_query($conn2, $upit_prigovor_istorija);

				if ($rezultat_insert_prigovori && $rezultat_istorija_statusa) {
					$sql_amso = "COMMIT;";
					$rezultat_amso = pg_query($conn2, $sql_amso);
				} else {
					$sql_amso = "ROLLBACK;";
					$rezultat_amso = pg_query($conn2, $sql_amso);


					exit;
				}
			}
		} //zatvara if($da)

	} //zatvara if($izmeni)

	//Branka 07.09.2015 - provera da li predmet ima sudski_postupak_id
	// $sql_sudski="select sudski_postupak_id from predmet_odstetnog_zahteva where id=$idstete";
	// $rezultat_sudski=pg_query($conn,$sql_sudski);
	// $niz_sud=pg_fetch_assoc($rezultat_sudski);
	// $sudski_postupak_id=$niz_sud['sudski_postupak_id'];
	// echo "<input type=\"hidden\" name=\"sudski_postupak_id\" id=\"sudski_postupak_id\"   value=\"$sudski_postupak_id\">\n";
	$sql_sudski_postupak = "select brsp from sudski_postupak where idsp=$sudski_postupak_id";
	$rezultat_sudski_postupak = pg_query($conn, $sql_sudski_postupak);
	$niz_sudski_postupak = pg_fetch_assoc($rezultat_sudski_postupak);
	$brsp = $niz_sudski_postupak['brsp'];
	//Branka 09.09.2015 - Upit kojim se izvlaèe sve rezervacije na dan
	$sql_rezervacije = "SELECT (rez_mat_lica + rez_nemat_lica + rez_renta_lica + rez_mat_stvari) as zbir,datum_od from rez_sp_razbijeno_sa_periodom WHERE idsp=$sudski_postupak_id order by datum_od DESC limit 1";
	$upit_rezervacije = pg_query($conn, $sql_rezervacije);
	$rezervacije_niz = pg_fetch_all($upit_rezervacije);
	//Div se prikazuje samo u sluèaju da je u pitanju predmet koji se povezuje sa sudskim
	if (!$prethodne && !$dokumentacija && !$zapisnik && !$dugme_kreiraj_dopis && !$dugme_pregledaj_dopise && !$dugme_resenje_odbijen && !$dugme_odluka && !$dugme_odluka_likvidacija && !$dugme_dopisi && !$odbijenica_likvidacija && !$lekarski_nalaz && !$obracun_visine_stete && !$obracun_visine_stete_n_dpz && !$obracun_visine_stete_0205_dpz && !resenje_IO_0903  && !$galerija && !$vozilo_dugme && !$nalozi && !$da) {
		if ($sudski_postupak_id && $rezervacije_niz) {
			echo "<div>";
			echo "<p><b>RAZBIJANJE&nbsp;&nbsp;&nbsp;</p><input type='radio' name='razbijanje' value='razbijanje_rezervacije' id='razbijanje_rezervacije'  onclick='prikazi_div(this.value)' >REZERVACIJA&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <input type='radio' name='razbijanje' value='razbijanje_isplata' id='razbijanje_isplata' onclick='prikazi_div(this.value)'>ISPLATA";
			echo "</div><br><br>";
		}
		$broj_rezervacija = "";
		$broj_rezervacija_sudski = "";
		if ($sudski_postupak_id && $rezervacije_niz) {
			echo "<div id='osvezi' >";
			echo "<div style='background-color:CC6666; width: 60%; height: 140px; float: left;' id='div_rezervacije' >";
			echo "<h2>Razbijanje rezervacija<h2><br>";
			echo " Rezervacije $brsp na dan &nbsp;<select id='rezervacije_sp' name='rezervacije_sp' onchange='izaberi_rezervaciju(this.value); prikazi_pregled($sudski_postupak_id);' >";
			echo "<option value='-1'>Izaberite</option>";
			$dan;
			for ($j = 0; $j < count($rezervacije_niz); $j++) {
				$dan = $rezervacije_niz[$j]['datum_od'];
				$zbir = $rezervacije_niz[$j]['zbir'];

				$sql_rez_na_dan = "select datum_od from rezervacije where idstete=$idstete and datum_od='$dan'";

				$rezultat_rez_na_dan = pg_query($conn, $sql_rez_na_dan);
				$niz_rez_na_dan = pg_fetch_all($rezultat_rez_na_dan);
				$broj_na_dan = count($niz_rez_na_dan);
				if (!$niz_rez_na_dan) {
					echo "<option value='$dan$zbir' $disable>Dan : $dan,  iznos : $zbir   </option>";
				}
			}
			echo "</select>";
			echo "<br><br>";

			echo "Dan <input type='text' readonly id='datum_rez' name='datum_rez' style='background-color:#CCCCCC'  > &nbsp;&nbsp;&nbsp; Iznos ";
			//Branka 07.09.2015 - Dugme koje se poziva ako se ¾eli prepisati ceo iznos - u sluèaju da se ¾eli povezati samo 1 predemet
			echo "<input type='button' value='Prepisi ceo iznos' onclick='prepisi_rezervaciju()'>";
			//Branka 09.09.2015. Upit kojim se provera broj unetih rezervacija za odreðeni predmet
			$dan = "'" . $dan . "'";
			$sql_broj_rez = "select count(*) as broj from rezervacije where idstete=$idstete and datum_od<=$dan";

			$rezultat_broj_rez = pg_query($conn, $sql_broj_rez);
			$niz_rez_broj = pg_fetch_assoc($rezultat_broj_rez);
			$broj_rezervacija = $niz_rez_broj['broj'];
			$broj_rezervacija_sudski = count($rezervacije_niz);
			echo "<input type='hidden' value='$broj_rezervacija' id='broj_rezervacija_na_dan' name='broj_rezervacija_na_dan' />";

			echo "<input type='hidden' value='$broj_rezervacija_sudski' id='broj_rezervacija_sudski' name='broj_rezervacija_sudski' />";
			echo "<input type='hidden' id='ostalo' name='ostalo' />";
			echo "<input type='hidden' id='ostalo_isplate' name='ostalo_isplate' />";
			echo "<input type='text'  id='iznos_rez' name='iznos_rez' onkeypress='return samoBrojeviITacka(event);'><input type='button' value='Snimi rezervaciju' onclick='snimi_rezervaciju($broj_rezervacija,$broj_rezervacija_sudski)'>";
			echo "</div>";
			echo "<div id='pregled_rezervacija' style=' width: 20%; float: left; display: inline-block;'  >";
			echo "</div>";
			echo "<div id='pregled_unetih_rezervacija' style=' width: 20%;float: left; display: inline-block;'  >";
			echo "</div>";
			echo "</div>";
		}

		//Branka 18.09.2015. DODATO ZA IZJEDNAÈAVANJE ISPLATA
		//Upit kojim se izvlaèe sve isplate na dan
		$sql_isplate = "SELECT DISTINCT(datum_naloga) AS datum from isplate_sp WHERE idsp=$sudski_postupak_id order by datum_naloga";
		$upit_isplate = pg_query($conn, $sql_isplate);
		$isplate_datumi_niz = pg_fetch_all($upit_isplate);

		echo "<div id='osvezi1'  >";
		if ($sudski_postupak_id && $isplate_datumi_niz) {

			//$display=($broj_rezervacija==$broj_rezervacija_sudski)?"":'display:none';
			echo "<div style='background-color:#FFDEAD; width: 60%; height:130px;  float: left;margin-top:15px;  id='div_isplate' >";
			echo "<div  id='iznosi_isplate'  style='height:30px'>";
			//echo "<div>";
			echo "<h2>Razbijanje isplata<h2><br>";
			echo " Isplate $brsp na dan &nbsp;<select id='isplate_sp' name='isplate_sp' onchange='izaberi_datum(this.value,$sudski_postupak_id); '>";
			echo "<option value='-1'>Izaberite</option>";
			$broj_datuma = 0;
			for ($j = 0; $j < count($isplate_datumi_niz); $j++) {
				$dan = $isplate_datumi_niz[$j]['datum'];

				$sql_isplate_uneto = "SELECT isp.idisp AS idisp ,isp.svrha AS svrha,isp.iznos AS iznos from isplate_sp AS isp WHERE isp.idsp=$sudski_postupak_id AND isp.datum_naloga='$dan' AND NOT EXISTS(SELECT * FROM isplate AS i WHERE i.svrha =isp.svrha AND i.datum_naloga=isp.datum_naloga AND i.konacna=isp.konacna AND i.rbr=isp.rbr AND idstete=$idstete) ";
				$upit_isplate_uneto = pg_query($conn, $sql_isplate_uneto);
				$isplate_datumi_niz_sve = pg_fetch_all($upit_isplate_uneto);

				if ($isplate_datumi_niz_sve) {
					echo "<option value='$dan'>$dan</option>";
					$broj_datuma = $broj_datuma + 1;
				}
			}
			echo "</select></div>";
			echo "<input type='hidden' value='$broj_datuma' id='broj_datuma' name='broj_datuma' />";
			echo "<input type='hidden' value='$broj_isplata_po_danu' id='broj_isplata_po_danu' name='broj_isplata_po_danu' />";
			echo "<div  id='iznosi_isplate_svrha' style='height:20px; margin-top:-42px;margin-left:300px;'  >";
			echo "</div><br>";
			echo "<div  id='iznosi_isplate' style='margin-top:50px;height:20px;'>";
			echo "Dan <input type='text' readonly  id='datum_isplate' name='datum_isplate' style='width:100px;background-color:#CCCCCC'> &nbsp;&nbsp;Svrha ";
			echo "<input type='text' id='svrha_isplate' readonly name='svrha_isplate' style='width:150px;background-color:#CCCCCC'>&nbsp;&nbsp;Iznos ";
			echo "<input type='button' value='Prepisi ceo iznos' onclick='prepisi_isplatu()'><input type='text' id='iznos_isplate' name='iznos_isplate' style='width:100px;'>";
			echo "<input type='button' value='Snimi isplatu' onclick='snimi_isplatu()'><br><br><br>";
			echo "</div>";
			echo "</div>";
			echo "<div id='pregled_isplata' style=' width: 20%; float: left;margin-top:15px; display: inline-block;'  >";
			echo "</div>";
			echo "<div id='pregled_unetih_isplata' style=' width: 20%; float: left;margin-top:15px;  display: inline-block;'  >";
			echo "</div>";
		}
		echo "</div>";
	} //KRAJ Div se prikazuje samo u sluèaju da je u pitanju predmet koji se povezuje sa sudskim predmetom
	echo "<br><br>";
	echo "<input type='hidden' value='$prigovor_indikator' id='prigovor_indikator' name='prigovor_indikator' />";

	echo "</form>\n";

	if (isset($_POST['osnovni_zapisnik']) || isset($_POST['dopunski_zapisnik'])) {
		echo "<script language=\"javascript\">\n";
		echo "document.getElementById(\"galerija_dugme\").click();\n";
		echo "</script>\n";
	}

	$pregled_naloga = $_GET['pregled_naloga'];
	if (isset($pregled_naloga)) {
		?>
		<script>
			$(document).ready(function() {
				document.getElementById("nalozi_dugme").click();
			});
		</script>
	<?php
	}

	?>
	<script>

	//DODAO VLADA
	function samoBrojevi(field, event)
	{
		var kod = event.which;

		if (kod==0 || kod==8 || (kod>47 && kod<58))
			return true;
		else if (kod==13)
			return handleEnter (field, event);
		else
			return false;
	}

	//DODAO VLADA - SACEKAJ DA SE UCITA STRANICA
	$(document).ready(function() {

		//DOHVATANJE POLJA SA BROJEM REGRESA
		var regresni_broj = $('#oznakaRegPotr').val();

		//AKO VEC POSTOJI OTVOREN REGRES,DISABLE-UJ ODGOVARAJUCA POLJA
		if (regresni_broj != '') {

			$("input[name='tip_lica']").prop('disabled', true);

			$('#ime_duznika').addClass('disabled');
			$('#prezime_duznika').addClass('disabled');
			$('#osiguranjeRegPotr').addClass('disabled');
			$('#jmbg_pib').addClass('disabled');
			$('#adresa_reg').addClass('disabled');
			$('#telefon_reg').addClass('disabled');
			$('#koliko_potrazivati').addClass('disabled');

			$('.disabled').each(function() {

				$(this).prop('readonly', true);
			});

			$('.disable_selekti').each(function() {

				$(this).prop('disabled', true);
				$(this).addClass('disabled');
			});
		}

		//AKO JE TIP DUZNIKA OSIGURAVAJUCE DRUSTVO
		if($('#regres_od').val() == 'Osiguravajuæe dru¹tvo') {
			
			//SETOVANJE TIPA LICA NA PRAVNO
			$('#tip_lica1').prop('checked', true); 
			$('#tip_lica').prop('disabled', true);
		}

		//AKO JE POLJE REGRESNI DUZNIK PRAZNO I SELEKTOVANO OSIGURANJE
		if($('#osiguranjeRegPotr').val() == '' && $('#osiguravajuce_drustvo_id').val() != '-1') {

			//UZIMANJE NAZIVA OSIGURANJA IZ SELECTA
			var ime_osiguranja = $('#osiguravajuce_drustvo_id option:selected').text();

			//POPUNJAVANJE POLJA REGRESNI DUZNIK SA TEKSTOM IZ SELECTA
			$('#osiguranjeRegPotr').val(ime_osiguranja);
		}
		
		//PROVERA TIPA LICA NA UCITAVANJE STRANICE
		var vrsta_lica = $("input[name='tip_lica']:checked").val();

		//AKO JE TIP LICA FIZICKO,PROMENI MAXLENGTH ATRIBUT I DODAJ BLUR FUNKCIJU ZA PROVERU JMBG-A
		if (vrsta_lica == 'fizicko') {  

			$("#jmbg_pib").attr('maxlength', 13);

			$("#jmbg_pib").off('blur');

			$("#jmbg_pib").blur(function(){

				validate_jmbg(this.value);

				if (!validate_jmbg(this.value)) {

					alert('JMBG je u neispravnom formatu!');
					$(this).val('');
				}
			});
		}

		//AKO JE TIP LICA PRAVNI,PROMENI MAXLENGTH ATRIBUT I DODAJ BLUR FUNKCIJU ZA PROVERU PIB-A
		if(vrsta_lica == 'pravno') {

			$("#jmbg_pib").attr('maxlength', 9);
			
			//RESETOVANJE I IZMENA ATRIBUTA POLJA
			$('#prezime_duznika').val('');
			$('.izmeni_tekst').html('Naziv regresnog du¾nika');
			$('#prezime_duznika').prop('readonly', true);
			$('#prezime_duznika').addClass('disabled');

			$("#jmbg_pib").off('blur');

			$("#jmbg_pib").blur(function(){

				proveri_pib(this);
			});
		}

		//FUNKCIJA NA PROMENU RADIO BUTTONA - DODAO VLADA
		$("input[name='tip_lica']").change(function() {

			//PROVERA TIPA LICA
			var vrsta = $("input[name='tip_lica']:checked").val();

			//AKO JE PRAVNO LICE,ISPRAZNI I DISABLE-UJ POLJE PREZIME I PROMENI TEKST LABELA
			if(vrsta == 'pravno'){

				$("#jmbg_pib").attr('maxlength', 9);
				
				$('#ime_duznika').val('');
				$('#prezime_duznika').val('');
				$('.izmeni_tekst').html('Naziv regresnog du¾nika');
				$('#prezime_duznika').prop('readonly', true);
				$('#prezime_duznika').addClass('disabled');
				$('#jmbg_pib').val('');

				$("#jmbg_pib").off('blur');

				//DODAVANJE BLUR FUNKCIJE ZA PROVERU PIBA
				$("#jmbg_pib").blur(function(){

					proveri_pib(this);
				});
			}
			//U SUPROTNOM,ENABLE-UJ POLJE PREZIME I PROMENI TEKST LABELA
			else {

				$("#jmbg_pib").attr('maxlength', 13);

				$('.izmeni_tekst').html('Ime regresnog du¾nika');
				$('#prezime_duznika').prop('readonly', false);
				$('#prezime_duznika').removeClass('disabled');
				$('#ime_duznika').val('');
				$('#prezime_duznika').val('');
				$('#jmbg_pib').val('');

				$("#jmbg_pib").off('blur');

				//DODAVANJE BLUR FUNKCIJE ZA PROVERU JMBG-A
				$("#jmbg_pib").blur(function(){

					validate_jmbg(this.value);

					if (!validate_jmbg(this.value)) {

						alert('JMBG je u neispravnom formatu!');
						$(this).val('');
					}
				});
			}
		});
	})

	//FUNKCIJA ZA PROVERU DA LI JE DATUM U ISPRAVNOM FORMATU - DODAO VLADA
	function pravilan_datum(dan, mesec, godina)
	{
		if (mesec==1 || mesec==3 || mesec==5 || mesec==7 || mesec==8 || mesec==10 || mesec==12)
		{
			if ((dan>=0) && (dan<=31))
			return true;
			else
			return false;
		}

		if (mesec==4 || mesec==6 || mesec==9 || mesec==11)
		{
			if ((dan>=0) && (dan<=30))
			return true;
			else
			return false;
		}

		if (mesec==2)
		{
			if (prestupna(godina))
			{
			if ((dan>=0) && (dan<=29))
				return true;
			else
				return false;
			}
			else
			{
			if ((dan>=0) && (dan<=28))
				return true;
			else
				return false;
			}
		}
	}

	//FUNKCIJA ZA PROVERU DA LI JE JMBG VALIDAN - DODAO VLADA
	function validate_jmbg(jmbg) 
	{
		var o 		= jmbg;
		var re 	= /(^\d{13}$)|(^$)/;
		var test 	= re.exec(o);

		if (!test) 
		{
			return false;
		}

		if(o == "")
			return false;
		
		if(o.substr(7, 2) == "66") 
		{
			return true;
		}

		var dan = parseInt(jmbg.substring(0, 2));
		var mesec;
		var godina;

		if(parseInt(jmbg.substring(2, 3)) == 0)
			mesec = parseInt(jmbg.substring(3, 4));
		else
			mesec = parseInt(jmbg.substring(2, 4));
		
		if (parseInt(jmbg.substring(4, 7)) > 899)
			godina = parseInt(jmbg.substring(4, 7)) + 1000;
		else 
		{
			if (parseInt(jmbg.substring(5, 7)) < 10)
				godina = parseInt(jmbg.substring(4, 7)) + 2000;
			else
				godina = parseInt(jmbg.substring(4, 7)) + 2002;
		}

		
		if (!pravilan_datum(dan, mesec, godina))
			return false;

		var trenutan_datum = new Date();
		if (godina > trenutan_datum.getFullYear())
			return false;

		var val = o.substring(0, 12);
		var ctr = o.substring(12, 13);
		var ctr1;

		ctr1 = 7 * (val.substring(0, 1)) + 6 * (val.substring(1, 2)) + 5
				* (val.substring(2, 3)) + 4 * (val.substring(3, 4)) + 3
				* (val.substring(4, 5)) + 2 * (val.substring(5, 6));
		ctr1 += 7 * (val.substring(6, 7)) + 6 * (val.substring(7, 8)) + 5
				* (val.substring(8, 9)) + 4 * (val.substring(9, 10)) + 3
				* (val.substring(10, 11)) + 2 * (val.substring(11, 12));
		ctr1 = 11 - (ctr1 % 11);

		if (ctr1 > 9)
			ctr1 = 0;
		if (ctr != ctr1) 
		{
			return false;
		} 
		else 
		{
			return true;
		}
	}


	//FUNKCIJA ZA PROVERU DA LI JE PIB U DOBROM FORMATU - DODAO VLADA
	function proveri_pib(b) {
		try {
			var a = b.value;
		}
		catch(err) {
			alert('Ne postoji ovaj objekat.');
			return false;
		}
		var cifra = 0;
		var i = 0;
		if (a.match(/^\d+$/)) {
		// PIB 
			if (a.length == 9) {
				for (i = 0; i < 8; i++) {
					cifra += parseInt(a.charAt(i));
					cifra -= (cifra > 10 ? 10 : 0);
					cifra *= 2;
					cifra -= (cifra > 11 ? 11 : 0);
				}
				cifra = (11 - cifra) > 9 ? 0 : 11 - cifra;
				if (cifra != parseInt(a.charAt(8))) {
					alert("Gre¹ka u PIB-u.\nNije dobar kontrolni broj.");
					b.value = '';
					b.style.border = '2px solid #007FFF';
					b.focus();
					return false;
				}
			}
			else
			{
				alert('Gre¹ka u PIB-u.\nMora imati taèno 9 cifara!');
				b.value = '';
				b.focus();
				return false;
			}
		}
		else {
			alert ("©ifra partnera sadr¾i iskljuèivo cifre.");
			b.value = '';
				b.focus();
			return false;
		}
		//b.style.border = '1px solid #C4C4C4';
		return true;
	}
	</script>
	<script type="text/javascript">
		$('#sumnjaNaPrevaru').on('change', function() {
			if (!$(this).is(':checked')) {
				$(this).val(false);
				$('.prevara').attr('checked', false);
				$('.prevara').val(false);
				//$('.prevara').attr('readonly', true);
				$('.prevara').attr('disabled', true);
			} else {
				$(this).val(true);
				//$('.prevara').attr('readonly', false);
				$('.prevara').attr('disabled', false);
			}

		});

		$('#nastaviti_saradnju').change(function() {
			if ($(this).is(':checked'))
				$(this).val(true);
			else
				$(this).val(false);
		});

		//Branka 07.09.2015 - Funkcija kojom se promenom izabrane rezervacije popunjava datum rezervacije za mirni 
		function izaberi_rezervaciju(vrednost) {
			if (vrednost != -1) {
				document.getElementById('ostalo').value = "";
				document.getElementById('datum_rez').value = vrednost.substring(0, 10);

			} else {
				document.getElementById('datum_rez').value = "";



			}
			document.getElementById('iznos_rez').value = "";
		}
		//Branka 08.09.2015. - Ukoliko se klikne na dugme prepisi celu rezervaciju, prepisuje se cela rezervacija (u sluèaju da se ¾eli povezati samo jedan predmet) 
		function prepisi_rezervaciju() {
			var iznos = document.getElementById('ostalo').value;
			document.getElementById('iznos_rez').value = iznos;
		}



		//  Marko Markovic otvaranje prozorceta za izmenu
		function izmeni_datum_iznos_rez() {
			document.getElementById("izmeni_datum_rez").style.display = "block";
		}

		//Marko Markovic - zatvaranje prozora

		function zatvori_prozor() {
			document.getElementById("izmeni_datum_rez").style.display = "none";
		}




		// Marko Markovic izmena datuma i iznosa rezervacija

		function izmeni_dat_iznos_rez(idstete) {
			var datum_prijave = document.getElementById('datumPrijave').value;
			var datum_rezervacije = document.getElementById('datum_rez').value;
			var iznos_rezervacije = document.getElementById('iznos_rez').value;
			var idstete = $("[name='idstete']").val();
			var url = "izmeni_datum_iznos_rez";

			$.ajax({
				type: 'POST',
				url: "funkcije.php",
				datatype: 'json',
				data: {
					funkcija: url,
					datum_prijave: datum_prijave,
					datum_rezervacije: datum_rezervacije,
					iznos_rezervacije: iznos_rezervacije,
					idstete: idstete
				},
				datatype: 'json',
				success: function(ret) {

					var data = JSON.parse(ret);
					if (data.flag == true) {
						alert(data.poruka);
						window.location.reload(true);
					} else {
						alert(data.poruka);
					}
				}
			});

		}

		// Marko Markovic kraj



		//Branka 06.10.2015. Ukoliko se klikne na dugme prepisi celu rezervaciju, prepisuje se cela isplata
		function prepisi_isplatu() {
			// 	var vrednost=document.getElementById('isplate_svrha_sp').value;
			// 	var text= ($("#isplate_svrha_sp option[value='"+vrednost+"']").text());
			// 	var iznos=(text).split('-')[1];
			var ostalo = document.getElementById('ostalo_isplate').value;
			document.getElementById('iznos_isplate').value = ostalo;
		}
		//Branka 08.09.2015. - Funkcija za snimanje rezervacija
		function snimi_rezervaciju(broj_rezervacija_na_dan, broj_rezervacija_sudski) {
			//Podaci koji su potrebni za cuvanje rezervacija 
			var datum_rezervacije = document.getElementById('datum_rez').value;
			var iznos_rezervacije = document.getElementById('iznos_rez').value;
			var idstete = document.pregled.idstete.value;
			var ostalo = document.getElementById('ostalo').value;
			if (document.getElementById('rezervacije_sp').value == -1) {

				alert("Izaberite datum rezervacije!");
				exit;
			}

			if (iznos_rezervacije == '') {
				alert("Unesite iznos rezervacije!");
				exit;

			}
			var ukupan_iznos_na_dan = (document.getElementById('rezervacije_sp').value).substring(10);
			var idsp = document.getElementById('sudski_postupak_id').value;
			var url = "snimi_rezervacije";
			$.ajax({
				type: 'POST',
				url: "funkcije.php",
				datatype: 'json',
				data: {
					funkcija: url,
					datum_rezervacije: datum_rezervacije,
					iznos_rezervacije: iznos_rezervacije,
					idstete: idstete,
					ukupan_iznos_na_dan: ukupan_iznos_na_dan,
					ostalo: ostalo,
					idsp: idsp
				},
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					//alert(data.poruka);
					if (data.flag == true) {
						// Ukoliko se unete sve rezervacije, prikazati poruku o tome, jedinica dodata jer se tek klikom na dugme moze dostici jednak broj, a pre toga je za jedan manji
						// Promenjive su Broj unetih rezeravija za reaktivaciju/aktivaciju predmeta i Broj rezervacija koliko ih ima sudski, odnosno koliko je potrebno uneti za mirni
						if (broj_rezervacija_na_dan + 1 == broj_rezervacija_sudski) {
							//alert("Uneli ste sve rezervacije za ovaj predmet !!!");
							document.getElementById('osvezi').style.display = 'none';
							document.getElementById('razbijanje_rezervacije').checked = false;
							//$("#osvezi1").load(location.href + " #osvezi1");
							//document.getElementById('pregled_rezervacija').style.visibility='hidden';
						}
						//Potrebno osveziti stranu, kako bi iz liste nestala rezervacija koja je trenutno uneta  
						//location.reload();
						$("#osvezi").load(location.href + " #osvezi");
						osvezi_listu(data.poruka);
						$("#rezervacija_id").load(location.href + " #rezervacija_id");
						$("#rezervacija_lista_id").load(location.href + " #rezervacija_lista_id");
					} else {
						alert(data.poruka);
					}
				}
			});
		}
		// Branka 09.09.2015. - Dodato da se ne prikazuje div za unos rezervacija ukoliko su unete rezervacije za sve dane 
		// window.onload = function() 
		// {
		// 	//Broj unetih rezeravija za reaktivaciju/aktivaciju predmeta
		// 	var broj_rezervacija_na_dan=document.getElementById('broj_rezervacija_na_dan').value;
		// 	//Broj rezervacija koliko ih ima sudski, odnosno koliko je potrebno uneti za mirni
		// 	var broj_rezervacija_sudski=document.getElementById('broj_rezervacija_sudski').value;
		// 	//Kada ta dva broja postanu jednaka, znaci da su unete rezervacije za sve dane, pa nema potrebe da se div za unos rezervacija prikazuje
		// 	if(broj_rezervacija_na_dan==broj_rezervacija_sudski)
		// 	{
		// 		var iznos_rezervacije=document.getElementById('div_rezervacije').style.visibility='hidden';
		// 	} 

		// }; 
		//Branka 17.09.2015. Funkcija za prikaz rezervacija svih predmeta povezanih za isti sudski predmet
		function prikazi_pregled(sudski_postupak_id) {
			var datum_rezervacije = document.getElementById('datum_rez').value;
			var iznos = (document.getElementById('rezervacije_sp').value).substring(10);
			var idstete = document.pregled.idstete.value;
			var url = "prikazi_rezervacije";
			$.ajax({
				type: 'POST',
				url: "funkcije.php",
				datatype: 'json',
				data: {
					funkcija: url,
					datum_rezervacije: datum_rezervacije,
					sudski_postupak_id: sudski_postupak_id,
					iznos: iznos,
					idstete: idstete

				},
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);

					//alert(data.rezervisano_do_sada);
					//alert(data.ostalo);

					if (document.getElementById('rezervacije_sp').value != -1) {
						document.getElementById('ostalo').value = data.ostalo;
						document.getElementById('pregled_rezervacija').innerHTML = data.tabela;
						//document.getElementById('pregled_rezervacija').style.background="#e5b0b0";
						document.getElementById('pregled_unetih_rezervacija').innerHTML = data.tabela_unete;
						//document.getElementById('pregled_unetih_rezervacija').style.background="#e5b0b0";


					} else {
						document.getElementById('ostalo').value = "";
						document.getElementById('pregled_rezervacija').innerHTML = "";
						document.getElementById('pregled_rezervacija').style.background = "";
					}

				}
			});

		}
		//Branka 18.09.2015. 
		function izaberi_datum(datum, sudski_postupak_id) {

			var idstete = document.pregled.idstete.value;

			var url = "prikazi_svrhu_i_iznos";
			$.ajax({
				type: 'POST',
				url: "funkcije.php",
				datatype: 'json',
				data: {
					funkcija: url,
					datum: datum,
					sudski_postupak_id: sudski_postupak_id,
					idstete: idstete

				},
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					//alert(data.poruka);
					if (datum != -1) {

						var broj_isplata_po_danu = data.broj_isplata_po_danu;
						document.getElementById('broj_isplata_po_danu').value = broj_isplata_po_danu;
						document.getElementById('iznosi_isplate_svrha').innerHTML = data.poruka;
						document.getElementById('iznosi_isplate_svrha').display = 'inline';
						$("select[name='isplate_svrha_sp'] option:gt(1)").attr("disabled", "disabled");
					} else {

						document.getElementById('iznosi_isplate_svrha').innerHTML = "";
						document.getElementById('iznosi_isplate_svrha').display = 'none';
						document.getElementById('datum_isplate').value = "";
						document.getElementById('svrha_isplate').value = "";
						document.getElementById('ostalo_isplate').value = data.ostalo;
						document.getElementById('pregled_isplata').innerHTML = "";



					}
				}
			});

		}
		//Branka 06.10.2015. Funkcija za prepis dana i svrhe isplate
		function popuni_dan_i_svrhu(vrednost) {

			var text = ($("#isplate_svrha_sp option[value='" + vrednost + "']").text());

			if (vrednost != -1) {
				document.getElementById('datum_isplate').value = document.getElementById('isplate_sp').value;
				document.getElementById('svrha_isplate').value = text.split('->')[0];
			} else {
				document.getElementById('datum_isplate').value = "";
				document.getElementById('svrha_isplate').value = "";
			}
			document.getElementById('iznos_isplate').value = "";

		}
		//Branka 06.10.2015. - funkcija za snimanje isplate
		function snimi_isplatu() {

			if (document.getElementById('isplate_sp').value == -1) {
				alert("Izaberite datum isplate!");
				exit;
			} else {
				var idstete = document.pregled.idstete.value;
				var id_isplate = document.getElementById('isplate_svrha_sp').value;
				var iznos = document.getElementById('iznos_isplate').value;
				var ostalo = document.getElementById('ostalo_isplate').value;
				var url = "snimi_isplate";
				if (document.getElementById('isplate_svrha_sp').value == -1) {
					alert("Izaberite svrhu i iznos isplate!");
					exit;
				}
				if (iznos == '') {
					alert("Unesite iznos isplate!");
					exit;
				}
			}
			$.ajax({
				type: 'POST',
				url: "funkcije.php",
				datatype: 'json',
				data: {
					funkcija: url,
					idstete: idstete,
					id_isplate: id_isplate,
					iznos: iznos,
					ostalo: ostalo
				},
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);

					if (data.flag) {
						$("#osvezi1").load(location.href + " #osvezi1");
						osvezi_listu_isplata(data.poruka);
						$("#isplaceno_id").load(location.href + " #isplaceno_id");
						$("#nalog_id").load(location.href + " #nalog_id");
						$("#nalog_id_hidden").load(location.href + " #nalog_id_hidden");
						$("#faza_id").load(location.href + " #faza_id");
						$("#reseno_fazno").load(location.href + " #reseno_fazno");
					} else {
						alert(data.poruka);
					}
				}
			});
		}
		//Branka 06.10.2015. Funkcija za pregled isplata
		function prikazi_pregled_isplata(sudski_postupak_id) {
			//document.getElementById('ostalo_isplate').value="";
			var id_isplate_sudski = document.getElementById('isplate_svrha_sp').value;
			var vrednost = document.getElementById('isplate_svrha_sp').value;
			var text = ($("#isplate_svrha_sp option[value='" + vrednost + "']").text());
			var iznos = (text).split('->')[1];
			var idstete = document.pregled.idstete.value;
			var url = "prikazi_isplate";
			$.ajax({
				type: 'POST',
				url: "funkcije.php",
				datatype: 'json',
				data: {
					funkcija: url,
					sudski_postupak_id: sudski_postupak_id,
					id_isplate_sudski: id_isplate_sudski,
					ukupan_iznos: iznos,
					idstete: idstete


				},
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					if (document.getElementById('isplate_svrha_sp').value != -1) {
						//alert(data.ostalo);
						document.getElementById('ostalo_isplate').value = data.ostalo;
						document.getElementById('pregled_isplata').innerHTML = data.tabela;
						//document.getElementById('pregled_isplata').style.background="#fcf3df";
						document.getElementById('pregled_unetih_isplata').innerHTML = data.tabela_unete;
						//document.getElementById('pregled_unetih_isplata').style.background="#fcf3df";
					} else {
						document.getElementById('ostalo_isplate').value = "";
						document.getElementById('pregled_isplata').innerHTML = "";
						document.getElementById('pregled_isplata').style.background = "";
						document.getElementById('pregled_unetih_isplata').innerHTML = "";
						document.getElementById('pregled_unetih_isplata').style.background = "";
					}

				}
			});
		}
		$('.prevara').change(function() {
			$('.prevara').each(function() {
				$(this).val(false);
			});
			if ($(this).is(':checked'))
				$(this).val(true);
		});

		$('#ocekivana_suma').on('input', function() {
			this.value = this.value.match(/^\d+\.?\d{0,2}/);
		});

	
		function snimiPrevaru() {
			if (!$('#sumnjaNaPrevaru').is(':checked') && $('#datumPrev').val() == '') {
			    alert('Moraju biti popunjeni podaci vezani za sumnju');
				return;
			}
			if ($('#sumnjaNaPrevaru').is(':checked') && $('#datumPrev').val() == '') {
		        alert('Datum sumnje mora biti popunjen ako unosite sumnju');
				return;
			}
			if (!$('#sumnjaNaPrevaru').is(':checked') && $('#datumPrev').val() != '') {
				alert('Kuæica za sumnju mora biti popunjena');
				return;
			}
			if (Date.parse($('#datumPrev').val()) > Date.parse($('#datumPrevare').val())) {
				alert('Datum sumnje mora biti manji ili jednak datumu prevare');
				return;
			}
            
			var ind = 0;
			var index = 0;
			var prevara_osumnjiceni = 0;

            $('.prevara').each(function(){
              if ($(this).is(':checked'))   {prevara_osumnjiceni = 1;return;}
			}); 

			$('.osumnjiceni').each(function(){
				if ($(this).is(':checked'))   {prevara_osumnjiceni = 2;return;}
			});

			if(prevara_osumnjiceni == 1) {alert('Mora postajati osunmjièeni za ishod prevare');return;} 

			$('.prevara').each(function() {
				if ($(this).is(':checked')) {
					var value = $(this).attr('id');
					index = value.substr(value.indexOf('_') + 1, 1);
					ind = 1;
				}

			});
			var osumnjiceni = [];
			
			if (ind == 0 && $('#datumPrevare').val() != '') {
				alert('Datum dokazane sumnje mora imati popunjenu opciju za dokazanu sumnju');
				return;
			}
			if (ind == 1 && $('#datumPrevare').val() == '') {
				$('#datumPrevare').val('');
				alert('Datum dokazane sumnje mora biti popunjen ako ste izabrali opciju za dokazanu sumnju');
				return;
			}
			if($('#sumnjaNaPrevaru').is(':checked')) document.getElementById('sumnjaNaPrevaru').value=true;
			
			var idstete = document.pregled.idstete.value;
			var sumnja = $('#sumnjaNaPrevaru').val();
			var datumPrev = $('#datumPrev').val();
			var ocekivana_suma = $('#ocekivana_suma').val();
			var datum_prevare = $('#datumPrevare').val();
			var nastaviti_saradnju = $('#nastaviti_saradnju').val();
			var napomena = $('#Napomena').val();
			$('.osumnjiceni').each(function() {
				if ($(this).is(':checked'))
					osumnjiceni.push($(this).val());
			});
                      

			var url = "snimi_prevaru";
			$.ajax({
				type: 'POST',
				url: "funkcije.php",
				async: false,
				datatype: 'json',
				data: {
					funkcija: url,
					idstete: idstete,
					sumnja: sumnja,
					datumPrev: datumPrev,
					ocekivana_suma: ocekivana_suma,
					datum_prevare: datum_prevare,
					nastaviti_saradnju: nastaviti_saradnju,
					napomena: napomena,
					index: index,
					osumnjiceni: osumnjiceni
				},
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					if (data.podaci['sumnja'] == 't') {
						document.getElementById("sumnjaNaPrevaru").checked = true;
						document.getElementById("sumnjaNaPrevaru").value = true;
						document.getElementById("sumnjaNaPrevaru").disabled = true;
					}
					if (typeof data.podaci['datum_sumnje'] !== 'undefined')
						if (data.podaci['datum_sumnje'] != 'null') {
							document.getElementById("datumPrev").value = data.podaci['datum_sumnje'];
							document.getElementById("datumPrev").disabled = true;
						}
					else
						document.getElementById("datumPrev").value = '';
						document.getElementById("broj_prevare").value = data.podaci['id'];
					if (data.flag == true) alert('Unos podataka je uspeo!!!');
					else alert(data.poruka);
				}
			});
		}

		///


		$(document).ready(function() {

			$("select[name='rezervacije_sp'] option:gt(1)").attr("disabled", "disabled");
			$("select[name='isplate_sp'] option:gt(1)").attr("disabled", "disabled");

		});

		function osvezi_listu(poruka) {
			alert(poruka);

			$("select[name='rezervacije_sp'] option:gt(1)").attr("disabled", "disabled");

		}

		function osvezi_listu_isplata(poruka) {
			alert(poruka);

			$("select[name='isplate_sp'] option:gt(1)").attr("disabled", "disabled");
			var broj_datuma = document.getElementById('broj_datuma').value;
			var broj_isplata_po_danu = document.getElementById('broj_isplata_po_danu').value;
			if (broj_datuma == 0 && broj_isplata_po_danu == 0) {
				alert("Sve isplate su unete!");
				document.getElementById('osvezi1').style.display = 'none';
				document.getElementById('razbijanje_isplata').checked = false;
			}


		}
		//Branka 14.10.2015. Zakomentarisano !!!
		// $( document ).ready(function() {	
		// var broj_rezervacija_na_dan=document.getElementById('broj_rezervacija_na_dan').value;
		// var broj_rezervacija_sudski=document.getElementById('broj_rezervacija_sudski').value; 
		// if(broj_rezervacija_na_dan==broj_rezervacija_sudski)
		// {
		// 	document.getElementById('osvezi').style.display='none';

		// } 

		// var broj_datuma=document.getElementById('broj_datuma').value;

		// if(broj_datuma==0)
		// {
		// document.getElementById('osvezi1').style.display='none';
		// }	
		// });	

		//Branka 13.10.2015 Funkcija za  prikaz ili sakrivanje diva
		function prikazi_div(vrednost) {

			if (vrednost == 'razbijanje_rezervacije') {
				//$("#osvezi").load(location.href + " #osvezi");
				document.getElementById('osvezi').style.display = 'inline';
				document.getElementById('osvezi1').style.display = 'none';
				$("select[name='rezervacije_sp'] option:gt(1)").attr("disabled", "disabled");
				var broj_rezervacija_sudski = document.getElementById('broj_rezervacija_sudski').value;
				var broj_rezervacija_na_dan = document.getElementById('broj_rezervacija_na_dan').value;


				if (broj_rezervacija_na_dan == broj_rezervacija_sudski) {
					document.getElementById('osvezi').style.display = 'none';
					document.getElementById('osvezi1').style.display = 'none';
					alert("Rezervacija je uneta!");

				}
			} else if (vrednost == 'razbijanje_isplata') {
				//$("#osvezi1").load(location.href + " #osvezi1");
				document.getElementById('osvezi1').style.display = 'inline';
				document.getElementById('osvezi').style.display = 'none';
				$("select[name='isplate_sp'] option:gt(1)").attr("disabled", "disabled");
				var broj_datuma = document.getElementById('broj_datuma').value;
				var broj_isplata_po_danu = document.getElementById('broj_isplata_po_danu').value;
				if (broj_datuma == 0 && broj_isplata_po_danu == 0) {
					document.getElementById('osvezi').style.display = 'none';
					document.getElementById('osvezi1').style.display = 'none';
					alert("Sve isplate su unete!");
				}

			}

		}

		$(document).ready(function() {

			document.getElementById('osvezi').style.display = 'none';
			document.getElementById('osvezi1').style.display = 'none';

		});


		// setInterval(function() {
		// 	$("select[name='rezervacije_sp'] option:gt(1)").attr("disabled", "disabled");
		// }, 2000);

		/*Eric Nikola, 2016*/
		//PROMENA JMBG/PIB-A O©TEÆENOG
		function promeniLIB(e) {
			if (e.checked) {
				$("#izmeni").hide();
				$("#jmbgPibOst").removeAttr('readonly');
				$("#jmbgPibOst").removeClass("disabled");
				$("#sacuvaj_novi_lib").show();
			} else {

				$("#jmbgPibOst").addClass('disabled');
				$("#jmbgPibOst").attr('readonly', true);
				document.getElementById('sacuvaj_novi_lib').style.display = 'none';
				$("#sacuvaj_novi_lib").hide();
				alert('JMBG/PIB NEÆE BITI PROMENJEN!');
				$("#jmbgPibOst").val("<?php echo $jmbgPibOst; ?>");
				$("#izmeni").show();
			}

		}


		function provera_postojecih_predmeta() {
			var jmbgpibost = document.getElementById('jmbgPibOst').value;
			var id_prijave = document.getElementById('odstetni_zahtev_id').value;
			var tip_predmeta = document.getElementById('tip_predmeta').value;

			var stetni_dogadjaj_id = document.getElementById('stetni_dogadjaj_id').value;

			if (!jmbgpibost || !tip_predmeta) {
				alert("Morate izabrati tip od¹tetnog zahteva i uneti jmbg o¹teæenog!");
				exit;
			}

			if (tip_predmeta == -1 || jmbgpibost == "") {
				alert("Morate izabrati tip od¹tetnog zahteva i uneti jmbg o¹teæenog!");
				exit;
			}

			var stetni_dogadjaj_id = document.getElementById('stetni_dogadjaj_id').value;
			var url = 'provera_postojecih_predmeta_visestruki_prolaz';

			$.ajax({
				type: 'POST',
				url: "../common/funkcije.php?funkcija=" + url,
				datatype: 'json',
				data: {
					tip_predmeta: tip_predmeta,
					jmbgpibost: jmbgpibost,
					stetni_dogadjaj_id: stetni_dogadjaj_id
				},
				success: function(ret) {

					var data = JSON.parse(ret);

					if (data.novi_da_ne == true) {
						// 						 	if(!data.predmet_id)
						// 						 	{

						updateLIB(jmbgpibost);
						// 							}

						// 						 	else
						// 						 		alert("Promena nije dozvoljena!\nPredmeti sa ovim brojem postoje!");

					}
					//Pronadjeno 1 ili vise rezervisanih predmeta za uneti jmbg i tip odstetnog zahteva
					else {
						var odgovor = data.odgovor;
						var predmet_id = data.predmet_id;
						alert("Promena nije dozvoljena!\n" + odgovor);
					}
				}
			});

		}

		function updateLIB(jmbgpibost) {
			var predmet_id = document.pregled.idstete.value;
			//alert(document.pregled.idstete.value);
			var url = 'updateLIB';
			$.ajax({
				type: 'POST',
				url: "../common/funkcije.php?funkcija=" + url,
				datatype: 'json',
				data: {
					id: predmet_id,
					lib: jmbgpibost,

				},
				success: function(ret) {

					if (ret == 1)

					{
						alert('USPE©NO IZMENJENO!');
						$("#jmbgPibOst").addClass('disabled');
						$("#jmbgPibOst").attr('readonly', true);
						$("#sacuvaj_novi_lib").hide();
						$("#promeni_lib").hide();
						$("#izmeni").show();
					} else
						alert(ret);

				}
			});

		}

		function VALID_JMBG(JMBG) {
			var jmbgtest = JMBG.value;
			if (jmbgtest.length == 9)
				return;

			var url = 'valid_jmbg';
			$.ajax({
				type: 'POST',
				url: "../common/funkcije.php?funkcija=" + url,
				datatype: 'json',
				data: {
					jmbg: jmbgtest,

				},
				success: function(ret) {

					if (ret) {
						alert(ret);
						JMBG.value = "";
						$(JMBG).css({
							"position": "relative"
						});
						for (var x = 1; x <= 3; x++) {
							$(JMBG).animate({
								left: -25
							}, 10).animate({
								left: 0
							}, 50).animate({
								left: 25
							}, 10).animate({
								left: 0
							}, 50);
						}
					}
				}
			});

		}


		/*
		 * @author Marko Stankoviæ 24.07.2018
		 * @return Vidljivost dugmeta
		 */
		function pravni_osnov_odustao(e) {

			//AKO JE CEKIRANO ODUSTAO
			if (e.checked == true) {

				//PRIKAZI DUGME RESENJE ODUSTAO
				$('#resenje_odustao').show();

				//RESETOVANJE RADIO BUTTON GRUPE OSNOVAN
				$('input[name="osnovan"]').prop('checked', false);

				//RESETOVANJE I SAKRIVANJE POLJA ZA OPCIJU OSNOVAN DELIMICNO
				$("[name='delimicnoProc']").val('');
				$('#dodatno').val('');
				$('#dodatno').hide();
				$('#labela_razlozi_umanjenja').hide();

				//SAKRIVANJE DUGMETA KREIRAJ ODBIJENICU
				$('#dugme_resenje_odbijen').hide();
			}
			//AKO JE ODCEKIRANO ODUSTAO, SAKRIJ DUGME RESENJE ODUSTAO
			else {

				$('#resenje_odustao').hide();
			}
		}

		/*
		 * @author Marko Stankoviæ 24.07.2018
		 * @return Vidljivost dugmeta
		 */
		function prikazi_formu_za_kreiranje_resenja_odustao(e) {

			//31.07.2018. dodavnje uslova i funkcije preko ajaksa za proveru da li su odabrani isti parametri ili nisu.
			var uslov = e;


			var pravni_odustao = $('#odustao_pravni_osnov').is(':checked') ? true : false;
			var odustao = $('#odustao').is(':checked') ? true : false;
			var odbijen = $('#odbijen').is(':checked') ? true : false;
			var idstete = $("[name='idstete']").val();

			$.ajax({
				type: 'POST',
				url: 'funkcije.php?funkcija=provera_odustao',
				data: {
					pravni_odustao: pravni_odustao,
					odustao: odustao,
					odbijen: odbijen,
					idstete: idstete,
					uslov: uslov
				},
				success: function(ret) {

					var data = JSON.parse(ret);
					if (data.flag == false) {
						alert(data.poruka);
					} else {
						window.open('resenje_odustao.php?id_stete=' + idstete + '&tabela_naziv=resenje_odustao', '_self');
					}
				}
			});



		}

		/*
		 * Prosledjuju se podaci za pregled 
		 */


		function prikazi_formu_resenje_odustao_u_likvidaciji(e) {

			var uslov = e;


			var pravni_odustao = $('#odustao_pravni_osnov').is(':checked') ? true : false;
			var odustao = $('#odustao').is(':checked') ? true : false;
			var odbijen = $('#odbijen').is(':checked') ? true : false;
			var idstete = $("[name='idstete']").val();

			$.ajax({
				type: 'POST',
				url: 'funkcije.php?funkcija=provera_odustao',
				data: {
					pravni_odustao: pravni_odustao,
					odustao: odustao,
					odbijen: odbijen,
					idstete: idstete,
					uslov: uslov
				},
				success: function(ret) {

					var data = JSON.parse(ret);
					if (data.flag == false) {
						alert(data.poruka);
					} else {
						window.open('resenje_odustao.php?id_stete=' + idstete + '&tabela_naziv=resenje_odustao_likvidacija', '_self');
					}
				}
			});

		}

		// Bogdan Golubovic 29.03.2018
		// Doradio: Lazar Milosavljeviæ 2018-10-16
		function prikaz_dugme_dat_kon() {
			if (document.getElementById('izmeni_dat_kon').checked == true) {
				//if($('#pravniOsnovDatumKompletiranjaDokumentacije').val()!="" && $('#datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete').val()!="")
				if ($('#pravniOsnovDatumKompletiranjaDokumentacije').val() != "" || $('#datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete').val() != "") {
					dataPickerDatumKompletiranja();
					$("#datumKompl").prop('disabled', false);
					$("#datumKompl").prop('readonly', true);
					document.getElementById('datumKompl').removeAttribute("onclick");
					$("#datumKompl").removeClass("disabled");
					$("#dat_kon").append("<input type='button' id='izmena_dat_kon_dugme'  name='izmena_dat_kon_dugme' onclick='izmenaDatumaKompletiranjaPOZ()' value='Izmeni'/>");
				} else {
					$("#izmeni_dat_kon").prop("checked", false);
					alert("Nije moguæe izmeniti datum kompletiranja, jer nisu uneti datum davanja pravnog osnova i datum kompletiranja dokumentacije za utvrðenu visinu ¹tete!");

				}
			} else if (document.getElementById('izmeni_dat_kon').checked == false) {
				$("#datumKompl").prop('disabled', true);
				$("#datumKompl").addClass("disabled");
				$('#izmena_dat_kon_dugme').remove();
			}
		}

		function dataPickerDatumKompletiranja() {
			$('#datumKompl').datepicker({
				minDate: '<?php echo $minDatumKompletiranjaIzmena; ?>',
				maxDate: '<?php echo $maxDatumKompletiranjaIzmena; ?>',
				dateFormat: 'yy-mm-dd'
			});

		}
		/*Dodao Bogdan Golubovic 12.04.2018
		 * Doradio: Lazar Milosavljeviæ 2018-10-16
		 * Ajax funkcija za izmenu datuma kompletiranja
		 */
		function izmenaDatumaKompletiranjaPOZ() {
			var predmet_id = document.pregled.idstete.value;
			var datumKompletiranjaIzmena = $('#datumKompl').val();
			var url = 'izmenaDatumaKompletiranjaPOZ';

			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=" + url,
				datatype: 'json',
				data: {
					id: predmet_id,
					datum: datumKompletiranjaIzmena,
				},
				cache: false,
				success: function(p) {
					var data = JSON.parse(p);
					if (typeof(data.poruka) != 'undefined' && typeof(data.podatak) != 'undefined') {
						$('#izmena_dat_kon_dugme').remove();
						document.pregled.datumKompl[1].value = data.podatak;
						$("#datumKompl").prop('disabled', true);
						$("#datumKompl").addClass("disabled");
						$("#izmeni_dat_kon").prop("checked", false);
						alert(data.poruka);
					} else if (typeof(data.greska) != 'undefined') {
						alert(data.greska);
					}
				}
			});
		}



		// Marko Markovic 2020-07-30 potpis kontrola
		function kontorlisano(resenje_id) {
			// alert('kontrola');
			var idstete = $("#id_stete").val();
			var kontrolisano = "kontrolisano";
			// $('#kontrola').attr('disabled', true);

			var url = "html_resenje_kontrolisano";
			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=" + url,
				data: {
					kontrolisano: kontrolisano,
					idstete: idstete,
					resenje_id: resenje_id
				},
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					if (data.flag == false) {
						alert(data.poruka);
						// $("#kontrola").attr('disabled', false);
					} else {
						alert(data.poruka);
					}
				}
			});
		}
		// -------- Marko Markovic karj -----

		// Marko Markovic 2020-07-30 potpis odobrio
		function odobreno(resenje_id) {
			// alert('odobrio');
			var idstete = $("#id_stete").val();
			var odobreno = "odobreno";
			// $('#odobrenje').attr('disabled', true);

			var url = "html_resenje_odobreno";
			$.ajax({
				type: 'POST',
				url: "funkcije.php?funkcija=" + url,
				data: {
					odobreno: odobreno,
					idstete: idstete,
					resenje_id: resenje_id
				},
				datatype: 'json',
				success: function(ret) {
					var data = JSON.parse(ret);
					if (data.flag == false) {
						alert(data.poruka);
						// $("#odobrenje").attr('disabled', false);
					} else {
						alert(data.poruka);
					}
				}
			});
		}
		// -------- Marko Markovic karj -----


	// Mare 2021-04-23
	function stampaj_generisana_dok()
	{
		var poz_id = document.pregled.idstete.value;


		var uslov_totalna_steta;
		if ($('#modal_totalna_steta').is(':checked')) 
		uslov_totalna_steta = "DA";
		else uslov_totalna_steta = "NE";

		var uslov_tehnoloski_list;
		if ($('#modal_tehnoloski_list').is(':checked')) 
		uslov_tehnoloski_list = "DA";
		else uslov_tehnoloski_list = "NE";

		var uslov_garantno_pismo;
		if ($('#modal_garantno_pismo').is(':checked')) 
		uslov_garantno_pismo = "DA";
		else uslov_garantno_pismo = "NE";

		var uslov_sporazum;
		if ($('#modal_sporazum').is(':checked')) 
		uslov_sporazum = "DA";
		else uslov_sporazum = "NE";

		var uslov_saglasnost;
		if ($('#modal_saglasnost').is(':checked')) 
		uslov_saglasnost = "DA";
		else uslov_saglasnost = "NE";

		var uslov_auto_dani;
		if ($('#modal_auto_dani').is(':checked')) 
		uslov_auto_dani = "DA";
		else uslov_auto_dani = "NE";

		var uslov_obracun_visine_stete;
		if ($('#modal_obracun_visine_stete_stvari').is(':checked')) 
		uslov_obracun_visine_stete = "DA";
		else uslov_obracun_visine_stete = "NE";

		var uslov_instrukcije;
		if ($('#modal_instrukcije').is(':checked')) 
		uslov_instrukcije = "DA";
		else uslov_instrukcije = "NE";

		var uslov_resenja;
		if ($('#modal_resenja').is(':checked')) 
		uslov_resenja = "DA";
		else uslov_resenja = "NE";


		// Mare 2021-05-20 dodato za nematerijalne stete
		var uslov_ao_lica;
		if ($('#modal_ao_lica').is(':checked')) 
		uslov_ao_lica = "DA";
		else uslov_ao_lica = "NE";

		var uslov_0205_dpz;
		if ($('#modal_0205_dpz').is(':checked')) 
		uslov_0205_dpz = "DA";
		else uslov_0205_dpz = "NE";

		var uslov_n_dpz;
		if ($('#modal_n_dpz').is(':checked')) 
		uslov_n_dpz = "DA";
		else uslov_n_dpz = "NE";
		


		var naziv_pdf = "dokumenti_likvidacija.pdf";
		
		$.ajax({
			type:'POST',
			url: "generisana_dokumentacija_funkcije.php?funkcija=stampaj_dokumenta_likvidacija",
			data: { poz_id: poz_id,
					uslov_resenja: uslov_resenja,
					uslov_instrukcije: uslov_instrukcije,
					uslov_obracun_visine_stete: uslov_obracun_visine_stete,
					uslov_auto_dani: uslov_auto_dani,
					uslov_saglasnost: uslov_saglasnost,
					uslov_sporazum: uslov_sporazum,
					uslov_garantno_pismo: uslov_garantno_pismo,
					uslov_tehnoloski_list: uslov_tehnoloski_list,
					uslov_totalna_steta: uslov_totalna_steta,
					uslov_ao_lica: uslov_ao_lica, 
					uslov_0205_dpz: uslov_0205_dpz,
					uslov_n_dpz: uslov_n_dpz
				},
			datatype: 'json',
					success: function(ret)
					{
						var data = JSON.parse(ret);
						// window.open("PDF_fajlovi/"+naziv_pdf, "_blank");
						var flag = data.flag;
						window.open(data.href,"_blank");
					}	
	});
	}

	</script>
	<?php require_once 'mail_forma.php'; ?>
	<?php require_once 'sluzbena_beleska.php'; ?>
	<?php require_once 'dodatna_napomena_forma.php'; ?>
</body>

</html>