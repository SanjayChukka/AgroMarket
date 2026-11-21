<?php

function saveRegistrationValues($sessionKey)
{
    $values = $_POST;
    unset($values['password'], $values['confirm_password'], $values['register']);
    $_SESSION[$sessionKey] = $values;
}
