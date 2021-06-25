<html>
<head>
<title>Dokumentacija</title>
<meta name="naslov" content="Dokumentacija">
<meta http-equiv="Content-Type" content="text/html; charset=iso8859-2">
<META HTTP-EQUIV="Content-Script-Type" CONTENT="text/javascript">
<link rel="stylesheet" type="text/css" href="../menistil.css">
<link rel="stylesheet" type="text/css" href="../css/stete_zapisnik.css">

<!-- Dodato za ručni unos delova AutoComplete POČETAK -->
<link href="zapisnik_dodatno/jquery-ui.css" rel="stylesheet" type="text/css" />
<script src="zapisnik_dodatno/jquery.min.js"></script>
<script src="zapisnik_dodatno/jquery-ui.min.js"></script>
<!-- Dodato za ručni unos delova AutoComplete KRAJ -->

<script type="text/javascript">
/************************************************
* Disable "Enter" key in Form script- By Nurul Fadilah(nurul@REMOVETHISvolmedia.com)
* This notice must stay intact for use
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
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
</script>
<script language="javascript" src="../common/cal2.js">
/*
Xin's Popup calendar script- Xin Yang (http://www.yxscripts.com/)
Script featured on/available at http://www.dynamicdrive.com/
This notice must stay intact for use
*/
</script>
<script language="javascript" src="../common/cal_confp.js"></script>

