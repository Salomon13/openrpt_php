<?php

function aPHP($ls, $bPDF, $bGG) { // INI FUNCTION ***
// PRE-FUSION OF INSTRUCTIONS AND PROCESS OF FUNCTIONS
// it unites lines with commas in one Ex: (::,18) + (10,::) = (10,18)
$i = -1; $iP = 0; // iP how many elements inside () with commas
$s3 = ""; // last instructions with (::)
foreach ($ls as $s) {
	$i++; // OK here
	if (strpos($s, "::")) {
		$i1 = strpos($s, "(");
		$i2 = strlen($s) - strripos($s, ")");
		$s2 = substr($s, -$i2);
		$s1 = substr($s, $i1+1, -$i2); // Content inside ()
		$sI = substr($s, 0, $i1); // instruction before ()
		if ($iP == 0 or $sI != $s3) {
			$lsY = explode(",", $s1);
			$iP = count($lsY);
			$s3 = $sI;
		} else {
			$ls[$i - 1] = ""; // to erase instruction before ()
			$lsQ = explode(",", $s1);
			for ($i2 = 0; $i2 < $iP; $i2++) {
				if ($lsQ[$i2] == "::" or $lsQ[$i2] == "'::'") $b = True;
				else $lsY[$i2] = $lsQ[$i2];
			} // print_r($lsY);
			$s1 = implode(",", $lsY);
			if (strpos($s1, "::")) continue;
			$iP = 0;
			$ls[$i] = substr($s, 0, $i1 + 1).$s1.$s2;
		}
		continue;
	}
}
// Function Qsele gives SELECT an abreviation
// Function Section gets new parameters, ready to create: foreach
//  Section(Name, SELECT, end line of groups, ABR, END, N-Groups)
//  ABR is abreviation, END is end of Section and will be defined later
$i3 = 0; // 1=A for 1° SELECT
$dQ = array(); // abreviation of each SELECT
$iS = 0; // Section line
$liS = array(); // line of each Section
$i = -1;
foreach ($ls as $s) {
	$i++;
	$s1 = substr($s,0,5);
	switch ($s1) {
	case "QSele":
		$i3++;
		$s1 = cntn($s,5);
		$s = chr($i3 + 64);
		$dQ[$s1] = $s; // Ex: Socio=>A
		$ls[$i] = "";
		$iQ = $i + 1;
		$s = "@sQ".$s." ";
		while (strpos($ls[$iQ], "@sQ ")) {
			$ls[$iQ] = str_replace("@sQ ", $s, $ls[$iQ]);
			$iQ++;
		}
		break;
	case "Secti":
		$iS = $i;
		$liS[] = $i; break;
	case "Secto":
		$s1 = cntn($s,6);
		$i1 = strpos($s1,",");
		$s2 = substr($s1,0,$i1).",".($i - 1 -$iS).",ABR,END,0";
		$ls[$iS] = str_replace("sctr",$s2,$ls[$iS]);
		$ls[$i] = "@@iY += ".substr($s1,$i1 + 1)."; //";
		break;
	}
}
// Function Group gets the line number of the next head, foot, etc
//  line numbers relative to the line where it is
//  Group(Name, Column, ini Head, ini Foot, end Group)
//  there is always end Group, not always Head, Foot
if ($bGG) { // INI GROUP
$iS = // Section line, the last one
$iG = 0; // Group line
$i = -1;
foreach ($ls as $s) {
	$i++;
	$s1 = substr($s,0,5);
	switch ($s1) {
	case "Secti":
		$iS = $i; break;
	case "Group":
		if ($iG) $ls[$iG] = str_replace("EndG",($i - 1 -$iG),$ls[$iG]); // group anterior
		$iG = $i;
		$s = $ls[$iS]; // +1 Group in this Section
		$i1 = strripos($s, ",");
		$i2 = substr($s, $i1 + 1, -1);
		$ls[$iS] = substr($s, 0, $i1 + 1).($i2 + 1).")";
		break;
	case "GHead":
		$ls[$iG] = str_replace("Head",($i - $iG),$ls[$iG]);
		$ls[$i] = "@@iY += ".cntn($s,5).";"; break;
	case "GFoot":
		$ls[$iG] = str_replace("Foot",($i - $iG),$ls[$iG]);
		$ls[$i] = "@@iY += ".cntn($s,5).";"; break;
	case "@@iY ": // End of Group
		if ($iG > 0 and strpos($s, "//")) {
			$ls[$iG] = str_replace("EndG",($i - 1 -$iG),$ls[$iG]);
			$iG = 0;
		}
		break;
	}
} //echo $ls[$iG]."\n";
} // END GROUP
//
// Function Section creates: the foreach
// Section has extra space, see up.
// Function Query tells the abreviation of SELECT of every variable
$iS = 0; // Line where Section is
$i = -1;
foreach ($ls as $s) {
	$i++;
	if (substr($s,0,7) == "Section") { // SELECT to foreach
		$iS = $i;
		$lsQ = explode(",", cntn($s, 7));
		$s1 = $lsQ[1];
		$s1 = $dQ[$s1]; // abreviation from SELECT to Section Ex: A
		$ls[$i] = str_replace("ABR", $s1, $s);
		$ls[$i - 3] = "@@bF = False; // 1° $s1 Re"; // 1° record
		$ls[$i - 2] = "@@dR".$s1." = @@db->query(@@sQ".$s1.");";
		$ls[$i - 1] = "foreach (@@dR".$s1." as @@d".$s1.") { // ini ".$lsQ[0]." section";
	} else if (substr($s,0,5) == "Query") {
		$ls[$i] = "";
		$s1 = cntn($s, 5); // Content inside ()
		if ($s1 == "Parameter Query") { // from $d['x'] to $x
			$s = str_replace("d['", "", $s);
			$ls[$i + 1] = str_replace("']", "", $s);
		} else { // from $d['x'] to $dA['x']
			$s = $ls[$i + 1];
			$i1 = strpos($s, "d[") + 1;
			$ls[$i + 1] = substr($s, 0, $i1).$dQ[$s1].substr($s, $i1);
		}
	} else if ($s == "} // end section") {
		$ls[$iS] = str_replace("END", ($i - $iS), $ls[$iS]);
	}
}
//
// Gets and process every Group inside every Section
//  Section(Name, SELECT, end line of groups, ABR, END, N-Groups)
$iSS = count($liS);
$iF = 0; // number of lines inserted by foot of previous section
for ($iS = 0; $iS < $iSS; $iS++) { // INI SECTION *
// 
$liS[$iS] += $iF;
$iL = $liS[$iS]; // absolute line of Section
$sS = $ls[$iL];
$lsS = explode(",", cntn($sS, 7));
$ls[$iL] = "";
if ($lsS[5] < 1) continue; // Section without Groups
//
$i2 = $lsS[2] + $iL; // absolute end line of groups
// All feet go together, the same for heads but
// contrary to xml, feet go before heads
// Section Ex: socio (outer), serie(inner)
//  Group(Name, Column, ini Head, ini Foot, end Group)
$sG = ""; // all the columns that group in Group and more Ex: $_cli = "";
$lsB = ""; // columns grouping foot, can differ sG in number: Ex: cli,serie
$sF = ""; // column that makes the group Ex: $dA['field_b']
$s1 = ""; // field Ex: field_a
$s2 = "0"; // there is head
$s3 = "0"; // there is foot
$lsH = array(); // section head lines
$lsC = array(); // comparisons
$lsFF = array(); // temporary head lines that go atop lsF
$lsF = array(); // section foot lines
$bF = True;  // no first foot
$bH = True;  // no first head
$iL++; $i3 = 0;
for ($iG = $iL; $iG <= $i2; $iG++) { // lines in one Section with Groups
	if (empty($ls[$iG])) continue;
	if (substr($ls[$iG], 0, 5) == "Group") {
		if ($s2) $lsH[] = "} // ".$s1; // there was head
		if ($s3) { // there was foot
			$lsFF[] = "@@_".$s1." = ".$sF."; //-";
			$lsFF[] = "} // ".$s1." //-";
			$lsF = array_merge($lsFF, $lsF);
			$lsFF = array();
		}
		//
		$liG = explode(",", cntn($ls[$iG], 5) ); // Group content
		if ($liG[2] or $liG[3]) $sG .= "@@_".$liG[1]." = ''; ";
		$sF = "@@d".$lsS[3]."['".$liG[1]."']";
		if ($liG[2]) { // there is head
			$lsH[] = "if (@@_".$liG[1]." != ".$sF.") {";
			$lsH[] = "@@_".$liG[1]." = ".$sF.";";
		}
		if ($liG[3]) { // there is foot
			$sG .= "@@b_".$liG[1]." = True; ";
			$lsB[] = $liG[1];
			if ($bF) { // only first foot
				$bF = False;
				$lsC[] = "if (@@bF) { // ini foot"; // line 0
			} else $s1 = " or @@b_".$s1;
			$lsC[] = "if (@@_".$liG[1]." != ".$sF.$s1.") @@b_".$liG[1]." = True; //-";
			$lsFF[] = "if (@@b_".$liG[1].") { //-";
		}
		$s1 = $liG[1];
		$s2 = $liG[2];
		$s3 = $liG[3];
		$i4 = $liG[4] + $iG;
		$i3 = ($liG[3]) ? $liG[3] : $liG[4];
		$i3 += $iG; // ls absolute end line of head or ini foot
	} else {
		if ($iG >= $i3) $lsFF[] = $ls[$iG]; // foot line
		else $lsH[] = $ls[$iG]; // head line
		if ($iG > $i4) continue;
	}
	$ls[$iG] = "";
}
//
if ($bF == False) { // there was foot, last one
	$lsFF[] = "@@_".$s1." = ".$sF."; //-";
	$lsFF[] = "} // ".$s1." //-";
	$lsF = array_merge($lsFF, $lsF);
	$lsF[] = "} // end foot";
}
if ($s2) {
	$lsH[] = "} // ".$liG[1];
	$lsH[] = "// end head";
	//
	$i3 = -1;
	foreach ($lsH as $s) { // change ..._field != $dA['field'] to ...$b_field
		$i3++;
		if (substr($s,0,2) == "if") {
			$s = cntn($s,2);
			$i1 = strpos($s, " !");
			if ($i1) {
				$s = substr($s, 4, -(strlen($s) - $i1)); // Ex: b_serie
				if (in_array($s, $lsB)) {
					$lsH[$i3] = "if (@@b_".$s.") {";
//					$lsH[$i3 + 1] = "";
				}
			}
		}		
	}
}
// echo $sG."\n"; print_r($lsC); print_r($lsF); //print_r($lsH);
// Function Sprintf sets in 0, sums in detail and resets in a group
$lsD = array(); // fields that only change with group like grouping lsB
$lsE = array(); // gives values to lsD, see examples.
$s2 = ""; $sD = "";
$i1 = -1;
foreach ($lsF as $s) {
	$i1++;
	if ($bPDF) {
		if (substr($s,-3) == "]);") { // $dA['field'] to $_field in foot
			$i2 = strripos($s, ",@@d");
			if ($i2) {
				// OK
				$s2 = substr($s, $i2 + 7, -4); // Ex: field
				$lsF[$i1] = substr($s, 0, $i2 + 3)."_".$s2.");";
				// lsD+lsB will not repeat a field
				if (in_array($s2, $lsB) or in_array($s2, $lsD)) continue;
				$lsD[] = $s2;
				$lsE[] = substr($s, $i2 + 1, -2); // Ex: $dA['field']
			}
			continue;
		}
	} else {
		if (strpos($s, "ell(")) { // Cell(field) to $_field in foot
			$i2 = strripos($s, "@@d");
			if ($i2) {
				$s2 = cntn(substr($s,$i2),3);
				$s2 = substr($s2, 2, -2);
				$lsF[$i1] = substr($s, 0, $i2 + 2)."_".$s2.");";
				// lsD+lsB will not repeat a field
				if (in_array($s2, $lsB) or in_array($s2, $lsD)) continue;
				$lsD[] = $s2;
				$lsE[] = substr($s, $i2, -1); // Ex: $dA['field']
			}
			continue;
		}
	}
	if (substr($s,0,3) == "Spr") {
		$s3 = cntn($s,7);
		$lsF[$i1] = "";
		$s = $lsF[$i1 - 1];
		$i2 = strripos($s, "@@_");
		if ($i2) {
			$i3 = strripos($s, ")"); // Ex: $_field to sprintf($_field)
			$lsF[$i1 - 1] = substr($s,0,$i2)."sprintf('".$s3."',@@_".$s2.")".substr($s,-(strlen($s)-$i3));
			$sD = " ".$s2;
		}
		continue;
	}
	if (substr($s,-3) == "//-" and count($lsD)) {
		foreach ($lsD as $s1) {
			if (strpos($sD, $s1)) {
				$s2 = "@@_$s1 = 0 ";
				$lsH[] = "@@_$s1 += ".$lsE[ key($lsD) ].";";
			} else $s2 = "@@_$s1 = ''";
			$sG = $s2."; ".$sG;
			$lsF[$i1] = substr($s2,0,-2)."0; ".$lsF[$i1];
		}
		$sD = "";
		$lsD = array(); $lsE = array();
	}
}
// Return lines of Section where they came from in ls
$i = $iL;
$ls[$i - 4] = $sG.$ls[$i - 4];
foreach ($lsC as $s) $ls[++$i] = $s;
foreach ($lsF as $s) $ls[++$i] = $s;
foreach ($lsH as $s) {
	if (!empty($s)) $ls[++$i] = $s;
}
foreach ($lsB as $s) $ls[++$i] = "if (@@b_".$s.") { @@b_".$s." = False; @@bF = True; }";
// Insert Foot again at the end of Section
if ($bF == False) {
	$i1 = $lsS[4] + $iL - 1; // ...from this line...
	$i2 = count($ls); // ...to the last line will advance...
	$iF = count($lsC) + count($lsF) + 1; // ...this, leaving empty lines...
	for ($i = 0; $i < $iF; $i++) $ls[] = "";
	$i4 = count($ls);
	for ($i = ($i2 - 1); $i > $i1; $i--) $ls[--$i4] = $ls[$i];
	$ls[++$i] = "if (@@bF) { // foot after section";	
	foreach ($lsF as $s) { // ... to be filled with Foot
		if (substr($s, -3) != "//-") { // lines removed in this foot
			$i++;
			$ls[$i] = $s;	
		}
	}
}
} // END SECTION *
// check if there is general foot and what contains
$lsD = array(); // fields that foot accumulates
$iL = count($ls) - 1;
$iF = 0;
$s3 = ""; // numeric format of field that accumulates
$b = False;
for ($i = $iL; $i > 0; $i--) {
	$s = $ls[$i];
	if (substr($s,0,1) == "}") break; // } // end foot or section
	if ($bPDF) {
		if (substr($s,-3) == "]);") { // $dB['field'] to $_Bfield in foot
			$i2 = strripos($s, ",@@d");
			if ($i2) { // s1 = Section and field Ex: Bfield
				$s1 = substr($s, $i2 + 4, 1).substr($s, $i2 + 7, -4);
				$b = True;
			}
		}
	} else {
		if (strpos($s, "ell(")) { // Cell($d['field']) to $_field in foot
			$i2 = strripos($s, "@@d");
			if ($i2) {
				$s1 = substr($s, $i2 + 3, 1).substr($s, $i2 + 6, -3);
				$b = True;
			}
		}
	}
	if ($b and strlen($s3)) {
		$b = False;
		$i3 = strripos($s, ")");
		$s2 = ($bPDF) ? "," : "";
		$ls[$i] = substr($s,0,$i2).$s2."sprintf('".$s3."',@@_".$s1.")".substr($s,-(strlen($s)-$i3));
		$s3 = "";
		if (in_array($s1, $lsD)) continue;
		$lsD[] = $s1;
	} //else $ls[$i] = substr($s, 0, $i2 + 3)."_".$s1.$s2.");";
	if (substr($s,0,3) == "Spr") {
		$s3 = cntn($s,7);
		$ls[$i] = "";
	}
}
if (count($lsD) > 0) { // fields that foot accumulates
	$b = False;
	$i = -1;
	foreach ($ls as $s) {
		$i++;
		if (substr($s,-3) == " Re") {
			$s1 = substr($s,-4);
			$s1 = substr($s1,0,1);
			foreach ($lsD as $s2) {
				if (substr($s2,0,1) == $s1) {
					$ls[$i] = "@@_".$s2." = 0; ".$ls[$i];
					$b = True;
				}
			}
		}
		if ($s == "} // end section") {
			$ls[$i -1] .= "@@_".$s2." += @@d".$s1."['".substr($s2,1)."'];";
			$b = False;
		}
	}
}
return $ls;
} // FIN FUNCTION ***


