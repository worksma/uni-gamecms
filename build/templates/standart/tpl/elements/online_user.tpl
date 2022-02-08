<?
	$frame = trading()->get_resource_active(3, '{user_id}');
?>
<hr>
<div class="row ml-1" style="cursor:pointer;" OnClick="window.location.href = '/profile?id={user_id}';" data-placement="bottom" tooltip="yes" data-original-title="Посетить профиль пользователя">
	{if(isset($frame))}
	<div class="playground m-40">
		<div class="frame">
			<img src="/files/playground/{{$frame}}" alt="{login}">
		</div>

		<img src="<?=convert_avatar('{user_id}');?>" alt="{login}">
	</div>
	{else}
		<img src="<?=convert_avatar('{user_id}');?>" alt="{login}" width="40" height="40" class="rounded-circle">
	{/if}

	<div>
		{if($very = new Verification($pdo) and $very->is_very('{user_id}'))}
			<span class="ml-2 mb-0" style="color: {gp_color}">{login}</span> <?=$very->get_very_style('standart');?>
		{else}
			<span class="ml-2 mb-0" style="color: {gp_color}">{login}</span>
		{/if}
		<br>
		<span class="ml-2">{gp_name}</span>
	</div>
</div>