<?php
/*-----------------------------------------*/
session_start();
/*Uèitaj klasu za ¹ifarnike */
require "../common/sifarnici_class.php";
$sifarnici_class = new sifarnici_class();
/*Uèitaj klasu za funkcije */
require "../common/funkcije_class.php";
$funkcije_class = new funkcije_class();
/*Podesi konekciju */ 
$conn_stete = pg_connect('dbname=stete user=zoranp');
date_default_timezone_set ('CET');
//omogucuje se sa vreme trajanja upita bude neograniceno
set_time_limit(0);
// DATUM I VREME KREIRANJA PDF-a
//$today = date("Y-m-d");
$today = date("Y-m-d H:i:s");
//uneseni delovodni broj

$upit = "SET client_encoding TO 'UTF-8'";
$result = pg_query($conn_stete, $upit);

// Pokupi podatke iz GET-a
$id_stete = $_GET['id_stete'];
$dopunski = $_GET['dopunski'];

// podaci iz tabele knjigas  -------------------------------------------------------------------
$sqlPodaci = "SELECT brst,vrstast,brpolise,ucesce,datumproc,substr(extract(year from datumevid)::text,3,2) as godina_otvaranja_stete,imenazivost,prezimeost,posbrost,adresaost,datumnast,vremenast,mestost,
						  uzrokstete,vremenskeprilike,markaost,godost,brsasost,regoznakaost,regpodost,tipost,predjenokm_ost,opstina_stete_id,mesto_stete_id,zemlja_stete_id,steta_u_inostranstvu
				   FROM knjigas 
				   WHERE idstete=".$id_stete;

$rez = pg_query($conn_stete, $sqlPodaci);
$niz_podataka_knjigas = pg_fetch_assoc($rez);

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

$osiguranik_posbroj = $niz_podataka_knjigas['posbrost'];
$osiguranik_adresa = $niz_podataka_knjigas['adresaost'];
$datum_nastanka = $niz_podataka_knjigas['datumnast'];
// Ukoliko je steta_u_inostranstvu=1::bit onda se za prvi deo uzima naziv zemlje
// Ako je steta_u_inostranstvu=0::bit onda se za prvi deo uzima naziv op¹tine i naziv mesta
if($niz_podataka_knjigas['steta_u_inostranstvu']==0)
	$mesto_nastanka_1 = $sifarnici_class->vratiNazivIspis('opstina', $niz_podataka_knjigas['opstina_stete_id'], 'UTF-8').", ".$sifarnici_class->vratiNazivIspis('mesto', $niz_podataka_knjigas['mesto_stete_id'], 'UTF-8');
else
	$mesto_nastanka_1 = $sifarnici_class->vratiNazivIspis('zemlje_drzave', $niz_podataka_knjigas['zemlja_stete_id'], 'UTF-8');
$mesto_nastanka_2 = $niz_podataka_knjigas['mestost'];
$mesto_nastanka = $mesto_nastanka_1.", ".$mesto_nastanka_2;
// $mesto_nastanka = $niz_podataka_knjigas['mestost'];

$uzrok_stete = $niz_podataka_knjigas['uzrokstete'];
$vreme_stete = $niz_podataka_knjigas['vremenast'];
$marka_vozila = $niz_podataka_knjigas['markaost'];
$god_proizvodnje = $niz_podataka_knjigas['godost'];
$br_sasije = $niz_podataka_knjigas['brsasost'];
$reg_oznaka = $niz_podataka_knjigas['regoznakaost'];
$reg_podrucje = $niz_podataka_knjigas['regpodost'];
$tip_vozila = $niz_podataka_knjigas['tipost'];
$predjeno_km = $niz_podataka_knjigas['predjenokm_ost'];
$ucesce_knjigas = $niz_podataka_knjigas['ucesce']; 

//echo "<br>broj odstetnog zahteva=".
// //$br_odstetnog_zahteva = $vrsta_stete."-".$broj_stete."/".$godina_otv_stete;
$br_odstetnog_zahteva = $funkcije_class->vrati_broj_predmeta_za_dokumente($id_stete);

//$naziv_dokumenta = $vrsta_stete."-".$broj_stete."-".$godina_otv_stete;
$naziv_dokumenta = $br_odstetnog_zahteva;
$naziv_dokumenta = str_replace("/", "-", $naziv_dokumenta);

$sqlMestoOst = "SELECT * FROM mesta WHERE sifmesta =".$osiguranik_posbroj;
$rezMesto = pg_query($conn_stete,$sqlMestoOst);
$niz_mesto = pg_fetch_assoc($rezMesto);
$osiguranik_mesto = $niz_mesto['mesto'];
// datum nastanka prikaz
$datum_nastanka_niz = explode('-',$datum_nastanka);
$datum_nastanka_prikaz = $datum_nastanka_niz[2].".".$datum_nastanka_niz[1].".".$datum_nastanka_niz[0].".";
// godina proizvodnje prikaz
$god_proizvodnje_niz = explode('-',$god_proizvodnje);
// $god_proizvodnje_prikaz = $god_proizvodnje_niz[2].".".$god_proizvodnje_niz[1].".".$god_proizvodnje_niz[0].".";
$god_proizvodnje_prikaz = $god_proizvodnje_niz[0].".";
// uzrok stete iz tabele


$uzrok_stete = $niz_podataka_knjigas['uzrokstete'];  

// Marko Markovic pocetak - VLADA ZAKOMENTARISAO ZBOG NOVOG UPITA
// $conn_stete = pg_connect("host=localhost  dbname=stete user=zoranp");
/*
//VLADA PROSIRIO UPIT - ZBOG DOBIJANJA ID-JA RIZIKA
$upit_uzrok_poz = "SELECT uzrok_id, rizik_id FROM predmet_odstetnog_zahteva where id =  $id_stete";
$rezUzrok_poz = pg_query($conn_stete, $upit_uzrok_poz);
$rezultat_uzrok_poz = pg_fetch_assoc($rezUzrok_poz);
$uzrok_stete_poz = $rezultat_uzrok_poz['uzrok_id'];

//DODAO VLADA
$rizik_id = $rezultat_uzrok_poz['rizik_id'];
// $uzrok_stete_poz = strtoupper($rezultat_uzrok_poz['uzrok_id']);	

$conn_amso = pg_connect("host=localhost  dbname=amso user=zoranp");
$upit_uzrok = "SELECT opis FROM sifarnici.aktuari_uzroci_i_uos 
				where id = $uzrok_stete_poz ";     
$rezUzrok = pg_query($conn_amso, $upit_uzrok);
$rezultat_uzrok = pg_fetch_assoc($rezUzrok);
// $opis_uzroka_stete = $rezultat_uzrok['opis'];	
$opis_uzroka_stete = mb_convert_encoding(strtoupper($rezultat_uzrok['opis']), "UTF-8", "ISO-8859-2");		
// Marko Marokovic kraj

//UPIT ZA DOBIJANJE OPISA RIZIKA - DODAO VLADA
$upit_rizik = "SELECT opis FROM sifarnici.aktuari_rizici WHERE id = $rizik_id";     
$rezultat_rizik = pg_query($conn_amso, $upit_rizik);
$niz_rizik = pg_fetch_assoc($rezultat_rizik);
$opis_rizika = mb_convert_encoding(strtoupper($niz_rizik['opis']), "UTF-8", "ISO-8859-2");
*/

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
WHERE po.id = $id_stete";

$rezultat_rizik_uzrok = pg_query($conn_stete, $upit_rizik_uzrok);
$podaci_rizik_uzrok = pg_fetch_array($rezultat_rizik_uzrok);

//UPIS OPISA RIZIKA I UZROKA U PROMENJIVE
$opis_rizika = mb_strtoupper($podaci_rizik_uzrok['opis_rizika'], 'UTF-8');
$opis_uzroka_stete = mb_strtoupper($podaci_rizik_uzrok['opis_uzroka'], 'UTF-8');	


/* Marko Markovic --- zakomentarisao
$sqlUzrok = "SELECT upper(opis) as opis FROM uzrok_stete WHERE id=".$uzrok_stete;
$rezUzrok = pg_query($conn_stete,$sqlUzrok);
$rezult = pg_fetch_assoc($rezUzrok);
$opis_uzroka_stete = strtoupper($rezult['opis']);
*/

// registracija
$registracija = $reg_podrucje."-".$reg_oznaka;

