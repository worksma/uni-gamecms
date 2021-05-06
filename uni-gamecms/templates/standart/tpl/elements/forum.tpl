<div class="col-lg-8">
	<img src="../{img}" alt="{name}">
	<div>
		<h3><a href="../forum/forum?id={id1}" title="{description}">{name}</a></h3>
		<p>{description}</p>		
	</div>
</div>
<div class="d-none d-lg-block col-lg-4">
	{if('{msg_id}' == '')}
		<p>Форум пуст</p>
	{else}
		<p><a href="../forum/topic?id={msg_id}&page=last">{msg_name}</a></p>
		<p>Дата: {msg_date}</p>
		{if($very = new Verification($pdo) and $very->is_very('{msg_author}'))}
			<p>Автор: <a href="../profile?id={msg_author}">{msg_login}</a> <?echo $very->get_very_style('standart');?></p>
		{else}
			<p>Автор: <a href="../profile?id={msg_author}">{msg_login}</a></p>
		{/if}
	{/if}
</div>