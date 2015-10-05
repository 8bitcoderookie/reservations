<?php

// http://localhost/moodle/admin/brg4/reservierung/make-reservation-json.php?asset=1&id=3&info=6ab&hour=11&date=2015-07-28&token=3892893849

header("Content-type: application/json; charset=utf-8");

require_once($_SERVER['DOCUMENT_ROOT'].'/moodle/config.php');
require_once('config.php');
require_once('setup-db-waltergasse-json.php');

if (!isloggedin()) {
	exit(json_encode(array('error' => 'Kein Benutzer angemeldet!'), JSON_UNESCAPED_UNICODE));
}

/* -----------------------
   check request parameter
   ----------------------- */

$user_id = $USER->id;

$res_id = 0;
if (isset($_REQUEST['id'])) {
	$res_id = intval($_REQUEST['id']);
}
else {
	exit(json_encode(array('error' => 'Übergabeparameter „id“ fehlt!'), JSON_UNESCAPED_UNICODE));
}


/* ---------------------------------------------------
   check if user is allowed to delete this reservation
   --------------------------------------------------- */

// site admins are allowed to delete any reservations!
// normal user can only delete their own rservations; so test before if red_id matches user_id!

if (!is_res_admin()) {
	$sql = <<<SQL
		SELECT `id` 
		FROM `reservierung` 
		WHERE `id` = $res_id 
		AND `user_id` = $user_id;
SQL;
	$result = $dbHomepage->query($sql);
	if($result->num_rows < 1){
		exit(json_encode(array('error' => 'Benutzer ('.$user_id.') hat keine Rechte die Reservierung ('.$res_id.') zu löschen!'), JSON_UNESCAPED_UNICODE));
	}
}


/* -------------------------
   try to delete reservation
   ------------------------- */

$sql = <<<SQL
	DELETE FROM `reservierung` 
	WHERE `reservierung`.`id` = $res_id;
SQL;

$result = $dbHomepage->query($sql);
if($dbHomepage->affected_rows > 0){
	exit(json_encode(array('success' => 'Reservierung gelöscht!'), JSON_UNESCAPED_UNICODE));
}
else {
	exit(json_encode(array('error' => 'Reservierung konnte nicht in der Datenbank gelöscht werden [' . $dbHomepage->error . '].'), JSON_UNESCAPED_UNICODE));
}

?>