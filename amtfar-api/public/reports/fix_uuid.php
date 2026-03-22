<?php
$c = file_get_contents('boleta.jrxml');
$c = preg_replace_callback('/uuid="([^"]+)"/', function($m) {
    if (strlen($m[1]) == 36) return $m[0];
    return 'uuid="'.sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)).'"';
}, $c);
file_put_contents('boleta.jrxml', $c);
