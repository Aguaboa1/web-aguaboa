-- Adicionar tabela de permissões de departamentos
CREATE TABLE IF NOT EXISTS user_department_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    department VARCHAR(50) NOT NULL,
    can_view BOOLEAN DEFAULT 0,
    can_edit BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_department (user_id, department)
);

-- Inserir permissões padrão para usuários existentes
INSERT INTO user_department_permissions (user_id, department, can_view, can_edit) 
SELECT u.id, 'comercial', 1, 1
FROM users u 
WHERE NOT EXISTS (
    SELECT 1 FROM user_department_permissions udp 
    WHERE udp.user_id = u.id AND udp.department = 'comercial'
);

-- Para admins, dar acesso completo a todos os departamentos
INSERT INTO user_department_permissions (user_id, department, can_view, can_edit) 
SELECT u.id, dept.name, 1, 1
FROM users u
CROSS JOIN (
    SELECT 'comercial' as name
    UNION SELECT 'financeiro'
    UNION SELECT 'rh'
    UNION SELECT 'qualidade'
    UNION SELECT 'atendimento'
    UNION SELECT 'producao'
) dept
WHERE u.role = 'admin'
ON DUPLICATE KEY UPDATE 
    can_view = 1,
    can_edit = 1;