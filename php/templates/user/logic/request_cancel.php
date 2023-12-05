<?php

$request_update = $db->prepare("UPDATE requests SET status=1, manager_comment=NULL, admin_comment=NULL, request_date=NULL, accept_manager_name=NULL WHERE eid =:eid");
$request_update->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);            
$request_update->execute(); 

$access_update = $db->prepare("UPDATE access SET status1=0, status2=0, training_date1=NULL, training_date2=NULL, delegated_manager_id=NULL WHERE eid =:eid");
$access_update->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);            
$access_update->execute();

$active_requests = $db->prepare("DELETE FROM active_requests WHERE eid =:eid");
$active_requests->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);            
$active_requests->execute();

        
function getCertLocation($cert){
    return '/var/www/certificates/'.$cert.'/'. $_SESSION['data']['eid'] . '_'.$cert.'.pdf';

}
function getCertLocationPHP($cert){
    return '/var/www/certificates/'.$cert.'/'. $_SESSION['data']['eid'] . '_'.$cert.'.php';
}
function removefile($file_path){
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

$file_path1 = getCertLocation('1');
$file_path1php = getCertLocationPHP('1');
$file_path2 = getCertLocation('2');
$file_path2php = getCertLocationPHP('2');

removefile($file_path1);
removefile($file_path1php);
removefile($file_path2);
removefile($file_path2php);

header('Location: /');