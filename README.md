# LograhPHP - Send exceptions from PHP to Telegram

[![Total Downloads](https://img.shields.io/packagist/dt/codelikesuraj/lograh-php.svg)](https://packagist.org/packages/codelikesuraj/lograh-php)

LograhPHP sends your exceptions to Telegram chats, channels or groups.

## Installation

Install the latest version with

```bash
$ composer require codelikesuraj/lograh-php
```

## Basic Usage

```php
<?php

use Codelikesuraj\LograhPHP\Logger;

// initialize logger with your Telegram bot credentials
$logger = new Logger(
    appName: "unique_name_to_identify_your_app",
    botToken: "api_key_generad_from_your_bot",
    chatId: "id_of_your_chat_or_channel_or_group"
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

## About

### Requirements

- LograhPHP works with PHP 8.0 or above.

### Author

Abdulbaki Suraj - <codelikesuraj> - <http://twitter.com/fliplikesuraj><br />

### License

LograhPHP is licensed under the MIT License.
