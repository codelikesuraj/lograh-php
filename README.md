# LograhPHP - Send exceptions from PHP to Telegram



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
    // optional list of exceptions to be ignored
    $logger->ignore([ExceptionA::class, ExceptionB::class]);
    
    // send exception to Telegram using any
    // of the following methods
    $logger->reportAsText($exception);
    $logger->reportAsJson($exception);
    $logger->reportAsJsonWithStackTrace($exception);
    
    // further processing
    ...
}
```
### Sample response message sent to Telegram
#### Report as TEXT
app: unique_name_to_identify_your_app<br>
timestamp: 0000-00-00 00:00:00 +0000<br>
"summary": Uncaught exception: 'exception_name' with message 'exception_message' in \/path\/to\/folder\/file.php:#
#### Report as JSON
```json
{
    "app": "unique_name_to_identify_your_app",
    "timestamp": "0000-00-00 00:00:00 +0000",
    "summary": "Uncaught exception: 'exception_name' with message 'exception_message' in \/path\/to\/folder\/file.php:#",
}
```
#### Report as JSON WITH STACK TRACE
```json
{
    "app": "unique_name_to_identify_your_app",
    "timestamp": "0000-00-00 00:00:00 +0000",
    "summary": "Uncaught exception: 'exception_name' with message 'exception_message' in \/path\/to\/folder\/file.php:#",
    "stack trace": [
        "#0 {main}"
    ]
}
```
## Author
Abdulbaki Suraj - <http://twitter.com/fliplikesuraj>

## License
LograhPHP is licensed under the MIT License.
