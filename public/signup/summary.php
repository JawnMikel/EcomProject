<?php
// GAINZ signup summary
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAINZ Signup Summary</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="auth-background" data-signup-page="summary">
    <div class="wizard-page">
        <div class="wizard-inner">
            <header class="wizard-header">
                <div class="auth-logo">
                    <span class="auth-logo-icon"><i class="fas fa-dumbbell"></i></span>
                    <span class="auth-logo-text">GAINZ</span>
                </div>
                <div class="wizard-status">
                    <div>STEP <span id="stepNumber">04</span> / 04</div>
                    <div>MISSION SUMMARY</div>
                </div>
            </header>

            <section class="wizard-card">
                <div class="wizard-card-content">
                    <div class="wizard-progress">
                        <div class="wizard-progress-fill" id="progressFill" style="width: 100%;"></div>
                    </div>

                    <div class="wizard-hero">REVIEW YOUR <span class="wizard-accent">SIGNUP</span></div>
                    <div class="wizard-description">Confirm your selections before launching the GAINZ regimen.</div>

                    <div class="wizard-summary-grid">
                        <div class="wizard-summary-item">
                            <div class="wizard-summary-label">Objective</div>
                            <div class="wizard-summary-value" id="summaryObjective">--</div>
                        </div>
                        <div class="wizard-summary-item">
                            <div class="wizard-summary-label">Selected Plan</div>
                            <div class="wizard-summary-value" id="summaryActivity">--</div>
                        </div>
                        <div class="wizard-summary-item">
                            <div class="wizard-summary-label">Sex</div>
                            <div class="wizard-summary-value" id="summarySex">--</div>
                        </div>
                        <div class="wizard-summary-item">
                            <div class="wizard-summary-label">Weight</div>
                            <div class="wizard-summary-value" id="summaryWeight">--</div>
                        </div>
                        <div class="wizard-summary-item">
                            <div class="wizard-summary-label">Height</div>
                            <div class="wizard-summary-value" id="summaryHeight">--</div>
                        </div>
                        <div class="wizard-summary-item">
                            <div class="wizard-summary-label">Age</div>
                            <div class="wizard-summary-value" id="summaryAge">--</div>
                        </div>
                    </div>

                    <div class="wizard-actions">
                        <button type="button" id="backButton" class="wizard-button secondary">BACK</button>
                        <button type="button" id="nextButton" class="wizard-button">COMPLETE SIGNUP</button>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>
