<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/PDOconn.php';

// require Aura.SqlQuery autoloader
// https://github.com/auraphp/Aura.SqlQuery
use Aura\SqlQuery\QueryFactory;

$action = isset($_GET['action']);
switch ($action) {
    case 'add':
        // TODO Ajax comment into DB
        break;

    default:

        // PDO connection
        $pdo = new PDOconn();
        // init query factory
        $queryFactory = new QueryFactory('Mysql');

        // template engine
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/template');
        $twig = new \Twig\Environment($loader);

        // cache in debug mode! Stopped.
        $twig = new \Twig\Environment($loader, [
            'cache' => __DIR__ .'/cache', 'debug'=>true,
        ]);

        /*
        $select = $queryFactory->newSelect();
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
        */

        $stmt = $pdo->query("SELECT id, username, email, homepage, text, tags, create_date, @row_number:=@row_number+1 AS row_num FROM `D_GUESTBOOK`, (SELECT @row_number:=0) AS t ORDER BY create_date DESC limit 100");
        $messages = $stmt->fetchAll();

        break;
}


echo $twig->render('main.html', [
    'message' => true,
    'messages' => $messages
]);
