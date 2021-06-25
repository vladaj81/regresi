<?php

function radio($ime, $broj, $vrednosti, $opis)
{
for ($j=0; $j < $broj; $j++) {
    echo "<input name=\"$ime\" type=\"radio\" value=\"" . $vrednosti[$j] . "\">";
    echo $opis[$j] . "\n";
    }
}

function opcija($vrednost, $tekst, $stara, $pamti) {
echo "<option value=\"" . $vrednost;
if ($vrednost == $stara) { echo " selected"; }
echo ">\n";
}

function izbor($ime, $opis, $conn, $tabela, $vraca1, $vraca2)
{
echo "<font>$opis:</font>   ";
echo "<select name=\"$ime\">\n";
$sql = "SELECT $vraca1, $vraca2 FROM $tabela ORDER BY $vraca2";
$rezultat = pg_query($conn, $sql);
// $polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);
for ($a=0; $a < $redova; $a++) {
    $niz = pg_fetch_assoc ($result);
    opcija($niz[0], $niz[2], $ime, 1);
    }
echo "</select>\n";
}

function l_dane($opis, $ime, $pamti, $vrednost)
{
echo "<tr><td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td>";
echo "&nbsp;<input name=\"$ime\" type=\"radio\" value=\"F\"";
if ($vrednost == "F") { echo " checked "; }
echo "onkeypress=\"return handleEnter(this, event)\"";
echo ">DA<br>\n";
echo "&nbsp;";
echo "<input name=\"$ime\" type=\"radio\" value=\"P\"";
if ($vrednost == "P") { echo " checked "; }
echo "onkeypress=\"return handleEnter(this, event)\"";
echo ">NE\n";
echo "</td>\n</tr>\n";
}

function polje_za_unos($opis, $ime, $duzina, $pamti, $vrednost)
{
echo "<tr><td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td>";
echo "<input name=\"$ime\" type=\"text\"";
if ($pamti == 1) { echo " value=\"" . $vrednost . "\""; }
echo " size=\"$duzina\" class=\"main\" onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n</tr>\n";
}

function polje_datuma($opis, $ime, $duzina, $pamti, $vrednost)
{
echo "<tr><td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td>";
echo "<input name=\"$ime\" type=\"text\"";
if ($pamti == 1) { echo " value=\"" . $vrednost . "\""; }
echo " size=\"$duzina\" class=\"main\" ";
echo " onclick=\"showCal('$ime')\" onkeypress=\"return handleEnter(this, event)\">\n";
echo "&nbsp;&nbsp;<font color=\"#CC0000\">Za izbor/promenu/brisanje datuma klikni u polje.</font>";
echo "</td>\n</tr>\n";
}


function tekst_podrucje($ime, $kolona, $redova, $vrednost)
{
if (!$vrednost) { $vrednost = ""; }
echo "<textarea name=\"$ime\" cols=\"$kolona\" rows=\"$redova\">";
echo $vrednost;
echo "</textarea>";
}

function drop_kombo($opis, $ime, $conn, $tabela, $vraca1, $vraca2, $vrednost)
{
$rezultat = pg_query ($conn, "SELECT $vraca1, $vraca2 FROM $tabela ORDER BY $vraca2");
if (!$rezultat) {
    echo "<br><br>Do¹lo je do gre¹ke prilikom konektovanja.\n";
    exit;
}

$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);

echo "<td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td>";

echo "<select name=\"$ime\">\n";

for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($rezultat);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($rezultat, $j) == $vraca1) {
    echo "<option ";
    if ($vrednost == $arr[$vraca1]) { echo "selected "; }
    echo "value = \"". $arr[$vraca1] . "\">";
    $sadrzaj_polja = $arr[$vraca2] . " ";
    }
    else {
        $sadrzaj_polja .= ' - ' . $arr[$vraca1];
	echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
}

//"SELECT $vraca1, $vraca2 FROM $tabela ORDER BY $vraca1"
function drop_kombo1($opis,$ime, $conn, $tabela, $vraca1, $vraca2, $vrednost)
{
$rezultat = pg_query ($conn, "SELECT $vraca1, $vraca2 FROM $tabela ORDER BY $vraca1");
if (!$rezultat) {
    echo "<br><br>Do¹lo je do gre¹ke prilikom konektovanja.\n";
    exit;
}

$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);

echo "<td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td";
if ($desni > 0) { echo " colspan=\"$desni\""; }
echo ">";

echo "<select name=\"$ime\">\n";

for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($rezultat);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($rezultat, $j) == $vraca1) {
    echo "<option ";
    if ($vrednost == $arr[$vraca1]) { echo "selected "; }
    echo "value = \"". $arr[$vraca1] . "\" ";

    echo ">\n";

//    $sadrzaj_polja = $arr[$vraca1];
    }
    else {
        $sadrzaj_polja = $arr[$vraca1] . ' - ' . $arr[$vraca2];
        echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
}




function drop_kombo0($opis, $sqldk1,$ime, $conn, $tabela, $vraca1, $vraca2, $vrednost)
{
$rezultat = pg_query ($conn, $sqldk1);
if (!$rezultat) {
    echo "<br><br>Do¹lo je do gre¹ke prilikom konektovanja.\n";
    exit;
}

$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);
/*
echo "<td align=\"right\">";
echo $opis . "";
echo "</td>\n<td";
*/
if ($desni > 0) { echo " colspan=\"$desni\""; }
//echo ">";

echo "<select name=\"$ime\">\n";

for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($rezultat);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($rezultat, $j) == $vraca1) {
    echo "<option ";
    if ($vrednost == $arr[$vraca2]) { echo "selected "; }
    echo "value = \"". $arr[$vraca2] . "\"";

    echo ">\n";

//    $sadrzaj_polja = $arr[$vraca1];
    }
    else {
        $sadrzaj_polja = $arr[$vraca1];
        echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
}
function drop_kombo_nema_ga($opis, $sqldk1,$ime, $conn, $tabela, $vraca1, $vraca2, $vrednost)
{
$rezultat = pg_query ($conn, $sqldk1);
if (!$rezultat) {
    echo "<br><br>Do¹lo je do gre¹ke prilikom konektovanja.\n";
    exit;
}

$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);
/*
echo "<td align=\"right\">";
echo $opis . "";
echo "</td>\n<td";
*/
if ($desni > 0) { echo " colspan=\"$desni\""; }
//echo ">";

echo "<select name=\"$ime\">\n";
for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($rezultat);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($rezultat, $j) == $vraca1) {
    echo "<option ";
    if ($vrednost == $arr[$vraca2]) { echo "selected "; }
    echo "value = \"". $arr[$vraca2] . "\" ";

    echo ">\n";

//    $sadrzaj_polja = $arr[$vraca1];
    }
    else {
        $sadrzaj_polja = $arr[$vraca1];
        echo $sadrzaj_polja . "</option>\n";
    }
}
}
echo "<option selected></option>";
echo "</select>";

echo "</td>\n";
}

function je_datum($vrednost) {
$niz = explode("-", $vrednost);
// echo $niz[0] . "-" . $niz[1] . "-" . $niz[2] . "<br>\n";
$dan = date ("Y-m-d", mktime (0,0,0,$niz[1],$niz[2],$niz[0]));
// echo "<br>" . $vrednost . " i " . $dan . "<br>\n";
if ($vrednost == $dan) {
    return true;
    }
    else {
    return false;
    }
}

function je_vreme($vrednost)
{
$niz = split(":", $vrednost);
if (strlen($vrednost) == 5 && count($niz) == 2 && $niz[0] < 24 && $niz[1] < 60) {
    return true;
    }
    else {
    return false;
    }
}


function razlika($datum1,$datum2) {

$niz1 =split("-", $datum1);
$niz2 = split("-", $datum2);
$g=$niz2[0]-$niz1[0];
$m=$niz2[1]-$niz1[1];
$d=$niz2[2]-$niz1[2];


switch ($niz1[1]){
       case 1:
       case 3:
       case 5:
       case 7:
       case 8:
       case 10:
       case 12: $dana=31;
       break;
       case 4:
       case 6:
       case 9:
       case 11: $dana=30;
       break;
       case 2: if (fmod($niz1[0],4)==0.00){$dana=29;}
               else {$dana=28;}
       break;

}


if ($g>=0 and $m>0 and $d<0){ $m=$m-1; $d=$d+$dana;}
if ($g>0 and $m<0 and $d>=0){ $g=$g-1; $m=$m+12;}
if ($g>0 and $m<=0 and $d<0){ $g=$g-1; $m=$m+11; $d=$d+$dana;}
//if ($g>0 and $m>0 and $d>0){$d++;}

$niz[0]=$g;
$niz[1]=$m;
$niz[2]=$d;
$niz[3]=$dana;
/*echo $g;
echo $m;
echo $d;*/
return $niz;

}


function ispis1($sql, $conn)
{
$result = pg_query($conn, $sql);
$polja = pg_num_fields($result);
$redova = pg_num_rows($result);
//echo "<tr><td>&nbsp;</td>\n";
$i=1;
while ($i<=$redova){
echo "<tr>";
$arr = pg_fetch_assoc ($result);

	for ($j=0; $j < $polja; $j++) {

	$sadrzaj_polja = $arr[pg_field_name($result, $j)];

	if ($sadrzaj_polja == "") {
		$sadrzaj_polja = "&nbsp;";
		}

	echo "<td>";

	/*if ($j > 1) {
	    $sadrzaj_polja = number_format($sadrzaj_polja, 0, ',', '.');
	    echo " align=\"right\">";
	    }
	else { echo ">"; }*/
	//echo "<font color=\"#CC0000\"><b>\n";
	echo $sadrzaj_polja . "</b></td>\n";
}
$i++;
echo "</tr>";
}
}



function ispis2($sql, $conn,$ime3){
$result = pg_query($conn, $sql);
$polja = pg_num_fields($result);
$redova=pg_num_rows($result);
echo "<select name=\"$ime3\">\n";
for($i=0; $i<$redova; $i++){
$arr = pg_fetch_assoc ($result);
for ($j=0; $j < $polja; $j++) {

	$sadrzaj_polja = $arr[pg_field_name($result, $j)];
	echo "<option>" . $sadrzaj_polja . "</option>";
	}
	}
echo "</select>";

}

function drop_kombo2($opis, $ime, $conn, $tabela, $vraca1, $vraca2, $vrednost)
{
$rezultat = pg_query ($conn, "SELECT $vraca1, $vraca2 FROM $tabela ORDER BY $vraca1");
if (!$rezultat) {
    echo "<br><br>Do¹lo je do gre¹ke prilikom konektovanja.\n";
    exit;
}

$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);

echo "<td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td";
if ($desni > 0) { echo " colspan=\"$desni\""; }
echo ">";

echo "<select name=\"$ime\">\n";

for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($rezultat);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($rezultat, $j) == $vraca1) {
    echo "<option ";
    if ($vrednost == $arr[$vraca1]) { echo "selected "; }
    echo "value = \"". $arr[$vraca1] . "\" ";

    echo ">\n";

//    $sadrzaj_polja = $arr[$vraca1];
    }
    else {
        $sadrzaj_polja =$arr[$vraca2];
        echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
}

function drop_kombo3($opis, $ime, $conn, $tabela, $vraca1, $vraca2,$vraca3, $vrednost)
{
$rezultat = pg_query ($conn, "SELECT $vraca2, $vraca3 FROM $tabela where sifrau=$vraca1 ");
if (!$rezultat) {
    echo "<br><br>Do¹lo je do gre¹ke prilikom konektovanja.\n";
    exit;
}

$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);

echo "<td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td";
if ($desni > 0) { echo " colspan=\"$desni\""; }
echo ">";

echo "<select name=\"$ime\">\n";

