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

$conn = pg_connect ("host=localhost dbname=stete user=zoranp");
if (!$conn) {
	echo "Gre¹ka otvaranja konekcije prema SQL serveru.";
	exit;
	}

$conn1 = pg_connect ("host=localhost dbname=amso user=zoranp");
if (!$conn1) {
    echo "<br><br>Do¹lo je do gre¹ke prilikom konektovanja.\n";
    exit;
}



// Parametri
// $zakljucano='2015-10-01';
//echo "PRIKAYI";
require_once ('../../common/zakljucano.php');


//ajax promenljive
$rate_datumi = $_POST['datumi_rata'];
$rate_iznosi = $_POST['iznosi_rata'];
$idregres = $_POST['idregres'];
$dokument_vrsta = $_POST['dokument'];
$dokument_broj = $_POST['dokument_broj'];
$dokument_datum = $_POST['dokument_datum'];
$jmbg_reg = $_POST['jmbg_reg'];
$duznik = $_POST['prezime_reg']." ".$_POST['ime_reg'];
$idregres = $_POST['id_regresa'];
$brReg = $_POST['broj_regresa'];
$brojst = $_POST['broj_stete'];
$adresa_reg = $_POST['adresa_reg'];
$telefon_reg = $_POST['telefon_reg'];
$broj_rata=$_POST['broj_rata'];

$da = 1;

$sql = "SET client_encoding TO 'UTF8'";
$rezultat=pg_query($conn1,$sql);
$rezultat=pg_query($conn,$sql);


$sql="begin;";
$rezultat=pg_query($conn1,$sql);
$rezultat=pg_query($conn,$sql);


// A®URIRANJE REGRESNE SA PODACIMA IZ DOKUMENTA ZA KNJI®ENJE

$sql ="update regresna set dokument_vrsta='$dokument_vrsta', dokument_broj='$dokument_broj', dokument_datum='$dokument_datum', brrata=$broj_rata, radnik=$radnik, jmbg_reg='$jmbg_reg', dana=current_date, vreme=current_time ";
$sql.="   where idregres=$idregres";


$rezultat=pg_query($conn,$sql);

if(!$rezultat) {
	/*
	echo json_encode('Gre¹ka'.pg_last_error($sql));
	die();
	*/
	$da = 0;
}

/*
if (!$rezultat) {
	$da = 0;
}
*/

