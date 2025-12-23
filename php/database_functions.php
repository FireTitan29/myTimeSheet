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

    function getStaffMemberDetails($staffID) {
        $pdo = connectToDatabase();
        $stmt = $pdo->prepare('SELECT * FROM staff WHERE staffID = :id LIMIT 1');

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

    function calculateStatistics($staffID) {
        $personStats = [
            'officeHours' => 0,
            'lateArrivals' => 0,
            'daysWorked' => 0,
            'daysMissed' => 0,
            'avgTimeIn' => 0,
            'leaveDays' => 0,
        ];

        $pdo = connectToDatabase();

        $today = new DateTime('today');
        $day = (int)$today->format('d');

        $start = new DateTime('first day of this month');
        $start->setTime(0, 0, 0);
        $end = clone $start;

        if ($day <= 25) {
            // Pay period: 26th of last month -> 26th of this month
            $start->modify('-1 month');
            // $end stays as this month
        } else {
            // Pay period: 26th of this month -> 26th of next month
            $end->modify('+1 month');
        }

        $start->setDate((int)$start->format('Y'), (int)$start->format('m'), 26);
        $end->setDate((int)$end->format('Y'), (int)$end->format('m'), 26);
        $end->setTime(0, 0, 0);

        $startDate = $start->format('Y-m-d');
        $endDate   = $end->format('Y-m-d');

        $stmt = $pdo->prepare(
            'SELECT *
            FROM timesheet
            WHERE staffID = :staffID
            AND date >= :startDate
            AND date < :endDate
            ORDER BY date ASC'
        );

        $stmt->execute([
            ':staffID'   => $staffID,
            ':startDate' => $startDate,
            ':endDate'   => $endDate,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lateArrivalsCount = 0;
        $daysWorkedCount = 0;
        $daysMissedCount = 0;
        $officeSeconds = 0;
        $timeInTotalSeconds = 0;
        $timeInCount = 0;

        // Track which dates were actually worked (weekday + timeIn present) up to today
        $todayStr = $today->format('Y-m-d');
        $workedWeekdayDates = [];

        foreach ($rows as $staffRecord) {
            $dateStr = $staffRecord['date'] ?? null;
            $timeInStr = $staffRecord['timeIn'] ?? null;
            $timeOutStr = $staffRecord['timeOut'] ?? null;

            if (empty($dateStr)) {
                continue;
            }

            // Ignore future dates when calculating "up to today" stats
            if ($dateStr > $todayStr) {
                continue;
            }

            // If the DB has placeholder rows, consider a day "worked" only when timeIn exists
            $workedToday = !empty($timeInStr);

            if ($workedToday) {
                $daysWorkedCount++;

                $dow = (int)date('N', strtotime($dateStr)); // 1=Mon ... 7=Sun
                if ($dow <= 5) {
                    $workedWeekdayDates[$dateStr] = true;

                    // Avg arrival time: accumulate seconds since midnight for weekday arrivals
                    $timeInDT = new DateTime($timeInStr);
                    
                    $timeInTotalSeconds += ((int)$timeInDT->format('H') * 3600)
                                         + ((int)$timeInDT->format('i') * 60)
                                         + (int)$timeInDT->format('s');
                    $timeInCount++;
                }

                // Late arrival = timeIn after 07:30 on that same day
                $timeIn = strtotime($timeInStr);
                $cutoff = strtotime(date('Y-m-d', $timeIn) . ' 07:30:00');
                if ($timeIn > $cutoff) {
                    $lateArrivalsCount++;
                }

                // Office hours (only if timeOut exists too)
                if (!empty($timeOutStr)) {
                    $timeOut = strtotime($timeOutStr);
                    if ($timeOut > $timeIn) {
                        $officeSeconds += ($timeOut - $timeIn);
                    }
                }
            }
        }

        // Days missed = all weekdays in the pay period up to today, minus weekdays actually worked
        $periodEndForCalc = $todayStr;

        // If today is beyond the pay-period end (rare, but safe), cap at last day in range
        $payPeriodLastDay = date('Y-m-d', strtotime($endDate . ' -1 day'));
        if ($periodEndForCalc > $payPeriodLastDay) {
            $periodEndForCalc = $payPeriodLastDay;
        }

        $weekdayTotal = 0;
        $cursor = new DateTime($startDate);
        $endCursor = new DateTime($periodEndForCalc);

        while ($cursor <= $endCursor) {
            $dow = (int)$cursor->format('N'); // 1=Mon ... 7=Sun
            if ($dow <= 5) {
                $weekdayTotal++;
            }
            $cursor->modify('+1 day');
        }

        $daysMissedCount = $weekdayTotal - count($workedWeekdayDates);
        if ($daysMissedCount < 0) {
            $daysMissedCount = 0;
        }

        // Average arrival time (weekdays only, up to today)
        if ($timeInCount > 0) {
            $avgSeconds = (int)round($timeInTotalSeconds / $timeInCount);
            $personStats['avgTimeIn'] = gmdate('H:i', $avgSeconds);
        } else {
            $personStats['avgTimeIn'] = null;
        }

        $personStats['lateArrivals'] = $lateArrivalsCount;
        $personStats['daysWorked'] = $daysWorkedCount;
        $personStats['daysMissed'] = $daysMissedCount;
        $personStats['officeHours'] = (int)round($officeSeconds / 3600);
        
        return $personStats;
    }

    function calculateLeaveDays($staffID) {
        $pdo = connectToDatabase();

        $stmt = $pdo->prepare(
            'SELECT ROUND(COUNT(DISTINCT date) / 17, 2) AS leaveAccrued
            FROM timesheet
            WHERE staffID = :staffID
            AND timeIn IS NOT NULL
            AND date >= MAKEDATE(YEAR(CURDATE()), 1)
            AND date <= CURDATE()'
        );

        $stmt->bindValue(':staffID', $staffID, PDO::PARAM_INT);
        $stmt->execute();

        $leaveAccrued = $stmt->fetchColumn();
        closeDatabase($pdo);
        
        $leaveAccrued = min($leaveAccrued, 15);
        return (float)$leaveAccrued;
    }

    function currentlyInAndOut() {
        $pdo = connectToDatabase();
        $today = date('Y-m-d');

        $stmt = $pdo->prepare(
            'SELECT s.staffID, s.staffName, t.timeIn, t.timeOut
            FROM staff s
            LEFT JOIN timesheet t
            ON t.staffID = s.staffID
            AND t.date = :today
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

?>