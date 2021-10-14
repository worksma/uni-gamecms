<?PHP
	if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/inc/configs')):
		if(mkdir($_SERVER['DOCUMENT_ROOT'] . '/inc/configs')):
			exit("Не удалось создать папку: inc/configs, создайте её вручную.");
		endif;
	endif;
	
	if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/robots.txt')):
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/robots.txt', "User-agent: *\nDisallow: /admin/\nHost: {$_SERVER['SERVER_NAME']}\nSitemap: http://{$_SERVER['SERVER_NAME']}/sitemap.xml");
	endif;
	
	require($_SERVER['DOCUMENT_ROOT'] . '/inc/dictionary.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/inc/engine.php');