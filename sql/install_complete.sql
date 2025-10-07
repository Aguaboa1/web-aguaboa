-- SQL para criar todas as tabelas do Sistema Aguaboa
-- Execute este código no phpMyAdmin

-- Selecionar o banco
USE aguaboa_gestao;

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(120),
    password_hash VARCHAR(255) NOT NULL,
    password_plain VARCHAR(255),
    role VARCHAR(20) NOT NULL DEFAULT 'equipe',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Criar tabela de clientes
CREATE TABLE IF NOT EXISTS clients (
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
);

-- Criar tabela de dados de envase
CREATE TABLE IF NOT EXISTS envase_data (
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
);

-- Criar tabela de histórico de uploads
CREATE TABLE IF NOT EXISTS upload_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_arquivo VARCHAR(200) NOT NULL,
    usuario_id INT,
    total_registros INT,
    registros_processados INT,
    status VARCHAR(50) NOT NULL DEFAULT 'processando',
    mensagem_erro VARCHAR(255),
    data_upload DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id)
);

-- Criar tabela de logs de atividade
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    ip_address VARCHAR(50),
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    extra_data TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id)
);

-- Criar tabela de ações dos clientes
CREATE TABLE IF NOT EXISTS actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    descricao TEXT,
    data_acao DATE,
    arquivo VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Criar tabela de informações adicionais dos clientes
CREATE TABLE IF NOT EXISTS client_infos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    info_json TEXT,
    data_info DATE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Inserir usuários padrão
INSERT IGNORE INTO users (username, password_hash, password_plain, role, email) VALUES 
('Branco', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '652409', 'admin', 'admin@aguaboa.com'),
('equipe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'equipe123', 'equipe', 'equipe@aguaboa.com');

-- Atualizar as senhas com hash correto
UPDATE users SET password_hash = '$2y$10$UhHJOWKZHlakfcQnE.5cYe5L5HfKpU8DgA8vOEKZYf6Zj6/0X2aBC' WHERE username = 'Branco';
UPDATE users SET password_hash = '$2y$10$B9j.xhEKJbGJmhNJVj4cFe6J6S5jPqYpLgKJYr8HnJAJq6LNx3aGS' WHERE username = 'equipe';

-- Inserir alguns clientes de exemplo (opcional)
INSERT IGNORE INTO clients (cliente, empresa, cidade, estado, tipo_cliente, cliente_exclusivo, cliente_premium) VALUES 
('João Silva', 'Distribuidora Silva Ltda', 'São Paulo', 'SP', 'Normal', FALSE, FALSE),
('Maria Santos', 'Águas Premium', 'Rio de Janeiro', 'RJ', 'Master', TRUE, TRUE),
('Pedro Costa', 'Costa Distribuidora', 'Belo Horizonte', 'MG', 'Normal', FALSE, FALSE);

SELECT 'Banco de dados criado com sucesso!' as status;