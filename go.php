<?php

error_reporting(E_ALL);

require_once(__DIR__ . '/vendor/autoload.php');

use Symfony\Component\Yaml\Yaml;



//----------------------------------------------------------------------------------------
function date_to_array(&$obj)
{
	$date_array = array();

	if (isset($obj->year))
	{
		$date_array[] = (Integer)$obj->year;
	
		if (isset($obj->month))
		{	
			$date_array[] = (Integer)$obj->month;

			if (isset($obj->day))
			{	
				$date_array[] = (Integer)$obj->day;
			}
		}		
	}
	else
	{
		// do we have a verbatim date?
		if (isset($obj->verbatimEventDate))
		{
			if (($timestamp = strtotime($obj->verbatimEventDate)) === false) 
			{
				// Failed to parse, maybe we can try and clean and parse?
				// e.g. 1843536445 Transcribed d/m/y: 16/8/54
			}
			else
			{
				// Success
				$date = getdate($timestamp);
		
				$date_array[] = (Integer)$date['year'];
				$date_array[] = (Integer)$date['mon'];
				$date_array[] = (Integer)$date['mday'];
			}					
		}
	}

	return $date_array;
}

//----------------------------------------------------------------------------------------
/**
 * Dates are complicated with different formatting rules, and sometimes they are 
 * missing all together.
 */
function do_date(&$obj, $stylesheet)
{
	// get date as CSL-JSON like array [year, month, day]
	$date_array = date_to_array($obj);
	
	if (count($date_array) == 0)
	{
		return; // no date information
	}

	// format date as a string for output so we enclose it in a single tag
	// dates can be complicated because of multiple ways to output them.

	// abbreviated English
	$months_M = array(
	'Jan',
	'Feb',
	'Mar',
	'Apr',
	'May',
	'Jun',
	'Jul',
	'Aug',
	'Sep',
	'Oct',
	'Nov',
	'Dec',
	);

	// full English
	$months_F = array(
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July',
	'August',
	'September',
	'October',
	'November',
	'December',
	);

	// Roman numerals
	$months_R = array(
	'I',
	'II',
	'III',
	'IV',
	'V',
	'VI',
	'VII',
	'VIII',
	'IX',
	'X',
	'XI',
	'XII',
	);

	if (isset($stylesheet['output']['date']))
	{
		$date_output = array();
	
		$n = count($date_array);
	
		if ($n == 3)
		{
			// day
			if (isset($stylesheet['output']['date']['day']))
			{
				$delimiter = (isset($stylesheet['output']['date']['day']['delimiter'])) ? $stylesheet['output']['date']['day']['delimiter'] : ' ';
			
				$date_output[] = $date_array[2];
				$date_output[] = $delimiter;
			}		
		}
	
		if ($n > 2)
		{
			// month
			if (isset($stylesheet['output']['date']['month']))
			{
				$delimiter = (isset($stylesheet['output']['date']['month']['delimiter'])) ? $stylesheet['output']['date']['month']['delimiter'] : ' ';
			
				if (isset($stylesheet['output']['date']['month']['format']))
				{
					switch ($stylesheet['output']['date']['month']['format'])
					{
						case 'F':
							$date_output[] = $months_F[$date_array[1]-1];
							break;			

						case 'M':
							$date_output[] = $months_M[$date_array[1]-1];
							break;
						
						case 'r':
							$date_output[] = strtolower($months_R[$date_array[1]-1]);
							break;			

						case 'R':
							$date_output[] = $months_R[$date_array[1]-1];
							break;			
								
						default:
							break;
					}			
			
				}
				else
				{
					$date_output[] = $date_array[1];
				}
			
				$date_output[] = $delimiter;
			}
		}
	
		if ($n >= 1)
		{
			// year
			if (isset($stylesheet['output']['date']['year']))
			{
				$delimiter = (isset($stylesheet['output']['date']['year']['delimiter'])) ? $stylesheet['output']['date']['year']['delimiter'] : '';
			
				$date_output[] = $date_array[0];
				$date_output[] = $delimiter;
			}
		}

	}
	$obj->date = join($date_output);
}


