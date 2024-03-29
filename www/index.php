<?php
// /**
//  * Front controller
//  */
//
//
define('BASE_PATH', dirname(__DIR__));
require_once  BASE_PATH . '/source/bootstrap.php';


use CD\Core\Request;
use CD\Core\Router;
use CD\Core\View;

$request = new Request();
$view = new View();

$router = new Router();
$router->addRoute('manager', [], '@manager/routes');
$router->addRoute('admin', [], '@admin/routes');
$router->addRoute('auth', [], '@auth/routes');
$router->addRoute('become-one', ['controller' => 'Home', 'action' => 'become']);
$router->addRoute('sandbox', ['controller' => 'Sandbox']);
$router->dispatch($request, $view);


// class ParseCSV
// {
//     public static string $delimiter = ',';
//
//     private string $filename;
//     private ?array $header = null;
//     private ?array $data = null;
//     private int $row_count = 0;
//
//     public function __construct($filename)
//     {
//         if (!empty($filename)) {
//             $this->file($filename);
//         }
//     }
//
//     public function file($filename): bool
//     {
//         if (!file_exists($filename)) {
//             echo 'File does not exist.';
//             return false;
//         } else if (!is_readable($filename)) {
//             echo 'File is not readable.';
//             return false;
//         }
//         $this->filename = $filename;
//         return true;
//     }
//
//     public function parse()
//     {
//         if (!isset($this->filename)) {
//             echo 'File not set.';
//             return false;
//         }
//
//         // clear any previous results.
//         $this->reset();
//
//         $file = fopen($this->filename, 'r');
//         while (!feof($file)) {
//             $row = fgetcsv($file, 0, self::$delimiter);
//             if ($row == [null] || $row === false) continue;
//             if (!$this->header) {
//                 $this->header = $row;
//             } else {
//                 $this->data[] = array_combine($this->header, $row);
//                 $this->row_count++;
//             }
//         }
//         fclose($file);
//         $pdo = new \CD\Core\DB\DB();
//         foreach ($this->data as $row) {
//             $cols = join(', ', array_keys($row));
//             $vals = trim(str_repeat('?, ', count($row)), ', ');
//             $sql = "INSERT INTO ManagerPayouts ($cols) VALUES ($vals)";
//             // $x = join(', ', array_values($row));
//             // $sql = "INSERT INTO ManagerShares ($cols) VALUES ($x)";
//             // echo '<pre>';
//             // print_r($row);
//             // echo '</pre>';
//             // exit;
//             try {
//                 // echo $sql . '<br>';
//                 $s = $pdo->db()->prepare($sql);
//                 $s->execute(array_values($row));
//             } catch (TypeError | PDOException $error) {
//                 echo $error . '<br>';
//                 echo '<pre>';
//                 print_r($row);
//                 echo '</pre>';
//                 exit;
//             }
//         }
//         return $this->data;
//     }
//
//     public function row_count(): int
//     {
//         return $this->row_count();
//     }
//
//     public function last_results(): array
//     {
//         return $this->data;
//     }
//
//     private function reset()
//     {
//         $this->header = null;
//         $this->data = null;
//         $this->row_count = 0;
//     }
// }
//
// $p = new ParseCSV('ax.csv');
// $p->parse();
//
//$vals = [
//    381.07,
//237.12,
//237.12,
//949.13,
//519.61,
//237.12,
//474.61,
//514.66,
//1286.29,
//237.12,
//259.80,
//259.80,
//789.38,
//259.80,
//259.80,
//1236.68,
//1730.76,
//216.54,
//216.54,
//1298.07,
//952.30,
//762.13,
//190.53,
//190.53,
//190.53,
//381.07,
//190.53,
//381.07,
//304.85,
//474.61,
//135.01,
//404.78,
//1079.09,
//];
//  $pdo = new \CD\Core\DB\DB();
//  for ($i = 1; $i <= 34; $i++) {
//      if ($i == 28) {
//          continue;
//      }
//      $sql = "UPDATE ManagerAccounts SET ManagerAccountTotalSLPGained = :bal WHERE ManagerAccountID = :id";
//      $s = $pdo->db()->prepare($sql);
//      $s->bindValue(':bal', $vals[$i-1]);
//      $s->bindValue(':id', $i);
//      $s->execute();
//  }
