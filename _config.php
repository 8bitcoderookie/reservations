<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/moodle/config.php');

// insert here all moodle user ids found in `moodle`.`mdl_user`, who are reservation admins
$res_admin_moodle_user_ids = array(2); // localhost: 2/admin

function is_res_admin() {
	global $USER, $res_admin_moodle_user_ids;
	return in_array($USER->id, $res_admin_moodle_user_ids);
}

?>