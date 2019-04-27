<?php

// how much test rows needed
define("COUNT_TEST_ROWS", 11);
// maximum message length (minimum = MESSAGE_MAX_LENGTH / 6)
define("MESSAGE_MAX_LENGTH", 1024);
// maximum keywords (minimum = KEYWORDS_LIMIT / 2)
define("KEYWORDS_LIMIT", 15);
// minimum keyword length
define("KEYWORD_MIN_LENGTH", 7);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/PDOconn.php';

// require the Faker autoloader
// https://github.com/fzaninotto/faker
use Faker\Factory;
//require_once 'vendor/fzaninotto/faker/src/autoload.php';

// require Aura.SqlQuery autoloader
// https://github.com/auraphp/Aura.SqlQuery
use Aura\SqlQuery\QueryFactory;

// template engine
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/template');
$twig = new \Twig\Environment($loader);

// cache in debug mode! Stopped.
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__ .'/cache', 'debug'=>true,
]);

createTables();

// create a Faker instance
$faker = Faker\Factory::create('ru_RU');

// PDO connection
$pdo = new PDOconn();
// init query factory
$queryFactory = new QueryFactory('Mysql');

// prepare insert transaction
$insert = $queryFactory->newInsert();
$htmlTestRows = "";
$goodRows = 0;
$badRows = 0;
$time_start = microtime(true);

for ($i = 0; $i < COUNT_TEST_ROWS; $i++) {
    $arFaker = createFakeData($faker);
    if($arFaker != null){
        $arFaker['keywords'] = "";
        $keywordsCnt = 0;
        do {
            $keyword = $arFaker['words'][rand(0, count($arFaker['words'])-1)];
            if(mb_strlen($keyword, 'UTF-8') >= KEYWORD_MIN_LENGTH) {
                $arFaker['keywords'] .= $keyword . ", ";
                $keywordsCnt++;
            }
        } while ($keywordsCnt < rand(round(KEYWORDS_LIMIT / 2), KEYWORDS_LIMIT));
        $arFaker['keywords'] = substr($arFaker['keywords'], 0, -2);
        // random created date and time
        $created = $faker->dateTimeThisYear($max = 'now', $timezone = null);//->format('d.m.Y - H:m:s');

        $htmlTestRows .= "<p>" . PHP_EOL;
        $htmlTestRows .= "<b>Username:</b> " . $arFaker['username'] . "<br/>" . PHP_EOL;
        $htmlTestRows .= "<b>E-Mail:</b> " . $arFaker['address'] . "<br/>" . PHP_EOL;
        $htmlTestRows .= "<b>Homepage:</b> " . $arFaker['homepage'] . "<br/>" . PHP_EOL;
        $htmlTestRows .= "<b>Message:</b> " . $arFaker['message'] . "<br/>" . PHP_EOL;
        $htmlTestRows .= "<b>Tags:</b> " . $arFaker['keywords'] . "<br/>" . PHP_EOL;
        $htmlTestRows .= "<b>Created:</b> " . $created->format('d.m.Y - G:i:s') . "<br/>" . PHP_EOL;
        $htmlTestRows .= "<p/>" . PHP_EOL;

        // insert into this table
        $insert->into('D_GUESTBOOK');
        $insert
            ->into('D_GUESTBOOK')      // INTO this table
            ->cols([                        // bind values as "(col) VALUES (:col)"
                'username' => $arFaker['username'],
                'email' => $arFaker['address'],
                'homepage' => $arFaker['homepage'],
                'text' => $arFaker['message'],
                'tags' => $arFaker['keywords'],
                'create_date' => $created->format('Y-m-d G:i:s')
            ]);
        // set up Multiple rows ...
        //$insert->addRow();
        // prepare transaction and execute all rows
        $sth = $pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());
        $goodRows++;
    } else {
        $badRows++;
        // 1000 errors its very bad... infinity loop? Nope. Break.
        if($badRows >= 1000) {
            break;
        }
    }
}


//$sth->rowCount()

$status = "Генерация тестовых данных завершена за " . number_format((float)microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2, '.', '') . " сек. Количество добавленных записей: " . $goodRows . ", ошибочных генераций: " . $badRows;

