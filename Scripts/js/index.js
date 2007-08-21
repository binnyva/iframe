var field_count = 1;
var code = '';

//Create the fieldset for a new database field.
function newField() {
	field_count++;
	var field = document.createElement("div");
	//InnerHTML is propabily the safest approch - cloneNode is possible - but we need to change the ids and everything.
	field.innerHTML = code.replace(/%COUNT%/g,field_count);
	$('extra_fields').appendChild(field);
	
	registerEventHandles(field_count);
	makeHelpText(field_count);
}

//This function will open up more options for specific type. For eg, it will show the 'Date Format' option if the date type is selected.
function fieldOptions(ele) {
	var type = ele.value;
	var field = ele.parentNode;

	var fc = getFieldCount(ele);

	var field = $('field_'+fc);
	var options = document.getElementsByClassName('type_options', field);
	for(var i=0; i<options.length; i++) {
		Element.hide(options[i]);
	};

	if(type == 'file') {
		document.getElementsByClassName('file_options',field)[0].style.display = 'block';
	} else if(type == 'date') {
		document.getElementsByClassName('date_options',field)[0].style.display = 'block';
	} else if(type == 'list') {
		document.getElementsByClassName('list_options',field)[0].style.display = 'block';
	} else if(type == 'password') {
		document.getElementsByClassName('password_options',field)[0].style.display = 'block';
	}
}

//Upon entering the title, this function will automatically fill in the field name
function autoFillFieldDetails(ele) {
	if(!ele) return;
	var fc = getFieldCount(ele);
	var field = $("field_title_"+fc).value;
	if($("field_"+fc).value) return;
	$("field_"+fc).value = unformat(field);
	
	var list = $("field_list_"+fc); //Wether or not this field should be listed.
	field = field.toLowerCase();
	//Auto Select the validations
	var validations = $("field_validation_"+fc).getElementsByTagName("option");
	if((field.indexOf("username") + 1) || (field.indexOf("id") + 1) || (field.indexOf("login") + 1)) {
		validations[0].selected = true; //Mandatory validation.
		validations[4].selected = true; //Unique validation 
	}
	if(field.indexOf("mail") + 1) {
		validations[1].selected = true; //Turn on the email validation. Yup, we hard codded the '[1]' part "This is a sad day for Science"(Dexter)
	}
	if((field.indexOf("price") + 1) || (field.indexOf("number") + 1) || (field.indexOf("amount") + 1)) {
		validations[3].selected = true; //Turn on the Number validation.
	}
	
	//Select types
	var type = $("field_type_"+fc)
	if(field.indexOf("date") + 1) {
		type.value = "date";
		fieldOptions(ele);
	}
	else if(field.indexOf("time") + 1) {
		type.value = "time";
		fieldOptions(ele);
	}
	else if(field.indexOf("desc") + 1) { //Description
		type.value = "textarea";
		list.checked = false;
	}
	else if(field.indexOf("content") + 1) {
		type.value = "editor";
		list.checked = false;
	}
	else if(field.indexOf("pass") + 1) {
		type.value = "password";
		list.checked = false;
		validations[0].selected = true; //Mandatory
		fieldOptions(type);
	}
	//All types of file uploads are handled here.
	else if((field.indexOf("file") + 1) || (field.indexOf("upload") + 1) || (field.indexOf("logo") + 1) || 
				(field.indexOf("image") + 1) || (field.indexOf("img") + 1) || (field.indexOf("picture") + 1) || 
				(field.indexOf("photo") + 1) ){
		type.value = "file";
		fieldOptions(type);
		list.checked = false;
	}
	
	//The extra options
	if((field.indexOf("image") + 1) || (field.indexOf("img") + 1) || (field.indexOf("pic") + 1) || 
				(field.indexOf("logo") + 1) || (field.indexOf("photo") + 1)) {
		$("field_filetype_"+fc).value = "jpg,jpeg,png,gif";
		validations[2].selected = true; //Filetype validation
	}
}

// Makes sure all the event handles handles for the new fields are set
function registerEventHandles() {
	for (var i = 0; fc=arguments[i], i<arguments.length; i++) {
		if(!$('field_type_'+fc)) continue;

		//Upon entering the title, this function will automatically fill in the field name
		$('field_title_'+fc).onchange = function(e) {
			if(!e) e = window.event;
			var ele = this || e.src;
			autoFillFieldDetails(ele);
		}

		$('field_type_'+fc).onchange = function(e) {
			if(!e) e = window.event;
			var ele = this || e.src;
			fieldOptions(ele);
		}
	}
}

/**
 * Helping functions. This function will replace all help text with a '?' and gives it a mouseover and mouseout event
 */
