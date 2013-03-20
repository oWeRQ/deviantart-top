<?php

class Deviantart
{
	protected $oEmbedUrl = 'http://backend.deviantart.com/oembed?url=';
	
	public $user_id = null;
	public $ui = null;

	public static $cache_time = 259200; //3600*24*3
	//public static $cache_time = 604800; //3600*24*7
	public static $silent = false;

	function userinfo($username)
	{
		libxml_use_internal_errors(true);

		$file = 'cache/profiles/'.$username.'.html';

		if (file_exists($file) && time()-filemtime($file) < self::$cache_time) {
			$html = file_get_contents($file);
		} else {
			$url = 'http://'.$username.'.deviantart.com';
			$html = @file_get_contents($url);
			file_put_contents($file, $html);
		}

		if (empty($html))
			return null;

		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$xpath = new DOMXPath($doc);

		$info = $xpath->query("//div[@class='pbox pppbox']")->item(0);

		preg_match_all('/([\d,]+)[ ]+(\w+)/', $info->textContent, $matches);

		$counts = array();
		foreach ($matches[2] as $i => $name) {
			$counts[strtolower($name)] = intval(str_replace(',', '', $matches[1][$i]));
		}

		return $counts;
	}

	function sendCall($callObj, $callMethod, $callParams, $method = 'get', $try = 3)
	{
		$callStr = '"'.$callObj.'","'.$callMethod.'",["'.join('","', $callParams).'"]';

		$parts = array(
			'scheme' => 'http',
			'host' => 'my.deviantart.com',
			'path' => '/global/difi/',
			'query' => '',
		);

		$params = array(
			'c' => array($callStr),
			't' => 'json',
		);

		if ($this->ui || $this->ui = $this->getUserToken())
			$params['ui'] = $this->ui;

		$parts['query'] = http_build_query($params);

		if ($method === 'get') {
			$url = $parts['scheme'].'://'.$parts['host'].$parts['path'].'?'.$parts['query'];
			$data_file = 'cache/'.md5($url).'.json';
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

				$data = file_get_contents($url);
				file_put_contents($data_file, $data);
			} elseif ($method === 'post') {
				$data = $this->sendPost($url, $params);
			}
		}

		$json = json_decode($data, true);

		if ($json['DiFi']['status'] === 'FAIL') {
			if (--$try > 0) {
				echo "try: $try\n";
				unlink($data_file);
				return $this->sendCall($callObj, $callMethod, $callParams, $method, $try);
			}

			echo "error: ".$json['DiFi']['response']['error']."\n";
			die();
		}

		return $json['DiFi']['response']['calls'][0]['response']['content'];
	}

	function sendPost($url, $data)
	{
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_COOKIEJAR, '.cookie');
		curl_setopt($ch, CURLOPT_COOKIEFILE, '.cookie');
		$result = curl_exec($ch);
		curl_close($ch);  
		return $result;
	}

	function getUserToken()
	{
		$cookie = file_get_contents('.cookie');
		if (preg_match('/userinfo\s+([_%\w]+)/', $cookie, $match)) {
			return urldecode($match[1]);
		}
		return false;
	}

	function getFavGalleries($user_id, $type = 21)
	{
		return $this->sendCall("Aggregations", "get_galleries_initial", array(
			$user_id,
			$type,
			1,
		));
	}

	function addFavGalleries($user_id, $gallery_id, $image_id, $position = 0)
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

	function toggleFavourite($image_id)
	{
		return $this->sendCall("Deviation", "Favourite", array(
			$image_id,
		), 'post');
	}

	function removeFavGalleries($user_id, $gallery_id, $image_id)
	{
		return $this->sendCall("Gallections", "remove_resource", array(
			$user_id,
			21,
			$gallery_id,
			1,
			$image_id,
		), 'post');
	}

	function getFavPage($user, $offset = 0)
	{
		return $this->sendCall("Resources", "htmlFromQuery", array(
			"favby:".$user,
			$offset,
			24,
			"thumb150",
			"artist:0,title:0,collections:1,galleries:1",
		));
	}

	function getDevwatch($id)
	{
		return $this->sendCall("MessageCenter", "get_views", array(
			$id,
			"oq:devwatch:0:48:f:tg=deviations,group=sender",
		), 'post');
	}

	function getFavs($user, $offset = 0)
	{
		$instorageRegex = '#class="instorage"#';
		$linkRegex = '#<a class="thumb[\s\w]*" href="(http://([^.]+)[^"]+)#';
		$thumbRegex = '#data-src="(http://([^.]+)([^"]+))#';
		$titleRegex = '#title="([^"]*)#';

		$favs = array();

		$content = $this->getFavPage($user, 0);

		while(true)
		{
			//sleep(1);
			foreach ($content['resources'] as $resource)
			{
				list(, $id, $html) = $resource;
				//print_r($resource);die();

				$instorage = preg_match($instorageRegex, $html) !== 0;

				if (preg_match($instorageRegex, $html) === 0) {
					if (preg_match($linkRegex, $html, $linkMatch)
						&& preg_match($thumbRegex, $html, $thumbMatch))
					{
						preg_match($titleRegex, $html, $titleMatch);

						//$imageUrl = str_replace('/150/', '/', $thumbMatch[1]);
						$thumbPath = parse_url($thumbMatch[1], PHP_URL_PATH);
						$imageUrl = 'http://fc0'.rand(0, 9).'.deviantart.net'.preg_replace('#^(/\w+)/150/#', '\1/', $thumbPath);
						$middleUrl = 'http://fc0'.rand(0, 9).'.deviantart.net'.preg_replace('#^(/\w+)/150/#', '\1/300W/', $thumbPath);
						$filename = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_BASENAME);

						$favs[] = array(
							'id' => $id,
							'title' => $titleMatch[1],
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

			if (count($content['resources']) < 24)
				break;

			$offset += 24;
			$content = $this->getFavPage($user, $offset);
		}

		return $favs;
	}
}

class Devianart extends Deviantart {}