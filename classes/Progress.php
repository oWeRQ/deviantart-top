<?php

class Progress
{
	protected $total;
	protected $index;
	protected $start;
	protected $update;

	public function __construct($total)
	{
		$this->total = $total;
		$this->index = 0;
		$this->start = time()-1;
		$this->update = $this->start;
	}

	public function step($force = false)
	{
		$this->index++;

		if ($force === false && time() - $this->update < 1)
			return;

		$this->update = time();
		$columns = getenv('COLUMNS');
		$speed = ceil($this->index / (time() - $this->start));
		$line = "index: ".$this->index."/".$this->total
			." speed: $speed items/s remain: ".floor(($this->total - $this->index) / $speed / 60)."m"
			." mem: ".floor(memory_get_usage() / 1024 / 1024).'M';
		echo "\r".str_pad($line, $columns);
	}

	public function end()
	{
		$this->step(true);
		echo "\r\n";
	}
}