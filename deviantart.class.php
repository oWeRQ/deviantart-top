<?php

class Devianart
{
	protected $oEmbedUrl = 'http://backend.deviantart.com/oembed?url=';

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
			$html = file_get_contents($url);
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

	function sendCall($callObj, $callMethod, $callParams, $username = 'my', $try = 3)
	{
		$callStr = '"'.$callObj.'","'.$callMethod.'",["'.join('","', $callParams).'"]';

		$parts = array(
			'scheme' => 'http',
			'host' => $username.'.deviantart.com',
			'path' => '/global/difi/',
			'query' => '',
		);

		$params = array(
			'c' => array($callStr),
			't' => 'json',
		);

		$parts['query'] = http_build_query($params);

		$url = $parts['scheme'].'://'.$parts['host'].$parts['path'].'?'.$parts['query'];

		$data_file = 'cache/'.md5($url).'.json';
		if (file_exists($data_file) && time()-filemtime($data_file) < self::$cache_time) {
			if (!self::$silent)
				echo "read: $data_file (".date('Y-m-d', filemtime($data_file)).")\n";
			$data = file_get_contents($data_file);
		} else {
			if (!self::$silent)
				echo "fetch: $data_file\n";
			$data = file_get_contents($url);
			file_put_contents($data_file, $data);
		}

		$json = json_decode($data, true);

		if ($json['DiFi']['status'] === 'FAIL') {
			if (--$try > 0) {
				echo "try: $try\n";
				unlink($data_file);
				return $this->sendCall($callObj, $callMethod, $callParams, $username, $try);
			}

			echo "error: ".$json['DiFi']['response']['error']."\n";
			die();
		}

		return $json['DiFi']['response']['calls'][0]['response']['content'];
	}

	function getFavGalleries($user_id, $type = 21)
	{
		//return $this->sendCall('"Aggregations","get_galleries_initial",["'.$user_id.'","'.$type.'","1"]');
		return $this->sendCall("Aggregations", "get_galleries_initial", array(
			$user_id,
			$type,
			1,
		));
	}

	function getFavPage($user, $offset = 0)
	{
		//return $this->sendCall('"Resources","htmlFromQuery",["favby:'.$user.'","'.$offset.'","24","thumb150","artist:0,title:0,collections:1,galleries:1"]');
		return $this->sendCall("Resources", "htmlFromQuery", array(
			"favby:".$user,
			$offset,
			24,
			"thumb150",
			"artist:0,title:0,collections:1,galleries:1",
		));
	}

	/* Need auth
	function getDevwatch($id)
	{
		return $this->sendCall('"MessageCenter","get_views",["'.$id.'","oq:devwatch:0:48:f:tg=deviations,group=sender"]');
	}
	*/

	function getFavs($user, $offset = 0)
	{
		$instorageRegex = '#class="instorage"#';
		$linkRegex = '#<a class="thumb[\s\w]*" href="(http://([^.]+)[^"]+)#';
		$thumbRegex = '#data-src="(http://([^.]+)([^"]+))#';

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
						//$imageUrl = str_replace('/150/', '/', $thumbMatch[1]);
						$thumbPath = parse_url($thumbMatch[1], PHP_URL_PATH);
						$imageUrl = 'http://fc0'.rand(0, 9).'.deviantart.net'.preg_replace('#^(/\w+)/150/#', '\1/', $thumbPath);
						$middleUrl = 'http://fc0'.rand(0, 9).'.deviantart.net'.preg_replace('#^(/\w+)/150/#', '\1/300W/', $thumbPath);
						$filename = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_BASENAME);

						$favs[] = array(
							'id' => $id,
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
						var_dump($html);
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
