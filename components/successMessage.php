<?php
    $heading = '';
    $message = '';

    if (isset($_SESSION['clock']['staff_id'])) {
        $record = getRecord($_SESSION['clock']['staff_id'], date('Y-m-d'));
    }

    $clockedTime = '';

    $clock = $_SESSION['clock']['type'] ?? '';

    if ($clock === 'in') {
        $heading = 'Clock-in successful';
        $message = 'Start time recorded: ';
        $clockedTime = date('H:i',strtotime($record['timeIn'])) ?? '';
    } else if ($clock === 'out') {
        $heading = 'Clock-out successful';
        $message = 'End time recorded: ';
        $clockedTime = date('H:i', strtotime($record['timeOut'])) ?? '';
    }
    unset($_SESSION['clock']);
?>
<?php if ($clock !== ''): ?>
<div class="success-block">
    <h3 class="success-heading"><?= $heading ?></h3>
    <p class="success-p"><?= $message ?><span class="success-bold-time"><?= $clockedTime ?></span></p>
</div>
<?php endif; ?>