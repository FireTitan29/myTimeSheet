<?php
if (!defined('APP_RUNNING')) {
    header("Location: ../index.php");
    exit;
}
?>

<?php
    $staffMembers = getAllStaff();
    $staffCount = sizeof($staffMembers);
    $role = $_GET['role'] ?? '';
    $selectedStaffID = $_GET['staffMember'] ?? '';

    $selectedPerson = [];

    if ($selectedStaffID !== '') {
        $selectedPerson = getStaffMemberDetails($selectedStaffID);
        if (!$selectedPerson) {
            $selectedPerson = [];
            $selectedStaffID = '';
        } 
    }

?>

<div class="staffmanagment-holder">
    <div class="block-holder">
        <div>
            <div class="staffblock-top">
                <h3 class="block-header-staff">Staff</h3>
                <small class="small-gray-text">Showing <?= $staffCount ?> staff members</small>
            </div>

            <form method="GET">
                <input type="hidden" name="view" value="staffmanagement">
                <?php if (!empty($role)): ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>

                <?php foreach ($staffMembers as $person): ?>
                    <label for="<?= $person['staffID'] ?>">
                        <input onchange="this.form.submit()" class="staff-radio" type="radio" name="staffMember" id="<?= $person['staffID'] ?>" value="<?= $person['staffID']?>"
                        <?php if (!empty($selectedStaffID) && $selectedStaffID == $person['staffID']) echo 'checked'; ?>>
                        <span class="staff-radio-block"><?= $person['staffName'] ?></span>
                    </label>
                <?php endforeach; ?>
            </form>
        </div>

        <div class="staffblock-bottom">
            <button data-tip="Add Staff Member" id="addstaffbutton" class="form-button-circle" onclick="openForm()"><img class="button-icon" src="images/icons/add_staff_icon.svg"></button>
        </div>
    </div>

    <?php include "components/addStaffPopUp.php"; ?>

    <?php if (!empty($selectedStaffID)): ?>
        <?php include "components/staffInfoPanel.php"; ?>
    <?php endif; ?>
</div>
