# 🇧🇷 GUIA DE CONFIGURAÇÃO - Sistema Aguaboa PHP

## 🚀 COMO CONFIGURAR NO XAMPP

### 1️⃣ **INICIAR O XAMPP**

1. **Abrir o XAMPP Control Panel:**
   - Vá em: `C:\xampp\xampp-control.exe`
   - Ou procure "XAMPP" no menu iniciar

2. **Iniciar os serviços:**
   - Clique em **"Start"** ao lado do **Apache**
   - Clique em **"Start"** ao lado do **MySQL**
   - Aguarde ficarem **VERDES** ✅

### 2️⃣ **CRIAR O BANCO DE DADOS**

**Método 1 - Via phpMyAdmin (Recomendado):**

1. Acesse: http://localhost/phpmyadmin
2. Clique em **"Novo"** (no lado esquerdo)
3. Digite o nome: `aguaboa_gestao`
4. Escolha: `utf8mb4_unicode_ci`
5. Clique em **"Criar"**

**Método 2 - Via SQL:**

1. No phpMyAdmin, clique na aba **"SQL"**
2. Cole este código e clique em **"Executar"**:

```sql
-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS aguaboa_gestao 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE aguaboa_gestao;

-- Criar tabela de usuários
CREATE TABLE users (
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
CREATE TABLE clients (
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

-- Criar tabela de logs
CREATE TABLE activity_log (
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
CREATE TABLE actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    descricao TEXT,
    data_acao DATE,
    arquivo VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Inserir usuários padrão
INSERT INTO users (username, password_hash, password_plain, role, email) VALUES 
('Branco', '$2y$10$UhHJOWKZHlakfcQnE.5cYe5L5HfKpU8DgA8vOEKZYf6Zj6/0X2aBC', '652409', 'admin', 'admin@aguaboa.com'),
('equipe', '$2y$10$B9j.xhEKJbGJmhNJVj4cFe6J6S5jPqYpLgKJYr8HnJAJq6LNx3aGS', 'equipe123', 'equipe', 'equipe@aguaboa.com');
```

### 3️⃣ **TESTAR O SISTEMA**

1. **Teste básico:**
   - Acesse: http://localhost/gestao-aguaboa-php/test.php
   - Deve aparecer "✅ Sistema pronto!"

2. **Fazer login:**
   - Acesse: http://localhost/gestao-aguaboa-php
   - **Administrador:** Usuário: `Branco` / Senha: `652409`
   - **Equipe:** Usuário: `equipe` / Senha: `equipe123`

## 🎯 LINKS IMPORTANTES

| Descrição | Link |
|-----------|------|
| 🌐 **Sistema Principal** | http://localhost/gestao-aguaboa-php |
| 🧪 **Teste do Sistema** | http://localhost/gestao-aguaboa-php/test.php |
| 🗄️ **phpMyAdmin** | http://localhost/phpmyadmin |
| 🎛️ **XAMPP Control** | C:\xampp\xampp-control.exe |

## 🔐 CREDENCIAIS DE ACESSO

| Tipo | Usuário | Senha | Permissões |
|------|---------|-------|------------|
| **Admin** | Branco | 652409 | Todas as funções |
| **Equipe** | equipe | equipe123 | Visualização apenas |

## ❌ PROBLEMAS COMUNS

### **Erro: "Não consegue conectar"**
**Solução:**
1. Abra o XAMPP Control Panel
2. Certifique-se que Apache e MySQL estão **verdes**
3. Se estiver vermelho, clique em "Start"

### **Erro: "Página não encontrada"**
**Solução:**
1. Verifique se Apache está rodando (verde no XAMPP)
2. Confirme o caminho: `C:\xampp\htdocs\gestao-aguaboa-php`
3. Teste: http://localhost (deve mostrar dashboard do XAMPP)

### **Erro: "Banco não encontrado"**
**Solução:**
1. Acesse: http://localhost/phpmyadmin
2. Crie o banco `aguaboa_gestao`
3. Execute o SQL acima

### **Erro: "Acesso negado ao MySQL"**
**Solução:**
1. No phpMyAdmin, vá em "Contas de usuário"
2. Edite o usuário "root"
3. Certifique-se que não tem senha

## 🎉 FUNCIONALIDADES DISPONÍVEIS

Após a configuração, você terá:

- ✅ **Sistema de Login** com 2 níveis de acesso
- ✅ **Gestão Completa de Clientes** (cadastro, edição, exclusão)
- ✅ **Busca e Filtros** inteligentes
- ✅ **Sistema de Logs** para auditoria
- ✅ **Interface Moderna** e responsiva
- ✅ **Classificações de Clientes** (Premium, Exclusivo, etc.)
- ✅ **Gerenciamento de Usuários** (apenas admin)

## 📞 PRECISA DE AJUDA?

Se ainda tiver problemas:

1. **Verifique os logs:** `C:\xampp\apache\logs\error.log`
2. **Teste outro projeto PHP** para verificar se XAMPP funciona
3. **Reinicie o XAMPP** completamente
4. **Verifique antivírus/firewall** (pode bloquear as portas)

---

**🔥 O sistema está pronto para uso! É uma réplica exata do sistema Python original, mas em PHP.**