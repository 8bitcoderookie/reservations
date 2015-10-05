<?php
exit("Wartungsmodus...");

/* *****************************************************
     author: Michael Rundel
       date: 16.07.2015
description: moodle page for reservation overview

****************************************************** */

// imports
// =======

// Moodle Import
require_once($_SERVER['DOCUMENT_ROOT'].'/moodle/config.php');
// reservation config file
require_once('config.php');

// $PAGE->https_required();
$PAGE->set_url($CFG->wwwroot.'/admin/brg4/reservierung/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$htmlTitle = 'Reservierungen';
$htmlHeading = $htmlTitle;

$device = isset($_REQUEST['device']) ? $_REQUEST['device'] : '';
if ($device == 'kiosk') {
	$_SESSION['mr_kiosk'] = 'yes';
}

//-------------------
global $PAGE, $OUTPUT;
$PAGE->set_title($htmlTitle); // optional
// $PAGE->set_heading($htmlHeading); // must be set in order to display page header!
$PAGE->navbar->add($htmlTitle);
echo $OUTPUT->header();
// echo $OUTPUT->heading($htmlHeading);
if (isset($_SESSION['mr_kiosk'])) {
	echo '<script type="text/javascript">var mr_runs_in_kiosk_mode = true;</script>';
}
else {
	echo '<script type="text/javascript">var mr_runs_in_kiosk_mode = false;</script>';
}
?>
	<form id="res-form">
		<h1>
			<select name="asset" id="asset"></select>
			<span> &nbsp;&nbsp;&nbsp;&nbsp;Reservierungen</span>
		</h1>
	</form>
	<div class="table-responsive" id="table-container">
		<table class="table table-striped table-bordered table-hover" id="tabelle-reservierungen" width="100%">
		</table>
		<p id="res-info"></p>
	</div>
	<script type="text/javascript">
	<?php require('jquery-2.1.4.min.js'); ?>
	</script>
	<script type="text/javascript">
	<?php require('reservierung.js'); ?>
	</script>
	<style type="text/css">
	<?php require('reservierung.css'); ?>
	</style>
<?php
if (isloggedin()) {
?>
	<script type="text/javascript">
		mr_reservierung.logged_in_user_id = <?php echo $USER->id; ?>;
		mr_reservierung.is_logged_in = true;
	</script>
<?php
}
else {
?>
	<script type="text/javascript">
		mr_reservierung.logged_in_user_id = 0;
		mr_reservierung.is_logged_in = false;
	</script>
<?php
}
?>
	<script type="text/javascript">
		mr_reservierung.logged_in_user_id = <?php echo $USER->id; ?>;
		mr_reservierung.res_admin_moodle_user_ids = ["<?php echo implode('", "', $res_admin_moodle_user_ids); ?>"];
		mr_reservierung.is_res_admin = function() {
			for (var i = 0; i < mr_reservierung.res_admin_moodle_user_ids.length; i++) {
				if (String(mr_reservierung.res_admin_moodle_user_ids[i]) == String(mr_reservierung.logged_in_user_id)) {
					return true;
				}
			}
			return false;
		}
	</script>
<?php
echo $OUTPUT->footer();
?>
