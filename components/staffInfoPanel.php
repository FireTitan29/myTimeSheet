<?php $personStats = [];
        if (!empty($selectedPerson)) {
            $personStats = calculateStatistics($selectedPerson['staffID']);
            $_SESSION['name'] = $selectedPerson['staffName'];
        } 
?>
<div class="block-holder-staff">
    <div>
        <div>
            <h3 class="staff-info-name-header"><?php if (!empty($selectedPerson)) echo $selectedPerson['staffName']; ?></h3>
            <span class="small-gray-role-text"><?php if (!empty($selectedPerson)) echo ucwords(strtolower($selectedPerson['role'])); ?></span>

            <div class="staff-info-button-div">
                <a href="index.php?view=table&name=<?php if (!empty($selectedPerson)) echo $selectedPerson['staffName'];  ?>"><button class="staff-info-button">View timesheet</button></a>
                <button onclick="openUpdateForm()" class="staff-info-button" type="submit">Edit Info</button>

                <form method="POST" style="display:inline; margin: 0px;">
                    <input type="hidden" name="staffID" value="<?= $selectedPerson['staffID'] ?>">
                    <button name="deactivate" value="true" type="submit" class="staff-info-button-red">Deactivate</button>
                </form>
            </div>
            <div class="staff-info-contact-div">
                <p class="contact-p"><span class="highlight-span">Email:</span> <?php if (!empty($selectedPerson)) echo $selectedPerson['email']; ?></p>
                <p class="contact-p"><span class="highlight-span">Phone:</span> <?php if (!empty($selectedPerson)) echo $selectedPerson['phone']; ?></p>
                <p class="contact-p"><span class="highlight-span">PIN:</span> <?php if (!empty($selectedPerson)) echo $selectedPerson['pin']; ?></p>
            </div>
        </div>
        <div class="leave-comment-block">
            <h3 class="squares-header">This Month</h3>
            <div class="squares-holder">
                <div class="square-block">
                    <p class="small-text-square-block">Days Worked</p>
                    <p class="large-text-square-block"><?= $personStats['daysWorked'] ?></p>
                </div>
                
                <div class="square-block">
                    <p class="small-text-square-block">Days Missed</p>
                    <p class="large-text-square-block"><?= $personStats['daysMissed'] ?></p>
                </div>

                <div class="square-block">
                    <p class="small-text-square-block">Office Hours</p>
                    <p class="large-text-square-block"><?= $personStats['officeHours'] ?>h</p>
                </div>

                <div class="square-block">
                    <p class="small-text-square-block">Late Arrivals</p>
                    <p class="large-text-square-block"><?= $personStats['lateArrivals'] ?></p>
                </div>
                
            </div>
            <div style="margin-top: 20px;">
                <div class="info-panel-heading" style="font-size: var(--normalFont); color:var(--darkColor);">Punctuality</div>
                <div class="punctuality-badges">
                    <?php $expectedArrivialTime = date( 'H:i', strtotime(getExpectedArrivialTime($selectedPerson['staffID']))) ?>
                    <span class="badge badge-family">Expected <?= $expectedArrivialTime ?></span>
                    <span id="month-badge" class="badge badge-<?= strtotime($personStats['avgTimeIn']) > strtotime($expectedArrivialTime) ? "bad" : "good" ?>">This Month <?= $personStats['avgTimeIn']?></span>
                    <span id="typical-badge" class="badge badge-<?= strtotime($personStats['lifetimeAvgTimeIn']) > strtotime($expectedArrivialTime) ? "bad" : "good" ?>">Usual <?= $personStats['lifetimeAvgTimeIn']?></span>
                </div>
            </div>
        </div>
        <div class="leave-comment-block">
            <div style="margin-top: 10px;">
            </div>
            <h3 class="squares-header">Leave remaining</h3>
            <!-- Leave Section -->
             <?php
                $leaveDaysRemaining = leaveTaken($selectedPerson['staffID']);
             ?>
            <div class="squares-holder">

                <div class="square-block">
                    <p class="small-text-square-block">Annual Leave</p>
                    <p class="large-text-square-block"><?= 15-$leaveDaysRemaining['annual'] ?> <span class="small-text-square-block">/ 15</span></p>
                </div>
                
                <div class="square-block">
                    <p class="small-text-square-block">Sick Leave</p>
                    <p class="large-text-square-block"><?= 10-$leaveDaysRemaining['sick'] ?> <span class="small-text-square-block">/ 10</span></p>
                </div>
                
                <div class="square-block">
                    <p class="small-text-square-block">Family Leave</p>
                    <p class="large-text-square-block"><?= 3 - $leaveDaysRemaining['family'] ?> <span class="small-text-square-block">/ 3</span></p>
                </div>
                
            </div>
            <div style="margin-top: 20px;">
                <p class="contact-p"><span class="highlight-span">Unpaid leave taken: </span><span class="dark-color"><?= $leaveDaysRemaining['unpaid'] ?> days</span></p>
                <p class="contact-p"><span class="highlight-span">Annual Leave accrude YTD: </span><span class="dark-color"><?= calculateAnnualLeaveDays($selectedPerson['staffID']) ?> days</span></p>
            </div>
        </div>
    </div>
</div>

<?php include "components/updateStaffPopUp.php" ?>

<script>
    document.querySelectorAll('.badge').forEach(badge => {
        if (
            !badge.classList.contains('badge-good') &&
            !badge.classList.contains('badge-bad') &&
            !badge.classList.contains('badge-family')
        ) {
            badge.style.display = 'none';
        }
    });
</script>