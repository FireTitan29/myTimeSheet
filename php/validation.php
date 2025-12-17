<?php

function validationStaffInputs($firstname, $surname, $role, $number, $email, &$errors, $update = 0, $currentId = '') {
    if ($firstname === '' && $surname === '') {
            $errors['name'] = 'Name fields cannot be empty';
        } else if ($firstname === '') {
            $errors['name'] = 'Firstname cannot be empty';
        } else if ($surname === '') {
            $errors['name'] = 'Surname cannot be empty';
        } else if (!ctype_alpha($firstname . $surname)) {
            $errors['name'] = 'Names cannot include numbers or spaces';
        }

        if ($role === '') {
            $errors['role'] = 'Required';
        }

        if ($update === 0) {
            // ADD: block if name exists
            $fullName = trim($firstname . ' ' . $surname);
            if ($fullName !== '' && getStaffMemberID($fullName)) {
                $errors['name'] = 'Staff member already exists';
            }
        } else {
            // UPDATE: only block if name belongs to someone else
            $fullName = trim($firstname . ' ' . $surname);
            if ($fullName !== '') {
                $existingId = getStaffMemberID($fullName); // returns staffID or null/false
                if ($existingId && (string)$existingId !== (string)$currentId) {
                    $errors['name'] = 'Staff member already exists';
                }
            }
        }
        // Phone number is optional. Only validate uniqueness/length when provided.
        $number = trim((string)$number);
        if ($number !== '') {
            if (phoneNumberExists($number)) {
                $errors['number'] = 'Number already exists';
            } else if (strlen($number) !== 10) {
                $errors['number'] = 'Phone number must be 10 digits';
            }
        }

        if ($email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Invalid email address";
            }
        }
}


?>