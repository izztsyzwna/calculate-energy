<?php 

function calculateElectricityDetails($voltage, $current, $rate) {
    // Basic power calculation: P = V * I
    $powerWatts = $voltage * $current;
    $powerKiloWatts = $powerWatts / 1000;
    
    // Rate conversion: Convert cents to RM
    $rateRM = $rate / 100;

    $schedule = [];

    // Generate data for 24 hours
    for ($hour = 1; $hour <= 24; $hour++) {
        $energyConsumed = $powerKiloWatts * $hour;
        $totalCost = $energyConsumed * $rateRM;

        $schedule[] = [
            'hour' => $hour,
            'energy' => $energyConsumed,
            'cost' => $totalCost
        ];
    }

    return [
        'summary' => [
            'power_w' => $powerWatts,
            'power_kw' => $powerKiloWatts,
            'rate_rm' => $rateRM
        ],
        'hourly_data' => $schedule
    ];
}

$viewData = null;
$formValues = ['v' => '', 'i' => '', 'r' => ''];

if (isset($_POST['calculate'])) {
    // Capture input values
    $v = (float)$_POST['voltage'];
    $i = (float)$_POST['current'];
    $r = (float)$_POST['rate'];

    // Keep values in form for better UX
    $formValues = ['v' => $v, 'i' => $i, 'r' => $r];

    // EXECUTE THE FUNCTION
    $viewData = calculateElectricityDetails($v, $i, $r);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Bill Forecaster</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; color: #333; }
        .main-card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .table-container { border-radius: 10px; overflow: hidden; }
        .result-header { background: #198754; color: white; border-radius: 10px; padding: 1.5rem; }
        .highlight-row { background-color: #e9f7ef !important; }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="row justify-content-center">
        <!-- Input Section -->
        <div class="col-lg-4 mb-4">
            <div class="card main-card p-4">
                <h4 class="mb-4 fw-bold text-success">Calculate Cost</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Voltage (V)</label>
                        <input type="number" step="any" name="voltage" class="form-control" value="<?= htmlspecialchars($formValues['v']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current (A)</label>
                        <input type="number" step="any" name="current" class="form-control" value="<?= htmlspecialchars($formValues['i']) ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Current Rate (sen/kWh)</label>
                        <input type="number" step="any" name="rate" class="form-control" value="<?= htmlspecialchars($formValues['r']) ?>" required>
                    </div>
                    <button type="submit" name="calculate" class="btn btn-success w-100 py-2 fw-bold">Calculate</button>
                </form>
            </div>
        </div>

        <!-- Output Section -->
        <div class="col-lg-8">
            <?php if ($viewData): ?>
                <!-- Power Summary -->
                <div class="result-header mb-4 shadow-sm">
                    <div class="row text-center">
                        <div class="col-md-6 border-end border-white border-opacity-25">
                            <p class="small text-uppercase mb-1" style="opacity: 0.8;">Power Load</p>
                            <h3><?= number_format($viewData['summary']['power_kw'], 5) ?> <small>kW</small></h3>
                        </div>
                        <div class="col-md-6">
                            <p class="small text-uppercase mb-1" style="opacity: 0.8;">Calculated Rate</p>
                            <h3>RM <?= number_format($viewData['summary']['rate_rm'], 3) ?> <small>/kWh</small></h3>
                        </div>
                    </div>
                </div>

                <!-- 24H Table -->
                <div class="card main-card p-4">
                    <h5 class="fw-bold mb-3">24-Hour Electricity Projection</h5>
                    <div class="table-container border">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3"># Hour</th>
                                    <th class="text-center">Energy (kWh)</th>
                                    <th class="text-end pe-3">Total (RM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($viewData['hourly_data'] as $item): ?>
                                    <tr class="<?= ($item['hour'] % 6 == 0) ? 'highlight-row' : '' ?>">
                                        <td class="ps-3"><?= $item['hour'] ?></td>
                                        <td class="text-center"><?= number_format($item['energy'], 5) ?></td>
                                        <td class="text-end pe-3 fw-bold text-success"><?= number_format($item['cost'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="card main-card p-5 text-center bg-white border border-dashed">
                    <div class="text-muted mb-3"><i class="display-1 opacity-25">⚡</i></div>
                    <h5>No data to display</h5>
                    <p class="text-muted">Enter voltage, current, and rate to generate the forecast.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>