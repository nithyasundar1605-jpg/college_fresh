<?php
// Mock $_SERVER
$_SERVER['REQUEST_METHOD'] = 'POST';

// Mock Input
$input_data = json_encode(['email' => 'admin@college.com', 'password' => 'admin123']);
$tmp_input = tempnam(sys_get_temp_dir(), 'php_input');
file_put_contents($tmp_input, $input_data);

// Override file_get_contents to read from our temp file when php://input is requested
// Actually, we can't easily override file_get_contents for built-in streams in a simple script without stream wrappers.
// Instead, let's just modify login.php temporarily OR use a slightly different approach.
// Easier: Just modify the test runner to set a global variable and have login.php use it if set? No, that requires editing login.php.

// Use stream wrapper to mock php://input
class VarStream {
    private $string;
    private $position;
    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->string = $GLOBALS['mock_input'];
        $this->position = 0;
        return true;
    }
    public function stream_read($count) {
        $ret = substr($this->string, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    public function stream_eof() {
        return $this->position >= strlen($this->string);
    }
    public function stream_stat() { return []; }
}
stream_wrapper_unregister("php");
stream_wrapper_register("php", "VarStream");
$GLOBALS['mock_input'] = $input_data;

// Capture Output
ob_start();
include 'c:/Users/acer/college_fresh/backend/auth/login.php';
$output = ob_get_clean();

// Restore wrapper (optional, script ends anyway)
stream_wrapper_restore("php");

echo "--- START OUTPUT ---\n";
echo bin2hex($output); // Hex dump to see hidden chars
echo "\n--- END OUTPUT ---\n";
echo $output;
?>
