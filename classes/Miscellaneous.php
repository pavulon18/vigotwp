<?php

/*
 * The MIT License
 *
 * Copyright 2018 Jim Baize <pavulon@hotmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Description of Miscellaneous
 *
 * @author Jim Baize <pavulon@hotmail.com>
 */
class Miscellaneous extends Model
{

    public function dropdown($query)
    {
        // This method makes it possible to populate a dropdown box using data
        // from the database.

        $this->query($query);
        $rows = $this->resultSet();
        return $rows;
    }

    public static function notify_password($email, $link)
    {
        require_once('Mail.php');
        require_once('Mail/mime.php');
        $from = "GCEMS Website Admin <www-data@gcems.duckdns.org>";
        $mesg = "Someone has requested that your Gibson County Web Portal password be changed" . PHP_EOL
                . "Please click on the following link to change your password." . PHP_EOL
                . "<a href=" . $link . "></a>";
        $subj = "Gibson County Ambulance Service Login Information";
        $mime = new Mail_mime();
        $mime->setTXTBody($mesg);
        $body = $mime->get();

        $headers = array('From' => $from,
            'To' => $email,
            'Subject' => $subj);
        $headers = $mime->headers($headers);


        $smtp = Mail::factory('smtp', array('host' => SMTP_HOST,
                    'port' => SMTP_PORT,
                    'auth' => true,
                    'username' => SMTP_USER,
                    'password' => SMTP_PASS));

        $mail = $smtp->send($email, $headers, $body);

        if (PEAR::isError($mail))
        {
            Messages::setMsg($mail->getMessage(), 'error');
        } else
        {
            //echo("
            //  Message successfully sent!
            // ");
        }
        return;
    }

    public static function isPasswordExpired($empNumber)
    {
        /*
         * Set up the query to search for the employee number
         * if the flag in the DB is set to 1 then take the user to the password
         * change form
         */

        echo 'Set up the query to search for the employee number
         if the flag in the DB is set to 1 then take the user to the password
         change form';
    }

}
