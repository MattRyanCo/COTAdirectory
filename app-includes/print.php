<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/class-membership-directory-printer.php';

global $cota_db, $connect, $cota_constants;

$families = $connect->query("SELECT * FROM families ORDER BY `familyname`");
$num_families = $families->num_rows;

$printer = new Membership_Directory_Printer();
