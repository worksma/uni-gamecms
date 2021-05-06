<?PHP
	if(!file_exists("{$_SERVER['DOCUMENT_ROOT']}/robots.txt")) {
		file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/robots.txt", "User-agent: *
Disallow: /admin/
Host: {$_SERVER['SERVER_NAME']}
Sitemap: http://{$_SERVER['SERVER_NAME']}/sitemap.xml");
	}
	
	@include("{$_SERVER['DOCUMENT_ROOT']}/inc/dictionary.php");
	@include("{$_SERVER['DOCUMENT_ROOT']}/inc/engine.php");