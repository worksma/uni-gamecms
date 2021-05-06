<section>
	<div class="tabs tabs-style-topline">
		<nav>
			<ul>
				<li><a href="#section-topline-1"><span>Страницы движка</span></a></li>
				<li onclick="load_pages(2);"><a href="#section-topline-2"><span>Пользовательские страницы</span></a></li>
				<li><a href="#section-topline-3"><span>Добавить страницу</span></a></li>
				<li onclick="load_classes(2);"><a href="#section-topline-4"><span>Категории</span></a></li>
			</ul>
		</nav>
		<div class="content-wrap">
			<section id="section-topline-1">
				<div class="content">
					<div class="table-responsive mb-0">
						<table class="pages_table table table-bordered">
							<thead>
								<tr>
									<td>Заголовок</td>
									<td>Описание</td>
									<td>Ключевые слова</td>
									<td>Тип</td>
									<td>Миниатюра</td>
									<td>Индексация</td>
									<td>Приватность</td>
									<td>Активность</td>
									<td>Действие</td>
								</tr>
							</thead>
							<tbody id="engine_pages">
								<tr><td colspan="10"><center><img src="{site_host}templates/admin/img/loader.gif"></center></td></tr>
								<script>load_pages(1);</script>
							</tbody>
						</table>						
					</div>
				</div>
			</section>
			<section id="section-topline-2">
				<div class="content">
					<div class="table-responsive mb-0">
						<table class="pages_table table table-bordered">
							<thead>
								<tr>
									<td>Название</td>
									<td>Индексация</td>
									<td>Приватность</td>
									<td>Активность</td>
									<td>Категория</td>
									<td>Миниатюра</td>
									<td>Действие</td>
								</tr>
							</thead>
							<tbody id="user_pages">
								<tr><td colspan="10"><center><img src="{site_host}templates/admin/img/loader.gif"></center></td></tr>
							</tbody>
						</table>						
					</div>
				</div>
			</section>
			<section id="section-topline-3">
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
							<option value="2">Всем пользователям</option>
							<option value="1">Только авторизованным</option>
						</select>
					</div>
					<div class="col-md-3 form-group">
						<label>
							<h4>
								Индексация
								<small>Индексировать поисковиками?</small>
							</h4>
						</label><br>
						<select id="input_robots" class="form-control" onchange="change_robots();">
							<option value="1">Индексируется</option>
							<option value="2">Не индексируется</option>
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
							<option value="1">Включить</option>
							<option value="2">Выключить</option>
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
							<input type="text" class="form-control" id="input_url" maxlength="30">
						</div>
						<div class="form-group">
							<label for="input_title">
								<h4>
									Заголовок
									<small>Для тега title</small>
								</h4>
							</label>
							<input type="text" class="form-control" id="input_title" maxlength="80">
						</div>
						<div class="form-group">
							<label for="input_description">
								<h4>
									Описание страницы
									<small>Для тега description</small>
								</h4>
							</label>
							<input type="text" class="form-control" id="input_description" maxlength="150">
						</div>
						<div class="form-group">
							<label for="input_keywords">
								<h4>
									Ключевые слова
									<small>Для тега keywords (писать через запятую)</small>
								</h4>
							</label>
							<input type="text" class="form-control" id="input_keywords" maxlength="150">
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
							<img id="img" src="../files/miniatures/standart.jpg" class="w-100">
						</div>
						<div class="col-md-8">
							<div class="input-group">
								<form enctype="multipart/form-data" action="ajax/actions_panel.php" method="POST" id="img_form">
									<input type="hidden" name="token" value="{token}">
									<input type="hidden" name="load_page_image_2" value="1">
									<input type="hidden" name="phpaction" value="1">
									<input class="input-file w100" type="file" accept="image/*" name="image">
									<input class="btn btn-default mt-5" type="submit" value="Загрузить">
									<div id="img_result"></div>
								</form>
							</div>
							<input value="files/miniatures/standart.jpg" type="hidden" id="input_image" maxlength="255">
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
				</div>
				<div class="col-md-12"><br></div>
				<div class="form-group">
					<label for="input_content">
						<h4>
							Контент
							<small>Все содержание страницы</small>
						</h4>
					</label>
					<textarea name="input_content" id="input_content" rows="10" cols="80"></textarea>
					<script> CKEDITOR.replace( 'input_content' ); </script>
				</div>

				<div id="create_page_result" class="mt-10"></div>
				<button onclick="create_page();" type="button" class="btn btn-default">Сохранить</button>
			</section>
			<section id="section-topline-4">
				<div class="panel panel-default">
					<div class="panel-heading">Добавить категорию</div>
					<div class="panel-body">
						<div class="input-group">
							<span class="input-group-btn">
								<button class="btn btn-default" type="button" onclick="add_class(2);">Добавить</button>
							</span>
							<input type="text" class="form-control" id="class_name" maxlength="20" value="">
						</div>
					</div>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading">Категории</div>
					<div class="panel-body" id="classes">
						<center><img src="{site_host}templates/admin/img/loader.gif"></center>
					</div>
				</div>
			</section>
		</div>
	</div>
</section>

<script src="{site_host}templates/admin/js/tabs.js"></script>
<script>
	(function() { [].slice.call( document.querySelectorAll( '.tabs' ) ).forEach( function( el ) { new CBPFWTabs( el ); }); })();
</script>