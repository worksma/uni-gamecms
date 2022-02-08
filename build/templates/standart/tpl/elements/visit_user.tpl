<?
	$frame = trading()->get_resource_active(3, '{user_id}');
	$end_show = "<img class='popover_avatar' src='".convert_avatar('{user_id}')."'>";

	if(isset($frame)):
		$end_show = "
			<div class='popover_avatar playground'>
				<div class='frame'>
					<img src='/files/playground/".$frame."'>
				</div>

				<img src='".convert_avatar('{user_id}')."''>
			</div>
		";
	endif;
?>
<a target="_blank" href="../profile?id={user_id}" id="visit_user{user_id}" style="color: {gp_color}"  data-container="body" data-toggle="popover" data-placement="top" data-content="{{$end_show}}">{login}<script>$('#visit_user{user_id}').popover({ html: true, animation: true, trigger: "hover" });</script></a>