<?php

if (!isset($uliniji1)) {
	foreach ($_POST as $kljuc => $vrednost) {
		${$kljuc} = $vrednost;
	}

$uliniji1 = 0;

$conn = pg_connect('dbname=stete user=zoranp');
if (!$conn) {
	echo "Greçka otvaranja konekcije prema SQL serveru.";
	exit;
	}

$upit = "SET client_encoding TO 'UTF8'";
$result=pg_query($conn, $upit);


}

date_default_timezone_set ('CET');
/*
$procena_datum = $_POST['procena_datum'];
$procena_iznos = $_POST['procena_iznos'];
$koeficijent = $_POST['koeficijent'];
$datum1 = $_POST['datum1'];
*/
//echo "Datum prenos=".$datum;
$reg_zahtev = $brReg;
//echo "Br reg=".$brReg;

$sql2="select idregres from regresna where brreg='$reg_zahtev' ";
$rezultat2=pg_query($conn,$sql2);
$niz2 = pg_fetch_assoc($rezultat2);
$idregres= $niz2['idregres'];

//function zaPrikaz($conn, $reg_zahtev) {

$upit = "SELECT * from procena_regres where idregres=$idregres order by id desc ";
//echo $upit ;

$rezultat = pg_query($conn, $upit);
$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);
//for ($a=0; $a < $redova; $a++) {
/*while ($arr2 = pg_fetch_assoc($rezultat)) {

  foreach ($arr2 as $kljuc => $vrednost) {
		${$kljuc} = $vrednost;
	}
*/
$arr2 = pg_fetch_assoc($rezultat);
$procena_datum = $arr2['procena_datum'];
$procena_iznos = $arr2['procena_iznos'];
$koeficijent = $arr2['koeficijent_izvesnosti']; 
//$procena_datum = $arr2['procena_datum'];
//$koeficijent = $arr2['koeficijent'] ? $arr2['koeficijent'] : '99';;

//$procena_iznos = number_format($procena_iznos, 2, ',', '.');
//$koeficijent = number_format($koeficijent_izvesnosti, 0, ',', '.');

if ($procena_datum){
/*
echo "<table align=\"center\"><tr>";
echo "<td align=\"right\">";
echo "Datum procene:";
echo "</td><td>";
*/
$rezultat = pg_query($conn, $upit);


echo "<label for='datum1' style='padding-left:260px; width:100px;'>Datum procene: </label>";
echo "<select id=\"datum1\" name=\"datum1\"  onchange=\"koeficijentPoDatumu(this.value)\" >\n";
  while ($arr3 = pg_fetch_assoc($rezultat))
  {
  
//     $procena_iznos = $arr3['procena__iznos'];
	   $procena_datum1 = $arr3['procena_datum'];
//	   $koeficijent = $arr3['koeficijent_izvesnosti']; 
	   echo "<option value=\"" . $arr3['procena_datum'] . "\"";
	   if ($datum1 == $procena_datum1) 
	   { 
				echo " SELECTED "; 
		 }
		 echo ">"  . $procena_datum1 .  "</option>";
		
		 
}
//echo "<option value= >proba</option>";
echo "</select>\n";

//echo "</td>\n";

//echo "<td colspan='4'>";
/*
echo "<td align=\"right\">";
echo "Iznos procene:";
echo"</td><td>";
*/
echo "<label for='procena_iz' style='padding-left:90px; width:85px;'>Iznos procene:</label>";
echo "<input type=\"text\" readonly=\"readonly\" id=\"procena_iz\" name=\"procena_iz\" value=\"$procena_iznos\" class=\"readonlyPolja\" style='margin-left:4px;width:180px;height:23px;'/>";
/*
echo "<td align=\"right\">";
echo "Koeficijent izvesnosti naplate:";
echo "</td><td>";
*/
echo "<label for='koef1' style='padding-left:5px; width:100px;'>Koeficijent izvesnosti naplate:</label>";
echo "<input type=\"text\" readonly=\"readonly\" id=\"koef1\" name=\"koef1\" value=\"$koeficijent\" class=\"readonlyPolja\" style='margin-left:6px;width:100px;height:23px;'/>";
//echo "</tr></table>";

}  

//}
//echo "<tr><td colspan=\"6\">&nbsp;</td></tr>\n";

if (!$uliniji1) {
//	pg_close($conn);
	pg_close($conn);
}

?>