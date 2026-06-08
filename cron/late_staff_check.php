<?php

require_once __DIR__ . '/../php/database_functions.php';

$lateStaff = getLateStaff();

if (empty($lateStaff)) {
    exit;
}

$body = '
<html>
<head>
<style>
    body {
        font-family: Arial, sans-serif;
        color: #333;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 15px;
    }

    th {
        background-color: #dc3545;
        color: #ffffff;
        text-align: left;
        padding: 10px;
    }

    td {
        border: 1px solid #dddddd;
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: #f8f9fa;
    }
</style>
</head>
<body>
    <h2>Late Staff Notification</h2>
    <p>The following staff members have not clocked in today.</p>

    <table>
        <tr>
            <th>Staff Member</th>
            <th>Expected Arrival</th>
        </tr>';

foreach ($lateStaff as $staff) {
    $body .= '
        <tr>
            <td>' . htmlspecialchars($staff['staffName']) . '</td>
            <td>' . htmlspecialchars($staff['expected_arrival_time']) . '</td>
        </tr>';
}

$body .= '
    </table>

    <p style="margin-top:20px; font-size:12px; color:#666;">
        Generated automatically by Staff Hub on ' . date('Y-m-d H:i:s') . '.
    </p>
</body>
</html>';

$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: notifications@mystaffhub.co.za',
    'Reply-To: notifications@mystaffhub.co.za'
];

mail(
    'notifications@mystaffhub.co.za',
    'Late Staff Notification - ' . date('Y-m-d'),
    $body,
    implode("\r\n", $headers)
);