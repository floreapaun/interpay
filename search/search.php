<?php

    $host        = "host = 127.0.0.1";
    $port        = "port = 5432";
    $dbname      = "dbname = interpay";
    $credentials = "user = postgres password=4137";

    $db = pg_connect( "$host $port $dbname $credentials"  );
    if(!$db) {
        echo "Error : Unable to open database<br>";
    } 

	//check whether user types a search string and processing it
	if(isset($_POST["query"]))
	{
		$sql="SELECT authors.name AS aname, books.name AS bname FROM authors 
            INNER JOIN books ON books.author_id = authors.id 
            WHERE authors.name LIKE '%" . $_POST["query"] . "%'";

        $ret = pg_query($db, $sql);

        if($ret)
        {
            $output = '';
            $output .= "<table id='response_table'>";
            if(pg_num_rows($ret))
            {
                while($row = pg_fetch_array($ret)) 
                {
                    $output .= "<tr>";
                    $output .= "<td>" . $row['aname'] . '</td>';
                    $output .= "<td>" . $row['bname'] . '</td>';
                    $output .= "</tr>";
                }
                $output .= "</table>";
                echo $output;
    
            }
            else
            {
                echo 'No Data For This Id';
            }
        }
        else
        {
            echo 'Result Error';
        }
    }
	
?>
