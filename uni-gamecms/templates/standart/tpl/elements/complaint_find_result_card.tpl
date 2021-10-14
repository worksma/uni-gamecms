<input type="hidden" value="{id}" id="accused-id">
<div class="table-responsive mb-0">
	<table class="table table-bordered baninfo">
		<tr>
			<td><b>Профиль</b></td>
			<td>
                {if('{user_id}' == '0')}
					<a href="#">
						<img src="../files/avatars/no_avatar.jpg" alt="Неизвестно"> Неизвестно
					</a>
                {else}
					<a target="_blank" href="../profile?id={user_id}" title="{gp_name}">
						<img src="../{avatar}" alt="{login}"> <span style="color: {gp_color}">{login}</span>
					</a>
                {/if}
			</td>
		</tr>
		<tr>
			<td><b>Идентификатор</b></td>
			<td>{name}</td>
		</tr>
		<tr>
			<td><b>Услуги</b></td>
			<td>{services}</td>
		</tr>
	</table>
</div>