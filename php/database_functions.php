<?php 
    function connectToDatabase() {
        $database = require __DIR__ . '/../../config/database.php';
        $host = $database['host'];
        $db   = $database['dbname'];
        $user = $database['user'];
        $pass = $database['pass'];
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    function closeDatabase(&$pdo) {
        $pdo = null;
    }

    function getStaffMemberID($staffName) {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT staffID FROM staff WHERE staffName = :staffName LIMIT 1');

        $stmt->execute([':staffName' => $staffName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $row ? $row['staffID'] : null;
    }

    function getStaffMemberDetails($staffID) {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT * FROM staff WHERE staffID = :id AND active = 1 LIMIT 1');

        $stmt->execute([':id' => $staffID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $row ?: null;
    }


    function getRecord($staffMemberID, $date) {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT * FROM timesheet WHERE staffID = :staffMemberID AND date = :currentDate LIMIT 1');

        $stmt->execute([':staffMemberID' => $staffMemberID, ':currentDate' => $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $row ? $row : null;
    }

    function phoneNumberExists($number) {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT phone FROM staff WHERE phone = :num LIMIT 1');

        $stmt->execute([':num' => $number]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $row ? true : false;
    }

        
    function filltable($month, $year, $personName) {
        $staffMemberID = getStaffMemberID($personName);

        if (!is_numeric($month)) {
            $month = date('m', strtotime('1 ' . $month));
        }

        $month = (int)$month;
        $year  = (int)$year;

        // Work out previous month + year
        $prevMonth = $month - 1;
        $prevYear  = $year;

        if ($prevMonth === 0) {
            $prevMonth = 12;
            $prevYear--;
        }
        $periodPublicHolidays = getYearPublicHolidays($year);
        // How many days in the previous month?
        $daysInPrev = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);

        // 1) From 26th of previous month → end of previous month
        for ($day = 26; $day <= $daysInPrev; $day++) {
            createRow($day, $prevMonth, $prevYear, $staffMemberID, $periodPublicHolidays);
        }

        // 2) From 1st of current month → 25th of current month
        for ($day = 1; $day <= 25; $day++) {
            createRow($day, $month, $year, $staffMemberID, $periodPublicHolidays);
        }
    }

    function getAllStaff() {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT * from staff WHERE active = 1 ORDER BY staffName ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function getAllStaffNames() {
        $pdo = connectToDatabase();
        // Sort in SQL so the list is always ordered correctly
        $stmt = $pdo->prepare('SELECT TRIM(staffName) AS staffName FROM staff WHERE active = 1 ORDER BY staffName ASC');
        $stmt->execute();
        $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
        closeDatabase($pdo);
        return $names;
    }

    function calculateLifetimeAvgTimeIn(int $staffID): ?string {
        $pdo = connectToDatabase();

        $stmt = $pdo->prepare(
            'SELECT AVG(
                HOUR(timeIn) * 3600 +
                MINUTE(timeIn) * 60 +
                SECOND(timeIn)
            )
            FROM timesheet
            WHERE staffID = :staffID
            AND timeIn IS NOT NULL
            AND is_leave = 0
            AND DAYOFWEEK(date) BETWEEN 2 AND 6'
        );

        $stmt->bindValue(':staffID', $staffID, PDO::PARAM_INT);
        $stmt->execute();

        $avgSeconds = $stmt->fetchColumn();
        closeDatabase($pdo);

        if ($avgSeconds === null) {
            return null;
        }

        return gmdate('H:i', (int) round($avgSeconds));
    }


    function currentlyInAndOut() {
        $pdo = connectToDatabase();
        $today = date('Y-m-d');

        $stmt = $pdo->prepare(
            'SELECT 
                s.staffID,
                s.staffName,
                t.timeIn,
                t.timeOut,
                t.staff_comment_early,
                t.staff_comment_late,
                t.management_comment,
                t.is_leave,
                l.leave_type
            FROM staff s
            LEFT JOIN timesheet t
                ON t.staffID = s.staffID
                AND t.date = :today
            LEFT JOIN staff_leave l
                ON l.leave_id = t.leave_id
            WHERE s.active = 1
            ORDER BY s.staffName ASC'
        );

        $stmt->bindValue(':today', $today);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function checkStaffPin($pin) {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT staffID FROM staff WHERE pin =:pin LIMIT 1');
        $stmt->bindValue(':pin', $pin);

        $stmt->execute();
        
        $staffID = $stmt->fetchColumn();

        if (empty($staffID)) return null;
        
        return $staffID;
    }

    // Check whether an IP Address is allowed
    function isIpAllowed(string $ip): bool {
        $conn = connectToDatabase();
        $stmt = $conn->prepare(
            'SELECT ipID 
            FROM allowed_ip_addresses 
            WHERE ipAddress = :ip
            LIMIT 1'
        );
        $stmt->bindValue(':ip', $ip);
        $stmt->execute();

        return (bool)$stmt->fetchColumn();
    }

    function getYearPublicHolidays($date) {
        $year = date('Y', strtotime($date));

        $pdo = connectToDatabase();
        $stmt = $pdo->prepare(
            'SELECT holiday_date, name
             FROM public_holiday
             WHERE YEAR(holiday_date) = :year
             AND country_code = \'ZA\'
             ORDER BY holiday_date ASC'
        );

        $stmt->execute([':year' => $year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        closeDatabase($pdo);

        return $rows;
    }

    function getAllPublicHolidays() {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare(
            'SELECT holiday_date, name
             FROM public_holiday
             WHERE country_code = \'ZA\'
             ORDER BY holiday_date ASC'
        );

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        closeDatabase($pdo);
        return $rows;
    }


    function getExpectedArrivialTime($staffMemberID) {
            // Getting the expected arrivial time
            $pdo = connectToDatabase();
            $stmt = $pdo->prepare('SELECT expected_arrival_time from staff where staffID = :staffID LIMIT 1');
            $stmt->execute([':staffID' => $staffMemberID]);
            
            $expectedArrivalTime = $stmt->fetchColumn();
            return $expectedArrivalTime;
    }

?>