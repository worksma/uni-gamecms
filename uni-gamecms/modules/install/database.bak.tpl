<?PHP
	$db["host"] = "{hostname}";
	$db["name"] = "{database}";
	$db["user"] = "{username}";
	$db["password"] = "{password}";
	
	try {
		$pdo = new PDO("mysql:host=".$db["host"].";dbname=".$db["name"], $db["user"], $db["password"]);
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$pdo->exec("set names utf8"); 
	}
	catch(PDOException $e) {
		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/logs/pdo_errors.txt", "[".date("Y-m-d H:i:s")."] : [".$e->getMessage()."]\r\n", FILE_APPEND);
		exit("Ошибка подключения к базе данных.");
	}
	
	unset($db);
	
	$STH = $pdo->query("SELECT * FROM config LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$conf = $STH->fetch();