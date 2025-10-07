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
            background: linear-gradient(135deg, #007fa3, #00a8cc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 550px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-brand {
            max-width: 600px;
            width: 100%;
            height: auto;
            margin: 0 auto 1.5rem;
            display: block;
        }
        
        .system-title {
            color: #007fa3 !important;
            font-size: 2.5rem !important;
            font-weight: bold !important;
            margin-bottom: 0.5rem !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            display: block !important;
            font-family: Arial, sans-serif !important;
        }
        
        .logo h1 {
            color: #007fa3;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: none;
        }
        
        .logo p {
            color: #666;
            font-size: 0.9rem;
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
            width: 100%;
            padding: 1rem;
            background: #007fa3;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #005f7a;
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
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #999;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="<?= BASE_URL ?>/images/logo-aguaboa.svg" alt="Aguaboa - Águas de Santa Bárbara" class="logo-brand">
            <h1 class="system-title" style="display: block !important; color: #007fa3 !important; font-size: 2.5rem !important; font-weight: bold !important;">Web Aguaboa</h1>
            <p>Sistema de Gestão Eficiente</p>
        </div>
        
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">👤 Usuário</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Senha</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">
                🚪 Entrar no Sistema
            </button>
        </form>
        
        <div class="footer">
            <p>Sistema desenvolvido para Aguaboa<br>
            Águas de Santa Bárbara - Versão PHP 1.0</p>
        </div>
    </div>
    
    <script>
        // Auto-focus no campo username
        document.getElementById('username').focus();
        
        // Detectar Enter nos campos
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                if (e.target.id === 'username') {
                    document.getElementById('password').focus();
                } else if (e.target.id === 'password') {
                    e.target.form.submit();
                }
            }
        });
    </script>
</body>
</html>