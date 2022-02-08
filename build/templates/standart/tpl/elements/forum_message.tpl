<?
	$frame = trading()->get_resource_active(3, '{author}');
?>
<div id="answer_{id}">
	<div class="top-area">
		<div class="left-side">
		{if($very = new Verification($pdo) and $very->is_very('{author}'))}
			<a href="../profile?id={author}">{login}</a> <?echo $very->get_very_style('standart');?>
		{else}
			<a href="../profile?id={author}">{login}</a>
		{/if}
			
		</div>
		<div class="right-side">
			{if(is_worthy("r"))}
			<a onclick="dell_answer('{id}');">
				<span class="m-icon icon-trash" tooltip="yes" title="Удалить"></span>
			</a>
			{/if}

			{if((is_worthy("r")) || (is_auth() && ('{author_id}' == '{my_id}') and ('1' == '{check}')))}
			<a href="../forum/edit_message?id={topic_id}&id2={id}">
				<span class="m-icon icon-pencil" tooltip="yes" title="Редактировать"></span>
			</a>
			{/if}

			<a href="#answer_{id}">
				{date}
			</a>
		</div>
	</div>

	<div class="center-area">
		<div class="left-side">
			{if(isset($frame))}
			<div class="playground">
				<div class="frame">
					<img src="/files/playground/{{$frame}}">
				</div>

				<img src="<?=convert_avatar('{author}');?>">
			</div>
			{else}
			<img src="<?=convert_avatar('{author}');?>">
			{/if}

			<p style="color: {gp_color}">{gp_name}</p><br>
			<p>Рейтинг: {reit}</p><br>
			<p>Сообщений: {answers}</p><br>
			<p>Спасибок: {thanks}</p>
		</div>
		<div class="right-side">
			<div id="text_{id}" class="with_code">
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
				<button class="btn btn-outline-primary btn-sm w-100" tooltip="yes" title="Ответить" onclick="answer({id}, '{login}', '{link}');">
					<span class="d-none d-lg-block">Ответ</span>
					<i class="d-block d-lg-none fas fa-comment"></i>
				</button>
				{else}
				<button class="btn btn-outline-primary btn-sm" tooltip="yes" title="Ответить" onclick="answer({id}, '{login}', '{link}');">
					<span class="d-none d-lg-block">Ответ</span>
					<i class="d-block d-lg-none fas fa-comment"></i>
				</button>
				<button class="btn btn-outline-primary btn-sm" tooltip="yes" title="Спасибо" onclick="thank({id}, 0);">
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
			{mess_thanks}
		</div>
		<div class="clearfix"></div>
	</div>
</div>