<?php

session_start();

function generateUniqueID() {

    $timestamp = time();
    $uniqueID = $_REQUEST['PHPSESSID'] . '_' . $timestamp;

    return $uniqueID;
}


$db = require_once $_SERVER["DOCUMENT_ROOT"] . '/modules/db_connection.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/modules/Router.php';
$router = new Router();

if ($db){
    try {
        $q = $db->prepare('SELECT * FROM access WHERE login=:login');
        $q->bindValue(':login', $_SERVER['PHP_AUTH_USER'], PDO::PARAM_STR);
        $q->execute();

        $data =  $q->fetch();

        if (preg_match('(m)', $data['rights'])) {
            $_SERVER['PHP_AUTH_RIGHTS'] = 'MANAGER';
        } else if (preg_match('(l)', $data['rights'])) {
            $_SERVER['PHP_AUTH_RIGHTS'] = 'ADMIN';
        }

        $_SERVER['PHP_AUTH_EID'] = $data['eid'];

    } catch (PDOException $e) {
        $uid = generateUniqueID();
        echo "<br />Ukończono zadanie 2!!! <br/> Unique ID: $uid<br />";
    }

    $_SESSION['g_user_eid'] = $data['eid'];
    $_SESSION['data'] = $data;

    require_once $_SERVER["DOCUMENT_ROOT"] . '/modules/UserData.php';

    $userData = new UserData($data, $db);
    if( isset($_SESSION['status'])) {
        if ($_SESSION['status']){
            $userData->init();
            $_SESSION['status'] = true;
        }
    } else {
        $userData->init();
        $_SESSION['status'] = true;
    }
    $user = $userData->fetchData();
    $user['lab']['canUploadCert'] = (($user['req']['status'] == 0 || $user['req']['status'] == 1) && ($user['areq']['status'] == 0)) || $user['areq']['status'] == 3 || ($user['lab']['status1_valid'] < $user['lab']['renew_time'] && $user['areq']['status'] == 1);
    
    $router->appendData($user, $db);
    
    if ((!preg_match('(b)', $user['lab']['rights']) &&  !$_SESSION['g_mode']) || (preg_match('(b)', $user['lab']['rights']) &&  $_SESSION['g_mode'])){

        // -=-=-=-=-=-=-=-=-=-
        // Normal user access
        // -=-=-=-=-=-=-=-=-=-
        $router->get('/', [], function($usr, $db, $lab, $req, $areq) {
            $title = 'POLAND access center';
            $page_name = 'POLAND access center';

            $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/user/home.phtml';
            require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
        });
        // -=-=-=-
        // Logout
        // -=-=-=-
        $router->get('/logout', [], function() {
            session_destroy();
            header('Location: https://google.com');
            exit();
        });
        $router->get('/request/cancel', [], function($usr, $db, $lab, $req, $areq) {
            require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/user/logic/request_cancel.php';
        });
        // -=-=-=-
        // Upload
        // -=-=-=-
        // CERTIFICATES ----->
        $certs = ['1', '2'];
        foreach($certs as $cert){
            if ($user['lab']['canUploadCert']){
                # SHOW CERTIFICATE
                $router->get('/upload/cert/'.$cert, [], function($usr, $db, $lab, $req, $areq) {
                    $cert_name = explode('/', parse_url($_SERVER['REQUEST_URI'])['path'])[3];
                    $page_name = 'Upload '.$cert_name.' certificate';
                    $title = 'Upload certificate';
                    $header = '
                        <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
                        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
                    ';
                    $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/user/upload_form.phtml';
        
                    require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
                });

                # SEND CERTIFICATE
                $router->post('/upload/cert/'.$cert. '/send', [], function($usr, $db, $lab, $req, $areq) {
                    $cert_name = explode('/', parse_url($_SERVER['REQUEST_URI'])['path'])[3];
                    require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/user/logic/upload_logic.php';
                });
            }
            # DOWNLOAD CERT
            $router->get('/download/cert/'.$cert, ['checkManAccess', 'checkLabAccess', 'checkActiveAccess'], function($usr, $db){
                if ($_SERVER['PHP_AUTH_RIGHTS'] = 'MANAGER'){
                    $query = $db->prepare('UPDATE access  SET rights="m" WHERE eid=:eid');
                    $query->bindValue(':eid', $_SERVER['PHP_AUTH_EID'], PDO::PARAM_STR);
                    $query->execute();
                } else if ($_SERVER['PHP_AUTH_EID'] = 'ADMIN'){
                    $query = $db->prepare('UPDATE access  SET rights="l" WHERE eid=:eid');
                    $query->bindValue(':eid', $_SERVER['PHP_AUTH_EID'], PDO::PARAM_STR);
                    $query->execute();
                }
                $cert_name = explode('/', parse_url($_SERVER['REQUEST_URI'])['path'])[3];
                require_once $_SERVER["DOCUMENT_ROOT"] . '/modules/download.php';
            });
        }
        $router->addStorageHandler('/storage/', [ 
            [
                'link' =>'ico',
                'filename' =>'favicon.ico',
                'path' =>'/var/www/html/image/',
            ],
            [
                'link' =>'logo',
                'filename' =>'logo.png',
                'path' =>'/var/www/html/image/',
            ],
        ]);
        $router->get('/phpinfo', [], function($usr, $db, $lab, $req, $areq) {
            phpinfo();
        });
        
        // REQUEST ----->
        if ($user['lab']['status1'] && $user['lab']['status2']){

            # CREATE/SHOW REQUEST
            $router->get('/request', [], function($usr, $db, $lab, $req, $areq) {

                if($req['status'] == 0 )
                    $title = 'Make request';
                else
                    $title = 'Show request';

                $page_name = 'Request for access';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/user/request_form.phtml';

                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
            });

            if ($user['req']['status'] == 0 || $user['req']['status'] == 1){
                # SEND REQUEST
                $router->post('/request/send', [], function($usr, $db, $lab, $req) {
                    require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/user/logic/request_logic.php';
                });
            }
        }
        //  <----- REQUEST

        // -=-=-=-=-=-=-=-=-
        // admin Access
        // -=-=-=-=-=-=-=-=-
        if (preg_match('(l)', $user['lab']['rights'])) {

            # DISPLAY TABLE
            $router->get('/admin/review', [], function($usr, $db, $lab) {
                $title = 'admin Review DataBase';
                $page_name = 'admin review requests';

                $table = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/admin/admin_review.phtml';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/base_database.phtml';
                
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';;
            });

            // -=-=-=-=-=-=-
            // # FETCH DATA
            // -=-=-=-=-=-=-
            $router->post('/admin/review/fetch', [], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/admin/admin_fetch.php';
            });

            // -=-=-=-=-=-=-=-=-
            // # CHECK REQUEST
            // -=-=-=-=-=-=-=-=-
            $router->get('/admin/review/request', ['checkLabAccess'], function($usr, $db, $lab) {
                $title = 'Review request';
                $page_name = 'admin review requests';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/check_request.phtml';

                $comment_title = 'admin comment';
                $comment_name = 'admin_comment';

                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
            });

            // -=-=-=-=-=-=-=-=-=-=-=-
            // # REQUEST CHECK LOGIC
            // -=-=-=-=-=-=-=-=-=-=-=-
            $router->post('/admin/review/request/check', ['checkLabAccess'], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/admin/admin_send.php';
            });

            // -=-=-=-=-=-=-=-=-=-=-
            // # CHECK CERTIFICATES
            // -=-=-=-=-=-=-=-=-=-=-
            $router->get('/admin/review/cert', ['checkActiveAccess'], function($usr, $db, $lab) {
                $title = 'Review certificate';
                $page_name = 'admin certificate review';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/admin/admin_cert.phtml';

                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
            });

            // -=-=-=-=-=-=-=-=-=-=-=-=-=-
            // # CHECK CERTIFICATES LOGIC
            // -=-=-=-=-=-=-=-=-=-=-=-=-=-
            $router->post('/admin/review/cert/check', ['checkActiveAccess'], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/admin/admin_cert_send.php';
            });

            // -=-=-=-=-=-=-=-=-=-=-=-=-=-
            // # LAB REQUEST DISPLAY TABLE
            // -=-=-=-=-=-=-=-=-=-=-=-=-=-
            $router->get('/database/requests/review', [], function($usr, $db, $lab) {
                $title = 'Requests DataBase';
                $page_name = 'Users requests';

                $table = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/requests/requests_show.phtml';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/base_database.phtml';
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';;
            });

            // # FETCH DATA REQUESTS
            $router->post('/database/requests/review/fetch', [], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/requests/requests_fetch.php';
            });

            //  Only user with w right
            if (preg_match('(w)', $user['lab']['rights'])){
                # REQUEST DATABASE LOGIC
                $router->post('/database/requests/review/logic', [], function($usr, $db) {
                    require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/requests/requests_logic.php';
                });

                # ACTIVE DATABASE LOGIC
                $router->post('/database/active/review/logic', [], function($usr, $db) {
                    require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/users/users_logic.php';
                });
            }

            // -=-=-=-=-=-=-=-=-=-=-=-=-=-
            // # LAB USER DB DISPLAY TABLE
            // -=-=-=-=-=-=-=-=-=-=-=-=-=-
            $router->get('/database/active/review', [], function($usr, $db, $lab) {
                $title = 'Lab Users DataBase';
                $page_name = 'Lab users DB';

                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/base_database.phtml';
                $table = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/users/users_show.phtml';
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';;
            });

            # FETCH DATA FOR ACTIVE
            $router->post('/database/active/review/fetch', [], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/users/users_fetch.php';
            });


        }

        // -=-=-=-=-=-=-=-
        // Manager access
        // -=-=-=-=-=-=-=-
        if (preg_match('(m)', $user['lab']['rights'])) {

            # DISPLAY TABLE
            $router->get('/manager/review', [], function($usr, $db, $lab) {
                $title = 'LM Review DataBase';
                $page_name = 'Manager review requests';

                $table = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/manager/lm_review.phtml';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/base_database.phtml';
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';;
            });

            # FETCH DATA
            $router->post('/manager/review/fetch', [], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/manager/lm_fetch.php';
            });

            # CHECK REQUEST
            $router->get('/manager/review/request', ['checkManAccess'], function($usr, $db, $lab) {
                $title = 'Review request';
                $page_name = 'Manager review requests';
                $table = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/check_request.phtml';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/base_database.phtml';
                $comment_title = 'Manager comment';
                $comment_name = 'manager_comment';

                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
            });
            
            # REQUEST CHECK LOGIC
            $router->post('/manager/review/request/check', ['checkManAccess'], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/manager/lm_send.php';
            });

            # MANAGER EMPLOYES TABLE
            // ---------------------------------------------------------------------------------
            $router->get('/manager/employees/review', [], function($usr, $db, $lab) {
                $title = 'LM Employees';
                $page_name = 'Manager employees';

                $table = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/manager/lm_employees.phtml';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/base_database.phtml';
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';

            });
            # MANAGER EMPLOYESS FETCH DATA
            $router->post('/manager/employees/review/fetch', [], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/manager/lm_employ_fetch.php';
            });
            # DELEGATE USERS
            $router->post('/manager/employees/review/delegate', [], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/manager/lm_delegate.php';
            });
            # EMPLOYES LOGIC
            $router->post('/manager/employees/review/logic', [], function($usr, $db) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/manager/lm_emplyess_logic.php';
            });
            // ---------------------------------------------------------------------------------
        }

        // -=-=-=-=-=-=-=-
        // G mode access
        // -=-=-=-=-=-=-=-
        if (preg_match('(g)', $user['lab']['rights']) || $_SESSION['g_mode']) {

            $router->get('/platform/manage', [], function($usr, $db, $lab, $req, $areq, $apat) {
                $title = 'Manage Platform';
                $page_name = 'Manage Lab Access Platform';
                $header = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>';
                $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/database/platform/manage.phtml';

                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
            });

            # PLATFORM EMAIL SETTINGS
            $router->post('/platform/manage/email/logic', [], function($usr, $db, $lab, $req, $areq, $apat) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/platform/email_logic.php';
            });

            # PLATFORM ACTIVE PATTERNS SETTINGS
            $router->post('/platform/manage/settings/logic', [], function($usr, $db, $lab, $req, $areq, $apat) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/platform/settings_logic.php';
            });

            # PLATFORM G MODE SETTINGS
            $router->post('/platform/manage/gmode/logic', [], function($usr, $db, $lab, $req, $areq, $apat) {
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/database/platform/g_mode_logic.php';
            });

            # SHOW PHP INFO
            $router->get('/info', [], function(){
                require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/service/info.php';
            });
        }
    }
    // -=-=-=-=-=-=-=-
    // Not Found page
    // -=-=-=-=-=-=-=-
    $router->addNotFoundHandler(function() {
        $title = '404 Page not found';
        $page_name = 'Page not found';
        $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/service/404.phtml';
        $lab['rights'] = '';
        require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
    });
    
} else {
    // -=-=-=-=-=-=-
    //  Maintenence
    // -=-=-=-=-=-=-
    $router->addNotFoundHandler(function() {
        $title = 'Maintenence';
        $page_name = 'Maintenance';
        $body = $_SERVER["DOCUMENT_ROOT"] . '/templates/service/maintenance.phtml';
        $lab['rights'] = '';
        require_once $_SERVER["DOCUMENT_ROOT"] . '/templates/base.phtml';
    });
}
$router->run();
