<!DOCTYPE html>
<html lang="es">
<head>
    <?=$head?>
    <title><?=$title?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&family=Exo+2:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --violet:   #8b5cf6;
            --violet-l: #a78bfa;
            --violet-d: #4c1d95;
            --green:    #10d9a0;
            --green-l:  #6eecd4;
            --cyan:     #22d3ee;
            --cyan-l:   #67e8f9;
            --bg:       #050814;
            --bg2:      #0a0f1e;
            --bg3:      #0f1629;
            --panel:    #0d1321cc;
            --border:   rgba(139,92,246,0.22);
            --border-g: rgba(16,217,160,0.2);
            --text:     #e2e8f0;
            --muted:    #64748b;
            --danger:   #f43f5e;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Exo 2', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ── Fondo animado ── */
        .bg-layer {
            position: fixed; inset: 0; z-index: 0; overflow: hidden;
        }

        /* Orbes de color en el fondo */
        .orb {
            position: absolute; border-radius: 50%;
            filter: blur(80px); opacity: 0.25;
            animation: drift 12s ease-in-out infinite alternate;
        }
        .orb-1 { width: 500px; height: 500px; background: var(--violet); top: -150px; left: -150px; animation-duration: 14s; }
        .orb-2 { width: 400px; height: 400px; background: var(--green);  bottom: -100px; right: -100px; animation-duration: 11s; animation-delay: -4s; }
        .orb-3 { width: 300px; height: 300px; background: var(--cyan);   top: 40%; left: 60%; animation-duration: 9s; animation-delay: -2s; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(40px, 30px) scale(1.08); }
        }

        /* Grid de puntos */
        .dot-grid {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(139,92,246,0.18) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
        }

        /* ── Card ── */
        .login-card {
            position: relative; z-index: 10;
            width: 100%; max-width: 420px;
            margin: 1.5rem;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 20px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow:
                0 0 0 1px rgba(139,92,246,0.08),
                0 20px 60px rgba(0,0,0,0.6),
                0 0 80px rgba(139,92,246,0.12);
            animation: cardIn 0.6s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(30px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Línea superior de color */
        .card-topline {
            height: 3px;
            background: linear-gradient(90deg, var(--violet), var(--cyan), var(--green));
            border-radius: 20px 20px 0 0;
        }

        /* ── Header ── */
        .login-header {
            padding: 2rem 2rem 1.2rem;
            text-align: center;
        }

        .logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px; height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(16,217,160,0.15));
            border: 1px solid var(--border);
            margin-bottom: 1rem;
            font-size: 28px;
            box-shadow: 0 0 20px rgba(139,92,246,0.2);
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-5px); }
        }

        .login-header h1 {
            font-family: 'Orbitron', monospace;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            background: linear-gradient(135deg, var(--violet-l), var(--cyan-l));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.3rem;
        }

        .login-header p {
            font-size: 0.78rem;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* ── Formulario ── */
        .login-form {
            padding: 0.5rem 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            font-family: 'Orbitron', monospace;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 15px;
            pointer-events: none;
            transition: color 0.25s;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 0.9rem 0.75rem 2.6rem;
            background: rgba(10,15,30,0.7);
            border: 1px solid rgba(139,92,246,0.2);
            border-radius: 10px;
            font-size: 0.95rem;
            color: var(--text);
            font-family: 'Exo 2', sans-serif;
            transition: all 0.25s;
            outline: none;
        }
        .form-control::placeholder { color: var(--muted); }
        .form-control:focus {
            border-color: var(--violet);
            background: rgba(10,15,30,0.9);
            box-shadow: 0 0 0 3px rgba(139,92,246,0.14),
                        0 0 16px rgba(139,92,246,0.1);
        }
        .form-control:focus + .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--violet-l);
        }
        /* Mover el icono antes del input en el DOM — usamos order trick */
        .input-wrap { display: flex; align-items: center; }
        .input-wrap .form-control { order: 2; }
        .input-wrap .input-icon   { order: 1; position: absolute; left: 14px; }

        /* Recordarme */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .remember-row input[type="checkbox"] {
            appearance: none;
            width: 16px; height: 16px;
            border: 1px solid rgba(139,92,246,0.35);
            border-radius: 4px;
            background: rgba(10,15,30,0.7);
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .remember-row input[type="checkbox"]:checked {
            background: var(--violet);
            border-color: var(--violet);
        }
        .remember-row input[type="checkbox"]:checked::after {
            content: '✓';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: white;
        }
        .remember-row label {
            font-size: 0.82rem;
            color: var(--muted);
            cursor: pointer;
            user-select: none;
        }

        /* ── Botón ── */
        .btn-login {
            width: 100%;
            padding: 0.85rem;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--violet) 0%, #6d28d9 50%, var(--cyan) 140%);
            color: white;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: 'Orbitron', monospace;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
            margin-top: 0.3rem;
            box-shadow: 0 4px 20px rgba(139,92,246,0.3);
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(139,92,246,0.45), 0 0 20px rgba(34,211,238,0.2);
        }
        .btn-login:hover::before { left: 100%; }
        .btn-login:active { transform: translateY(0); }

        /* ── Error/Flash ── */
        .alert-error {
            background: rgba(244,63,94,0.1);
            border: 1px solid rgba(244,63,94,0.3);
            border-radius: 8px;
            padding: 0.7rem 1rem;
            font-size: 0.83rem;
            color: #fb7185;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ── Divider ── */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
            margin: 0 2rem;
        }

        /* ── Footer ── */
        .login-footer {
            padding: 1.2rem 2rem 1.8rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }
        .login-footer p {
            font-size: 0.8rem;
            color: var(--muted);
        }
        .login-footer a {
            color: var(--violet-l);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .login-footer a:hover { color: var(--cyan-l); }

        /* Badge de versión */
        .version-badge {
            position: fixed;
            bottom: 1.2rem; right: 1.4rem;
            z-index: 20;
            font-family: 'Orbitron', monospace;
            font-size: 0.6rem;
            letter-spacing: 2px;
            color: rgba(100,116,139,0.5);
        }
    </style>
</head>

<body>

    <!-- Fondo -->
    <div class="bg-layer">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="dot-grid"></div>
    </div>

    <!-- Card -->
    <div class="login-card">
        <div class="card-topline"></div>

        <div class="login-header">
            <div class="logo-wrap">🚀</div>
            <h1>Terminal Principal</h1>
            <p>Sistema de Gestión</p>
        </div>

        <?php if (!empty($error)): ?>
        <div style="padding: 0 2rem 0.5rem;">
            <div class="alert-error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="" id="loginForm">

            <div class="form-group">
                <label class="form-label" for="email">Correo Electrónico</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="usuario@empresa.com"
                        autocomplete="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                    <span class="input-icon">✉</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        autocomplete="current-password"
                    >
                    <span class="input-icon">🔒</span>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordar sesión</label>
            </div>

            <button type="submit" class="btn-login">
                Ingresar al sistema
            </button>

        </form>

        <div class="divider"></div>

        <div class="login-footer">
            <p><a href="<?= $recuperar_url ?? '#' ?>">¿Olvidaste tu contraseña?</a></p>
            <p>¿No tenés cuenta? <a href="<?= $registro_url ?? '#' ?>">Registrate</a></p>
        </div>
    </div>

    <div class="version-badge">v1.0 · SYS</div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email    = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email.trim()) {
                e.preventDefault();
                showError('Por favor ingresá tu correo electrónico.');
                return;
            }
            if (!password.trim()) {
                e.preventDefault();
                showError('Por favor ingresá tu contraseña.');
                return;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showError('El correo ingresado no es válido.');
                return;
            }
            console.log('Formulario válido — enviando...');
        });

        function showError(msg) {
            // Eliminar error previo si existe
            const prev = document.querySelector('.alert-error');
            if (prev) prev.remove();

            const div = document.createElement('div');
            div.className = 'alert-error';
            div.style.margin = '0 2rem 0.5rem';
            div.style.animation = 'cardIn 0.3s ease both';
            div.textContent = '⚠️ ' + msg;

            const form = document.getElementById('loginForm');
            form.parentNode.insertBefore(div, form);
        }
    </script>
</body>
</html>