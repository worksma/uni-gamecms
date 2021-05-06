<head>
	<meta charset="UTF-8">
	<title>{title}</title>

	<meta name="robots" content="none">
	<meta name="author" content="worksma.ru">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<meta name="dc.title" content="{title}">
	<meta name="dc.rights" content="Copyright 2021, WORKSMA. Все права сохранены.">
	<meta name="dc.creator" content="worksma.ru">
	<meta name="dc.language" content="RU">

	<link rel="stylesheet" href="{site_host}templates/admin/css/primary.css?v={cache}">
	<link rel="shortcut icon" href="{site_host}templates/admin/img/favicon.ico?v={cache}">
	<link rel="image_src" href="{image}">

	<script src="{site_host}templates/admin/js/jquery.js?v={cache}"></script>
	<script src="{site_host}templates/admin/js/nprogress.js?v={cache}"></script>
	<script src="{site_host}templates/admin/js/secondary.js?v={cache}"></script>
	<script src="{site_host}templates/admin/js/bootstrap.js?v={cache}"></script>
	<script src="{site_host}ajax/ajax-admin.js?v={cache}"></script>

	{other}
</head>
<body>
	<input id="token" type="hidden" value="{token}">
	<div id="global_result">
		<span class="glyphicon glyphicon-ok result_ok disp-n"></span>
		<span class="glyphicon glyphicon-remove result_error disp-n"></span>
		<span class="glyphicon glyphicon-ok result_ok_b disp-n"></span>
		<span class="glyphicon glyphicon-remove result_error_b disp-n"></span>
	</div>