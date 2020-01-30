<?php
require_once('base.php');
// this is a bit sloppy, but it's only a demo...
session_name('dsreg');
session_start();

$formdata = [];

if (@key_exists('pending', $_SESSION)) {
    $_SESSION['messages'] = [
        'Welcome ' . $_SESSION['user']->email,
        'Please check your inbox for a verification email'
    ];
    $_SESSION['registered'] = true;
    return;
}

$_SESSION['messages'] = [ 'Just a short step away...', 'Please enter the following information' ];
$_error = false;

if (!key_exists('register', $_POST)) return;

function test_email() {
    if (!$_POST['email']) {
        $_SESSION['form_errors']['email'] = 'This is a required field';
    } else if (!preg_match('/\w[\w\.]*@\w[\w\.]+/', $_POST['email'])) {
        $_SESSION['form_errors']['email'] = 'invalid email address!';
    } else return is_email_registered();

    return false;
}

function is_email_registered () {
    $test = new User();
    $test->email = $_POST['email'];
    if (!$test->fetch()) return true;
    $_SESSION['form_errors']['email'] = 'Email address already registered!';
    return false;
}

function test_password () {
    if (!$_POST['pass1'] || !$_POST['pass2']) {
        $_SESSION['form_errors']['pass1'] = $_SESSION['form_errors']['pass2'] = 'This is a required field';
        $_POST['pass1'] = $_POST['pass2'] = '';
        return false;
    }
    if ((strlen($_POST['pass1']) < 8) || !preg_match('/[0-9]/', $_POST['pass1']) || !preg_match('/[a-zA-Z]/', $_POST['pass1'])) {
        $_SESSION['form_errors']['pass1'] = 'Password must be at least 8 characters long and must use numbers and letters!';
        $_POST['pass2'] = $_POST['pass1'] = '';
        return false;

    } else if ($_POST['pass1'] !== $_POST['pass2']) {
        $_SESSION['form_errors']['pass2'] = 'Passwords do not match!';
        $_POST['pass2'] = '';
        return false;

    }
    return true;
}

function test_birthday() {
    if (!$_POST['byear'] || !$_POST['bmonth'] || !$_POST['bday']) {
        $_SESSION['form_errors']['byear'] = $_SESSION['form_errors']['bmonth'] = $_SESSION['form_errors']['bday'] = 'This is a required field';
        return false;
    }
    return true;
}

if (test_email() && test_password() && test_birthday()) {
    $user = new User();
    $user->email = $_POST['email'];
    $user->password = $_POST['pass1'];
    $user->birthday = $_POST['byear'] . '-' . $_POST['bmonth'] . '-' . $_POST['bday'] . ' 00:00:00';
    $user->save();
    $_SESSION['user'] = $user;
    $_SESSION['registered'] = true;
    $_SESSION['messages'] = [
        'Welcome ' . $_SESSION['user']->email,
        'Please check your inbox for a verification email'
    ];
    $_SESSION['pending'] = true;
}

