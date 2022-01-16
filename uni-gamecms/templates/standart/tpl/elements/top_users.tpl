<?
	$frame = trading()->get_resource_active(3, '{id}');
?>
<hr>
<div class="row ml-1" style="cursor:pointer;" OnClick="window.location.href = '/profile?id={id}';" data-placement="bottom" tooltip="yes" data-original-title="Посетить профиль пользователя">
	{if(isset($frame))}
	<div class="playground m-40">
		<div class="frame">
			<img src="/files/playground/{{$frame}}" alt="{login}">
		</div>

		<img src="<?=convert_avatar('{id}');?>" alt="{login}">
	</div>
	{else}
		<img src="<?=convert_avatar('{user_id}');?>" alt="{login}" width="40" height="40" class="rounded-circle">
	{/if}
		
	<div>
		{if($very = new Verification($pdo) and $very->is_very('{id}'))}
			<p class="ml-2 mb-0" style="color: {gp_color}">{login} <?=$very->get_very_style('standart');?></p>
		{else}
			<p class="ml-2 mb-0" style="color: {gp_color}">{login}</p>
		{/if}
		
		<span class="ml-2" title="Рейтинг" tooltip="yes"><i class="fas fa-star text-warning"></i> {reit}</span>
		<span class="ml-1" title="Сообщений" tooltip="yes"><i class="fas fa-envelope text-info"></i> {answers}</span>
		<span class="ml-1" title="Спасибок" tooltip="yes"><i class="fas fa-hand-point-right text-danger"></i> {thanks}</span>
	</div>
</div>