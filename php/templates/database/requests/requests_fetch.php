<?php
header('Content-Type: application/json');
$sql = 'SELECT access.eid, login, access.rights, access.delegated_manager_id, access.manager_id, access.name, access.email, access.training_date1, access.status1, access.status2, access.training_date2, access.comments, requests.status, requests.bid, requests.bsid, requests.request_date, requests.manager_comment, requests.admin_comment, requests.building3, requests.building2, requests.building1, requests.room4, requests.room3, requests.room2, requests.room1 FROM access LEFT JOIN requests ON access.eid=requests.eid ';
// query select data from db
$select_query = $db->prepare($sql);
$select_query->execute();

$data = array();
foreach ($select_query as $row) {
    $user_request = [
        'eid' => $row['eid'],
        'name' => $row['name'],
        'login' => $row['login'],
        'email' => $row['email'],
        'comments' => $row['comments'],
        'manager_id' => $row['manager_id'],
        'delegated_manager_id' => $row['delegated_manager_id'],
        'training_date1' => $row['training_date1'],
        'status1' => $row['status1'],
        'training_date2' => $row['training_date2'],
        'status2' => $row['status2'],


        'rights' => $row['rights'],
        'status' => $row['status'],
        'request_date' => $row['request_date'],
        'manager_comment' => $row['manager_comment'],
        'admin_comment' => $row['admin_comment'],
        'bid' => $row['bid'],
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
if (isset($output)) {
    $data['data'] = $output;
} else {
    $data['data'] = [];
}
exit(json_encode($data));
