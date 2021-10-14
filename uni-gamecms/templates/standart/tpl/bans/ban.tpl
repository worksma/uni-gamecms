<div class="col-lg-6">
	<div class="block ban-application ban-information">
		<div class="block_head">
			Информация
		</div>

		<p><b>Ник: </b>{nick}</p>
		<p><b>Причина: </b>{reason}</p>
		<p><b>Статус: </b><span id="status" class="text-{color}">{status}</span></p>
		<p><b>Сервер: </b>{server}</p>
		<p><b>Игрок: </b><a href="../profile?id={author_id}">{author_login}</a></p>
		<p><b>Дата создания: </b>{date}</p>
		{if('{closed}' != '0')}
			<p><b>Рассмотрел: </b><a href="../profile?id={closed}">{closed_a}</a></p>
		{else}
			<p><div id="closed"></div></p>
		{/if}
	</div>
	{if(('{bid}' != '0') || is_worthy_specifically("o", {server_id}) || is_worthy_specifically("i", {server_id}))}
	<div class="block ban-application ban-information">
		<div class="block_head">
			Операции
		</div>

		{if('{bid}' != '0')}
		<input type="hidden" class="form-control" id="search_ban" value="{bid}">
		<button onclick="search_ban_application({server_id})" type="button" class="btn btn-primary">Подробнее</button>

		<div id="ban" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">Подробная информация</h4>
					</div>
					<div class="modal-body" id="baninfo">
					</div>
				</div>
			</div>
		</div>
		{/if}
		{if(is_worthy_specifically("i", {server_id}))}
		<button onclick="close_ban({id},'1','{bid}');" type="button" class="btn btn-success">Разбанен</button>
		<button onclick="close_ban({id},'2','{bid}');" type="button" class="btn btn-warning">Не разбанен</button>
		{/if}
		{if(is_worthy_specifically("o", {server_id}))}
		<button onclick="dell_ban({id});" type="button" class="btn btn-danger">Удалить</button>
		{/if}
	</div>
	{/if}
</div>
<div class="col-lg-6">
	<div class="block">
		<div class="block_head">
			Материал
		</div>
		<p class="mb-0"><b>Комментарий игрока</b></p>
		<div class="noty-block with_code mt-1">{text}</div>

		<p class="mb-0"><b>Скриншоты</b></p>
		{if('{imgs}' == '0')}
			<small>Пользователь не прикрепил скриншотов</small>
		{else}
			{imgs}
		{/if}
		
		<p class="mb-0 mt-2"><b>Демо</b></p>
		{if('{demo}' == '0')}
			<small>Пользователь не указал ссылку на демо</small>
		{else}
			<a class="btn btn-outline-primary" href="{demo}" target="_blank">
				Скачать
			</a>
		{/if}
	</div>
</div>
<div class="col-lg-12">
	<div class="block">
		<div class="block_head">
			Комментарии
		</div>
		{if(is_auth())}
			<div id="add_new_comments">
				<textarea id="text" maxlength="500"></textarea>

				<div class="smile_input_forum mt-3">
					<input id="send_btn" class="btn btn-primary" type="button" onclick="send_ban_comment({id});" value="Отправить">
					<div id="smile_btn" class="smile_btn" data-container="body" data-toggle="popover" data-placement="top" data-content="empty"></div>
				</div>
			</div>
			<script>
				$(document).ready(function() {
					init_tinymce("text", "lite", "{file_manager_theme}", "{file_manager}", "{{md5($conf->code)}}");
					get_smiles('#smile_btn', 1);
				});

				$('#smile_btn').popover({ html: true, animation: true, trigger: "click" });
				$('#smile_btn').on('show.bs.popover', function () {
					$(document).mouseup(function (e) {
						var container = $(".popover-body");
						if (container.has(e.target).length === 0){
							$('#smile_btn').popover('hide');
							selected = 'gcms_smiles';
						}
					});
				});

				function set_smile(elem){
					var smile =  "<img src=\""+$(elem).attr("src")+"\" class=\"g_smile\" height=\"20px\" width=\"20px\">";
					tinymce.activeEditor.insertContent(smile);
					$('#smile_btn').popover('hide');
					selected = 'gcms_smiles';
				}
			</script>
		{/if}
		<div id="comments" class="mt-3">
			<div class="loader"></div>
			<script>load_ban_comments("{id}");</script>
		</div>
	</div>
</div>