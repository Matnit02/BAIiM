<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/modules/UserData.php';
$user = new UserData(['eid' => $usr['user']], $db);

$access = $user->getaccess();
$active = $user->getActiveRequest();
$request = $user->getRequest();

if (isset($_POST['accept']) || isset($_POST['save']) || isset($_POST['deny']) || isset($_POST['block'])) {
    $admin_comment = filter_input(INPUT_POST, 'admin_comment', FILTER_SANITIZE_STRING);

    if (isset($_POST['accept'])) {
        $update_query = $db->prepare("UPDATE requests SET status=8, admin_comment=:admin_comment WHERE eid =:user_eid");

        if ($active['status'] != 0) {
            $insert_active_request = $db->prepare("UPDATE active_requests SET bid=:bid, bsid=:bsid, building3=:building3, building2=:building2, building1=:building1, room4=:room4, room3=:room3, room2=:room2, room1=:room1, room3_justification=:room3_justification, room2_justification=:room2_justification, room1_justification=:room1_justification, room4_justification=:room4_justification, activaiton_day=:activaiton_day, status=1 WHERE eid=:user_eid");
        } else {
            $insert_active_request = $db->prepare("INSERT INTO active_requests(eid, bid, bsid, building3, building2, building1, room4, room3, room2, room1, room3_justification, room2_justification, room1_justification, room4_justification, activaiton_day, `status`) VALUES (:user_eid, :bid, :bsid, :building3, :building2, :building1, :room4, :room3, :room2, :room1, :room3_justification, :room2_justification, :room1_justification, :room4_justification, :activaiton_day, 1)");
        }

        $insert_active_request->bindValue(':user_eid', $usr['user'], PDO::PARAM_STR);
        $insert_active_request->bindValue(':bid', $request['bid'], PDO::PARAM_STR);
        $insert_active_request->bindValue(':bsid', $request['bsid'], PDO::PARAM_STR);;

        $insert_active_request->bindValue(':building3', $request['building3'], PDO::PARAM_BOOL);
        $insert_active_request->bindValue(':building2', $request['building2'], PDO::PARAM_BOOL);
        $insert_active_request->bindValue(':building1', $request['building1'], PDO::PARAM_BOOL);
        $insert_active_request->bindValue(':room4', $request['room4'], PDO::PARAM_BOOL);
        $insert_active_request->bindValue(':room3', $request['room3'], PDO::PARAM_BOOL);
        $insert_active_request->bindValue(':room2', $request['room2'], PDO::PARAM_BOOL);
        $insert_active_request->bindValue(':room1', $request['room1'], PDO::PARAM_BOOL);

        $insert_active_request->bindValue(':activaiton_day', date('Y-m-d'), PDO::PARAM_STR);

        $insert_active_request->bindValue(':room3_justification', $request['room3_justification'], PDO::PARAM_STR);
        $insert_active_request->bindValue(':room2_justification', $request['room2_justification'], PDO::PARAM_STR);
        $insert_active_request->bindValue(':room1_justification', $request['room1_justification'], PDO::PARAM_STR);
        $insert_active_request->bindValue(':room4_justification', $request['room4_justification'], PDO::PARAM_STR);


        
        // $insert_active_request->execute();
        
    } elseif (isset($_POST['save'])) {
        $_SESSION['review_alert'] = 'Save complited';
        $update_query = $db->prepare("UPDATE requests SET status=5, admin_comment=:admin_comment WHERE eid =:user_eid");
    } elseif (isset($_POST['deny'])) {
        $update_query = $db->prepare("UPDATE requests SET status=7, admin_comment=:admin_comment WHERE eid =:user_eid");
    } else {
        $update_query = $db->prepare("UPDATE requests SET status=6, admin_comment=:admin_comment WHERE eid =:user_eid");
    }

    $update_query->bindValue(':user_eid', $usr['user'], PDO::PARAM_STR);
    $update_query->bindValue(':admin_comment', $admin_comment, PDO::PARAM_STR);
    $update_query->execute();

    if (isset($_POST['save'])) {
        header("Location: /admin/review/request?user=" . $usr['user']);
    } else {
        header('Location: /admin/review');
    }
    exit();
} else {
    // User get to form without sumbit
}
header("Location: /");
exit();
