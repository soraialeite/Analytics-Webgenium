<?php
header("Content-type: image/png");

$legenda = 'chdl=Visualizacoes|Visitas&chco=0077CC,E34E00&chdlp=t';
$tamanho = 'chs=450x190';
$tipo 	 = 'cht=lc&chf=bg,s,FFFFFF&chco=0077CC,E34E00&chxt=y,x&chls=2|2&chxl=1:|'.$_GET['start_format'].'|'.$_GET['end_format'].'';
$dados 	 = 'chd=t2:'.$_GET['visualizacoes'].'|'.$_GET['visitas'].'&chds='.$_GET['minimo'].','.$_GET['maximo'];

die(file_get_contents('http://chart.apis.google.com/chart?'.$tamanho.'&'.$tipo.'&'.$legenda.'&'.$dados));

?>