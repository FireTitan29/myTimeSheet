<?php

function getCurrentPayPeriod(): array {
    $today = new DateTime('today');
    $day = (int)$today->format('d');

    $start = new DateTime('first day of this month');
    $end = clone $start;

    if ($day <= 25) {
        $start->modify('-1 month');
    } else {
        $end->modify('+1 month');
    }

    $start->setDate((int)$start->format('Y'), (int)$start->format('m'), 26);
    $end->setDate((int)$end->format('Y'), (int)$end->format('m'), 26);
    $end->setTime(0, 0, 0);

    return [
        'startDate' => $start->format('Y-m-d'),
        'endDate'   => $end->format('Y-m-d'),
    ];
}

function calculateAverageArrivalTime(int $totalSeconds, int $count): ?string {
    if ($count === 0) {
        return null;
    }
    return gmdate('H:i', (int)round($totalSeconds / $count));
}

function getTimesheetRowsForPeriod($staffID, $startDate, $endDate): array {
    $pdo = connectToDatabase();

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
    closeDatabase($pdo);

    return $rows;
}

function calculateWorkedStats(array $rows, DateTime $today, $expectedArrivalTime): array {
    $lateArrivalsCount = 0;
    $daysWorkedCount = 0;
    $officeSeconds = 0;
    $timeInTotalSeconds = 0;
    $timeInCount = 0;

    $todayStr = $today->format('Y-m-d');
    $workedWeekdayDates = [];

    foreach ($rows as $staffRecord) {
        $dateStr   = $staffRecord['date'] ?? null;
        $timeInStr = $staffRecord['timeIn'] ?? null;
        $timeOutStr = $staffRecord['timeOut'] ?? null;

        if (empty($dateStr) || $dateStr > $todayStr) {
            continue;
        }

        if (empty($timeInStr)) {
            continue;
        }

        $daysWorkedCount++;

        $dow = (int)date('N', strtotime($dateStr));
        if ($dow <= 5) {
            $workedWeekdayDates[$dateStr] = true;

            $timeInDT = new DateTime($timeInStr);
            $timeInTotalSeconds +=
                ((int)$timeInDT->format('H') * 3600) +
                ((int)$timeInDT->format('i') * 60) +
                (int)$timeInDT->format('s');
            $timeInCount++;
        }

        $timeIn = strtotime($timeInStr); 
        $cutoff = strtotime($dateStr . ' ' . $expectedArrivalTime);
        if ($timeIn > $cutoff) {
            $lateArrivalsCount++;
        }

        if (!empty($timeOutStr)) {
            $timeOut = strtotime($timeOutStr);
            if ($timeOut > $timeIn) {
                $officeSeconds += ($timeOut - $timeIn);
            }
        }
    }

    return [
        'daysWorked' => $daysWorkedCount,
        'lateArrivals' => $lateArrivalsCount,
        'officeSeconds' => $officeSeconds,
        'workedWeekdayDates' => $workedWeekdayDates,
        'timeInTotalSeconds' => $timeInTotalSeconds,
        'timeInCount' => $timeInCount,
    ];
}

function getLeaveDatesForPeriod(int $staffID, string $startDate, string $endDate): array {
    $pdo = connectToDatabase();

    $stmt = $pdo->prepare(
        'SELECT DISTINCT date
         FROM timesheet
         WHERE staffID = :staffID
           AND is_leave = 1
           AND date >= :startDate
           AND date < :endDate'
    );

    $stmt->execute([
        ':staffID'   => $staffID,
        ':startDate' => $startDate,
        ':endDate'   => $endDate,
    ]);

    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    closeDatabase($pdo);

    return $dates ?: [];
}

function calculateDaysMissed(
    string $startDate,
    string $endDate,
    string $todayStr,
    array $workedWeekdayDates,
    array $publicHolidays = [],
    array $leaveDates = []
): int {

    $holidayLookup = array_flip(
        array_column($publicHolidays, 'holiday_date')
    );
    $leaveLookup = array_flip($leaveDates);

    $payPeriodLastDay = date('Y-m-d', strtotime($endDate . ' -1 day'));
    $periodEndForCalc = min($todayStr, $payPeriodLastDay);

    $weekdayTotal = 0;
    $cursor = new DateTime($startDate);
    $endCursor = new DateTime($periodEndForCalc);

    while ($cursor <= $endCursor) {
        $dateStr = $cursor->format('Y-m-d');
        $dow = (int)$cursor->format('N');

        if ($dow <= 5) {

            if (isset($holidayLookup[$dateStr])) {
                $cursor->modify('+1 day');
                continue;
            }

            if (isset($leaveLookup[$dateStr])) {
                $cursor->modify('+1 day');
                continue;
            }

            $weekdayTotal++;
        }

        $cursor->modify('+1 day');
    }

    $daysMissed = $weekdayTotal - count($workedWeekdayDates);
    return max(0, $daysMissed);
}

