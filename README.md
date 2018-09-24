## ReCaptcha для Битрикс

### Установка через composer
```
composer install vf92/recaptcha-bitrix
```
или добавляем в composer.json
```
{
...
"require": {
    ...
    "vf92/recaptcha-bitrix": "^1.3.5",
    ...
    }
    ...
}
```

### Использование
```php
use Vf92\ReCaptcha\ReCaptcha;
/** Подключаем Js просто - обычно хватает */
ReCaptcha::addJs(ReCaptcha::INIT_RECAPTCHA_BY_JS);

/** Подключаем Js async defer - обычно хватает */
ReCaptcha::addJsAsync(ReCaptcha::INIT_RECAPTCHA_BY_JS);

/** проверяем капчу */
$isChecked = ReCaptcha::checkCaptcha($secretKey);//bool

/** возвращает строку с капчей 
если аяксом грузится - подключит скрипт прямо в строку
можно добавить дополнительные классы
*/
ReCaptcha::getCaptchaStatic($key, $additionalClass = '', $isAjax = false)//string 

/** через конструктор */
$recaptcha = new ReCaptcha($key, $secretKet);
/** возвращает строку с капчей 
если аяксом грузится - подключит скрипт прямо в строку
можно добавить дополнительные классы
*/
$recaptcha->getCaptcha($additionalClass = '', $isAjax = false);//string
/** возвращает публичный ключ */
$recaptcha->getParams();//array
/** проверяем капчу можно явно передать значение капчи*/
$recaptcha->check($recaptcha='');//bool
```
