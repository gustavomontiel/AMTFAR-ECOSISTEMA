<?php
$cmd = 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe test_jasper.php';
$descriptorspec = [
   0 => ["pipe", "r"],
   1 => ["pipe", "w"],
   2 => ["pipe", "w"]
];
$process = proc_open($cmd, $descriptorspec, $pipes);
if (is_resource($process)) {
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);
    file_put_contents('out.txt', $stdout);
    file_put_contents('err.txt', $stderr);
}
