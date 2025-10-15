<?php
/**
 * Database Connection Configuration
 * Health & Sanitation Management System
 */

// Load Brevo credentials (local config only)
require_once __DIR__ . '/../config/brevo.php';
putenv('BREVO_API_KEY=' . BREVO_API_KEY);
putenv('BREVO_SENDER_EMAIL=' . BREVO_SENDER_EMAIL);
putenv('BREVO_SENDER_NAME=' . BREVO_SENDER_NAME);

// Database configuration
define('DB_HOST', 'localhost:3307');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'capstone-hs');

// Create connection class
class Database {
    private $host = DB_HOST;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    private $database = DB_NAME;
    private $connection;
    
    public function __construct() {
        $this->connect();
        $this->ensureSchema();
    }
    
    private function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Contact: create a new message
    public function createContactMessage($data) {
        try {
            $sql = "INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, status) 
                    VALUES (:first_name, :last_name, :email, :phone, :subject, :message, 'new')";
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name'  => $data['last_name'],
                ':email'      => $data['email'],
                ':phone'      => $data['phone'],
                ':subject'    => $data['subject'],
                ':message'    => $data['message'],
            ]);
        } catch (PDOException $e) {
            error_log("Create contact message error: " . $e->getMessage());
            return false;
        }
    }

    // Contact: fetch messages (optionally filter by status or search)
    public function getContactMessages($status = null, $search = null) {
        try {
            $sql = "SELECT * FROM contact_messages";
            $conds = [];
            $params = [];
            if ($status) { $conds[] = "status = :status"; $params[':status'] = $status; }
            if ($search) {
                $conds[] = "(first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR phone LIKE :q OR subject LIKE :q OR message LIKE :q)";
                $params[':q'] = "%".$search."%";
            }
            if ($conds) { $sql .= " WHERE " . implode(' AND ', $conds); }
            $sql .= " ORDER BY created_at DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get contact messages error: " . $e->getMessage());
            return [];
        }
    }
    
    // Ensure required tables exist
    private function ensureSchema() {
        try {
            // Users table (minimal, if not already created elsewhere)
            $this->connection->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin','doctor','nurse','citizen') DEFAULT 'citizen',
                status ENUM('active','inactive','pending') DEFAULT 'active',
                profile_picture VARCHAR(255) NULL,
                phone VARCHAR(20) NULL,
                address TEXT NULL,
                date_of_birth DATE NULL,
                gender ENUM('male','female','other') NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Ensure 'inspector' role exists in users.role enum
            try {
                $col = $this->connection->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
                if ($col && isset($col['Type']) && stripos($col['Type'], "'inspector'") === false) {
                    $this->connection->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','doctor','nurse','inspector','citizen') DEFAULT 'citizen'");
                }
            } catch (PDOException $e) {
                // ignore if cannot alter
            }

            // Ensure verification_status column exists on users
            try {
                $check = $this->connection->query("SHOW COLUMNS FROM users LIKE 'verification_status'");
                if ($check->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE users ADD COLUMN verification_status ENUM('unverified','pending','verified','rejected') DEFAULT 'unverified' AFTER gender");
                }
            } catch (PDOException $e) {
                // ignore if column already exists or insufficient privileges
            }

            // Appointments table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS appointments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                middle_name VARCHAR(50),
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                birth_date DATE NOT NULL,
                gender ENUM('male', 'female', 'other', 'prefer-not-to-say') NOT NULL,
                civil_status ENUM('single', 'married', 'divorced', 'widowed') NOT NULL,
                address TEXT NOT NULL,
                appointment_type VARCHAR(100) NOT NULL,
                preferred_date DATE NOT NULL,
                health_concerns TEXT NOT NULL,
                medical_history TEXT NOT NULL,
                current_medications TEXT,
                allergies TEXT,
                emergency_contact_name VARCHAR(100) NOT NULL,
                emergency_contact_phone VARCHAR(20) NOT NULL,
                status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Ensure soft-delete column on appointments
            try {
                $col = $this->connection->query("SHOW COLUMNS FROM appointments LIKE 'deleted_at'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE appointments ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
                }
                // Ensure index on deleted_at for faster filtering
                $idx = $this->connection->query("SHOW INDEX FROM appointments WHERE Key_name = 'idx_appointments_deleted_at'");
                if ($idx->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE appointments ADD INDEX idx_appointments_deleted_at (deleted_at)");
                }
            } catch (PDOException $e) {
                // ignore if cannot alter
            }

            // Service requests table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS service_requests (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                service_type VARCHAR(100) NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                address TEXT NOT NULL,
                service_details TEXT NOT NULL,
                preferred_date DATE,
                urgency ENUM('low','medium','high','emergency') DEFAULT 'medium',
                status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Ensure soft-delete column on service_requests
            try {
                $col = $this->connection->query("SHOW COLUMNS FROM service_requests LIKE 'deleted_at'");
                if ($col->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
                }
                // Ensure index on deleted_at for faster filtering
                $idx = $this->connection->query("SHOW INDEX FROM service_requests WHERE Key_name = 'idx_service_requests_deleted_at'");
                if ($idx->rowCount() === 0) {
                    $this->connection->exec("ALTER TABLE service_requests ADD INDEX idx_service_requests_deleted_at (deleted_at)");
                }
            } catch (PDOException $e) {
                // ignore if cannot alter
            }

            // User verifications table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS user_verifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                document_type VARCHAR(50) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                status ENUM('pending','verified','rejected') DEFAULT 'pending',
                notes TEXT NULL,
                reviewed_by INT NULL,
                reviewed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Contact messages table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                subject VARCHAR(100) NOT NULL,
                message TEXT NOT NULL,
                status ENUM('new','read','archived') DEFAULT 'new',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $this->connection->exec("CREATE TABLE IF NOT EXISTS login_otps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(150) NOT NULL,
                otp_hash VARCHAR(255) NOT NULL,
                attempts TINYINT UNSIGNED DEFAULT 0,
                expires_at DATETIME NOT NULL,
                verified_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX login_otps_email_idx (email),
                INDEX login_otps_expires_idx (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Audit logs table
            $this->connection->exec("CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                role VARCHAR(20) NULL,
                action VARCHAR(50) NOT NULL,
                details TEXT NULL,
                ip VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id), INDEX (role), INDEX (action), INDEX (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (PDOException $e) {
            error_log('Schema ensure error: ' . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // User authentication methods
    public function registerUser($first_name, $last_name, $email, $password, $role = 'citizen') {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (first_name, last_name, email, password, role, created_at) 
                    VALUES (:first_name, :last_name, :email, :password, :role, NOW())";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);
            
            if ($stmt->execute()) {
                return $this->connection->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    public function loginUser($email, $password) {
        try {
            $sql = "SELECT id, first_name, last_name, email, password, role, status 
                    FROM users WHERE email = :email";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if user is active
                if ($user['status'] !== 'active') {
                    return ['error' => 'Account is not active. Please contact administrator.'];
                }
                
                // Remove password from returned data
                unset($user['password']);
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserById($user_id) {
        try {
            $sql = "SELECT id, first_name, last_name, email, role, status, verification_status, 
                           profile_picture, phone, address, date_of_birth, gender, created_at 
                    FROM users WHERE id = :user_id";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateUser($user_id, $data) {
        try {
            $fields = [];
            $params = [':user_id' => $user_id];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'password') {
                    $fields[] = "{$key} = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :user_id";
            
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            return false;
        }
    }

    // Update password with current password verification
    public function updateUserPassword($user_id, $current_password, $new_password) {
        try {
            $stmt = $this->connection->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['ok' => false, 'error' => 'User not found'];
            if (!password_verify($current_password, $row['password'])) {
                return ['ok' => false, 'error' => 'Current password is incorrect'];
            }
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $up = $this->connection->prepare("UPDATE users SET password = :p, updated_at = NOW() WHERE id = :id");
            $up->execute([':p' => $hashed, ':id' => $user_id]);
            return ['ok' => true];
        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return ['ok' => false, 'error' => 'Server error'];
        }
    }

    // Log audit events
    public function logAudit($user_id, $role, $action, $details = null) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $stmt = $this->connection->prepare("INSERT INTO audit_logs (user_id, role, action, details, ip, user_agent) VALUES (:uid, :role, :action, :details, :ip, :ua)");
            $stmt->execute([
                ':uid' => $user_id,
                ':role' => $role,
                ':action' => $action,
                ':details' => $details,
                ':ip' => $ip,
                ':ua' => $ua,
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Log audit error: " . $e->getMessage());
            return false;
        }
    }

    // Fetch audit logs with optional filters
    public function getAuditLogs($filters = []) {
        try {
            $sql = "SELECT al.*, u.first_name, u.last_name, u.email FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id";
            $where = [];
            $params = [];
            if (!empty($filters['role'])) { $where[] = 'al.role = :role'; $params[':role'] = $filters['role']; }
            if (!empty($filters['user_id'])) { $where[] = 'al.user_id = :uid'; $params[':uid'] = (int)$filters['user_id']; }
            if (!empty($filters['action'])) { $where[] = 'al.action = :action'; $params[':action'] = $filters['action']; }
            if (!empty($filters['date_from'])) { $where[] = 'al.created_at >= :df'; $params[':df'] = $filters['date_from'] . ' 00:00:00'; }
            if (!empty($filters['date_to'])) { $where[] = 'al.created_at <= :dt'; $params[':dt'] = $filters['date_to'] . ' 23:59:59'; }
            if (!empty($filters['q'])) { $where[] = '(al.details LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q)'; $params[':q'] = '%'.$filters['q'].'%'; }
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY al.created_at DESC';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get audit logs error: " . $e->getMessage());
            return [];
        }
    }

    // Fetch audit logs with pagination (limit/offset)
    public function getAuditLogsPaginated($filters = [], $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT al.*, u.first_name, u.last_name, u.email FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id";
            $where = [];
            $params = [];
            if (!empty($filters['role'])) { $where[] = 'al.role = :role'; $params[':role'] = $filters['role']; }
            if (!empty($filters['user_id'])) { $where[] = 'al.user_id = :uid'; $params[':uid'] = (int)$filters['user_id']; }
            if (!empty($filters['action'])) { $where[] = 'al.action = :action'; $params[':action'] = $filters['action']; }
            if (!empty($filters['date_from'])) { $where[] = 'al.created_at >= :df'; $params[':df'] = $filters['date_from'] . ' 00:00:00'; }
            if (!empty($filters['date_to'])) { $where[] = 'al.created_at <= :dt'; $params[':dt'] = $filters['date_to'] . ' 23:59:59'; }
            if (!empty($filters['q'])) { $where[] = '(al.details LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q)'; $params[':q'] = '%'.$filters['q'].'%'; }
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY al.created_at DESC LIMIT :lim OFFSET :off';
            $stmt = $this->connection->prepare($sql);
            foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get audit logs (paginated) error: " . $e->getMessage());
            return [];
        }
    }

    // Count audit logs for given filters
    public function countAuditLogs($filters = []) {
        try {
            $sql = "SELECT COUNT(*) AS cnt FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id";
            $where = [];
            $params = [];
            if (!empty($filters['role'])) { $where[] = 'al.role = :role'; $params[':role'] = $filters['role']; }
            if (!empty($filters['user_id'])) { $where[] = 'al.user_id = :uid'; $params[':uid'] = (int)$filters['user_id']; }
            if (!empty($filters['action'])) { $where[] = 'al.action = :action'; $params[':action'] = $filters['action']; }
            if (!empty($filters['date_from'])) { $where[] = 'al.created_at >= :df'; $params[':df'] = $filters['date_from'] . ' 00:00:00'; }
            if (!empty($filters['date_to'])) { $where[] = 'al.created_at <= :dt'; $params[':dt'] = $filters['date_to'] . ' 23:59:59'; }
            if (!empty($filters['q'])) { $where[] = '(al.details LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q)'; $params[':q'] = '%'.$filters['q'].'%'; }
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['cnt'] : 0;
        } catch (PDOException $e) {
            error_log("Count audit logs error: " . $e->getMessage());
            return 0;
        }
    }
    
    public function emailExists($email, $exclude_id = null) {
        try {
            $sql = "SELECT id FROM users WHERE email = :email";
            $params = [':email' => $email];
            
            if ($exclude_id) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $exclude_id;
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Email check error: " . $e->getMessage());
            return false;
        }
    }
    
    // Create appointment
    public function createAppointment($appointmentData) {
        try {
            // Gate: only verified users can create
            if (!$this->isUserVerified($appointmentData['user_id'])) {
                return false;
            }
            $sql = "INSERT INTO appointments (
                user_id, first_name, middle_name, last_name, email, phone, 
                birth_date, gender, civil_status, address, appointment_type, 
                preferred_date, health_concerns, medical_history, current_medications, 
                allergies, emergency_contact_name, emergency_contact_phone
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([
                $appointmentData['user_id'],
                $appointmentData['first_name'],
                $appointmentData['middle_name'],
                $appointmentData['last_name'],
                $appointmentData['email'],
                $appointmentData['phone'],
                $appointmentData['birth_date'],
                $appointmentData['gender'],
                $appointmentData['civil_status'],
                $appointmentData['address'],
                $appointmentData['appointment_type'],
                $appointmentData['preferred_date'],
                $appointmentData['health_concerns'],
                $appointmentData['medical_history'],
                $appointmentData['current_medications'],
                $appointmentData['allergies'],
                $appointmentData['emergency_contact_name'],
                $appointmentData['emergency_contact_phone']
            ]);
        } catch (PDOException $e) {
            error_log("Appointment creation error: " . $e->getMessage());
            return false;
        }
    }

    // Create service request
    public function createServiceRequest($serviceData) {
        try {
            // Gate: only verified users can create
            if (!$this->isUserVerified((int)$serviceData['user_id'])) {
                return false;
            }
            $sql = "INSERT INTO service_requests (
                user_id, service_type, full_name, email, phone, 
                address, service_details, preferred_date, urgency
            ) VALUES (:user_id, :service_type, :full_name, :email, :phone, :address, :service_details, :preferred_date, :urgency)";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':user_id', (int)$serviceData['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':service_type', (string)$serviceData['service_type'], PDO::PARAM_STR);
            $stmt->bindValue(':full_name', (string)$serviceData['full_name'], PDO::PARAM_STR);
            $stmt->bindValue(':email', (string)$serviceData['email'], PDO::PARAM_STR);
            $stmt->bindValue(':phone', (string)$serviceData['phone'], PDO::PARAM_STR);
            $stmt->bindValue(':address', (string)$serviceData['address'], PDO::PARAM_STR);
            $stmt->bindValue(':service_details', (string)$serviceData['service_details'], PDO::PARAM_STR);
            if (!empty($serviceData['preferred_date'])) {
                $stmt->bindValue(':preferred_date', $serviceData['preferred_date'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':preferred_date', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(':urgency', (string)$serviceData['urgency'], PDO::PARAM_STR);

            $ok = $stmt->execute();
            if (!$ok) {
                $info = $stmt->errorInfo();
                error_log('Service request insert failed: ' . implode(' | ', $info));
            }
            return $ok;
        } catch (PDOException $e) {
            error_log("Service request creation error: " . $e->getMessage());
            return false;
        }
    }

    // Get user appointments
    public function getUserAppointments($user_id) {
        try {
            $sql = "SELECT * FROM appointments WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get appointments error: " . $e->getMessage());
            return [];
        }
    }

    // Get user service requests
    public function getUserServiceRequests($user_id) {
        try {
            $sql = "SELECT * FROM service_requests WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get service requests error: " . $e->getMessage());
            return [];
        }
    }

    // Cancel appointment
    public function cancelAppointment($appointment_id, $user_id) {
        try {
            $sql = "UPDATE appointments SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ? AND user_id = ? AND status IN ('pending') AND deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $ok = $stmt->execute([$appointment_id, $user_id]);
            return $ok && ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            error_log("Cancel appointment error: " . $e->getMessage());
            return false;
        }
    }

    // Cancel service request
    public function cancelServiceRequest($request_id, $user_id) {
        try {
            $sql = "UPDATE service_requests SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ? AND user_id = ? AND status IN ('pending') AND deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $ok = $stmt->execute([$request_id, $user_id]);
            return $ok && ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            error_log("Cancel service request error: " . $e->getMessage());
            return false;
        }
    }

    // Verification helpers
    public function isUserVerified($user_id) {
        try {
            $stmt = $this->connection->prepare("SELECT verification_status FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch();
            return $row && $row['verification_status'] === 'verified';
        } catch (PDOException $e) {
            error_log("Check verified error: " . $e->getMessage());
            return false;
        }
    }

    public function submitUserVerification($user_id, $document_type, $file_path) {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("INSERT INTO user_verifications (user_id, document_type, file_path, status, created_at, updated_at) VALUES (:uid, :dtype, :f, 'pending', NOW(), NOW())");
            $stmt->execute([':uid' => $user_id, ':dtype' => $document_type, ':f' => $file_path]);
            $this->connection->prepare("UPDATE users SET verification_status = 'pending' WHERE id = ?")->execute([$user_id]);
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Submit verification error: " . $e->getMessage());
            return false;
        }
    }

    public function listUserVerifications($status = null) {
        try {
            $sql = "SELECT v.*, u.first_name, u.last_name, u.email FROM user_verifications v JOIN users u ON v.user_id = u.id";
            if ($status) { $sql .= " WHERE v.status = :status"; }
            $sql .= " ORDER BY v.created_at DESC";
            $stmt = $this->connection->prepare($sql);
            if ($status) { $stmt->bindParam(':status', $status); }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("List verifications error: " . $e->getMessage());
            return [];
        }
    }

    public function updateUserVerificationStatus($verification_id, $status, $admin_id = null, $note = null) {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("UPDATE user_verifications SET status = :st, reviewed_by = :rb, reviewed_at = NOW(), notes = :nt WHERE id = :id");
            $stmt->execute([':st' => $status, ':rb' => $admin_id, ':nt' => $note, ':id' => $verification_id]);

            // Sync user's verification_status
            $ownerStmt = $this->connection->prepare("SELECT user_id FROM user_verifications WHERE id = :id");
            $ownerStmt->execute([':id' => $verification_id]);
            $row = $ownerStmt->fetch();
            if ($row) {
                $vs = ($status === 'verified') ? 'verified' : (($status === 'rejected') ? 'rejected' : 'unverified');
                $this->connection->prepare("UPDATE users SET verification_status = :vs WHERE id = :uid")->execute([':vs' => $vs, ':uid' => $row['user_id']]);
            }

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Update verification status error: " . $e->getMessage());
            return false;
        }
    }

    // Role-based redirect helper
    public static function getRoleRedirect($role) {
        $redirects = [
            'admin' => 'admin/DashboardOverview_new.php',
            'doctor' => 'doctor/doctor.php',
            'nurse' => 'nurse/nurse.php',
            'citizen' => 'citizen/citizen.php',
            'inspector' => 'inspection/inspector.php'
        ];
        
        return isset($redirects[$role]) ? $redirects[$role] : 'citizen/citizen.php';
    }
}

// Create database instance
$database = new Database();
$db = $database->getConnection();

// Helper functions
function startSecureSession() {
    // Check if headers have already been sent
    if (headers_sent()) {
        error_log("Warning: Cannot start session - headers already sent");
        return false;
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        
        // Regenerate session ID for security (only if session is active)
        if (session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }
    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
        $isApi = (strpos($uri, '/api/') !== false) || (stripos($accept, 'application/json') !== false) || (stripos($ctype, 'application/json') !== false);
        if ($isApi) {
            if (!headers_sent()) header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success'=>false,'message'=>'Unauthorized']);
            exit();
        }
        header('Location: index.html');
        exit();
    }
}

function requireRole($required_role) {
    requireLogin();
    if ($_SESSION['role'] !== $required_role) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
        $isApi = (strpos($uri, '/api/') !== false) || (stripos($accept, 'application/json') !== false) || (stripos($ctype, 'application/json') !== false);
        if ($isApi) {
            if (!headers_sent()) header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'Forbidden']);
            exit();
        }
        header('Location: ' . Database::getRoleRedirect($_SESSION['role']));
        exit();
    }
}

function redirectByRole($role) {
    $redirect_url = Database::getRoleRedirect($role);
    header("Location: {$redirect_url}");
    exit();
}

// SQL to create users table (run this once to set up the database)
/*
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'nurse', 'citizen') DEFAULT 'citizen',
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    profile_picture VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, role) 
VALUES ('Admin', 'User', 'admin@healthsanitation.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Create appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female', 'other', 'prefer-not-to-say') NOT NULL,
    civil_status ENUM('single', 'married', 'divorced', 'widowed') NOT NULL,
    address TEXT NOT NULL,
    appointment_type VARCHAR(100) NOT NULL,
    preferred_date DATE NOT NULL,
    health_concerns TEXT NOT NULL,
    medical_history TEXT NOT NULL,
    current_medications TEXT,
    allergies TEXT,
    emergency_contact_name VARCHAR(100) NOT NULL,
    emergency_contact_phone VARCHAR(20) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create service_requests table
CREATE TABLE IF NOT EXISTS service_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    service_details TEXT NOT NULL,
    preferred_date DATE,
    urgency ENUM('low', 'medium', 'high', 'emergency') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
*/