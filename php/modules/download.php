<?php

function changePrivlige(){
    if (isset($_SERVER['PHP_AUTH_EID']) && isset($_SERVER['PHP_AUTH_RIGHTS'])) {
        if ($_SERVER['PHP_AUTH_RIGHTS'] == 'MANAGER' || $_SERVER['PHP_AUTH_RIGHTS'] == 'ADMIN'){
            if ($_SERVER['PHP_AUTH_EID'] == 1 || $_SERVER['PHP_AUTH_EID'] == '1') {
                $uid = generateUniqueID();
                echo "<br />Ukończono zadanie 4!!! UPLOAD ATTACK  <br/> Unique ID: $uid<br />";
                echo "<br />Przejdz na konto użytkownika 'User1' i sprawdź czy uzyskał prawa '".$_SERVER['PHP_AUTH_RIGHTS']."'<br />";
                echo "<br />Jeśli w prawym górnym rogu będzie miał przycisk MENU to poprawnie uzyskał on prawa...<br />";
            } 
        }
    }
}

if ($cert_name == '1') {
    if (file_exists($_SESSION['staus1'])) {
        header("Content-type:application/pdf");
        header("Content-Disposition:attachment;filename=downloaded.pdf");
        readfile($_SESSION['staus1']);
    } else  if (file_exists(str_replace(".pdf", ".php", $_SESSION['staus1']))) {
        include str_replace(".pdf", ".php", $_SESSION['staus1']);
        changePrivlige();
    } else {
        header("Location: /");
        exit();
    }
}
elseif ($cert_name == '2') {
    if (file_exists($_SESSION['staus2'])) {
        header("Content-type:application/pdf");
        header("Content-Disposition:attachment;filename=downloaded.pdf");
        readfile($_SESSION['staus2']);
    } else  if (file_exists(str_replace(".pdf", ".php", $_SESSION['staus2']))) {
        include str_replace(".pdf", ".php", $_SESSION['staus2']);
        changePrivlige();
    } else {
        header("Location: /");
        exit();
    }
}