/*$select = $queryFactory->newSelect();
$select->cols([
    count('id')
])->from('D_GUESTBOOK') ;

// prepare the statment
$sth = $pdo->prepare($select->getStatement());
// bind the values and execute
$sth->execute($select->getBindValues());
// get the results back as an associative array
$result = $sth->fetch(PDO::FETCH_ASSOC);
var_dump($result);*/


$body = $status . "<br/>" . $htmlTestRows;
$response = $body;

//echo $response;

$template = $twig->load('main.html');
echo $template->render([
    'demo' => true,
    'status' => $status
]);
//echo $twig->render('main.html', ['messages' => $messages]);


function createFakeData($faker) {
    $arFaker = null;
    // random name
    $arFaker['username'] = $faker->word . $faker->word . $faker->numberBetween($min = 1954, $max = 2001);
    // random address
    $arFaker['address'] = $faker->freeEmail;
    // random Homepage
    $arFaker['homepage'] = $faker->optional($weight = 0.4)->domainName;
    // random text
    $arFaker['message'] = $faker->realText($maxNbChars = rand(round(MESSAGE_MAX_LENGTH / 6), MESSAGE_MAX_LENGTH));
    // random tags, with utf-8
    $wordsPattern = "~[^\p{L}\\'\-\\xC2\\xAD]+~u";
    $arFaker['words'] = preg_split($wordsPattern, $arFaker['message'], -1, PREG_SPLIT_NO_EMPTY);
    if(is_null($arFaker['message']) || strlen($arFaker['message']) < 10 || is_null($arFaker['username']) || is_null($arFaker['address'])){
        return null;
    }
    return $arFaker;
}

function createTables() {
    $pdo = new PDOconn();

    $sqlCreateTableGuestBook = <<<SQL
    DROP TABLE IF EXISTS `D_GUESTBOOK`;
    
    SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
    SET time_zone = "+00:00";
    
    /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
    /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
    /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
    /*!40101 SET NAMES utf8 */;
    
    --
    -- Структура таблицы `D_GUESTBOOK`
    --
    
    CREATE TABLE IF NOT EXISTS `D_GUESTBOOK` (
    `id` int(11) NOT NULL,
      `user_id` int(11) DEFAULT NULL COMMENT 'When User schon registriert ist',
      `username` varchar(64) NOT NULL DEFAULT 'NULL' COMMENT 'Anmeldename des Benutzers',
      `email` varchar(64) NOT NULL DEFAULT 'NULL' COMMENT 'E-Mail-Adresse des Kontos',
      `homepage` varchar(128) DEFAULT NULL COMMENT 'Web-site des Benutzers',
      `text` varchar(2048) NOT NULL DEFAULT 'NULL' COMMENT 'Nachricht, die Benutzer eingegeben',
      `tags` varchar(256) DEFAULT NULL COMMENT 'die Stichwörter',
      `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Datum, wann die Nachricht hinzugefügt',
      `is_approved` int(1) NOT NULL DEFAULT '1' COMMENT '1 - When Daten akzeptiert sind'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The Guestbook';
    
    --
    -- Индексы сохранённых таблиц
    --
    
    --
    -- Индексы таблицы `D_GUESTBOOK`
    --
    ALTER TABLE `D_GUESTBOOK`
     ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);
    
    --
    -- AUTO_INCREMENT для сохранённых таблиц
    --
    
    --
    -- AUTO_INCREMENT для таблицы `D_GUESTBOOK`
    --
    ALTER TABLE `D_GUESTBOOK`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
    --
    -- Ограничения внешнего ключа сохраненных таблиц
    --
    
    --
    -- Ограничения внешнего ключа таблицы `D_GUESTBOOK`
    --
    ALTER TABLE `D_GUESTBOOK`
    ADD CONSTRAINT `D_GUESTBOOK_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `S_USERS` (`id`);
    
    /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
    /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
    /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
    SQL;

    $crtResult = $pdo->query($sqlCreateTableGuestBook);
    //var_dump($crtResult);
}