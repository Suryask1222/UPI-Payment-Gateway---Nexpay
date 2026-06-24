<?php
/**
 * Nex Pay - Direct Payment Form View
 */
?>

<div class="payment-card direct-pay-card"
    style="max-width: 500px; text-align: left; padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); background-color: var(--bg-surface);">
    <div
        style="text-align: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
        <div class="payment-logo"
            style="font-size: 1.5rem; font-family: var(--font-heading); font-weight: 800; color: var(--text-main); display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fas fa-wallet" style="color: var(--primary);"></i>
            <span><?= h(SITE_NAME) ?></span>
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 6px; font-weight: 500;">Instant Direct
            Payment Desk</p>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/index.php?route=/pay/direct-initiate"
        style="display: flex; flex-direction: column; gap: 1.25rem;">

        <div class="form-group" style="margin-bottom: 0;">
            <label for="payer_name"
                style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Payer
                Full Name <span style="color: var(--danger);">*</span></label>
            <div style="position: relative; display: flex; align-items: center;">
                <i class="far fa-user" style="position: absolute; left: 14px; color: var(--text-muted);"></i>
                <input type="text" id="payer_name" name="payer_name" class="form-control" placeholder="e.g. surya k"
                    style="padding-left: 36px;" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="amount"
                style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Payment
                Amount (INR) <span style="color: var(--danger);">*</span></label>
            <div style="position: relative; display: flex; align-items: center;">
                <span style="position: absolute; left: 14px; font-weight: 700; color: var(--text-muted);">₹</span>
                <input type="number" id="amount" name="amount" step="0.01" min="1" class="form-control"
                    placeholder="100.00"
                    style="padding-left: 36px; font-family: var(--font-heading); font-weight: 700; font-size: 1.1rem; color: var(--success);"
                    required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="payer_type"
                style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Payment
                Purpose / Scope</label>
            <div style="position: relative; display: flex; align-items: center;">
                <i class="far fa-circle-question" style="position: absolute; left: 14px; color: var(--text-muted);"></i>
                <input type="text" id="payer_type" name="payer_type" class="form-control"
                    placeholder="e.g. Internship Fee, Invoice, Cert charges" style="padding-left: 36px;" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="reference_id"
                style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Reference
                ID / Number</label>
            <div style="position: relative; display: flex; align-items: center;">
                <i class="far fa-id-card" style="position: absolute; left: 14px; color: var(--text-muted);"></i>
                <input type="text" id="reference_id" name="reference_id" class="form-control"
                    placeholder="e.g. Intern ID, Invoice ID (Optional)" style="padding-left: 36px;">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="payer_notes"
                style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Optional
                Description / Notes</label>
            <div style="position: relative; display: flex; align-items: center;">
                <i class="far fa-comment-dots" style="position: absolute; left: 14px; color: var(--text-muted);"></i>
                <input type="text" id="payer_notes" name="payer_notes" class="form-control"
                    placeholder="e.g. Domain fees, certificate processing" style="padding-left: 36px;">
            </div>
        </div>

        <button type="submit" class="btn btn-primary"
            style="height: 50px; font-size: 1rem; width: 100%; border-radius: var(--radius-sm); font-weight: 700; margin-top: 0.5rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
            Proceed to Checkout <i class="fas fa-arrow-right" style="margin-left: 6px;"></i>
        </button>
    </form>

    <div
        style="margin-top: 1.5rem; text-align: center; border-top: 1px solid var(--border-color); padding-top: 1rem; display: flex; justify-content: center; align-items: center; gap: 8px; font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">
        <i class="fas fa-shield-halved" style="color: var(--success); font-size: 0.85rem;"></i>
        <span>SECURE SSL 256-BIT ENCRYPTED TRANSACTION</span>
    </div>
</div>