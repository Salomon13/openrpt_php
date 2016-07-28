<?php /* Turn a xml made with openrpt into php
Instance: report.xml -> report.php
	you get report.php by typing in terminal:
 php aPDF.php report.xml >> report.php
*/
require_once "aPHP.php";
if (count($argv) == 2) $sA = $argv[1]; // xml
else die("ERROR IN ARGUMENTS\n");
$sP = "pXA";
if (!file_exists($sA)) die("FILE NOT FOUND\n");
if (substr($sA,-4) != ".xml") die("FILE NOT A XML\n");
// open txt model
$lsT = array(); // it has all lines php from template
$dX = array(); // it has labels as keys and a php instruction as value
$iL = -1;
$b = True; // Separate labels
$sTXT = fopen($sP.".php", "r");
while (($s = fgets($sTXT, 4096)) !== false) {
	$s = trim($s);
	if (empty($s)) {
		$b = False;
		continue;
	}
	$s1 = substr($s,0,1);
	if ($s1 == "#") continue;
	if ($s1 == "[") {
		$sE = substr($s,1);
		$i = strpos($sE, "]");
		$s3 = trim(substr($sE, $i + 1)); // instruction
		$s2 = substr($sE, 0, $i); // labels echo $s1.":".$s2.":".$s3;
		$dX[$s2] = $s3;
		if (isset($lsT[$iL]) and substr($lsT[$iL], 0, 1) == "#") $lsT[$iL] .= "$s2,";
		else {
			$iL++;
			$b = True;
			$lsT[$iL] = "#,$s2,"; // Ej: #,lefmargin,rigthmargin,rpthead label rect x,
		}
		continue;
	}
	$iL++;
	$b = True;
	$lsT[$iL] = $s;
}
if (!feof($sTXT)) die("Error: unexpected fgets() fail\n");
fclose($sTXT);// print_r($dX);
//foreach ($lsT as $s) echo $s."\n";
// Open xml y turns the content of labels into instructions, where:
//(@@=$) (++=label content) (|=") (::=unite same lines)
$sFile = fopen($sA, "r"); //$sZ = substr($sA,0,-3)."pdf"; //$sFZ = fopen($sA, "r");
if (!$sFile) die("ERROR DE LECTURA\n");
$sEE = ""; // labels in process Ej: ' rpthead label rect'
$ls = array(); // instructions
$lsE = array(); // labels with contents
$lsP = array(); // parameters Ej: active=true type=string default=2016-06-27 name=Fecha
$iPP = 0;
$iL = -1;
$bGG = False; // there are groups
$bQ = False; $sQ = ""; // multiline content, only for sql
while (($s = fgets($sFile, 4096)) !== false) {
	$iL++;
	if ($iL < 2) continue;
	$s = trim($s);
	if ($bQ) {
		$sQ .= " ".$s;
		if (substr($s,-6) == "</sql>") {
			$sC = sqlP(substr($sQ, 0, -6), $dP, $iPP); // MetaSql, parameters, num. of pa.
			$lsQ = explode(";", $sC);
			foreach ($lsQ as $s) {
				$ls[] = $s.";";
				$lsE[] = "sql";
			}
			$bQ = False; $sQ = "";
		}
		continue;
	}
	$i = strpos($s, ">");
	$sE = substr($s, 1, $i - 1); // single label Ej: rpthead o /rpthead
	if (substr($sE, -1) == "/") continue; // no init Ej: portrait/
	if (substr($sE,0,9) == "parameter") { // Excep, content inside label
		$s = substr($s, 10, -1); // it takes out the content
		$lsP[$iPP++] = str_replace('"',"",$s);
		$sE = "parameter";
	} 
	$sC = substr($s, $i + 1); // sC: label content including label that closes
	if (empty($sC)) { // if no sC it adds or removes label
		if (substr($sE, 0, 1) == "/") {
			$sE = substr($sE, 1);
			if (strpos($sEE, $sE)) {  // remove label
				$sEE = str_replace(" $sE", "", $sEE);
				if ($sE == "section") { // Excep, close loop
					for ($i = 0; $i < 2; $i++) { // Excep, section needs space
						$ls[] = ""; $lsE[] = "";
					}
					$ls[] = "} // end section";
					$lsE[] = "";
				}
			}
		} else {
			if ($sE == "section") {
				for ($i = 0; $i < 3; $i++) { // Excep, section needs space
					$ls[] = ""; $lsE[] = "";
				}
			}
			$sEE .= " $sE"; // add label
		}
		continue;
	}
	if (substr($sC,0,2) == "</") continue; // no sC Ex:<etc></etc>
	$s = ltrim($sEE." $sE");
	if ($sE == "height" and strpos($sEE,"group")) { // head/foot
		for ($i = 0; $i < 3; $i++) { // Excep, group needs space
			$ls[] = ""; $lsE[] = "";
		}
		$bGG = True;
	}
	if ($s == "parameter description") { // Excep, sC joins the parameter
		$lsP[$iPP - 1] .= " D=".substr(str_replace(" ","_",$sC), 0, -14);
	} else if ($s == "querysource sql") {
		if ($iPP > 0 and !isset($dP)) $dP = creaD($lsP); // parameters are set in dP
		else $dP = array();
		if (substr($sC,-6) == "</sql>") {
			$sC = sqlP(substr($sC, 0, -6), $dP, $iPP); // MetaSql, parameters, num. of pa.
			$lsQ = explode(";", $sC);
			foreach ($lsQ as $s) {
				$ls[] = $s.";";
				$lsE[] = "sql";
			}
		} else {
			$bQ = True;
			$sQ = $sC;
		}
	} else if (isset($dX[$s])) {
		$sC = substr($sC, 0, -(strlen($sE) + 3));
		$ls[] = str_replace("++", $sC, $dX[$s]); // replace sC in instruction/function
		$lsE[] = $s; // it gets label Ex: 'rpthead rect x'
	}
}
if (!feof($sFile)) die("Error: unexpected fgets() fail\n");
fclose($sFile);
//
$ls = aPHP($ls, False, $bGG);
//
// Function XY gives its value to next Cell: ->setCellValue(xy,'')
// Function Borders (Rct), explained at bottom converts here its coordinates
// Function title changes title rpt
// Function Img creates commented line for image
$i = -1; $iL = 0;
$b = False; // no Borders
$sTT = "rpt";
foreach ($ls as $s) {
	$i++;
	$s1 = substr($s,0,3);
	if ($iPP and $s1 == "Que") $ls[$i] = ""; // Query out of sections
	else if ($s1 == "XY(") {
		$s2 = cntn($s,2); // Content inside ()
		$ls[$i] = "";
	} else if ($s1 == "Cel") {
		$ls[$i] = "->setCellValue(".$s2.",".cntn($s,4).")";
	} else if ($s1 == "Tit") {
		$sTT = cntn($s,5);
		$ls[$i] = "";
	} else if ($s1 == "Rct") {
		$liP = explode(",", cntn($s, 3)); // 4 int: x,y,w,h
		$s1 = xlsX($liP[0]).xlsY($liP[1], True);
		$s2 = xlsX($liP[0] + $liP[2]).xlsY($liP[1] + $liP[3],True);
		$sF = "THIN";
		$s = $ls[$i + 1]; // WRct sometimes does not appear next line
		if (substr($s,0,4) == "WRct") {
			if (cntn($s,4) > 2) $sF = "THICK";
			$ls[$i + 1] = "";
		}
		$sP = "@@lsB".chr(65 + $iL);
		$ls[$i] = $sP." = array('borders'=>array('outline'=>array('style'=> PHPExcel_Style_Border::BORDER_$sF) )); @@xls->getActiveSheet()->getStyle('$s1:$s2')->applyFromArray($sP);";
	} else if ($s1 == "Img") {
		$lsA = explode(",", cntn($s,3));
		$ls[$i-3] = "/* @@oD = new PHPExcel_Worksheet_Drawing();";
		$ls[$i-2] = "@@oD->setPath('path+img.jpg');";
		$ls[$i-1] = "@@oD->setCoordinates('".xlsX($lsA[0]).xlsY($lsA[1],True)."'); @@oD->setHeight(".$lsA[3].");";
		$ls[$i] = "@@oD->setWorksheet(@@xls->getActiveSheet()); */";
	}
}
//
// Coordinates Convertion
$i = -1;
foreach ($ls as $s) {
	$i++;
	$s1 = substr($s,0,7);
	if ($s1 == "@@iY +=") {
		$i2 = strlen($s) - strripos($s, ";");
		$s = substr($s, 8, -$i2);
		if (ctype_digit($s)) $ls[$i] = "@@iY += ".xlsY($s, True).";";
	} else if ($s1 == "->setCe") { // ->setCellValue : 14
		$liP = explode(",", cntn($s,14), 3); // 3 datos Ej: 122,$iY + 9,@@d['socio']
		$s = xlsY($liP[1],False);
		$iY = strpos($s, "+");
		if ($iY) $s = "'.($s)";
		else {
			$s = (ctype_digit($s)) ? "$s'" : "'.$s";
		}
		$ls[$i] = "->setCellValue('".xlsX($liP[0]).$s.",".$liP[2].")";
	}
}
//
// Function Font depends a lot on every cell, more at the bottom
$lsC = array(); // Array cell
$lsF = array(); // Array font, captures s2
$s2 = ""; // font content Ej: 'name'=>'Arial','size'=>12,'bold'=>true
$i = -1;
$iY = 0; // font in process=1, Cell in process=2
$iV = 0; // number of empty lines
foreach ($ls as $s) { // INI FONT *
$i++;
$s1 = substr($s,0,4);
if ($s1 == "Font") {
	$s1 = cntn($s,4); // Content inside ()
	if (strpos($s1,"normal")) {
		$ls[$i] = "";
		continue;
	}
	if (strpos($s1,"10 Pitch")) $s1 = str_replace(" 10 Pitch","",$s1);
	if (substr($s1,1,4) == "bold") $s1 = "'bold'=>true";
	$s2 .= (empty($s2)) ? $s1 : ",$s1";
	$ls[$i] = "";
	$iV = 0;
	$iY = 1;
	continue;
}
if ($iY and $s1 == "->se") { // ->setCellValue
	$s1 = cntn($s,14); // Content inside ()
	$iF = -1; // lsC has syncronized index (iF) with lsF
	$b = True; // New font
	foreach ($lsF as $s) { // font is not repeated inside array
		$iF++;
		if ($s == $s2) {
			$b = False;
			break;
		}
	}
	if ($b) {
		$iF = count($lsF);
		$lsF[$iF] = $s2;
	}
	$s2 = "";
	$s = substr($s1, 0, strpos($s1, ",")); // Posc Ej: F1 o A.(i + 2)
	if (isset($lsC[$iF])) $lsC[$iF] .= ";".$s;
	else $lsC[$iF] = $s;
	// move up the lines that contain setCell
	for ($iL = ($i - 1); $iL > 1; $iL--) {
		if (empty($ls[$iL])) $iV++;
		else $iL = 1;
	}
	if ($iV) { // iV = number of lines setCell is moved up
		$ls[$i - $iV] = $ls[$i];
		$ls[$i] = "";
	}
	$iY = 2;
	continue;
}
if ($iY != 2 or empty($s)) continue;
// Creates php in sF from array font Ex: lsCA
// Creates php in ls from array cell that quotes font Ex: A1
$iF = -1;
foreach ($lsF as $s) {
	$iF++;
	$s1 = "@@lsC".chr($iF + 65);
	if (!isset($lsC[$iF])) continue;
	$lsQ = explode(";",$lsC[$iF]);
	$iY = $i - 1;
	foreach ($lsQ as $s2) {
		$ls[$iY--] .= "@@xls->getActiveSheet()->getStyle(".$s2.")->applyFromArray(".$s1."); ";
	}
}
$s2 = "";
$iY = 0;
$lsC = array();
} // END FONT *
// php all array font to ls around line 4
$sF = ""; // capture all lsF
$iF = -1;
foreach ($lsF as $s) {
	$iF++;
	$s1 = "@@lsC".chr($iF + 65);
	$sF .= $s1." = array('font'=> array(".$s.")); ";
}
$iF = count($ls);
for ($iL = 0; $iL < $iF; $iL++) { // will be on first empty line
	if (empty($ls[$iL])) {
		$ls[$iL] = $sF;
		break;
	}
}
//
// It is inserted: ($xls->setActiveSheetIndex(0)) , (;)
$b = False; // setCell... in process. Now they are next to each other
$i = -1;
foreach ($ls as $s) {
	$i++;
	if (substr($s,0,4) == "->se") {
		if ($b == False) {
			$ls[$i] = "@@xls->setActiveSheetIndex(0)".$ls[$i];
			$b = True;
		}
	} else {
		if ($b) {
			$ls[$i - 1] .= ";";
			$b = False;
		}
	}
}
//
// FUSION OF INSTRUCTIONS WITH TEMPLATE IN ls
$lsA = $ls;
$ls = array();
$b = True;
foreach ($lsT as $sT) {
	if (substr($sT, 0, 1) != "#") {
		$ls[] = $sT;
		continue;
	}
	if ($b) {
		$b = False;
		foreach ($lsA as $s) {
			if (!empty($s)) $ls[] = $s;
		}
	}
}
//
// POST FUSION OF INSTRUCTIONS
//
// Title and Parameters, these last create a GET form
$i = -1;
foreach ($ls as $s) {
	$i++;
	if ($i < 12) {
		if (substr($s,0,7) == "->setTi") $ls[$i] = str_replace("rpt", $sTT, $ls[$i]);
		else if ($iPP and $s == "// ***") {
			$s = "";
			for ($i1 = 0; $i1 < $iPP; $i1++) $s .= "@@".$dP[$i1]["name"]."=@@_GET['".$dP[$i1]["name"]."']; ";
			$ls[$i] = $s;
			$ls[$i - 1] = "if (@@_GET) {";
		}
	} else {
		if (substr($s,0,5) ==  "heade") $ls[$i] = str_replace("rpt", $sTT, $ls[$i]);
	}
}
if ($iPP) $ls[$i - 1] = "} // END GET";
//
foreach ($ls as $s) {
	if (!empty($s)) echo fnc($s)."\n";
}
if ($iPP) echo creaHTML($dP, $iPP, $sA, "XLS");

