<?
	$frame = trading()->get_resource_active(3, '{user_id}');
	$very = new Verification(pdo());
?>
<div class="dialog" id="{id}">
	<div onclick="open_dialog({id});">
		{if(isset($frame))}
		<div class="playground">
			<div class="frame">
				<img src="/files/playground/{{$frame}}" class="rounded-0">
			</div>

			<img src="<?=convert_avatar('{user_id}');?>" class="rounded-0">
		</div>
		{else}
		<img src="<?=convert_avatar('{user_id}');?>">
		{/if}

		<div>
			<p>{login}
				{if($very->is_very('{user_id}'))}
					{{$very->get_very_style('standart')}}
				{/if}
			</p>
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