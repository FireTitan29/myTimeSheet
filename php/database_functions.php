<?php 
    function connectToDatabase() {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=HealthyPet', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            return $pdo;
        } catch (PDOException $error) {
            die("Connection to the DB failed, here's why: " . $error->getMessage());
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

        // Work out previous month + year
        $prevMonth = $month - 1;
        $prevYear  = $year;

        if ($prevMonth === 0) {
            $prevMonth = 12;
            $prevYear--;
        }

        // How many days in the previous month?
        $daysInPrev = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);

        // 1) From 26th of previous month → end of previous month
        for ($day = 26; $day <= $daysInPrev; $day++) {
            createRow($day, $prevMonth, $prevYear, $staffMemberID);
        }

        // 2) From 1st of current month → 25th of current month
        for ($day = 1; $day <= 25; $day++) {
            createRow($day, $month, $year, $staffMemberID);
        }
    }

    function getAllStaff() {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT * from staff ORDER BY staffName ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function getAllStaffNames() {
        $pdo = connectToDatabase();
        // Sort in SQL so the list is always ordered correctly
        $stmt = $pdo->prepare('SELECT TRIM(staffName) AS staffName FROM staff ORDER BY staffName ASC');
        $stmt->execute();
        $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
        closeDatabase($pdo);
        return $names;
    }
?>