// AKO JE AO POLISA   // Marko Markovic 2019-07-04 umesto AO AK  na zahtev Sase Mandica
if($vrsta_stete=='AK')
{

	//NEVENA PERIC 2019-08-14 umesto tabele polise u upitu izmenjeno na kasko
	$sqlAK = "SELECT snagakw, ccm, nosiv, vrsta FROM kasko WHERE brpolise=".$broj_polise;
	$rezAK = pg_query($conn_amso,$sqlAK);

	$niz_AK = pg_fetch_assoc($rezAK);

	$snaga_vozila = $niz_AK['snagakw'];
	$zapremina_vozila = $niz_AK['ccm'];
	$nosivost_vozila = $niz_AK['nosiv'];
	 
	$snaga_vozila_niz = explode(".",$snaga_vozila);
	$snaga = $snaga_vozila_niz[0];
	$zapremina_vozila_niz = explode(".",$zapremina_vozila);
	$zapremina = $zapremina_vozila_niz[0];
	$nosivost_vozila_niz = explode(".",$snaga_vozila);
	$nosivost = $nosivost_vozila[0];
	 
	$vrsta_voz_sifra = $niz_AK['vrsta'];

	$sqlPremijskaGrupa = "SELECT naziv FROM prem_grupa WHERE grupa like '".$vrsta_voz_sifra."'";
	$rezPremGrupa = pg_query($conn_amso,$sqlPremijskaGrupa);
	$niz_prem = pg_fetch_assoc($rezPremGrupa);
	//$vrsta_vozila = $niz_prem['naziv'];
    //NEVENA P. 
	$vrsta_vozila=mb_convert_encoding($niz_prem['naziv'], "UTF-8", "ISO-8859-2");
	/* $sqlAO = "SELECT snagakw, ccm, nosiv, vrsta FROM polise WHERE brpolise=".$broj_polise;
	$rezAO = pg_query($conn_amso,$sqlAO);
	 
	$niz_AO = pg_fetch_assoc($rezAO);

	$snaga_vozila = $niz_AO['snagakw'];
	$zapremina_vozila = $niz_AO['ccm'];
	$nosivost_vozila = $niz_AO['nosiv'];
	 
	$snaga_vozila_niz = explode(".",$snaga_vozila);
	$snaga = $snaga_vozila_niz[0];
	$zapremina_vozila_niz = explode(".",$zapremina_vozila);
	$zapremina = $zapremina_vozila_niz[0];
	$nosivost_vozila_niz = explode(".",$snaga_vozila);
	$nosivost = $nosivost_vozila[0];
	 
	$vrsta_voz_sifra = $niz_AO['vrsta'];

	$sqlPremijskaGrupa = "SELECT naziv FROM prem_grupa WHERE grupa like '".$vrsta_voz_sifra."'";
	$rezPremGrupa = pg_query($conn_amso,$sqlPremijskaGrupa);
	$niz_prem = pg_fetch_assoc($rezPremGrupa);
	$vrsta_vozila = $niz_prem['naziv']; */
	 
}
// AKO JE POLISA AK
$ugov_ucesce_niz = explode(".",$ucesce_knjigas);
$ugov_ucesce_procenat = $ugov_ucesce_niz[0];


// podaci iz tabele zapisnik  -------------------------------------------------------------------
$sqlZapisnik = "SELECT *, (rad+farbanje)as ukupni_rad FROM zapisnik_o_ostecenju_vozila WHERE id_stete=".$id_stete." AND dopunski=".$dopunski;
$rezultZapisnik = pg_query($conn_stete, $sqlZapisnik);
$nizZapisnik = pg_fetch_assoc($rezultZapisnik);
// var_dump($nizZapisnik);
$id_zapisnik = $nizZapisnik['id'];
$mesto_pregleda_voz_prikaz = $nizZapisnik['mesto_pregleda'];
$vrsta_vozila = $vrsta_vozila ? $vrsta_vozila : $nizZapisnik['vrsta_vozila'];
$snaga_vozila = $snaga_vozila ? $snaga_vozila : $nizZapisnik['snaga_vozila'];
$zapremina_vozila = $zapremina_vozila ? $zapremina_vozila : $nizZapisnik['zapremina_vozila'];
$nosivost_vozila = $nosivost_vozila ? $nosivost_vozila : $nizZapisnik['nosivost_vozila'];
$boja_vozila = $nizZapisnik['boja_vozila'];
$broj_vrata_vozila = $nizZapisnik['broj_vrata'];
$tezina_vozila = $nizZapisnik['tezina_vozila'];
$nosivost_vozila = $nizZapisnik['nosivost_vozila'];
$boja_vozila = $nizZapisnik['boja_vozila'];
$predjeno_km = $predjeno_km ? $predjeno_km : $nizZapisnik['predjeno_km'];
$stanje_vozila_id = $nizZapisnik['stanje_vozila'];
//dodato 31.03.2017.
$datum_snimanja = $nizZapisnik['datum_snimanja'];
$datum_pregleda = $nizZapisnik['datum_pregleda'];
$datum_pregleda = date("d.m.Y", strtotime($datum_pregleda));



	$sql_stanje_vozila = "SELECT * FROM sifarnici.zapisnik_stanja_vozila WHERE id=".$stanje_vozila_id;
	$rezultat_stanje_vozila = pg_query($conn_stete,$sql_stanje_vozila);
	$niz_stanje_vozila = pg_fetch_array($rezultat_stanje_vozila);
$stanje_vozila = $niz_stanje_vozila['opis'];
$pokretnost_vozila = $nizZapisnik['vozilo_pokretno'];
$fotografisanost_vozila = $nizZapisnik['vozilo_fotografisano'];
$uvid_u_zapisnik_mupa_vozila = $nizZapisnik['uvid_u_zapisnik_mupa'];
$datum_zapisnika = $nizZapisnik['datum_vreme'];
$datum_zapisnika = substr($datum_zapisnika,0,10);
$datum_zapisnika_niz = explode("-",$datum_zapisnika);
$datum_zapisnika = $datum_zapisnika_niz[2].".".$datum_zapisnika_niz[1].".".$datum_zapisnika_niz[0].".godine"; 
if($datum_snimanja){
	$datum_snimanja = date("d.m.Y", strtotime($datum_snimanja));
}
else {
	$datum_snimanja = $datum_zapisnika;
}
// ucesce minimum
$ucesce_minimum = $nizZapisnik['ucesce_minimum'];
// rad
$rad = $nizZapisnik['rad'];
// farbanje
$farbanje = $nizZapisnik['farbanje'];
// ukupno
$ukupni_rad = $nizZapisnik['ukupni_rad']; 
// zapisnik uradio korisnik (Izvuci ga iz baze korisnika)
$radnik_id = $nizZapisnik['procenitelj_uradio'];
$conn_zabrane = pg_connect('dbname=zabrane user=zoranp');
$sqlRadnik = "SELECT * FROM unosivaci WHERE sifra=".$radnik_id;
$rezultRadnik = pg_query($conn_zabrane, $sqlRadnik);
$nizRadnik = pg_fetch_assoc($rezultRadnik);
$procenitelj_uradio = $nizRadnik['ime'];
// broj motora
$broj_motora = $nizZapisnik['broj_motora'];
// napomena
$napomena_rucno_uneta = $nizZapisnik['napomena'];

// Pokupi sve delove za zapisnik
// Delovi za zamenu
$sql_zamena = "SELECT 
									CASE 	WHEN (s.procenat_amortizacije <> 0) 
     										THEN d.naziv_auto_dela||' (Amortizacija '||s.procenat_amortizacije||'%)'
     										ELSE d.naziv_auto_dela
    							END 
										AS naziv_auto_dela 
								FROM 
										zapisnik_o_ostecenju_stavke s 
									INNER JOIN 
										sifarnici.zapisnik_auto_delova d 
									ON (s.id_zapisnik_auto_delova = d.id) 
								WHERE 
										id_zapisnik=$id_zapisnik 
									AND 
										deo_zamena=1::bit";
$rezultat_zamena = pg_query($conn_stete, $sql_zamena);
$niz_zamena = pg_fetch_all($rezultat_zamena);
// Delovi za popravku
$sql_popravka = "SELECT 
								CASE 	WHEN (s.procenat_amortizacije <> 0) 
     										THEN d.naziv_auto_dela || ' (Amortizacija '||s.procenat_amortizacije||'%)' || ' (' || szso.stepen_ostecenja_naziv || ')'
     										ELSE d.naziv_auto_dela || ' (' || szso.stepen_ostecenja_naziv || ')'
    							END 
										AS naziv_auto_dela  
								FROM 
										zapisnik_o_ostecenju_stavke s 
									INNER JOIN 
										sifarnici.zapisnik_auto_delova d 
									ON (s.id_zapisnik_auto_delova = d.id)
									INNER JOIN 
										sifarnici.zapisnik_stepen_ostecenja_vozila szso 
									ON (s.id_stepen_ostecenja = szso.id)  
								WHERE 
										id_zapisnik=$id_zapisnik 
									AND 
										deo_popravka=1::bit";
$rezultat_popravka = pg_query($conn_stete, $sql_popravka);
$niz_popravka = pg_fetch_all($rezultat_popravka);
// Delovi za kontrolu
$sql_kontrola = "SELECT 
								CASE 	WHEN (s.procenat_amortizacije <> 0) 
     										THEN d.naziv_auto_dela||' (Amortizacija '||s.procenat_amortizacije||'%)'
     										ELSE d.naziv_auto_dela
    							END 
										AS naziv_auto_dela 
								FROM 
										zapisnik_o_ostecenju_stavke s 
									INNER JOIN 
										sifarnici.zapisnik_auto_delova d 
									ON (s.id_zapisnik_auto_delova = d.id) 
								WHERE 
										id_zapisnik=$id_zapisnik 
									AND 
										deo_kontrola=1::bit";
