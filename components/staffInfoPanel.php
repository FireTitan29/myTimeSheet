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
            </div>
        </div>

        <h3 class="squares-header">This Month</h3>
        <div class="squares-holder">
            <div class="square-block">
                <p class="small-text-square-block">Office Hours</p>
                <p class="large-text-square-block"><?= $personStats['officeHours'] ?>h</p>
            </div>
            <div class="square-block">
                <p class="small-text-square-block">Late Arrivals</p>
                <p class="large-text-square-block"><?= $personStats['lateArrivals'] ?></p>
            </div>
            <div class="square-block">
                <p class="small-text-square-block">Days Worked</p>
                <p class="large-text-square-block"><?= $personStats['daysWorked'] ?></p>
            </div>
            <div class="square-block">
                <p class="small-text-square-block">Days Missed</p>
                <p class="large-text-square-block"><?= $personStats['daysMissed'] ?></p>
            </div>
        </div>
        <br>
        <p class="contact-p"><span class="highlight-span">Average Arival Time:</span><span class="dark-color"> <?= $personStats['avgTimeIn'] ?></span></p>
        <p class="contact-p"><span class="highlight-span">Annual Leave accrued (YTD):</span><span class="dark-color"> <?= calculateAnnualLeaveDays($selectedPerson['staffID']) ?></span></p>
        <p>Leave Section coming soon</p>
    </div>
</div>

<?php include "components/updateStaffPopUp.php" ?>