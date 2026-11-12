<?php
$im = imagecreatefromstring(file_get_contents('test_gtraffic2.png'));
$nonTrans = 0;
for ($px = 0; $px < imagesx($im); $px += 2) {
    for ($py = 0; $py < imagesy($im); $py += 2) {
        $rgba = imagecolorat($im, $px, $py);
        $alpha = ($rgba & 0x7F000000) >> 24;
        if ($alpha < 120) $nonTrans++;
    }
}
echo "Non-transparent pixels in test_gtraffic2.png: {$nonTrans}\n";
unlink('test_gtraffic1.png');
unlink('test_gtraffic2.png');
unlink('test_gtraffic.php');

