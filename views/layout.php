<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>deviantART Top</title>
	<link rel="stylesheet" href="css/jquery.modalWindow.css">
	<link rel="stylesheet" href="css/jquery.thumbsSlider.css">
	<link rel="stylesheet" href="css/jquery.gallery.css">
	<link rel="stylesheet" href="css/gallery.custom.css">
	<link rel="stylesheet" href="css/index.css">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="js/jquery.plugin.js"></script>
	<script src="js/jquery.iskey.js"></script>
	<script src="js/jquery.overlay.js"></script>
	<script src="js/jquery.windowCenter.js"></script>
	<script src="js/jquery.modalWindow.js"></script>
	<script src="js/jquery.thumbsSlider.js"></script>
	<script src="js/jquery.gallery.js"></script>
	<script src="js/index.js"></script>
</head>
<body>

<? if (isset($this->sidebar)): ?>
	<div class="l-sidebar">
		<?=$this->sidebar?>
	</div>
<? endif ?>

<div class="l-content">
	<?=$content?>
</div>

</body>
</html>