// final replacement
function fnc($s) {
$s = str_replace("@@", "$", $s);
$s = str_replace("|", '"', $s);
return $s;
}

// to convert string list in array d
function creaD($lsP) {
$d = array();
$iP =  -1;
foreach ($lsP as $s) {
	$iP++;
	$ls = explode(" ", $s);
	for ($i = 0; $i < count($ls); $i++) {
		$s = $ls[$i];
		$iX = strpos($s, "=");
		$d[$iP][ substr($s,0,$iX) ] = substr($s,$iX + 1);
	}
}
return $d;
}

// Convert MetaSql en Sql
function sqlP($sQ, $dP, $iP) { // MetaSql, parameters, num. of param
$sQ = str_replace(';', "", $sQ);
$i = strpos($sQ, "FROM ");
if ($i == False) $i = strpos($sQ, "from ");
$s2 = "";
if ($i) {
	$s1 = substr($sQ, 0, $i);
	$s2 = substr($sQ, $i + 5);
} else return $s1;
$i2 = strlen($s2);
$iX = strpos($s2, "&lt?");
$bI = True;
while ($iX) {
	$iX += 4;
	$sX = "";
	$b = True;
	for ($i = $iX; $i < $i2; $i++) {
		$s = substr($s2, $i, 1);
		if ($s == "?") break;
		if ($s == ")") $b = False;
		if ($b and $s != " ") $sX .= $s;
	}
	$iZ = $i;
	$s = substr(strtoupper($sX), 0, 5); // Ex: IFEXISTS("field"
	if ($s == "VALUE") {
		$s = substr($sX, 7, -1); // VALUE("field" brings name of parameter
		for ($i = 0; $i < $iP; $i++) {
			if ($dP[$i]["name"] == $s) {
				$sX = ($dP[$i]["type"] == "string") ? " '@@$s'" : " @@$s";
				break;
			}	
		}
	} else if ($s == "IFEXI") { // IFEXISTS("
		$s = substr($sX, 10, -1);
		if ($bI) {
			$bI = False;
			$sX = '";';
		} else $sX = "";
		$sX .= 'if (@@'.$s.') @@sQ .= "';
	} else if ($s == "ENDIF") { // ENDIF("
		$sX = '";';
	}
	$s2 = substr($s2, 0, $iX - 5).$sX.substr($s2, $iZ + 2);
	$i2 = strlen($s2);
	$iX = strpos($s2, "&lt?");
}
if ($bI == False) {
	$i = strripos($s2,";");
	if (substr($s2,$i + 1,2) != "if") $s2 = substr($s2,0,$i).';@@sQ .= "'.substr($s2, $i + 1);
}
return '@@sQ ="'.$s1.'FROM '.$s2.'"'; // s1 before FROM, s2 after
}

