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
?>

<img id="top" src="/assets/graphics/top.png" alt="">
<div id="form_container">

    <h1><a>Add New Employee</a></h1>
    <form id="register" class="appnitro"  method="post" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form_description">
            <h2>New Personnel</h2>
            <p>Enter the following information.<br />
                (* Indicates a required field)</p>
        </div>						
        <ul >
            <li id="li_2" >
                <label class="description" for="callSign">Radio Call Sign </label>
                <div>
                    <input id="element_2" name="callSign" class="element text medium" type="text" maxlength="255" value="17-"/> 
                </div> 
            </li>
            <li id="li_1" >
                <label class="description" for="element_1">*Name </label>
                <span>
                    <input id="element_1_1" name= "firstName" class="element text" maxlength="255" size="8" value=""/>
                    <label>First</label>
                </span>
                <span>
                    <input id="element_1_3" name= "middleName" class="element text" maxlength="255" size="14" value=""/>
                    <label>Middle</label>
                </span> 
                <span>
                    <input id="element_1_2" name= "lastName" class="element text" maxlength="255" size="14" value=""/>
                    <label>Last</label>
                </span> 
            </li>
            <li id="li_3" >
                <label class="description" for="dateOfBirth">Date of Birth </label>
                <span>
                    <input id="dobMonth" name="dobMonth" class="element text" size="2" maxlength="2" value="" type="text"> /
                    <label for="dobMonth">MM</label>
                </span>
                <span>
                    <input id="dobDay" name="dobDay" class="element text" size="2" maxlength="2" value="" type="text"> /
                    <label for="dobDay">DD</label>
                </span>
                <span>
                    <input id="dobYear" name="dobYear" class="element text" size="4" maxlength="4" value="" type="text">
                    <label for="dobYear">YYYY</label>
                </span>

                <span id="calendar_3">
                    <img id="cal_img_3" class="datepicker" src="/assets/images/calendar.gif" alt="Pick a date.">	
                </span>
                <script type="text/javascript">
                    Calendar.setup({
                        inputField: "dobYear",
                        baseField: "dateOfBirth",
                        displayArea: "calendar_3",
                        button: "cal_img_3",
                        ifFormat: "%B %e, %Y",
                        onSelect: selectDate
                    });
                </script>

            </li>
            <li id="li_6" >
                <label class="description" for="telephoneNumber">Telephone Number </label>
                <div>
                    <input id="telephoneNumber" name="telephoneNumber" class="element text medium" type="tel" pattern="[\(]\d{3}[\)]\d{3}[\-]\d{4}" value=""/> 
                </div> 
            </li>
            <li id="li_8" >
                <label class="description" for="email">*Email </label>
                <div>
                    <input id="email" name="email" class="element text medium" type="email" maxlength="255" value=""/> 
                </div> 
            </li>
            <li id="li_9" >
                <label class="description" for="username">*Username </label>
                <div>
                    <input id="username" name="username" class="element text medium" type="text" maxlength="255" value=""/> 
                </div> 
            </li>
            <li id="li_10" >
                <label class="description" for="password1">*Password </label>
                <div>
                    <input id="password1" name="password1" class="element text medium" type="password" maxlength="255" value=""/> 
                </div> 
            </li>
            <li id="li_11" >
                <label class="description" for="password2">*Repeat Password </label>
                <div>
                    <input id="password2" name="password2" class="element text medium" type="password" maxlength="255" value=""/> 
                </div> 
            </li>
            <li id="li_4" >
                <label class="description" for="element_4">Address </label>

                <div>
                    <input id="addressLineOne" name="addressLineOne" class="element text large" value="" type="text">
                    <label for="addressLineOne">Street Address</label>
                </div>

                <div>
                    <input id="addressLineTwo" name="addressLineTwo" class="element text large" value="" type="text">
                    <label for="addressLineTwo">Address Line 2</label>
                </div>

                <div class="left">
                    <input id="element_4_3" name="element_4_3" class="element text medium" value="" type="text">
                    <label for="element_4_3">City</label>
                </div>

                <div class="right">
                    <input id="element_4_4" name="element_4_4" class="element text medium" value="" type="text">
                    <label for="element_4_4">State / Province / Region</label>
                </div>

                <div class="left">
                    <input id="zipCode" name="zipCode" class="element text medium" maxlength="15" value="" type="tel" pattern="d{5}">
                    <label for="zipCode">Postal / Zip Code</label>
                </div>
            </li>
            <li id="li_startDate" >
                <label class="description" for="startDate">Official Start Date </label>
                <span>
                    <input id="startMonth" name="startMonth" class="element text" size="2" maxlength="2" value="" type="text"> /
                    <label for="startMonth">MM</label>
                </span>
                <span>
                    <input id="startDay" name="startDay" class="element text" size="2" maxlength="2" value="" type="text"> /
                    <label for="startDay">DD</label>
                </span>
                <span>
                    <input id="startYear" name="startYear" class="element text" size="4" maxlength="4" value="" type="text">
                    <label for="startYear">YYYY</label>
                </span>
                <span id="calendar_3">
                    <img id="cal_img_3" class="datepicker" src="/assets/images/calendar.gif" alt="Pick a date.">	
                </span>
                <script type="text/javascript">
                    Calendar.setup({
                        inputField: "startYear",
                        baseField: "startYear",
                        displayArea: "calendar_3",
                        button: "cal_img_3",
                        ifFormat: "%B %e, %Y",
                        onSelect: selectDate
                    });
                </script>
            </li>
            <li id="register" >
                <label class="description" for="securityRole">Security Role </label>
                <div>
                    <select class="element select medium" id="securityRole" name="securityRole" required="">
                        <?php
                        // Reads the names of the security roles from the database
                        // populates the dropdown with this information.
                        $dropDown = new Miscellaneous();

                        $query = 'SELECT * FROM security_roles';
                        $rows = $dropDown->dropdown($query);
                        
                        foreach ($rows as $row) :
                            {
                                echo "<option value='" . $row['SecurityRoleID'] . "'>" . $row['Security_Role_Name'] . "</option>";
                            }
                        endforeach;
                        ?>
                    </select>
                </div>
            </li>
            <li id="li_5" >
                <label class="description" for="element_5">Emergency Contact Name </label>
                <span>
                    <input id="emergencyFirstName" name= "emergencyFirstName" class="element text" maxlength="255" size="8" value=""/>
                    <label>First</label>
                </span>
                <span>
                    <input id="emergencyLastName" name= "emergencyLastName" class="element text" maxlength="255" size="14" value=""/>
                    <label>Last</label>
                </span> 
            </li>
            <li id="li_7" >
                <label class="description" for="emergencyTelephone">Emergency Contact Telephone Number </label>
                <div>
                    <input id="emergencyTelephone" name="emergencyTelephone" class="element text medium" type="tel" pattern="[\(]\d{3}[\)]\d{3}[\-]\d{4}" value=""/> 
                </div>
            </li>
            <li class="buttons">
                <input type="hidden" name="form_id" value="30785" />
                <input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
            </li>
        </ul>
    </form>	
    <div id="footer">
        Generated by <a href="http://www.phpform.org">pForm</a>
    </div>
</div>
<img id="bottom" src="/assets/graphics/bottom.png" alt="">

