<?php
$im = imagecreatefrompng("test.png");

$yellow = imagecolorallocate($im, 255, 255, 0);
imagestring($im, 0, 30, 0, 'Hello world!', $yellow);

#header('Content-Disposition: Attachment;filename=image.png'); 
header('Content-Type: image/png');

imagepng($im);
imagedestroy($im);
?>
