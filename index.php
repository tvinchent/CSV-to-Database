<?php
/*
TO DO
- refactorisation simple: juste isoler les controle en fonction
- ajouter check vertical
- mapping (*)
- si possible
--
- voir plugin wordpress
- transformer en plugin wordpress
- tester sur le serveur milford

(*) mapping: (ne pas isoler ces settings en dur mais les regrouper et les identifier dans le code)
// préparation au mapping
- check valeur de la ligne 3, colonne B, C
- supprimer les lignes 1, 2 et 3
- supprimer les colonnes 1 a 4, 6 8 et autres..
- enlever les % juste avant l'envoie BDD car ce traitement seulement sur les valeurs inséré, pas sur les valeurs testé
- (si il y a des lignes bien precises, les hard coder, sinon mettre 0) remplacer les 0 par null (ici donc ",,")
// mapping en lui meme
- faire un tableau de la requete: a partir d'un tableau sans valeur superflu, en précisant dans le insert le bon ordre

Axe d'amelioration:
- améliorer la lisbilité du message d'erreur du controle de schema
- ne pas supprimer de colonnes (pas de array slice), plutot faire des references
- refactorisation des erreurs: voir les pattern pour un meilleur architecturage des erreurs imbriquée
- isoler les controles (essayer avec error handling / exception (cf w3school) pour la lisibilite du code)
- effectuer plus de verification sur le CSV (necessairement aprés la refactorisation car après cette opération, le code atteindra un stade difficilement maintenable)
- afficher le contenu et demander si cela correspond
- catcher les erreurs de BDD (erreur géré par default)
- refactorisation Orienté Objet

Refactorisation:
- formulaire
- si fichier soumis (verif upload (check CSV (prepare for BDD + envoie BDD)))
- [fonction] verif upload: si fichier soumis, fonction qui test si le fichier soumis est valide
- [fonction] parse CSV (private)
- [fonction] check CSV (singleton, use / herite de parse) // insérer ici en dur les colonnes a checker
- [fonction] delete CSV (use / herite de parse // return: array sans la ligne passé en parametre)
- [fonction] prepare for BDD (singleton, use / herite de delete) // insérer ici en dur les colonnes a supp
- [fonction] envoie BDD (use / herite de prepare for BDD): si contenu du CSV ok
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
// !!!!!!!!!!!!! A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
// !!!!!!!!!!!!! Refaire les parse avec foreach car les insertions doivent etre separé des controles
// !!!!!!!!!!!!! afin de pouvoir insérer les données seulement une fois que celles-ci ont été verifié
// !!!!!!!!!!!!! Faires fonctions parse (utilisé par check et delete) donc privé quand passage en OO
// !!!!!!!!!!!!! Faires fonctions check: check vertical (param: colonne a recup), check horizontal (param: ligne a recup)
// !!!!!!!!!!!!! Faires fonctions delete: delete vertical (param: colonne a recup), delete horizontal (param: ligne a recup)
// !!!!!!!!!!!!! Il vaut mieux répéter des instructions (mais factorisé) plutot que de mélanger des fonctionnalités distinctes de code
// !!!!!!!!!!!!! Et ensuite avoir à les contourner au moyen de exit
        // db query
                $db = new PDO('mysql:host='.$dbHost.';dbname='.$dbName.';charset=utf8', $dbUsername, $dbPassword);
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
// !!!!!!!!!!!!! END OF A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
                // START OF SPECIFIC LINES DELETION
                // delete the header lines
                $lines = array_slice($lines, 1);
                // END OF SPECIFIC LINES DELETION
                // parse every lines
                foreach($lines as $line) {
                    // cut every element of the line to format
                    $line = explode(',', $line);
                    // check the first columns concordance
// !!!!!!!!!!!!! A CHANGER AVEC schema vertical, a priori en mettre 2, donc ajouter && !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                    if($firstline===$csvSchema){
                        $value_for_db_insert="'";
                        foreach ($line as $key=>$element) {
                            // START OF SPECIFIC COLUMN DELETION
                            // END OF SPECIFIC COLUMN DELETION
                            // parse every lines
                            $value_for_db_insert.=$element."', '";
                        }
// !!!!!!!!!!!!! A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
                        // remove last comma
                        $value_for_db_insert = substr($value_for_db_insert,0,strlen($value_for_db_insert)-3);
                        // then insert
                        $result = $db->exec("INSERT INTO $dbTable VALUES(".$value_for_db_insert.")");
// !!!!!!!!!!!!! END OF A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
                    }
                    else{
// !!!!!!!!!!!!! A CHANGER AVEC message schema vertical plus précis (comme l'horizontal) !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        echo "Your CSV vertical schema is invalid.";
                    }
                }
// !!!!!!!!!!!!! A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
                // close db connection
                $db = null;
                echo "Your CSV data has been successfully inserted into the database";
// !!!!!!!!!!!!! END OF A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
            }
            // error message
            else{
                echo "Your CSV schema is invalid.";
                echo "<br><strong>Your document header columns is: </strong>";
                print_r($firstline);
                echo "<br><strong>Must be: </strong>";
                print_r($csvSchema);
                echo "<br><strong>Difference: </strong>";
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