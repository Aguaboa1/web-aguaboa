<?php
/**
 * Configuração do banco de dados
 * Web Aguaboa - Gestão Comercial
 */

class Database {
    private static $instance = null;
    private $connection;

    // Configurações do banco - XAMPP
    private $host = 'localhost';
    private $dbname = 'aguaboa_gestao';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $port = 3306;

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    /**
     * Inicializar tabelas do banco de dados
     */
    public function initializeTables() {
        $this->createUsersTable();
        $this->createClientsTable();
        $this->createEnvaseDataTable();
        $this->createUploadHistoryTable();
        $this->createActivityLogTable();
        $this->createActionsTable();
        $this->createClientInfosTable();
        $this->createUserDepartmentPermissionsTable();
        $this->insertDefaultUsers();
        $this->setupDefaultPermissions();
    }

    private function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(80) NOT NULL UNIQUE,
            email VARCHAR(120),
            password_hash VARCHAR(255) NOT NULL,
            password_plain VARCHAR(255),
            role VARCHAR(20) NOT NULL DEFAULT 'equipe',
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            last_login DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )";
        $this->connection->exec($sql);
    }

    private function createClientsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS clients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente VARCHAR(255) NOT NULL,
            empresa VARCHAR(255),
            cidade VARCHAR(255),
            estado VARCHAR(100),
            tipo_cliente VARCHAR(50),
            cliente_exclusivo BOOLEAN DEFAULT FALSE,
            cliente_premium BOOLEAN DEFAULT FALSE,
            tipo_frete VARCHAR(50),
            freteiro_nome VARCHAR(255),
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )";
        $this->connection->exec($sql);
    }

    private function createEnvaseDataTable() {
        $sql = "CREATE TABLE IF NOT EXISTS envase_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            empresa VARCHAR(100) NOT NULL,
            cidade VARCHAR(100),
            produto VARCHAR(100) NOT NULL,
            ano INT NOT NULL,
            mes INT NOT NULL,
            dia INT NOT NULL,
            quantidade INT NOT NULL,
            arquivo_origem VARCHAR(200),
            data_upload DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_empresa (empresa),
            INDEX idx_ano (ano),
            INDEX idx_mes (mes),
            INDEX idx_dia (dia)
        )";
        $this->connection->exec($sql);
    }

    private function createUploadHistoryTable() {
        $sql = "CREATE TABLE IF NOT EXISTS upload_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_arquivo VARCHAR(200) NOT NULL,
            usuario_id INT,
            total_registros INT,
            registros_processados INT,
            status VARCHAR(50) NOT NULL DEFAULT 'processando',
            mensagem_erro VARCHAR(255),
            data_upload DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES users(id)
        )";
        $this->connection->exec($sql);
    }

    private function createActivityLogTable() {
        $sql = "CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            description VARCHAR(255),
            ip_address VARCHAR(50),
            timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            extra_data TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX idx_user_id (user_id)
        )";
        $this->connection->exec($sql);
    }

    private function createActionsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS actions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            descricao TEXT,
            data_acao DATE,
            prazo_conclusao DATE,
            arquivo VARCHAR(255),
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
        )";
        $this->connection->exec($sql);
        
        // Verificar se a coluna prazo_conclusao existe e adicionar se necessário
        try {
            $stmt = $this->connection->query("SHOW COLUMNS FROM actions LIKE 'prazo_conclusao'");
            $columnExists = $stmt->fetch();
            
            if (!$columnExists) {
                $this->connection->exec("ALTER TABLE actions ADD COLUMN prazo_conclusao DATE AFTER data_acao");
            }
        } catch (Exception $e) {
            // Ignorar erro se a coluna já existir
        }
    }

    private function createClientInfosTable() {
        $sql = "CREATE TABLE IF NOT EXISTS client_infos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            info_json TEXT,
            data_info DATE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
        )";
        $this->connection->exec($sql);
    }

    private function insertDefaultUsers() {
        // Verificar se já existem usuários
        $stmt = $this->connection->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Criar usuários padrão
            $adminPassword = password_hash('652409', PASSWORD_DEFAULT);
            $equipePassword = password_hash('equipe123', PASSWORD_DEFAULT);
            $producaoPassword = password_hash('producao123', PASSWORD_DEFAULT);
            $supervisorPassword = password_hash('supervisor123', PASSWORD_DEFAULT);
            $operadorPassword = password_hash('operador123', PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, password_hash, password_plain, role, email) VALUES 
                    ('Branco', ?, '652409', 'admin', 'admin@aguaboa.com'),
                    ('equipe', ?, 'equipe123', 'equipe', 'equipe@aguaboa.com'),
                    ('Producao', ?, 'producao123', 'producao', 'producao@aguaboa.com'),
                    ('Supervisor', ?, 'supervisor123', 'producao', 'supervisor@aguaboa.com'),
                    ('Operador', ?, 'operador123', 'producao', 'operador@aguaboa.com')";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$adminPassword, $equipePassword, $producaoPassword, $supervisorPassword, $operadorPassword]);
        }
    }

    private function createUserDepartmentPermissionsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS user_department_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            department VARCHAR(50) NOT NULL,
            can_view BOOLEAN DEFAULT FALSE,
            can_edit BOOLEAN DEFAULT FALSE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_department (user_id, department)
        )";
        $this->connection->exec($sql);
    }

    private function setupDefaultPermissions() {
        // Verificar se já existem permissões
        $stmt = $this->connection->query("SELECT COUNT(*) FROM user_department_permissions");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Obter todos os usuários
            $stmt = $this->connection->query("SELECT id, role FROM users");
            $users = $stmt->fetchAll();

            $insertStmt = $this->connection->prepare("
                INSERT INTO user_department_permissions (user_id, department, can_view, can_edit) 
                VALUES (?, ?, ?, ?)
            ");

            foreach ($users as $user) {
                $permissions = [];
                
                if ($user['role'] === 'admin') {
                    $permissions = [
                        'comercial' => ['can_view' => true, 'can_edit' => true],
                        'financeiro' => ['can_view' => true, 'can_edit' => true],
                        'rh' => ['can_view' => true, 'can_edit' => true],
                        'qualidade' => ['can_view' => true, 'can_edit' => true],
                        'atendimento' => ['can_view' => true, 'can_edit' => true],
                        'producao' => ['can_view' => true, 'can_edit' => true]
                    ];
                } elseif ($user['role'] === 'equipe') {
                    $permissions = [
                        'comercial' => ['can_view' => true, 'can_edit' => true],
                        'financeiro' => ['can_view' => true, 'can_edit' => false],
                        'rh' => ['can_view' => false, 'can_edit' => false],
                        'qualidade' => ['can_view' => true, 'can_edit' => false],
                        'atendimento' => ['can_view' => true, 'can_edit' => false],
                        'producao' => ['can_view' => true, 'can_edit' => false]
                    ];
                } elseif ($user['role'] === 'producao') {
                    $permissions = [
                        'comercial' => ['can_view' => true, 'can_edit' => false],
                        'financeiro' => ['can_view' => false, 'can_edit' => false],
                        'rh' => ['can_view' => false, 'can_edit' => false],
                        'qualidade' => ['can_view' => true, 'can_edit' => true],
                        'atendimento' => ['can_view' => true, 'can_edit' => false],
                        'producao' => ['can_view' => true, 'can_edit' => true]
                    ];
                }

                foreach ($permissions as $department => $perms) {
                    $insertStmt->execute([
                        $user['id'],
                        $department,
                        $perms['can_view'],
                        $perms['can_edit']
                    ]);
                }
            }
        }
    }
}