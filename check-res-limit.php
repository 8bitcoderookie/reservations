<?php

require_once('setup-db-waltergasse-json.php');

function get_week_limit_for_asset($asset_id) {
	global $dbHomepage;

	$sql = <<<SQL
		SELECT `wochen` FROM `reservierungsdings` WHERE `id` = $asset_id
SQL;

	$result = $dbHomepage->query($sql);
	if (!$result) {
		exit(json_encode(array('error' => 'Fehler bei der Datenbankabfrage [' . $dbHomepage->error . ']'), JSON_UNESCAPED_UNICODE));
	}
	else {
		if($result->num_rows > 0){
			$row = $result->fetch_assoc();
			return $row['wochen'];
		}
		else {
			exit(json_encode(array('error' => 'Kein Reservierungsding mit der Nummer '.$asset_id.' gefunden.'), JSON_UNESCAPED_UNICODE));
		}
	}
	return 0;
}

?>