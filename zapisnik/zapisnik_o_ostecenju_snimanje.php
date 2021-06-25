<?php
session_start();
if (isset($_SESSION['radnik']) && $_SESSION['radnik']) {
	$radnik = $_SESSION['radnik'];
}
else {
	session_destroy();
	header("Location: ../../common/login.php");
	exit;
}
header('Content-type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Fri, 25 Jul 1997 00:00:00 GMT');

$conn_stete = pg_connect ("dbname=stete user=zoranp");
if (!$conn_stete) {
	echo "<br><br>Do¹lo je do gre¹ke prilikom konektovanja.\n";
	exit;
}
// Da li je trajno snimanje ili nije?
$trajno = $_POST['trajno'];
$datum_snimanja = $_POST['datum_snimanja'];
$datum_snimanja = date("Y-m-d", strtotime($datum_snimanja));


// podaci iz liste za zamenu delova
$lista_zamena_id_auto = $_POST['lista_zamena_id_auto'];
$lista_zamena_id_stepen = $_POST['lista_zamena_id_stepen'];
$lista_zamena_amortizacija_dela = $_POST['lista_zamena_amortizacija_dela'];
// podaci iz liste za popravku delova
$lista_popravka_id_auto = $_POST['lista_popravka_id_auto'];
$lista_popravka_id_stepen = $_POST['lista_popravka_id_stepen'];
$lista_popravka_amortizacija_dela = $_POST['lista_popravka_amortizacija_dela'];
// podaci iz liste za kontrola 
$lista_kontrola_id_auto = $_POST['lista_kontrola_id_auto'];
$lista_kontrola_id_stepen = $_POST['lista_kontrola_id_stepen'];
$lista_kontrola_amortizacija_dela = $_POST['lista_kontrola_amortizacija_dela'];
// podaci iz liste za ostale radove
$lista_oradovi_id_auto = $_POST['lista_oradovi_id_auto'];
$lista_oradovi_id_stepen = $_POST['lista_oradovi_id_stepen'];
$lista_oradovi_amortizacija_dela = $_POST['lista_oradovi_amortizacija_dela'];
$osnovni_predmet_id_reaktiviranog = $_POST['osnovni_predmet_id_reaktiviranog'];
// podaci sa forme 
$podaci_sa_forme = $_POST['podaci_sa_forme'];

// podaci sa odstetnog zahteva
$id_stete = $podaci_sa_forme[0]['value'];
$broj_stete = $podaci_sa_forme[1]['value'];
$broj_polise = $podaci_sa_forme[2]['value'];
$ucesce_procenat = $podaci_sa_forme[3]['value'];
$ucesce_minimum = $podaci_sa_forme[4]['value'];
$ucesce_minimum = ($ucesce_minimum=="") ? 0 : $ucesce_minimum;
$datum_pregleda = $podaci_sa_forme[5]['value'];
$datum_pregleda = date("Y-m-d", strtotime($datum_pregleda));
$osiguranik = strtoupper($podaci_sa_forme[6]['value']);
$mesto = strtoupper($podaci_sa_forme[7]['value']);
$adresa = strtoupper($podaci_sa_forme[8]['value']);
$datum_nastanka = $podaci_sa_forme[9]['value'];
$mesto_nezgode = strtoupper($podaci_sa_forme[10]['value']);
$uzrok_nezgode = strtoupper($podaci_sa_forme[11]['value']);
$marka_vozila = strtoupper($podaci_sa_forme[12]['value']);
$tip_vozila = strtoupper($podaci_sa_forme[13]['value']);
$godina_proizvodnje = $podaci_sa_forme[14]['value'];
$broj_sasije = strtoupper($podaci_sa_forme[15]['value']);
$reg_oznaka = $podaci_sa_forme[16]['value'] ? strtoupper($podaci_sa_forme[16]['value']) : "null";
// podaci sa ao polise ili sa zapisnika 
$vrsta_vozila = $podaci_sa_forme[17]['value'] ? strtoupper($podaci_sa_forme[17]['value']) : "";
$mesto_pregleda = $podaci_sa_forme[18]['value'] ? strtoupper($podaci_sa_forme[18]['value']) : null;
//$mesto_pregleda = mb_convert_encoding($mesto_pregleda, 'ISO-8859-2','UTF-8');
$tezina_vozila = $podaci_sa_forme[19]['value'] ? $podaci_sa_forme[19]['value'] : "null";
$nosivost_vozila = $podaci_sa_forme[20]['value'] ? $podaci_sa_forme[20]['value'] : "null";
$boja_vozila = $podaci_sa_forme[21]['value'] ? strtoupper($podaci_sa_forme[21]['value']) : null;
$snaga_vozila= $podaci_sa_forme[22]['value'] ? $podaci_sa_forme[22]['value'] : "null";
$zapremina_vozila= $podaci_sa_forme[23]['value'] ? $podaci_sa_forme[23]['value'] : "null";
$broj_vrata= $podaci_sa_forme[24]['value'] ? $podaci_sa_forme[24]['value'] : "null";
$predjeno_km = $podaci_sa_forme[25]['value'] ? $podaci_sa_forme[25]['value'] : "";
$broj_motora = $podaci_sa_forme[26]['value'] ? strtoupper($podaci_sa_forme[26]['value']) : null;
$broj_motora = "'$broj_motora'";
$rad = $podaci_sa_forme[27]['value'] ? $podaci_sa_forme[27]['value'] : 0;
$farbanje = $podaci_sa_forme[28]['value'] ? $podaci_sa_forme[28]['value'] : 0;

