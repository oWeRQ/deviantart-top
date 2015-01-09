<?php

class ImageSig
{
	public $size = 16;
	public $colormap = null;
	public $colormap_colors = [];
	public $color_indexes = [];

	public function __construct($colormap = 'colormap.php') {
		$this->colormap_colors = require_once($colormap);
		$this->makeColorMap();
	}

	public function makeColorMap()
	{
		$this->colormap = new Imagick();
		$this->colormap->newImage(1, 32, 'none');
		$this->colormap->setImageType(imagick::IMGTYPE_PALETTE);
		//$this->colormap->setImageType(imagick::IMGTYPE_PALETTEMATTE);

		$this->color_indexes = [];

		foreach ($this->colormap_colors as $i => $color) {
			$this->color_indexes[pack('CCC', $color[0], $color[1], $color[2])] = dechex($i);
			$this->colormap->setImageColormapColor($i, 'rgb('.join(',', $color).')');
		}
	}

	public function makeSig($filename, $sig_file = null)
	{
		try {
			$sig = new Imagick($filename);
		} catch (ImagickException $e) {
			echo "\nImagickException: ".$e->getMessage().", filename: $filename\n";
			return null;
		}

		$sig->scaleImage($this->size, $this->size);
		$sig->mapImage($this->colormap, false);

		if ($sig_file) {
			$sig->setImageFormat('png');
			$sig->writeImage($sig_file);	
		}

		$sig->setImageFormat('rgb');
		$sig_blob = $sig->getImageBlob();
		$sig->destroy();

		$sig_data = '';

		for ($i = 0; $i < 256; $i++) {
			$raw_color = substr($sig_blob, $i*3, 3);
			if (isset($this->color_indexes[$raw_color])) {
				$sig_data .= $this->color_indexes[$raw_color];
			} else {
				$sig_data .= 0;
			}
		}

		return $sig_data;
	}
}
