<?php
header('Content-Type: application/json');
$sql = 'SELECT access.eid, access.name, login, access.email, access.comments, access.manager_id, access.delegated_manager_id, access.rights, access.training_date1, status, bid, bsid, admin_comment, building3, building2, building1, room4, room3, room2, room1, activaiton_day, upload_cer_time FROM access INNER JOIN active_requests ON access.eid=active_requests.eid WHERE status!=4 ';
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
        'rights' => $row['rights'],
        'expiration_date' => $row['training_date1'],
        'status' => $row['status'],
        'bid' => $row['bid'],
        'bsid' => $row['bsid'],
        'admin_comment' => $row['admin_comment'],
        'building3' => $row['building3'],
        'building2' => $row['building2'],
        'building1' => $row['building1'],
        'room4' => $row['room4'],
        'room3' => $row['room3'],
        'room2' => $row['room2'],
        'room1' => $row['room1'],
        'activaiton_day' => $row['activaiton_day'],
        'upload_cer_time' => $row['upload_cer_time'],
    ];
    $output[] = $user_request;
}
if (isset($output)) {
    $data['data'] = $output;
} else {
    $data['data'] = [];
}
exit(json_encode($data));
