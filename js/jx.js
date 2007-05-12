jx = {
	http : false, // We create the HTTP Object
	format : 'text',
	callback : function(data){},
	//Create a xmlHttpRequest object - this is the constructor. 
	getHTTPObject : function() {
		var http = false;
		//Use IE's ActiveX items to load the file.
		if(typeof ActiveXObject != 'undefined') {
			try {http = new ActiveXObject("Msxml2.XMLHTTP");}
			catch (e) {
				try {http = new ActiveXObject("Microsoft.XMLHTTP");}
				catch (E) {http = false;}
			}
		//If ActiveX is not available, use the XMLHttpRequest of Firefox/Mozilla etc. to load the document.
		} else if (XMLHttpRequest) {
			try {http = new XMLHttpRequest();}
			catch (e) {http = false;}
		}
		return http;
	},

	// This function is called from the user's script. 
	//Arguments - 
	//	url	- The url of the serverside script that is to be called. Append all the arguments to 
	//			this url - eg. 'get_data.php?id=5&car=benz'
	//	callback - Function that must be called once the data is ready.
	//	format - The return type for this function. Could be 'json' or 'text'. If it is json, the string will be
	//			'eval'ed before returning it. Default:'text'
	//	method - GET or POST. Default 'GET'
	load : function (url,callback,format,method) {
		this.init(); //The XMLHttpRequest object is recreated at every call - to defeat Cache problem in IE
		if(!this.http||!url) return;

		if(callback) this.callback=callback;
		if(!method) var method = "GET";//Default method is GET
		if(!format) var format = "text";//Default return type is 'text'
		this.format = format.toLowerCase();
		method = method.toUpperCase();

		var parameters = null;

		//Kill the Cache problem in IE.
		var now = "uid=" + new Date().getTime();
		url += (url.indexOf("?")+1) ? "&" : "?";
		url += now;
		
		var parts = url.split("\?");
		url = parts[0];
		parameters = parts[1];
		parameters = parameters.replace(/ /g,'+');
		
		if(method != "POST") {
			url = url + "?" + parameters;
		}
		this.http.open(method, url, true);

		if(method=="POST") {
			this.http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			this.http.setRequestHeader("Content-length", parameters.length);
			this.http.setRequestHeader("Connection", "close");
		}

		var ths = this;
		this.http.onreadystatechange = function () {
			var http = ths.http;
			if (http.readyState == 4) {//Ready State will be 4 when the document is loaded.
				if(http.status == 200) {
					var result = "";
					if(http.responseText) result = http.responseText;
					//If the return is in JSON format, eval the result before returning it.
					if(ths.format.charAt(0) == "j") {
						result = result.replace(/[\n\r]/g,"");//\n's in the text to be evaluated will create problems in IE
						result = eval('('+result+')'); 
					}
	
					//Give the data to the callback function.
					if(ths.callback) ths.callback(result);
				}
			}
		}
		this.http.send(parameters);
	},
	init : function() {this.http = this.getHTTPObject();}
}