<script language="javascript" >
// Dodato za ručni unos delova AutoComplete POČETAK
(function($) {
    $.widget("ui.combobox", {
        _create: function() {
            var input, self = this,
                select = this.element,
                selected = select.children(":selected"),
                value = selected.val() ? selected.text() : "",
                wrapper = this.wrapper = $("<span>").addClass("ui-combobox").insertAfter(select);

            input = $("<input id='dodatno' style='margin-left:0px;width:400px;color:black;font-size:12px;height:24px;' onkeyup='this.value=povecajSlova(this.value);'>").appendTo(wrapper).val(value).addClass("ui-state-default ui-combobox-input").autocomplete({
                delay: 1000,
                minLength: 3,
                source: function(request, response) {
                    var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");

                    response(select.find("option").map(function() {
                        var text = $(this).text();
                        if (this.value && (!request.term || matcher.test(text))) return {
                            label: text.replace(
                            new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + $.ui.autocomplete.escapeRegex(request.term) + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>"),
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
                                return false;
                            }
                        });
                        if (!valid) {
                        }
                    }
                }
            }).addClass("ui-widget ui-widget-content ui-corner-left");

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
    $("#auto_delovi").combobox();
    $("#auto_delovi").toggle();
});
// Ručni unos AutoComplete KRAJ

function izborKategorije(kategorija, obrisi)
{
	$("#auto_delovi").empty();

	if(obrisi) $("#dodatno").val('');
	
	$.ajax({
	    type:'GET',
	    url: 'funkcije.php?funkcija=vrati_delove_opcije&kategorija='+kategorija,
	    datatype: 'json',
	    async: false,
	    success: function(ret) {
	      var data = JSON.parse(ret);
	      $("#auto_delovi").append(data.opcije);
	    }
	});
}

// Dodato za upis ručno unetog dela u bazu
function dodajDeoNew()
{
	var deo_id = $("#auto_delovi").val();
	var deo_text = $("#auto_delovi :selected").text();
	deo_text = deo_text.toUpperCase();
	var dodatno = $("#dodatno").val();
	dodatno = dodatno.trim();
	dodatno = dodatno.toUpperCase();
	var kategorija = $("#auto_delovi_kategorija").val();

	var novi_id_deo;

	// Proveri da li je odabran ili unet neki deo
	if(deo_id==-1 && dodatno=='')
	{
		alert('Morate izabrati deo iz liste!!!');
		return false;
	}
	if(deo_id!=-1 && dodatno=='')
	{
		alert('Morate uneti naziv dela ili rada!!!');
		return false;
	}	
	
	if (deo_text == dodatno)
	{
		//alert('Jednaki!');
		return true;
	}
	else
	{
		// Snimi novouneti deo
		$.ajax({
	    type:'GET',
	    url: 'funkcije.php?funkcija=snimi_novi_deo_zapisnik&kategorija='+kategorija+'&naziv_auto_dela='+dodatno,
	    datatype: 'json',
	    async: false,
	    success: function(ret) {
	    	var data = JSON.parse(ret);
	    	if(!data.flag)
	    	{
		    	alert(data.poruka);
	    		return false;
	    	}
	    	else
	    	{
	    		novi_id_deo = data.novi_id;
	    	}	
	    }
		});
		// Ucitaj ga ponovo
		izborKategorije(kategorija, false);
		if(novi_id_deo != 'undefined')
			$("#auto_delovi").val(novi_id_deo);
		return true;
	}
}
//Dodato za upis ručno unetog dela u bazu KRAJ

function dodajDeo(){

	// DODATO zbog autocomplete
	var provera_dela = dodajDeoNew();
	if(provera_dela)
	{
		//alert('PROSLO');
	}
	else
	{
		alert('Morate uneti naziv auto-dela!!!');
		exit;
	}
	// DODATO KRAJ
	
	// Proveri da li deo postoji u nekom od postojećih
	var deo_kategorija = $('#auto_delovi_kategorija option:selected').val();
	var deo_id = $('#auto_delovi option:selected').val();
	
	$("#lista_zamena option").each(function()
	{
		id = $(this).val();
		id = id.split("_");
		id = id[0]; 
		if(deo_id == id)
		{
	    alert("Već ste odabrali taj deo u ubacili ga u listu za ZAMENU!!!");
	    exit;
		}    
	});
	$("#lista_popravka option").each(function()
	{
		id = $(this).val();
		id = id.split("_");
		id = id[0]; 
		if(deo_id == id)
		{
	    alert("Već ste odabrali taj deo u ubacili ga u listu za POPRAVKU!!!");
	    exit;
		}  
	});
	$("#lista_kontrola option").each(function()
	{
		id = $(this).val();
		id = id.split("_");
		id = id[0]; 
		if(deo_id == id)
		{
	    alert("Već ste odabrali taj deo u ubacili ga u listu ISPITATI!!!");
	    exit;
		}  
	});
	$("#lista_ostaliradovi option").each(function()
	{
		id = $(this).val();
		id = id.split("_");
		id = id[0]; 
		if(deo_id == id)
		{
	    alert("Već ste odabrali taj deo u ubacili ga u listu za OSTALE RADOVE!!!");
	    exit;
		}  
	});

	$(document).ready(function(){

		var auto_delovi_kategorija = $('#auto_delovi_kategorija option:selected').val();
		//alert('kategorija:'+auto_delovi_kategorija);
		var stepen_ost_id = $('#stepen_ostecenja option:selected').val(); 
		//alert('stepen ostecenja id:'+stepen_ost_id);
		var deo_amortizacija = $('#amortizacija_dela option:selected').val();
		if (deo_amortizacija != 0) 
			deo_amortizacija_prikaz = "("+deo_amortizacija+"%)";
		else
			deo_amortizacija_prikaz = "";
		//alert('amortizacija:'+deo_amortizacija);
		
		var auto_deo_id = $('#auto_delovi option:selected').val();
		//var auto_deo_id_zadnji  = $('#auto_delovi_zadnji option:selected').val();
		//alert('auto deo zad='+auto_deo_id_zadnji);
		var stepen_ost_id = $('#stepen_ostecenja option:selected').val();
		var selekcija_dela = $('input[name=popravka_pregled]:checked').val();

		if(auto_deo_id==0 || stepen_ost_id==0 || selekcija_dela==null)
		{
			if(auto_deo_id==0)
			{
				alert('Morate izabrati auto deo iz ponuđene liste!');
				$('#auto_delovi').focus();
			}
			if(stepen_ost_id==0)
			{
				alert('Morate izabrati stepen oštećenja dela!');
				$('#stepen_ostecenja').focus();
			}
			if(selekcija_dela==null)
			{
				alert('Morate opredeliti auto deo predvidjenim listama!');
			}
		}
		else
		{
			
			//var stepen_ost = $('#stepen_ostecenja option:selected').text();
			var auto_deo = $('#auto_delovi option:selected').text();
			//alert('auto deo='+selekcija_dela);
			//$('#auto_delovi option:selected').appendTo('#lista_kontrola');
			if(selekcija_dela=="deo_za_kontrolu") 
			{
				//$('#auto_delovi option:selected').appendTo('#lista_kontrola');  
				$('#lista_kontrola').append('<option value="'+auto_deo_id+'_'+stepen_ost_id+'_'+deo_amortizacija+'">'+auto_deo+deo_amortizacija_prikaz+'</option>');
				//$('#auto_delovi option:selected').remove();
			}
			if(selekcija_dela=="deo_za_popravku")
			{
				$('#lista_popravka').append('<option value="'+auto_deo_id+'_'+stepen_ost_id+'_'+deo_amortizacija+'">'+auto_deo+deo_amortizacija_prikaz+'</option>');
				//$('#auto_delovi option:selected').remove();
			}
			if(selekcija_dela=="deo_za_zamenu")
			{
				$('#lista_zamena').append('<option value="'+auto_deo_id+'_'+stepen_ost_id+'_'+deo_amortizacija+'">'+auto_deo+deo_amortizacija_prikaz+'</option>');
				//$('#auto_delovi option:selected').remove();
			}
			if(selekcija_dela=="deo_ostaliradovi")
			{
				$('#lista_ostaliradovi').append('<option value="'+auto_deo_id+'_'+stepen_ost_id+'_'+deo_amortizacija+'">'+auto_deo+deo_amortizacija_prikaz+'</option>');
				//$('#auto_delovi option:selected').remove();
			}
		}
		
	});

	$('#amortizacija_dela').val(0);
	$('#dodatno').val('');
}
function skiniDeo()
{
	$(document).ready(function(){

		var lista_kontrola_select = $('#lista_kontrola option:selected').val();
		var lista_popravka_select = $('#lista_popravka option:selected').val();
		var lista_zamena_select = $('#lista_zamena option:selected').val();
		var lista_ostaliradovi_select = $('#lista_ostaliradovi option:selected').val();
		
		var check = false;
		if(lista_kontrola_select)
		{
			//$('#lista_kontrola option:selected').appendTo('#auto_delovi');
			//var lista_k_val = $('#lista_kontrola option:selected').val();
			//lista_k_val_niz = lista_k_val.split('_');
			//lista_k_value = lista_k_val_niz[0]
			$('#lista_kontrola option:selected').remove();
			check = true;
		}
		if(lista_popravka_select)
		{
			//$('#lista_popravka option:selected').appendTo('#auto_delovi');
			$('#lista_popravka option:selected').remove();
			check = true;
		}
		if(lista_zamena_select)
		{
			$('#lista_zamena option:selected').remove();
			
			//$('#option:selected').appendTo('#auto_delovi');
			check = true;
		}
		if(lista_ostaliradovi_select)
		{
			$('#lista_ostaliradovi option:selected').remove();
			check = true;
		}
		if(!check)
		{
			alert('Morate selektovati deo da biste izbacili iz liste! \n Za selektovanje više elemenata držite CTRL.');
		}
		
	});
}

/*-----------------------------
 *	Omogucen unos samo slova  |
 *-----------------------------
 */
function samoSlova(evt)
{
  var kod = evt.which;
  //alert('kod='+kod);
  if (kod==0 || kod==8 || kod==13 || (kod>97 && kod<122) || (kod>65 && kod<90) || kod==134 || kod==143 ||  kod==166 || kod==167 || kod==159 || kod==208 || kod==209 || kod==230 || kod==231)
    return true;
  else
    return false;
}
/*-----------------------------------------
 *  Omogucen unos sledecih karaktera:     |
 *  brojevi  /   +  ) - (  .  backspace   |
 *-----------------------------------------
 */ 
function samoBrojevi(evt)
{
  var kod = evt.which;
  //alert('kod='+kod);
  if (kod==0 || kod==8 || kod==13   || (kod>47 && kod<58))
    return true;
  else
    return false;
}

 // pored mogucnosti unosejna cifara, mogu se unositi:  beli znak(0) , backspace(8) , enter(13) ,  zagrade(40,41)  , -(45) , +(43) , .(46) , /(47) 
 function zaTelefonskiBroj(evt)
{
	  var kod = evt.which;
	  //alert('kod='+kod);
	  if (kod==0 || kod==8 || kod==13  || kod==40 || kod==41 || kod==43 || kod==45 || kod==46 || kod==47 || (kod>47 && kod<58))          
	    return true;
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
    return true;
  else
    return false;
}

function samoBrojeviIPlusIJednako(evt)
{
  var kod = evt.which;
  if (kod==0 || kod==8 || kod==13   || (kod>47 && kod<58) || kod==88 || kod==120 || kod==43)
    return true;
  else
    return false;
}

function povecajSlova(str) 
{
	return str.toUpperCase();
}

function postaviStepenOstecenja(element)
{
	if(element.checked && element.value=='deo_za_zamenu')
		$('#stepen_ostecenja').val(1);
	else
		$('#stepen_ostecenja').val(0);
}
</script>
<style type="text/css">
 input[type=text] , textarea{
/*  text-transform: uppercase; */
}
</style>
</head>

<body bgcolor="#F2F4F9">

<?php
/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);
*/

//šđčćžČĆŽĐŠ

// KONEKCIJA NA BAZU STETE
$conn_stete = pg_connect ("dbname=stete user=zoranp");
if (!$conn_stete) {
	echo "Greška otvaranja konekcije prema SQL serveru.";
	exit;
}
// KONEKCIJA NA BAZU AMSO
$conn_amso = pg_connect ("dbname=amso user=zoranp");
if(!$conn_amso) {
	echo "Greška otvaranja konekcija prema SQL serveru.";
}

//echo "<h1>Zapisnik o utvrđivanju oštećenja na vozilu</h1>\n";
echo "<font color=\"navy\">&nbsp;<br></font>\n";


echo "<form action=\"$PHP_SELF\" name=\"zapisnik\" id=\"zapisnik\" method=\"post\" accept-charset=\"iso8859-2\" >\n\n";

$vrati=1;

if ($zatvori_dok) 
{
  if ($dugme=='DA') 
  {
    require "pregled.php";
  }
}
else 
{
	/* Branka 2014-10-31 - DA/NE procena hidden promenljive - POČETAK*/
	echo "<input type=\"hidden\" name=\"imao_policijski_zapisnik\" value=\"$imao_policijski_zapisnik\">\n";
	echo "<input type=\"hidden\" name=\"imao_evropski_izvestaj\" value=\"$imao_evropski_izvestaj\">\n";
	echo "<input type=\"hidden\" name=\"izvrsio_uporedjivanje_vozila\" value=\"$izvrsio_uporedjivanje_vozila\">\n";
	echo "<input type=\"hidden\" name=\"slikao_drugo_vozilo_odvojeno\" value=\"$slikao_drugo_vozilo_odvojeno\">\n";
	echo "<input type=\"hidden\" name=\"slikao_gde\" value=\"$slikao_gde\">\n";
	echo "<input type=\"hidden\" name=\"slikao_kada\" value=\"$slikao_kada\">\n";
	echo "<input type=\"hidden\" name=\"slikao_vreme\" value=\"$slikao_vreme\">\n";
	echo "<input type=\"hidden\" name=\"stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen\" value=\"$stetni_dogadjaj_nastao_na_nacin_na_koji_je_prijavljen\">\n";
	/* Branka 2014-10-31 - DA/NE procena hidden promenljive - KRAJ*/
  /* Branka 2014-07-03 - tip i osnov rente - hidden promenljive*/
  echo "<input type=\"hidden\" name=\"tip_rente\" value=\"$tip_rente\">\n";
  echo "<input type=\"hidden\" name=\"osnov_rente\" value=\"$osnov_rente\">\n";	
  echo "<input type=\"hidden\" name=\"idstete\" value=\"$idstete\">\n";
  echo "<input type=\"hidden\" name=\"brSt\" value=\"$brSt\">\n";
  echo "<input type=\"hidden\" name=\"vrstaSt\" value=\"$vrstaSt\">\n";
  echo "<input type=\"hidden\" name=\"tipSt\" value=\"$tipSt\">\n";
  echo "<input type=\"hidden\" name=\"orgJed1\" value=\"$orgJed1\">\n";
  echo "<input type=\"hidden\" name=\"orgJed2\" value=\"$orgJed2\">\n";
  echo "<input type=\"hidden\" name=\"brPolise\" value=\"$brPolise\">\n";
  echo "<input type=\"hidden\" name=\"slovo\" value=\"$slovo\">\n";
  echo "<input type=\"hidden\" name=\"vrPolise\" value=\"$vrPolise\">\n";
  echo "<input type=\"hidden\" name=\"sifra\" value=\"$sifra\">\n";
  echo "<input type=\"hidden\" name=\"faza\" value=\"$faza\">\n";
  echo "<input type=\"hidden\" name=\"vazi_od\" value=\"$vazi_od\">\n";
  echo "<input type=\"hidden\" name=\"vazi_do\" value=\"$vazi_do\">\n";

  echo "<input type=\"hidden\" name=\"rbrSt\" value=\"$rbrSt\">\n";
  echo "<input type=\"hidden\" name=\"sumaOs\" value=\"$sumaOs\">\n";
  echo "<input type=\"hidden\" name=\"datumEvid\" value=\"$datumEvid\">\n";
  echo "<input type=\"hidden\" name=\"datumKompl\" value=\"$datumKompl\">\n";
  echo "<input type=\"hidden\" name=\"datumNast\" value=\"$datumNast\">\n";
  echo "<input type=\"hidden\" name=\"mestoSt\" value=\"$mestoSt\">\n";
  echo "<input type=\"hidden\" name=\"ucesce\" value=\"$ucesce\">\n";
  echo "<input type=\"hidden\" name=\"premija\" value=\"$premija\">\n";
  echo "<input type=\"hidden\" name=\"prezimeOst\" value=\"$prezimeOst\">\n";
  echo "<input type=\"hidden\" name=\"imeNazivOst\" value=\"$imeNazivOst\">\n";
  echo "<input type=\"hidden\" name=\"jmbgPibOst\" value=\"$jmbgPibOst\">\n";
  echo "<input type=\"hidden\" name=\"ovlLiceOst\" value=\"$ovlLiceOst\">\n";
  echo "<input type=\"hidden\" name=\"telefon1\" value=\"$telefon1\">\n";
  echo "<input type=\"hidden\" name=\"telefon2\" value=\"$telefon2\">\n";
  echo "<input type=\"hidden\" name=\"markaOst\" value=\"$markaOst\">\n";
  echo "<input type=\"hidden\" name=\"tipOst\" value=\"$tipOst\">\n";
  echo "<input type=\"hidden\" name=\"godOst\" value=\"$godOst\">\n";
  echo "<input type=\"hidden\" name=\"regPodOst\" value=\"$regPodOst\">\n";
  echo "<input type=\"hidden\" name=\"regOznakaOst\" value=\"$regOznakaOst\">\n";
  echo "<input type=\"hidden\" name=\"tgOst\" value=\"$tgOst\">\n";
  echo "<input type=\"hidden\" name=\"nazivOsigOst\" value=\"$nazivOsigOst\">\n";
  echo "<input type=\"hidden\" name=\"brPoliseOst\" value=\"$brPoliseOst\">\n";
  echo "<input type=\"hidden\" name=\"vaznostOdOst\" value=\"$vaznostOdOst\">\n";
  echo "<input type=\"hidden\" name=\"vaznostDoOst\" value=\"$vaznostDoOst\">\n";

  echo "<input type=\"hidden\" name=\"prezimeKriv\" value=\"$prezimeKriv\">\n";
  echo "<input type=\"hidden\" name=\"imeNazivKriv\" value=\"$imeNazivKriv\">\n";
  echo "<input type=\"hidden\" name=\"jmbgPibKriv\" value=\"$jmbgPibKriv\">\n";
  echo "<input type=\"hidden\" name=\"ovlLiceKriv\" value=\"$ovlLiceKriv\">\n";
  echo "<input type=\"hidden\" name=\"markaKriv\" value=\"$markaKriv\">\n";
  echo "<input type=\"hidden\" name=\"tipKriv\" value=\"$tipKriv\">\n";
  echo "<input type=\"hidden\" name=\"godKriv\" value=\"$godKriv\">\n";
  echo "<input type=\"hidden\" name=\"regPodKriv\" value=\"$regPodKriv\">\n";
  echo "<input type=\"hidden\" name=\"regOznakaKriv\" value=\"$regOznakaKriv\">\n";
  echo "<input type=\"hidden\" name=\"tgKriv\" value=\"$tgKriv\">\n";

  echo "<input type=\"hidden\" name=\"nazivOsigKriv\" value=\"$nazivOsigKriv\">\n";
  echo "<input type=\"hidden\" name=\"brPoliseKriv\" value=\"$brPoliseKriv\">\n";
  echo "<input type=\"hidden\" name=\"vaznostOdKriv\" value=\"$vaznostOdKriv\">\n";
  echo "<input type=\"hidden\" name=\"vaznostDoKriv\" value=\"$vaznostDoKriv\">\n";

  echo "<input type=\"hidden\" name=\"vrstaRegStet\" value=\"$vrstaRegStet\">\n";
  echo "<input type=\"hidden\" name=\"oznakaRegStet\" value=\"$oznakaRegStet\">\n";
  echo "<input type=\"hidden\" name=\"osiguranjeRegStet\" value=\"$osiguranjeRegStet\">\n";
  echo "<input type=\"hidden\" name=\"drzavaRegStet\" value=\"$drzavaRegStet\">\n";

  echo "<input type=\"hidden\" name=\"procenitelj1\" value=\"$procenitelj1\">\n";
  echo "<input type=\"hidden\" name=\"procenitelj2\" value=\"$procenitelj2\">\n";
  echo "<input type=\"hidden\" name=\"datumProc\" value=\"$datumProc\">\n";
  echo "<input type=\"hidden\" name=\"servis\" value=\"$servis\">\n";

  echo "<input type=\"hidden\" name=\"dana\" value=\"$dana\">\n";
  echo "<input type=\"hidden\" name=\"datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete\" value=\"$datumKompletiranjaDokumentacijeUtvrdjivanjeVisineStete\">\n";
  echo "<input type=\"hidden\" name=\"pocetak\" value=\"$pocetak\">\n";
  echo "<input type=\"hidden\" name=\"kraj\" value=\"$kraj\">\n";
  echo "<input type=\"hidden\" name=\"obradjivac1\" value=\"$obradjivac1\">\n";
  echo "<input type=\"hidden\" name=\"obradjivac2\" value=\"$obradjivac2\">\n";
  echo "<input type=\"hidden\" name=\"datumPonuda1\" value=\"$datumPonuda1\">\n";
  echo "<input type=\"hidden\" name=\"likvidatorPonuda1\" value=\"$likvidatorPonuda1\">\n";
  echo "<input type=\"hidden\" name=\"datumPrigovor\" value=\"$datumPrigovor\">\n";
  echo "<input type=\"hidden\" name=\"komisija1\" value=\"$komisija1\">\n";
  echo "<input type=\"hidden\" name=\"komisija2\" value=\"$komisija2\">\n";

  echo "<input type=\"hidden\" name=\"datumPonuda2\" value=\"$datumPonuda2\">\n";
  echo "<input type=\"hidden\" name=\"likvidatorPonuda2\" value=\"$likvidatorPonuda2\">\n";
  echo "<input type=\"hidden\" name=\"zahtevano\" value=\"$zahtevano\">\n";
  echo "<input type=\"hidden\" name=\"rezervisano\" value=\"$rezervisano\">\n";
  echo "<input type=\"hidden\" name=\"isplaceno\" value=\"$isplaceno\">\n";
  echo "<input type=\"hidden\" name=\"nalog\" value=\"$nalog\">\n";
  echo "<input type=\"hidden\" name=\"isplata\" value=\"$isplata\">\n";
  echo "<input type=\"hidden\" name=\"odustao\" value=\"$odustao\">\n";
  echo "<input type=\"hidden\" name=\"dokNijeSt\" value=\"$dokNijeSt\">\n";
  echo "<input type=\"hidden\" name=\"sp\" value=\"$sp\">\n";
  echo "<input type=\"hidden\" name=\"arhivirano\" value=\"$arhivirano\">\n";
  echo "<input type=\"hidden\" name=\"napomena\" value=\"$napomena\">\n";

  echo "<input type=\"hidden\" name=\"datumPravniOsnov\" value=\"$datumPravniOsnov\">\n";
  echo "<input type=\"hidden\" name=\"osnovan\" value=\"$osnovan\">\n";
  echo "<input type=\"hidden\" name=\"delimicnoProc\" value=\"$delimicnoProc\">\n";
  echo "<input type=\"hidden\" name=\"vraceno\" value=\"$vraceno\">\n";
  echo "<input type=\"hidden\" name=\"vrstaRegPotr\" value=\"$vrstaRegPotr\">\n";
  echo "<input type=\"hidden\" name=\"oznakaRegPotr\" value=\"$vrstaRegPotr\">\n";
  echo "<input type=\"hidden\" name=\"osiguranjeRegPotr\" value=\"$osiguranjeRegPotr\">\n";
  echo "<input type=\"hidden\" name=\"drzavaRegPotr\" value=\"$drzavaRegPotr\">\n";
  echo "<input type=\"hidden\" name=\"regPotr\" value=\"$regPotr\">\n";
  echo "<input type=\"hidden\" name=\"pravniOsnovDao\" value=\"$pravniOsnovDao\">\n";
  echo "<input type=\"hidden\" name=\"pravniOsnovNapomena\" value=\"$pravniOsnovNapomena\">\n";
  echo "<input type=\"hidden\" name=\"pravniOsnovObradjivac\" value=\"$pravniOsnovObradjivac\">\n";
  echo "<input type=\"hidden\" name=\"datumPrijemaPredmetaPravnaSluzba\" value=\"$datumPrijemaPredmetaPravnaSluzba\">\n";
  echo "<input type=\"hidden\" name=\"pravniOsnovDatumKompletiranjaDokumentacije\" value=\"$pravniOsnovDatumKompletiranjaDokumentacije\">\n";

  echo "<input type=\"hidden\" name=\"malusProc\" value=\"$malusProc\">\n";
  echo "<input type=\"hidden\" name=\"malusIznos\" value=\"$malusIznos\">\n";
  echo "<input type=\"hidden\" name=\"dugZaPremiju\" value=\"$dugZaPremiju\">\n";
  echo "<input type=\"hidden\" name=\"kompenzovano\" value=\"$kompenzovano\">\n";
  echo "<input type=\"hidden\" name=\"preostaliDug\" value=\"$preostaliDug\">\n";
  echo "<input type=\"hidden\" name=\"datumKomOsnov\" value=\"$datumKomOsnov\">\n";
  echo "<input type=\"hidden\" name=\"kompenzovati\" value=\"$kompenzovati\">\n";

  echo "<input type=\"hidden\" name=\"imePrePovr1\" value=\"$imePrePovr1\">\n";
  echo "<input type=\"hidden\" name=\"polozajPovr1\" value=\"$polozajPovr1\">\n";
  echo "<input type=\"hidden\" name=\"imePrePovr2\" value=\"$imePrePovr2\">\n";
  echo "<input type=\"hidden\" name=\"polozajPovr2\" value=\"$polozajPovr2\">\n";
  echo "<input type=\"hidden\" name=\"imePrePovr3\" value=\"$imePrePovr3\">\n";
  echo "<input type=\"hidden\" name=\"polozajPovr3\" value=\"$polozajPovr3\">\n";
  echo "<input type=\"hidden\" name=\"imePrePovr4\" value=\"$imePrePovr4\">\n";
  echo "<input type=\"hidden\" name=\"polozajPovr4\" value=\"$polozajPovr4\">\n";
  echo "<input type=\"hidden\" name=\"imePrePovr5\" value=\"$imePrePovr5\">\n";
  echo "<input type=\"hidden\" name=\"polozajPovr5\" value=\"$polozajPovr5\">\n";

  echo "<input type=\"hidden\" name=\"prezimeVoz\" value=\"$prezimeVoz\">\n";
  echo "<input type=\"hidden\" name=\"imeVoz\" value=\"$imeVoz\">\n";
  echo "<input type=\"hidden\" name=\"jmbgVoz\" value=\"$jmbgVoz\">\n";
  echo "<input type=\"hidden\" name=\"telefonv1\" value=\"$telefonv1\">\n";
  echo "<input type=\"hidden\" name=\"telefonv2\" value=\"$telefonv2\">\n";

  echo "<input type=\"hidden\" name=\"dugme\" value=\"$dugme\">\n";
  echo "<input type=\"hidden\" name=\"vrati\" value=\"$vrati\">\n";

  echo "<input type=\"hidden\" name=\"regStPov\" value=\"$regStPov\">\n";
  echo "<input type=\"hidden\" name=\"prigovor\" value=\"$prigovor\">\n";

  echo "<input type=\"hidden\" name=\"prvaUpotreba\" value=\"$prvaUpotreba\">\n";
  
  echo "<input type=\"hidden\" name=\"vrstaVozila\" value=\"$vrstaVozila\">\n";
  echo "<input type=\"hidden\" name=\"zemljaProizv\" value=\"$zemljaProizv\">\n";
  echo "<input type=\"hidden\" name=\"marka\" value=\"$marka\">\n";
  echo "<input type=\"hidden\" name=\"tip\" value=\"$tip\">\n";
  echo "<input type=\"hidden\" name=\"model\" value=\"$model\">\n";
  echo "<input type=\"hidden\" name=\"sifraVoz\" value=\"$sifraVoz\">\n";
  echo "<input type=\"hidden\" name=\"cena\" value=\"$cena\">\n";
  echo "<input type=\"hidden\" name=\"procAmortizacije\" value=\"$procAmortizacije\">\n";
  echo "<input type=\"hidden\" name=\"vrednost\" value=\"$vrednost\">\n";
  
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
  
  echo "<input type=\"hidden\" name=\"foto\" value=\"$foto\">\n";
  echo "<input type=\"hidden\" name=\"opisOst\" value=\"$opisOst\">\n";
  
  echo "<input type=\"hidden\" name=\"modelOst\" value=\"$modelOst\">\n";
  echo "<input type=\"hidden\" name=\"modelKriv\" value=\"$modelKriv\">\n";
  echo "<input type=\"hidden\" name=\"prihvacena\" value=\"$prihvacena\">\n";
  echo "<input type=\"hidden\" name=\"pocetak2\" value=\"$pocetak2\">\n";
  echo "<input type=\"hidden\" name=\"kraj2\" value=\"$kraj2\">\n";
  echo "<input type=\"hidden\" name=\"prihvacena2\" value=\"$prihvacena2\">\n";
  echo "<input type=\"hidden\" name=\"gotovina\" value=\"$gotovina\">\n";
  echo "<input type=\"hidden\" name=\"virman\" value=\"$virman\">\n";
  echo "<input type=\"hidden\" name=\"doznaka\" value=\"$doznaka\">\n";
  echo "<input type=\"hidden\" name=\"kompenzacija\" value=\"$kompenzacija\">\n";
  echo "<input type=\"hidden\" name=\"fotoaparat\" value=\"$fotoaparat\">\n";
  echo "<input type=\"hidden\" name=\"teren\" value=\"$teren\">\n";

  echo "<input type=\"hidden\" name=\"kfPredato\" value=\"$kfPredato\">\n";
  echo "<input type=\"hidden\" name=\"vinkulirano\" value=\"$vinkulirano\">\n";
  echo "<input type=\"hidden\" name=\"pravnaPredato\" value=\"$pravnaPredato\">\n";

  echo "<input type=\"hidden\" name=\"tekRacun_ost\" value=\"$tekRacun_ost\">\n";
  echo "<input type=\"hidden\" name=\"nacin_resavanja\" value=\"$nacin_resavanja\">\n";
  echo "<input type=\"hidden\" name=\"nacin_resavanja2\" value=\"$nacin_resavanja2\">\n";
  echo "<input type=\"hidden\" name=\"naknadna_isplata\" value=\"$naknadna_isplata\">\n";
  echo "<input type=\"hidden\" name=\"pkc\" value=\"$pkc\">\n";
  echo "<input type=\"hidden\" name=\"datumPrijave\" value=\"$datumPrijave\">\n";
  echo "<input type=\"hidden\" name=\"struktura\" value=\"$struktura\">\n";

  echo "<input type=\"hidden\" name=\"nastda\" value=\"$nastda\">\n";
  echo "<input type=\"hidden\" name=\"prida\" value=\"$prida\">\n";
  echo "<input type=\"hidden\" name=\"komda\" value=\"$komda\">\n";

  echo "<input type=\"hidden\" name=\"adresaOst\" value=\"$adresaOst\">\n";
  echo "<input type=\"hidden\" name=\"adresaOvllice\" value=\"$adresaOvllice\">\n";
  echo "<input type=\"hidden\" name=\"posbrOst\" value=\"$posbrOst\">\n";
  echo "<input type=\"hidden\" name=\"posbrOvllice\" value=\"$posbrOvllice\">\n";


  echo "<input type=\"hidden\" name=\"vremeNast\" value=\"$vremeNast\">\n";
  echo "<input type=\"hidden\" name=\"uzrokStete\" value=\"$uzrokStete\">\n";
  echo "<input type=\"hidden\" name=\"opisStete\" value=\"$opisStete\">\n";
  echo "<input type=\"hidden\" name=\"vremenskePrilike\" value=\"$vremenskePrilike\">\n";
  echo "<input type=\"hidden\" name=\"brsasOst\" value=\"$brsasOst\">\n";
  echo "<input type=\"hidden\" name=\"brsasKriv\" value=\"$brsasKriv\">\n";
  echo "<input type=\"hidden\" name=\"storno\" value=\"$storno\">\n";
  echo "<input type=\"hidden\" name=\"totalnaSteta\" value=\"$totalnaSteta\">\n";

  /*novo*/
  echo "<input type=\"hidden\" name=\"predjenoKmOst\" value=\"$predjenoKmOst\">\n";
  echo "<input type=\"hidden\" name=\"prezimeVozKriv\" value=\"$prezimeVozKriv\">\n";
  echo "<input type=\"hidden\" name=\"imeVozKriv\" value=\"$imeVozKriv\">\n";
  echo "<input type=\"hidden\" name=\"jmbgVozKriv\" value=\"$jmbgVozKriv\">\n";
  echo "<input type=\"hidden\" name=\"adresaPovr1\" value=\"$adresaPovr1\">\n";
  echo "<input type=\"hidden\" name=\"telefonPovr1\" value=\"$telefonPovr1\">\n";
  echo "<input type=\"hidden\" name=\"povredePovr1\" value=\"$povredePovr1\">\n";
  echo "<input type=\"hidden\" name=\"adresaPovr2\" value=\"$adresaPovr2\">\n";
  echo "<input type=\"hidden\" name=\"telefonPovr2\" value=\"$telefonPovr2\">\n";
  echo "<input type=\"hidden\" name=\"povredePovr2\" value=\"$povredePovr2\">\n";
  echo "<input type=\"hidden\" name=\"adresaPovr3\" value=\"$adresaPovr3\">\n";
  echo "<input type=\"hidden\" name=\"telefonPovr3\" value=\"$telefonPovr3\">\n";
  echo "<input type=\"hidden\" name=\"povredePovr3\" value=\"$povredePovr3\">\n";
  echo "<input type=\"hidden\" name=\"adresaPovr4\" value=\"$adresaPovr4\">\n";
  echo "<input type=\"hidden\" name=\"telefonPovr4\" value=\"$telefonPovr4\">\n";
  echo "<input type=\"hidden\" name=\"povredePovr4\" value=\"$povredePovr4\">\n";
  echo "<input type=\"hidden\" name=\"adresaPovr5\" value=\"$adresaPovr5\">\n";
  echo "<input type=\"hidden\" name=\"telefonPovr5\" value=\"$telefonPovr5\">\n";
  echo "<input type=\"hidden\" name=\"povredePovr5\" value=\"$povredePovr5\">\n";
  echo "<input type=\"hidden\" name=\"osnovni_predmet_id_reaktiviranog\" id=\"osnovni_predmet_id_reaktiviranog\" value=\"$osnovni_predmet_id_reaktiviranog\">\n";
  

  /*dodato 29.02.2012. dragan*/
  echo "<input type=\"hidden\" name=\"servis_fakturisano_id\" value=\"$servis_fakturisano_id\">\n";
  echo "<input type=\"hidden\" name=\"servis_upuceno_id\" value=\"$servis_upuceno_id\">\n";
  echo "<input type=\"hidden\" name=\"opstina_stete_id\" value=\"$opstina_stete_id\">\n";

  echo "<input type=\"hidden\" name=\"rbrReaktivirana\" value=\"$rbrReaktivirana\">\n";
  echo "<input type=\"hidden\" name=\"rbrSD\" value=\"$rbrSD\">\n";
  echo "<input type=\"hidden\" name=\"rbrSteta\" value=\"$rbrSteta\">\n";
  echo "<input type=\"hidden\" name=\"reaktivirana\" value=\"$reaktivirana\">\n";
  echo "<input type=\"hidden\" name=\"idreak\" value=\"$idreak\">\n";
  
  // Dodato 01-avgust-2013 - Lazar Milosavljević - Zbog RAZLOGA REAKTIVACIJE
  echo "<input type=\"hidden\" name=\"razlog_reaktivacije\" value=\"$razlog_reaktivacije\">\n";
  
  // Dodato 08-02-2013 - Lazar Milosavljević - Zbog PRIGOVORA
  echo "<input type=\"hidden\" name=\"osnovan_po_prigovoru_na_visinu_odstete\" value=\"$osnovan_po_prigovoru_na_visinu_odstete\">\n";
  echo "<input type=\"hidden\" name=\"delimicno_resen_po_prigovoru_procenat\" value=\"$delimicno_resen_po_prigovoru_procenat\">\n";
  
  // Dodato 08-02-2013 - Lazar Milosavljević - Zbog promena za vrste šteta DPZ && tip šteta ZP
  echo "<input type=\"hidden\" name=\"osteceni_broj_pasosa\" value=\"$osteceni_broj_pasosa\">\n";
  echo "<input type=\"hidden\" name=\"osteceni_pol\" value=\"$osteceni_pol\">\n";
  echo "<input type=\"hidden\" name=\"osteceni_email\" value=\"$osteceni_email\">\n";
  echo "<input type=\"hidden\" name=\"datum_ulaska_u_zemlju_destinacije\" value=\"$datum_ulaska_u_zemlju_destinacije\">\n";
  echo "<input type=\"hidden\" name=\"datum_izlaska_iz_zemlje_destinacije\" value=\"$datum_izlaska_iz_zemlje_destinacije\">\n";
  echo "<input type=\"hidden\" name=\"datum_prijema_medicinska_ustanova\" value=\"$datum_prijema_medicinska_ustanova\">\n";
  echo "<input type=\"hidden\" name=\"datum_otpustanja_medicinska_ustanova\" value=\"$datum_otpustanja_medicinska_ustanova\">\n";
  echo "<input type=\"hidden\" name=\"naziv_medicinske_ustanove\" value=\"$naziv_medicinske_ustanove\">\n";
  echo "<input type=\"hidden\" name=\"ime_lekara\" value=\"$ime_lekara\">\n";
  echo "<input type=\"hidden\" name=\"vrsta_povrede_ili_bolesti\" value=\"$vrsta_povrede_ili_bolesti\">\n";
  echo "<input type=\"hidden\" name=\"vrsta_lecenja\" value=\"$vrsta_lecenja\">\n";
  echo "<input type=\"hidden\" name=\"napomena_o_osiguranom_slucaju\" value=\"$napomena_o_osiguranom_slucaju\">\n";
  
  // Dodato 15-08-2013 - Lazar Milosavljević - Zbog zaključavanja polja: Broj polise, Vreme nastanka, Reaktivirane
  echo "<input type=\"hidden\" name=\"brPoliseBaza\" value=\"$brPoliseBaza\">\n";
  echo "<input type=\"hidden\" name=\"vremeNastBaza\" value=\"$vremeNastBaza\">\n";
  echo "<input type=\"hidden\" name=\"reaktiviranaBaza\" value=\"$reaktiviranaBaza\">\n";
  
  // Dodato 09-09-2013 - Lazar Milosavljević - Zbog novih polja: Mesto štete, Geografska širina i Geografska dužina
  echo "<input type=\"hidden\" name=\"mesto_stete_id\" value=\"$mesto_stete_id\">\n";
  echo "<input type=\"hidden\" name=\"geografska_sirina_stete\" value=\"$geografska_sirina_stete\">\n";
  echo "<input type=\"hidden\" name=\"geografska_duzina_stete\" value=\"$geografska_duzina_stete\">\n";
  
  // Dodato 17-09-2013 - Lazar Milosavljević - Zbog novog polja: steta_u_inostranstvu, zemlja_stete_id
  echo "<input type='hidden' name='steta_u_inostranstvu' value=\"$steta_u_inostranstvu\">\n";
  echo "<input type='hidden' name='zemlja_stete_id' value=\"$zemlja_stete_id\">\n";

  // Dodato 18-09-2013 - Lazar Milosavljević - Zbog zaključavanja polja: jmbgPibOst
  echo "<input type='hidden' name='jmbgPibOstBazaDisabled' value='$jmbgPibOstBazaDisabled'>\n";
  
  // Dodato 24-09-2013 - Lazar Milosavljević - Zbog zaključavanja polja: zemlja štete na DPZ-ZP štetama
  echo "<input type='hidden' name='zemlja_stete_dpz_zp_id' value='$zemlja_stete_dpz_zp_id'>\n";
  
  // Dodato 14-11-2013 - Lazar Milosavljević - Zbog datuma prijema fakture iz servisa (servis_fakturisano_datum)
  echo "<input type='hidden' name='servis_fakturisano_datum' value='$servis_fakturisano_datum'>\n";
  
  //MARIJA 7.11.2014
  echo "<input type=\"hidden\" name=\"osteceni_mesto_id\" value=\"$osteceni_mesto_id\">\n";
  echo "<input type=\"hidden\" name=\"ovlasceno_lice_mesto_id\" value=\"$ovlasceno_lice_mesto_id\">\n";
  echo "<input type=\"hidden\" name=\"vozac_mesto_id\" value=\"$vozac_mesto_id\">\n";
  echo "<input type=\"hidden\" name=\"osiguranik_krivac_mesto_id\" value=\"$osiguranik_krivac_mesto_id\">\n";
  echo "<input type=\"hidden\" name=\"vozac_krivac_mesto_id\" value=\"$vozac_krivac_mesto_id\">\n";
  echo "<input type=\"hidden\" name=\"osteceni_mesto_opis\" value=\"$osteceni_mesto_opis\">\n";
  echo "<input type=\"hidden\" name=\"osteceni_zemlja\" value=\"$osteceni_zemlja\">\n";
  echo "<input type=\"hidden\" name=\"ovlasceno_lice_mesto_opis\" value=\"$ovlasceno_lice_mesto_opis\">\n";
  echo "<input type=\"hidden\" name=\"osiguranik_krivac_mesto_opis\" value=\"$osiguranik_krivac_mesto_opis\">\n";
  echo "<input type=\"hidden\" name=\"vozac_mesto_opis\" value=\"$vozac_mesto_opis\">\n";
  echo "<input type=\"hidden\" name=\"vozac_krivac_mesto_opis\" value=\"$vozac_krivac_mesto_opis\">\n";
  //adresa za osiguranika krivca, za vozaca i za vozaca krivca
  echo "<input type=\"hidden\" name=\"osiguranik_krivac_adresa\" value=\"$osiguranik_krivac_adresa\">\n";
  echo "<input type=\"hidden\" name=\"vozac_adresa\" value=\"$vozac_adresa\">\n";
  echo "<input type=\"hidden\" name=\"vozac_krivac_adresa\" value=\"$vozac_krivac_adresa\">\n";
  //zemlja koje su dodate za sva lica stete
  echo "<input type=\"hidden\" name=\"osteceni_zemlja_id\" value=\"$osteceni_zemlja_id\">\n";
  echo "<input type=\"hidden\" name=\"ovlasceno_lice_zemlja_id\" value=\"$ovlasceno_lice_zemlja_id\">\n";
  echo "<input type=\"hidden\" name=\"osiguranik_krivac_zemlja_id\" value=\"$osiguranik_krivac_zemlja_id\">\n";
  echo "<input type=\"hidden\" name=\"vozac_zemlja_id\" value=\"$vozac_zemlja_id\">\n";
  echo "<input type=\"hidden\" name=\"vozac_krivac_zemlja_id\" value=\"$vozac_krivac_zemlja_id\">\n";
  //brojevi telefona za osiguranika krivca i vozaca krivca
  echo "<input type=\"hidden\" name=\"osiguranik_krivac_telefon1\" value=\"$osiguranik_krivac_telefon1\">\n";
  echo "<input type=\"hidden\" name=\"osiguranik_krivac_telefon2\" value=\"$osiguranik_krivac_telefon2\">\n";
  echo "<input type=\"hidden\" name=\"vozac_krivac_telefon1\" value=\"$vozac_krivac_telefon1\">\n";
  echo "<input type=\"hidden\" name=\"vozac_krivac_telefon2\" value=\"$vozac_krivac_telefon2\">\n";
  //brojevi licnih karti dodati za sva lica
  echo "<input type=\"hidden\" name=\"osteceni_broj_licne_karte\" value=\"$osteceni_broj_licne_karte\">\n";
  // echo "<input type=\"hidden\" name=\"ovlasceno_lice_broj_licne_karte\" value=\"$ovlasceno_lice_broj_licne_karte\">\n";
  echo "<input type=\"hidden\" name=\"osiguranik_krivac_broj_licne_karte\" value=\"$osiguranik_krivac_broj_licne_karte\">\n";
  echo "<input type=\"hidden\" name=\"vozac_broj_licne_karte\" value=\"$vozac_broj_licne_karte\">\n";
  echo "<input type=\"hidden\" name=\"vozac_krivac_broj_licne_karte\" value=\"$vozac_krivac_broj_licne_karte\">\n";
  //ZAVRSENO
  
  // MARIJA 23.02.2015 dodato za razlog umanjenja stete - POCETAK
  echo "<input type=\"hidden\" name=\"razlog_umanjenja_stete_id\" value=\"$razlog_umanjenja_stete_id\">\n";
  echo "<input type=\"hidden\" name=\"pravni_osnov_izvestaj\" value=\"$pravni_osnov_izvestaj\">\n";
  echo "<input type=\"hidden\" name=\"alkotest_osteceni\" value=\"$alkotest_osteceni\">\n";
  echo "<input type=\"hidden\" name=\"alkotest_krivac\" value=\"$alkotest_krivac\">\n";
  echo "<input type=\"hidden\" name=\"lista_za_pravni_osnov\" value=\"$vrednost_lista_osnova\">\n";
  echo "<input type=\"hidden\" name=\"regres_od\" value=\"$regres_od\">\n";
  echo "<input type=\"hidden\" name=\"osiguravajuce_drustvo_id\" value=\"$osiguravajuce_drustvo_id\">\n";
  echo "<input type=\"hidden\" name=\"osnov_pravnog_osnova\" value=\"$osnov_pravnog_osnova\">\n";
  echo "<input type=\"hidden\" name=\"potvrdjen_osnov_za_regres\" value=\"$potvrdjen_osnov_za_regres\">\n";
  echo "<input type=\"hidden\" name=\"razlog_regresa_id\" value=\"$razlog_regresa_id\">\n";
  
  echo "<input type=\"hidden\" name=\"regresno_potrazivanje_napomena\" value=\"$regresno_potrazivanje_napomena\">\n";
  echo "<input type=\"hidden\" name=\"datum_otvaranja_regresa\" value=\"$datum_otvaranja_regresa\">\n";
  // 2016-03-22
  echo "<input type=\"hidden\" name=\"prijaviti_u_reosiguranje\" value=\"$prijaviti_u_reosiguranje\">\n";
  // MARIJA 23.02.2015 dodato za razlog umanjenja stete - KRAJ
  //Nemanja Jovanovic
  echo "<input type=\"hidden\" name=\"napomenaSnimanje\" value=\"$napomenaSnimanje\">\n";
  //$sql="SELECT * FROM dok_polja WHERE status='A' ORDER BY redosled";
$sql="SELECT * FROM dok ORDER BY redosled";
$result=pg_query($conn_stete, $sql);
if (!$result) {
  echo "<font color=\"#CC0000\">Greška u određivanju broja relevantnih zapisa u tabeli <b>hov_polja</b>.</font>\n";
  //include "../common/zavrsi.inc";
 // exit;
}



if(!$submit)
{
	
	//DODATO JER SE POSMATRAJU SVI PREDMETI (OSNOVNI I REAKTIVIRANI)
	
		$sqlPrethodniZapisnici = "SELECT id FROM predmet_odstetnog_zahteva WHERE (osnovni_predmet_id = $osnovni_predmet_id_reaktiviranog OR id=$osnovni_predmet_id_reaktiviranog)";
		$rezultatPrethodniZapisnici = pg_query($conn,$sqlPrethodniZapisnici);
		$rezultatPrethodni = pg_fetch_all_columns($rezultatPrethodniZapisnici);
		$idZapisnici = implode(",", $rezultatPrethodni);
	
	// ODVDE SE VADE PODACI ZA ZAPISNIK AKO JE VEC IZRADJEN
		
	$sqlZapisnik = "SELECT * FROM zapisnik_o_ostecenju_vozila WHERE id_stete IN ($idZapisnici) order by dopunski desc limit 1";
    //echo $sqlZapisnik;
	$rezultatZapisnik = pg_query($conn,$sqlZapisnik);
	$podaciZapisnik = pg_fetch_assoc($rezultatZapisnik);
	$br_rez = pg_num_rows($rezultatZapisnik);
	if($br_rez)
	{
	  $vrsta_vozila = $vrstaVozila_Zapisnik = $podaciZapisnik['vrsta_vozila'];
	  $vrstaVozila_Zapisnik = $podaciZapisnik['vrsta_vozila'];
		$datumPregleda_Zapisnik = $podaciZapisnik['datum_pregleda'];
		$datumPregleda_Zapisnik_niz = explode('-',$datumPregleda_Zapisnik);
		$datumPregleda_Zapisnik_prikaz = $datumPregleda_Zapisnik_niz[2].".".$datumPregleda_Zapisnik_niz[1].".".$datumPregleda_Zapisnik_niz[0].".";
		$mestoPregleda_Zapisnik = $podaciZapisnik['mesto_pregleda'];
		$tezinaVozila_Zapisnik = $podaciZapisnik['tezina_vozila'];
		$nosivostVozila_Zapisnik = $podaciZapisnik['nosivost_vozila'];
		$bojaVozila_Zapinsik = $podaciZapisnik['boja_vozila'];
		$brojVrata_Zapisnik = $podaciZapisnik['broj_vrata'];
		$snagaVozila_Zapisnik = $podaciZapisnik['snaga_vozila'];
		$zapreminaVozila_Zapisnik = $podaciZapisnik['zapremina_vozila'];
		$predjenoKm_Zapisnik = $podaciZapisnik['predjeno_km'];
		$stanjeVozila_Zapisnik = $podaciZapisnik['stanje_vozila'];
		$pokretno_Zapisnik = $podaciZapisnik['vozilo_pokretno'];
		$fotografisano_Zapisnik = $podaciZapisnik['vozilo_fotografisano'];
		$uvidMup_Zapisnik = trim($podaciZapisnik['uvid_u_zapisnik_mupa']);
		$ucesceMinimum_Zapisnik = $podaciZapisnik['ucesce_minimum'];
		$rad_Zapisnik = $podaciZapisnik['rad'];
		$farbanje_Zapisnik = $podaciZapisnik['farbanje'];
		$brojMotora_Zapisnik = $podaciZapisnik['broj_motora'];
		$napomena_Zapisnik = $podaciZapisnik['napomena'];
		$trajno_Zapisnik = $podaciZapisnik['trajno'];
		$id_Zapisnik = $podaciZapisnik['id'];
		$datum_pregleda = $podaciZapisnik['datum_pregleda'];
		$datum_pregleda_prikaz = date("d.m.Y", strtotime($datum_pregleda));
		$datum_snimanja = $podaciZapisnik['datum_snimanja'];
		if($datum_snimanja)
		{	
			$datum_snimanja = date("d.m.Y", strtotime($datum_snimanja));
		}
	}
	
	// OVDE SE IZVLACE SVI PODACI IZ KNJIGAS
    $sqlPodaci = "SELECT 	brst,vrstast,brpolise,ucesce,datumproc,
    											substr(extract(year from datumevid)::text,3,2) as godina_otvaranja_stete,
    											upper(imenazivost) as imenazivost,upper(prezimeost) as prezimeost,posbrost,
    											upper(adresaost) as adresaost,datumnast,vremenast,upper(mestost) as mestost,
						  						uzrokstete,vremenskeprilike,markaost,godost,brsasost,regoznakaost,
						  						regpodost,tipost,predjenokm_ost, vrpolise, opstina_stete_id, mesto_stete_id,
  												steta_u_inostranstvu,zemlja_stete_id
				   				FROM 
				   					knjigas 
				   				WHERE 
    								idstete=".$idstete;

	$rez = pg_query($conn_stete,$sqlPodaci);
	$niz_podataka_knjigas = pg_fetch_assoc($rez);
	//print_r($niz_podataka_knjigas);
	//echo "<br>mesto=".$osiguranik_posbroj = $niz_podataka_knjigas['posbrost'];
	
	$broj_stete = $niz_podataka_knjigas['brst'];
	$vrsta_stete = $niz_podataka_knjigas['vrstast'];
	$godina_otv_stete = $niz_podataka_knjigas['godina_otvaranja_stete'];
	$godina_otv_stete_niz = explode('-',$godina_otv_stete);
	$godina_otv_stete_prikaz = $godina_otv_stete_niz[2].".".$godina_otv_stete_niz[1].".".$godina_otv_stete_niz[0];
	$broj_polise = $niz_podataka_knjigas['brpolise'];
	$ugov_ucesce = $niz_podataka_knjigas['ucesce'];
	$dat_pregleda_voz = $niz_podataka_knjigas['datumproc'];
	$dat_pregleda_voz_niz = explode('-',$dat_pregleda_voz);
	$dat_pregleda_voz_prikaz = $dat_pregleda_voz_niz[2].".".$dat_pregleda_voz_niz[1].".".$dat_pregleda_voz_niz[0].".";
	
	$osiguranik_naziv = $niz_podataka_knjigas['imenazivost']." ".$niz_podataka_knjigas['prezimeost'];
	
	//$osiguranik_naziv= $niz_podataka_knjigas['prezimeost'];
	
	
	$osiguranik_posbroj = $niz_podataka_knjigas['posbrost'];
	$osiguranik_adresa = $niz_podataka_knjigas['adresaost'];
	$osiguranik_adresa = strtr($osiguranik_adresa, array("ć" => "Ć","č" => "Č","ž" => "Ž","š" => "Š","đ" => "Đ"));
	$datum_nastanka = $niz_podataka_knjigas['datumnast'];
	// Ukoliko je steta_u_inostranstvu=1::bit onda se za prvi deo uzima naziv zemlje
	// Ako je steta_u_inostranstvu=0::bit onda se za prvi deo uzima naziv opštine i naziv mesta
	if($niz_podataka_knjigas['steta_u_inostranstvu']==0)
		$mesto_nastanka_1 = $sifarnici_class->vratiNazivIspis('opstina', $niz_podataka_knjigas['opstina_stete_id']).", ".$sifarnici_class->vratiNazivIspis('mesto', $niz_podataka_knjigas['mesto_stete_id']);
	else 
		$mesto_nastanka_1 = $sifarnici_class->vratiNazivIspis('zemlje_drzave', $niz_podataka_knjigas['zemlja_stete_id']);
	$mesto_nastanka_2 = $niz_podataka_knjigas['mestost'];
	$mesto_nastanka = $mesto_nastanka_1.", ".$mesto_nastanka_2; 
	$mesto_nastanka = strtr($mesto_nastanka, array("ć" => "Ć","č" => "Č","ž" => "Ž","š" => "Š","đ" => "Đ"));
	$uzrok_stete = $niz_podataka_knjigas['uzrokstete'];
	$vreme_stete = $niz_podataka_knjigas['vremenast'];
	$marka_vozila = $niz_podataka_knjigas['markaost'];
	$god_proizvodnje = $niz_podataka_knjigas['godost'];
	$br_sasije = $niz_podataka_knjigas['brsasost'];
	$reg_oznaka = $niz_podataka_knjigas['regoznakaost'];
	$reg_podrucje = $niz_podataka_knjigas['regpodost'];
	$tip_vozila = $niz_podataka_knjigas['tipost'];
	$predjeno_km = $niz_podataka_knjigas['predjenokm_ost'];
	$vrsta_polise = $niz_podataka_knjigas['vrpolise'];
	
	//$br_odstetnog_zahteva = $vrsta_stete."-".$broj_stete."/".$godina_otv_stete;
	$br_odstetnog_zahteva = $funkcije_class->vrati_broj_predmeta_za_dokumente($idstete);
	
 	$sqlMestoOst = "SELECT * FROM mesta WHERE sifmesta =".$osiguranik_posbroj;
	$rezMesto = pg_query($conn_stete,$sqlMestoOst);
    $niz_mesto = pg_fetch_assoc($rezMesto);
    $osiguranik_mesto = $niz_mesto['mesto'];
    // datum nastanka prikaz
    $datum_nastanka_niz = explode('-',$datum_nastanka);
    $datum_nastanka_prikaz = $datum_nastanka_niz[2].".".$datum_nastanka_niz[1].".".$datum_nastanka_niz[0].".";
    // godina proizvodnje prikaz
    $god_proizvodnje_niz = explode('-',$god_proizvodnje);
//     $god_proizvodnje_prikaz = $god_proizvodnje_niz[2].".".$god_proizvodnje_niz[1].".".$god_proizvodnje_niz[0].".";
    $god_proizvodnje_prikaz = $god_proizvodnje_niz[0].".";
    // uzrok stete iz tabele 
    $uzrok_stete = $niz_podataka_knjigas['uzrokstete'];
    $sqlUzrok = "SELECT upper(opis) as opis FROM uzrok_stete WHERE id=".$uzrok_stete;
    $rezUzrok = pg_query($conn_stete,$sqlUzrok);
    $rezult = pg_fetch_assoc($rezUzrok);
    $opis_uzroka_stete = $rezult['opis'];
    $opis_uzroka_stete = strtr($opis_uzroka_stete, array("ć" => "Ć","č" => "Č","ž" => "Ž","š" => "Š","đ" => "Đ"));
    // registracija
    $registracija = $reg_podrucje."-".$reg_oznaka;
    
    // U zavisnosti koja je vrsta osiguranja, mogu se izvuci podaci:
    $conn_imhotep = pg_connect ("host=imhotep dbname=polise user=admin");
    switch ($vrsta_polise) {
    	case 'AO':
    	case 'OK':
    		$sql_ao = "select snagakw, ccm, nosiv, vrsta,brmot from polise where brpolise=$broj_polise";
    		$rez_ao = pg_query($conn_amso,$sql_ao);
    		$niz_ao = pg_fetch_array($rez_ao);
//     		$snaga_vozila = $niz_ao['snagakw'];
//     		$zapremina_vozila = $niz_ao['ccm'];
//     		$nosivost_vozila = $niz_ao['nosiv'];
//     		$broj_motora = $niz_ao['brmot'];
//     		$vrsta_voz_sifra = $niz_ao['vrsta'];
    		break;
    	case 'AK':
    		$sql_ak = "select vrsta, brmot, snagakw, nosiv, ccm from kasko where brpolise=$broj_polise";
    		$rez_ak = pg_query($conn_amso,$sql_ak);
    		$niz_ak = pg_fetch_array($rez_ak);
    		$snaga_vozila = $niz_ak['snagakw'];
    		$zapremina_vozila = $niz_ak['ccm'];
    		$nosivost_vozila = $niz_ak['nosiv'];
    		$broj_motora = $niz_ak['brmot'];
    		$vrsta_voz_sifra = $niz_ak['vrsta'];
    		break;
    	case 'LS':
    		$sql_ls = "select nosivost, zapremina, snaga from polisels where brpolise=$broj_polise and status='A'";
    		$rez_ls = pg_query($conn_amso,$sql_ls);
    		$niz_ls = pg_fetch_array($rez_ls);
    		$snaga_vozila = $niz_ls['snaga'];
    		$zapremina_vozila = $niz_ls['zapremina'];
    		$nosivost_vozila = $niz_ls['nosivost'];
    		break;
    	default:
    		break;
    }
    
    $sqlPremijskaGrupa = "SELECT upper(naziv) as naziv FROM prem_grupa WHERE grupa like '".$vrsta_voz_sifra."'";
    $rezPremGrupa = pg_query($conn_amso,$sqlPremijskaGrupa);
    $niz_prem = pg_fetch_assoc($rezPremGrupa);
    $vrsta_vozila = $niz_prem['naziv'];
    $vrsta_vozila = strtr($vrsta_vozila, array("ć" => "Ć","č" => "Č","ž" => "Ž","š" => "Š","đ" => "Đ"));
    
    if (!$snagaVozila_Zapisnik)
    	$snagaVozila_Zapisnik = $snaga_vozila;
    if (!$zapreminaVozila_Zapisnik)
    	$zapreminaVozila_Zapisnik = $zapremina_vozila;
    if (!$nosivostVozila_Zapisnik)
    	$nosivostVozila_Zapisnik = $nosivost_vozila;
    if (!$brojMotora_Zapisnik)
    	$brojMotora_Zapisnik = $broj_motora;

    // AKO JE POLISA AK
    $ugov_ucesce_niz = explode(".",$ucesce);
    $ugov_ucesce_procenat = $ugov_ucesce_niz[0];
    if ($ugov_ucesce_procenat == "" || !$ugov_ucesce_procenat) 
   		$ugov_ucesce_procenat = 0;
    
    if($vrsta_stete == 'AO' && $vrstaVozila_Zapisnik=="")
			$vrsta_vozila = '';
    else if($vrstaVozila_Zapisnik=="")
     	$vrsta_vozila = $niz_prem['naziv'];
    else
     	$vrsta_vozila = $vrstaVozila_Zapisnik;
    
    // Učitaj delove ako nije trajno snimljen zapisnik
    if($trajno_Zapisnik == 0)
    {
    	// Učitaj delove za ZAMENU
    	$sqlZamenaZapisnik = "SELECT * FROM zapisnik_o_ostecenju_stavke WHERE id_zapisnik = $id_Zapisnik AND deo_zamena=1::bit ";
    	$rezultatZamenaZapisnik = pg_query($conn,$sqlZamenaZapisnik);
    	$podaciZamenaZapisnik = pg_fetch_all($rezultatZamenaZapisnik);
    	// Učitaj delove za POPRAVKU
    	$sqlPopravkaZapisnik = "SELECT * FROM zapisnik_o_ostecenju_stavke WHERE id_zapisnik = $id_Zapisnik AND deo_popravka=1::bit ";
    	$rezultatPopravkaZapisnik = pg_query($conn,$sqlPopravkaZapisnik);
    	$podaciPopravkaZapisnik = pg_fetch_all($rezultatPopravkaZapisnik);
    	// Učitaj delove za ISPITATI
    	$sqlIspitatiZapisnik = "SELECT * FROM zapisnik_o_ostecenju_stavke WHERE id_zapisnik = $id_Zapisnik AND deo_kontrola=1::bit ";
    	$rezultatIspitatiZapisnik = pg_query($conn,$sqlIspitatiZapisnik);
    	$podaciIspitatiZapisnik = pg_fetch_all($rezultatIspitatiZapisnik);
    	// Učitaj delove za OSTALI RADOVI
    	$sqlOstaliRadoviZapisnik = "SELECT * FROM zapisnik_o_ostecenju_stavke WHERE id_zapisnik = $id_Zapisnik AND deo_o_radovi=1::bit ";
    	$rezultatOstaliRadoviZapisnik = pg_query($conn,$sqlOstaliRadoviZapisnik);
    	$podaciOstaliRadoviZapisnik = pg_fetch_all($rezultatOstaliRadoviZapisnik);
    }
    else
    {
    	$napomena_Zapisnik = '';
    	$rad_Zapisnik = '';
    	$farbanje_Zapisnik = '';
    }
}

echo "<input type=\"submit\" value=\"Zatvori\" class=\"button\" id=\"zatvori_dok\" name=\"zatvori_dok\" style='display:none;' >\n";
echo "</form>\n";

//while ($row = pg_fetch_assoc($result)) {

echo "<form action=\"$PHP_SELF\" name=\"forma_zapisnik\" id=\"forma_zapisnik\" method=\"post\" accept-charset=\"iso8859-2\">\n\n";

echo "<input type=\"hidden\" name=\"idstete\" value=\"$idstete\">\n";
echo "<div class='div_naslov'>Zapisnik o utvrđivanju oštećenja na vozilu\n";

echo "<div class='div_header1' style=''>";
echo "<table >";
echo "<tr>";
echo 	"<td><label for='broj_stete' "; if($br_odstetnog_zahteva) echo " class='label_ima_vrednost'>Broj predmeta:</label>";
echo 	"<input type='text' name='broj_stete' ";  if($br_odstetnog_zahteva) echo "value='".$br_odstetnog_zahteva."' class='readonlyTextboxes' readonly='readonly' /></td>";  
echo 	"<td><label for='broj_polise' "; if($broj_polise) echo " class='label_ima_vrednost' >Broj polise:</label>"; else echo " class='label_nema_vrednost' ";
echo 	"<input type='text' name='broj_polise' "; if($broj_polise) echo "value='".$broj_polise."' class='readonlyTextboxes' readonly='readonly' /></td>"; 
if($vrsta_stete=='AK' && $vrsta_polise=='AK')
{
	echo 	"<td><label for='ucesce_procenat'  ";     if($ugov_ucesce_procenat) echo " class='label_ima_vrednost' >Ugovoreno učešće %:</label>"; else echo " class='label_nema_vrednost' >Ugovoreno učešće %:</label>";
	echo 	"<input type='text' name='ucesce_procenat' onkeypress='return samoBrojevi(event);' "; if($ugov_ucesce_procenat || $ugov_ucesce_procenat==0) echo "value='".$ugov_ucesce_procenat."' class='readonlyTextboxes' readonly='readonly' /></td>"; else echo "/></td>";
	echo 	"<td><label for='ucesce_minimum'  ";     if($ucesceMinimum_Zapisnik) echo " class='label_ima_vrednost' style='float:left;' >Ugovoreno učešće min:</label>"; else echo " class='label_nema_vrednost' style='float:left;' >Ugovoreno učešće min:</label>";
	echo 	"<input type='text' name='ucesce_minimum' onkeypress='return samoBrojevi(event);'  "; if($ucesceMinimum_Zapisnik) echo "value='".$ucesceMinimum_Zapisnik."' class='readonlyTextboxes' readonly='readonly' style='float:left;text-align:right;width:50px;'/><input value='evra' class='readonlyTextboxes' readonly='readonly' ></td>"; else echo "style='float:left;text-align:right;width:50px;'/><input value='evra' class='readonlyTextboxes' readonly='readonly' ></td>";
}
else if($vrsta_stete=='AK' && $vrsta_polise=='LS')
{
	echo 	"<td><label for='ucesce_procenat'  ";     if($ugov_ucesce_procenat) echo " class='label_ima_vrednost' >Ugovoreno učešće %:</label>"; else echo " class='label_nema_vrednost' >Ugovoreno učešće %:</label>";
	echo 	"<input type='text' name='ucesce_procenat' onkeypress='return samoBrojevi(event);' "; if($ugov_ucesce_procenat || $ugov_ucesce_procenat==0) echo "value='".$ugov_ucesce_procenat."' class='readonlyTextboxes' readonly='readonly' /></td>"; else echo "/></td>";
	echo 	"<td><input type='hidden' name='ucesce_minimum' value='NULL' /></td>";
}
else
{
	echo 	"<input type='hidden' name='ucesce_procenat' value='NULL' />";
	echo 	"<input type='hidden' name='ucesce_minimum' value='NULL' />";
}
echo "</tr>";
echo "</table>";
echo "</div>";


echo "<div class='div_header2' style=''>";
echo "<table style='position:relative; width:100%;'>";
echo "<tr>";
echo 	"<td colspan='4' style='background-color:#D3D9DB;'><label style='font-weight:bold;'>PODACI IZ KNJIGE ŠTETA:</label>";
echo "</tr>";
echo "<tr>";
echo 	"<td><label for='datum_pregleda' ";         
		echo " class='label_nema_vrednost' >"; 
	echo "Datum pregleda:</label>";
echo 	"<input type='text' name='datum_pregleda' id='datum_pregleda'  readonly='readonly' style='background-color:#e6e6e6 !important'"; 
	if($dat_pregleda_voz && !$datum_pregleda_prikaz) 
	{
		echo " value='".$dat_pregleda_voz_prikaz."'/></td>"; 
	}
	else if ($datum_pregleda_prikaz)
	{
		echo " value='".$datum_pregleda_prikaz."'/></td>";
	}
	else 
		echo " /></td>";
	echo 	"<td><label for='osiguranik' ";
	if($osiguranik_naziv) 
		echo " class='label_ima_vrednost' >";  
	else 
		echo "class='label_nema_vrednost' >"; 
	echo "Osiguranik:</label>";
echo 	"<input type='text' name='osiguranik' ";
	if($osiguranik_naziv) 
		echo " value='".$osiguranik_naziv."' class='readonlyTextboxes' readonly='readonly'/></td>"; 
	else 
		echo " /></td>";
echo 	"<td><label for='Mesto' ";  			    
	if($osiguranik_mesto) 
		echo " class='label_ima_vrednost' >";  
	else 
		echo "class='label_nema_vrednost' >";
	echo "Mesto:</label>"; 
echo 	"<input type='text' name='mesto' ";
	if($osiguranik_mesto) 
		echo " value='".$osiguranik_mesto."' class='readonlyTextboxes' readonly='readonly'/></td>"; 
	else 
		echo " /></td>";
echo 	"<td><label for='adresa' ";                 
	if($osiguranik_adresa) 
		echo " class='label_ima_vrednost' >";  
	else 
		echo "class='label_nema_vrednost' >"; 
	echo "Adresa:</label>";
echo 	"<input type='text' name='adresa' ";
	if($osiguranik_adresa) 
		echo " value='".$osiguranik_adresa."' class='readonlyTextboxes' readonly='readonly'/></td>"; 
	else 
		echo " /></td>"; 
echo "</tr>";

//ZAKOMENTARISAO VLADA ZBOG NOVOG UPITA
//------------------------ Marko Markovic ---------------- uzroci koji se prenose sa knjige steta -----------------
/*
$conn_amso = pg_connect ("host=localhost dbname=amso user=zoranp");
$sqlUzroci = "SELECT opis,* FROM sifarnici.aktuari_uzroci_i_uos where id = $uzrok_baza";
$rezUzroci = pg_query($conn_amso, $sqlUzroci);
$podaciUzroci = pg_fetch_all($rezUzroci);
$uzrok = $podaciUzroci[0]['opis'];
//$uzrok = strtr($uzrok, array("Ä" => "Ä","Ä" => "Ä","ĹĄ" => "Ĺ ","Ĺž" => "Ĺ˝","Ä" => "Ä"));
*/	

// ---------------- Marko Markovic kraj -----------


//UPIT ZA DOBIJANJE OPISA UZROKA I RIZIKA ZA ODREDJENU STETU - DODAO VLADA
$upit_rizik_uzrok = "

WITH opis_rizika AS(

	SELECT id,opis FROM dblink('host=localhost dbname=amso user=zoranp', 'SELECT id, opis FROM sifarnici.aktuari_rizici ') AS ar (id integer, opis text)
),
opis_uzroka AS(
	SELECT id,opis FROM dblink('host=localhost dbname=amso user=zoranp', 'SELECT id, opis FROM sifarnici.aktuari_uzroci_i_uos ') AS au (id integer, opis text)
)

SELECT ri.opis AS opis_rizika, ou.opis AS opis_uzroka FROM predmet_odstetnog_zahteva AS po
INNER JOIN opis_rizika AS ri ON ri.id = po.rizik_id
INNER JOIN opis_uzroka AS ou ON ou.id = po.uzrok_id
WHERE po.id = $idstete";

$rezultat_rizik_uzrok = pg_query($conn_stete, $upit_rizik_uzrok);
$podaci_rizik_uzrok = pg_fetch_array($rezultat_rizik_uzrok);

//UPIS OPISA RIZIKA U PROMENJIVU
$opis_rizika = $podaci_rizik_uzrok['opis_rizika'];
$uzrok = $podaci_rizik_uzrok['opis_uzroka'];

//SETOVANJE LABELE U ZAVISNOSTI OD VREDNOSTI
$klasa_labela = ($opis_rizika != '') ? 'label_ima_vrednost' : 'label_nema_vrednost';
 
													 
//NOVI RED - DODAO VLADA
echo "<tr>";
echo "<td colspan='2'><label style='float: left;' class=$klasa_labela >Prijavljeni rizik:</label><label id='prijavljeni_rizik' class='readonlyTextboxes' style='float: left; width: 350px; margin-left: 20px;'> $opis_rizika</label></td>";


echo 	"<td colspan='2'><label for='uzrok_nezgode' style='float: left;'";           if($uzrok_stete) echo " class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >"; } echo "Uzrok štete po riziku:</label>";
echo 	"<input type='text' name='uzrok_nezgode' style='float: left; width: 350px;'";  if($uzrok_stete) echo " value='".$uzrok."' class='readonlyTextboxes' readonly='readonly'/></td>";
														else echo " value='".$uzrok."' class='readonlyTextboxes' readonly='readonly'/></td>";		
echo "</tr>";
//DODAO VLADA - KRAJ

echo "<tr>";
echo 	"<td><label for='datum_nastanka' ";          if($datum_nastanka) echo "class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >";} echo "Datum nezgode:</label>";
echo 	"<input type='text' name='datum_nastanka' "; if($datum_nastanka) echo " value='".$datum_nastanka_prikaz."' class='readonlyTextboxes' readonly='readonly'/></td>"; else echo " /></td>"; 
echo 	"<td><label for='mesto_nezgode'  ";          if($mesto_nastanka) echo "class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >"; } echo "Mesto nezgode:</label>";
echo 	"<input type='text' name='mesto_nezgode' ";  if($mesto_nastanka) echo " value='".$mesto_nastanka."' class='readonlyTextboxes' readonly='readonly'/></td>"; else echo " /></td>";

// echo 	"<td><label for='uzrok_nezgode' ";           if($uzrok_stete) echo " class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >"; } echo "Uzrok nezgode:</label>";
// echo 	"<input type='text' name='uzrok_nezgode' ";  if($uzrok_stete) echo " value='".$opis_uzroka_stete."' class='readonlyTextboxes' readonly='readonly'/></td>";  else echo " /></td>";





echo 	"<td><label for='marka_voz' "; if($marka_vozila) echo "class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >";} echo "Marka vozila:</label>";
echo 	"<input type='text' name='marka_voz' "; if($marka_vozila) echo " value='".$marka_vozila."' class='readonlyTextboxes' readonly='readonly'/></td>";  else echo " /></td>";
echo "</tr>";

