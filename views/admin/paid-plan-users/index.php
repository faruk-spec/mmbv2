<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<style>
.ppu-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:28px 32px;margin-bottom:24px;}
.ppu-label{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-secondary);margin-bottom:6px;}
.ppu-select{width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:.9rem;font-family:inherit;}
.ppu-select:focus{outline:none;border-color:var(--cyan);}
.ppu-plan-row{display:flex;align-items:flex-start;gap:12px;padding:14px 0;border-bottom:1px solid var(--border-color);}
.ppu-plan-row:last-child{border-bottom:none;}
.ppu-plan-cb{width:18px;height:18px;margin-top:2px;flex-shrink:0;accent-color:var(--cyan);}
.ppu-plan-info{flex:1;min-width:0;}
.ppu-plan-name{font-weight:700;font-size:.9rem;margin-bottom:2px;}
.ppu-plan-meta{font-size:.75rem;color:var(--text-secondary);}
.ppu-refund-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:.68rem;font-weight:700;background:rgba(245,158,11,.12);color:#f59e0b;border:1px solid rgba(245,158,11,.25);}
.ppu-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:8px;font-size:.85rem;font-weight:700;cursor:pointer;border:none;transition:all .15s;font-family:inherit;}
.ppu-btn-primary{background:var(--cyan);color:#06060a;}
.ppu-btn-primary:hover{opacity:.88;}
.ppu-btn-primary:disabled{opacity:.45;cursor:not-allowed;}
.ppu-empty{padding:32px;text-align:center;color:var(--text-secondary);font-size:.85rem;}
/* Refund popup */
.ppu-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:9999;align-items:center;justify-content:center;}
.ppu-overlay.open{display:flex;}
.ppu-popup{background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;padding:32px;max-width:460px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.5);}
.ppu-popup h3{font-size:1.05rem;font-weight:800;margin-bottom:14px;color:var(--text-primary);}
.ppu-popup p{font-size:.85rem;color:var(--text-secondary);line-height:1.6;}
.ppu-popup .ppu-pop-amount{font-size:1.4rem;font-weight:800;color:#f59e0b;margin:12px 0;}
.ppu-pop-actions{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;}
.ppu-btn-confirm{background:rgba(0,255,136,.12);border:1px solid rgba(0,255,136,.3);color:var(--green);padding:10px 20px;border-radius:8px;font-size:.83rem;font-weight:700;cursor:pointer;font-family:inherit;}
.ppu-btn-confirm:hover{background:rgba(0,255,136,.2);}
.ppu-btn-skip{background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.25);color:#94a3b8;padding:10px 20px;border-radius:8px;font-size:.83rem;font-weight:700;cursor:pointer;font-family:inherit;}
.ppu-btn-skip:hover{background:rgba(148,163,184,.16);}
</style>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div style="margin-bottom:24px;">
    <h1 style="font-size:1.3rem;font-weight:800;margin-bottom:4px;">Users with Paid Plans</h1>
    <p style="color:var(--text-secondary);font-size:.85rem;">Select a user to view their active paid subscriptions. Cancel selected plans and optionally issue refunds.</p>
</div>

<div class="ppu-card">
    <div class="ppu-label">Select User</div>
    <?php if (empty($paidUsers)): ?>
        <div class="ppu-empty"><i class="fas fa-users" style="font-size:2rem;opacity:.3;display:block;margin-bottom:10px;"></i>No users with active paid plans found.</div>
    <?php else: ?>
        <select id="ppuUserSelect" class="ppu-select">
            <option value="">— Choose a user —</option>
            <?php foreach ($paidUsers as $u): ?>
                <option value="<?= (int) $u['user_id'] ?>"
                        data-name="<?= View::e($u['user_name']) ?>"
                        data-email="<?= View::e($u['user_email']) ?>">
                    <?= View::e($u['user_name']) ?> (<?= View::e($u['user_email']) ?>)
                    — <?= View::e($u['plan_sources']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
</div>

<!-- Plans panel — populated via AJAX -->
<div id="ppuPlansPanel" style="display:none;">
    <form id="ppuCancelForm" method="post" action="/admin/paid-plan-users/cancel">
        <?= Security::csrfField() ?>
        <input type="hidden" name="user_id" id="ppuUserId">

        <div class="ppu-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
                <div>
                    <div style="font-weight:800;font-size:1rem;" id="ppuSelectedName"></div>
                    <div style="font-size:.78rem;color:var(--text-secondary);" id="ppuSelectedEmail"></div>
                </div>
                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.84rem;">
                        <input type="checkbox" name="notify_user" value="1" id="ppuNotify" style="accent-color:var(--cyan);width:15px;height:15px;">
                        Notify user via email
                    </label>
                </div>
            </div>

            <div class="ppu-label" style="margin-bottom:12px;">Active Paid Subscriptions</div>
            <div id="ppuPlansList">
                <div class="ppu-empty"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;font-size:1.2rem;"></i>Loading…</div>
            </div>

            <!-- hidden inputs for selected payment IDs (populated by JS before submit) -->
            <div id="ppuHiddenIds"></div>

            <!-- hidden field for issue_refund decision (set by popup) -->
            <input type="hidden" name="issue_refund" id="ppuIssueRefund" value="0">

            <div style="margin-top:24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="button" id="ppuSubmitBtn" class="ppu-btn ppu-btn-primary" disabled>
                    <i class="fas fa-ban"></i> Cancel Selected Plans
                </button>
                <span style="font-size:.78rem;color:var(--text-secondary);">User will be assigned the free plan automatically.</span>
            </div>
        </div>
    </form>
</div>

<!-- Refund confirmation popup -->
<div class="ppu-overlay" id="ppuRefundOverlay" role="dialog" aria-modal="true">
    <div class="ppu-popup">
        <h3><i class="fas fa-undo" style="color:#f59e0b;margin-right:8px;"></i>Refund Eligible</h3>
        <p>One or more selected plans qualify for a refund within their policy window.</p>
        <div class="ppu-pop-amount" id="ppuRefundTotal"></div>
        <p>Would you like to automatically queue these refunds for review? They will appear on the <a href="/admin/refunds" style="color:var(--cyan);">Refunds page</a> awaiting your approval.</p>
        <div class="ppu-pop-actions">
            <button type="button" class="ppu-btn-confirm" id="ppuConfirmWithRefund">
                <i class="fas fa-check"></i> Yes — queue refunds &amp; cancel
            </button>
            <button type="button" class="ppu-btn-skip" id="ppuConfirmNoRefund">
                Cancel without refund
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const userSelect   = document.getElementById('ppuUserSelect');
    const plansPanel   = document.getElementById('ppuPlansPanel');
    const plansList    = document.getElementById('ppuPlansList');
    const userIdInput  = document.getElementById('ppuUserId');
    const selectedName = document.getElementById('ppuSelectedName');
    const selectedEmail= document.getElementById('ppuSelectedEmail');
    const submitBtn    = document.getElementById('ppuSubmitBtn');
    const hiddenIds    = document.getElementById('ppuHiddenIds');
    const issueRefund  = document.getElementById('ppuIssueRefund');
    const overlay      = document.getElementById('ppuRefundOverlay');
    const refundTotal  = document.getElementById('ppuRefundTotal');
    const form         = document.getElementById('ppuCancelForm');

    let currentPayments = [];

    if (!userSelect) return;

    userSelect.addEventListener('change', function () {
        const userId = this.value;
        const opt    = this.options[this.selectedIndex];
        if (!userId) {
            plansPanel.style.display = 'none';
            return;
        }

        userIdInput.value  = userId;
        selectedName.textContent  = opt.dataset.name || '';
        selectedEmail.textContent = opt.dataset.email || '';
        plansPanel.style.display  = '';
        submitBtn.disabled        = true;
        plansList.innerHTML       = '<div class="ppu-empty"><i class="fas fa-spinner fa-spin" style="display:block;margin-bottom:8px;font-size:1.2rem;"></i>Loading…</div>';

        fetch('/admin/paid-plan-users/user-plans?user_id=' + encodeURIComponent(userId))
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    plansList.innerHTML = '<div class="ppu-empty">Could not load plans.</div>';
                    return;
                }
                currentPayments = data.payments || [];
                renderPlans(currentPayments);
            })
            .catch(() => {
                plansList.innerHTML = '<div class="ppu-empty">Network error. Please try again.</div>';
            });
    });

    function renderPlans(payments) {
        if (!payments.length) {
            plansList.innerHTML = '<div class="ppu-empty">No active paid subscriptions found for this user.</div>';
            return;
        }

        let html = '';
        payments.forEach(p => {
            const refund = p.refund_eligible;
            const refundBadge = refund
                ? `<span class="ppu-refund-badge"><i class="fas fa-undo"></i> Refund eligible — ${p.refund_days_left}d left (${formatCurrency(p.refund_amount, p.currency)})</span>`
                : '';
            const billingCycle = (p.billing_cycle || 'one-time').replace('_', ' ');
            html += `
            <div class="ppu-plan-row">
                <input type="checkbox" class="ppu-plan-cb ppuPlanCb" value="${escHtml(p.id)}" data-refund="${refund ? '1' : '0'}" data-amount="${escHtml(p.refund_amount)}" data-currency="${escHtml(p.currency || 'USD')}">
                <div class="ppu-plan-info">
                    <div class="ppu-plan-name">${escHtml(p.plan_name || 'Subscription #' + p.id)}</div>
                    <div class="ppu-plan-meta">
                        ${escHtml(p.app_label)} &middot; ${escHtml(billingCycle)} &middot;
                        ${formatCurrency(p.amount, p.currency)}
                        &middot; paid ${formatDate(p.paid_at || p.created_at)}
                    </div>
                    ${refundBadge ? '<div style="margin-top:5px;">' + refundBadge + '</div>' : ''}
                </div>
            </div>`;
        });
        plansList.innerHTML = html;

        plansList.querySelectorAll('.ppuPlanCb').forEach(cb => cb.addEventListener('change', updateSubmitState));
        updateSubmitState();
    }

    function updateSubmitState() {
        const checked = plansList.querySelectorAll('.ppuPlanCb:checked');
        submitBtn.disabled = checked.length === 0;
    }

    submitBtn.addEventListener('click', function () {
        const checked = Array.from(plansList.querySelectorAll('.ppuPlanCb:checked'));
        if (!checked.length) return;

        // Check total refund eligibility
        let totalRefund = 0;
        let refundCurrency = 'USD';
        checked.forEach(cb => {
            if (cb.dataset.refund === '1') {
                totalRefund += parseFloat(cb.dataset.amount) || 0;
                refundCurrency = cb.dataset.currency || refundCurrency;
            }
        });

        if (totalRefund > 0) {
            refundTotal.textContent = formatCurrency(totalRefund, refundCurrency) + ' total refund';
            overlay.classList.add('open');
        } else {
            // No refund eligible — submit directly without refund
            issueRefund.value = '0';
            submitWithIds(checked);
        }
    });

    document.getElementById('ppuConfirmWithRefund').addEventListener('click', function () {
        overlay.classList.remove('open');
        const checked = Array.from(plansList.querySelectorAll('.ppuPlanCb:checked'));
        issueRefund.value = '1';
        submitWithIds(checked);
    });

    document.getElementById('ppuConfirmNoRefund').addEventListener('click', function () {
        overlay.classList.remove('open');
        const checked = Array.from(plansList.querySelectorAll('.ppuPlanCb:checked'));
        issueRefund.value = '0';
        submitWithIds(checked);
    });

    // Close popup on backdrop click
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.classList.remove('open');
    });

    function submitWithIds(checkboxes) {
        hiddenIds.innerHTML = '';
        checkboxes.forEach(cb => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'payment_ids[]';
            inp.value = cb.value;
            hiddenIds.appendChild(inp);
        });
        form.submit();
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }

    function formatCurrency(amount, currency) {
        const num = parseFloat(amount) || 0;
        try {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: currency || 'USD', maximumFractionDigits: 2 }).format(num);
        } catch(e) { console.warn('Currency formatting error:', e); return currency + ' ' + num.toFixed(2); }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
})();
</script>
<?php View::endSection(); ?>
