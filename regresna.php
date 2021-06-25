<?php

session_start();
$root =$_SERVER ["DOCUMENT_ROOT"];
require_once "$root/common/no_cache.php";
require_once "$root/privilegije/privilegije.php";
require_once "$root/common/zabrane.php";
$sifra_u_nizu = array('008004004','008004004','001002003');
$sifra_provera= implode("','",$sifra_u_nizu);
zabrana_istekla_sesija($sifra_provera, $root);
require "../../common/no_cache.php";
session_start();
if ($_SESSION['radnik']) {
$radnik = $_SESSION['radnik'];
}
else {
session_destroy();
header("Location: ../../common/login.php");
exit;
}


//DODAO VLADA 
if($_GET['idstete']) {

	$idstete = $_GET['idstete'];
}

//echo 'Id stete je: '.$idstete;

//DODAO VLADA 
if($_GET['idregres']) {

	$idregres = $_GET['idregres'];
}


//echo 'Id regres je: '.$idregres;

$status = $_REQUEST['status'];
?>
<html>
<head>
<title>Regresna potraživanja - ažuriranje</title>
<meta name="naslov" content="Regresna potraživanja - ažuriranje">
<meta http-equiv="Content-Type" content="text/html; charset=iso8859-2">

<script type="text/javascript" src="../../js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="../../js/jquery-ui.js"></script>
<script type="text/javascript" language="javascript" src="../../js/jquery.ui.datepicker-sr-SR.js"></script>
<script type="text/javascript" src="../../js/jquery.maskedinput.js"></script>
<script type="text/javascript" src="../../js/jquery-imask.js"></script>

<link href="../../stete/zapisnik_dodatno/jquery-ui.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../../menistil.css">
<script type="text/javascript">
/***********************************************
* Disable "Enter" key in Form script- By Nurul Fadilah(nurul@REMOVETHISvolmedia.com)
* This notice must stay intact for use
* Visit http://www.dynamicdrive.com/ for full source code

***********************************************/

//Samo brojevi
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

function handleEnter (field, event) {
		var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
		if (keyCode == 13) {
			var i;
			for (i = 0; i < field.form.elements.length; i++)
				if (field == field.form.elements[i])
					break;
			i = (i + 1) % field.form.elements.length;
			field.form.elements[i].focus();
			return false;
		}
		else
		return true;
}


$(function() {

	//VLADA FORMATIRAO DATUME
	$( "#datum_zahteva" ).datepicker({
 		//maxDate: new Date(),
 		minDate: '2014-01-01',
		maxDate: '0',
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd'
	});

	//DODAO VLADA - ZA AUTOMATSKO SETOVANJE DATUMA U DATEPICKERU
	$('#datum_zahteva').datepicker('setDate', 'today');

		$( "#datumang" ).datepicker({
		minDate: '2014-01-01',
		maxDate: '0',
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd'
	});

	// 2016-11-09 datum utuzenja 
	$( "#datum_utuzenja" ).datepicker({
		minDate: '2014-01-01',
		maxDate: '0',
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd'
	});

	// 2016-11-09 datum poravnanja
	$( "#datum_por" ).datepicker({
		minDate: '2014-01-01',
		maxDate: '0',
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd'
	});

	// 2016-11-09 datum konacne isplate
	$( "#datum_isplate" ).datepicker({
		minDate: '2014-01-01',
		maxDate: '0',
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd'
	});

	// 2016-11-17 datum isknjizavanja
	$( "#datumisk" ).datepicker({
		minDate: '2014-01-01',
		maxDate: '0',
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd'
	});
 });




function pokaziDiv() {
	if ($('#dokument').val()=="0")
	{
		$('#div_knjizenje').show();
	}
	else {
		$('#div_knjizenje').hide();
	}
}

$(document).ready(function(){
     jQuery(function($){
			   $('input[name^=procena_datum]').mask('2099-99-99');
			   $("input[name^=procena_iznos]").iMask({
				      type : 'number',
				      groupSymbol : ' ',
				      decSymbol : '.'
				});
		});
});

function upload_fajl(idregres)
{
	
	var fd = new FormData(),
	myFile = document.getElementById("file").files[0];
	fd.append( 'file', myFile);
	
	var url = 'upload_regresi/postavljanje_dokumentacije.php?idregres='+idregres;
	$.ajax({
		url: url,
		type: 'POST',
		data: fd,
		processData: false,
		contentType: false,
		success: function(ret){
	  	var data = JSON.parse(ret);
	    alert(data.poruka);
	    window.location.reload(true);
	  }
	});
}

$(document).ready(function(){

	$('input[name^=dokument_datum]').mask('2099-99-99');
	$('#div_knjizenje').hide();
    //dugme dodaj polja za iznose i datume rata
	$('#dodaj_rate').click(function(){
		var broj_rata = $('#broj_rata').val();
		var i;
		var datum_rate= $('#tabela_rate tr').length;
		for(i=0; i<datum_rate; i++)
		{
			$('#tabela_rate tbody>tr:eq(1)').remove();
		}

		for(i=0; i<broj_rata; i++)
		{
			var datum_rate= $('#tabela_rate tr').length;
			$('#tabela_rate tbody>tr:last').after(
				'<tr><td><label style="margin-left:5px;">Datum '+datum_rate+'. rate:</label></td>'+
				'<td><input type="text" name="datumi_rata[]"  id="datumi_rata[]" class="datum_rate_class" style="margin-left:10px; width:100px;" onblur="return validanDatum(this.value,this);"/></td>'+
				'<td><label style="margin-left:20px;">iznos '+datum_rate+'.rate:</label></td>'+
				'<td><input type="text" name="iznosi_rata[]"  id="iznosi_rata[]" class="iznos_rate_class" style="margin-left:10px; width:100px;" onkeyup="saberiRate();"/></td>'+
				'</tr>'
				);
		}
		$('#tabela_rate tbody>tr:last').after(
				'<tr>'+
				'<tr><td><label style="margin-left:5px;">Ukupno zaduženje:</label></td>'+
				'<td><input type="text" name="ukupno_zaduzenje" id="ukupno_zaduzenje"  style="margin-left:10px; width:100px;"  onkeypress="return samoBrojevi(this,event);" readonly="readonly" /></td>'+
				'</tr>'
		);

		jQuery(function($){
			   $('input[name^=datumi_rata]').mask('2099-99-99');
			   $("input[name^=iznosi_rata]").iMask({
				      type : 'number',
				      groupSymbol : ' ',
				      decSymbol : '.'
				});
			});
		// groupSymbol : ',',
	});

});

function roundNumber(rnum, rlength)
{ 	  // Arguments: number to round, number of decimal places
	  var newnumber = Math.round(rnum*Math.pow(10,rlength))/Math.pow(10,rlength);
	  return parseFloat(newnumber); // Output the result to the form field (change for your purposes)
}

// proveriti sta radi funkcija
function saberiRate()
{
	var uk_zaduzenje = 0;
	$(".iznos_rate_class").each(function(){
		if($(this).val())
		{
			var iznos = $(this).val().replace(/ /g,'');;
			uk_zaduzenje = parseFloat(uk_zaduzenje) + parseFloat(iznos);
		}
	});
	document.getElementById('ukupno_zaduzenje').value = roundNumber(uk_zaduzenje,2);
}

// proverava da li je validan datum pocetka i isteka osiguranja
function validanDatum(datum,obj)
{
    var x=datum.split("-");
	var date = new Date(x[0],(x[1]-1),x[2]);

	var dan = datum.substring(8, 10);
	var mesec = datum.substring(5, 7);
	var godina = datum.substring(0, 4);

	if ((dan != date.getDate() || mesec != (date.getMonth()+1) || godina != date.getFullYear()))
	{
		alert('Nije dobar format datuma');
		obj.value = '';
	}
}
// proverava datume, datum prethodne rate mora biti manje od datuma sledece rate
function proveriDatumePrethRata(datum1, datum2)
{
	var x1=datum1.split("-");
	var dat_pret_rate = new Date(x1[0],(x1[1]-1),x1[2]);

	var x2=datum2.split("-");
	var dat_sled_rate = new Date(x2[0],(x2[1]-1),x2[2]);

	if(dat_sled_rate > dat_pret_rate)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function popuniDiv() {
//	var polja = $("#regresna").serializeArray();

	//alert("Usao u funckiju");
	var url = 'vidi_rate_regres.php';
	$.ajax({
		cache: false,
		url: url,
		type: 'POST',
		data: { brReg: $('#brReg').val() },
		dataType: "html",
		success: function(t) {
			location.reload(); 
			$('#vidi_regres').empty();
			$('#vidi_regres').html(t);
			$('#vidi_regres').show();
		}
	});
}

function popuniDivp() {
	var url = 'procena_regresa.php';
	$.ajax({
		cache: false,
		url: url,
		type: 'POST',
		data: { brReg: $('#brReg').val() },
		dataType: "html",
		success: function(t) {
			$('#procena').empty();
			$('#procena').html(t);
			$('#procena').show();
		}
	});
}
function klik() {
	var validacija_polja = proveraPolja();

	if(validacija_polja==true)
	{
		$('#snimi_rate').hide();
		$('#upisi').hide();
		$('#jmbg_reg').attr('readonly',true);
		var polja = $("#regresna").serializeArray();
		var url = 'knjizenje_rata.php';
		$.ajax({
			cache: false,
			url: url,
			type: 'POST',
			data: polja,
			dataType: "json",
			success: function(t) {

				console.log(t);

				switch (t['rezul']) {
					case 1:
						alert('Podaci uspešno upisani.');
						$('#div_knjizenje').hide();
//						Ubaciti poziv ajax-a koji puni div '#vidi_rate_regres'
						popuniDiv();
						//window.close();
						window.location.reload();
						break;
					case 2:
						alert('Ilegalni karakteri u lozinci.');
						return false;
						break;
					case 3:
						alert('Ilegalni karakteri u lozinci.');
						return false;
						break;
					case 0:
						alert('Neuspešan upis podataka.');
						$('#snimi_rate').show();
						$('#jmbg_reg').attr('readonly',false);
						return false;
						break;
				}
			}
		});
	}
	else {
		alert('Greska');
	}
}

function kliks() {
	var validacija_polja = proveraPolja();
	if(validacija_polja==true)
	{
		var polja = $("#regresna").serializeArray();
		var url = 'knjizenje_rata.php';
		$.ajax({
			cache: false,
			url: url,
			type: 'POST',
			data: polja,
			dataType: "html",
			success: function(t) {
				alert(t);
			}
		});
	}
}

function klikp() {
	var validacija_polja = proveraPoljaProcena();
//	alert("valid="+validacija_polja);
	if(validacija_polja==true)
	{
		$('#div_procena').show();
		var polja = $("#regresna").serializeArray();

		var brReg = $('#brReg').val();
		var procena_datum = $('#procena_datum').val();
		var procena_iznos = $('#procena_iznos').val();
		var koeficijent = $('#koeficijent').val();
		var radnik = $('#radnik').val();

		var url = 'unos_procena.php';
		$.ajax({
			cache: false,
			url: url,
			type: 'POST',
			data: {brReg, procena_datum, procena_iznos, koeficijent, radnik},
			dataType: "json",
			success: function(t) {
				console.log(t);
			//	alert(t['rezul']);
				switch (t['rezul']) {
					case 1:
						alert('Podaci uspešno upisani.');
						$('#div_procena').show();
//						Ubaciti poziv ajax-a koji puni div '#popuniDivp()'
						//popuniDiv();
						window.location.reload(true);
						document.getElementById('procena_datum').value="";
						document.getElementById('koeficijent').value="0";
						document.getElementById('procena_iznos').value="";
						break;
					case 2:
						alert('Ilegalni karakteri u lozinci.');
						return false;
						break;
					case 3:
						alert('Ilegalni karakteri u lozinci.');
						return false;
						break;
					case 0:
						alert('Neuspešan upis podataka.');
						$('#div_procena').show();
						return false;
						break;
				}
			}
		});
	}
}

function kliksp() {
	var validacija_polja = proveraPoljaProcena();
	if(validacija_polja==true)
	{
		var polja = $("#regresna").serializeArray();
		var url = 'unos_procena.php';
		$.ajax({
			cache: false,
			url: url,
			type: 'POST',
			data: polja,
			dataType: "html",
			success: function(t) {
				alert(t);
			}
		});
	}
}

function proveraPoljaProcena(){

	var procena_datum = document.getElementById('procena_datum').value;
	var koeficijent = document.getElementById('koeficijent').value;
    var procena_iznos = document.getElementById('procena_iznos').value;

	if ( !procena_datum || !procena_iznos )
	{
		  if ( !procena_datum  )   {
		     alert('Morate izabrati datum procene!');
		     $('#procena_datum').focus();
		    return false;
			}
	 		if ( !procena_iznos  )   {
	 		alert('Morate izabrati iznos procene!');
		     $('#procena_iznos').focus();
		    return false;
			}
	}
	else
	{

	  var datum_upisa = document.getElementById('datum_upisa').value;
      var x1=datum_upisa.split("-");
	  var dat_upisa_reg = new Date(x1[0],(x1[1]-1),x1[2]);

	  var x2=procena_datum.split("-");
	  var dat_proc_koef = new Date(x2[0],(x2[1]-1),x2[2]);

	  var currentDate = new Date();

	     if (dat_proc_koef  >= dat_upisa_reg && dat_proc_koef <= currentDate)
	     {
	        return true;
	     }

	     else
	     {
	        alert('Datum procene mora biti veći ili jednak datumu upisa i manji ili jednak današnjem datumu!');
	        document.getElementById('procena_datum').value="";
	        $('#procena_datum').focus();
		    return false;
	     }
 	}
}

function proveraPolja(){

    var dokument = document.getElementById('dokument').value;
    var dokument_datum = document.getElementById('dokument_datum').value;
    var dokument_broj = document.getElementById('dokument_broj').value;
    var broj_rata = document.getElementById('broj_rata').value;

	var br_polja_datuma = 0;
	var br_unesenih_datuma = 0;
	var previous_element = null;
	var provera = null;

	$(".datum_rate_class").each(function(index){
		if(previous_element)
		{
			index_sle = index+1;
   		    provera = proveriDatumePrethRata(previous_element, $(this).val());
   		    if(provera==false)
   		    {
   		    	$(this).focus();
				alert('Datum '+index+'. rate mora biti manji od datuma '+index_sle+'. rate!');
				return false;
   	   	    }
		}
		br_polja_datuma++;
		if($(this).val())
		{
			br_unesenih_datuma++;
		}
		previous_element = $(this).val();
	});


	var br_polja_rata = 0;
	var br_unesenih_rata = 0;
	$(".iznos_rate_class").each(function(){
		//niz_rate_iznosi[br_polja_rata] = $(this).val();
		br_polja_rata++;
		if($(this).val())
		{
			br_unesenih_rata++;
		}
	});

	var jmbg_pib = document.getElementById('jmbg_reg').value;

	/*if(!jmbg_pib)
	{
		alert('Morate uneti JMBG/PIB broj!');
		$('#jmbg_reg').focus();
		return false;
	}
	else*/ if (dokument=='0' || !dokument_datum || !dokument_broj || !broj_rata)  //  || !broj_rata
	{
		alert('Morate izabrati dokument i uneti datum i broj dokumenta kao i broj rata!');
		return false;
	}
	else
	{
		if (broj_rata==br_unesenih_rata && broj_rata==br_unesenih_datuma)
		{
			return true;
		}
		else if ((broj_rata!=br_unesenih_rata || broj_rata!=br_unesenih_datuma) && provera!=false)
		{
			if(br_unesenih_rata!=br_unesenih_datuma)
			{
				alert('Broj unešenih datuma rata i iznosa rata mora biti jednak!');
				return false;
			}

			alert('Morate popuniti sve datume rata i iznose rata!');
			return false;
		}
	}
}
// function onblur

function proveriJmbg()
{
	var br = document.getElementById('jmbg_reg').value;
	var i=0;
	for( i=0; i < document.regresna.fizpra.length; i++ )
	{
		if( document.regresna.fizpra[i].checked == true )
		fp = document.regresna.fizpra[i].value;
	}

	if(br.length!=13 && br.length!=0 && fp=='F')
	{
		alert('Morate uneti tačno 13 cifara!');
		document.getElementById('jmbg_reg').value = "";
	}

	if(br.length!=9 && br.length!=0 && fp=='P')
	{
		alert('Morate uneti tačno 9 cifara!');
		document.getElementById('jmbg_reg').value = "";
	}
}


function proveriJmbgPib(fp)
{
	if(fp=='F')
	{
		var br = document.getElementById('jmbg_reg').value;
		if(br.length!=13 && br.length!=0)
		{
			alert('Uneli ste '+br.length+' cifara za jmbg! Morate uneti 13 cifara.');
			document.getElementById('jmbg_reg').value = "";
		}
	}
	if(fp=='P')
	{
		var br = document.getElementById('jmbg_reg').value;
		if(br.length!=9 && br.length!=0)
		{
			alert('Uneli ste '+br.length+' cifara za pib! Morate uneti 9 cifara.');
			document.getElementById('jmbg_reg').value = "";
		}
	}

}

//onkeyup="proveraPibaOS(this.value)" onmousedown="proveraPibaOS(this.value)	"  onmouseup="proveraPibaOS(this.value)	"
function proveraPibaOS(pib_broj)
{
	var fp = 0;
	var i=0;
	for( i=0; i < document.regresna.fizpra.length; i++ )
	{
		if( document.regresna.fizpra[i].checked == true )
		fp = document.regresna.fizpra[i].value;
	}

	var pib = pib_broj.length;
	if(pib==9 && fp=='P')
	{
		proveri_pib(document.getElementById('jmbg_reg'));
	}
}
/*
 * Copyright 2011,2012, SPL 61 d.o.o. Sva prava zadržana.
 * Verzija: 0.07
 * Status: RFC
 *
 * Datum poslednje izmene: 21.01.2012
 * Izmenio: zp
 */
//Parametar b je html objekat tipa input
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
				alert("Greška u PIB-u.\nNije dobar kontrolni broj.");
				b.value = '';
				//b.style.border = '2px solid #007FFF';
				b.focus();
				return false;
			}
		}
		else
		{
			alert('Greška u PIB-u.\nMora imati tačno 9 cifara!');
			b.value = '';
			b.focus();
			return false;
		}
	}
	else {
		alert ("Šifra partnera sadrži isključivo cifre.");
		b.value = '';
			b.focus();
		return false;
	}
	//b.style.border = '1px solid #C4C4C4';
	return true;
}


