<ul class="collapsible-menu user-menu">
	<li class="collapsible">
		<a href="">
			<img src="../{avatar}" alt="{login}">
			<h3>{login}</h3>
			<p>{group_name}</p>

			<script>check_news();</script>
			<div id="check_mess"></div>
			{if('{countOfOpenTickets}' > '0' || '{countOfOpenBans}' > '0')}
			<i class="point"></i>
			{/if}
		</a>
		<ul>
			<li>
				<a href="../profile?id={user_id}">Мой профиль</a>
			</li>
			<li>
				<a href="../messages">Сообщения</a>
			</li>
			<li>
				<a href="../friends">Друзья</a>
			</li>
			<li>
				<a href="../settings">Настройки</a>
			</li>
			<li>
				<a href="../purse">Баланс: <font id="balance">{balance}</font>{{$messages['RUB']}}</a>
			</li>
			<li>
				<a href="../notifications">Уведомления</a>
			</li>
			<li>
				<a href="../my_stores">Услуги</a>
			</li>
			{if('{countOfOpenTickets}' > '0')}
			<li>
				<a href="../support/all_tickets">Открытые тикеты: +{countOfOpenTickets}</a>
			</li>
			{/if}
            {if('{countOfOpenBans}' > '0')}
				<li>
					<a href="../bans/index">Заявки на разбан: +{countOfOpenBans}</a>
				</li>
            {/if}
            {if('{countOfOpenComplaints}' > '0')}
				<li>
					<a href="../complaints/index">Жалобы: +{countOfOpenComplaints}</a>
				</li>
            {/if}
			{if(is_admin_id())}
			<li>
				<a href="../admin" target="_blank">Админ центр</a>
			</li>
			{/if}
			<li>
				<a href="../exit">Выход</a>
			</li>
		</ul>
	</li>
</ul>