# 🔧 GUIA DE CONFIGURAÇÃO NO XAMPP

## ⚡ Configuração Rápida

### 1. **Iniciar Serviços do XAMPP**
   - Abra o XAMPP Control Panel
   - Clique em **Start** para Apache
   - Clique em **Start** para MySQL
   - Aguarde aparecer verde ao lado dos dois

### 2. **Configurar o Banco de Dados**
   Executar um dos comandos abaixo:

   **Opção A - Via Terminal:**
   ```bash
   C:\xampp\php\php.exe "C:\xampp\htdocs\gestao-aguaboa-php\scripts\setup_database.php"
   ```

   **Opção B - Via phpMyAdmin:**
   - Acesse: http://localhost/phpmyadmin
   - Clique em "New" (Novo)
   - Nome: `aguaboa_gestao`
   - Collation: `utf8mb4_unicode_ci`
   - Importe o arquivo: `sql/create_database.sql`

### 3. **Acessar o Sistema**
   - 🌐 **Sistema:** http://localhost/gestao-aguaboa-php
   - 🧪 **Teste:** http://localhost/gestao-aguaboa-php/test.php

---

## 👤 CREDENCIAIS DE ACESSO

| Tipo  | Usuário | Senha     | Permissões |
|-------|---------|-----------|------------|
| Admin | Branco  | 652409    | Completas  |
| Equipe| equipe  | equipe123 | Limitadas  |

---

## 🔧 TROUBLESHOOTING

### ❌ **Erro: "Connection refused"**
**Solução:**
1. Verifique se MySQL está verde no XAMPP
2. Reinicie o MySQL no XAMPP
3. Verifique se a porta 3306 não está ocupada

### ❌ **Erro: "Access denied"**
**Solução:**
1. No XAMPP, clique em "Admin" ao lado do MySQL
2. Vá em "User accounts" → "root"
3. Certifique-se que a senha está vazia

### ❌ **Erro: "Page not found"**
**Solução:**
1. Verifique se Apache está verde no XAMPP
2. Certifique-se do caminho: `C:\xampp\htdocs\gestao-aguaboa-php`
3. Teste: http://localhost/gestao-aguaboa-php/test.php

### ❌ **Erro: mod_rewrite**
**Solução:**
1. Edite: `C:\xampp\apache\conf\httpd.conf`
2. Descomente: `LoadModule rewrite_module modules/mod_rewrite.so`
3. Reinicie o Apache

---

## 🚀 VERIFICAÇÃO RÁPIDA

Execute estes passos para verificar se tudo está funcionando:

1. **Teste de Conectividade:**
   ```
   http://localhost/gestao-aguaboa-php/test.php
   ```

2. **Login no Sistema:**
   ```
   http://localhost/gestao-aguaboa-php
   Usuário: Branco
   Senha: 652409
   ```

3. **Verificar phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   Banco: aguaboa_gestao
   ```

---

## 📁 ESTRUTURA DE ARQUIVOS

```
C:\xampp\htdocs\gestao-aguaboa-php\
├── public\
│   ├── index.php          ← Arquivo principal
│   ├── test.php           ← Teste do sistema  
│   └── .htaccess          ← Configuração Apache
├── config\
│   ├── database.php       ← Configuração MySQL
│   └── init.php          ← Inicialização
├── scripts\
│   └── setup_database.php ← Criação do banco
└── src\                   ← Código fonte
```

---

## 💡 DICAS IMPORTANTES

- ✅ **Sempre inicie Apache e MySQL** antes de usar
- ✅ **Use http://localhost** (não https)
- ✅ **Porta padrão:** Apache(80), MySQL(3306)
- ✅ **Logs do Apache:** `C:\xampp\apache\logs\error.log`
- ✅ **Logs do MySQL:** `C:\xampp\mysql\data\*.err`

---

## 🆘 SUPORTE

Se ainda tiver problemas:

1. Verifique os logs do XAMPP
2. Teste com outro projeto PHP simples
3. Reinstale o XAMPP se necessário
4. Verifique antivírus/firewall