function keoficijentPovecaj()
{
	var koef = document.getElementById('koeficijent').value;
	var koef_int = parseInt(koef,10);
	var plus = parseInt(1,10);

	if(koef_int<10)
	{
		var zbir = koef_int+plus;
		document.getElementById('koeficijent').value=zbir;
	}

}

function keoficijentSmanji()
{
	var koef = document.getElementById('koeficijent').value;
	var koef_int = parseInt(koef,10);
	var minus = parseInt(1,10);

	if(koef_int>0)
	{
		var zbir = koef_int-minus;
		document.getElementById('koeficijent').value=zbir;
	}

}
function koeficijentPoDatumu(datum)
{
//alert('podaci procene za: '+datum);
    var selektovan_datum=datum;
	  var url = 'procena_po_datumu.php';     //po_datumu
		$.ajax({
			cache: false,
			url: url,
			type: 'POST',
			data: { brReg: $('#brReg').val(), datum:selektovan_datum },
			dataType: "json",
			success: function(t) {
				$('#procena_iz').val(t['iznos']);
				$('#koef1').val(t['koef']);
			}
		});
}

</script>
<!-- dodato 2015-12-16 POCETAK -->
<script>

//MARIJA 2015-12-08 f-ja kojom se izvlace predmeti za dati broj polise
function pronadji_sve_predmete_polise()
{
	document.getElementById('skriven_label').style.display='none';
	document.getElementById('lista_predmeta').style.display='none';
	document.getElementById('podaci_stetni_dogadjaj').innerHTML = '';
/*
	document.getElementById('jmbg_reg').value = null;
	document.getElementById('prezime_reg').value = null;
	document.getElementById('ime_reg').value = null;
	document.getElementById('adresa_reg').value = null;
	document.getElementById('zemlja_reg_id_hidden').value = -1;
	document.getElementById('zemlja_reg_id').value = 199;
	document.getElementById('telefon_reg').value = null;
*/
	document.getElementById('opis_label').style.display='none';
	document.getElementById('opis_text').style.display='none';

	document.getElementById('opstina_regresnog_duznika').style.display='inline';
	document.getElementById('mesto_regresnog_duznika').style.display='inline';
	document.getElementById('mesto_reg_id').value = '-1';
	
	var broj_polise = document.getElementById('brpolise').value;
	var vrsta_osiguranja = document.getElementById('vrsta_osiguranja').value;

	var url = "pronadji_sve_predmete";

	$.ajax({
	    type:'POST',
	    url: "funkcije.php",
	    datatype: 'json',
		data: {
			funkcija:url,
			broj_polise:broj_polise,
			vrsta_osiguranja:vrsta_osiguranja
		 },
	     success: function(ret)
	     {
			var data = JSON.parse(ret);

			if(data.flag == false)
			{
				alert("Ne postoji otvoren predmet odštetnog zahteva za uneti broj polise i vrstu obrasca!");
				document.getElementById('skriven_label').style.display='none';
				document.getElementById('lista_predmeta').style.display='none';
			}
			else
			{
				//console.log(data);
				document.getElementById('skriven_label').style.display='inline';
				document.getElementById('lista_predmeta').style.display='inline';
				document.getElementById('podaci_o_povezanim_regresima').style.display='inline';
			//	podaci_o_povezanim_regresima
				$('#lista_predmeta').empty();
				$('#lista_predmeta').append(data.opcije);
				$('#podaci_o_povezanim_regresima').after('<td colspan="6">' + data.povezani_regresi + '</td>');
			}
			//popuni_podatke_sd(-1);
	     }
	});
}

//MARIJA 2015-12-09 f-ja kojom se popunjavaju podaci o SD na osnovu izabranog POZ
function popuni_podatke_sd(value)
{
	var vrsta_osiguranja = document.getElementById('vrsta_osiguranja').value;
	var url = "popuni_podatke_stetni_dogadjaj";

	document.getElementById('idstete').value = value;

	$.ajax({
	    type:'POST',
	    url: "funkcije.php",
	    datatype: 'json',
		data: {
			funkcija:url,
			predmet_id:value,
			vrsta_osiguranja:vrsta_osiguranja
		 },
	     success: function(ret)
	     {
			var data = JSON.parse(ret);
			document.getElementById('podaci_stetni_dogadjaj').innerHTML = data.html;
			document.getElementById('jmbg_reg').value = data.jmbg_reg;
			console.log(data);
			if(data.fizpra == 'F')
			{
				document.getElementById('fizicko').checked = true;
			}
			else if(data.fizpra == 'P')
			{
				document.getElementById('pravno').checked = true;
			}

			document.getElementById('prezime_reg').value = data.prezime_reg;
			document.getElementById('ime_reg').value = data.ime_reg;
			document.getElementById('adresa_reg').value = data.adresa_reg;
			document.getElementById('zemlja_reg_id').value = data.zemlja_reg;
			
		/*	if(data.zemlja_reg != 199 && data.zemlja_reg !='')
			{
				document.getElementById('opstina_regresnog_duznika').style.visibility='hidden';
				document.getElementById('mesto_regresnog_duznika').style.visibility='hidden';
				document.getElementById('opis_label').style.visibility='visible';
				document.getElementById('opis_text').style.visibility='visible';
			}
			else if(data.zemlja_reg == 199 || data.zemlja_reg =='')
			{
				document.getElementById('opstina_regresnog_duznika').style.visibility='visible';
				document.getElementById('mesto_regresnog_duznika').style.visibility='visible';
				document.getElementById('opis_label').style.visibility='hidden';
				document.getElementById('opis_text').style.visibility='hidden';
				$('#mesto_reg_id').append(data.mesto_reg);
			}*/
					

 			document.getElementById('zemlja_reg_id_hidden').value = data.zemlja_reg;
			document.getElementById('mesto_reg_id').value = data.mesto_reg;
			document.getElementById('zemlja_reg_id').value = data.zemlja_reg;
			document.getElementById('telefon_reg').value = data.telefon_reg;
			document.getElementById('mesto_nastanka_id').value = data.mesto_nastanka_id;
			document.getElementById('zemlja_nastanka_id').value = data.zemlja_nastanka_id;
			document.getElementById('brojst').value = data.novi_broj_predmeta;
			document.getElementById('datumisp').value = data.nalog;
			document.getElementById('iznosisp').value = data.iznos_isplate_mirni;
			document.getElementById('predmet_id').value = value;
			document.getElementById('regres_od').value = data.regres_od;

			/*
			if(data.regres_od == 'Krivac vlasnik vozila')
			{
				//readonly=\"readonly\" class=\"readonlyPolja\"
				$( "#jmbg_reg" ).addClass( "readonlyPolja" );
				$('#jmbg_reg').prop('readonly', true);
				$( "#prezime_reg" ).addClass( "readonlyPolja" );
				$('#prezime_reg').prop('readonly', true);
				$( "#ime_reg" ).addClass( "readonlyPolja" );
				$('#ime_reg').prop('readonly', true);
				$( "#adresa_reg" ).addClass( "readonlyPolja" );
				$('#adresa_reg').prop('readonly', true);
				$('#zemlja_reg_id').prop("disabled", true); 
				$('#opstina_reg_id').prop("disabled", true); 
				//$('#mesto_reg_id').prop("disabled", false); 
			}
			else
			{
				$( "#jmbg_reg" ).removeClass( "readonlyPolja" );
				$('#jmbg_reg').prop('readonly', false);
				$( "#prezime_reg" ).removeClass( "readonlyPolja" );
				$('#prezime_reg').prop('readonly', false);
				$( "#ime_reg" ).removeClass( "readonlyPolja" );
				$('#ime_reg').prop('readonly', false);
				$( "#adresa_reg" ).removeClass( "readonlyPolja" );
				$('#adresa_reg').prop('readonly', false);
				$('#zemlja_reg_id').prop("disabled", false); 
				$('#opstina_reg_id').prop("disabled", false); 
			}
			*/
	     }
	});
}

function vrati_mesta(value,id)
{
	$.ajax({
	    type:'GET',
	    url: '../../common/funkcije.php?funkcija=vrati_sifarnik_mesta_opcije&opstina_id='+value,
	    datatype: 'json',
	    success: function(ret) {
	      var data = JSON.parse(ret);
	      if(id == 'lista_opstina_nastanka')
	      {
		      $('#lista_mesta_nastanka').empty();
		      //document.getElementById('lista_mesta').disabled=false;
		      $('#lista_mesta_nastanka').append(data.opcije);
		      document.getElementById('lista_mesta_nastanka').disabled=false;
	      }
	      else if(id == 'opstina_reg_id')
	      {
	    	  $('#mesto_reg_id').empty();
		      $('#mesto_reg_id').append(data.opcije);
		      document.getElementById('mesto_reg_id').disabled=false;
	      }
	    }
	});
}

//DODAO VLADA - ZA POPUNJAVANJE SELECT LISTE SA MESTIMA I AUTOMATSKI ODABIR
function vrati_mesta_reg(value,id,id_mesta)
{
	$.ajax({
	    type:'GET',
	    url: '../../common/funkcije.php?funkcija=vrati_sifarnik_mesta_opcije&opstina_id='+value,
	    datatype: 'json',
	    success: function(ret) {
			var data = JSON.parse(ret);

			console.log(data);

			$('#mesto_reg_id').html(data.opcije);

			//DOHVATANJE SELECT LISTE SA MESTIMA PO ID-JU
			var select_mesta = document.getElementById('mesto_reg_id');
			var broj_mesta = select_mesta.options.length;

			//PROLAZAK KROZ LISTU SA MESTIMA
			for (var i = 0; i < broj_mesta ; i++) {

				//AKO JE ID MESTA ISTI KAO ID IZ LISTE,SELEKTUJ GA
				if (select_mesta.options[i].value == id_mesta) {

					//select_opstine.options[i].selected = true;
					select_mesta.options[i].setAttribute('selected', true);
					//select_drzave.disabled = true;
				}
			}

			//UPIS ID-JA MESTA U HIDDEN POLJE
			document.getElementById('mesto_reg_id_hidden').value = 	document.getElementById('mesto_reg_id').value;
	    }
	});
}

/*
function prikazi_na_osnovu_zemlja(value)
{

	document.getElementById('zemlja_nastanka_id').value = value;
		 
	if(value == 199)
	{
		document.getElementById('prikazi_opstinu_text').style.display='inline';
		document.getElementById('prikazi_opstinu').style.display='inline';
		document.getElementById('prikazi_mesto_text').style.display='inline';
		document.getElementById('lista_mesta_nastanka').style.display='inline';
		document.getElementById('prikaz_opisa_text').style.display='none';
		document.getElementById('opis_lokacije').style.display='none';
	}
	else 
	{
		document.getElementById('prikaz_opisa_text').style.display='inline';
		document.getElementById('opis_lokacije').style.display='inline';
	}
}
*/

// dodato 2015-12-18
/* 
 *dopunje 2015-12-24 kako bi se yabranio unos unih advokatskih kancelarija pre nego sto se razduzi postojeca advokatska kancelarija, 
 * dakle da se ne mogu postaviti kao aktivne dve advokatske kancelarije u isto vreme
 */
function otvori_stranu_adv_kancelarija(idregres)
{
	//alert(idregres);
	var url = 'proveri_status_advokatske_kancelarije';
	$.ajax({
	    type:'POST',
	    url: "funkcije.php",
	    datatype: 'json',
		data: {
			funkcija:url,
			idregres:idregres
		 },
	     success: function(ret)
	     {
			var data = JSON.parse(ret);

			if(data.flag)
			{
				window.location ='advokatska_kancelarija.php?idregres=' + idregres + '&akcija=dodaj';
			}
			else
			{
				alert("Morate razdužiti sve postojeće advokatske kancelarije kako bi angažovali novu!");
				$('#adv_unos').prop("disabled", false); 
			}
				
	     }
	});
}

// dodato 2015-12-22
function popuni_mesto(value)
{
	document.getElementById('mesto_nastanka_id').value=value;
}


function postavi_polja_po_zemlji(value)
{
	if(value != 199)
	{
		document.getElementById('opstina_regresnog_duznika').style.display='none';
		document.getElementById('mesto_regresnog_duznika').style.display='none';
		document.getElementById('opis_label').style.display='inline';
		document.getElementById('opis_text').style.display='inline';
	}
	else if(value == 199)
	{
		document.getElementById('opstina_regresnog_duznika').style.visibility='visible';
		document.getElementById('mesto_regresnog_duznika').style.visibility='visible';
		document.getElementById('opis_label').style.visibility='hidden';
		document.getElementById('opis_text').style.visibility='hidden';
	}
	document.getElementById('zemlja_reg_id_hidden').value=value;
	
}

// dodato kako bi se napravila iymena da ukoliko kliknemo na otpisan po predlogu komisije, da se ukloni dugme Upisi plan otplate
function proveri_status_regresa(value)
{
	if(value == 'OT')
	{
		document.getElementById('dugme').style.visibility='hidden';
		document.getElementById('div_knjizenje').style.display='none';
	    document.getElementById('dokument').value=null;
	    document.getElementById('dokument_datum').value=null;
	    document.getElementById('dokument_broj').value=null;
	    document.getElementById('broj_rata').value=null;
	}
	else
	{
		document.getElementById('dugme').style.visibility='visible';
	}
}
</script>
<!-- dodato 2015-12-16 KRAJ -->
<script language="javascript" src="../../common/cal2.js">
/*
Xin's Popup calendar script- Xin Yang (http://www.yxscripts.com/)
Script featured on/available at http://www.dynamicdrive.com/
This notice must stay intact for use
*/
</script>

