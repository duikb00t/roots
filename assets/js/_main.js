/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can 
 * always reference jQuery with $, even when in .noConflict() mode.
 *
 * Google CDN, Latest jQuery
 * To use the default WordPress version of jQuery, go to lib/config.php and
 * remove or comment out: add_theme_support('jquery-cdn');
 * ======================================================================== */

(function ($) {

// Use this variable to set up the common and page specific functions. If you 
// rename this variable, you will also need to rename the namespace below.
	var Roots = {
		// All pages
		common: {
			init: function () {
				// JavaScript to be fired on all pages

				// Function to enable L1 navigation dropdown item links
				// Tested on bootstrap 3.3.1
				$('#menu-primary-navigation .dropdown').on('show.bs.dropdown', function (e) {
					e.preventDefault();
				});

				// requires .videoWrapper style
				// change static sized iframe video to responsive sized ( add checks to apply for any other than Youtube)
				if ($("iframe[src^='http://www.youtube.com'], iframe[src^='https://www.youtube.com']").length) {
					$("iframe[src^='http://www.youtube.com'], iframe[src^='https://www.youtube.com']").removeAttr('height').removeAttr('width').wrap("<div class='videoWrapper'></div>");
				}

				// Add Google Analytics plugin to Wistia videos.
				// UNCOMMENT THIS SECTION IF USING WISTIA
				//wistiaEmbeds.onFind(function(video) {
				//  video.addPlugin("googleAnalytics", {
				//    src: "//fast.wistia.com/labs/google-analytics/plugin.js",
				//    outsideIframe: true
				//  });
				//});

				// RESPONSIVE TABLE FIXER
				// UNCOMMENT THIS SECTION IF YOU WANT RESPONSIVE TABLES
				//Roots.common.responsiveTable();
			},
			responsiveTable: function () {
				if ($('table').length) {
					// just get the user created tables
					$table = $('table').not('.crayon-table');

					//first fix any tables without theads
					$($table).each(function () {
						$hasHead = $('thead td, thead th', this).length;
						if (!$hasHead) {
							$(this).prepend('<thead></thead>').find("tr:first").prependTo($('thead', this));
						}
					});

					//second update tables to have data attrs
					$($table).each(function () {
						$hasHead = $('thead td, thead th', this).length;
						$col_titles = [];

						if ($hasHead) {//make sure our current table has what we need to get started.
							// cache our column titles (include td for bad html)
							$(this).find('th, td').each(function () {
								$content = $(this).text() + ': ';
								$col_titles.push($content);
							});

							// add our column titles to data attrs on each tr>td
							$(this).find('tr').each(function () {
								$row = $(this);
								$row.children("td").each(function (key) {
									$(this).attr('data-label', $col_titles[key]);
								});
							});
						}
					});
				}
			}

		},
		// Home page
		home: {
			init: function () {
				// JavaScript to be fired on the home page
			}
		},
		// About us page, note the change from about-us to about_us.
		about_us: {
			init: function () {
				// JavaScript to be fired on the about us page
			}
		}
	};

// The routing fires all common scripts, followed by the page specific scripts.
// Add additional events for more control over timing e.g. a finalize event
	var UTIL = {
		fire: function (func, funcname, args) {
			var namespace = Roots;
			funcname = (funcname === undefined) ? 'init' : funcname;
			if (func !== '' && namespace[func] && typeof namespace[func][funcname] === 'function') {
				namespace[func][funcname](args);
			}
		},
		loadEvents: function () {
			UTIL.fire('common');

			$.each(document.body.className.replace(/-/g, '_').split(/\s+/), function (i, classnm) {
				UTIL.fire(classnm);
			});
		}
	};

	$(document).ready(UTIL.loadEvents);

})(jQuery); // Fully reference jQuery after this point.
