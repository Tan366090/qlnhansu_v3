<?php
$notifications = Notification::getInstance()->getAll();
?>

<?php if (!empty($notifications)): ?>
    <div class="notification-container">
        <?php foreach ($notifications as $notification): ?>
            <div class="notification notification-<?php echo $notification['type']; ?>">
                <div class="notification-icon">
                    <?php
                    switch ($notification['type']) {
                        case 'success':
                            echo '✅';
                            break;
                        case 'error':
                            echo '❌';
                            break;
                        case 'warning':
                            echo '⚠️';
                            break;
                        case 'info':
                            echo 'ℹ️';
                            break;
                    }
                    ?>
                </div>
                <div class="notification-content">
                    <div class="notification-message">
                        <?php echo htmlspecialchars($notification['message']); ?>
                    </div>
                    <?php if (!empty($notification['context'])): ?>
                        <div class="notification-context">
                            <?php
                            foreach ($notification['context'] as $key => $value) {
                                echo "<strong>$key:</strong> " . htmlspecialchars($value) . "<br>";
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>

    <style>
        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .notification {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
        }

        .notification-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .notification-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .notification-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .notification-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .notification-icon {
            font-size: 20px;
            margin-right: 10px;
        }

        .notification-content {
            flex: 1;
        }

        .notification-message {
            font-weight: 500;
        }

        .notification-context {
            font-size: 0.9em;
            margin-top: 5px;
            color: inherit;
            opacity: 0.8;
        }

        .notification-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            color: inherit;
            opacity: 0.7;
        }

        .notification-close:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 576px) {
            .notification-container {
                left: 20px;
                right: 20px;
            }
            
            .notification {
                max-width: none;
            }
        }
    </style>

    <script>
        // Tự động đóng thông báo sau 5 giây
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const notifications = document.querySelectorAll('.notification');
                notifications.forEach(function(notification) {
                    notification.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                });
            }, 5000);
        });
    </script>
<?php endif; ?> 