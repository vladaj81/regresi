<?php

$conn = pg_connect ("dbname=stete user=zoranp");
if (!$conn) {
	echo "Gre¹ka otvaranja konekcije prema SQL serveru.";
	exit;
	}

//ajax promenljive
$procena_datum = $_POST['procena_datum'];
$procena_iznos = $_POST['procena_iznos'];
$koeficijent = $_POST['koeficijent'];
$radnik = $_POST['radnik'];
//$idregres = $_POST['idregres'];

$da = 1;

//$sql = "SET client_encoding TO 'UTF8'";
//$rezultat=pg_query($conn,$sql);
	
//$dando = date('d.m.Y');
$sql="commit;";
$rezultat=pg_query($conn,$sql);

$sql2="select idregres,brojst from regresna where brreg='$brReg'";

$rezultat2=pg_query($conn,$sql2);
$niz2 = pg_fetch_assoc($rezultat2);
$idregres= $niz2['idregres'];

$procena_iznos = str_replace(" ", "", $procena_iznos);

$sql="insert into procena_regres (idregres, procena_datum, procena_iznos, koeficijent_izvesnosti, radnik, dana, vreme)  values ($idregres, '$procena_datum', $procena_iznos, $koeficijent, $radnik, current_date, current_time)";
//echo $sql;


$rezultat=pg_query($conn,$sql);

if (!$rezultat) {
	$da = 0;
}

$rezul = 0;

if ( $da ) {
$rezul = 1;
}

$lista['rezul'] = $rezul;
echo json_encode($lista);

pg_close($conn);
?>