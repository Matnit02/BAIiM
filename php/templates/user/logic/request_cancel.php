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


header('Location: /');