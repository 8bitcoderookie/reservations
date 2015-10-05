<?php

/* *****************************************************
     author: Michael Rundel
       date: 26.07.2015
description: moodle page for reservation according to a time table

todo:
- lehrer heraussuchen und select füllen
- submit datenbank übernehmen
- aktuelle woche abrufen und als default in die Felder eintragen (super bei Stundenplanänderungen.).
- für schulfreie Tage Reservierungen löschen.


****************************************************** */

// imports
// =======

// Moodle Import
require_once($_SERVER['DOCUMENT_ROOT'].'/moodle/config.php');
require_once('config.php');

// $PAGE->https_required();
$PAGE->set_url($CFG->wwwroot.'/admin/brg4/reserbierung/index-reservierung-nach-stundenplan.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$htmlTitle = 'Revervierungen nach Stundenplan';
$htmlHeading = 'Reservierungen nach Stundenplan';

//-------------------
global $PAGE, $OUTPUT;
$PAGE->set_title($htmlTitle); // optional
// $PAGE->set_heading($htmlHeading); // must be set in order to display page header!
$PAGE->navbar->add($htmlTitle);
echo $OUTPUT->header();
// echo $OUTPUT->heading($htmlHeading);
if (is_res_admin()) {
?>
	<div class="table-responsive" id="table-container">
		<table class="table table-striped table-bordered table-hover" id="tabelle-reservierungen" width="100%">
			<thead>
				<tr>
					<th></th>
					<th>Montag</th>
					<th>Dienstag</th>
					<th>Mittwoch</th>
					<th>Donnerstag</th>
					<th>Freitag</th>
				</tr>
			</thead>
			<tbody>
			<tr data-hour="1">
				<th>1. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="1"></select><input type="text" size="5" class="info" tabindex="2"></td>
				<td data-day="2"><select class="teacher" tabindex="25"></select><input type="text" size="5" class="info" tabindex="26"></td>
				<td data-day="3"><select class="teacher" tabindex="49"></select><input type="text" size="5" class="info" tabindex="50"></td>
				<td data-day="4"><select class="teacher" tabindex="73"></select><input type="text" size="5" class="info" tabindex="74"></td>
				<td data-day="5"><select class="teacher" tabindex="97"></select><input type="text" size="5" class="info" tabindex="98"></td>
			</tr>
			<tr data-hour="2">
				<th>2. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="3"></select><input type="text" size="5" class="info" tabindex="4"></td>
				<td data-day="2"><select class="teacher" tabindex="27"></select><input type="text" size="5" class="info" tabindex="28"></td>
				<td data-day="3"><select class="teacher" tabindex="51"></select><input type="text" size="5" class="info" tabindex="52"></td>
				<td data-day="4"><select class="teacher" tabindex="75"></select><input type="text" size="5" class="info" tabindex="76"></td>
				<td data-day="5"><select class="teacher" tabindex="99"></select><input type="text" size="5" class="info" tabindex="100"></td>
			</tr>
			<tr data-hour="3">
				<th>3. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="5"></select><input type="text" size="5" class="info" tabindex="6"></td>
				<td data-day="2"><select class="teacher" tabindex="29"></select><input type="text" size="5" class="info" tabindex="30"></td>
				<td data-day="3"><select class="teacher" tabindex="53"></select><input type="text" size="5" class="info" tabindex="54"></td>
				<td data-day="4"><select class="teacher" tabindex="77"></select><input type="text" size="5" class="info" tabindex="78"></td>
				<td data-day="5"><select class="teacher" tabindex="101"></select><input type="text" size="5" class="info" tabindex="102"></td>
			</tr>
			<tr data-hour="4">
				<th>4. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="7"></select><input type="text" size="5" class="info" tabindex="8"></td>
				<td data-day="2"><select class="teacher" tabindex="31"></select><input type="text" size="5" class="info" tabindex="32"></td>
				<td data-day="3"><select class="teacher" tabindex="55"></select><input type="text" size="5" class="info" tabindex="56"></td>
				<td data-day="4"><select class="teacher" tabindex="79"></select><input type="text" size="5" class="info" tabindex="80"></td>
				<td data-day="5"><select class="teacher" tabindex="103"></select><input type="text" size="5" class="info" tabindex="104"></td>
			</tr>
			<tr data-hour="5">
				<th>5. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="9"></select><input type="text" size="5" class="info" tabindex="10"></td>
				<td data-day="2"><select class="teacher" tabindex="33"></select><input type="text" size="5" class="info" tabindex="34"></td>
				<td data-day="3"><select class="teacher" tabindex="57"></select><input type="text" size="5" class="info" tabindex="58"></td>
				<td data-day="4"><select class="teacher" tabindex="81"></select><input type="text" size="5" class="info" tabindex="82"></td>
				<td data-day="5"><select class="teacher" tabindex="105"></select><input type="text" size="5" class="info" tabindex="106"></td>
			</tr>
			<tr data-hour="6">
				<th>6. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="11"></select><input type="text" size="5" class="info" tabindex="12"></td>
				<td data-day="2"><select class="teacher" tabindex="35"></select><input type="text" size="5" class="info" tabindex="36"></td>
				<td data-day="3"><select class="teacher" tabindex="59"></select><input type="text" size="5" class="info" tabindex="60"></td>
				<td data-day="4"><select class="teacher" tabindex="83"></select><input type="text" size="5" class="info" tabindex="84"></td>
				<td data-day="5"><select class="teacher" tabindex="107"></select><input type="text" size="5" class="info" tabindex="108"></td>
			</tr>
			<tr data-hour="7">
				<th>7. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="13"></select><input type="text" size="5" class="info" tabindex="14"></td>
				<td data-day="2"><select class="teacher" tabindex="37"></select><input type="text" size="5" class="info" tabindex="38"></td>
				<td data-day="3"><select class="teacher" tabindex="61"></select><input type="text" size="5" class="info" tabindex="62"></td>
				<td data-day="4"><select class="teacher" tabindex="85"></select><input type="text" size="5" class="info" tabindex="86"></td>
				<td data-day="5"><select class="teacher" tabindex="109"></select><input type="text" size="5" class="info" tabindex="110"></td>
			</tr>
			<tr data-hour="8">
				<th>8. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="15"></select><input type="text" size="5" class="info" tabindex="16"></td>
				<td data-day="2"><select class="teacher" tabindex="39"></select><input type="text" size="5" class="info" tabindex="40"></td>
				<td data-day="3"><select class="teacher" tabindex="63"></select><input type="text" size="5" class="info" tabindex="64"></td>
				<td data-day="4"><select class="teacher" tabindex="87"></select><input type="text" size="5" class="info" tabindex="88"></td>
				<td data-day="5"><select class="teacher" tabindex="111"></select><input type="text" size="5" class="info" tabindex="112"></td>
			</tr>
			<tr data-hour="9">
				<th>9. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="17"></select><input type="text" size="5" class="info" tabindex="18"></td>
				<td data-day="2"><select class="teacher" tabindex="41"></select><input type="text" size="5" class="info" tabindex="42"></td>
				<td data-day="3"><select class="teacher" tabindex="65"></select><input type="text" size="5" class="info" tabindex="66"></td>
				<td data-day="4"><select class="teacher" tabindex="89"></select><input type="text" size="5" class="info" tabindex="90"></td>
				<td data-day="5"><select class="teacher" tabindex="113"></select><input type="text" size="5" class="info" tabindex="114"></td>
			</tr>
			<tr data-hour="10">
				<th>10. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="19"></select><input type="text" size="5" class="info" tabindex="20"></td>
				<td data-day="2"><select class="teacher" tabindex="43"></select><input type="text" size="5" class="info" tabindex="44"></td>
				<td data-day="3"><select class="teacher" tabindex="67"></select><input type="text" size="5" class="info" tabindex="68"></td>
				<td data-day="4"><select class="teacher" tabindex="91"></select><input type="text" size="5" class="info" tabindex="92"></td>
				<td data-day="5"><select class="teacher" tabindex="115"></select><input type="text" size="5" class="info" tabindex="116"></td>
			</tr>
			<tr data-hour="11">
				<th>11. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="21"></select><input type="text" size="5" class="info" tabindex="22"></td>
				<td data-day="2"><select class="teacher" tabindex="45"></select><input type="text" size="5" class="info" tabindex="46"></td>
				<td data-day="3"><select class="teacher" tabindex="69"></select><input type="text" size="5" class="info" tabindex="70"></td>
				<td data-day="4"><select class="teacher" tabindex="93"></select><input type="text" size="5" class="info" tabindex="94"></td>
				<td data-day="5"><select class="teacher" tabindex="117"></select><input type="text" size="5" class="info" tabindex="118"></td>
			</tr>
			<tr data-hour="12">
				<th>12. Std.</th>
				<td data-day="1"><select class="teacher" tabindex="23"></select><input type="text" size="5" class="info" tabindex="24"></td>
				<td data-day="2"><select class="teacher" tabindex="47"></select><input type="text" size="5" class="info" tabindex="48"></td>
				<td data-day="3"><select class="teacher" tabindex="71"></select><input type="text" size="5" class="info" tabindex="72"></td>
				<td data-day="4"><select class="teacher" tabindex="95"></select><input type="text" size="5" class="info" tabindex="96"></td>
				<td data-day="5"><select class="teacher" tabindex="119"></select><input type="text" size="5" class="info" tabindex="120"></td>
			</tr>
			</tbody>
		</table>
	</div>
	<form id="res-form">

		<label for="von">Erster Tag der Reservierung (inkl.):</label> <input type="date" name="von" id="von" placeholder="yyyy-mm-dd" value="2015-09-06"><br>
		<label for="bis">Letzter Tag der Reservierung (inkl.):</label> <input type="date" name="bis" id="bis" placeholder="yyyy-mm-dd" value="2015-12-06"><br>
		<p><input type="checkbox" name="delete-old-reservations" onclick="alert('to be done...')">&nbsp;An diesen Tagen die Stundenplanmäßigen Reservierungen löschen.</p>
		<select name="asset" id="asset">
		</select>
		<input type="button" id="submitbutton" value="Stundenplan in die Datenbank eintragen">
		<input type="button" id="clearform" value="Stundenplan Formular löschen"><br>
		<textarea name="console" id="console" rows="5" cols="200"></textarea>
	</form>
	<p id="res-info"></p>

	<script type="text/javascript">
	<?php require('jquery-2.1.4.min.js'); ?>
	</script>
	<script type="text/javascript">
	<?php require('index-reservierung-nach-stundenplan.js'); ?>
	</script>
	<style type="text/css">
	<?php require('reservierung.css'); ?>
	</style>
<?php
}
else {
	echo('<p>Sorry, this page can only be accessed by a site admin.</p>');
}
echo $OUTPUT->footer();
?>
