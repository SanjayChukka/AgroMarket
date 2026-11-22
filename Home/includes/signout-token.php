<?php
function signoutToken(): string
{
    $_SESSION['signout_key'] ??= bin2hex(random_bytes(32));
    return hash_hmac('sha256', $_SESSION['user_id'] . ':' . $_SESSION['user_role'], $_SESSION['signout_key']);
}
