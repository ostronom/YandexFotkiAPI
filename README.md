# YandexFotkiAPI

Врядли это будут юзать, тем более импортные гуманоиды, поэтому дальше по-русски.

## Как пользоваться

### Настройка

Создаём инстанс API и приписываем ему юзера.

    $api = new YandexFotkiAPI\API();
    $api->setUser('maxum');

Каждый инстанс API завязан на своего юзера и может получать только его альбомы или фотографии.
Если надо работать с закрытыми ресурсами, то

    $api->setPassword('password');

Все объекты отражают какой-либо из Atom-фидов протокола и чтобы получить какое-либо/коллекцию полей
к объекту нужно обратиться как к хешу.

### Коллекция альбомов

Список альбомов `API::getAlbumsCollection` содержит основные поля
предоставляет возможность ходить по нему `foreach`-ем.

    $albums = $api->getAlbumsCollection();
    foreach ($albums as $album) {
    ...
    }

### Альбом

Помимо возможности получить альбом из коллекции, его можно запросить напрямую по идентификатору.

    $album = $api->getAlbum(<album_id>);

album_id -- это *не* внутренний id яндекса из фида, это его цифровая часть (последняя).

### Коллекция фотографий

Альбом может содержать фотографии, которые вытаскиваются аналогично коллекции альбомов:

    foreach ($album->getPhotoCollection() as $photo) {
    ...
    }

### Фотография

Также как и альбом, отдельную фотографию можно вытащить по id:

    $photo = $api->getPhoto(<photo_id>);

### Изменение альбома/фотографии

Те поля, которые можно изменять в соответствии с API Яндекса, можно изменять и в "хэше"-объекте,
например:

    $photo['f:protected'] = true;

Для сохранение изменений:

    $photo->commit();

Аналогично для альбомов.

Удалить фотографию или альбом:

    $photo->delete();

## Особенности реализации

- API намеренно сделан ленивым. Именно, пока вы не начнёте реально работать с объектом, он не "вычислиться":
не загрузится с сервера (если он, конечно, не пришел как часть бОльшего ответа), не распарсится и т.п. 
- Итераторы работают над коллекцией с автоматической выгрузкой "страниц" ответов, вообще нет необходимости отслеживать
это как-либо самому.
- Для альбомов и фотографий есть хелпер-метод `getParentId`, который вовращает яндекс-идентификатор альбома, в котором 
содержится данный альбом или фотграфия или null, если такого нет (для альбома).