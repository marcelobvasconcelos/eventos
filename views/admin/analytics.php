<?php
$title = 'Dashboard Analítico';
ob_start();
?>
<div class="row mb-4 animate-slide-down">
    <div class="col-md-8">
        <h2 class="fw-bold text-white"><i class="fas fa-chart-pie me-2"></i>Dashboard Analítico</h2>
        <p class="text-white-50">Estatísticas detalhadas e indicadores de performance.</p>
    </div>
    <div class="col-md-4 text-md-end">
        <form action="" method="GET" class="d-inline-block">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                <select name="year" class="form-select border-start-0" onchange="this.form.submit()">
                    <?php 
                    $currentYear = date('Y');
                    for ($y = $currentYear; $y >= $currentYear - 4; $y--) {
                        $selected = ($year == $y) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row g-4 mb-5 animate-slide-up">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="fas fa-calendar-check fa-4x text-primary"></i>
                </div>
                <h6 class="text-uppercase text-muted fw-bold small">Total de Eventos (<?php echo $year; ?>)</h6>
                <h2 class="fw-bold display-5 mb-0 text-primary"><?php echo $analyticsData['total_events']; ?></h2>
                <div class="small mt-2 text-success">
                    <i class="fas fa-check-circle me-1"></i> <?php echo $analyticsData['status_stats']['realized']; ?> Realizados
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="fas fa-clock fa-4x text-info"></i>
                </div>
                <h6 class="text-uppercase text-muted fw-bold small">Horas Totais (Ano)</h6>
                <h2 class="fw-bold display-5 mb-0 text-info"><?php echo $analyticsData['hours_stats']['year']; ?>h</h2>
                <div class="small mt-2 text-muted">
                    Carga horária acumulada
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="fas fa-chart-line fa-4x text-warning"></i>
                </div>
                <h6 class="text-uppercase text-muted fw-bold small">Média Mensal</h6>
                <h2 class="fw-bold display-5 mb-0 text-warning">
                    <?php 
                    $monthsPassed = ($year == date('Y')) ? (int)date('m') : 12;
                    echo round($analyticsData['total_events'] / $monthsPassed, 1); 
                    ?>
                </h2>
                <div class="small mt-2 text-muted">
                    Eventos / Mês
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="fas fa-hourglass-half fa-4x text-success"></i>
                </div>
                <h6 class="text-uppercase text-muted fw-bold small">Horas (Este Mês)</h6>
                <h2 class="fw-bold display-5 mb-0 text-success"><?php echo $analyticsData['hours_stats']['month']; ?>h</h2>
                <div class="small mt-2 text-muted">
                    Mês Atual
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Timeline Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-secondary"><i class="fas fa-stream me-2"></i>Linha do Tempo (<?php echo $year; ?>)</h5>
            </div>
            <div class="card-body">
                <canvas id="timelineChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <!-- Location Chart -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 py-3">
                <h5 class="fw-bold mb-0 text-secondary"><i class="fas fa-map-marker-alt me-2"></i>Eventos por Local</h5>
            </div>
            <div class="card-body">
                <canvas id="locationChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
         <div class="card border-0 shadow-sm h-100 bg-light">
             <div class="card-body text-center p-5">
                 <h4 class="fw-bold text-muted mb-3">Gestão de Inteligência</h4>
                 <p class="mb-0 text-muted" style="max-width: 600px; margin: 0 auto;">
                     Use estes dados para otimizar a alocação de recursos. O pico de horas mensais indica a necessidade de reforço nas equipes de apoio e segurança.
                 </p>
             </div>
         </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data Preparation
    const timelineData = <?php echo json_encode($analyticsData['timeline_stats']); ?>;
    const locationData = <?php echo json_encode($analyticsData['location_stats']); ?>;

    const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    const realizedCounts = Object.values(timelineData).map(d => d.realized);
    const scheduledCounts = Object.values(timelineData).map(d => d.scheduled);

    const locLabels = locationData.map(d => d.name || 'Outro');
    const locCounts = locationData.map(d => d.total);

    // Timeline Chart
    const ctxTimeline = document.getElementById('timelineChart').getContext('2d');
    new Chart(ctxTimeline, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Realizados',
                    data: realizedCounts,
                    backgroundColor: '#198754', // Success Green
                    borderRadius: 4
                },
                {
                    label: 'Agendados',
                    data: scheduledCounts,
                    backgroundColor: '#0d6efd', // Primary Blue
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    // Location Chart
    const ctxLocation = document.getElementById('locationChart').getContext('2d');
    new Chart(ctxLocation, {
        type: 'doughnut',
        data: {
            labels: locLabels,
            datasets: [{
                data: locCounts,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
