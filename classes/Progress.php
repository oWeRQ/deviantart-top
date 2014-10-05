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

	public function step()
	{
		$this->index++;

		if (time() - $this->update < 1)
			return;

		$this->update = time();

		$this->display();
	}

	public function end()
	{
		$this->display();
		echo "\r\n";
	}

	public function display()
	{
		$columns = getenv('COLUMNS');
		$speed = ceil($this->index / (time() - $this->start));
		$remain = $speed > 0 ? floor(($this->total - $this->index) / $speed / 60) : 0;
		$line = "index: {$this->index}/{$this->total}"
			." speed: {$speed} items/s"
			." remain: {$remain}m"
			." mem: ".floor(memory_get_usage() / 1024 / 1024).'M';
		echo "\r".str_pad($line, $columns);
	}
}