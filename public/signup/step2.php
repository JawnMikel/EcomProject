<?php
// GAINZ signup step 2
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAINZ Biometrics</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="auth-background" data-signup-page="step2">
    <div class="wizard-page">
        <div class="wizard-inner">
            <header class="wizard-header">
                <div class="auth-logo">
                    <span class="auth-logo-icon"><i class="fas fa-dumbbell"></i></span>
                    <span class="auth-logo-text">GAINZ</span>
                </div>
                <div class="wizard-status">
                    <div>STEP <span id="stepNumber">02</span> / 04</div>
                    <div>BIOMETRICS</div>
                </div>
            </header>

            <section class="wizard-card">
                <div class="wizard-card-content">
                    <div class="wizard-progress">
                        <div class="wizard-progress-fill" id="progressFill" style="width: 50%;"></div>
                    </div>

                    <div class="wizard-hero">ENTER YOUR <span class="wizard-accent">METRICS</span></div>
                    <div class="wizard-description">Enter your physical metrics to calibrate your metabolic profile.</div>

                    <div class="wizard-form-grid">
                        <div class="wizard-field">
                            <label for="heightInput">Height (cm)</label>
                            <input id="heightInput" type="number" placeholder="180" min="100" max="250">
                        </div>
                        <div class="wizard-field">
                            <label for="weightInput">Weight (kg)</label>
                            <input id="weightInput" type="number" placeholder="85.0" step="0.1" min="30" max="200">
                        </div>
                        <div class="wizard-field">
                            <label for="ageInput">Age</label>
                            <input id="ageInput" type="number" placeholder="28" min="14" max="99">
                        </div>
                        <div class="wizard-field">
                            <label>Sex</label>
                            <div class="wizard-toggle-group">
                                <button type="button" class="wizard-toggle signup-toggle" data-value="male">MALE</button>
                                <button type="button" class="wizard-toggle signup-toggle" data-value="female">FEMALE</button>
                            </div>
                        </div>
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
