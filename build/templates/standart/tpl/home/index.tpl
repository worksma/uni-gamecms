<div class="col-lg-9 order-is-first">
	<div class="block disp-n" id="notifications_line">
		<div class="block_head">
			Уведомления
			<button onclick="hide_notifications();" class="btn btn-outline-primary btn-sm">Скрыть все</button>
		</div> 
		<div id="notifications">
			{notifications}
		</div>
	</div>

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

	{if($conf->show_events != '0')}
	<div class="block">
		<div class="block_head">
			Последние события проекта
		</div> 
		<div id="events">
			{func EventsRibbon:get_events(0, 0, $conf->show_events)}
		</div>
	</div>
	{/if}
</div>

<div class="col-lg-3 order-is-last">
	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar.tpl"}
	{include file="/elements/vk_widgets.tpl"}
</div>