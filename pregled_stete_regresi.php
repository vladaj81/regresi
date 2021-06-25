<?php session_start();$root =$_SERVER ["DOCUMENT_ROOT"];require_once "$root/common/no_cache.php";require_once "$root/privilegije/privilegije.php";require_once "$root/common/zabrane.php";$sifra_u_nizu = array('008007001','008007006');$sifra_provera= implode("','",$sifra_u_nizu);zabrana_istekla_sesija($sifra_provera, $root);?><?php

session_start();
if (isset($_SESSION['radnik']) && $_SESSION['radnik']) {
$radnik = $_SESSION['radnik'];
}
else {
session_destroy();
header("Location: ../../login.php");
exit();
}

//require "../../common/no_cache.php";
date_default_timezone_set ('Europe/Belgrade');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title></title>
<meta name="naslov" content="Pregled mirnih u odnosu na regresna potraživanja">
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<meta http-equiv="language" content="sr_RS" />
<META HTTP-EQUIV="Content-Script-Type" CONTENT="text/javascript">
<link rel="stylesheet" type="text/css" href="../../css/stil.css" media="screen" />
<link href="../../css/jquery-ui.css" rel="stylesheet" type="text/css" media="all" />
<style>
	.disabled{ BACKGROUND: #E6E6FA ; color:black ; height:18px ;}
	#ui-datepicker-div {display: none;}
	* {
		font: 10pt Arial, Helvetica, sans-serif;
	}
</style>
<script type="text/javascript" language="javascript" src="../../js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="../../js/jquery-ui.js"></script>
<script type="text/javascript" language="javascript" src="../../js/jquery.tablescroll.js"></script>
<script type="text/javascript" language="javascript" src="../../js/jquery-ui-i18nn.js"></script>
<script src="../../js/jquery.maskedinput.js" type="text/javascript"></script>
<script type="text/javascript" language="javascript" src="../../js/jquery.ui.datepicker-sr-SR.js"></script>
<script language="javascript" >

function popuniPrikaz() {

		$('#radim').css('display', 'block');
		$('#content').css('display', 'none');
			$.ajax({
			cache: false,
			url: 'pregled_mirni_regresi.php',
			type: 'POST',
			data: { danod: $('#danod').val(), dando: $('#dando').val(), prikaz: $('#prikaz').val() },
			dataType: "html",
			success: function(t) {
				$('#tabrad').empty();
				$('#tabrad').html(t);
				$('#radim').css('display', 'none');
				$('#content').css('display', 'block');
				$('#thetable').tableScroll({height:450});
			}
		});
	}

	//Funkcija koja postavlja masku za vreme nastanka stetnog dogadjaja
function maskiraj_vreme()
{
	jQuery(function($)
	{
	 $('#vreme_nastanka').mask('99:99');
	});
}
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
function samoBrojeviITacka(evt)
{
  var kod = evt.which;
  if (kod==0 || kod==8 || kod==13   || (kod>47 && kod<58) || kod==46)
    return true;
  else
    return false;
}
function samoBrojeviITackaIMinus(evt)
{
  var kod = evt.which;
  if (kod==0 || kod==8 || kod==13   || (kod>47 && kod<58) || kod==46 || kod==45)
  {
	  if(kod==13) return false;
	  return true;
  }
  else
    return false;
}

</script>
<head>
<body onLoad="$('#stampa').css('cursor', 'pointer');">
<?php
// Inicijalni parametri
$danod = '01.01.' . (intval(date('Y'))-4);  //  3 godine je rok zastarelosti regresnih potrazivanja, mada mi prikazujemo jos jednu da bi ispratili sve predmete
$dando = date('d.m.Y');
?>

<div id="container">

<div id="okolog" class="noprint">
<img src="../../images/icg/tb2_l.gif" alt="" class="levo" />
<img src="../../images/icg/tb2_r.gif" alt="" class="desno" />
<span id="natpis">Pregled predmeta odštetnih zahteva u odnosu na otvorena regresna potraživanja</span>
</div>

<div id="radim" class="noprint">
	<br />
	<span style="font:11pt normal Arial,sans-serif;color:red;font-weight:bold;">Molimo sačekajte... Stranica se učitava...</span><br /><br />
	<img src="../../images/png/radim.gif" alt="" />
	<br /><br />
</div>

<div id="nazad" class="noprint">
</div>

<div id="content">

<form id="regzah" name="popis" method="post" action="#">
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Od dana evidentiranja predmeta štete:
<?php
	echo "<input type=\"text\" id=\"danod\" name=\"dan\" value=\"$danod\" size=\"10\" readonly=\"readonly\" />\n";
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Do dana:
<?php
	echo "<input type=\"text\" id=\"dando\" name=\"dan\" value=\"$dando\" size=\"10\" readonly=\"readonly\" />\n";
	//	echo "<input type='hidden' id=\"kont\" name=\"kont\" value=\"$kont\">\n";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<font >Predmeti šteta: </font>";
	echo "<select style='width:250px;' name='prikaz' id='prikaz'>";
	echo "<option value='1' >Svi - sa otvorenim i bez regresa</option>";
	echo "<option value='2' >Bez regresa</option>";
	echo "<option value='3' >Sa otvorenim regresnom</option>";
	echo "<option value='4' selected='selected'>Ispunjeni uslovi za otvaranje regresa</option>";
	echo "</select>";

	/*
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Vrsta obrasca:

	$orgorg = array(0 => 'AO', 1 => 'OK', 2 => 'AK', 3 => 'IO', 4 => 'ZK', 5 => 'GR', 6 => 'JS', 7 => 'N',8 => 'DPZ', 9 => 'LS');
	echo "<select name='vrsta_osiguranja_pretraga' id='vrsta_osiguranja_pretraga' style='margin-right:20px;float:left;' onchange='vrati_na_pocetak();'>\n";

	foreach ($orgorg as $org) {
		echo "<option ";
		if ($vrPolise == $org) { echo "selected "; }
		echo "value='$org'>$org</option>";
	}

	echo "</select>";

	*/
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<img id="stampa" src="../../images/png/emitenti.png" style="vertical-align:middle;" alt="" style="cursor:pointer;" onClick="popuniPrikaz();">
</form>

<div id="tabrad" class="tablescroll" style="width:100%;" align="center"></div>

<script type="text/javascript" language="javascript">
//<![CDATA[
$(function() {
	$( "#danod" ).datepicker({
		onClose: function(date) {
			$( "#dando" ).datepicker( "option", "minDate", date );
			$( '#tabrad' ).empty();
		},
		numberOfMonths: 2,
    showButtonPanel: true,
    minDate: '01.01.2011',
		maxDate: +0,
		showWeek: true,
		firstDay: 1,
    changeMonth: true,
    changeYear: true,
    showAnim: 'show'
	});
});

$( "#danod" ).datepicker( "option", $.datepicker.regional['srRS']);

$(function() {
	$( "#dando" ).datepicker({
		onClose: function(date) {
			$( "#danod" ).datepicker( "option", "maxDate", date );
			$( '#tabrad' ).empty();
		},
		numberOfMonths: 2,
    showButtonPanel: true,
    minDate: '01.01.2011',
		maxDate: +0,
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
    changeYear: true,
    showAnim: 'show'
	});
});
$( "#dando" ).datepicker( "option", $.datepicker.regional['srRS']);

//]]>
</script>
<br />

</div>

<div id="okolod" class="noprint">
<img src="../../images/icg/tb1_leftr.gif" alt="" class="levo" />
<img src="../../images/icg/tb1_r.gif" alt="" class="desno" />
</div>

</div>

<br />


</body>
</html>
