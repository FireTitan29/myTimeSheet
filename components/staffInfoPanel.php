<?php $personStats = [];
        if (!empty($selectedPerson)) {
            $personStats = calculateStatistics($selectedPerson['staffID']);
            $_SESSION['name'] = $selectedPerson['staffName'];
        } 
?>
<div class="block-holder-staff">
    <div>
        <h3 class="staff-info-name-header"><?php if (!empty($selectedPerson)) echo $selectedPerson['staffName']; ?></h3>
        <span class="small-gray-role-text"><?php if (!empty($selectedPerson)) echo ucwords(strtolower($selectedPerson['role'])); ?></span>

        <div class="staff-info-button-div">
            <a href="index.php?view=table&name=<?php if (!empty($selectedPerson)) echo $selectedPerson['staffName'];  ?>"><button class="staff-info-button">View timesheet</button></a>
            <button class="staff-info-button">Edit Info</button>
            <button class="staff-info-button-red">Deactivate</button>
        </div>
        <div class="staff-info-contact-div">
            <p class="contact-p"><span class="highlight-span">Email:</span> <?php if (!empty($selectedPerson)) echo $selectedPerson['email']; ?></p>
            <p class="contact-p"><span class="highlight-span">Phone:</span> <?php if (!empty($selectedPerson)) echo $selectedPerson['phone']; ?></p>
        </div>
        <h3 class="squares-header">This Month</h3>
        <div class="squares-holder">
            <div class="square-block">
                <p class="small-text-square-block">Office Hours</p>
                <p class="large-text-square-block"><?= $personStats['officeHours'] ?></p>
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
    </div>
</div>