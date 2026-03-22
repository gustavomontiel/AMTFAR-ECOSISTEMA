<?php
$cmd = 'vendor\geekcom\phpjasper\bin\jasperstarter\bin\jasperstarter process "public\reports\boleta.jrxml" -o "public\reports\boleta_out_test" -f pdf -t mysql -u root -H 127.0.0.1 -n amtfar --db-port 3306 2>&1';
$output = shell_exec($cmd);
file_put_contents('jasper_error_2.txt', $output);