function makeHelpText(field_count) {
	var helps = []
	if(field_count) {
		helps = document.getElementsByClassName("help",$('field_'+field_count));
	} else {
		helps = document.getElementsByClassName("help");
	}
	
	for(var i=0; i<helps.length; i++) {
		var help_text = helps[i].innerHTML;
		helps[i].innerHTML = "<a href='#' onmouseover='tip(this,\""+escape(help_text)+"\")' onmouseout='clearTip()'> ? </a>"
	}
}
//Shows the tip by positioning the 'tip' div next to the '?' that was mouseover'ed and giving it the help text
function tip(ele,help_text) {
	if(!$('tip-holder') || !$('tip')) return;
	$('tip-holder').style.display = "block";
	var xy = Position.cumulativeOffset(ele)
	$('tip-holder').style.left = (xy[0] + 10) + 'px';
	$('tip-holder').style.top = xy[1] + 'px';

	$('tip').innerHTML = unescape(help_text);
}
//Hide the tip - happens on mouseout
function clearTip() {
	if(!$('tip-holder') || !$('tip')) return;
	$('tip-holder').style.display = "none";
	$('tip').innerHTML = ''
}

//Parses the given elements id and finds its field count - the digit after the last '_'
function getFieldCount(ele) {
	var parts = ele.id.split('_');
	var fc = parts[parts.length-1];
	return fc;
}

function setCharAt(str, index, char) {
	if(index > str.length-1) return str;
	return str.substring(0,index) + char + str.substring(index+1);
}

//Return the string after formatting it - all the _ will be made space and the text will be title cased.
function format(str) {
	str = str.replace(/_/g,' ');
	str = setCharAt(str, 0, str.charAt(0).toUpperCase());
	for(var i=0; i<str.length-1; i++) {
		if(str.charAt(i) === ' ')
			str = setCharAt(str, i+1, str.charAt(i+1).toUpperCase());
	}
	return str;
}
//All char will be lower cased and all spaces will become '_'
function unformat(str) {
	var result = str.toLowerCase();
	result = result.replace(/ /g,'_');
	return result;
}

/**
 * This funciton will take a JSON string generated at code creation time and use it to auto fill the fields next time.
 */
function parseSerializedData() {
	var data = eval("(" + $F("serialized") + ")");

	//Create fields before inserting the data.
	var fc = data['field_count'];
	for(var j=1;j<fc-1;j++) {
		newField(); //Create the new fields
	}
	
	for(var id in data) { //Handle each element
		var value = data[id];
		var ele = $("" + id);
		
		if(!ele && id.match(/_\d$/) && value) { //This data should be in a higher field.
			//We got issues
		}
		
		if(ele) {
			if(ele.tagName=="INPUT") {
				if(ele.type=="checkbox") { //Handle checkboxes
					ele.checked = (value) ? true : false;
				} else {
					ele.value = value;
				}
				// :TODO: Radio buttons?

			} else if(ele.tagName=="SELECT") {
				if(typeof(value) == "object") { //Multiple select cases.
					var options = ele.getElementsByTagName("option");
					for(var i=0; i<options.length; i++) { //Go thru each option and enble the ones that were chosen
						if(options[i].value == value)
							options[i].selected = true;
					}
				} else {
					ele.value = value;
				}
				if(id.match(/^field_type/)) fieldOptions(ele);//This is needed to show the extra options

			} else if(ele.tagName=="TEXTAREA") {
				ele.value = value;
			}
		}
	}
}

function init() {
	//Get the HTML required for making more fields.
	code = '<fieldset class="field" id="field_%COUNT%">' + $('field_1').innerHTML + '</fieldset><br />';
	code = code.replace(/_1/g,"_%COUNT%");
	code = code.replace(/Field 1/g,"Field %COUNT%");
	
	field_count = $("fields_area").getElementsByTagName("fieldset").length;

	//Set up the handlers
	$("parse-serialized-data").onclick = parseSerializedData;
	$("clearer").onclick = function(e) { //Clear the fields
		var e = e || window.event;
		if(field_count > 1) {//If more than one field is set, ask for confirmation before deleting everything
			if(!confirm("Are you sure?")) {
				Event.stop(e);
			}
		}
	}
	$('title').onchange = function() {
		var title = $F('title');
		if( !$F('table') ) $('table').value = unformat($F('title'));
		if( !$F('file')  ) $('file').value  = $F('table') + ".php";
		if( !$F('single')) $('single').value= $F('title').replace(/e?s$/i,'');
	}
	
	for(var i=1; i<=field_count; i++) {
		registerEventHandles(i);
		autoFillFieldDetails($('field_title_'+i));
		fieldOptions($('field_type_'+i));
		makeHelpText(i-1);
	}
}
Event.observe(window, 'load', init, false);
