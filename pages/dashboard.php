
<?php 
    $staff = currentlyInAndOut(); 
    $in = 0;
    $out = 0;
    $lateCount = 0;
    $pendingClockOut = 0;
?>

<!-- <div style="margin-top: 5px;"></div> -->
<div class="entire-block-dashboard">
    <div style="margin-bottom:20px;">
        <h3 class="block-header">Dashboard</h3>
        <!-- In-->
        <div class="dashboard-holder">
            <div class="block-holder-dashboard">
                <h3 class="block-header">Currently In</h3>
                <?php foreach ($staff as $person): ?>
                    <?php if ($person['timeIn'] && !$person['timeOut']) $pendingClockOut++; ?>
                    <?php if (!$person['timeOut'] && $person['timeIn']): ?>
                    <label class="in-out-person in-person">
                        <?= $person['staffName'] ?><br>
                        <span class="timein-dashboard">Clocked In: <?= date('H:i', strtotime($person['timeIn'])); ?></span>
                        <?php 
                            $in++; 
                            $timeIn = strtotime($person['timeIn']);
                            $cutoff = strtotime(date('Y-m-d', strtotime($person['timeIn'])) . ' 07:30:00');
                            $isLate = $timeIn > $cutoff;

                            if ($isLate) {
                                $lateCount++;
                                echo "<span class='late-pill'>⏰ Late</span>";
                            }
                        ?>
                    </label>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <!-- OUT -->
            <div class="block-holder-dashboard">
                <h3 class="block-header">Not In</h3>
                <?php foreach ($staff as $person): ?>
                    <?php if (!$person['timeOut'] && !$person['timeIn']): ?>
                        <label class="in-out-person out-person">
                            <?= $person['staffName'] ?><br>
                            <span class="timeout-dashboard">Not Clocked In</span>
                            <?php $out++; ?>
                        </label>
                    <?php elseif ($person['timeOut'] && $person['timeIn']): ?>
                        <label class="in-out-person in-person">
                            <?= $person['staffName'] ?><br>
                            <span class="timein-dashboard">Clocked Out: <?= date('H:i', strtotime($person['timeOut'])); ?></span>
                            <?php $out++; ?>
                        </label>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <span class="small-gray-text">
            Last updated: <?= date('H:i') ?> | <span class="small-gray-text" id="last-updated">Refreshing in 15s</span>
        </span>
    </div>

    <!-- Overview -->
    <div class="dashboard-holder">
        <div class="block-holder-today-overview">
            <h3 class="block-header">Today's Overview</h3>
            <div style="display: flex; flex-direction: row;">
                <div class="square-block">
                        <p class="small-text-square-block">Total Staff</p>
                        <p class="large-text-square-block"><?= sizeof($staff) ?></p>
                </div>
                <div class="square-block">
                        <p class="small-text-square-block">Clocked In</p>
                        <p class="large-text-square-block"><?= $in ?></p>
                </div>
                <div class="square-block">
                        <p class="small-text-square-block">Late</p>
                        <p class="large-text-square-block"><?= $lateCount ?></p>
                </div>
                <div class="square-block">
                        <p class="small-text-square-block">Not In</p>
                        <p class="large-text-square-block"><?= $out ?></p>
                </div>
                <div class="square-block">
                        <p class="small-text-square-block">Clock-out Pending</p>
                        <p class="large-text-square-block"><?= $pendingClockOut ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    let seconds = 15;
    const el = document.getElementById('last-updated');

    setInterval(() => {
        seconds--;
        if (el) el.textContent = `Refreshing in ${seconds}s`;
    }, 1000);

    let autoRefresh = true;

    document.addEventListener('focusin', () => autoRefresh = false);
    document.addEventListener('focusout', () => autoRefresh = true);

    setTimeout(() => {
        if (autoRefresh) location.reload();
    }, 15000);
</script>