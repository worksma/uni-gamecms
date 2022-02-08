<div class="col-lg-9 order-is-first">
	<div class="block block-table">
		<div class="block_head">Мои услуги</div>
		<div class="table-responsive mb-0">
			<table class="table table-bordered admins">
				<thead>
					<tr>
						<td>#</td>
						<td>Идентификатор</td>
						<td>Активность</td>
						<td>Сервер</td>
						<td>Параметры</td>
					</tr>
				</thead>
				<tbody id="my_stores">
					<tr>
						<td colspan="10">
							<div class="loader"></div>
							<script>get_user_srotes();</script>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	function local_change_admin_type(id) {
		var type = $('#store_type_'+id+' option:selected').val();
		change_admin_bind_type(type, id);
	}
</script>

<div class="col-lg-3 order-is-last">
	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar_secondary.tpl"}
</div>