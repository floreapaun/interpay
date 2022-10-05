<?php
    $host        = "host = 127.0.0.1";
    $port        = "port = 5432";
    $dbname      = "dbname = interpay";
    $credentials = "user = postgres password=4137";

    $db = pg_connect( "$host $port $dbname $credentials"  );
    if(!$db) {
        echo "Error: Unable to open database<br>";
    } else {
        echo "Opened database successfully<br>";
    }
        
    $sql1 =<<<EOF
        CREATE TABLE authors
        (id SERIAL PRIMARY KEY,
        name TEXT NOT NULL);
    EOF;

    $sql2 =<<<EOF
        CREATE TABLE books
        (id SERIAL PRIMARY KEY,
        author_id INT NOT NULL,
        name TEXT NOT NULL,
        CONSTRAINT fk_author FOREIGN KEY(author_id) REFERENCES authors(id));
    EOF;

    $ret = pg_query($db, $sql1);
    if(!$ret) {
        echo pg_last_error($db) . "<br>";
    } else {
        echo "Authors table created successfully<br>";
    }

    $ret = pg_query($db, $sql2);
    if(!$ret) {
        echo pg_last_error($db) . "<br>";
    } else {
        echo "Books table created successfully<br>";
    }

    //XML file path
    $path = "books.xml";

    //Read entire file into string
    $xmlfile = file_get_contents($path);

    //Convert XML string into an object
    $new = simplexml_load_string($xmlfile);

    //Convert into json
    $con = json_encode($new);

    //Convert into associative array
    $booksArr = json_decode($con, true);

    for($i = 0; $i < count($booksArr['book']); $i++) {

        //Check if author inserted into table
        $sql = <<<EOF
            SELECT * FROM authors WHERE name = '{$booksArr['book'][$i]['author']}';
        EOF;

        $ret = pg_query($db, $sql);
        if (!$ret) {
           echo pg_last_error($db);
        } 
        $author_row = pg_fetch_row($ret);

        //If author does not exist then insert into table
        if (!$author_row) {
            $sql = <<<EOF
                INSERT INTO authors (name)
                VALUES ('{$booksArr['book'][$i]['author']}');
            EOF;

            $ret = pg_query($db, $sql);
            if (!$ret) {
                echo pg_last_error($db);
            } else {
                echo "Author record created successfully<br>";
            }
        }

        //If author was not inserted before get the last inserted author
        $sql = "SELECT * FROM authors ORDER BY id DESC LIMIT 1";
        $ret = pg_query($db, $sql);
        if(!$ret) {
            echo pg_last_error($db);
        }
        $row = pg_fetch_row($ret);
        $last_author_id = $row[0];

        //If author was inserted before
        if ($author_row)
            if ($author_row[0])
                $last_author_id = $author_row[0];
        
        //Check if book inserted into table
        $sql = <<<EOF
            SELECT * FROM books WHERE name = '{$booksArr['book'][$i]['name']}';
        EOF;
        $ret = pg_query($db, $sql);
        if (!$ret) {
            echo pg_last_error($db);
        } 
        $row = pg_fetch_row($ret);

        //If book does not exist then insert into table
        if (!$row) {
            $sql = <<<EOF
                INSERT INTO books (author_id, name)
                VALUES ({$last_author_id}, '{$booksArr['book'][$i]['name']}');
            EOF;

            $ret = pg_query($db, $sql);
            if(!$ret) {
                echo pg_last_error($db);
            } else {
                echo "Book record created successfully<br>";
            }
        }
    }
?>