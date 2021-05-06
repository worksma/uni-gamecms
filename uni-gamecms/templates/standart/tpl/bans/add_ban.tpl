<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Инструкция к заполнению
		</div>
		{include file="/bans/instructions.tpl"}
	</div>

	<div class="block">
		<div class="block_head">
			Добавить заявку
		</div>

		<div class="form-group">
			<label for="server">
				<h4>
					Сервер
				</h4>
			</label>
			<select id="server" class="form-control" onchange="local_change_server();">
				{servers}
			</select>
		</div>
		<div id="none" class="disp-n">
			<div class="form-group">
				<label for="nick">
					<h4>
						Ваш ник
					</h4>
				</label>
				<input type="text" class="form-control" id="nick" maxlength="250" autocomplete="off">
			</div>
			<div class="form-group">
				<label for="reason">
					<h4>
						Причина бана
					</h4>
				</label>
				<input type="text" class="form-control" id="reason" maxlength="250" autocomplete="off">
			</div>
		</div>

		<div id="db" class="disp-n">
			<div class="form-group">
				<label for="nick_db">
					<h4>
						Ваш идентификатор
					</h4>
				</label>
				<input type="text" class="form-control" id="nick_db" maxlength="250" placeholder="Введите ник/steam_id/ip">
				<button class="btn btn-outline-primary mt-3" onclick="find_bans();">Найти</button>
				<div id="bans_table" class="table-responsive mb-0 mt-3 disp-n">
					<table class="table table-bordered">
						<thead>
							<tr>
								<td>Действие</td>
								<td>Ник</td>
								<td>Причина</td>
								<td>Дата окончания</td>
							</tr>
						</thead>
						<tbody id="search_ban_res_min">
							<tr>
								<td colspan="10">
									<div class="loader"></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div id="search_ban_res_full" class="mt-3"></div>
			</div>
		</div>

		<div id="dop" class="disp-n">
			<div class="form-group">
				<label for="text">
					<h4>
						Пояснения
					</h4>
				</label><br>
				<textarea id="text" rows="3" maxlength="2000"></textarea>
			</div>
			<div class="form-group">
				<label>
					<h4>
						Скриншоты <small>(максимальное количество: 5)</small>
					</h4>
				</label><br>
				<div id="imgs"></div>
				<div class="input-group">
					<form enctype="multipart/form-data" action="ajax/actions_a.php" method="POST" id="add_img_form">
						<input type="hidden" id="token" name="token" value="{token}">
						<input type="hidden" id="add_ban_img" name="add_ban_img" value="1">
						<input type="hidden" id="phpaction" name="phpaction" value="1">
						<input type="file" id="ban_img" accept="image/*" name="ban_img"/>
						<input type="hidden" id="counter" name="counter" value="0">
						<div id="img_result"></div>
						<input class="btn btn-outline-primary btn-sm" type="submit" value="Загрузить">
					</form>
				</div>
				<input value="" type="hidden" id="screens" autocomplete="off">
			</div>
			<div class="form-group">
				<label for="demo">
					<h4>
						Ссылка на демо <small>(Необязательно)</small>
					</h4>
				</label>
				<input type="text" class="form-control" id="demo" maxlength="250" autocomplete="off">
			</div>

			<div id="new_result" class="mt-10"></div>
			<button id="create_btn" onclick="add_ban();" type="button" class="btn btn-primary mt-3">Создать</button>
		</div>
		{script}
	</div>
</div>
<script>
	$(document).ready(function() {
		init_tinymce("text", "lite", "{file_manager_theme}", "{file_manager}", "{{md5($conf->code)}}");
	});
	$("#add_img_form").submit(function (event){
		NProgress.start();
		event.preventDefault();
		var data = new FormData($('#add_img_form')[0]);
		$.ajax({
			type: "POST",
			url: "../ajax/actions_a.php",
			data: data,
			contentType: false,
			processData: false,
		}).done(function (html) {
			NProgress.done();
			$("#img_result").empty();
			$("#img_result").append(html);
			$('#add_img_form')[0].reset();
		});
	});
	
	function local_change_server() {
		var server_type = $('#server option:selected').attr('data-server-type');
		select_ban_type(server_type);
	}
	local_change_server();
</script>

<div class="col-lg-3 order-is-last">
	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar_secondary.tpl"}
</div>