# pusher
[![Latest Stable Version](http://www.maiguoer.com/haiaouang/pusher/stable.svg)](https://packagist.org/packages/haiaouang/pusher)
[![License](http://www.maiguoer.com/haiaouang/pusher/license.svg)](https://packagist.org/packages/haiaouang/pusher)

推送管理laravel包开发，用于管理多个推送第三方驱动

## 安装
在你的终端运行以下命令

`composer require haiaouang/pusher`

或者在composer.json中添加

`"haiaouang/pusher": "1.0.*"`

然后在你的终端运行以下命令

`composer update`

在配置文件中添加 config/app.php

```php
    'providers' => [
        /**
         * 添加供应商
         */
        Hht\Pusher\PusherServiceProvider::class,
    ],
```

## 依赖包

* haiaouang/support : https://github.com/haiaouang/support
* haiaouang/mipush : https://github.com/haiaouang/mipush