for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($rezultat);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($rezultat, $j) == $vraca2) {
    echo "<option ";
    if ($vrednost == $arr[$vraca2]) { echo "selected "; }
    echo "value = \"". $arr[$vraca2] . "\" ";

    echo ">\n";

//    $sadrzaj_polja = $arr[$vraca1];
    }
    else {
        $sadrzaj_polja =$arr[$vraca3];
        echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
}
//Branka 16.04.2015. - Funkcija koja vraca tabelu sa rezervacijama
if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'vrati_predmete_odstetnih_zahteva')
{

	vrati_predmete_odstetnih_zahteva();
}
function vrati_predmete_odstetnih_zahteva($razbijanje,$broj_polise,$datum_stetnog_dogadjaja,$vreme_stetnog_dogadjaja,$idsp,$datum_upisa)
{
	$ret = new stdClass();
	if($razbijanje!='razbijanje')
	{
		$broj_polise=$_GET['broj_polise'];
		$vrsta_polise=$_GET['vrsta_polise'];
		$datum_stetnog_dogadjaja=$_GET['datum_stetnog_dogadjaja'];
		$idsp=$_GET['idsp'];
		$datum_upisa=$_GET['datum_upisa'];

		$vreme_stetnog_dogadjaja=$_GET['vreme_s_d'];
		$pib_jmbg_ostecenog=$_GET['pib_jmbg_ostecenog'];
		$broj_predmeta=$_GET['broj_predmeta'];
	}

	if($datum_stetnog_dogadjaja)
	{
		$datum_stetnog_dogadjaja=date('Y-m-d', strtotime($datum_stetnog_dogadjaja));
	}
	if($datum_upisa)
	{
		$datum_upisa=date('Y-m-d', strtotime($datum_upisa));
	}
	//konekcija
	$conn_stete = pg_connect ("dbname=stete user=zoranp");
	if (!$conn_stete) {
		echo "Gre¹ka otvaranja konekcije prema SQL serveru.";
		exit;
	}
	//uslov za broj polise
	$uslov1=($broj_polise)?"broj_polise=$broj_polise":"";
	//uslov za jmbg/pib ostecenog
	if($pib_jmbg_ostecenog)
	{
		$uslov2=($broj_polise)?"AND jmbgpibost='$pib_jmbg_ostecenog'":"jmbgpibost='$pib_jmbg_ostecenog'";		
	}
	else
	{
		$uslov2="";
	}
	//uslov za vrstu polise
	if($vrsta_polise)
	{
		$uslov3=($broj_polise || $pib_jmbg_ostecenog)?"AND vrsta_osiguranja='$vrsta_polise'":"vrsta_osiguranja='$vrsta_polise'";
	}
	else 
	{
		$uslov3="";
	}
	//uslov za datum stetnog dogadjaja
	
	if($datum_stetnog_dogadjaja)
	{
		$uslov4=($broj_polise || $pib_jmbg_ostecenog || $vrsta_polise)?" AND datum_nastanka='$datum_stetnog_dogadjaja'":" datum_nastanka='$datum_stetnog_dogadjaja'";
	}
	else
	{
		$uslov4="";
	}
	
	//uslov za vreme stetnog dogadjaja
	
	if($vreme_stetnog_dogadjaja)
	{
		$uslov5=($broj_polise || $pib_jmbg_ostecenog || $vrsta_polise || $datum_stetnog_dogadjaja)?"AND vreme_nastanka='$vreme_stetnog_dogadjaja'":"vreme_nastanka='$vreme_stetnog_dogadjaja'";
	}
	else
	{
		$uslov5="";
	}
	//uslov za broj predmeta
	if($broj_predmeta)
	{
		$uslov6 = ($broj_polise || $pib_jmbg_ostecenog || $vrsta_polise || $datum_stetnog_dogadjaja || $vreme_stetnog_dogadjaja)?"AND (get_broj_predmeta(1,ido) = '$broj_predmeta' OR get_broj_predmeta(2,ido) = '$broj_predmeta')":"(get_broj_predmeta(1,ido) = '$broj_predmeta' OR get_broj_predmeta(2,ido) = '$broj_predmeta')";
	}
	else
	{
		$uslov6="";
	}
	$flag=true;
	if($broj_polise && $vrsta_polise=="")
	{
		$poruka="Izaberite vrstu obrasca!";
		$flag=false;
		
	}
	//$uslov7=" AND sudski_postupak_id is null";
	$uslov7="AND  ((SELECT sudski_postupak_id FROM predmet_odstetnog_zahteva WHERE osnovni_predmet_id=ido OR id=ido   ORDER BY id DESC LIMIT 1) IS NULL OR (SELECT sudski_postupak_id FROM predmet_odstetnog_zahteva WHERE osnovni_predmet_id=ido OR id=ido   ORDER BY id DESC LIMIT 1) NOT IN ($idsp)) ";
	$uslov8=" AND datum_otvaranja_predmeta<= '$datum_upisa'";
	
	$sql_predmeti="SELECT ido,tip,jmbgpibost,vrsta_osiguranja,stetni_dogadjaj_id,broj_polise,datum_nastanka,vreme_nastanka, count(*) -1 as broj, get_broj_predmeta(0, ido) AS osnovni,(SELECT id FROM predmet_odstetnog_zahteva WHERE osnovni_predmet_id=ido OR id=ido   ORDER BY id DESC LIMIT 1) as id_poslednjeg,(SELECT concat(imenazivost,'  ', prezimeost) AS ime_prezime_poslednjeg FROM predmet_odstetnog_zahteva WHERE coalesce(osnovni_predmet_id, id)=ido   ORDER BY id DESC LIMIT 1) as ime_poslednjeg,
	textcat_all(CASE WHEN id = ido THEN NULL ELSE predmeti END) FROM (SELECT
	coalesce(osnovni_predmet_id, a.id) as ido, tip_predmeta as tip ,b.stetni_dogadjaj_id as stetni_dogadjaj_id ,jmbgpibost,prezimeost,datum_otvaranja_predmeta, imenazivost,c.broj_polise as broj_polise,c.datum_nastanka as datum_nastanka,c.vreme_nastanka as vreme_nastanka, a.id,c.vrsta_obrasca as vrsta_osiguranja, get_broj_predmeta (0,
	a.id) predmeti FROM predmet_odstetnog_zahteva as a JOIN odstetni_zahtev
	as b ON (odstetni_zahtev_id = b.id) JOIN (SELECT * FROM stetni_dogadjaj) AS c ON (stetni_dogadjaj_id = c.id) ORDER BY osnovni_predmet_id desc) as
	foo WHERE  $uslov1 $uslov2 $uslov3 $uslov4  $uslov6 $uslov7 $uslov8   GROUP BY ido,stetni_dogadjaj_id,tip,broj_polise,datum_nastanka,vreme_nastanka,jmbgpibost,vrsta_osiguranja ORDER BY broj desc;";

	$upit_predmeti = pg_query($conn_stete,$sql_predmeti);
	$niz_predmeti= pg_fetch_all($upit_predmeti);

	$tabela_predmeti='<table border="1" style="background-color:#DDEEEE" width="100%" id="predmeti" name="predmeti" >';
	
	/*Uèitaj klasu za funkcije */
	require "../../common/funkcije_class.php";
	$funkcije_class = new funkcije_class();
	$tabela_predmeti.='<tr><th></th><th>Broj predmeta</th><th>Reaktivacije</th><th>Tip predmeta</th><th>Broj polise</th><th>Ime i prezime o¹teæenog</th><th>JMBG/PIB o¹teæenog</th><th>Datum ¹tetnog dogaðaja</th><th>Vreme ¹tetnog dogaðaja</th></tr>';
	if($niz_predmeti)
	{
		for($i=0; $i<count($niz_predmeti);$i++)
		{
			$predmet_id=$niz_predmeti[$i]['ido'];
			//$predmet_id=$niz_predmeti[$i]['id_poslednjeg'];
			$predmet_id_poslednji=$niz_predmeti[$i]['id_poslednjeg'];
			$predmet_id_poslednji=($predmet_id_poslednji)?$predmet_id_poslednji:$predmet_id;
			

			//$broj_predmeta= $funkcije_class->vrati_broj_predmeta_za_dokumente($predmet_id);
			$broj_predmeta=$niz_predmeti[$i]['osnovni'];
			//$ime_ostecenog=$niz_predmeti[$i]['ime_poslednjeg'].' &nbsp;'.$niz_predmeti[$i]['prezime_poslednjeg'];
			$ime_ostecenog=$niz_predmeti[$i]['ime_poslednjeg'];
			$jmbgpibost=$niz_predmeti[$i]['jmbgpibost'];
			$broj_polise=$niz_predmeti[$i]['broj_polise'];
			$datum_stetnog_dogadjaja=$niz_predmeti[$i]['datum_nastanka'];
			$vreme_stetnog_dogadjaja=$niz_predmeti[$i]['vreme_nastanka'];
			$osnovni_predmet_id=$niz_predmeti[$i]['ido'];
			$stetni_dogadjaj_id=$niz_predmeti[$i]['stetni_dogadjaj_id'];
			//$osnovni_predmet_id=($osnovni_predmet_id)?$osnovni_predmet_id:$predmet_id;
			$broj_predmeta_parametar="'".$broj_predmeta."'";
			$tip_predmeta=$niz_predmeti[$i]['tip'];
			$status=($niz_predmeti[$i]['nalog'] && $niz_predmeti[$i]['nalog']!=null && $niz_predmeti[$i]['nalog']!="")?"RE©EN":"RESERVISAN";
			$reaktivacije=$niz_predmeti[$i]['textcat_all'];
			//Da li je predmet resen
			
			$sql_resen="SELECT get_broj_predmeta(2, id) AS broj,id  from predmet_odstetnog_zahteva WHERE (osnovni_predmet_id=$osnovni_predmet_id OR id=$osnovni_predmet_id) AND nalog IS NULL ";
			$upit_resen = pg_query($conn_stete,$sql_resen);
			$niz_resen = pg_fetch_assoc($upit_resen);
			$broj_rezervisanog=$niz_resen['broj'];
			$id_neresenog_predmeta=$niz_resen['id'];
			//Broj poslednjeg predmeta
			$broj_poslednjeg_predmeta = $funkcije_class->vrati_broj_predmeta_za_dokumente($id_neresenog_predmeta);
			$broj_poslednjeg_predmeta="'".$broj_poslednjeg_predmeta."'";

			$resen=($niz_resen['broj'])?'Predmet '.$broj_rezervisanog.' nije re¹en pa prelazi u sudski':'resen';
			$resen="'".$resen."'";
			
			//Branka 16.09.2015. Da ki vec postoji sudski
			$sql_da_li_postoji_sudski="SELECT get_broj_predmeta(2, id) AS broj, brsp from predmet_odstetnog_zahteva AS p INNER JOIN sudski_postupak s ON (s.idsp=p.sudski_postupak_id) WHERE (osnovni_predmet_id=$osnovni_predmet_id OR id=$osnovni_predmet_id) AND sudski_postupak_id IS NOT NULL";
			$upit__da_li_postoji_sudski = pg_query($conn_stete,$sql_da_li_postoji_sudski);
			$niz_da_li_postoji_sudski = pg_fetch_assoc($upit__da_li_postoji_sudski);
			$broj=$niz_da_li_postoji_sudski['broj'];
			$brsp=$niz_da_li_postoji_sudski['brsp'];
			
			$postoji=($broj)?$broj." je veæ povezan za sudski postupak ".$brsp:'ne postoji';
			$postoji="'".$postoji."'";
			
			//$tabela_predmeti.='<tr id="red'.$predmet_id.'"><td align="center"><input type="checkbox"  value='.$predmet_id.' id="izdvojeni_predmet'.$predmet_id.'" name="izdvojeni_predmet"><input type="checkbox" hidden width="5%" id='.$predmet_id.' onchange = "provera_stetnog_dogadjaja('.$osnovni_predmet_id.','.$predmet_id_poslednji.','.$broj_predmeta_parametar.','.$stetni_dogadjaj_id.','.$resen.','.$postoji.','.$broj_poslednjeg_predmeta.')" ><input type="hidden" value='.$predmet_id.' name="id_predmet" id="id_predmet"></td><td align="center"><a target="_blank" href="../../stete/pregled.php?idstete='.$predmet_id.'&dugme=DA"> &nbsp;'.$broj_predmeta.'</a></td><td align="center"> &nbsp;'.$reaktivacije.'</td><td align="center"> &nbsp;'.$tip_predmeta.'</td><td align="center"> &nbsp;'.$broj_polise.'</td><td align="center">&nbsp;'.$ime_ostecenog.'</td><td align="center">&nbsp;'.$jmbgpibost.'</td><td align="center">&nbsp;'.$datum_stetnog_dogadjaja.'</td><td align="center">&nbsp;'.$vreme_stetnog_dogadjaja.'</td></tr>';
			
			
			
			
			if($razbijanje=='razbijanje')
			{
				$tabela_predmeti.='<tr id="red'.$predmet_id.'"><td align="center"><input type="checkbox"  value='.$predmet_id.' id="izdvojeni_predmet'.$predmet_id.'" name="izdvojeni_predmet"><input type="checkbox" hidden width="5%" id='.$predmet_id.' onchange = "provera_jmbga_ostecenog('.$osnovni_predmet_id.','.$predmet_id_poslednji.','.$broj_predmeta_parametar.','.$stetni_dogadjaj_id.','.$postoji.','.$broj_poslednjeg_predmeta.')" ><input type="hidden" value='.$predmet_id.' name="id_predmet" id="id_predmet"></td><td align="center"><a target="_blank" href="../../stete/pregled.php?idstete='.$predmet_id.'&dugme=DA"> &nbsp;'.$broj_predmeta.'</a></td><td align="center"> &nbsp;'.$reaktivacije.'</td><td align="center"> &nbsp;'.$tip_predmeta.'</td><td align="center"> &nbsp;'.$broj_polise.'</td><td align="center">&nbsp;'.$ime_ostecenog.'</td><td align="center">&nbsp;'.$jmbgpibost.'</td><td align="center">&nbsp;'.$datum_stetnog_dogadjaja.'</td><td align="center">&nbsp;'.$vreme_stetnog_dogadjaja.'</td></tr>';
			}
			else 
			{
				$tabela_predmeti.='<tr id="red'.$predmet_id.'"><td align="center"><input type="checkbox"  value='.$predmet_id.' id="izdvojeni_predmet'.$predmet_id.'" name="izdvojeni_predmet"><input type="checkbox" hidden width="5%" id='.$predmet_id.' onchange = "provera_stetnog_dogadjaja('.$osnovni_predmet_id.','.$predmet_id_poslednji.','.$broj_predmeta_parametar.','.$stetni_dogadjaj_id.','.$postoji.','.$broj_poslednjeg_predmeta.')" ><input type="hidden" value='.$predmet_id.' name="id_predmet" id="id_predmet"></td><td align="center"><a target="_blank" href="../../stete/pregled.php?idstete='.$predmet_id.'&dugme=DA"> &nbsp;'.$broj_predmeta.'</a></td><td align="center"> &nbsp;'.$reaktivacije.'</td><td align="center"> &nbsp;'.$tip_predmeta.'</td><td align="center"> &nbsp;'.$broj_polise.'</td><td align="center">&nbsp;'.$ime_ostecenog.'</td><td align="center">&nbsp;'.$jmbgpibost.'</td><td align="center">&nbsp;'.$datum_stetnog_dogadjaja.'</td><td align="center">&nbsp;'.$vreme_stetnog_dogadjaja.'</td></tr>';
			}
			
		}
		$tabela_predmeti.='</table><table width="100%"><tr><td colspan="8"><input type="button" height="10px" value="Izaberite predmete za povezivanje" id="povezi_predmete" onclick="izaberi_predmete()"></td></tr><tr><td colspan="10" align="right"></td></tr></table>';
	}
	else 
	{
		$tabela_predmeti.='<tr><td></td><td colspan="8" align="center"><font color="red" style="font-size:14pt">Nema pronaðenih predmeta!!!</font></td></tr>';
	}
	//$tabela_predmeti.='</table><table width="60%"><tr><td colspan="8"><input type="button" value="Otvori sudske predmete" onclick="otvori_sudske_predmete('.$idsp.')"></td><td></td><td  align="right"><label>Nije nijedan od ponuðenih predmeta</label><input type="checkbox" id="otvori_novi" name="otvori_novi" onclick="prikazi_dugme_za_novi()"></td></tr><tr><td colspan="10" align="right"><input type="button" hidden id="otvori_novi_dugme" name="otvori_novi_dugme" value="Otvorite novi" onclick="kreiraj_novi_odstetni_zahtev('.$idsp.')"></td></tr></table>';
	

	$tabela_predmeti.='</table><table width="100%"><tr><td colspan="8" align="left">';
	//Zakomentarisano jer je odluceno da se direktna tuzba otvara prvo kao mirni, pa se zatvara istog dana, pa se radi aktivacija/reaktivacija
// 	if($razbijanje=='razbijanje')
// 	{
	$tabela_predmeti.='<input type="button"  id="otvori_novi_dugme" name="otvori_novi_dugme" value="Otvorite novi predmet - Direktna tu¾ba" onclick="kreiraj_novi_odstetni_zahtev('.$idsp.')">';	
// 	}
	$tabela_predmeti.='</td></tr><tr><td colspan="10" align="right"><input type="button" id="zavrseno_povezivanje" value="Zavr¹eno povezivanje" onclick="izmeni_sudski('.$idsp.')" ></td></tr></table>';
	if($razbijanje!='razbijanje')
	{
	    $ret->tabela_predmeti= mb_convert_encoding($sql_predmeti/*$tabela_predmeti*/, 'utf-8','iso-8859-2');
	$ret->upit= mb_convert_encoding($sql_predmeti, 'utf-8','iso-8859-2');
	$ret->flag= mb_convert_encoding($flag, 'utf-8','iso-8859-2');
	$ret->poruka= mb_convert_encoding($poruka, 'utf-8','iso-8859-2');
	echo json_encode($ret);
	exit;
	}
	else
	{
		echo $tabela_predmeti;
	}
	
}
if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'vrati_tipove_suda')
{
	$kategorija = $_GET['kategorija'];
	vrati_tipove_suda($kategorija);
}

function vrati_tipove_suda($kategorija,$sa_forme)
{
	

	
	$ret = new stdClass();
	$conn_amso = pg_connect("dbname=amso user=zoranp");

	$sql_tip_suda = "SELECT
	DISTINCT tip
	FROM
	sifarnici.sudovi WHERE nadleznost = '$kategorija' AND aktivan='novi'";

	$upit_tip_suda = pg_query($conn_amso,$sql_tip_suda);
	$niz_tip_suda = pg_fetch_all($upit_tip_suda);

	$opcije = "<option value='-1' >Izaberite</option>";
	// Proði kroz niz i kreiraj opcije za selekt
	for ($i = 0; $i < count($niz_tip_suda); $i++)
	{
		// Pokupip vrednosti iz niza
		$ispis = $niz_tip_suda[$i]['tip'];
		$select_tip=($sa_forme && $sa_forme==$ispis)?"selected":"";
		// Postavi opciju za selekt
		$opcije .= "<option value='$ispis' $select_tip >";
		$opcije .= $ispis;
		$opcije .= "</option>";
	}

	
if($sa_forme)
{
	echo $opcije;
}
else 
{
	$ret->opcije = mb_convert_encoding($opcije, 'utf-8', 'iso-8859-2');
	echo json_encode($ret);exit;
}
}

if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'vrati_sudove')
{
	$tip_sud = $_GET['tip_sud'];
	vrati_sudove($tip_sud);
}

function vrati_sudove($tip_sud,$sa_forme)
{
	$ret = new stdClass();
	$conn_amso = pg_connect("dbname=amso user=zoranp");

	$sql_sudovi = "SELECT
	sj.id as id, sj.naziv as naziv, sj.sudska_jedinica as sudska_jedinica, s.naziv as sud_naziv
	FROM
	sifarnici.sudovi AS s
	INNER JOIN sifarnici.sudske_jedinice AS sj ON (s.id=sj.sud_id)
	WHERE s.tip = '$tip_sud' AND s.aktivan='novi' ORDER BY sj.sud_id,sj.id";

// 	$sql_sudovi = "SELECT
// 	id, naziv
// 	FROM
// 	sifarnici.sudovi 
// 	WHERE tip = '$tip_sud' AND aktivan='novi'";

	$upit_sudovi = pg_query($conn_amso,$sql_sudovi);
	$niz_sudovi = pg_fetch_all($upit_sudovi);

	$opcije = "<option value='-1' >Izaberite</option>";
	// Proði kroz niz i kreiraj opcije za selekt
	for ($i = 0; $i < count($niz_sudovi); $i++)
	{
		// Pokupip vrednosti iz niza
		$sudska_jedinica=$niz_sudovi[$i]['sudska_jedinica'];
		if($sudska_jedinica=='t')
		{
			$ispis =$niz_sudovi[$i]['sud_naziv']." - ".$niz_sudovi[$i]['naziv'];
		}
		else 
		{
			$ispis = $niz_sudovi[$i]['naziv'];
		}
		$id_sud= $niz_sudovi[$i]['id'];
		$select_sud=($sa_forme && $sa_forme==$id_sud)?"selected":"";
		// Postavi opciju za selekt
		$opcije .= "<option value='$id_sud' $select_sud>";
		$opcije .= $ispis;
		$opcije .= "</option>";
	}


	if($sa_forme)
	{
		echo $opcije;
	}
	else
	{
	$ret->opcije = mb_convert_encoding($opcije, 'utf-8', 'iso-8859-2');
	
	echo json_encode($ret);exit;
}
}

