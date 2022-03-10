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
					<table class="table playground">
						<thead>
							<tr>
								<td class="number">#</td>
								<td class="name">Наименование</td>
								<td class="price">Стоимость</td>
								<td class="availability">Наличие</td>
								<td class="actions">Действия</td>
							</tr>
						</thead>
						<tbody id="product">
							<tr>
								<td colspan="7">
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
							<a target="_blank" href="https://worksma.ru/forum/threads/Работа-с-исполнительным-файлов-Торговой-площадки.172/">
								<span class="glyphicon glyphicon-link"></span> Подробнее о исполнительном файле
							</a>
						</p>
					</div>
					
					<label for="resource" class="mb-0 mt-4">Ресурс</label>
					<input id="resource" type="file" min="0" class="form-control">
					
					<button class="btn btn-default btn-block mt-4" onclick="add_product();">Добавить</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<div class="modal-title">Настройка RCON команд</div>
			</div>
			<div class="modal-body p_rcon">
				<div class="block-text">
					Добавление команды
				</div>
				
				<form id="form_rcon_add" class="row">
					<input type="hidden" name="pid">
					<div class="col-lg-4">
						<select name="server" class="form-control">
							{servers}
						</select>
					</div>
					<div class="col-lg-6">
						<input name="command" type="text" class="form-control" placeholder="Консольная команда" autocomplete="off">
					</div>
					<div class="col-lg-2">
						<button type="submit" class="btn btn-primary">Добавить</button>
					</div>
					
					<div class="col-md-12">
						<div class="bs-callout bs-callout-info mt-5" style="white-space: pre-line;">Для работы опции, должна быть включена функция RCON у <a href="/admin/servers">сервера</a>.
						
						<b>Параметры:</b>
						<b>{uid}</b> - отправка ID профиля покупателя.
						<b>{id}</b> - индекс купленного товара.
						<b>{price}</b> - цена, за которую был куплен товар
						<b>{steamid}</b> - отправит из профиля, если нет то придет 0.
						</div>
					</div>
				</form>
				
				<div class="block-text">
					Имеющиеся команды
				</div>
					
				<div class="f-table">
					<table class="table table-responsive">
						<tbody id="rcon_list"></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="{site_host}templates/admin/js/tabs.js"></script>
<script>
	$(function() {
		load_category();
		load_opt_category();
		load_product();
		load_sels_product();
		
		[].slice.call(document.querySelectorAll('.tabs')).forEach(function(e) {
			new CBPFWTabs(e);
		});
	});
</script>