<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?= $title ?>
    </title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            color: #2d2d2d;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        h1 {
            color: #2d6a4f;
            border-bottom: 2px solid #40916c;
            padding-bottom: 0.5rem;
        }

        h2 {
            color: #40916c;
        }

        .badge {
            background: #40916c;
            color: #fff;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: normal;
            margin-left: 1rem;
        }

        .section {
            margin-bottom: 2rem;
        }

        .print-btn {
            text-align: right;
            margin-bottom: 1rem;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="print-btn">
        <button onclick="window.print()"
            style="background:#2d6a4f; color:#fff; border:none; padding:0.5rem 1.5rem; border-radius:20px; cursor:pointer;">
            🖨️ Print / Save as PDF
        </button>
    </div>

    <h1>🌿 Leave No Trace Briefing</h1>
    <p><strong>Tour:</strong>
        <?= htmlspecialchars($tour['tour_name']) ?>
        <span class="badge">
            <?= ucfirst(str_replace('_', ' ', $tour['tour_type'])) ?>
        </span>
    </p>

    <div class="section">
        <h2>🛡️ Safety Guidelines</h2>
        <p>
            <?= nl2br(htmlspecialchars($briefing['safety_text'])) ?>
        </p>
    </div>

    <div class="section">
        <h2>🌱 Environmental Responsibility</h2>
        <p>
            <?= nl2br(htmlspecialchars($briefing['environmental_text'])) ?>
        </p>
    </div>

    <div class="section">
        <h2>🎒 Recommended Equipment</h2>
        <p>
            <?= nl2br(htmlspecialchars($briefing['equipment_text'])) ?>
        </p>
    </div>

    <div class="section">
        <h2>🆘 Emergency Contacts</h2>
        <p>
            <?= nl2br(htmlspecialchars($briefing['emergency_contact'])) ?>
        </p>
    </div>

    <p style="text-align:center; color:#888; margin-top:3rem;">EcoVoyage – Travel Responsibly</p>
</body>

</html>