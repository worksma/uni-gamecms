<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Тикеты
		</div>

		<div class="noty-block info">
			<p>
				Мы будем благодарны, если Вы проверите <a target="_blank" href="../pages/baza_znaniy"><b>Базу Знаний</b></a> перед обращением к нам. Вы можете найти ответ на Ваш вопрос в базе. Если же этого не случилось, то создайте тикет.
			</p>
		</div>

		<div class="table-responsive mb-0 mt-3">
			<table class="table table-bordered">
				<thead>
					<tr>
						<td width="10%">#</td>
						<td>Тема</td>
						<td>Статус</td>
						<td>Дата</td>
					</tr>
				</thead>
				<tbody id="tickets">
					<tr>
						<td colspan="10">
							<div class="loader"></div>
							<script>load_tickets('{id}');</script>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	<div class="block">
		<a href="../support/add_ticket" class="btn btn-outline-primary btn-xl">Создать тикет</a>
		{if(is_worthy("p"))}
			<a href="../support/all_tickets" class="btn btn-outline-primary btn-xl mt-2">Тикеты</a>
		{/if}
	</div>

	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar_secondary.tpl"}
</div>