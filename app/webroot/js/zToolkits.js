/**
 *This toolkits including some self-developed scripts that somehow useful
 *!!Attention: This script must be included after jquery
 */
function __zClearForm(frm) {
	jQuery("#" + frm).find("input").each(function(){
		if (this.type == "text") jQuery(this).val("");
	});
	jQuery("#" + frm).find("select").each(function(){
		jQuery(this).get(0).selectedIndex = 0;
	});
}

function __zSetFromTo(sel, iptstart, iptend) {
	var datestr = jQuery("#" + sel).val();
	var dates = datestr.split(",");
	if (dates.length == 2) {
		jQuery("#" + iptstart).val(dates[0]);
		jQuery("#" + iptend).val(dates[1]);
	}
}

function __zYesterday(ipt) {
	var oneDate = new Date();
	oneDate.setDate(oneDate.getDate() - 1);
	window.document.getElementById(ipt).value =
		oneDate.getFullYear() + "-" + (oneDate.getMonth() + 1) + "-" + oneDate.getDate();
}

function __zOneWeek(s, e, ipts, ipte) {
	window.document.getElementById(ipts).value = s;
	window.document.getElementById(ipte).value = e;
		
}

function __zOneMonth(m, lastDate, ipts, ipte) {
	window.document.getElementById(ipts).value = m + "-01";
	window.document.getElementById(ipte).value = m + "-" + lastDate;
}

function __changeAction(formId, url) {
	var frm = document.getElementById(formId);
	frm.action = url;
	//alert(frm.action);
}

/*script by Josh Fraser (http://www.onlineaspect.com)*/
/*start*/
function timezones(offset, name) {
	this.offset = offset;
	this.name = name;
}

