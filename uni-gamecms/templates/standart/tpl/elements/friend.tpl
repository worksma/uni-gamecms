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
		{if('{type}' == '2')}
		<a href="#" onclick="cancel_friend('{id}'),dell_block('{id}')">
			Отменить заявку
		</a>
		{/if}
		{if('{type}' == '3')}
		<a href="#" onclick="take_friend('{id}'), dell_block('{id}'), load_col_infriends()">
			Принять
		</a>
		<a href="#" onclick="reject_friend('{id}'), dell_block('{id}'), load_col_infriends()">
			Отклонить
		</a>
		{/if}
	</div>
</div>