# Введение
UNI GameCMS - это движок сайта, который позволит вам создать свою игровую социальную сеть, заточенную для монетизации и объединению игроков. В основе движка есть различные дополнения по типу форума, профиля, чата, платёжных систем, управления игровыми серверами и т.п.

# Системные требования
```
1. Сервер на Nginx или Apache.
2. Установленная версия PHP не ниже 7.4
3. База данных MySQL или её аналог.
4. Включенный модуль ZIP в настройках PHP
```

# Настройки Nginx
Для запуска сайта на Nginx требуется прописать в nginx.conf
```
location / {
  rewrite ^/(.*)$ /index.php?/$1 last;
}
```

# Платёжные системы (Payment systems)
1. Lava
2. FreeKassa
3. UnitPay
4. ROBOKASSA
5. WalletOne
6. InterKassa
7. WebMoney
8. Paysera
9. ЮMoney
10. Qiwi
11. LiqPay
12. AnyPay
13. AmaraPay

# Лицензии и благодарности (Licenses and commendations)
1. Основа для дизайна сайта [Bootstrap](https://getbootstrap.com/docs/4.0/about/license/)
2. TinyMCE under the GNU LESSER GENERAL PUBLIC LICENSE. Version 2.1, February 1999.
3. PHP License v3.01 PHP Group.
4. Trading Platform [WORKSMA](https://worksma.ru)
