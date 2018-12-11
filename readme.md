# Learning. Create a Ad Site

Framework: Laravel 5.7

Список изменений:
-

#### Бэкенд

Панель администратора

Работа с пользователями:

- Фильтр пользователей
- Изменение статуса
- ИЗменение прав

Регионы:

- На главной странице регионов выводятся только корневые регионы
- Добавление под-регионов с негораниченным числом вложенности
- Наследование регионов
- Список наследников выводится внутри каждого родительского региона

Категории:

- Категории написаны по принципу Nested Set
- Все дерево категорий выводится на главной странице помечая глубину вложенности
- Возможность поднимать/отпускать категории в списке
- "Атрибуты работают через Категории"
- Добавление атрибутов категории с возможностью указания: названия, типа, сортировки, 
вариантов выбора и параметра "Обязательно".
- Атрибуты привязанны к ее родительской категории.
- Атрибуты наследуются от всех своих родительских категорий

Объявления:

- Редактирование объявлений пользователя
- Проверка объявления и его публикация
- Возможность отклонить объявление с указанием причины

Страницы: 

- Страницы по принципу Nested Set
- Удобный текстовый редактор при создании страницы
- Интерактивная загрузка изображений на сервер
- Фильтрация тэгов и другого кода (Purifier)

Система тикетов:

- Принятие тикета, закрытие и тд
- Переписка с пользователем создавшим тикет 
- Отслеживание статусов изменения тикетов (открытие, закрытие и тд)

Прочее:

- Быстрая возможность добавить новый сервис для отправки смс, выбор драйвера для отправки
- Поиск объявлений по Elasticsearch

#### Фронтенд

Личный кабинет:

- Добавление номера телефона в профиле
- Подтверждение нового номера телефона через смс
- Двухфакторная аутентификация через смс (если он был активирован и функция включена)
- Добавление объявления
- Редактирование объявления

Система объявлений:

- Добавление аттрибутов (которые задаются в категориях) и фотографий к объявлениям
- Загрузка фотографий
- Избранные объявления
- Поиск объявлений по атрибутам

Система баннеров:

- Размещение баннеров на сайте с оплатой за количество показов
- Статистика рекламодеталея с показами и кликами
- Перемодерация баннеров
- Интеграция оплаты через робокассу

Система страниц:

- Просмотр страниц 
- Система ЧПУ для страниц и для вложенных страниц

Система тикетов:

- Создание нового тикета для связи с администрацией
- Добавление новых сообщений к текущему тикету

Меню:

- Верхнее меню (формируется на основе страниц, по вложенности)

Система уведомлений и очередей:

- Добавлена система очередей и событий
- Уведомления пользователя по смс и телефону при принятии объявления модератором (с очередью)

Прочее:

- Система ЧПУ, выводит всю цепочку Региона и Категории. Прим: /moskva/balashiha/kvartiri/arenda/, число вложенности
 неограниченно и зависит от вложенности самого региона и категории.
- ЧПУ кэшируются и обновляются при любых изменениях с категорией или регионом 
- Система статистики и контроля очередей Horizon 
