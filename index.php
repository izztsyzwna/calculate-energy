<?php 
include 'db_connection.php';

// Initialize default values for the Result card
$calc_data = [
    'v' => 0, 'i' => 0, 'r' => 0,
    'p' => 0, 'e' => 0, 'cost' => 0
];

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM calculations WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Handle clear all
if (isset($_POST['clear_all'])) {
    mysqli_query($conn, "TRUNCATE TABLE calculations");
    header("Location: index.php");
    exit();
}

// Step 1: Calculate
if (isset($_POST['calculate'])) {
    $v = $_POST['voltage'];
    $i = $_POST['current'];
    $r = $_POST['rate'];

    $power = $v * $i;
    $energy = ($power * 24) / 1000;
    $total_rm = $energy * ($r / 100);

    $calc_data = [
        'v' => $v, 'i' => $i, 'r' => $r,
        'p' => $power, 'e' => $energy, 'cost' => $total_rm
    ];
}

// Step 2: Save
if (isset($_POST['save_to_db'])) {
    $v = $_POST['v']; $i = $_POST['i']; $r = $_POST['r'];
    $p = $_POST['p']; $e = $_POST['e']; $cost = $_POST['cost'];

    if ($cost > 0) {
        $sql = "INSERT INTO calculations (voltage, current_amp, rate, power_w, energy_kwh, total_cost) 
                VALUES ('$v', '$i', '$r', '$p', '$e', '$cost')";
        mysqli_query($conn, $sql);
        header("Location: index.php");
        exit();
    }
}

$results = mysqli_query($conn, "SELECT * FROM calculations ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Energy Calculator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { color: #212529; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .btn-reset { background-color: #6c757d; color: white; }
        .btn-reset:hover { background-color: #5a6268; color: white; }
        .table-borderless td { border: 0; }
        .card { border-radius: 12px; }
    </style>
</head>
<body class="bg-light p-4">

<div class="container">
    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-3">1. Enter Details</h5>
                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Voltage (V)</label>
                            <input type="number" step="0.01" name="voltage" class="form-control" value="<?= isset($_POST['voltage']) ? $_POST['voltage'] : '' ?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Current (A)</label>
                            <input type="number" step="0.01" name="current" class="form-control" value="<?= isset($_POST['current']) ? $_POST['current'] : '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Rate (sen/kWh)</label>
                            <input type="number" step="0.01" name="rate" class="form-control" value="<?= isset($_POST['rate']) ? $_POST['rate'] : '' ?>" required>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="calculate" class="btn btn-primary flex-grow-1 fw-bold">Calculate</button>
                            <a href="index.php" class="btn btn-reset px-4">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-3">2. Result</h5>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Power Usage:</td>
                            <td class="text-end fw-bold"><?= $calc_data['p'] ?> W</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Energy Consumption:</td>
                            <td class="text-end fw-bold"><?= number_format($calc_data['e'], 3) ?> kWh/day</td>
                        </tr>
                    </table>
                    <hr class="text-muted opacity-25">
                    <div class="text-center py-2">
                        <small class="text-muted text-uppercase fw-semibold" style="letter-spacing: 1px;">Estimated Daily Cost</small>
                        <h2 class="text-success fw-bold mt-1">RM <?= number_format($calc_data['cost'], 2) ?></h2>
                    </div>
                    
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="v" value="<?= $calc_data['v'] ?>">
                        <input type="hidden" name="i" value="<?= $calc_data['i'] ?>">
                        <input type="hidden" name="r" value="<?= $calc_data['r'] ?>">
                        <input type="hidden" name="p" value="<?= $calc_data['p'] ?>">
                        <input type="hidden" name="e" value="<?= $calc_data['e'] ?>">
                        <input type="hidden" name="cost" value="<?= $calc_data['cost'] ?>">
                        <button type="submit" name="save_to_db" class="btn btn-success w-100 fw-bold py-2" <?= $calc_data['cost'] <= 0 ? 'disabled' : '' ?>>
                            Save to History
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm p-4 border-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">3. Usage History</h5>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete all records?');">
                        <button type="submit" name="clear_all" class="btn btn-outline-danger btn-sm px-3">Clear All History</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th>Input Details</th>
                                <th>Power (W)</th>
                                <th class="text-end">Cost (RM)</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($results) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($results)): ?>
                                <tr class="align-middle">
                                    <td>
                                        <div class="fw-semibold small text-dark"><?= $row['voltage'] ?>V / <?= $row['current_amp'] ?>A</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Rate: <?= $row['rate'] ?> sen</div>
                                    </td>
                                    <td><?= number_format($row['power_w'], 1) ?> W</td>
                                    <td class="text-end fw-bold text-success">RM <?= number_format($row['total_cost'], 2) ?></td>
                                    <td class="text-center">
                                        <a href="?delete=<?= $row['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger px-3 fw-semibold" 
                                           onclick="return confirm('Delete this record?')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-5 small">There is no calculation history yet. Please make a new calculation.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
