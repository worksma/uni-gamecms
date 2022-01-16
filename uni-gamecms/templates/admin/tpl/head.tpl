<head>
	<meta charset="UTF-8">
	<title>{title}</title>

	<meta name="robots" content="none">
	<meta name="author" content="worksma.ru">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" href="{site_host}templates/admin/css/primary.css?v={cache}">
	<link rel="stylesheet" href="{site_host}files/toasts/toasty.min.css">
	<link rel="shortcut icon" href="{site_host}templates/admin/img/favicon.ico?v={cache}">
	<link rel="image_src" href="{image}">

	<script src="{site_host}templates/admin/js/jquery.js?v={cache}"></script>
	<script src="{site_host}templates/admin/js/nprogress.js?v={cache}"></script>
	<script src="{site_host}templates/admin/js/secondary.js?v={cache}"></script>
	<script src="{site_host}templates/admin/js/bootstrap.js?v={cache}"></script>
	<script src="{site_host}ajax/performers/functions.min.js?v={cache}"></script>
	<script src="{site_host}ajax/performers/acp.min.js?v={cache}"></script>

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