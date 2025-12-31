<?php
    $isUpdateSubmit = isset($_POST['updateStaffMember']);

    $id = $selectedPerson['staffID'] ?? '';

    $names = preg_split('/\s+/', trim($selectedPerson['staffName'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    $defaultFirstname = $names[0] ?? '';
    $defaultSurname = $names[1] ?? '';

    $firstname = trim($_POST['firstname-update'] ?? $defaultFirstname);
    $surname   = trim($_POST['surname-update'] ?? $defaultSurname);
    $email     = trim($_POST['email-update'] ?? ($selectedPerson['email'] ?? ''));
    $phone     = trim($_POST['number-update'] ?? ($selectedPerson['phone'] ?? ''));
    $updateRole = $_POST['role-update'] ?? ($selectedPerson['role'] ?? '');
?>
<div class="popUpForm" id="updateStaff-form">
    <div class="block-holder-popup">
        <div>
            <h3 class="block-header">Update Staff Details</h3>
            <form method="POST">
                <small class="form-error-message"><?php if ($isUpdateSubmit && isset($errors['name'])) echo $errors['name']; ?></small>
                <input id="firstname-update" autocomplete="off" class="form-text-input-name" type="text" placeholder="First Name" name="firstname-update" value="<?= htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8') ?>">
                <input autocomplete="off" class="form-text-input-name" type="text" placeholder="Surname" name="surname-update" value="<?= htmlspecialchars($surname, ENT_QUOTES, 'UTF-8') ?>"><br>
                
                <small class="form-error-message"><?php if ($isUpdateSubmit && isset($errors['email'])) echo $errors['email']; ?></small>
                <input autocomplete="off" class="form-text-input-email" type="text" placeholder="Email" name="email-update" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"><br>
                
                <small class="form-error-message"><?php if ($isUpdateSubmit && isset($errors['number'])) echo $errors['number']; ?></small>
                <input autocomplete="off" class="form-text-input-email" type="text" placeholder="Phone Number" name="number-update" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"><br>
                <small class="form-error-message"><?php if ($isUpdateSubmit && isset($errors['role'])) echo $errors['role']; ?></small>
                <select class="optionBox" name="role-update">
                    <option value="" hidden   <?= $updateRole === '' ? 'selected' : '' ?>>Role</option>
                    <option value="manager"   <?= $updateRole === 'manager' ? 'selected' : '' ?>>Manager</option>
                    <option value="admin"     <?= $updateRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="office"    <?= $updateRole === 'office' ? 'selected' : '' ?>>Office</option>
                    <option value="salesrep"  <?= $updateRole === 'salesrep' ? 'selected' : '' ?>>Sales Rep</option>
                    <option value="warehouse" <?= $updateRole === 'warehouse' ? 'selected' : '' ?>>Warehouse</option>
                    <option value="staff"     <?= $updateRole === 'staff' ? 'selected' : '' ?>>General Staff</option>
                </select>
                <input type="hidden" name="updateStaffMember" value="true">
                <input type="hidden" name="staffID" value="<?= $id ?>">

                <br>
                <div class="button-div-popup">
                    <button class="form-button" type="submit">Submit</button>
                    <span style="margin-right: 5px;"></span>
                    <button class="form-button" type="button" onclick="closeUpdateForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openUpdateForm() {
        const el = document.getElementById('updateStaff-form');
        if (el) el.style.display = 'flex';
        document.getElementById('firstname-update')?.focus();
    }

    function closeUpdateForm() {
        const el = document.getElementById('updateStaff-form');
        if (el) el.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errors) && $isUpdateSubmit): ?>
            openUpdateForm();
        <?php endif; ?>
    });
</script>