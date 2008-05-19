//Make the rounded corners, well..., rounded.
function makeRounded() {
	JSL.dom("rounded-corner").each(function(ele) {
		var inner = ele.innerHTML;//Or do it using DOM.
		ele.innerHTML = "<div class='wrapper1'><div class='wrapper2'>"
				+ "<div class='wrapper3'><div class='wrapper4'>"
				+ inner + "</div></div></div></div>";
    });
}
$(window).load(makeRounded);
