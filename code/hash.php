<?php

$hash = password_hash('admin', PASSWORD_DEFAULT);

if (password_verify('1232', $hash)) {

}