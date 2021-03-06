<?php

/*
  The MIT License

  Copyright 2018 Jim Baize <pavulon@hotmail.com>.

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.

 * 
 */

//namespace vigoTwpFd;

//Start Session
session_start();

//At least while in development, I need a way to see the testing server
//while I am on its local network.  The router will not do a turnaround.

require_once('config.php');

require_once('classes/Bootstrap.php');
require_once('classes/Controller.php');
require_once('classes/Model.php');
require_once('classes/Messages.php');
require_once('classes/Miscellaneous.php');
require_once('classes/StoPasswordReset.php');

require_once('controllers/home.php');
require_once('controllers/employees.php');
require_once('controllers/jobtitles.php');
require_once('controllers/administrators.php');
require_once('controllers/galleries.php');

require_once('models/home.php');
require_once('models/employee.php');
require_once('models/jobtitle.php');
require_once('models/administrator.php');
require_once('models/gallery.php');

$bootstrap = new Bootstrap($_GET);
$controller = $bootstrap->createController();

date_default_timezone_set(DEFAULT_TIMEZONE);

if ($controller)
{
    $controller->executeAction();
}
