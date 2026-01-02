<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <h1><?= $title ?></h1>
    
    <!-- Date Filter -->
    <div class="card mb-3">
        <h3>Date Range Filter</h3>
        <form method="get" class="form-inline">
            <div class="form-group">
                <label>From:</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>To:</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Apply Filter</button>
            <a href="/admin/analytics/reports" class="btn btn-secondary">Reset</a>
        </form>
    </div>
    
    <!-- Daily Statistics -->
    <div class="card mb-3">
        <h3>Daily Event Count</h3>
        <?php if (empty($dailyStats)): ?>
            <p>No data available for the selected date range.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Events</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyStats as $stat): ?>
                    <tr>
                        <td><?= htmlspecialchars($stat['date']) ?></td>
                        <td><?= number_format($stat['count']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Simple Bar Chart -->
            <div class="chart-container">
                <canvas id="dailyChart"></canvas>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Events by Type -->
    <div class="card mb-3">
        <h3>Events by Type</h3>
        <?php if (empty($eventsByType)): ?>
            <p>No data available for the selected date range.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Event Type</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalEvents = array_sum(array_column($eventsByType, 'count'));
                    foreach ($eventsByType as $event): 
                        $percentage = $totalEvents > 0 ? ($event['count'] / $totalEvents * 100) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($event['event_type']) ?></td>
                        <td><?= number_format($event['count']) ?></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $percentage ?>%"><?= number_format($percentage, 1) ?>%</div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Browser Statistics -->
    <div class="card mb-3">
        <h3>Browser Distribution</h3>
        <?php if (empty($browserStats)): ?>
            <p>No browser data available.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Browser</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($browserStats as $browser): ?>
                    <tr>
                        <td><?= htmlspecialchars($browser['browser'] ?? 'Unknown') ?></td>
                        <td><?= number_format($browser['count']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Geographic Distribution -->
    <div class="card mb-3">
        <h3>Top 10 Countries</h3>
        <?php if (empty($geoStats)): ?>
            <p>No geographic data available.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($geoStats as $geo): ?>
                    <tr>
                        <td><?= htmlspecialchars($geo['country'] ?? 'Unknown') ?></td>
                        <td><?= number_format($geo['count']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Hourly Distribution -->
    <div class="card mb-3">
        <h3>Hourly Activity</h3>
        <?php if (empty($hourlyStats)): ?>
            <p>No hourly data available.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Hour</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hourlyStats as $hourly): ?>
                    <tr>
                        <td><?= sprintf('%02d:00', $hourly['hour']) ?></td>
                        <td><?= number_format($hourly['count']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.form-inline {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group label {
    font-weight: 600;
    margin-bottom: 5px;
}
.progress {
    background: #e9ecef;
    border-radius: 4px;
    height: 25px;
    overflow: hidden;
    width: 200px;
}
.progress-bar {
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    font-size: 12px;
}
.chart-container {
    margin-top: 20px;
    max-width: 100%;
    height: 300px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
<?php if (!empty($dailyStats)): ?>
// Daily stats chart
const dailyData = {
    labels: [<?php foreach ($dailyStats as $stat): ?>'<?= $stat['date'] ?>',<?php endforeach; ?>],
    datasets: [{
        label: 'Events',
        data: [<?php foreach ($dailyStats as $stat): ?><?= $stat['count'] ?>,<?php endforeach; ?>],
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

const dailyConfig = {
    type: 'bar',
    data: dailyData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
};

new Chart(document.getElementById('dailyChart'), dailyConfig);
<?php endif; ?>
</script>

<?php View::endSection(); ?>
