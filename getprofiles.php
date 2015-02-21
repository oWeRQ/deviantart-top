<?php

require_once 'classes/autoload.php';

Deviantart::$cache_time = 3600*24*14;
$deviantart = new Deviantart;
$deviantartTop = new DeviantartTop;

$authors = $deviantartTop->db->images->distinct('local.author');
rsort($authors);

$start = time();
$total = count($authors);
foreach ($authors as $i => $author) {
	if ($oldProfileRecord = $deviantartTop->db->profiles->findOne(['id' => $author])) {
		// if ($author == 'janedj') {
		// 	$profile = $deviantart->userinfo($author);
		// 	var_dump($oldProfileRecord);
		// 	var_dump($profile);
		// 	die();
		// }

		if (isset($oldProfileRecord['server_deleted']) && $oldProfileRecord['server_deleted'] !== false)
			continue;

		if ($oldProfileRecord['local_updated'] > time() - Deviantart::$cache_time)
			continue;
	}

	$line = "get profile: ".($i+1)."/$total $author";
	echo "\r".str_pad($line, 80);

	if ($profile = $deviantart->userinfo($author)) {
		if (!isset($profile['deactivated'])) {
			$profile['username'] = $author;
			$deviantartTop->saveData('profiles', $author, $profile);
		} else {
			if ($oldProfileRecord) {
				$deviantartTop->db->profiles->update(['id' => $author], ['$set' => [
					'local_deleted' => false,
					'server_deleted' => time(),
				]]);
			} else {
				$deviantartTop->db->profiles->insert([
					'id' => $author,
					'local' => false,
					'local_deleted' => false,
					'server' => false,
					'server_deleted' => time(),
				]);
			}
		}
	}
}

echo "\n";