echo "<tr>";
echo 	"<td><label for='tip_voz' ";  if($tip_vozila) echo "class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >";} echo "Tip vozila:</label>";
echo 	"<input type='text' name='tip_voz' ";  if($tip_vozila) echo " value='".$tip_vozila."' class='readonlyTextboxes' readonly='readonly'/></td>";  else echo " /></td>";
echo 	"<td><label for='god_proiz' ";  if($god_proizvodnje) echo "class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >";} echo "Godina proizvodnje:</label>";
echo 	"<input type='text' name='god_proiz' "; if($god_proizvodnje) echo " value='".$god_proizvodnje_prikaz."' class='readonlyTextboxes' readonly='readonly'/></td>";  else echo " /></td>";
echo 	"<td><label for='br_sasije' ";  if($br_sasije) echo "class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >";} echo "Br. šasije:</label>";
echo 	"<input type='text' name='br_sasije' ";  if($br_sasije) echo " value='".$br_sasije."' class='readonlyTextboxes' readonly='readonly'/></td>";  else echo " /></td>";
echo 	"<td><label for='reg_oznaka'";  if($reg_oznaka) echo "class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >";} echo "Registarsa oznaka:</label>";
echo 	"<input type='text' name='reg_oznaka' ";  if($reg_oznaka) echo " value='".$registracija."' class='readonlyTextboxes' readonly='readonly'/></td>";  else echo " /></td>";
echo "</tr>";

