<?php

class Progress
{
	protected $total;
	protected $index;
	protected $start;

	public function __construct($total)
	{
		$this->total = $total;
		$this->index = 0;
		$this->start = time()-1;
	}

	public function step()
	{
		$this->index++;
		$columns = getenv('COLUMNS');
		//if ($this->index % 100 === 0)
		$speed = floor($this->index / (time() - $this->start));
		$line = "index: ".$this->index."/".$this->total
			." speed: $speed items/s remain: ".floor(($this->total - $this->index) / $speed / 60)."m"
			." mem: ".floor(memory_get_usage() / 1024 / 1024).'M';
		echo "\r".str_pad($line, $columns);
	}

	public function end()
	{
		echo "\r\n";
	}
}