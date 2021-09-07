<?php
    session_start();
    unset($_SESSION["classList"][$_GET["row"]]);
    header("Location: ../")
?>