function calculate_time_zone() {
	var rightNow = new Date();
	var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);  // jan 1st
	var june1 = new Date(rightNow.getFullYear(), 6, 1, 0, 0, 0, 0); // june 1st
	var temp = jan1.toGMTString();
	var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	temp = june1.toGMTString();
	var june2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	var std_time_offset = (jan1 - jan2) / (1000 * 60 * 60);
	var daylight_time_offset = (june1 - june2) / (1000 * 60 * 60);
	var dst;
	if (std_time_offset == daylight_time_offset) {
		dst = "0"; // daylight savings time is NOT observed
	} else {
		// positive is southern, negative is northern hemisphere
		var hemisphere = std_time_offset - daylight_time_offset;
		if (hemisphere >= 0)
			std_time_offset = daylight_time_offset;
		dst = "1"; // daylight savings time is observed
	}
	//set timezones basic data
	var tzs = new Array();
	tzs[0] = new timezones("-12:00,0", "International Date Line West");
	tzs[1] = new timezones("-11:00,0", "Midway Island, Samoa");
	tzs[2] = new timezones("-10:00,0", "Hawaii");
	tzs[3] = new timezones("-09:00,1", "Alaska");
	tzs[4] = new timezones("-08:00,1", "Pacific Time (US & Canada)");
	tzs[5] = new timezones("-07:00,0", "Arizona");
	tzs[6] = new timezones("-07:00,1", "Mountain Time (US & Canada)");
	tzs[7] = new timezones("-06:00,0", "Central America, Saskatchewan");
	tzs[8] = new timezones("-06:00,1", "Central Time (US & Canada), Guadalajara, Mexico city");
	tzs[9] = new timezones("-05:00,0", "Indiana, Bogota, Lima, Quito, Rio Branco");
	tzs[10] = new timezones("-05:00,1", "Eastern time (US & Canada)");
	tzs[11] = new timezones("-04:00,1", "Atlantic time (Canada), Manaus, Santiago");
	tzs[12] = new timezones("-04:00,0", "Caracas, La Paz");
	tzs[13] = new timezones("-03:30,1", "Newfoundland");
	tzs[14] = new timezones("-03:00,1", "Greenland, Brasilia, Montevideo");
	tzs[15] = new timezones("-03:00,0", "Buenos Aires, Georgetown");
	tzs[16] = new timezones("-02:00,1", "Mid-Atlantic");
	tzs[17] = new timezones("-01:00,1", "Azores");
	tzs[18] = new timezones("-01:00,0", "Cape Verde Is.");
	tzs[19] = new timezones("00:00,0", "Casablanca, Monrovia, Reykjavik");
	tzs[20] = new timezones("00:00,1", "GMT: Dublin, Edinburgh, Lisbon, London");
	tzs[21] = new timezones("+01:00,1", "Amsterdam, Berlin, Rome, Vienna, Prague, Brussels");
	tzs[22] = new timezones("+01:00,0", "West Central Africa");
	tzs[23] = new timezones("+02:00,1", "Amman, Athens, Istanbul, Beirut, Cairo, Jerusalem");
	tzs[24] = new timezones("+02:00,0", "Harare, Pretoria");
	tzs[25] = new timezones("+03:00,1", "Baghdad, Moscow, St. Petersburg, Volgograd");
	tzs[26] = new timezones("+03:00,0", "Kuwait, Riyadh, Nairobi, Tbilisi");
	tzs[27] = new timezones("+03:30,0", "Tehran");
	tzs[28] = new timezones("+04:00,0", "Abu Dhadi, Muscat");
	tzs[29] = new timezones("+04:00,1", "Baku, Yerevan");
	tzs[30] = new timezones("+04:30,0", "Kabul");
	tzs[31] = new timezones("+05:00,1", "Ekaterinburg");
	tzs[32] = new timezones("+05:00,0", "Islamabad, Karachi, Tashkent");
	tzs[33] = new timezones("+05:30,0", "Chennai, Kolkata, Mumbai, New Delhi, Sri Jayawardenepura");
	tzs[34] = new timezones("+05:45,0", "Kathmandu");
	tzs[35] = new timezones("+06:00,0", "Astana, Dhaka");
	tzs[36] = new timezones("+06:00,1", "Almaty, Nonosibirsk");
	tzs[37] = new timezones("+06:30,0", "Yangon (Rangoon)");
	tzs[38] = new timezones("+07:00,1", "Krasnoyarsk");
	tzs[39] = new timezones("+07:00,0", "Bangkok, Hanoi, Jakarta");
	tzs[40] = new timezones("+08:00,0", "Beijing, Hong Kong, Singapore, Taipei");
	tzs[41] = new timezones("+08:00,1", "Irkutsk, Ulaan Bataar, Perth");
	tzs[42] = new timezones("+09:00,1", "Yakutsk");
	tzs[43] = new timezones("+09:00,0", "Seoul, Osaka, Sapporo, Tokyo");
	tzs[44] = new timezones("+09:30,0", "Darwin");
	tzs[45] = new timezones("+09:30,1", "Adelaide");
	tzs[46] = new timezones("+10:00,0", "Brisbane, Guam, Port Moresby");
	tzs[47] = new timezones("+10:00,1", "Canberra, Melbourne, Sydney, Hobart, Vladivostok");
	tzs[48] = new timezones("+11:00,0", "Magadan, Solomon Is., New Caledonia");
	tzs[49] = new timezones("+12:00,1", "Auckland, Wellington");
	tzs[50] = new timezones("+12:00,0", "Fiji, Kamchatka, Marshall Is.");
	tzs[51] = new timezones("+13:00,0", "Nuku'alofa");
	var i;
	// check just to avoid error messages
	for (i = 0; i < tzs.length; i++) {
		if (tzs[i].offset == convert(std_time_offset)+","+dst) {
			return tzs[i].name + "(" + tzs[i].offset + ")";
		}
	}
}

function convert(value) {
	var hours = parseInt(value);
   	value -= parseInt(value);
	value *= 60;
	var mins = parseInt(value);
   	value -= parseInt(value);
	value *= 60;
	var secs = parseInt(value);
	var display_hours = hours;
	// handle GMT case (00:00)
	if (hours == 0) {
		display_hours = "00";
	} else if (hours > 0) {
		// add a plus sign and perhaps an extra 0
		display_hours = (hours < 10) ? "+0"+hours : "+"+hours;
	} else {
		// add an extra 0 if needed 
		display_hours = (hours > -10) ? "-0"+Math.abs(hours) : hours;
	}
	
	mins = (mins < 10) ? "0"+mins : mins;
	return display_hours+":"+mins;
}
/*script by Josh Fraser (http://www.onlineaspect.com)*/
/*end*/