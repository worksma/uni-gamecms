<?
	$frame = trading()->get_resource_active(3, '{user_id}');
?>
<div class="event" id="event{id}">
	<div>
		<a href="../{link}">{content}</a>
	</div>
	<a href="../profile?id={user_id}" title="{gp_name}">
		{if(isset($frame))}
		<div class="playground">
			<div class="frame">
				<img src="/files/playground/{{$frame}}" class="rounded-0">
			</div>

			<img src="<?=convert_avatar('{user_id}');?>" alt="{login}" class="rounded-0">
		</div>
		{else}
			<img src="<?=convert_avatar('{user_id}');?>" alt="{login}">
		{/if}
		<span style="color: {gp_color}">{login}</span>
	</a>
	<span>
		{date}
	</span>
	<a class="btn btn-outline-primary btn-sm" href="../{link}">
		Перейти
	</a>
	{if(is_worthy("d"))}
		<i class="m-icon icon-remove" onclick="dell_event({id})" tooltip="yes" data-placement="left" title="Удалить уведомление"></i>
	{/if}
</div>