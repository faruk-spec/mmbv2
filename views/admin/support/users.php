<?php
/**
 * Admin Support Users & Agent Management
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<div style="padding:28px;">
    <?php if (!empty($_SESSION['flash_success'])): ?>
    <div style="background:rgba(0,255,136,.1);border:1px solid rgba(0,255,136,.25);color:#00ff88;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.88rem;">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <?php unset($_SESSION['flash_success']); ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
    <div style="background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.25);color:#ff6b6b;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.88rem;">
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <?php unset($_SESSION['flash_error']); ?>
    </div>
    <?php endif; ?>

    <!-- ── AGENT MANAGEMENT ─────────────────────────────────────────────── -->
    <div style="margin-bottom:36px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <div>
                <h1 style="font-size:1.4rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
                    <i class="fas fa-user-shield" style="color:#ff9f43;margin-right:10px;"></i>Support Agents
                </h1>
                <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.83rem;">Agents can view and reply to live chats and support tickets.</p>
            </div>
            <!-- Add Agent trigger -->
            <button onclick="document.getElementById('addAgentModal').style.display='flex'"
                style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;background:linear-gradient(135deg,#ff9f43,#ff2ec4);border:none;border-radius:8px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;">
                <i class="fas fa-plus"></i> Add Agent
            </button>
        </div>

        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
            <?php if (empty($agents)): ?>
            <div style="padding:48px;text-align:center;color:var(--text-secondary,#8892a6);">
                <i class="fas fa-user-shield" style="font-size:2rem;opacity:.3;display:block;margin-bottom:12px;"></i>
                No support agents yet. Add one above.
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));">
                            <?php foreach (['Agent','Email','Role','Notes','Assigned By','Since','Actions'] as $h): ?>
                            <th style="padding:11px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.73rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $h ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents as $agent): ?>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;"
                            onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                            <td style="padding:12px 14px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#ff9f43,#ff2ec4);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:white;flex-shrink:0;">
                                        <?= strtoupper(substr($agent['name'], 0, 1)) ?>
                                    </div>
                                    <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;"><?= htmlspecialchars($agent['name']) ?></div>
                                </div>
                            </td>
                            <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.83rem;"><?= htmlspecialchars($agent['email']) ?></td>
                            <td style="padding:12px 14px;">
                                <span style="display:inline-block;padding:2px 9px;border-radius:20px;font-size:.73rem;font-weight:600;background:rgba(0,240,255,.1);color:#00f0ff;">
                                    <?= htmlspecialchars($agent['role']) ?>
                                </span>
                            </td>
                            <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.8rem;max-width:180px;">
                                <?= htmlspecialchars($agent['notes'] ?: '—') ?>
                            </td>
                            <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.8rem;">
                                <?= htmlspecialchars($agent['assigned_by_name'] ?? '—') ?>
                            </td>
                            <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.8rem;">
                                <?= date('M j, Y', strtotime($agent['created_at'])) ?>
                            </td>
                            <td style="padding:12px 14px;">
                                <form method="POST" action="/admin/support/agents/<?= (int)$agent['user_id'] ?>/remove"
                                      onsubmit="return confirm('Remove this agent?')">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                    <button type="submit" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.2);color:#ff6b6b;border-radius:6px;font-size:.78rem;font-weight:500;cursor:pointer;">
                                        <i class="fas fa-user-minus"></i> Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── SUPPORT USERS ────────────────────────────────────────────────── -->
    <div>
        <div style="margin-bottom:16px;">
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
                <i class="fas fa-users" style="color:#00f0ff;margin-right:8px;"></i>Support Users
            </h2>
            <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.83rem;">Users who have submitted tickets or initiated live chats.</p>
        </div>

        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
            <?php if (empty($users)): ?>
            <div style="padding:60px;text-align:center;color:var(--text-secondary,#8892a6);">
                <i class="fas fa-users" style="font-size:2rem;opacity:.3;display:block;margin-bottom:12px;"></i>
                No support users yet.
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));">
                            <?php foreach (['User','Email','Tickets','Chats','Last Activity','Actions'] as $h): ?>
                            <th style="padding:12px 14px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.73rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $h ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;"
                            onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                            <td style="padding:12px 14px;">
                                <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;"><?= htmlspecialchars($user['name']) ?></div>
                            </td>
                            <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.83rem;"><?= htmlspecialchars($user['email']) ?></td>
                            <td style="padding:12px 14px;text-align:center;">
                                <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:600;background:rgba(0,240,255,.1);color:#00f0ff;">
                                    <?= (int)$user['ticket_count'] ?>
                                </span>
                            </td>
                            <td style="padding:12px 14px;text-align:center;">
                                <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:600;background:rgba(255,46,196,.1);color:#ff2ec4;">
                                    <?= (int)$user['chat_count'] ?>
                                </span>
                            </td>
                            <td style="padding:12px 14px;color:var(--text-secondary,#8892a6);font-size:.8rem;">
                                <?php
                                $la = $user['last_activity'];
                                echo $la && $la !== '1970-01-01' ? date('M j, Y', strtotime($la)) : '—';
                                ?>
                            </td>
                            <td style="padding:12px 14px;">
                                <a href="/admin/support/tickets?user_id=<?= (int)$user['id'] ?>"
                                   style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                                    <i class="fas fa-ticket"></i> Tickets
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Agent Modal -->
<div id="addAgentModal" style="display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);"
     onclick="if(event.target===this)this.style.display='none'">
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:16px;padding:28px;width:100%;max-width:460px;margin:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="margin:0;font-size:1.05rem;font-weight:700;color:var(--text-primary,#e8eefc);">
                <i class="fas fa-user-shield" style="color:#ff9f43;margin-right:8px;"></i>Add Support Agent
            </h3>
            <button onclick="document.getElementById('addAgentModal').style.display='none'"
                style="background:none;border:none;color:var(--text-secondary,#8892a6);cursor:pointer;font-size:18px;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="/admin/support/agents/add">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.82rem;font-weight:600;color:var(--text-secondary,#8892a6);margin-bottom:6px;">Select User *</label>
                <select name="user_id" required
                    style="width:100%;padding:10px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.88rem;">
                    <option value="">— choose a user —</option>
                    <?php foreach ($allUsers as $u):
                        if ($u['is_agent']) continue; // skip already agents
                    ?>
                    <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:.82rem;font-weight:600;color:var(--text-secondary,#8892a6);margin-bottom:6px;">Notes (optional)</label>
                <textarea name="notes" rows="2" placeholder="e.g. Handles billing queries, available Mon-Fri"
                    style="width:100%;padding:10px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.88rem;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('addAgentModal').style.display='none'"
                    style="padding:9px 20px;background:var(--bg-secondary,#0c0c12);border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;color:var(--text-secondary,#8892a6);font-size:.88rem;cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="padding:9px 20px;background:linear-gradient(135deg,#ff9f43,#ff2ec4);border:none;border-radius:8px;color:white;font-weight:600;font-size:.88rem;cursor:pointer;">
                    <i class="fas fa-user-plus" style="margin-right:5px;"></i>Add Agent
                </button>
            </div>
        </form>
    </div>
</div>

<?php View::endSection(); ?>
