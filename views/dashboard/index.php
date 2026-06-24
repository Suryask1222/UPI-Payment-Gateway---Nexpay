<?php
/**
 * Nex Pay - Redesigned Dashboard Overview
 */
?>

<!-- 1. Stats Overview Widgets Grid -->
<div class="widgets-grid" style="margin-bottom: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
    <div class="widget" style="padding: 1.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); position: relative; overflow: hidden; transition: var(--transition);">
        <div style="position: absolute; right: 1.5rem; top: 1.5rem; font-size: 1.5rem; color: var(--success); opacity: 0.15;">
            <i class="fas fa-coins"></i>
        </div>
        <div class="widget-label" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Total Revenue</div>
        <div class="widget-value" style="color: var(--success); font-family: var(--font-heading); font-weight: 800; font-size: 1.85rem; margin-top: 4px;"><?= formatCurrency($stats['total_revenue']) ?></div>
        <div class="widget-meta" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">Lifetime settled volume</div>
    </div>
    
    <div class="widget widget-info" style="padding: 1.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); position: relative; overflow: hidden; transition: var(--transition);">
        <div style="position: absolute; right: 1.5rem; top: 1.5rem; font-size: 1.5rem; color: var(--info); opacity: 0.15;">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="widget-label" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Today's Revenue</div>
        <div class="widget-value" style="color: var(--info); font-family: var(--font-heading); font-weight: 800; font-size: 1.85rem; margin-top: 4px;"><?= formatCurrency($stats['today_revenue']) ?></div>
        <div class="widget-meta" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">Settled since midnight</div>
    </div>
    
    <div class="widget widget-warning" style="padding: 1.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); position: relative; overflow: hidden; transition: var(--transition);">
        <div style="position: absolute; right: 1.5rem; top: 1.5rem; font-size: 1.5rem; color: var(--warning); opacity: 0.15;">
            <i class="fas fa-clock-rotate-left"></i>
        </div>
        <div class="widget-label" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Pending Verifications</div>
        <div class="widget-value" style="color: var(--warning); font-family: var(--font-heading); font-weight: 800; font-size: 1.85rem; margin-top: 4px;"><?= (int)$stats['pending_count'] ?></div>
        <div class="widget-meta" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">Transactions awaiting action</div>
    </div>
    
    <div class="widget widget-success" style="padding: 1.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); position: relative; overflow: hidden; transition: var(--transition);">
        <div style="position: absolute; right: 1.5rem; top: 1.5rem; font-size: 1.5rem; color: var(--primary); opacity: 0.15;">
            <i class="fas fa-percent"></i>
        </div>
        <div class="widget-label" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Success Rate</div>
        <div class="widget-value" style="color: var(--text-main); font-family: var(--font-heading); font-weight: 800; font-size: 1.85rem; margin-top: 4px;"><?= (float)$stats['success_rate'] ?>%</div>
        <div class="widget-meta" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;">Settlement verification ratio</div>
    </div>
</div>

