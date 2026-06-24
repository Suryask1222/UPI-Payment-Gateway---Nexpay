<?php

?>

<div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
    <form method="GET" action="<?= BASE_URL ?>/admin_transactions.php" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 250px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Search transactions</label>
            <input type="text" name="search" value="<?= h($search ?? '') ?>" class="form-control" placeholder="Search Order ID, UTR, or Payer Name..." style="height: 42px;">
        </div>
        
        <div style="width: 180px; min-width: 150px;">
            <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase;">Status Filter</label>
            <select name="status" class="form-control" style="height: 42px;">
                <option value="">All Transactions</option>
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
            <a href="<?= BASE_URL ?>/admin_transactions.php" class="btn btn-outline" style="height: 42px;">Reset</a>
        </div>
    </form>
</div>

<div class="workspace-grid">
    <?php foreach ($orders as $o): ?>
        <?php 
            $isDuplicate = in_array($o['utr_ref'], $duplicates);
            $history = $userHistories[$o['order_no']] ?? ['approved' => 0, 'total' => 0];
        ?>
        <div class="transaction-card <?= $isDuplicate ? 'duplicate-utr' : '' ?>">
            
            
            <div class="card-row">
                <span class="order-badge"><?= h($o['order_no']) ?></span>
                <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">
                    <?= date('d M Y, H:i', strtotime($o['created_at'])) ?>
                </span>
            </div>

            
            <?php if ($isDuplicate && !empty($o['utr_ref'])): ?>
                <div class="duplicate-banner">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>Duplicate UTR Submission (<?= h($o['utr_ref']) ?>)</span>
                </div>
            <?php endif; ?>

            
            <div style="display: flex; align-items: baseline; margin: 6px 0;">
                <span style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-right: 4px;">₹</span>
                <span style="font-size: 2rem; font-family: var(--font-heading); font-weight: 800; color: var(--success); line-height: 1;">
                    <?= number_format($o['amount'], 2) ?>
                </span>
                <span class="badge badge-<?= $o['status'] ?>" style="margin-left: auto;"><?= h(str_replace('_', ' ', $o['status'])) ?></span>
            </div>

            
            <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; border-top: 1px dashed var(--border-color); padding-top: 10px;">
                <div class="card-row">
                    <span class="lbl">Payer Name:</span>
                    <span class="val"><?= h($o['payer_name'] ?: 'Not submitted') ?></span>
                </div>
                
                <div class="card-row">
                    <span class="lbl">UTR Ref No:</span>
                    <span class="val" style="font-family: monospace; font-weight:700; color: <?= !empty($o['utr_ref']) ? 'var(--primary)' : 'var(--text-muted)' ?>;">
                        <?= h($o['utr_ref'] ?: 'Waiting for submission') ?>
                    </span>
                </div>
                
                <div class="card-row">
                    <span class="lbl">UPI Rotator ID:</span>
                    <span class="val" style="font-size: 0.78rem; font-family: monospace;"><?= h($o['upi_id']) ?></span>
                </div>

                <?php if (!empty($o['payer_notes'])): ?>
                    <div style="margin-top: 4px; padding: 6px 10px; background: var(--bg-base); border-radius: var(--radius-sm); font-size: 0.78rem; color: var(--text-muted);">
                        <strong>Payer Note:</strong> <?= h($o['payer_notes']) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($o['admin_note'])): ?>
                    <div style="margin-top: 4px; padding: 6px 10px; background: var(--primary-light); border-radius: var(--radius-sm); font-size: 0.78rem; color: #818cf8;">
                        <strong>Admin Note:</strong> <?= h($o['admin_note']) ?>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if ($history['total'] > 1): ?>
                <div style="font-size: 0.75rem; color: var(--info); font-weight: 600; display: flex; align-items: center; gap: 5px; margin-top: 4px; padding: 4px 8px; background: var(--info-light); border-radius: 4px;">
                    <i class="fas fa-history"></i>
                    <span>Repeat customer (<?= (int)$history['approved'] ?> of <?= (int)$history['total'] ?> verified)</span>
                </div>
            <?php endif; ?>

            
            <div style="font-size: 0.72rem; color: var(--text-muted); display: flex; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 8px; margin-top: 4px;">
                <span>IP: <?= h($o['user_ip']) ?></span>
                <span title="<?= h($o['user_agent']) ?>" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: right;">
                    <?= h($o['user_agent']) ?>
                </span>
            </div>

            
            <?php if (in_array($o['status'], ['pending', 'under_review', 'on_hold'])): ?>
                <div class="quick-actions">
                    <button onclick="AdminApp.triggerActionModal('<?= $o['order_no'] ?>', 'approved', '<?= $o['utr_ref'] ?>')" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button onclick="AdminApp.triggerActionModal('<?= $o['order_no'] ?>', 'rejected', '<?= $o['utr_ref'] ?>')" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    
                    <?php if ($o['status'] !== 'on_hold'): ?>
                        <button onclick="AdminApp.triggerActionModal('<?= $o['order_no'] ?>', 'on_hold', '<?= $o['utr_ref'] ?>')" class="btn btn-outline" style="border-color: var(--primary); color: #818cf8;">
                            <i class="fas fa-pause"></i> Hold
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?>
        <div class="card text-center" style="grid-column: 1 / -1; padding: 4rem 0;">
            <i class="fas fa-magnifying-glass" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h4 style="font-family: var(--font-heading);">No transactions found</h4>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">Try updating your query or selecting another status filter.</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="action-dialog-modal">
    <div class="modal-content">
        <div class="card-header" style="border-bottom: none; padding-bottom: 0; margin-bottom: 1.5rem;">
            <h3 class="card-title" id="modal-action-label">Update Transaction</h3>
            <button onclick="AdminApp.closeModal('action-dialog-modal')" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <form onsubmit="AdminApp.submitActionForm(event)">
            <input type="hidden" id="form-action-order" name="order">
            <input type="hidden" id="form-action-type" name="action">

            <div style="margin-bottom: 1.25rem; font-size: 0.85rem; color: var(--text-muted);">
                Transaction Order: <span id="modal-order-no" style="font-family: monospace; font-weight:700; color:var(--text-main);"></span>
            </div>

            <div class="form-group">
                <label for="form-action-utr">Confirm UTR / Reference ID</label>
                <input type="text" id="form-action-utr" name="utr" class="form-control" placeholder="12-digit transaction UTR number" maxlength="12">
            </div>

            <div class="form-group" style="margin-bottom: 1.75rem;">
                <label for="form-action-note">Administrator Notes / Feedback</label>
                <textarea id="form-action-note" name="note" class="form-control" placeholder="e.g. Verified in Bank ledger statement, or Rejection reason" rows="3" style="resize:vertical; font-family:var(--font-sans);"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-outline" onclick="AdminApp.closeModal('action-dialog-modal')">Cancel</button>
                <button type="submit" class="btn" id="btn-modal-action-submit">Apply Changes</button>
            </div>
        </form>
    </div>
</div>
