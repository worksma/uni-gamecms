<div class="dialog" id="{id}">
	<div onclick="open_dialog({id});">
		<img src="../{avatar}" alt="{login}"> 
		<div>
			<p>{login}</p>
			<p>{date}</p>
			{if('{new}' == '0')}
				<p class="text-default">Все сообщения прочитаны</p>
			{else}
				{if('{new}' == '1')}
					<p class="text-success">Есть непрочитанные сообщения</p>
				{else}
					<p class="text-info">Собеседник еще не прочел сообщения</p>
				{/if}
			{/if}
		</div>
	</div>
	<div onclick="dell_dialog({id});">
		<span class="m-icon icon-trash"></span>
	</div>
</div>