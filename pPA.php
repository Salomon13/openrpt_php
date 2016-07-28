<?php
# THIS IS A NO EXCUTABLE PHP TEMPLATE
# (#) is a comment  that does not appear on the result
# (@@=$) (++=content) (|=") (::=lines to unite)
//
// ***
$sEmpre = "roca"; // DB used by cone
require('../../fpdf/fpdf.php');
require_once "../aa/cone.php";
//
class PDF extends FPDF {
function creaDoc($db) { @@iY = 0;
[title] @@this->SetTitle(|++|);

[topmargin] @@this->SetTopMargin(++);
[rightmargin] @@this->SetRightMargin(++);
[leftmargin] @@this->SetLeftMargin(++);

[querysource name] QSele(++)
[querysource sql] Qsele(++)
# DO NOT PUT SPACE WHERE COMMAS

[rpthead height] @@iY += ++;

[rpthead image rect x] Img(++,::,::,::)
[rpthead image rect y] Img(::,++,::,::)
[rpthead image rect width] Img(::,::,++,::)
[rpthead image rect height] Img(::,::,::,++)

[rpthead label rect x] @@this->SetXY(++,::);
[rpthead label rect y] @@this->SetXY(::,++);
[rpthead label font face] SetFont(++)
[rpthead label font size] SetSize(++)
[rpthead label font weight] SetWght(++)
[rpthead label string] @@this->Cell(0,0,'++');
[rpthead field rect x] @@this->SetXY(++,::);
[rpthead field rect y] @@this->SetXY(::,++);
[rpthead field font face] SetFont(++)
[rpthead field font size] SetSize(++)
[rpthead field font weight] SetWght(++)
[rpthead field data query] Query(++)
[rpthead field data column] @@this->Cell(0,0,@@d['++']);

[rpthead rect x] @@this->Rect(++,::,::,::);
[rpthead rect y] @@this->Rect(::,++,::,::);
[rpthead rect width] @@this->Rect(::,::,++,::);
[rpthead rect height] @@this->Rect(::,::,::,++); 

[section name] Section(++,sctr)
[section detail key query] Sector(++,::)
[section detail height] Sector(::,++)

[section group name] Group(++,::,Head,Foot,EndG)
[section group column] Group(::,++,Head,Foot,EndG)

[section group head height] GHead(++)
[section group head label rect x] @@this->SetXY(++,::);
[section group head label rect y] @@this->SetXY(::,@@iY + ++);
[section group head label font face] SetFont(++)
[section group head label font size] SetSize(++)
[section group head label font weight] SetWght(++)
[section group head label string] @@this->Cell(0,0,'++');
[section group head field rect x] @@this->SetXY(++,::);
[section group head field rect y] @@this->SetXY(::,@@iY + ++);
[section group head field font face] SetFont(++)
[section group head field font size] SetSize(++)
[section group head field font weight] SetWght(++)
[section group head field data query] Query(++)
[section group head field data column] @@this->Cell(0,0,@@d['++']);

[section group foot height] GFoot(++)
[section group foot label rect x] @@this->SetXY(++,::);
[section group foot label rect y] @@this->SetXY(::,@@iY + ++);
[section group foot label font face] SetFont(++)
[section group foot label font size] SetSize(++)
[section group foot label font weight] SetWght(++)
[section group foot label string] @@this->Cell(0,0,'++');
[section group foot field rect x] @@this->SetXY(++,::);
[section group foot field rect y] @@this->SetXY(::,@@iY + ++);
[section group foot field font face] SetFont(++)
[section group foot field font size] SetSize(++)
[section group foot field font weight] SetWght(++)
[section group foot field data query] Query(++)
[section group foot field data column] @@this->Cell(0,0,@@d['++']);
[section group foot field format] Sprintf(++)

[section detail label rect x] @@this->SetXY(++,::);
[section detail label rect y] @@this->SetXY(::,@@iY + ++);
[section detail label font face] SetFont(++)
[section detail label font size] SetSize(++)
[section deatil label font weight] SetWght(++)
[section detail label string] @@this->Cell(0,0,'++');
[section detail field rect x] @@this->SetXY(++,::);
[section detail field rect y] @@this->SetXY(::,@@iY + ++);
[section detail field font face] SetFont(++)
[section detail field font size] SetSize(++)
[section deatil field font weight] SetWght(++)
[section detail field data query] Query(++)
[section detail field data column] @@this->Cell(0,0,@@d['++']);

[rptfoot height] @@iY += ++;
[rptfoot label rect x] @@this->SetXY(++,::);
[rptfoot label rect y] @@this->SetXY(::,@@iY + ++);
[rptfoot label font face] SetFont(++)
[rptfoot label font size] SetSize(++)
[rptfoot label font weight] SetWght(++)
[rptfoot label string] @@this->Cell(0,0,'++');
[rptfoot field rect x] @@this->SetXY(++,::);
[rptfoot field rect y] @@this->SetXY(::,@@iY + ++);
[rptfoot field font face] SetFont(++)
[rptfoot field font size] SetSize(++)
[rptfoot field font weight] SetWght(++)
[rptfoot field data query] Query(++)
[rptfoot field data column] @@this->Cell(0,0,@@d['++']);
[rptfoot field format] Sprintf(++)

//
}} // end class PDF --------
//
$pdf = new PDF();
$pdf->AddPage();
$pdf->creaDoc($db);
$pdf->Output();
exit;
//
?>
