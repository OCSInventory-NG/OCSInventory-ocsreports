<?php
function gz_uncompress($srcName, $dstName) {
$string = implode("", gzfile($srcName));
$fp = fopen($dstName, "w");
fwrite($fp, $string, strlen($string));
fclose($fp);
} 

function gz_compress($srcName, $dstName)
{
$fp = fopen($srcName, "r");
$data = fread ($fp, filesize($srcName));
fclose($fp);

$zp = gzopen($dstName, "w9");
gzwrite($zp, $data);
gzclose($zp);
}

?>