<?php

// http://localhost/moodle/admin/brg4/reservierung/get-reservations-json.php?asset=2&begin=2015-07-01&end=2015-09-01

header("Content-type: application/json; charset=utf-8");

require_once('setup-db-moodle-json.php');
require_once('setup-db-waltergasse-json.php');

/* -----------------------
   check request parameter
   ----------------------- */

$asset_id = 2;
if (isset($_REQUEST['asset'])) {
	$asset_id = intval($_REQUEST['asset']);
}
else {
	exit(json_encode(array('error' => 'Übergabeparameter `asset` fehlt!'), JSON_UNESCAPED_UNICODE));
}

$date_begin = '2015-07-01';
if (isset($_REQUEST['begin'])) {
	$date_begin = strval($_REQUEST['begin']);
}
else {
	exit(json_encode(array('error' => 'Übergabeparameter `begin` fehlt!'), JSON_UNESCAPED_UNICODE));
}

$date_end = '2015-09-01';
if (isset($_REQUEST['end'])) {
	$date_end = strval($_REQUEST['end']);
}
else {
	exit(json_encode(array('error' => 'Übergabeparameter `end` fehlt!'), JSON_UNESCAPED_UNICODE));
}


/* ===============================
   query database for reservations
   =============================== */

/* -------------------------------
   get data from brg4 homepage DB
   ------------------------------- */

$sql = <<<SQL
	SELECT CONCAT_WS('-',`datum`,LPAD(`schulstunde_id`,2,'0')) AS `elmid`, `user_id` , `info`,  `id`
	FROM `reservierung` 
	WHERE `datum` between '$date_begin' AND '$date_end'
	AND `rservierungsdings_id` = $asset_id;
SQL;

if(!$result = $dbHomepage->query($sql)){
	exit(json_encode(array('error' => 'There was an error running the query [' . $dbHomepage->error . ']'), JSON_UNESCAPED_UNICODE));
}

$data = array();
$moodle_user_id = array();
while($row = $result->fetch_assoc()){
    $data[]= array(
		'elmid' => $row['elmid'],
		'res_id' => $row['id'],
		'user_id' => $row['user_id'],
		'info' => $row['info']
    );
	$moodle_user_id[] = $row['user_id'];
}

/* ---------------------------------------
   get data from Moodle DB and splice data
   --------------------------------------- */

$json = array();

if (count($data) > 0) {
	$sql = 'SELECT `id`, `firstname`, `lastname` FROM `mdl_user` WHERE `id` IN ('.implode(',', $moodle_user_id).')';

	if(!$result = $dbMoodle->query($sql)){
		exit(json_encode(array('error' => 'There was an error running the query [' . $dbMoodle->error . ']'), JSON_UNESCAPED_UNICODE));
	}

	$firstname = array();
	$lastname = array();
	while($row = $result->fetch_assoc()){
		$firstname[$row['id']] = $row['firstname'];
		$lastname[$row['id']] = $row['lastname'];
	}

	foreach ($data as $value) {
		$json[]= array(
			'elmid' => $value['elmid'],
			'res_id' => $value['res_id'],
			'user_id' => $value['user_id'],
			'user_fn' => isset($firstname[$value['user_id']]) ? $firstname[$value['user_id']] : 'vorname?',
			'user_ln' => isset($lastname[$value['user_id']]) ? $lastname[$value['user_id']] : 'nachname?',
			'info' => $value['info']
		);
	}
}

/* ----------------------------
   query database for usernames
   ---------------------------- */

echo json_encode($json, JSON_UNESCAPED_UNICODE);

?>