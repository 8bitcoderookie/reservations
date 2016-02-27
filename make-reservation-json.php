<?php

// http://localhost/moodle/admin/brg4/reservierung/make-reservation-json.php?asset=1&id=3&info=6ab&hour=11&date=2015-07-28&token=3892893849

header("Content-type: application/json; charset=utf-8");

require_once($_SERVER['DOCUMENT_ROOT'].'/moodle/config.php');
require_once('config.php');
require_once('setup-db-moodle-json.php');
require_once('setup-db-waltergasse-json.php');

$teacher_cohort_id = 2;
$email_notify = true;

if (!isloggedin()) {
	exit(json_encode(array('error' => 'Kein Benutzer angemeldet!'), JSON_UNESCAPED_UNICODE));
}

/* -----------------------
   check request parameter
   ----------------------- */

// only site admins can make reservations for other users than themselfs
$user_id = 0;
if (is_res_admin()) {
	if (isset($_REQUEST['id'])) {
		$user_id = intval($_REQUEST['id']);
	}
}
if ($user_id == 0) { // either of preset or submitted value 0
	$user_id = $USER->id;
}

$info = '';
if (isset($_REQUEST['info'])) {
	$info = strval($_REQUEST['info']);
}
else {
	exit(json_encode(array('error' => 'Übergabeparameter „info“ fehlt!'), JSON_UNESCAPED_UNICODE));
}

$class_hour = 0;
if (isset($_REQUEST['hour'])) {
	$class_hour = intval($_REQUEST['hour']);
}
else {
	exit(json_encode(array('error' => 'Übergabeparameter „hour“ fehlt!'), JSON_UNESCAPED_UNICODE));
}

$res_date = '';
if (isset($_REQUEST['date'])) {
	$res_date = strval($_REQUEST['date']);
}
else {
	exit(json_encode(array('error' => 'Übergabeparameter „date“ fehlt!'), JSON_UNESCAPED_UNICODE));
}

$asset_id = 0;
if (isset($_REQUEST['asset'])) {
	$asset_id = intval($_REQUEST['asset']);
}
else {
	exit(json_encode(array('error' => 'Übergabeparameter „asset“ fehlt!'), JSON_UNESCAPED_UNICODE));
}


$fix = 0;
if (is_res_admin()) {
	if (isset($_REQUEST['fix'])) {
		if (intval($_REQUEST['fix']) == 1) {
			$fix = 1;
		}
	}
}


/* ------------------------------------
   check submitter is a teacher
   ------------------------------------ */

if (!is_res_admin()) {
	$sql = <<<SQL
		SELECT `timeadded`
		FROM `mdl_cohort_members`
		WHERE `cohortid` = {$teacher_cohort_id}
		AND `userid` = {$user_id};
SQL;
	if(!$result = $dbMoodle->query($sql)){
		exit(json_encode(array('error' => 'There was an error running the query [' . $dbMoodle->error . ']'), JSON_UNESCAPED_UNICODE));
	}
	if($result->num_rows < 1){
		exit(json_encode(array('error' => 'You are note listed in the teacher group. Only teachers can make reservations.'), JSON_UNESCAPED_UNICODE));
	}
}



/* ------------------------------------
   check if reservation allready exists
   ------------------------------------ */

$sql = <<<SQL
	SELECT r.`id`, r.`rservierungsdings_id`, r.`datum`, r.`schulstunde_id`, r.`user_id`, r.`info`, r.`stundenplan`, rd.`beschreibung`, s.`vonbis`
	FROM `reservierung` AS r, `reservierungsdings` AS rd, `schulstunde` AS s
	WHERE `rservierungsdings_id` = $asset_id
	AND `datum` LIKE '$res_date'
	AND `schulstunde_id` = $class_hour
	AND rd.`id` = r.`rservierungsdings_id`
	AND s.`id` = r.`schulstunde_id`
SQL;
$result = $dbHomepage->query($sql);
if($result->num_rows > 0){
	$row = $result->fetch_assoc();
	$reservation_id = $row['id'];
	$old_user_id = $row['user_id'];
	if ($user_id == $old_user_id) { // same user; just update
		update_reservation($reservation_id, $user_id, $info);
		exit(json_encode(array('success' => 'Änderung der Reservierung erfolgreich (reservation_id = '.$reservation_id.')!'), JSON_UNESCAPED_UNICODE));
	}
	elseif (is_res_admin()) { // reservation exists, siteadmin overrides!
		update_reservation($reservation_id, $user_id, $info);
		// inform OLD holder of reservation abaout cancellation...
		if ($email_notify == true) {
			$subject = 'Reservierung für '.$row['beschreibung'].' am '.$row['datum'].' aufgehoben!';
			$message = '<p>Bedauerlicherweise wurde deine Reservierung  für '.$row['beschreibung'].' am '.$row['datum'].' um '.$row['vonbis'].' für '.$row['info'].' aufgehoben, da eine andere Veranstaltung Vorrang hat.</p>';
			$former_user_name = notify_former_holder_of_reservation($old_user_id, $subject, $message);
		}
		if ($user_id != $USER->id) { // admin reservation for another user
			// inform_user_about_reservation($user_id,$asset_id,$class_hour,$res_date,$info);
			exit(json_encode(array('success' => 'Rerservierung mit Fremdreservierung überschrieben ('.$former_user_name.': '.$row['beschreibung'].' am '.$row['datum'].' um '.$row['vonbis'].' für '.$row['info'].'; reservation_id = '.$reservation_id.'); beide Benutzer wurden per E-Mail verständigt.!'), JSON_UNESCAPED_UNICODE));
		}
		else {
			exit(json_encode(array('success' => 'Rerservierung überschrieben ('.$former_user_name.': '.$row['beschreibung'].' am '.$row['datum'].' um '.$row['vonbis'].' für '.$row['info'].'; reservation_id = '.$reservation_id.'), Benutzer wurde per E-Mail verständigt.!'), JSON_UNESCAPED_UNICODE));
		}
	}
	else {
		// another teacher is not allowed to override a reservation
		exit(json_encode(array('error' => 'Termin ist bereits reserviert!'), JSON_UNESCAPED_UNICODE));
	}
}
else {
	// reservation does not exist, insert in DB
	$insert_id = insert_reservation($asset_id, $res_date, $class_hour, $user_id, $info, $fix);
	if ($user_id != $USER->id) { // different users
		// inform_user_about_reservation($user_id,$asset_id,$class_hour,$res_date,$info);
		exit(json_encode(array('success' => 'Fremdreservierung erfolgreich (reservation_id = '.$insert_id.')! Benutzer wurde per E-Mail verständigt.'), JSON_UNESCAPED_UNICODE));
	}
	else {
		exit(json_encode(array('success' => 'Reservierung erfolgreich (reservation_id = '.$insert_id.')! '), JSON_UNESCAPED_UNICODE));
	}
}