function calculateAnnualLeaveDays(int $staffID): float {
    $pdo = connectToDatabase();

    $stmt = $pdo->prepare(
        'SELECT ROUND(COUNT(DISTINCT t.date) / 17, 2)
         FROM timesheet t
         WHERE t.staffID = :staffID
           AND t.timeIn IS NOT NULL
           AND t.is_leave = 0
           AND t.date >= MAKEDATE(YEAR(CURDATE()), 1)
           AND t.date <= CURDATE()'
    );

    $stmt->bindValue(':staffID', $staffID, PDO::PARAM_INT);
    $stmt->execute();
    $leaveAccrued = (float) $stmt->fetchColumn();

    $leaveAccrued = min($leaveAccrued, 15);

    $stmt = $pdo->prepare(
        'SELECT leave_balance
         FROM staff
         WHERE staffID = :staffID'
    );

    $stmt->bindValue(':staffID', $staffID, PDO::PARAM_INT);
    $stmt->execute();
    $leaveBalance = (float) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(duration_days), 0)
         FROM staff_leave
         WHERE staffID = :staffID
           AND leave_type = \'Annual\''
    );

    $stmt->bindValue(':staffID', $staffID, PDO::PARAM_INT);
    $stmt->execute();
    $leaveTaken = (float) $stmt->fetchColumn();

    closeDatabase($pdo);

    return round(($leaveBalance + $leaveAccrued) - $leaveTaken, 2);
}

function calculateStatistics(int $staffID): array {
    $personStats = [
        'officeHours' => 0,
        'lateArrivals' => 0,
        'daysWorked' => 0,
        'daysMissed' => 0,
        'avgTimeIn' => null,
        'lifetimeAvgTimeIn' => calculateLifetimeAvgTimeIn($staffID),
    ];

    $today = new DateTime('today');
    $todayStr = $today->format('Y-m-d');

    ['startDate' => $startDate, 'endDate' => $endDate] = getCurrentPayPeriod();

    $rows = getTimesheetRowsForPeriod($staffID, $startDate, $endDate);
    $workedStats = calculateWorkedStats($rows, $today, getExpectedArrivialTime($staffID));

    $personStats['daysWorked'] = $workedStats['daysWorked'];
    $personStats['lateArrivals'] = $workedStats['lateArrivals'];
    $personStats['officeHours'] = (int)round($workedStats['officeSeconds'] / 3600);

    $personStats['daysMissed'] = calculateDaysMissed(
        $startDate,
        $endDate,
        $todayStr,
        $workedStats['workedWeekdayDates'],
        getAllPublicHolidays(),
        getLeaveDatesForPeriod($staffID, $startDate, $endDate)
    );

    $personStats['avgTimeIn'] = calculateAverageArrivalTime(
        $workedStats['timeInTotalSeconds'],
        $workedStats['timeInCount']
    );

    return $personStats;
}

function leaveTaken($staffID) {

    $leaveArray = [
        'annual' => 0,
        'sick' => 0,
        'family' => 0,
        'unpaid' => 0,
    ];
    $period = getCurrentPayPeriod();

    $pdo = connectToDatabase();

    $stmt = $pdo->prepare(
    "SELECT 
        sl.leave_type,
        SUM(
            sl.duration_days *
            CASE 
                WHEN sl.day_type = 'half' THEN 0.5
                ELSE 1
            END
        ) AS days_taken
    FROM staff_leave sl
    WHERE sl.staffID = :staffID
    AND sl.created_at >= :startDate
    AND sl.created_at < :endDate
    GROUP BY sl.leave_type"
    );

    $stmt->execute([
        ':staffID'    => $staffID,
        ':startDate' => $period['startDate'],
        ':endDate'   => $period['endDate']
    ]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $leaveArray[strtolower($row['leave_type'])] = (float)$row['days_taken'];
    }

    closeDatabase($pdo);
    return $leaveArray;
}