//----------------------------------------------------------------------------------------
/**
 * @brief Convert a decimal latitude or longitude to deg° min' sec'' format in HTML
 *
 * @param decimal Latitude or longitude as a decimal number
 *
 * @return Degree format
 */
function decimal_to_degrees($decimal, 
		$degree_symbol="°", 
		$minutes_symbol="'", 
		$seconds_symbol='"')
{

	$decimal = abs($decimal);
	$degrees = floor($decimal);
	$minutes = floor(60 * ($decimal - $degrees));
	$seconds = round(60 * (60 * ($decimal - $degrees) - $minutes));
	
	if ($seconds == 60)
	{
		$minutes++;
		$seconds = 0;
	}
	
	$result = $degrees . $degree_symbol;
	
	if ($minutes != 0)
	{
		$result .= $minutes . $minutes_symbol;
	}
	
	if ($seconds != 0)
	{
		$result .= $seconds . $seconds_symbol;
	}
	return $result;
}


//----------------------------------------------------------------------------------------
// Format a value, for example make a string upper case or lower case.
function format_value($value, $format)
{
	switch ($format)
	{
		case 'uppercase':
			$value = mb_strtoupper($value);
			break;

		case 'lowercase':
			$value = mb_strtolower($value);
			break;
			
		default:
			break;	
	}
	
	return $value;
}


//----------------------------------------------------------------------------------------
// Take decimal coordinates and convert to desired output style
// To do, support precision, etc.
function do_coordinates(&$obj, $stylesheet)
{
	if (isset($obj->decimalLatitude) && isset($obj->decimalLongitude))
	{
		$degree_symbol 	= "°";
		$minutes_symbol = "'"; 
		$seconds_symbol = '"';
		$format 		= 'dms';
	
		$options = $stylesheet['output']['latitude'];
	
	
		$degree_symbol = (isset($options['degrees']) ? $options['degrees'] : $degree_symbol );
		$minutes_symbol = (isset($options['minutes']) ? $options['minutes'] : $minutes_symbol );
		$seconds_symbol = (isset($options['seconds']) ? $options['seconds'] : $seconds_symbol );	
		$format = (isset($options['format']) ? $options['format'] : $format );
	
		switch ($format)
		{
			case 'decimal':
				$obj->latitude = $obj->decimalLatitude . $degree_symbol;
				$obj->longitude = $obj->decimalLongitude . $degree_symbol;			
				break;
	
			case 'dms':
			default:
				$obj->latitude = decimal_to_degrees($obj->decimalLatitude, $degree_symbol, $minutes_symbol, $seconds_symbol);
				$obj->longitude = decimal_to_degrees($obj->decimalLongitude, $degree_symbol, $minutes_symbol, $seconds_symbol);
				break;
		}
	
		$obj->latitude .= ($obj->decimalLatitude < 0.0 ? 'S' : 'N');
		$obj->longitude .= ($obj->decimalLongitude < 0.0 ? 'W' : 'E');

	}
}

//----------------------------------------------------------------------------------------
// Output specimen
function do_specimen(&$obj, $stylesheet)
{
	// default
	$obj->specimenCode = $obj->catalogNumber; // need rules

	// is specimen a type?
	if (isset($obj->typeStatus) && $obj->typeStatus != '')
	{
		if (isset($stylesheet['output']['specimenCode']['type']))
		{
			$options = $stylesheet['output']['specimenCode']['type'];
		
			if (isset($options['prefix']))
			{
				$prefix = '';
			
				if (preg_match('/^\$/', $options['prefix']))
				{
					$key = $options['prefix'];
					$key = str_replace('$', '', $key);
					if (isset($obj->{$key}))
					{
						$prefix = $obj->{$key};
					
						if (isset($options['format']))
						{
							$prefix = format_value($prefix, $options['format']);					
						}
					}
				}
				else
				{
					$prefix = $options['prefix'];
				}
		
				if ($prefix != '')
				{
					$obj->specimenCode = $prefix . ' ' . $obj->specimenCode;
				}
			}		
		
			if (isset($options['suffix']))
			{
				$obj->specimenCode .= $options['suffix'];
			}
		}
	}
}

