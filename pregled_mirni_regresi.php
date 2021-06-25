<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);

session_start();
if (isset($_SESSION['radnik']) && $_SESSION['radnik']) {
$radnik = $_SESSION['radnik'];
}
else {
session_destroy();
header("Location: ../../login.php");
exit();
}

if (isset($_POST)) {
	foreach ($_POST as $kljuc => $vrednost) {
	${$kljuc} = $vrednost;
	}
}

date_default_timezone_set ('Europe/Belgrade');

//$upit = "SET client_encoding TO 'UTF8'";
//$rezultat = pg_query($conn, $upit);

require "funkcije.php";

require "../../common/sifarnici_class.php";
$sifarnici_class = new sifarnici_class();

require "../../common/funkcije_class.php";
$funkcije_class = new funkcije_class();


$conn = pg_connect ("host=localhost dbname=stete user=zoranp");
if (!$conn) {
    echo "<br><br>Doslo je do greske prilikom konektovanja.\n";
    exit;
}

function kreirajUpit($danod, $dando) {
// SVI PREDMETI ODSTETNOG ZAHTEVA KOJI SU OTVORENI U OVOM PERIODU i bilo koja 4 polja iz pravne razlicite od null
// TABELE: STETNI_DOGADJAJ,, ODSTETNI_ZAHTEV, PREDMET_ODSTETNOG_ZAHTEVA
// TABELA: PRAVNI (vrstaregpotr,oznakaregpotr,osiguranjeregpotr,drzavaregpotr)

	$upit = "select vrsta_osiguranja,idsdog, idstete,g.broj_polise,g.nalog AS nalog,CASE WHEN (stari_broj_predmeta IS NOT NULL) THEN get_broj_predmeta(0,idstete) ELSE novi_broj_predmeta END AS netreba, CASE WHEN (datum_otvaranja_predmeta < '2015-01-01'::date AND sudski_postupak_id IS NULL) THEN stari_broj_predmeta ELSE novi_broj_predmeta  END AS broj_predmeta, datum_otvaranja_predmeta as datum,CASE WHEN prezimeost NOTNULL THEN prezimeost else '' end || ' ' || case when imenazivost notnull then imenazivost else '' end AS osteceni, izbor, potvrda, oznakaregpotr, isplaceno,faza,g.regresno_potrazivanje_napomena from ";

	$upit .= "(select vrsta_osiguranja, a.id as idstete, novi_broj_predmeta, c.id as idsdog, a.sudski_postupak_id AS sudski_postupak_id,vrsta_osiguranja || ' ' || brst || ' / ' || case when datumkompl isnull and datumevid isnull then '' else substr(extract(year from case when datumkompl isnull then datumevid else datumkompl end)::text,3,2) end  as broj_stete,stari_broj_predmeta, concat(vrstaregpotr, oznakaregpotr, osiguranjeregpotr, drzavaregpotr,radnik_evidentirao_potvrdu_za_regres) as izbor,isplaceno,faza, prezimeost, imenazivost,broj_polise, potvrdjen_osnov_za_regres as potvrda, oznakaregpotr, datum_otvaranja_predmeta, razlog_regresa_id, regres_od, nalog,p.regresno_potrazivanje_napomena AS regresno_potrazivanje_napomena from predmet_odstetnog_zahteva as a, odstetni_zahtev as b, stetni_dogadjaj as c, pravni AS p where a.odstetni_zahtev_id = b.id and b.stetni_dogadjaj_id = c.id and a.id = p.idstete and datum_otvaranja_predmeta >= to_date('$danod', 'DD.MM.YYYY') AND datum_otvaranja_predmeta <= to_date('$dando', 'DD.MM.YYYY') ) as g where (izbor != '' OR regres_od !='Izaberite' OR razlog_regresa_id > 0 ) AND potvrda is NOT false";
	return $upit;
//	order by vrsta_osiguranja,idsdog,idstete,g.broj_polise
}
function zaPrikaz($conn, $danod, $dando) {

$prikaz=$_POST['prikaz'];
$dugme='DA';
$tekst='Datum otvaranja predmeta';
	$i = 1;
	$upit = "SET client_encoding TO 'UTF8'";
	$rezultat = pg_query($conn, $upit);

	$upit = kreirajUpit($danod, $dando );
  // dodatI podaci iz regresa i veza sa tabelom steta_regres  COALESCE(brreg,oznakaregpotr) AS
  $upit = "select vrsta_osiguranja,idsdog, idstete,idregres,broj_polise, broj_predmeta, datum AS datum2, to_char(datum, 'DD.MM.YYYY.') AS datum, CASE WHEN potvrda THEN 'DA' ELSE CASE WHEN NOT potvrda THEN 'NE' ELSE '&nbsp;' END END AS potvrda, osteceni, brreg,duznik,adresa, izbor,sum(potrazuje) AS potrazuje, sum(isplaceno) AS isplaceno,faza, to_char(nalog, 'DD.MM.YYYY.') AS nalog, regresno_potrazivanje_napomena
   from ( select foo.vrsta_osiguranja, foo.idstete,foo.idsdog, fo.idregres, isplaceno,faza,broj_predmeta,datum, foo.nalog AS nalog, COALESCE(brreg,oznakaregpotr) AS brreg,duznik,adresa,broj_polise,iznosisp as potrazuje, izbor, osteceni,potvrda, foo.regresno_potrazivanje_napomena AS regresno_potrazivanje_napomena  from ($upit) as foo LEFT OUTER JOIN (select idstete, idregres,brreg, CASE WHEN prezime_reg NOTNULL THEN prezime_reg else '' end || ' ' || case when ime_reg notnull then ime_reg else '' end AS duznik,adresa_reg as adresa,iznosisp from (select  b.idstete, a.idregres as idregres,brreg,prezime_reg,ime_reg,adresa_reg,iznosisp from regresna as a, steta_regres as b where a.idregres=b.idregres) as fg ) as fo ON (foo.idstete = fo.idstete) ) as goo ";

  $upit .= " WHERE (isplaceno != 0) ";

  if ($prikaz == '2')
    $upit .= " AND (brreg ISNULL OR brreg='') ";
  
  if ($prikaz == '3')
	  $upit .= " AND (brreg IS NOT NULL) ";
if ($prikaz == '4')
  	$upit .= " AND potvrda IS TRUE AND (brreg ISNULL OR brreg='') and nalog is not null  "; 

  $upit .= "GROUP BY vrsta_osiguranja,idsdog, idstete,idregres,broj_predmeta,datum,brreg,duznik,adresa,izbor,broj_polise,osteceni, faza,potvrda, nalog,regresno_potrazivanje_napomena ORDER BY datum2, vrsta_osiguranja,idsdog, broj_polise,idstete,idregres,broj_predmeta,potvrda";

	$rezultat = pg_query($conn, $upit);

//  echo $upit;

	/*

	<th width='52%' style="color: white;background-color: gray;" colspan=6 ><b>MIRNI POSTUPAK</b></th>
	  <th width='46%' style="color: white;background-color: black;" colspan=4 ><b>REGRESNO POTRAžIVANJE</b></th></tr>
		<tr>
			<th width='9%' style="color: white;background-color: gray;text-align:center;"><b>Regres</b></th>
			<th width='13%' style="color: white;background-color: gray;text-align:center;"><b>Dužnik</b></th>
			<th width='14%' style="color: white;background-color: gray;text-align:center;"><b>Adresa</b></th>
			<th width='10%' style="color: white;background-color: gray;text-align:center;"><b>Potraživanje</b></th>
	*/
	$dugme=($prikaz=='4')?'<th><b></b></th>':null;
	echo <<<EOF
	<br />
	<table id="thetable" style="width:98%;" cellspacing="0">
	<thead>
	  <tr style="color: white;font-weight:bold;background-color: black;">
	 		<th><b>R.br</b></th>
			<th><b>Predmet</b></th>
			<th><b>Datum</b></br>otvaranja predmeta</th>
			<th><b>Datum</b></br>rešavanja</th>
			<th><b>Oštećeni</b></th>
			<th><b>Br.polise</b></th>
			<th><b>Rešeno</b></th>
			<th><b>Faza</b></th>
			<th><b>Potvrđen osnov u JKŠ</b></th>
			<th><b>Regres</b></th>
			<th><b>Regresni<br />dužnik</b></th>
			<th><b>Adresa</b></th>
			<th><b>Regresno<br/>potraživanje</b></th>
			<th><b>Isplaćeno u finansijama</b></th>
			<th><b>Napomena</b></th>
			$dugme
			</tr>
		</tr>
	</thead>
	<tbody>
EOF;
	while ($arr = pg_fetch_assoc($rezultat)) {
	foreach ($arr as $kljuc => $vrednost) {
		${$kljuc} = $vrednost;
	}
	$placeno_finansije = prikazi_placeno($idstete);

	$placeno_finansije = $placeno_finansije['suma'];

	//IZMENJENO FORMATIRANJE
	$placeno_finansije_uslov=($placeno_finansije>=number_format($isplaceno, 2))?true:false;
	//$placeno_finansije_uslov = true;

	
	if(($prikaz == '4' && $placeno_finansije_uslov) || $prikaz =='1' || $prikaz =='2' || $prikaz =='3' ){
	
	if ($i == 1) {
		echo "<tr class=\"first\">\n";
	}
	else {
		echo "<tr>\n";
	}
	$broj = isset($broj) && $broj ? number_format($broj, 0, ',', '.') : '';
//	$potrazuje = isset($potrazuje) && $potrazuje ? number_format($potrazuje, 2, ',', '.') : '';
	$brreg = isset($brreg) ? $brreg : 'Nema regresa';
	$duznik = trim($duznik);

/*
 	if(isset($potvrda)==t) { $potvrda='DA';}
	else
	{
	if(isset($potvrda)==f) { $potvrda='NE';}
	else $potvrda=NULL;
	}
*/
	echo "<td class=\"centar\">$i</td>\n";
	echo "<td class=\"centar\">\n";
	echo "<a href=\"../../stete/pregled.php?idstete=$idstete&dugme=DA\" target=\"_blank\">$broj_predmeta</a></td>\n";
	echo "<td title=\"$tekst\" class=\"centar\">$datum</td>\n";
	echo "<td class=\"centar\">$nalog</td>\n";
	echo "<td>$osteceni</td>\n";
	echo "<td class=\"centar\">$broj_polise</td>\n";
	echo "<td class=\"broj\">" . number_format($isplaceno, 2, ',', '.') . "</td>\n";
	echo "<td>$faza</td>\n";
  	echo "<td class=\"centar\">$potvrda</td>\n";

  echo "<td align=\"center\">\n";
  if ($brreg !='Nema regresa')
    //  echo "<a href=\"regresna_ispravka.php?idregres=" . $idregres . " \"target=regresna_ispravka.php\">\n";
//echo $brreg . "</a></td>\n";
      echo "<a href='regresna.php?idregres=$idregres&status=izmeni' TARGET='top'>".  $brreg . "</a></td>\n";
      else { echo "$brreg</td>\n";}
//      else { echo "<td  class=\"centar\">$brreg</td>\n";}

	echo "<td>$duznik</td>\n";
	echo "<td>$adresa</td>\n";
	echo "<td class=\"broj\">" . number_format($potrazuje, 2, ',', '.') . "</td>\n";
	echo "<td class=\"broj\">" . $placeno_finansije . "</td>\n";
	echo "<td>$regresno_potrazivanje_napomena</td>\n";
	if($prikaz == '4' && $placeno_finansije_uslov){
		
		//echo "<td><button id='otvori' onclick='otvori()' target='_blank' type='button' style='align:center' name='otvori'>Otvori</button></td>\n";
		echo "<td><button type=\"button\" id=\"otvori_regres\" onclick=\"redirekcija_regres('$idstete')\">Otvori</button></td>";
	}
	echo "</tr>\n";
	$i++;
	}}
	while ($i < 20)
{
		echo "<tr>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<td>&nbsp;</td>\n";
		echo "</tr>\n";
		$i++;
	}

	echo "</tbody>\n</table><br />\n";

		echo <<<EOF
	<script type="text/javascript" language="javascript">
	//<![CDATA[
	$.fn.tableScroll.defaults =
	{
		flush: true, // makes the last thead and tbody column flush with the scrollbar
		width: null, // width of the table (head, body and foot), null defaults to the tables natural width
		height: 360, // height of the scrollable area
		containerClass: 'tablescroll' // the plugin wraps the table in a div with this css class
	};
//]]>
</script>
EOF;
}


