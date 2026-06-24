<?php
/**
 * Nex Pay - Payment Links Management View
 */
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 10px;">
    <div>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Generate, monitor, and copy manual payment checkout links for candidates, interns, and client accounts.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.75rem;">
        <div class="card-header" style="margin-bottom: 1.5rem;">
            <h3 class="card-title"><i class="fas fa-link" style="color: var(--primary); margin-right: 8px;"></i>Generate New Payment Link</h3>
        </div>
        
        <form onsubmit="PaymentLinksApp.generateLink(event)" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div class="form-group">
                <label for="link-amount">Amount (INR) <span style="color: var(--danger);">*</span></label>
                <div style="position: relative; display: flex; align-items: center;">
                    <span style="position: absolute; left: 12px; font-weight: 700; color: var(--text-muted);">₹</span>
                    <input type="number" id="link-amount" name="amount" step="0.01" min="1" class="form-control" placeholder="100.00" style="padding-left: 28px;" required>
                </div>
            </div>

            <input type="hidden" name="payer_type" value="intern">

            <div class="form-group">
                <label for="link-upi-account">UPI Target Account</label>
                <select id="link-upi-account" name="upi_account_id" class="form-control">
                    <option value="">Auto-Rotate (Round-Robin Pool)</option>
                    <?php foreach ($upiAccounts as $upi): ?>
                        <option value="<?= $upi['id'] ?>" <?= !$upi['active'] ? 'disabled' : '' ?>>
                            <?= h($upi['payee_name']) ?> (<?= h($upi['upi_id']) ?>) <?= !$upi['active'] ? '[DISABLED]' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="link-reference-id">Reference ID / Number</label>
                <input type="text" id="link-reference-id" name="reference_id" class="form-control" placeholder="e.g. Intern ID, Invoice ID (Optional)">
            </div>

            <div class="form-group">
                <label for="link-payer-name">Payer Full Name (Pre-fill)</label>
                <input type="text" id="link-payer-name" name="payer_name" class="form-control" placeholder="e.g. John Doe">
            </div>

            <div class="form-group">
                <label for="link-payer-notes">Description / Notes (Pre-fill)</label>
                <input type="text" id="link-payer-notes" name="payer_notes" class="form-control" placeholder="e.g. Certificate charge, Invoice settlement">
            </div>

            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                <button type="submit" class="btn btn-primary" id="btn-generate-link" style="height: 44px; min-width: 200px;">
                    <i class="fas fa-plus"></i> Generate Payment Link
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
    <form method="GET" action="<?= BASE_URL ?>/admin_payment_links.php" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 250px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Search Payment Links</label>
            <input type="text" name="search" value="<?= h($search ?? '') ?>" class="form-control" placeholder="Search Order ID, UTR, Payer Name, or Ref ID..." style="height: 42px;">
        </div>
        
        <div style="width: 180px; min-width: 150px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Status Filter</label>
            <select name="status" class="form-control" style="height: 42px;">
                <option value="">All Links</option>
                <option value="pending" <?= ($statusFilter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="under_review" <?= ($statusFilter ?? '') === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                <option value="approved" <?= ($statusFilter ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= ($statusFilter ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="on_hold" <?= ($statusFilter ?? '') === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                <option value="expired" <?= ($statusFilter ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="height: 42px;">Filter</button>
            <a href="<?= BASE_URL ?>/admin_payment_links.php" class="btn btn-outline" style="height: 42px;">Reset</a>
        </div>
    </form>
</div>

<div class="card" style="padding: 1.5rem;">
    <div class="card-header" style="margin-bottom: 1rem;">
        <h3 class="card-title">Generated Checkout Links</h3>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Order No / Date</th>
                    <th>Payer Details</th>
                    <th>Target UPI</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment URL / Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php
                        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $paymentUrl = $proto . '://' . $host . BASE_URL . '/pay.php?order=' . $order['order_no'];
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; font-family: monospace; color: var(--text-main);"><?= h($order['order_no']) ?></div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?= h($order['payer_name'] ?: 'Sender Unsubmitted') ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                Scope: <strong style="color: var(--primary);"><?= h(ucwords(str_replace('_', ' ', $order['payer_type']))) ?></strong>
                                <?php if (!empty($order['reference_id'])): ?>
                                    | Ref ID: <strong style="color: var(--text-main);"><?= h($order['reference_id']) ?></strong>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 0.8rem;"><?= h($order['payee_name']) ?></div>
                            <div style="font-family: monospace; font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;"><?= h($order['upi_id']) ?></div>
                        </td>
                        <td>
                            <strong style="color: var(--success); font-size: 0.95rem;"><?= formatCurrency($order['amount']) ?></strong>
                        </td>
                        <td>
                            <span class="badge badge-<?= $order['status'] ?>"><?= h(str_replace('_', ' ', $order['status'])) ?></span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; align-items: center; max-width: 250px;">
                                <input type="text" readonly value="<?= h($paymentUrl) ?>" class="form-control" style="font-size: 0.75rem; height: 32px; padding: 4px 8px; width: 180px; font-family: monospace;" id="url-<?= $order['order_no'] ?>" onclick="this.select()">
                                <button onclick="PaymentLinksApp.copyUrl('<?= $order['order_no'] ?>')" class="btn btn-outline" style="height: 32px; padding: 4px 10px; font-size: 0.75rem; border-color: var(--primary); color: #818cf8;" id="btn-copy-<?= $order['order_no'] ?>">
                                    <i class="far fa-copy"></i>
                                </button>
                                <a href="<?= h($paymentUrl) ?>" target="_blank" class="btn btn-outline" style="height: 32px; padding: 4px 10px; font-size: 0.75rem;">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <?php if (in_array($order['status'], ['pending', 'under_review', 'on_hold'])): ?>
                                    <a href="<?= BASE_URL ?>/admin_transactions.php?search=<?= urlencode($order['order_no']) ?>" class="btn btn-outline btn-success" style="padding: 4px 8px; font-size: 0.75rem; height: 32px;">
                                        <i class="fas fa-shield-halved"></i> Verify
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] === 'pending' && empty($order['utr_ref'])): ?>
                                    <button onclick="PaymentLinksApp.deleteLink('<?= $order['order_no'] ?>')" class="btn btn-outline btn-danger" style="padding: 4px 8px; font-size: 0.75rem; height: 32px;">
                                        <i class="far fa-trash-can"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 3rem 0;">
                            <i class="fas fa-link" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <h4 style="font-family: var(--font-heading);">No payment links recorded</h4>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">Use the generator above to create custom checkouts.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="link-success-modal">
    <div class="modal-content" style="max-width: 480px; text-align: center;">
        <div class="card-header" style="border-bottom: none; padding-bottom: 0; margin-bottom: 1rem; justify-content: flex-end;">
            <button onclick="AdminApp.closeModal('link-success-modal')" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="success-illustration" style="background-color: var(--success-light); color: var(--success); width: 64px; height: 64px; margin: 0 auto 1.25rem;">
            <i class="fas fa-link"></i>
        </div>
        
        <h3 style="font-family: var(--font-heading); font-weight: 800; font-size: 1.4rem; margin-bottom: 0.5rem;">Payment Link Ready!</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">The checkout session has been initialized. Send the URL below to the payer.</p>
        
        <div class="form-group" style="text-align: left; margin-bottom: 1.5rem;">
            <label style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Checkout URL</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" id="generated-url-field" readonly class="form-control" style="font-family: monospace; font-size: 0.82rem;" onclick="this.select()">
                <button onclick="PaymentLinksApp.copyGeneratedUrl()" class="btn btn-primary" style="min-width: 90px;" id="btn-copy-modal">
                    Copy
                </button>
            </div>
        </div>

        <div style="display:flex; justify-content:center; gap:10px;">
            <a href="" id="btn-modal-test-link" target="_blank" class="btn btn-outline" style="width: 100%;">
                <i class="fas fa-external-link-alt"></i> Open Checkout
            </a>
            <button type="button" class="btn btn-primary" onclick="AdminApp.closeModal('link-success-modal'); window.location.reload();" style="width: 100%;">
                Done
            </button>
        </div>
    </div>
</div>

<script>
    const PaymentLinksApp = {
        generateLink: function(event) {
            event.preventDefault();
            const btn = document.getElementById('btn-generate-link');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Generating...';

            const formData = new FormData(event.target);

            fetch(BASE_URL + '/index.php?route=/admin/payment-links/generate', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.success) {
                    document.getElementById('generated-url-field').value = data.payment_url;
                    document.getElementById('btn-modal-test-link').href = data.payment_url;
                    document.getElementById('btn-copy-modal').innerHTML = 'Copy';
                    
                    AdminApp.openModal('link-success-modal');
                } else {
                    alert(data.message || 'Failed to generate payment link.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                console.error(err);
                alert('A network error occurred.');
            });
        },

        copyUrl: function(orderNo) {
            const input = document.getElementById('url-' + orderNo);
            const btn = document.getElementById('btn-copy-' + orderNo);
            
            if (!input || !btn) return;
            
            navigator.clipboard.writeText(input.value).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check" style="color: var(--success);"></i>';
                btn.style.borderColor = 'var(--success)';
                
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.style.borderColor = '';
                }, 2000);
            });
        },

        copyGeneratedUrl: function() {
            const input = document.getElementById('generated-url-field');
            const btn = document.getElementById('btn-copy-modal');
            
            if (!input || !btn) return;
            
            navigator.clipboard.writeText(input.value).then(() => {
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.style.backgroundColor = 'var(--success)';
                
                setTimeout(() => {
                    btn.innerHTML = 'Copy';
                    btn.style.backgroundColor = '';
                }, 2000);
            });
        },

        deleteLink: function(orderNo) {
            if (!confirm('Are you sure you want to cancel and delete this unused payment link?')) return;
            
            const formData = new FormData();
            formData.append('order_no', orderNo);

            fetch(BASE_URL + '/index.php?route=/admin/payment-links/delete', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to delete payment link.');
                }
            });
        }
    };
</script>