//----------------------------------------------------------------------------------------
// Collectors
function do_recorded_by(&$obj, $stylesheet)
{
	// need to parse string and format, string will be a mess, use author_parsing 
	// code based on citation parsing

	// for now do nothing	

}	


// recorded by ----------------------------------------------------------------------------

// may need et al. rules, e,g GBIF 1320666588
// E. L. Taylor, M. F. F. da Silva, J. Oliviero, C. S. Rosário, J. B. Silva & M. R. Santos
// E.L. Taylor et al.

// 1563116637 
// Poulsen, Axel Dalberg; Bau, Billy; Akoitai, Thomas; Akai, Saxon
// Axel Dalberg Poulsen, Billy Bieso Bau, Thomas Akoitai & Saxon Akai


// depth/elevation
// the field elevation accuracy shuld be handled so we can have elevation ranges


//----------------------------------------------------------------------------------------
// Output one occurrence
function occurrence_to_string($obj, $stylesheet, $tag = false)
{
	// pre-process 
	do_date($obj, $stylesheet);
	do_coordinates($obj, $stylesheet);
	do_specimen($obj, $stylesheet);

	$output = array();

	// iterature through stylesheet, outputting as we go, taking into
	// account the relevant options.
	foreach ($stylesheet['output'] as $key => $options)
	{
		$value = '';
		
		if (isset($obj->{$key}))
		{
			// translate tag if needed. for example, we may want to treat different
			// Darwin Core fields as the same
			if ($tag)
			{
				switch ($key)
				{
					case 'latitude':
					case 'longitude':
						$tag_name = 'geoCoordinate';
						break;
			
					default:
						$tag_name = $key;
						break;
				}
			}
	
			// special formatting?
			if (isset($options['format']))
			{
				$value = format_value($obj->{$key}, $options['format']);
			}
			else
			{
				$value = $obj->{$key};
			}
		
			if (isset($options['prefix']))
			{
				$value = $options['prefix'] . $value;
			}						
	
			// are we including tags for training? if so, open tag
			if ($tag)
			{
				$value = '<' . $tag_name . '>' . $value;
			}		
	
			// does value have units?
			if (isset($options['units']))
			{
				$value .= ' ' . $options['units'];
			}
		
			if (isset($options['suffix']))
			{
				$value .= $options['suffix'];
			}		
		
			// is there a field-specific delimiter?
			if (isset($options['delimiter']))
			{
				$value .= $options['delimiter'];
			}
			else
			{
				$value .= $stylesheet['delimiter'];		
			}
		
			// close training tag
			if ($tag)
			{
				// if value has a trailing space we want to insert the tag
				// before that space
			
				$space = false;
				if (preg_match('/\s$/', $value))
				{
					$space = true;
					$value = preg_replace('/\s$/', '', $value);
				}
			
				$value .= '</' . $tag_name . '>';
			
				// if had trailing space, add back
				if ($space)
				{
					$value .= ' ';
				}
			}
		
			$output[] = $value;
		}

	}

	return join('', $output) . "\n";
}



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
1454554267,
1454544712,
1563116637,
2828757737
);

$ids=array(
2464718898
);

// three specimens that Plazi has mangled 
// http://localhost/~rpage/plazi-tester/?uri=03CE87C3-6C78-C034-BDB4-FDCD03B113CF
// https://doi.org/10.11646/phytotaxa.547.2.9
// BRAZIL: Bahia: Camacã, RPPN, Serra Bonita, 15°23’30”S, 39°33’55”W, 835 m [a.s.l.], 3 March 2006, A. M. Amorim et al. 5696 ( holotype : CEPEC [2 sheets]!, isotypes: RB !, SP !)
$ids=array(
1424191576,
1090501610,
2452944718,
);


// get style sheet
$filename = 'test.yaml';

// Parse YAML file and convert to object
$stylesheet = Yaml::parseFile($filename);


foreach ($ids as $id)
{
	$occurrence_filename = 'gbif/' . $id . '.json';

	// get occurrence from GBIF
	$json = file_get_contents($occurrence_filename);
	$obj = json_decode($json);

	//print_r($obj);

	// Check it is OK (how?)

	$output = occurrence_to_string($obj, $stylesheet, true);

	echo '<sequence>' . $output . '</sequence>' . "\n";

}


?>

