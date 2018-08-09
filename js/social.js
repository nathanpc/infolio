/**
 * social.js
 * Stuff related to social media and all that sharing crap I hate.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

/**
 * Populates the href for the sharing buttons in the page.
 */
function populate_share_buttons() {
	// Simple URL shares.
	document.getElementById("facebook-share").href += encodeURIComponent(window.location.href);
	document.getElementById("gplus-share").href += encodeURIComponent(window.location.href);
	document.getElementById("whatsapp-share").href += encodeURIComponent(window.location.href);

	// Get page details.
	var title = document.title;
	var description = "";
	var image = "";
	var metas = document.getElementsByTagName("meta");
	for (var i = 0; i < metas.length; i++) {
		// Description
		if (metas[i].name == "description") {
			description = metas[i].content;
		}

		// Title
		if (metas[i].getAttribute("property") == "og:title") {
			title = metas[i].content;
		}

		// Image
		if (metas[i].getAttribute("property") == "og:image") {
			image = metas[i].content;
		}
	}

	// More complex shares.
	document.getElementById("twitter-share").href += encodeURIComponent(title + " \"" + description + "\" " + window.location.href);
	document.getElementById("linkedin-share").href += encodeURIComponent(window.location.href) + "&title=" + encodeURIComponent(title) + "&summary=" + encodeURIComponent(description) + "&source=Innove%20Workshop%20Company";
	document.getElementById("pinterest-share").href += encodeURIComponent(window.location.href) + "&media=" + encodeURIComponent(image) + "&description=" + encodeURIComponent(title);
	document.getElementById("email-share").href += "&subject=" + encodeURIComponent(title) + "&body=" + encodeURIComponent(description + "\n\n" + window.location.href);
}
