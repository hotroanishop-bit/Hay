<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - <?php echo htmlspecialchars($siteName ?? 'Hay API Keys'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .maintenance-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .maintenance-icon {
            font-size: 80px;
            margin-bottom: 30px;
            display: block;
        }

        .site-name {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        .maintenance-title {
            font-size: 24px;
            font-weight: 600;
            color: #555;
            margin-bottom: 20px;
        }

        .maintenance-message {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .back-soon {
            font-size: 18px;
            font-weight: 500;
            color: #764ba2;
            margin-top: 20px;
        }

        .divider {
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            margin: 30px auto;
            border-radius: 2px;
        }

        @media (max-width: 480px) {
            .maintenance-container {
                padding: 40px 25px;
            }

            .maintenance-icon {
                font-size: 60px;
            }

            .site-name {
                font-size: 22px;
            }

            .maintenance-title {
                font-size: 20px;
            }

            .maintenance-message {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <span class="maintenance-icon">&#128736;</span>
        
        <h1 class="site-name"><?php echo htmlspecialchars($siteName ?? 'Hay API Keys'); ?></h1>
        
        <div class="divider"></div>
        
        <h2 class="maintenance-title">Under Maintenance</h2>
        
        <p class="maintenance-message">
            <?php echo htmlspecialchars($maintenanceMessage ?? 'We are currently performing scheduled maintenance. Please check back soon.'); ?>
        </p>
        
        <p class="back-soon">We'll be back soon!</p>
    </div>
</body>
</html>
