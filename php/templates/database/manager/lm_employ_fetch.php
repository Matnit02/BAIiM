<?php
header('Content-Type: application/json');
// query select data from db
$access_users_select = $db->prepare('SELECT requests.status AS st ,active_requests.status, access.eid, access.login, access.eid, access.delegated_manager_id, manager_id, name, email, training_date1, active_requests.building3, active_requests.building2, active_requests.building1, active_requests.room4, active_requests.room3, active_requests.room2, active_requests.room1 FROM (access LEFT JOIN requests ON access.eid = requests.eid)  LEFT JOIN active_requests ON access.eid = active_requests.eid WHERE ( access.manager_id=:eid OR access.delegated_manager_id=:eid_s )');
$access_users_select->bindValue(':eid_s', $_SESSION['data']['eid'], PDO::PARAM_STR);
$access_users_select->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);
$access_users_select->execute();

$data = array();
// fetch records about users for query to json table
foreach($access_users_select as $row) {
    $manager_email = '';
    $delegated_to_you = $row['delegated_manager_id'] == $_SESSION['data']['eid'];
    $delegated_by_you = FALSE;

    if (isset($row['delegated_manager_id'])){
        $access_manager_select = $db->prepare('SELECT access.email FROM access WHERE eid=:manager_id');
        if ($row['delegated_manager_id'] == $_SESSION['data']['eid']) {
            $access_manager_select->bindValue(':manager_id', $row['manager_id'], PDO::PARAM_STR);
        } else {
            $access_manager_select->bindValue(':manager_id', $row['delegated_manager_id'], PDO::PARAM_STR);
        }
        $access_manager_select->execute();
        if ($access_manager_select->rowCount()==1){
            $manager_email = $access_manager_select->fetch(PDO::FETCH_ASSOC)['email'];
        }
    }

    $user_request = [
        'eid' => $row['eid'],
        'st' => $row['st'],
        'status' => $row['status'],
        'name' => $row['name'],
        'eid' => $row['eid'],
        'login' => $row['login'],
        'email' => $row['email'],
        'delegated_to_you' => $delegated_to_you,
        'delegated_manager_email' =>  $manager_email,
        'delegated_by_you' => !empty($row['delegated_manager_id']) ,
        'delegated_to' =>  $manager_email,
        'expire_date' => date('Y-m-d', (strtotime('+3 years',  strtotime($row['training_date1'])))),

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



