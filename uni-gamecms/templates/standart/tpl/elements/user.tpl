<div class="friend" id="{id}">
	<a href="../profile?id={id}">
		<img alt="{login}" src="../{avatar}">
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