<!-- 2. Revenue Analytics Chart Card -->
<div class="card shadow-glow-indigo" style="padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
        <div>
            <h3 class="card-title" style="font-family: var(--font-heading); font-weight: 700; font-size: 1.15rem; color: var(--text-main);"><i class="fas fa-chart-area" style="color: var(--primary); margin-right: 8px;"></i>Settlement Analytics</h3>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Daily revenue settlement curve for the past 7 days</p>
        </div>
        <div style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">
            Peak Daily Revenue: <span style="color: var(--success); font-weight: 700;"><?= formatCurrency($chart['max']) ?></span>
        </div>
    </div>

    <!-- Chart Wrapper (Fixes stretching issues) -->
    <div style="position: relative; width: 100%; height: 240px; overflow: hidden; padding: 10px 0;">
        <svg class="svg-chart" viewBox="0 0 600 220" style="height: 100%; width: 100%; overflow: visible;">
            <defs>
                <linearGradient id="chart-gradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.3"/>
                    <stop offset="100%" stop-color="var(--primary)" stop-opacity="0.0"/>
                </linearGradient>
                <filter id="neon-glow" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="3.5" result="blur" />
                    <feComposite in="SourceGraphic" in2="blur" operator="over" />
                </filter>
            </defs>

            <!-- Horizontal Grid lines -->
            <line x1="40" y1="30" x2="560" y2="30" class="chart-grid-line" style="stroke: var(--border-color); stroke-opacity: 0.4; stroke-dasharray: 4 4;" />
            <line x1="40" y1="90" x2="560" y2="90" class="chart-grid-line" style="stroke: var(--border-color); stroke-opacity: 0.4; stroke-dasharray: 4 4;" />
            <line x1="40" y1="150" x2="560" y2="150" class="chart-grid-line" style="stroke: var(--border-color); stroke-opacity: 0.4; stroke-dasharray: 4 4;" />
            <line x1="40" y1="190" x2="560" y2="190" style="stroke: var(--border-color); stroke-width: 1.5; stroke-opacity: 0.8;" />

            <?php if (!empty($chart['polyline'])): ?>
                <!-- Filled Area -->
                <polygon points="<?= h($chart['area']) ?>" class="chart-area" style="fill: url(#chart-gradient);" />

                <!-- Trend Polyline Curve -->
                <polyline points="<?= h($chart['polyline']) ?>" class="chart-line" style="filter: url(#neon-glow); stroke: var(--primary); stroke-width: 3.5; fill: none; stroke-linecap: round; stroke-linejoin: round;" />

                <!-- Circle node plots and texts -->
                <?php foreach ($chart['points'] as $pt): ?>
                    <!-- Outer pulsating glow ring -->
                    <circle cx="<?= (float)$pt['x'] ?>" cy="<?= (float)$pt['y'] ?>" r="8" style="fill: var(--primary); fill-opacity: 0.15;" />
                    <!-- Inner clean node marker -->
                    <circle cx="<?= (float)$pt['x'] ?>" cy="<?= (float)$pt['y'] ?>" r="4.5" class="chart-point" style="fill: var(--bg-surface); stroke: var(--primary); stroke-width: 2.5; cursor: pointer; transition: var(--transition);" />
                    
                    <!-- Node Value tag -->
                    <text x="<?= (float)$pt['x'] ?>" y="<?= (float)$pt['y'] - 14 ?>" font-size="10" font-weight="700" font-family="var(--font-heading)" fill="var(--text-main)" text-anchor="middle">
                        <?= $pt['total'] > 0 ? '₹' . number_format($pt['total'], 0) : '' ?>
                    </text>
                    
                    <!-- Bottom Date tag -->
                    <text x="<?= (float)$pt['x'] ?>" y="210" font-size="10.5" font-weight="600" font-family="var(--font-sans)" fill="var(--text-muted)" text-anchor="middle">
                        <?= h($pt['day']) ?>
                    </text>
                <?php endforeach; ?>
            <?php else: ?>
                <text x="300" y="110" font-size="14" fill="var(--text-muted)" text-anchor="middle" font-family="var(--font-sans)">No chart trend data available.</text>
            <?php endif; ?>
        </svg>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start; margin-top: 1.5rem;">
    <!-- 3. Activity Timeline -->
    <div class="card" style="margin-bottom: 0; padding: 1.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
            <h3 class="card-title" style="font-family: var(--font-heading); font-weight: 700; font-size: 1.15rem; color: var(--text-main);"><i class="fas fa-list-timeline" style="color: var(--primary); margin-right: 8px;"></i>Activity Timeline</h3>
            <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Real-time desk logs</span>
        </div>
        
        <div style="max-height: 400px; overflow-y: auto; padding-left: 15px; padding-right: 10px; padding-top: 5px; margin-top: 5px;">
            <div class="timeline">
                <?php foreach ($activities as $act): ?>
                    <?php 
                        $typeClass = '';
                        if (strpos($act['action_type'], 'approve') !== false) $typeClass = 'approved';
                        elseif (strpos($act['action_type'], 'reject') !== false) $typeClass = 'rejected';
                        elseif (strpos($act['action_type'], 'hold') !== false) $typeClass = 'hold';
                        elseif ($act['action_type'] === 'login') $typeClass = 'login';
                    ?>
                    <div class="timeline-item <?= $typeClass ?>">
                        <div class="timeline-time" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 2px;"><?= date('d M H:i', strtotime($act['created_at'])) ?></div>
                        <div class="timeline-title" style="font-weight: 600; font-size: 0.9rem; color: var(--text-main); display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <?= h(ucwords(str_replace('_', ' ', $act['action_type']))) ?>
                            <?php if (!empty($act['order_no'])): ?>
                                <span style="font-family: monospace; font-size: 0.75rem; background: var(--bg-base); border: 1px solid var(--border-color); padding: 2px 6px; border-radius: 4px; color: var(--text-main);"><?= h($act['order_no']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="timeline-desc" style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;"><?= h($act['details']) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($activities)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 2rem 0;">No logs registered yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 4. Verification Stats card -->
    <div class="card" style="margin-bottom: 0; padding: 1.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface);">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
            <h3 class="card-title" style="font-family: var(--font-heading); font-weight: 700; font-size: 1.15rem; color: var(--text-main);"><i class="fas fa-sliders" style="color: var(--primary); margin-right: 8px;"></i>Verification Stats</h3>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">
                    <span style="color: var(--text-muted);">Approved Settlements</span>
                    <span style="color: var(--success); font-weight: 700;"><?= (int)$stats['approved_count'] ?></span>
                </div>
                <div style="height: 6px; background-color: var(--border-color); border-radius: 3px; overflow: hidden;">
                    <?php 
                        $totalTx = $stats['approved_count'] + $stats['rejected_count'];
                        $approvedPercentage = $totalTx > 0 ? ($stats['approved_count'] / $totalTx) * 100 : 0;
                    ?>
                    <div style="height: 100%; width: <?= $approvedPercentage ?>%; background-color: var(--success); border-radius: 3px;"></div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">
                    <span style="color: var(--text-muted);">Rejected / Failed</span>
                    <span style="color: var(--danger); font-weight: 700;"><?= (int)$stats['rejected_count'] ?></span>
                </div>
                <div style="height: 6px; background-color: var(--border-color); border-radius: 3px; overflow: hidden;">
                    <?php 
                        $rejectedPercentage = $totalTx > 0 ? ($stats['rejected_count'] / $totalTx) * 100 : 0;
                    ?>
                    <div style="height: 100%; width: <?= $rejectedPercentage ?>%; background-color: var(--danger); border-radius: 3px;"></div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                    <span style="color: var(--text-muted); font-weight: 500;">Active Rotator VPAs</span>
                    <span style="font-weight: 700; color: var(--text-main);"><?= (int)$stats['active_upi_count'] ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                    <span style="color: var(--text-muted); font-weight: 500;">Queue Verification</span>
                    <span style="font-weight: 700; color: var(--warning);"><?= (int)$stats['pending_count'] ?> orders</span>
                </div>
            </div>
        </div>
    </div>
</div>