<style>
.disabledbig {
	BACKGROUND: #DDDDDD;
	font-weight: bold;
	color: black;
	height: 30;
	font-size: 15;
}


	#div_knjizenje{
		display: none;
		padding: 10px;
	 	position:relative;
	 	float:left;
	 	background-color:#FDFFC2;/*FFDACA;*/
	 	-moz-box-shadow: 0 0 1px #F6F6F6;/*#D7DE14; /*FF0000;*/
	    border-width:1px;
	    border-style:solid;
	    border-bottom-color:#aaa;
	    border-right-color:#aaa;
	    border-top-color:#fff;
	    border-left-color:#fff;
	    border-radius:6px;
	    -moz-border-radius:6px;
	    -webkit-border-radius:6px;
	    background-image: -moz-linear-gradient(top,#D5D3D0,#DDEEEE, #E7F8F8,#D5D3D0);
	    font-family: Arial;
	    color:#1F3961;
	    display:block;
	    width:1000px;
	}

  #div_procena{
		display: none;
		padding: 10px;
	 	position:relative;
	 	float:left;
	 	background-color:#FDFFC2;/*FFDACA;*/
	 	-moz-box-shadow: 0 0 1px #F6F6F6;/*#D7DE14; /*FF0000;*/
	    border-width:1px;
	    border-style:solid;
	    border-bottom-color:#aaa;
	    border-right-color:#aaa;
	    border-top-color:#fff;
	    border-left-color:#fff;
	    border-radius:6px;
	    -moz-border-radius:6px;
	    -webkit-border-radius:6px;
	    background-image: -moz-linear-gradient(top,#D5D3D0,#DDEEEE, #E7F8F8,#D5D3D0);
	    font-family: Arial;
	    color:#1F3961;
	    display:block;
	    width:1000px;
	}

.readonlyPolja {
   width: 187px;
   margin:2px;
   margin-left:4px;
   margin:2px;
   margin-left:4px;
   background-color:#D1CFCC;
   border:1px solid #A5A6A8;
   margin-right: 1em;
   text-decoration: none;
   -moz-border-radius: 2px;
   padding: 2px;

}

/*DODAO VLADA - STIL ZA DISABLE-OVANA POLJA*/
[disabled] {

color: #000000;
background-color: #D1CFCC;
border:1px solid #b3b3b3;
margin-right: 1em;
}

/*DODAO VLADA - ZA SAKRIVANJE DATEPICKERA*/
#ui-datepicker-div {display: none;}
</style>

<script language="javascript" src="../../common/cal_confp.js"></script>
</head>

<!--DODAO VLADA - ONLOAD FUNKCIJA,KOJOJ SE PROSLEDJUJE STATUS IZ GET ZAHTEVA-->
<body bgcolor="#F2F4F9" onload="dohvatiPodatkeForma()">

<div align="center">
  <table border="0" style="border-collapse: collapse" width="100%" id="table259">
    <tr>
      <td align="center">
            <TABLE class=tbt cellSpacing=0 cellPadding=0 border=0 id="table260" width="100%">
              <TBODY>
              <TR>
                <TD class=tbtl width="22">
        <IMG height=22 alt=""
                  src="../../images/icg/tb2_l.gif" width=22></TD>
                <TD class=tbtbot style="background-image: url('../../images/icg/tb2_m.gif')">
        <b><font color="#FF6500" face="Verdana">Regresna potraživanja - ažuriranje</font></b></TD>
                <TD class=tbtbot style="background-image: url('../../images/icg/tb2_m.gif')">
        </TD>
                <TD class=tbtr width="124">
        <IMG height=22 alt=""
                  src="../../images/icg/tb2_r.gif" width=124></TD></TR></TBODY></TABLE>
            <table border="1" cellpadding="5" id="table261" width="100%" bgcolor="#F2F4F9" style="border-collapse: collapse" bordercolorlight="#C0C0C0" bordercolordark="#C0C0C0" cellspacing="5">
<tr>
<td>

<?php
require "../../common/sifarnici_class.php";
$sifarnici_class = new sifarnici_class();

if (!$radnik ) {
echo "<script type=\"text/javascript\">";
echo "window.open ('../../meni.php', 'contents')";
echo "</script>";
}

require "funkcije.php";
//izmenio Marko Stanković  ukinuto jer nema potrebe za proverom jel imamo privilegije
// if ($radnik > 999) {

// require "../../common/sudske_pravo.php";

// }

//šđčćžČĆŽĐŠ

$conn = pg_connect ("host=localhost dbname=stete user=zoranp");
if (!$conn) {
	echo "Greška otvaranja konekcije prema SQL serveru.";
	exit;
	}
$conn1 = pg_connect ("host=localhost  dbname=amso user=zoranp");
if (!$conn1) {
	echo "Greška otvaranja konekcije prema SQL serveru.";
	exit;
	}
	

echo "<form action='' id=\"regresna\" name=\"regresna\" method=\"post\" accept-charset=\"iso8859-2\" >\n";

// 2015-12-16
if (!$submit){

	$datum_upisa=date("Y-m-d");

}

echo "<font color=\"navy\" size=\"+1\"><b>Regresna potraživanja - ažuriranje</b></font>\n";


if (!$snimi_adv){$daadv=1;$sub=1;}

if ($snimi_adv){

$daadv=1;

$prikazi_formu = 0;


if ( !$brsud  && $daadv){

require "advokatska_kancelarija.php";
echo "<script language=\"javascript\">\n";
echo "document.regresna.brsud.value='';\n";
echo "alert(\"Unesite sud!\")\n";
echo "document.regresna.brsud.focus();\n";
echo "</script>\n";
$daadv=0;}


if ( !$advokat  && $daadv){

require "advokatska_kancelarija.php";
echo "<script language=\"javascript\">\n";
echo "document.regresna.advokat.value='';\n";
echo "alert(\"Unesite advokata!\")\n";
echo "document.regresna.advokat.focus();\n";
echo "</script>\n";
$daadv=0;}


if ( !je_datum($datumang) && $daadv){

require "advokatska_kancelarija.php";
echo "<script language=\"javascript\">\n";
echo "document.regresna.datumang.value='';\n";
echo "alert(\"Neispravan datum angažovanja!\")\n";
echo "document.regresna.datumang.focus();\n";
echo "</script>\n";
$daadv=0;}


if ($daadv && $status == 'izmeni'){


$sql="begin;";
$rezultat=pg_query($conn,$sql);

$sql="select max(datumang) as stari from advokat where idregres=$idregres ";
$rezultat=pg_query($conn,$sql);
$niz=pg_fetch_assoc($rezultat);

$stari= $niz['stari'];

if ($datumang<$stari){
require "advokatska_kancelarija.php";
echo "<script type=\"text/javascript\">";
echo "alert(\"Datum angažovanja je mlađi od datuma angažovanja prethodnog advokata!\")\n";
echo "</script>";

}
else{
	
	$datumang= date('Y-m-d', strtotime($datumang));
	$datum_zahteva=date('Y-m-d', strtotime($datum_zahteva));
	

$sql="insert into advokat ( idregres, brsud , advokat ,  datumang , radnik , dana , vreme) values ($idregres, ";

if ($brsud){$sql.="'$brsud',"; }
else{$sql.="null,";}
if ($advokat){$sql.="'$advokat',"; }
else{$sql.="null,";}
if ($datumang){$sql.="'$datumang',"; }
else{$sql.="null,";}
$sql.=" $radnik, current_date, current_time )";


$rezultat1=pg_query($conn,$sql);


$sql="update regresna set brsud='$brsud', advokat='$advokat'  where idregres=$idregres";

$rezultat2=pg_query($conn,$sql);


if ( $rezultat1 && $rezultat2) {

$sql="commit;";
$rezultat=pg_query($conn,$sql);
$sub=1;

}
else {
$sql="rollback;";
$rezultat=pg_query($conn,$sql);
}
}

}
}

//VLADA DODAO IDREGRES U IF
if (!$submit && !$adv_unos && !$snimi_adv && !$zatvori_adv && !$steta && $idregres){

	$sql=" select * from regresna where idregres=$idregres";


	$rezultat=pg_query($conn,$sql);
	$niz = pg_fetch_assoc($rezultat);


	$brReg=$niz['brreg'];
	$datum_upisa=$niz['datum_upisa'];

	$jmbg_reg=$niz['jmbg_reg'];
	$prezime_reg=$niz['prezime_reg'];
	$ime_reg=$niz['ime_reg'];
	$adresa_reg=$niz['adresa_reg'];
	$mesto_reg = $niz['mesto_reg'];
	// izmena zbog id mesta u tabeli regresna 
	$mesto_reg_id=$niz['mesto_reg_id'];
	$zemlja_reg_id=$niz['zemlja_reg_id'];

	$telefon_reg=$niz['telefon_reg'];

	$potrazivanje=$niz['potrazivanje'];
	$datum_zahteva=$niz['datum_zahteva'];
	$datum_por=$niz['datum_por'];
	$brRata=$niz['brrata'];
	$dug=$niz['dug'];

	$dokument = $niz['dokument_vrsta'] ? $niz['dokument_vrsta'] : '0';
	$dokument_broj=$niz['dokument_broj'];
	$dokument_datum=$niz['dokument_datum'];

	$datum_isplate=$niz['datum_isplate'];
	$datum_utuzenja=$niz['datum_utuzenja'];

	//$brSud=$niz['brsud'];
	//$advokat=$niz['advokat'];
	$napomena=$niz['napomena'];

	$brojst=$niz['brojst'];
	$brpolise=$niz['brpolise'];
	$datumnast=$niz['datumnast'];
	$opis_lokacije=$niz['mestonast'];
	$datumisp=$niz['datumisp'];
	$iznosisp=$niz['iznosisp'];
	$odluka=$niz['odluka'];
	$procnap=$niz['procnap'];
	$kamata=$niz['kamata'];
	$datumisk=$niz['datumisk'];
	$odlukaisk=$niz['odlukaisk'];
	$opis=$niz['opis'];

	//ZAKOMENTARISAO VLADA
	//$fizpra=$niz['fizpra'];
	// dodato za izvlacenje podataka o zemlji i mestu nastnka stete
	$mesto_nastanka_id = $niz['mesto_nastanka_id'];
	$zemlja_nastanka_id = $niz['zemlja_nastanka_id'];


	$sub=1;


	//UPIT ZA DOBIJANJE ID-JA STETE,NA OSNOVU ID-JA REGRESA
	$upit_id_stete = "SELECT idstete FROM steta_regres WHERE idregres = $idregres"; 

	//IZVRSAVANJE UPITA
	$rezultat_id = pg_query($conn, $upit_id_stete);

	//AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA,OBAVESTI KORISNIKA I PREKINI IZVRSAVANJE SKRIPTE
	if (!$rezultat_id) {

		echo json_encode('Greška pri izvršavanju upita. Pokušajte ponovo.' .pg_last_error($conn));
		die();
	}

	else {
		//UPIS PODATAKA IZ BAZE U NIZ I DOBIJANJE ID-JA STETE
		$niz_id = pg_fetch_array($rezultat_id);
		$id_stete = $niz_id['idstete'];


		//UPIT ZA DOBIJANJE PODATAKA O STETNOM DOGADJAJU
		$upit_stetni_dogadjaj2 = "SELECT poz.id, sd.datum_nastanka, sd.zemlja_nastanka_id, sd.mesto_nastanka_id
		
		FROM predmet_odstetnog_zahteva AS poz

		INNER JOIN odstetni_zahtev AS oz ON oz.id = poz.odstetni_zahtev_id

		INNER JOIN stetni_dogadjaj AS sd ON sd.id = oz.stetni_dogadjaj_id
		
		WHERE poz.id = $id_stete";

		//IZVRSAVANJE UPITA I UPIS U NIZ
		$rezultat_stetni_dogadjaj2 = pg_query($conn, $upit_stetni_dogadjaj2);
		$niz_stetni_dogadjaj2 = pg_fetch_assoc($rezultat_stetni_dogadjaj2);

		//UPIS VREDNOSTI U PROMENJIVE
		$datumnast = $niz_stetni_dogadjaj2['datum_nastanka'];
		$mesto_nastanka_id = $niz_stetni_dogadjaj2['mesto_nastanka_id'];
		$zemlja_nastanka_id = $niz_stetni_dogadjaj2['zemlja_nastanka_id'];
	}

}

//DODAO VLADA - AKO JE NOV REGRES I POSTOJI ID STETE
if (!$submit && !$adv_unos && !$snimi_adv && !$zatvori_adv && !$steta && $idstete){

	//UPIT ZA DOBIJANJE PODATAKA O STETNOM DOGADJAJU
	$upit_stetni_dogadjaj = "SELECT poz.id, sd.datum_nastanka, sd.zemlja_nastanka_id, sd.mesto_nastanka_id

	FROM predmet_odstetnog_zahteva AS poz

	INNER JOIN odstetni_zahtev AS oz ON oz.id = poz.odstetni_zahtev_id

	INNER JOIN stetni_dogadjaj AS sd ON sd.id = oz.stetni_dogadjaj_id

	WHERE poz.id = $idstete";

	//IZVRSAVANJE UPITA I UPIS U NIZ
	$rezultat_stetni_dogadjaj = pg_query($conn, $upit_stetni_dogadjaj);
	$niz_stetni_dogadjaj = pg_fetch_assoc($rezultat_stetni_dogadjaj);

	//UPIS VREDNOSTI U PROMENJIVE
	$datumnast = $niz_stetni_dogadjaj['datum_nastanka'];
	$mesto_nastanka_id = $niz_stetni_dogadjaj['mesto_nastanka_id'];
	$zemlja_nastanka_id = $niz_stetni_dogadjaj['zemlja_nastanka_id'];

	$id_stete = $idstete;
}

if($steta && $brojst){

$dast=1;

if (!ereg("^[SPZŽKAOsNIU]{1,3}\-[0-9]{1,8}\/[0-9]{2,2}$",$brojst) ){
    echo "<script language=\"javascript\">\n";
    echo "document.regresna.brojst.value='';\n";
    echo "alert(\"Broj štete nije unet u ispravnom formatu!\")\n";
    echo "document.regresna.brojst.focus();\n";
    echo "</script>\n";
    $dast = 0;
    }

//echo $dast;

if($dast){
/*
$niz1 =split("-", $brojst);
$niz2 = split("/", $niz1[1]);
$vrstast=$niz1[0];
$brst=$niz2[0];
$g=$niz2[1];

if ($g==98 || $g==99){$godina='19' . $g;}
                 else{$godina='20' . $g;}

$datum1=$godina . '-01-01';
$datum2=$godina . '-12-31';
*/
$niz_brstete = explode(';',$brojst);
$brojac = count($niz_brstete);
/* ZAKOMENTARISALA - NISAM SIGURNA DA TREBA DA BUDE ZAKOMENTARISANO
for( $i=0 ; $i<$brojac-1; $i++ )
{
$niz_brstete[$i]= trim($niz_brstete[$i]);

	$vrsta_st = substr($niz_brstete[$i],0,2);
	$broj_st = substr($niz_brstete[$i],3,-3);
	$god_st = substr($niz_brstete[$i],-2);
	
	if ($vrsta_st == 'AO' || $vrsta_st == 'AK' || $vrsta_st == 'ZK') {
	preg_match('/^(\d+)$/', $broj_st, $matches);
	$tip_broja_stete = isset($matches[1]) && $matches[1] ? 1 : 0;
	if (!$tip_broja_stete) {
		preg_match('/^(\d+)\-(\d+)\-(\d+)$/', $broj_st, $matches);
		$tip_broja_stete = isset($matches[0]) && $matches[0] ? 2 : 0;
				if ($tip_broja_stete) {
			$broj_odstetnog_zahteva = $matches[1];
			$broj_predmeta = $matches[2];
			$broj_aktivacije = $matches[3];
		}
	}
	else {
		$brst = $matches[0];
	}
}

if ($vrsta_st == "SP") {
	preg_match('/^(\d+)$/', $broj_st, $matches);
	$tip_broja_stete = isset($matches[1]) && $matches[1] ? 1 : 0;
	if (!$tip_broja_stete) {
		preg_match('/^([AKOZ]{2,2})\-(\d+)$/', $broj_st, $matches);
		$tip_broja_stete = isset($matches[0]) && $matches[0] ? 2 : 0;
	}
}
	
	if ($god_st==98 || $god_st==99){$godina='19' . $god_st;}
                 else{$godina='20' . $god_st;}

switch ($vrsta_st) {

	case 'SP':$sql="select  s.brpolise as brpolise, s.datum_nast as datumnast, s.jmbg_osig as jmbg_reg, s.prezime_osig as prezime_reg, s.ime_osig as ime_reg, s.adresa_osig as adresa_reg, s.mesto_osig as mesto_reg, s.telefon_osig as telefon_reg , case when (select sum(iznos) as isplaceno from isplate_sp where idsp=s.idsp ) isnull then 0.00 else (select sum(iznos) as isplaceno from isplate_sp where idsp=s.idsp ) end as iznosisp, datum_isplate as datumisp from sudski_postupak as s where s.brsp='$niz_brstete[$i]' group by s.idsp,s.brpolise, s.datum_nast, s.datum_isplate, s.jmbg_osig, s.prezime_osig, s.ime_osig, s.adresa_osig, s.mesto_osig, s.telefon_osig  ";
//	echo $sql;	
	break;
	case 'ZK':$sql="select  z.brzk as brpolise, z.datum_stete as datumnast, z.jmbg_osig as jmbg_reg, z.prezime_osig as prezime_reg, z.ime_osig as ime_reg, z.adresa_osig as adresa_reg, z.mesto_osig as mesto_reg, z.telefon_osig as telefon_reg , case when (select sum(iznos) as isplaceno from isplate_zk where idzk=z.idzk ) isnull then 0.00 else (select sum(iznos) as isplaceno from isplate_zk where idzk=z.idzk ) end as iznosisp, datum_isplate as datumisp from zeleni as z where z.brojzk='$niz_brstete[$i]' group by z.idzk, z.brzk, z.datum_stete, z.datum_isplate, z.jmbg_osig, z.prezime_osig, z.ime_osig, z.adresa_osig, z.mesto_osig, z.telefon_osig ";
//	echo $sql;
	break;
	default:
	if ($tip_broja_stete == 2)
	$sql = "SELECT a.id as idstete, vrsta_osiguranja,broj_polise as brpolise, c.datum_nastanka as datumnast, a.mestost as mestonast, a.jmbgpibkriv as jmbg_reg, a.prezimekriv as prezime_reg, a.imenazivkriv as ime_reg, case when (select sum(iznos) as isplaceno from isplate where idstete=a.id ) isnull then 0.00 else (select sum(iznos) as isplaceno from isplate where idstete=a.id ) end as iznosisp,nalog as datumisp FROM  predmet_odstetnog_zahteva as a, odstetni_zahtev as b, stetni_dogadjaj as c where a.odstetni_zahtev_id = b.id and b.stetni_dogadjaj_id = c.id and vrsta_osiguranja = '$vrsta_st' AND broj_zahteva = $broj_odstetnog_zahteva AND extract(year FROM datum_podnosenja_zahteva) = '$godina' and broj_predmeta = $broj_predmeta AND broj_aktivacije = $broj_aktivacije";
	
	if ($tip_broja_stete == 1)
	
	$sql="select  k.brpolise as brpolise, k.datumnast as datumnast, k.mestost as mestonast, k.jmbgpibkriv as jmbg_reg, k.prezimekriv as prezime_reg, k.imenazivkriv as ime_reg, case when (select sum(iznos) as isplaceno from isplate where idstete=k.idstete ) isnull then 0.00 else (select sum(iznos) as isplaceno from isplate where idstete=k.idstete ) end as iznosisp, nalog as datumisp from knjigas as k where  where  k.vrstast='$vrsta_st' and k.brst=$broj_st and extract(year from k.datumevid)='$godina  group by k.idstete, k.brpolise, k.datumnast, k.mestost, k.nalog, k.jmbgpibkriv, k.prezimekriv, k.imenazivkriv   ";
	break;
//echo $sql;
}

$rezultat=pg_query($conn,$sql);
$niz = pg_fetch_assoc($rezultat);


$brpolise=$niz['brpolise'];
$datumnast=$niz['datumnast'];
$mestonast=$niz['mestonast'];
$iznosisp=$niz['iznosisp'];
$datumisp=$niz['datumisp'];
$jmbg_reg=$niz['jmbg_reg'];
$prezime_reg=$niz['prezime_reg'];
$ime_reg=$niz['ime_reg'];
$adresa_reg=$niz['adresa_reg'];
$mesto_reg=$niz['mesto_reg'];
$telefon_reg=$niz['telefon_reg'];

}*/ // za štetu FOR
} // ok je broj štete
} // prevuci podatke iz štete


