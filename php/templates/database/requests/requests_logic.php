<?php
if (isset($_POST['logic'])) {
    switch ($_POST['logic']) {
        case 'addUser':
            $nsn_data = filter_input(INPUT_POST, 'nsn_data', FILTER_SANITIZE_STRING);

            if (!empty($nsn_data)) {
                echo exec('python3 /var/www/cgi-bin/ldap_basic_info.py ' . $nsn_data);
            }
            break;
        case 'deleteRequest':

            $user_eid = filter_input(INPUT_POST, 'user_eid', FILTER_SANITIZE_STRING);

            // Seting user status to 0
            $request_update = $db->prepare("DELETE FROM requests WHERE eid =:eid");
            $request_update->bindValue(':eid', $user_eid, PDO::PARAM_STR);
            $request_update->execute();

            $access_update = $db->prepare("UPDATE access SET delegated_manager_id=NULL WHERE eid =:eid");
            $access_update->bindValue(':eid', $user_eid, PDO::PARAM_STR);
            $access_update->execute();

            break;
        case 'deleteUser':
            $user_eid = filter_input(INPUT_POST, 'user_eid', FILTER_SANITIZE_STRING);

            $active_update = $db->prepare('UPDATE active_requests SET status=4, upload_cer_time=NULL WHERE eid=:eid');
            $active_update->bindValue(':eid', $user_eid, PDO::PARAM_INT);
            $active_update->execute();

            // Seting user status to 0
            $request_update = $db->prepare("DELETE FROM requests WHERE eid =:eid");
            $request_update->bindValue(':eid', $user_eid, PDO::PARAM_STR);
            $request_update->execute();

            $access_update = $db->prepare("DELETE FROM access WHERE eid =:eid");
            $access_update->bindValue(':eid', $user_eid, PDO::PARAM_STR);
            $access_update->execute();
            break;

        case 'updateUser':
            $options = array("options" => array("default" => false));
            $data = [
                'eid' =>    filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_STRING),
                'comments'    => filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING),
                'manager_id' =>    filter_input(INPUT_POST, 'manager_id', FILTER_SANITIZE_STRING),
                'delegated_manager_id' => filter_input(INPUT_POST, 'delegated_manager_id', FILTER_SANITIZE_STRING),
                'status1' =>    filter_input(INPUT_POST, 'status1', FILTER_VALIDATE_BOOLEAN, $options),
                'training_date2' =>    filter_input(INPUT_POST, 'training_date2', FILTER_SANITIZE_STRING),
                'status2' =>    filter_input(INPUT_POST, 'status2', FILTER_VALIDATE_BOOLEAN, $options),
                'training_date1' =>  filter_input(INPUT_POST, 'training_date1', FILTER_SANITIZE_STRING),
                'rights' =>    filter_input(INPUT_POST, 'rights', FILTER_SANITIZE_STRING),
            ];
            

            if ($_SESSION['g_user_eid'] != $data['eid']) {
                if ((ctype_digit($data['delegated_manager_id']) && strlen($data['delegated_manager_id']) == 8) || empty($data['delegated_manager_id'])) {
                    if ((ctype_digit($data['manager_id']) && strlen($data['manager_id']) == 8) || empty($data['delegated_manager_id'])) {
                        // check if user is not edit his data
                        $access_update = $db->prepare("UPDATE access SET training_date2=:training_date2, training_date1=:training_date1, comments=:comments, rights=:rights, manager_id=:manager_id, status2=:status2, status1=:status1, delegated_manager_id=:delegated_manager_id WHERE eid =:eid");
                        // $access_update = $db->prepare("UPDATE access SET comments=:comments, rights=:rights, manager_id=:manager_id, status2=:status2, training_date1=:training_date1, training_date2=:training_date2, status1=:status1, delegated_manager_id=:delegated_manager_id WHERE eid =:eid");
                        $access_update->bindValue(':eid', $data['eid'], PDO::PARAM_STR);
                        $access_update->bindValue(':comments', $data['comments'], PDO::PARAM_STR);
                        $access_update->bindValue(':manager_id', $data['manager_id'], PDO::PARAM_STR);
                        $access_update->bindValue(':delegated_manager_id', $data['delegated_manager_id'], PDO::PARAM_STR);

                        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$data['training_date1'])) {
                            $access_update->bindValue(':training_date1', $data['training_date1'], PDO::PARAM_STR);
                        } else {
                            $access_update->bindValue(':training_date1', NULL);
                        }

                        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$data['training_date2'])) {
                            $access_update->bindValue(':training_date2', $data['training_date2'], PDO::PARAM_STR);
                        } else {
                            $access_update->bindValue(':training_date2', NULL);
                        }

                        $access_update->bindValue(':status1', $data['status1'], PDO::PARAM_BOOL);
                        $access_update->bindValue(':status2', $data['status2'], PDO::PARAM_BOOL);
                        $access_update->bindValue(':rights', $data['rights'], PDO::PARAM_STR);
                        $access_update->execute();
                    }
                }
                $request = [
                    'status' =>	filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING),
                    'request_date' => filter_input(INPUT_POST, 'request_date', FILTER_SANITIZE_STRING),
                    'manager_comment' => filter_input(INPUT_POST, 'manager_comment', FILTER_SANITIZE_STRING),
                    'admin_comment' => filter_input(INPUT_POST, 'admin_comment', FILTER_SANITIZE_STRING),
                    'bid'	=> filter_input(INPUT_POST, 'bid', FILTER_SANITIZE_STRING),
                    'bsid' => filter_input(INPUT_POST, 'bsid', FILTER_SANITIZE_STRING),
                    'building3'	=> filter_input(INPUT_POST, 'building3', FILTER_VALIDATE_BOOLEAN, $options),
                    'building2' =>	filter_input(INPUT_POST, 'building2', FILTER_VALIDATE_BOOLEAN, $options),
                    'building1' => filter_input(INPUT_POST, 'building1', FILTER_VALIDATE_BOOLEAN, $options),
                    'room4' => filter_input(INPUT_POST, 'room4', FILTER_VALIDATE_BOOLEAN, $options),
                    'room3' => filter_input(INPUT_POST, 'room3', FILTER_VALIDATE_BOOLEAN, $options),
                    'room2' => filter_input(INPUT_POST, 'room2', FILTER_VALIDATE_BOOLEAN, $options),
                    'room1' => filter_input(INPUT_POST, 'room1', FILTER_VALIDATE_BOOLEAN, $options),
                ];

                if (empty($request['status'])) {
                    $request['status'] = 1;
                } else {
                    if ($request['status'] == 0) {
                        $request['status'] = 1;
                    }
                }

                if (
                    ( empty($data['bid']) || strlen($data['bid'] ) <= 20 ) &&
                    ( empty($request['bsid']) || strlen($request['bsid']) <= 20 ) &&
                    $request['status'] >= 1
                ) {
                    require_once $_SERVER["DOCUMENT_ROOT"] . '/modules/UserData.php';
                    $user = new UserData( ['eid' => $data['eid']], $db);
                    $db_request = $user->getRequest();

                    if ($db_request['status'] != 0) {
                        // user is in requests table
                        $request_user_add = $db->prepare("UPDATE requests SET status=:status, bid=:bid, bsid=:bsid, building3=:building3,building2=:building2,building1=:building1,room4=:room4,room3=:room3,room2=:room2,room1=:room1, request_date=:request_date WHERE eid =:eid");
                    } else {
                        // user is not in requests table
                        $request_user_add = $db->prepare("INSERT INTO requests(status, eid, bid, bsid, building3, building2, building1, room4, room3, room2, room1, request_date) VALUES (:status, :eid, :bid, :bsid, :building3, :building2, :building1, :room4, :room3, :room2, :room1, :request_date)");
                    }
                    $request_user_add->bindValue(':eid', $data['eid'], PDO::PARAM_STR);
                    $request_user_add->bindValue(':bid', $request['bid'], PDO::PARAM_STR);
                    $request_user_add->bindValue(':bsid', $request['bsid'], PDO::PARAM_STR);

                    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $request['request_date'])) {
                        $request_user_add->bindValue(':request_date', $request['request_date'], PDO::PARAM_STR);
                    } else {
                        $request_user_add->bindValue(':request_date', NULL);
                    }

                    $request_user_add->bindValue(':status', $request['status'], PDO::PARAM_INT);
                    $request_user_add->bindValue(':building3', $request['building3'], PDO::PARAM_BOOL);
                    $request_user_add->bindValue(':building2', $request['building2'], PDO::PARAM_BOOL);
                    $request_user_add->bindValue(':building1', $request['building1'], PDO::PARAM_BOOL);
                    $request_user_add->bindValue(':room4', $request['room4'], PDO::PARAM_BOOL);
                    $request_user_add->bindValue(':room3', $request['room3'], PDO::PARAM_BOOL);
                    $request_user_add->bindValue(':room2', $request['room2'], PDO::PARAM_BOOL);
                    $request_user_add->bindValue(':room1', $request['room1'], PDO::PARAM_BOOL);
                    $request_user_add->execute();
                }
            }
            break;
    }
}