//$i = -1; // For testing up
//foreach ($ls as $s) {
//	$i++;
// echo $lsE[$i++].": ".$s."\n";
//	if (!empty($s)) echo $i.":".$s."\n";
//}
//exit;

// (XML-XLS)
function xlsX($i) {
$i = round($i / 38);
if ($i < 1) $i = 1;
return chr($i + 64);
}
function xlsY($s, $b) {  // Ej: @@iY + 10  o  @@iY  o  10, True: if 0=>1
$s = trim($s);
$i = strpos($s,".");
if ($i) $s = substr($s, 0, $i);
$i = strpos($s, "+");
if ($i) {
	$iY = trim(substr($s, $i + 1)); // depues de +
	$s = substr($s, 0, $i); // antes de +
	if (ctype_digit($iY)) {
		$iY = round($iY / 24);
		if ($iY == 0) {
			$s1 = ($b) ? "+ 1": "";
		} else $s1 = "+ ".$iY;
	} else $s1 = "+ ".$iY;
	$s .= $s1;
} else {
	if (ctype_digit($s)) {
		$s = round($s / 24);
		if ($s == "0" and $b) $s = "1";
	}
}
return trim($s);
}

function cntn($s, $i) { // line, function posc of -(-
$i2 = strlen($s) - strripos($s, ")");
return substr($s, $i+1, -$i2); // Content inside ()
}

