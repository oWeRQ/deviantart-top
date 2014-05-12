<?php

class View
{
	protected $layout = false;

	public function __construct($layout = false)
	{
		if ($layout)
			$this->layout = $layout;
	}

	public function render($view, $data = null, $return = false)
	{
		$content = $this->renderPartial($view, $data, true);

		if ($this->layout)
			return $this->renderPartial($this->layout, array('content' => $content), $return);
		
		if ($return)
			return $content;
		else
			echo $content;
	}

	public function renderPartial($_view, $_data = null, $_return = false)
	{
		$_viewFile = 'views/'.$_view.'.php';

		if (is_array($_data))
			extract($_data);

		if ($_return) {
			ob_start();
			include $_viewFile;
			return ob_get_clean();
		}

		include $_viewFile;
	}
}