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

//namespace vigoTwpFd;

abstract class Model
{
    protected $dbh;
    protected $stmt;
    protected $error;

    public function __construct()
    {
        $options = [PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try
        {
            $this->dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, $options);
        }
        catch (PDOException $e)
        {
            $this->error = $e->getMessage();
        }        
    }

    public function query($query)
    {
        try
        {
            $this->stmt = $this->dbh->prepare($query);
        }
        catch (PDOException $e)
        {
            $this->error = $e->getMessage();
        }
    }

    //Binds the prep statement
    public function bind($param, $value, $type = null)
    {
        if (is_null($type))
        {
            switch (true)
            {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
        $this->stmt->execute();
    }

    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    public function test_input($dataIn)
    {
        $data = htmlspecialchars(stripslashes(trim($dataIn)));
        //$data = trim($data);
        //$data = stripslashes($data);
        //$data = htmlspecialchars($data);
        return $data;
    }

    public function transactionStart()
    {
        $this->dbh->beginTransaction();
    }

    public function transactionCommit()
    {
        $this->dbh->commit();
    }

    public function transactionRollback()
    {
        $this->dbh->rollBack();
    }

    public function passwordChangeEngine($pwHash, $row)
    {
        try
        {
            $this->transactionStart();
            $this->query('UPDATE personnel'
                    . ' SET Deleted_Timestamp = NOW()'
                    . ' WHERE Personnel_ID = :Personnel_ID'
                    . ' ORDER BY Inserted_Timestamp DESC LIMIT 1');
            $this->bind(':Personnel_ID', $row['Personnel_ID']);
            $this->execute();

            $this->query('INSERT INTO personnel (Personnel_ID, FirstName, MiddleName, LastName, CallSign, DateOfBirth, SocialSecurityNumber, Address_Line_1, Address_Line_2, Zip_Code, Telephone, Emergency_Contact_First_Name, Emergency_Contact_Last_Name, Emergency_Contact_Telephone, Username, Password, Email, Is_PW_Expired)'
                    . ' VALUES(:Personnel_ID, :FirstName, :MiddleName, :LastName, :CallSign, :DateOfBirth, :SocialSecurityNumber, :Address_Line_1, :Address_Line_2, :Zip_Code, :Telephone, :Emergency_Contact_First_Name, :Emergency_Contact_Last_Name, :Emergency_Contact_Telephone, :Username, :Password, :Email, :Is_PW_Expired)');
            $this->bind(':Personnel_ID', $row['Personnel_ID']);
            $this->bind(':FirstName', $row['FirstName']);
            $this->bind(':MiddleName', $row['MiddleName']);
            $this->bind(':LastName', $row['LastName']);
            $this->bind(':CallSign', $row['CallSign']);
            $this->bind(':DateOfBirth', $row['DateOfBirth']);
            $this->bind(':SocialSecurityNumber', $row['SocialSecurityNumber']); 
            $this->bind(':Address_Line_1', $row['Address_Line_2']);
            $this->bind(':Address_Line_2', $row['Address_Line_2']);
            $this->bind(':Zip_Code', $row['Zip_Code']);
            $this->bind(':Telephone', $row['Telephone']);
            $this->bind(':Emergency_Contact_First_Name', $row['Emergency_Contact_First_Name']);
            $this->bind(':Emergency_Contact_Last_Name', $row['Emergency_Contact_Last_Name']);
            $this->bind(':Emergency_Contact_Telephone', $row['Emergency_Contact_Telephone']);
            $this->bind(':Username', $row['Username']);
            $this->bind(':Password', $pwHash);
            $this->bind(':Email', $row['Email']);
            $this->bind(':Is_PW_Expired', $row['Is_PW_Expired']);
            $this->execute();
            $this->transactionCommit();
        }
        catch (PDOException $ex)
        {
            $this->transactionRollback();
            echo $ex->getMessage();
        }
        return;
    }
}
