<?php

// Parse results and see how well model did


$filename = '';
if ($argc < 2)
{
	echo "Usage: results.php <output file>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

?>
		<html>
			<head>
				<!-- colour names from https://www.w3schools.com/colors/colors_names.asp -->
				<style>
					li {
						padding:0.4em;
					}
					
		

  .country { background-color: rgb(223,128,32); }
  .stateProvince { background-color: rgb(242,156,36); }
  .locality { background-color: rgb(254,191,132); }
  .geoCoordinate { background-color: rgb(186,252,136); }
  
  .elevation { background-color: rgb(250,128,159); }
  
  /*
  .day { background-color: rgb(241,135,252); }
  .month { background-color: rgb(241,135,252); }
  .year { background-color: rgb(241,135,252); }
  */
  
  .date  { background-color: rgb(241,135,252); }
  
  .recordedBy { background-color: rgb(143,234,139); }
  .recordNumber { background-color: rgb(143,234,139); }

  .specimenCode { background-color: rgb(255,247,136); }
  
  .institutionCode { background-color: rgb(123,134,234); }
			
					.note {
						background: Beige;
					}					
					
					
					
				</style>
			</head>
			<body>
<?php

$state = 0;
$current_tag = '';
$tag_count = -1;

$predicted = array();
$actual = array();
$token = array();

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	if ($line == '')
	{
		if ($state == 1)
		{
			echo '<div style="padding:4px;">';
		
			$n = count($token);
			
			
			
			for ($i = 0; $i < $n; $i++)
			{
				echo '<span class="' . $predicted[$i] . '">' . $token[$i] . ' </span>';			
			}
			
			echo '<span>Predicted</span>';
			echo '<br />';
			
			
			
			
			for ($i = 0; $i < $n; $i++)
			{
				echo '<span class="' . $actual[$i] . '">' . $token[$i] . ' </span> ';			
			}
			echo '<span>Ground truth</span>';
		
			echo '</div>';
		
			/*
			// emit
			//print_r($data);
			
			echo "<sequence>\n";

			
			foreach ($data as $d)
			{
				echo '<' . $d->tag . '>';
				echo htmlspecialchars(join(' ', $d->tokens), ENT_XML1 | ENT_COMPAT, 'UTF-8');
				echo '</' . $d->tag . '>';
				echo ' ';
			}
			echo "\n";
			echo "</sequence>\n";
			*/
			
			//print_r($predicted);
			//print_r($actual);
						
		}
		$state = 0;
		$predicted = array();
		$actual = array();
		$token = array();
		
	}
	else
	{
		$state = 1;
	}
	
	if ($state == 1)
	{
		$row = preg_split("/[\s|\t]+/u", $line);	
		
		// print_r($row);
		
		$n = count($row);
		$predicted_tag = $row[$n - 1];
		$actual_tag = $row[$n - 2];
		
		$predicted[] = $predicted_tag;
		$actual[] = $actual_tag;
		$token[] = $row[0];
	}
}



?>

</body>
</html>

