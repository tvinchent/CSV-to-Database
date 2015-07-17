<?php
/*
NOTE:
If there is the problem with the data, it's probably due a file format
How to fix:
- We will need to change the value of "PHP_EOL" by a "\r\n" or "\r" in $lines = explode(PHP_EOL, $csvData);
- Also change the value of "MilfordCsvSchemaProblematicColumns" in setting_prod

TO DO
- ecrire ce qui est en commentaire pour le moment
--
- ajouter la ligne pour mettre à jour le graph
- ajouter la maj du titre
- ajouter un test pour controler le format Windows du doc: verifier si la lines1 est définie
--
- transformer en plugin wordpress
- tester sur le serveur milford

(*) mapping: (ne pas isoler ces settings en dur mais les regrouper et les identifier dans le code)
// préparation au mapping
- check valeur de la ligne 3, colonne B, C
- supprimer les lignes 1, 2 et 3
- supprimer les colonnes 1 a 4, 6 8 et autres..
- enlever les % juste avant l'envoie BDD car ce traitement seulement sur les valeurs inséré, pas sur les valeurs testé
- (pour les 0/null: si il y a des lignes bien precises, les hard coder, sinon mettre 0) remplacer les 0 par null (ici donc ",,")
// mapping en lui meme
- faire un tableau de la requete: a partir d'un tableau sans valeur superflu, en précisant dans le insert le bon ordre

Axe d'amelioration:
- throw error quand le mapping ne reconnait rien, ne pas le mettre simplement dans la valeur de requette qui ne sera jamais visible
- faire des unset commencant a 1 et pas a 0 pour supprimer le décallage
- refactorisation simple: juste isoler les controles en fonction (pb: je parse a chaque fois le fichier donc essayer plutot de stocker mon parse dans une global)
Pour cela: fonctionnement agile, cad commitable en partie fonctionelle
cf le CORE: commencer par factoriser la gauche, l'insérer dans le code existant
Puis parse, l'insérer dans le code existant
Puis parse vertically / horizontally, l'insérer dans le code existant
Puis check vertically / horizontally, l'insérer dans le code existant
Puis check, l'insérer dans le code existant
Puis delete vertically / horizontally, l'insérer dans le code existant
Puis delete, l'insérer dans le code existant
Puis prepare, l'insérer dans le code existant
Puis envoie BDD, l'insérer dans le code existant
- ajouter la possibilité de specifier s'il s'agit d'un format mac ou windows, mettre par défault le format du systeme (en reperant les var d'env)
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
- CORE: si fichier soumis (verif upload (check CSV (prepare for BDD + envoie BDD)))
- [fonction] verif upload (observer) : si fichier soumis, fonction qui test si le fichier soumis est valide
- [fonction] parse CSV (private)
- [fonction] parse vertically CSV (use / herite de parse)
- [fonction] parse horizontally CSV (use / herite de parse)
- [fonction] check vertically CSV (private, singleton, use / herite de parse vertically)
- [fonction] check horizontally CSV (private, singleton, use / herite de parse horizontally)
- [fonction] check CSV (singleton, use / herite de check vertically, check horizontally) // insérer ici en dur les colonnes a checker
- [fonction] delete vertically CSV (use / herite de parse vertically // return: array sans la ligne passé en parametre)
- [fonction] delete horizontally CSV (use / herite de parse horizontally // return: array sans la ligne passé en parametre)
- [fonction] delete CSV (singleton, use / herite de delete vertically, delete horizontally // return: array sans la ligne passé en parametre)
- [fonction] prepare for BDD (singleton, use / herite de delete): si contenu du CSV ok // insérer ici en dur les colonnes a delete
- [fonction] envoie BDD (use / herite de prepare for BDD)
*/

$var1 = "variable1";
$var2 = "variable3";
$tableau = array($var1, $var2);
 
switch ($tableau){
    case array("variable1", "variable2"):
        echo "Le tableau correspond !";
    break;
     
    case array(NULL, NULL):
        echo "Le tableau ne correspond pas.";
    break; 
}

