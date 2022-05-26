<?php

// Convert a Darwin Core record to a citation using a stylesheet

error_reporting(E_ALL);

require_once(__dir__ . '/author-parsing.php');

//----------------------------------------------------------------------------------------
// Convert date to CSL-JSON style array
function date_to_array(&$obj)
{
	$date_array = array();

	// use Darwin Core fields if we have them
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
	$obj->specimenCode = $obj->catalogNumber; 
	
	if (isset($stylesheet['output']['specimenCode']['field']))
	{
		$key = $stylesheet['output']['specimenCode']['field'];
		$key = str_replace('$', '', $key);
		
		if (isset($obj->{$key}))
		{
			$obj->specimenCode = $obj->{$key};
		}
	}

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
	if (isset($obj->recordedBy))
	{
		$result = parse_author_string($obj->recordedBy);
		
		//print_r($result);
		
		if (count($result->author) > 0)
		{
			$obj->recorders = $result->author;
			
			$names = array();
			
			$n = count($obj->recorders);
			$i = 0;
			
			foreach ($obj->recorders as $name)
			{
				$nameparts = array();
				if (isset($name->given))
				{
					$nameparts[] = $name->given;
				}
				if (isset($name->family))
				{
					$nameparts[] = $name->family;
				}
				
				$namestring = join(' ', $nameparts);
				
				if ($i > 0 && $i < $n - 1)
				{
					$names[] = ', ';
				}
				if ($n > 1 && $i == $n - 1)
				{
					$names[] = ' & ';
				}
			
				$names[] = $namestring;
				
				$i++;
			
			}
			$obj->recordedBy = join('', $names);
		}
	}
}	



//----------------------------------------------------------------------------------------
// Elevation might be a range (do we make handling this an option?)
function do_elevation(&$obj, $stylesheet)
{
	if (isset($obj->elevation))
	{
		if (isset($obj->elevationAccuracy) && $obj->elevationAccuracy != '')
		{
			if ($obj->elevationAccuracy > 0)
			{		
				$min = $obj->elevation - $obj->elevationAccuracy;
				$max = $obj->elevation + $obj->elevationAccuracy;
				$obj->elevation = $min . '-' . $max;	
			}
		}
	}

}

//----------------------------------------------------------------------------------------
// Build a list of instititions based on one or more related occurrences
// This mainly applies to plants
/*

If GBIF doesn't have a clustering but we "know" that there should be a cluster
we could fake it by creating a dummy file with just the bare detals that we need

{
  "currentOccurrence": {},
  "relatedOccurrences": [{
    "occurrence": {
      "institutionCode": "XA"
    }
  }, {
    "occurrence": {
      "institutionCode": "XB"
    }
  }, {
    "occurrence": {
      "institutionCode": "XC"
    }
  }]
}


*/
function do_related(&$obj, $stylesheet)
{
	$status_keys = array(
		'holotype',
		'isotype',
		'lectotype',
		'type',
		'unknown'
	);

	$institution_codes = array();

	$related_occurrences = array();

	$related_occurrences[] = $obj;
	
	// get more here if we can....
	$related_filename = 'gbif/related/' . $obj->key . '.json';
	if (file_exists($related_filename))
	{
		$json = file_get_contents($related_filename);
		$related_obj = json_decode($json);
		
		foreach ($related_obj->relatedOccurrences as $related)
		{
			$related_occurrences[] = $related->occurrence;
		}
	}

	foreach ($related_occurrences as $occurrence)
	{
		if (isset($occurrence->institutionCode))
		{
			$code = $occurrence->institutionCode;
		
			// some institution codes may have to be translated
			switch ($code)
			{
				case 'MNHN':
					$code = 'P';
					break;
				
				case 'NHMUK':
					$code = 'BM';
					break;
		
				default:
					// replace ong institution codes with collection codes
					// if they are uppercase
					if (!preg_match('/^\p{Lu}$/u', $occurrence->institutionCode))
					{
						if (isset($occurrence->collectionCode))
						{
							if (!preg_match('/^\p{Lu}$/u', $occurrence->collectionCode))
							{
								$code = $occurrence->collectionCode;
							}
					
						}
					
					}
				
					break;
			}
		
			// group by type status
		
			$status = 'unknown';
		
			if (isset($occurrence->typeStatus) && $occurrence->typeStatus != '')
			{
				$status = strtolower($occurrence->typeStatus);
			
				// translate status
				switch ($status)
				{
					case 'type':
						// big assumption!
						$status = 'isotype';
						break;
			
					default:
						break;
				}
			}
		
			if (!isset($institution_codes[$status]))
			{
				$institution_codes[$status] = array();
			}
			
			$institution_codes[$status][] = $code;
		}
	}

	// make unique
	foreach ($institution_codes as $status => $list)
	{
		$institution_codes[$status] = array_unique($list);
		asort($institution_codes[$status]);
	}
	
	print_r($institution_codes);

	$output = array();

	foreach ($status_keys as $key)
	{	
		if (isset($institution_codes[$key]))
		{
			$string = '';
	
			switch ($key)
			{
				case 'holotype':
				case 'isotype':
					$string = $key;
					if (count($institution_codes[$key]) > 1)
					{
						$string .= 's';
					}
					$string .= ': ';
			
					$string .= join(', ', $institution_codes[$key]);
					break;
			
				default:
					if (count($institution_codes[$key]) > 0)
					{		   
						$string .= join(', ', $institution_codes[$key]);
					}
					break;
			}
	
			if ($string != '')
			{
				$output[] = $string;
			}
		}
	}

	$obj->related = join(', ', $output);

}

//----------------------------------------------------------------------------------------
// Output one occurrence
function occurrence_to_string($obj, $stylesheet, $tag = false)
{
	// pre-process 
	do_date($obj, $stylesheet);
	do_coordinates($obj, $stylesheet);
	do_specimen($obj, $stylesheet);
	do_elevation($obj, $stylesheet);
	do_recorded_by($obj, $stylesheet);
	do_related($obj, $stylesheet);

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
				$value = str_replace('&', '&amp;', $value);
			
			
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


?>
