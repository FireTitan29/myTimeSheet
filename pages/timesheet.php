<?php
if (!defined('APP_RUNNING')) {
    header("Location: ../index.php");
    exit;
}
?>

<?php    
    // Determine "today"
    $today = time();
    $cutoffDay = 26;

    // Safe sanitisation of GET inputs (do NOT cast month to int)
    $monthName = $_GET['month'] ?? null;
    $yearInput = $_GET['year'] ?? null;

    // Month must be a valid name from our allowed list
    if (!in_array($monthName, get_months_array(), true)) {
        $monthName = null;
    }

    // Year must be numeric and within reasonable bounds
    if (!is_numeric($yearInput) || $yearInput < 1970 || $yearInput > 9999) {
        $yearInput = null;
    }

    $yearInput = $yearInput !== null ? (int)$yearInput : null;

    // Base date (from sanitised GET or today)
    if ($monthName && $yearInput) {
        $baseDate = strtotime("1 $monthName $yearInput");
    } elseif ($monthName) {
        $baseDate = strtotime("1 $monthName");
    } else {
        $baseDate = $today;
    }

    // Determine payroll period start (26th logic)
    if ((int)date('d', $today) >= $cutoffDay) {
        $periodStart = strtotime(date('Y-m-' . $cutoffDay, $baseDate));
    } else {
        $periodStart = strtotime(date('Y-m-' . $cutoffDay, strtotime('-1 month', $baseDate)));
    }

    // Payroll month is month AFTER period start
    $payrollMonthDate = strtotime('+1 month', $periodStart);

    // Final month values
    $month        = $monthName ?? date('m', $payrollMonthDate);
    $monthNameStr = $monthName ?? date('F', $payrollMonthDate);

    // Final year (handles Dec -> Jan rollover correctly)
    $year = $yearInput ?? (
        date('m', $payrollMonthDate) === '01' && date('m', $periodStart) === '12'
            ? (int)date('Y', $periodStart) + 1
            : (int)date('Y', $periodStart)
    );

    // Selected payroll month/year
    $selectedMonth = (int)$month;
    $selectedYear  = (int)$year;


?>
<?php if ($role === 'admin'): ?>
<form method="GET">
    <label for="name"> Select Staff Member<br>
    <input type="hidden" name="view" value="table">
        <select class="optionBox-person" name="name" id="name" onchange="this.form.submit()">
            <option hidden default <?php if (!isset($person)) {echo "selected";}?>><?php if ($person) {echo $person;} else {echo 'None';}?></option>
            <?php foreach (getAllStaffNames() AS $name):?>
                <option <?php if ($person === $name) echo 'selected' ?>><?php echo htmlspecialchars($name) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</form>
<?php endif; ?>
<!-- Seleting the Month for the Table -->
<form class="date-holder" method="GET">
    <input type="hidden" name="view" value="table">
    <label for="month">
        <select class="optionBox" name="month" id="month" onchange="this.form.submit()">
            <option hidden disabled selected>
                <?php echo $monthNameStr ?: date('F'); ?>
            </option>
            <!-- Creating Options -->
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <?php $monthName = date('F', mktime(0,0,0,$i,1)); ?>
                <option <?php if ($monthName === $monthNameStr) echo 'selected'; ?>>
                    <?= $monthName ?>
                </option>
            <?php endfor; ?>
        </select>
    </label>
    <input class="yearInput" type="number" name="year" value="<?= $year?>" onchange="this.form.submit()">
</form>
<table>
    <tr>
        <th>Date</th>
        <th>Day</th>
        <th>Time in</th>
        <th>Time Out</th>
        <th class="comments">Management Comments</th>
        <th class="comments">Staff Comments</th>
    </tr>
    <?php fillTable($month, $year, $person);?>
</table>
<?php include 'components/commentPopUp.php'; ?>