$da=1;
if (!$adv_unos && $daadv){


echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" >\n";


echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

echo "<tr>\n";

//IZMENIO VLADA
$status_nepromenjenih_podataka = ($idregres) ? "readonly=\"readonly\" class=\"readonlyPolja\"": "";


//KOD ZA DOBIJANJE BROJA REGRESA - PREMESTIO VLADA
$g=date("Y");

$sqlr="select max(substr(brreg,3,length(brreg)-5)::integer)::integer as brojr from regresna where extract(year from datum_upisa)=extract(year from current_date) " ;

$rez=pg_query($conn,$sqlr);
$nizr = pg_fetch_assoc($rez);

$brojr= $nizr['brojr'];

echo "<td align=\"right\" style='width:17%'>\n";
echo "Redni broj:</td>\n";

if($idstete){

	//UPIT ZA PROVERU DA LI REGRES ZA ODREDJENU STETU VEC POSTOJI - DODAO VLADA
	$upit_provera = "SELECT idstete FROM steta_regres WHERE idstete = $idstete";
	$rezultat_provera = pg_query($conn,$upit_provera);

	$red_provera = pg_fetch_array($rezultat_provera);

	//AKO NE POSTOJI OTVOREN REGRES,NASTAVI DALJE
	if(!$red_provera['idstete']) {

		if (!$brojr){$brojr='1';}
		else{$brojr= $nizr['brojr']+1;}

		$brReg= "R-" . $brojr . "/" . substr($g,2,2);

		//UPIT ZA UNOS U TABELU REGRESNA
		$upit_otvaranje_regresa = "INSERT INTO regresna(brreg, datum_upisa) VALUES('$brReg', current_date) RETURNING idregres";
		$rezultat_otvaranje = pg_query($conn,$upit_otvaranje_regresa);

		$red_otvaranje = pg_fetch_array($rezultat_otvaranje);

		//AKO SE UPIT NE IZVRSI
		if(!$red_otvaranje['idregres']) {

			$poruka = "Greška pri unosu u tabelu regresna.";

			echo "<script type=\"text/javascript\">";
			echo "alert(\"Greska! $poruka\")\n";
			echo "</script>";
		}
		//AKO SE UPIT IZVRSI
		else {

			$idregresa = $red_otvaranje['idregres'];

			//UPIT ZA UNOS U TABELU STETA_REGRES - URADITI UPDATE VRSTE KASNIJE
			$upit_steta_regres = "INSERT INTO steta_regres(id, idregres, idstete, radnik, dana, vreme) VALUES(DEFAULT, $idregresa, $idstete, $radnik, current_date, current_time) RETURNING id";
			$rezultat_steta_regres = pg_query($conn,$upit_steta_regres);

			$red_steta_regres = pg_fetch_array($rezultat_steta_regres);
			
			//AKO SE UPIT NE IZVRSI
			if(!$red_steta_regres['id']) {

				$poruka = "Greška pri unosu u tabelu steta_regres.";

				echo "<script type=\"text/javascript\">";
				echo "alert(\"Greska! $poruka\")\n";
				echo "</script>";
			}

			//AKO SE UPIT USPESNO IZVRSI
			if($rezultat_steta_regres)
			{
				$sql_pravni_select = "select * from pravni where idstete = $idstete";
			
				$rezultat_pravni_select = pg_query($conn,$sql_pravni_select);
					
				if($rezultat_pravni_select)
				{
					$sql_pravni_update = "update pravni set ";

					if ($brReg){$sql_pravni_update .="regpotr = true,"; }
					else{$sql_pravni_update .="regpotr = null,";}
					$sql_pravni_update .=" radnik=$radnik, dana=current_date, vreme=current_time where idstete = $idstete";
					$rezultat_pravni_update = pg_query($conn,$sql_pravni_update);
					if($rezultat_pravni_update)
					{
						$flag = true;
					}
					else
					{
						$flag = false;
						$poruka = "UPDATE pravna $sql_pravni_update";
					}
				}
			}
			else
			{
				$flag = false;
				$poruka = "STETA REGES $sql_sr";
			}
		}
	}
	else {

		if (!$brojr){$brojr='1';}
		else{$brojr= $nizr['brojr'];}

		$brReg= "R-" . $brojr . "/" . substr($g,2,2);
	}
}

echo "<td align=\"left\" style='width:17%'><input name=\"br_Reg\" id=\"brReg\" value=\"$brReg\" size=\"15\" height=\"15\"  DISABLED style=\"color: #000080;\" onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";


echo "<script language=\"javascript\">\n";
echo "document.regresna.brReg.focus();\n";
echo "</script>\n";

echo "<td align=\"right\" style='width:17%'>\n";
echo "Datum upisa:</td>\n";

//IZMENIO VLADA - AKO NE POSTOJI ID REGRESA
if(!$idregres)
{
	$datum_upisa = date('Y-m-d');
}
echo "<td align=\"left\" style='width:17%'><input name=\"datum_upisa\" value=\"$datum_upisa\" size=\"15\" height=\"15\"  readonly=\"readonly\" class=\"readonlyPolja\" onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";

echo "<td align=\"right\" style='width:17%'>\n";
echo "Datum pokretanja reg.zahteva:</td>\n";

//IZMENIO VLADA - AKO POSTOJI ID REGRESA
if($idregres)
{
	echo "<td align=\"left\" style='width:15%'><input name=\"datum_zahteva\" value=\"$datum_zahteva\" size=\"15\" height=\"15\" $status_nepromenjenih_podataka onkeypress=\"return handleEnter(this, event)\">\n";
}else {
	echo "<td align=\"left\" style='width:15%'><input name=\"datum_zahteva\" id=\"datum_zahteva\" value=\"\" size=\"15\" height=\"15\" $status_nepromenjenih_podataka onkeypress=\"return handleEnter(this, event)\">\n";	
}
//IZMENIO VLADA KRAJ
echo "</td></tr>\n";

//dodatak

echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";

echo "<tr><td colspan=\"6\"><b>PODACI O ŠTETNOM DOGAĐAJU</b></td></tr>\n";

echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";


// 2015-12-16
echo "<tr>";

	//DODAO VLADA
	echo "<td align=\"right\">\n";
	echo "Br.odštetnog zahteva: ";
	echo "</td>";

	echo "<td align=\"left\">";
	echo "<a target=\"_blank\" style=\"font-size:16px;\" id=\"lista_predmeta\" name=\"lista_predmeta\" readonly=\"readonly\" href></a>";
	echo "</td>";

	echo "<td align=\"right\">\n";
	echo "Broj polise: ";
	echo "</td>";
	echo "<td>";
	//VLADA DODAO KLASU READONLY POLJA I READONLY ATRIBUT
	echo "<input type='text' name='brpolise' class='readonlyPolja' id='brpolise' value='$brpolise' size='15' readonly=\"readonly\">";
	//echo "<input type='button' name='pronadji' value='pronađi' onclick='pronadji_sve_predmete_polise();'>";
	echo "</td>";

	echo "<td align='right'>";
	echo "Vrsta obrasca: ";
	echo "</td>";
	echo "<td>";
	$orgorg = array(0 => 'AO', 1 => 'OK', 2 => 'AK', 3 => 'IO', 4 => 'ZK', 5 => 'GR', 6 => 'JS', 7 => 'N',8 => 'DPZ', 9 => 'LS');
	//$orgorg = array(0 => 'AO', 1 => 'AK');

	//VLADA DODAO DISABLED
	echo "<select name='vrsta_osiguranja' id='vrsta_osiguranja' style='margin-right:20px;float:left;' disabled='true'>\n";
	
	foreach ($orgorg as $org) {
		echo "<option ";
		if ($vrPolise == $org) { echo "selected "; }
		echo "value='$org'>$org</option>";
	}
	echo "</select>";
	echo "</td>";

	echo "</tr>\n";
	echo "<tr id='podaci_stetni_dogadjaj'></tr>";

	/*
	$sql_idstete = "SELECT * FROM steta_regres WHERE idregres = $idregres";
	$rezultat_idstete = pg_query($conn,$sql_idstete);
	$niz_idstete = pg_fetch_assoc($rezultat_idstete);
	$idstete = $niz_idstete['idstete'];
	$vrsta_st_zk = $niz_idstete['vrsta'];
	
	echo "<td align=\"right\">\n";
	//echo "Broj štete:</td>\n";
	echo "Br.odštetnog zahteva:</td>\n";
	echo "<td  align=\"left\">";
	if($vrsta_st_zk == 'SP' || $vrsta_st_zk == 'ZK')
	{
		echo "<label style='font-weight: bold; font-size:18px;'>" . $brojst . "</label>";
	}
	else 
	{
		echo '<a target="_blank" style="font-size:16px;" href="../../stete/pregled.php?idstete='.$idstete.'&dugme=DA">' . $brojst . '</a>';
	}
	//echo '<a target="_blank" style="font-size:16px;" href="../../stete/pregled.php?idstete='.$idstete.'&dugme=DA">' . $brojst . '</a>';
	//echo "<label style='font-weight: bold; font-size:18px;'>" . $brojst . "</label>";
	//<input name=\"brojst\" id=\"brojst\" value=\"$brojst\" size=\"15\" height=\"15\" readonly=\"readonly\" class=\"readonlyPolja\" title=\"Ne možete promeniti broj odštetnog zahteva\" title=\"Ne možete promeniti \" onkeypress=\"return handleEnter(this, event)\">\n";
	echo "</td>\n";
	*/
	/*
	echo "<td align=\"right\">\n";
	echo "Broj polise:</td>\n";
	echo "<td align=\"left\"><input name=\"brpolise\" id=\"brpolise\" value=\"$brpolise\" size=\"15\" height=\"15\"  readonly=\"readonly\" class=\"readonlyPolja\" title=\"Ne možete promeniti broj polise\" title=\"Ne možete promeniti \"  onkeypress=\"return handleEnter(this, event)\">\n";
	echo "</td>";
	echo "</tr>\n";
	*/
	
	echo "<tr><td align=\"right\" >\n";
	echo "Datum nastanka:</td>\n";
	//DODAO VLADA READONLY I KLASU READONLY
	echo "<td  align=\"left\"><input id=\"datumnast\" name=\"datumnast\" value=\"$datumnast\" size=\"15\" height=\"15\" class='readonlyPolja' $status_nepromenjenih_podataka onkeypress=\"return handleEnter(this, event)\" readonly=\"readonly\">\n";
	echo "</td>\n";
	
	if($zemlja_nastanka_id == 199)
	{
		$opstina_nastnka_po_mesto = $sifarnici_class->vratiOpstinuPoMestu($mesto_nastanka_id);
		$opstina_nastnka_id = $opstina_nastnka_po_mesto['id'];
		$opstina_nastanka_naziv = $opstina_nastnka_po_mesto['vrednost'];
		echo "<td align=\"right\">\n";
		echo "Opština nastanka:</td>\n";
		echo "<td  align=\"left\">";
		echo "<select name='opstina_nastanka_id' id='opstina_nastanka_id' disabled ><option value='$opstina_nastnka_id' selected>$opstina_nastanka_naziv</option></select>";
		echo "</td>\n";
		
		$rezultat_mesto_nastnka_po_id = $sifarnici_class->vratiNazivMesta($mesto_nastanka_id);
		$niz_nastanka_naziv = pg_fetch_assoc($rezultat_mesto_nastnka_po_id);
		$mesto_nastanka_naziv = $niz_nastanka_naziv['vrednost'];
		echo "<td align=\"right\">\n";
		echo "Mesto nastanka:</td>\n";
		echo "<td  align=\"left\">";
		echo "<select name='mesto_nastanka_id' id='mesto_nastanka_id' disabled><option value='$mesto_nastanka_id'>$mesto_nastanka_naziv</option></select>";
		echo "</td>\n";
	}
	else if($zemlja_nastanka_id != 199)
	{
		$sql_zemlja_nastanka_naziv = "SELECT * FROM sifarnici.zemlje_drzave WHERE id=$zemlja_nastanka_id ";
		$rezultat_zemlja_nastanka_naziv = pg_query($conn,$sql_zemlja_nastanka_naziv);
		$niz_zemlja_nastanka_naziv = pg_fetch_assoc($rezultat_zemlja_nastanka_naziv);
		$zemlja_nastanka_naziv = $niz_zemlja_nastanka_naziv['naziv'];
		
		echo "<td align=\"right\">\n";
		echo "Zemlja nastanka:</td>\n";
		echo "<td  align=\"left\">";
		echo "<select name='zemlja_nastanka_id' id='zemlja_nastanka_id' DISABLED><option value='$zemlja_nastanka_id'>$zemlja_nastanka_naziv</option></select>";
		echo "</td>\n";
	}

/*  dodato 2016-12-15 POCETAK */
echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";

echo "<tr id='prethodni'><td colspan=\"6\"><b>PODACI O POVEZANIM REGRESIMA </b></td></tr>\n";

//$display_regresi = ($idregres) ? '' : 'display:none';
/*
	if ($idregres) {
		$sql_povezani_regresi = "WITH pocetni_regres_poz_id AS (
    SELECT idregres, idstete
    FROM steta_regres
    WHERE idregres = $idregres
    ORDER BY idregres DESC
), 
pocetni_regres_oz_id AS (
    SELECT idregres, odstetni_zahtev_id
    FROM predmet_odstetnog_zahteva AS poz
        INNER JOIN pocetni_regres_poz_id
            ON poz.id = pocetni_regres_poz_id.idstete
), 
pocetni_regres_sd_id AS (
    SELECT idregres, stetni_dogadjaj_id
    FROM odstetni_zahtev AS oz
        INNER JOIN pocetni_regres_oz_id
            ON oz.id = pocetni_regres_oz_id.odstetni_zahtev_id
), 
vezani_regresi_oz_id AS (
    SELECT idregres, id
    FROM odstetni_zahtev AS oz
    INNER JOIN pocetni_regres_sd_id
        ON oz.stetni_dogadjaj_id = pocetni_regres_sd_id.stetni_dogadjaj_id
), 
vezani_regresi_poz_id AS (
    SELECT idregres, poz.id, poz.novi_broj_predmeta
    FROM predmet_odstetnog_zahteva AS poz
        INNER JOIN vezani_regresi_oz_id
            ON poz.odstetni_zahtev_id = vezani_regresi_oz_id.id
),
tabela_advokat AS
(
SELECT ak.naziv,adv.datumang,adv.datum_razduzenja FROM pocetni_regres_poz_id AS pocetni
LEFT OUTER JOIN  advokat AS adv
ON adv.idregres=pocetni.idregres
LEFT OUTER JOIN dblink('host=localhost dbname=amso user=zoranp', 'SELECT id, naziv FROM sifarnici.advokatske_kancelarije ') AS ak (id integer, naziv text) ON ak.id = adv.advokatska_kancelarija_id
)
SELECT DISTINCT ON (sr.idregres)
vezani_regresi_poz_id.idregres AS pocetni_regres, sr.idregres AS vezani_regres, vezani_regresi_poz_id.novi_broj_predmeta, 
reg.brreg, reg.potrazivanje, tabela_advokat.naziv, tabela_advokat.datumang, tabela_advokat.datum_razduzenja
FROM tabela_advokat, steta_regres AS sr
    INNER JOIN vezani_regresi_poz_id
        ON sr.idstete = vezani_regresi_poz_id.id
INNER JOIN regresna AS reg
ON sr.idregres = reg.idregres
            AND vezani_regresi_poz_id.idregres <> sr.idregres
ORDER BY sr.idregres DESC,  tabela_advokat.datum_razduzenja DESC, tabela_advokat.datumang DESC  ";
		
		$sql_povezani_regresi ="
-- sve predmete koje se nalaze na ovom regresu, sve povezne idstete za dati rerges
WITH pocetni_regres_poz_id AS (
    SELECT idregres, idstete
    FROM steta_regres
    WHERE idregres = $idregres
    ORDER BY idregres DESC
), 
-- za sve povezane idstete iz prethodnog upita nadji sve odsttene na kojima se nalaze
pocetni_regres_oz_id AS (
    SELECT idregres, odstetni_zahtev_id
    FROM predmet_odstetnog_zahteva AS poz
        INNER JOIN pocetni_regres_poz_id
            ON poz.id = pocetni_regres_poz_id.idstete
), 
-- za sve odstetne zahteve iz prethodnog upita nadji sve steten dogadjaje
pocetni_regres_sd_id AS (
    SELECT idregres, stetni_dogadjaj_id
    FROM odstetni_zahtev AS oz
        INNER JOIN pocetni_regres_oz_id
            ON oz.id = pocetni_regres_oz_id.odstetni_zahtev_id
), 
-- za pronadjen stetni dogadjaj nadji sve odstetene zahteve koji se nalaze na njemu
vezani_regresi_oz_id AS (
    SELECT idregres, id
    FROM odstetni_zahtev AS oz
    INNER JOIN pocetni_regres_sd_id
        ON oz.stetni_dogadjaj_id = pocetni_regres_sd_id.stetni_dogadjaj_id
), 
-- za sve pronadjene oz u prethodnomm upitu nadji predmete koji ser nalaze na njemu
vezani_regresi_poz_id AS (
    SELECT idregres, poz.id, poz.novi_broj_predmeta
    FROM predmet_odstetnog_zahteva AS poz
        INNER JOIN vezani_regresi_oz_id
            ON poz.odstetni_zahtev_id = vezani_regresi_oz_id.id
),
tabela_advokat AS
(
SELECT adv.advokatska_kancelarija_id AS advokatska_kancelarija_id, adv.datumang AS datumang, adv.datum_razduzenja AS datum_razduzenja, adv.idregres AS advokat_regresa, ak.naziv AS advokatska_kancelarija FROM steta_regres AS vrpi
LEFT JOIN advokat AS adv
ON vrpi.idregres=adv.idregres
LEFT OUTER JOIN dblink('host=localhost dbname=amso user=zoranp', 'SELECT id, naziv FROM sifarnici.advokatske_kancelarije ') AS ak (id integer, naziv text) 
ON ak.id = adv.advokatska_kancelarija_id
)
SELECT DISTINCT ON (sr.idregres)
vezani_regresi_poz_id.idregres AS pocetni_regres, sr.idregres AS vezani_regres, vezani_regresi_poz_id.novi_broj_predmeta, 
reg.brreg, reg.potrazivanje, tabela_advokat.advokatska_kancelarija_id, tabela_advokat.datumang, tabela_advokat.datum_razduzenja, tabela_advokat.advokatska_kancelarija
FROM steta_regres AS sr
    INNER JOIN vezani_regresi_poz_id
        ON sr.idstete = vezani_regresi_poz_id.id
INNER JOIN regresna AS reg
ON sr.idregres = reg.idregres
LEFT OUTER  JOIN tabela_advokat AS tabela_advokat
ON tabela_advokat.advokat_regresa=sr.idregres
            WHERE  vezani_regresi_poz_id.idregres <> sr.idregres
ORDER BY sr.idregres DESC,  tabela_advokat.datum_razduzenja DESC, tabela_advokat.datumang DESC
";
		
		
		
		
	$upit_povezani_regresi = pg_query($conn,$sql_povezani_regresi);
	$niz_povezani_regresi = pg_fetch_all($upit_povezani_regresi);
	if($niz_povezani_regresi)
	{
		echo "<tr>";
		echo "<td colspan=\"6\" align='center'>";
		echo "<table border='2' style='border-collapse: collapse;'>";
		echo "<tr style='background-color: #DDEEEE;'>";
		echo "<td align='center'><b>Povezani regres</b></td>";
		echo "<td align='center'><b>Potrazuje</b></td>";
		echo "<td align='center'><b>Advokatska kancelarija</b></td>";
		echo "</tr>";
		for($k=0; $k<count($niz_povezani_regresi); $k++)
		{
			$boja_reda_regesa= ($k%2==0) ? '': '#BBCCCC';
			$vezani_regres = $niz_povezani_regresi[$k]['vezani_regres'];
			$broj_vezani_regres = $niz_povezani_regresi[$k]['brreg'];
			$potrazivanje_vezani_regres = $niz_povezani_regresi[$k]['potrazivanje'];
			$advokat_vezani_regres = $niz_povezani_regresi[$k]['naziv'];
			echo "<tr style='background-color:$boja_reda_regesa;'>";
			
			echo "<td width='250px;' align='center'><a href='regresna.php?idregres=$vezani_regres&&status=izmeni'>$broj_vezani_regres</a></td>";
			echo "<td width='250px;' align='center'>$potrazivanje_vezani_regres</td>";
			echo "<td width='250px;' align='center'>$advokat_vezani_regres</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</td>";
		echo "</tr>";
	}
}
else
{*/
	echo "<tr id='podaci_o_povezanim_regresima' name='podaci_o_povezanim_regresima'></tr>";
//}

/*  dodato 2016-12-15 KRAJ */

echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";

echo "<tr><td colspan=\"6\"><b>PODACI O REGRESNOM DUŽNIKU</b></td></tr>\n";

echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

// dodato 2015-12-21
//$status_izmeni_readonly = ($status == 'izmeni') ? "readonly='readonly' class='readonlyPolja'" : "";

//PREMESTIO VLADA
$readonly = "readonly='readonly' class='readonlyPolja'";

echo "<tr>";
//DODAO VLADA
echo "<td align=\"right\" style='display:inline;'>\n";
echo "Tip lica: &nbsp;";
echo "</td>";
echo "<td align=\"left\" style='display:inline;'>\n";
echo "<label>Fizičko</label>";
echo "&nbsp;<input type=\"radio\" name=\"fizpra\" value=\"F\" id='fizicko'";
echo " disabled ";
//if($fizpra=="F" ) { echo " checked " ; }
if($dokument)     { echo " disabled "; }
echo "onkeypress=\"return handleEnter(this, event)\" onclick=\"proveriJmbgPib(this.value)\">\n";
echo "&nbsp;";

echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<label>Pravno</label>";
echo "<input type=\"radio\" name=\"fizpra\" value=\"P\" id='pravno' ";
echo " disabled ";
//if($fizpra=="P") { echo " checked "; }
if($dokument)   {  echo " disabled "; }
echo "onkeypress=\"return handlerEnter(this,event)\" onclick=\"proveriJmbgPib(this.value)\">\n";
echo "&nbsp;";

//DODAO VLADA
$disabled = 'disabled';


$regres_od_lice = array(0 => 'Izaberite', 1=>'Krivac vlasnik vozila', 2 => 'Krivac vozač vozila', 3 => 'Osiguravajuće društvo', 4 => 'Ostalo');
echo "<td align=\"right\">Regres od </td>";

echo "<td align=\"left\"><select id='regres_od' name='regres_od' $disabled>";
foreach ($regres_od_lice as $reges_lice)
{
	echo "<option value='$reges_lice'>$reges_lice</option>";
}
echo "</select></td>";	

// dodato 2015-12-21
echo "</tr>";

echo "<tr style='display: inline-block;margin-bottom: 7px;'></tr>";

echo "<tr><td align=\"right\" style='display:inline;'>\n";
echo "JMBG/PIB:</td>\n";
//DODAO VLADA READONLY
echo "<td  align=\"left\" style='display:inline'><input name=\"jmbg_reg\" id=\"jmbg_reg\" $readonly onkeypress=\"return samoBrojevi(this,event);\" onkeyup=\"proveraPibaOS(this.value);\" onblur=\"proveriJmbg();\" value=\"$jmbg_reg\" size=\"20\" height=\"15\" maxlength=\"13\" "; 
echo "  onkeypress=\"return handleEnter(this, event); return samoBrojevi(this,event);\">\n";
echo "</td>\n";

//DODAO VLADA
echo "<td align=\"right\">\n";
echo "Ime/Naziv:</td>\n";

//DODAO VLADA READONLY
echo "<td align=\"left\"><input name=\"ime_reg\" id=\"ime_reg\" value=\"$ime_reg\" size=\"15\" $readonly height=\"15\" onkeypress=\"return handleEnter(this, event)\"  autocomplete='off'>\n";
echo "</td>";

echo "<td align=\"right\">\n";
echo "Prezime:";
echo "<input name=\"prezime_reg\" id=\"prezime_reg\" value=\"$prezime_reg\" $readonly size=\"20\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";

			
// 2015-12-16	
echo "<td align=\"right\">\n";
echo "Telefon:</td>\n";
echo "<td  align=\"left\"><input name=\"telefon_reg\" value=\"$telefon_reg\" $readonly id='telefon_reg' size=\"15\" height=\"15\" onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>";		
echo "</tr>\n";

echo "<tr><td align=\"right\" style='display:inline;'>\n";
echo "Zemlja:</td>\n";

// dodato 2015-12-17
$sql_zemlje = "SELECT id, naziv FROM sifarnici.zemlje_drzave ORDER BY id";
$result_zemlje = pg_query($conn, $sql_zemlje);

//VLADA DODAO DISABLED
echo "<td align='left' style='display:inline; margin-left:15px;'><select name='zemlja_reg_id' id='zemlja_reg_id' style='width:200px;' $disabled onChange='postavi_polja_po_zemlji(value);'><option value='-1'>Izaberite zemlju</option>";
while ($arr_zemlje = pg_fetch_assoc($result_zemlje)) {
	echo "<option value=\"" . $arr_zemlje['id'] . "\"";

	if($arr_zemlje['id']==$zemlja_reg_id)
	{
		echo "selected";
	}

	echo ">" . $arr_zemlje['naziv'] . "\n";
}
echo "</select>";
echo "</td>\n";

$sql_sve_opstine = "SELECT id, naziv FROM sifarnici.opstina_aktivne ORDER BY id";
$result_sve_opstine  = pg_query($conn1, $sql_sve_opstine);

$rezultat_opstina_po_mestu = $sifarnici_class->vratiOpstinuPoMestu($mesto_reg_id);
$opstina_sifarnik = $rezultat_opstina_po_mestu['id'];


//DODAO VLADA
echo "<td align=\"right\" id='label_opstina'>\n";
echo "Opština: ";
echo "</td>";

echo "<td align=\"left\" id='opstina_regresnog_duznika' $status_izmeni_hidden_om>\n";

//VLADA DODAO DISABLED
echo "<select name='opstina_reg_id' id='opstina_reg_id'  onchange='vrati_mesta(this.value,id)' $disabled style='width:200px;' ><option value='-1'>Izaberite opstinu</option>";
while ($arr_opstina = pg_fetch_assoc($result_sve_opstine)) {
	echo "<option value=\"" . $arr_opstina['id'] . "\"";
	
	if($arr_opstina['id'] == $opstina_sifarnik)
	{
		echo "selected ";
	}

	echo ">" . $arr_opstina['naziv'] . "</option>\n";
}
echo "</select>";
echo "</td>\n";

// dodato 2016-01-27
$sql_mesto_reg = "SELECT * FROM sifarnici.mesto_aktivna WHERE id=$mesto_reg_id";
$result_mesto_reg = pg_query($conn1, $sql_mesto_reg);
$arr_mesto_reg = pg_fetch_assoc($result_mesto_reg);

echo "<td align=\"right\" id='mesto_regresnog_duznika' $status_izmeni_hidden_om>\n";
echo "Mesto:";

//VLADA DODAO DISABLED I IZMENIO
echo "<select name='mesto_reg_id' id='mesto_reg_id' style='width:190px;' $disabled>";
echo "<option value='-1'>Izaberite mesto</option>";

if($mesto_reg_id)
{
	echo "<option value='$mesto_reg_id' selected>" .  $arr_mesto_reg['naziv'] . "</option>";
}

echo "</select></td>\n";


echo "<td align=\"right\">\n";
echo "Adresa:</td>\n";
echo "<td  align=\"left\"><input name=\"adresa_reg\" id=\"adresa_reg\" value=\"$adresa_reg\" size=\"20\" height=\"15\" $readonly onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";

echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";

echo "<tr><td colspan=\"6\"><b>POTRAŽIVANJE</b></td></tr>\n";

echo "<tr><td align=\"right\">\n";
echo "Isplaćeno po odštetnom zahtevu:</td>\n";

//VLADA DODAO READONLY
echo "<td  align=\"left\"><input name=\"iznosisp\" id=\"iznosisp\" value=\"$iznosisp\" size=\"15\" $readonly height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";

echo "<td align=\"right\">\n";
echo "Datum isplate:</td>\n";

//VLADA DODAO READONLY
echo "<td align=\"left\"><input name=\"datumisp\" id=\"datumisp\" value=\"$datumisp\" size=\"15\" $readonly height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td></tr>\n";

echo "<tr><td align=\"right\">\n";
echo "Status regresa:</td>\n";
// dodato da ukoliko je upisan plan otplate da ne moze da se izabere status - otpisan po predlogu komisiji
$zabrani_izbor_opcije = ($dokument=='0') ? '': 'disabled' ;

echo "<td  align=\"left\">\n";
if($odluka == '')
{
	$odluka_ispis = 'Postupak u toku';
}
else if($odluka == 'ORZ')
{
	$odluka_ispis = 'Okončan reg.zahtev';
}
else if($odluka == 'OT')
{
	$odluka_ispis = 'Otpisan po predlogu komisije - ne postoji osnov za regresno potraživanje';
}
echo "<select name=\"odluka\" onChange='proveri_status_regresa(value);' style='width:190px;' title='$odluka_ispis' >\n";

echo "<option ";
if ($odluka == '') { echo "selected "; }
echo "value=\"\">Postupak u toku</option>";

echo "<option ";
if ($odluka == 'ORZ') { echo "selected "; }
echo "value=\"ORZ\">Okončan reg.zahtev</option>";

echo "<option ";
if ($odluka == 'OT') { echo "selected "; }
echo "value=\"OT\" $zabrani_izbor_opcije>Otpisan po predlogu komisije - ne postoji osnov za regresno potraživanje</option>";

echo "</select></td>";

echo "<td align=\"right\" >\n";
echo "Kamata:</td>\n";
echo "<td  align=\"left\"><input name=\"kamata\" value=\"$kamata\" size=\"15\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td></tr>\n";

echo "<tr><td>&nbsp;</td></tr>\n";
echo "<td align=\"right\">\n";
echo "Visina potraživanja:</td>\n";

//DODAO VLADA ID
echo "<td align=\"left\"><input name=\"potrazivanje\" id=\"potrazivanje\" value=\"$potrazivanje\" size=\"15\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";
echo "<tr><td>&nbsp;</td></tr>\n";

if($status == 'izmeni')
{
	

echo "<tr>";
echo "<td colspan='6'>";
//dugme za prikazivanje diva
if(!$dokument && $odluka != 'OT')
	echo "<input type='button' name='dugme' value='Upiši plan otplate' za knjiženje' id='dugme'  style=' display:block; float: left; padding:10px; margin-left:40px;' onClick=\"pokaziDiv();\" />";

echo "<div id='div_knjizenje' >";

echo "<label for='dokument' style='padding-left:9px;'>Dokument:</label>";
echo "<select name=\"dokument\" id=\"dokument\" >\n";
//1
$vrednosti = array('0' => 'Izaberite vrstu dokumenta', 'PO' => 'Poravnanje', 'VSP' => 'Vansudski sporazum', 'SO' => 'Sudska odluka', 'SIO' => 'Sudska izvršna odluka', 'SDOD' => 'Saglasnost drugog O. Društva', 'PRR' => 'Prihvaćeno rešenje o regresu');
foreach ($vrednosti as $kljuc => $vrednost) {
	echo "<option value=\"$kljuc\"";
	if ($dokument == $kljuc) {
		echo " selected";
	}
	echo ">$vrednost</option>\n";
}
echo "</select>";

echo "<label for='dokument_datum' style='padding-left:5px; width:100px;'>Datum dokumenta:</label>";
echo "<input type='text' name='dokument_datum' id='dokument_datum' value='' onblur='return validanDatum(this.value,this);'/>";
echo "<label for='dokument_broj'  style='width:50px; padding-left:5px;'>Broj dokumenta:</label>";
echo "<input type='text' name='dokument_broj' id='dokument_broj' value='' /><br>";
echo "<table id='tabela_rate'>";
echo "<tbody><tr>";
echo "<td>";
echo "<label for='broj_rata' style='padding:5px; '>Broj rata:</label>";
echo "</td>";
echo "<td>";
echo "<input type='text' name='broj_rata' id='broj_rata' value='' onkeypress='return samoBrojevi(this,event);' style='margin-left:10px;width:50px;'/>";
echo "</td>";
echo "<td>";
echo "<input type='button' name='dodaj_rate' id='dodaj_rate' value='Dodaj rate'  />";
echo "</td>";
echo "<td>
    	  <input type=\"button\" name=\"snimi_rate\" id=\"snimi_rate\"  style=' display:block; float: right; padding:10px; margin-left:170px;' value=\"Knjiži rate\" onClick=\"klik();\" />
      </td>";
echo "</tr></tbody>";
echo "</table>";
echo "</div>";
echo "</td>";
echo "</tr>";

echo "<tr>";

if($dokument)
{
	echo"<td align=\"right\">";
	echo "Vrsta dokumenta:";
	echo"</td>";
	switch($dokument)
	{
		case 'PO':
		{
			echo "<td><input type=\"text\" readonly=\"readonly\" value=\"Poravnanje\" class=\"readonlyPolja\"/></td>";
			break;
		}
		case 'VSP':
			{
				echo "<td><input type=\"text\" readonly=\"readonly\" value=\"Vansudski sporazum\" class=\"readonlyPolja\"/></td>";
				break;
			}

		case 'SO':
			{
				echo "<td><input type=\"text\" readonly=\"readonly\" value=\"Sudska odluka\" class=\"readonlyPolja\"/></td>";
				break;
			}

		case 'SIO':
			{
				echo "<td><input type=\"text\" readonly=\"readonly\" value=\"Sudska izvršna odluka\" class=\"readonlyPolja\"/></td>";
				break;
			}

		case 'SDOD':
			{
				echo "<td><input type=\"text\" readonly=\"readonly\" value=\"Saglasnost drugog O.Društva\" class=\"readonlyPolja\" style='margin-left:4px;width:200px;height:23px;' /></td>";
				break;
			}
		case 'PP':
			{
				echo "<td><input type=\"text\" readonly=\"readonly\" value=\"Proknjiženo ranije\" class=\"readonlyPolja\"  /></td>";
				break;
			}
		case 'PRR':
			{
				echo "<td><input type=\"text\" readonly=\"readonly\" value=\"Prihvaćeno rešenje o regresu\" class=\"readonlyPolja\" style='margin-left:4px;width:200px;height:23px;' /></td>";
				break;
			}
	}
	echo"<td align=\"right\">";
	echo "Broj dokumenta:";
	echo"</td>";
	echo "<td><input type=\"text\" readonly=\"readonly\" value=\"$dokument_broj\" class=\"readonlyPolja\" /></td>";

	echo"<td align=\"right\">";
	echo "Datum dokumenta:";
	echo"</td>";
	echo "<td><input type=\"text\" readonly=\"readonly\" value=\"$dokument_datum\" class=\"readonlyPolja\" /></td>";

}
echo "</tr>";
}// kraj IF za status izmenjen


echo "<tr><td>&nbsp;</td></tr>\n";

echo "<tr><td align=\"center\" valign=\"middle\" colspan=\"2\">\n";
echo "<strong>\n";
echo "ODLUKA O ISKNJIŽENJU\n";
echo "</strong>\n";
echo "</td>\n";

echo "<td colspan=\"2\">&nbsp;</td>\n";
echo "<td align=\"center\" valign=\"middle\" colspan=\"2\">\n";
echo "<strong>\n";
echo "KRATAK OPIS PREDMETA\n";
echo "</strong>\n";
echo "</td></tr>\n";

echo "<tr><td colspan=\"2\" align=\"center\" valign=\"middle\"><textarea rows=\"3\" cols=\"50\" name=\"odlukaisk\" >\n";
echo "$odlukaisk</textarea>\n";
echo "</td>\n";

echo "<td align=\"right\">\n";
echo "Datum isknjiženja:</td>\n";
echo "<td  align=\"left\"><input name=\"datumisk\" id=\"datumisk\"  value=\"$datumisk\" size=\"15\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";

echo "<td colspan=\"2\" align=\"center\" valign=\"middle\"><textarea rows=\"3\" cols=\"50\" name=\"opis\" >\n";
echo "$opis</textarea>\n";
echo "</td></tr>\n";

echo "<tr><td>&nbsp;</td></tr>\n";

$uliniji = 1;

echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";
echo "<tr><td colspan=\"6\" align=\"left\"><b>REDOVNA NAPLATA REGRESA (GRUPA RAČUNA 212)</b></td></tr>\n";
echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";
  
echo "<tr><td colspan=\"6\">\n";

echo "<div id=\"vidi_regres\" style=\"width:100%;\">\n";

require_once('vidi_rate_regres.php');
echo "\n</div>\n";
echo "</td></tr>\n";
//}

/*-------------------------------------*/
/* BANE START, 24.09.2019 - sudski naplata regresa */

$konta_sudska_naplata_regresa = "'21821210', '21821203'";
$j_snr = 2019;  //  kad god da je otvoren regresni zahtev ($datum_upisa) prva godina kada su formirana posebna konta za potrazivanja po osnovu naplate sudskim putem je 2019.!
$k_snr = date('Y') + 1;

$upit_sudska_naplata_regresa = "
  SELECT datknjiz AS dospeva, opisdok, brojdok, konto, duguje, potrazuje
  FROM g" . $j_snr . "
  WHERE konto IN ($konta_sudska_naplata_regresa) AND extract(year FROM datknjiz) = " . $j_snr++ . " AND vrstadok NOT IN ('PS', 'ZK')
";
for ($i_snr = $j_snr; $i_snr < $k_snr; $i_snr++) {

	//DODAO VLADA,RADI TESTIRANJA - NA LOKALU NE POSTOJI TABELA G2021
	//if($i_snr != '2021') {

		$upit_sudska_naplata_regresa .= " UNION ALL SELECT  CASE WHEN duguje notnull THEN dospeva ELSE datknjiz END AS dospeva, opisdok, brojdok, konto, duguje, potrazuje FROM g" . $i_snr . " WHERE konto IN ($konta_sudska_naplata_regresa) AND extract(year FROM datknjiz) = $i_snr AND vrstadok NOT IN ('PS', 'ZK')";

	//}
}

//  echo "$upit_sudska_naplata_regresa";

$i = 1;
preg_match('/R\-(\d+)\/(\d+)/', $brReg, $matches);
$upit_sudska_naplata_regresa_zajedno = "
  SELECT
    to_char(dospeva, 'DD.MM.YYYY.') AS datnaloga,
    dospeva,
    rzahtev,
    opisdok,
    dugovni,
    potrazni,
    saldo,
    ispisi
  FROM
  (
    SELECT
      dospeva,
      substring(brojdok FROM E'(R\\\\-\\\\d+\\\\/\\\\d+)') AS rzahtev,
      opisdok,
      duguje AS dugovni,
      sum(duguje) OVER (PARTITION BY konto, dospeva) AS treba,
      potrazuje AS potrazni,
      sum(duguje-potrazuje) OVER (ORDER BY dospeva) AS saldo,
      CASE WHEN dospeva > current_date THEN 0 ELSE 1 END AS ispisi
    FROM
      ($upit_sudska_naplata_regresa) AS bar
    WHERE
      substring(brojdok FROM E'R\\\\-(\\\\d+)\\\\/')::integer = " . $matches[1] . "
      AND substring(brojdok FROM E'\\\\/(\\\\d+)')::integer = " . $matches[2] . "
  ) AS foo
  WHERE
    treba + potrazni != 0
  ORDER BY
    dospeva, dugovni DESC
";

//  echo $upit_sudska_naplata_regresa_zajedno . "\n";
$rezultat_sudska_naplata_regresa_zajedno = pg_query($conn1, $upit_sudska_naplata_regresa_zajedno);

//  Ako uopste ima taksi naplacenih sudksim putem, za regresni predmet
if(pg_num_rows($rezultat_sudska_naplata_regresa_zajedno) > 0) {
  echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";
  echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";
  echo "<tr><td colspan=\"6\" align=\"left\"><b>SUDSKA NAPLATA REGRESA - TROŠKOVI POSTUPKA (GRUPA RAČUNA 218)</b></td></tr>\n";
  echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

  echo "<tr><td colspan=\"6\">\n";
  echo "<table width=\"1000px\" border=\"2\" cellspacing=\"0\" align=\"center\">";
  echo "<tr bgcolor=\"#66FF66\">";
  echo "<td align=\"center\"><b>Red.br.</b></td>\n";
  echo "<td align=\"center\"><b>Dospeva</b></td>\n";
  echo "<td align=\"center\"><b>Opis dokumenta</b></td>\n";
  echo "<td align=\"center\"><b>Zaduženje</b></td>\n";
  echo "<td align=\"center\"><b>Uplata</b></td>\n";
  echo "<td align=\"center\"><b>Stanje duga na dan</b></td>\n";
  echo "</tr>\n";

  $style = '#B3FFB3';
  while ($arr_sudska_naplata_regresa_zajedno = pg_fetch_assoc($rezultat_sudska_naplata_regresa_zajedno)) {
    
    foreach ($arr_sudska_naplata_regresa_zajedno as $kljuc_sudska_naplata_regresa_zajedno => $vrednost_sudska_naplata_regresa_zajedno) {
      ${$kljuc_sudska_naplata_regresa_zajedno} = $vrednost_sudska_naplata_regresa_zajedno;
    }
    
    $dugovni_sudska_naplata_regresa_zajedno = number_format($dugovni_sudska_naplata_regresa_zajedno, 2, ',', '.');
    $potrazni_sudska_naplata_regresa_zajedno = number_format($potrazni_sudska_naplata_regresa_zajedno, 2, ',', '.');
    $saldo_sudska_naplata_regresa_zajedno = $ispisi_sudska_naplata_regresa_zajedno ? number_format($saldo_sudska_naplata_regresa_zajedno, 2, ',', '.') : '&nbsp;';
    $style = $style == '#F2F4F9' ? '#B3FFB3' : '#F2F4F9';
    echo "<tr bgcolor=\"$style\" onMouseOver='this.style.backgroundColor=\"#FFCD9F\"' onMouseOut='this.style.backgroundColor=\"$style\";'>";
    echo <<<EOF
    <td align="center">$i</td>
    <td align="center">$datnaloga</td>
    <td>$opisdok</td>
    <td align="right">$dugovni</td>
    <td align="right">$potrazni</td>
    <td align="right">$saldo</td>
    </tr>
EOF;
  $i++;
  }

  echo "</table>\n";
  echo "</td></tr>\n";

} //  end uopste ima rezultata za naplatu taksi sudskim putem

/* BANE END -  sudski naplata regresa */
/*-------------------------------------*/


//  PROCENA IZVESNOSTI NAPLATE
echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

//DODAO VLADA
if(!$idregres) {

	$upit_id_regresa = "SELECT idregres FROM steta_regres WHERE idstete = $idstete";
	$rezultat_id_regresa = pg_query($conn,$upit_id_regresa);

	$niz_id_regres = pg_fetch_array($rezultat_id_regresa);

	$idregres = $niz_id_regres['idregres'];

}

	
// 2015-12-16 
//if($status == 'izmeni')
//{
	echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";

	echo "<tr><td colspan=\"6\" align=\"left\"><b>PROCENA IZVESNOSTI NAPLATE</b></td></tr>\n";

	if (!$datum1) $datum1='';

	//DODAO VLADA
	if($idregres) {

		$upit = "SELECT * from procena_regres where idregres=$idregres order by id desc";
		$rezultat = pg_query($conn, $upit);
		$red = pg_num_rows($rezultat);

	}

	$style = '#DDEEEE';

		$uliniji1 = 1;
	echo "<tr><td colspan='6'>\n";
	echo "<div id=\"procena\" style=\"width:100%;\">\n";

	//DODAO VLADA
	if($idregres) {

		require_once('procena_regresa.php');
	}

	echo "\n</div>\n";
	echo "</td></tr>\n";
	echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

	//VIDETI DA LI TREBA OVO SAKRITI - VLADA
	echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "<td colspan='6'>";
	echo "<div id='div_procena' >";

	echo "<label for='procena_datum' style='padding-left:5px; width:100px;'>Datum procene:</label>";
	echo "<input type='text' name='procena_datum' id='procena_datum' value='' onblur='return validanDatum(this.value,this);'/>";

	echo "<label for='procena_iznos'  style='width:50px; padding-left:5px;'>Iznos procene:</label>";
	echo "<input type='text' name='procena_iznos' id='procena_iznos' value='' onkeypress='return samoBrojevi(this,event);'/>";

	echo "<label for='koeficijent' style='padding-left:9px;'>Koeficijent izvesnosti:</label>";

	echo "<input type=\"range\" readonly=\"readonly\" id=\"koeficijent\" name=\"koeficijent\" value=\"0\" style='width:50px;height:23px;font-weight:bold;' onkeypress='return samoBrojevi(this,event);'/>";
	echo "<input type=\"button\"  value=\" + \" onClick=\"keoficijentPovecaj();\"  style='font-size:15px;margin:0;padding::9px;width:25px;height:25px';\">";
	echo "<input type=\"button\" value=\" - \" onClick=\"keoficijentSmanji();\" style='font-size:15px;margin:0;padding::9px;width:25px;height:25px';\">";
	echo "<input type=\"button\" name=\"snimi_procenu\" id=\"snimi_procenu\"  style=' display:block; float: right; padding-left::5px;width:100px;height:35px; margin-left:60px;'   value=\"Snimi procenu\" onClick=\"klikp();\"/>";

	echo "</div>";
	echo "</td>";

	echo "</tr>";

//}
echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";

echo "<tr><td colspan=\"6\" align=\"left\"><b>SUD</b></td></tr>\n";

echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

//if($status == 'izmeni')
//{
	
	$sql1 = "select  idadv, sudska_jedinica_id as sud, advokatska_kancelarija_id AS advokatska_kancelarija_id, datumang,datum_razduzenja from advokat where idregres=$idregres order by idadv desc";
	$result=pg_query($conn, $sql1);

	$niz_advokati = pg_fetch_all($result);
	
	if($niz_advokati)
	{
		echo "<tr><td align=\"left\" colspan=\"5\"><table width=\"70%\" border=\"1\" cellspacing=\"0\" >";
		echo "<tr bgcolor=\"#DDEEEE\"><td align=\"center\" width=\"5%\"><b>R.br</b></td>\n";
		echo "<td align=\"center\" width=\"35%\"><b>Sud</b></td>\n";
		echo "<td align=\"center\" width=\"40%\"><b>Advokatska kancelarija</b></td>\n";
		echo "<td align=\"center\"><b>Datum angažovanja</b></td>\n";
		echo "<td align=\"center\"><b>Datum razduženja</b></td>\n";
		echo "</tr>\n";
		
		for ($i=0; $i < count($niz_advokati); $i++) 
		{
			$style = $style == '#F2F4F9' ? '#DDEEEE' : '#F2F4F9';
			echo "<tr bgcolor=\"$style\">";
			
			$idadv = $niz_advokati[$i]['idadv'];
			$sud_id= $niz_advokati[$i]['sud'];
			$advokatska_kancelarija_id= $niz_advokati[$i]['advokatska_kancelarija_id'];
			$datum_angazovanja = $niz_advokati[$i]['datumang'];
			$datum_razduzenja = $niz_advokati[$i]['datum_razduzenja'];
			
			if($idadv)
			{
				echo "<td align=\"center\" >";
				echo "<a href=\"advokatska_kancelarija.php?idregres=" . $idregres . "&idadv=" . $idadv .  "&akcija=izmeni \">\n";
				echo $i+1 . "</a></td>\n";
			}
			
			if($sud_id)
			{
				$sql_prikazi_podatke_sud = "SELECT
										sj.id as id, sj.naziv as naziv, sj.sudska_jedinica as sudska_jedinica, s.naziv as sud_naziv
										FROM
										sifarnici.sudovi AS s
										INNER JOIN sifarnici.sudske_jedinice AS sj ON (s.id=sj.sud_id)
										WHERE s.aktivan='novi' AND sj.id=$sud_id";
				$result_prikazi_podatke_sud= pg_query($conn1, $sql_prikazi_podatke_sud);
				$niz_prikazi_podatke_sud= pg_fetch_assoc($result_prikazi_podatke_sud);
			
				echo "<td align=\"center\" >";
				
				if($niz_prikazi_podatke_sud['sudska_jedinica'] == 't')
				{
					echo $niz_prikazi_podatke_sud['sud_naziv'] . ' - ' . $niz_prikazi_podatke_sud['naziv'];
				}
				else 
				{
					echo $niz_prikazi_podatke_sud['sud_naziv'];
				}
				echo "</td>\n";
			}
			else
			{
				echo "<td align=\"center\" >";
				echo "&nbsp;";
				echo "</td>\n";
			}
			
			if($advokatska_kancelarija_id)
			{
				$sql_prikazi_podatke_ak = "SELECT * FROM sifarnici.advokatske_kancelarije WHERE id = $advokatska_kancelarija_id";
				$result_prikazi_podatke_ak = pg_query($conn1, $sql_prikazi_podatke_ak);
				$niz_prikazi_podatke_ak = pg_fetch_assoc($result_prikazi_podatke_ak);
				
				echo "<td align=\"center\" >";
				echo $niz_prikazi_podatke_ak['naziv'];
				echo "</td>\n";
			}
			else 
			{
				echo "<td align=\"center\" >";
				echo "&nbsp;";
				echo "</td>\n";
			}
			
			if($datum_angazovanja)
			{
				echo "<td align=\"left\"> ";
				echo $datum_angazovanja;
				echo "</td>\n";
			}
			else
			{
				echo "<td align=\"center\" >";
				echo "&nbsp;";
				echo "</td>\n";
			}
			
			if($datum_razduzenja)
			{
				echo "<td align=\"left\"> ";
				echo $datum_razduzenja;
				echo "</td>\n";
			}
			else
			{
				echo "<td align=\"center\" >";
				echo "&nbsp;";
				echo "</td>\n";
			}
			
			echo "</tr>\n";
		} // kraj dodatog FOR-a za advokate
		
		echo "</table></td>";
		
		echo "<td  align=\"left\" >\n";
		echo "<input type=\"button\" value=\"Advokat\" class=\"button\"  name=\"adv_unos\"  id=\"adv_unos\" onclick='otvori_stranu_adv_kancelarija($idregres);'>\n";
		echo "</td>\n";
		echo "</tr>";
	
	} // kraj unetih advokata
	
 // kraj za if statusa
	else 
	{
		echo "<tr><td  align=\"right\" >";
		echo "Sud:</td>\n";
		echo "<td  align=\"left\">";
		$lista_sudova = $sifarnici_class->vrati_nadleznosti_i_sudove();

		echo "<select name='brSud' id='brSud' style='width:250px;'><option value='-1'>Izaberite</option>";
		for($i=0; $i<count($lista_sudova); $i++)
		{
			$sudovi_po_nadleznosti = $lista_sudova[$i];
			for($j=0; $j<count($sudovi_po_nadleznosti); $j++)
			{
				$sud_id = $sudovi_po_nadleznosti[$j]['id'];
				$sud_naziv = $sudovi_po_nadleznosti[$j]['naziv'];
				$sudska_jedinica = $sudovi_po_nadleznosti[$j]['sudska_jedinica'];
				if($sudska_jedinica=='t')
				{
					$ispis =$sudovi_po_nadleznosti[$j]['sud_naziv']." - ".$sudovi_po_nadleznosti[$j]['naziv'];
				}
				else
				{
					$ispis = $sudovi_po_nadleznosti[$j]['sud_naziv'];
				}
				
				echo "<option value='$sud_id'>$ispis</option>";
			}
		}
		echo "</select>";
		echo "</td>\n";
		
		echo "<td align=\"right\" >\n";
		echo "Advokat:</td>\n";
		echo "<td  align=\"left\" >";
		$lista_advokatske_kancelarije = $sifarnici_class->vrati_sve_advokatske_kancelarije();

		echo "<select name='advokat' id='advokat'><option value='-1'>Izaberite</option>";
		for($i=0; $i<count($lista_advokatske_kancelarije); $i++)
		{
			$kancelarija_id = $lista_advokatske_kancelarije[$i]['id'];
			$kancelarija_naziv = $lista_advokatske_kancelarije[$i]['naziv'];
			$kancelarija_status = $lista_advokatske_kancelarije[$i]['status'];
			$status_kancelarija = ($kancelarija_status == 'N') ? 'disabled' : '';
			echo "<option value='$kancelarija_id' $status_kancelarija>$kancelarija_naziv</option>";
		}
		echo "</select>";
		echo "</td>\n";
		
		
		echo "<td align=\"right\" >\n";
		echo "Datum angažovanja:</td>\n";
		echo "<td  align=\"left\" colspan=\"3\"><input name=\"datumang\" id='datumang' value=\"$datumang\" size=\"15\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
		echo "</td>";
		echo "</tr>";
	}
//}

echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

echo "<tr><td colspan=\"6\"><hr color=\"#000000\"></td></tr>\n";

echo "</table>\n";



echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" >\n";

echo "<tr><td><table width=\"100%\" border=\"0\" cellspacing=\"0\">\n";


echo "<tr><td align=\"right\" >\n";
echo "Datum utuženja:</td>\n";
echo "<td  align=\"left\"><input id=\"datum_utuzenja\" name=\"datum_utuzenja\" value=\"$datum_utuzenja\" size=\"15\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";

echo "<td align=\"right\" >\n";
echo "Datum konačne isplate:</td>\n";
echo "<td  align=\"left\"><input id=\"datum_isplate\" name=\"datum_isplate\" value=\"$datum_isplate\" size=\"15\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";

echo "<tr><td align=\"right\">\n";
echo "Datum poravnanja:</td>\n";
echo "<td align=\"left\"><input id=\"datum_por\" name=\"datum_por\" value=\"$datum_por\" size=\"15\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td></tr><tr height='15px'></tr>\n";
echo "<tr>";


echo "<td align=\"right\">\n";
echo"<label>Postavite skeniranu dokumentaciju</label></td>";
echo " <td><input type='file' id='file' name='file' multiple>";

echo "<input type='button' onclick='upload_fajl($idregres)' value='Postavi fajl'/></td></tr>";
$target_dir = "../../arhiva/regresi/dokumentacija/$idregres/dokument/";



if($idregres)
{
	$putanja="../../arhiva/regresi/dokumentacija/$idregres/dokument/";

	//ZAKOMENTARISAO VLADA - NE RADI NISTA
	//$filecount = scandir($putanja);

	if(count($filecount)>2)
	{

		echo "<tr><td></td><td ><div class='ajax-file-upload-statusbar' style='width: 150px;margin-top:20; border:1px solid #ff0000 ' align='center' >
		<img class='ajax-file-upload-preview' style='width: 100%; height: auto; display: none;' align='center'>
		<div class='ajax-file-upload-filename' align='center' 	>$brReg&nbsp";
		echo"<a href='../../arhiva/regresi/dokumentacija/$idregres/dokument/dokument.pdf' target='_blank'  align='right'>
		<img width='32' height='32' style='height:32!important;width:32!important' src='../../stete/upload_stete/icons/pdf.png'>
		</a>
		</div></div>";
	}
}


echo "</td><td></td></tr>\n";



echo "</table></td>\n";
echo "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\">\n";
echo "<td align=\"center\" valign=\"middle\" >\n";
echo "<strong>\n";
echo "NAPOMENA\n";
echo "</strong>\n";
echo "</td></tr>\n";



echo "<tr><td  align=\"center\" valign=\"middle\" >\n";
echo "&nbsp;\n";
echo "<textarea rows=\"5\" cols=\"50\" name=\"napomena\">\n";
echo "$napomena</textarea>\n";
echo "</td>\n";
echo "</tr>\n";

echo "</table></td></tr>\n";


echo "<tr><td>&nbsp;</td></tr>\n";

echo "<tr><td align=\"right\" >";
echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" id=\"upisi\" value=\"Upiši\" class=\"button\" name=\"submit\"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Zatvori\" class=\"button\" name=\"zatvori\">\n";
echo "</td>\n";
echo "<td align=\"left\">";
echo "&nbsp;&nbsp;&nbsp;<font color=\"#CC0000\">* Radnik je: <b>$radnik</b></font></p></td></tr>\n";

echo "</table>\n";
}


