<?php
echo "Current File: " . __FILE__ . "\n";
echo "Current Dir: " . __DIR__ . "\n";
echo "Parent Dir: " . dirname(__DIR__) . "\n";
echo "Grandparent Dir: " . dirname(dirname(__DIR__)) . "\n";
echo "Target Path: " . dirname(dirname(__DIR__)) . "/certificates/" . "\n";
echo "Exists: " . (is_dir(dirname(dirname(__DIR__)) . "/certificates/") ? "YES" : "NO") . "\n";
?>
