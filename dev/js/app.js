/**
 * App
 * @desc	All methods and global variables are contained in the App.
 *			Setup and global variables here.
 */
 var App = {

 	settings: {
 		$window:	$(window),
 		$body:		$('body'),
 	},

 	init: function() {

 	},
 	
}; // end of App

/**
* default
* @desc chart
*/
App.default = function () {
	var _this = this;

};

$(function ($, App) {
	App.init();
	App.default();
}(jQuery, App));