function inform_user_about_reservation($user_id,$asset_id,$class_hour,$res_date,$info) {
	global $email_notify;

	if ($email_notify == true) {
		$asset_description = get_description_for_asset_id($asset_id);
		$schoolhour_description = get_timeslice_for_schoolhour_id($class_hour);
		// inform NEW holder of reservation...
		$subject = $asset_description.' am '.$res_date.' für dich reserviert';
		$message = '<p>'.$asset_description.' wurde für dich am '.$res_date.' für den Zeitraum '.$schoolhour_description.' bzgl. '.$info.' reserviert.</p>';
		$former_user_name = notify_former_holder_of_reservation($user_id, $subject, $message);
	}
}

function get_description_for_asset_id($id) {
	global $dbHomepage;
	$description = '';

	$sql = <<<SQL
		SELECT `beschreibung` FROM `reservierungsdings` WHERE `id`='$id'
SQL;
	$result = $dbHomepage->query($sql);
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		$description = $row['beschreibung'];
	}
	return $description;
}

function get_timeslice_for_schoolhour_id($id) {
	global $dbHomepage;
	$timeslice = '';

	$sql = <<<SQL
		SELECT `vonbis` FROM `schulstunde` WHERE `id`='$id'
SQL;
	$result = $dbHomepage->query($sql);
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		$timeslice = $row['beschreibung'];
	}
	return $timeslice;
}

function insert_reservation($asset_id, $res_date, $class_hour, $user_id, $info, $fix) {
	global $dbHomepage;

	$sql = <<<SQL
		INSERT INTO `waltergasse`.`reservierung` (`id`, `rservierungsdings_id`, `datum`, `schulstunde_id`, `user_id`, `info`, `stundenplan`)
		VALUES (NULL, '$asset_id', '$res_date', '$class_hour', '$user_id', '$info', '$fix');
SQL;
	$result = $dbHomepage->query($sql);
	if($dbHomepage->affected_rows < 1){
		exit(json_encode(array('error' => 'Fehler beim Eintragen in der Datenbank [' . $dbHomepage->error . ']'), JSON_UNESCAPED_UNICODE));
	}
	return $dbHomepage->insert_id;
}



function update_reservation($reservation_id, $user_id, $info) {
	global $dbHomepage;

	$sql = <<<SQL
		UPDATE `waltergasse`.`reservierung`
		SET `info` = '$info',  `user_id` = '$user_id'
		WHERE `reservierung`.`id` = $reservation_id;
SQL;
	$result = $dbHomepage->query($sql);
	if($dbHomepage->affected_rows < 1){
		exit(json_encode(array('error' => 'Fehler beim Ändern des Datensatzes [' . $dbHomepage->error . ']'), JSON_UNESCAPED_UNICODE));
	}
}



function notify_former_holder_of_reservation($to_user_id, $subject, $html_message) {
	global $dbMoodle;
	$sql = <<<SQL
		SELECT *
		FROM `mdl_user`
		WHERE `id` = {$to_user_id};
SQL;
	if(!$result = $dbMoodle->query($sql)){
		exit(json_encode(array('error' => 'There was an error running the query [' . $dbMoodle->error . ']'), JSON_UNESCAPED_UNICODE));
	}
	if($result->num_rows < 1){
		exit(json_encode(array('error' => 'no such user id='.to_user_id.'.'), JSON_UNESCAPED_UNICODE));
	}
	else {
		$row = $result->fetch_assoc();
		$toUser = new stdClass();
		$toUser->email = $row['email'];
		$toUser->firstname = $row['firstname'];
		$toUser->lastname = $row['lastname'];
		$toUser->maildisplay = true;
		$toUser->mailformat = $row['mailformat']; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
		$toUser->id = $to_user_id.
		$toUser->firstnamephonetic = '';
		$toUser->lastnamephonetic = '';
		$toUser->middlename = '';
		$toUser->alternatename = '';
		// email_to_user($toUser, $USER, $subject,  html_to_text($html_message), $html_message, '', false); // set 'false' for noreply@...

		// definition found in /lib/moodlelib.php
		// function email_to_user($user, $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '', $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79)
		// see also:
		// http://articlebin.michaelmilette.com/sending-custom-emails-in-moodle-using-the-email_to_user-function/
	}
	return $row['firstname'].' '.$row['lastname'];
}


?>
