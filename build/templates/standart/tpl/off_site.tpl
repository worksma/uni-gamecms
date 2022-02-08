{sql select($off_data, $pdo, 'off_message', 'config__secondary', 'null', 0)}
<head>
	<meta charset="UTF-8">
	<title>{{$conf->name}}</title>
	<style>
		html {
			height: 100%;
			background: rgb(221, 221, 221) none repeat scroll 0% 0%;
		}
		h1 {
			margin-top: 20%;
			text-align: center;
			font-family: arial;
			font-size: 35px;
		}
	</style>
</head>
<body>
	<h1>{{$off_data[0]['off_message']}}</h1>