$stanje_vozila = $podaci_sa_forme[29]['value'] ? strtoupper($podaci_sa_forme[29]['value']) : null;
$stanje_vozila = "'$stanje_vozila'";

if($podaci_sa_forme[30]['name']=="vozilo_pokretno")
{
	$vozilo_pokretno = $podaci_sa_forme[30]['value'];
	if($vozilo_pokretno=="DA")
		$vozilo_pok = "'DA'";
	else if ($vozilo_pokretno=="NE")
		$vozilo_pok = "'NE'";
}
else 
{
	$vozilo_pok="null";
}

if($podaci_sa_forme[31]['name']=="vozilo_foto")
{
	$vozilo_fotografisano = $podaci_sa_forme[31]['value'];
	if($vozilo_fotografisano=="DA")
		$vozilo_fot = "'DA'";
	else if ($vozilo_fotografisano=="NE")
		$vozilo_fot = "'NE'";
}
else
{
	$vozilo_fot="null";
}

if($podaci_sa_forme[32]['name']=="zapisnik_mup")
{
	$vozilo_zapisnik_mup = $podaci_sa_forme[32]['value'];
	if($vozilo_zapisnik_mup =="DA")
		$vozilo_zap = "'DA'";
	else if ($vozilo_zapisnik_mup =="NE")
		$vozilo_zap = "'NE'";
	else if ($vozilo_zapisnik_mup =="EIOS")
		$vozilo_zap = "'EIOS'";
}
else
{
	$vozilo_zap="null";
}

$napomena = $podaci_sa_forme[33]['value'] ? $podaci_sa_forme[33]['value'] : null;
$napomena = "'$napomena'";
$procenitelj_uradio = $podaci_sa_forme[34]['value'] ? $podaci_sa_forme[34]['value'] : null;
$procenitelj_uradio = "'$procenitelj_uradio'";


//$auto_delovi_kategorija = $podaci_sa_forme[27]['value'];  // 1 prednji deo   2 zadnji deo


// provera
$provera = 1;
$provera_stavke = 1;
$chek =1;


$sql = "SET client_encoding TO 'UTF-8'";
$rezult = pg_query($conn_stete,$sql); 
 
$sql="begin;";
$rezult = pg_query($conn_stete, $sql);

// PROVERA DA LI POSTOJI URADJEN ZAPISNIK po id_stete

if($osnovni_predmet_id_reaktiviranog && $osnovni_predmet_id_reaktiviranog != $id_stete){
	$sqlPrethodniZapisnici = "SELECT id FROM predmet_odstetnog_zahteva WHERE (osnovni_predmet_id = $osnovni_predmet_id_reaktiviranog OR id=$osnovni_predmet_id_reaktiviranog) AND id<=$id_stete";
	$rezultatPrethodniZapisnici = pg_query($conn_stete,$sqlPrethodniZapisnici);
	$rezultatPrethodni = pg_fetch_all_columns($rezultatPrethodniZapisnici);
	$idZapisnici = implode(",", $rezultatPrethodni);
	$deo_upita = "id_stete IN ($idZapisnici)";
	
}
else 
{
	$deo_upita = "id_stete = $id_stete";
}


