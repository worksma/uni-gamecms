<div class="col-lg-3 order-is-last">
	{include file="/index/authorization.tpl"}
	{include file="/elements/vk_widgets.tpl"}
	{include file="/index/sidebar.tpl"}
</div>

<div class="col-lg-9 order-is-first">
	<div id="main-slider" class="carousel slide" data-ride="carousel">
		<div class="carousel-inner">
			{for($i=0;$i < count($slider);$i++)}  
				<div class="carousel-item {if($i==0)}active{/if}">
					<img class="d-block w-100" src="{{$slider[$i]['image']}}">
					<div class="carousel-caption">
						<h1>
							{if(!empty($slider[$i]['link']))}<a href="{{$slider[$i]['link']}}" target="_blank">{/if}
							{{$slider[$i]['title']}}
							{if(!empty($slider[$i]['link']))}</a>{/if}
						</h1>
						<p class="d-none d-lg-block">{{$slider[$i]['content']}}</p>
						{if(!empty($slider[$i]['link']))}
							<a href="{{$slider[$i]['link']}}" class="px-4 btn btn-primary d-none d-lg-inline-block" target="_blank">Подробнее</a>
						{/if}
					</div>
				</div>
			{/for}
		</div>
		<a class="carousel-control-prev" href="#main-slider" role="button" data-slide="prev">
			<span class="carousel-control-prev-icon" aria-hidden="true"></span>
			<span class="sr-only">Previous</span>
		</a>
		<a class="carousel-control-next" href="#main-slider" role="button" data-slide="next">
			<span class="carousel-control-next-icon" aria-hidden="true"></span>
			<span class="sr-only">Next</span>
		</a>
	</div>

	{include file="/elements/chat.tpl"}

	{if($conf->show_news != '0')}
	<div class="block">
		<div class="block_head">
			Новости проекта
		</div> 
		<div class="vertical-center-line">
			<div id="new_news" class="clearfix">
				{func Widgets:last_news($conf->show_news)}
			</div>
		</div>
	</div>
	{/if}

	<div class="block">
		<div class="block_head">
			Форум
		</div> 
		<div id="forum">
			{func Forum:get_forums()}
		</div>
	</div>

	{include file="/index/main_info.tpl"}
</div>

<div class="disp-n" id="auth-result">{conf_mess}</div>
<script>
	var conf_mess = $("#auth-result > p").text();
	
	if(conf_mess != '') {
		var conf_mess_style = $("#auth-result > p").attr("class");

		if(conf_mess_style.indexOf("danger") > 0) {
			conf_mess_style = 'error';
		} else {
			conf_mess_style = 'success';
		}

		show_noty('Down', 'error', conf_mess, 10000);
	}
</script>