$rezultat_kontrola = pg_query($conn_stete, $sql_kontrola);
$niz_kontrola = pg_fetch_all($rezultat_kontrola);
// Delovi za ostalo
$sql_ostalo = "SELECT 
							CASE 	WHEN (s.procenat_amortizacije <> 0) 
     										THEN d.naziv_auto_dela||' (Amortizacija '||s.procenat_amortizacije||'%)'
     										ELSE d.naziv_auto_dela
    							END 
										AS naziv_auto_dela 
							FROM 
									zapisnik_o_ostecenju_stavke s 
								INNER JOIN 
									sifarnici.zapisnik_auto_delova d 
								ON (s.id_zapisnik_auto_delova = d.id) 
							WHERE 
									id_zapisnik=$id_zapisnik 
								AND 
									deo_o_radovi=1::bit";
$rezultat_ostalo = pg_query($conn_stete, $sql_ostalo);
$niz_ostalo = pg_fetch_all($rezultat_ostalo);
// Nizovi svih delova
$delovi_za_zamenu = $niz_zamena;
$delovi_za_popravku = $niz_popravka;
$delovi_za_kontrolu = $niz_kontrola;
$delovi_za_ostalo = $niz_ostalo;
// Sreðivanje spiskova delova
$delovi_desno = array();
if ($delovi_za_popravku) 
{
	for ($i = 0; $i < count($delovi_za_popravku); $i++) 
	{
		$niz_za_dodati = array( 'deo' => $delovi_za_popravku[$i]['naziv_auto_dela'], 'nacin' => 'p');
		array_push($delovi_desno, $niz_za_dodati);
	}
}
if ($delovi_za_kontrolu)
{
	for ($i = 0; $i < count($delovi_za_kontrolu); $i++) 
	{
		$niz_za_dodati = array( 'deo' => $delovi_za_kontrolu[$i]['naziv_auto_dela'], 'nacin' => 'k');
		array_push($delovi_desno, $niz_za_dodati);
	}
}
if ($delovi_za_ostalo)
{
	for ($i = 0; $i < count($delovi_za_ostalo); $i++) 
	{
		$niz_za_dodati = array( 'deo' => $delovi_za_ostalo[$i]['naziv_auto_dela'], 'nacin' => 'o');
		array_push($delovi_desno, $niz_za_dodati);
	}
}

/*
 * Ukoliko je pozvan probni zapisnik
 * onda se popunjavaju promenljive na sledeæi naèin:
 * */
