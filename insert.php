<?php
include "db_connection.php";

function tableExists($pdo, $table) {
    $table = preg_replace('/\W+/i', '', $table);
    $sql = "SELECT 1 FROM information_schema.tables 
    WHERE table_schema = 'interpay' AND table_name = ?";
    $stmt =  $pdo->prepare($sql);
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

$sql = "CREATE TABLE authors (id SERIAL PRIMARY KEY, name TEXT NOT NULL)";
if (!tableExists($pdo, "authors"))
    $pdo->prepare($sql)->execute();

$sql = "CREATE TABLE books 
(id SERIAL PRIMARY KEY,
author_id INT NOT NULL,
name TEXT NOT NULL,
CONSTRAINT fk_author FOREIGN KEY(author_id) REFERENCES authors(id))";
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

foreach ($booksArr['book'] as $book) {

    //Check if author already inserted into table
    $sql = "SELECT * FROM authors WHERE name = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $book['author'], PDO::PARAM_STR);
    $stmt->execute();
    $author_row = $stmt->fetch();

    if ($author_row)
        $last_author_id = $author_row['id'];
    else {
        $sql = "INSERT INTO authors (name) VALUES (?)";
               
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $book['author'], PDO::PARAM_STR);
        $stmt->execute();
        echo "Author record created successfully!<br>";

        $sql = "SELECT * FROM authors ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $author_row = $stmt->fetch();
        $last_author_id = $author_row['id'];
    }
    
    //Check if book inserted into table
    $sql = "SELECT * FROM books WHERE name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $book['name'], PDO::PARAM_STR);
    $stmt->execute();
    $book_row = $stmt->fetch();

    //If book does not exist then insert into table
    if (!$book_row) {
        $sql = "INSERT INTO books (author_id, name) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $last_author_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $book['name'], PDO::PARAM_STR);
        $stmt->execute();
        echo "Book record created successfully!<br>";
    }
}

$pdo = null;
$stmt = null;
