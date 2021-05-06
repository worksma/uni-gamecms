<div class="page">
	<div class="row">
		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					Шаблон движка
				</div>
				<b>Шаблон</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_template(1);">Изменить</button>
					</span>
					<select id="template" class="form-control">
						{templates}
					</select>
				</div>
				<hr>
				<b>Шаблон для мобильного устройства</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_template(2);">Изменить</button>
					</span>
					<select id="template_mobile" class="form-control">
						{templates_mobile}
					</select>
				</div>
				<hr>
				<b>Персональный шаблон</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_template(3);">Задать</button>
					</span>
					<select id="template_prsonal" class="form-control">
						{templates_prsonal}
					</select>
				</div>
				<small class="c-868686">Данный шаблон будет отображаться только Вам.</small>
			</div>
		</div>
		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					Установить шаблон, введя ключ
				</div>

				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" onclick="install_template_by_key();">Установить</button>
					</span>
					<input type="text" class="form-control" id="template_key" maxlength="40" autocomplete="off" placeholder="Введите ключ">
				</div>
				<div id="installing_result"></div>
			</div>

			<div class="block">
				<div class="block_head">
					Кэширование файлов шаблона
				</div>

				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {caching_act}" onclick="change_value('config','caching','1','1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {caching_act2}" onclick="change_value('config','caching','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>

					<button class="btn btn-default" type="button" onclick="fast_admin_action('dell_cache');">Сбросить кэш</button>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3">
			<div class="block">
				<div class="block_head">
					Файлы шаблона {template}
				</div>
				{template_tpls}
			</div>
			<br>
			<div class="block">
				<div class="block_head">
					Стили шаблона {template}
				</div>
				{template_css}
			</div>
		</div>
		<div class="col-md-9">
			<div class="block">
				<div class="block_head">
					<div class="f-l">
						Редактор <span id="name1"></span>
					</div>
					<div class="btn-group btn-group-sm f-r m--5">
						<font id="editor_result"></font>
						<a href="#" title="Отменить действие" class="btn btn-default" onclick="editor.undo();"><span class="glyphicon glyphicon-chevron-left"></span></a>
						<a href="#" title="Вернуть действие" class="btn btn-default"  onclick="editor.redo();"><span class="glyphicon glyphicon-chevron-right"></span></a>
						<a class="btn btn-default disabled" id="save_code">Сохранить</a>
					</div>
				</div>
				<input type="hidden" id="file_name" value="none">
				<div id="editor">
					<textarea id="code"> Выберите шаблон для редактирования. </textarea>
				</div>
				<div class="temp_info">
					<span class="label label-default ">ctrl z (Назад)</span>
					<span class="label label-default">ctrl y (Вперед)</span>
					<span class="label label-default">F11 (Полноэкранный режим); Esc (Выход)</span>
					<span class="label label-default">ctrl x (Вырезать)</span>
					<span class="label label-default">ctrl c (Копировать)</span>
					<span class="label label-default">ctrl v (Вставить)</span>
					<span class="label label-default">ctrl f (Поиск)</span>
					<span class="label label-default">shift ctrl g (Найди след)</span>
					<span class="label label-default">shift ctrl f (Найди пред)</span>
				</div>
				<div id="warning"></div>
			</div>
		</div>
	</div>
	<script>
		var editor = CodeMirror.fromTextArea(document.getElementById('code'), {
			lineNumbers: 'true',
			scrollbarStyle: 'simple',
			mode: 'text/html',
			theme: 'monokai',
			extraKeys: {
				"F11": function(cm) {
					cm.setOption("fullScreen", !cm.getOption("fullScreen"));
				},
				"Esc": function(cm) {
					if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
				}
			}
		});
		editor.on("change", function(){
			on_save_editor_button();
		});
	</script>
	<br>

	<div class="row">
		<div class="col-md-3">
			<div class="block">
				<div class="block_head">
					Изображения шаблона {template}
				</div>
				
				{if('{have_images}' == '1')}
				<ol class="tree">
					<li class="toggle">
						images
						<input type="checkbox">
						<ol class="tree">
							{template_images}
						</ol>
					</li>
				</ol>
				{/if}
				{template_imgs}

				<ol class="tree" id="data3">
					<li class="file">
						<a onclick="upload_template_img('templates/{template}/img/', 'data3');" alt="Загрузить свое изображение" class="c-p">
							Загрузить свое изображение
						</a>
					</li>
				</ol>
			</div>
			<div class="block">
				<div class="block_head">
					Другие изображения
				</div>
				<ol class="tree">
					<li class="toggle">
						avatars
						<input type="checkbox">
						<ol class="tree">
							<li class="file" id="data4">
								<a onclick="upload_template_img('files/avatars/', 'data4');" alt="Загрузить свое изображение" class="c-p">
									Загрузить свое изображение
								</a>
							</li>
							{engine_avatars}
						</ol>
					</li>
				</ol>
				<ol class="tree">
					<li class="toggle">
						forums_imgs
						<input type="checkbox">
						<ol class="tree">
							<li class="file" id="data5">
								<a onclick="upload_template_img('files/forums_imgs/', 'data5');" alt="Загрузить свое изображение" class="c-p">
									Загрузить свое изображение
								</a>
							</li>
							{engine_forums_imgs}
						</ol>
					</li>
				</ol>
				<ol class="tree">
					<li class="toggle">
						maps_imgs
						<input type="checkbox">
						<ol class="tree">
							<li class="file" id="data6">
								<a onclick="upload_template_img('files/maps_imgs/', 'data6');" alt="Загрузить свое изображение" class="c-p">
									Загрузить свое изображение
								</a>
							</li>
							{engine_maps_imgs}
						</ol>
					</li>
				</ol>
				<ol class="tree">
					<li class="toggle">
						news_imgs
						<input type="checkbox">
						<ol class="tree">
							<li class="file" id="data7">
								<a onclick="upload_template_img('files/news_imgs/', 'data7');" alt="Загрузить свое изображение" class="c-p">
									Загрузить свое изображение
								</a>
							</li>
							{engine_news_imgs}
						</ol>
					</li>
				</ol>
			</div>
		</div>
		<div class="col-md-9">
			<div class="block" id="image_block">
				<div class="block_head">
					<div class="f-l">
						Просмотр <span id="name2"></span>
					</div>
				</div>
				<div id="img">
					Выберите изображение
				</div>
				<form enctype="multipart/form-data" action="ajax/actions_a.php" method="POST" id="img_send" class="disp-n mt-10">
					<input type="hidden" name="token" value="{token}">
					<input type="hidden" name="img_name" id="img_name" value="">
					<input type="hidden" name="replace_tpl_img" value="1">
					<input type="hidden" name="phpaction" value="1">
					<input type="hidden" name="data" id="data" value="">
					<input type="hidden" name="folder" id="folder" value="">
					<input class="input-file" type="file" name="tpl_img" accept="image/*"/>
					<input id="btn" class="btn btn-default mt-5" type="submit" value="">
					<img class="disp-n" id="loader" src="{site_host}templates/admin/img/loader.gif">
					<div id="sending_result"></div>
				</form>
			</div>
		</div>
	</div>
	<script>
		$("#img_send").submit(function (event){
			event.preventDefault();
			var data = new FormData($('#img_send')[0]);
			$.ajax({
				type: "POST",
				url: "../ajax/actions_panel.php",
				data: data,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$('#loader').show();
				}
			}).done(function (html) {
				$("#sending_result").empty();
				$("#sending_result").append(html);
				$('#loader').hide();
				$('#img_send')[0].reset();
			});
		});

		var img_block = document.getElementById('image_block');
		var width = img_block.clientWidth;
		var img_block_bottom = img_block.getBoundingClientRect().bottom + window.pageYOffset;

		window.onscroll = function() {
			if (img_block.classList.contains('fixed') && window.pageYOffset < img_block_bottom) {
				img_block.classList.remove('fixed');
				img_block.style.width = 'auto';
			} else if (window.pageYOffset > img_block_bottom) {
				img_block.classList.add('fixed');
				img_block.style.width = width+"px";
			}
		};
	</script>
</div>