// include setting
if (file_exists("include/setting_prod.php")) include_once 'include/setting_prod.php'; else include_once 'include/setting.php';

if(isset($_POST["submit"]) && isset($_FILES["csv"])){
	if($_FILES["csv"]["error"] == 0) {
        if($_FILES["csv"]["type"] == "text/csv") {
        // parse CSV
        	$csvData = file_get_contents($_FILES["csv"]["tmp_name"]);
            // parse every lines
            $lines = explode(PHP_EOL, $csvData);
            if(isset($lines[2])){
                $firstline = explode(',', $lines[2]);// PRODUCTION SPECIFIC CODE: the header is on line 3
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
                    // check if table1 is empty
                    $selectall = $db->query("SELECT * FROM $dbTable1");
                    $result = $selectall->fetch();
                    $counttable = (count($result));
                    // if not empty: delete current value before inserting
                    if($counttable > 1){
                        $delete = $db->prepare("DELETE FROM $dbTable1");
                        $delete->execute();
                        $count = $delete->rowCount();
                        print("Deleted $count rows.\n");
                    }
                    // check if table2 is empty
                    $selectall = $db->query("SELECT * FROM $dbTable2");
                    $result = $selectall->fetch();
                    $counttable = (count($result));
                    // if not empty: delete current value before inserting
                    if($counttable > 1){
                        $delete = $db->prepare("DELETE FROM $dbTable2");
                        $delete->execute();
                        $count = $delete->rowCount();
                        print("Deleted $count rows.\n");
                    }
    // !!!!!!!!!!!!! END OF A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
                    // START OF SPECIFIC LINES DELETION
                    // special care: 1 row shift. Example: if you want to delete the line 41 of your CSV, you have to unset: unset($lines[42]);
                    // if I unset 29, I need to unset 28
    // PRODUCTION SPECIFIC CODE
                    unset($lines[0],$lines[1],$lines[2],$lines[3],$lines[4],$lines[5],$lines[6],$lines[7],$lines[8],$lines[9]);
                    unset($lines[16]);
                    unset($lines[20],$lines[21],$lines[22],$lines[23],$lines[24],$lines[25],$lines[26],$lines[27],$lines[28]);
                    unset($lines[35],$lines[39]);
                    unset($lines[40],$lines[41],$lines[42],$lines[43],$lines[44],$lines[45],$lines[46],$lines[47]);
                    unset($lines[54],$lines[58],$lines[59]);
                    unset($lines[60],$lines[61],$lines[62],$lines[63],$lines[64],$lines[65],$lines[66],$lines[67],$lines[68],$lines[69]);
                    unset($lines[70],$lines[71],$lines[72],$lines[73],$lines[74],$lines[75],$lines[76],$lines[77],$lines[78],$lines[79]);
                    unset($lines[80],$lines[81],$lines[82],$lines[83],$lines[84],$lines[85]);
                    unset($lines[92],$lines[96]);
    // END OF PRODUCTION SPECIFIC CODE
                    // END OF SPECIFIC LINES DELETION
                    // parse every lines
                    foreach($lines as $column) {
                        // cut every element of the column to format
                        $column = explode(',', $column);
// START OF SPECIFIC COLUMN DELETION
// (prendre juste le else, if pas pour le moment car le inception simple ne sert que pour le graphique et c'est pas clair // if column != 87 à 96 supprimer tout element inutile dont inception simple else supprimer tout element inutile mais sans inception simple
// supprimer tout element inutile dont fund et inception simple
// enlever le % (seulement si )
                        $value_for_db_insert="'";
                // START OF SPECIFIC INSERT
// if column = x alors fund = act/bal/cons en mappant dans les parentheses apres le $table les bonnes colonnes
// & colmunsOrderForInsert = 
// & table = perf_after_tax
// if column = x alors fund = act/bal/cons en mappant dans les parentheses apres le $table les bonnes colonnes
// & table = perf_table
// test: echo $insertDB
                        switch ($column[0]) {
                            case '700200':
                                $value_for_db_insert.='act';
                                break;
                            case '700201':
                                $value_for_db_insert.='dyn';
                                break;
                            case '700202':
                                $value_for_db_insert.='bal';
                                break;
                            case '700203':
                                $value_for_db_insert.='tas';
                                break;
                            case '700204':
                                $value_for_db_insert.='glob';
                                break;
                            case '700205':
                                $value_for_db_insert.='inc';
                                break;
                            case '700300':
                                $value_for_db_insert.='x_act';
                                break;
                            case '700301':
                                $value_for_db_insert.='x_bal';
                                break;
                            case '700302':
                                $value_for_db_insert.='cons';
                                break;

                            default:
                                $value_for_db_insert.='error fund';
                                break;
                        }
                        switch ($column[2]) {
                            case 'High PIR Return':
                                $value_for_db_insert.='_2800';
                                $table_for_db_insert=$dbTable2;
                                break;
                            case 'Medium PIR Return':
                                $value_for_db_insert.='_1750';
                                $table_for_db_insert=$dbTable2;
                                break;
                            case 'Low PIR Return':
                                $value_for_db_insert.='_1050';
                                $table_for_db_insert=$dbTable2;
                                break;
                            case 'Zero PIR Return':
                                $table_for_db_insert=$dbTable1;
                                break;
                            
                            default:
                                $value_for_db_insert.='error return type';
                                $table_for_db_insert='error table';
                                break;
                        }
                        $colmuns_order_for_db_insert='fund,since_inception,1_month,3_months,6_months,1_year,2_years,3_years,5_years';
                        $value_for_db_insert.='\',\'';
                // END OF SPECIFIC INSERT

                        unset($column[0],$column[1],$column[2],$column[3],$column[9]);
// since inception (juste for graph): set 
                        unset($column[11],$column[13],$column[15],$column[16],$column[17],$column[18],$column[19]);
                        unset($column[20],$column[21],$column[22]);
// END OF SPECIFIC COLUMN DELETION
                        foreach ($column as $key=>$element) {
                                // parse every columns
// START OF SPECIFIC VALUE FORMAT
                            $caseWithJustASpace = "\r";
                            // supprime la valeur de fin si c'est case non vide et pas juste un espace et contie
                            // delete the end value if value not empty or contains just a space and contains a "%"
                            if (!empty($element) and $element!=$caseWithJustASpace and strstr($element,"%")) {
                                $element = substr($element,0,strlen($element)-1);
                                // delete the end value if still contains a "%"
                                if (strstr($element,"%")){
                                    $element = substr($element,0,strlen($element)-1);
                                }
                            }
// END OF SPECIFIC VALUE FORMAT
                                $value_for_db_insert.=$element."', '";
                        }
// !!!!!!!!!!!!! A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
                        // remove last comma
                        $value_for_db_insert = substr($value_for_db_insert,0,strlen($value_for_db_insert)-3);
                        // then insert
                        $result = $db->exec("INSERT INTO $table_for_db_insert ($colmuns_order_for_db_insert) VALUES($value_for_db_insert)");

    // !!!!!!!!!!!!! END OF A DEPLACER DANS UNE FONCTION DEDIE A L'INSERTION
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
                    var_dump($firstline);
                    echo "<br><strong>Awaiting: </strong>";
                    var_dump($csvSchema);
                    echo "<br><strong>Difference: </strong>";
                    var_dump($diffArray);
                }
            }
            else{
                echo "Your CSV schema is invalid.";
                echo "<br><br><strong>Awaiting: </strong>";
                var_dump($csvSchema);
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

?>

<html>
<head></head>
<body>

<!-- Upload form -->
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data">
<input type="file" name="csv"></input>
<input type="submit" name="submit" value="Upload file"></input>
</form>
<p>Note: To resolve a malformation of your CVS, it must be at the Windows format. Open it in Excel and save it as a "<strong>Windows</strong> comma separated value"
<br>Do no hesitate to make a copy of your 2 table database before using.
</p>

</body>
</html>