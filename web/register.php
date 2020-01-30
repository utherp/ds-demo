<?php
    require_once('../base.php');
    require_once('../registration.php');
    $_form_errors = &$_SESSION['form_errors'];
?>    
<html>
    <head>
        <title><?= isset($_SESSION['registered']) ? 'Thank you' : 'Register'?></title>
        <link rel='stylesheet' type='text/css' href='assets/css/style.css' />
        <style>
            div.content {
                position: relative;
                border: thin solid black;
                padding-left: 2%;
                width: 40vw;
                min-height: 80vh;
                top: 10vh;
                left: 30vw;
            }
            div.content form div input {
                width: 90%;
                margin-left: 5%;
                margin-right: 5%;
            }
            div.content div.birthday {
                width: 90%;
                margin-left: 5%;
                margin-right: 5%;
            }
            div.content select {
                float: left;
                padding: 2%;
            }
            span.error {
                color: red;
            }
            select.invalid, input.invalid{
                border: thin solid red;
                background-color: pink;
            }
            select + select {
                margin-left: 2%;
            }
            select.month {
                width: 45%;
            }
            select.day {
                width: 20%;
            }
            select.year {
                width: 31%;
            }
        </style>
    </head>
    <body>
        <div class="content">
            <h2><?=isset($_SESSION['registered']) ? "<strong>Thank you for registering</strong>" : "<strong>Don't have an account: </strong>Registration"?></h2>
<?php          foreach ($_SESSION['messages'] as $m) {
?>            <h3 class='error'><?=$m?></h3>
<?php          }

            if (!isset($_SESSION['registered'])) {
?>            <form method="post" class="form-horizontal">
                <div>
                    <div class="text-left mb3">Email <span class='error'><?=@$_form_errors['email']?></span></div>
                    <div><input name="email" id="email" value="<?=$_POST['email']?>" class="ds-input" placeholder="" required="" autocomplete="off"></div>
                </div>
                <div class="form-group mb5">
                    <div class="text-left mb3">Password <span class='error'><?=@$_form_errors['pass1']?></span></div>
                    <div><input type="password" name="pass1" value="<?=$_POST['pass1']?>" class="ds-input <?=isset($_form_errors['pass1']) ? 'invalid' : ''?>" placeholder="" required=""></div>
                </div>      
                <div class="form-group mb5">
                    <div class="text-left mb3">Repeat Password <span class='error'><?=@$_form_errors['pass2']?></span></div>
                    <div><input type="password" name="pass2" value="<?=$_POST['pass2']?>" class="ds-input <?=isset($_form_errors['pass2']) ? 'invalid' : ''?>" placeholder="" required=""></div>
                </div>
                <div class="form-group mb5">
                    <div class="text-left mb3 ">Birthday <span class='error'><?=@$_form_errors['byear']?></span></div>
                    <div class='birthday'>
                    <select name="bmonth" id="month" required_select="Month" value="<?=$_POST['bmonth']?>" class="month <?=isset($_form_errors['bmonth']) ? 'invalid' : ''?>" data-size="10">
                        <option value="" selected="">MM</option>
<?php   $months = [ 1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December' ];
                        foreach ($months as $i => $m) {
?>                      <option value="<?=$i?>" <?=($_POST['bmonth'] == $i) ? 'selected=""' : ''?>><?=$m?></option>
<?php               }
?>                  </select>
                    <select style="" name="bday" id="day" required_select="Day" value="<?=$_POST['bday']?>" class="day <?=isset($_form_errors['bday']) ? 'invalid' : ''?>" data-size="10">
                        <option value="" selected="">DD</option>
<?php                   for ($i = 1; $i < 32; $i++) {
?>                      <option <?=($_POST['bday'] == $i) ? 'selected=""' : ''?>><?=$i?></option>
<?php                   }
?>                  </select>
                    <select style="" name="byear" id="year" required_select="Year" value="<?=$_POST['byear']?>" class="year <?=isset($_form_errors['byear']) ? 'invalid' : ''?>" data-size="10">
                        <option value="" selected="">YYYY</option>
<?php                   for ($i = 1945; $i < 2010; $i++) {
?>                      <option <?=($_POST['byear'] == $i) ? 'selected=""' : ''?>><?=$i?></option>
<?php                   }
?>                  </select>
                    </div>
                </div>
                <br />
                <input type='hidden' name='register' value='1' />
                <div style='margin-top: 10%;'>
                    <button type="submit" style='font-size: 14pt' class="btn btn-primary btn-lg btn-block">Click to Continue</button>
                </div>
            </form>
<?php       }
?>
        </div>
    </body>
</html>

