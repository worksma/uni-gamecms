<?php
	/*
		Торговая площадка https://worksma.ru
		Разработано командой WORKSMA.
	*/
	$version = PHP_VERSION;
	if (intval($version)<7)
	    exit('[У Вас установлена PHP ' . $version . ', для работы системы требуется 7.0 и выше.]');

	require(__DIR__ . "/inc/initiator.php");