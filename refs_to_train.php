<?php

// Convert a list of specimen citations (one per line) to training XML format
// We wrap each reference in a default tag.

error_reporting(E_ALL);

$filename = '';
$output_filename = '';

if ($argc < 2)
{
	echo "Usage: refs_to_train.php <references file>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
	$output_filename = str_replace('.txt', '', $filename) . '.src.xml';	
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= "<dataset>\n";


$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	if ($line != '')
	{
		$line = strip_tags($line);	
		$line = trim($line);	
	
		$xml .=  '<sequence>' . "\n";
		$xml .=  '<note>' . htmlspecialchars($line, ENT_XML1 | ENT_NOQUOTES, 'UTF-8') . '</note>' . "\n";
		$xml .=  '</sequence>'. "\n";
	}
}

$xml .=  '</dataset>'. "\n";

file_put_contents($output_filename, $xml);

?>
