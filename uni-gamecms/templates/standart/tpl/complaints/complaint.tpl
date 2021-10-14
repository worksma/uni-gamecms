<div class="col-lg-6">
    <div class="block ban-application ban-information">
        <div class="block_head">
            Информация
        </div>

        <p><b>Статус: </b><span id="status" class="text-{color}">{status}</span></p>
        <p><b>Сервер: </b>{server_name}</p>
        <p><b>Нарушитель: </b> {if('{accused_id}' == '0')}Не зарегистрирован{else}<a href="../profile?id={accused_id}">{accused_login}</a>{/if}</p>
        <p>
            <b>Идентификатор нарушителя: </b>
            <a data-target="#services" data-toggle="modal" title="Подробнее" onclick="get_admin_info2({accused_admin_id})">{accused_admin_nick}</a>
        </p>
        {if('{numberOfOtherComplaints}' > '1')}
            <p>
                <b>Другие жалобы на нарушителя: </b><a href="../complaints/index?accusedProfileId={accused_id}">Подробнее ({numberOfOtherComplaints})</a>
            </p>
        {/if}

        <div id="services" class="modal fade">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Услуги</h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive mb-0">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td>#</td>
                                    <td>Услуга</td>
                                    <td>Дата покупки</td>
                                    <td>Дата окончания</td>
                                    <td>Осталось</td>
                                </tr>
                                </thead>
                                <tbody id="admin_info{accused_admin_id}">
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

        <p><b>Автор: </b><a href="../profile?id={author_id}">{author_login}</a></p>
        <p><b>Дата создания: </b>{date}</p>
        {if('{judge_id}' != '')}
            <p>
                <b>Рассмотрел: </b><a href="../profile?id={judge_id}">{judge_login}</a>
            </p>
        {else}
            <p><div id="closed"></div></p>
        {/if}
    </div>

    {if((is_worthy_specifically("k", {server_id}) || is_worthy_specifically("u", {server_id}) && '{sentence}' == '0') || ('{sentence}' != '0' && is_worthy_specifically("u", {server_id})))}
        <div class="block ban-application" id="complaintCloseBlock">
            <div class="block_head">
                Операции
            </div>

            <div class="form-group mb-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-primary" type="button" onclick="closeComplaint({id}, {accused_admin_id});">Выполнить</button>
                    </div>
                    <select id="sentence" class="form-control">
                        {if('{sentence}' == '0')}
                            {if(is_worthy_specifically("k", {server_id}))}
                                <option value="1">Оправдать</option>
                                <option value="2">Выдать предупреждение</option>
                            {/if}
                            {if(is_worthy_specifically("j", {server_id}))}
                                <option value="3">Удалить услуги</option>
                                <option value="4">Заблокировать услуги</option>
                            {/if}
                        {/if}
                        {if(is_worthy_specifically("u", {server_id}))}
                            <option value="5">Удалить жалобу</option>
                        {/if}
                    </select>
                </div>
            </div>
        </div>
        <script>
            if($('#sentence').find('option').length === 0) {
            	$('#complaintCloseBlock').remove();
            }
        </script>
    {/if}
</div>

<div class="col-lg-6">
    <div class="block">
        <div class="block_head">
            Материал
        </div>
        <p class="mb-0"><b>Комментарий автора</b></p>
        <div class="noty-block with_code mt-1">{description}</div>

        <p class="mb-0"><b>Скриншоты</b></p>
        {if('{screens}' == '')}
            <small>Пользователь не прикрепил скриншотов</small>
        {else}
            {screens}
        {/if}

        <p class="mb-0 mt-2"><b>Демо</b></p>
        {if('{demo}' == '')}
            <small>Пользователь не указал ссылку на демо</small>
        {else}
            <a class="btn btn-outline-primary" href="{demo}" target="_blank">
                Скачать
            </a>
        {/if}
    </div>
</div>

<div class="col-lg-12">
    <div class="block">
        <div class="block_head">
            Комментарии
        </div>

        {if(is_auth() && '{sentence}' == '0')}
			<textarea id="text" maxlength="500"></textarea>

			<div class="smile_input_forum mt-3">
				<input id="send_btn" class="btn btn-primary" type="button" onclick="sendComplaintComment({id});" value="Отправить">
				<div id="smile_btn" class="smile_btn" data-container="body" data-toggle="popover" data-placement="top" data-content="empty"></div>
			</div>

            <script>
				$(document).ready(function () {
					init_tinymce(
						'text',
						'lite',
						"{file_manager_theme}",
						"{file_manager}",
						"{{md5($conf->code)}}"
					);
					get_smiles('#smile_btn', 1);
				});

				$('#smile_btn').popover({html: true, animation: true, trigger: "click"});
				$('#smile_btn').on('show.bs.popover', function () {
					$(document).mouseup(function (e) {
						var container = $('.popover-body');
						if (container.has(e.target).length === 0) {
							$('#smile_btn').popover('hide');
							selected = 'gcms_smiles';
						}
					});
				});

				function set_smile(elem) {
					var smile = '<img src="' + $(elem).attr('src') + '" class="g_smile" height="20px" width="20px">';
					tinymce.activeEditor.insertContent(smile);
					$('#smile_btn').popover('hide');
					selected = 'gcms_smiles';
				}
            </script>
        {/if}

        <div id="comments" class="mt-3">
            <div class="loader"></div>
            <script>loadComplaintComments({id});</script>
        </div>
    </div>
</div>