<?php 
    $staffMembers = getAllStaffNames();
    
    // Determine "today"
    $today = time();
    $cutoffDay = 26;

    // Base date (from GET or today)
    if (isset($_GET['month'], $_GET['year'])) {
        $baseDate = strtotime('1 ' . $_GET['month'] . ' ' . (int)$_GET['year']);
    } elseif (isset($_GET['month'])) {
        $baseDate = strtotime('1 ' . $_GET['month']);
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
    $month        = $_GET['month'] ?? date('m', $payrollMonthDate);
    $monthNameStr = $_GET['month'] ?? date('F', $payrollMonthDate);

    // Final year (handles Dec -> Jan rollover correctly)
    $year = $_GET['year'] ?? (
        date('m', $payrollMonthDate) === '01' && date('m', $periodStart) === '12'
            ? (int)date('Y', $periodStart) + 1
            : (int)date('Y', $periodStart)
    );

    // Selected payroll month/year (e.g. February 2026)
    $selectedMonth = (int)$month;
    $selectedYear  = (int)$year;


?>
<?php if ($role === 'admin'): ?>
<form method="GET">
    <label for="name"> Select Staff Member<br>
    <input type="hidden" name="view" value="table">
        <select class="optionBox-person" name="name" id="name" onchange="this.form.submit()">
            <option hidden default <?php if (!isset($person)) {echo "selected";}?>><?php if ($person) {echo $person;} else {echo 'None';}?></option>
            <?php foreach ($staffMembers AS $name):?>
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