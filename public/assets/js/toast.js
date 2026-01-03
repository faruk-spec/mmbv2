/**
 * Animated Toast Notification System
 * Beautiful, modern toast notifications with auto-dismiss
 * 
 * Usage:
 *   showToast('Message here', 'success'); // Green success toast
 *   showToast('Error message', 'error');   // Red error toast
 *   showToast('Info message', 'info');     // Blue info toast
 *   showToast('Warning!', 'warning');      // Orange warning toast
 */

// Create toast container if it doesn't exist
function initToastContainer() {
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }
}

/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - Type of toast: 'success', 'error', 'info', 'warning'
 * @param {number} duration - Duration in milliseconds (default 5000)
 */
function showToast(message, type = 'info', duration = 5000) {
    initToastContainer();
    
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    toast.id = toastId;
    toast.className = 'toast toast-' + type;
    
    // Toast styles
    const colors = {
        success: { bg: '#00ff88', icon: '✓', iconBg: '#00dd70' },
        error: { bg: '#ff4646', icon: '×', iconBg: '#dd2828' },
        info: { bg: '#00d4ff', icon: 'ℹ', iconBg: '#00b8dd' },
        warning: { bg: '#ffb800', icon: '⚠', iconBg: '#dd9a00' }
    };
    
    const color = colors[type] || colors.info;
    
    toast.style.cssText = `
        background: var(--card-bg, #1a1a2e);
        border-left: 4px solid ${color.bg};
        border-radius: 8px;
        padding: 16px;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: flex-start;
        gap: 12px;
        pointer-events: auto;
        animation: slideInRight 0.3s ease-out;
        position: relative;
        overflow: hidden;
    `;
    
    // Icon
    const icon = document.createElement('div');
    icon.style.cssText = `
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: ${color.iconBg};
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
        flex-shrink: 0;
    `;
    icon.textContent = color.icon;
    
    // Message
    const messageDiv = document.createElement('div');
    messageDiv.style.cssText = `
        flex: 1;
        color: #ffffff;
        font-size: 14px;
        line-height: 1.5;
        padding-top: 4px;
        font-weight: 500;
    `;
    messageDiv.textContent = message;
    
    // Close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: var(--text-secondary, #999);
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s;
        flex-shrink: 0;
    `;
    closeBtn.onmouseover = () => {
        closeBtn.style.background = 'rgba(255, 255, 255, 0.1)';
        closeBtn.style.color = 'var(--text-primary, #fff)';
    };
    closeBtn.onmouseout = () => {
        closeBtn.style.background = 'none';
        closeBtn.style.color = 'var(--text-secondary, #999)';
    };
    closeBtn.onclick = () => removeToast(toastId);
    
    // Progress bar
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: ${color.bg};
        width: 100%;
        animation: progressBar ${duration}ms linear;
    `;
    
    toast.appendChild(icon);
    toast.appendChild(messageDiv);
    toast.appendChild(closeBtn);
    toast.appendChild(progressBar);
    container.appendChild(toast);
    
    // Auto remove after duration
    setTimeout(() => removeToast(toastId), duration);
}

/**
 * Remove a toast notification
 * @param {string} toastId - ID of the toast to remove
 */
function removeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    @keyframes progressBar {
        from {
            width: 100%;
        }
        to {
            width: 0%;
        }
    }
    
    @media (max-width: 480px) {
        #toast-container {
            left: 10px;
            right: 10px;
            top: 10px;
        }
        
        .toast {
            min-width: auto !important;
            max-width: none !important;
        }
    }
`;
document.head.appendChild(style);

// Auto-convert PHP flash messages to toasts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Look for flash message alerts and convert them
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const message = alert.textContent.trim();
        let type = 'info';
        
        if (alert.classList.contains('alert-success')) {
            type = 'success';
        } else if (alert.classList.contains('alert-error') || alert.classList.contains('alert-danger')) {
            type = 'error';
        } else if (alert.classList.contains('alert-warning')) {
            type = 'warning';
        }
        
        if (message) {
            // Hide the original alert
            alert.style.display = 'none';
            // Show as toast
            showToast(message, type);
        }
    });
});
