<div class="page">
	<script>
		var link = document.createElement('link');
		link.rel = 'stylesheet';
		link.type = 'text/css';
		link.href = '../modules/editors/tinymce/skins/lightgray/skin.min.css';
		document.getElementsByTagName('head')[0].appendChild(link);
	</script>
	<div class="col-md-12">
		<div class="block">
			<div class="block_head">Настройка форума</div>
			<b>Переподсчет сообщений и спасибок у пользователей</b><br>
			<button class="btn btn-default" type="button" onclick="recount();">Выполнить</button>
			<div id="recount_result"></div>
		</div>
		<form id="forum_settings">
			<div class="block">
				<div class="block_head">
					Настройка файлового менеджера
				</div>
				<div class="row">
					<div class="col-md-4">
						<b>Тема редактора</b>
						<select class="form-control" name="file_manager_theme" id="file_manager_theme">
							<option value="1" {fmt_act1}>Светлая</option>
							<option value="2" {fmt_act2}>Темная</option>
						</select>
						<br>
						<b>Файловый менеджер</b>
						<select class="form-control" name="file_manager" id="file_manager">
							<option value="1" {fm_act1}>Включить</option>
							<option value="2" {fm_act2}>Выключить</option>
						</select>
						<br>
						<b>Максимально допустимый размер загружаемого файла</b>
						<input class="form-control" name="file_max_size" id="file_max_size" type="number" maxlength="5" value="{file_max_size}" placeholder="В мегабайтах">
					</div>
					<div class="col-md-4">
						<b>Разрешенные расширения изображений</b>
						<input class="form-control" name="ext_img" id="ext_img" type="text" maxlength="255" value="{ext_img}" placeholder="Через пробел">
						<br>
						<b>Разрешенные расширения аудио файлов</b>
						<input class="form-control" name="ext_music" id="ext_music" type="text" maxlength="255" value="{ext_music}" placeholder="Через пробел">
						<br>
						<b>Разрешенные расширения видео файлов</b>
						<input class="form-control" name="ext_video" id="ext_video" type="text" maxlength="255" value="{ext_video}" placeholder="Через пробел">
						</div>
					<div class="col-md-4">
						<b>Разрешенные расширения документов</b>
						<input class="form-control" name="ext_file" id="ext_file" type="text" maxlength="255" value="{ext_file}" placeholder="Через пробел">
						<br>
						<b>Разрешенные расширения архивов</b>
						<input class="form-control" name="ext_misc" id="ext_misc" type="text" maxlength="255" value="{ext_misc}" placeholder="Через пробел">
					</div>
				</div>

				<button class="btn btn-default mt-10" type="button" onclick="save_forum_settings();">Сохранить</button>
			</div>
			<!--
			<div class="block">
				<div class="block_head">
					Настройка рекдатора сообщений
				</div>
				<b>Включение/Выключение модулей редактора</b>

				<div class="btn-toolbar editor_buttons_settings" data-toggle="buttons">
					<label class="btn btn-default btn-sm" tooltip="yes" title="Вернуть">
						<input type="checkbox" autocomplete="off" name="lists">Списки
					</label>
				</div>

				<b>Включение/Выключение кнопок редактора</b>
				<div class="btn-toolbar editor_buttons_settings" data-toggle="buttons">
					<label class="btn btn-default btn-sm active" tooltip="yes" title="Вернуть">
						<input type="checkbox" autocomplete="off" name="undo" checked><i class="mce-ico mce-i-undo"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Отменить">
						<input type="checkbox" autocomplete="off" name="redo"><i class="mce-ico mce-i-redo"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Очистить формат">
						<input type="checkbox" autocomplete="off" name="removeformat"><i class="mce-ico mce-i-removeformat"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Полужирный">
						<input type="checkbox" autocomplete="off" name="bold"><i class="mce-ico mce-i-bold"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Курсив">
						<input type="checkbox" autocomplete="off" name="italic"><i class="mce-ico mce-i-italic"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Подчеркнутый">
						<input type="checkbox" autocomplete="off" name="underline"><i class="mce-ico mce-i-underline"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Зачеркнутый">
						<input type="checkbox" autocomplete="off" name="strikethrough"><i class="mce-ico mce-i-strikethrough"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="По левуму краю">
						<input type="checkbox" autocomplete="off" name="alignleft"><i class="mce-ico mce-i-alignleft"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="По центру">
						<input type="checkbox" autocomplete="off" name="aligncenter"><i class="mce-ico mce-i-aligncenter"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="По правому краю">
						<input type="checkbox" autocomplete="off" name="alignright"><i class="mce-ico mce-i-alignright"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="По ширине">
						<input type="checkbox" autocomplete="off" name="alignjustify"><i class="mce-ico mce-i-alignjustify"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Размер шрифта">
						<input type="checkbox" autocomplete="off" name="fontsizeselect">Размер шрифта
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Поиск и замена">
						<input type="checkbox" autocomplete="off" name="searchreplace"><i class="mce-ico mce-i-searchreplace"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Маркированный список">
						<input type="checkbox" autocomplete="off" name="bullist"><i class="mce-ico mce-i-bullist"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Нумерованный список">
						<input type="checkbox" autocomplete="off" name="numlist"><i class="mce-ico mce-i-numlist"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Уменьшить отступ">
						<input type="checkbox" autocomplete="off" name="outdent"><i class="mce-ico mce-i-outdent"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Увиличить отступ">
						<input type="checkbox" autocomplete="off" name="indent"><i class="mce-ico mce-i-indent"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Цитата">
						<input type="checkbox" autocomplete="off" name="blockquote"><i class="mce-ico mce-i-blockquote"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Вставить/редактировать ссылку">
						<input type="checkbox" autocomplete="off" name="link"><i class="mce-ico mce-i-link"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Удалить ссылку">
						<input type="checkbox" autocomplete="off" name="unlink"><i class="mce-ico mce-i-unlink"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Якорь">
						<input type="checkbox" autocomplete="off" name="anchor"><i class="mce-ico mce-i-anchor"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Вставить/редактировать изображение">
						<input type="checkbox" autocomplete="off" name="image"><i class="mce-ico mce-i-image"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Вставить/редактировать видео">
						<input type="checkbox" autocomplete="off" name="media"><i class="mce-ico mce-i-media"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Файловый менеджер">
						<input type="checkbox" autocomplete="off" name="browse"><i class="mce-ico mce-i-browse"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Добавить/изменить пример кода">
						<input type="checkbox" autocomplete="off" name="codesample"><i class="mce-ico mce-i-codesample"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Добавить спойлер">
						<input type="checkbox" autocomplete="off" name="spoiler-add"><i class="mce-ico mce-i-code"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Вставить дату/время">
						<input type="checkbox" autocomplete="off" name="insertdatetime"><i class="mce-ico mce-i-insertdatetime"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Цвет текста">
						<input type="checkbox" autocomplete="off" name="forecolor"><i class="mce-ico mce-i-forecolor"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Цвет фона">
						<input type="checkbox" autocomplete="off" name="backcolor"><i class="mce-ico mce-i-backcolor"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Горизонтальная линия">
						<input type="checkbox" autocomplete="off" name="hr"><i class="mce-ico mce-i-hr"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Нижний индекс">
						<input type="checkbox" autocomplete="off" name="subscript"><i class="mce-ico mce-i-subscript"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Верхний индекс">
						<input type="checkbox" autocomplete="off" name="superscript"><i class="mce-ico mce-i-superscript"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Специальные символы">
						<input type="checkbox" autocomplete="off" name="charmap"><i class="mce-ico mce-i-charmap"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Полноэкранный режим">
						<input type="checkbox" autocomplete="off" name="fullscreen"><i class="mce-ico mce-i-fullscreen"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Направление слева направо">
						<input type="checkbox" autocomplete="off" name="ltr"><i class="mce-ico mce-i-ltr"></i>
					</label>
					<label class="btn btn-default btn-sm" tooltip="yes" title="Направление справа налево">
						<input type="checkbox" autocomplete="off" name="rtl"><i class="mce-ico mce-i-rtl"></i>
					</label>
				</div>
			</div>
			-->
		</form>
	</div>
</div>