<tr>
	<td>
		<a href="../complaints/complaint?id={id}">
			Подробнее
		</a>
	</td>
	<td>
		{if('{accused_profile_id}' == '0')}
			<a href="#">
				<img src="../files/avatars/no_avatar.jpg" alt="Неизвестно"> Неизвестно
			</a>
		{else}
			<a target="_blank" href="../profile?id={accused_profile_id}">
				<img src="../{accused_avatar}" alt="{accused_login}"> {accused_login}
			</a>
		{/if}
	</td>
	<td>
		<p class="text-{color}">
			{status}
		</p>
	</td>
	<td>{date}</td>
</tr>