if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'vrati_sudije')
{
	$id_sud = $_GET['id_sud'];
	vrati_sudije($id_sud);
}

function vrati_sudije($id_sud,$sa_forme)
{
	$ret = new stdClass();
	$conn_amso = pg_connect("dbname=amso user=zoranp");

	$sql_sudije = "SELECT
	id,ime_prezime as naziv
	FROM
	sifarnici.sudije
	WHERE sud_id =$id_sud ";

	$upit_sudije = pg_query($conn_amso,$sql_sudije);
	$niz_sudije = pg_fetch_all($upit_sudije);

	$opcije = "<option value='-1' >Izaberite</option>";
	// Proði kroz niz i kreiraj opcije za selekt
	for ($i = 0; $i < count($niz_sudije); $i++)
	{
		// Pokupip vrednosti iz niza
		$ispis = $niz_sudije[$i]['naziv'];
		$id_sudija= $niz_sudije[$i]['id'];
		$select_sudija=($sa_forme && $sa_forme==$id_sudija)?"selected":"";
		// Postavi opciju za selekt
		$opcije .= "<option value='$id_sudija' $select_sudija >";
		$opcije .= $ispis;
		$opcije .= "</option>";
	}


	if($sa_forme)
	{
		echo $opcije;
	}
	else 
	{
	$ret->opcije = mb_convert_encoding($opcije, 'utf-8', 'iso-8859-2');
	echo json_encode($ret);exit;
	}
}
if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'snimi_u_tabelu_sudski_mirni')
{
	snimi_u_tabelu_sudski_mirni();
}

function snimi_u_tabelu_sudski_mirni()
{
	$cekirani_predmeti=$_POST['cekirani_predmeti'];
	$idsp=$_POST['idsp'];
	$datum_upisa=$_POST['datum_upisa'];
	$datum=date("Y-m-d");
	$ret = new stdClass();
	$conn_stete = pg_connect("dbname=stete user=zoranp");
	require "../../common/funkcije_class.php";
	$funkcije_class = new funkcije_class();
	// BEGIN
	$sql = "BEGIN;";
	$rezultat = pg_query($conn_stete, $sql);
	$predmeti_vec_povezani="";
	$flag=true;
	//rezervacije za sudski
	if($datum > $datum_upisa)
	{
			$sql_rezervacije="SELECT (rez_mat_lica + rez_nemat_lica + rez_renta_lica + rez_mat_stvari) as zbir,datum_od from rez_sp_razbijeno_sa_periodom WHERE idsp=$idsp";
			$upit_rezervacije = pg_query($conn_stete,$sql_rezervacije);
			$rezervacije_niz = pg_fetch_all($upit_rezervacije);
			
			//isplate za sudski
			
// 			$sql_isplate_sudski="SELECT sum(iznos) as iznos FROM isplate WHERE idsp in ($idsp)";
// 			$upit_isplate_iznos = pg_query($conn_stete,$sql_isplate_sudski);
// 			$isplate_sudski_niz = pg_fetch_assoc($upit_isplate_iznos);
// 			$isplata_sudski=$isplate_sudski_niz['iznos'];
				
			
			
			$rezervacije_tekst="Zbir rezervacija èekiranih predmeta mora biti : ";
			$rezervacije_tekst_mirni="Zbir rezervacija èekiranih predmeta je : ";
			//$isplate_tekst_mirni="Zbir isplata èekiranih predmeta je : ";
			
			
			for ($j=0;$j<count($rezervacije_niz);$j++)
			{
				$dan=$rezervacije_niz[$j]['datum_od'];
				$zbir=$rezervacije_niz[$j]['zbir'];
				//za mirne
				
				//Rezervacije za mirne
				
				$cekirani_predmeti_idjevi="";
				$zbir_rez_na_dan=0;
				$isplate=0;
				for ($k=0; $k<count($cekirani_predmeti);$k++)
				{
					$sql_rezervacije_mirni="SELECT rezervisano from rezervacije WHERE idstete in ($cekirani_predmeti[$k]) AND datum_od<'$dan' ORDER BY idrez DESC LIMIT 1";
					$upit_rezervacije_mirni = pg_query($conn_stete,$sql_rezervacije_mirni);
					$rezervacije_niz_mirni = pg_fetch_assoc($upit_rezervacije_mirni);
					$zbir_rez_na_dan=$zbir_rez_na_dan + $rezervacije_niz_mirni['rezervisano'];

					
					//isplate za mirni
// 					$sql_isplate="SELECT sum(iznos) as iznos FROM isplate WHERE idstete in ($cekirani_predmeti[$k])    ";
// 					$upit_isplate_mirni = pg_query($conn_stete,$sql_isplate);
// 					$niz_isplate_mirni = pg_fetch_assoc($upit_isplate_mirni);
// 					$isplate=$isplate + $niz_isplate_mirni['iznos'];
						
				}
				$rezervacije_tekst_mirni.="na ".date('d.m.Y',strtotime($dan))." - ".$zbir_rez_na_dan. " ";
				$rezervacije_tekst.="na ".date('d.m.Y',strtotime($dan))." - ".$zbir. " ";
				//$isplate_tekst_mirni.=$isplate_tekst_mirni;
			}
			
}
	
	for ($i=0; $i<count($cekirani_predmeti);$i++)
	{
		$predmet_odstetnog_zahteva_id=$cekirani_predmeti[$i];
		$broj_predmeta= $funkcije_class->vrati_broj_predmeta_za_dokumente($predmet_odstetnog_zahteva_id);
		$sql_select="SELECT sudski_postupak_id FROM predmet_odstetnog_zahteva WHERE id=$predmet_odstetnog_zahteva_id";
		$upit_sudski_postupak_id = pg_query($conn_stete,$sql_select);
		$sudski_postupak_id_niz = pg_fetch_assoc($upit_sudski_postupak_id);
		$sudski_postupak_id=$sudski_postupak_id_niz['sudski_postupak_id'];
		

		if((!$sudski_postupak_id || $sudski_postupak_id==null) && $flag==true)
		{
			$sql_update="UPDATE predmet_odstetnog_zahteva SET sudski_postupak_id=$idsp WHERE id=$predmet_odstetnog_zahteva_id";	
			$rezultat_insert=pg_query($conn_stete,$sql_update);
			if($rezultat_insert)
			{
				$flag=true;
			}
			else 
			{
				$flag=false;
				$poruka="Gre¹ka u povezivanju predmeta";
			}
		}
		else
		{
			
			$predmeti_vec_povezani.=$broj_predmeta ."  ";
			$flag=false;
			$poruka="Izabrani predmeti   $predmeti_vec_povezani su veæ vezani za neku tu¾bu! Poku¹ajte ponovo!";
		}
		
		
	}
	if ($flag==true)
	{
		$poruka="Uspe¹no povezana tu¾ba sa mirnim predmetima";
		$sql = "COMMIT;";
		$rezultat = pg_query($conn_stete, $sql);
	
	}
	else
	{
		$sql = "ROLLBACK;";
		$rezultat = pg_query($conn_stete, $sql);
	}
	
	$ret->poruka = mb_convert_encoding($poruka, 'utf-8', 'iso-8859-2');
	$ret->rezervacije_tekst = mb_convert_encoding($rezervacije_tekst."  ".$rezervacije_tekst_mirni, 'utf-8', 'iso-8859-2');
	$ret->sql_rezervacije_mirni = mb_convert_encoding($sql_rezervacije_mirni, 'utf-8', 'iso-8859-2');
	$ret->flag = mb_convert_encoding($flag, 'utf-8', 'iso-8859-2');
	echo json_encode($ret);exit;
}

if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'promeni_status_predmeta')
{
	promeni_status_predmeta();
}
function promeni_status_predmeta()
{
	$osnovni_predmet_id=$_POST['osnovni_predmet_id'];
	$sudski_id=$_POST['idsp'];
	$ret = new stdClass();
	$conn_stete = pg_connect("dbname=stete user=zoranp");
	
	$sql_update_neresenog="UPDATE predmet_odstetnog_zahteva set sudski_postupak_id=$sudski_id WHERE (id=$osnovni_predmet_id or osnovni_predmet_id=$osnovni_predmet_id ) AND nalog is NULL RETURNING id;";
	$rezultat_update = pg_query($conn_stete, $sql_update_neresenog);
	$niz_update = pg_fetch_array($rezultat_update);
	$id_predmeta = $niz_update['id'];
					
	$ret->id_predmeta = mb_convert_encoding($id_predmeta, 'utf-8', 'iso-8859-2');
	echo json_encode($ret);exit;
	
}
if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'vrati_povezani_predmet')
{
	vrati_povezani_predmet();
}
function vrati_povezani_predmet()
{
	$cekirani_predmeti=$_POST['cekirani_predmeti'];
	$ret = new stdClass();
	$conn_stete = pg_connect("dbname=stete user=zoranp");

		$sql="SELECT coalesce(osnovni_predmet_id, id) as ido from predmet_odstetnog_zahteva where (id=$cekirani_predmeti OR osnovni_predmet_id=$cekirani_predmeti) and sudski_postupak_id IS NOT NULL ";
		$upit = pg_query($conn_stete,$sql);
		$id_unetog_niz= pg_fetch_assoc($upit);
		$id_unetog=$id_unetog_niz['ido'];
		
	$ret->id_unetog = mb_convert_encoding($id_unetog, 'utf-8', 'iso-8859-2');
	echo json_encode($ret);exit;
}
if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'provera_stetnog_dogadjaja')
{
	provera_stetnog_dogadjaja();
}
function provera_stetnog_dogadjaja()
{
$idsp=$_POST['idsp'];
$osnovni_predmet_id=$_POST['osnovni_predmet_id'];
$ret = new stdClass();
$conn_stete = pg_connect("dbname=stete user=zoranp");

$flag_x;

$sql_cekirani_predmet="SELECT id from stetni_dogadjaj WHERE id IN (select stetni_dogadjaj_id FROM odstetni_zahtev WHERE id IN (SELECT odstetni_zahtev_id FROM predmet_odstetnog_zahteva WHERE id=$osnovni_predmet_id))";
$upit_cekirani_predmet = pg_query($conn_stete,$sql_cekirani_predmet);
$id_stetnog_dogadjaja_cekiranog_niz= pg_fetch_assoc($upit_cekirani_predmet);
$id_stetnog_dogadjaja_cekiranog=$id_stetnog_dogadjaja_cekiranog_niz['id'];

$sql_stetnog_dogadjaja_sudski="SELECT id from stetni_dogadjaj WHERE id IN (select stetni_dogadjaj_id FROM odstetni_zahtev WHERE id IN (SELECT odstetni_zahtev_id FROM predmet_odstetnog_zahteva WHERE sudski_postupak_id=$idsp))";
$upit_stetnog_dogadjaja_sudski = pg_query($conn_stete,$sql_stetnog_dogadjaja_sudski);
$id_stetnog_dogadjaja_sudski_niz= pg_fetch_assoc($upit_stetnog_dogadjaja_sudski);
$id_stetnog_dogadjaja_sudski=$id_stetnog_dogadjaja_sudski_niz['id'];

if($id_stetnog_dogadjaja_sudski && $id_stetnog_dogadjaja_cekiranog!=$id_stetnog_dogadjaja_sudski)
{
	$poruka='Ne mo¾ete dodati predmet sa drugog ¹tetnog dogaðaja!';
	$flag_x=false;
}	
else 
{
$flag_x=true;	
}



$ret->flag_x = mb_convert_encoding($flag_x, 'utf-8', 'iso-8859-2');
$ret->poruka = mb_convert_encoding($poruka, 'utf-8', 'iso-8859-2');
echo json_encode($ret);exit;
}

// DODATO 29.09.2015
if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'predmet_resen_provera')
{
	predmet_resen_provera();
}
function predmet_resen_provera()
{
	$ret = new stdClass();
	$flag;
	$osnovni_predmet_id = $_POST['osnovni_predmet_id'];
	$conn_stete = pg_connect("dbname=stete user=zoranp");
	
	$sql_resen="SELECT id from predmet_odstetnog_zahteva WHERE (osnovni_predmet_id=$osnovni_predmet_id OR id=$osnovni_predmet_id) AND nalog IS NULL ";
	$upit_resen = pg_query($conn_stete,$sql_resen);
	$niz_resen = pg_fetch_assoc($upit_resen);
	
	$flag = ($niz_resen['id'])? false :true;
	
	$ret->flag =$flag;
	echo json_encode($ret);exit;
}
// Branka 08.10.2015. - Funkcija koja provera da li je jmbg o¹teæenog u izabaranom predmetu jmbg tu¾ioca u sudskom predmetu
if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'provera_jmbga_ostecenog')
{
	provera_jmbga_ostecenog();
}
function provera_jmbga_ostecenog()
{
	$ret = new stdClass();
	$flag;
	$osnovni_predmet_id = $_POST['osnovni_predmet_id'];
	$idsp = $_POST['idsp'];
	$conn_stete = pg_connect("dbname=stete user=zoranp");
	
	$sql_jmbg="SELECT jmbgpibost from predmet_odstetnog_zahteva WHERE (osnovni_predmet_id=$osnovni_predmet_id OR id=$osnovni_predmet_id)";
	$upit_jmbg = pg_query($conn_stete,$sql_jmbg);
	$niz_jmbg = pg_fetch_assoc($upit_jmbg);
	$jmbg_cekiranog=$niz_jmbg['jmbgpibost'];
	
	
	$sql_tuzilac="SELECT tipstms,tipstml,tipstn from tuzilac WHERE idsp=$idsp and jmbg='$jmbg_cekiranog'";
	$upit_tuzilac= pg_query($conn_stete,$sql_tuzilac);
	$niz_tuzilac= pg_fetch_all($upit_tuzilac);
	$tipovi=array();
	for($i=0;$i<count($niz_tuzilac);$i++)
	{
		$tipstms=$niz_tuzilac[$i]['tipstms'];
		$tipstml=$niz_tuzilac[$i]['tipstml'];
		$tipstn=$niz_tuzilac[$i]['tipstn'];
		
		if($tipstms=='t')
		{
			$tip="S";
			array_push($tipovi,"'".$tip."'");
		}
		if($tipstml=='t' || $tipstn=='t')
		{
			$tip="L";
			array_push($tipovi,"'".$tip."'");
		}
	}
	
	$sql_sudski="SELECT vr_osig from sudski_postupak WHERE idsp=$idsp";
	$upit_sudski= pg_query($conn_stete,$sql_sudski);
	$niz_sudski= pg_fetch_assoc($upit_sudski);
	$vr_osig=$niz_sudski['vr_osig'];
	$arr = implode ( ', ', $tipovi);
	if($vr_osig=='AO' && ($tipstms || $tipstml || $tipstn))
	{
		$deo_upita='AND tip_predmeta in ('.$arr.') ';
	}
	else 
	{
		$deo_upita='';
	}

	
	
	$sql_jmbg_tip="SELECT jmbgpibost from predmet_odstetnog_zahteva WHERE (osnovni_predmet_id=$osnovni_predmet_id OR id=$osnovni_predmet_id) AND jmbgpibost IN (SELECT jmbg FROM tuzilac WHERE idsp=$idsp) $deo_upita";
	$upit_jmbg_tip = pg_query($conn_stete,$sql_jmbg_tip);
	$niz_jmbg_pib = pg_fetch_assoc($upit_jmbg_tip);

	$flag = ($niz_jmbg_pib)? true : false;
	$ret->flag =$flag;
	$ret->upit =mb_convert_encoding($sql_jmbg_tip, 'utf-8', 'iso-8859-2');
	
	echo json_encode($ret);exit;
}

