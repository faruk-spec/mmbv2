<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content analytics-dashboard">
    <div class="dashboard-header">
        <h1><?= $title ?></h1>
        <div class="header-actions">
            <button id="liveToggle" class="btn btn-success active" data-live="true">
                <i class="fa fa-circle pulse"></i> Live
            </button>
        </div>
    </div>
    
    <!-- Advanced Filters -->
    <div class="filters-panel card">
        <div class="filter-row">
            <div class="filter-group">
                <label>Date Range</label>
                <div class="date-inputs">
                    <input type="date" id="dateFrom" class="form-control" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                    <span>to</span>
                    <input type="date" id="dateTo" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            
            <div class="filter-group">
                <label>Timeframe</label>
                <select id="timeframe" class="form-control">
                    <option value="day">Daily</option>
                    <option value="hour">Hourly</option>
                    <option value="minute">Minute</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Quick Filters</label>
                <div class="quick-filters">
                    <button class="btn btn-sm" data-range="today">Today</button>
                    <button class="btn btn-sm" data-range="yesterday">Yesterday</button>
                    <button class="btn btn-sm" data-range="7days">Last 7 Days</button>
                    <button class="btn btn-sm active" data-range="30days">Last 30 Days</button>
                    <button class="btn btn-sm" data-range="90days">Last 90 Days</button>
                </div>
            </div>
            
            <div class="filter-group">
                <button id="applyFilters" class="btn btn-primary">Apply Filters</button>
                <button id="resetFilters" class="btn btn-secondary">Reset</button>
            </div>
        </div>
    </div>
    
    <!-- Live Stats Bar -->
    <div class="live-stats-bar">
        <div class="stat-item">
            <span class="stat-label">Active Now</span>
            <span class="stat-value" id="activeNow">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Events/Min</span>
            <span class="stat-value" id="eventsPerMin">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Total Events</span>
            <span class="stat-value" id="totalEvents">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Unique Users</span>
            <span class="stat-value" id="uniqueUsers">0</span>
        </div>
    </div>
    
    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Timeline Chart (Line/Area) -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h3>Events Timeline</h3>
                <div class="chart-controls">
                    <button class="chart-type-btn active" data-type="line" data-chart="timeline">
                        <i class="fa fa-line-chart"></i>
                    </button>
                    <button class="chart-type-btn" data-type="area" data-chart="timeline">
                        <i class="fa fa-area-chart"></i>
                    </button>
                    <button class="chart-type-btn" data-type="bar" data-chart="timeline">
                        <i class="fa fa-bar-chart"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>
        
        <!-- Event Types Distribution (Pie/Doughnut) -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Event Types</h3>
                <div class="chart-controls">
                    <button class="chart-type-btn active" data-type="doughnut" data-chart="eventTypes">
                        <i class="fa fa-pie-chart"></i>
                    </button>
                    <button class="chart-type-btn" data-type="pie" data-chart="eventTypes">
                        <i class="fa fa-circle-o"></i>
                    </button>
                    <button class="chart-type-btn" data-type="bar" data-chart="eventTypes">
                        <i class="fa fa-bar-chart"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="eventTypesChart"></canvas>
            </div>
        </div>
        
        <!-- Browser Distribution -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Browsers</h3>
                <div class="chart-controls">
                    <button class="chart-type-btn active" data-type="doughnut" data-chart="browsers">
                        <i class="fa fa-pie-chart"></i>
                    </button>
                    <button class="chart-type-btn" data-type="bar" data-chart="browsers">
                        <i class="fa fa-bar-chart"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="browsersChart"></canvas>
            </div>
        </div>
        
        <!-- Platform Distribution -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Platforms</h3>
                <div class="chart-controls">
                    <button class="chart-type-btn active" data-type="polarArea" data-chart="platforms">
                        <i class="fa fa-circle"></i>
                    </button>
                    <button class="chart-type-btn" data-type="doughnut" data-chart="platforms">
                        <i class="fa fa-pie-chart"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="platformsChart"></canvas>
            </div>
        </div>
        
        <!-- Geographic Distribution -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Top Countries</h3>
                <div class="chart-controls">
                    <button class="chart-type-btn active" data-type="bar" data-chart="countries" data-horizontal="true">
                        <i class="fa fa-align-left"></i>
                    </button>
                    <button class="chart-type-btn" data-type="bar" data-chart="countries">
                        <i class="fa fa-bar-chart"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="countriesChart"></canvas>
            </div>
        </div>
        
        <!-- Hourly Pattern Heatmap -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h3>Hourly Activity Pattern (24h)</h3>
            </div>
            <div class="chart-container">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
        
        <!-- Day of Week Pattern -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Day of Week Pattern</h3>
                <div class="chart-controls">
                    <button class="chart-type-btn active" data-type="radar" data-chart="dayPattern">
                        <i class="fa fa-star"></i>
                    </button>
                    <button class="chart-type-btn" data-type="bar" data-chart="dayPattern">
                        <i class="fa fa-bar-chart"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="dayPatternChart"></canvas>
            </div>
        </div>
        
        <!-- Conversion Funnel -->
        <div class="chart-card half-width">
            <div class="chart-header">
                <h3>Conversion Funnel</h3>
            </div>
            <div class="chart-container funnel">
                <div id="conversionFunnel"></div>
            </div>
        </div>
    </div>
    

