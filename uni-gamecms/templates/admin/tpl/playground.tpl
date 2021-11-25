<style>
	.mt-4 {
		margin-top: 0.4rem;
	}
</style>
<div class="page">
	<div class="row">
		<?
			if((int)ini_get("upload_max_filesize") < 100):
				$danger_filesize = true;
			endif;

			$sth = pdo()->query("SELECT * FROM `playground__category` WHERE 1");
			if($sth->rowCount()):
				while($row = $sth->fetch(PDO::FETCH_OBJ)):
					if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/files/playground/' . $row->code_name)):
						$danger_directions .= '/files/playground/' . $row->code_name . '<br>';
					endif;
				endwhile;
			endif;

			if(isset($danger_filesize) || isset($danger_directions)):
		?>
		<div class="col-md-12">
			<div class="bs-callout bs-callout-error">
				<h5>Важно!</h5>
				<p>
					<?if(isset($danger_filesize)):?>
						У Вас установлено <?=(int)ini_get("upload_max_filesize");?> мегабайт в настройках PHP (upload_max_filesize). Рекомендуем установить данное значение не менее 100М.<br><br>
					<?endif;?>

					<?if(isset($danger_directions)):?>
						Отсутствуют следующие дирекции:<br><?print_r($danger_directions);?>
					<?endif;?>
				</p>
			</div><br>
		</div>
		<?endif;?>

		<div class="col-md-8">
			<div class="block">
				<div class="block_head">Действующие товары</div>
				<div>
					<table class="table">
						<thead>
							<tr>
								<td>#</td>
								<td>Наименование</td>
								<td>Стоимость</td>
								<td>Категория</td>
								<td>Ресурс</td>
								<td>Действия</td>
							</tr>
						</thead>
						<tbody id="product">
							<tr>
								<td colspan="5">
									<center>
										<img src="{site_host}templates/admin/img/loader.gif" alt="Загрузка..">
									</center>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="block">
				<div class="block_head">Конфигурации</div>
				<label class="mb-0">Наименование валюты</label>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_currency();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="currency" maxlength="32" autocomplete="off" value="{currency}">
				</div>
				
				<label class="mb-0 mt-4">Курс валюты</label>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_course();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="course" maxlength="32" autocomplete="off" value="{course}">
				</div>

				<label class="mb-0 mt-4">Секретный ключ</label>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_secret();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="secret" maxlength="255" autocomplete="off" value="{secret}">
				</div>

				<label class="mb-0 mt-4">Сколько выводить товаров на страницу?</label>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_limit_product();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="limit_product" maxlength="255" autocomplete="off" value="{limit_product}">
				</div>

				<label class="mb-0 mt-4">Бонус при пополнение баланса</label>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_bonuses();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="bonuses" maxlength="9" autocomplete="off" value="{bonuses}" placeholder="В процентах">
				</div>
			</div>
		
			<div class="block">
				<div class="block_head">Управление категориями</div>
				<section>
					<div class="tabs tabs-style-topline">
						<nav>
							<ul>
								<li>
									<a href="#section-topline-1">
										<span>Добавление</span>
									</a>
								</li>
								<li>
									<a href="#section-topline-2">
										<span>Удаление</span>
									</a>
								</li>
							</ul>
							<div class="content-wrap">
								<section id="section-topline-1">
									<div class="row">
										<div class="form-row">
											<div class="form-group col-md-6">
												<label for="name_category">Наименование</label>
												<input id="name_category" type="text" class="form-control" placeholder="Введите название..">
											</div>
											<div class="form-group col-md-6">
												<label for="code_category">Кодовое имя</label>
												<input id="code_category" type="text" class="form-control" placeholder="Введите название..">
											</div>
										</div>
									</div>
								
									<button class="btn btn-default btn-block mt-4" onclick="add_category();">Добавить</button>
								</section>
								<section id="section-topline-2">
									<label for="id_category" class="mb-0">Категория</label>
									<select class="form-control" id="id_category">
										<option>- выбрать -</option>
									</select>
									
									<button class="btn btn-default btn-block mt-4" onclick="remove_category();">Удалить</button>
								</section>
							</div>
						</nav>
					</div>
				</section>
			</div>
		
			<div class="block">
				<div class="block_head">Добавить товар</div>
				<div>
					<label for="name" class="mb-0">Наименование</label>
					<input id="name" type="text" class="form-control" placeholder="Введите название..">
					
					<label for="category" class="mb-0 mt-4">Категория</label>
					<select class="form-control" id="category">
						<option>- выбрать -</option>
					</select>
					
					<label for="price" class="mb-0 mt-4">Стоимость</label>
					<input id="price" type="number" min="0" class="form-control" value="10">
					
					<label for="executor" class="mb-0 mt-4">Исполнительный файл</label>
					<input id="executor" type="text" class="form-control" placeholder="Ссылка или патч файл">
					<div class="bs-callout bs-callout-info mt-10">
						<p>
							<a target="_blank" href="https://worksma.ru/wiki/executive-file">
								<span class="glyphicon glyphicon-link"></span> Подробнее о исполнительном файле
							</a>
						</p>
					</div>
					
					<label for="resource" class="mb-0 mt-4">Ресурс</label>
					<input id="resource" type="file" min="0" class="form-control">
					
					<button class="btn btn-default btn-block mt-4" onclick="add_product();">Добавить</button>
				</div>
			</div>
			
			<div class="block">
				<div class="block_head">Добавление продаж</div>
				<section>
					<div class="tabs tabs-style-topline">
						<nav>
							<ul>
								<li>
									<a href="#section-topline-3">
										<span>Добавление</span>
									</a>
								</li>
								<li>
									<a href="#section-topline-4">
										<span>Удаление</span>
									</a>
								</li>
							</ul>
							<div class="content-wrap">
								<section id="section-topline-3">
									<div class="row">
										<div class="form-row">
											<div class="form-group col-md-6">
												<label for="sels_id">Продукт</label>
												<select class="form-control" id="sels_id"></select>
											</div>
											<div class="form-group col-md-6">
												<label for="sels_count">Количество</label>
												<input id="sels_count" type="number" class="form-control" value="10">
											</div>
										</div>
									</div>
								
									<button class="btn btn-default btn-block mt-4" onclick="add_sels();">Добавить</button>
								</section>
								<section id="section-topline-4">
									<div class="row">
										<div class="form-row">
											<div class="form-group col-md-6">
												<label for="remove_id">Продукт</label>
												<select class="form-control" id="remove_id"></select>
											</div>
											<div class="form-group col-md-6">
												<label for="remove_count">Количество</label>
												<input id="remove_count" type="number" class="form-control" value="10">
											</div>
										</div>
									</div>
									
									<button class="btn btn-default btn-block mt-4" onclick="remove_sels();">Удалить</button>
								</section>
							</div>
						</nav>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
<script>
	document.addEventListener("DOMContentLoaded", load_category);
	document.addEventListener("DOMContentLoaded", load_opt_category);
	document.addEventListener("DOMContentLoaded", load_product);
	document.addEventListener("DOMContentLoaded", load_sels_product);
</script>
<script src="{site_host}templates/admin/js/tabs.js"></script>
<script>
	(function() { [].slice.call( document.querySelectorAll( '.tabs' ) ).forEach( function( el ) { new CBPFWTabs( el ); }); })();
</script>
<link href="{site_host}files/toasts/toasty.min.css?v={cache}" rel="stylesheet">
<script src="{site_host}files/toasts/toasty.min.js?v={cache}" type="text/javascript"></script>