// MARIJA 2015-10-07
if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'prikazi_listu_predmeta')
{
	prikazi_listu_predmeta();
}
function prikazi_listu_predmeta()
{
	$datum_od = $_POST['datum_od'];
	$datum_od = date('Y-m-d', strtotime($datum_od));
	$datum_do = $_POST['datum_do'];
	$datum_do = date('Y-m-d', strtotime($datum_do));
	$status = $_POST['status'];
	$ispisi_sve_predmetet;
	$dodatni_filter = $_POST['dodatni_filter'];

	$ret = new stdClass();
	$conn_stete = pg_connect("dbname=stete user=zoranp");

	$id_sudskih = vrati_sudske_predmete_po_statusu($datum_od, $datum_do, $status);
	$arr = implode ( ',', $id_sudskih);
	$deo_upita;
	$redni_broj;
	
	
	
	if(($status == 1 || $status == 2 || $status == 3))
	{
		$deo_upita = 'AND idsp IN ('. $arr .')';
	}
	
	$html = <<<EOF
		<table  width="99%" rules="all" frame="box" style="table-layout:fixed; margin-left:0px;">
			<thead>
				<tr height="40px;">
					<td bgcolor="#668073" width="30px;" style="color:#F0F2F1; font-weight:bold;  text-align:center; border: 1px solid white;"  height="15px;">RB</td>
					<td bgcolor="#668073" style="width:100px; color:#F0F2F1; font-weight:bold;  text-align:center; border: 1px solid white;">Broj SP</td>
					<td bgcolor="#668073" style="width:300px; color:#F0F2F1; font-weight:bold;  text-align:center; border: 1px solid white;">Povezani predmeti u mrinom</td>
EOF;
	
	if($dodatni_filter == "po_datumu" || $dodatni_filter == "sve")
	{
		$html .= <<<EOF
					<td bgcolor="#668073" style="width:140px; color:#F0F2F1; font-weight:bold;  text-align:center; border: 1px solid white;">Datum kompletiranja</td>
					<td bgcolor="#668073" style="width:140px; color:#F0F2F1; font-weight:bold;  text-align:center; border: 1px solid white;">Datum re¹avanja ¹tete</td>
EOF;
	}
	if($dodatni_filter == "sve" || $dodatni_filter == "po_rezervaciji")
	{
		$html .= <<<EOF
					<td bgcolor="#F2F4F9" style="width:5px; outline: #F2F4F9 solid thin; border-radius:10%; "></td>
					<td bgcolor="#668073" style="width:310px; color:#F0F2F1;font-weight:bold;  text-align:center; border: 1px solid white;">Rezervacije po predmetu</td>
EOF;
	}
	
	if($dodatni_filter == "sve" || $dodatni_filter == "po_isplati")
	{
		$html .= <<<EOF
					<td bgcolor="#668073" style="color:#F0F2F1;font-weight:bold;  text-align:center; border: 1px solid white;">Isplate po datumu</td>
EOF;
	}
	
	
	$html .= <<<EOF
					<td bgcolor="#668073" style="width:75px; border: 1px solid white;"></td>
				</tr>
		</thead>
	</table>
			<div id='tabela_podataka'>
			<table width="100%" rules="all" frame="box" style="margin-left:0px;">
	<tbody >
EOF;

	$sql_lista_predmeta_nepovezani = "SELECT sp.idsp AS idsp,sp.vr_osig AS vr_osig, sp.brsp AS brsp, sp.datum_kompletiranja AS datum_kompletiranja, sp.datum_isplate AS datum_isplate, sp.datum_upisa AS datum_upisa, sp.brpolise AS brpolise, case when sp.sifra isnull then '&nbsp;' else sp.sifra end as sifra, case when sp.prezime_osig isnull then '&nbsp;' else sp.prezime_osig end as prezime_osig, case when sp.ime_osig isnull then '&nbsp;' else sp.ime_osig end as ime_osig
									FROM sudski_postupak AS sp
									WHERE sp.datum_upisa BETWEEN '$datum_od' AND '$datum_do' $deo_upita  order by idsp";
	$rezultat_lista_predmeta_nepovezani = pg_query($conn_stete, $sql_lista_predmeta_nepovezani);
	$niz_lista_predmeta_nepovezani = pg_fetch_all($rezultat_lista_predmeta_nepovezani);
	$ukupan_broj_sudskih = count($niz_lista_predmeta_nepovezani);
		
	$predmet_id;

	for ($i = 0; $i < count($niz_lista_predmeta_nepovezani); $i++)
	{
		$color_reda = ($i%2 == 0) ? '#E6FFF2': '#D0D8D4';
		$idsp = $niz_lista_predmeta_nepovezani[$i]['idsp'];
		$brSp = $niz_lista_predmeta_nepovezani[$i]['brsp'];
		$vrsta_osiguranja = $niz_lista_predmeta_nepovezani[$i]['vr_osig'];
		$datum_upisa = $niz_lista_predmeta_nepovezani[$i]['datum_upisa'];
		$brpolise = $niz_lista_predmeta_nepovezani[$i]['brpolise'];
		$datum_kompletiranja = $niz_lista_predmeta_nepovezani[$i]['datum_kompletiranja'];
		$datum_isplate = $niz_lista_predmeta_nepovezani[$i]['datum_isplate'];


		$datum_upisa = date('d.m.Y',strtotime($datum_upisa));
		$redni_broj = $i+1;
		/*podaci o tipu tuzioca*/
		$sql_tuzilac = "SELECT * FROM
						((SELECT * FROM tuzilac WHERE (tipstml=true OR tipstn=true) AND idsp IN ($idsp))
						UNION ALL
						(SELECT * FROM tuzilac WHERE tipstms=true AND idsp IN ($idsp))
						UNION ALL
						(SELECT * FROM tuzilac  WHERE (tipstml IS NULL AND tipstn IS NULL AND tipstms IS NULL) AND idsp IN ($idsp))) AS foo ORDER BY idtuzilac";
		$rezultat_tuzilac = pg_query($conn_stete, $sql_tuzilac);
		$redova_tuzilac = pg_num_rows($rezultat_tuzilac);
		$niz_tuzilac = pg_fetch_all($rezultat_tuzilac);
		$broj_predmeta_za_povezivanje_na_sudski = count($niz_tuzilac);
						
		$sql_broj_ostecenih = " SELECT COUNT(id) AS broj FROM predmet_odstetnog_zahteva WHERE sudski_postupak_id= $idsp";
		$rezultat_broj_ostecenih = pg_query($conn_stete, $sql_broj_ostecenih);
		$niz_broj_ostecenih = pg_fetch_assoc($rezultat_broj_ostecenih);
		$broj_mirnih_po_sudskom = $niz_broj_ostecenih['broj'];
		$visina_polja_datuma_mirni = 100/$broj_mirnih_po_sudskom;
		
		$html .= <<<EOF
				<tr bgcolor="$color_reda" style="border-bottom:2pt solid black;">
			<td width="30px;">$redni_broj</td>
EOF;
		if (!$brSp)
		{
			$html .= <<<EOF
			<td style="width:100px;">&nbsp;</td>
EOF;
		}
		else
		{
			
			$niz_sudskih=vrati_sudske_predmete_po_statusu($idsp,'nema',3);
			//var_dump ($niz_sudskih);
			if($idsp==$niz_sudskih)
			{
				$html .= <<<EOF
			<td style="width:100px;"><a href="sudski_ispravka_novo.php?idsp=$idsp">$brSp</a></td>
EOF;
			}
			else
			{
				$html .= <<<EOF
			<td style="width:100px;"><a href="sudski_ispravka.php?idsp=$idsp">$brSp</a></td>
EOF;
			}
		}
						
		$html .= <<<EOF
			<td style="width:300px;">
				<table align="left" rules="rows" height="100%"  style="margin-top:0px;">
					<tr style="height:10px;" >
						<td style="width:150px; font-size: 8pt; background:#F2FFFF;">tuzilac SP</td>
						<td style="width:150px; font-size: 8pt; background:#F2FFFF;">osteceni MP</td>
					</tr>
EOF;

					for($j=0; $j<$broj_predmeta_za_povezivanje_na_sudski; $j++)
					{
						$jmbg_tuzioca = $niz_tuzilac[$j]['jmbg'];
						$tipstms =  $niz_tuzilac[$j]['tipstms'];
						$tipstml =  $niz_tuzilac[$j]['tipstml'];
						$tipstn =  $niz_tuzilac[$j]['tipstn'];
										
						$podaci_o_mirnom = vrati_podatke_predmeta($broj_predmeta_za_povezivanje_na_sudski,$idsp, $jmbg_tuzioca, $tipstms, $tipstml, $tipstn, $vrsta_osiguranja);
						$tip_tuzioca = $podaci_o_mirnom[$j]['tip_tuzioca'];
						$predmet_id = $podaci_o_mirnom[$j]['predmet_id'];
						$jmbg_osteceni = $podaci_o_mirnom[$j]['jmbg_osteceni'];
						$tip_predmeta = $podaci_o_mirnom[$j]['tip'];
						$novi_broj_predmeta = $podaci_o_mirnom[$j]['novi_broj_predmeta'];
						
										
						if (!$jmbg_tuzioca)
						{
							$html .= <<<EOF
						<tr><td>&nbsp;</td></tr>
EOF;
						}
						else
						{
							$html .= <<<EOF
						<tr>
							<td><br>$jmbg_tuzioca, $tip_tuzioca</td>
EOF;
											
							if($broj_mirnih_po_sudskom == 0 && $j == 0)
							{
								$html .= <<<EOF
							<td rowspan="$broj_predmeta_za_povezivanje_na_sudski"><font style="color:red;"><b>nema povezanih predmeta</b></font></td>
EOF;
							}
							else if($broj_mirnih_po_sudskom >0)
							{
								if($predmet_id)
								{
									$html .= <<<EOF
							<td><a target="_blank" href="../../stete/pregled.php?idstete=$predmet_id&dugme=DA">$novi_broj_predmeta</a><br>$jmbg_osteceni, $tip_predmeta</td>
EOF;
								}
								else
								{
									$html .= <<<EOF
							<td><font style="color:red;"><b>nije povezan $predmet_id</b></font></td>
EOF;
								}
							}

							$html .= <<<EOF
						</tr>
EOF;
						}
					}

					$html .= <<<EOF
						</table>
					</td>
EOF;
						
			if($broj_mirnih_po_sudskom != $broj_predmeta_za_povezivanje_na_sudski)
			{
				$html .= <<<EOF
					<td colspan="5"><font style="color:red; align:center;"><b>NISTE POVEZALI SVE PREDMETE U MIRNOM ZA OVAJ SP</b></font></td>
EOF;
					}
			else
			{
					/*ovde bi trebao da bude pocetak if za filter datum */	
			if($dodatni_filter == "sve" || $dodatni_filter == "po_datumu")
			{
				
						
						$html .= <<<EOF
					<td style="height:100%; width:140px;">
						<table rules="all" height="100%" broder="1" style="margin-top:0px;">
							<tr style="height:10px;">
								<td style="width:70px; font-size: 8pt; background:#F2FFFF;">SP</td>
								<td style="width:70px; font-size: 8pt; background:#F2FFFF;">MP</td>
							</tr>
							<tr>
EOF;

						if(!$datum_kompletiranja && $datum_isplate)
						{
							$html .= <<<EOF
								<td rowspan="$broj_mirnih_po_sudskom" colspan="2" style="font-size:9pt;  font-weight: bold; color:red; text-align:center;">sudski nema kompletiranu dokumentaciju
EOF;
						}
						else if(!$datum_kompletiranja && !$datum_isplate)
						{
							$html .= <<<EOF
								<td rowspan="$broj_mirnih_po_sudskom" colspan="2" style="font-size:9pt;  font-weight: bold; color:green; text-align:center;">sudski nije re¹en kompletno
EOF;
						}
						else
						{

							for($j=0; $j<$broj_predmeta_za_povezivanje_na_sudski; $j++)
							{
								$jmbg_tuzioca = $niz_tuzilac[$j]['jmbg'];
								$tipstms =  $niz_tuzilac[$j]['tipstms'];
								$tipstml =  $niz_tuzilac[$j]['tipstml'];
								$tipstn =  $niz_tuzilac[$j]['tipstn'];
									
								$podaci_o_mirnom = vrati_podatke_predmeta($broj_predmeta_za_povezivanje_na_sudski,$idsp, $jmbg_tuzioca, $tipstms, $tipstml, $tipstn, $vrsta_osiguranja);
								$datum_kompletiranja_mirni = $podaci_o_mirnom[$j]['datum_kompletiranja_mirni'];
								
								$color_datum_kompletiranja = ($datum_kompletiranja == $datum_kompletiranja_mirni && $datum_kompletiranja_mirni) ? 'green':'red' ;
								
								
								// uslov po filteru datuma
							
								if($j == 0)
								{
									$datum_kompletiranja = date('d.m.Y',strtotime($datum_kompletiranja));
									$html .= <<<EOF
									<td rowspan="$broj_mirnih_po_sudskom" style="font-size:9pt; color:$color_datum_kompletiranja; font-weight: bold;">$datum_kompletiranja</td>
									<td>
										<table rules="rows" height="100%">
EOF;
								}

								if(!$datum_kompletiranja_mirni)
								{
									$html .= <<<EOF
										<tr><td style="color:red; height:$visina_polja_datuma_mirni%; text-align:center; font-weight: bold;">nije unet datum kompret</td></tr>
EOF;
								}
								else
								{
									$datum_kompletiranja_mirni = date('d.m.Y', strtotime($datum_kompletiranja_mirni));
									if($datum_kompletiranja == $datum_kompletiranja_mirni)
									{
										$html .= <<<EOF
										<tr><td style="font-size:9pt; color:green; font-weight: bold; height:$visina_polja_datuma_mirni%">$datum_kompletiranja_mirni</td></tr>
EOF;
									}
									else
									{
										$html .= <<<EOF
										<tr><td style="font-size:9pt; color:red; font-weight: bold; height:$visina_polja_datuma_mirni%">$datum_kompletiranja_mirni</td></tr>
EOF;
									}
								}
							}
							
								$html .= <<<EOF
									</table>
								</td>
EOF;
							}
				$html .= <<<EOF
							</tr>
						</table>
					</td>
EOF;
					
					
			$html .= <<<EOF
						
					<td style="height:100%; width:140px;">
						<table rules="all" height="100%" style="margin-top:0px;">
							<tr style="height:10px;">
								<td style="width:70px; font-size: 8pt; background:#F2FFFF;">SP</td>
								<td style="width:70px; font-size: 8pt; background:#F2FFFF;">MP</td>
							</tr>
							<tr>
EOF;
				if(!$datum_isplate)
				{
					$html .= <<<EOF
								<td rowspan="$broj_mirnih_po_sudskom" colspan="2" style='color:green; font-weight: bold; text-align:center;'>sudski nije kompletno re¹en</td>
EOF;
				}
				else
				{
					for($j=0; $j<$broj_predmeta_za_povezivanje_na_sudski; $j++)
					{
						$jmbg_tuzioca = $niz_tuzilac[$j]['jmbg'];
						$tipstms =  $niz_tuzilac[$j]['tipstms'];
						$tipstml =  $niz_tuzilac[$j]['tipstml'];
						$tipstn =  $niz_tuzilac[$j]['tipstn'];
						
						$podaci_o_mirnom = vrati_podatke_predmeta($broj_predmeta_za_povezivanje_na_sudski,$idsp, $jmbg_tuzioca, $tipstms, $tipstml, $tipstn, $vrsta_osiguranja);
						$nalog_mirni = $podaci_o_mirnom[$j]['nalog_mirni'];
							
						$color_datum_resavanje = ($datum_isplate == $nalog_mirni && $nalog_mirni) ? 'green' : 'red';
						
						if($j == 0)
						{
							$datum_isplate = date('d.m.Y',strtotime($datum_isplate));
							$html .= <<<EOF
								<td rowspan="$broj_mirnih_po_sudskom" style="font-size:9pt; color:$color_datum_resavanje; font-weight: bold;">$datum_isplate</td>
EOF;
							
							$html .= <<<EOF
								<td style="height:100%;">
									<table rules="all" style="height:100%;">
EOF;
						}

						if(!$nalog_mirni)
						{
							$html .=<<<EOF
										<tr><td style="color:red; height:$visina_polja_datuma_mirni%; text-align:center; font-weight: bold;">nije unet datum re¹avanja</td></tr>
EOF;
						}
						else
						{
							$nalog_mirni = date('d.m.Y', strtotime($nalog_mirni));
							if($datum_isplate == $nalog_mirni)
							{
								$html .= <<<EOF
										<tr><td style="font-size:9pt; color:green; font-weight: bold; height:$visina_polja_datuma_mirni%;">$nalog_mirni</td></tr>
EOF;
							}
							else
							{
								$html .= <<<EOF
										<tr><td style="font-size:9pt; color:red; font-weight: bold; height:$visina_polja_datuma_mirni%;">$nalog_mirni</font></td></tr>
EOF;
							}
						}
					}
					$html .= <<<EOF
							</table>
						</td>
EOF;
				}

				$html .= <<<EOF
					</tr>
				</table>
				</td>
EOF;
			
			
			
			/*DA SE NE ISPISUJE DRUGI DEO POCETAK*/ 
// 				if($dodatni_filter == "po_datumu")
// 				{
// 					$html .=<<<EOF
// 					<td></td><td></td>
// EOF;
// 				}
				/*DA SE NE ISPISUJE DRUGI DEO KRAJ*/
				
		/* kraj IF koji se odnosi na filter za datum i postavljanje uslova za else*/}
			
			if($dodatni_filter == "sve" || $dodatni_filter == "po_rezervaciji")
			{
			/* kolona koja se odnosi na rezervaciju */
			
						$html .= <<<EOF
				<td style="background-color:black; width:5px;"></td>

						
						
				<td  style="width:190px;">
					<table rules="all" height="100%"  width="100%" style="margin-top:0px;" >
						<tr style="height:10px;">
							<td style=" font-size: 8pt; background:#F2FFFF; width:50px;">Datum</td>
							<td style=" font-size: 8pt; background:#F2FFFF; width:70px;">SP</td>
							<td style=" font-size: 8pt; background:#F2FFFF; width:70px;">MP</td>
						</tr>
						<tr>
							<td>
								<table rules="rows" height="100%">
EOF;

						$sql_rezervacije_sudski = "SELECT (rez_mat_lica+rez_nemat_lica+rez_renta_lica+rez_mat_stvari) AS rezervisana_suma, datum_od AS datum_od FROM rez_sp_razbijeno_sa_periodom WHERE idsp =$idsp";
						$rezultat_rezervacije_sudski = pg_query($conn_stete, $sql_rezervacije_sudski);
						$niz_rezervacije_sudski = pg_fetch_all($rezultat_rezervacije_sudski);
						$broj_rezervacija_po_datumu_sudki = count($niz_rezervacije_sudski); 
						$visina_po_predmetu_u_mirnom = 100/$broj_rezervacija_po_datumu_sudki;

				for($j=0; $j<count($niz_rezervacije_sudski); $j++)
				{
					$datum_rezervacije_sudski = trim($niz_rezervacije_sudski[$j]['datum_od'], '');
					$datum_rezervacije_sudski = (!$datum_rezervacije_sudski || $datum_rezervacije_sudski == '') ? '' : date('d.m.Y', strtotime($datum_rezervacije_sudski));
					
						$html .=<<<EOF
									<tr height="$visina_po_predmetu_u_mirnom%"><td style="font-size:9pt; font-weight: bold; width:100%;">$datum_rezervacije_sudski<td></tr>
EOF;
					
				}

		$html .= <<<EOF
						</table>
					</td>
					<td>
						<table rules="rows" width="100%" height="100%">
EOF;

		for ($j=0; $j<count($niz_rezervacije_sudski); $j++)
		{
			$datum_rezervacije_sudski = $niz_rezervacije_sudski[$j]['datum_od'];
			$iznos_rezervacije_sudski = $niz_rezervacije_sudski[$j]['rezervisana_suma'];
			
			$sql_ukupna_rezervacija_u_mirnom ="SELECT SUM(r.rezervisano) AS ukupna_rezervacija FROM predmet_odstetnog_zahteva AS p
			LEFT JOIN rezervacije AS r
			ON p.id=r.idstete
			WHERE p.sudski_postupak_id=$idsp AND r.datum_od='$datum_rezervacije_sudski'";
			$rezultat_ukupna_rezervacija_u_mirnom = pg_query($conn_stete, $sql_ukupna_rezervacija_u_mirnom);
			$niz_ukupna_rezervacija_u_mirnom = pg_fetch_assoc($rezultat_ukupna_rezervacija_u_mirnom);
			$ukupna_rezervacija_u_mirnom= $niz_ukupna_rezervacija_u_mirnom['ukupna_rezervacija'];
			
			$color_iznos_sudska_rezervacija = ($ukupna_rezervacija_u_mirnom == $iznos_rezervacije_sudski) ? 'green': 'red';
			
			$html .=<<<EOF
							<tr><td style="font-size:9pt; color:$color_iznos_sudska_rezervacija; font-weight: bold; height:$visina_po_predmetu_u_mirnom%;">$iznos_rezervacije_sudski<td></tr>
EOF;
		}

		$html .= <<<EOF
				</table>
			</td>
				
			<td height="100%">
				<table rules="rows" height="100%">
EOF;

		for ($j=0; $j<count($niz_rezervacije_sudski); $j++)
		{
			$datum_rezervacije_sudski = $niz_rezervacije_sudski[$j]['datum_od'];
			$iznos_rezervacije_sudski = $niz_rezervacije_sudski[$j]['rezervisana_suma'];
		
			$sql_iznos_rezervacija_mirni = "SELECT p.novi_broj_predmeta AS novi_broj_predmeta,p.id AS predmet_id,r.rezervisano AS rezervisano,r.idstete FROM predmet_odstetnog_zahteva AS p
											LEFT JOIN rezervacije AS r
											ON p.id=r.idstete
											WHERE p.sudski_postupak_id=$idsp AND r.datum_od='$datum_rezervacije_sudski'";
			$rezultat_iznos_rezervacija_mirni = pg_query($conn_stete, $sql_iznos_rezervacija_mirni);
			$niz_iznos_rezervacija_mirni = pg_fetch_all($rezultat_iznos_rezervacija_mirni);
			//$broj_predmeta_u_mirnom_po_datumu_rezervacije = count($niz_iznos_rezervacija_mirni);
		
		
			$sql_ukupna_rezervacija_u_mirnom ="SELECT SUM(r.rezervisano) AS ukupna_rezervacija FROM predmet_odstetnog_zahteva AS p
											LEFT JOIN rezervacije AS r
											ON p.id=r.idstete
											WHERE p.sudski_postupak_id=$idsp AND r.datum_od='$datum_rezervacije_sudski'";
			$rezultat_ukupna_rezervacija_u_mirnom = pg_query($conn_stete, $sql_ukupna_rezervacija_u_mirnom);
			$niz_ukupna_rezervacija_u_mirnom = pg_fetch_assoc($rezultat_ukupna_rezervacija_u_mirnom);
			$ukupna_rezervacija_u_mirnom= $niz_ukupna_rezervacija_u_mirnom['ukupna_rezervacija'];
			
			
			$broj_rezervacija_u_mirnom = count($niz_iznos_rezervacija_mirni);
		
			$color_iznos_rezervacije = ($iznos_rezervacije_sudski == $ukupna_rezervacija_u_mirnom && $broj_rezervacija_u_mirnom==$broj_predmeta_za_povezivanje_na_sudski) ? 'green':'red';
		
			$html .=<<<EOF
				<tr height="$visina_po_predmetu_u_mirnom%"><td>
				<table>
EOF;

			for($k=0; $k<count($niz_iznos_rezervacija_mirni); $k++)
			{
				$iznos_rezervacije_u_mirnom = $niz_iznos_rezervacija_mirni[$k]['rezervisano'];
				$novi_broj_predmeta_u_mirnom = $niz_iznos_rezervacija_mirni[$k]['novi_broj_predmeta'];
				$predmet_id = $niz_iznos_rezervacija_mirni[$k]['predmet_id'];
		
				if($iznos_rezervacije_sudski == $ukupna_rezervacija_u_mirnom && $broj_rezervacija_u_mirnom==$broj_predmeta_za_povezivanje_na_sudski)
				{
					$html .=<<<EOF
					<tr><td><a target="_blank" href="../../stete/pregled.php?idstete=$predmet_id&dugme=DA">$novi_broj_predmeta_u_mirnom</a></td><td style="font-size:9pt; color:$color_iznos_rezervacije; font-weight: bold;">$iznos_rezervacije_u_mirnom</td>
					</tr>
EOF;
				}
				else
				{
					
					$html .=<<<EOF
						<tr><td><a target="_blank" href="../../stete/pregled.php?idstete=$predmet_id&dugme=DA">$novi_broj_predmeta_u_mirnom</a></td><td style="font-size:9pt; color:$color_iznos_rezervacije; font-weight: bold;">$iznos_rezervacije_u_mirnom</td></tr>
						<tr><td colspan="2" style="font-size:9pt; color:$color_iznos_rezervacije; font-weight: bold; text-align:center;">niste rasporedili iznos rezervacije na sve predmete</td></tr>
EOF;
					
				}
			}
			$html .=<<<EOF
				</table>
				</td></tr>
EOF;
		}
		
				$html .= <<<EOF
			</table>
			</td>
			</tr>
				</table>
				</td>
EOF;
				
				/* kraj IF-a uslov ukoliko se aktivira filter po_rezervaciji*/
			}
			
				if($dodatni_filter == "sve" || $dodatni_filter == "po_isplati")
				{
					
					/* upit kojim se izvlaci datum iz tabele - POCETAK*/
					$sql_datum_ispate_sudski = "SELECT DISTINCT(datum_naloga) AS datum_naloga_sudski FROM isplate_sp WHERE idsp = $idsp;";
					$rezultat_datum_ispate_sudski = pg_query($conn_stete, $sql_datum_ispate_sudski);
					$niz_datum_ispate_sudski = pg_fetch_all($rezultat_datum_ispate_sudski);
					$broj_datuma_isplate=count($niz_datum_ispate_sudski);
					$visina_datuma_isplate = 100/$broj_datuma_isplate;
					/* upit kojim se izvlaci datum iz tabele - KRAJ*/
				
				/*pocetak uslova za sve*/
				
				$html .= <<<EOF
				
						<td>
							<table width="100%" style="margin-top:-2px;" >
								<tr height="10px" width="100%">									
EOF;
				
				$html .= <<<EOF
									<td style=" font-size: 8pt;">
										<table style="margin-top:0px;" rules="rows" width="100%" >
											<tr>
												<td style=" font-size: 8pt; background:#F2FFFF; width:70px; height:10px;">Datum</td>
												<td style=" font-size: 8pt; background:#F2FFFF; width:140px; ">Svrha</td>
												<td style=" font-size: 8pt; background:#F2FFFF; width:80px;">SP</td>
												<td style=" font-size: 8pt; background:#F2FFFF;">MP</td>
											</tr>
EOF;
				
				for($j=0; $j<$broj_datuma_isplate; $j++)
				{
					$datum_isplate_sudski = $niz_datum_ispate_sudski[$j]['datum_naloga_sudski'];
					
					$podaci_o_mirnom = vrati_podatke_predmeta($broj_predmeta_za_povezivanje_na_sudski,$idsp, $jmbg_tuzioca, $tipstms, $tipstml, $tipstn, $vrsta_osiguranja);
					$predmet_id = $podaci_o_mirnom[$j]['predmet_id'];
					
					if(!$datum_isplate_sudski)
					{
						$html .= <<<EOF
								<tr><td colspan="4" style="font-size: 10pt; color:green; font-weight: bold; text-align:center;"> sudski postupak nije kompletno re¹en </td></tr>
EOF;
					}
					else 
					{	
					
					$sql_iznos_svrha_sudski = " SELECT svrha, iznos, rbr FROM isplate_sp WHERE idsp = $idsp AND datum_naloga= '$datum_isplate_sudski';";
					$rezultat_iznos_svrha_sudski = pg_query($conn_stete, $sql_iznos_svrha_sudski);
					$niz_iznos_svrha_sudski = pg_fetch_all($rezultat_iznos_svrha_sudski);
					$broj_isplata = count($niz_iznos_svrha_sudski);
					$visina_polja = $visina_datuma_isplate/$broj_isplata;
					
					$datum_isplate_sudski = date('d.m.Y', strtotime($datum_isplate_sudski));
					$html .= <<<EOF
								<tr>
									<td rowspan="$broj_isplata" style=" font-size:9pt;font-weight: bold;"> $datum_isplate_sudski </td>
EOF;
					
					for($l=0; $l<$broj_isplata; $l++)
					{
						$svrha_isplate_sudska = $niz_iznos_svrha_sudski[$l]['svrha'];
						$rbr_isplate_sudska = $niz_iznos_svrha_sudski[$l]['rbr'];
						$iznos_isplate_sudski = $niz_iznos_svrha_sudski[$l]['iznos'];
						
						$datum_isplate_sudski = date('Y-m-d', strtotime($datum_isplate_sudski));
						$sql_iznos_isplate_mirni ="SELECT p.id AS predmet_id,i.svrha AS svrha, i.datum_naloga AS datum_naloga, i.iznos AS iznos, p.novi_broj_predmeta AS novi_broj_predmeta FROM isplate AS i
												LEFT JOIN predmet_odstetnog_zahteva AS p
												ON p.id=i.idstete
												WHERE p.sudski_postupak_id = $idsp AND i.datum_naloga='$datum_isplate_sudski' AND i.svrha='$svrha_isplate_sudska' AND i.rbr=$rbr_isplate_sudska";
						$rezultat_iznos_isplate_mirni = pg_query($conn_stete, $sql_iznos_isplate_mirni);
						$niz_iznos_isplate_mirni = pg_fetch_all($rezultat_iznos_isplate_mirni);
						$broj_iznosa_po_svrsi_i_datumu = count($niz_iznos_isplate_mirni);
						
						$sql_ukupan_iznos_po_datumu = " SELECT SUM(iznos) AS ukupan_iznos FROM isplate WHERE datum_naloga='$datum_isplate_sudski' AND svrha='$svrha_isplate_sudska' AND rbr=$rbr_isplate_sudska";
						$rezultat_ukupan_iznos_po_datumu = pg_query($conn_stete, $sql_ukupan_iznos_po_datumu);
						$niz_ukupan_iznos_po_datumu = pg_fetch_assoc($rezultat_ukupan_iznos_po_datumu);
						$ukupan_iznos_po_datumu = $niz_ukupan_iznos_po_datumu['ukupan_iznos'];

						$color_iznos = ($ukupan_iznos_po_datumu == $iznos_isplate_sudski && $broj_iznosa_po_svrsi_i_datumu == $broj_predmeta_za_povezivanje_na_sudski) ? 'green' : 'red' ;
						
						$html .=<<<EOF
								<td style=" font-size: 8pt; background:#F2FFFF;  font-style: italic; ">$svrha_isplate_sudska</td>
EOF;
						
						$html .=<<<EOF
								<td style="color:$color_iznos; font-weight: bold;">$iznos_isplate_sudski</td>
EOF;
						$html .=<<<EOF
								<td>
									<table>
EOF;
						
						for($m=0; $m<$broj_iznosa_po_svrsi_i_datumu; $m++)
						{
							$novi_broj_predmeta = $niz_iznos_isplate_mirni[$m]['novi_broj_predmeta'];
							$iznos_isplate_mirni = $niz_iznos_isplate_mirni[$m]['iznos'];
							$predmet_id = $niz_iznos_isplate_mirni[$m]['predmet_id'];
							
							if($broj_iznosa_po_svrsi_i_datumu == $broj_predmeta_za_povezivanje_na_sudski)
							{
								$html .=<<<EOF
										<tr><td style="font-size:9pt;"><a target="_blank" href="../../stete/pregled.php?idstete=$predmet_id&dugme=DA">$novi_broj_predmeta </a><font style="color:$color_iznos; font-weight: bold;">$iznos_isplate_mirni</font></td></tr>
EOF;
							}
							else 
							{
								$html .=<<<EOF
										<tr><td style="font-size:9pt;"><a target="_blank" href="../../stete/pregled.php?idstete=$predmet_id&dugme=DA">$novi_broj_predmeta </a><font style="color:$color_iznos; font-weight: bold;">$iznos_isplate_mirni</font><br><font style="color:red; text-align:center;">niste rasporedili iznose na sve predmete</font></td></tr>
EOF;
							}
						}
						
						
						$html .=<<<EOF
									</table>
								</td>
							</tr>
EOF;
						}
						/*dodato za else*/}
	/*dodato*/	}
				
				
				$html .=<<<EOF
						
EOF;
				
				
				
				
				$html .=<<<EOF

										</table>
									</td>
								</tr>
						
						
						
						</table>
						</td>
						
EOF;
				
				
		/* uslov da je dodatni filter sve*/	}

			
/*kraj elsakoji se odnosi na deo ukoliko su podaci poveyani,kod te poruke*/	}

			$datum_upisa = date('Y-m-d',strtotime($datum_upisa));
			if($status == 3)
			{
				$html .= <<<EOF
					<td align="center" style="width:60px;"></td>
EOF;
			}
			else
			{
				if($datum_upisa>='2012-01-01')
				{
					$html .= <<<EOF
						<td align="center" style="width:60px;"><input type="button"  value="Razbij" onclick="razbij_sudski_predmet($idsp)"></td>
EOF;
				}
				else
				{
					$html .= <<<EOF
						<td align="center" style="width:60px;"><input type="button"  value="Razbij" onclick="razbij_sudski_predmet_jedna_steta($idsp)"></td>
EOF;
				}
			}
			$html .= <<<EOF
			</tr>
EOF;

}



	/* deo kojim se zatvara druga tabela i div */
	$html .= <<<EOF
	</tbody >
	</table>
	</div>

EOF;
	
	$ret->html = mb_convert_encoding($html, 'utf-8', 'iso-8859-2');
	$ret->datum_do = "Dodatni " . $dodatni_filter;
	$ret->redni_broj = $redni_broj;
	$ret->status = mb_convert_encoding($status, 'utf-8', 'iso-8859-2');

	echo json_encode($ret);exit;
}


