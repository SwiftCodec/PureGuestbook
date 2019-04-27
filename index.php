<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/PDOconn.php';

// require Aura.SqlQuery autoloader
// https://github.com/auraphp/Aura.SqlQuery
use Aura\SqlQuery\QueryFactory;

// init query factory
$queryFactory = new QueryFactory('Mysql');

// template engine
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/template');
$twig = new \Twig\Environment($loader);

// cache in debug mode! Stopped.
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__ .'/cache', 'debug'=>true,
]);

$action = (isset($_GET['action']) ? $_GET['action'] : null);
switch ($action) {
    case 'message_add':

/*
      'tags' => string 'ацууца' (length=12)
      'checkRules' => string 'on' (length=2)*/

        $username = (isset($_POST['username']) ? $_POST['username'] : "");
        $email = (isset($_POST['email']) ? urldecode($_POST['email']) : "");
        $message = (isset($_POST['message']) ? strip_tags(urldecode($_POST['message'])) : "");
        $homepage = (isset($_POST['homepage']) ? urldecode($_POST['homepage']) : "");
        $tags = (isset($_POST['tags']) ? urldecode($_POST['tags']) : "");

        if($username == "" || $email == "" || $message == "") {
            // TODO Ajax json
            echo "data_required";
            return;
        } else if(!(bool)preg_match('/^[A-z0-9]{1,}$/', $username) || (strlen($username) < 8 || strlen($username) > 32)) {
            echo "invalid_username";
            return;
        } else if(!(bool)preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email) || strlen($email) > 64) {
            echo "invalid_email";
            return;
        } else if(strlen($message) < 10 || strlen($message) > 2048) {
            echo "invalid_message";
            return;
        } else if(strlen($homepage) > 128) {
            echo "invalid_homepage";
            return;
        } else if(strlen($tags) > 256) {
            echo "invalid_tags";
            return;
        }

        // PDO connection
        $pdo = new PDOconn();
        // init query factory
        $queryFactory = new QueryFactory('Mysql');
        // prepare insert transaction
        $insert = $queryFactory->newInsert();
        // insert into this table
        $insert->into('D_GUESTBOOK');
        $insert
            ->into('D_GUESTBOOK')      // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'username' => $username,
                'email' => $email,
                'homepage' => $homepage,
                'text' => $message,
                'tags' => $tags,
                'create_date' => date_create()->format('Y-m-d H:i:s')
            ]);
        // prepare transaction and execute all rows
        $sth = $pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());


        //var_dump($_POST);



        echo "success";
        break;

    default:

        // PDO connection
        $pdo = new PDOconn();

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


        echo $twig->render('main.html', [
            'message' => true,
            'message_add' => true,
            'messages' => (isset($messages) ? $messages : null)
        ]);

        break;
}

