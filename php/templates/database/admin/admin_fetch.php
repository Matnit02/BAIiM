<?php
header('Content-Type: application/json');
$requests_user_select = $db->prepare('SELECT access.eid , name, login, email, manager_id, delegated_manager_id, training_date1, training_date2, access.comments, requests.bid, requests.bsid, requests.request_date, requests.manager_comment, requests.admin_comment, requests.building3, requests.building2, requests.building1, requests.room4, requests.room3, requests.room2, requests.room1 FROM access INNER JOIN requests ON access.eid=requests.eid WHERE requests.status=5');
$requests_user_select->execute();


$data = array();
foreach ($requests_user_select as $row) {
    $manager_id = NULL;

    if (!empty($row['manager_id'])) {
        $access_delegated_select = $db->prepare('SELECT email FROM access WHERE eid=:manager_id');
        $access_delegated_select->bindValue(':manager_id', $row['manager_id'], PDO::PARAM_STR);
        $access_delegated_select->execute();
        if ($access_delegated_select->rowCount() == 1) {
            $manager_id = $access_delegated_select->fetch()['email'];
        }
    }

    $delegated_delegated_email = NUll;
    if (!empty($row['delegated_manager_id'])) {

        $access_delegated_select = $db->prepare('SELECT email FROM access WHERE eid=:manager_id');
        $access_delegated_select->bindValue(':manager_id', $row['delegated_manager_id'], PDO::PARAM_STR);
        $access_delegated_select->execute();
        if ($access_delegated_select->rowCount() == 1) {
            $delegated_delegated_email = $access_delegated_select->fetch()['email'];
        }
    }

    $user_request = [
        'eid' => $row['eid'],
        'review' => 'request',
        'name' => $row['name'],
        'login' => $row['login'],
        'email' => $row['email'],
        'comments' => $row['comments'],
        'manager_id' => $manager_id,
        'delegated_manager_id' => $delegated_delegated_email,
        'date' => $row['request_date'],
        'manager_comment' => $row['manager_comment'],
        'admin_comment' => $row['admin_comment'],

        'training_date1' => $row['training_date1'],
        'training_date2' => $row['training_date2'],
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

$active_user_select = $db->prepare('SELECT access.eid , name, login, email, manager_id, delegated_manager_id, bid, bsid, training_date1, training_date2, building3, building2, building1, room4, room3, room2, room1, activaiton_day, comments, admin_comment FROM access INNER JOIN active_requests ON access.eid=active_requests.eid WHERE status=2');
$active_user_select->execute();
foreach ($active_user_select as $row) {
    $manager_id = NULL;
    if (!empty($row['manager_id'])) {
        $access_delegated_select = $db->prepare('SELECT email FROM access WHERE eid=:manager_id');
        $access_delegated_select->bindValue(':manager_id', $row['manager_id'], PDO::PARAM_STR);
        $access_delegated_select->execute();
        if ($access_delegated_select->rowCount() == 1) {
            $manager_id = $access_delegated_select->fetch()['email'];
        }
    }


    $delegated_delegated_email = NUll;
    if (!empty($row['delegated_manager_id'])) {
        $access_delegated_select = $db->prepare('SELECT email FROM access WHERE eid=:manager_id');
        $access_delegated_select->bindValue(':manager_id', $row['delegated_manager_id'], PDO::PARAM_STR);
        $access_delegated_select->execute();
        if ($access_delegated_select->rowCount() == 1) {
            $delegated_delegated_email = $access_delegated_select->fetch()['email'];
        }
    }


    $user_request = [
        'eid' => $row['eid'],
        'review' => 'cert_check',
        'name' => $row['name'],
        'login' => $row['login'],
        'email' => $row['email'],
        'comments' => $row['comments'],
        'manager_id' => $manager_id,
        'delegated_manager_id' => $delegated_delegated_email,
        'date' => $row['activaiton_day'],
        'manager_comment' => 'IMPLICITY_NULL',
        'admin_comment' => $row['admin_comment'],

        'training_date1' => $row['training_date1'],
        'training_date2' => $row['training_date2'],
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