// Create a Form in HTML for the parameters
// Parameters, n of p, title(of the report), format (PDF)
function creaHTML($dP, $iPP, $sA, $sT) {
$sA = substr($sA, 0, strpos($sA, "."));
echo '<!DOCTYPE html>
<html>
<head>
<title>'.$sT.' '.ucfirst($sA).'</title>
<meta http-equiv="Content-Type" content="test/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" media="screen" href="../aa/css/jquery-ui.css" />
</head>
<body bgcolor="#cccccc">
<br><br>
<form action="'.$sA.'.php" method="get">
<table border="1" align="center">
';
for ($i = 0; $i < $iPP; $i++) {
	$sT = $dP[$i]["type"];
	if ($sT != "bool") continue;
	$s = $dP[$i]["name"];
	$s1 = (isset($dP[$i]["D"])) ? 'title="'.str_replace("_"," ",$dP[$i]["D"]).'"' : "";
	echo '<tr><td>'.ucfirst($s).'</td><td>';
	echo '<select name="'.$s.'" id="'.$s.'" '.$s1.'><option value="0">NO</option><option value="1">SI</option></select></td></tr>';
}
echo '
</table>';
echo '<table border="1" align="center">
';
for ($i = 0; $i < $iPP; $i++) {
	$sT = $dP[$i]["type"];
	if ($sT == "bool") continue;
	$s = $dP[$i]["name"];
	$s1 = 'title="';
	$s1 .= (isset($dP[$i]["D"])) ? str_replace("_"," ",$dP[$i]["D"])." tipo ".$sT : " tipo ".$sT;
	echo '<tr><td>'.ucfirst($s).'</td><td>';
	$s2 = ($sT == "integer") ? '"number"' : '"text"';
	$s3 = (isset($dP[$i]["default"])) ? 'value="'.$dP[$i]["default"].'"' : '""';
	echo '<input name="'.$s.'" id="'.$s.'" '.$s1.'" type='.$s2.' '.$s3.'/></td></tr>';
}
echo '
<tr><td> </td>
	<td align=center><input type="submit" value="Aceptar" name="B1"><input type="reset" value="Borrar"></td>
</tr>
</table></form></body></html>';
}
// END
?>
