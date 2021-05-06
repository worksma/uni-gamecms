<hr>
<div class="row" style="cursor:pointer;" OnClick="window.location.href = '/profile?id={id}';" data-placement="bottom" tooltip="yes" data-original-title="Посетить профиль пользователя">
	<div class="col-md-3">
		<img src="../{avatar}" alt="{login}" width="40px">
	</div>
	<div class="col">
		{if($very = new Verification($pdo) and $very->is_very('{id}'))}
			<span style="color: {gp_color}">{login}</span> <?echo $very->get_very_style('standart');?>
		{else}
			<span style="color: {gp_color}">{login}</span>
		{/if}
		<br>
		<span title="Рейтинг" tooltip="yes"><i class="fas fa-star"></i> {reit}</span>
		<span title="Сообщений" tooltip="yes"><i class="fas fa-envelope"></i> {answers}</span>
		<span title="Спасибок" tooltip="yes"><i class="fas fa-hand-point-right"></i> {thanks}</span>
	</div>
</div>