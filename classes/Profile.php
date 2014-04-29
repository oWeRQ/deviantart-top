<?php

class Profile
{
	public static $tokens = array();

	public static function begin($token)
	{
		if (!array_key_exists($token, self::$tokens)) {
			self::$tokens[$token] = array(
				'count' => 0,
				'first' => microtime(true),
				'start' => microtime(true),
				'end' => false,
				'min' => false,
				'max' => false,
				'total' => 0,
			);
		} else {
			self::$tokens[$token]['start'] = microtime(true);
		}
	}

	public static function end($token)
	{
		if (!array_key_exists($token, self::$tokens))
			throw new Exception("Profile $token doesn't begin");

		self::$tokens[$token]['count']++;
		self::$tokens[$token]['end'] = microtime(true);

		$time = self::$tokens[$token]['end'] - self::$tokens[$token]['start'];

		if (self::$tokens[$token]['min'] > $time || self::$tokens[$token]['min'] === false)
			self::$tokens[$token]['min'] = $time;

		if (self::$tokens[$token]['max'] < $time)
			self::$tokens[$token]['max'] = $time;

		self::$tokens[$token]['total'] += $time;
	}

	public static function getTokens($sort = 'end')
	{
		$tokens = self::$tokens;

		foreach ($tokens as &$token) {
			if ($token['end'] !== false)
				$token['avg'] = $token['total'] / $token['count'];
		}

		uasort($tokens, function($a, $b) use($sort) {
			if ($a[$sort] == $b[$sort])
				return 0;

			return $a[$sort] < $b[$sort] ? -1 : 1;
		});

		return $tokens;
	}

	public static function consoleScript()
	{
		return '<script>console.log('.json_encode(self::getTokens()).');</script>';
	}
}