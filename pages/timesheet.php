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
<div id="timesheet" class="mobile-table-holder table-scroll">
<div class="mobile-page-top">
    <form method="GET">
        <input type="hidden" name="view" value="table">
        <!-- Fixes bug when user is selected, the month & year changes back to Latest instead of selected month -->
        <input type="hidden" name="month" value="<?=
            isset($_GET['month'])
                ? htmlspecialchars($_GET['month'])
                : htmlspecialchars($monthNameStr)
        ?>">

        <input type="hidden" name="year" value="<?=
            isset($_GET['year'])
                ? (int)$_GET['year']
                : (int)$year
        ?>">
        <label for="name"><span class="print-hide-element">Select Staff Member</span><br>

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
        <select class="optionBox mobile-date" name="month" id="month" onchange="this.form.submit()">
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
<?php if ((isset($_GET["name"]) || isset($person)) && in_array($person, getAllStaffNames())): ?>
    <button id="download" class="download-timesheet-button hide-this-mobile">Download Timesheet</button><br>
<?php endif; ?>
</div>
<table>
    <tr>
        <th>Date</th>
        <th>Day</th>
        <th>Time in</th>
        <th>Time Out</th>
        <th class="comments">Management Comments</th>
        <th class="comments">Staff Comments</th>
        <th>Leave</th>
    </tr>
    <?php fillTable($month, $year, $person);?>
</table>
</div>
<?php include 'components/commentPopUp.php'; ?>
<?php include 'components/leavePopUp.php'; ?>
<?php include 'components/leaveDetailsPopUp.php'; ?>

<script>
function toggleRow(event, row) {
    // Do not toggle row when clicking interactive elements,
    // EXCEPT for "View comments" preview buttons
    if (
        event.target.closest('.commentLabel') ||
        event.target.closest('.leave-preview') ||
        (event.target.closest('button') && !event.target.closest('.toggle-comments'))
    ) {
        return;
    }

    if (row.dataset.hasExpand !== '1') {
        return;
    }

    const isOpen = row.classList.contains('row-open');

    // Expanded content blocks
    const contentBlocks = row.querySelectorAll('.comments-content, .comment-block');

    // Collapsed preview elements (titles, truncated text, view buttons)
    const previewBlocks = row.querySelectorAll('.row-preview');

    contentBlocks.forEach(block => {
        block.style.display = isOpen ? 'none' : 'block';
    });

    previewBlocks.forEach(el => {
        el.style.display = isOpen ? '' : 'none';
    });

    row.classList.toggle('row-open', !isOpen);
}
</script>


<!-- Adding ability to screenshot page to download to image -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function expandAllRows() {
  const rows = document.querySelectorAll('tr[data-has-expand="1"]');

    rows.forEach(row => {
        if (!row.classList.contains('row-open')) {
        const contentBlocks = row.querySelectorAll('.comments-content, .comment-block');
        const previewBlocks = row.querySelectorAll('.row-preview');

        contentBlocks.forEach(block => block.style.display = 'block');
        previewBlocks.forEach(el => el.style.display = 'none');

        row.classList.add('row-open');
        }
    });
    }
    document.getElementById("download").addEventListener("click", async () => {
    const element = document.getElementById("timesheet");
    if (!element) {
        console.error("Timesheet export element not found");
        return;
    }

    expandAllRows();

    const nameSelect = document.getElementById("name");
    const person = nameSelect
        ? nameSelect.options[nameSelect.selectedIndex].text.trim()
        : "Unknown";

    const monthSelect = document.getElementById("month");
    const month = monthSelect
        ? monthSelect.options[monthSelect.selectedIndex].text.trim()
        : "Month";

    const yearInput = document.querySelector('input[name="year"]');
    const year = yearInput ? yearInput.value : "Year";

    const safePerson = person.replace(/\s+/g, "-");
    const safeMonth  = month.replace(/\s+/g, "-");
    const filename = `Timesheet_${safePerson}_${safeMonth}-${year}.png`;

    await document.fonts.ready;

    html2canvas(element, {
        scale: 2,
        useCORS: true,
        backgroundColor: "#ffffff",
        windowWidth: element.scrollWidth,
        windowHeight: element.scrollHeight
    }).then(canvas => {
        const link = document.createElement("a");
        link.download = filename;
        link.href = canvas.toDataURL("image/png");
        link.click();
    });
    });
</script>