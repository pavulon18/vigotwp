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

public function test_input($data)
{
$data = trim($data);
$data = stripslashes($data);
$data = htmlspecialchars($data);
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
$this->query('UPDATE employees'
. ' SET Deleted_at = NOW()'
. ' WHERE Employee_Number = :Employee_Number'
. ' ORDER BY Inserted_at DESC LIMIT 1');
$this->bind(':Employee_Number', $row['Employee_Number']);
$this->execute();

$this->query('INSERT INTO employees (Employee_Number, First_Name, Middle_Name, Last_Name, Pay_Rate, Sick_Days_Remaining, Vacation_Days_Remaining, Personal_Days_Remaining, FMLA_Days_Remaining, Is_On_Short_Term_Disability, Is_On_Long_Term_Disability, Is_On_FMLA, username, password, email, Is_PW_Expired, remember_me)'
. ' VALUES (:Employee_Number, :First_Name, :Middle_Name, :Last_Name, :Pay_Rate, :Sick_Days_Remaining, :Vacation_Days_Remaining, :Personal_Days_Remaining, :FMLA_Days_Remaining, :Is_On_Short_Term_Disability, :Is_On_Long_Term_Disability, :Is_On_FMLA, :username, :password, :email, :isPWExpired, :rememberMe)');
$this->bind(':Employee_Number', $row['Employee_Number']);
$this->bind(':First_Name', $row['First_Name']);
$this->bind(':Middle_Name', $row['Middle_Name']);
$this->bind(':Last_Name', $row['Last_Name']);
$this->bind(':username', $row['username']);
$this->bind(':password', $pwHash);
$this->bind(':email', $row['email']);
$this->bind(':isPWExpired', 0);
$this->bind('rememberMe', $row['remember_me']);
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