$sqlImaZap = "SELECT * FROM zapisnik_o_ostecenju_vozila WHERE $deo_upita";
$rezImaZap = pg_query($sqlImaZap);
$rez = pg_num_rows($rezImaZap);
//var_dump($rez);
// AKO NEMA PRETHODNO SNIMLJENOG ZAPISNIKA PO OVOJ STETE, ONDA NIJE DOPUNSKI, SVAKI SLEDECI JESTE DOPUNSKI
if($rez==0)
{
	$dopunski_s =0;
}
else 
{
	$sqlImaZap = "SELECT MAX(dopunski) AS dopunski FROM zapisnik_o_ostecenju_vozila WHERE $deo_upita";
	$rezImaZap = pg_query($sqlImaZap);
	$rez_niz = pg_fetch_assoc($rezImaZap);
	$dopunski = $rez_niz['dopunski'];
// 	if($trajno == 0 && $dopunski)
// 	if($trajno == 0)
// 	{ 
// 		$dopunski_s = $dopunski;
// 	}
// 	else
// 	{
// 		$dopunski_s = $dopunski+1;
// 	}
	$dopunski_s = $dopunski+1;
	
		// Dozvoljeno je da ima maksimalno 1 zapisnik koji ima trajno=0::bit
	$sqlTrajnoZapisnik = "SELECT * FROM zapisnik_o_ostecenju_vozila WHERE trajno=0::bit AND id_stete=".$id_stete;
	$rezultatTrajnoZapisnik = pg_query($sqlTrajnoZapisnik);
	$podaciTrajnoZapisnik = pg_fetch_array($rezultatTrajnoZapisnik);
	if ($podaciTrajnoZapisnik) 
	{
		$trajnoZapisnik = $podaciTrajnoZapisnik['trajno'];
		$idZapisnik = $podaciTrajnoZapisnik['id'];
		$dopunski_s = $podaciTrajnoZapisnik['dopunski'];
	}
	
}

//SNIMANJE REDA U TABELU ZAPISNIK
if($podaciTrajnoZapisnik)
{
	// Ukoliko ima jednog sa TRAJNO=0::BIT onda se radi update osnovne tabele i brisanje svih stavki
	// Obrisi sve stavke - zapisnik_o_ostecenju_stavke
	$sqlDeleteZapisnikStavke = "DELETE FROM
																zapisnik_o_ostecenju_stavke
															WHERE
																id_zapisnik = $idZapisnik
															";
	$rezultDeleteZapisnikStavke = pg_query($conn_stete, $sqlDeleteZapisnikStavke);
	// UPDATE osnovne tabele - zapisnik_o_ostecenju_vozila
	$sqlUpdateZapisnik = "UPDATE 
														zapisnik_o_ostecenju_vozila
										  	SET 
										  			id_stete=$id_stete, datum_pregleda='$datum_pregleda', mesto_pregleda=upper('$mesto_pregleda'), tezina_vozila=$tezina_vozila, 
										       	nosivost_vozila=$nosivost_vozila, boja_vozila=upper('$boja_vozila'), broj_vrata=$broj_vrata, snaga_vozila=$snaga_vozila, 
										       	zapremina_vozila=$zapremina_vozila, vrsta_vozila=upper('$vrsta_vozila'), predjeno_km=upper('$predjeno_km'), stanje_vozila=upper($stanje_vozila), 
										       	datum_vreme=default, vozilo_pokretno=$vozilo_pok, vozilo_fotografisano=$vozilo_fot, uvid_u_zapisnik_mupa=$vozilo_zap, 
										       	dopunski=$dopunski_s, ucesce_minimum=$ucesce_minimum, rad=$rad, farbanje=$farbanje, broj_motora=upper($broj_motora), 
										       	procenitelj_uradio=$procenitelj_uradio, napomena=$napomena, trajno=$trajno::bit, datum_snimanja='$datum_snimanja'
										 		WHERE 
										 				id=$idZapisnik;
												";
	$rezultZapisnik = pg_query($conn_stete, $sqlUpdateZapisnik);
	$id_zapisnika = $idZapisnik;

}
else 
{
	// Ukoliko nema onda je ovaj upit ispod
	$sqlInsertZapisnik = "INSERT INTO 
														zapisnik_o_ostecenju_vozila 
												VALUES 
													(default, $id_stete, '$datum_pregleda', upper('$mesto_pregleda'), $tezina_vozila, $nosivost_vozila,
							  					upper('$boja_vozila'), $broj_vrata, $snaga_vozila, $zapremina_vozila, upper('$vrsta_vozila'), upper('$predjeno_km'), 
													upper($stanje_vozila), default, $vozilo_pok,$vozilo_fot,$vozilo_zap,$dopunski_s, $ucesce_minimum, 
													$rad, $farbanje, upper($broj_motora), $procenitelj_uradio, $napomena , $trajno::bit, '$datum_snimanja')
												RETURNING id
												";
	$rezultZapisnik = pg_query($conn_stete, $sqlInsertZapisnik);
	$rez = pg_fetch_assoc($rezultZapisnik);
	$id_zapisnika = $rez['id'];
}
// Za proveru
// $list['rezultat'] = 0;
// $list['poruka'] = $sqlInsertZapisnik.$sqlUpdateZapisnik;
// echo json_encode($list);
// exit;

