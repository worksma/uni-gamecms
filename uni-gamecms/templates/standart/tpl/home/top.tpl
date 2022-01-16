<?
	$request_url = $_SERVER['REQUEST_URI'];
	if(strpos($request_url, "profile") !== false):
		global $profile;
		$background = trading()->get_resource_active(1, $profile->id);
	endif;
?>
<div class="container-fluid wapper">
	{if(isset($background))}
	<div class="content" style="background: url('/files/playground/{{$background}}') no-repeat; background-attachment: scroll; background-attachment: fixed; background-size: cover;">
	{else}
	<div class="content">
	{/if}
		<div class="header">
			<div class="container">
				<button class="menu-trigger btn d-block d-lg-none collapsed" type="button" data-toggle="collapse" data-target="#hidden-menu" ></button>
				<div class="clearfix d-block d-lg-none"></div>
				<div class="collapse d-none d-lg-block" id="hidden-menu">
					<ul class="collapsible-menu">
						{menu}
					</ul>
				</div>