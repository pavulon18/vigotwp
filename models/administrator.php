<?php

/**
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
 * Description of administrator
 * functions for only administrator level users.
 *
 * @author Jim Baize <pavulon@hotmail.com>
 */
class AdministratorModel extends Model
{

    public function Index()
    {
        Miscellaneous::checkIsLoggedIn();
        Miscellaneous::checkIsAdmin();
        return;
    }

    public function register()
    {
        Miscellaneous::checkIsLoggedIn();
        Miscellaneous::checkIsAdmin();
        //Sanitize Post
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        $dateOfBirth = $post['dobYear'] . '-' . $post['dobMonth'] . '-' . $post['dobDay'];
        $startDate = $post['startYear'] . '-' . $post['startMonth'] . '-' . $post['startDay'];

        if ($post['submit'])
        {
            $this->query('SELECT MAX(Personnel_ID) AS MaxPersonnelID FROM Personnel');
            $result = $this->single();
            $personnelID = $result['MaxPersonnelID'] + 1;
            
            $pwHash = password_hash($post['password1'], PASSWORD_DEFAULT);
            if ($post['password1'] != $post['password2'])
            {
                Messages::setMsg('The passwords do not match', 'error');
                header('Location: ' . ROOT_URL . 'administrators/register');
                die();
            } else
            {
                /**
                 * In this situation, I am inserting into three different tables.
                 * To make sure I don't have issues if something breaks, I am going 
                 * to use transactions. 
                 * */
                try
                {
                    $this->transactionStart();
                    $this->query('INSERT INTO personnel (Personnel_ID, FirstName, MiddleName, LastName, CallSign, DateOfBirth, Address_Line_1, Address_Line_2, Zip_Code, Telephone, Emergency_Contact_First_Name, Emergency_Contact_Last_Name, Emergency_Contact_Telephone, Username, Password, Email, Is_PW_Expired, Changed_By)'
                            . ' VALUES (:Personnel_ID, :FirstName, :MiddleName, :LastName, :CallSign, :DateOfBirth, :Address_Line_1, :Address_Line_2, :Zip_Code, :Telephone, :Emergency_Contact_First_Name, :Emergency_Contact_Last_Name, :Emergency_Contact_Telephone, :Username, :Password, :Email, :Is_PW_Expired, :Changed_By)');
                    $this->bind(':Personnel_ID', $personnelID);
                    $this->bind(':FirstName', $post['firstName']);
                    $this->bind(':MiddleName', $post['middleName']);
                    $this->bind(':LastName', $post['lastName']);
                    $this->bind(':CallSign', $post['callSign']);
                    $this->bind(':DateOfBirth', $dateOfBirth);
                    $this->bind(':Address_Line_1', $post['addressLineOne']);
                    $this->bind(':Address_Line_2', $post['addressLineTwo']);
                    $this->bind(':Zip_Code', $post['zipCode']);
                    $this->bind('Telephone', $post['telephoneNumber']);
                    $this->bind(':Emergency_Contact_First_Name', $post['emergencyFirstName']);
                    $this->bind(':Emergency_Contact_Last_Name', $post['emergencyLastName']);
                    $this->bind(':Emergency_Contact_Telephone', $post['emergencyTelephone']);
                    $this->bind(':Username', $post['username']);
                    $this->bind(':Password', $pwHash);
                    $this->bind(':Email', $post['email']);
                    $this->bind('Is_PW_Expired', 'Y');
                    $this->bind(':Changed_By', $_SESSION['user_data']['personnelID']);
                    $this->execute();

                    $this->query('INSERT INTO personnel_securityroles (Personnel_ID, Security_Role_ID, Changed_By)'
                            . ' VALUES (:Personnel_ID, :Security_Role_ID, :Changed_By)');
                    $this->bind(':Personnel_ID', $personnelID);
                    $this->bind(':Security_Role_ID', $post['securityRole']);
                    $this->bind(':Changed_By', $_SESSION['user_data']['personnelID']);
                    $this->execute();

                    $this->query('INSERT INTO personnel_employmentdates (Personnel_ID, Hire_Date, Changed_By)'
                            . ' VALUES (:Personnel_ID, :Hire_Date, :Changed_By)');
                    $this->bind(':Personnel_ID', $personnelID);
                    $this->bind(':Hire_Date', $startDate);
                    $this->bind(':Changed_By', $_SESSION['user_data']['personnelID']);
                    $this->execute();

                    $this->transactionCommit();
                    
                } catch (PDOException $ex)
                {
                    $this->transactionRollback();
                    echo $ex->getMessage();
                    Messages::setMsg($ex->getMessage(), 'error');
                }

                if ($this->lastInsertId())
                {
                    //Redirect
                    header('Location: ' . ROOT_URL . 'administrators/roster');
                    die();
                }
            }
        }
        return;
    }

