<?php

require_once('php/utilityFunctions.php');
require_once('php/db.php');
require_once('php/error_management.php');
use DB\DBAccess;

session_start();

$htmlPage = file_get_contents("html/signup.html");


// // page header
// $pageHeader = file_get_contents("html/components/header.html");

// // page footer
// $pageFooter = file_get_contents("html/components/footer.html");

// // replace the placeholders
// $htmlPage = str_replace('<pageHeader/>', $pageHeader, $htmlPage);
// $htmlPage = str_replace('<pageFooter/>', $pageFooter, $htmlPage);


//header footer and dynamic navbar all at once
require_once('php/full_sec_loader.php');


$errors = '';

// check if the user press the submit button
if($_SERVER['REQUEST_METHOD'] == "POST"){

    // take the data
    $username       = $_POST['username'];
    $firstname      = $_POST['firstname'];
    $lastname       = $_POST['lastname'];
    $email          = $_POST['email'];
    $password       = $_POST['password'];
    $rep_password   = $_POST['repeated_password'];

    // create a connection istance to talk with the db
    $connection_manager = new DBAccess();
    $conn_ok = $connection_manager->openDBConnection();

    if($conn_ok){

        $errors = validateUserData($connection_manager, $username, $firstname, $lastname, $email, $password, $rep_password);
        if(!$errors){
            
            // if the username is valid 
            if($connection_manager->insertNewUser($username, $firstname, $lastname, $email, $password)){
                //echo "creazione utente";
                $connection_manager->closeDBConnection();
                
                $_SESSION['username'] = $username;
                //echo "utente creato";
                header("location: private_area.php");
            }
        }
    } else {
        $errors = createDBErrorHTML();
    }
}

// inserisce la lista degli errori o elimina il tag dalla pagina
$htmlPage = str_replace("<formErrors/>", $errors, $htmlPage);

// se l'utente ha già effettuato il login non deve visualizzare questa pagina
if(isset($_SESSION['username']) && $_SESSION['username'] != '') {             
    header("location: private_area.php");
}

//str_replace finale col conenuto specifico della pagina
echo $htmlPage;

?>