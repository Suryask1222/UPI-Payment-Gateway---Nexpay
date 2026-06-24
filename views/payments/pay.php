<?php
/**
 * Nex Pay - Redesigned Customer Checkout Page
 */
?>

<div class="razorpay-checkout-wrapper"
    style="width: 100%; display: flex; justify-content: center; align-items: center;">
    <?php if ($order['status'] === 'pending' && empty($order['utr_ref'])): ?>
        <!-- Active Payment Screen (Razorpay Style UI) -->
        <div class="razorpay-checkout-card">
            <!-- Left Pane: Branding & Amount Details -->
            <div class="checkout-left-pane">
                <div class="checkout-brand-header">
                    <i class="fas fa-wallet brand-icon"></i>
                    <span class="brand-text"><?= h(SITE_NAME) ?></span>
                </div>

                <div class="checkout-amount-summary">
                    <span class="amount-title">PAYMENT AMOUNT</span>
                    <div class="amount-val">
                        <span class="currency">₹</span>
                        <span class="num"><?= number_format($order['amount'], 2) ?></span>
                    </div>
                    <div class="order-ref-badge">Order ID: <?= h($order['order_no']) ?></div>
                </div>

                <div class="checkout-secure-footer">
                    <i class="fas fa-shield-halved secure-icon"></i>
                    <span>Secure payments by <?= h(SITE_NAME) ?></span>
                </div>
            </div>

            <!-- Right Pane: Interaction Tabs -->
            <div class="checkout-right-pane">
                <!-- Navigation Tabs -->
                <div class="checkout-tabs-navigation">
                    <button onclick="switchCheckoutTab('qr')" id="btn-tab-qr" class="checkout-tab-btn active">
                        <i class="fas fa-qrcode"></i> Scan QR
                    </button>
                    <button onclick="switchCheckoutTab('apps')" id="btn-tab-apps" class="checkout-tab-btn">
                        <i class="fas fa-mobile-screen-button"></i> UPI Apps
                    </button>
                    <button onclick="switchCheckoutTab('verify')" id="btn-tab-verify" class="checkout-tab-btn">
                        <i class="fas fa-circle-check"></i> Verify Pay
                    </button>
                </div>

                <!-- Tab 1: Dynamic QR Code -->
                <div id="content-tab-qr" class="checkout-tab-content active">
                    <div class="qr-content-pane">
                        <div class="qr-outer-container">
                            <div id="qrcode"></div>
                        </div>

                        <div class="upi-id-copy-strip"
                            onclick="Gateway.copyToClipboard('<?= h($order['upi_id']) ?>', 'upi-copy-visual-id')">
                            <div class="upi-strip-text">
                                <span class="label">VPA Recipient ID</span>
                                <span class="val" id="upi-copy-visual-id"><?= h($order['upi_id']) ?></span>
                            </div>
                            <i class="far fa-copy copy-icon"></i>
                        </div>

                        <p class="payment-instructions">Scan this dynamic QR code using Google Pay, PhonePe, Paytm, or BHIM
                            to pay instantly.</p>
                    </div>
                </div>

                <!-- Tab 2: Mobile App Intents -->
                <div id="content-tab-apps" class="checkout-tab-content">
                    <div class="apps-content-pane">
                        <p class="payment-instructions-bold">Choose an installed UPI application to complete your payment:
                        </p>

                        <div class="intent-apps-grid">
                            <button class="checkout-intent-btn btn-paytm"
                                onclick="Gateway.payWithPaytm('<?= h($mobile) ?>', '<?= h($order['upi_id']) ?>', '<?= h($order['amount']) ?>', '<?= h($order['order_no']) ?>')">
                                <img src="https://img.icons8.com/color/48/paytm.png" alt="Paytm Logo"
                                    class="checkout-app-logo" />
                                <span>Pay with Paytm App</span>
                            </button>

                            <button class="checkout-intent-btn btn-phonepe"
                                onclick="Gateway.payWithPhonePe('<?= h($order['upi_id']) ?>', '<?= h($order['amount']) ?>', '<?= h($order['order_no']) ?>')">
                                <img src="https://img.icons8.com/color/48/phone-pe.png" alt="PhonePe Logo"
                                    class="checkout-app-logo" />
                                <span>Pay with PhonePe App</span>
                            </button>


                        </div>
                    </div>
                </div>

                <!-- Tab 3: UTR Submission -->
                <div id="content-tab-verify" class="checkout-tab-content">
                    <div class="verify-content-pane">
                        <div class="alert-info-box">
                            <i class="fas fa-circle-info"></i>
                            <span>Once your transfer is complete, submit your 12-digit bank UTR ID below to
                                auto-approve.</span>
                        </div>

                        <div class="form-group">
                            <label for="payer_name">Payer Full Name</label>
                            <input type="text" id="payer_name" class="form-control" placeholder="Enter sender name"
                                value="<?= h($order['payer_name'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="utr_input">12-Digit UTR / Reference ID</label>
                            <input type="text" id="utr_input" maxlength="12" class="form-control font-mono-utr"
                                placeholder="12-digit transaction UTR number" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="payer_notes">Optional Notes</label>
                            <input type="text" id="payer_notes" class="form-control"
                                placeholder="Add reference / Invoice details" value="<?= h($order['payer_notes'] ?? '') ?>">
                        </div>

                        <button id="btn-submit-verification" class="btn btn-primary"
                            onclick="Gateway.submitUTR('<?= h($order['order_no']) ?>')"
                            style="width:100%; height:46px; border-radius: var(--radius-sm); font-weight:700;">
                            Verify & Approve Settlement
                        </button>
                    </div>
                </div>

                <!-- Security Trust Footer -->
                <div class="checkout-security-footer">
                    <i class="fas fa-lock security-lock-icon"></i>
                    <span>PCI-DSS Compliant • 256-bit SSL Secure</span>
                </div>
            </div>
        </div>

        <script>
            function switchCheckoutTab(tabName) {
                document.querySelectorAll('.checkout-tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.checkout-tab-content').forEach(content => content.classList.remove('active'));

                document.getElementById('btn-tab-' + tabName).classList.add('active');
                document.getElementById('content-tab-' + tabName).classList.add('active');
            }
        </script>

    <?php else: ?>
        <!-- Post-Checkout / Status Layout Page -->
        <div class="payment-card"
            style="width: 100%; max-width: 480px; padding: 2.5rem; text-align: center; border-radius: var(--radius-lg); border: 1px solid var(--border-color); background-color: var(--bg-surface); box-shadow: var(--shadow-lg);">
            <div class="payment-header" style="justify-content: center; margin-bottom: 1.5rem;">
                <div class="payment-logo"
                    style="font-family: var(--font-heading); font-weight: 800; font-size: 1.3rem; color: var(--text-main); display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-wallet" style="color: var(--primary);"></i>
                    <span><?= h(SITE_NAME) ?></span>
                </div>
            </div>

            <?php if ($order['status'] === 'pending' && !empty($order['utr_ref'])): ?>
                <div>
                    <div class="success-illustration"
                        style="background-color: var(--warning-light); color: var(--warning); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1.25rem;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <h2
                        style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; color: var(--text-main); margin-bottom: 0.5rem;">
                        Verification Pending</h2>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.5rem; line-height: 1.5;">
                        Your payment submission is being reviewed by our verification desk. Once confirmed, you will be
                        redirected.
                    </p>

                    <div
                        style="text-align: left; background: var(--bg-base); padding: 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 0.85rem; margin-bottom: 1.5rem; display:flex; flex-direction:column; gap:8px;">
                        <div style="display:flex; justify-content:space-between;"><span
                                style="color:var(--text-muted);">Amount:</span><strong
                                style="color:var(--text-main);"><?= formatCurrency($order['amount']) ?></strong></div>
                        <div style="display:flex; justify-content:space-between;"><span
                                style="color:var(--text-muted);">Payer:</span><strong
                                style="color:var(--text-main);"><?= h($order['payer_name']) ?></strong></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:var(--text-muted);">UTR
                                Ref:</span><strong
                                style="font-family:monospace; color:var(--primary);"><?= h($order['utr_ref']) ?></strong></div>
                    </div>

                    <button class="btn btn-outline" onclick="window.location.reload();"
                        style="width: 100%; height: 46px; border-radius: var(--radius-sm);">
                        <i class="fas fa-rotate"></i> Refresh Status
                    </button>
                </div>

            <?php elseif ($order['status'] === 'under_review'): ?>
                <div>
                    <div class="success-illustration"
                        style="background-color: var(--info-light); color: var(--info); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1.25rem;">
                        <i class="fas fa-magnifying-glass"></i>
                    </div>
                    <h2
                        style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; color: var(--text-main); margin-bottom: 0.5rem;">
                        Under Review</h2>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.5rem; line-height: 1.5;">
                        Additional manual validation is currently being performed by our accounts desk.
                    </p>

                    <?php if (!empty($order['admin_note'])): ?>
                        <div
                            style="background-color: var(--info-light); color: var(--info); padding: 12px; border-radius: var(--radius-sm); font-size: 0.8rem; margin-bottom: 1.5rem; text-align: left; border: 1px solid rgba(6, 182, 212, 0.15); line-height: 1.4;">
                            <strong>Verification details needed:</strong> <?= h($order['admin_note']) ?>
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-outline" onclick="window.location.reload();"
                        style="width: 100%; height: 46px; border-radius: var(--radius-sm);">
                        <i class="fas fa-rotate"></i> Check for Updates
                    </button>
                </div>

            <?php elseif ($order['status'] === 'on_hold'): ?>
                <div>
                    <div class="success-illustration"
                        style="background-color: var(--primary-light); color: var(--primary); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1.25rem;">
                        <i class="fas fa-pause"></i>
                    </div>
                    <h2
                        style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; color: var(--text-main); margin-bottom: 0.5rem;">
                        Transaction On Hold</h2>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.5rem; line-height: 1.5;">
                        This transaction has been placed on hold. Please reach out to customer support.
                    </p>

                    <?php if (!empty($order['admin_note'])): ?>
                        <div
                            style="background-color: var(--primary-light); color: #818cf8; padding: 12px; border-radius: var(--radius-sm); font-size: 0.8rem; margin-bottom: 1.5rem; text-align: left; border: 1px solid rgba(79, 70, 229, 0.15); line-height: 1.4;">
                            <strong>Feedback:</strong> <?= h($order['admin_note']) ?>
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-outline" onclick="window.location.reload();"
                        style="width: 100%; height: 46px; border-radius: var(--radius-sm);">
                        <i class="fas fa-rotate"></i> Refresh Page
                    </button>
                </div>

            <?php elseif ($order['status'] === 'rejected'): ?>
                <div>
                    <div class="success-illustration"
                        style="background-color: var(--danger-light); color: var(--danger); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1.25rem;">
                        <i class="fas fa-circle-xmark"></i>
                    </div>
                    <h2
                        style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; color: var(--text-main); margin-bottom: 0.5rem;">
                        Verification Failed</h2>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.5rem; line-height: 1.5;">
                        Your payment verification request has been rejected. Please review feedback or initiate a new session.
                    </p>

                    <?php if (!empty($order['admin_note'])): ?>
                        <div
                            style="background-color: var(--danger-light); color: var(--danger); padding: 12px; border-radius: var(--radius-sm); font-size: 0.8rem; margin-bottom: 1.5rem; text-align: left; border: 1px solid rgba(239, 68, 68, 0.15); line-height: 1.4;">
                            <strong>Reason:</strong> <?= h($order['admin_note']) ?>
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-danger" onclick="window.location.href='<?= BASE_URL ?>/pay.php';"
                        style="width: 100%; height: 46px; border-radius: var(--radius-sm);">
                        Try New Payment Session
                    </button>
                </div>

            <?php elseif ($order['status'] === 'expired'): ?>
                <div>
                    <div class="success-illustration"
                        style="background-color: var(--bg-surface-hover); color: var(--text-muted); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1.25rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h2
                        style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; color: var(--text-main); margin-bottom: 0.5rem;">
                        Session Expired</h2>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.5rem; line-height: 1.5;">
                        For security, this checkout session timed out (limit: 15 minutes). Please return to the portal to
                        request a new link.
                    </p>

                    <?php
                    $returnUrl = ($order['payer_type'] === 'intern') ? '../portal/intern/certificate.php' : '../cert_apply.php';
                    ?>
                    <a href="<?= h($returnUrl) ?>" class="btn btn-primary"
                        style="width: 100%; height: 46px; display: inline-flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); font-weight:700;">
                        Return to Portal
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        <?php if ($order['status'] === 'pending' && empty($order['utr_ref'])): ?>
            // Initializing client-side dynamic QR using restored Gateway controller
            Gateway.initQR('qrcode', '<?= $upiUri ?>');
        <?php endif; ?>

        <?php if ($order['status'] === 'pending' || $order['status'] === 'under_review' || $order['status'] === 'on_hold'): ?>
            // Boot status polling routine
            Gateway.startStatusPolling('<?= $order['order_no'] ?>');
        <?php endif; ?>
    });
</script>