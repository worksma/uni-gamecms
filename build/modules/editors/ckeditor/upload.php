<?php
$protection = 0;
include $_SERVER["DOCUMENT_ROOT"].'/inc/start.php';

if(!is_admin()) {
	exit('Access denied!');
}

if($_FILES['upload']) {
	if (($_FILES['upload'] == "none") OR (empty($_FILES['upload']['name'])) ) {
		$message = "Вы не выбрали файл";
	} else if ($_FILES['upload']["size"] == 0 OR $_FILES['upload']["size"] > 2050000) {
		$message = "Размер файла не соответствует нормам";
	} else if (!if_img($_FILES['upload']['name'])) {
		$message = "Допускается загрузка только картинок JPG и PNG.";
	} else if (!is_uploaded_file($_FILES['upload']["tmp_name"])) {
		$message = "Что-то пошло не так. Попытайтесь загрузить файл ещё раз.";
	} else {
		$file_type = explode(".", $_FILES['upload']['name']);
		$file_type = end($file_type);

		$name = rand(1, 1000).'-'.md5($_FILES['upload']['name']).'.'.$file_type;

		$root_path = $_SERVER["DOCUMENT_ROOT"]."/files/filemanager/admin/".$name;
		$full_path = $full_site_host."files/filemanager/admin/".$name;

		move_uploaded_file($_FILES['upload']['tmp_name'], $root_path);

		$message = "Файл ".$_FILES['upload']['name']." загружен";
		$size = @getimagesize( $root_path );
		if($size[0]<50 OR $size[1]<50){
			unlink( $root_path );
			$message = "Файл не является допустимым изображением";
			$full_path = "";
		}
	}
	$callback = $_REQUEST['CKEditorFuncNum'];
	echo '<script>window.parent.CKEDITOR.tools.callFunction("'.$callback.'", "'.$full_path.'", "'.$message.'" );</script>';
}
exit();
?>