/*
- Conversion de coordenadas para obtener:
$xls->setActiveSheetIndex(0) ->setCellValue('A'.$iY, $s)
 ->setCellValue('B'.$iY, $d[2])
->setCellValue('D'.$iY, $d[3]);

- Font, recibido asi:
Font('name'=>'Courier 10 Pitch')
Font('size'=>12)
Font('bold'=>bold)
 convertido a array de estilo:
$lsAA = array(
 'font'  => array(
	'bold'  => true,
	'color' => array('rgb' => 'FF0000'),
	'size'  => 15,
	'name'  => 'Verdana'
));
$xls->getActiveSheet()->getStyle('A1')->applyFromArray($lsAA);

- Bordes, recibido asi: x,y,w,h  o  grosor
rct(++,::,::,::)
rct(++)
 convertido a array de estilo:
$lsBB = array(
 'borders' => array(
	'outline' => array(
	'style' => PHPExcel_Style_Border::BORDER_THICK,
	'color' => array('argb' => 'FFFF0000'),
	)
));
$xls->getStyle('B2:G8')->applyFromArray($lsBB);

$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setName('Logo');
$objDrawing->setDescription('Logo');
$objDrawing->setPath('./images/officelogo.jpg');
$objDrawing->setHeight(36);
$objDrawing->setCoordinates('B15');
$objDrawing->setOffsetX(110);
$objDrawing->setRotation(25);
$objDrawing->getShadow()->setVisible(true);
$objDrawing->getShadow()->setDirection(45);
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
*/
// FIN
?>
