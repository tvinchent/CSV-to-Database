<?php
// Notice: be sure data is set to ON in php.ini

/*
Axe d'amelioration:
- mapper avec les bonnes données
- tester sur le serveur milford
- update et non insert si donnée déjà existantes
- passer en parametre et en variable de settings la connexion bdd.
- passer en parametre et en variable de settings les noms de la premiere colonne a tester.
Les passer à l'interieur d'un array. Modifier le check et insert into associé. 
Les inserer aussi dans le message d'erreur pour que l'utilisateur sache ce qu'il doit mettre.
Les isoler dans un fichier setting.php
- isoler les controles (essayer avec error handling / exception (cf w3school) pour la lisibilite du code):
- effectuer plus de verification sur le CSV (aprés la refactorisation car après cette opération, le code atteindra un stade difficilement maintenable)
- afficher le contenu et demander si cela correspond

Refactorisation ergonomique (qui suivent le parcours utilisateur):
- formulaire
- affichage erreur (exit(); si erreur): si fichier soumis, fonction qui test si le fichier soumis est valide
- check contenu du CSV (juste verifier si la premiere ligne correspond bien a la BDD): si fichier soumis et valide
- envoie BDD: si contenu du CSV ok

*/

if(isset($_POST["submit"]) && isset($_FILES["csv"])){
	if($_FILES["csv"]["error"] == 0) {
        if($_FILES["csv"]["type"] == "text/csv") {
            //parse CSV
        	$csvData = file_get_contents($_FILES["csv"]["tmp_name"]);
			$lines = explode(PHP_EOL, $csvData);
            $firstline = explode(',', $lines[0]);
            // check the first line concordance
            if($firstline[0]=="name" && $firstline[1]=="age" && $firstline[2]=="skill"){
                $db = new PDO('mysql:host=localhost;dbname=test;charset=utf8','root','');
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
                    // db query
                    $result = $db->exec("INSERT INTO fromCSV(val1, val2, val3) VALUES(".$value_for_db_insert.")");
                }
                // close db connection
                $db = null;
                echo "Your CSV data has been successfully inserted into the database";
            }// error message
            else{
                echo "Your CSV schema is invalid.".$firstline[0].$firstline[1].$firstline[2];
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