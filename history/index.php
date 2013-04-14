<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>deviantART Top History</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="highcharts.js"></script>
</head>
<body>
<div id="container" style="width:100%; height:800px;"></div>
<?php 

$history = json_decode(file_get_contents('../galleries_history.json'), true);

$categories = array_keys($history);
$series = array();

foreach (current($history) as $gallery) {
	$galleryName = $gallery['name'];
	
	$data = array();

	foreach ($history as $date => $galleries) {
		foreach ($galleries as $gallery) {
			if ($gallery['name'] == $galleryName) {
				$data[] = $gallery['count'];
				break;
			}
		}
	}

	$series[] = array(
		'name' => $galleryName,
		'data' => $data,
	);
}

?>
<script>
	$('#container').highcharts({
		chart: {
			type: 'line'
		},
		title: {
			text: 'Count By Date'
		},
		xAxis: {
			categories: <?=json_encode($categories)?>
		},
		yAxis: {
			title: {
				text: 'Count'
			}
		},
		series: <?=json_encode($series)?>
	});
</script>
</body>
</html>