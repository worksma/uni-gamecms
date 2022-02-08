{if('{title}' == '1')}
	<div class="col-lg-12">
		<h2>Темы форума</h2>
	</div>
{else}
	<div class="col-lg-8">
		<img src="../{img}" alt="{name}">
		<div>
			<h3><a href="../forum/topic?id={id}" title="{name}">{name}</a></h3>
			<p>
				<span tooltip="yes" title="Автор темы"><i class="fas fa-user"></i> <a href="../profile?id={topic_author}">{topic_login}</a></span>
				<span tooltip="yes" title="Просмотров"><i class="far fa-eye"></i> {views}</span>
				<span tooltip="yes" title="Ответов"><i class="fas fa-envelope"></i> {answers}</span>
			</p>		
		</div>
	</div>
	<div class="d-none d-lg-block col-lg-4">
		{if('{last_msg}' == '')}
			<p>Сообщений нет</p>
		{else}
			<p>Последнее сообщение:</p>
			<p>От <a href="../profile?id={msg_author}">{msg_login}</a></p>
			<p>{date}</p>
		{/if}
	</div>
{/if}