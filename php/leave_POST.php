<?php 

    function getLeaveRecord($staffMemberID, $date) {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT sl.*
            FROM timesheet t
            JOIN staff_leave sl ON sl.leave_id = t.leave_id
            WHERE t.staffID = :staffMemberID
            AND t.date = :currentDate
            AND t.is_leave = 1
            LIMIT 1;');

        $stmt->execute([':staffMemberID' => $staffMemberID, ':currentDate' => $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $row ? $row : null;
    }

    function getLeaveStartDate($staffMemberID, $leaveID) {
        $pdo = connectToDatabase();

        $stmt = $pdo->prepare(
            "SELECT date
            FROM timesheet
            WHERE staffID = :staff_id
            AND leave_id = :leave_id
            ORDER BY date ASC
            LIMIT 1"
        );

        $stmt->execute([
            ':staff_id' => $staffMemberID,
            ':leave_id' => $leaveID
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $row ? $row['date'] : null;
    }

    function getLeaveEndDate($staffMemberID, $leaveID) {
        $pdo = connectToDatabase();

        $stmt = $pdo->prepare(
            "SELECT date
            FROM timesheet
            WHERE staffID = :staff_id
            AND leave_id = :leave_id
            ORDER BY date DESC
            LIMIT 1"
        );

        $stmt->execute([
            ':staff_id' => $staffMemberID,
            ':leave_id' => $leaveID
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $row ? $row['date'] : null;
    }

    function getLeaveDetails($leaveID) {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT * FROM staff_leave WHERE leave_id = :leaveID');
        $stmt->execute([':leaveID' => $leaveID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $row ? $row : null;
    }

    // Sending leave to the DB
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leaveRequest'])) {
        $staffID = $_POST['staffID'] ?? '';
        $startDate = $_POST['calendarDate'] ?? '';
        $leaveType = $_POST['leaveType'] ?? '';
        $dayType = $_POST['dayType'] ?? 'full';

        $doctorsNoteReceived = isset($_POST['doctorsNote'])
        ? (int) $_POST['doctorsNote']
        : null;

        // Controls the loop for creating records
        $numberOfDays = $_POST['numberOfDaysLeave'] ?? '';

        $comment = $_POST['commentText'] ?? '';

        if ($dayType === 'half') {
            $numberOfDays = 1;
        }

        if ($leaveType === '') {
            $errors['leave'] = 'Leave Type must be selected';
        } else if (!is_numeric($numberOfDays) || $numberOfDays <= 0) {
            $errors['leave'] = 'Number of Days must be greater than 0';
        }
        
        $doctorsNoteRequired = null;
        $weekDay = date('N', strtotime($startDate));
        
        if ($leaveType === 'sick' && ($weekDay === 1 || $weekDay === 5) && $doctorsNoteReceived === 0) {
            $errors['leave'] = 'Doctors note is required';
        } else if ($leaveType === 'sick' && ($weekDay === 1 || $weekDay === 5) && $doctorsNoteReceived === 1) {
            $doctorsNoteRequired = 1;
        }

        // Looking for days already taken by other leave
        $requestedDates = [];
        $currentDate = new DateTime($startDate);
        $daysAdded = 0;
        
        while ($daysAdded < $numberOfDays) {
            $dayOfWeek = (int) $currentDate->format('N');

            if ($dayOfWeek <= 5) { // Mon–Fri only
                $requestedDates[] = $currentDate->format('Y-m-d');
                $daysAdded++;
            }

            $currentDate->modify('+1 day');
        }

        $pdo = connectToDatabase();

        $placeholders = implode(',', array_fill(0, count($requestedDates), '?'));

        $sql = "
            SELECT t.date
            FROM timesheet t
            WHERE t.staffID = ?
            AND t.is_leave = 1
            AND t.date IN ($placeholders)
        ";

        $params = array_merge([$staffID], $requestedDates);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $overlappingDates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        closeDatabase($pdo);

        if (!empty($overlappingDates)) {
            $errors['leave'] =
                'Leave already exists on the following days: ' .
                implode(', ', $overlappingDates);
        }

        if (empty($errors)) {
            $pdo = connectToDatabase();
            $pdo->beginTransaction();

            try {
                $existingRecord = getLeaveRecord($staffID, $startDate);

                if ($existingRecord) {
                    // UPDATE existing leave metadata
                    $stmt = $pdo->prepare(
                        "UPDATE staff_leave
                        SET leave_type = :leaveType,
                            day_type = :dayType,
                            doctors_note_required = :doctorsNoteRequired,
                            doctors_note_received = :doctorsNoteReceived,
                            duration_days = :duration_days
                            leave_comment = :comment
                        WHERE leave_id = :leaveID"
                    );

                    $stmt->execute([
                        ':leaveType' => $leaveType,
                        ':dayType' => $dayType,
                        ':doctorsNoteRequired' => (int)$doctorsNoteRequired,
                        ':doctorsNoteReceived' => (int)$doctorsNoteReceived,
                        ':comment' => $comment,
                        ':duration_days' => $numberOfDays,
                        ':leaveID' => $existingRecord['leave_id']
                    ]);

                    $leaveID = $existingRecord['leave_id'];
                } else {
                    // INSERT new leave metadata
                    $stmt = $pdo->prepare(
                        "INSERT INTO staff_leave
                            (staffID, leave_type, day_type, doctors_note_required, doctors_note_received, leave_comment, duration_days)
                        VALUES
                            (:staffID, :leaveType, :dayType, :doctorsNoteRequired, :doctorsNoteReceived, :comment, :duration_days)"
                    );

                    $stmt->execute([
                        ':staffID' => $staffID,
                        ':leaveType' => $leaveType,
                        ':dayType' => $dayType,
                        ':doctorsNoteRequired' => (int)$doctorsNoteRequired,
                        ':doctorsNoteReceived' => (int)$doctorsNoteReceived,
                        ':comment' => $comment,
                        ':duration_days' => $numberOfDays,
                    ]);

                    $leaveID = $pdo->lastInsertId();
                }

                $currentDate = new DateTime($startDate);
                $recordsCreated = 0;

                while ($recordsCreated < $numberOfDays) {

                    // 1 = Monday, 7 = Sunday
                    $dayOfWeek = (int) $currentDate->format('N');

                    if ($dayOfWeek <= 5) {
                        // Check if a timesheet record already exists for this date
                        $checkStmt = $pdo->prepare(
                            "SELECT recordID
                             FROM timesheet
                             WHERE staffID = :staffID
                               AND date = :leaveDate
                             LIMIT 1"
                        );

                        $checkStmt->execute([
                            ':staffID'   => $staffID,
                            ':leaveDate'=> $currentDate->format('Y-m-d')
                        ]);

                        $existingTimesheet = $checkStmt->fetch(PDO::FETCH_ASSOC);

                        if ($existingTimesheet) {
                            // Update existing timesheet row
                            $stmt = $pdo->prepare(
                                "UPDATE timesheet
                                 SET is_leave = 1,
                                     leave_id = :leaveID
                                 WHERE recordID = :recordID"
                            );

                            $stmt->execute([
                                ':leaveID'  => $leaveID,
                                ':recordID'=> $existingTimesheet['recordID']
                            ]);
                        } else {
                            // Insert new timesheet row
                            $stmt = $pdo->prepare(
                                "INSERT INTO timesheet
                                    (staffID, date, is_leave, leave_id)
                                 VALUES
                                    (:staffID, :leaveDate, 1, :leaveID)"
                            );

                            $stmt->execute([
                                ':staffID'   => $staffID,
                                ':leaveDate'=> $currentDate->format('Y-m-d'),
                                ':leaveID'  => $leaveID
                            ]);
                        }

                        $recordsCreated++;
                    }

                    // Move to next calendar day
                    $currentDate->modify('+1 day');
                }

                $pdo->commit();
                // Redirect to dashboard
                header('Location: index.php?view=table');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    }

    // Delete Leave
    
?>