<a href="../profile?id={user_id}" title="{login}">
	<img src="../{avatar}" alt="{login}">
	<span style="color: {gp_color}">{login}</span>

	{if('{showSum}' == '1')}
		<span>{sum}</span>
	{else}
		<span>{gp_name}</span>
	{/if}
</a>