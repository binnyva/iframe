//This function will check/uncheck all the checkboxes if the main one is clicked.
function checkAll() {
	$(".select-row").prop("checked", this.checked);
}

function submit(action) {
	if(action != "sort") {
		var selecteds = $(".select-row");
		var selected_rows = 0;
		for(var i=0; i<selecteds.length; i++) {
			if($(selecteds[i]).prop("checked")) selected_rows++;
		}
		
		if(selected_rows) {
			if(action=="delete") {
				if(!confirm("Delete " + selected_rows + " row(s)?")) {
					return true; // If user press Cancel, return now itself - if not, the form will be submitted.
				}
			}
		}
		else alert("Please select an item");
	}
	
	$("#list-form-action").val(action);
	$("#display-form").submit();
}

function selectRow () {
	var check = this.firstChild.firstChild;
	if(check) check.checked=true;
}

function init() {
	$("#selection-toggle").click(checkAll);
	//Remove the all-selected if any checkbox has been unselected.
	$(".select-row").click(function(e) {
		if(!this.checked) $("#selection-toggle").prop("checked", false);
	});

	//For going to the edit section if a row is clicked.
	$(".data-table tr").click(selectRow);
	$(".table tr").click(selectRow);
	
	if(window.main) main();
}
