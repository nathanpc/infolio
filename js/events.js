/**
 * events.js
 * Event handling.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

/**
 * Handles the window resizing event.
 */
window.onresize = function() {
	// I know this looks absolutely awful, but I'm in an airplane and extremely tired, give me a break.
	$(".split-panel").height($(window).height() - ($(".navbar-container").height() + $(".footer-container").height()) - 25);
}

/**
 * Things to do when the DOM is ready.
 */
$(document).ready(function () {
	window.onresize();
});

