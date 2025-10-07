# 📦 SISTEMA DE BACKUPS CONFIGURADO

## 🎯 **Localização dos Backups:**
```
C:\xampp\htdocs\Backups\
```

## 🚀 **Como fazer backup:**

### Opção 1 - Backup Completo:
```bash
php criar_backup.php
```

### Opção 2 - Backup Rápido:
```bash
php backup_rapido.php
```

## 📋 **O que é salvo automaticamente:**
- ✅ Todos os arquivos do sistema
- ✅ Banco de dados completo (aguaboa_gestao)
- ✅ Uploads e configurações
- ✅ Sistema de permissões
- ✅ Todos os usuários com senhas

## 📁 **Estrutura do backup:**
```
C:\xampp\htdocs\Backups\
├── gestao-aguaboa-php-backup-2025-10-02-1122\
├── gestao-aguaboa-php-backup-2025-10-02-1628\
└── gestao-aguaboa-php-backup-2025-10-02-1629\
```

## 🔄 **Para restaurar um backup:**
1. Copie a pasta do backup para: `c:\xampp\htdocs\gestao-aguaboa-php\`
2. Importe o arquivo: `backup_database.sql`
3. Acesse: `http://localhost/gestao-aguaboa-php/public/`

## ⚡ **Comandos rápidos:**
```bash
# Fazer backup agora
php backup_rapido.php

# Ver backups disponíveis
dir "C:\xampp\htdocs\Backups\"

# Restaurar último backup
# (copie manualmente a pasta mais recente)
```

---
**✅ Sistema de backup configurado e funcionando!**  
**📍 Todos os backups são salvos em: C:\xampp\htdocs\Backups\**

## 🧭 Tornar o comando BK disponível globalmente
Se quiser digitar apenas `BK` de qualquer pasta do Windows, adicione a pasta do projeto ao PATH ou copie `BK.bat` para uma pasta que já esteja no PATH.

Exemplo rápido (PowerShell, executar como Administrador):

1. Adicione a pasta do projeto ao PATH temporariamente na sessão atual:
	$env:Path += ";C:\xampp\htdocs\gestao-aguaboa-php"

2. Para adicionar permanentemente, use as Variáveis de Ambiente do Windows (Painel de Controle) ou crie um link simbólico:
	mklink "C:\Windows\BK.bat" "C:\xampp\htdocs\gestao-aguaboa-php\BK.bat"

Observação: criar links em C:\Windows requer privilégios de administrador.