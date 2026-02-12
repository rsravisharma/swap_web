<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;

class FCMService
{
    private $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = config('firebase.credentials');
            $projectId = config('firebase.project_id');

            if (empty($credentialsPath) || !file_exists($credentialsPath)) {
                throw new \Exception('Firebase credentials file not found');
            }

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withProjectId($projectId);

            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('FCMService initialization failed: ' . $e->getMessage());
            $this->messaging = null;
        }
    }

    /**
     * Send notification to a specific topic (V1 API)
     */
    public function sendToTopic(string $topic, array $notification, array $data = []): array
    {
        if (!$this->messaging) {
            return ['success' => false, 'error' => 'FCM not initialized'];
        }

        try {
            $firebaseNotification = Notification::create(
                $notification['title'] ?? 'Notification',
                $notification['body'] ?? ''
            );

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($firebaseNotification)
                ->withData($data);

            // 🔥 FIXED: Android specific config - use fromArray instead of create()
            if (isset($notification['sound'])) {
                $androidConfig = AndroidConfig::fromArray([
                    'priority' => 'high',
                    'notification' => [
                        'sound' => $notification['sound'],
                        'channel_id' => 'default',
                    ],
                ]);
                $message = $message->withAndroidConfig($androidConfig);
            }

            $messageId = $this->messaging->send($message);

            Log::info('FCM Topic Notification Sent', [
                'topic' => $topic,
                'message_id' => $messageId,
                'data' => $data
            ]);

            return [
                'success' => true,
                'message_id' => $messageId,
                'topic' => $topic
            ];

        } catch (\Exception $e) {
            Log::error('FCM Topic Send Error', [
                'topic' => $topic,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to specific device token (V1 API)
     */
    public function sendToDevice(string $token, array $notification, array $data = []): array
    {
        if (!$this->messaging) {
            return ['success' => false, 'error' => 'FCM not initialized'];
        }

        try {
            $firebaseNotification = Notification::create(
                $notification['title'] ?? 'Notification',
                $notification['body'] ?? ''
            );

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($firebaseNotification)
                ->withData($data);

            // 🔥 FIXED: Android config for better delivery - use fromArray instead of create()
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'chat_messages',
                    'default_sound' => true,
                    'notification_priority' => 'PRIORITY_HIGH',
                ],
            ]);

            $message = $message->withAndroidConfig($androidConfig);

            $messageId = $this->messaging->send($message);

            Log::info('FCM Device Notification Sent', [
                'token' => substr($token, 0, 20) . '...',
                'message_id' => $messageId
            ]);

            return [
                'success' => true,
                'message_id' => $messageId
            ];

        } catch (\Exception $e) {
            Log::error('FCM Device Send Error', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send to multiple topics
     */
    public function sendToTopics(array $topics, array $notification, array $data = []): array
    {
        $results = [];
        foreach ($topics as $topic) {
            $results[$topic] = $this->sendToTopic($topic, $notification, $data);
        }
        return $results;
    }

    /**
     * Subscribe tokens to topic
     */
    public function subscribeToTopic(string $topic, array $tokens): array
    {
        if (!$this->messaging) {
            return ['success' => false, 'error' => 'FCM not initialized'];
        }

        try {
            $this->messaging->subscribeToTopic($topic, $tokens);
            return ['success' => true, 'topic' => $topic, 'tokens' => count($tokens)];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Unsubscribe tokens from topic
     */
    public function unsubscribeFromTopic(string $topic, array $tokens): array
    {
        if (!$this->messaging) {
            return ['success' => false, 'error' => 'FCM not initialized'];
        }

        try {
            $this->messaging->unsubscribeFromTopic($topic, $tokens);
            return ['success' => true, 'topic' => $topic, 'tokens' => count($tokens)];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if service is ready
     */
    public function isReady(): bool
    {
        return $this->messaging !== null;
    }
}