echo "<tr>";
echo 	"<td><label for='vrsta_voz' ";  if($vrsta_vozila) echo "class='label_ima_vrednost' >";  else { echo "class='label_nema_vrednost' >";} echo "Vrsta vozila:</label>";
echo 	"<input type='text' name='vrsta_voz' id='vrsta_voz' onchange='this.value=povecajSlova(this.value);' onkeyup='this.value=povecajSlova(this.value);' "; if($vrsta_vozila) echo " value='".$vrsta_vozila."' class='readonlyTextboxes' readonly='readonly'/></td>";  else echo " /></td>";
echo "</tr>";
echo "<tr>";
echo 	"<td colspan='4' style='background-color:#D3D9DB;'><label style='font-weight:bold;'>PODACI KOJI SE UNOSE NA ZAPISNIKU:</label>";

echo "</tr>";
echo "<tr>";
echo 	"<td><label for='mesto_pregleda' ";
	if($mestoPregleda_Zapisnik) 
		echo "class='label_ima_vrednost' >";  
	else 
		echo "class='label_nema_vrednost' >"; 
	echo "Mesto pregleda:</label>";
echo 	"<input type='text' name='mesto_pregleda' onchange='this.value=povecajSlova(this.value);' onkeyup='this.value=povecajSlova(this.value);' "; 
	if($mestoPregleda_Zapisnik) 
		echo " value='".$mestoPregleda_Zapisnik."' /></td>";
	else
		echo "/> </td>";
