<?php

error_reporting(E_ALL);

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/gbif_to_citation.php');


use Symfony\Component\Yaml\Yaml;


//----------------------------------------------------------------------------------------
function get($url, $format = '')
{
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	if ($format != '')
	{
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: " . $format));	
	}
	
	$response = curl_exec($ch);
	if($response == FALSE) 
	{
		$errorText = curl_error($ch);
		curl_close($ch);
		die($errorText);
	}
	
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	
	curl_close($ch);
	
	return $response;
}

//----------------------------------------------------------------------------------------


$occurrence_filename = 'gbif/3391977334.json';
$occurrence_filename = 'gbif/1928872005.json';
$occurrence_filename = 'gbif/2806595618.json';
$occurrence_filename = 'gbif/1843536445.json'; // verbatim date, extra stuff
$occurrence_filename = 'gbif/912400147.json'; // verbatim date
//$occurrence_filename = '574717666.json'; // https://phytokeys.pensoft.net/article/48573/

$occurrence_filename = 'gbif/1424105040.json';
//$occurrence_filename = 'gbif/2998756127.json';
//$occurrence_filename = 'gbif/3766061302.json';
//$occurrence_filename = 'gbif/1320666588.json';

//$occurrence_filename = 'gbif/2828757737.json'; // https://www.biodiversitylibrary.org/page/61647885

$occurrence_filename = 'gbif/1563116637.json';
$occurrence_filename = 'gbif/1454554267.json';
$occurrence_filename = 'gbif/1454544712.json';
//$occurrence_filename = 'gbif/1090339037.json'; // another verison of 1454544712 with BARCODE BISH


/*

1454554267	https://journals.rbge.org.uk/ejb/article/view/1666/1557

1454544712 https://journals.rbge.org.uk/ejb/article/view/1654/1545 decimal latlon

2464718898	https://ia902305.us.archive.org/23/items/biostor-63960/biostor-63960.pdf

*/

$ids = array(
2464718898
);


/*
$ids = array(
1454554267,
1454544712,
1563116637,
2828757737
);
*/

/*
$ids=array(
2464718898, // Sobralia purpurea Dressler
);
*/

/*
// three specimens that Plazi has mangled 
// http://localhost/~rpage/plazi-tester/?uri=03CE87C3-6C78-C034-BDB4-FDCD03B113CF
// https://doi.org/10.11646/phytotaxa.547.2.9
// BRAZIL: Bahia: Camacã, RPPN, Serra Bonita, 15°23’30”S, 39°33’55”W, 835 m [a.s.l.], 3 March 2006, A. M. Amorim et al. 5696 ( holotype : CEPEC [2 sheets]!, isotypes: RB !, SP !)
$ids=array(
1424191576,
1090501610,
2452944718,
);
*/
/*
$ids=array(
1258824207, // Scaphyglottis monspirrae Dressler, has elevationAccuracy 100
);
*/

// not a type but same details as 
// Type. Ecuador. Pichincha: canton Quito, parroquia Pacto, primary road between the town of 
// Pacto and Mashpi Lodge, 0°9'49.3"N , 78°49'14.6"W, 1662 m, 15 Mar 2019, 
// J.L. Clark & L. Jost 16286 (holotype: US; isotypes: ECUAMZ, QCA, SEL). 
// Plazi 078AF250231E51F8AFAB447BCD8A1B72

/*	
$ids=array(
2974135324
);
*/

/*
// NEW ZEALAND. CHATHAM ISLANDS. Tupuangi Lagoon, Pitt I., Chatham Is., 1 February 1957, B. G Hamlin. 692 (Holotype: WELT SP003332, isotype CHR 121104)
// 5773DE60FF8BFF99FF7EFACBFEC0F8D3
$ids=array(
1091144232
);
*/

$ids = array();

$files = scandir('gbif');

foreach ($files as $filename)
{
	if (preg_match('/\.json$/', $filename))
	{
		$id  = str_replace('.json', '', $filename);
		$ids[] = $id;
	}
}

$ids=array(1056006536);


$ids=array(
//1424191576,
//1090501610,
2452944718,
);


// get style sheet
$filename = 'test.yaml';

// Parse YAML file and convert to object
$stylesheet = Yaml::parseFile($filename);


foreach ($ids as $id)
{
	$occurrence_filename = 'gbif/' . $id . '.json';
	
	if (!file_exists($occurrence_filename))
	{
		$json = get('https://api.gbif.org/v1/occurrence/' . $id);
		file_put_contents($occurrence_filename, $json);
	}
	
	// related
	$related_filename = 'gbif/related/' . $id . '.json';
	if (!file_exists($related_filename))
	{
		$json = get('https://api.gbif.org/v1/occurrence/' . $id . '/experimental/related');
		file_put_contents($related_filename, $json);
	}

	// get occurrence from GBIF
	$json = file_get_contents($occurrence_filename);
	$obj = json_decode($json);

	//print_r($obj);

	// Check it is OK (how?)

	$output = occurrence_to_string($obj, $stylesheet, true);

	echo '<sequence>' . $output . '</sequence>' . "\n";

}


?>

