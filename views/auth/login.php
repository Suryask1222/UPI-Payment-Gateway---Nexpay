<div class="payment-card" style="max-width: 400px; padding: 2.5rem; text-align: left;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <div class="brand" style="justify-content: center; font-size: 1.5rem; color: var(--text-main);">
            <i class="fas fa-wallet" style="color: #4f46e5;"></i>
            <span>Nex Pay</span>
        </div>
        <h2 style="font-family: var(--font-heading); font-weight: 700; margin-top: 0.5rem; font-size: 1.25rem;">Admin Access</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Login to manage payments and UPI configurations.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div style="background-color: var(--danger-light); color: var(--danger); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 600; border: 1px solid rgba(239, 68, 68, 0.15); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-circle-exclamation"></i>
            <span><?= h($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/admin_login.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter administrator username" required autofocus>
        </div>

        <div class="form-group" style="margin-bottom: 1.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label for="password" style="margin-bottom: 0;">Password</label>
            </div>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; height: 46px; border-radius: var(--radius-sm);">
            <span>Login to Control Center</span>
            <i class="fas fa-chevron-right" style="font-size: 0.8rem;"></i>
        </button>
    </form>
</div>