echo "<input type=\"hidden\" name=\"datum_upisa\" id=\"datum_upisa\" value=\"$datum_upisa\">\n";
//echo "<input type=\"hidden\" name=\"idregres\" id=\"idregres\" value=\"$idregres\">\n";
echo "<input type=\"hidden\" name=\"radnik\" id=\"radnik\" value=\"$radnik\">\n";
//echo "<input type=\"hidden\" name=\"idstete\" id=\"idstete\" value=\"$idstete\">\n";

if ($adv_unos){ require "advokatska_kancelarija.php";}


if ($zatvori){

echo "<script type=\"text/javascript\">";
echo "window.close()\n";
//echo "window.open('stete.php','main')\n";
echo "</script>";


}

$nastavi = true;

if ($submit){

//DODAO VLADA - FUNKCIJA ZA PROVERU DATUMA
function provera_datuma($datum) {

	if(preg_match("/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/", $datum)) {

		return true;
	}
}

if ($iznosisp && !preg_match("/^[0-9]{1,14}\.?[0-9]{0,2}$/", $iznosisp)){

	echo "<script language=\"javascript\">\n";
	echo "document.regresna.iznosisp.value='';\n";
	echo "alert(\"Neispravan isplaćeni iznos po odštetnom zahtevu!\")\n";
	echo "document.regresna.iznosisp.focus();\n";
	echo "</script>\n";
	$nastavi = false;
}

if ($kamata && !preg_match("/^[0-9]{1,14}\.?[0-9]{0,2}$/", $kamata)){

	echo "<script language=\"javascript\">\n";
	echo "document.regresna.kamata .value='';\n";
	echo "alert(\"Neispravan format iznosa kamate!\")\n";
	echo "document.regresna.kamata.focus();\n";
	echo "</script>\n";
	$nastavi = false;
}

if ($potrazivanje && !preg_match("/^[0-9]{1,14}\.?[0-9]{0,2}$/", $potrazivanje)){

	echo "<script language=\"javascript\">\n";
	echo "document.regresna.potrazivanje.value='';\n";
	echo "alert(\"Neispravan iznos visine potraživanja!\")\n";
	echo "document.regresna.potrazivanje.focus();\n";
	echo "</script>\n";
	$nastavi = false;
}



//PROVERA DA LI SU DATUMI U ISPRAVNOM FORMATU
if($datum_zahteva && !provera_datuma($datum_zahteva)) {

	echo "<script language=\"javascript\">\n";
	echo "document.regresna.datum_zahteva.value='';\n";
	echo "alert(\"Neispravan datum zahteva!\")\n";
	echo "document.regresna.datum_zahteva.focus();\n";
	echo "</script>\n";
	$nastavi = false;
} 

if($datumisk && !provera_datuma($datumisk)) {

	echo "<script language=\"javascript\">\n";
	echo "document.regresna.datumisk.value='';\n";
	echo "alert(\"Neispravan datum isknjiženja!\")\n";
	echo "document.regresna.datumisk.focus();\n";
	echo "</script>\n";
	$nastavi = false;
} 

if($datum_utuzenja && !provera_datuma($datum_utuzenja)) {

	echo "<script language=\"javascript\">\n";
	echo "document.regresna.datum_utuzenja.value='';\n";
	echo "alert(\"Neispravan datum utuženja!\")\n";
	echo "document.regresna.datum_utuzenja.focus();\n";
	echo "</script>\n";
	$nastavi = false;
} 


if($datum_por && !provera_datuma($datum_por)) {

	echo "<script language=\"javascript\">\n";
	echo "document.regresna.datum_por.value='';\n";
	echo "alert(\"Neispravan datum poravnanja!\")\n";
	echo "document.regresna.datum_por.focus();\n";
	echo "</script>\n";
	$nastavi = false;
} 

if($datum_isplate && !provera_datuma($datum_isplate)) {

	echo "<script language=\"javascript\">\n";
	echo "document.regresna.datum_isplate.value='';\n";
	echo "alert(\"Neispravan datum isplate!\")\n";
	echo "document.regresna.datum_isplate.focus();\n";
	echo "</script>\n";
	$nastavi = false;
} 


if($_GET['idregres']) {

	$idregres = $_GET['idregres'];
}

//USLOVI ZA UPDATE DETALJA O REGRESU
if ($status == 'izmeni' && $idregres && $submit && $nastavi) {

	$vrsta_osiguranja = substr($brojst, 0, 2);

	//UPIT ZA AZURIRANJE VRSTE OSIGURANJA U TABELI STETA_REGRES
	$upit_sr = "UPDATE steta_regres SET vrsta = '$vrsta_osiguranja' WHERE idregres = $idregres";

	$rezultat_sr = pg_query($conn,$upit_sr);


	//AKO SE UPIT NE IZVRSI
	if(!$rezultat_sr) {

		echo "<script type=\"text/javascript\">";
		echo "alert(\"Greška u izvršavanju upita!\")\n";
		echo "window.close()\n";
		echo "</script>";
	}

	$sql="begin;";
	$rezultat=pg_query($conn,$sql);

	$sql="update regresna set datum_upisa='$datum_upisa' , ";

	if ($fizpra){$sql.="fizpra='$fizpra',"; }
	else{$sql.="fizpra=null,";}
	if ($jmbg_reg){$sql.="jmbg_reg='$jmbg_reg',"; }
	else{$sql.="jmbg_reg=null,";}
	if ($prezime_reg){$sql.="prezime_reg='$prezime_reg',"; }
	else{$sql.="prezime_reg=null,";}
	if ($ime_reg){$sql.="ime_reg='$ime_reg',"; }
	else{$sql.="ime_reg=null,";}
	if ($adresa_reg){$sql.="adresa_reg='$adresa_reg',"; }
	else{$sql.="adresa_reg=null,";}
	if ($mesto_reg){$sql.="mesto_reg='$mesto_reg',"; }
	else{$sql.="mesto_reg=null,";}
	if ($telefon_reg){$sql.="telefon_reg='$telefon_reg',"; }
	else{$sql.="telefon_reg=null,";}


	if ($potrazivanje){$sql.="potrazivanje=$potrazivanje,"; }
	else{$sql.="potrazivanje=null,";}
	if ($datum_zahteva){$sql.="datum_zahteva='$datum_zahteva',"; }
	else{$sql.="datum_zahteva=null,";}
	if ($datum_por){$sql.="datum_por='$datum_por',"; }
	else{$sql.="datum_por=null,";}
	if ($brRata){$sql.="brrata='$brRata',"; }
	else{$sql.="brrata=null,";}
	if ($dug){$sql.="dug=$dug,"; }
	else{$sql.="dug=null,";}

	if ($datum_isplate){$sql.="datum_isplate='$datum_isplate',"; }
	else{$sql.="datum_isplate=null,";}
	if ($datum_utuzenja){$sql.="datum_utuzenja='$datum_utuzenja',"; }
	else{$sql.="datum_utuzenja=null,";}

	if ($napomena){$sql.="napomena='$napomena',"; }
	else{$sql.="napomena=null,";}
	$sql.="  radnik=$radnik, dana=current_date, vreme=current_time, brojst='$brojst', brpolise=$brpolise , ";

	if ($datumnast){$sql.="datumnast='$datumnast',"; }
	else{$sql.="datumnast=null,";}
	if ($mestonast){$sql.="mestonast='$mestonast',"; }
	else{$sql.="mestonast=null,";}
	if ($datumisp){$sql.="datumisp='$datumisp',"; }
	else{$sql.="datumisp=null,";}
	if ($iznosisp){$sql.="iznosisp=$iznosisp,"; }
	else{$sql.="iznosisp=null,";}
	if ($odluka){$sql.="odluka='$odluka',"; }
	else{$sql.="odluka=null,";}
	if ($procnap){$sql.="procnap=$procnap,"; }
	else{$sql.="procnap=null,";}
	if ($kamata){$sql.="kamata=$kamata,"; }
	else{$sql.="kamata=null,";}
	if ($datumisk){$sql.="datumisk='$datumisk',"; }
	else{$sql.="datumisk=null,";}
	if ($odlukaisk){$sql.="odlukaisk='$odlukaisk', "; }
	else{$sql.="odlukaisk=null,";}
	if ($opis){$sql.="opis='$opis',"; }
	else{$sql.="opis=null,";}

	//VLADA PROSIRIO UPIT
	if ($mesto_nastanka_id){$sql.="mesto_nastanka_id='$mesto_nastanka_id',"; }
	else{$sql.="mesto_nastanka_id=null,";}
	if ($zemlja_nastanka_id){$sql.="zemlja_nastanka_id='$zemlja_nastanka_id',"; }
	else{$sql.="zemlja_nastanka_id=null,";}
	if ($mesto_reg_id_hidden){$sql.="mesto_reg_id='$mesto_reg_id_hidden',"; }
	else{$sql.="mesto_reg_id=null,";}
	if ($zemlja_reg_id_hidden){$sql.="zemlja_reg_id='$zemlja_reg_id_hidden' "; }
	else{$sql.="zemlja_reg_id=null ";}

	
	$sql.="   where idregres=$idregres";


	$rezultat1=pg_query($conn,$sql);

	if ($rezultat1) {

		$sql="commit;";
		$rezultat=pg_query($conn,$sql);

		//UNOS ADVOKATA
		if($brSud && $advokat && $datumang) {

			$provera = true;
		}

		if($brSud !='-1' && $advokat !='-1' && $datumang && $provera){

			$advokat_staro = NULL;
			$sud_staro = NULL;


			$sql_advokat = "insert into advokat ( idregres, brsud , advokat ,  datumang , radnik , dana , vreme, advokatska_kancelarija_id, sudska_jedinica_id, datum_razduzenja) values ($idregres, ";

			if ($brSud){$sql_advokat .="'$sud_staro',"; }
			else{$sql_advokat .="null,";}
			if ($advokat){$sql_advokat .="'$advokat_staro',"; }
			else{$sql_advokat .="null,";}
			if ($datumang){$sql_advokat .="'$datumang',"; }
			else{$sql_advokat .="null,";}
			$sql_advokat .=" $radnik, current_date, current_time, ";
			if ($advokat){$sql_advokat .="$advokat,"; }
			else{$sql_advokat .="null,";}
			if ($brSud){$sql_advokat .="$brSud, NULL)"; }
			else{$sql_advokat .="null,NULL)";}

			$rezultat_advokat = pg_query($conn,$sql_advokat);

			if($rezultat_advokat)
			{
				$upit_azuriraj_regresna = "UPDATE regresna SET brsud = '$brSud', advokat = '$advokat' WHERE idregres = $idregres";

				$rezultat_azuriraj = pg_query($conn,$upit_azuriraj_regresna);
			}
			else {
				
				echo "<script type=\"text/javascript\">";
				echo "alert(\"Greška u izvršavanju upita!\")\n";
				echo "window.close()\n";
				echo "</script>";
			}
		}

		echo "<script type=\"text/javascript\">";
		echo "alert(\"Podaci su uspešno promenjeni!\")\n";
		//echo "window.close()\n";
		echo "window.location = 'regresna.php?idregres=$idregres&status=izmeni'";
		echo "</script>";
	}
	else {

		echo "<script type=\"text/javascript\">";
		echo "alert(\"Greška u izvršavanju upita!\")\n";
		echo "window.close()\n";
		echo "</script>";

		$sql="rollback;";
		$rezultat=pg_query($conn,$sql);
	}
}
/*
else {
	echo "<script language=\"javascript\">\n";
	echo "alert(\"Morate popuniti sva polja forme!\")\n";
	echo "</script>\n";
}
*/

}
 /* 2015-12-16 dodata hidden polja za zemlju, opstinu, mesto */
