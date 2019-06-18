/**  beginning of redde-checkout-status.js */
var logging = true;
(function () {
	const cUrl = $(location).attr("href");
	start(cUrl);
	function smb() {
		$("#pb").hide();
		$("#mb").show();
	}
	function spr() {
		$("#mb").hide();
		$("#pb").show();
	}
	function cs(period) {
		$(".r-o").delay(period).fadeOut(300);
	}
	function ss() {
		$(".r-o").show();
	}
	function rs(period) {
		if (period) {
			cs(period);
		} else {
			cs(1000);
		}
	}
	function start(aURL) {
			smb();
			dt(rs(), 100);
	}
	function rt(a, p) {
		setTimeout(() => {
			window.location.href = a;
		}, p);
	}
})(jQuery, document, window);
$(document).bind("contextmenu", function (e) {
	return false;
});
document.onkeydown = function(e) {
	if(event.keyCode == 123) {
		return false;
	}
	if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
		return false;
	}
	if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
		return false;
	}
	if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
		return false;
	}
	if(e.ctrlKey && e.shiftKey && e.keyCode == 'E'.charCodeAt(0)) {
		return false;
	}
	if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
		e.preventDefault();
		return false;
	}
	if(e.ctrlKey && (e.which == 69)) {
		e.preventDefault();
		return false;
	}
	if(e.ctrlKey && (e.which == 73)) {
		e.preventDefault();
		return false;
	}
	if(e.ctrlKey && (e.which == 83)) {
		e.preventDefault();
		return false;
	}
}
function dt(a, p) {
	setTimeout(() => {
		a
	}, p);
}
function shout(a) {
	if (logging) {
		console.log(a);
	}
}
/**  End of redde-checkout-status.js */