// provera da li upseno izvrsen upit
if(!$rezultZapisnik){
	$provera=0;
}

// dohvatanje poslednjeg id-a zapisnika

// 1-- PODACI ZA ZAPISNIK STAVKE INSERT DELOVA ZA ZAMENU
$string_id_auto_deo_zamena = strlen($lista_zamena_id_auto);
if($string_id_auto_deo_zamena>0)
{
	$lista_zamena_id_auto_niz = explode(',',$lista_zamena_id_auto);

	$br_eleme_id_auto_z = count($lista_zamena_id_auto_niz);
	$lista_zamena_id_stepen_niz = explode(',',$lista_zamena_id_stepen);
	$lista_zamena_amortizacija_dela_niz = explode(',',$lista_zamena_amortizacija_dela); 
	
	for($i=0; $i<$br_eleme_id_auto_z; $i++)
	{
		$id_auto_dela = $lista_zamena_id_auto_niz[$i];
		$id_stepen_ost = $lista_zamena_id_stepen_niz[$i];
		$amortizacija_dela = $lista_zamena_amortizacija_dela_niz[$i];
	
		$sqlInsertStavkeZapisnik = "INSERT INTO zapisnik_o_ostecenju_stavke VALUES (default, $id_zapisnika, $id_auto_dela, $id_stepen_ost, 1::bit, 0::bit, 0::bit,0::bit,$amortizacija_dela)";
		$rezInsertStavke1 = pg_query($conn_stete,$sqlInsertStavkeZapisnik);
		// provera da li upseno izvrsen upit
		if(!$rezInsertStavke1 ){
			$provera_stavke=0;
			$chek=0;
		}
	}
}
// 2-- PODACI STAVKE INSERT DELOVA ZA POPRAVKU
$string_id_auto_deo_popravka = strlen($lista_popravka_id_auto);
if($string_id_auto_deo_popravka>0)
{
	$lista_popravka_id_auto_niz = explode(',',$lista_popravka_id_auto);
	$br_eleme_id_auto_p = count($lista_popravka_id_auto_niz);
	$lista_popravka_id_stepen_niz = explode(',',$lista_popravka_id_stepen);
	$lista_popravka_amortizacija_dela_niz = explode(',',$lista_popravka_amortizacija_dela); 
	
	for($i=0; $i<$br_eleme_id_auto_p; $i++)
	{
		$id_auto_dela = $lista_popravka_id_auto_niz[$i];
		$id_stepen_ost = $lista_popravka_id_stepen_niz[$i];
		$amortizacija_dela = $lista_popravka_amortizacija_dela_niz[$i];
	
		$sqlInsertStavkeZapisnik = "INSERT INTO zapisnik_o_ostecenju_stavke VALUES (default, $id_zapisnika, $id_auto_dela, $id_stepen_ost, 0::bit, 1::bit, 0::bit,0::bit,$amortizacija_dela)";
		$rezInsertStavke2 = pg_query($conn_stete,$sqlInsertStavkeZapisnik);
		// provera da li upseno izvrsen upit
		if(!$rezInsertStavke2 && $chek==0){
			$chek=0;
			$provera_stavke=0;
		}
		
	}
}
// 3-- PODACI ZA ZAPISNIK STAVKE INSERT DELOVA ZA KONTROLU
$string_id_auto_deo_kontrola = strlen($lista_kontrola_id_auto);
if($string_id_auto_deo_kontrola>0)
{
	$lista_kontrola_id_auto_niz = explode(',',$lista_kontrola_id_auto);
	$br_eleme_id_auto_k = count($lista_kontrola_id_auto_niz);
	$lista_kontrola_id_stepen_niz = explode(',',$lista_kontrola_id_stepen);
	$lista_kontrola_amortizacija_dela_niz = explode(',',$lista_kontrola_amortizacija_dela); 
	
	for($i=0; $i<$br_eleme_id_auto_k; $i++)
	{
		$id_auto_dela = $lista_kontrola_id_auto_niz[$i];
		$id_stepen_ost = $lista_kontrola_id_stepen_niz[$i];
		$amortizacija_dela = $lista_kontrola_amortizacija_dela_niz[$i];
	
		$sqlInsertStavkeZapisnik = "INSERT INTO zapisnik_o_ostecenju_stavke VALUES (default, $id_zapisnika, $id_auto_dela, $id_stepen_ost, 0::bit, 0::bit, 1::bit,0::bit,$amortizacija_dela)";
		$rezInsertStavke3 = pg_query($conn_stete,$sqlInsertStavkeZapisnik);
		// provera da li upseno izvrsen upit
		if(!$rezInsertStavke3 && $chek==0){
			$provera_stavke=0;
			$chek=0;
		}
	}
}	
// 4-- PODACI STAVKE INSERT DELOVA ZA OSTALE RADOVE
$string_id_auto_deo_oradova = strlen($lista_oradovi_id_auto);
if($string_id_auto_deo_oradova>0)
{
	$lista_oradovi_id_auto_niz = explode(',',$lista_oradovi_id_auto);
	$br_eleme_id_auto_o = count($lista_oradovi_id_auto_niz);
	$lista_oradovi_id_stepen_niz = explode(',',$lista_oradovi_id_stepen);
	$lista_oradovi_amortizacija_dela_niz = explode(',',$lista_oradovi_amortizacija_dela); 
	
	for($i=0; $i<$br_eleme_id_auto_o; $i++)
	{
		$id_auto_dela = $lista_oradovi_id_auto_niz[$i];
		$id_stepen_ost = $lista_oradovi_id_stepen_niz[$i];
		$amortizacija_dela = $lista_oradovi_amortizacija_dela_niz[$i];
	
		$sqlInsertStavkeZapisnik = "INSERT INTO zapisnik_o_ostecenju_stavke VALUES (default, $id_zapisnika, $id_auto_dela, $id_stepen_ost, 0::bit, 0::bit, 0::bit,1::bit,$amortizacija_dela)";
		$rezInsertStavke4 = pg_query($conn_stete,$sqlInsertStavkeZapisnik);
		// provera da li upseno izvrsen upit
		if(!$rezInsertStavke4 && $chek==0){
			$provera_stavke=0;
			$chek=0;
		}
	}
}

if($provera==1 && $provera_stavke==1)
{
	$sql="commit;";
	$rezult = pg_query($conn_stete, $sql);
	$rezul = 1;
}

$list['idstete'] = $id_stete;
$list['dopunski']= $dopunski_s;
$list['rezultat'] = $rezul;

echo json_encode($list);

/*
 * Za proveru 
 * $list['rezultat'] = 0;
 * $list['poruka'] = $sqlInsertZapisnik;
 * echo json_encode($list);
 * exit;
 * */

pg_close($conn_stete);
?>