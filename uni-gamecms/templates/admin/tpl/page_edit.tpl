<div class="page">
<div class="block">
	<div class="block_head">
		Редактирование страницы
	</div>
	<div class="row">
		<div class="col-md-3 form-group">
			<label for="input_title">
				<h4>
					Категория
				</h4>
			</label>
			<select id="input_class" class="form-control">
				{classes}
			</select>
		</div>
		<script>
			function change_privacy() {
				if($('#input_privacy').val() == '1') {
					$("#input_robots option[value=1]").prop('selected', 'false');
					$("#input_robots option[value=2]").prop('selected', 'true');
				}
			}
			function change_robots() {
				if($('#input_robots').val() == '1') {
					$("#input_privacy option[value=1]").prop('selected', 'false');
					$("#input_privacy option[value=2]").prop('selected', 'true');
				}
			}
		</script>
		<div class="col-md-3 form-group">
			<label>
				<h4>
					Приватность
					<small>Страница доступна</small>
				</h4>
			</label><br>
			<select id="input_privacy" class="form-control" onchange="change_privacy();">
				<option value="2" {if('{privacy}' == '2')} selected {/if}>Всем пользователям</option>
				<option value="1" {if('{privacy}' == '1')} selected {/if}>Только авторизованным</option>
			</select>
		</div>
		<div class="col-md-3 form-group">
			<label>
				<h4>
					Индексация
					<small>Страница индексируется поисковиками?</small>
				</h4>
			</label><br>
			<select id="input_robots" class="form-control" onchange="change_robots();">
				<option value="1" {if('{robots}' == '1')} selected {/if}>Индексируется</option>
				<option value="2" {if('{robots}' == '2')} selected {/if}>Не индексируется</option>
			</select>
		</div>
		<div class="col-md-3 form-group">
			<label>
				<h4>
					Активность
					<small>Включить/выключить страницу</small>
				</h4>
			</label><br>
			<select id="input_active" class="form-control">
				<option value="1" {if('{active}' == '1')} selected {/if}>Включить</option>
				<option value="2" {if('{active}' == '2')} selected {/if}>Выключить</option>
			</select>
		</div>
		<div class="col-md-12"><br></div>
		<div class="col-md-5">
			<div class="form-group">
				<label for="input_url">
					<h4>
						Адрес
						<small>Для url (Пример: gamecms.ru/pages/<b>test</b>)</small>
					</h4>
				</label>
				<input type="text" class="form-control" id="input_url" maxlength="30" value="{url}">
			</div>
			<div class="form-group">
				<label for="input_title">
					<h4>
						Заголовок
						<small>Для тега title</small>
					</h4>
				</label>
				<input type="text" class="form-control" id="input_title" maxlength="80" value="{title}">
			</div>
			<div class="form-group">
				<label for="input_description">
					<h4>
						Описание страницы
						<small>Для тега description</small>
					</h4>
				</label>
				<input type="text" class="form-control" id="input_description" maxlength="150" value="{description}">
			</div>
			<div class="form-group">
				<label for="input_keywords">
					<h4>
						Ключевые слова
						<small>Для тега keywords (писать через запятую)</small>
					</h4>
				</label>
				<input type="text" class="form-control" id="input_keywords" maxlength="150" value="{keywords}">
			</div>
		</div>
		<div class="col-md-1"></div>
		<div class="col-md-5 form-group">
			<label for="input_url">
				<h4>
					Изображение
					<small>Старайтесь загружать изображения с небольшим весом.</small>
				</h4>
			</label>
			<div class="col-md-4">
				<img id="img" src="../{image}" class="w-100">
			</div>
			<div class="col-md-8">
				<div class="input-group">
					<form enctype="multipart/form-data" action="ajax/actions_panel.php" method="POST" id="img_form">
						<input type="hidden" name="token" value="{token}">
						<input type="hidden" name="load_page_image_2" value="1">
						<input type="hidden" name="phpaction" value="1">
						<input type="hidden" name="id" value="{id}">
						<input class="input-file w100" type="file" accept="image/*" name="image">
						<input class="btn btn-default mt-5" type="submit" value="Загрузить">
						<div id="img_result"></div>
					</form>
				</div>
			</div>
			<script>
				$("#img_form").submit(function (event){
					NProgress.start();
					event.preventDefault();
					var data = new FormData($('#img_form')[0]);
					$.ajax({
						type: "POST",
						url: "../ajax/actions_panel.php",
						data: data,
						contentType: false,
						processData: false,
					}).done(function (html) {
						$("#img_result").empty();
						$("#img_result").append(html);
						$('#img_form')[0].reset();
					});
					NProgress.done();
				});
			</script>
		</div>

		<div class="col-md-12">
			<br>
			<label for="input_content">
				<h4>
					Контент
					<small>Все содержание страницы</small>
				</h4>
			</label>
			<textarea name="input_content" id="input_content" rows="10" cols="80">
				{content}
			</textarea>
			<script>
				CKEDITOR.replace( 'input_content' );
			</script>

			<div id="edit_page_result" class="mt-10"></div>
			<button onclick="page_edit({id});" type="button" class="btn btn-default">Сохранить</button>
		</div>
	</div>
</div>
</div>