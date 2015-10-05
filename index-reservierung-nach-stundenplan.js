
Date.prototype.yyyymmdd = function() {
   var yyyy = this.getFullYear().toString();
   var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
   var dd  = this.getDate().toString();
   return yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]); // padding
};


var mr_reservierung = {};

mr_reservierung.no_of_weeks = 3;

mr_reservierung.data_queue;

mr_reservierung.table = null;

mr_reservierung.load_asset_options = function(){
	jQuery.getJSON( 'get-assets-json.php', function( data ) {
		if (data['error'] != null) {
			alert(data['error']);
		}
		else {
			var options_text = '';
			jQuery.each( data, function( key, obj ) {
				options_text += '<option value="'+obj['id']+'">'+obj['name']+'</option>';
			});
			jQuery('#asset').html(options_text);
			mr_reservierung.load_teacher_options();
		}
	});
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
			jQuery('select.teacher').html(options_text);
			jQuery('#submitbutton').click(mr_reservierung.save_to_db);
		}
	});
}

mr_reservierung.clear_stundenplan_form = function(){
	jQuery('#tabelle-reservierungen select').val(0);
	jQuery('#tabelle-reservierungen input[type="text"]').val('');
}

mr_reservierung.console_log = function(text){
	var $console = jQuery('#console');
	$console.val($console.val()+text+'\n');
	$console.scrollTop = 999999;
}

mr_reservierung.console_clear = function(text){
	jQuery('#console').val('');
}

mr_reservierung.save_to_db = function(){
	var wochentag;
	var stunde;
	var teacher;
	var info;
	var start;
	var end;
	var allDates;
	var currentDate;
	var i;
	var asset;

	asset = jQuery("#asset").val();
	startDate = new Date( jQuery("#von").val().replace( /(\d{4})-(\d{2})-(\d{2})/, "$1/$2/$3") );
	endDate = new Date( jQuery("#bis").val().replace( /(\d{4})-(\d{2})-(\d{2})/, "$1/$2/$3") );
	mr_reservierung.console_log('Erster Tag: '+startDate.toString());
	mr_reservierung.console_log('Letzter Tag: '+endDate.toString());
    currentDate = startDate;
    allDates = [];

	while (currentDate <= endDate) {
		allDates.push(new Date(currentDate));
		currentDate.setDate(currentDate.getDate() + 1);
	}	

	/*
	for (i = 0; i < allDates.length; ++i) {
		console.log(allDates[i].toString());
	}
	*/
	mr_reservierung.data_queue = new Array();
	jQuery('#tabelle-reservierungen tbody td').each(function(index){
		// mr_reservierung.console_log(index);
		wochentag = $( this ).data('day');
		stunde = $( this ).parent().data('hour');
		teacher = $(this).find('.teacher').val();
		info = jQuery.trim($(this).find('.info').val());
		if ( (teacher > 0) && (info.length > 0) ) {
			for (i = 0; i < allDates.length; ++i) {
				if (allDates[i].getDay() == wochentag) {
					mr_reservierung.data_queue.push(new Array(allDates[i].yyyymmdd(), asset, teacher, info, stunde));
				}
			}
		}
	});
	mr_reservierung.pop_data_and_submit_to_db();	
};

mr_reservierung.pop_data_and_submit_to_db = function(){
	if (mr_reservierung.data_queue.length > 0) {
		var record = mr_reservierung.data_queue.pop();
		mr_reservierung.console_log("submitting: "+record[0]+"(asset "+record[1]+", teacher "+record[2]+", info "+record[3]+", stunde "+record[4]+")");
		jQuery.getJSON( 'make-reservation-json.php', {
			id: record[2],
			asset: record[1],
			info: record[3],
			hour: record[4],
			date: record[0],
			fix: 1
		}, 
		function( data ) {
			if (data['error'] != null) {
				mr_reservierung.console_log('ERROR: '+data['error']);
				mr_reservierung.pop_data_and_submit_to_db();	
			}
			else {
				mr_reservierung.console_log('OK: '+data['success']);
				mr_reservierung.pop_data_and_submit_to_db();	
			}
		});
	}
	else {
		mr_reservierung.console_log("data_queue empty");
	}
};


$(document).ready(function(){
	mr_reservierung.table = jQuery('#tabelle-reservierungen');
	mr_reservierung.load_asset_options();
	// moodle page mods
	jQuery('.navbar').addClass('hidden-xs').addClass('hidden-sm');
	jQuery('aside').remove();
	jQuery('#region-main-box').removeClass('span9').addClass('span12');
	jQuery('#region-main').removeClass('span8').addClass('span12');
	jQuery('#console').val('');
	jQuery('#clearform').click(mr_reservierung.clear_stundenplan_form);
});


