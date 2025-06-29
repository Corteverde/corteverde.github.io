<?php
$TOKEN = 'aeef09f92c1b4b3b820f4bea6c4b3b3e';

function setResponseHeaders() {
  header("Content-Type: application/json; charset=UTF-8");

  header('X-Custom-Author: Corteverde');

  header('Cache-Control: max-age=800');
}

function cors() {
  // Specify domains from which requests are allowed
  header('Access-Control-Allow-Origin: *');

  // Specify which request methods are allowed
  header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

  // Specify which request methods are allowed
  header('Allow: PUT, GET, POST, DELETE, OPTIONS');

  // Additional headers which may be sent along with the CORS request
  header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');

  // Set the age to 1 day to improve speed/caching.
  header('Access-Control-Max-Age: 86400');

  // Exit early so the page isn't fully loaded for options requests
  if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
    exit();
  }
  if (strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
    $result['status'] = 'error';
    $result['description'] = 'Method not allowed.';
    http_response_code(405);
    echo json_encode($result);
    exit();
  }
}

setResponseHeaders();
cors();

$_POST = json_decode(file_get_contents('php://input'), true);

$from = 'web@corteverde.it';
$email = $_POST['email'];
$name = $_POST['name'];
$text = $_POST['message'];
$privacy = $_POST['privacy'];
$apikey = $_POST['apikey'];
$to ="corte@corteverde.it";
$bcc ="rmarchet@gmail.com";

$subj = "Email dal sito corteverde.it - Modulo Contatti";

if ($apikey != $TOKEN) {
  $result['status'] = 'error';
  $result['description'] = 'Invalid token.';
  $result['payload'] = $apikey;
  http_response_code(401);
  echo json_encode($result);
} else if (!isset($email) or !isset($text) or !isset($name) or !isset($privacy)) {
  $result['status'] = 'error';
  $result['description'] = 'One or more required fields are not filled.';
  $result['payload'] = $_POST;
  http_response_code(400);
  echo json_encode($result);
} else {
  $msg = "Messaggio inviato dalla form di contatti del sito www.corteverde.it.\n".
    "\nEmail mittente: ".$email.
    "\nNome mittente: ".$name.
    "\nMessaggio: ".$text;

  $headers = 'From: '.$from.PHP_EOL.
    'Reply-To: '.$email.PHP_EOL.
    'BCC: '.$bcc.PHP_EOL.
    'X-Mailer: PHP/' . phpversion() .PHP_EOL;

  if(mail($to, $subj, $msg, $headers)) {
    $result['status'] = 'success';
    $result['to'] = $to;
    http_response_code(200);
    echo json_encode($result);
  } else {
    $result['status'] = 'error';
    $result['description'] = 'Error while trying to send the email.';
    http_response_code(500);
    echo json_encode($result);
  }
}
?>