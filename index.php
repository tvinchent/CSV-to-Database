<?php
/*
NOTE:
If there is the problem with the data, it's probably due a file format
How to fix:
- We will need to change the value of "PHP_EOL" by a "\r\n" or "\r" in $lines = explode(PHP_EOL, $csvData);
- Also change the value of "MilfordCsvSchemaProblematicColumns" in setting_prod
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
            if(isset($lines[2])){
                $firstline = explode(',', $lines[2]);// the header is on line 3
            // check the first line concordance
                if($firstline===$csvSchema){
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
// START OF SPECIFIC LINES DELETION
                    // special care: 1 row shift. Example: if you want to delete the line 41 of your CSV, you have to unset: unset($lines[42]);
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
// END OF SPECIFIC LINES DELETION
                    // parse every lines
                    foreach($lines as $column) {
                        // cut every element of the column to format
                        $column = explode(',', $column);

                        $value_for_db_insert="'";
// START OF SPECIFIC INSERT
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
// START OF SPECIFIC COLUMN DELETION
                        unset($column[0],$column[1],$column[2],$column[3],$column[9]);
                        // since inception (just for graph): set $column[3]
                        unset($column[11],$column[13],$column[15],$column[16],$column[17],$column[18],$column[19]);
                        unset($column[20],$column[21],$column[22]);
// END OF SPECIFIC COLUMN DELETION
                        foreach ($column as $key=>$element) {
                            // parse every columns
// START OF SPECIFIC VALUE FORMAT
                            $caseWithJustASpace = "\r";
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
                        // remove last comma
                        $value_for_db_insert = substr($value_for_db_insert,0,strlen($value_for_db_insert)-3);
                        // then insert
                        $result = $db->exec("INSERT INTO $table_for_db_insert ($colmuns_order_for_db_insert) VALUES($value_for_db_insert)");
                    }
                    // insert old value
                    $result = $db->exec("INSERT INTO `perf_table` (`fund`, `1_month`, `3_months`, `6_months`, `1_year`, `2_years`, `3_years`, `5_years`, `since_inception`) VALUES
                    ('act_b', 0.8, 2.41, 4.88, 10, 10, 10, 10, 10),
                    ('bal_b', -2.02, -1.83, 2.85, 11.39, 13.57, 18.98, 14.02, 3.83),
                    ('cons_b', 0.32, 0.92, 1.83, 3.72, 3.28, NULL, NULL, 3.12),
                    ('dyn_b', -7.07, -4.67, 2.5, 7.15, NULL, NULL, NULL, 4.04),
                    ('glob_b', 0.67, 2.05, 4.15, 8.47, 8.05, NULL, NULL, 8.01),
                    ('inc_b', 0.32, 0.92, 1.83, 3.72, 3.28, 3.08, 3.02, 3),
                    ('tas_b', -0.99, 1.18, 7.32, 11.64, 11.51, 15.02, 11.44, 2.87),
                    ('x_act_b', 0.8, 2.41, 4.88, 10, 10, 10, 10, 10),
                    ('x_bal_b', -2.02, -1.83, 2.85, 11.39, 13.57, 18.98, 14.02, 3.83)");
                    // close db connection
                    $db = null;
                    echo "Your CSV data has been successfully inserted into the database";
                }
                // error message
                else{
                    echo "Your CSV schema is invalid.";
                    echo "<br><strong>Your document header columns is: </strong>";
                    var_dump($firstline);
                    echo "<br><strong>Awaiting: </strong>";
                    var_dump($csvSchema);
                    echo "<br><strong>Difference: </strong>";
                    $diffArray = array_diff_assoc($firstline, $csvSchema);
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