    public function roster()
    {
        Miscellaneous::checkIsLoggedIn();
        Miscellaneous::checkIsAdmin();
        $this->query('SELECT * FROM personnel e1'
                . ' WHERE Inserted_Timestamp = '
                . ' (SELECT MAX(e2.Inserted_Timestamp)'
                . ' FROM personnel e2'
                . ' WHERE e1.Personnel_ID = e2.Personnel_ID)');
        $rows = $this->resultSet();
        return $rows;
        /*
         * I just realized this will list every employee past or present.
         * I will need to fix this.  I should probably give the administrator 
         * the ability to choose which option, either list all, list past, or list
         * present.
         */
    }

    public function changeuserpass()
    {
        Miscellaneous::checkIsLoggedIn();
        Miscellaneous::checkIsAdmin();

        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        if ($post['submit'])
        {
            $pwHash = password_hash($post['password'], PASSWORD_DEFAULT);
            if ($post['password'] === $post['password2'])
            {
                try
                {
                    //Start the transaction
                    $this->transactionStart();
                    $this->query('SELECT * FROM personnel WHERE Personnel_ID = :Personnel_ID ORDER BY Inserted_Timestamp DESC LIMIT 1');
                    $this->bind(':Personnel_ID', $post['personnelID']);
                    $rows = $this->single();

                    //copy the required data fields into a new row complete with time stamps changed as appropriate
                    $this->query('INSERT INTO personnel (Personnel_ID, FirstName, MiddleName, LastName, CallSign, DateOfBirth, SocialSecurityNumber, Address_Line_1, Address_Line_2, Zip_Code, Telephone, Emergency_Contact_Name, Emergency_Contact_Telephone, Username, Password, Email, Is_PW_Expired)'
                            . ' VALUES (:Personnel_ID, :FirstName, :MiddleName, :LastName, :CallSign, :DateOfBirth, :SocialSecurityNumber, :Address_Line_1, :Address_Line_2, :Zip_Code, :Telephone, :Emergency_Contact_Name, :Emergency_Contact_Telephone, :Username, :Password, :Email, :Is_PW_Expired)');
                    $this->bind(':Personnel_ID', $rows['Personnel_ID']);
                    $this->bind(':FirstName', $rows['FirstName']);
                    $this->bind(':MiddleName', $rows['MiddleName']);
                    $this->bind(':LastName', $rows['LastName']);
                    $this->bind(':CallSign', $rows['CallSign']);
                    $this->bind(':DateOfBirth', $rows['DateOfBirth']); 
                    $this->bind(':SocialSecurityNumber', $rows['SocialSecurityNumber']);
                    $this->bind(':Address_Line_1', $rows['Address_Line_1']);
                    $this->bind(':Address_Line_2', $rows['Address_Line_2']);
                    $this->bind(':Zip_Code', $rows['Zip_Code']);
                    $this->bind(':Telephone', $rows['Telephone']);
                    $this->bind(':Emergency_Contact_Name', $rows['Emergency_Contact_Name']);
                    $this->bind(':Emergency_Contact_Telephone', $rows['Emergency_Contact_Telephone']);
                    $this->bind(':Username', $rows['Username']);
                    $this->bind(':Password', $pwHash);
                    $this->bind(':Email', $rows['Email']);
                    $this->bind(':Is_PW_Expired', $rows['Is_PW_Expired']);
                    $this->execute();

                    //update the above inserted row with the new password hash
                    $this->query('UPDATE personnel SET Password = :pwHash WHERE Personnel_ID = :personnelID ORDER BY Inserted_Timestamp DESC LIMIT 1');
                    $this->bind('personnelID', $post['personnelID']);
                    $this->bind(':pwHash', $pwHash);
                    $this->execute();

                    $this->transactionCommit();

                    Messages::setMsg('Password successfully changed', 'success');
                    header('Location: ' . ROOT_URL . 'administrators');
                    die();
                } catch (PDOException $ex)
                {
                    $this->transactionRollback();
                    echo $ex->getMessage();
                    Messages::setMsg($ex->getMessage(), 'error');
                }
            } else
            {
                Messages::setMsg('There was an error.  Please try again.', 'error');
                header('Location: ' . ROOT_URL . 'administrators/changeuserpass');
                die();
            }
        }
        return;
    }
/*
    
    
        $this->query('SELECT e1.*, eprh.* FROM employees e1 '
                . 'LEFT OUTER JOIN employees e2 ON '
                . 'e1.employee_number = e2.employee_number AND '
                . 'e2.inserted_at > e1.inserted_at '
                . 'LEFT JOIN employee_payrollhours eprh ON '
                . 'eprh.employee_number = e1.employee_number '
                . 'WHERE e2.employee_number is null');
        $rows = $this->resultSet();
        return $rows;


        /**
         * Option #2
         * Test
         * 

          $this->query('SELECT e.*, p.* ' .
          'FROM (select *, max(inserted_at) AS most_recent ' .
          'FROM employees GROUP BY employee_number) e ' .
          'LEFT JOIN employee_payrollhours p ' .
          'ON p.employee_number = e.employee_number');
          $rows = $this->resultSet();
         * 
         *
          return $rows;
         * 
         */
    /*
   */
}
