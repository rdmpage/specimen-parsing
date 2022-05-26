<?php

// Parse XML-marked results and output in relevant format.
// This where we'd need to do checking and post-processing
// to clean up the output.

error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/author-parsing.php');

$filename = '';
$output_filename = '';

if ($argc < 2)
{
	echo "Usage: parse_results_to_native.php <XML file>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
	//$output_filename = basename($filename, '.xml') . '.out';	
	$output_filename = str_replace('.xml', '', $filename) . '.out';	
}

//touch($output_filename);

// Parse XML file and extract individual tokens and their tags
$xml = file_get_contents($filename);

$dom= new DOMDocument;
$dom->loadXML($xml);
$xpath = new DOMXPath($dom);

$citations = array();

foreach($xpath->query('//sequence') as $node)
{
	$obj = new stdclass;

	foreach ($node->childNodes as $n) { 
		switch ($n->nodeName)
		{
			case '#text':
				break;
				
			default:
				$tag = $n->nodeName;
				$text = $n->firstChild->nodeValue;
				
				if (!isset($obj->{$tag}))
				{
					$obj->{$tag} = array();
				}
				
				$obj->{$tag}[] = $text;
				break;
		}
	} 
	
	$citations[] = $obj;

}

echo json_encode($citations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);



?>