echo "<input type='hidden' name='brojst' id='brojst' value='$brojst'>";
echo "<input type='hidden' name='fizpra' id='fizpra' value='$fizpra'>";
echo "<input type='hidden' name='mesto_nastanka_id' id='mesto_nastanka_id' value='$mesto_nastanka_id'>";
echo "<input type='hidden' name='zemlja_nastanka_id' id='zemlja_nastanka_id' value='$zemlja_nastanka_id'>";
//echo "<input type='hidden' name='predmet_id' id='predmet_id' value='$idstete'>";
echo "<input type='hidden' name='mesto_reg_id_hidden' id='mesto_reg_id_hidden' value=''>";
echo "<input type='hidden' name='zemlja_reg_id_hidden' id='zemlja_reg_id_hidden' value='$zemlja_reg_id'>";
echo "<input type='hidden' name='broj_regresa' id='broj_regresa' value='$brReg'>";
echo "<input type='hidden' name='id_regresa' id='id_regresa' value='$idregres'>";
echo "<input type='hidden' name='broj_stete' id='broj_stete' value=''>";

echo "</form>\n";

pg_close($conn);


?>
</td>
</tr>

  </table>
          <TABLE class=tbn cellSpacing=0 cellPadding=0 border=0 id="table265" width="100%">
            <TBODY>
              <TR>
                <td width="22">
                <img border="0" src="../../images/icg/tb1_leftr.gif" width="39" height="22"></td>
                <TD class=tbnbot style="background-image: url('../../images/icg/tb1_m.gif')">
                <b><span lang="en-us">&nbsp;&nbsp;&nbsp;&nbsp;
                </span></b></TD>
                        <TD class=tbnr width="22">
                <IMG height=22 alt=""
                  src="../../images/icg/tb1_r.gif" width=39></TD>
                </TR>
              </TBODY>
            </TABLE>
            <p>&nbsp;</td>
      </tr>
    </table>
    </div>

