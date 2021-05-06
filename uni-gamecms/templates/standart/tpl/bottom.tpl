					</div>
				</div>
			</div>
			<div class="footer">
				<div class="container">
					<div class="row">
						<div class="col-lg-5">
							<a href="{site_host}" title="{site_name}">
								<img src="{site_host}templates/{template}/img/logo.png" alt="{site_name}">
							</a>
							<p>
								{{$footer_description}}
							</p>

							<hr class="my-3 d-block d-lg-none">
						</div>
						<div class="col-lg-2 col-6">
							<strong>
								Навигация
							</strong>
							<ul>
								{for($i=0;$i < count($vertical_menu_2);$i++)}  
								<li>
									<a href="{{$vertical_menu_2[$i]['link']}}" title="{{$vertical_menu_2[$i]['name']}}">{{$vertical_menu_2[$i]['name']}}</a>
								</li>
								{/for}
							</ul>
						</div>
						<div class="col-lg-2 col-6">
							<strong>
								Проект
							</strong>
							<ul>
								{for($i=0;$i < count($vertical_menu_3);$i++)}  
								<li>
									<a href="{{$vertical_menu_3[$i]['link']}}" title="{{$vertical_menu_3[$i]['name']}}">{{$vertical_menu_3[$i]['name']}}</a>
								</li>
								{/for}
							</ul>
						</div>
						<div class="col-lg-3">
							<strong>
								Полезные ссылки
							</strong>
							<ul>
								{for($i=0;$i < count($vertical_menu_4);$i++)}  
								<li>
									<a href="{{$vertical_menu_4[$i]['link']}}" title="{{$vertical_menu_4[$i]['name']}}">{{$vertical_menu_4[$i]['name']}}</a>
								</li>
								{/for}
							</ul>
							{if($conf->cote == 1)}
								<div id="cote" onclick="click_cote();"><img src="{site_host}/ajax/sound/cote1.gif"></div>
							{/if}
						</div>

						<div class="col-lg-12">
							<hr class="my-3">
						</div>

						<div class="col-lg-8 copyright">
							<p><a href="{site_host}" title="{site_name}">{site_name}</a> © Все права защищены</p>
							{gamecms_copyright}
						</div>
						<div class="col-lg-4 banners">
							{for($i=0;$i < count($footer_banners);$i++)}  
							<a href="{{$footer_banners[$i]['link']}}" target="_blank">
								<img src="{{$footer_banners[$i]['img']}}" alt="banner">
							</a>
							{/for}
						</div>
					</div>
				</div>
			</div>
		</div>

		<script src="{site_host}templates/{template}/js/lightbox.js"></script>
		<script>
			window.onload = function () {
				$('[tooltip="yes"]').tooltip();
				$('[data-toggle="dropdown"]').dropdown();
			};
		</script>
	</body>