function vrati_podatke_predmeta($broj_tuzilaca,$idsp, $jmbg_tuzioca, $tipstms, $tipstml, $tipstn, $vrsta_osiguranja)
{
	$conn_stete = pg_connect("dbname=stete user=zoranp");
	$tip_tuzioca;
	$tip;
	$niz_podataka_ostecenog_mirni = array();

	for($i=0; $i<$broj_tuzilaca; $i++)
	{
	if($tipstms == 't' && $tipstml == 't' && $tipstn == 't')
	{
		if($i==0)
		{
			$tip_tuzioca = 'MS';
			$tip = 'S';
		}
		elseif ($i==1)
		{
			$tip_tuzioca = 'ML';
			$tip = 'L';
		}
		else
		{
			$tip_tuzioca = 'N';
			$tip = 'L';
		}
	}
	else if($tipstms == 't' && $tipstml == 'f' && $tipstn == 'f')
	{
		$tip_tuzioca = 'MS';
		
		$tip = ($vrsta_osiguranja == 'AO') ? 'S' : 'P';
	}
	else if($tipstms == 't' && $tipstml == 't' && $tipstn == 'f')
	{
		$tip_tuzioca = ($i==0) ? 'MS' : 'ML';
		$tip = ($i==0) ? 'S' : 'L';
	}
	else if($tipstms == 't' && $tipstml == 'f' && $tipstn == 't')
	{
		$tip_tuzioca = ($i==0) ? 'MS' : 'N';
		$tip = ($i==0) ? 'S' : 'L';
	}
	else if($tipstms == 'f' && $tipstml == 't' && $tipstn == 'f')
	{
		$tip_tuzioca = 'ML';
		$tip = 'L';
	}
	else if($tipstms == 'f' && $tipstml == 't' && $tipstn == 't')
	{
		$tip_tuzioca = ($i==0) ? 'ML' : 'N';
		$tip = 'L';
	}
	else if($tipstms == 'f' && $tipstml == 'f' && $tipstn == 't')
	{
		$tip_tuzioca = 'N';
		//$tip = 'L';
		$tip = 'N';
	}

	$sql_osteceni_u_predmetu = "SELECT id AS predmet_id, tip_predmeta AS tip_predmeta, jmbgpibost AS jmbg_osteceni, novi_broj_predmeta AS novi_broj_predmeta,
								datumkonac AS datumkonac, nalog AS nalog, isplata AS isplata, isplaceno AS isplaceno 
								FROM predmet_odstetnog_zahteva WHERE sudski_postupak_id= $idsp";
	
	
	if($jmbg_tuzioca != NULL)
	{
		$sql_osteceni_u_predmetu .= " AND jmbgpibost='$jmbg_tuzioca'";
	}
	
	if($tipstml !=NULL && $tipstn !=NULL && $tipstms !=NULL)
	{
		if($tip_tuzioca == 'N')
		{
			$sql_osteceni_u_predmetu .= " AND (tip_predmeta='L' OR tip_predmeta='N')";
		}
		else 
		{
			$sql_osteceni_u_predmetu .= " AND tip_predmeta='$tip'";
		}
	}
	$rezultat_osteceni_u_predmetu = pg_query($conn_stete, $sql_osteceni_u_predmetu);
	$niz_osteceni_u_predmetu = pg_fetch_assoc($rezultat_osteceni_u_predmetu);

	$predmet_id = $niz_osteceni_u_predmetu['predmet_id'];
	$jmbg_osteceni = $niz_osteceni_u_predmetu['jmbg_osteceni'];
	$novi_broj_predmeta = $niz_osteceni_u_predmetu['novi_broj_predmeta'];
	$tip_predmeta = $niz_osteceni_u_predmetu['tip_predmeta'];
	$datum_kompletiranja_mirni = $niz_osteceni_u_predmetu['datumkonac'];
	$nalog_mirni = $niz_osteceni_u_predmetu['nalog'];
	$isplata_mirni = $niz_osteceni_u_predmetu['isplata'];
	$isplaceno_mirni = $niz_osteceni_u_predmetu['isplaceno'];

	$niz_podataka = array('tip_tuzioca'=>$tip_tuzioca, 'tip'=>$tip, 'predmet_id'=>$predmet_id, 'jmbg_osteceni'=>$jmbg_osteceni, 'novi_broj_predmeta'=>$novi_broj_predmeta, 'datum_kompletiranja_mirni'=>$datum_kompletiranja_mirni,'nalog_mirni'=>$nalog_mirni, 'isplaceno_mirni'=>$isplaceno_mirni, 'sql'=>$sql_osteceni_u_predmetu);

	array_push($niz_podataka_ostecenog_mirni, $niz_podataka);

	}
	return $niz_podataka_ostecenog_mirni;
}

