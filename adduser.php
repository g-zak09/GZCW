<?php
//header("location: signup.php");
print_r($_POST);
include_once("connection.php");//import equivalent!
if($_POST["AccountType"]=="Admin"){
    $role=1;

}else{
    $role=0;
}
//$role=1;
//$username="bob";
$hashedpassword=password_hash($_POST["password"],PASSWORD_DEFAULT);
echo($hashedpassword);
try{
    $stmt=$conn->prepare("INSERT INTO tblusers
    (UserID, Username, Surname, Forename, Password, Year, Balance, Role)
    VALUES
    (NULL, :Username, :Surname, :Forename, :Password, :Year, :Balance, :Role)
    ");
    $stmt->bindParam(":Surname", $_POST["surname"]);
    $stmt->bindParam(":Forename", $_POST["forename"]);
    $stmt->bindParam(":Password", $hashedpassword);
    $stmt->bindParam(":Balance", $_POST["balance"]);
    $stmt->bindParam(":Role", $role);
    $stmt->bindParam(":Username", $username);
    $stmt->execute();
}
catch(PDOException $e)
{
    echo("error: " . $e->getMessage());

}
?>