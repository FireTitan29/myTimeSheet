<?php

    function createRow($day, $month, $year, $staffMemberID) {
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
            $cutoff = strtotime(date('Y-m-d', strtotime($staffRecord['timeIn'])) . ' 07:30:00');
            
            $isLate = $timeIn > $cutoff;
}
        include 'dateRow.php';
    }

    


?>