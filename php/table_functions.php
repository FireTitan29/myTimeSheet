<?php

    function get_months_array($format = 'F') {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            // mktime(hour, minute, second, month, day, year)
            $timestamp = mktime(0, 0, 0, $m, 1, date('Y'));
            $months[$m] = date($format, $timestamp);
        }
        return $months;
    }

    function createRow($day, $month, $year, $staffMemberID, $periodPublicHolidays) {
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        $date = date("d M", $timestamp);
        $weekDay = date("l", $timestamp);

        $todayDate = date("d m Y",time());
        $calendarDate = date("d m Y", $timestamp);
        $dbDate = date("Y-m-d", $timestamp);

        $staffRecord = getRecord($staffMemberID, $dbDate);
        $isLate = false;

        
        if (!empty($staffRecord['timeIn'])) {
            $timeIn = strtotime($staffRecord['timeIn']);

            $expectedArrivalTime = getExpectedArrivialTime($staffMemberID);
            $cutoff = strtotime(date('Y-m-d', strtotime($staffRecord['timeIn'])) . ' ' . $expectedArrivalTime);
            
            $isLate = $timeIn > $cutoff;
        }

        $isPublicHoliday = in_array(
            $dbDate,
            array_column($periodPublicHolidays, 'holiday_date'),
            true
        );

        $publicHolidayName = null;

        foreach ($periodPublicHolidays as $holiday) {
            if ($holiday['holiday_date'] === $dbDate) {
                $publicHolidayName = $holiday['name'];
                break;
            }
        }

        include 'components/dateRow.php';
    }

    


?>