<script>

//FUNKCIJA ZA DOHVATANJE PODATAKA ZA REGRESNU FORMU
function dohvatiPodatkeForma() {
	
	//IZDVAJANJE POSLEDNJEG SEGMENTA IZ URL-A
	var url            	 = document.location.href; 
	var urlSegmenti      = url.split('?');
	var indexSegmenta    = urlSegmenti.length - 1;
	var poslednjiSegment = urlSegmenti[indexSegmenta];

	//DOBIJANJE PODSEGMENATA SA ID-JEM STETE I STATUSOM ZAHTEVA
	var prvi_podsegment = poslednjiSegment.split('&');
	var segment_id_stete = prvi_podsegment[0];
	var segment_status = prvi_podsegment[1];

	//DOBIJANJE NIZOVA SA ID-JEM STETE I STATUSOM
	var id_stete_niz = segment_id_stete.split('=');
	var status_niz = segment_status.split('=');


	//AKO JE STATUS,TJ. AKCIJA IZMENI
	if (status_niz[1] == 'izmeni') {

		//AKO POSTOJI SAMO ID STETE
		if (id_stete_niz[0] == 'idstete') {

			//UPIS ID-JA STETE IZ NIZA U PROMENJIVU
			var id_stete = id_stete_niz[1];
			var id_regresa = '';
		}
		//AKO POSTOJI SAMO ID REGRESA
		if (id_stete_niz[0] == 'idregres') {

			//UPIS ID-JA REGRESA IZ NIZA U PROMENJIVU
			var id_stete = '';
			var id_regresa = id_stete_niz[1];
		}

		var funkcija = 'dohvati_podatke_regres';

		$.ajax({

			url: 'funkcije.php',
			method: 'POST',
			dataType: 'json',

			data: {funkcija:funkcija, id_stete:id_stete, id_regresa:id_regresa},

			success: function(data) {
				
				console.log(data);

				//UPIS BROJA STETE U HIDDEN POLJE
				document.getElementById('broj_stete').value = data.id_stete;		

				//UPIS BROJA POLISE I VRSTE OBRASCA U POLJA FORME
				document.getElementById('brpolise').value = data.broj_polise;		
				document.getElementById('vrsta_osiguranja').innerHTML = '<option value=' + data.vrsta_obrasca + ' selected>' + data.vrsta_obrasca + '</option>';	;
				
				//UPIS BROJA PREDMETA I DODAVANJE LINKA
				document.getElementById("lista_predmeta").innerHTML = data.novi_broj_predmeta;
				$("#lista_predmeta").attr("href", "../../stete/pregled.php?idstete=" + data.id_stete + "&dugme=DA");

				//UPIS VREDNOSTI U HIDDEN POLJE
				document.getElementById('brojst').value = data.novi_broj_predmeta;		

				//UPIS DOBIJENIH PODATAKA U ODGOVARAJUCA POLJE FORME
				document.getElementById('regres_od').innerHTML = '<option value=' + data.regres_od + ' selected>' + data.regres_od + '</option>';

				
				//POPUNJAVANJE HIDDEN POLJA
				document.getElementById('fizpra').value = data.tip_lica;
			
				//AKO JE TIP LICA P,CEKIRAJ RADIO BUTTON PRAVNO
				if(data.tip_lica == 'P') {

					document.getElementById("pravno").checked = true;
				}	

				//AKO JE TIP LICA F,CEKIRAJ RADIO BUTTON FIZICKO
				if(data.tip_lica == 'F') {

					document.getElementById("fizicko").checked = true;
				}	
				

				//AKO POSTOJI SAMO ID STETE
				if (id_stete_niz[0] == 'idstete') {

					document.getElementById('jmbg_reg').value = data.jmbg_reg;
					document.getElementById('telefon_reg').value = data.telefon_reg;
					document.getElementById('adresa_reg').value = data.adresa_reg;

					//AKO JE TIP LICA PRAVNO
					if(data.tip_lica == 'P') {

						//AKO JE REGRES OD OSIGURAVAJUCEG DRUSTVA
						if(data.regres_od == 'Osiguravajuće društvo') {
						
							document.getElementById('ime_reg').value = data.osiguranje_reg;
						}
						//AKO JE REGRES OD PRAVNOG LICA, KOJE NIJE OSIGURAVAJUCE DRUSTVO
						else {

							document.getElementById('ime_reg').value = data.ime_reg;
						}
					}
					//AKO JE TIP LICA FIZICKO
					else {

						document.getElementById('ime_reg').value = data.ime_reg;
						document.getElementById('prezime_reg').value = data.prezime_reg;
					}

				}
				
				//AKO POSTOJE PODACI ZA ZEMLJU
				if(data.zemlja_id != undefined) {

					//POPUNJAVANJE HIDDEN POLJA
					document.getElementById('zemlja_reg_id_hidden').value = data.zemlja_id;
				
					//DOHVATANJE SELECT LISTE SA ZEMLJAMA PO ID-JU - DODAO VLADA
					var select_zemlje = document.getElementById('zemlja_reg_id');
					var broj_stavki = select_zemlje.options.length;

					//PROLAZAK KROZ LISTU SA DRZAVAMA
					for (var i = 0; i < broj_stavki; i++) {

						//AKO JE ID ZEMLJE IZ BAZE RAZLICIT OD -1
						if(data.zemlja_id != '-1') {

							//AKO JE ID DRZAVE ISTI KAO NAZIV IZ LISTE,SELEKTUJ GA
							if (select_zemlje.options[i].value == data.zemlja_id) {

								//select_zemlje.options[i].selected = true;
								select_zemlje.options[i].setAttribute('selected', true);
								//select_drzave.disabled = true;
							}
						}
					}
				}

				//AKO JE U PITANJU STRANA DRZAVA
				if(data.zemlja_id != '199') {
					
					//SAKRIVANJE POLJA SA OPSTINOM I MESTOM
					document.getElementById('label_opstina').style.display = 'none';
					document.getElementById('opstina_regresnog_duznika').style.display = 'none';
					document.getElementById('mesto_regresnog_duznika').style.display = 'none';
				
					//RESETOVANJE HIDDEN POLJA
					document.getElementById('mesto_reg_id_hidden').value = 	'';
				}


				//AKO JE DRZAVA SRBIJA I POSTOJI ID OPSTINE
				if(data.id_opstine != undefined && data.zemlja_id == '199') {

					//AKO POSTOJI ID MESTA
					if(data.id_mesta) {

						//POZIVANJE FUNKCIJE ZA POPUNJAVANJE LISTE SA MESTIMA,NA OSNOVU ID-JA OPSTINE
						vrati_mesta_reg(data.id_opstine,'mesto_reg_id',data.id_mesta);
					}
				
					//DOHVATANJE SELECT LISTE SA OPSTINAMA PO ID-JU
					var select_opstine = document.getElementById('opstina_reg_id');
					var broj_opstina = select_opstine.options.length;

					//PROLAZAK KROZ LISTU SA OPSTINAMA
					for (var i = 0; i < broj_opstina; i++) {

						//AKO JE ID OPSTINE ISTI KAO ID IZ LISTE,SELEKTUJ GA
						if (select_opstine.options[i].value == data.id_opstine) {

							//select_opstine.options[i].selected = true;
							select_opstine.options[i].setAttribute('selected', true);
							//select_drzave.disabled = true;
						}
					}
				}

				//AKO POSTOJE POVEZANI REGRESI,UPISI IH U ODGOVARAJUCI DIV
				if(data.povezani_regresi != undefined) {

					$('#podaci_o_povezanim_regresima').html('<td colspan="6">' + data.povezani_regresi + '</td>');
				}

				//DOHVATANJE POLJA ISPLACENO PO ODSTETNOM ZAHTEVU PO ID-JU
				document.getElementById('potrazivanje').value = data.koliko_potrazivati;				
			}
		});

		//SETOVANJE NAZIVA FUNKCIJE I ID-JA STETE
		var funkcija2 = 'prikazi_placeno';
		var id_stete2 = '<?php echo $id_stete ?>';

		$.ajax({

			url: 'pregled_mirni_regresi.php',
			method: 'POST',
			dataType: 'json',

			data: {id_stete:id_stete2, funkcija:funkcija2},

			success: function(data) {
				
				console.log(data);

				//UPIS VREDNOSTI I DATUMA ISPLATE
				var iznos_isplate = data.suma;
				iznos_isplate = iznos_isplate.replace(',', '');

				document.getElementById('iznosisp').value = iznos_isplate;
				document.getElementById('datumisp').value = data.datum_isplate;

			}
		});
	}	
}

</script>

</body>
</html>
