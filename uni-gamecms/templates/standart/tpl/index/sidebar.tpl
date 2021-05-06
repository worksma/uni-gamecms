<div class="block">
	<div class="block_head">
		Сейчас онлайн <span id="users_online_number"></span>
	</div>
	<div id="online_users">
		{func Widgets:online_users()}
	</div>
</div>

<div class="block">
	<div class="block_head">
		Топ пользователей
	</div>
	<div id="top_users">
		{func Widgets:top_users('5')}
	</div>
</div>

<div class="block">
	<div class="block_head">
		Последнее на форуме
	</div>
	<div id="last_activity">
		{func Widgets:last_forum_activity('5')}
	</div>
</div>

{if($conf->disp_last_online == '1')}
<div class="block">
	<div class="block_head">
		Сегодня были <span id="count_of_last_onl_us"></span>
	</div> 
	<div id="load_last_online">
		{func Widgets:were_online()}
	</div>
</div>
{/if}