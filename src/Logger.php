<?php

declare(strict_types=1);

namespace Codelikesuraj\LograhPHP;

class Logger
{
    /** Telegram bot API */
    private const BOT_API  = "https://api.telegram.org/bot";

    /** Name of this app */
    private string $appName;

    /** API key generate from telegram bot */
    private string $botToken;

    /** chat_id of user or channel_id of group/channel */
    private string $chatId;

    /** disable audio alert on telegram */
    private bool $disableNotification;

    /** disable web page preview for embedded links */
    private bool $disableWebPagePreview;

    /** number of retries */
    private int $retries;

    public function __construct(
        string $appName,
        string $botToken,
        string $chatId,
        bool $disableNotification = true,
        bool $disableWebPagePreview = true,
        int $retries = 3
    ) {
        if (!extension_loaded('curl')) {
            throw new \Exception("Curl extension is missing");
        }

        $this->appName = $appName;
        $this->botToken = $botToken;
        $this->chatId = $chatId;
        $this->disableNotification = $disableNotification;
        $this->disableWebPagePreview = $disableWebPagePreview;
        $this->retries = $retries;
    }

    protected static function generateTraceLine(\Throwable $exception)
    {
        foreach ($exception->getTrace() as $key => $stackPoint) {
            $trace[] = sprintf(
                "#%s %s(%s): %s(%s)",
                $key,
                $stackPoint['file'],
                $stackPoint['line'],
                $stackPoint['function'],
                implode(', ', $stackPoint['args'])
            );
        }
        $trace[] = '#' . ++$key . ' {main}';

        return $trace ?? [];
    }

    protected static function generateTextSummary(\Throwable $exception)
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

    public function generateJsonSummary(\Throwable $exception): string
    {
        $data = [
            'app' => $this->appName,
            'timestamp' => date("Y-m-d H:i:s T O"),
            'message' => self::generateTextSummary($exception),
            'stack trace' => self::generateTraceLine($exception),
        ];

        $json = "```json \n";
        $json .= json_encode($data, JSON_PRETTY_PRINT);
        $json .= "\n```";

        return $json;
    }

    public function sendReport(\Throwable $exception): void
    {
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
                throw new \RuntimeException(sprintf("Curl error (code %d): %s", curl_errno($ch), curl_error($ch)));
            }
        }

        if (!is_string($resp)) {
            throw new \RuntimeException('Telegram API error - Description: Unrecognized response');
        }

        $result = json_decode($resp, true);
        if ($result['ok'] === false) {
            throw new \RuntimeException(sprintf("Telegram API error - Description: %s", $result['description']));
        }
    }
}
