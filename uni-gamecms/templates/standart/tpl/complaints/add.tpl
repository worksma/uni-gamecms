<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Инструкция к заполнению
		</div>

		<p>Выберите игрока, на которого вы хотите подать жалобу, подробно опишите жалобу, приложите в качестве доказательств скриншоты и демо запись</p>
	</div>

	<div class="block">
		<div class="block_head">
			Добавить жалобу
		</div>
		
		<div class="form-group">
			<label for="server">
				<h4>Сервер</h4>
			</label>
			<select id="server_id" class="form-control">
                {servers}
			</select>
		</div>

		<div class="form-group">
			<label for="accused">
				<h4>Индентификатор обвиняемого</h4>
			</label>

			<input type="text" class="form-control" id="accused" maxlength="256" placeholder="Введите ник / steam_id / логин на сайте">
			<button class="btn btn-outline-primary mt-3" onclick="findTheAccused()">Найти</button>

			<div id="find-result-table" class="table-responsive mb-0 mt-3" style="display: none">
				<table class="table table-bordered">
					<thead>
						<tr>
							<td>Действие</td>
							<td>Профиль</td>
							<td>Индентификатор</td>
							<td>Услуги</td>
						</tr>
					</thead>
					<tbody id="find-result">
						<tr>
							<td colspan="10">
								<div class="loader"></div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div id="accused-info" style="display: none;"></div>

		<div id="additional-info" style="display: none">
			<div class="form-group mt-3">
				<label for="description">
					<h4>Пояснения</h4>
				</label>
				<br>

				<textarea id="description" rows="3" maxlength="2000"></textarea>
			</div>

			<div class="form-group">
				<label>
					<h4>Скриншоты <small>(максимальное количество: 5)</small></h4>
				</label>
				<br>

				<div id="imgs"></div>

				<div class="input-group mt-3">
					<input type="file" id="image" accept="image/*">
					<button class="btn btn-outline-primary" type="button" onclick="loadImages('complaints');">Загрузить</button>
				</div>
				<div id="load-image-result"></div>

				<input type="hidden" id="counter" value="0">
				<input type="hidden" id="images-load-result-value" value="" autocomplete="off">
			</div>

			<div class="form-group">
				<label for="demo">
					<h4>Ссылка на демо <small>(Необязательно)</small></h4>
				</label>
				<input type="text" class="form-control" id="demo" maxlength="250" autocomplete="off">
			</div>

			<div id="result" class="mt-10"></div>
			<button onclick="addComplaint(this);" type="button" class="btn btn-primary mt-3">Создать</button>
		</div>
	</div>
</div>
<script>
  $(document).ready(function () {
    init_tinymce('description', 'lite', "{file_manager_theme}", "{file_manager}", "{{md5($conf->code)}}");
  });
</script>

<div class="col-lg-3 order-is-last">
    {include file="/home/navigation.tpl"}
    {include file="/home/sidebar_secondary.tpl"}
</div>