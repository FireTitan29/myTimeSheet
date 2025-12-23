<?php
    // Clocking IN to the system from Admin Panel
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['timeIn'])) {
        $personName = $_SESSION['name'];
        $staffMemberID = getStaffMemberID($personName);
        $date = date('Y-m-d');

        $pdo = connectToDatabase();
            $stmt = $pdo->prepare("
            SELECT * 
            FROM timesheet 
            WHERE staffID = :staffID 
            AND date = :date
            LIMIT 1
        ");

        $stmt->bindValue(':staffID', $staffMemberID, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if (!$result) {
            $stmt = $pdo->prepare('INSERT INTO timesheet (staffID, date, timeIn) 
                                    VALUES (:id, :date, NOW())');
            $stmt->bindValue(':id', $staffMemberID);
            $stmt->bindValue(':date', $date);
    
            $stmt->execute();
            header('Location: index.php?view=table&success=1');
        } else if ($result && !$result['timeIn']) { 
             $stmt = $pdo->prepare(' UPDATE timesheet
            SET timeIn = NOW()
            WHERE staffID = :id
            AND date = :date
            AND timeIn IS NULL
        ');
            $stmt->bindValue(':id', $staffMemberID);
            $stmt->bindValue(':date', $date);
                        $stmt->execute();
            header('Location: index.php?view=table&success=2');
        } else {
            header('Location: index.php?view=table&success=0');
        }
        closeDatabase($pdo);
        exit;
    }


    // Clocking OUT of the system from Admin Panel
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['timeOut'])) {
        $personName    = $_SESSION['name'];
        $staffMemberID = getStaffMemberID($personName);
        $date          = date('Y-m-d');

        $pdo = connectToDatabase();

        // Check today's record
        $stmt = $pdo->prepare("
            SELECT timeIn, timeOut
            FROM timesheet
            WHERE staffID = :staffID
            AND date = :date
            LIMIT 1
        ");
        $stmt->bindValue(':staffID', $staffMemberID, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            // No record for today = never clocked in
            header('Location: index.php?view=table&success=no_in');
            exit;
        }

        if (!empty($result['timeOut'])) {
            // Already clocked out
            header('Location: index.php?view=table&success=already_out');
            exit;
        }

        // Clock out now, but only if timeOut is still NULL
        $stmt = $pdo->prepare("
            UPDATE timesheet
            SET timeOut = NOW()
            WHERE staffID = :id
            AND date = :date
            AND timeOut IS NULL
        ");
        $stmt->bindValue(':id', $staffMemberID, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        header('Location: index.php?view=table&success=1');
        closeDatabase($pdo);
        exit;
    }

    
    // Submitting the Management Comment Form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['CommentPopUpFormSubmit'])) {
        $recordID = $_POST['commentID'];
        $staffID = getStaffMemberID($_SESSION['name']);
        $commentText = $_POST['commentText'];
        if ($commentText === '') {
            $commentText = null;
        }
        $calendarDate = $_POST['calendarDate'];

        $pdo = connectToDatabase();
        $existing = getRecord($staffID, $calendarDate);
        if ($existing && empty($existing['timeIn']) && $commentText === null) {

            // 1) Row exists, no timeIn, comment now cleared -> DELETE row
            $stmt = $pdo->prepare('DELETE FROM timesheet WHERE recordID = :id');
            $stmt->bindValue(':id', $recordID, PDO::PARAM_INT);

        } else if ($existing) {

            // 2) Row exists (with or without timeIn) -> UPDATE comment
            $stmt = $pdo->prepare(' UPDATE timesheet 
                                    SET management_comment = :comment 
                                    WHERE recordID = :id');
            $stmt->bindValue(':id', $recordID);
            $stmt->bindValue(':comment', $commentText);

        } else {

            if ($commentText === null) {
                // No comment to save and no record -> nothing to do
                header('Location: index.php');
                exit;
            }

            // 3) No row exists for that date -> INSERT new row with comment
            $stmt = $pdo->prepare(' INSERT INTO timesheet (staffID, date, management_comment)
                                    VALUES (:staffID, :date, :comment)');
            $stmt->bindValue(':staffID', $staffID);
            $stmt->bindValue(':date', $calendarDate);
            $stmt->bindValue(':comment', $commentText);
        }
        $stmt->execute();
        header('Location: index.php?view=table');
        exit;
    } 

    // Staff Member Clock in from PIN Page
    function clockInStaff($staffMemberID) {
        $date = date('Y-m-d');

        $pdo = connectToDatabase();
            $stmt = $pdo->prepare("
            SELECT * 
            FROM timesheet 
            WHERE staffID = :staffID 
            AND date = :date
            LIMIT 1
        ");

        $stmt->bindValue(':staffID', $staffMemberID, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            $stmt = $pdo->prepare('INSERT INTO timesheet (staffID, date, timeIn) 
                                    VALUES (:id, :date, NOW())');
            $stmt->bindValue(':id', $staffMemberID);
            $stmt->bindValue(':date', $date);
    
            $stmt->execute();
            header('Location: index.php?success=1');
        } else if ($result && !$result['timeIn']) { 
             $stmt = $pdo->prepare(' UPDATE timesheet
            SET timeIn = NOW()
            WHERE staffID = :id
            AND date = :date
            AND timeIn IS NULL
        ');
            $stmt->bindValue(':id', $staffMemberID);
            $stmt->bindValue(':date', $date);
                        $stmt->execute();
            header('Location: index.php?success=2');
        } else {

            header('Location: index.php?success=0');
        }
        closeDatabase($pdo);
        exit;
    }

    // Staff Member Clock out from PIN Page
    function clockOutStaff($staffMemberID) {
        $date = date('Y-m-d');

        $pdo = connectToDatabase();

        // Check today's record
        $stmt = $pdo->prepare("
            SELECT timeIn, timeOut
            FROM timesheet
            WHERE staffID = :staffID
            AND date = :date
            LIMIT 1
        ");
        $stmt->bindValue(':staffID', $staffMemberID, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            // No record for today = never clocked in
            header('Location: index.php?success=no_in');
            exit;
        }

        if (!empty($result['timeOut'])) {
            // Already clocked out
            header('Location: index.php?success=already_out');
            exit;
        }

        // Clock out now, but only if timeOut is still NULL
        $stmt = $pdo->prepare("
            UPDATE timesheet
            SET timeOut = NOW()
            WHERE staffID = :id
            AND date = :date
            AND timeOut IS NULL
        ");
        $stmt->bindValue(':id', $staffMemberID, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        header('Location: index.php?success=1');
        closeDatabase($pdo);
        exit;
    }

    // Clock in/out Process from STAFF PIN Page
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clockInClockOut'])) {
        $pin = trim($_POST['pin'] ?? '');

        $nowTs    = time();
        $cutoffTs = strtotime('today 07:30');
        $finishTs = strtotime('today 17:00');

        if ($pin === '') {
            $errors['pin'] = 'Cannot be Empty';
        } else {
            $staffID = checkStaffPin($pin);

            if (!$staffID) {
                $errors['pin'] = 'Invalid PIN';
            } else {
                $record = getRecord($staffID, date('Y-m-d'));

                // Determine today's state
                $hasIn  = ($record && !empty($record['timeIn']));
                $hasOut = ($record && !empty($record['timeOut']));

                // If both timeIn and timeOut exist, they've already completed the day
                if ($hasIn && $hasOut) {
                    $errors['pin'] = 'You have already completed your day';

                // Not clocked in yet
                } elseif (!$hasIn) {

                    // Prevent clock-in after 17:00
                    if ($nowTs > $finishTs) {
                        $errors['pin'] = "You can't clock in after 17:00";

                    // Early clock-in (before 07:30)
                    } elseif ($nowTs < $cutoffTs) {
                        clockInStaff($staffID);

                    // Late clock-in (07:30 to 17:00)
                    } else {
                        $errors['late'] = getStaffMemberDetails($staffID)['staffName'];
                        $errors['offence'] = "You're clocking in <i><strong>after</strong></i> your scheduled start time (07:30). Please add a short note for your manager.";
                    }

                // Clocked in but not clocked out yet
                } else {

                    // After 17:00 = allow clock out
                    if ($nowTs > $finishTs) {
                        clockOutStaff($staffID);

                    // Clocking out before scheduled end time (before 17:30)
                    } else {
                        $errors['late'] = getStaffMemberDetails($staffID)['staffName'];
                        $errors['offence'] = "You're clocking out <i><strong>before</strong></i> your scheduled end time (17:30). Please add a short note for your manager.";
                    }
                }
            }
        }
    }

    function generatePin(): string {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    function isDuplicateKey(PDOException $e): bool {
        // MySQL duplicate key is 23000
        return $e->getCode() === '23000';
    }

    // Adding Staff Member
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["addStaffMember"])) {
        $firstname = trim($_POST['firstname'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $number = trim($_POST['number'] ?? '');
        $phoneForDb = ($number === '') ? null : $number;
        $role = $_POST['role'] ?? '';

        validationStaffInputs($firstname, $surname, $role, $number, $email, $errors);

        if (empty($errors)) {
            $pdo = connectToDatabase();
            $name = $firstname . ' ' . $surname;

            $sql = 'INSERT INTO staff (staffName, email, role, phone, pin)
                    VALUES (:name, :email, :role, :phone, :pin)';

            $maxTries = 50;

            for ($i = 0; $i < $maxTries; $i++) {
                $pin = generatePin();

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':name', $name);
                    $stmt->bindValue(':email', $email);
                    $stmt->bindValue(':role', $role);
                    $stmt->bindValue(':pin', $pin);

                    if ($phoneForDb === null) {
                        $stmt->bindValue(':phone', null, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue(':phone', $phoneForDb);
                    }

                    $stmt->execute();

                    header('Location: index.php?view=staffmanagement&success=1');
                    exit;

                } catch (PDOException $e) {
                    if (isDuplicateKey($e)) {
                        // PIN collision - try again
                        continue;
                    }
                    throw $e; // real error
                }
            }

            // If we somehow couldn't find a unique pin
            $errors['pin'] = 'Could not generate a unique PIN. Please try again.';
        }
    }

    // Updating Staff Member Details
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateStaffMember'])) {
        $firstname = trim($_POST['firstname-update'] ?? '');
        $surname = trim($_POST['surname-update'] ?? '');
        $email = trim($_POST['email-update'] ?? '');
        $number = trim($_POST['number-update'] ?? '');
        $phoneForDb = ($number === '') ? null : $number;
        $role = $_POST['role-update'] ?? '';
        $id = $_POST['staffID'] ?? '';

        $person = getStaffMemberDetails($id);

        validationStaffInputs($firstname, $surname, $role, '', $email, $errors, 1, $id);

        // Phone is optional. If provided, enforce uniqueness and 10 digits.
        if ($number !== '') {
            if (phoneNumberExists($number) && $number !== ($person['phone'] ?? '')) {
                $errors['number'] = 'Number already exists';
            } else if (strlen($number) !== 10) {
                $errors['number'] = 'Phone number must be 10 digits';
            }
        }

        if (empty($errors)) {
            $pdo = connectToDatabase();
            $name = $firstname . ' ' . $surname;

            $stmt = $pdo->prepare(
                'UPDATE staff SET
                    staffName = :name,
                    email     = :email,
                    role      = :role,
                    phone     = :phone
                WHERE staffID = :id'
            );

            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':role', $role);
            if ($phoneForDb === null) {
                $stmt->bindValue(':phone', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':phone', $phoneForDb);
            }
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            header("Location: index.php?view=staffmanagement&staffMember=$id");
        }
    }
    
    // Deactivate Staff Member
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate'])) {
        $staffID = $_POST['staffID'];

        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('UPDATE staff SET active = 0 WHERE staffID = :staffID');
        $stmt->bindValue(':staffID', $staffID);

        $stmt->execute();
        header('Location: index.php?view=staffmanagement');
    }
?>