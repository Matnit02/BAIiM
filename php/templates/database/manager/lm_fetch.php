<?php
header('Content-Type: application/json');
// query select data from db
$access_users_select = $db->prepare('SELECT access.eid, login, access.delegated_manager_id, access.manager_id, access.name, access.email, access.training_date1, access.training_date2, access.comments, requests.bid, requests.bsid, requests.request_date, requests.manager_comment, requests.building3, requests.building2, requests.building1, requests.room4, requests.room3, requests.room2, requests.room1 FROM access INNER JOIN requests ON access.eid = requests.eid WHERE requests.status=2 AND ( access.manager_id=:eid OR access.delegated_manager_id=:eid_s )');
$access_users_select->bindValue(':eid_s', $_SESSION['data']['eid'], PDO::PARAM_STR);
$access_users_select->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);

$access_users_select->execute();

$data = array();
// fetch records about users for query to json table
foreach($access_users_select as $row) {

    $user_request = [
        'eid' => $row['eid'],
        'name' => $row['name'],
        'login' => $row['login'],
        'email' => $row['email'],
        'comments' => $row['comments'],
        'bid' => $row['bid'],
        'request_date' => $row['request_date'],
        'manager_comment' => $row['manager_comment'],
        
        'training_date1' => $row['training_date1'],
        'training_date2' => $row['training_date2'],
        'bsid' => $row['bsid'],

        'building3' => $row['building3'],
        'building2' => $row['building2'],
        'building1' => $row['building1'],
        'room4' => $row['room4'],
        'room3' => $row['room3'],
        'room2' => $row['room2'],
        'room1' => $row['room1'],
    ];
    $output[] = $user_request;
}
if(isset($output)){
    $data['data'] = $output;
    
} else {
    $data['data'] = [];
}
exit(json_encode($data));



