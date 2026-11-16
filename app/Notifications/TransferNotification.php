<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TransferNotification extends Notification
{
    public function __construct(
        public int $transferId,
        public string $kind,
        public string $title,
        public string $message,
        public string $url,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transfer_id' => $this->transferId,
            'kind' => $this->kind,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}