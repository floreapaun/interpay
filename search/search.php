<?php

    $dsn = "pgsql:dbname=interpay host=localhost";
    $options = [
        PDO::ATTR_EMULATE_PREPARES   => false, 
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
    ];
    try {
        $pdo = new PDO($dsn, "postgres", "4137", $options);
    } catch (Exception $e) {
        error_log($e->getMessage());
        exit('Fatal error!');
    }

	if (isset($_POST["query"])) {
		$sql = <<<EOF
            SELECT authors.name AS aname, books.name AS bname FROM authors 
            INNER JOIN books ON books.author_id = authors.id 
            WHERE authors.name LIKE ?;
        EOF;

        $name = "%{$_POST['query']}%";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $name, PDO::PARAM_STR);
        $stmt->execute();
        
        $output = "<table id='response_table'>";
        while ($row = $stmt->fetch()) {
            $output .= "<tr>";
            $output .= "<td>" . $row['aname'] . '</td>';
            $output .= "<td>" . $row['bname'] . '</td>';
            $output .= "</tr>";
        }
        if (strlen($output) < 28)
            echo "No authors found.";
        else {
            $output .= "</table>";
            echo $output;
        }          
    }

    $pdo = null;
    $stmt = null;
	
?>
