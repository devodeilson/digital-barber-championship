<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;

class PushNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    public function sendToUser(User $user, string $title, string $body, array $data = [])
    {
        if (!$user->device_token) {
            return false;
        }

        $message = CloudMessage::withTarget('token', $user->device_token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        try {
            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send push notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = [])
    {
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        try {
            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send topic notification', [
                'topic' => $topic,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendToMultipleUsers(array $userIds, string $title, string $body, array $data = [])
    {
        $tokens = User::whereIn('id', $userIds)
            ->whereNotNull('device_token')
            ->pluck('device_token')
            ->toArray();

        if (empty($tokens)) {
            return false;
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        try {
            $this->messaging->sendMulticast($message, $tokens);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send multicast notification', [
                'user_ids' => $userIds,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 