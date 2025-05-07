<?php
namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationHandler {
    private static $mailer;
    private static $pusher;
    
    public static function init() {
        // Initialize email
        self::$mailer = new PHPMailer(true);
        self::$mailer->isSMTP();
        self::$mailer->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        self::$mailer->SMTPAuth = true;
        self::$mailer->Username = getenv('SMTP_USERNAME');
        self::$mailer->Password = getenv('SMTP_PASSWORD');
        self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        self::$mailer->Port = getenv('SMTP_PORT') ?: 587;
        self::$mailer->CharSet = 'UTF-8';
        
        // Initialize Pusher for realtime notifications
        self::$pusher = new \Pusher\Pusher(
            $_ENV['PUSHER_APP_ID'],
            $_ENV['PUSHER_KEY'],
            $_ENV['PUSHER_SECRET'],
            ['cluster' => $_ENV['PUSHER_CLUSTER']]
        );
    }
    
    public static function sendEmail($to, $subject, $body, $attachments = []) {
        try {
            if (!isset(self::$mailer)) {
                self::init();
            }
            
            self::$mailer->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME') ?: 'HR System');
            self::$mailer->addAddress($to);
            self::$mailer->isHTML(true);
            self::$mailer->Subject = $subject;
            self::$mailer->Body = $body;
            
            foreach ($attachments as $attachment) {
                self::$mailer->addAttachment($attachment);
            }
            
            self::$mailer->send();
            return true;
        } catch (Exception $e) {
            Logger::error("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    public static function sendRealtimeNotification($channel, $event, $data) {
        try {
            if (!isset(self::$pusher)) {
                self::init();
            }
            
            self::$pusher->trigger($channel, $event, $data);
            return true;
        } catch (\Exception $e) {
            Logger::error("Realtime notification failed: " . $e->getMessage());
            return false;
        }
    }
    
    public static function sendPushNotification($deviceToken, $title, $message, $data = []) {
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';
            $headers = [
                'Authorization: key=' . $_ENV['FCM_SERVER_KEY'],
                'Content-Type: application/json'
            ];
            
            $fields = [
                'to' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $message
                ],
                'data' => $data
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            
            $result = curl_exec($ch);
            curl_close($ch);
            
            return json_decode($result, true);
        } catch (\Exception $e) {
            Logger::error("Push notification failed: " . $e->getMessage());
            return false;
        }
    }
    
    public static function saveNotification($userId, $type, $title, $message, $data = []) {
        try {
            $db = \App\Config\Database::getInstance();
            $conn = $db->getConnection();
            
            $sql = "INSERT INTO notifications (user_id, type, title, message, data, created_at) 
                    VALUES (:user_id, :type, :title, :message, :data, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':type' => $type,
                ':title' => $title,
                ':message' => $message,
                ':data' => json_encode($data)
            ]);
            
            return $conn->lastInsertId();
        } catch (\Exception $e) {
            Logger::error("Notification save failed: " . $e->getMessage());
            return false;
        }
    }
} 