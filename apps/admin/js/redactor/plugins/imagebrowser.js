/**
 * Integrates Redactor with Elefant's thumbnail image browser.
 * Requires Elefant's filemanager/util/browser handler.
 */

if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.imagebrowser = {
	// Initialize the plugin
	init: function () {
		this.button.addAfter.call (this, 'link', 'imagebrowser', $.i18n ('Insert Image'), $.proxy (this.open_dialog, this));
	},
	
	open_dialog: function (self, evt, button) {
		$.filebrowser ({
			thumbs: true,
			callback: $.proxy (this.insert_image, this)
		});
	},
	
	insert_image: function (file) {
		this.exec.command.call (this, 'inserthtml', '<img src="' + file + '" alt="" style="" />');
	}
};