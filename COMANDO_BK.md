# 🚀 COMANDO BK - BACKUP INSTANTÂNEO

## ⚡ **Como usar:**

### Opção 1 - Comando PHP:
```bash
php BK.php
```

### Opção 2 - Arquivo Batch (Windows):
```bash
BK.bat
```

### Opção 3 - PowerShell:
```powershell
.\BK.ps1
```

## 🎯 **O que acontece quando você digita BK:**

1. ✅ **Backup automático** criado em `C:\xampp\htdocs\Backups\`
2. ✅ **Arquivos copiados** - Todo o sistema
3. ✅ **Banco exportado** - MySQL completo
4. ✅ **Nome com timestamp** - gestao-aguaboa-php-backup-YYYY-MM-DD-HHMM
5. ✅ **Info criada** - Arquivo BK_INFO.md com detalhes

## 📦 **Resultado:**
```
C:\xampp\htdocs\Backups\
└── gestao-aguaboa-php-backup-2025-10-02-1634\
    ├── backup_database.sql      (Banco de dados)
    ├── BK_INFO.md              (Info do backup)
    ├── config\                 (Configurações)
    ├── src\                    (Código fonte)
    ├── public\                 (Arquivos públicos)
    └── [todos os arquivos...]
```

## ⚡ **Comandos Rápidos:**

**No Terminal/PowerShell:**
```bash
# Navegar para o projeto
cd C:\xampp\htdocs\gestao-aguaboa-php

# Fazer backup
php BK.php
```

**Ainda mais rápido:**
```bash
# Duplo clique no arquivo
BK.bat
```

## 🔥 **Super Rápido:**
- Salve `BK.bat` na área de trabalho
- Duplo clique quando quiser backup
- Pronto! 🎉

---
**🎯 Comando BK = Backup instantâneo e seguro!**