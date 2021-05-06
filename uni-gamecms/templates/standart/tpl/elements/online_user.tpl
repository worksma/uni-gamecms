<hr>
<div class="row" style="cursor:pointer;" OnClick="window.location.href = '/profile?id={user_id}';" data-placement="bottom" tooltip="yes" data-original-title="Посетить профиль пользователя">
	<div class="col-md-3">
		<img src="../{avatar}" alt="{login}" width="40px">
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