<?php /* Turn a xml made with openrpt into php
Instance: report.xml -> report.php
	you get report.php by typing in terminal:
 php aPDF.php report.xml >> report.php
*/
require_once "aPHP.php";
if (count($argv) == 2) $sA = $argv[1]; // xml
else die("ERROR IN ARGUMENTS\n");
$sP = "pPA";
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
$ls = aPHP($ls, True, $bGG);
//
// Function Img
$i = -1;
foreach ($ls as $s) {
	$i++;
	if ($iPP and substr($s,0,5) == "Query") $ls[$i] = ""; // Query out of sections
	if (substr($s,0,3) != "Img") continue;
	$lsA = explode(",", cntn($s, 3));
	$ls[$i] = "//@@this->Image('path+img.jpg',".pdfX($lsA[0]).",".pdfY($lsA[1]).",".pdfX($lsA[2] - 16).",".pdfY($lsA[3]).");";
}
// Functions Font, fuse in one and do not repeat if possible
// Parameter SetFont(): 1=Font, 2=F_and_Size, 3=F_Bold_S
// if only size changes use instead FontSize()
// 1° SetXY must have SetFont before it
// the Set pull the line before down
// and then create a SetFont before SetXY
$lsA = array("", "", "");
$i = -1; $sM = "";
$b = True; // b=True yet not the 1° SetXY
$iL = 0; // ( > 1) lines font in process, cases from 1 to 7
$s2 = "@@this->SetFont";
foreach ($ls as $s) {
	$i++;
	if (substr($s, 0, 3) == "Set") {
		$s1 = cntn($s, 7); // Content inside ()
		$sC = "01Font12Size24Wght"; // array_lsE-case-instruction
		$iF = strpos($sC, substr($s, 3, 4));
		if ($iF) {
			$lsA[ substr($sC, $iF - 2, 1) ] = $s1;
			$iL += substr($sC, $iF - 1, 1);
			$ls[$i] = $ls[$i - 1];
			$ls[$i - 1] = "";
		}
	} else {
		if ($iL == 0) continue;
		if ($b) {
			if (empty($lsA[0])) $lsA[0] = "Courier";
			$b = false;
		}
		if ($lsA[2] == "normal") $lsA[2] = "";
		else if ($lsA[2] == "bold") $lsA[2] = "B";
		if (substr($lsA[0], 0, 7) == "Courier") $lsA[0] = "Courier";
		$s = "'".$lsA[0]."','".$lsA[2]."',".$lsA[1]; // Font Bold Size
		if ($s == $sM) {
			$iL = 0;
			continue;
		}
		if ($iL == 6) $ls[$i-2] = $s2."(".$s.");"; // Only possible cases 5,6
		else {
			$i2 = strripos($s, ",");
			if (substr($s,0,$i2) == substr($sM,0,$i2)) $ls[$i-2] = $s2."Size(".$lsA[1].");";
			else $ls[$i-2] = $s2."(".$s.");";
		}
		$sM = $s;
		$iL = 0; // OK do NOT empty lsE
	}
}
//
// Coordinates Convertion
$iF = 0;
$i = -1;
foreach ($ls as $s) {
	$i++;
	if (substr($s,0,7) == "@@iY +=") {
		$i2 = strlen($s) - strripos($s, ";");
		$s = substr($s, 8, -$i2);
		if (ctype_digit($s)) $ls[$i] = "@@iY += ".pdfY($s)."; if (@@iY > 270) { @@this->AddPage(); @@iY = $iF; }";
		continue;
	}
	if (substr($s,0,12) == "@@this->Rect") {
		$li = explode(",", cntn($s,12));
		$ls[$i] = "@@this->Rect(".pdfX($li[0]).",".pdfY($li[1] - 12).",".pdfX($li[2] + $li[0] - 40).",".pdfY($li[3] + $li[1] - 38).");";
	}
	//
	if (substr($s,0,11) != "@@this->Set") continue;
	$s1 = substr($s,11,-(strlen($s) - strpos($s,"(")));
	$s = substr($s,11);
	switch ($s1) {
	case "TopMargin":
		$iF = pdfY( cntn($s, 9) ); // for $iY = iF
		$ls[$i] = "@@this->SetTopMargin(".$iF.");";
		break;
	case "RightMargin":
		$ls[$i] = "@@this->SetRightMargin(".pdfX( cntn($s, 11) ).");";
		break;
	case "LeftMargin":
		$ls[$i] = "@@this->SetLeftMargin(".pdfX( cntn($s, 10) ).");";
		break;
	case "XY":
		$li = explode(",", cntn($s, 2));
		$ls[$i] = "@@this->SetXY(".pdfX($li[0]).",".pdfY($li[1]).");";
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
// Parameters
if ($iPP) {
$i = -1;
foreach ($ls as $s) {
	$i++;
	if ($s == "// ***") {
		$s = "";
		for ($i1 = 0; $i1 < $iPP; $i1++) $s .= "@@".$dP[$i1]["name"]."=@@_GET['".$dP[$i1]["name"]."']; ";
		$ls[$i] = $s;
		$ls[$i - 1] = "if (@@_GET) {";
		continue;
	}
	if (strpos($s, "creaDoc") ) {
		$s1 = "db";
		for ($i1 = 0; $i1 < $iPP; $i1++) $s1 .= ",@@".$dP[$i1]["name"];
		$ls[$i] = str_replace("db", $s1, $s);
		continue;
	}
}
$ls[$i - 1] = "} // END GET";
}
//
foreach ($ls as $s) {
	if (!empty($s)) echo fnc($s)."\n";
}
//
if ($iPP) echo creaHTML($dP, $iPP, $sA, "PDF");

//$i = -1; // For testing up
//foreach ($ls as $s) {
//	$i++;
// echo $lsE[$i++].": ".$s."\n";
//	if (!empty($s)) echo $i.":".$s."\n";
//}
//exit;


// (XML-PDF)
function pdfX($i) {
return round($i / 3.5);
}
function pdfY($s) { // Ej: @@iY + 10  o  @@iY  o  10
$s = trim($s);
$i = strpos($s,".");
if ($i) $s = substr($s, 0, $i);
$i = strpos($s, "+");
if ($i) {
	$iY = trim(substr($s, $i + 1)); // after +
	$s = substr($s, 0, $i); // before +
	if (ctype_digit($iY)) {
		$iY = round($iY / 4);
		$s1 = ($iY == 0) ? "" : "+ ".$iY;
	} else $s1 = "+ ".$iY;
	$s .= $s1;
} else {
	if (ctype_digit($s)) $s = round($s / 4);
}
return trim($s);
}

function cntn($s, $i) { // line, function posc of -(-
$i2 = strlen($s) - strripos($s, ")");
return substr($s, $i+1, -$i2); // Content inside ()
}

// $this->Rect(10,88,180,23);	// x,y,w,h
?>
