<?php 
/***
 * Author : Web Developer Kanai
 * Uri: https://ksconsultant.online
 * Position: Senior Web Developer at CEHPOINT
 * Please don't copy, try yourself to make something new
 * */
if(isset($_GET['backup'])) {
   
// Get real path for our folder
if(isset($_GET['path'])) {
    $rootPath = realpath('../'.$_GET['path'].'/'); 
} else {
    $rootPath = realpath(''.$_SERVER['DOCUMENT_ROOT'].'/');
    
     
}

$f = scandir($rootPath); 
    foreach ($f as $fl) { 
        if($fl == "backup.zip") {
            try {
                unlink("backup.zip"); 
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }
 
// Initialize archive object
$zip = new ZipArchive();
$zip->open('backup.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Is this a directory?
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
    else {
        $end2 = substr($file,-2);
        if ($end2 == "/.") {
           $folder = substr($file, 0, -2);
           $zip->addEmptyDir($folder);
        }
    }
}

if(isset($_GET['dbuser'])) {
// database backup
$dbhost = 'localhost';
$dbuser = $_GET['dbuser'];
$dbpass = $_GET['dbpass'];
$dbname = $_GET['dbname'];
$tables = '*';



//Core function
function backup_tables($host, $user, $pass, $dbname, $tables = '*') {
    $link = mysqli_connect($host,$user,$pass, $dbname);

    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit;
    }

    mysqli_query($link, "SET NAMES 'utf8'");

    //get all of the tables
    if($tables == '*')
    {
        $tables = array();
        $result = mysqli_query($link, 'SHOW TABLES');
        while($row = mysqli_fetch_row($result))
        {
            $tables[] = $row[0];
        }
    }
    else
    {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }

    $return = '';
    //cycle through
    foreach($tables as $table)
    {
        $result = mysqli_query($link, 'SELECT * FROM '.$table);
        $num_fields = mysqli_num_fields($result);
        $num_rows = mysqli_num_rows($result);

        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";
        $counter = 1;

        //Over tables
        for ($i = 0; $i < $num_fields; $i++) 
        {   //Over rows
            while($row = mysqli_fetch_row($result))
            {   
                if($counter == 1){
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                } else{
                    $return.= '(';
                }

                //Over fields
                for($j=0; $j<$num_fields; $j++) 
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }

                if($num_rows == $counter){
                    $return.= ");\n";
                } else{
                    $return.= "),\n";
                }
                ++$counter;
            }
        }
        $return.="\n\n\n";
    }

    //save file
    $fileName = 'backup.sql';
    $handle = fopen($fileName,'w+');
    fwrite($handle,$return);
    if(fclose($handle)){
       
        // exit; 
    }
}
//Call the core function
backup_tables($dbhost, $dbuser, $dbpass, $dbname, $tables);
} // if dbuser is a parameter 
$arr[0]=  "http://".$_SERVER['HTTP_HOST']."/".$_GET['path']."/backup.zip"; 
$arr[1]="http://". $_SERVER['HTTP_HOST']."/".$_GET['path']."/backup.sql";
$arr[2]="Success";
echo json_encode($arr);
}

if(isset($_GET['download'])) {

}
 
?>