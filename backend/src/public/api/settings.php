<?php
header('Content-Type: application/json');
require_once '../config/config.php';

class SettingsAPI {
    private $conn;
    private $table_general = 'settings_general';
    private $table_user = 'settings_user';
    private $table_security = 'settings_security';
    private $table_theme = 'settings_theme';
    private $table_backups = 'settings_backups';
    private $table_history = 'settings_history';
    private $table_notifications = 'settings_notifications';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // General settings
    public function getGeneralSettings() {
        $sql = "SELECT * FROM {$this->table_general}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function saveGeneralSettings($data) {
        $sql = "UPDATE {$this->table_general} SET company_name = ?, address = ?, phone = ?, email = ?, website = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $data['company_name'], $data['address'], $data['phone'], $data['email'], $data['website']);
        $success = $stmt->execute();
        if ($success) {
            $this->saveSettingsHistory('general', $data);
        }
        return $success;
    }

    // User settings
    public function getUserSettings() {
        $sql = "SELECT * FROM {$this->table_user}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function saveUserSettings($data) {
        $sql = "UPDATE {$this->table_user} SET default_role = ?, default_permissions = ?, password_policy = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $data['default_role'], $data['default_permissions'], $data['password_policy']);
        $success = $stmt->execute();
        if ($success) {
            $this->saveSettingsHistory('user', $data);
        }
        return $success;
    }

    // Security settings
    public function getSecuritySettings() {
        $sql = "SELECT * FROM {$this->table_security}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function saveSecuritySettings($data) {
        $sql = "UPDATE {$this->table_security} SET session_timeout = ?, login_attempts = ?, two_factor_auth = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $data['session_timeout'], $data['login_attempts'], $data['two_factor_auth']);
        $success = $stmt->execute();
        if ($success) {
            $this->saveSettingsHistory('security', $data);
        }
        return $success;
    }

    // Theme settings
    public function getThemeSettings() {
        $sql = "SELECT * FROM {$this->table_theme}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function saveThemeSettings($data) {
        $sql = "UPDATE {$this->table_theme} SET primary_color = ?, secondary_color = ?, font_family = ?, dark_mode = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $data['primary_color'], $data['secondary_color'], $data['font_family'], $data['dark_mode']);
        $success = $stmt->execute();
        if ($success) {
            $this->saveSettingsHistory('theme', $data);
        }
        return $success;
    }

    // Backup management
    public function getBackups() {
        $sql = "SELECT * FROM {$this->table_backups} ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createBackup($data) {
        $sql = "INSERT INTO {$this->table_backups} (name, description, type, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $status = 'PENDING';
        $stmt->bind_param("ssss", $data['name'], $data['description'], $data['type'], $status);
        return $stmt->execute();
    }

    public function restoreBackup($id) {
        $sql = "UPDATE {$this->table_backups} SET status = 'RESTORING' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function deleteBackup($id) {
        $sql = "DELETE FROM {$this->table_backups} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Settings history
    private function saveSettingsHistory($category, $data) {
        $sql = "INSERT INTO {$this->table_history} (category, settings_data, changed_by, changed_at) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $user_id = $_SESSION['user_id'] ?? 0;
        $changed_at = date('Y-m-d H:i:s');
        $settings_data = json_encode($data);
        $stmt->bind_param("ssis", $category, $settings_data, $user_id, $changed_at);
        return $stmt->execute();
    }

    public function getSettingsHistory($category = null) {
        $sql = "SELECT * FROM {$this->table_history}";
        if ($category) {
            $sql .= " WHERE category = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $category);
        } else {
            $stmt = $this->conn->prepare($sql);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Notification settings
    public function getNotificationSettings() {
        $sql = "SELECT * FROM {$this->table_notifications}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function saveNotificationSettings($data) {
        $sql = "UPDATE {$this->table_notifications} SET email_notifications = ?, push_notifications = ?, notification_sound = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $data['email_notifications'], $data['push_notifications'], $data['notification_sound']);
        $success = $stmt->execute();
        if ($success) {
            $this->saveSettingsHistory('notifications', $data);
        }
        return $success;
    }
}

// Initialize API
$settingsAPI = new SettingsAPI($conn);

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch($method) {
    case 'GET':
        switch($action) {
            case 'general':
                echo json_encode($settingsAPI->getGeneralSettings());
                break;
            case 'user':
                echo json_encode($settingsAPI->getUserSettings());
                break;
            case 'security':
                echo json_encode($settingsAPI->getSecuritySettings());
                break;
            case 'theme':
                echo json_encode($settingsAPI->getThemeSettings());
                break;
            case 'backups':
                echo json_encode($settingsAPI->getBackups());
                break;
            case 'history':
                $category = $_GET['category'] ?? null;
                echo json_encode($settingsAPI->getSettingsHistory($category));
                break;
            case 'notifications':
                echo json_encode($settingsAPI->getNotificationSettings());
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Invalid action']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        switch($action) {
            case 'save_general':
                echo json_encode(['success' => $settingsAPI->saveGeneralSettings($data)]);
                break;
            case 'save_user':
                echo json_encode(['success' => $settingsAPI->saveUserSettings($data)]);
                break;
            case 'save_security':
                echo json_encode(['success' => $settingsAPI->saveSecuritySettings($data)]);
                break;
            case 'save_theme':
                echo json_encode(['success' => $settingsAPI->saveThemeSettings($data)]);
                break;
            case 'create_backup':
                echo json_encode(['success' => $settingsAPI->createBackup($data)]);
                break;
            case 'restore_backup':
                echo json_encode(['success' => $settingsAPI->restoreBackup($data['id'])]);
                break;
            case 'delete_backup':
                echo json_encode(['success' => $settingsAPI->deleteBackup($data['id'])]);
                break;
            case 'save_notifications':
                echo json_encode(['success' => $settingsAPI->saveNotificationSettings($data)]);
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Invalid action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?> 