if ($_GET['probni'] == 'DA') 
{
	$id_stete = $_GET['idstete'];
	// Broj dopunskog
	$sql_broj_dopunskog = "SELECT MAX(dopunski)+1 AS dopunski FROM zapisnik_o_ostecenju_vozila WHERE id_stete=".$id_stete;
	$rezultat_broj_dopunskog = pg_query($conn_stete, $sql_broj_dopunskog);
	$niz_broj_dopunskog = pg_fetch_array($rezultat_broj_dopunskog);
	$dopunski = $niz_broj_dopunskog['dopunski'];
	$br_odstetnog_zahteva = $_GET['broj_stete'];
	$naziv_dokumenta = ($dopunski == 0 || $dopunski == "") ? $br_odstetnog_zahteva : $br_odstetnog_zahteva."_dopunski_".$dopunski;
	$naziv_dokumenta = str_replace("/", "-", $naziv_dokumenta);
	$broj_polise = $_GET['broj_polise'];
	$ugov_ucesce_procenat = $_GET['ucesce_procenat']!='NULL' ? $_GET['ucesce_procenat'] : "";
	$ucesce_minimum = $_GET['ucesce_minimum']!='NULL' ? $_GET['ucesce_minimum'] : "";
	$datum_pregleda = $_GET['datum_pregleda'];
	$datum_snimanja  = $_GET['datum_snimanja'];
	$mesto_pregleda_voz_prikaz = $_GET['mesto_pregleda'];
	$osiguranik_naziv = strtoupper($_GET['osiguranik']);
	$osiguranik_posbroj = "";
	$osiguranik_mesto = strtoupper($_GET['mesto']);
	$osiguranik_adresa = strtoupper($_GET['adresa']);
	$datum_nastanka_prikaz = $_GET['datum_nastanka'];
	$mesto_nastanka = strtoupper($_GET['mesto_nezgode']);
	$opis_uzroka_stete = mb_strtoupper($_GET['uzrok_nezgode'], 'UTF-8');

	//DODAO VLADA - ZA DOBIJANJE OPISA RIZIKA
	$opis_rizika = mb_strtoupper($_GET['prijavljeni_rizik'], 'UTF-8');

	$vrsta_vozila = strtoupper($_GET['vrsta_voz']);
	$marka_vozila = strtoupper($_GET['marka_voz']);
	$god_proizvodnje_prikaz = $_GET['god_proiz'];
	$br_sasije = strtoupper($_GET['br_sasije']);
	$registracija = strtoupper($_GET['reg_oznaka']);
	$tip_vozila  = strtoupper($_GET['tip_voz']);
	// Mesto pregleda !!! ???
	$god_proizvodnje = $_GET['god_proizv'];
	$broj_motora = strtoupper($_GET['broj_motora']);
	$tezina_vozila = $_GET['tezina_voz'];
	$snaga_vozila = $_GET['snaga_voz'];
	$boja_vozila = strtoupper($_GET['boja_voz']);
	$nosivost_vozila = $_GET['nosivost_voz'];
	$zapremina_vozila = $_GET['zapremina_voz'];
	$broj_vrata_vozila = $_GET['broj_vrata'];
	$predjeno_km = $_GET['predjeno_km'];
	$pokretnost_vozila = strtoupper($_GET['vozilo_pokretno']);
		$stanje_vozila_id = $_GET['stanje_vozila'];
		$sql_stanje_vozila = "SELECT * FROM sifarnici.zapisnik_stanja_vozila WHERE id=".$stanje_vozila_id;
		$rezultat_stanje_vozila = pg_query($conn_stete,$sql_stanje_vozila);
		$niz_stanje_vozila = pg_fetch_array($rezultat_stanje_vozila);
	$stanje_vozila = $niz_stanje_vozila['opis'];
	$fotografisanost_vozila = strtoupper($_GET['vozilo_foto']);
	$uvid_u_zapisnik_mupa_vozila = strtoupper($_GET['zapisnik_mup']);
	$rad = $_GET['rad'];
	$farbanje = $_GET['farbanje'];
	$ukupni_rad = $rad + $farbanje;
	$datum_zapisnika = date("d.m.Y.");
	$radnik_id = $_GET['procenitelj_uradio'];
	$conn_zabrane = pg_connect('dbname=zabrane user=zoranp');
	$sqlRadnik = "SELECT * FROM unosivaci WHERE sifra=".$radnik_id;
	$rezultRadnik = pg_query($conn_zabrane, $sqlRadnik);
	$nizRadnik = pg_fetch_assoc($rezultRadnik);
	$procenitelj_uradio = $nizRadnik['ime'];
	if($_GET['napomena1'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena1']."\n";
	if($_GET['napomena2'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena2']."\n";
	if($_GET['napomena3'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena3']."\n";
	if($_GET['napomena4'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena4']."\n";
	if($_GET['napomena5'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena5']."\n";
	if($_GET['napomena6'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena6']."\n";
	if($_GET['napomena7'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena7']."\n";
	if($_GET['napomena8'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena8']."\n";
	if($_GET['napomena9'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena9']."\n";
	if($_GET['napomena10'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena10']."\n";
	if($_GET['napomena11'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena11']."\n";
	if($_GET['napomena12'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena12']."\n";
	if($_GET['napomena13'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena13']."\n";
	if($_GET['napomena14'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena14']."\n";
	if($_GET['napomena15'] != "undefined")
		$napomena_rucno_uneta .= $_GET['napomena15']."\n";
	$napomena_rucno_uneta =mb_convert_encoding($napomena_rucno_uneta, 'UTF-8', 'ISO-8859-2');
	// Pokupi sve delove za zapisnik (stringovi sa ID-ovima delova)
	$delovi_za_zamenu_probni = str_replace('"', "", $_GET['zamena']);
	$delovi_za_popravku_probni = str_replace('"', "", $_GET['popravka']);
	$delovi_za_kontrolu_probni = str_replace('"', "", $_GET['kontrola']);
	$delovi_za_ostalo_probni = str_replace('"', "", $_GET['ostalo']);
	$delovi_za_zamenu_probni = stripslashes($delovi_za_zamenu_probni);
	$delovi_za_popravku_probni = stripslashes($delovi_za_popravku_probni); 
	$delovi_za_kontrolu_probni = stripslashes($delovi_za_kontrolu_probni);
	$delovi_za_ostalo_probni = stripslashes($delovi_za_ostalo_probni);
	$delovi_za_zamenu_probni = str_replace("\\","", $delovi_za_zamenu_probni);
	$delovi_za_popravku_probni = str_replace("\\","", $delovi_za_popravku_probni);
	$delovi_za_kontrolu_probni = str_replace("\\","", $delovi_za_kontrolu_probni);
	$delovi_za_ostalo_probni = str_replace("\\","", $delovi_za_ostalo_probni);
	$delovi_za_zamenu_amortizacija_probni = str_replace('"', "", $_GET['zamena_amortizacija']);
	$delovi_za_popravku_amortizacija_probni = str_replace('"', "", $_GET['popravka_amortizacija']);
	$delovi_za_kontrolu_amortizacija_probni = str_replace('"', "", $_GET['kontrola_amortizacija']);
	$delovi_za_ostalo_amortizacija_probni = str_replace('"', "", $_GET['ostalo_amortizacija']);
	$delovi_za_zamenu_amortizacija_probni = stripslashes($delovi_za_zamenu_amortizacija_probni);
	$delovi_za_popravku_amortizacija_probni = stripslashes($delovi_za_popravku_amortizacija_probni);
	$delovi_za_kontrolu_amortizacija_probni = stripslashes($delovi_za_kontrolu_amortizacija_probni);
	$delovi_za_ostalo_amortizacija_probni = stripslashes($delovi_za_ostalo_amortizacija_probni);
	$delovi_za_zamenu_amortizacija_probni = str_replace("\\","", $delovi_za_zamenu_amortizacija_probni);
	$delovi_za_popravku_amortizacija_probni = str_replace("\\","", $delovi_za_popravku_amortizacija_probni);
	$delovi_za_kontrolu_amortizacija_probni = str_replace("\\","", $delovi_za_kontrolu_amortizacija_probni);
	$delovi_za_ostalo_amortizacija_probni = str_replace("\\","", $delovi_za_ostalo_amortizacija_probni);
	// Stepen o¹teæenja u sluèaju da su delovi za popravku
	$delovi_za_popravku_stepen_ostecenja_probni = str_replace('"', "", $_GET['popravka_stepen_ostecenja']);
	$delovi_za_popravku_stepen_ostecenja_probni = stripslashes($delovi_za_popravku_stepen_ostecenja_probni);
	$delovi_za_popravku_stepen_ostecenja_probni = str_replace("\\","", $delovi_za_popravku_stepen_ostecenja_probni);
	// Prebaci ih u nizove
	if ($delovi_za_zamenu_probni!="") 
	{
		$delovi_za_zamenu_probni_niz = explode(",", $delovi_za_zamenu_probni);
	}
	if ($delovi_za_popravku_probni!="") 
	{
		$delovi_za_popravku_probni_niz = explode(",", $delovi_za_popravku_probni);
	}
	if ($delovi_za_kontrolu_probni!="") 
	{
		$delovi_za_kontrolu_probni_niz = explode(",", $delovi_za_kontrolu_probni);
	}
	if ($delovi_za_ostalo_probni!="") 
	{
		$delovi_za_ostalo_probni_niz = explode(",", $delovi_za_ostalo_probni);
	}
	if ($delovi_za_zamenu_amortizacija_probni!="")
	{
		$delovi_za_zamenu_amortizacija_probni_niz = explode(",", $delovi_za_zamenu_amortizacija_probni);
	}
	if ($delovi_za_popravku_amortizacija_probni!="")
	{
		$delovi_za_popravku_amortizacija_probni_niz = explode(",", $delovi_za_popravku_amortizacija_probni);
	}
	if ($delovi_za_kontrolu_amortizacija_probni!="")
	{
		$delovi_za_kontrolu_amortizacija_probni_niz = explode(",", $delovi_za_kontrolu_amortizacija_probni);
	}
	if ($delovi_za_ostalo_amortizacija_probni!="")
	{
		$delovi_za_ostalo_amortizacija_probni_niz = explode(",", $delovi_za_ostalo_amortizacija_probni);
	}
	if ($delovi_za_popravku_stepen_ostecenja_probni!="")
	{
		$delovi_za_popravku_stepen_ostecenja_probni = explode(",", $delovi_za_popravku_stepen_ostecenja_probni);
	}
	// Za svaki deo u nizu izvuæi naziv dela i dodati ga u niz
	// Za levu stranu
	$delovi_za_zamenu = array();
	if ($delovi_za_zamenu_probni_niz) 
	{
		for ($i = 0; $i < count($delovi_za_zamenu_probni_niz); $i++)
		{
			$sql_deo_zamena = "SELECT d.naziv_auto_dela FROM sifarnici.zapisnik_auto_delova d WHERE id=".$delovi_za_zamenu_probni_niz[$i];
			$rezultat_deo_zamena = pg_query($conn_stete, $sql_deo_zamena);
			$niz_deo_zamena = pg_fetch_array($rezultat_deo_zamena);
			$deo_prikaz_pdf = $niz_deo_zamena['naziv_auto_dela'];
			if ($delovi_za_zamenu_amortizacija_probni_niz[$i]!=0) {
				$deo_prikaz_pdf .= " (Amortizacija ".$delovi_za_zamenu_amortizacija_probni_niz[$i]."%)";
			}
			$niz_za_dodati = array( 'naziv_auto_dela' => $deo_prikaz_pdf, 'nacin' => 'z');
			array_push($delovi_za_zamenu, $niz_za_dodati);
		}
	}
	
	// Za desnu stranu
	$delovi_desno = array();
	if ($delovi_za_popravku_probni_niz)
	{
		for ($i = 0; $i < count($delovi_za_popravku_probni_niz); $i++)
		{
			$sql_deo_popravka = "SELECT d.naziv_auto_dela FROM sifarnici.zapisnik_auto_delova d WHERE id=".$delovi_za_popravku_probni_niz[$i];
			$rezultat_deo_popravka = pg_query($conn_stete, $sql_deo_popravka);
			$niz_deo_popravka = pg_fetch_array($rezultat_deo_popravka);
			$deo_prikaz_pdf = $niz_deo_popravka['naziv_auto_dela'];
			if ($delovi_za_popravku_amortizacija_probni_niz[$i]!=0) {
				$deo_prikaz_pdf .= " (Amortizacija ".$delovi_za_popravku_amortizacija_probni_niz[$i]."%)";
			}
			// Prikaz stepena o¹teæenja
			$sql_deo_popravka_stepen_ostecenja = "SELECT szso.stepen_ostecenja_naziv FROM sifarnici.zapisnik_stepen_ostecenja_vozila szso WHERE id=".$delovi_za_popravku_stepen_ostecenja_probni[$i];
			$rezultat_deo_popravka_stepen_ostecenja = pg_query($conn_stete, $sql_deo_popravka_stepen_ostecenja);
			$niz_deo_popravka_stepen_ostecenja = pg_fetch_array($rezultat_deo_popravka_stepen_ostecenja);
			$deo_popravka_stepen_ostecenja = $niz_deo_popravka_stepen_ostecenja['stepen_ostecenja_naziv'];
			$stepen_ostecenja_pdf = mb_convert_encoding("Stepen o¹teæenja - ", 'UTF-8', 'ISO-8859-2').$deo_popravka_stepen_ostecenja;
			$stepen_ostecenja_pdf = $deo_popravka_stepen_ostecenja;
			$deo_prikaz_pdf .= " ($stepen_ostecenja_pdf)";
			$niz_za_dodati = array( 'deo' => $deo_prikaz_pdf, 'nacin' => 'p');
			array_push($delovi_desno, $niz_za_dodati);
		}
	}
	if ($delovi_za_kontrolu_probni_niz)
	{
		for ($i = 0; $i < count($delovi_za_kontrolu_probni_niz); $i++)
		{
			$sql_deo_kontrola = "SELECT d.naziv_auto_dela FROM sifarnici.zapisnik_auto_delova d WHERE id=".$delovi_za_kontrolu_probni_niz[$i];
			$rezultat_deo_kontrola = pg_query($conn_stete, $sql_deo_kontrola);
			$niz_deo_kontrola = pg_fetch_array($rezultat_deo_kontrola);
			$deo_prikaz_pdf = $niz_deo_kontrola['naziv_auto_dela'];
			if ($delovi_za_kontrolu_amortizacija_probni_niz[$i]!=0) {
				$deo_prikaz_pdf .= " (Amortizacija ".$delovi_za_kontrolu_amortizacija_probni_niz[$i]."%)";
			}
			$niz_za_dodati = array( 'deo' => $deo_prikaz_pdf, 'nacin' => 'k');
			array_push($delovi_desno, $niz_za_dodati);
		}
	}
	if ($delovi_za_ostalo_probni_niz)
	{
		for ($i = 0; $i < count($delovi_za_ostalo_probni_niz); $i++)
		{
			$sql_deo_ostalo = "SELECT d.naziv_auto_dela FROM sifarnici.zapisnik_auto_delova d WHERE id=".$delovi_za_ostalo_probni_niz[$i];
			$rezultat_deo_ostalo = pg_query($conn_stete, $sql_deo_ostalo);
			$niz_deo_ostalo = pg_fetch_array($rezultat_deo_ostalo);
			$deo_prikaz_pdf = $niz_deo_ostalo['naziv_auto_dela'];
			if ($delovi_za_ostalo_amortizacija_probni[$i]!=0) {
				$deo_prikaz_pdf .= " (Amortizacija ".$delovi_za_ostalo_amortizacija_probni[$i]."%)";
			}
			$niz_za_dodati = array( 'deo' => $deo_prikaz_pdf, 'nacin' => 'o');
			array_push($delovi_desno, $niz_za_dodati);
		}
	}
}




/*  POCETAK PDF-A za kreiranje zapisnika */
require_once('../tcpdf/config/lang/srp.php');
require_once('../tcpdf/tcpdf.php');

$font_name = 'dejavusans';

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

// 	public  $dodatno_footer = "";
	// Page footer
	public function Footer() {
		// Pozicija na 10 mm od dna strane
		$this->SetY(-13);
		$this->SetFont($font_name, 'I', 8, '', true);
		$this->Cell(0, 10, 'strana '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		if ($_GET['probni'] == 'DA') 
		{
			$this->SetAlpha(0.5);
 			$this->Image(K_PATH_IMAGES.'probni_zapisnik.png', 10, 10, 200, 280);
// 			$this->Image(K_PATH_IMAGES.'amsologo.jpg', 10, 10, 32, 18, '', '', '', true, 200);
		}
	}
}

/*   POCETAK PDF-A opomene pdf za prikaz */
$pdf_zapisnik = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf_zapisnik->setFooterFont($font_name);
$pdf_zapisnik->SetCreator(PDF_CREATOR);
$pdf_zapisnik->SetAuthor('AMS Osiguranje');
$pdf_zapisnik->SetTitle('Zapisnik o utrvdjivanju ostecenja na vozilu');
$pdf_zapisnik->setPrintHeader(false);
$pdf_zapisnik->setPrintFooter(true);
$pdf_zapisnik->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf_zapisnik->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
$pdf_zapisnik->SetFooterMargin(5);
$pdf_zapisnik->SetAutoPageBreak(TRUE, 10); //PDF_MARGIN_BOTTOM
$pdf_zapisnik->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf_zapisnik->setLanguageArray($l);
// ********************************
// $pdf_zapisnik->dodatno_footer = "Zapisnik zahteva - ".$br_odstetnog_zahteva;
$pdf_zapisnik->AddPage();
// Datum pregleda vozila
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(10, 35);
$pdf_zapisnik->Cell(20, 5, mb_convert_encoding("Datum pregleda vozila: ", 'UTF-8', 'ISO-8859-2'),0);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(40, 35);
$pdf_zapisnik->Cell(20, 5, $datum_pregleda,0);
// Mesto pregleda vozila
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(70, 35);
$pdf_zapisnik->Cell(20, 5, mb_convert_encoding("Mesto pregleda vozila: ", 'UTF-8', 'ISO-8859-2'),0);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(100, 35);
$pdf_zapisnik->Cell(20, 5, $mesto_pregleda_voz_prikaz,0);
// Tabela sa podacima iz knjige
// 1.red - podaci o osiguraniku 
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(10, 40);
$pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Osiguranik: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(27, 40);
$osiguranik_podaci_ispis = $osiguranik_naziv.", ".$osiguranik_adresa.", ".$osiguranik_posbroj." ".$osiguranik_mesto;
$pdf_zapisnik->Cell(170, 4, $osiguranik_podaci_ispis ,0, 0, 'L', false, '', 1);
// $pdf_zapisnik->SetXY(27, 40); 
// $pdf_zapisnik->Cell(72, 4, $osiguranik_naziv,0, 0, $align='L', false, '', 1);
// $pdf_zapisnik->SetXY(100, 40);
// $pdf_zapisnik->Cell(10, 4, $osiguranik_posbroj,0, 0, $align='L', false, '', 1);
// $pdf_zapisnik->SetXY(111, 40);
// $pdf_zapisnik->Cell(39, 4, $osiguranik_mesto,0, 0, $align='L', false, '', 1);
// $pdf_zapisnik->SetXY(150, 40);
// $pdf_zapisnik->Cell(49, 4, $osiguranik_adresa,0, 0, $align='L', false, '', 1);

// 2.red - podaci o korisniku
// $pdf_zapisnik->SetFont($font_name, '', 7);
// $pdf_zapisnik->SetXY(10, 44);
// $pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Korisnik: ", 'UTF-8', 'ISO-8859-2'),1);
// $pdf_zapisnik->SetFont($font_name, 'B', 7);
// $pdf_zapisnik->SetXY(50, 44);
// $pdf_zapisnik->Cell(190, 4, "",0);
// $pdf_zapisnik->SetXY(100, 44);
// $pdf_zapisnik->Cell(190, 4, "",0);
// $pdf_zapisnik->SetXY(110, 44);
// $pdf_zapisnik->Cell(190, 4, "",0);
// $pdf_zapisnik->SetXY(140, 44);
// $pdf_zapisnik->Cell(190, 4, "",0);
//$opis_uzroka_stete
// 3.red - podaci o nezgodi
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(10, 44);
$pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Podaci o nezgodi: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(40, 44);

$nezgoda_podaci_ispis = $datum_nastanka_prikaz."godine,  ".$mesto_nastanka;    // Marko Markovic
$nezgoda_podaci_ispis = rtrim($nezgoda_podaci_ispis, ",");
$pdf_zapisnik->Cell(160, 4, $nezgoda_podaci_ispis ,0, 0, 'L', false, '', 1);
// $pdf_zapisnik->SetXY(50, 44);
// $pdf_zapisnik->Cell(190, 4, $datum_nastanka_prikaz,0);
// $pdf_zapisnik->SetXY(100, 44);
// $pdf_zapisnik->Cell(190, 4, $mesto_nastanka,0);
// $pdf_zapisnik->SetXY(140, 44);
// $pdf_zapisnik->Cell(190, 4, $opis_uzroka_stete,0);

//DOBIJANJE KOORDINATA Y OSE - DODAO VLADA
$yOsa = $pdf_zapisnik->GetY();


//NOVI RED SA PRIJAVLJENIM RIZIKOM I UZROKOM STETE - DODAO VLADA
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(10, $yOsa+4);
$pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Prijavljeni rizik: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(30, $yOsa+4);
$pdf_zapisnik->Cell(65, 4, $opis_rizika, 0, 0, 'L', false, '', 1); 

$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(100, $yOsa+4);
$pdf_zapisnik->Cell(100, 4, mb_convert_encoding("Uzrok ¹tete po riziku: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(128, $yOsa+4);
$pdf_zapisnik->Cell(65, 4, $opis_uzroka_stete, 0, 0, 'L', false, '', 1); 
//NOVI RED - KRAJ

// 4.red - podaci o vozilu: vrsta, marka, god.proizvodnje, br.¹asije
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(10, $yOsa+8);
$pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Vrsta: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(17.25, $yOsa+8);
//$pdf_zapisnik->Cell(33, 4, mb_convert_encoding($vrsta_vozila, 'UTF-8', 'ISO-8859-2'), 0, 0, 'L', false, '', 1);  // Marko Markovic
$pdf_zapisnik->Cell(33, 4, $vrsta_vozila, 0, 0, 'L', false, '', 1);  // Marko Markovic
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(50, $yOsa+8);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Marka: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(60, $yOsa+8);
$pdf_zapisnik->Cell(40, 4, $marka_vozila, 0,0,'L',false, 0,1 );
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(100, $yOsa+8);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("God.proizvodnje: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(125, $yOsa+8);
$pdf_zapisnik->Cell(25, 4, $god_proizvodnje_prikaz."godine",0, 0, 'L', false, '', 1);
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(150, $yOsa+8);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Broj ¹asije: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(165, $yOsa+8);
$pdf_zapisnik->Cell(35, 4, $br_sasije,0, 0, 'L', false, '', 1);

// 5.red - podaci o vozilu: reg.oznaka, tip, dat.prve upot., br.motora
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(10, $yOsa+12);
$pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Reg.oznaka: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(27, $yOsa+12);
$pdf_zapisnik->Cell(23, 4, $registracija,0,0,'L',false, 0,1);
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(50, $yOsa+12);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Tip: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(55, $yOsa+12);
$pdf_zapisnik->Cell(45, 4, $tip_vozila,0,0,'L',false, 0,1 );
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(100, $yOsa+12);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Dat.prve upot.: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(120, $yOsa+12);
$pdf_zapisnik->Cell(30, 4, $datum_prve_upotrebe,0,0,'L',false, 0,1);
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(150, $yOsa+12);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Broj motora: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(167, $yOsa+12);
$pdf_zapisnik->Cell(30, 4, $broj_motora,0,0,'L',false, 0,1);

// 6.red - podaci o vozilu: te¾ina, snaga, bojavozila, preðenakm(neboldovano)
$pdf_zapisnik->SetFont($font_name, '', 7);
// $pdf_zapisnik->Rect(10, 56, 190, 8);
// $pdf_zapisnik->Rect(10, 56, 40, 8);
// $pdf_zapisnik->Rect(10, 56, 90, 8);
// $pdf_zapisnik->Rect(10, 56, 140, 8);
// $pdf_zapisnik->Rect(10, 56, 190, 8);
// $pdf_zapisnik->Rect(10, 56, 40, 8);
// $pdf_zapisnik->Rect(10, 56, 90, 8);
// $pdf_zapisnik->Rect(10, 56, 140, 8);
$pdf_zapisnik->SetXY(10, $yOsa+16);
$pdf_zapisnik->Cell(40, 8, "",1);
$pdf_zapisnik->SetXY(50, $yOsa+16);
$pdf_zapisnik->Cell(50, 8, "",1);
$pdf_zapisnik->SetXY(100, $yOsa+16);
$pdf_zapisnik->Cell(50, 8, "",1);
$pdf_zapisnik->SetXY(150, $yOsa+16);
$pdf_zapisnik->Cell(50, 8, "",1);
$pdf_zapisnik->SetXY(10, $yOsa+16);
$pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Te¾ina: ", 'UTF-8', 'ISO-8859-2'),0);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(30, $yOsa+16);
$pdf_zapisnik->Cell(20, 4, $tezina_vozila,0,0,'L',false, 0,1);
$pdf_zapisnik->SetFont($font_name, '', 7); 
$pdf_zapisnik->SetXY(50, $yOsa+16);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Snaga(kw): ", 'UTF-8', 'ISO-8859-2'),0);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(75, $yOsa+16);
$pdf_zapisnik->Cell(25, 4, $snaga_vozila,0,0,'L',false, 0,1);
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(100, $yOsa+16);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Boja vozila: ", 'UTF-8', 'ISO-8859-2'),0);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(125, $yOsa+16);
$pdf_zapisnik->Cell(25, 4, $boja_vozila,0,0,'L',false, 0,1 );
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(150, $yOsa+16);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Preðena km: ", 'UTF-8', 'ISO-8859-2'),0);

// 7.red - podaci o vozilu: nosivost, zapremina, broj vrata, preðenakm(boldovano)
$pdf_zapisnik->SetXY(10, $yOsa+20);
$pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Nosivost: ", 'UTF-8', 'ISO-8859-2'),0);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(30, $yOsa+20);
$pdf_zapisnik->Cell(20, 4, $nosivost_vozila,0,0,'L',false, 0,1);
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(50, $yOsa+20);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Zapremina(ccm): ", 'UTF-8', 'ISO-8859-2'),0);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(75, $yOsa+20);
$pdf_zapisnik->Cell(25, 4, $zapremina_vozila,0,0,'L',false, 0,1);
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(100, $yOsa+20);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Broj vrata: ", 'UTF-8', 'ISO-8859-2'),0);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(125, $yOsa+20);
$pdf_zapisnik->Cell(25, 4, $broj_vrata_vozila,0,0,'L',false, 0,1);
$pdf_zapisnik->SetXY(150, $yOsa+20);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding($predjeno_km, 'UTF-8', 'ISO-8859-2'),0,0,'L',false, 0,1);

// 8.red - podaci o vozilu: vozilo je pokretno, stanje vozila, fotografisano, uvid u zapisnik MUP-a
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(10, $yOsa+24);
$pdf_zapisnik->Cell(190, 4, mb_convert_encoding("Vozilo je pokretno: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(40, $yOsa+24);
$pdf_zapisnik->Cell(20, 4, $pokretnost_vozila,0);
$pdf_zapisnik->SetFont($font_name, '', 7); 
$pdf_zapisnik->SetXY(50, $yOsa+24);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Stanje vozila: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(70, $yOsa+24);
$pdf_zapisnik->Cell(30, 4, $stanje_vozila, 0,0,'L',false, 0,1);
$pdf_zapisnik->SetFont($font_name, '', 7);
$pdf_zapisnik->SetXY(100, $yOsa+24);
$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Fotografisano: ", 'UTF-8', 'ISO-8859-2'),1);
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(125, $yOsa+24);
$pdf_zapisnik->Cell(30, 4, $fotografisanost_vozila,0);
$pdf_zapisnik->SetFont($font_name, '', 7);


if($uvid_u_zapisnik_mupa_vozila == 'EIOS')
{
	$pdf_zapisnik->SetXY(150, $yOsa+24);
	$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Uvid u Evropski izve¹taj: ", 'UTF-8', 'ISO-8859-2'),1);
}
else
{
	$pdf_zapisnik->SetXY(150, $yOsa+24);
	$pdf_zapisnik->Cell(50, 4, mb_convert_encoding("Uvid u zapisnik MUP-a: ", 'UTF-8', 'ISO-8859-2'),1);
}
$pdf_zapisnik->SetFont($font_name, 'B', 7);
$pdf_zapisnik->SetXY(185, $yOsa+24);
$pdf_zapisnik->Cell(30, 4, $uvid_u_zapisnik_mupa_vozila,0);	
// Podnaslov
$pdf_zapisnik->SetFont($font_name, 'B', 12);
$pdf_zapisnik->SetXY(10, $yOsa+32);
$pdf_zapisnik->Cell(190, 6, mb_convert_encoding("Opis o¹teæenja i potrebnih radova na vozilu", 'UTF-8', 'ISO-8859-2'),0,1,'C');
$pdf_zapisnik->SetFont($font_name, '', 7);

// Podela na levu i desnu stranu spiska delova na zapisniku
$delovi_za_zamenu_1 = array_slice($delovi_za_zamenu, 0, 34);
$delovi_za_zamenu_2 = array_slice($delovi_za_zamenu, 34, 76);
$delovi_za_zamenu_3 = array_slice($delovi_za_zamenu, 110, 76);

$delovi_desno_1 = array_slice($delovi_desno, 0, 30);
$delovi_desno_2 = array_slice($delovi_desno, 30, 70);
$delovi_desno_3 = array_slice($delovi_desno, 100, 70);

// POPUNJAVANJE LEVE STRANE sa delovima
// Kreiranje liste za prvih 36 delova za zamenu
if(count($delovi_za_zamenu_1)>0)
{
	$td1 = "";
	$td1 .= "<u>DELOVI ZA ZAMENU</u>";
	$td1 .= "<dl>";
	for ($i = 0; $i < count($delovi_za_zamenu_1); $i++) 
	{
		$td1 .= "<dt>";
		$td1 .= ($i+1).". ".$delovi_za_zamenu_1[$i]['naziv_auto_dela'];
		$td1 .= "</dt>";
	}
	$td1 .= "</dl>";
}

if(count($delovi_za_zamenu_2)>0)
{
	$td3 = "";
	$td3 .= "<u>DELOVI ZA ZAMENU</u>";
	$td3 .= "<dl>";
	// Kreiranje liste za drugih 76 delova za zamenu
	for ($i = 0; $i < count($delovi_za_zamenu_2); $i++)
	{
		$td3 .= "<dt>";
		$td3 .= ($i+35).". ".$delovi_za_zamenu_2[$i]['naziv_auto_dela'];
		$td3 .= "</dt>";
	}
	$td3 .= "</dl>";
}

if(count($delovi_za_zamenu_3)>0)
{
	$td5 = "";
	$td5 .= "<u>DELOVI ZA ZAMENU</u>";
	$td5 .= "<dl>";
	// Kreiranje liste za trecih 76 delova za zamenu
	for ($i = 0; $i < count($delovi_za_zamenu_3); $i++)
	{
		$td5 .= "<dt>";
		$td5 .= ($i+111).". ".$delovi_za_zamenu_3[$i]['naziv_auto_dela'];
		$td5 .= "</dt>";
	}
	$td5 .= "</dl>";
}

// POPUNJAVANJE DESNE STRANE sa delovima
$brojac_desno = 0;
$td2 = "";
if(count($delovi_desno_1)>0)
{
	switch ($delovi_desno_1[0]['nacin']) {
		case 'p':
			$td2 .= "<u>DELOVI ZA POPRAVKU</u>";
			$td2 .= "<dl>";
			break;
		case 'k':
			$td2 .= "<u>ISPITATI</u>";
			$td2 .= "<dl>";
			break;
		case 'o':
			$td2 .= "<u>OSTALI RADOVI</u>";
			$td2 .= "<dl>";
			break;
	}
	for ($i = 0; $i < count($delovi_desno_1); $i++)
	{
		if($i!=0 && $delovi_desno_1[$i]['nacin']!=$delovi_desno_1[$i-1]['nacin'])
		{	
			$td2 .= "</dl>";
			switch ($delovi_desno_1[$i]['nacin']) 
			{
				case 'p':
					$td2 .= "<u>DELOVI ZA POPRAVKU</u>";
					$td2 .= "<dl>";
					break;
				case 'k':
					$td2 .= "<u>ISPITATI</u>";
					$td2 .= "<dl>";
					break;
				case 'o':
					$td2 .= "<u>OSTALI RADOVI</u>";
					$td2 .= "<dl>";
					break;
			}
			$brojac_desno = 0;
		}
		$td2 .= "<dt>";
		$brojac_desno++;
		$td2 .= $brojac_desno.". ".$delovi_desno_1[$i]['deo'];
		$td2 .= "</dt>";
	}
	$td2 .= "</dl>";
}	

$td4 = "";
if(count($delovi_desno_2)>0)
{
	switch ($delovi_desno_2[0]['nacin']) {
		case 'p':
			$td4 .= "<u>DELOVI ZA POPRAVKU</u>";
			$td4 .= "<dl>";
			break;
		case 'k':
			$td4 .= "<u>ISPITATI</u>";
			$td4 .= "<dl>";
			break;
		case 'o':
			$td4 .= "<u>OSTALI RADOVI</u>";
			$td4 .= "<dl>";
			break;
	}
	for ($i = 0; $i < count($delovi_desno_2); $i++)
	{
		if($i!=0 && $delovi_desno_2[$i]['nacin']!=$delovi_desno_2[$i-1]['nacin'])
		{
			$td4 .= "</dl>";
			switch ($delovi_desno_2[$i]['nacin'])
			{
				case 'p':
					$td4 .= "<u>DELOVI ZA POPRAVKU</u>";
					$td4 .= "<dl>";
					break;
				case 'k':
					$td4 .= "<u>ISPITATI</u>";
					$td4 .= "<dl>";
					break;
				case 'o':
					$td4 .= "<u>OSTALI RADOVI</u>";
					$td4 .= "<dl>";
					break;
			}
			$brojac_desno = 0;
		}
		$td4 .= "<dt>";
		$brojac_desno++;
		$td4 .= $brojac_desno.". ".$delovi_desno_2[$i]['deo'];
		$td4 .= "</dt>";
	}
	$td4 .= "</dl>";
}	

$td6 = "";
if(count($delovi_desno_3)>0)
{
	switch ($delovi_desno_3[0]['nacin']) {
		case 'p':
			$td6 .= "<u>DELOVI ZA POPRAVKU</u>";
			$td6 .= "<dl>";
			break;
		case 'k':
			$td6 .= "<u>ISPITATI</u>";
			$td6 .= "<dl>";
			break;
		case 'o':
			$td6 .= "<u>OSTALI RADOVI</u>";
			$td6 .= "<dl>";
			break;
	}
	for ($i = 0; $i < count($delovi_desno_3); $i++)
	{
		if($i!=0 && $delovi_desno_3[$i]['nacin']!=$delovi_desno_3[$i-1]['nacin'])
		{
			$td6 .= "</dl>";
			switch ($delovi_desno_3[$i]['nacin'])
			{
				case 'p':
					$td6 .= "<u>DELOVI ZA POPRAVKU</u>";
					$td6 .= "<dl>";
					break;
				case 'k':
					$td6 .= "<u>ISPITATI</u>";
					$td6 .= "<dl>";
					break;
				case 'o':
					$td6 .= "<u>OSTALI RADOVI</u>";
					$td6 .= "<dl>";
					break;
			}
			$brojac_desno = 0;
		}
		$td6 .= "<dt>";
		$brojac_desno++;
		$td6 .= $brojac_desno.". ".$delovi_desno_3[$i]['deo'];
		$td6 .= "</dt>";
	}
	$td6 .= "</dl>";
}
// Popunjavanje strana Zapisnika
if ($td1 || $td2)
{
$html[1] = "<table>
						<tr>
							<td>
								$td1
							</td>
							<td>
								$td2
							</td>
						</tr>
					</table>";
}

if ($td3 || $td4)
{
	$html[2] = "<table>
								<tr>
									<td>
										$td3
									</td>
									<td>
										$td4
									</td>
									</tr>
								</table>";
}

if ($td5 || $td6) 
{
	$html[3] = "<table>
								<tr>
									<td>
										$td5
									</td>
									<td>
										$td6
									</td>
								</tr>
							</table>";
}

$broj_strana = count($html);

for ($i = 1; $i <= $broj_strana; $i++) {
	$pdf_zapisnik->setPage($i, true);
	
	$pdf_zapisnik->SetFont($font_name, '', 12);
	// $pdf_zapisnik->SetAutoPageBreak(TRUE, 80); //PDF_MARGIN_BOTTOM
	// Logo AMS Osiguranja sa leve strane i adresa
	$pdf_zapisnik->Image(K_PATH_IMAGES.'amsologo.jpg', 10, 10, 32, 18, '', '', '', true, 200);
	$pdf_zapisnik->SetFont($font_name, '', 7);
	$pdf_zapisnik->SetXY(10, 28);
	$pdf_zapisnik->Cell(50,0,"Beograd, Ruzveltova 16",0,1,'L',false,'',1);
	// Naslov: "Zapisnik o utrvðivanju o¹teæenja na vozilu"
	$pdf_zapisnik->SetXY(65, 10);
	$pdf_zapisnik->SetFont($font_name, 'B', 15);
	if ($dopunski && $dopunski != 0) 
	{
		$pdf_zapisnik->Cell(75, 10, mb_convert_encoding("Dopunski zapisnik", 'UTF-8', 'ISO-8859-2'),0,0,'C',false, 0,1 );
	}
	else 
	{
		$pdf_zapisnik->Cell(75, 10, mb_convert_encoding("Zapisnik", 'UTF-8', 'ISO-8859-2'),0,0,'C',false, 0,1 );
	}
	$pdf_zapisnik->SetXY(65, 17);
	$pdf_zapisnik->SetFont($font_name, 'B', 14);
	$pdf_zapisnik->Cell(75, 10, mb_convert_encoding("o utvrðivanju o¹teæenja na vozilu", 'UTF-8', 'ISO-8859-2'),0,0,'C',false, 0,1 );
	if ($dopunski && $dopunski != 0)
	{
		$pdf_zapisnik->SetXY(65, 24);
		$pdf_zapisnik->SetFont($font_name, 'B', 14);
		$pdf_zapisnik->Cell(75, 10, mb_convert_encoding("Broj ".$dopunski, 'UTF-8', 'ISO-8859-2'),0,0,'C',false, 0,1 );
	}
	// Mala tabela gore desno (broj od¹tetnog zahteva, broj polise, uèe¹æe)
	$pdf_zapisnik->SetFont($font_name, '', 7);
	$pdf_zapisnik->SetXY(150, 10);
	$pdf_zapisnik->Cell(50, 7, mb_convert_encoding("Od¹tetni zahtev: ", 'UTF-8', 'ISO-8859-2'),1 );
	$pdf_zapisnik->SetFont($font_name, 'B', 7);
	$pdf_zapisnik->SetXY(175, 10);
	$pdf_zapisnik->Cell(20, 7, $br_odstetnog_zahteva,0 );
	$pdf_zapisnik->SetFont($font_name, '', 7);
	$pdf_zapisnik->SetXY(150, 17);
	$pdf_zapisnik->Cell(50, 7, "Broj polise: ",1 );
	$pdf_zapisnik->SetFont($font_name, 'B', 7);
	$pdf_zapisnik->SetXY(175, 17);
	$pdf_zapisnik->Cell(20, 7, $broj_polise,0 );
	$pdf_zapisnik->SetFont($font_name, '', 7);
	$pdf_zapisnik->SetXY(150, 24);
	$pdf_zapisnik->Cell(50, 7, mb_convert_encoding("Ugovoreno uèe¹æe: ", 'UTF-8', 'ISO-8859-2'),1);
	$pdf_zapisnik->SetFont($font_name, '', 6);
	$pdf_zapisnik->SetXY(175, 22);
	$pdf_zapisnik->Cell(15, 7, "procenat: ",0);
	$pdf_zapisnik->SetFont($font_name, 'B', 7);
	$pdf_zapisnik->SetXY(187.5, 22);
	$pdf_zapisnik->Cell(5, 7, $ugov_ucesce_procenat." %",0);
	$pdf_zapisnik->SetFont($font_name, '', 6);
	$pdf_zapisnik->SetXY(175, 25);
	$pdf_zapisnik->Cell(15, 7, "minimum: ",0);
	$pdf_zapisnik->SetFont($font_name, 'B', 7);
	$pdf_zapisnik->SetXY(181, 25);
	$pdf_zapisnik->Cell(12, 7, $ucesce_minimum,0, 0, 'R');
	$pdf_zapisnik->SetXY(192, 25);
	$html_euro_znak = "&#8364;";
	$pdf_zapisnik->writeHTML($html_euro_znak, true, false, false, false, $align='');
	$pdf_zapisnik->SetFont($font_name, '', 7);
	
	if ($i==1) 
	{
		$pdf_zapisnik->SetXY(10,80);
	}
	else 
	{
		$pdf_zapisnik->SetXY(10,40);
	}
	
	if ($i!=1)
	{
		$pdf_zapisnik->SetFont($font_name, 'B', 12);
		$pdf_zapisnik->SetXY(10, 32);
		$pdf_zapisnik->Cell(190, 6, mb_convert_encoding("Opis o¹teæenja i potrebnih radova na vozilu (NASTAVAK)", 'UTF-8', 'ISO-8859-2'),0,1,'C');
		$pdf_zapisnik->SetFont($font_name, '', 7);
	}

	//DODAO VLADA - PRAZAN RED ZBOG RAZMAKA
	$tabela = "<table>
				<tr>
					<td>
					</td>
					<td>
					</td>
				</tr>
			</table>";
	
	//UPIS TABELE U HTML - DODAO VLADA
	$pdf_zapisnik->writeHTML($tabela);

	$pdf_zapisnik->writeHTML($html[$i]);
	
	if($i==1)
	{
		// Napomene gornji deo
		$pdf_zapisnik->SetXY(10,195);
		$pdf_zapisnik->Cell(190,40,'',1 );
		//Obavezne napomene
		$sql_napomena_gore_obavezne = "SELECT * FROM sifarnici.zapisnik_napomene WHERE obavezno = TRUE AND pozicija_pdf = 'G' AND redni_broj_napomene <> 0 ORDER BY id";
		$rezultat_napomena_gore_obavezne = pg_query($conn_stete, $sql_napomena_gore_obavezne);
		$niz_napomena_gore_obavezne = pg_fetch_all($rezultat_napomena_gore_obavezne);
		$niz_napomene_gore = array();
		$napomena_ispis = "";
		for ($j = 0; $j < count($niz_napomena_gore_obavezne); $j++) {
			if ($niz_napomena_gore_obavezne[$j]['prefix_sufix_celo'] == 'cela' || $niz_napomena_gore_obavezne[$j]['prefix_sufix_celo'] == 'sufix') 
			{
				$napomena_ispis .= $niz_napomena_gore_obavezne[$j]['napomena'];
				$niz_za_dodati = array( 'napomena' => $napomena_ispis);
				array_push($niz_napomene_gore, $niz_za_dodati);
				$napomena_ispis = "";
			}
			else 
			{
				$napomena_ispis .= $niz_napomena_gore_obavezne[$j]['napomena'];
			}
		}
		
// 		var_dump($niz_napomena_gore_obavezne);exit;
		// Obavezna napomena 1
		$pdf_zapisnik->SetXY(10, 195);
		// $pdf_zapisnik->Cell(190,5, mb_convert_encoding($niz_napomene_gore[0]['napomena'], "UTF-8", "ISO-8859-2"),0,1,'L',false,'',1);
		$pdf_zapisnik->Cell(190,5, $niz_napomene_gore[0]['napomena'], 0,1,'L',false,'',1);
		$pdf_zapisnik->SetXY(10, 199);
		// $pdf_zapisnik->Cell(190,5, mb_convert_encoding($niz_napomene_gore[1]['napomena'], "UTF-8", "ISO-8859-2"),0,1,'L',false,'',1);
		$pdf_zapisnik->Cell(190,5, $niz_napomene_gore[1]['napomena'], 0,1,'L',false,'',1);
		// Napomena ruèno upisana
		$pdf_zapisnik->SetXY(10, 204);
// 		$pdf_zapisnik->Cell(190,5,$napomena_rucno_uneta,0,1,'L',false,'',1);
		// $pdf_zapisnik->MultiCell(190, 30, mb_convert_encoding($napomena_rucno_uneta, "UTF-8", "ISO-8859-2"),0,'L',false,1, '', '', true, 3, false, true, 30, 'T', true);
		$pdf_zapisnik->MultiCell(190, 30, $napomena_rucno_uneta, 0,'L',false,1, '', '', true, 3, false, true, 30, 'T', true);


		// Napomene levi deo (uvek iste)
		$sql_napomena_levo = "SELECT napomena FROM sifarnici.zapisnik_napomene WHERE pozicija_pdf = 'L' AND redni_broj_napomene <> 0 ORDER BY id";
		$rezultat_napomena_levo = pg_query($conn_stete, $sql_napomena_levo);
		$niz_napomena_levo = pg_fetch_all($rezultat_napomena_levo);
		$pdf_zapisnik->SetXY(10,235);
		$pdf_zapisnik->Cell(95,40,'',1 );
		$pdf_zapisnik->SetXY(10,235);

		// $niz_napomena_levo = mb_convert_encoding($niz_napomena_levo, "UTF-8", "ISO-8859-2");

		// $pdf_zapisnik->MultiCell(95, 10, mb_convert_encoding($niz_napomena_levo[0]['napomena'], "UTF-8", "ISO-8859-2"),0,'L');
		$pdf_zapisnik->MultiCell(95, 10, $niz_napomena_levo[0]['napomena'], 0,'L');
		$pdf_zapisnik->SetXY(10,242);
		// $pdf_zapisnik->MultiCell(95, 10, mb_convert_encoding($niz_napomena_levo[1]['napomena'], "UTF-8", "ISO-8859-2"),0,'L');
		$pdf_zapisnik->MultiCell(95, 10, $niz_napomena_levo[1]['napomena'], 0,'L');
		$pdf_zapisnik->SetXY(10,249);
		// $pdf_zapisnik->MultiCell(95, 20, mb_convert_encoding($niz_napomena_levo[2]['napomena'], "UTF-8", "ISO-8859-2"),0,'L');
		$pdf_zapisnik->MultiCell(95, 20, $niz_napomena_levo[2]['napomena'],0,'L');
		// Rad, farbanje i ukupno
		$pdf_zapisnik->SetFont($font_name, '', 10);
		$pdf_zapisnik->SetXY(105,235);
		$pdf_zapisnik->Cell(95,40,'',1 );
		$pdf_zapisnik->SetXY(105,235);
		$pdf_zapisnik->Cell(75, 5, mb_convert_encoding("Rad: ", 'UTF-8', 'ISO-8859-2'));
		$pdf_zapisnik->SetXY(178,235);
		$pdf_zapisnik->Cell(20, 5, $rad." h", 0,0,'R',false, 0,1 );
		$pdf_zapisnik->SetXY(105,242);
		$pdf_zapisnik->Cell(75, 5, mb_convert_encoding("Farbanje: ", 'UTF-8', 'ISO-8859-2'));
		$pdf_zapisnik->SetXY(178,242);
		$pdf_zapisnik->Cell(20, 5, $farbanje." h", 0,0,'R',false, 0,1 );
		$pdf_zapisnik->SetXY(105,249);
		$pdf_zapisnik->Cell(75, 5, mb_convert_encoding("Ukupno vreme po zanatima: ", 'UTF-8', 'ISO-8859-2'));
		$pdf_zapisnik->SetXY(178,249);
		$pdf_zapisnik->Cell(20, 5, $ukupni_rad." h", 0,0,'R',false, 0,1 );
		$pdf_zapisnik->SetFont($font_name, '', 7);
		$pdf_zapisnik->SetXY(10,275);
		$pdf_zapisnik->Cell(30, 5, mb_convert_encoding("O¹teæenik: ", 'UTF-8', 'ISO-8859-2'), 0,0,'L',false, 0,1 );
		$pdf_zapisnik->SetXY(95,275);
		$pdf_zapisnik->Cell(20, 5, mb_convert_encoding("U Beogradu, ", 'UTF-8', 'ISO-8859-2'), 0,0,'C',false, 0,1 );
		$pdf_zapisnik->SetXY(170,275);
		$pdf_zapisnik->Cell(30, 5, mb_convert_encoding("Procenitelj: ".$procenitelj_uradio, 'UTF-8', 'ISO-8859-2'), 0,0,'R',false, 0,1 );
		$pdf_zapisnik->SetXY(10,280);
		$pdf_zapisnik->Cell(30, 5, mb_convert_encoding("________________________________", 'UTF-8', 'ISO-8859-2'), 0,0,'L',false, 0,1 );
		$pdf_zapisnik->SetXY(95,280);
		$pdf_zapisnik->Cell(20, 5, mb_convert_encoding($datum_snimanja, 'UTF-8', 'ISO-8859-2'), 0,0,'C',false, 0,1 );

// --------------------- Marko Markovic 2020-03-25 potpis procenitelja -----------------------------
		$pdf_zapisnik->SetXY(170,280);
		if($radnik_id == 3093)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'andrejic.png', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'andrejic_milos.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_andrejic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3081)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'kojic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'kojic_dejan.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_kojic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3078)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'gardovic.png', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_gardovic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_gardovic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3119)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'banic.png', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'banic_djordje.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_banic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3116)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'mirkovic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'mirkovic_marko.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_mirkovic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3029)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'mandic.png', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'mandic_sasa.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_mandic_sasa.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_s_mandic_crop_resize.jpg', 160, 280, 40, 6, '', '', '', true, 200);

			// $pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_sasa_mandic.jpg', 160, 280, 40, 6, '', '', '', true, 200);	
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_mandic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3033)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'braunovic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'braunovic_darko.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_braunovic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3079)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'slobodan_krstic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_krstic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3038)
		{
			// $pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_ivetic.jpg', 160, 280, 40, 6, '', '', '', true, 200);     
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_ivetic.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}

		//----------- 2020-06-22 --- Marko Markovic novi procenitelji ---- 
		else if($radnik_id == 15)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_andonovic_danijel.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 21)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_bogic_dedovic.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 23)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_buturovic_zeljko.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 16)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_dragan_zvekic.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 22)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_dusko_urosevic.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 18)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_markovic_marko.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 17)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_milan_karas.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 20)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_miodrag_jolic.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 24)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_nikolic_nenad.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 19)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_sreckovic_nenad.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 25)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_vladimir_djinic.png', 160, 280, 40, 6, '', '', '', true, 200);
		}
		else if($radnik_id == 3123)
		{
			$pdf_zapisnik->Image(K_PATH_IMAGES.'potpis_p_vidakovic_m.jpg', 160, 280, 40, 6, '', '', '', true, 200);
		}
		//-----------

		else 
		{
			$pdf_zapisnik->Cell(30, 5, mb_convert_encoding("________________________________", 'UTF-8', 'ISO-8859-2'), 0,0,'R',false, 0,1 );
		}
// ------------------- Marko Markovic kraj --------------------------
	}
	
	if ($i!=$broj_strana) 
	{
		$pdf_zapisnik->AddPage();
	}
}

if($_GET['procena_email'])
{
	$current_timestamp = strtotime("now");
	$radnik = $_SESSION['radnik'];
	$naziv_pdf =  $id_stete."-".$radnik."-".$current_timestamp;
	$href = "../arhiva/stete/dokumentacija_tmp_za_mail/".$naziv_pdf.".pdf";

	
	$pdf_zapisnik->Output($href, "F");
	
	$ret = new stdClass();
	
	$ret->href = $href;
	$ret->naziv_pdf = $naziv_pdf.".pdf";
	
	echo json_encode($ret);
	exit();
}
else 
{
	$pdf_zapisnik->Output("Zapisnik-".$naziv_dokumenta.".pdf", "D");
}


// ostecenja na vozilu:

?>
<script text='text/javascript' />
window.close();
</script>