function vrati_sudske_predmete_po_statusu($datum_od, $datum_do, $status)
{
	$conn_stete = pg_connect("dbname=stete user=zoranp");
	
	$flag;
	
	// izvalcenje svih sudskih postupaka u nekom intervalu
	if($datum_do=='nema')
	{
		$sql_povezani = "SELECT sp.idsp AS idsp,sp.vr_osig AS vr_osig, sp.brsp AS brsp, sp.datum_kompletiranja AS datum_kompletiranja, sp.datum_isplate AS datum_isplate, sp.datum_upisa AS datum_upisa, sp.brpolise AS brpolise, case when sp.sifra isnull then '&nbsp;' else sp.sifra end as sifra, case when sp.prezime_osig isnull then '&nbsp;' else sp.prezime_osig end as prezime_osig, case when sp.ime_osig isnull then '&nbsp;' else sp.ime_osig end as ime_osig
		FROM sudski_postupak AS sp
		WHERE sp.idsp=$datum_od";
		
	}
	else 
	{
		$sql_povezani = "SELECT sp.idsp AS idsp,sp.vr_osig AS vr_osig, sp.brsp AS brsp, sp.datum_kompletiranja AS datum_kompletiranja, sp.datum_isplate AS datum_isplate, sp.datum_upisa AS datum_upisa, sp.brpolise AS brpolise, case when sp.sifra isnull then '&nbsp;' else sp.sifra end as sifra, case when sp.prezime_osig isnull then '&nbsp;' else sp.prezime_osig end as prezime_osig, case when sp.ime_osig isnull then '&nbsp;' else sp.ime_osig end as ime_osig
					FROM sudski_postupak AS sp
					WHERE sp.datum_upisa BETWEEN '$datum_od' AND '$datum_do'  order by idsp";
	}
	$rezultat_povezani = pg_query($conn_stete, $sql_povezani);
	$niz_povezani = pg_fetch_all($rezultat_povezani);
	$broj_svih_sudskih = count($niz_povezani);
	

	
	$string;

	$niz_sudskih = array();
	
	for ($i=0; $i<count($niz_povezani); $i++)
	{
		$flag_povezan=true;
		$idsp = $niz_povezani[$i]['idsp'];
		$vrsta_osiguranja = $niz_povezani[$i]['vr_osig'];
		$datum_kompetiranja_sudski = $niz_povezani[$i]['datum_kompletiranja'];
		$datum_isplate_sudski = $niz_povezani[$i]['datum_isplate'];
		
		// izvlacenje svih tuzilaca na osnovu idsp bey obzira koliko tipova je stiklirano
		$sql_tuzilac = "SELECT * FROM
						((SELECT * FROM tuzilac WHERE (tipstml=true OR tipstn=true) AND idsp IN ($idsp))
						UNION ALL
						(SELECT * FROM tuzilac WHERE tipstms=true AND idsp IN ($idsp))) AS foo ORDER BY idtuzilac";
		$rezultat_tuzilac = pg_query($conn_stete, $sql_tuzilac);
		$niz_tuzilac = pg_fetch_all($rezultat_tuzilac);
		$broj_tuzilaca_na_sudskom = count($niz_tuzilac);
		
		for($j=0; $j<$broj_tuzilaca_na_sudskom; $j++)
		{
			$jmbg_tuzioca = trim($niz_tuzilac[$j]['jmbg'], "");
			$tipstms = $niz_tuzilac[$j]['tipstms'];
			$tipstml = $niz_tuzilac[$j]['tipstml'];
			$tipstn = $niz_tuzilac[$j]['tipstn'];
			
			if($jmbg_tuzioca)
			{
			
			// svi predmeti u mirnom koji su vezani za dati sudski
			$sql_broj_ostecenih = " SELECT COUNT(id) AS broj FROM predmet_odstetnog_zahteva WHERE sudski_postupak_id= $idsp";
			$rezultat_broj_ostecenih = pg_query($conn_stete, $sql_broj_ostecenih);
			$niz_broj_ostecenih = pg_fetch_assoc($rezultat_broj_ostecenih);
			$broj_mirnih_po_sudskom = $niz_broj_ostecenih['broj'];
			
			//  podaci o mirnom i izvalcenje podataka na osnovu jmbg, isdt i tipa i poredjenje datum sa sudskim
			$niz_predmeta_mirni = vrati_podatke_predmeta($broj_tuzilaca_na_sudskom, $idsp, $jmbg_tuzioca, $tipstms, $tipstml, $tipstn, $vrsta_osiguranja);
			$datum_kompletiranja_mirni = $niz_predmeta_mirni[$j]['datum_kompletiranja_mirni'];
			$datum_isplate_mirni = $niz_predmeta_mirni[$j]['nalog_mirni'];
			
			// upit za izvalacenje rezervacija iz sudskih
			$sql_rezervacije_sudski = "SELECT (rez_mat_lica+rez_nemat_lica+rez_renta_lica+rez_mat_stvari) AS rezervisana_suma, datum_od AS datum_od FROM rez_sp_razbijeno_sa_periodom WHERE idsp =$idsp";
			$rezultat_rezervacije_sudski = pg_query($conn_stete, $sql_rezervacije_sudski);
			$niz_rezervacije_sudski = pg_fetch_all($rezultat_rezervacije_sudski);
			$broj_rezervacija_po_datumu_sudski = count($niz_rezervacije_sudski);
			
			for($k=0; $k<$broj_rezervacija_po_datumu_sudski; $k++)
			{
				$datum_rezervacije_sudski = $niz_rezervacije_sudski[$k]['datum_od'];
				$iznos_rezervacije_sudski = $niz_rezervacije_sudski[$k]['rezervisana_suma'];
				
				$sql_iznos_rezervacija_mirni = "SELECT p.novi_broj_predmeta AS novi_broj_predmeta,p.id AS predmet_id,r.rezervisano AS rezervisano,r.idstete FROM predmet_odstetnog_zahteva AS p
												LEFT JOIN rezervacije AS r
												ON p.id=r.idstete
												WHERE p.sudski_postupak_id=$idsp AND r.datum_od='$datum_rezervacije_sudski'";
				$rezultat_iznos_rezervacija_mirni = pg_query($conn_stete, $sql_iznos_rezervacija_mirni);
				$niz_iznos_rezervacija_mirni = pg_fetch_all($rezultat_iznos_rezervacija_mirni);
				//$broj_predmeta_u_mirnom_po_datumu_rezervacije = count($niz_iznos_rezervacija_mirni);
				$broj_rezervacija_po_datumu_mirni = count($niz_iznos_rezervacija_mirni);
				
				$sql_ukupna_rezervacija_u_mirnom ="SELECT SUM(r.rezervisano) AS ukupna_rezervacija FROM predmet_odstetnog_zahteva AS p
												LEFT JOIN rezervacije AS r
												ON p.id=r.idstete
												WHERE p.sudski_postupak_id=$idsp AND r.datum_od='$datum_rezervacije_sudski'";
				$rezultat_ukupna_rezervacija_u_mirnom = pg_query($conn_stete, $sql_ukupna_rezervacija_u_mirnom);
				$niz_ukupna_rezervacija_u_mirnom = pg_fetch_assoc($rezultat_ukupna_rezervacija_u_mirnom);
				$ukupna_rezervacija_u_mirnom= $niz_ukupna_rezervacija_u_mirnom['ukupna_rezervacija'];
				
				
				if($broj_mirnih_po_sudskom != $broj_tuzilaca_na_sudskom && $status == 1)
				{
					$flag=false;
					
					
					array_push($niz_sudskih, $idsp);
				}
				//else if($broj_mirnih_po_sudskom == $broj_tuzilaca_na_sudskom  && (($datum_kompletiranja_mirni && !$datum_kompetiranja_sudski) || ($datum_kompletiranja_mirni && $datum_kompetiranja_sudski && ($datum_kompletiranja_mirni != $datum_kompetiranja_sudski)) || !$datum_isplate_sudski || !$datum_isplate_mirni || ($datum_isplate_mirni != $datum_isplate_sudski) || !$broj_tuzilaca_na_sudskom || !$broj_rezervacija_po_datumu_mirni || ($broj_tuzilaca_na_sudskom != $broj_rezervacija_po_datumu_mirni)) && $status == 2)
				else if($broj_mirnih_po_sudskom == $broj_tuzilaca_na_sudskom  && (($datum_kompletiranja_mirni && $datum_kompetiranja_sudski && ($datum_kompletiranja_mirni != $datum_kompetiranja_sudski)) || ($datum_isplate_sudski && $datum_isplate_mirni && ($datum_isplate_mirni != $datum_isplate_sudski)) || ($broj_tuzilaca_na_sudskom && $broj_rezervacija_po_datumu_mirni && ($broj_tuzilaca_na_sudskom != $broj_rezervacija_po_datumu_mirni)) || ( ($iznos_rezervacije_sudski != $ukupna_rezervacija_u_mirnom))) && $status == 2)
				{
					$flag=false;
					
					array_push($niz_sudskih, $idsp);
				}
				else if($broj_mirnih_po_sudskom == $broj_tuzilaca_na_sudskom  && $datum_kompletiranja_mirni && $datum_kompetiranja_sudski && ($datum_kompletiranja_mirni == $datum_kompetiranja_sudski) && $datum_isplate_sudski && $datum_isplate_mirni && ($datum_isplate_mirni == $datum_isplate_sudski) && $broj_tuzilaca_na_sudskom && $broj_rezervacija_po_datumu_mirni && ($broj_tuzilaca_na_sudskom == $broj_rezervacija_po_datumu_mirni) && $status == 3)
				{
					$flag = true;
					
				}
				else if($flag_povezan && $broj_mirnih_po_sudskom == $broj_tuzilaca_na_sudskom  &&  ((!$datum_kompetiranja_sudski && !$datum_isplate_sudski) || ($datum_kompetiranja_sudski && !$datum_isplate_sudski && ($datum_kompletiranja_mirni == $datum_kompetiranja_sudski))) && ($iznos_rezervacije_sudski == $ukupna_rezervacija_u_mirnom) && $status == 3)
				//else if($broj_mirnih_po_sudskom == $broj_tuzilaca_na_sudskom  &&  ((!$datum_kompetiranja_sudski && !$datum_isplate_sudski) || ($datum_kompetiranja_sudski && !$datum_isplate_sudski && ($datum_kompletiranja_mirni == $datum_kompetiranja_sudski))) && ($iznos_rezervacije_sudski == $ukupna_rezervacija_u_mirnom) && $status == 3)
				{
					//&& ($iznos_rezervacije_sudski == $ukupna_rezervacija_u_mirnom)
					$flag=false;
					array_push($niz_sudskih, $idsp);
				}
				else 
				{
					$flag = false;
					$flag_povezan=false;
				}
			}
			
			$sql_datum_ispate_sudski = "SELECT DISTINCT(datum_naloga) AS datum_naloga_sudski FROM isplate_sp WHERE idsp = $idsp;";
			$rezultat_datum_ispate_sudski = pg_query($conn_stete, $sql_datum_ispate_sudski);
			$niz_datum_ispate_sudski = pg_fetch_all($rezultat_datum_ispate_sudski);
			
			for($k=0; $k<count($niz_datum_ispate_sudski); $k++)
			{
				$datum_isplate_sudski = $niz_datum_ispate_sudski[$k]['datum_naloga_sudski'];

				$sql_iznos_svrha_sudski = " SELECT svrha, iznos, rbr FROM isplate_sp WHERE idsp = $idsp AND datum_naloga= '$datum_isplate_sudski';";
				$rezultat_iznos_svrha_sudski = pg_query($conn_stete, $sql_iznos_svrha_sudski);
				$niz_iznos_svrha_sudski = pg_fetch_all($rezultat_iznos_svrha_sudski);
				$broj_isplata = count($niz_iznos_svrha_sudski);
				
				
				for($l=0; $l<$broj_isplata; $l++)
				{
					$svrha_isplate_sudska = $niz_iznos_svrha_sudski[$l]['svrha'];
					$rbr_isplate_sudska = $niz_iznos_svrha_sudski[$l]['rbr'];
					$iznos_isplate_sudski = $niz_iznos_svrha_sudski[$l]['iznos'];
				
					//$datum_isplate_sudski = date('Y-m-d', strtotime($datum_isplate_sudski));
					$sql_iznos_isplate_mirni ="SELECT p.id AS predmet_id,i.svrha AS svrha, i.datum_naloga AS datum_naloga, i.iznos AS iznos, p.novi_broj_predmeta AS novi_broj_predmeta FROM isplate AS i
													LEFT JOIN predmet_odstetnog_zahteva AS p
													ON p.id=i.idstete
													WHERE p.sudski_postupak_id = $idsp AND i.datum_naloga='$datum_isplate_sudski' AND i.svrha='$svrha_isplate_sudska' AND i.rbr=$rbr_isplate_sudska";
					$rezultat_iznos_isplate_mirni = pg_query($conn_stete, $sql_iznos_isplate_mirni);
					$niz_iznos_isplate_mirni = pg_fetch_all($rezultat_iznos_isplate_mirni);
					$broj_iznosa_po_svrsi_i_datumu = count($niz_iznos_isplate_mirni);
				
					$sql_ukupan_iznos_po_datumu = " SELECT SUM(iznos) AS ukupan_iznos FROM isplate WHERE datum_naloga='$datum_isplate_sudski' AND svrha='$svrha_isplate_sudska' AND rbr=$rbr_isplate_sudska";
					$rezultat_ukupan_iznos_po_datumu = pg_query($conn_stete, $sql_ukupan_iznos_po_datumu);
					$niz_ukupan_iznos_po_datumu = pg_fetch_assoc($rezultat_ukupan_iznos_po_datumu);
					$ukupan_iznos_po_datumu = $niz_ukupan_iznos_po_datumu['ukupan_iznos'];
					
					
					for($m=0; $m<$broj_iznosa_po_svrsi_i_datumu; $m++)
					{
						$novi_broj_predmeta = $niz_iznos_isplate_mirni[$m]['novi_broj_predmeta'];
						$iznos_isplate_mirni = $niz_iznos_isplate_mirni[$m]['iznos'];
						$predmet_id = $niz_iznos_isplate_mirni[$m]['predmet_id'];
						
						
						if($flag == true && $ukupan_iznos_po_datumu && $iznos_isplate_sudski && ($broj_iznosa_po_svrsi_i_datumu == $broj_tuzilaca_na_sudskom) && ($iznos_isplate_sudski==$ukupan_iznos_po_datumu))
						{
							array_push($niz_sudskih, $idsp);
							
						}
						
					}
				}
			}
			
			/*dodato ako jmbg postoji*/}
						
		}
		
	}
	
	if($datum_do=='nema' && $flag_povezan)
	{
		return $niz_sudskih[0];
	}
	else
	{
		return $niz_sudskih;
	}
	
}

