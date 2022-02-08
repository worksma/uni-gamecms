<?
	$frame = trading()->get_resource_active(3, '{id}');
?>
<div class="friend" id="{id}">
	<a href="../profile?id={id}">
		{if(isset($frame))}
		<div class="playground ml-2">
			<div class="frame">
				<img class="rounded-0" src="/files/playground/{{$frame}}">
			</div>

			<img class="rounded-0" src="<?=convert_avatar('{id}');?>" alt="{login}">
		</div>
		{else}
		<img class="ml-2" src="<?=convert_avatar('{id}');?>" alt="{login}">
		{/if}
	</a>
	<div>
		<a title="{login}" href="../profile?id={id}" style="color: {gp_color}">{login} <small>({gp_name})</small></a>

		<a href="../friends?id={id}">
			Друзья
		</a>
		<a href="../messages?create_id={id}">
			Написать сообщение
		</a>
		{if(is_worthy("f"))}
		<a href="../edit_user?id={id}">
			Редактирование пользователя
		</a>
		{/if}
		{if(is_worthy("g"))}
		<a onclick="dell_user('{id}')">
			Удалить пользователя
		</a>
		{/if}
	</div>
</div>