<section>
	<div class="tabs tabs-style-topline">
		<nav>
			<ul>
				<li><a href="#section-topline-2"><span>Категории</span></a></li>
				<li onclick="load_news_adm();"><a href="#section-topline-2"><span>Существующие новости</span></a></li>
			</ul>
		</nav>
		<div class="content-wrap">

			<section id="section-topline-1">
				<div class="panel panel-default">
					<div class="panel-heading">Добавить категорию</div>
					<div class="panel-body">
						<div class="input-group">
							<span class="input-group-btn">
								<button class="btn btn-default" type="button" onclick="add_class(1);">Добавить</button>
							</span>
							<input type="text" class="form-control" id="class_name" maxlength="255" autocomplete="off" value="">
						</div>
					</div>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading">Категории</div>
					<div class="panel-body" id="classes">
						<center><img src="{site_host}templates/admin/img/loader.gif"></center>
					</div>
				</div>
				<script>load_classes(1);</script>
			</section>

			<section id="section-topline-2">
				<div class="content">
					<div class="table-responsive mb-0">
						<table class="news_table table table-bordered">
							<thead>
								<tr>
									<td>#</td>
									<td>Название</td>
									<td>Категория</td>
									<td>Дата</td>
									<td>Автор</td>
									<td>Действие</td>
								</tr>
							</thead>
							<tbody id="news">
								<tr><td colspan="10"><center><img src="{site_host}templates/admin/img/loader.gif"></center></td></tr>
							</tbody>
						</table>
					</div>
				</div>
			</section>
		</div>
	</div>
</section>
<script src="{site_host}templates/admin/js/tabs.js"></script>
<script>
	(function() { [].slice.call( document.querySelectorAll( '.tabs' ) ).forEach( function( el ) { new CBPFWTabs( el ); }); })();
</script>