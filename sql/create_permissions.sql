-- Criar tabela de permissões de departamentos
CREATE TABLE IF NOT EXISTS user_department_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    department VARCHAR(50) NOT NULL,
    can_view BOOLEAN DEFAULT FALSE,
    can_edit BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_department (user_id, department)
);

-- Inserir permissões padrão para o usuário admin (assumindo ID 1)
INSERT INTO user_department_permissions (user_id, department, can_view, can_edit) VALUES
(1, 'comercial', TRUE, TRUE),
(1, 'financeiro', TRUE, TRUE),
(1, 'rh', TRUE, TRUE),
(1, 'qualidade', TRUE, TRUE),
(1, 'atendimento', TRUE, TRUE),
(1, 'producao', TRUE, TRUE)
ON DUPLICATE KEY UPDATE can_view = TRUE, can_edit = TRUE;

-- Inserir permissões limitadas para usuário equipe (assumindo ID 2)
INSERT INTO user_department_permissions (user_id, department, can_view, can_edit) VALUES
(2, 'comercial', TRUE, FALSE),
(2, 'atendimento', TRUE, FALSE)
ON DUPLICATE KEY UPDATE can_view = VALUES(can_view), can_edit = VALUES(can_edit);