<?php

namespace Eyewitness\Eye\Notifications\Drivers;

use Exception;
use GuzzleHttp\Client;
use Eyewitness\Eye\Eye;

class Slack implements DriverInterface
{
    /**
     * Fire a notification.
     *
     * @param  \Eyewitness\Eye\Repo\Notifications\Recipient  $recipient
     * @param  \Eyewitness\Eye\Notifications\Messages\MessageInterface  $message
     * @return void
     */
    public function fire($recipient, $message)
    {
        $headers = [
            'connect_timeout' => 30,
            'timeout' => 30,
            'verify' => false,
            'debug' => false,
            'Content-Type' => 'application/json'
        ];

        $body = [
            'text' => $message->title(),
            'icon_url' => 'https://eyewitness.io/img/logo/icon_192_192.png',
            'username' => 'Eyewitness.io | '.config('app.name', 'Your Application'),
            'attachments' => [[
                'text' => $message->markupDescription(),
                'color' => $message->isError() ? 'danger' : 'good',
                'fields' => $this->parseMeta($message->meta())
            ]]
        ];

        try {
            app(Client::class)->post($recipient->address, [
                'body' => json_encode($body),
                'headers' => $headers
            ]);
        } catch (Exception $e) {
            app(Eye::class)->logger()->error('Unable to send Slack notification', $e);
        }
    }

    /**
     * Format the meta content for Slack attachments.
     * 
     * @param  array  $meta
     * @return array
     */
    protected function parseMeta($meta)
    {
        $fields = [];
        
        foreach ($meta as $key => $value) {
            $fields[] = [
                'title' => $key,
                'value' => $value,
            ];
        }
        
        return $fields;
    }
}