echo 	"<td><label for='tezina_voz' ";
	if($tezinaVozila_Zapisnik) 
		echo "class='label_ima_vrednost' >";  
	else 
		echo "class='label_nema_vrednost' >"; 
	echo "Težina vozila:</label>";
echo 	"<input type='text' name='tezina_voz' id='tezina_voz' "; 
	if($tezinaVozila_Zapisnik) 
		echo " value='".$tezinaVozila_Zapisnik."'  onkeypress='return samoBrojeviITacka(event);' /></td>";
	else 
		echo "onkeypress='return samoBrojevi(event);' /> </td>";
echo 	"<td><label for='nosivost_voz' ";
	if($nosivostVozila_Zapisnik)
		echo "class='label_ima_vrednost' >";
	else 
		echo "class='label_nema_vrednost' >";
	echo "Nosivost vozila:</label>";
echo 	"<input type='text' name='nosivost_voz' id='nosivost_voz'  onkeypress='return samoBrojeviITacka(event);'"; 
	if($nosivostVozila_Zapisnik) 
		echo " value='".$nosivostVozila_Zapisnik."'  onkeypress='return samoBrojevi(event);' /> </td>";
	else 
		echo "onkeypress='return samoBrojevi(event);' /> </td>";
echo 	"<td><label for='boja_voz' ";
	if($bojaVozila_Zapinsik)
		echo "class='label_ima_vrednost' >";
	else
		echo "class='label_nema_vrednost' >";
	echo "Boja vozila:</label>";
