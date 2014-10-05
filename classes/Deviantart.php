<?php

class Deviantart
{
	protected $oEmbedUrl = 'http://backend.deviantart.com/oembed?url=';
	
	public $user_id = null;
	public $ui = null;
	public $ua = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:27.0) Gecko/20100101 Firefox/27.0';
	public $cookie_file = '../.cookie';

	public static $cache_time = 259200; //3600*24*3
	//public static $cache_time = 604800; //3600*24*7
	public static $silent = false;

	public static function silent($value)
	{
		self::$silent = $value;
	}

	public function __construct($user_id = null)
	{
		$this->user_id = $user_id;
		$this->cookie_file = realpath(dirname(__FILE__).'/'.$this->cookie_file);
	}

	public function userinfo($username)
	{
		$file = 'cache/profiles/'.$username.'.html';

		if (file_exists($file) && time()-filemtime($file) < self::$cache_time) {
			$html = file_get_contents($file);
		} else {
			$url = 'http://'.$username.'.deviantart.com';
			$html = $this->sendGet($url);
			if ($html) {
				file_put_contents($file, $html);
			}
		}

		if (empty($html))
			return null;

		if (strpos($html, 'error-deactivated') !== false)
			return null;

		libxml_use_internal_errors(true);
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$xpath = new DOMXPath($doc);

		$info = $xpath->query("//div[@class='pbox pppbox']")->item(0);

		if (!$info)
			return null;
		
		preg_match_all('/([\d,]+)[ ]+(\w+)/', $info->textContent, $matches);

		$counts = array();
		foreach ($matches[2] as $i => $name) {
			$counts[strtolower($name)] = intval(str_replace(',', '', $matches[1][$i]));
		}

		/*
		preg_match_all('/data-userid="(\d+)"/', $html, $matches);
		if (count($matches[1]) === 0) {
			return null;
		}

		if (count($matches[1]) > 1) {
			var_dump($matches[1]);
			die();
		}

		$counts['id'] = $matches[1][0];
		*/

		return $counts;
	}

	public function sendCall($callObject, $callMethod, $callParams, $method = 'get', $retry = 3)
	{
		return $this->sendCalls(array(
			array(
				'object' => $callObject,
				'method' => $callMethod,
				'params' => $callParams,
			),
		), $method, $retry)[0]['response']['content'];
	}

	public function sendCalls(array $calls, $method = 'get', $retry = 3)
	{
		$parts = array(
			'scheme' => 'http',
			'host' => 'my.deviantart.com',
			'path' => '/global/difi/',
			'query' => '',
		);

		$params = array(
			'c' => array_map(function($call){
				return '"'.$call['object'].'","'.$call['method'].'",["'.join('","', $call['params']).'"]';
			}, $calls),
			't' => 'json',
		);

		if ($this->ui || $this->ui = $this->getUserToken())
			$params['ui'] = $this->ui;

		$parts['query'] = http_build_query($params);

		if ($method === 'get') {
			$url = $parts['scheme'].'://'.$parts['host'].$parts['path'].'?'.$parts['query'];
			$data_file = 'cache/difi/'.md5($url).'.json';
		} else {
			$url = $parts['scheme'].'://'.$parts['host'].$parts['path'];
		}
		
		if ($method === 'get' && file_exists($data_file) && time()-filemtime($data_file) < self::$cache_time) {
			if (!self::$silent)
				echo "read: $data_file (".date('Y-m-d', filemtime($data_file)).")\n";
			$data = file_get_contents($data_file);
		} else {
			if ($method === 'get') {
				if (!self::$silent)
					echo "fetch: $data_file\n";

				//$data = file_get_contents($url);
				$data = $this->sendGet($url);
				if ($data) {
					@file_put_contents($data_file, $data);
					@chmod($data_file, 0666);
				}
			} elseif ($method === 'post') {
				$data = $this->sendPost($url, $params);
			}
		}

		/*if (!$data) {
			echo 'error: no DiFi response;';
			die();
		}*/

		$json = json_decode($data, true);

		if ($json['DiFi']['status'] === 'FAIL') {
			//return array();
			if (--$retry > 0) {
				echo "retry: $retry\n";
				@unlink($data_file);
				return $this->sendCalls($calls, $method, $retry);
			}

			echo "error: ".$json['DiFi']['response']['error']."; details: ".$json['DiFi']['response']['details'].";\n";
			die();
		}

		return $json['DiFi']['response']['calls'];
	}

	public function sendGet($url)
	{
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public function sendPost($url, $data)
	{
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public function getUserToken()
	{
		$cookie = file_get_contents($this->cookie_file);
		if (preg_match('/userinfo\s+([_%\w]+)/', $cookie, $match)) {
			return urldecode($match[1]);
		}
		return false;
	}

	public function getFavGalleries($user_id)
	{
		return $this->sendCall("Gallections", "get_collections_for_lub", array(
			$user_id,
		));
	}

	public function addFavGalleries($user_id, $gallery_id, $image_id, $position = 0)
	{
		return $this->sendCall("Aggregations", "add_resource", array(
			$user_id,
			"551235953",
			21,
			$gallery_id,
			1,
			$image_id,
			$position,
		), 'post');
	}

	// @return string "Favourite removed" or "Favourite added"
	public function toggleFavourite($image_id)
	{
		return $this->sendCall("Deviation", "Favourite", array(
			$image_id,
		), 'post');
	}

	public function removeFavGalleries($user_id, $gallery_id, $image_id)
	{
		return $this->sendCall("Gallections", "remove_resource", array(
			$user_id,
			21,
			$gallery_id,
			1,
			$image_id,
		), 'post');
	}

	public function getFavPage($user, $offset = 0)
	{
		return $this->sendCall("Resources", "htmlFromQuery", array(
			"favby:".$user,
			$offset,
			24,
			"thumb150",
			"artist:0,title:0,collections:1,galleries:1",
		));
	}

	public function getDevwatch($id)
	{
		return $this->sendCall("MessageCenter", "get_views", array(
			$id,
			"oq:devwatch:0:48:f:tg=deviations,group=sender",
		), 'post');
	}

	public function parsePageResource($resource)
	{
		$ismatureRegex = '#class="thumb ismature"#';
		$instorageRegex = '#class="instorage"#';
		$linkRegex = '#<a class="thumb[\s\w-_]*" href="(http://([^.]+)[^"]+)#';
		$thumbRegex = '#data-src="(http://([^.]+)([^"]+))#';
		$titleRegex = '#title="([^"]*)#';
		$titlePartsRegex = '#(.+) by (.)([-\w]+), (\w+ \d+, \d+) in (.+)#u';

		list(, $id, $html) = $resource;

		$ismature = preg_match($ismatureRegex, $html) !== 0;
		$instorage = preg_match($instorageRegex, $html) !== 0;

		if ($instorage)
			return null;

		if (preg_match($instorageRegex, $html) === 0) {
			if (preg_match($linkRegex, $html, $linkMatch)
				&& preg_match($thumbRegex, $html, $thumbMatch))
			{
				preg_match($titleRegex, $html, $titleMatch);
				preg_match($titlePartsRegex, html_entity_decode($titleMatch[1]), $titlePartsMatch);

				//$imageUrl = str_replace('/150/', '/', $thumbMatch[1]);
				$thumbPath = parse_url($thumbMatch[1], PHP_URL_PATH);
				$imageUrl = 'http://fc0'.rand(0, 9).'.deviantart.net'.preg_replace('#^(/\w+)/150/#', '\1/', $thumbPath);
				$middleUrl = 'http://fc0'.rand(0, 9).'.deviantart.net'.preg_replace('#^(/\w+)/150/#', '\1/300W/', $thumbPath);
				$filename = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_BASENAME);

				return array(
					'id' => $id,
					'ismature' => $ismature,
					'instorage' => $instorage,
					'titlefull' => $titleMatch[1],
					'title' => $titlePartsMatch[1],
					'usersymbol' => $titlePartsMatch[2],
					'nickname' => $titlePartsMatch[3],
					'date' => date('Y-m-d', strtotime($titlePartsMatch[4])),
					'categories' => explode(' > ', $titlePartsMatch[5]),
					'page' => $linkMatch[1],
					'author' => $linkMatch[2],
					'thumb' => $thumbMatch[1],
					'image' => $imageUrl,
					'middle' => $middleUrl,
					'filename' => $filename,
				);
			}
			else
			{
				echo "\nERROR PARSE:";
				echo "\n============\n";
				echo $html;
				echo "\n";
			}
		}
	}

	public function getFavs($user, $offset = 0)
	{
		$favs = array();

		$content = $this->getFavPage($user, 0);

		while(true)
		{
			//sleep(1);
			foreach ($content['resources'] as $resource)
			{
				$favs[] = $this->parsePageResource($resource);
			}

			if (count($content['resources']) < 24)
				break;

			$offset += 24;
			$content = $this->getFavPage($user, $offset);
		}

		return $favs;
	}
}

//class Devianart extends Deviantart {}