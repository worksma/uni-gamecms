<div class="col-md-6">
    Название группы
    <input value="{name}" type="text" class="form-control mb-10" id="name{id}" maxlength="30" autocomplete="off" placeholder="Введите название">

    Права группы
    <input value="{rights}" type="text" class="form-control mb-10" id="rights{id}" maxlength="512" autocomplete="off" placeholder="Введите флаги">

    <div class="row">
        <div class="col-md-3">
            Цвет
            <input value="{color}" type="text" class="form-control mb-10" id="color{id}">
        </div>
        <div class="col-md-6">
            Дополнительный стиль
            <input value="{style}" type="text" class="form-control mb-10" id="style{id}" maxlength="240" placeholder="Код CSS">
        </div>
        <div class="col-md-3">
            Готовые варианты

            <button class="btn btn-default btn-block" type="button" data-toggle="collapse" data-target="#stylesExamples{id}">
                Открыть
            </button>
        </div>

        <div class="col-md-12 collapse" id="stylesExamples{id}">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="color: #FF8300"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="color: #00FF87;font-style: italic;"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="color: #FF00A7;font-weight: bold;"
                            >
                                Пример
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="color: #2A5EC1;text-decoration: line-through;"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #FF6C00;
										text-shadow: 1px 1px 6px rgb(255, 108, 0);
										font-weight: bold;
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #000000;
										font-weight: bold;
										text-shadow: 1px 1px 1px rgb(255, 0, 0), 2px 2px 1px rgb(255, 0, 0);
										letter-spacing: 2px;
									"
                            >
                                Пример
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #F574B9;
                                        background-color: #F574B9;
                                        background-image: linear-gradient( 64.5deg,  rgba(245,116,185,1) 14.7%, rgba(89,97,223,1) 88.7% );
                                        background-clip: border-box;
                                        -webkit-background-clip: text;
                                        -webkit-text-fill-color: transparent;
                                        font-weight: bold;
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #FA8BFF;
                                        background-color: #FA8BFF;
                                        background-image: linear-gradient(45deg, #FA8BFF 0%, #2BD2FF 52%, #2BFF88 90%);
                                        background-clip: border-box;
                                        -webkit-background-clip: text;
                                        -webkit-text-fill-color: transparent;
                                        font-weight: bold;
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #FBDA61;
                                        background-color: #FBDA61;
                                        background-image: linear-gradient(45deg, #FBDA61 0%, #FF5ACD 100%);
                                        background-clip: border-box;
                                        -webkit-background-clip: text;
                                        -webkit-text-fill-color: transparent;
                                        font-weight: bold;
									"
                            >
                                Пример
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #F4D03F;
										background-color: #F4D03F;
                                        background-image: linear-gradient(132deg, #F4D03F 0%, #16A085 100%);
                                        background-clip: border-box;
                                        -webkit-background-clip: text;
                                        -webkit-text-fill-color: transparent;
                                        font-weight: bold;
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #C20027;
                                        background-color: rgba(194,0,39,1);
                                        background-image: linear-gradient( 89.4deg,  rgba(194,0,39,1) 0.8%, rgba(10,35,104,1) 99.4% );
                                        background-clip: border-box;
                                        -webkit-background-clip: text;
                                        -webkit-text-fill-color: transparent;
                                        font-weight: bold;
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #4776E6;
										background-color: #4776E6;
                                        background-image: linear-gradient(to right, #4776E6 0%, #8E54E9  51%, #4776E6  100%);
                                        background-clip: border-box;
                                        -webkit-background-clip: text;
                                        -webkit-text-fill-color: transparent;
                                        font-weight: bold;
									"
                            >
                                Пример
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #000000;
										background: url(../files/groups_styles/1.gif);
										font-weight: bold;
										-webkit-text-fill-color: transparent;
										-webkit-background-clip: text;
										text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.11);
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #000000;
										background: url(../files/groups_styles/1.gif);
										font-weight: bold;
										-webkit-text-fill-color: transparent;
										-webkit-background-clip: text;
										text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.11);
										background-size: cover;
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #BD2B2B;
										background: url(../files/groups_styles/2.gif) left no-repeat;
										font-weight: bold;
										text-shadow: #FF0000 1px 1px 6px;
									"
                            >
                                Пример
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #FA5FE8;
										background: url(../files/groups_styles/3.gif) left no-repeat;
										font-weight: bold;
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #0058FF;
										background: url(../files/groups_styles/4.gif) left no-repeat;
										font-weight: bold;
										text-shadow: rgba(0, 0, 0, 0.05) 1px 1px 3px;
									"
                            >
                                Пример
                            </a>
                            <br>
                            <a
                                    class="c-p"
                                    onclick="setGroupStyle('{id}', this);"
                                    style="
										color: #8000FF;
										background: url(../files/groups_styles/5.gif) left no-repeat;
										font-weight: bold;
										text-shadow: rgba(0, 0, 0, 0) 1px 1px 3px;
									"
                            >
                                Пример
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div id="colorpicker{id}"></div>
    <script>
		$(document).ready(function () {
			$('#colorpicker{id}').farbtastic('#color{id}');
		});
    </script>
</div>
<div class="col-md-12">
    {if('{id}' == '')}
        <div id="result"></div>
        <button class="btn2" onclick="add_group();">Добавить</button>
    {else}
        <div id="result{id}"></div>
        <button class="btn2" onclick="edit_group({id});">Изменить</button>
        <button class="btn2 btn-cancel" onclick="dell_group({id});">Удалить</button>
        <hr>
    {/if}
</div>

