<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			{name}
		</div>
		<div id="answers" class="topic-answers">
			{if('{page}' == '1')}
			<div id="answer_0">
				<div class="top-area">
					<div class="left-side">
						{if($very = new Verification($pdo) and $very->is_very('{author_id}'))}
							<a href="../profile?id={author_id}">{author_login}</a> <?echo $very->get_very_style('standart');?>
						{else}
							<a href="../profile?id={author_id}">{author_login}</a>
						{/if}
					</div>
					<div class="right-side">
						<a href="#answer_0">
							{date}
						</a>
					</div>
				</div>

				<div class="center-area">
					<div class="left-side">
						<?$playground = new Playground($pdo, $conf);?>
						
						{if($fmimage = $playground->get_resource_active(3, {author_id}))}
							<div class="profile-frame mb-4">
								<div class="profile-avatar-frame">
									<img src="../files/playground/<?echo $fmimage;?>">
								</div>
								{if($avaimage = $playground->get_resource_active(2, {author_id}))}
									<img src="../files/playground/<?echo $avaimage;?>">
								{else}
									<img src="../{author_avatar}">
								{/if}
							</div>
						{else}
							{if($avaimage = $playground->get_resource_active(2, {author_id}))}
								<img src="../files/playground/<?echo $avaimage;?>">
							{else}
								<img src="../{author_avatar}">
							{/if}
						{/if}

						<p style="color: {group_color}">{group_name}</p><br>
						<p>Рейтинг: {reit}</p><br>
						<p>Сообщений: {answers}</p><br>
						<p>Спасибок: {thanks}</p>
					</div>
					<div class="right-side">
						<div id="text_0" class="with_code">
							{text}
						</div>
						{if('{edited_by_id}' != '')}
							<div class="edited">Отредактировал: <a href="../profile?id={edited_by_id}" title="{edited_by_login}">{edited_by_login}</a>, {edited_time}</div>
						{/if}
						{if('{signature}' != '')}
							<div class="with_code signature">
								{signature}
							</div>
						{/if}
					</div>
					<div class="clearfix"></div>
				</div>

				<div class="bottom-area">
					{if(is_auth())}
					<div class="left-side">
						<div class="btn-group">
							{if('{author_id}' == '{my_id}')}
							<button class="btn btn-outline-primary btn-sm w-100" tooltip="yes" title="Ответить" onclick="answer(0, '{author_login}', '{link}');">
								<span class="d-none d-lg-block">Ответ</span>
								<i class="d-block d-lg-none fas fa-comment"></i>
							</button>
							{else}
							<button class="btn btn-outline-primary btn-sm" tooltip="yes" title="Ответить" onclick="answer(0, '{author_login}', '{link}');">
								<span class="d-none d-lg-block">Ответ</span>
								<i class="d-block d-lg-none fas fa-comment"></i>
							</button>
							<button class="btn btn-outline-primary btn-sm" tooltip="yes" title="Спасибо" onclick="thank({id}, 1);">
								<span class="d-none d-lg-block">Спасибо</span>
								<i class="d-block d-lg-none fas fa-thumbs-up"></i>
							</button>
							{/if}
						</div>
					</div>
					{else}
					<div class="left-side">
						<div class="btn-group">
							<button class="btn btn-outline-primary btn-sm" tooltip="yes" title="Ответить" onclick="show_stub();">
								<span class="d-none d-lg-block">Ответ</span>
								<i class="d-block d-lg-none fas fa-comment"></i>
							</button>
							<button class="btn btn-outline-primary btn-sm" tooltip="yes" title="Спасибо" onclick="show_stub();">
								<span class="d-none d-lg-block">Спасибо</span>
								<i class="d-block d-lg-none fas fa-thumbs-up"></i>
							</button>
						</div>
					</div>
					{/if}

					<div class="right-side">
						{thanks_str}
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
			{/if}

			{func Forum:get_answers("{id}", "{start}", "{limit}", "{script}", "{link}")}
		</div>
	</div>

	<div id="pagination2">{pagination}</div>

	{if(is_auth())}
	<div class="block mt-4">
		{if('{status}' == '1' or '{status}' == '3')}
			<div class="block_head">
				Оставить ответ
			</div>
			<div id="send_answer">
				<textarea id="text" maxlength="2000"></textarea>
				<div class="smile_input_forum mt-3">
					<input id="send_btn" class="btn btn-primary" type="button" onclick="send_answer('{id}');" value="Отправить">
					<div id="smile_btn" class="smile_btn visible-lg-inline-block" data-container="body" data-toggle="popover" data-placement="top" data-content="empty"></div>
				</div>
			</div>
		{else}
			<div class="disabled_input form-control">Тема закрыта <span class="glyphicon glyphicon-ban-circle"></span></div>
		{/if}
	</div>
	{/if}
</div>

<script>
	$(document).ready(function() {
		init_tinymce("text", "forum", "{file_manager_theme}", "{file_manager}", "{{md5($conf->code)}}");
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

<div class="col-lg-3 order-is-last">
	{if((is_worthy("e")) || (is_auth() && '{author_id}' == '{my_id}'))}
	<div class="block">
		{if(is_worthy("e"))}
			<a href="#" onclick="dell_topic('{id}' , '{id2}');" class="btn btn-outline-primary btn-xl mb-2">Удалить</a>
		{/if}
		{if((is_worthy("e")) || (is_auth() && '{author_id}' == '{my_id}'))}
			<a href="../forum/edit_topic?id={id}" class="btn btn-outline-primary btn-xl">Редактировать</a>
		{/if}
	</div>
	{/if}

	{if(is_auth())}
		{include file="/home/navigation.tpl"}
		{include file="/forum/sidebar.tpl"}
	{else}
		{include file="/index/authorization.tpl"}
		{include file="/forum/sidebar.tpl"}
	{/if}
</div>