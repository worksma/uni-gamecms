{configuration}

// Оформление шаблона сайта
// 0 - стандартный (белый)
// 1 - дарк (тёмный)
// 2 - дарк 2 (в синих тонах)
{var:theme}1{/var}

// Оформление мониторинга
// 0 - стандартный (блочный)
// 1 - табличный
// 2 - табличный растянутый
{var:monitoringType}2{/var}

// Слайдер (850x360)
// -----------------
// 1й слайд
{var:slider[0]['title']} Магазин - продажа услуг в онлайн режиме. {/var}
{var:slider[0]['content']} В магазине Вы можете оплатить любую из представленных услуг и сразу же воспользоваться ею на игровом сервере. {/var}
{var:slider[0]['image']} ../templates/standart/img/slide-1.jpg {/var}
{var:slider[0]['link']} ../store {/var}
// 2й слайд
{var:slider[1]['title']} Забанили, но Вы считаете себя невиновным? {/var}
{var:slider[1]['content']} Раздел разбана поможет Вам обжаловать несправедливый бан. Оформите заявку, приложите скриншоты и ожидайте разбана, если Вы невиновны. {/var}
{var:slider[1]['image']} ../templates/standart/img/slide-2.jpg {/var}
{var:slider[1]['link']} ../bans/ {/var}
// 3й слайд
{var:slider[2]['title']} Есть вопрос? Обратитесь к администрации. {/var}
{var:slider[2]['content']} Если у Вас имеются вопросы, Вы можете открыть тикет в разделе поддержки и своевременно получить ответ администрации на него. {/var}
{var:slider[2]['image']} ../templates/standart/img/slide-3.jpg {/var}
{var:slider[2]['link']} ../support/ {/var}


// Вертикальное меню в сайдбаре
// ----------------------------
//Магазин услуг
{var:vertical_menu[0]['name']} Магазин услуг {/var}
{var:vertical_menu[0]['link']} ../store {/var}
//Новости проекта
{var:vertical_menu[1]['name']} Новости проекта {/var}
{var:vertical_menu[1]['link']} ../news/ {/var}
//Форум
{var:vertical_menu[2]['name']} Форум {/var}
{var:vertical_menu[2]['link']} ../forum/ {/var}
//Пользователи
{var:vertical_menu[3]['name']} Пользователи {/var}
{var:vertical_menu[3]['link']} ../users {/var}
//События проекта
{var:vertical_menu[4]['name']} События проекта {/var}
{var:vertical_menu[4]['link']} ../events {/var}
//Поддержка
{var:vertical_menu[5]['name']} Поддержка {/var}
{var:vertical_menu[5]['link']} ../support/ {/var}


// Вертикальное меню(навигация) в футере
// -------------------------------------
//Главная страница
{var:vertical_menu_2[0]['name']} Главная страница {/var}
{var:vertical_menu_2[0]['link']} ../ {/var}
//Новости проекта
{var:vertical_menu_2[1]['name']} Новости проекта {/var}
{var:vertical_menu_2[1]['link']} ../news/ {/var}
//Магазин услуг
{var:vertical_menu_2[2]['name']} Магазин услуг {/var}
{var:vertical_menu_2[2]['link']} ../store {/var}
//Форум
{var:vertical_menu_2[3]['name']} Форум {/var}
{var:vertical_menu_2[3]['link']} ../forum/ {/var}
//Поддержка
{var:vertical_menu_2[4]['name']} Поддержка {/var}
{var:vertical_menu_2[4]['link']} ../support/ {/var}


// Вертикальное меню(проект) в футере
// ----------------------------------
//Пользователи
{var:vertical_menu_3[0]['name']} Пользователи {/var}
{var:vertical_menu_3[0]['link']} ../users {/var}
//Администраторы
{var:vertical_menu_3[1]['name']} Администраторы {/var}
{var:vertical_menu_3[1]['link']} ../admins {/var}
//Список банов
{var:vertical_menu_3[2]['name']} Список банов {/var}
{var:vertical_menu_3[2]['link']} ../banlist {/var}
//Заявки на разбан
{var:vertical_menu_3[3]['name']} Заявки на разбан {/var}
{var:vertical_menu_3[3]['link']} ../bans {/var}
//Игровая статистика
{var:vertical_menu_3[4]['name']} Игровая статистика {/var}
{var:vertical_menu_3[4]['link']} ../stats {/var}


// Вертикальное меню(полезные ссылки) в футере
// -------------------------------------------
//Согласие на обработку персональных данных
{var:vertical_menu_4[0]['name']} Об обработке персональных данных {/var}
{var:vertical_menu_4[0]['link']} ../processing-of-personal-data {/var}
//Политика конфиденциальности
{var:vertical_menu_4[1]['name']} Политика конфиденциальности {/var}
{var:vertical_menu_4[1]['link']} ../privacy-policy {/var}
//Правила проекта
{var:vertical_menu_4[2]['name']} Правила проекта {/var}
{var:vertical_menu_4[2]['link']} ../pages/rules {/var}
//База знаний
{var:vertical_menu_4[3]['name']} База знаний {/var}
{var:vertical_menu_4[3]['link']} ../pages/baza_znaniy {/var}


// Баннеры в футере (88x31)
// ------------------------
//banner 1
{var:footer_banners[0]['link']} https://worksma.ru/ {/var}
{var:footer_banners[0]['img']} ../templates/standart/img/88x31.png{/var}
//banner 2
{var:footer_banners[1]['link']} https://worksma.ru/ {/var}
{var:footer_banners[1]['img']} ../templates/standart/img/88x31.png{/var}
//banner 3
{var:footer_banners[2]['link']} https://worksma.ru/ {/var}
{var:footer_banners[2]['img']} ../templates/standart/img/88x31.png{/var}


// Описание проекта в футере
// -------------------------
{var:footer_description}
Рады видеть Вас на нашем игровом проекте, посвященном легендарной игре Counter-Strike. На наших серверах Вы можете насладиться приятной игрой в кругу хороших игроков и под руководством отзывчивой администрации. 
{/var}


{/configuration}