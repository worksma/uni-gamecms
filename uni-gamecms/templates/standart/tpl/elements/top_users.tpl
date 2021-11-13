<style>
	.top-users-frame {
		position: relative;
	}
	
	.top-users-frame img {
		background: none;
		filter: none;
		display: block;
		padding: 0px;
	}
	
	.top-users-avatar-frame {
		position: absolute;
	}
	
	.top-users-avatar-frame > img {
		transform: scale(1.23);
		background: none;
	}
</style>
<hr>
<div class="row ml-2" style="cursor:pointer;" OnClick="window.location.href = '/profile?id={id}';" data-placement="bottom" tooltip="yes" data-original-title="Посетить профиль пользователя">
	<?$playground = new Playground($pdo, $conf);?>
		
	{if($fmimage = $playground->get_resource_active(3, {id}))}
		<div class="top-users-frame">
			<div class="top-users-avatar-frame">
				<img src="../files/playground/<?=$fmimage;?>" width="40px" height="40px">
			</div>
			{if($avaimage = $playground->get_resource_active(2, {id}))}
				<img src="../files/playground/<?=$avaimage;?>" alt="{login}" width="40px" height="40px">
			{else}
				<img src="../{avatar}" alt="{login}" width="40px" height="40px">
			{/if}
		</div>
	{else}
		{if($avaimage = $playground->get_resource_active(2, {id}))}
			<img src="../files/playground/<?=$avaimage;?>" alt="{login}" width="40px" height="40px">
		{else}
			<img src="../{avatar}" alt="{login}" width="40px" height="40px">
		{/if}
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