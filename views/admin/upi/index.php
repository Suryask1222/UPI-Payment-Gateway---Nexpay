<?php

?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 10px;">
    <div>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Configure target UPI IDs for round-robin transaction shuffling and portal rotation.</p>
    </div>
    <button onclick="AdminApp.openAddUpiModal()" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New UPI ID
    </button>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem;">
    <?php foreach ($accounts as $a): ?>
        <div class="card" style="margin-bottom: 0; padding: 1.5rem; display: flex; flex-direction: column; gap: 12px; position: relative;">
            
            
            <?php if ($a['is_default']): ?>
                <div style="position: absolute; top: -10px; right: 15px; background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: #0b0f19; font-size: 0.65rem; font-weight: 800; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: var(--shadow-sm);">
                    <i class="fas fa-star" style="margin-right: 4px;"></i> System Default
                </div>
            <?php endif; ?>

            
            <div>
                <h4 style="font-family: var(--font-heading); font-weight: 700; font-size: 1.1rem; color: var(--text-main); margin-bottom: 2px;">
                    <?= h($a['payee_name']) ?>
                </h4>
                <div style="font-family: monospace; font-size: 0.85rem; color: var(--primary); font-weight: 600; display:flex; align-items:center; gap:6px;">
                    <i class="far fa-circle-dot" style="font-size: 0.75rem;"></i>
                    <span><?= h($a['upi_id']) ?></span>
                </div>
            </div>

            
            <div style="display: flex; gap: 8px;">
                <span class="badge badge-<?= $a['active'] ? 'approved' : 'rejected' ?>">
                    <?= $a['active'] ? 'Active' : 'Disabled' ?>
                </span>
                <span class="badge badge-on_hold" style="text-transform: uppercase; background: var(--bg-surface-hover); color: var(--text-main); border: 1px solid var(--border-color);">
                    Pool: <?= h(ucwords($a['purpose'])) ?>
                </span>
            </div>

            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: var(--bg-base); padding: 10px; border-radius: var(--radius-sm); font-size: 0.8rem; margin: 4px 0;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">Settled Volume</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--success); margin-top: 2px;">
                        <?= formatCurrency($a['total_revenue']) ?>
                    </div>
                </div>
                <div>
                    <div style="color: var(--text-muted); font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">Transactions</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main); margin-top: 2px;">
                        <?= (int)$a['tx_count'] ?> orders
                    </div>
                </div>
            </div>

            
            <div style="font-size: 0.72rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                <span>Last Rotated:</span>
                <span style="font-weight: 600; color: var(--text-main);">
                    <?= $a['last_used_at'] ? date('d M H:i', strtotime($a['last_used_at'])) : 'Never used' ?>
                </span>
            </div>

            
            <div style="display: flex; gap: 6px; border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 4px; flex-wrap: wrap;">
                
                <button onclick="AdminApp.openEditUpiModal(<?= $a['id'] ?>, '<?= h($a['upi_id']) ?>', '<?= h($a['payee_name']) ?>', '<?= h($a['purpose']) ?>')" class="btn btn-outline" style="flex:1; padding: 0.4rem; font-size: 0.78rem;">
                    <i class="far fa-edit"></i> Edit
                </button>

                
                <button onclick="AdminApp.toggleUpi(<?= $a['id'] ?>, <?= $a['active'] ? 0 : 1 ?>)" class="btn btn-outline" style="flex:1; padding: 0.4rem; font-size: 0.78rem; border-color: <?= $a['active'] ? 'var(--danger)' : 'var(--success)' ?>; color: <?= $a['active'] ? 'var(--danger)' : 'var(--success)' ?>;">
                    <i class="fas <?= $a['active'] ? 'fa-ban' : 'fa-check' ?>"></i> <?= $a['active'] ? 'Disable' : 'Enable' ?>
                </button>

                
                <?php if (!$a['is_default'] && $a['active']): ?>
                    <button onclick="AdminApp.setUpiDefault(<?= $a['id'] ?>)" class="btn btn-outline" style="width:100%; padding: 0.4rem; font-size: 0.78rem; border-color: var(--warning); color: var(--warning);">
                        <i class="far fa-star"></i> Set as Default
                    </button>
                <?php endif; ?>

                
                <button onclick="AdminApp.deleteUpi(<?= $a['id'] ?>)" class="btn btn-outline" style="width:100%; padding: 0.4rem; font-size: 0.78rem; border-color: var(--danger); color: var(--danger); background: rgba(239, 68, 68, 0.01);">
                    <i class="far fa-trash-can"></i> Delete Account
                </button>
            </div>
            
        </div>
    <?php endforeach; ?>
    <?php if (empty($accounts)): ?>
        <div class="card text-center" style="grid-column: 1 / -1; padding: 4rem 0;">
            <i class="fas fa-rotate" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h4 style="font-family: var(--font-heading);">No UPI IDs added</h4>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">Click the button in top right to start adding UPI Accounts.</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="upi-form-modal">
    <div class="modal-content">
        <div class="card-header" style="border-bottom: none; padding-bottom: 0; margin-bottom: 1.5rem;">
            <h3 class="card-title" id="upi-modal-title">Add UPI Rotator</h3>
            <button onclick="AdminApp.closeModal('upi-form-modal')" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-muted);"><i class="fas fa-times"></i></button>
        </div>
        
        <form onsubmit="AdminApp.submitUpiForm(event)">
            <input type="hidden" id="upi-form-op" name="op" value="add">
            <input type="hidden" id="upi-form-id" name="id" value="">

            <div class="form-group">
                <label for="upi-form-id-field">Recipient UPI ID (pa)</label>
                <input type="text" id="upi-form-id-field" name="upi_id" class="form-control" placeholder="e.g. nexentora@ybl, merchant@paytm" required>
            </div>

            <div class="form-group">
                <label for="upi-form-name">Payee Name (pn)</label>
                <input type="text" id="upi-form-name" name="payee_name" class="form-control" placeholder="e.g. Nexentora Technologies Pvt Ltd" required>
            </div>

            <div class="form-group" style="margin-bottom: 1.75rem;">
                <label for="upi-form-purpose">Rotation Pool / Scope</label>
                <select id="upi-form-purpose" name="purpose" class="form-control">
                    <option value="all">All Pools (Default)</option>
                    <option value="intern">Intern Fee Payments</option>
                    <option value="client">Client Invoices</option>
                </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-outline" onclick="AdminApp.closeModal('upi-form-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-upi-submit">Save Settings</button>
            </div>
        </form>
    </div>
</div>
