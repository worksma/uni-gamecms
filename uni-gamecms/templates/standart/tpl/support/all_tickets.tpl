<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Тикеты
		</div>
		<div class="tab-content">
			<div class="tab-pane fade show active" id="open-tickets-tab" role="tabpanel" aria-labelledby="open_tickets-tab">
				<div class="table-responsive mb-0">
					<table class="table table-bordered">
						<thead>
							<tr>
								<td>#</td>
								<td>Тема</td>
								<td>Открыл</td>
								<td>Последний ответ</td>
								<td>Дата создания</td>
							</tr>
						</thead>
						<tbody id="open_tickets">
							<tr>
								<td colspan="10">
									<div class="loader"></div>
									<script>load_open_tickets();</script>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="tab-pane fade" id="close-tickets-tab" role="tabpanel" aria-labelledby="close_tickets-tab">
				<div class="table-responsive mb-0">
					<table class="table table-bordered">
						<thead>
							<tr>
								<td>#</td>
								<td>Тема</td>
								<td>Закрыл</td>
								<td>Последний ответ</td>
								<td>Дата создания</td>
							</tr>
						</thead>
						<tbody id="close_tickets">
							<tr>
								<td colspan="10">
									<div class="loader"></div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	<div class="block">
		<div class="block_head">
			Списки
		</div>
		<div class="vertical-navigation">
			<ul class="nav">
				<li>
					<a class="active" id="open-tickets-tab" data-toggle="tab" href="#open-tickets-tab">Открытые тикеты ({count1})</a>
				</li>
				<li>
					<a id="open-tickets-tab" data-toggle="tab" href="#close-tickets-tab" onclick="load_close_tickets('first')">Закрытые тикеты ({count2})</a>
				</li>
			</ul>
		</div>
	</div>

	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar_secondary.tpl"}
</div>