if ($da) {

	$kontrola = strlen($jmbg_reg);
	switch ($kontrola) {
		case 9:
			break;
		case 13:
			break;
		default:
			$da = 0;
			break;
	}

// provera u partneru
$sql1="select sifra from partneri where sifra='$jmbg_reg'";

$rezultat1=pg_query($conn1,$sql1);

$niz1=pg_fetch_assoc($rezultat1);
$sifraf=$niz1['sifra'];

if (!$sifraf && $da){

$sql="insert into partneri (sifra,naziv,adresa,posbroj,mesto,telefon) values ('$jmbg_reg','$duznik',";
if ($adresa_reg){$sql.="SUBSTRING('$adresa_reg' FROM 1 FOR 30),";}
else{$sql.="null,";}
if ($posbroj){$sql.="$posbroj,";}
else{$sql.="1,";}
if ($mesto_reg){$sql.="SUBSTRING('$mesto_reg' FROM 1 FOR 20),";}
else{$sql.="null,";}
if ($telefon_reg){$sql.="SUBSTRING('$telefon_reg' FROM 1 FOR 50))";}
else{$sql.="null)";}

//echo $sql;

$rezultat1=pg_query($conn1,$sql);


if (!$rezultat1) {
	$da = 0;
	/*
	echo json_encode('Gre¹ka'.pg_last_error($sql));
	die();
	*/
}

}

/*
$dokument_knjiz = $dokument_datum < $zakljucano ? $zakljucano : $dokument_datum;
*/


$dokument_knjiz = proveriZDatum ('REG', $dokument_datum, $conn1);

$godina = substr($dokument_knjiz, 0, 4);


$vrstadok="RZ";
$ppsi="PP";
$brojdok=$brReg;
$brrata = $broj_rata;
$ff='F';
$mnt ="111000";

$partner=$jmbg_reg;

if (substr($brojst, 0, 2) == 'SP') {

	preg_match('/(SP\-\d+\/\d+)/', $brojst, $matches);
	if (!$matches[0]) {
	preg_match('/(SP\-[AKOZ]{2,2}\-\d+\/\d+)/', $brojst, $matches);
	if (!$matches[0]) {
		$da = 0;
	}
	}
	if ($da) {

		$pitanje = "SELECT * FROM sudski_postupak WHERE brsp = '" . $matches[1] . "'";
		$rez = pg_query($conn, $pitanje);
		$niz = pg_fetch_assoc($rez);
		$konto = substr($niz['sifra'], 0, 2);
		switch ($konto) {
			case '03':
				$konto = '212' . $konto;
				break;
			case '10':
				$konto = '212' . $konto;
				break;
			default:
				$da = 0;
				break;
			}
	}
}
else {
	$konto = (substr($brojst,0,2)) == 'AK' ? '21203' : '21210';
}

$konto .= strlen($jmbg_reg) == 13 ? '2' : '1';
$konto .= $brrata == 1 ? '2' : '0';
$konto_d = substr($konto, 0, 6) . '1';

for ($i = 0; $i < $brrata; $i++) {

$rbr=$i+1;

if ($brrata==1) {
	$text = 'u celosti';
}
else {
	$text = $rbr . ". rata";
}

$opisdok=$prezime_reg . " " . $ime_reg . " " . $brojst  . ' ' . $text;

$rate_iznosi[$i] = str_replace(" ", "", $rate_iznosi[$i]);
$iznosobr += $rate_iznosi[$i];
$brdok=sprintf($idregres, "%6d");


$sql="insert into reg_rate (idregres, brrate, iznos, dospeva,uplata,datum_uplate, radnik, dana, vreme)  values ($idregres, $rbr, $rate_iznosi[$i], '$rate_datumi[$i]', null,null,$radnik, current_date, current_time)";



$rezultat2=pg_query($conn,$sql);

if (!$rezultat2) {
	$da = 0;
	break;
}

//echo "reg_rate " . $sql;
//echo '<br>kotno ' . $konto;

$sql="insert into g" . $godina . " (datknjiz, vrstadok, brdok, ff, partner, ppsi, opisdok, brojdok, datdok, dospeva, duguje, potrazuje, opetnalog, konto,  mnt, radnik, knjizdana, vremknjiz) values ('$dokument_knjiz', '$vrstadok', '$brdok', '$ff', '$partner', '$ppsi', upper('$opisdok'), '$brojdok', '$dokument_datum'::date, '" . $rate_datumi[$i] . "'::date , " . $rate_iznosi[$i] . ", 0, '$brdok', '$konto', '$mnt', $radnik, current_date, current_time)";
$rezultat3=pg_query($conn1,$sql);

if (!$rezultat3) {
	$da = 0;
	break;
}

if ($brrata == 1) {
	break;
}

//echo "glavne " . $sql;

if ($rate_datumi[$i] <= date("Y-m-d")  ) {

$sql="insert into g" . $godina . " (datknjiz, vrstadok, brdok, ff, partner, ppsi, opisdok, brojdok, datdok, dospeva, duguje, potrazuje, opetnalog, konto,  mnt, radnik, knjizdana, vremknjiz) values ('$dokument_knjiz'::date, 'ID', to_char('$dokument_datum'::date, 'YYMMDD'), '$ff', '$partner', '$ppsi', upper('$opisdok'), '$brojdok', '$dokument_datum'::date, '" . $rate_datumi[$i] ."'::date , -($rate_iznosi[$i]), 0, to_char('$dokument_datum'::date, 'YYMMDD'), '$konto', '$mnt', $radnik, current_date, current_time)";
$rezultat3=pg_query($conn1,$sql);

if (!$rezultat3) {
	$da = 0;
	break;
}

//ZASTO SE OVO PONAVLJA DVA PUTA
$sql="insert into g" . $godina . " (datknjiz, vrstadok, brdok,ff, partner, ppsi, opisdok, brojdok, datdok, dospeva, duguje, potrazuje, opetnalog, konto,  mnt, radnik, knjizdana, vremknjiz) values ('$dokument_knjiz'::date, 'ID', to_char('$dokument_datum'::date, 'YYMMDD'), '$ff', '$partner', '$ppsi', upper('$opisdok'), '$brojdok', '$dokument_datum'::date, '" . $rate_datumi[$i] ."'::date , ". $rate_iznosi[$i] . ", 0, to_char('$dokument_datum'::date, 'YYMMDD'), '$konto_d', '$mnt', $radnik, current_date, current_time)";

$rezultat3=pg_query($conn1,$sql);

if (!$rezultat3) {
	$da = 0;
	break;
}


}

}

// KNJI®ENJE 6
$konto = substr($brojst,0,2) == 'AK' ? "65203" : "65210";


$ppsi="SI";
$potrazuje=$iznosobr;
$opisdok = $prezime_reg . " " . $ime_reg . " " . $brojst;


$sql="insert into g" . $godina . " (datknjiz, vrstadok, brdok,ff, partner, ppsi, opisdok, brojdok, datdok, dospeva, duguje, potrazuje, opetnalog, konto, mnt, radnik, knjizdana, vremknjiz) values ('$dokument_knjiz', '$vrstadok', '$brdok', '$ff', null, '$ppsi',  upper('$opisdok'), '$brojdok', '$dokument_datum'::date,'$dokument_datum'::date, 0, $potrazuje, '$brdok', '$konto','$mnt', $radnik, current_date, current_time)";
$rezultat4=pg_query($conn1,$sql);

if (!$rezultat4) {
	$da = 0;
}

}

$rezul = 0;

if ( $da ) {
$sql="commit;";
$rezultat=pg_query($conn,$sql);
$rezultat=pg_query($conn1,$sql);
// $provera = pg_num_rows($rezultat);
$rezul=1;
}
else {
$sql="rollback;";
$rezultat=pg_query($conn,$sql);
$rezultat=pg_query($conn1,$sql);
}

$lista['rezul'] = $rezul;
echo json_encode($lista);

pg_close($conn);
?>
