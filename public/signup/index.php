<?php
// GAINZ signup gateway
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAINZ New Recruit</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="auth-background">
    <div class="auth-page">
        <div class="auth-inner">
            <header class="auth-branding">
                <div class="auth-logo">
                    <span class="auth-logo-icon"><i class="fas fa-dumbbell"></i></span>
                    <span class="auth-logo-text">GAINZ</span>
                </div>
                <div class="auth-subtitle">INDUSTRIAL ATHLETICS</div>
            </header>

            <section class="auth-card">
                <div class="auth-card-content">
                    <div class="auth-heading">
                        <span class="auth-heading-title">NEW RECRUIT</span>
                        <div class="auth-line"></div>
                    </div>

                    <div class="auth-subheading">ENLIST FOR PERFORMANCE</div>

                    <label class="auth-label">Email Address</label>
                    <div class="auth-field">
                        <input type="email" placeholder="EXAMPLE@DOMAIN.COM" aria-label="Email Address">
                        <span class="auth-field-icon"><i class="fas fa-envelope"></i></span>
                    </div>

                    <label class="auth-label">Access Code</label>
                    <div class="auth-field">
                        <input type="password" placeholder="● ● ● ● ● ● ● ●" aria-label="Access Code">
                        <span class="auth-field-icon"><i class="fas fa-lock"></i></span>
                    </div>

                    <a href="step1.php" class="auth-button">
                        CREATE ACCOUNT
                    </a>

                    <div class="auth-actions signup-actions">
                        <a href="../login.php" class="auth-action-link">ALREADY A RECRUIT? LOGIN</a>
                    </div>
                </div>
            </section>

            <footer class="auth-footer">
                <div class="auth-footer-links">
                    <a href="#">TERMS OF SERVICE</a>
                    <a href="#">PRIVACY PROTOCOL</a>
                </div>
                <div class="auth-footer-copy">© 2024 GAINZ INDUSTRIAL ATHLETICS. NO WEAKNESS.</div>
                <div class="auth-footer-copy small-copy">V4.0.2</div>
            </footer>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>
