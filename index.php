<?php 

$show_result = false;
$calc_data = ['v' => 0, 'i' => 0, 'r' => 0, 'p_w' => 0, 'p_kw' => 0];

if (isset($_POST['calculate'])) {
    $v = $_POST['voltage'];
    $i = $_POST['current'];
    $r = $_POST['rate']; // rate in cents

    $power_w = $v * $i;
    $power_kw = $power_w / 1000; 
    
    $calc_data = [
        'v' => $v, 'i' => $i, 'r' => $r, 
        'p_w' => $power_w, 'p_kw' => $power_kw
    ];
    $show_result = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy Forecaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; color: #2d3436; font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .table-container { max-height: 600px; overflow-y: auto; border-radius: 8px; }
        .highlight-row { background-color: #f0fff4 !important; font-weight: 600; }
        .bg-success-gradient { 
            background: linear-gradient(135deg, #28a745 0%, #218838 100%); 
            color: white; 
        }
    </style>
</head>
<body class="p-4">

<div class="container">
    <header class="text-center mb-5">
        <h2 class="fw-bold text-primary">⚡ Energy Consumption Forecaster</h2>
        <p class="text-muted">Analyze power costs over a 24-hour cycle</p>
    </header>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-4 mb-3">
                <h5 class="fw-bold mb-4">Device Settings</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Voltage (V)</label>
                        <input type="number" step="0.01" name="voltage" class="form-control form-control-lg" value="<?= $calc_data['v'] ?: '' ?>" placeholder="e.g. 240" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Current (A)</label>
                        <input type="number" step="0.01" name="current" class="form-control form-control-lg" value="<?= $calc_data['i'] ?: '' ?>" placeholder="e.g. 0.5" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Rate (cents/kWh)</label>
                        <input type="number" step="0.01" name="rate" class="form-control form-control-lg" value="<?= $calc_data['r'] ?: '' ?>" placeholder="e.g. 21.8" required>
                    </div>
                    <button type="submit" name="calculate" class="btn btn-success w-100 fw-bold py-3 shadow">Calculate Schedule</button>
                    <a href="index.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted small">Reset All</a>
                </form>
            </div>

            <?php if($show_result): ?>
            <div class="card p-4 bg-success-gradient text-center shadow">
                <small class="text-uppercase fw-bold" style="opacity: 0.8;">Current Power Load</small>
                <h2 class="fw-bold mb-0 mt-1"><?= number_format($calc_data['p_kw'], 4) ?> kW</h2>
                <p class="small mb-0" style="opacity: 0.7;"><?= number_format($calc_data['p_w'], 2) ?> Watts</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-8">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">24-Hour Forecast Table</h5>
                    <?php if($show_result): ?>
                        <span class="badge bg-primary px-3 py-2">Rate: <?= $calc_data['r'] ?> cents</span>
                    <?php endif; ?>
                </div>
                
                <?php if($show_result): ?>
                <div class="table-container border">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th class="py-3 ps-3">Usage Duration</th>
                                <th class="py-3 text-center">Energy (kWh)</th>
                                <th class="py-3 text-end pe-3">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            for ($hour = 1; $hour <= 24; $hour++) {
                                $energy = ($calc_data['p_w'] * $hour) / 1000;
                                $cost = $energy * ($calc_data['r'] / 100);
                                $class = ($hour % 6 == 0) ? 'highlight-row' : '';
                                
                                echo "<tr class='$class'>";
                                echo "<td class='ps-3 fw-medium'>Hour $hour</td>";
                                echo "<td class='text-center'>" . number_format($energy, 3) . " kWh</td>";
                                echo "<td class='text-end pe-3 fw-bold text-success'>RM " . number_format($cost, 2) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5 bg-light rounded border border-dashed">
                    <div class="mb-3 display-6 text-muted">📊</div>
                    <p class="fw-medium mb-0">No data to display</p>
                    <p class="small text-muted">Fill in the settings and click calculate to see the forecast.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
