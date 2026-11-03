<?php
header('Content-Type: text/plain');

echo "PHP Version: " . PHP_VERSION . PHP_EOL;
echo "PDO available: " . (class_exists('PDO') ? 'YES' : 'NO') . PHP_EOL;

echo "PDO Drivers: ";
print_r(PDO::getAvailableDrivers());

echo "pdo_pgsql loaded: "
    . (extension_loaded('pdo_pgsql') ? 'YES' : 'NO');