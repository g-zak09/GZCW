<!DOCTYPE HTML> 
<html> 
    <head> 
        <title>Sign up</title> 
    </head> 
    <body>
        <form action="adduser.php" method="post">
        Forename:<input type="text" name="forename"><br>
        Surname:<input type="text" name="surname"><br>
        Username: <input type="text" name="username"><br>
        Password:<input type="password" name="password"><br>
        Date Of Birth:<input type="date" name="year"><br>
        </form>
        <input type="submit" value="Add User"><br>
        
    </body>
</html>

    <?php
        include_once("connection.php");
        $stmt=$conn->prepare("SELECT * FROM tblusers");
        $stmt->execute();
        while($row=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            //print_r($row);
            echo($row["Forename"]." ".$row["Surname"]." &rarr; ".$row["Username"]);
            echo("<br>");
        }
    ?>
        
    </body>
</html>