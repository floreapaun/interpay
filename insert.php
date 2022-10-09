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

    function tableExists($pdo, $table) {
        $table = preg_replace('/[^\da-z_]/i', '', $table);
        try {
            $result = $pdo->query("SELECT 1 FROM {$table} LIMIT 1");
        } catch (Exception $e) {
            return FALSE;
        }
        return $result !== FALSE;
    }

    $sql = <<<EOF
        CREATE TABLE authors
        (id SERIAL PRIMARY KEY,
        name TEXT NOT NULL);
    EOF;
    if (!tableExists($pdo, "authors"))
        $pdo->prepare($sql)->execute();

    $sql = <<<EOF
        CREATE TABLE books
        (id SERIAL PRIMARY KEY,
        author_id INT NOT NULL,
        name TEXT NOT NULL,
        CONSTRAINT fk_author FOREIGN KEY(author_id) REFERENCES authors(id));
    EOF;
    if (!tableExists($pdo, "books"))
        $pdo->prepare($sql)->execute();

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

        //Check if author already inserted into table
        $sql = <<<EOF
            SELECT * FROM authors WHERE name = ?;
        EOF;

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $booksArr['book'][$i]['author'], PDO::PARAM_STR);
        $stmt->execute();
        $author_row = $stmt->fetch();

        if ($author_row)
            $last_author_id = $author_row['id'];
        else {
            $sql = <<<EOF
                INSERT INTO authors (name) VALUES (?);
            EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $booksArr['book'][$i]['author'], PDO::PARAM_STR);
            $stmt->execute();
            echo "Author record created successfully!<br>";

            $sql = "SELECT * FROM authors ORDER BY id DESC LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $author_row = $stmt->fetch();
            $last_author_id = $author_row['id'];
        }
        
        //Check if book inserted into table
        $sql = <<<EOF
            SELECT * FROM books WHERE name = ?;
        EOF;
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $booksArr['book'][$i]['name'], PDO::PARAM_STR);
        $stmt->execute();
        $book_row = $stmt->fetch();

        //If book does not exist then insert into table
        if (!$book_row) {
            $sql = <<<EOF
                INSERT INTO books (author_id, name) VALUES (?, ?);
            EOF;
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $last_author_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $booksArr['book'][$i]['name'], PDO::PARAM_STR);
            $stmt->execute();
            echo "Book record created successfully!<br>";
        }
    }

    $pdo = null;
    $stmt = null;

?>