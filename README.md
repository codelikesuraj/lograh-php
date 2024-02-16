# LograhPHP - Send exceptions from PHP to Telegram

[![Total Downloads](https://img.shields.io/packagist/dt/codelikesuraj/lograh-php.svg)](https://packagist.org/packages/codelikesuraj/lograh-php)

LograhPHP sends your exceptions to Telegram chats, channels or groups.

## Requirements
- PHP 8.0 or above.
- php_curl extension enabled.
- A Telegram bot's access token and chat/channel/group id. Generate a Telegram bot with https://telegram.me/BotFather.

## Installation
```bash
$ composer require codelikesuraj/lograh-php
```

## Basic Usage

### Sample code
```php
<?php

use Codelikesuraj\LograhPHP\Logger;

// initialize logger with your Telegram bot credentials
$logger = new Logger(
    appName: "unique_name_to_identify_your_app",
    botToken: "api_key_generated_from_your_telegram_bot",
    chatId: "id_of_your_telegram_chat_or_channel_or_group"
);

try {
    // code that may generate an exception
    ...
} catch (\Throwable $exception) {
    $logger->reportException($exception);
    // further processing
    ...
}
```
### Sample response
```json
{
    "app": "unique_name_to_identify_your_app",
    "timestamp": "####-##-## ##:##:## TMZ +####",
    "message": "Uncaught exception: 'exception_name' with message 'exception_message' in \/path\/to\/folder\/file.php:#",
    "stack trace": [
        "#0 {main}"
    ]
}
```
## Author
Abdulbaki Suraj - <http://twitter.com/fliplikesuraj>

## License
LograhPHP is licensed under the MIT License.
