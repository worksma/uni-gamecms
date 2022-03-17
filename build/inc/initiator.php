<?PHP
	if(!file_exists(__DIR__ . "/configs")):
		if(!mkdir(__DIR__ . "/configs")):
			exit("Не удалось создать папку: inc/configs, создайте её вручную.");
		endif;
	endif;

	if(!file_exists("./robots.txt")):
		file_put_contents("./robots.txt", "User-agent: *\nDisallow: /admin/\nHost: {$_SERVER['SERVER_NAME']}\nSitemap: https://{$_SERVER['SERVER_NAME']}/sitemap.xml");
	endif;

	require_once(__DIR__ . "/dictionary.php");
	require_once(__DIR__ . "/engine.php");