if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'pronadji_sve_predmete')
{
	pronadji_sve_predmete();
}
function pronadji_sve_predmete()
{
	$conn_stete = pg_connect("host=localhost dbname=stete user=zoranp");
	
	$ret = new stdClass();

	$broj_polise = $_POST['broj_polise'];
	$vrsta_osiguranja  = $_POST['vrsta_osiguranja'];
	$flag;
	
	
	$sql = "SELECT poz.novi_broj_predmeta AS novi_broj_predmeta, poz.id AS id FROM predmet_odstetnog_zahteva AS poz
			INNER JOIN odstetni_zahtev AS oz
			ON oz.id=poz.odstetni_zahtev_id
			INNER JOIN stetni_dogadjaj AS sd
			ON sd.id=oz.stetni_dogadjaj_id
			WHERE sd.vrsta_obrasca='$vrsta_osiguranja' AND sd.broj_polise=$broj_polise";
	
	$upit = pg_query($conn_stete,$sql);
	$niz = pg_fetch_all($upit);
	
	if($niz)
	{
		$sql_povezani_regresi ="
WITH tabela_predmeta AS
(
SELECT poz.id AS predmet_id, poz.novi_broj_predmeta FROM stetni_dogadjaj AS sd
INNER JOIN odstetni_zahtev AS oz
ON oz.stetni_dogadjaj_id = sd.id
INNER JOIN predmet_odstetnog_zahteva AS poz
ON  poz.odstetni_zahtev_id=oz.id

WHERE broj_polise=$broj_polise AND vrsta_obrasca='$vrsta_osiguranja'

), 
tabela_steta_regres AS 
(
SELECT sr.idregres,idstete,brreg, potrazivanje, adv.advokatska_kancelarija_id, adv.datumang, adv.datum_razduzenja, ak.naziv AS advokatska_kancelarija FROM steta_regres AS sr
INNER JOIN regresna AS reg
ON sr.idregres = reg.idregres
LEFT OUTER JOIN advokat As adv
ON adv.idregres=sr.idregres
LEFT OUTER JOIN dblink('host=localhost dbname=amso user=zoranp', 'SELECT id, naziv FROM sifarnici.advokatske_kancelarije ') AS ak (id integer, naziv text) ON ak.id = adv.advokatska_kancelarija_id
)
SELECT DISTINCT ON (tabela_steta_regres.idregres)
tabela_steta_regres.idregres, tabela_steta_regres.brreg, tabela_steta_regres.potrazivanje,tabela_predmeta.novi_broj_predmeta,
tabela_steta_regres.advokatska_kancelarija_id, tabela_steta_regres.datum_razduzenja, tabela_steta_regres.datumang ,  tabela_steta_regres.advokatska_kancelarija
FROM tabela_predmeta, tabela_steta_regres
WHERE tabela_predmeta.predmet_id=tabela_steta_regres.idstete 
ORDER BY tabela_steta_regres.idregres,  tabela_steta_regres.datum_razduzenja DESC, tabela_steta_regres.datumang DESC";
		$upit_povezani_regresi = pg_query($conn_stete,$sql_povezani_regresi);
		$niz_povezani_regresi = pg_fetch_all($upit_povezani_regresi);
		
		if($niz_povezani_regresi)
		{
			$povezani_regresi = "
					<table  border='2' style='border-collapse: collapse;' width='750px'>
						<tr style='background-color: #DDEEEE;'>
							<td width='250px;' align='center'>Povezan regres</td>
							<td width='250px;' align='center'>Potrazivanje</td>
							<td width='250px;' align='center'>Advokatska kancelarija</td>
						</tr>
					";
			
			
			for($k=0; $k<count($niz_povezani_regresi); $k++)
			{
				$boja_reda_regesa= ($k%2==0) ? '': '#BBCCCC';
				$id_povezanog_regresa = $niz_povezani_regresi[$k]['idregres'];
				$broj_povezanog_regresa = $niz_povezani_regresi[$k]['brreg'];
				$broj_predmeta_povezanog_regresa = $niz_povezani_regresi[$k]['novi_broj_predmeta'];
				$potrazivanje_povezanog_regresa = $niz_povezani_regresi[$k]['potrazivanje'];
				$advokat_povezanog_regresa = $niz_povezani_regresi[$k]['advokatska_kancelarija'];
				$povezani_regresi .= "<tr style='background-color:$boja_reda_regesa;'>
						<td width='250px;' align='center'><a href='regresna.php?idregres=$id_povezanog_regresa&&status=izmeni'>$broj_povezanog_regresa</a></td>
						<td width='250px;' align='center'>$potrazivanje_povezanog_regresa</td>
						<td width='250px;' align='center'>$advokat_povezanog_regresa</td>
						</tr>";
			}
			
			
			
			$povezani_regresi .= "</table>";
			
		}
		else 
		{
			$povezani_regresi = "";
			//$povezani_regresi = "nema nema nema nema";
			//$povezani_regresi = $sql_povezani_regresi;
		}
		
		
		
		
		
		$opcije = "<option value='-1' >--Izaberite--</option>";
		// Proði kroz niz i kreiraj opcije za selekt
		for($i=0; $i<count($niz); $i++)
		{
			$predmet_id = $niz[$i]['id'];
			$predmet_opis = $niz[$i]['novi_broj_predmeta'];
		
			$opcije .= "<option value='$predmet_id'>$predmet_opis</option>";
		}
		$flag = true;
		$ret->opcije = mb_convert_encoding($opcije, 'utf-8', 'iso-8859-2');
		$ret->povezani_regresi = mb_convert_encoding($povezani_regresi, 'utf-8', 'iso-8859-2');
	}
	else 
	{
		$flag = false;
		
	}
	$ret->flag = $flag;
	echo json_encode($ret);exit;
}

if(isset($_REQUEST['funkcija']) && $_REQUEST['funkcija'] == 'popuni_podatke_stetni_dogadjaj')
{
	popuni_podatke_stetni_dogadjaj();
}
function popuni_podatke_stetni_dogadjaj()
{
	$conn_stete = pg_connect("host=localhost dbname=stete user=zoranp");
	$conn_amso = pg_connect("host=localhost dbname=amso user=zoranp");
	
	$ret = new stdClass();
	
	$vrsta_osiguranja = $_POST['vrsta_osiguranja'];
	$predmet_id = $_POST['predmet_id'];
	
	$sql_pravna = "SELECT sd.mesto_nastanka_id AS mesto_nastanka_id,sd.zemlja_nastanka_id AS zemlja_nastanka_id, sd.datum_nastanka AS datum_nastanka, sd.opis_lokacije AS opis_lokacije, 
	p.regres_od AS regres_od, p.osiguranjeregpotr AS osiguranjeregpotr, p.drzavaregpotr AS drzavaregpotr, p.osiguravajuce_drustvo_id AS osiguravajuce_drustvo_id, poz.nalog AS nalog, poz.isplaceno AS iznos_isplate_mirni, poz.novi_broj_predmeta AS novi_broj_predmeta,
poz.jmbgPibKriv AS jmbgPibKriv, poz.prezimeKriv AS prezimeKriv,poz.imeNazivKriv AS imeNazivKriv, poz.osiguranik_krivac_adresa AS osiguranik_krivac_adresa,
poz.osiguranik_krivac_telefon1 AS osiguranik_krivac_telefon1 , poz.osiguranik_krivac_mesto_id AS osiguranik_krivac_mesto_id, poz.osiguranik_krivac_zemlja_id AS osiguranik_krivac_zemlja_id,
v.prezimevozkriv AS prezimevozkriv, v.imevozkriv AS imevozkriv, v.jmbgvozkriv AS jmbgvozkriv, v.vozac_krivac_mesto_id AS vozac_krivac_mesto_id, v.vozac_krivac_mesto_opis AS vozac_krivac_mesto_opis, v.vozac_krivac_adresa AS vozac_krivac_adresa, v.vozac_krivac_telefon1 AS vozac_krivac_telefon1, v.vozac_krivac_zemlja_id AS vozac_krivac_zemlja_id
FROM pravni AS p
INNER JOIN predmet_odstetnog_zahteva AS poz 
ON poz.id=p.idstete
INNER JOIN odstetni_zahtev AS oz
On oz.id=poz.odstetni_zahtev_id
INNER JOIN stetni_dogadjaj AS sd
ON sd.id=oz.stetni_dogadjaj_id
LEFT JOIN vozac AS v
ON poz.id=v.idstete
WHERE p.idstete= $predmet_id";
	$upit_pravna = pg_query($conn_stete,$sql_pravna);
	$niz_pravna = pg_fetch_assoc($upit_pravna);
	
	$regres_od = $niz_pravna['regres_od'];
	
	$mesto_nastanka_id = $niz_pravna['mesto_nastanka_id'];
	$zemlja_nastanka_id = $niz_pravna['zemlja_nastanka_id'];
	$datumnast = $niz_pravna['datum_nastanka'];

// 	$nalog = $niz_pravna['nalog'];
// 	$iznos_isplate_mirni = $niz_pravna['iznos_isplate_mirni'];
	$novi_broj_predmeta = $niz_pravna['novi_broj_predmeta'];
	$opis_lokacije_stetni_dogadjaj = $niz_pravna['opis_lokacije'];
	
	$sql_zemlje = "SELECT id,naziv,kontinent_ispis FROM sifarnici.zemlje_drzave WHERE id=$zemlja_nastanka_id;";
	$rezultat_zemlje = pg_query($conn_stete,$sql_zemlje);
	$niz_zemlje = pg_fetch_assoc($rezultat_zemlje);
	$naziv_ispis_zemlja = $niz_zemlje['naziv'];
	
	$sql_mesto_aktivna = "SELECT opstina_id,naziv_ispis FROM sifarnici.mesto_aktivna WHERE id=$mesto_nastanka_id";
	$upit_mesto_aktivna = pg_query($conn_amso,$sql_mesto_aktivna);
	$niz_mesto_aktivna = pg_fetch_assoc($upit_mesto_aktivna);
	$naziv_ispis = $niz_mesto_aktivna['naziv_ispis'];
	$opstina_nastanka_id = $niz_mesto_aktivna['opstina_id'];
	
	$sql_opstine = "SELECT naziv_ispis FROM sifarnici.opstina_aktivne WHERE id=$opstina_nastanka_id";
	$rezultat_opstine = pg_query($conn_amso,$sql_opstine);
	$niz_opstine = pg_fetch_assoc($rezultat_opstine);
	$naziv_ispis_opstine = $niz_opstine['naziv_ispis'];
	
	/* Upit kojim se izvlace podaci o isplati predmeta odstetnog zahteva za koji se otvara regresno potrazivanje */
	$sql_isplata_regpotr = "SELECT datum_naloga, iznos FROM isplate WHERE idstete = $predmet_id AND konacna='DA'";
	$rezultat_isplata_regpotr = pg_query($conn_stete,$sql_isplata_regpotr);
	$niz_isplata_regpotr = pg_fetch_assoc($rezultat_isplata_regpotr);
	$iznos_isplate_mirni = $niz_isplata_regpotr['iznos'];
	$nalog = $niz_isplata_regpotr['datum_naloga'];
	
	$html = "
			<td align='right' style='width:17%'><label>Zemlja nastanka</label></td>
	<td>
			<select name='zemlja_nastanka_id' style='width:190px;'>
				<option value='$zemlja_nastanka_id'>$naziv_ispis_zemlja</option>
		    </select>
	</td>";
	if($zemlja_nastanka_id == 199)
	{
		$html .= "
		<td align='right' style='width:17%'><label>Op¹tina:</label>
		<select name='opstina_nastanka_id' style='width:190px;'>
		<option value='$opstina_nastanka_id'>$naziv_ispis_opstine</option>
		</select>
		</td>
		<td align='right' style='width:17%'><label>Mesto:</label>
		<select name='mesto_nastanka_id' style='width:190px;'>
		<option value='$mesto_nastanka_id'>$naziv_ispis</option>
		</select>
		";
	}
	else {
		$html .= "<td align=\"right\" style='width:17%'>\n
		Opis:</td>\n
		<td  align=\"left\" style='width:17%'><input name=\"opis_lokacije\" value=\"$opis_lokacije_stetni_dogadjaj\" size=\"20\" height=\"15\"  onkeypress=\"return handleEnter(this, event)\" readonly=\"readonly\" class=\"readonlyPolja\">\n
		</td>\n";
	}
	
	$html .= "</td>
		<td align='right' style='width:17%'><label>Datum nastanka</label></td>
		<td style='width:17%'><input name='datumnast' id='datumnast' value='$datumnast' readonly></input></td>
		";
	
	if($regres_od == 'Krivac vlasnik vozila')
	{
		$jmbg_reg = $niz_pravna['jmbgpibkriv'];
		$fizpra = (strlen($jmbg_reg) == 13) ? 'F':'P' ;
		$prezime_reg = $niz_pravna['prezimekriv'];
		//$prezime_reg = $mesto_nastanka_id;
		$ime_reg = $niz_pravna['imenazivkriv'];
		//$ime_reg = $naziv_ispis;
		$adresa_reg = $niz_pravna['osiguranik_krivac_adresa'];
		$mesto_reg = $niz_pravna['osiguranik_krivac_mesto_id'];
		$zemlja_reg = $niz_pravna['osiguranik_krivac_zemlja_id'];
		$telefon_reg = $niz_pravna['osiguranik_krivac_telefon1'];
	}	
	else if($regres_od == 'Krivac vozaè vozila')
	{
		$jmbg_reg = $niz_pravna['jmbgvozkriv'];
		$fizpra = (strlen($jmbg_reg) == 13) ? 'F':'P' ;
		$prezime_reg = $niz_pravna['prezimevozkriv'];
		$ime_reg = $niz_pravna['imevozkriv'];
		$adresa_reg = $niz_pravna['vozac_krivac_adresa'];
		$mesto_reg = $niz_pravna['vozac_krivac_mesto_id'];
		$zemlja_reg = $niz_pravna['vozac_krivac_zemlja_id'];
		$telefon_reg = $niz_pravna['vozac_krivac_telefon1'];
	}
	else if($regres_od == 'Osiguravajuæe dru¹tvo')
	{
		$osiguravajuce_drustvo_id = $niz_pravna['osiguravajuce_drustvo_id'];
		if($osiguravajuce_drustvo_id != 26)
		{
			$sql_osiguravajuce_drustvo = "SELECT * FROM sifarnici.osiguravajuca_drustva WHERE id = $osiguravajuce_drustvo_id";
			$rezultat_osiguravajuce_drustvo = pg_query($conn_stete,$sql_osiguravajuce_drustvo);
			$niz_osiguravajuce_drustvo = pg_fetch_assoc($rezultat_osiguravajuce_drustvo);
			
			$jmbg_reg = $niz_osiguravajuce_drustvo['osiguravajuce_drustvo_pib'];
			$fizpra = 'P' ;
			$prezime_reg = null;
			$ime_reg = $niz_osiguravajuce_drustvo['osiguravajuce_drustvo_naziv'];
			$adresa_reg = NULL;
			$mesto_reg = NULL;
			$zemlja_reg = $niz_osiguravajuce_drustvo['zemlja_id'];
			$telefon_reg = NULL;
		}
		else 
		{
			$jmbg_reg = NULL;
			$fizpra = 'P' ;
			$prezime_reg = null;
			$ime_reg = $niz_pravna['osiguranjeregpotr'];
			$adresa_reg = NULL;
			$mesto_reg = NULL;
			$zemlja_reg_naziv = $niz_pravna['drzavaregpotr'];
			$telefon_reg = NULL;
			
			$sql_zemlja_osiguravajuce_drustvo = "SELECT * FROM sifarnici.zemlje_drzave WHERE naziv ILIKE '%$zemlja_reg_naziv%'";
			$rezultat_zemlja_osiguravajuce_drustvo = pg_query($conn_stete,$sql_zemlja_osiguravajuce_drustvo);
			$niz_zemlja_osiguravajuce_drustvo = pg_fetch_assoc($rezultat_zemlja_osiguravajuce_drustvo);
			$zemlja_reg = $niz_zemlja_osiguravajuce_drustvo['id'];
			
		}
	}
	
	$sql_podaci_mesto = " SELECT * FROM sifarnici.mesto_aktivna WHERE id=$mesto_reg";
	$upit_podaci_mesto = pg_query($conn_amso,$sql_podaci_mesto);
	$niz_podaci_mesto = pg_fetch_assoc($upit_podaci_mesto);
	$mesto_ispis_regresnog_duznika = $niz_podaci_mesto['naziv'];
	
	$mesto_reg_ispis = "<option value='$mesto_reg' selected>$mesto_ispis_regresnog_duznika</option>";
	
	$ret->jmbg_reg = mb_convert_encoding($jmbg_reg, 'utf-8', 'iso-8859-2');
	$ret->fizpra = mb_convert_encoding($fizpra, 'utf-8', 'iso-8859-2');
	$ret->prezime_reg = mb_convert_encoding($prezime_reg, 'utf-8', 'iso-8859-2');
	$ret->ime_reg = mb_convert_encoding($ime_reg, 'utf-8', 'iso-8859-2');
	$ret->adresa_reg = mb_convert_encoding($adresa_reg, 'utf-8', 'iso-8859-2');
	$ret->mesto_reg = mb_convert_encoding($mesto_reg_ispis, 'utf-8', 'iso-8859-2');
	$ret->zemlja_reg = mb_convert_encoding($zemlja_reg, 'utf-8', 'iso-8859-2');
	$ret->telefon_reg = mb_convert_encoding($telefon_reg, 'utf-8', 'iso-8859-2');
	
	
	$ret->mesto_nastanka_id = mb_convert_encoding($mesto_nastanka_id, 'utf-8', 'iso-8859-2');
	$ret->zemlja_nastanka_id = mb_convert_encoding($zemlja_nastanka_id, 'utf-8', 'iso-8859-2');
	$ret->novi_broj_predmeta = mb_convert_encoding($novi_broj_predmeta, 'utf-8', 'iso-8859-2');
	
	$ret->nalog = mb_convert_encoding($nalog, 'utf-8', 'iso-8859-2');
	$ret->iznos_isplate_mirni = mb_convert_encoding($iznos_isplate_mirni, 'utf-8', 'iso-8859-2');
	$ret->regres_od = mb_convert_encoding($regres_od, 'utf-8', 'iso-8859-2');
	
	$ret->html = mb_convert_encoding($html, 'utf-8', 'iso-8859-2');
	echo json_encode($ret);exit;
}

