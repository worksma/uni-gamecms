<ul class="collapsible-menu user-menu">
	<li class="collapsible">
		<a href="">
			<?
				$playground = new Playground($pdo, $conf);
				$avaimage = $playground->get_resource_active(2, $_SESSION['id']);
				
				if($avaimage):
			?>
				<img src="../files/playground/<?echo $avaimage;?>" alt="{login}">
			<?
				else:
			?>
				<img src="../{avatar}" alt="{login}">
			<?
				endif;
			?>
			
			<h3>{login}</h3>
			<p>{group}</p>

			<script>check_news();</script>
			<div id="check_mess"></div>
			{if('{tickets}' > '0' || '{bans}' > '0')}
			<i class="point"></i>
			{/if}
		</a>
		<ul>
			<li>
				<a href="../profile">Мой профиль</a>
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
				<a href="../purse">Баланс: <font id="balance">{balance}</font>руб.</a>
			</li>
			<li>
				<a href="../notifications">Уведомления</a>
			</li>
			<li>
				<a href="../my_stores">Услуги</a>
			</li>
			{if('{tickets}' > '0')}
			<li>
				<a href="../support/all_tickets">Открытые тикеты: +{tickets}</a>
			</li>
			{/if}
			{if('{bans}' > '0')}
			<li>
				<a href="../bans/index">Заявки на разбан: +{bans}</a>
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