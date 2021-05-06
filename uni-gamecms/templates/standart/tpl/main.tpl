{include file="config.tpl"}
<!DOCTYPE html>
<html lang="ru">
	{if($conf->off == 1 && !is_admin())}
		{include file="off_site.tpl"}
	{else}
		{content}
	{/if}
</html>