<div class="popUpForm" id="addStaff-form">
    <div class="block-holder-popup" >
        <div>
            <?php
                // Only show validation errors for the form that was actually submitted
                $isAddSubmit = isset($_POST['addStaffMember']);
                $addRole = $_POST['role'] ?? '';
            ?>
            <h3 class="block-header">Add Staff Member</h3>
            <form method="POST">
                <small class="form-error-message"><?php if ($isAddSubmit && isset($errors['name'])) echo $errors['name']; ?></small>
                <input id="firstname-add" autocomplete="off" class="form-text-input-name" type="text" placeholder="First Name" name="firstname" value="<?= htmlspecialchars($_POST['firstname'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input autocomplete="off" class="form-text-input-name" type="text" placeholder="Surname" name="surname" value="<?= htmlspecialchars($_POST['surname'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><br>

                <small class="form-error-message"><?php if ($isAddSubmit && isset($errors['email'])) echo $errors['email']; ?></small>
                <input autocomplete="off" class="form-text-input-email" type="text" placeholder="Email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><br>
                <small class="form-error-message"><?php if ($isAddSubmit && isset($errors['number'])) echo $errors['number']; ?></small>
                <input autocomplete="off" class="form-text-input-email" type="text" placeholder="Phone Number" name="number" value="<?= htmlspecialchars($_POST['number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><br>
                <small class="form-error-message"><?php if ($isAddSubmit && isset($errors['role'])) echo $errors['role']; ?></small>
                <select class="optionBox" name="role">
                    <option value="" hidden   <?= $addRole === '' ? 'selected' : '' ?>>Role</option>
                    <option value="manager"   <?= $addRole === 'manager' ? 'selected' : '' ?>>Manager</option>
                    <option value="admin"     <?= $addRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="office"    <?= $addRole === 'office' ? 'selected' : '' ?>>Office</option>
                    <option value="salesrep"  <?= $addRole === 'salesrep' ? 'selected' : '' ?>>Sales Rep</option>
                    <option value="warehouse" <?= $addRole === 'warehouse' ? 'selected' : '' ?>>Warehouse</option>
                    <option value="staff"     <?= $addRole === 'staff' ? 'selected' : '' ?>>General Staff</option>
                </select>
                <input type="hidden" name="addStaffMember" value="true">
                <br>
                <button class="form-button" type="submit">Submit</button>
                <button class="form-button" type="button" onclick="closeForm()">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openForm() {
        document.getElementById("addStaff-form").style.display = "flex";
        
        document.getElementById("firstname-add")?.focus();
    }

    function closeForm() {
        document.getElementById("addStaff-form").style.display = "none";
    }

    document.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errors) && $isAddSubmit): ?>
            openForm();
        <?php endif; ?>
    });
</script>