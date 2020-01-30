<?php
require_once('base.php');

class User extends DBObj {

    protected static $db_preload_file = '../db/users.ser';
    protected static $db_table = 'users';
    protected static $db_primary_key = 'id';
    protected static $db_fields = [ 
        'email'    => 'varchar(320)',
        'password' => 'varchar(60)',
        'birthday' => 'datetime'
    ];

    protected function _set_password($value) {
        return password_hash($value, PASSWORD_BCRYPT);
    }

}

