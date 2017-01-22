<?php
error_reporting(E_ALL);
if (php_sapi_name() !== 'cli') {
    exit() ;
}

require 'vendor/autoload.php';

$header1 = <<<'EOT'
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="
EOT;
$header2 = <<<'EOT'
markdown.css">
</head>
<body>
EOT;
$footer = <<<'EOT'
</body>
</html>
EOT;
$Parsedown = new Parsedown();

function scan_file($dir_path, $relative_root = '')
{
	global $header1, $header2, $footer, $Parsedown;
	$files = array_diff(scandir($dir_path), array('..', '.'));
	foreach ($files as &$file) {
		$file_path = "$dir_path/$file";
		if (is_dir($file_path)) {
			scan_file($file_path, $relative_root . '../');
		} else {
			if (substr($file_path, -3) === '.md') {
				echo "$file_path\n";
				$markdown = preg_replace('/(\\[.*?\\]\\(.*?)\\.md\\)/', '$1.html)', file_get_contents($file_path));
				$html = $header1 . $relative_root . $header2 . $Parsedown->text($markdown) . $footer;
				file_put_contents(substr($file_path, 0, -3) . '.html', $html);
				if (strtolower($file) === 'readme.md') {
					file_put_contents("$dir_path/index.html", $html);
				}
			}
		}
	}
}

if ($argc > 1 && file_exists($argv[1])) {
    $file_path = realpath($argv[1]);
    scan_file($file_path);
    copy(__DIR__ . '/markdown.css', $file_path . '/markdown.css');
} else {
    echo "File not exists.\n";
}
