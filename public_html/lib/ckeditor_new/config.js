/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
	// Define changes to default configuration here. For example:
	config.language = 'fa';
	// config.uiColor = '#AADC6E';

	config.filebrowserBrowseUrl = '/finder/browse.php?type=files';
	config.filebrowserImageBrowseUrl = '/finder/browse.php?type=images';
	config.filebrowserFlashBrowseUrl = '/finder/browse.php?type=flash';
	config.filebrowserUploadUrl = '/finder/upload.php?type=files';
	config.filebrowserImageUploadUrl = '/finder/upload.php?type=images';
	config.filebrowserFlashUploadUrl = '/finder/upload.php?type=flash';

	config.toolbar = 'RastakCMSToolbar';

	config.toolbar_RastakCMSToolbar = [
		['Source', '-', 'Save', 'NewPage', 'Preview', '-', 'Templates'],
		['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Print', 'SpellChecker', 'Scayt'],
		['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'],
		['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
		'/', ['Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript'],
		['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote', 'CreateDiv'],
		['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
		['BidiLtr', 'BidiRtl'],
		['Link', 'Unlink', 'Anchor'],
		['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'],
		'/', ['Styles', 'Format', 'Font', 'FontSize'],
		['TextColor', 'BGColor'],
		['ShowBlocks']
	];

	config.enterMode = CKEDITOR.ENTER_P;
	config.shiftEnterMode = CKEDITOR.ENTER_BR;
	config.tabIndex = 1;

	CKEDITOR.config.keystrokes = [
		[CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/ , 'redo'],
		[CKEDITOR.CTRL + 90 /*Z*/ , 'undo'],
		[CKEDITOR.CTRL + 83 /*S*/ , 'save']
	];

	//config.extraPlugins 	= 'autogrow';
	config.extraPlugins = 'save';
	config.height = '500px';
	config.removePlugins = 'resize';

};