//AKO JE FUNKCIJA U POSTU DOHVATI PRIKAZI PLACENO,POZOVI JE
if(isset($_POST['funkcija']) && $_POST['funkcija'] == 'prikazi_placeno')
{
	$suma_glavna_knjiga = prikazi_placeno($_POST['id_stete']);
	echo json_encode($suma_glavna_knjiga);
	die();
}

function prikazi_placeno($idstete){

	//$ret = new stdClass();

	$conn_amso = pg_connect ("host=localhost dbname=amso user=zoranp");
	if(!$conn_amso){
		echo "Greška otvaranja konekcije prema SQL serveru";
	}
	$conn_stete = pg_connect ("host=localhost dbname=stete user=zoranp");
	if(!$conn_stete){
		echo "Greška otvaranja konekcije prema SQL serveru";
	}


	$sql_dohvati_podatke_za_predmet="SELECT poz.novi_broj_predmeta,i.datum_naloga as datum_naloga, sp.brsp as brsp
	FROM predmet_odstetnog_zahteva poz
	INNER JOIN isplate i ON (poz.id=i.idstete)
	LEFT OUTER JOIN sudski_postupak sp ON (poz.sudski_postupak_id=sp.idsp)
	WHERE poz.id=$idstete
	ORDER BY i.datum_naloga asc";

	$rezultat_dohvati_podatke_za_predmet = pg_query($conn_stete, $sql_dohvati_podatke_za_predmet);
	$niz_dohvati_podatke_za_predmet = pg_fetch_all($rezultat_dohvati_podatke_za_predmet);

	//najmanji datum naloga iz isplata
	$datum_naloga=$niz_dohvati_podatke_za_predmet[0]['datum_naloga'];
	
	$godina_nalog=substr($datum_naloga, 0,4);
	
	$godina=$godina_nalog;
	if($niz_dohvati_podatke_za_predmet){

		$br = count($niz_dohvati_podatke_za_predmet);

		for($i = 0; $i < $br; $i++)
		{
			$novi_broj_predmeta = $niz_dohvati_podatke_za_predmet[$i]['novi_broj_predmeta'];
		
						
			//podaci iz glavne knjige

			$provera_placanja_upit = "WITH provera_placanja AS(";

			$provera_placanja_upit .= "SELECT gk.duguje, gk.datknjiz, gk.partner,  gk.opisdok, gk.vrstadok,p.naziv,p.adresa, p.mesto, gk.brdok
			FROM g$godina gk inner join partneri p on gk.partner=p.sifra
			WHERE gk.konto LIKE '430%'
			AND (brojdok='$novi_broj_predmeta')
			AND gk.potrazuje=0
			";

			for ($i = $godina + 1; $i <= date('Y'); $i++) {

				$provera_placanja_upit .= " UNION ALL SELECT gk.duguje, gk.datknjiz, gk.partner,  gk.opisdok, gk.vrstadok,p.naziv,p.adresa, p.mesto, gk.brdok
				FROM g$i gk inner join partneri p on gk.partner=p.sifra
				WHERE gk.konto LIKE '430%'
				AND (brojdok='$novi_broj_predmeta')
				AND gk.potrazuje=0";
			}

			$provera_placanja_upit .= ")";
			$provera_placanja_upit .= "SELECT sum(duguje) AS suma, MAX(datknjiz) AS datum_isplate FROM provera_placanja";

			$rez_provera_placanja = pg_query($conn_amso, $provera_placanja_upit);
			$niz_provera_placanja = pg_fetch_array($rez_provera_placanja);
			$ukupan_br=count($niz_provera_placanja);

			if(!empty($niz_provera_placanja)){

				$niz_slanje = array();

				$niz_slanje['suma'] = number_format($niz_provera_placanja['suma'], 2);
				$niz_slanje['datum_isplate'] = $niz_provera_placanja['datum_isplate'];

			}
		}
		
	} //kraj if empty
	
	return $niz_slanje;
  
}
zaPrikaz($conn, $danod, $dando);
?>
<script>

