<?PHP
	$version = PHP_VERSION;
	
	if($version[0] <= 7):
		if($version[0] == 7):
			if($version[2] < 4):
				exit('[У Вас установлена PHP ' . $version . ', для работы системы требуется 7.4 и выше.]');
			endif;
		else:
			exit('[У Вас установлена PHP ' . $version . ', для работы системы требуется 7.4 и выше.]');
		endif;
	endif;

	require("inc/engine.php");