echo 	"<input type='text' name='boja_voz' id='boja_voz' onchange='this.value=povecajSlova(this.value);' onkeyup='this.value=povecajSlova(this.value);' ";
	if($bojaVozila_Zapinsik)
		echo " value='".$bojaVozila_Zapinsik."' ' /> </td>";
	else
		echo "/> </td>";
echo "</tr>";

echo "<tr>";
echo 	"<td><label for='snaga_voz' ";  
	if($snaga) 
		echo " class='label_ima_vrednost' >"; 
	else if($snagaVozila_Zapisnik) 
		echo " class='label_ima_vrednost' >"; 
	else 
		echo " class='label_nema_vrednost' >";
	echo "Snaga vozila:</label>";
echo 	"<input type='text' name='snaga_voz' id='snaga_voz' maxlength='5' onkeypress='return samoBrojeviITacka(event);'  "; 
	if($snaga) 
		echo "value='".$snaga."' /></td>"; 
	else if($snagaVozila_Zapisnik) 
		echo "value='".$snagaVozila_Zapisnik."' /></td>"; 
	else 
		echo "/></td>";
echo 	"<td><label for='zapremina_voz' "; 
	if($zapremina) 
		echo " class='label_ima_vrednost' >"; 
	else if($zapreminaVozila_Zapisnik) 
		echo " class='label_ima_vrednost' >"; 
	else 
		echo " class='label_nema_vrednost' >";
	echo "Zapremina vozila:</label>";
echo 	"<input type='text' name='zapremina_voz' id='zapremina_voz' maxlength='5' onkeypress='return samoBrojeviITacka(event);' "; 
	if($zapremina) 
		echo "value='".$zapremina."' /></td>"; 
	else if($zapreminaVozila_Zapisnik) 
		echo "value='".$zapreminaVozila_Zapisnik."' /></td>"; 
	else 
		echo "/></td>";
echo 	"<td><label for='broj_vrata' ";
	if($brojVrata_Zapisnik)
		echo "class='label_ima_vrednost' >";
	else
		echo "class='label_nema_vrednost' >";
	echo "Broj vrata:</label>";
echo 	"<input type='text' name='broj_vrata' id='broj_vrata' onkeypress='return samoBrojevi(event);' ";
	if($brojVrata_Zapisnik)
		echo "value='".$brojVrata_Zapisnik."' /></td>";
	else
		echo "/></td>";
echo 	"<td><label for='predjeno_km' ";
	if($predjenoKm_Zapisnik)
		echo "class='label_ima_vrednost' >";
	else if($predjeno_km)
		echo "class='label_ima_vrednost' >";
	else
		echo "class='label_nema_vrednost' >";
	echo "Pređeno km:</label>";
echo 	"<input type='text' name='predjeno_km' id='predjeno_km'  onkeypress='return samoBrojeviIPlusIJednako(event);'  onchange='this.value=povecajSlova(this.value);' onkeyup='this.value=povecajSlova(this.value);'";
	if($predjenoKm_Zapisnik)
		echo "value='".$predjenoKm_Zapisnik."' /></td>";
	else if ($predjeno_km)
		echo "value='".$predjeno_km."' /></td>";
	else
		echo "/></td>";
echo "</tr>";

echo "<tr>";
echo 	"<td><label for='broj_motora' ";
	if($brojMotora_Zapisnik)
		echo "class='label_ima_vrednost' >";
	else
		echo "class='label_nema_vrednost' >";
	echo "Broj motora:</label>";
echo 	"<input type='text' name='broj_motora' id='broj_motora' onchange='this.value=povecajSlova(this.value);' onkeyup='this.value=povecajSlova(this.value);' ";
if($brojMotora_Zapisnik)
	echo "value='".$brojMotora_Zapisnik."' /></td>";
else
	echo "/></td>";

echo 	"<td style='text-align:right;padding-right:10px;'>";
	echo "PODACI O IZVEDENIM RADOVIMA:</label>";
echo 	"</td>";

echo 	"<td><label for='rad' ";
	if($rad_Zapisnik)
		echo "class='label_ima_vrednost' >";
	else
		echo "class='label_nema_vrednost' >";
	echo "RAD:</label>";
echo 	"<input type='text' name='rad' id='rad' onkeypress='return samoBrojeviITackaIMinus(event);' ";
if($rad_Zapisnik)
	echo "value='".$rad_Zapisnik."' /></td>";
else
	echo "/></td>";

echo 	"<td><label for='farbanje' ";
if($farbanje_Zapisnik)
	echo "class='label_ima_vrednost' >";
else
	echo "class='label_nema_vrednost' >";
echo "FARBANJE:</label>";
echo 	"<input type='text' name='farbanje' id='farbanje' onkeypress='return samoBrojeviITackaIMinus(event);' ";
if($farbanje_Zapisnik)
	echo "value='".$farbanje_Zapisnik."' /></td>";
else
	echo "/></td>";

echo "</tr>";

echo "<tr>";

echo 	"<td><label for='stanje_vozila' ";
if($stanjeVozila_Zapisnik)
	echo "class='label_ima_vrednost' >";
else
	echo "class='label_nema_vrednost' >";
echo "Stanje vozila:</label>";
// echo 	"<input type='text' name='stanje_vozila' id='stanje_vozila' onchange='this.value=povecajSlova(this.value);' onkeyup='this.value=povecajSlova(this.value);' ";
// if($stanjeVozila_Zapisnik)
// 	echo "value='".$stanjeVozila_Zapisnik."' /></td>";
// else
// 	echo "/></td>";
	echo "<select name='stanje_vozila' id='stanje_vozila' style='margin-left:32px;width:150px;'>";
		echo "<option value='-1'>-Odaberi stanje vozila-</option>";
		$sql_stanja_vozila = "SELECT * FROM sifarnici.zapisnik_stanja_vozila ORDER BY redosled";
		$rezultat_stanja_vozila = pg_query($conn_stete,$sql_stanja_vozila);
		$niz_stanja_vozila = pg_fetch_all($rezultat_stanja_vozila);
		for ($i = 0; $i < count($niz_stanja_vozila); $i++)
		{
			echo "<option value='".$niz_stanja_vozila[$i]['id']."' ";
			if ($niz_stanja_vozila[$i]['id'] == $stanjeVozila_Zapisnik)  
			{
				echo "selected=selected ";
			}
			echo ">";
			echo $niz_stanja_vozila[$i]['opis'];
			echo "</option>";
		}
	echo "</select>";
echo "</td>";

echo 	"<td>";
echo 	"<label for='vozilo_pokretno' ";
if($pokretno_Zapisnik)
	echo "class='label_ima_vrednost' >";
else
	echo "class='label_nema_vrednost' >";
echo "Vozilo je pokretno:</label>";
echo 	"<label style='position:relative;clear:left;margin-left:30px;'>DA:</label>";
echo 	"<input type='radio' name='vozilo_pokretno' id='vozilo_pokretno_da' value='DA' title='DA' ";
if($pokretno_Zapisnik == 'DA')
	echo "checked=checked";
echo 	"/>";
echo 	"<label style='position:relative; '>NE:</label>";
echo 	"<input type='radio' name='vozilo_pokretno' id='vozilo_pokretno_ne' value='NE' title='NE'";
if($pokretno_Zapisnik == 'NE')
	echo "checked=checked";
echo 	"/>";
echo 	"</td>";

echo 	"<td>";
echo 	"<label for='vozilo_foto' ";
if($fotografisano_Zapisnik)
	echo "class='label_ima_vrednost' >";
else
	echo "class='label_nema_vrednost' >";
echo "Vozilo je fotografisano:</label>";
echo 	"<label style='position:relative;clear:left;margin-left:30px;'>DA:</label>";
echo 	"<input type='radio' name='vozilo_foto' id='vozilo_foto_da' value='DA' title='DA' ";
if($fotografisano_Zapisnik == 'DA')
	echo "checked=checked";
echo 	"/>";
echo 	"<label style='position:relative; '>NE:</label>";
echo 	"<input type='radio' name='vozilo_foto' id='vozilo_foto_ne' value='NE' title='NE'";
if($fotografisano_Zapisnik == 'NE')
	echo "checked=checked";
echo 	"/>";
echo 	"</td>";

echo 	"<td>";
echo 	"<label for='zapisnik_mup' ";
if($uvidMup_Zapisnik)
	echo "class='label_ima_vrednost' >";
else
	echo "class='label_nema_vrednost' >";
echo "Uvid u zapisnik MUP-a:</label>";
echo 	"<label style='position:relative;clear:left;margin-left:30px;'>DA:</label>";
echo 	"<input type='radio' name='zapisnik_mup' id='zapisnik_mup_da' value='DA' title='DA'";
if($uvidMup_Zapisnik == 'DA')
	echo "checked=checked";
echo 	"/>";
echo 	"<label style='position:relative; '>NE:</label>";
echo 	"<input type='radio' name='zapisnik_mup' id='zapisnik_mup_ne' value='NE' title='NE'";
if($uvidMup_Zapisnik == 'NE')
	echo "checked=checked";
echo 	"/>";
echo 	"<label style='position:relative; '>EIOS:</label>";
echo 	"<input type='radio' name='zapisnik_mup' id='zapisnik_mup_eios' value='EIOS' title='EIOS'";
if($uvidMup_Zapisnik == 'EIOS')
	echo "checked=checked";
echo 	"/>";
echo 	"</td>";


echo "</tr>";

echo "<tr>";

	echo 	"<td style='text-align:right;padding-right:10px;'>";
	echo 	"<label for='napomena' ";
	if($napomena_Zapisnik)
		echo "class='label_ima_vrednost' >";
	else
		echo "class='label_nema_vrednost' >";
	echo "NAPOMENA:</label>";
	echo 	"</td>";
	
	echo "<td colspan='3'>";
	echo "<textarea name='napomena' id='napomena' style='resize: none;text-transform: none;' rows='4' cols='100'>$napomena_Zapisnik</textarea>";
	$procenitelj_uradio = $_SESSION["radnik"];
	echo "<input type=\"hidden\" name=\"procenitelj_uradio\" value=\"$procenitelj_uradio\">\n";
	echo "</td>";

echo "</tr>";

echo "<tr>";
echo 	"<td colspan='3'>";
echo 		"<label for='spisak_napomena' style='margin-right:20px;' >Odaberite napomenu:</label>";
echo 		"<select id='spisak_napomena' style='width:700px;'>";
echo 		"<option value='-1'></option>";
// Dodaj ostale opcije
$sql_napomene_zapisnik = "
													SELECT 
														foo.redni_broj_napomene as id, textcat_all(foo.napomena) as napomena 
													FROM 
														(
														SELECT * 
														FROM sifarnici.zapisnik_napomene 
														WHERE 
															obavezno = false 
														    AND 
															pozicija_pdf = 'G'
																AND
															redni_broj_napomene <> 0
														ORDER BY id
														) AS foo 
													GROUP BY foo.redni_broj_napomene 
													ORDER BY foo.redni_broj_napomene;
													";
$rezultat_napomene_zapisnik = pg_query($conn_stete,$sql_napomene_zapisnik);
$niz_napomene_zapisnik = pg_fetch_all($rezultat_napomene_zapisnik);
for ($i = 0; $i < count($niz_napomene_zapisnik); $i++) 
{
	echo "<option value='".$niz_napomene_zapisnik[$i]['id']."'>".$niz_napomene_zapisnik[$i]['napomena']."</option>";
}
echo 		"</select>";
echo 	"</td>";
echo 	"<td>";
echo 		"<input type='button' name='dodaj_napomenu' id='dodaj_napomenu' value='Dodaj napomenu' onclick='return dodajNapomenu();'/>";
echo 	"</td>";
echo "</tr>";

echo "</table>";
echo "</div>";

