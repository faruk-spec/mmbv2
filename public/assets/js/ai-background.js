/**
 * AI-Style Animated Background System
 * Subtle, performance-friendly background animations
 */

class AIBackground {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.particles = [];
        this.animationId = null;
        this.isEnabled = true;
        this.preferReducedMotion = false;
        
        // Performance settings
        this.maxParticles = 50;
        this.fps = 30;
        this.fpsInterval = 1000 / this.fps;
        this.lastFrameTime = Date.now();
        
        // Check user preferences
        this.checkPreferences();
        
        // Initialize if enabled
        if (this.isEnabled && !this.preferReducedMotion) {
            this.init();
        }
    }
    
    checkPreferences() {
        // Check for reduced motion preference
        if (window.matchMedia) {
            const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
            this.preferReducedMotion = motionQuery.matches;
            
            // Listen for changes
            motionQuery.addEventListener('change', (e) => {
                this.preferReducedMotion = e.matches;
                if (this.preferReducedMotion) {
                    this.stop();
                } else if (this.isEnabled) {
                    this.start();
                }
            });
        }
        
        // Check localStorage for user preference
        const savedPref = localStorage.getItem('ai-background-enabled');
        if (savedPref !== null) {
            this.isEnabled = savedPref === 'true';
        }
    }
    
    init() {
        // Create canvas
        this.canvas = document.createElement('canvas');
        this.canvas.id = 'ai-background-canvas';
        this.canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            opacity: 0.6;
        `;
        
        document.body.appendChild(this.canvas);
        this.ctx = this.canvas.getContext('2d', { alpha: true });
        
        // Set canvas size
        this.resize();
        
        // Create particles
        this.createParticles();
        
        // Start animation
        this.start();
        
        // Handle resize
        window.addEventListener('resize', () => this.resize());
        
        // Add toggle control
        this.addToggleControl();
    }
    
    resize() {
        if (!this.canvas) return;
        
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }
    
    createParticles() {
        // Calculate particle density: approximately 1 particle per 30,000 pixels of screen area
        // This ensures optimal performance while maintaining visual appeal across different screen sizes
        const PIXELS_PER_PARTICLE = 30000;
        const particleCount = Math.min(
            this.maxParticles,
            Math.floor((window.innerWidth * window.innerHeight) / PIXELS_PER_PARTICLE)
        );
        
        for (let i = 0; i < particleCount; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                vx: (Math.random() - 0.5) * 0.3,
                vy: (Math.random() - 0.5) * 0.3,
                size: Math.random() * 2 + 1,
                opacity: Math.random() * 0.5 + 0.1,
                color: this.getRandomColor()
            });
        }
    }
    
    getRandomColor() {
        const colors = [
            'rgba(0, 217, 255, ',      // cyan
            'rgba(0, 102, 255, ',       // blue
            'rgba(255, 46, 196, ',      // magenta
            'rgba(153, 69, 255, '       // purple
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    animate() {
        if (!this.isEnabled || this.preferReducedMotion) return;
        
        this.animationId = requestAnimationFrame(() => this.animate());
        
        // Throttle to target FPS
        const now = Date.now();
        const elapsed = now - this.lastFrameTime;
        
        if (elapsed < this.fpsInterval) return;
        
        this.lastFrameTime = now - (elapsed % this.fpsInterval);
        
        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Draw gradient overlay
        this.drawGradient();
        
        // Update and draw particles
        this.particles.forEach(particle => {
            // Update position
            particle.x += particle.vx;
            particle.y += particle.vy;
            
            // Wrap around edges
            if (particle.x < 0) particle.x = this.canvas.width;
            if (particle.x > this.canvas.width) particle.x = 0;
            if (particle.y < 0) particle.y = this.canvas.height;
            if (particle.y > this.canvas.height) particle.y = 0;
            
            // Draw particle with glow
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            
            // Create radial gradient for glow effect
            const gradient = this.ctx.createRadialGradient(
                particle.x, particle.y, 0,
                particle.x, particle.y, particle.size * 4
            );
            gradient.addColorStop(0, particle.color + particle.opacity + ')');
            gradient.addColorStop(1, particle.color + '0)');
            
            this.ctx.fillStyle = gradient;
            this.ctx.fill();
        });
    }
    
    drawGradient() {
        // Subtle moving gradient
        const time = Date.now() * 0.0001;
        
        const gradient = this.ctx.createLinearGradient(
            0, 0,
            this.canvas.width, this.canvas.height
        );
        
        const theme = document.documentElement.getAttribute('data-theme') || 'dark';
        const isDark = theme === 'dark';
        
        if (isDark) {
            gradient.addColorStop(0, `rgba(0, 217, 255, ${0.02 + Math.sin(time) * 0.01})`);
            gradient.addColorStop(0.5, 'rgba(0, 0, 0, 0)');
            gradient.addColorStop(1, `rgba(255, 46, 196, ${0.02 + Math.cos(time) * 0.01})`);
        } else {
            gradient.addColorStop(0, `rgba(0, 217, 255, ${0.01 + Math.sin(time) * 0.005})`);
            gradient.addColorStop(0.5, 'rgba(255, 255, 255, 0)');
            gradient.addColorStop(1, `rgba(0, 102, 255, ${0.01 + Math.cos(time) * 0.005})`);
        }
        
        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    }
    
    start() {
        if (!this.animationId && !this.preferReducedMotion) {
            this.animate();
        }
    }
    
    stop() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }
        if (this.canvas && this.ctx) {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        }
    }
    
    toggle() {
        this.isEnabled = !this.isEnabled;
        localStorage.setItem('ai-background-enabled', this.isEnabled);
        
        if (this.isEnabled && !this.preferReducedMotion) {
            if (!this.canvas) {
                this.init();
            } else {
                this.start();
            }
        } else {
            this.stop();
        }
        
        // Update toggle button
        this.updateToggleButton();
    }
    
    addToggleControl() {
        // Create toggle button
        const toggle = document.createElement('button');
        toggle.id = 'ai-background-toggle';
        toggle.className = 'ai-bg-toggle';
        toggle.setAttribute('aria-label', 'Toggle animated background');
        toggle.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
            </svg>
            <span>${this.isEnabled ? 'Animations On' : 'Animations Off'}</span>
        `;
        
        toggle.addEventListener('click', () => this.toggle());
        
        // Add styles for toggle button
        const style = document.createElement('style');
        style.textContent = `
            .ai-bg-toggle {
                position: fixed;
                bottom: 80px;
                right: var(--space-2xl, 32px);
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                color: var(--text-secondary);
                padding: var(--space-sm, 8px) var(--space-md, 12px);
                border-radius: var(--radius-full, 9999px);
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: var(--space-sm, 8px);
                font-size: var(--font-size-xs, 11px);
                font-weight: var(--font-medium, 500);
                transition: all var(--transition, 0.25s);
                z-index: 99;
                box-shadow: var(--shadow-sm);
                font-family: inherit;
            }
            
            .ai-bg-toggle:hover {
                background: var(--bg-elevated);
                border-color: var(--cyan);
                color: var(--text-primary);
                box-shadow: var(--shadow-md);
            }
            
            @media (max-width: 768px) {
                .ai-bg-toggle {
                    bottom: 70px;
                    right: var(--space-lg, 16px);
                    font-size: 0;
                    padding: var(--space-sm, 8px);
                }
                
                .ai-bg-toggle span {
                    display: none;
                }
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(toggle);
    }
    
    updateToggleButton() {
        const toggle = document.getElementById('ai-background-toggle');
        if (toggle) {
            const span = toggle.querySelector('span');
            if (span) {
                span.textContent = this.isEnabled ? 'Animations On' : 'Animations Off';
            }
        }
    }
    
    destroy() {
        this.stop();
        
        if (this.canvas) {
            this.canvas.remove();
            this.canvas = null;
        }
        
        const toggle = document.getElementById('ai-background-toggle');
        if (toggle) {
            toggle.remove();
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.aiBackground = new AIBackground();
    });
} else {
    window.aiBackground = new AIBackground();
}
