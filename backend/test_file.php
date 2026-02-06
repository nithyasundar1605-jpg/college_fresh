<?php
$file = 'uploads/profile_pics/profile_9_1769958485.jpeg';
if (file_exists($file)) {
    echo "File exists at $file\n";
    echo "Size: " . filesize($file) . " bytes\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "\n";
} else {
    echo "File DOES NOT exist at $file\n";
}
?>