echo "<div class='div_izbor'>";
echo "<div class='select_lista_delovi' >";
echo "<label for='auto_delovi_kategorija' style='margin-right:10px;'>Sekcija vozila:</label>";
echo "<select name='auto_delovi_kategorija' id='auto_delovi_kategorija' onchange='izborKategorije(this.value, true);'class=\"\">\n";
// echo "<option value='1'>PREDNJI DEO VOZILA</option>";
// echo "<option value='2'>ZADNJI DEO VOZILA</option>";
$sql_kategorije = "SELECT distinct(kategorija) as kategorija FROM sifarnici.zapisnik_auto_delova";
$rezultat_kategorije = pg_query($conn_stete, $sql_kategorije);
$niz_kategorije = pg_fetch_all($rezultat_kategorije);
for ($i = 0; $i < count($niz_kategorije); $i++)
{
	echo "<option value='".$niz_kategorije[$i]['kategorija']."'>".$niz_kategorije[$i]['kategorija']."</option>";
}
echo "</select>";
echo "</div>";
echo "<br/>";
// SELECT LISTA AUTO DELOVI
echo "<div class='select_lista_delovi'>";
echo "<label for='auto_delovi' style='margin-right:30px;'>Deo vozila:</label>";
echo "<select name='auto_delovi' id='auto_delovi' class='lista_podnaslov_option1' name='designation'>\n";
$sql_delovi = "SELECT id, naziv_auto_dela, kategorija, prioritet FROM sifarnici.zapisnik_auto_delova ORDER BY prioritet, naziv_auto_dela";
$rezultat_delovi = pg_query($conn_stete, $sql_delovi);
$niz_delovi = pg_fetch_all($rezultat_delovi);
echo "<option value='-1'></option>";
for ($j = 0; $j < count($niz_delovi); $j++)
{
	if ($niz_kategorije[0]['kategorija'] == $niz_delovi[$j]['kategorija'])
	{
		echo "<option value='".$niz_delovi[$j]['id']."'>".$niz_delovi[$j]['naziv_auto_dela']."</option>";
	}
}
echo "</select>\n";
echo "<div style='float:right;'>";
echo "<select name='amortizacija_dela' id='amortizacija_dela' class='select_lista' >\n";
$sqlStepenOstecenja = "SELECT * FROM sifarnici.zapisnik_procenat_amortizacije_dela ORDER BY procenat;";
$result = pg_query($conn, $sqlStepenOstecenja);
while ($arr = pg_fetch_assoc($result)) {
	echo "<option value=\"" . $arr['procenat'] . "\">";
	echo $arr['opis'] . "\n</option>";
}
echo "</select>\n";
echo "</div>";
echo "</div>";




// SELECT LISTA STEPEN 
echo "<div class='select_lista_stepen' >";
echo "<select name='stepen_ostecenja' id='stepen_ostecenja' class='select_lista' >\n";
echo "<option value=\"0\">Izaberite stepen oštećenja\n";
$sqlStepenOstecenja = "SELECT * FROM sifarnici.zapisnik_stepen_ostecenja_vozila ORDER BY redosled ASC";
$result = pg_query($conn, $sqlStepenOstecenja);
while ($arr = pg_fetch_assoc($result)) {
	echo "<option value=\"" . $arr['id'] . "\"";
	if ($stepen_ostecenja == $arr['id']) {
		echo " SELECTED";
	}
	echo ">" . $arr['stepen_ostecenja_naziv'] . "\n</option>";
}
echo "</select>\n";

echo "</div>";

	echo "<div style='padding-left:30px;width:120px; display:inline; float:left;'>";
		// NAZIVI ZA RADIO BTN
		echo "<div style='width:100px;'>\n";
		echo "<font style='margin-left:8px;' title='Zamena'>Z</font>\n";
		echo "<font style='margin-left:14px;' title='Popravka'>P</font>\n";
		echo "<font style='margin-left:12px;'  title='Ispitati'>I</font>\n";
		echo "<font style='margin-left:13px;' title='Ostali radovi'>O</font>\n";
		echo "</div>\n";
		// RADIO BTN  
		echo "<div style='width:120px;'>";
		echo "<input type='radio' name='popravka_pregled' id='popravka_pregled_z'  value='deo_za_zamenu'   title='Zamena'  onclick='postaviStepenOstecenja(this);'/>\n";
		echo "<input type='radio' name='popravka_pregled' id='popravka_pregled_p'  value='deo_za_popravku'  title='Popravka' onclick='postaviStepenOstecenja(this);'/>\n";
		echo "<input type='radio' name='popravka_pregled' id='popravka_pregled_k'  value='deo_za_kontrolu' title='Ispitati' onclick='postaviStepenOstecenja(this);'/>\n";
		echo "<input type='radio' name='popravka_pregled' id='popravka_pregled_o'  value='deo_ostaliradovi'   title='Ostali radovi' onclick='postaviStepenOstecenja(this);'/>\n";
		echo "</div>";
	echo "</div>";
	echo "<div  style='width:120px; display:inline; float:right;'  >";
	echo "<input type='button' name='skini_deo' class='dugme_minus' title='Izbaci selektovani deo iz liste!' onclick='skiniDeo();'/>";
	echo "<input type='button' name='dodaj_deo' class='dugme_dodaj' title='Dodaj deo u predvidjenu listu!' onclick='dodajDeo();'/>";
	echo "</div>";
echo "</div>";


echo "<div class='div_delovi'>";

echo "<div class='div_za_listu'>";
echo "<font class='font_naslov'>ZAMENA</font>\n";
echo "<select multiple name='lista_zamena' id='lista_zamena' class='lista'>\n";
	// 	Dodaj sve opcije koje su u nizu delova za ZAMENU $podaciZamenaZapisnik
	if ($podaciZamenaZapisnik) 
	{
		for ($i = 0; $i < count($podaciZamenaZapisnik); $i++) 
		{
			$auto_deo_id =  $podaciZamenaZapisnik[$i]['id_zapisnik_auto_delova'];
			$auto_deo_stepen_ostecenja =  $podaciZamenaZapisnik[$i]['id_stepen_ostecenja'];
			$auto_deo_procenat_amortizacije =  $podaciZamenaZapisnik[$i]['procenat_amortizacije'];
			// učitaj vrednosti za VALUE
			$value_muliti_opcije = $auto_deo_id."_".$auto_deo_stepen_ostecenja."_".$auto_deo_procenat_amortizacije;
			// Učitaj naziv auto dela
			$sqlNazivDela = "SELECT naziv_auto_dela FROM sifarnici.zapisnik_auto_delova WHERE id=$auto_deo_id;";
			$rezultatNazivDela = pg_query($conn,$sqlNazivDela);
			$podaciNazivDela = pg_fetch_array($rezultatNazivDela);
			$ispis_muliti_opcije = $podaciNazivDela['naziv_auto_dela'];
			if($auto_deo_procenat_amortizacije != 0)
				$ispis_muliti_opcije .= "(".$auto_deo_procenat_amortizacije."%)"; 
			echo "<option value='$value_muliti_opcije'>$ispis_muliti_opcije</option>";
		}
	}	
echo "</select>\n";
echo "</div>";

echo "<div class='div_za_listu' >\n";
echo "<font class='font_naslov'>POPRAVKA</font>\n";
echo "<select multiple name='lista_popravka' id='lista_popravka' class='lista' title='POPRAVKA'>\n";
	// Dodaj sve opcije koje su u nizu delova za POPRAVKU $podaciPopravkaZapisnik
	if ($podaciPopravkaZapisnik)
	{
		for ($i = 0; $i < count($podaciPopravkaZapisnik); $i++)
		{
			$auto_deo_id =  $podaciPopravkaZapisnik[$i]['id_zapisnik_auto_delova'];
			$auto_deo_stepen_ostecenja =  $podaciPopravkaZapisnik[$i]['id_stepen_ostecenja'];
			$auto_deo_procenat_amortizacije =  $podaciPopravkaZapisnik[$i]['procenat_amortizacije'];
			// učitaj vrednosti za VALUE
			$value_muliti_opcije = $auto_deo_id."_".$auto_deo_stepen_ostecenja."_".$auto_deo_procenat_amortizacije;
			// Učitaj naziv auto dela
			$sqlNazivDela = "SELECT naziv_auto_dela FROM sifarnici.zapisnik_auto_delova WHERE id=$auto_deo_id;";
			$rezultatNazivDela = pg_query($conn,$sqlNazivDela);
			$podaciNazivDela = pg_fetch_array($rezultatNazivDela);
			$ispis_muliti_opcije = $podaciNazivDela['naziv_auto_dela'];
			if($auto_deo_procenat_amortizacije != 0)
				$ispis_muliti_opcije .= "(".$auto_deo_procenat_amortizacije."%)"; 
			echo "<option value='$value_muliti_opcije'>$ispis_muliti_opcije</option>";
		}
	}
echo "</select>\n";
echo "</div>";

echo "<div class='div_za_listu' >";
echo "<font class='font_naslov'>ISPITATI</font>\n";
echo "<select multiple name='lista_kontrola' id='lista_kontrola'  class='lista' title='ISPITATI'>\n";
	// Dodaj sve opcije koje su u nizu delova za IPITATI $podaciIspitatiZapisnik
	if ($podaciIspitatiZapisnik)
	{
		for ($i = 0; $i < count($podaciIspitatiZapisnik); $i++)
		{
			$auto_deo_id =  $podaciIspitatiZapisnik[$i]['id_zapisnik_auto_delova'];
			$auto_deo_stepen_ostecenja =  $podaciIspitatiZapisnik[$i]['id_stepen_ostecenja'];
			$auto_deo_procenat_amortizacije =  $podaciIspitatiZapisnik[$i]['procenat_amortizacije'];
			// učitaj vrednosti za VALUE
			$value_muliti_opcije = $auto_deo_id."_".$auto_deo_stepen_ostecenja."_".$auto_deo_procenat_amortizacije;
			// Učitaj naziv auto dela
			$sqlNazivDela = "SELECT naziv_auto_dela FROM sifarnici.zapisnik_auto_delova WHERE id=$auto_deo_id;";
			$rezultatNazivDela = pg_query($conn,$sqlNazivDela);
			$podaciNazivDela = pg_fetch_array($rezultatNazivDela);
			$ispis_muliti_opcije = $podaciNazivDela['naziv_auto_dela'];
			if($auto_deo_procenat_amortizacije != 0)
				$ispis_muliti_opcije .= "(".$auto_deo_procenat_amortizacije."%)"; 
			echo "<option value='$value_muliti_opcije'>$ispis_muliti_opcije</option>";
		}
	}
echo "</select>\n";
echo "</div>";

echo "<div class='div_za_listu'>";
echo "<font class='font_naslov'>OSTALI RADOVI</font>\n";
echo "<select multiple name='lista_ostaliradovi' id='lista_ostaliradovi' class='lista'>\n";
	// Dodaj sve opcije koje su u nizu delova za OSTALE RADOVE $podaciOstaliRadoviZapisnik
	if ($podaciOstaliRadoviZapisnik)
	{
		for ($i = 0; $i < count($podaciOstaliRadoviZapisnik); $i++)
		{
			$auto_deo_id =  $podaciOstaliRadoviZapisnik[$i]['id_zapisnik_auto_delova'];
			$auto_deo_stepen_ostecenja =  $podaciOstaliRadoviZapisnik[$i]['id_stepen_ostecenja'];
			$auto_deo_procenat_amortizacije =  $podaciOstaliRadoviZapisnik[$i]['procenat_amortizacije'];
			// učitaj vrednosti za VALUE
			$value_muliti_opcije = $auto_deo_id."_".$auto_deo_stepen_ostecenja."_".$auto_deo_procenat_amortizacije;
			// Učitaj naziv auto dela
			$sqlNazivDela = "SELECT naziv_auto_dela FROM sifarnici.zapisnik_auto_delova WHERE id=$auto_deo_id;";
			$rezultatNazivDela = pg_query($conn,$sqlNazivDela);
			$podaciNazivDela = pg_fetch_array($rezultatNazivDela);
			$ispis_muliti_opcije = $podaciNazivDela['naziv_auto_dela'];
			if($auto_deo_procenat_amortizacije != 0)
				$ispis_muliti_opcije .= "(".$auto_deo_procenat_amortizacije."%)"; 
			echo "<option value='$value_muliti_opcije'>$ispis_muliti_opcije</option>";
		}
	}
echo "</select>\n";
echo "</div>";

echo "</div>";

// echo "</div>"; // spusteno na kraj forme  


echo "</form>";
echo 	"<input type='hidden' name='datum_pregleda_pregled' id='datum_pregleda_pregled' value='$dat_pregleda_voz_prikaz' ";
echo "<br>";


$brojdok = pg_num_rows($result);

$sqlImaZap = "SELECT * FROM zapisnik_o_ostecenju_vozila WHERE id_stete=".$idstete;
$rezImaZap = pg_query($conn_stete, $sqlImaZap);
$rez = pg_num_rows($rezImaZap);
if($datum_snimanja)
{
echo "<p>&nbsp;</p><label>Datum snimanja:</label><input id='datum_snimanja' name='datum_snimanja' style='width:120px !important; background-color:#e6e6e6' readonly value='$datum_snimanja'>";
}
else 
{
	echo "<p>&nbsp;</p><label>Datum snimanja:</label><input id='datum_snimanja' name='datum_snimanja' style='width:120px !important; background-color:#e6e6e6' readonly >";
}
echo "<br><input type=\"button\" value=\"Privremeno snimi zapisnik\" class=\"button\" name=\"snimi_dok\"  onclick='snimiZapisnik(false, 0);' style='margin: 20px 0 0 20px;'>\n";
 	echo "<input type=\"button\" value=\"Trajno unesi zapisnik\" class=\"button\" name=\"snimi_dok\"  onclick='snimiZapisnik(false, 1);' style='margin: 20px 0 0 20px;'>\n";
// 	echo "<input type=\"button\" value=\"Unesi zapisnik\" class=\"button\" name=\"snimi_dok\"  onclick='snimiZapisnik(false, 1);' style='margin: 20px 0 0 20px;'>\n";
	echo "<input type=\"button\" value=\"Zatvori\" class=\"button\" name=\"zatvori\"  onclick='zatvoriZapisnik();' style='margin: 20px 0 0 20px;'>\n";
	echo "<input type=\"button\" value=\"Prikaži probni zapisnik\" class=\"button\" name=\"probni_zapisnik\"  onclick='snimiZapisnik(true, 0);' style='margin: 20px 0 0 100px;'>\n";
	echo "<br style='clear:both;'/>";
	echo "<br style='clear:both;'/>";
echo "</div>"; // zatvara glavni div



// 	if($_SERVER['REQUEST_METHOD']=='POST' && $snimi_dok)
// 	{
// 		echo "<br> usao u snimi";
// 	}

}

pg_close($conn_stete);

?>

</body>


<script type='text/javascript'>

function zatvoriZapisnik()
{
	$('#zatvori_dok').click();
}

function dodajNapomenu()
{
	// Proveri da li je izabrana neka napomena
	if($('#spisak_napomena').val() == '-1')
	{
		alert("Odaberite neku napomenu!!!");
		$('#spisak_napomena').focus();
		return false;
	}

	// Prikupi podatak iz selekta 'spisak_napomena'
	var tekst_napomene = $('#spisak_napomena :selected').text();
	// Dodaj u postojeci text sa novim redom u textarea 'napomena'
	if($('#napomena').val()!='')
		$('#napomena').val($('#napomena').val()+'\n'+tekst_napomene);
	else
		$('#napomena').val(tekst_napomene);
	// Postavi spisak napomena na -1
	$('#spisak_napomena').val('-1');
	
	return true;
}

