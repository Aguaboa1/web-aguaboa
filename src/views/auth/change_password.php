<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #007fa3, #00a8cc);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            background: rgba(255,255,255,0.1);
            margin-left: 0.5rem;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #007fa3;
            box-shadow: 0 0 0 3px rgba(0, 127, 163, 0.1);
        }
        
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 1rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007fa3;
            color: white;
        }
        
        .btn-primary:hover {
            background: #005f7a;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔒 Alterar Senha</h1>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="user-info">
                👤 <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] === 'admin' ? 'Admin' : 'Equipe' ?>)
            </div>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>/crm">← Voltar para Página Inicial</a>
                <a href="<?= BASE_URL ?>/auth/logout">Sair</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; color: #007fa3;">Alterar Senha</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">🔒 Senha Atual</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">🆕 Nova Senha</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <small style="color: #666;">Mínimo de 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">✅ Confirmar Nova Senha</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">💾 Salvar Nova Senha</button>
                    <a href="<?= BASE_URL ?>/crm" class="btn btn-secondary">❌ Cancelar (Página Inicial)</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Validação de confirmação de senha
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('As senhas não coincidem');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Auto-focus no primeiro campo
        document.getElementById('current_password').focus();
    </script>
</body>
</html>