if(isset($_POST['funkcija']) && $_POST['funkcija'] == 'proveri_status_advokatske_kancelarije')
{
	proveri_status_advokatske_kancelarije();
}
function proveri_status_advokatske_kancelarije()
{
	$idregres = $_POST['idregres'];
	
	$conn_stete = pg_connect("host=localhost dbname=stete user=zoranp");

	$sql = "SELECT * FROM advokat WHERE idregres=$idregres ORDER BY idadv DESC LIMIT 1;";
	$rezultat = pg_query($conn_stete,$sql);
	$niz = pg_fetch_assoc($rezultat);
	$datum_razduzenja = $niz['datum_razduzenja'];
	$idadv= $niz['idadv'];
	
	if($datum_razduzenja || !$idadv)
	{
		$flag = true;
	}
	else 
	{
		$flag = false;
	}
	
	$ret = new stdClass();
	
	$ret->sql = mb_convert_encoding($sql, 'utf-8', 'iso-8859-2');
	$ret->flag = $flag;
	echo json_encode($ret);exit;
}

//DODAO VLADA

//AKO JE FUNKCIJA U POSTU DOHVATI PODATKE REGRES,POZOVI JE I PROSLEDI ID STETE
if(isset($_POST['funkcija']) && $_POST['funkcija'] == 'dohvati_podatke_regres')
{	
	dohvati_podatke_regres($_POST['id_stete'], $_POST['id_regresa']);
}

//FUNKCIJA ZA GENERISANJE PODATAKA O REGRESU,NA OSNOVU ID-JA STETE.DRUGI PARAMETAR SLUZI KADA POSTOJI SAMO ID REGRESA
function dohvati_podatke_regres($id_stete, $id_regresa) {

	//KREIRANJE KONEKCIJE KA BAZI STETE
	$konekcija_stete = pg_connect("host=localhost dbname=stete user=zoranp");

	//AKO DODJE DO GRESKE U KONEKCIJI
	if (!$konekcija_stete) {

		echo json_encode('Gre¹ka otvaranja konekcije prema SQL serveru.');
		die();
	}

	//UPIT ZA SETOVANJE CLIENT ENKODINGA
	$upit_enkoding = "set client_encoding='utf8'";

	//IZVRSAVANJE UPITA
	$rezultat = pg_query($konekcija_stete, $upit_enkoding);

	//AKO NE POSTOJI ID STETE,A POSTOJI ID REGRESA
	if ($id_stete == '') {

		//UPIT ZA DOBIJANJE ID-JA STETE,NA OSNOVU ID-JA REGRESA
		$upit_id_stete = "SELECT idstete FROM steta_regres WHERE idregres = $id_regresa"; 

		//IZVRSAVANJE UPITA
		$rezultat_id = pg_query($konekcija_stete, $upit_id_stete);

		//AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA,OBAVESTI KORISNIKA I PREKINI IZVRSAVANJE SKRIPTE
		if (!$rezultat_id) {

			echo json_encode('Gre¹ka pri izvr¹avanju upita. Poku¹ajte ponovo.' .pg_last_error($konekcija_stete));
			die();
		}

		else {
			//UPIS PODATAKA IZ BAZE U NIZ I DOBIJANJE ID-JA STETE
			$niz_id = pg_fetch_array($rezultat_id);
			$id_stete = $niz_id['idstete'];
		}
	}
	

	//UPIT ZA DOBIJANJE SVIH PODATAKA ZA REGRESNU FORMU, NA OSNOVU ID-JA STETE
	$upit_podaci_regres = 
	
	"SELECT poz.novi_broj_predmeta, poz.id, 
			
			sd.vrsta_obrasca, sd.broj_polise,

			p.regres_od, p.tip_lica, p.jmbg_pib, p.ime_reg, p.prezime_reg, p.osiguranjeregpotr, p.telefon_reg,
			
			p.drzava_reg_id, p.opstina_reg_id, p.mesto_reg_id, p.adresa_reg, p.koliko_potrazivati 

	FROM predmet_odstetnog_zahteva AS poz

	INNER JOIN odstetni_zahtev AS oz ON oz.id = poz.odstetni_zahtev_id

	INNER JOIN stetni_dogadjaj AS sd ON sd.id = oz.stetni_dogadjaj_id

	INNER JOIN pravni AS p ON p.idstete = poz.id

	WHERE poz.id = $id_stete";
	

	//IZVRSAVANJE UPITA
	$rezultat_podaci = pg_query($konekcija_stete, $upit_podaci_regres);

	//AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA,OBAVESTI KORISNIKA I PREKINI IZVRSAVANJE SKRIPTE
	if (!$rezultat_podaci) {

        echo json_encode('GreÅ¡ka pri izvrÅ¡avanju upita. PokuÅ¡ajte ponovo.' .pg_last_error($konekcija_stete));
        die();
	}


	//AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat_podaci) > 0) {

		$ret = new stdClass();

		//UPIS PODATAKA IZ BAZE U NIZ
		$niz_podaci = pg_fetch_array($rezultat_podaci);
		
	
		//UPIS PODATAKA U PROMENJIVE
		$regres_od = $niz_podaci['regres_od'];
		$broj_polise = $niz_podaci['broj_polise'];
		$vrsta_obrasca =  $niz_podaci['vrsta_obrasca'];
		$novi_broj_predmeta = $niz_podaci['novi_broj_predmeta'];
		$id_stete = $niz_podaci['id'];
		$tip_lica = $niz_podaci['tip_lica'];

		$zemlja_id = $niz_podaci['drzava_reg_id'];
		$koliko_potrazivati = $niz_podaci['koliko_potrazivati'];

		$osiguranje_reg = $niz_podaci['osiguranjeregpotr'];

		$jmbg_reg = $niz_podaci['jmbg_pib'];
		$ime_reg = $niz_podaci['ime_reg'];
		$prezime_reg = $niz_podaci['prezime_reg'];
		$telefon_reg = $niz_podaci['telefon_reg'];
		$adresa_reg = $niz_podaci['adresa_reg'];

		$opstina_id = $niz_podaci['opstina_reg_id'];
		$mesto_id = $niz_podaci['mesto_reg_id'];
		

		//AKO POSTOJI PREDMET,NA OSNOVU ID-JA STETE
		if($niz_podaci) {

			//UPIT ZA DOBIJANJE POVEZANIH REGRESA
			$upit_povezani_regresi = "

			WITH tabela_predmeta AS
			(

			SELECT poz.id AS predmet_id, poz.novi_broj_predmeta FROM stetni_dogadjaj AS sd

			INNER JOIN odstetni_zahtev AS oz ON oz.stetni_dogadjaj_id = sd.id

			INNER JOIN predmet_odstetnog_zahteva AS poz ON  poz.odstetni_zahtev_id=oz.id

			WHERE broj_polise = $broj_polise AND vrsta_obrasca = '$vrsta_obrasca'

			), 

			tabela_steta_regres AS 
			(

			SELECT sr.idregres,idstete,brreg, potrazivanje, adv.advokatska_kancelarija_id, adv.datumang, adv.datum_razduzenja, ak.naziv AS advokatska_kancelarija FROM steta_regres AS sr
			
			INNER JOIN regresna AS reg ON sr.idregres = reg.idregres

			LEFT OUTER JOIN advokat AS adv ON adv.idregres=sr.idregres
			
			LEFT OUTER JOIN dblink('host=localhost dbname=amso user=zoranp', 'SELECT id, naziv FROM sifarnici.advokatske_kancelarije ') AS ak (id integer, naziv text) ON ak.id = adv.advokatska_kancelarija_id
			
			)

			SELECT DISTINCT ON (tabela_steta_regres.idregres)

			tabela_steta_regres.idregres, tabela_steta_regres.brreg, tabela_steta_regres.potrazivanje,tabela_predmeta.novi_broj_predmeta,

			tabela_steta_regres.advokatska_kancelarija_id, tabela_steta_regres.datum_razduzenja, tabela_steta_regres.datumang ,  tabela_steta_regres.advokatska_kancelarija
			
			FROM tabela_predmeta, tabela_steta_regres
			
			WHERE tabela_predmeta.predmet_id = tabela_steta_regres.idstete 
			
			ORDER BY tabela_steta_regres.idregres, tabela_steta_regres.datum_razduzenja DESC, tabela_steta_regres.datumang DESC";


			//IZVRSAVANJE UPITA
			$rezultat_povezani_regresi = pg_query($konekcija_stete, $upit_povezani_regresi);

			//AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA,OBAVESTI KORISNIKA I PREKINI IZVRSAVANJE SKRIPTE
			if(!$rezultat_povezani_regresi) {

				echo json_encode('GreÅ¡ka pri izvrÅ¡avanju upita. PokuÅ¡ajte ponovo.');
				die();
			}

			//KREIRANJE NIZA SA POVEZANIM REGRESIMA
			$niz_povezani_regresi = pg_fetch_all($rezultat_povezani_regresi);
			$ukupan_broj_regresa = count($niz_povezani_regresi);

			//AKO POSTOJE POVEZANI REGRESI ZA ODREDJENU POLISU
			if($niz_povezani_regresi)
			{	
				//KREIRANJE TABELE SA POVEZANIM REGRESIMA
				$povezani_regresi = "<table border='2' style='border-collapse: collapse;' width='750px'>
										<tr style='background-color: #DDEEEE;'>
											<td width='250px;' align='center'>Povezan regres</td>
											<td width='250px;' align='center'>Potrazivanje</td>
											<td width='250px;' align='center'>Advokatska kancelarija</td>
										</tr>";
				
				//PROLAZAK KROZ NIZ SA REGRESIMA I GENERISANJE REDOVA U TABELI
				for($i = 0; $i < $ukupan_broj_regresa; $i++)
				{
					$boja_reda_regesa = ($k%2 == 0) ? '' : '#BBCCCC';
					$id_povezanog_regresa = $niz_povezani_regresi[$i]['idregres'];
					$broj_povezanog_regresa = $niz_povezani_regresi[$i]['brreg'];
					$broj_predmeta_povezanog_regresa = $niz_povezani_regresi[$i]['novi_broj_predmeta'];
					$potrazivanje_povezanog_regresa = $niz_povezani_regresi[$i]['potrazivanje'];
					$advokat_povezanog_regresa = $niz_povezani_regresi[$i]['advokatska_kancelarija'];

					$povezani_regresi .= "<tr style='background-color: $boja_reda_regesa;'>
											<td width='250px;' align='center'><a href='regresna.php?idregres=$id_povezanog_regresa&&status=izmeni'>$broj_povezanog_regresa</a></td>
											<td width='250px;' align='center'>$potrazivanje_povezanog_regresa</td>
											<td width='250px;' align='center'>$advokat_povezanog_regresa</td>
										</tr>";
				}
				
				//ZATVARANJE TABELE
				$povezani_regresi .= "</table>";
				
			}
			//AKO NE POSTOJE POVEZANI REGRESI ZA ODREDJENU POLISU
			else 
			{
				$povezani_regresi = "";
			}
		
		}

		
		//DODAVANJE PODATAKA O REGRESU U OBJEKAT ZA SLANJE
		$ret->regres_od = $regres_od;
		$ret->broj_polise = $broj_polise;
		$ret->vrsta_obrasca = $vrsta_obrasca;
		$ret->id_stete = $id_stete;
		$ret->novi_broj_predmeta = $novi_broj_predmeta;
		$ret->tip_lica = $tip_lica;
		$ret->jmbg_reg = $jmbg_reg;
		$ret->ime_reg = $ime_reg;
		$ret->osiguranje_reg = $osiguranje_reg;
		$ret->prezime_reg = $prezime_reg;
		$ret->telefon_reg = $telefon_reg;
		$ret->adresa_reg = $adresa_reg;
		$ret->osiguranje_reg = $osiguranje_reg;

		$ret->zemlja_id = $zemlja_id;
		$ret->id_mesta = $mesto_id;
		$ret->id_opstine = $opstina_id;
		$ret->koliko_potrazivati = $koliko_potrazivati;

		$ret->povezani_regresi = $povezani_regresi;

		echo json_encode($ret);
		die();
	}

	echo json_encode('Nema rezultata za zadati upit.');
	die();
}
?>
