
<?php

    /* Save Registration Form Values */
    function saveRegistrationValues($sessionKey)
    {
    $values = $_POST;

    /* Remove Passwords and Submit Button */
    unset(
        $values['password'],
        $values['confirm_password'],
        $values['register']
    );

    $_SESSION[$sessionKey] = $values;
}
