
<?php

    $link = mysqli_connect("localhost", "root", "", "agromarket");

    if ($link === false) {
    die("ERROR: Could not connect! " . mysqli_connect_error());
    }

    mysqli_set_charset($link, "utf8mb4");

?>
