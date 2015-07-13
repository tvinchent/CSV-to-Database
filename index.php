<?php
// Notice: be sure data is set to ON in php.ini

/*
Axe d'amelioration:
- voir plugin wordpress
- refactorisation (voir les pattern pour un meilleur architecturage des erreur imbriquée, sinon juste isoler les controle en fonction)
- transformer en plugin wordpress
- tester sur le serveur milford
- isoler les controles (essayer avec error handling / exception (cf w3school) pour la lisibilite du code)
- effectuer plus de verification sur le CSV (necessairement aprés la refactorisation car après cette opération, le code atteindra un stade difficilement maintenable)
- afficher le contenu et demander si cela correspond
- catcher les erreurs de BDD (erreur géré par default)
- refactorisation OO

=> En attendant CSV client: faire simple, donc:
- (fait) verification correspondance csv envoyé / schema pre etabli
- no mapping, car peut-etre pas necessaire

Refactorisation:
- formulaire
- affichage erreur (exit(); si erreur): si fichier soumis, fonction qui test si le fichier soumis est valide
- parse CSV
- envoie BDD: si contenu du CSV ok
*/

// include setting
if (file_exists("include/setting_prod.php")) include_once 'include/setting_prod.php'; else include_once 'include/setting.php';

if(isset($_POST["submit"]) && isset($_FILES["csv"])){
	if($_FILES["csv"]["error"] == 0) {
        if($_FILES["csv"]["type"] == "text/csv") {
        // parse CSV
        	$csvData = file_get_contents($_FILES["csv"]["tmp_name"]);
            // parse every lines
			$lines = explode(PHP_EOL, $csvData);
            $firstline = explode(',', $lines[0]);
        // check the first line concordance
            if($firstline===$csvSchema){
                $db = new PDO('mysql:host='.$dbHost.';dbname='.$dbName.';charset=utf8', $dbUsername, $dbPassword);
        // db query
                // check if table is empty
                $selectall = $db->query("SELECT * FROM $dbTable");
                $result = $selectall->fetch();
                $counttable = (count($result));
                // if not empty: delete current value before inserting
                if($counttable > 1){
                    $delete = $db->prepare("DELETE FROM $dbTable");
                    $delete->execute();
                    $count = $delete->rowCount();
                    print("Deleted $count rows.\n");
                }
                // delete the header column
                $lines = array_slice($lines, 1);
                // parse every lines
                foreach($lines as $line) {
                    // cut every element of the line to format
                    $line = explode(',', $line);
                    $value_for_db_insert="'";
                    foreach ($line as $key=>$element) {
                        $value_for_db_insert.=$element."', '";
                    }
                    // remove last comma
                    $value_for_db_insert = substr($value_for_db_insert,0,strlen($value_for_db_insert)-3);
                    // then insert
                    $result = $db->exec("INSERT INTO $dbTable VALUES(".$value_for_db_insert.")");
                }
                // close db connection
                $db = null;
                echo "Your CSV data has been successfully inserted into the database";
            }// error message
            else{
                echo "Your CSV schema is invalid. Your document header columns is: 1: ";
                print_r($firstline);
                echo "<br>Must be: ";
                print_r($csvSchema);
                echo "<br>Difference: ";
                $diffArray = array_diff_assoc($firstline, $csvSchema);
                print_r($diffArray);
            }
            
        }
        else{
        	echo "Your files must be a CSV.";
        }
    }
    else{
    	echo "Error in upload.";
    }
}
else{
	echo "Choose a file.";
}

?>

<html>
<head></head>
<body>

<!-- Upload form -->
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data">
<input type="file" name="csv"></input>
<input type="submit" name="submit" value="Upload file"></input>
</form>

</body>
</html>