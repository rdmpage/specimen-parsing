<?php


// Just do a run starting from scratch where references are in refs.txt

// train
if (1)
{
	$command = 'php parse_train.php core.xml';
	system($command);

	$command = 'crf_learn data/parsCit.template core.train core.model';
	system($command);

}

$command = 'php refs_to_train.php refs.txt';
system($command);

$command = 'php parse_train.php refs.src.xml';
system($command);

$command = 'crf_test  -m core.model refs.src.train > out.train';
system($command);

$command = 'php parse_results_to_xml.php out.train > out.xml';
system($command);

//$command = 'php parse_results_to_native.php out.xml';
//system($command);

?>




