
Date.prototype.yyyymmdd = function() {
   var yyyy = this.getFullYear().toString();
   var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
   var dd  = this.getDate().toString();
   return yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]); // padding
};

var mr_reservierung = {};

mr_reservierung.show_no_of_weeks = 3;

mr_reservierung.res_input_placeholder = 'Klasse/Gegenstand';

mr_reservierung.weekdays = new Array('So','Mo','Di','Mi','Do','Fr','Sa');

mr_reservierung.weekdays_long = new Array('Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag');

mr_reservierung.month_name = new Array("Jänner", "Februar", "März", "April", "Mai", "Juni","Juli", "August", "September", "Oktober", "November", "Dezember");

mr_reservierung.table = null;

mr_reservierung.school_hour = null;

// mr_reservierung.base_login_url = 'http://localhost/moodle/login/index.php';

mr_reservierung.last_update_timestamp = 0;

mr_reservierung.is_logged_in = false;

mr_reservierung.table_showing_asset_id = 0;

mr_reservierung.is_updating_table = false;

mr_reservierung.addDays = function(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

mr_reservierung.pad = function(n, width, z) {
  z = z || '0';
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

mr_reservierung.is_in_time = function(d,weeks){
	var last_date = mr_reservierung.addDays(new Date(),(weeks*7));
	if (d.getTime() < last_date.getTime()) {
		return true;
	}
	return false;
}

mr_reservierung.get_time_restriction_for_selected_asset = function() {
	var asset_id = jQuery("#asset").val();
	return jQuery('#asset option[value='+asset_id+']').attr('data-limit');
}

mr_reservierung.show_asset_note = function() {
	var asset_id = jQuery("#asset").val();
	var note_text = jQuery('#asset option[value='+asset_id+']').attr('data-bemerkung');
	var note_container = jQuery("#assetnote");
	if (note_text.length == 0) {
		if (note_container.length > 0) {
			note_container.remove();
		}
	}
	else {
		if (note_container.length > 0) {
			note_container.html('Hinweis: '+note_text);
		}
		else {
			jQuery("#res-form").append('<p id="assetnote" class="error">Hinweis: '+note_text+'</p>')
		}
	}
}

mr_reservierung.update = function(){
	var i;
	var j;
	var thead = '<th></th>';
	var tbody = '';
	var d = null;
	var cell = new Array();
	var lastDay = -1;
	var th_class = '';
	var td_class = '';
	var cell_contents = '';
	var th_contents = '';
	var date_today = new Date();
	var date_tomorrow = mr_reservierung.addDays(new Date(),1);
	var starting_date = date_today;
	var begin_date;
	var end_date;
	var asset_id = jQuery("#asset").val();

	if (mr_reservierung.is_updating_table == true) {
		return;
	}
	else {
		mr_reservierung.is_updating_table = true;
	}
	if (jQuery("#von").length > 0) {
		if(jQuery("#von").val().match(/^[0-9]{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/)) {
			starting_date = new Date(jQuery("#von").val());
			// date_today = new Date(starting_date);
			// date_tomorrow = mr_reservierung.addDays(new Date(starting_date),1);
		}
	}
	mr_reservierung.last_update_timestamp = new Date().getTime();
	jQuery('#res-info').text('Reservierungsliste wird aktualisiert...');
	mr_reservierung.disable_form_elements();
	var cell_contents_free_text = '--frei--';
	var cell_contents_free_button = '<input type="button" style="margin:-10px;" value="--frei--">';
	var time_limit_in_weeks = mr_reservierung.get_time_restriction_for_selected_asset();
	console.log('time_limit_in_weeks = '+time_limit_in_weeks);
	for (i = 0; i<mr_reservierung.show_no_of_weeks*7; i++) {
		d = mr_reservierung.addDays(new Date(starting_date),i);
		if ( (d.getDay() != 0) && (d.getDay() != 6) ) { // omit sundays (0) and saturdays (6)
			th_class = (lastDay > d.getDay() ? 'week' : '');
			lastDay = d.getDay();
			if (d.toLocaleDateString() == date_today.toLocaleDateString()) {
				th_contents = 'heute';
			}
			else if (d.toLocaleDateString() == date_tomorrow.toLocaleDateString()) {
				th_contents = 'morgen';
			}
			else {
				th_contents = mr_reservierung.weekdays[d.getDay()]+', '+d.getDate()+'. '+(d.getMonth()+1)+'.';
			}
			thead += '<th class="'+th_class+'">'+th_contents+'</th>';
			// thead += '<th>'+d.toDateString()+'</th>';
			td_class = (th_class.length > 0 ? ' '+th_class : '');
			for (key in mr_reservierung.school_hour) {
				j = parseInt(key);
				if (cell[j] == null) {
					cell[j] = new Array('<th class="headcol">'+String(key)+' Std. ('+mr_reservierung.school_hour[key]+')</th>');
				}
				cell_contents = cell_contents_free_text;
				if (mr_reservierung.is_logged_in)	{
					if (mr_reservierung.is_res_admin())	{
						cell_contents = cell_contents_free_button;
					}
					else {
						if (time_limit_in_weeks > 0) {
							if (mr_reservierung.is_in_time(d,time_limit_in_weeks)) {
								cell_contents = cell_contents_free_button;
							}
						}
						else {
							cell_contents = cell_contents_free_button;
						}
					}
				}
				cell[j].push(
					'<td '
					+'title="'+mr_reservierung.weekdays_long[d.getDay()]+', '+d.getDate()+'. '+mr_reservierung.month_name[d.getMonth()]+'; '+String(j)+'. Stunde ('+mr_reservierung.school_hour[key]+')"'
					+'class="free'+td_class+'" id="'+d.getFullYear()+'-'
					+mr_reservierung.pad(String(d.getMonth()+1),2)+'-'
					+mr_reservierung.pad(d.getDate(),2)+'-'
					+mr_reservierung.pad(String(j),2)+'">'+cell_contents+'</td>'
				);
			}
		}
	}
	is_tab_room = false;
	if (jQuery("#asset").val() == 7)	{ // TAB Raum
		is_tab_room = true;
	}
	for (key in mr_reservierung.school_hour) {
		j = parseInt(key);
		if ( (is_tab_room == true) && (j > 4) ) {
			// do not show these hours
		}
		else {
			tbody += '<tr id="std'+j+'">';
			for (i = 0; i<cell[j].length; i++)	{
				tbody += cell[j][i];
			}
			tbody += '</tr>';
		}
	}
	var temp_table = jQuery('<thead><tr>'+thead+'</tr></thead><tbody>'+tbody+'</tbody><tfoot><tr>'+thead+'</tr></tfoot>');
	begin_date = starting_date.yyyymmdd();
	end_date = mr_reservierung.addDays(starting_date,mr_reservierung.show_no_of_weeks*7).yyyymmdd();
	mr_reservierung.load_reservations(temp_table, begin_date, end_date);
}

mr_reservierung.load_reservations = function(temp_table, begin_date, end_date){
	var asset_id = jQuery("#asset").val();
	jQuery.getJSON( 'get-reservations-json.php', {
		asset: asset_id,
		begin: begin_date,
		end: end_date
	}, 
	function( data ) {
		if (data['error'] != null) {
			alert(data['error']);
		}
		else {
			jQuery.each( data, function( key, obj ) {
				// cell_contents = String(obj['user_ln']+obj['user_fn']).substring(0,3).toUpperCase(); // Username
				cell_contents = String(obj['info'].toLowerCase()); // info
				cell_class = "blocked";
				
				// mr_reservierung.is_res_admin()
				// mr_reservierung.is_time_restricted_asset(id) 
				if (mr_reservierung.is_logged_in == true) {
					// console.log(obj['user_id']+", "+obj['user_ln']+", "+obj['info']+", "+M.mdl_user_id);
					if ( (mr_reservierung.is_res_admin()) || (obj['user_id'] == M.mdl_user_id) ) {
						cell_contents = '<input type="button" class="form-submit" data="'+obj['user_id']+'" style="margin:-10px;" value="'+cell_contents+'">';
						cell_class = 'editable';
					}
					else {
						// cell_contents = '<input type="button" disabled="disabled" style="margin:-10px;" value="'+cell_contents+'">';
					}
				}
				oldTitle = jQuery('#'+obj['elmid'], temp_table).attr('title');
				jQuery('#'+obj['elmid'], temp_table)
					.removeClass('free')
					.addClass(cell_class)
					.attr('title',oldTitle+'\n'+obj['user_ln']+' '+obj['user_fn']+' ('+obj['info']+')')
					.attr('data-res-id',obj['res_id'])
					.html(cell_contents);
			});
			mr_reservierung.table
				.empty()
				.append(temp_table)
			var now = new Date();
			var date_string = now.getDate()+'. '+mr_reservierung.month_name[now.getMonth()]+' '+now.getFullYear();
			var time_string = now.toTimeString();
			time_string = time_string.substr(0,time_string.lastIndexOf(':'));
			jQuery('#res-info').text('Reservierungsliste Stand: '+time_string+' ('+date_string+')');
			mr_reservierung.table_showing_asset_id = asset_id;
			mr_reservierung.is_updating_table = false;
			mr_reservierung.enable_form_elements();
		}
	});
}

mr_reservierung.load_asset_options = function(){
	jQuery.getJSON( 'get-assets-json.php', function( data ) {
		if (data['error'] != null) {
			alert(data['error']);
		}
		else {
			var options_text = '';
			jQuery.each( data, function( key, obj ) {
				options_text += '<option data-bemerkung="'+obj['bemerkung']+'" data-limit="'+obj['limit']+'" value="'+obj['id']+'">'+obj['name']+'</option>';
			});
			jQuery('#asset').html(options_text);
			var fragment = window.location.hash.substr(1); 
			if (fragment.length > 0) {
				var request_asset_id = parseInt(fragment);
				if (request_asset_id > 0) {
					jQuery("#asset").val(request_asset_id);
				}
			}
			jQuery('#asset').bind('change', function() {
				if (mr_reservierung.table_showing_asset_id != jQuery("#asset").val()) {
					window.location.hash = '#'+jQuery("#asset").val();
					// jQuery("a[href*='/moodle/login/']").attr('href',mr_reservierung.base_login_url+'?goto='+location.href);
					mr_reservierung.update();
					mr_reservierung.show_asset_note();
				}
			});
			mr_reservierung.show_asset_note();
			mr_reservierung.load_school_hours();
		}
	});
}

mr_reservierung.load_school_hours = function(){
	jQuery.getJSON( 'get-school-hours.php', function( data ) {
		if (data['error'] != null) {
			alert(data['error']);
		}
		else {
			mr_reservierung.school_hour = new Array();
			jQuery.each( data, function( key, obj ) {
				mr_reservierung.school_hour[obj['id']] = obj['vonbis'];
			});
			// console.log(mr_reservierung.school_hour);
			mr_reservierung.update();
		}
	});
}

mr_reservierung.setup_button_click_handlers = function(){
	mr_reservierung.setup_button_click_handlers_do();
	mr_reservierung.setup_button_click_handlers_edit();
}

mr_reservierung.setup_button_click_handlers_do = function(){
	mr_reservierung.table.on( 'click', 'td.free input', function(event) {
		console.log('click td.free');
		var details = '';
		var pre = '';
		var user = 'von '+M.mdl_user_firstname+' '+M.mdl_user_lastname;
		if (mr_reservierung.is_res_admin()) {
			if (jQuery("#id").length > 0)  {
				if (jQuery("#id").val() > 0)  {
					user = user+' für '+jQuery("#id option:selected").text()
					pre = 'IN VERTRETUNG RESERVIEREN\n';
				}
			}
		}
		do {
			details = prompt(
				pre+jQuery('#asset option:selected').text()+' reservieren'
				+'\nam '+jQuery(this).parent().attr('title')
				+'\n'+user
				+'\nfür... (Bitte unten eintragen!)'
				, mr_reservierung.res_input_placeholder
			);
		} while (details == mr_reservierung.res_input_placeholder);
		if (details.length > 0)	{
			details = details.replace('/', ' '); 
			details = details.replace(',', ' '); 
			details = details.replace(';', ' '); 
			details = details.replace(':', ' '); 
			mr_reservierung.make_reservation(jQuery(event.target).parent().attr('id'), details);
		}
	});
	mr_reservierung.table.on( 'click', 'td.blocked', function(event) {
		console.log('click td.blocked');
		alert('Reservierungsdetails:\n'+$(event.target).attr("title"));
		event.stopPropagation();
	});

}

mr_reservierung.setup_button_click_handlers_edit = function(){
	mr_reservierung.table.on( 'click', 'td.editable input', function(event) {
		var pre = '';
		var new_owner = '';
		var details = '';

		if (jQuery(event.target).attr('data') == M.mdl_user_id) {
			pre = 'RESERVIERUNG ÄNDERN ODER LÖSCHEN!\n';
		}
		else {
			if (jQuery('#id').length > 0) {
				if ( jQuery(event.target).attr('data') == jQuery('#id').val() ) {
					pre = 'IN VERTRETUNG ÄNDERN ODER LÖSCHEN!\n';
				}
				else {
					pre = 'RESERVIERUNG ÜBERSCHREIBEN\n';
					if (jQuery('#id').val() == 0) {
						new_owner = 'FÜR '+M.mdl_user_firstname.toUpperCase()+' '+M.mdl_user_lastname.toUpperCase()+'\n';
					}
					else {
						new_owner = 'FÜR '+jQuery("#id option:selected").text().toUpperCase()+'\n';
					}
				}
			} 
		}

		details = prompt(
			pre+new_owner+jQuery(this).parent().attr('title')+'\nBitte neue Daten eintragen.\nEin leeres Feld löscht die Reservierung.'
			, jQuery(this).val()
		);
		if (details.length > 0)	{
			mr_reservierung.make_reservation(jQuery(event.target).parent().attr('id'), details);
		}
		else {
			mr_reservierung.delete_reservation(jQuery(event.target).parent().data('res-id'));
		}
		event.stopPropagation();
	});
}

mr_reservierung.make_reservation = function(elmid, details){
	var d = elmid.substring(0,10);
	var h = parseInt(elmid.substr(elmid.length - 2));
	mr_reservierung.disable_form_elements();
	var parameter;
	if (mr_reservierung.is_res_admin()) {
		var res_for = 0;
		if (jQuery('#id').length > 0)  {
			res_for = jQuery('#id').val();
		}
		parameter = {
			id: res_for,
			asset: jQuery('#asset').val(),
			info: details,
			hour: h,
			date: d
		}
	}
	else {
		parameter = {
			asset: jQuery('#asset').val(),
			info: details,
			hour: h,
			date: d
		}
	}
	jQuery.getJSON( 'make-reservation-json.php', parameter, 
		function( data ) {
			if (data['error'] != null) {
				alert(data['error']);
				mr_reservierung.enable_form_elements();
			}
			else {
				console.log(data['success']);
				mr_reservierung.update();
			}
		}
	);
}

mr_reservierung.disable_form_elements = function(){
	mr_reservierung.table.addClass('disabled');
	jQuery('.editable :button',mr_reservierung.table).prop( "disabled", true );
	jQuery('#asset').prop( "disabled", true );
	if (jQuery('#von').length > 0) {
		jQuery('#von').prop( "disabled", true );
	}
	if (jQuery('#id').length > 0) {
		jQuery('#id').prop( "disabled", true );
	}
}

mr_reservierung.enable_form_elements = function(){
	mr_reservierung.table.removeClass('disabled');
	jQuery('.editable :button',mr_reservierung.table).prop( "disabled", false );
	jQuery('#asset').prop( "disabled", false );
	if (jQuery('#von').length > 0) {
		jQuery('#von').prop( "disabled", false );
	}
	if (jQuery('#id').length > 0) {
		jQuery('#id').prop( "disabled", false );
	}
}

mr_reservierung.delete_reservation = function(res_id){
	mr_reservierung.disable_form_elements()
	jQuery.getJSON( 'delete-reservation-json.php', {
		id: res_id
	}, 
	function( data ) {
		if (data['error'] != null) {
			alert(data['error']);
		}
		else {
			mr_reservierung.update();
		}
	});
}

mr_reservierung.activate_buttons = function(){
	jQuery('#tabelle-reservierungen td').html('<input type="button" value="'+jQuery(this).text()+'">')
}


mr_reservierung.secure_in_kiosk_mode = function(){
	if (mr_runs_in_kiosk_mode) {
		// disable logo image link
		jQuery("a.brand").attr('href','#'); 
		// user menu links, exept logout
		jQuery(".usermenu ul[role='menu'] li[role='presentation']").each(function( index ) {
			var href = jQuery( this ).find('a').attr('href');
			if (typeof href == 'string') {
				if (href.indexOf('logout.php') < 0) {
					jQuery( this ).remove();
				}
			}
			else {
				jQuery( this ).remove();
			}
		}); 
	}
}

mr_reservierung.insert_starting_date_select_box = function(){
	// if ( (!mr_reservierung.is_logged_in) || (mr_reservierung.is_res_admin()) ) {
		// jQuery("#asset").after(' <input type="date" name="von" id="von" placeholder="yyyy-mm-dd" size="10" value="'+new Date().yyyymmdd()+'"> ');
		var date_select_box = ' <select name="von" id="von"><option value="'+(new Date().yyyymmdd())+'">ab heute</option>';
		for (i = 0; i<25*7; i++) {
			d = mr_reservierung.addDays(new Date(),i);
			if (d.getDay() == 1) { // Mondays
				date_select_box += '<option value="'+(d.yyyymmdd())+'">ab '+mr_reservierung.weekdays[d.getDay()]+', '+d.getDate()+'. '+mr_reservierung.month_name[d.getMonth()]+'</option>'
			}
		}
		date_select_box += '</select> ';
		jQuery("#asset").after(date_select_box);
		jQuery('#von')
			.attr('data', jQuery("#von").val())
			.bind('change', function() {
				if (jQuery('#von').attr('data') != jQuery("#von").val()) {
					mr_reservierung.update();
					jQuery('#von').attr('data', jQuery("#von").val())
				}
			});
	// }
}

mr_reservierung.insert_teacher_select_box = function(){
	if (mr_reservierung.is_res_admin()) {
		var date_select_box = ' <select name="id" id="id"></select> ';
		jQuery("#asset").after(date_select_box);
		mr_reservierung.load_teacher_options();
	}
}

mr_reservierung.load_teacher_options = function(){
	jQuery.getJSON( 'get-teachers-json.php', function( data ) {
		if (data['error'] != null) {
			alert(data['error']);
		}
		else {
			var options_text = '<option value="0">---</option>';
			jQuery.each( data, function( key, obj ) {
				options_text += '<option value="'+obj['id']+'">'+obj['lastname']+', '+obj['firstname']+'</option>';
			});
			jQuery('select#id').html(options_text);
		}
	});
}

mr_reservierung.enable_update_table_on_mouse_wiggle = function(){
	// allow mouse wiggle to update the table
	jQuery(document).mousemove(function( event ) {
		// console.log(mr_reservierung.last_update_timestamp);
		if (mr_reservierung.last_update_timestamp > 0) { // wait for the first update
			var now = new Date().getTime();
			// console.log(now);
			if (now > mr_reservierung.last_update_timestamp+(5*60*1000)) { // allow update only after 5min.
				mr_reservierung.update();
			}
		}
	});
}


mr_reservierung.insert_logon_logoff_buttons = function(){
	if (mr_reservierung.is_logged_in) {
		jQuery("#asset").after('<input type="button" class="btn btn-default btn-lg" value="'+M.mdl_user_firstname+' '+M.mdl_user_lastname+' abmelden" name="res-logoff" id="res-logoff">');
		jQuery("#res-logoff").click(function() {
			window.location = jQuery("a[href*='/moodle/login/logout.']").attr('href')+'&goto='+window.location;
		});
		var original_url = jQuery("a[href*='/moodle/login/logout.']").attr('href');
		jQuery("a[href*='/moodle/login/logout.']").attr('href',original_url+'&goto='+window.location);
	}
	else {
		jQuery("#asset").after('<input type="button" value="anmelden" name="res-login" id="res-login">');
		jQuery("#res-login").click(function() {
			window.location = '/moodle/login/index.php';
		});
	}
}

mr_reservierung.moodle_page_mods = function(){
	// moodle page mods
	jQuery('.navbar').addClass('hidden-xs').addClass('hidden-sm');
	jQuery('aside').remove();
	jQuery('#region-main-box').removeClass('span9').addClass('span12');
	jQuery('#region-main').removeClass('span8').addClass('span12');
}


$(document).ready(function(){
	mr_reservierung.table = jQuery('#tabelle-reservierungen');
	mr_reservierung.load_asset_options();
	mr_reservierung.moodle_page_mods();
	mr_reservierung.setup_button_click_handlers();
	mr_reservierung.insert_logon_logoff_buttons();
	mr_reservierung.insert_teacher_select_box();
	mr_reservierung.insert_starting_date_select_box();
	mr_reservierung.secure_in_kiosk_mode();
	mr_reservierung.enable_update_table_on_mouse_wiggle();
});


