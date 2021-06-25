<?php
if (!isset($uliniji)) {
	foreach ($_POST as $kljuc => $vrednost) {
		${$kljuc} = $vrednost;
	}

$uliniji = 0;

$conn = pg_connect('dbname=amso user=zoranp');
if (!$conn) {
	echo "Greçka otvaranja konekcije prema SQL serveru.";
	exit;
	}

$upit = "SET client_encoding TO 'UTF8'";
$result=pg_query($conn, $upit);


}

date_default_timezone_set ('CET');

	$reg_zahtev = $brReg;


function kreirajUpit() {
	$konta = "'2123', '2122', '2124', '2120310', '2120311', '2120312', '2120320', '2120321', '2120322', '2121010', '2121011', '2121012', '2121020', '2121021', '2121022','041010','041003'";
	$danod = '01.01.2003';
	$dando = date('d.m.Y');
	$j = substr($danod,-4);
	$k = date('Y') + 1;
	$upit = "SELECT datknjiz AS dospeva, opisdok, brojdok, konto, duguje, potrazuje FROM g" . $j . " WHERE konto IN ($konta) AND extract(year FROM datknjiz) = " . $j++ . " AND vrstadok NOT IN ('PS', 'ZK')";
	for ($i = $j; $i < $k; $i++) {
		if ($i < 2006) {
			$upit .= " UNION ALL SELECT datknjiz AS dospeva, opisdok, brojdok, konto, duguje, potrazuje FROM g" . $i . " WHERE konto IN ($konta) AND extract(year FROM datknjiz) = $i AND vrstadok NOT IN ('PS', 'ZK')";
		}
		else {
			$upit .= " UNION ALL SELECT  CASE WHEN duguje notnull THEN dospeva ELSE datknjiz END AS dospeva, opisdok, brojdok, konto, duguje, potrazuje FROM g" . $i . " WHERE konto IN ($konta) AND extract(year FROM datknjiz) = $i AND vrstadok NOT IN ('PS', 'MR', 'ZK')";
		}
	}
	return $upit;
}

function zaPrikaz($conn, $reg_zahtev) {
	$i = 1;
	preg_match('/R\-(\d+)\/(\d+)/', $reg_zahtev, $matches);
	$upit = kreirajUpit();
	$upit = "SELECT to_char(dospeva, 'DD.MM.YYYY.') AS datnaloga, dospeva, rzahtev, opisdok, dugovni, potrazni, saldo, ispisi FROM (SELECT dospeva, substring(brojdok FROM E'(R\\\\-\\\\d+\\\\/\\\\d+)') AS rzahtev, opisdok, duguje AS dugovni, sum(duguje) OVER (PARTITION BY konto, dospeva) AS treba, potrazuje AS potrazni, sum(CASE WHEN konto IN ('2123', '2124') OR konto ~ E'[12]$' THEN duguje-potrazuje ELSE 0 END) OVER (ORDER BY dospeva) AS saldo, CASE WHEN dospeva > current_date THEN 0 ELSE 1 END AS ispisi FROM ($upit) AS bar WHERE substring(brojdok FROM E'R\\\\-(\\\\d+)\\\\/')::integer = " . $matches[1] . " AND substring(brojdok FROM E'\\\\/(\\\\d+)')::integer = " . $matches[2] . ") AS foo WHERE treba + potrazni != 0 ORDER BY dospeva, dugovni DESC";
//	echo $upit . "\n";
	$rezultat = pg_query($conn, $upit);
	$style = '#DDEEEE';
	while ($arr = pg_fetch_assoc($rezultat)) {
	foreach ($arr as $kljuc => $vrednost) {
		${$kljuc} = $vrednost;
	}
	$dugovni = number_format($dugovni, 2, ',', '.');
	$potrazni = number_format($potrazni, 2, ',', '.');
	$saldo = $ispisi ? number_format($saldo, 2, ',', '.') : '&nbsp;';
	$style = $style == '#F2F4F9' ? '#DDEEEE' : '#F2F4F9';
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
}

echo "<table width=\"1000px\" border=\"2\" cellspacing=\"0\" align=\"center\">";
echo "<tr bgcolor=\"#BBCCCC\">";
echo "<td align=\"center\"><b>Red.br.</b></td>\n";
echo "<td align=\"center\"><b>Dospeva</b></td>\n";
echo "<td align=\"center\"><b>Opis dokumenta</b></td>\n";
echo "<td align=\"center\"><b>Zaduzenje</b></td>\n";
echo "<td align=\"center\"><b>Uplata</b></td>\n";
echo "<td align=\"center\"><b>Stanje duga na dan</b></td>\n";
echo "</tr>\n";

if (!$uliniji) {
	zaPrikaz($conn, $reg_zahtev);
}
else {
	zaPrikaz($conn1, $reg_zahtev);
}


echo "</table>\n";


if (!$uliniji) {
	pg_close($conn);
//	pg_close($conn1);
}

?>
