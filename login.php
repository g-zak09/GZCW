<?php
session_start();//to allow session variables
//header("location: .php");
print_r($_POST);
array_map("htmlspecialchars", $_POST); //inputs cannot inject html
include_once("connection.php");//equivalent of import

try{
    $stmt=$conn->prepare("SELECT * FROM tblusers WHERE Username=:Username;");
    $stmt->bindParam(":Username", $_POST["username"]);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo("Invalid Username.");
    }
    else{
        //check password
        while($row=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            print_r($row);
            $hashed=$row["Password"];
            $attempt=$_POST["password"];

            if(password_verify($attempt,$hashed)){
                echo("Correct password, login successful");
                $_SESSION["firstname"]=$row["Forename"];//session variable, global, lasts until browser closed
                $_SESSION["loggedinuser"]=$row["UserID"];
                $_SESSION["admin"]=$row["Role"];

                
            }
            else{
                echo("Incorrect password");
            }
            //echo($row["Name"]." ".$row["Description"]." ".$row["Price"]);
            echo("<br>");
        }

        echo("ok");
    }
}
catch(PDOException $e)
{
    echo("error: " . $e->getMessage());
}
?>