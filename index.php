<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/PDOconn.php';

// require Aura.SqlQuery autoloader
// https://github.com/auraphp/Aura.SqlQuery
use Aura\SqlQuery\QueryFactory;

// PDO connection
$pdo = new PDOconn();
// init query factory
$queryFactory = new QueryFactory('Mysql');

// template engine
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/template');
$twig = new \Twig\Environment($loader);

// кеш пока отключим
/*$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__ .'/cache',
]);*/

$select = $queryFactory->newSelect();
//$pdo->exec('SET @row_number:=0;');

$select->cols([
    'id',
    'username',
    'email',
    'homepage',
    'text',
    'tags',
    'create_date',
    '@row_number:=@row_number+1 AS row_number'
])->from('D_GUESTBOOK') ;

// prepare the statment
$sth = $pdo->prepare($select->getStatement());
// bind the values and execute
$sth->execute($select->getBindValues());
// get the results back as an associative array
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

var_dump($sth);
//var_dump($result);


echo $twig->render('main.html', ['messages' => $result]);
