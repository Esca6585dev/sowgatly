<?php
require_once 'vendor/autoload.php';

use AlexandrMironov\PhpSmpp\Smpp;

try {
    $smpp = new Smpp([]); // Just try to instantiate it (configuration will be added later)
    echo "SMPP class found!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}