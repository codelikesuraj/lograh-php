<?php

declare(strict_types=1);

namespace Codelikesuraj\LograhPHP;

use Exception;
use RuntimeException;
use Throwable;

class Logger
{
    /**
     * Base url for the bot API
     */
    private const BOT_API  = "https://api.telegram.org/bot";

    /**
     * A unique name for the app/project
     */
    private string $appName;

    /**
     * The API key generate from the telegram bot.
     * Create a bot and get its access token with https://telegram.me/BotFather.
     */
    private string $botToken;

    /**
     * Telegram chat_id of the user, channel or group
     */
    private string $chatId;

    /**
     * Disable audio alerts on Telegram
     */
    private bool $disableNotification;

    /**
     * Disable web page preview for embedded links
     */
    private bool $disableWebPagePreview;

    /**
     * Array of exceptions to ignore
     */
    private array $ignoredExceptions = [];

    /**
     * Number of retries when sending exception
     */
    private int $retries;

    /**
     * @param  string    $appName                Unique app for app/project
     * @param  string    $botToken               Telegram bot access token
     * @param  string    $chatId                 Telegram chat, channel or group id
     * @param  bool      $disableNotification    Disable audio alerts on Telegram
     * @param  bool      $disableWebPagePreview  Disable web page preview for embedded links
     * @param  int       $disableWebPagePreview  Disable web page preview for embedded links
     * @throws Exception If the curl extension is missing
     */
    public function __construct(
        string $appName,
        string $botToken,
        string $chatId,
        bool $disableNotification = true,
        bool $disableWebPagePreview = true,
        int $retries = 3
    ) {
        if (!extension_loaded('curl')) {
            throw new Exception("Curl extension is missing");
        }

        $this->appName = $appName;
        $this->botToken = $botToken;
        $this->chatId = $chatId;
        $this->disableNotification = $disableNotification;
        $this->disableWebPagePreview = $disableWebPagePreview;
        $this->retries = $retries;
    }

    /**
     * Sets exceptions to be ignored
     * 
     * @param  array     $exceptions
     * @return self
     * @throws Exception If any class is not defined
     */
    public function ignore(array $exceptions): self
    {
        foreach ($exceptions as $exception) {
            if (!class_exists($exception, false)) {
                throw new Exception("Class not defined");
            }

            if (!in_array($exception, $this->ignoredExceptions)) {
                $this->ignoredExceptions[] = $exception;
            }
        }

        return $this;
    }

    /**
     * Generates an array for the stack trace
     * 
     * @param  Throwable $exception
     * @return array
     */
    protected static function generateStackTrace(Throwable $exception): array
    {
        foreach ($exception->getTrace() as $key => $stackPoint) {
            $trace[] = sprintf(
                "#%s %s(%s): %s()",
                $key,
                $stackPoint['file'],
                $stackPoint['line'],
                $stackPoint['function']
            );
        }
        $trace[] = '#' . (isset($key) ? ++$key : '0') . ' {main}';

        return $trace;
    }

    /**
     * Generates a text summary of the exception
     * 
     * @param  Throwable $exception
     * @return string
     */
    protected static function generateTextSummary(Throwable $exception): string
    {
        return sprintf(
            "Uncaught exception: '%s' with message '%s' in %s:%s",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getFile(),
            $exception->getLine()
        );
    }

    /**
     * Generates a json summary of the exception including
     * the text summary and stack trace
     * 
     * @param  Throwable $exception
     * @return string
     */
    public function generateJsonSummary(Throwable $exception): string
    {
        $data = [
            'app' => $this->appName,
            'timestamp' => date(DATE_RFC2822),
            'summary' => self::generateTextSummary($exception),
            'stack trace' => self::generateStackTrace($exception),
        ];

        $json = "```json \n";
        $json .= json_encode($data, JSON_PRETTY_PRINT);
        $json .= "\n```";

        return $json;
    }

    /**
     * Sends the caught exception to Telegram
     * 
     * @param  Throwable        $exception
     * @return void
     * @throws RuntimeException For any CURL or Telegram API error
     */
    public function report(Throwable $exception): void
    {
        if (in_array(get_class($exception), $this->ignoredExceptions)) {
            return;
        }

        $ch = curl_init();
        $url = sprintf("%s%s/sendMessage", self::BOT_API, $this->botToken);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type' => 'application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'text' => self::generateJsonSummary($exception, ['appName' => $this->appName], ['hi' => 'ad']),
            'chat_id' => $this->chatId,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => $this->disableWebPagePreview,
            'disable_notification' => $this->disableNotification,
        ]));

        while (true) {
            $resp = curl_exec($ch);
            if ($resp !== false) {
                curl_close($ch);
                break;
            }

            if (!$this->retries--) {
                throw new RuntimeException(sprintf("Curl error (code %d): %s", curl_errno($ch), curl_error($ch)));
            }
        }

        if (!is_string($resp)) {
            throw new RuntimeException('Telegram API error - Description: Unrecognized response');
        }

        $result = json_decode($resp, true);
        if ($result['ok'] === false) {
            throw new RuntimeException(sprintf("Telegram API error - Description: %s", $result['description']));
        }
    }
}