//FUNKCIJA NA KLIK DUGMETA OTVORI - DODAO VLADA
function redirekcija_regres(idstete) {

	var naslov = 'Na stranici predmet odštetnog zahteva - sekcija: Regresno potraživanje' + '\n' + '\n';
	var poruka = '';

	var id_stete = idstete;
	var id_regresa = '';

	var funkcija = 'dohvati_podatke_regres';

	var provera = false;

	$.ajax({

		url: 'funkcije.php',
		method: 'POST',
		dataType: 'json',

		data: {funkcija:funkcija, id_stete:id_stete, id_regresa:id_regresa},

		success: function(data) {

			console.log(data);

			//AKO U BAZI NE POSTOJI BILO KOJI OD PODATAKA
			if(!data.broj_polise) {

				poruka += 'Morate uneti broj polise.' + '\n';
			}
			if(!data.id_stete) {

				poruka += 'Morate uneti broj štete.' + '\n';
			}
			if(!data.novi_broj_predmeta) {

				poruka += 'Morate uneti broj predmeta.' + '\n';
			}
			if(!data.vrsta_obrasca) {

				poruka += 'Morate uneti vrstu obrasca.' + '\n';
			}
			if(!data.regres_od) {

				poruka += 'Morate uneti od koga je regres.' + '\n';
			}
			if(!data.tip_lica) {

				poruka += 'Morate uneti tip lica.' + '\n';
			}
			if(!data.jmbg_reg) {

				poruka += 'Morate uneti jmbg.' + '\n';
			}
			if(!data.telefon_reg) {

				poruka += 'Morate uneti telefon.' + '\n';
			}
			if(!data.zemlja_id || data.zemlja_id == '-1') {

				poruka += 'Morate uneti zemlju.' + '\n';
			} 
			if(!data.adresa_reg) {
				
				poruka += 'Morate uneti adresu.' + '\n';
			}
			if(!data.koliko_potrazivati) {

				poruka += 'Morate uneti iznos potraživanja.' + '\n';
			}

			//AKO JE ZEMLJA SRBIJA ILI NEMA ZEMLJE U BAZI
			if(!data.zemlja_id || data.zemlja_id == 199 || data.zemlja_id == '-1') {

				//AKO U BAZI NE POSTOJI ID MESTA ILI ID OPSTINE
				if(!data.id_mesta || data.id_mesta == '-1') {

					poruka += 'Morate uneti mesto.' + '\n';
				}
				if(!data.id_opstine || data.id_opstine == '-1') {

					poruka += 'Morate uneti opštinu.' + '\n';
				}
			}
			
			//AKO JE REGRES OD OSIGURAVAJUCEG DRUSTVA
			if(data.regres_od && data.regres_od === "Osiguravajuće društvo") {

				//AKO NE POSTOJI NAZIV OSIGURANJA
				if(!data.osiguranje_reg || data.osiguranje_reg == '?') {

					poruka += 'Morate uneti naziv osiguravajućeg društva.' + '\n';
				}
			}

			//AKO REGRES NIJE OD OSIGURAVAJUCEG DRUSTVA
			if(data.regres_od && data.regres_od !== "Osiguravajuće društvo") {

				//AKO JE TIP LICA PRAVNO
				if(data.tip_lica && data.tip_lica == 'P') {

					//AKO NE POSTOJI NAZIV PRAVNOG LICA
					if(!data.ime_reg) {

						poruka += 'Morate uneti naziv pravnog lica.' + '\n';
					}
				}
				//AKO JE TIP LICA FIZICKO
				else {

					//AKO NE POSTOJI IME ILI PREZIME
					if(!data.ime_reg) {

						poruka += 'Morate uneti ime.' + '\n';
					}
					if(!data.prezime_reg) {

						poruka += 'Morate uneti prezime.' + '\n';
					}
				}
			}

			if(poruka != '') {
				
				naslov += poruka;

				alert(naslov);
				return false;
			}

			//KREIRANJE URL-A KA STRANICI ZA OTVARANJE REGRESA I REDIREKCIJA
			href="../pravna/regresna.php?idstete=" + idstete + "&status=izmeni";

			window.open(href, '_blank');
		}
	});
}

</script>