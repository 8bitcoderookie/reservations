<?php

header("Content-type: application/json; charset=utf-8");

require_once($_SERVER['DOCUMENT_ROOT'].'/waltergasse/code/config.php');
require_once('setup-db-waltergasse-json.php');

$sql = <<<SQL
	SELECT `id`, `beschreibung`, `wochen`, `bemerkung`
	FROM `reservierungsdings` 
	WHERE 1
	ORDER BY `id` ASC;
SQL;

if(!$result = $dbHomepage->query($sql)){
	exit(json_encode(array('error' => 'There was an error running the query [' . $dbHomepage->error . ']'), JSON_UNESCAPED_UNICODE));
}

$json = array();
while($row = $result->fetch_assoc()){
    $json[]= array(
		'id' => $row['id'],
		'name' => $row['beschreibung'],
		'limit' => $row['wochen'],
		'bemerkung' => $row['bemerkung']
    );
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);

?>