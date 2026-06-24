<?php

?>

<div class="payment-card" style="max-width: 500px;">

    <div style="text-align: center; margin-bottom: 2rem;">
        <div class="brand" style="justify-content: center; font-size: 1.4rem; color: var(--text-main);">
            <i class="fas fa-wallet" style="color: var(--success);"></i>
            <span>Nex Pay</span>
        </div>
    </div>


    <div class="success-illustration"
        style="background-color: var(--success-light); color: var(--success); margin-bottom: 1.5rem;">
        <i class="fas fa-circle-check"></i>
    </div>

    <h2
        style="font-family: var(--font-heading); font-weight: 800; font-size: 1.6rem; color: var(--text-main); margin-bottom: 0.5rem;">
        Payment Verified</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; line-height: 1.5;">
        "Your payment has been successfully verified."
    </p>


    <div
        style="text-align: left; background: var(--bg-base); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem; display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem;">
        <div
            style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--border-color); padding-bottom:8px;">
            <span style="color:var(--text-muted); font-weight:600;">Order Number</span>
            <span
                style="font-family:monospace; font-weight:700; color:var(--text-main);"><?= h($order['order_no']) ?></span>
        </div>

        <div
            style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--border-color); padding-bottom:8px;">
            <span style="color:var(--text-muted); font-weight:600;">Amount Paid</span>
            <strong style="color:var(--success); font-size: 1rem;"><?= formatCurrency($order['amount']) ?></strong>
        </div>

        <div
            style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--border-color); padding-bottom:8px;">
            <span style="color:var(--text-muted); font-weight:600;">Payer Name</span>
            <span
                style="font-weight:700; color:var(--text-main);"><?= h($order['payer_name'] ?: 'Verified Customer') ?></span>
        </div>

        <div
            style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--border-color); padding-bottom:8px;">
            <span style="color:var(--text-muted); font-weight:600;">UTR Reference</span>
            <span
                style="font-family:monospace; font-weight:700; color:var(--primary);"><?= h($order['utr_ref']) ?></span>
        </div>

        <div style="display:flex; justify-content:space-between; padding-bottom:2px;">
            <span style="color:var(--text-muted); font-weight:600;">Verification Date</span>
            <span
                style="font-weight:700; color:var(--text-main);"><?= date('d M Y, H:i', strtotime($order['updated_at'])) ?></span>
        </div>
    </div>


    <?php
    $payerType = $order['payer_type'] ?? '';
    $isCertOnly = false;

    try {
        if ($payerType === 'cert_user') {
            $isCertOnly = true;
        } elseif ($payerType === 'intern' && !empty($order['reference_id'])) {

            $chkCert = $pdo->prepare("SELECT COUNT(*) FROM certificate_requests WHERE id = ?");
            $chkCert->execute([$order['reference_id']]);
            if ($chkCert->fetchColumn() > 0) {
                $isCertOnly = true;
            }
        }
    } catch (PDOException $e) {
        error_log("Success view certificate check ignored (table may not exist): " . $e->getMessage());
    }


    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = preg_replace('#/pay(/.*)?$#', '', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));
    $siteBase = $proto . '://' . $host . rtrim($basePath, '/');

    // Redirect directly back to the payment portal index where they paid
    $portalRedirect = $siteBase . '/pay.php';
    $btnText = "Make Another Payment";
    $btnIcon = '<i class="fas fa-arrow-left"></i>';
    ?>





    <a href="<?= h($portalRedirect) ?>" class="btn btn-success"
        style="width: 100%; height: 48px; border-radius: var(--radius-sm); text-decoration: none; font-size: 0.95rem;">
        <span><?= $btnText ?></span>
        <?= $btnIcon ?>
    </a>


    <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: 1.5rem;">
        Redirecting you back in <span id="countdown-sec" style="font-weight: 700; color: var(--text-main);">10</span>
        seconds...
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let sec = 10;
        const countdownEl = document.getElementById('countdown-sec');
        const redirectUrl = '<?= addslashes($portalRedirect) ?>';

        const timer = setInterval(() => {
            sec--;
            if (countdownEl) countdownEl.textContent = sec;
            if (sec <= 0) {
                clearInterval(timer);
                window.location.href = redirectUrl;
            }
        }, 1000);
    });
</script>