</div>

<style>
.analytics-dashboard {
    padding: 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.filters-panel {
    padding: 20px;
    margin-bottom: 20px;
}

.filter-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.date-inputs {
    display: flex;
    gap: 10px;
    align-items: center;
}

.quick-filters {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.quick-filters .btn {
    padding: 5px 10px;
    font-size: 12px;
    background: #6c757d;
    color: white;
    border: 1px solid #6c757d;
}

.quick-filters .btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.live-stats-bar {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-item {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.stat-label {
    display: block;
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 32px;
    font-weight: bold;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.chart-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.chart-card.full-width {
    grid-column: span 2;
}

.chart-card.half-width {
    grid-column: span 1;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.chart-header h3 {
    margin: 0;
    font-size: 18px;
}

.chart-controls {
    display: flex;
    gap: 5px;
}

.chart-type-btn {
    background: #f0f0f0;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.chart-type-btn:hover {
    background: #e0e0e0;
}

.chart-type-btn.active {
    background: #007bff;
    color: white;
}

.chart-type-btn.loading {
    pointer-events: none;
    opacity: 0.6;
}

.chart-type-btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

.chart-container {
    position: relative;
    height: 300px;
}

.chart-card.full-width .chart-container {
    height: 400px;
}

.chart-container.funnel {
    height: auto;
}

#conversionFunnel {
    padding: 20px 0;
}

.funnel-stage {
    background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 20px;
    margin: 10px auto;
    border-radius: 5px;
    text-align: center;
    position: relative;
}

.funnel-stage:nth-child(1) { width: 100%; }
.funnel-stage:nth-child(2) { width: 80%; }
.funnel-stage:nth-child(3) { width: 60%; }
.funnel-stage:nth-child(4) { width: 40%; }

.funnel-stage h4 {
    margin: 0 0 5px 0;
}

.funnel-stage p {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

.pulse {
    animation: pulse-animation 2s infinite;
}

@keyframes pulse-animation {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-card.full-width,
    .chart-card.half-width {
        grid-column: span 1;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Analytics Dashboard Controller
class AnalyticsDashboard {
    constructor() {
        this.charts = {};
        this.liveMode = true;
        this.refreshInterval = null;
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadData();
        this.startLiveUpdates();
    }
    
    setupEventListeners() {
        // Apply filters
        document.getElementById('applyFilters').addEventListener('click', () => this.loadData());
        document.getElementById('resetFilters').addEventListener('click', () => this.resetFilters());
        
        // Quick filters
        document.querySelectorAll('.quick-filters .btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.quick-filters .btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.applyQuickFilter(e.target.dataset.range);
            });
        });
        
        // Live toggle
        document.getElementById('liveToggle').addEventListener('click', (e) => {
            this.liveMode = !this.liveMode;
            e.target.classList.toggle('active');
            e.target.dataset.live = this.liveMode;
            if (this.liveMode) {
                this.startLiveUpdates();
            } else {
                this.stopLiveUpdates();
            }
        });
        
        // Chart type toggles
        document.querySelectorAll('.chart-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const button = e.target.closest('.chart-type-btn');
                const chartName = button.dataset.chart;
                const chartType = button.dataset.type;
                const horizontal = button.dataset.horizontal === 'true';
                
                // Add loading state
                button.classList.add('loading');
                
                // Remove active from all buttons
                button.parentElement.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active'));
                button.classList.add('active');
                
                // Update chart with a small delay to show loader
                setTimeout(() => {
                    this.updateChartType(chartName, chartType, horizontal);
                    button.classList.remove('loading');
                }, 100);
            });
        });
    }
    
    applyQuickFilter(range) {
        const today = new Date();
        let dateFrom = new Date();
        
        switch(range) {
            case 'today':
                dateFrom = new Date(today);
                break;
            case 'yesterday':
                dateFrom = new Date(today);
                dateFrom.setDate(dateFrom.getDate() - 1);
                break;
            case '7days':
                dateFrom = new Date(today);
                dateFrom.setDate(dateFrom.getDate() - 7);
                break;
            case '30days':
                dateFrom = new Date(today);
                dateFrom.setDate(dateFrom.getDate() - 30);
                break;
            case '90days':
                dateFrom = new Date(today);
                dateFrom.setDate(dateFrom.getDate() - 90);
                break;
        }
        
        document.getElementById('dateFrom').value = dateFrom.toISOString().split('T')[0];
        document.getElementById('dateTo').value = today.toISOString().split('T')[0];
        this.loadData();
    }
    
    resetFilters() {
        const today = new Date();
        const thirtyDaysAgo = new Date(today);
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        document.getElementById('dateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById('dateTo').value = today.toISOString().split('T')[0];
        document.getElementById('timeframe').value = 'day';
        this.loadData();
    }
    
    async loadData() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const timeframe = document.getElementById('timeframe').value;
        
        try {
            const response = await fetch(`/admin/analytics/export?ajax=1&action=stats&date_from=${dateFrom}&date_to=${dateTo}&timeframe=${timeframe}`);
            const data = await response.json();
            this.updateDashboard(data);
        } catch (error) {
            console.error('Error loading analytics data:', error);
        }
    }
    
    updateDashboard(data) {
        // Update live stats
        document.getElementById('activeNow').textContent = data.activeNow || 0;
        
        // Check if timeline data exists
        if (!data.timeline || !Array.isArray(data.timeline)) {
            data.timeline = [];
        }
        
        const totalEvents = data.timeline.reduce((sum, item) => sum + parseInt(item.count), 0);
        const uniqueUsers = data.timeline.reduce((sum, item) => sum + parseInt(item.unique_users), 0);
        
        document.getElementById('totalEvents').textContent = totalEvents.toLocaleString();
        document.getElementById('uniqueUsers').textContent = uniqueUsers.toLocaleString();
        
        if (data.timeline.length > 0) {
            const eventsPerMin = (totalEvents / (data.timeline.length * (data.timeframe === 'minute' ? 1 : 60))).toFixed(1);
            document.getElementById('eventsPerMin').textContent = eventsPerMin;
        }
        
        // Update charts
        this.updateTimelineChart(data.timeline);
        this.updateEventTypesChart(data.eventTypes);
        this.updateBrowsersChart(data.browsers);
        this.updatePlatformsChart(data.platforms);
        this.updateCountriesChart(data.countries);
        this.updateHourlyChart(data.hourlyPattern);
        this.updateDayPatternChart(data.dayPattern);
        this.updateConversionFunnel(data.conversions);
    }
    
    updateTimelineChart(data) {
        const ctx = document.getElementById('timelineChart').getContext('2d');
        
        if (this.charts.timeline) {
            this.charts.timeline.destroy();
        }
        
        this.charts.timeline = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.time_period),
                datasets: [{
                    label: 'Events',
                    data: data.map(item => item.count),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.3)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Unique Users',
                    data: data.map(item => item.unique_users),
                    borderColor: '#f093fb',
                    backgroundColor: 'rgba(240, 147, 251, 0.3)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
    }
    
    updateEventTypesChart(data) {
        const ctx = document.getElementById('eventTypesChart').getContext('2d');
        
        if (this.charts.eventTypes) {
            this.charts.eventTypes.destroy();
        }
        
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
        ];
        
        this.charts.eventTypes = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.event_type),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: colors.slice(0, data.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    updateBrowsersChart(data) {
        const ctx = document.getElementById('browsersChart').getContext('2d');
        
        if (this.charts.browsers) {
            this.charts.browsers.destroy();
        }
        
        const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
        
        this.charts.browsers = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.browser || 'Unknown'),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: colors.slice(0, data.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    updatePlatformsChart(data) {
        const ctx = document.getElementById('platformsChart').getContext('2d');
        
        if (this.charts.platforms) {
            this.charts.platforms.destroy();
        }
        
        const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
        
        this.charts.platforms = new Chart(ctx, {
            type: 'polarArea',
            data: {
                labels: data.map(item => item.platform || 'Unknown'),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: colors.slice(0, data.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    updateCountriesChart(data) {
        const ctx = document.getElementById('countriesChart').getContext('2d');
        
        if (this.charts.countries) {
            this.charts.countries.destroy();
        }
        
        this.charts.countries = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.country || 'Unknown'),
                datasets: [{
                    label: 'Visits',
                    data: data.map(item => item.count),
                    backgroundColor: '#4facfe'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    updateHourlyChart(data) {
        const ctx = document.getElementById('hourlyChart').getContext('2d');
        
        if (this.charts.hourly) {
            this.charts.hourly.destroy();
        }
        
        // Fill in missing hours
        const hours = Array.from({length: 24}, (_, i) => i);
        const hourData = hours.map(hour => {
            const found = data.find(item => parseInt(item.hour) === hour);
            return found ? parseInt(found.count) : 0;
        });
        
        this.charts.hourly = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: hours.map(h => `${h}:00`),
                datasets: [{
                    label: 'Events by Hour',
                    data: hourData,
                    backgroundColor: hours.map((h, i) => {
                        const intensity = hourData[i] / Math.max(...hourData);
                        return `rgba(79, 172, 254, ${0.3 + intensity * 0.7})`;
                    })
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    updateDayPatternChart(data) {
        const ctx = document.getElementById('dayPatternChart').getContext('2d');
        
        if (this.charts.dayPattern) {
            this.charts.dayPattern.destroy();
        }
        
        this.charts.dayPattern = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: data.map(item => item.day_name),
                datasets: [{
                    label: 'Events by Day',
                    data: data.map(item => item.count),
                    backgroundColor: 'rgba(79, 172, 254, 0.2)',
                    borderColor: '#4facfe',
                    pointBackgroundColor: '#4facfe'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    updateConversionFunnel(data) {
        const funnel = document.getElementById('conversionFunnel');
        
        const stages = [
            { name: 'Page Visits', type: 'page_visit' },
            { name: 'Registrations', type: 'user_register' },
            { name: 'Logins', type: 'user_login' },
            { name: 'Return Visits', type: 'return_visit' }
        ];
        
        let html = '';
        stages.forEach((stage, index) => {
            const found = data.find(item => item.event_type === stage.type);
            const count = found ? parseInt(found.count) : 0;
            
            html += `
                <div class="funnel-stage">
                    <h4>${stage.name}</h4>
                    <p>${count.toLocaleString()}</p>
                </div>
            `;
        });
        
        funnel.innerHTML = html;
    }
    
    updateChartType(chartName, newType, horizontal = false) {
        if (!this.charts[chartName]) return;
        
        const oldChart = this.charts[chartName];
        const ctx = oldChart.canvas.getContext('2d');
        const data = oldChart.data;
        let options = JSON.parse(JSON.stringify(oldChart.options));
        
        // Handle horizontal bar charts (Chart.js v3 syntax)
        if (newType === 'bar' && horizontal) {
            options.indexAxis = 'y';
        } else {
            delete options.indexAxis;
        }
        
        oldChart.destroy();
        
        this.charts[chartName] = new Chart(ctx, {
            type: newType,
            data: data,
            options: options
        });
    }
    
    startLiveUpdates() {
        this.refreshInterval = setInterval(() => {
            if (this.liveMode) {
                this.loadData();
            }
        }, 30000); // Update every 30 seconds
    }
    
    stopLiveUpdates() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', () => {
    new AnalyticsDashboard();
});
</script>

<?php View::endSection(); ?>
