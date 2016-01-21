# Introduction #

Класс для чтения конфигурации из таблицы БД


# Details #
Параметры:
  * $table - имя таблицы (возможно с указанием БД и схемы)
  * $keyFields - ключевые поля. Поля по которым группируются выбранные значения. Если не задано, используется набор полей составляющих PRIMARY KEY
  * $valueFields - поля значений. Если не задано то используются все поля в базе (за исключением ключевых). Единственное поле становится единственным значением.
  * $keyDelimiter - разделитель ключей. Если ключ содержит разделитель то он разбивается и значения группируются по соответствующих частям ключа. По умолчанию разделитель точка (.)


# Примеры #
> Допустим мы используем для хранения конфигурации такую таблицу. Будем считать что PRIMARY KEY не определен.

|**key**|**subkey**|**value**|
|:------|:---------|:--------|
|session|save\_path|/tmp     |
|session|name      |SessID   |
|session|handler.class|mySessionClass|
|session|handler.param|someSessionClassParam|
|site   |title     |My Site Title|


Без указания ключевых полей конструкция работать не будет, будет брошено исключение.

Если передать $keyFields = 'key' то в конфигурации будет присутствовать только 2 последних строки, значения которых можно получить так:
```
$config->session->subkey; //'handler.param'
$config->session->value; //'someSessionClassParam'
$config->site->subkey; //'title'
$config->site->value; //'My Site Title'
```

Поэтому имеет смысл или полностью описать смысл таблицы в ее определении указав первичный ключ (по первым двум полям в данном случае), или передать правильный параметр в $keyFields, в данном случае array('key','subkey'). Тогда можно обращаться к конфигу так:
```
$config->session->save_path; //'/tmp'
$config->session->handler->class; //'mySessionClass'
$config->session->handler->param; //'someSessionClassParam'
$config->site->title; //'My Site Title'
```