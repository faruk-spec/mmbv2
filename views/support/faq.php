<?php
/**
 * Support FAQ Page
 */
use Core\View;
View::extend('main');
?>

<?php View::section('styles'); ?>
<style>.dashboard-main-content { padding: 0 !important; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <div style="flex:1;padding:24px 28px;min-width:0;">
            <div style="margin-bottom:22px;">
                <h1 style="font-size:1.4rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;display:flex;align-items:center;gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00f0ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Frequently Asked Questions
                </h1>
                <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.85rem;">Find answers to the most common questions.</p>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php
                $faqs = [
                    ['q'=>'How do I create a support ticket?','a'=>'Go to <a href="/support/create" style="color:#00f0ff;">Create Ticket</a> in the sidebar, fill in the subject and description, choose a priority, and click Submit.'],
                    ['q'=>'How long does it take to get a response?','a'=>'Our team typically responds within 24 hours on business days. Urgent tickets are prioritized and handled faster.'],
                    ['q'=>'Can I reply to a ticket after submitting?','a'=>'Yes. Open the ticket from <a href="/support" style="color:#00f0ff;">My Tickets</a> and use the reply form at the bottom of the conversation thread.'],
                    ['q'=>'What does "Waiting on Customer" status mean?','a'=>'It means our agent has responded and is waiting for more information from you. Please check the ticket and reply.'],
                    ['q'=>'Can I use live chat?','a'=>'Yes! Click the headset button in the bottom-right corner of any page to start a live chat session with our support team.'],
                    ['q'=>'How do I escalate an urgent issue?','a'=>'Create a new ticket with <strong style="color:#ff6b6b;">Urgent</strong> priority, or use Live Chat for immediate attention.'],
                    ['q'=>'Can I attach files to my ticket?','a'=>'You can paste file contents or links in the description. File attachment support is coming soon.'],
                    ['q'=>'How do I reopen a resolved ticket?','a'=>'Resolved tickets can still receive replies. Just open the ticket and submit a new reply to reopen communication.'],
                ];
                foreach ($faqs as $i => $faq):
                ?>
                <div class="faq-item" style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.07));border-radius:10px;overflow:hidden;">
                    <button onclick="toggleFaq(<?= $i ?>)" style="width:100%;padding:14px 18px;background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:12px;text-align:left;">
                        <span style="font-weight:600;font-size:.9rem;color:var(--text-primary,#e8eefc);"><?= htmlspecialchars($faq['q']) ?></span>
                        <svg id="faq-icon-<?= $i ?>" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary,#8892a6)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;transition:transform .2s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="faq-body-<?= $i ?>" style="display:none;padding:0 18px 14px;border-top:1px solid var(--border-color,rgba(255,255,255,.05));">
                        <p style="margin:12px 0 0;color:var(--text-secondary,#8892a6);font-size:.88rem;line-height:1.7;"><?= $faq['a'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:24px;background:linear-gradient(135deg,rgba(0,240,255,.06),rgba(255,46,196,.04));border:1px solid rgba(0,240,255,.15);border-radius:12px;padding:20px;text-align:center;">
                <div style="font-weight:600;color:var(--text-primary,#e8eefc);margin-bottom:8px;">Still have questions?</div>
                <p style="color:var(--text-secondary,#8892a6);font-size:.87rem;margin:0 0 14px;">Our support team is happy to help.</p>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                    <a href="/support/create" style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:7px;color:white;font-weight:600;text-decoration:none;font-size:.875rem;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Open a Ticket
                    </a>
                    <a href="/support/live" style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:rgba(0,240,255,.1);border:1px solid rgba(0,240,255,.2);border-radius:7px;color:#00f0ff;font-weight:600;text-decoration:none;font-size:.875rem;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                        Live Chat
                    </a>
                </div>
            </div>
        </div><!-- /main content -->
</div><!-- /support flex wrapper -->

<script>
function toggleFaq(i) {
    var body = document.getElementById('faq-body-' + i);
    var icon = document.getElementById('faq-icon-' + i);
    var isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : 'block';
    icon.style.transform = isOpen ? '' : 'rotate(180deg)';
}
</script>

<?php View::endSection(); ?>
