<?php

function validationStaffInputs($firstname, $surname, $role, $number, $email, &$errors) {
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

        if (getStaffMemberID($firstname . ' ' . $surname)) {
            $errors['name'] = "Staff member already exists";
        }
        
        if ($number !== '') {
            if (phoneNumberExists($number)) {
                $errors['number'] = 'Number already exists';
            } else if (strlen($number) < 10 || strlen($number) > 10) {
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