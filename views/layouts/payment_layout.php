<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title ?? 'Payment Gateway') ?> - <?= h(SITE_NAME) ?></title>
    
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body style="background: linear-gradient(135deg, #0b0f19 0%, #151b2c 100%); color: #f8fafc;">
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>

    <div class="payment-wrapper">
        <?= $content ?>
    </div>

    
    <script src="<?= BASE_URL ?>/assets/js/gateway.js"></script>
</body>
</html>
