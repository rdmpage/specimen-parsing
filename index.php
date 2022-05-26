<?php
?>

<html>
<head>
	<meta charset="utf-8" />
	<title>Material citations</title>

	<link href="entireframework.min.css" rel="stylesheet" type="text/css">
	<script src="js/jquery.js" type="text/javascript"></script>
	
	<style>
			.hero {
				background: #eee;
				padding: 20px;
				border-radius: 10px;
				margin-top: 1em;
			}

			.hero h1 {
				margin-top: 0;
				margin-bottom: 0.3em;
			}
	</style>	
	
</head>
<body>
<div class="container">
<div class="hero">
<h1>
	Parse material citations
</h1>
<p>
Convert material citation strings into structured JSON.
</p>
</div>

<p>
Enter material citation strings, one per line, then click <strong>Parse</strong>.
To create training data use the <a href="editor.html">editor</a>.
</p>

<div>
	<textarea class="smooth" style="font-size:1em;box-sizing: border-box;width:100%;" id="text"  name="text" rows="10" >
VIETNAM. Khanh Hoa Province: Khanh Vinh District, Son Thai Commune, 12°12’59.4”N, 108°44’54.5”E, ca. 1,000 m elev., 23 September 2018, T. C. Hsu 10952 (holotype TAIF-537150!; isotype: SGN!)
	</textarea>
    <br />
   <button class="btn btn-a btn-sm smooth" onclick="parse()">Parse</button>
   
</div>

<!--
<div class="msg">
Parsed citations will appear below.
</div>
-->


<div id="output" style="display:none;">
<p><a id="apixml" href=".">API call for result in XML</a></p>
<p><a id="api" href=".">API call for result below</a></p>
<div id="result" style="padding:1em;font-family:monospace;font-size:0.8em;white-space:pre-wrap;background-color:rgb(50,50,50);color:#8EFA00;"></div>
</div>


<!--
<h2>OpenURL</h2>
<div id="openurl"></div>
-->

</div>

<script>
function parse() {
	var output =  document.getElementById("output");
	output.style.display = "none";

	document.getElementById("result").innerHTML = "";

	var text = document.getElementById("text").value;
	
	var url = 'api.php?text=' + encodeURIComponent(text);
		
	var xmlurl = 'api.php?text=' + encodeURIComponent(text) + '&format=xml';

	 $.getJSON(url + '&callback=?', function(data) {
		if (data) {
			document.getElementById("api").setAttribute('href', url);
			document.getElementById("apixml").setAttribute('href', xmlurl);
			document.getElementById("result").innerHTML = JSON.stringify(data, null, 2);
		} else {
		
		}
		output.style.display = "block";
	 });
}


</script>

</body>
</html>

<?php

?>
