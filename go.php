<?php

error_reporting(E_ALL);

require_once(__DIR__ . '/vendor/autoload.php');

use Symfony\Component\Yaml\Yaml;


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

*/



$json = file_get_contents($occurrence_filename);
$obj = json_decode($json);

//print_r($obj);

$filename = 'test.yaml';
	
// Parse YAML file and convert to object
$stylesheet = Yaml::parseFile($filename);

//print_r($stylesheet);

// check sheet is OK

/*
$target = 'Papua New Guinea. Milne Bay Province: Normanby Island, Waikaiuna [Bay], 5 m, 17 Apr 1956 (fl, fr), L.J. Brass 25460 (holotype: LAE [acc. # 47392]; isotypes: A, K [K000922591], L [L.4307630], S, US [acc. # 2408171]).';
*/

// pre-process

// date ----------------------------------------------------------------------------------
// dates are complicated with different formatting rules, and sometimes they are missing all together





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

// format date as a string for output so we enclose it in a single tag
// dates can be complicated becaiuse of multiple ways to output them.

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


	//print_r($date_output);
	//exit();

}
$obj->date = join($date_output);

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

// coordinates

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


// specimen code--------------------------------------------------------------------------
// we may need to construct this from constituent parts

$obj->specimenCode = $obj->catalogNumber; // need rules


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

// recorded by ----------------------------------------------------------------------------

// may need et al. rules, e,g GBIF 1320666588
// E. L. Taylor, M. F. F. da Silva, J. Oliviero, C. S. Rosário, J. B. Silva & M. R. Santos
// E.L. Taylor et al.

// 1563116637 
// Poulsen, Axel Dalberg; Bau, Billy; Akoitai, Thomas; Akai, Saxon
// Axel Dalberg Poulsen, Billy Bieso Bau, Thomas Akoitai & Saxon Akai




// process -------------------------------------------------------------------------------
$output = array();

$tag = true;

foreach ($stylesheet['output'] as $key => $options)
{
	//echo "key=$key\n";
	$value = '';
	if (isset($obj->{$key}))
	{
	
		// translate tag if needed
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
	
		// special treatment?
		if (isset($options['format']))
		{
			$value = format_value($obj->{$key}, $options['format']);
		}
		else
		{
			$value = $obj->{$key};
		}
		
		//echo "value=$value\n";
		
		if (isset($options['prefix']))
		{
			$value = $options['prefix'] . $value;
		}						
	
		if ($tag)
		{
			$value = '<' . $tag_name . '>' . $value;
		}
		
	
		if (isset($options['units']))
		{
			$value .= ' ' . $options['units'];
		}
		
		if (isset($options['suffix']))
		{
			$value .= $options['suffix'];
		}		
		
		if (isset($options['delimiter']))
		{
			$value .= $options['delimiter'];
		}
		else
		{
			$value .= $stylesheet['delimiter'];		
		}
		
		if ($tag)
		{
			$space = false;
			if (preg_match('/\s$/', $value))
			{
				$space = true;
				$value = preg_replace('/\s$/', '', $value);
			}
			
			$value .= '</' . $tag_name . '>';
			
			if ($space)
			{
				$value .= ' ';
			}
		}
		

		$output[] =  $value;

	}

}

// print_r($output);

//$output[] = $stylesheet['end'];

//echo $target . "\n";
echo join('', $output) . "\n";

// echo date('M',strtotime($obj->eventDate));



?>

