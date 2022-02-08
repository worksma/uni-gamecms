/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

var url = "//"+location.host;
 
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	config.language = 'ru';
	config.extraPlugins = 'spoiler';
	// config.uiColor = '#AADC6E';
	config.filebrowserUploadUrl = url + '/modules/editors/ckeditor/upload.php';
	    config.protectedSource.push(/<(script)[^>]*>.*<\/script>/ig);// разрешить теги <script>
    config.protectedSource.push(/<\?[\s\S]*?\?>/g);// разрешить php-код
    config.protectedSource.push(/<!--dev-->[\s\S]*<!--\/dev-->/g);
    config.allowedContent = true; /* все теги */
};

if ( !CKEDITOR.env.ie || CKEDITOR.env.version > 7 ) {
	CKEDITOR.env.isCompatible = true;
}