function snimiZapisnik(probni, trajno)
{

	if(trajno == 1)
	{	  
		// Proveri da li je odabrano stanje vozila
		var stanje_vozila = $('#stanje_vozila');
		if(stanje_vozila.val() == -1)
		{
			alert("Niste odabrali STANJE VOZILA!!!");
			stanje_vozila.focus();
			exit;
		}
		// Proveri da li su uneti i da li su brojevi za RAD i FARBANJE
		var rad_forma = $('#rad');
		var farbanje_forma = $('#farbanje');
		if(rad_forma.val() == "")
		{
			alert("Niste uneli vreme potrebno za RAD!!!");
			rad_forma.focus();
			exit;
		}
		else if(isNaN(rad_forma.val()))
		{
			alert("Nepravilan broj za RAD!!!");
			rad_forma.focus();
			exit;
		}
		if(farbanje_forma.val() == "")
		{
			alert("Niste uneli vreme potrebno za FARBANJE!!!");
			farbanje_forma.focus();
			exit;
		}
		else if(isNaN(farbanje_forma.val()))
		{
			alert("Nepravilan broj za FARBANJE!!!");
			farbanje_forma.focus();
			exit;
		}
	
		
		// Proveri da li je odabran bar jedan deo za Zapisnik
		var  broj_delova_za_zamenu = $('#lista_zamena option').size();
		var  broj_delova_za_popravku = $('#lista_popravka option').size();
		var  broj_delova_za_kontrolu = $('#lista_kontrola option').size();
		var  broj_delova_za_ostalo = $('#lista_ostaliradovi option').size();
		
		if (broj_delova_za_zamenu == 0 && broj_delova_za_popravku == 0 && broj_delova_za_kontrolu == 0 && broj_delova_za_ostalo == 0) 
		{
				alert("Odaberite bar jedan deo!!!");
				$('#auto_delovi_kategorija').focus();
				exit;
		}
		
	}

	if(probni)
	{
		// Proveri da li je odabran bar jedan deo za Zapisnik
		var  broj_delova_za_zamenu = $('#lista_zamena option').size();
		var  broj_delova_za_popravku = $('#lista_popravka option').size();
		var  broj_delova_za_kontrolu = $('#lista_kontrola option').size();
		var  broj_delova_za_ostalo = $('#lista_ostaliradovi option').size();
		if (broj_delova_za_zamenu == 0 && broj_delova_za_popravku == 0 && broj_delova_za_kontrolu == 0 && broj_delova_za_ostalo == 0) 
		{
				alert("Odaberite bar jedan deo!!!");
				$('#auto_delovi_kategorija').focus();
				exit;
		}
	}
	var osnovni_predmet_id_reaktiviranog = $('#osnovni_predmet_id_reaktiviranog').val();

	// Proveri da li su označeni DA ili NE za pokretnost, fotografisanost i uvid u zapisnika MUP-a za vozilo
	if(!$('#vozilo_pokretno_da').is(':checked') && !$('#vozilo_pokretno_ne').is(':checked')) 
	{ 
		alert("Niste označili da li je vozilo pokretno!");
	  $('#vozilo_pokretno_da').focus();
	  exit;
	}
	if(!$('#vozilo_foto_da').is(':checked') && !$('#vozilo_foto_ne').is(':checked')) 
	{ 
		alert("Niste označili da li je vozilo fotografisano!");
		$('#vozilo_foto_da').focus();
		exit;
	}
	if(!$('#zapisnik_mup_da').is(':checked') && !$('#zapisnik_mup_ne').is(':checked') && !$('#zapisnik_mup_eios').is(':checked')) 
	{ 
		alert("Niste označili da li postoji uvid u zapisnik MUP-a!");
		$('#zapisnik_mup_da').focus();
		exit;
	}
	
	
//---------------------------------------------------------------------------------
// dohvatanje vrednosti iz liste za ZAMENU delova 
  var niz_lista_zamena_value_id_auto_deo = new Array();
  var niz_lista_zamena_value_id_stepen_ost = new Array();
  var niz_lista_zamena_value_amortizacija_dela = new Array();
  var i = 0;
  $('#lista_zamena option').each(function(){
  	var value = $(this).val();
    var niz_value = value.split("_");
    var id_auto_deo = niz_value[0];
    var id_stepen_ost = niz_value[1];
    var amortizacija_dela = niz_value[2];
    niz_lista_zamena_value_id_auto_deo[i] = id_auto_deo;
   	niz_lista_zamena_value_id_stepen_ost[i] = id_stepen_ost;
   	niz_lista_zamena_value_amortizacija_dela[i] = amortizacija_dela;
   	i++;  
  });
  br= niz_lista_zamena_value_id_auto_deo.length;
 	//alert('elemet liste='+niz_lista_zamena_value_amortizacija_dela[1]);exit;
  var zamena_id_auto = niz_lista_zamena_value_id_auto_deo.join(",");
  var zamena_id_stepen = niz_lista_zamena_value_id_stepen_ost.join(','); 
  var zamena_amortizacija_dela = niz_lista_zamena_value_amortizacija_dela.join(',');

//------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------
	// dohvatanje vrednosti iz liste za POPRAVKA delova 
	var niz_lista_popravka_value_id_auto_deo = new Array();
	var niz_lista_popravka_value_id_stepen_ost = new Array();
	var niz_lista_popravka_value_amortizacija_dela = new Array();
	var i = 0;
	$('#lista_popravka option').each(function(){
		var value = $(this).val();
		var niz_value = value.split("_");
		var id_auto_deo = niz_value[0];
		var id_stepen_ost = niz_value[1];
	  var amortizacija_dela = niz_value[2];
		niz_lista_popravka_value_id_auto_deo[i] = id_auto_deo;
		niz_lista_popravka_value_id_stepen_ost[i] = id_stepen_ost; 
		niz_lista_popravka_value_amortizacija_dela[i] = amortizacija_dela;
	  i++; 
	});
	br= niz_lista_popravka_value_id_auto_deo.length;
//	alert('elemet liste='+niz_lista_popravka_value_id_auto_deo[i]);
	var popravka_id_auto = niz_lista_popravka_value_id_auto_deo.join(",");
	var popravka_id_stepen = niz_lista_popravka_value_id_stepen_ost.join(',');
	var popravka_amortizacija_dela = niz_lista_popravka_value_amortizacija_dela.join(','); 

//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
// dohvatanje vrednosti iz liste za ISPITATI delova 
	var niz_lista_kontrola_value_id_auto_deo = new Array();
	var niz_lista_kontrola_value_id_stepen_ost = new Array();
	var niz_lista_kontrola_value_amortizacija_dela = new Array();
	var i = 0;
	$('#lista_kontrola option').each(function(){
		var value = $(this).val();
		var niz_value = value.split("_");
		var id_auto_deo = niz_value[0];
		var id_stepen_ost = niz_value[1];
	  var amortizacija_dela = niz_value[2];
		niz_lista_kontrola_value_id_auto_deo[i] = id_auto_deo;
		niz_lista_kontrola_value_id_stepen_ost[i] = id_stepen_ost;  
		niz_lista_kontrola_value_amortizacija_dela[i] = amortizacija_dela;
	  i++;
	});
	br= niz_lista_kontrola_value_id_auto_deo.length;
// alert('elemet liste='+niz_lista_kontrola_value_id_auto_deo[i]);
	var kontrola_id_auto = niz_lista_kontrola_value_id_auto_deo.join(",");
	var kontrola_id_stepen = niz_lista_kontrola_value_id_stepen_ost.join(','); 
	var kontrola_amortizacija_dela = niz_lista_kontrola_value_amortizacija_dela.join(',');
// alert('elementi liste kontrola='+kontrola_id_auto);
//------------------------------------------------------------------------------------
// dohvatanje vrednosti iz liste za ORADOVI delova 
	var niz_lista_oradovi_value_id_auto_deo = new Array();
	var niz_lista_oradovi_value_id_stepen_ost = new Array();
	var niz_lista_oradovi_value_amortizacija_dela = new Array();
	var i = 0;
	$('#lista_ostaliradovi option').each(function(){
		var value = $(this).val();
		var niz_value = value.split("_");
		var id_auto_deo = niz_value[0];
		var id_stepen_ost = niz_value[1];
	  var amortizacija_dela = niz_value[2];
		niz_lista_oradovi_value_id_auto_deo[i] = id_auto_deo;
		niz_lista_oradovi_value_id_stepen_ost[i] = id_stepen_ost;  
		niz_lista_oradovi_value_amortizacija_dela[i] = amortizacija_dela;
	  i++;
	});
	br= niz_lista_oradovi_value_id_auto_deo.length;
	var oradovi_id_auto = niz_lista_oradovi_value_id_auto_deo.join(",");
	var oradovi_id_stepen = niz_lista_oradovi_value_id_stepen_ost.join(',');
	var oradovi_amortizacija_dela = niz_lista_oradovi_value_amortizacija_dela.join(','); 

     // AJAX POZIV 
	var  forma_zapisnik = $("#forma_zapisnik").serializeArray();
	var  datum_snimanja = $("#datum_snimanja").val();
	if(!datum_snimanja || datum_snimanja=="" ) 
	{ 
		alert("Niste uneli datum snimanja!");
		$('#datum_snimanja').focus();
		exit;
	}
/*
     for ( var pomocma = 0; pomocma < forma_zapisnik.length; pomocma++) 
			{
				alert(pomocma+'. '+forma_zapisnik[pomocma]['name']);
			}
    exit;
*/

	var url = 'zapisnik_o_ostecenju_snimanje.php';
	
	if(probni)
	{	
		var prijavljeni_rizik = $('#prijavljeni_rizik').html();
		var napomena = $("#napomena").val();
		var napomena_niz = napomena.split("\n");
		//alert(napomena_niz);exit;
		//var napomena1 = napomena.substr(0,500);
		var napomena1 = napomena_niz[0];
	//	var napomena2 = napomena.substr(500,500);
		var napomena2 = napomena_niz[1];
	//	var napomena3 = napomena.substr(1000,500);
		var napomena3 = napomena_niz[2];
	//	var napomena4 = napomena.substr(1500,500);
		var napomena4 = napomena_niz[3];
		var napomena5 = napomena_niz[4];
		var napomena6 = napomena_niz[5];
		var napomena7 = napomena_niz[6];
		var napomena8 = napomena_niz[7];
		var napomena9 = napomena_niz[8];
		var napomena10 = napomena_niz[9];
		var napomena11 = napomena_niz[10];
		var napomena12 = napomena_niz[11];
		var napomena13 = napomena_niz[12];
		var napomena14 = napomena_niz[13];
		var napomena15 = napomena_niz[14];
		//alert("Završi poziv PROBNOG zapisnika!");
		//exit;
		// AJAX poziv ka prikazu PDF-a za probni zapisnik
		//window.open("zapisnik_kreiranje_pdf.php?probni=DA&podaci="+forma_zapisnik, "_blank");
		window.open( "zapisnik_kreiranje_pdf.php" 
									+ "?probni=DA&" 
									+ $("#forma_zapisnik").serialize()
									+ "&prijavljeni_rizik=" + prijavljeni_rizik
									+ "&zamena="+JSON.stringify(zamena_id_auto)
									+ "&popravka="+JSON.stringify(popravka_id_auto)
									+ "&kontrola="+JSON.stringify(kontrola_id_auto)
									+ "&ostalo="+JSON.stringify(oradovi_id_auto)
									+ "&zamena_amortizacija="+JSON.stringify(zamena_amortizacija_dela)
									+ "&popravka_amortizacija="+JSON.stringify(popravka_amortizacija_dela)
									+ "&kontrola_amortizacija="+JSON.stringify(kontrola_amortizacija_dela)
									+ "&ostalo_amortizacija="+JSON.stringify(oradovi_amortizacija_dela)
									+ "&napomena1="+napomena1
									+ "&napomena2="+napomena2
									+ "&napomena3="+napomena3
									+ "&napomena4="+napomena4
									+ "&napomena5="+napomena5
									+ "&napomena6="+napomena6
									+ "&napomena7="+napomena7
									+ "&napomena8="+napomena8
									+ "&napomena9="+napomena9
									+ "&napomena10="+napomena10
									+ "&napomena11="+napomena11
									+ "&napomena12="+napomena12
									+ "&napomena13="+napomena13
									+ "&napomena14="+napomena14
									+ "&napomena15="+napomena15
									+ "&popravka_stepen_ostecenja="+JSON.stringify(popravka_id_stepen)
									+ "&datum_snimanja="+datum_snimanja
									, '_blank' );
		exit;
	} 
	else
	{
	
    	$.ajax({
    		cache: false,
    		url: url,
    		type: 'POST',
    		data:{ podaci_sa_forme: forma_zapisnik, trajno: trajno, 
    			   lista_zamena_id_auto: zamena_id_auto, lista_zamena_id_stepen: zamena_id_stepen, lista_zamena_amortizacija_dela: zamena_amortizacija_dela,
    			   lista_popravka_id_auto: popravka_id_auto, lista_popravka_id_stepen: popravka_id_stepen, lista_popravka_amortizacija_dela: popravka_amortizacija_dela,
    			   lista_kontrola_id_auto: kontrola_id_auto, lista_kontrola_id_stepen: kontrola_id_stepen, lista_kontrola_amortizacija_dela: kontrola_amortizacija_dela,
    			   lista_oradovi_id_auto: oradovi_id_auto, lista_oradovi_id_stepen: oradovi_id_stepen, lista_oradovi_amortizacija_dela: oradovi_amortizacija_dela, osnovni_predmet_id_reaktiviranog:osnovni_predmet_id_reaktiviranog, datum_snimanja:datum_snimanja},
    		dataType: "json",
    		success: function(t) {
        	
    			switch( t['rezultat'] )
    			{
    				case 1:
    					id_stete = t['idstete'];
    					dopunski = t['dopunski'];
    					alert('Uspesno ste uneli zapisnik');
							if (trajno == 1) 
							{
								window.open("zapisnik_kreiranje_pdf.php?id_stete="+id_stete+"&dopunski="+dopunski, "_blank");	
							}
      				$('#zatvori_dok').click(); 
    					break;
    				case 0:
    					alert('Neuspešan unos zapisnika! ');
    					alert(t['poruka']); 
    					break;
    				case 2:
    					alert('Neuspesan unos zapisnika! stavke');
    					break;
    				case 3:
    					alert('Neuspesan unos zapisnika! zapisnik');
    					break;
    			}

    		 }
    	  });	
	}

}
$( document ).ready(function() {

	var datum_pregleda_pregled = $("#datum_pregleda_pregled").val();
	
	
	$( "#datum_pregleda" ).datepicker({
			
			showWeek: true,
			firstDay: 1,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'dd.mm.yy',
			maxDate: new Date()
		});
	
	if(datum_pregleda_pregled!='...'){
		$( "#datum_pregleda" ).datepicker('option','minDate',datum_pregleda_pregled);
		}
	
	
	$( "#datum_snimanja" ).datepicker({
		
		showWeek: true,
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'dd.mm.yy',
		maxDate: new Date()
	});	
	
	if(datum_pregleda_pregled!='...'){
		$( "#datum_snimanja" ).datepicker('option','minDate',datum_pregleda_pregled);
		}

});
		


//Kraj funkcije
</script>

</html>
