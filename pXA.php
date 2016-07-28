<?php
# THIS IS A NO EXCUTABLE PHP TEMPLATE
# (#) is a comment  that does not appear on the result
# (@@=$) (++=content) (|=") (::=lines to unite)
//
// ***
$sEmpre = "roca";
date_default_timezone_set('Europe/London');
include "../aa/cone.php";
require_once '../../PHPExcel/Classes/PHPExcel.php';
$xls = new PHPExcel();
$xls->getProperties()->setCreator("Mario")
 ->setTitle("rpt")
 ->setDescription('General')
 ->setKeywords('office 2007 openxml php')
 ->setCategory('report'); $iY = 0;

[title] Title(++);

[querysource name] QSele(++)
[querysource sql] Qsele(++)
# DO NOT PUT SPACE WHERE COMMAS

[rpthead height] @@iY += ++;

[rpthead image rect x] Img(++,::,::,::)
[rpthead image rect y] Img(::,++,::,::)
[rpthead image rect width] Img(::,::,++,::)
[rpthead image rect height] Img(::,::,::,++)

[rpthead label rect x] XY(++,::)
[rpthead label rect y] XY(::,++)
[rpthead label font face] Font('name'=>|++|)
[rpthead label font size] Font('size'=>++)
[rpthead label font weight] Font('bold'=>++)
[rpthead label string] Cell('++')
[rpthead field rect x] XY(++,::)
[rpthead field rect y] XY(::,++)
[rpthead field font face] Font('name'=>|++|)
[rpthead field font size] Font('size'=>++)
[rpthead field font weight] Font('bold'=>++)
[rpthead field data query] Query(++)
[rpthead field data column] Cell(@@d['++'])

[rpthead rect x] Rct(++,::,::,::)
[rpthead rect y] Rct(::,++,::,::)
[rpthead rect width] Rct(::,::,++,::)
[rpthead rect height] Rct(::,::,::,++) 
[rpthead rect weight] WRct(++)

[section name] Section(++,sctr)
[section detail key query] Sector(++,::)
[section detail height] Sector(::,++)

[section group name] Group(++,::,Head,Foot,EndG)
[section group column] Group(::,++,Head,Foot,EndG)

[section group head height] GHead(++)
[section group head label rect x] XY(++,::)
[section group head label rect y] XY(::,@@iY + ++)
[section group head label font face] Font('name'=>|++|)
[section group head label font size] Font('size'=>++)
[section group head label font weight] Font('bold'=>++)
[section group head label string] Cell('++')
[section group head field rect x] XY(++,::)
[section group head field rect y] XY(::,@@iY + ++)
[section group head field font face] Font('name'=>|++|)
[section group head field font size] Font('size'=>++)
[section group head field font weight] Font('bold'=>++)
[section group head field data query] Query(++)
[section group head field data column] Cell(@@d['++'])

[section group foot height] GFoot(++)
[section group foot label rect x] XY(++,::)
[section group foot label rect y] XY(::,@@iY + ++)
[section group foot label font face] Font('name'=>|++|)
[section group foot label font size] Font('size'=>++)
[section group foot label font weight] Font('bold'=>++)
[section group foot label string] Cell('++')
[section group foot field rect x] XY(++,::)
[section group foot field rect y] XY(::,@@iY + ++)
[section group foot field font face] Font('name'=>|++|)
[section group foot field font size] Font('size'=>++)
[section group foot field font weight] Font('bold'=>++)
[section group foot field data query] Query(++)
[section group foot field data column] Cell(@@d['++'])
[section group foot field format] Sprintf(++)

[section detail label rect x] XY(++,::)
[section detail label rect y] XY(::,@@iY + ++)
[section detail label font face] Font('name'=>|++|)
[section detail label font size] Font('size'=>++)
[section detail label font weight] Font('bold'=>++)
[section detail label string] Cell('++')
[section detail field rect x] XY(++,::)
[section detail field rect y] XY(::,@@iY + ++)
[section detail field font face] Font('name'=>|++|)
[section detail field font size] Font('size'=>++)
[section detail field font weight] Font('bold'=>++)
[section detail field data query] Query(++)
[section detail field data column] Cell(@@d['++'])

[rptfoot height] @@iY += ++;
[rptfoot label rect x] XY(++,::)
[rptfoot label rect y] XY(::,@@iY + ++)
[rptfoot label font face] Font('name'=>|++|)
[rptfoot label font size] Font('size'=>++)
[rptfoot label font weight] Font('bold'=>++)
[rptfoot label string] Cell('++')
[rptfoot field rect x] XY(++,::)
[rptfoot field rect y] XY(::,@@iY + ++)
[rptfoot field font face] Font('name'=>|++|)
[rptfoot field font size] Font('size'=>++)
[rptfoot field font weight] Font('bold'=>++)
[rptfoot field data query] Query(++)
[rptfoot field data column] Cell(@@d['++'])
[rptfoot field format] Sprintf(++)

//
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rpt.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
$objWriter->save('php://output');
exit;
//
?>
