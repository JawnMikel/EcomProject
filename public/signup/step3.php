<?php
// GAINZ signup step 3
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAINZ Program Goals</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="auth-background" data-signup-page="step3">
    <div class="wizard-page">
        <div class="wizard-inner">
            <header class="wizard-header">
                <div class="auth-logo">
                    <span class="auth-logo-icon"><i class="fas fa-dumbbell"></i></span>
                    <span class="auth-logo-text">GAINZ</span>
                </div>
                <div class="wizard-status">
                    <div>STEP <span id="stepNumber">03</span> / 04</div>
                    <div>TRAINING TACTICS</div>
                </div>
            </header>

            <section class="wizard-card">
                <div class="wizard-card-content">
                    <div class="wizard-progress">
                        <div class="wizard-progress-fill" id="progressFill" style="width: 75%;"></div>
                    </div>

                    <div class="wizard-hero">CHOOSE YOUR <span class="wizard-accent">ROUTINE</span></div>
                    <div class="wizard-description">Choose the plan format that matches your training schedule.</div>

                    <div class="wizard-option-grid">
                        <button type="button" class="wizard-option signup-option" data-value="hypertrophy">
                            <div class="wizard-option-title"><i class="fas fa-fire"></i> HYPERTROPHY ROUTINE</div>
                            <div class="wizard-option-description">Focused volume for muscle density and shape.</div>
                        </button>
                        <button type="button" class="wizard-option signup-option" data-value="split">
                            <div class="wizard-option-title"><i class="fas fa-calendar-alt"></i> SPLIT TRAINING</div>
                            <div class="wizard-option-description">Balanced split sessions for muscle recovery and growth.</div>
                        </button>
                        <button type="button" class="wizard-option signup-option" data-value="fullbody">
                            <div class="wizard-option-title"><i class="fas fa-layer-group"></i> FULL BODY</div>
                            <div class="wizard-option-description">Efficient compound days for maximum throughput.</div>
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
