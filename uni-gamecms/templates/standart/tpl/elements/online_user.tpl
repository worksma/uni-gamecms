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
<div class="row" style="cursor:pointer;" OnClick="window.location.href = '/profile?id={user_id}';" data-placement="bottom" tooltip="yes" data-original-title="Посетить профиль пользователя">
	<div class="col-md-3">
		<?$playground = new Playground($pdo, $conf);?>
		
		{if($fmimage = $playground->get_resource_active(3, {user_id}))}
			<div class="top-users-frame">
				<div class="top-users-avatar-frame">
					<img src="../files/playground/<?echo $fmimage;?>" width="40px">
				</div>
				{if($avaimage = $playground->get_resource_active(2, {user_id}))}
					<img src="../files/playground/<?echo $avaimage;?>" alt="{login}" width="40px">
				{else}
					<img src="{avatar}" alt="{login}" width="40px">
				{/if}
			</div>
		{else}
			{if($avaimage = $playground->get_resource_active(2, {user_id}))}
				<img src="../files/playground/<?echo $avaimage;?>" alt="{login}" width="40px">
			{else}
				<img src="../{avatar}" alt="{login}" width="40px">
			{/if}
		{/if}
	</div>
	<div class="col">
		{if($very = new Verification($pdo) and $very->is_very('{user_id}'))}
			<span style="color: {gp_color}">{login}</span> <?echo $very->get_very_style('standart');?>
		{else}
			<span style="color: {gp_color}">{login}</span>
		{/if}
		<br>
		<span>{gp_name}</span>
	</div>
</div>