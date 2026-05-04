<?php
// GAINZ signup step 1
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAINZ Recruit Objective</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="auth-background" data-signup-page="step1">
    <div class="wizard-page">
        <div class="wizard-inner">
            <header class="wizard-header">
                <div class="auth-logo">
                    <span class="auth-logo-icon"><i class="fas fa-dumbbell"></i></span>
                    <span class="auth-logo-text">GAINZ</span>
                </div>
                <div class="wizard-status">
                    <div>STEP <span id="stepNumber">01</span> / 04</div>
                    <div>MISSION OBJECTIVE</div>
                </div>
            </header>

            <section class="wizard-card">
                <div class="wizard-card-content">
                    <div class="wizard-progress">
                        <div class="wizard-progress-fill" id="progressFill" style="width: 25%;"></div>
                    </div>

                    <div class="wizard-hero">CHOOSE YOUR <span class="wizard-accent">OBJECTIVE</span></div>
                    <div class="wizard-description">Select your primary training directive to calibrate your workout architecture.</div>

                    <div class="wizard-option-grid">
                        <button type="button" class="wizard-option signup-option" data-value="strength" data-label="Strength & Power">
                            <div class="wizard-option-title"><i class="fas fa-dumbbell"></i> STRENGTH & POWER</div>
                            <div class="wizard-option-description">Heavy compound movements and raw explosive force.</div>
                        </button>
                        <button type="button" class="wizard-option signup-option" data-value="endurance" data-label="Endurance & Cardio">
                            <div class="wizard-option-title"><i class="fas fa-bolt"></i> ENDURANCE & CARDIO</div>
                            <div class="wizard-option-description">High-intensity conditioning for sustained output.</div>
                        </button>
                        <button type="button" class="wizard-option signup-option" data-value="body" data-label="Body Composition">
                            <div class="wizard-option-title"><i class="fas fa-chart-line"></i> BODY COMPOSITION</div>
                            <div class="wizard-option-description">Hypertrophy-focused programming for definition.</div>
                        </button>
                    </div>

                    <div class="wizard-actions">
                        <button type="button" id="backButton" class="wizard-button secondary">BACK</button>
                        <button type="button" id="nextButton" class="wizard-button">NEXT STEP</button>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>
