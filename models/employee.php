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

class EmployeeModel extends Model
{

    public function Index()
    {
        Miscellaneous::checkIsLoggedIn();
        return;
    }

    public function login()
    {
        // Sanitize POST
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        if ($post['submit'])
        {
            // Compare Login
            $this->query('SELECT * FROM personnel WHERE Username = :username ORDER BY Inserted_Timestamp DESC LIMIT 1');
            $this->bind(':username', $post['username']);
            $row = $this->single();

            if (empty($row))
            {
                Messages::setMsg('Incorrect Login', 'error');
                return;
            }
            $this->query('SELECT * FROM personnel_securityroles WHERE personnel_securityroles.Personnel_ID = ' . $row['Personnel_ID'] . ' ORDER BY Inserted_Timestamp DESC LIMIT 1');
            $row2 = $this->single();

            if (password_verify($post['password'], $row['Password']))
            {
                $_SESSION['is_logged_in'] = true;
                $_SESSION['user_data'] = array(
                    "personnelID" => $row['Personnel_ID'],
                    "firstName" => $row['FirstName'],
                    "lastName" => $row['LastName'],
                    "securityRole" => $row2['Security_Role_ID']
                );
                //Checking if the password has been expired.  If yes then the password will need to be changed.
                // Need to add this functionality
                header('Location: ' . ROOT_URL . 'employees');
                die();
            } else
            {
                Messages::setMsg('Incorrect Login', 'error');
            }
        }
        return;
    }

    public function forgotPassword()
    {
        // Sanitize POST
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        /* This method was modified from the original code.
         * The original code can be found at
         * https://www.martinstoeckli.ch/php/php.html#passwordreset
         * 
         * @author Martin Stoeckli - www.martinstoeckli.ch/php
         * @copyright Martin Stoeckli 2013, this code may be freely used in every
         *   type of project, it is provided without warranties of any kind.
         * @version 2.1
         */

        if ($post['submit'])
        {
            //Search the database for the given email address
            $this->query('SELECT * FROM personnel WHERE Email = :email ORDER BY Inserted_Timestamp DESC LIMIT 1');
            $this->bind(':email', $post['email']);
            $row = $this->single();
            $email = $row['Email'];
            $personnelID = $row['Personnel_ID'];

            if (!empty($row)) //checking if the search returned a value
            {
                // Generate a new token with its hash
                StoPasswordReset::generateToken($tokenForLink, $tokenHashForDatabase);

                // Store the hash together with the UserId and the creation date
                $this->query('INSERT INTO recoveryemails_enc (Personnel_ID, Token)'
                        . ' VALUES (:personnelID, :token)');
                $this->bind(':token', $tokenHashForDatabase);
                $this->bind(':personnelID', $personnelID);
                $this->execute();

                // Send link with the original token
                $emailLink = ROOT_URL . 'employees/resetpassword/' . $tokenForLink;
                Miscellaneous::notify_password($email, $emailLink); // calls the method to send out the email to the user
            }
        }
    }

    public function resetpassword()
    {
        /* This method was modified from the original code by Jim Baize
         * March 2018.
         * The original code can be found at
         * https://www.martinstoeckli.ch/php/php.html#passwordreset
         * 
         * @author Martin Stoeckli - www.martinstoeckli.ch/php
         * @copyright Martin Stoeckli 2013, this code may be freely used in every
         *   type of project, it is provided without warranties of any kind.
         * @version 2.1
         */
        $id = $this->test_input($_GET['id']); // The token from the URL

        $this->query('SELECT * FROM recoveryemails_enc ORDER BY Creation_DateTime DESC LIMIT 1');
        $result = $this->single(); // Run the above query and return a single result

        $item = $this->test_input($result['Token']); // The hash of the token in the database
        $createDateTime = date_create($result['Creation_DateTime']);  // DateTime the token was first created and stored in the DB

        if (!isset($id) || !StoPasswordReset::isTokenValid($id))
        {
            Messages::setMsg('The token is invalid.', 'error');
            //header('Location: ' . ROOT_URL);
            return;
        } elseif (!hash_equals($item, hash('sha256', $id, FALSE)) || isset($result['Redeemed_DateTime'])) // March 13, 2018 -- Still need to test the Redeemed part of this statement
        {
            Messages::setMsg('<p>The token does not exist or has already been used.<br/></p>', 'error');
            //header('Location: ' . ROOT_URL);
        } elseif (StoPasswordReset::isTokenExpired($createDateTime))  // Check whether the token has expired
        {
            Messages::setMsg('The token has expired.', 'error');
        } else
        {
            $this->query('UPDATE recoveryemails_enc SET Redeemed_DateTime = now() where Token = :token');
            $this->bind(':token', hash('sha256', $id, FALSE));
            $this->execute();
            $_SESSION['personnelID'] = $result['Personnel_ID'];

            header('Location: ' . ROOT_URL . 'employees/changeforgottenpassword');
            die();
            /*
             * That brings up another thought.  How to restrict the number of requests?
             * What is a reasonable rate limit?
             */
        }
        return;
    }

    public function changeforgottenpassword()
    {
        /*
         * Allows users to change their own password.
         * This method is if the user has forgotten his password
         * and used the forgot password link
         */


        // Sanitize POST
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $personnelID = $this->test_input($_SESSION['personnelID']);

        if ($post['submit'])
        {
            $this->query('SELECT * FROM personnel WHERE Personnel_ID = :personnelID ORDER BY Inserted_Timestamp DESC LIMIT 1');
            $this->bind(':personnelID', $personnelID);
            $row = $this->single();

            if ($post['newPassword1'] === $post['newPassword2'])
            {
                $pwHash = password_hash($post['newPassword1'], PASSWORD_DEFAULT); //hash the password going to the database
                $this->passwordChangeEngine($pwHash, $row);

                header('Location: ' . ROOT_URL . 'employees/login');
                die();
            }
        }
        //header('Location: ' . ROOT_URL . 'employees');
        //die();

        return;
    }

    public function changeknownpassword()
    {
        Miscellaneous::checkIsLoggedIn();
        /*
         * This method will allow the user to change his password
         * This will be used if the user has already logged in and knows the
         * current password
         */

        // Sanitize POST
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        if ($post['submit'])
        {
            $this->query('SELECT * FROM personnel WHERE Personnel_ID = :personnelID ORDER BY Inserted_Timestamp DESC LIMIT 1');
            $this->bind(':personnelID', $_SESSION['user_data']['personnelID']);
            $row = $this->single();

            if (empty($post['oldPassword']))
            {
                Messages::setMsg('The old password must be supplied.', 'error');
                return; // do I want a return statement or do I want
                //header('Location: ' . ROOT_URL);  // this header statement?
            } elseif (!password_verify($post['oldPassword'], $row['Password']))
            {
                Messages::setMsg('There was an error.  Please try again.', 'error');
                return; // do I want a return statement or do I want
                //header('Location: ' . ROOT_URL);  // this header statement?
            }

            if ($post['newPassword1'] === $post['newPassword2'])
            {
                $pwHash = password_hash($post['newPassword1'], PASSWORD_DEFAULT); //hash the password going to the database

                $this->passwordChangeEngine($pwHash, $row);

                if ($this->lastInsertId())
                {
                    //Redirect
                    header('Location: ' . ROOT_URL . 'employees');
                    die();
                }
            }
        }
        return;
    }

}
