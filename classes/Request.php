<?php

class Request
{
	public static function getUsername()
	{
		return isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'admin';
	}

	public static function isAjax()
	{
		return isset($_REQUEST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
	}

	public static function param($name, $default = null) {
		if (isset($_REQUEST[$name]) && !empty($_REQUEST[$name]))
			return $_REQUEST[$name];

		return $default;
	}

	public static function parseQuery($queryString) {
		$paramRegex = '/(^|\s+)(\w+):\s*("[^"]+"|\'[^\']+\'|\S+)/';

		preg_match_all($paramRegex, $queryString, $matches);

		$params = array();
		foreach ($matches[2] as $i => $match) {
			$params[$match] = trim($matches[3][$i], '\'"');
		}

		$query = trim(preg_replace($paramRegex, '', $queryString));

		return array(
			0 => $query,
			'query' => $query,
			1 => $params,
			'params' => $params,
		);
	}

	public static function buildQuery($query, $params) {
		$paramsString = '';

		foreach ($params as $key => $value) {
			if (!empty($value))
				$paramsString .= $key.':'.(strpos($value, ' ') === false ? $value : '\''.$value.'\'').' ';
		}

		return trim($paramsString.$query);
	}
}