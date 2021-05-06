<div class="col-lg-6">
	<div class="block ban-application ban-information">
		<div class="block_head">
			Сообщение
		</div>
		<div class="with_code">
			{text}
		</div>
	</div>
</div>
<div class="col-lg-6">
	<div class="block">
		<div class="block_head">
			Информация
		</div>

		<p><b>Тема:</b> {name}</p>
		<p><b>Автор:</b> <a target="_blank" href="../profile?id={author_id}">{author_login}</a></p>
		<p>
			<b>Статус:</b> 
			{if('{status}' == '1')}
				<span id="status" class="label label-info">Открыт</span>
			{else}
				<span id="status" class="label label-success">Закрыт</span>
			{/if}
		</p>
		<p><b>Дата создания:</b> {date}</p>
	</div>

	{if(('{status}' == '1') or (is_worthy("l")))}
	<div class="block">
		<div class="block_head">
			Операции
		</div>
		{if('{status}' == '1')}
			<button class="btn btn-success" onclick="close_ticket('{id}')">Закрыть тикет </button>
		{/if}
		{if(is_worthy("l"))}
			<button class="btn btn-danger" onclick="dell_ticket('{id}')">Удалить тикет </button>
		{/if}
	</div>
	{/if}
</div>
<div class="col-lg-12">
	<div class="block">
		<div class="block_head">
			Комментарии
		</div>
		<div id="add_ticket_answer">
			{if('{status}' == '1')}
				<textarea id="text" maxlenght="1000"></textarea>
				<div class="smile_input_forum mt-3">
					<input id="send_btn" class="btn btn-primary" type="button" onclick="send_ticket_answer({id});" value="Отправить"></input>
					<div id="smile_btn" class="smile_btn" data-container="body" data-toggle="popover" data-placement="top" data-content="empty"></div>
				</div>
			{/if}
		</div>
		<div id="answers" class="mt-3">
			<div class="loader"></div>
			<script>load_ticket_answers("{id}");</script>
		</div>
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