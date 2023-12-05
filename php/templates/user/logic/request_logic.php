<?php
// Checking if user use form to sent query to request_form_logic
if (isset($_POST['submit']) || isset($_POST['save'])) {

    $data['checked_credentials'] = false;

    $data['bid'] = filter_input(INPUT_POST, 'bid', FILTER_SANITIZE_STRING);

    $data['bsid'] = filter_input(INPUT_POST, 'bsid', FILTER_SANITIZE_STRING);

    // Default options for filter input
    $options = array("options" => array("default" => false));

    $data['checkbox'] = [
        'training_attendance' => filter_input(INPUT_POST, 'training_attendance', FILTER_VALIDATE_BOOLEAN, $options),
        'ohs' => filter_input(INPUT_POST, 'ohs', FILTER_VALIDATE_BOOLEAN, $options),
        'medical' => filter_input(INPUT_POST, 'medical', FILTER_VALIDATE_BOOLEAN, $options),
        'trainings' => filter_input(INPUT_POST, 'trainings', FILTER_VALIDATE_BOOLEAN, $options)
    ];

    $data['lab_access'] = [
        'building3' => filter_input(INPUT_POST, 'building3', FILTER_VALIDATE_BOOLEAN, $options),
        'building2' =>  filter_input(INPUT_POST, 'building2', FILTER_VALIDATE_BOOLEAN, $options),
        'building1' => filter_input(INPUT_POST, 'building1', FILTER_VALIDATE_BOOLEAN, $options),
        'room4' => filter_input(INPUT_POST, 'room4', FILTER_VALIDATE_BOOLEAN, $options),
        'room3' => filter_input(INPUT_POST, 'room3', FILTER_VALIDATE_BOOLEAN, $options),
        'room2' => filter_input(INPUT_POST, 'room2', FILTER_VALIDATE_BOOLEAN, $options),
        'room1' => filter_input(INPUT_POST, 'room1', FILTER_VALIDATE_BOOLEAN, $options)
    ];

    $data['lab_justification'] = [
        'room4_justification' => filter_input(INPUT_POST, 'room4_justification', FILTER_SANITIZE_STRING),
        'room3_justification' => filter_input(INPUT_POST, 'room3_justification', FILTER_SANITIZE_STRING),
        'room2_justification' => filter_input(INPUT_POST, 'room2_justification', FILTER_SANITIZE_STRING),
        'room1_justification' => isset($_POST['room1_justification']) ? $_POST['room1_justification'] : '',
        // filter_input(INPUT_POST, 'room1_justification', FILTER_SANITIZE_STRIPPED)
    ];
    // $data['lab_justification']['room1_justification'] = $_POST['room2_justification'];

    $is_manager = preg_match('(m)', $lab['rights']);

    if (isset($_POST['submit'])) {
        if (empty($data['bid']) || strlen($data['bid']) <= 20) {
            if (empty($data['bsid']) || strlen($data['bsid']) <= 20) {
                if (!empty($data['checkbox']['training_attendance']) && !empty($data['checkbox']['ohs']) &&  !empty($data['checkbox']['medical']) && !empty($data['checkbox']['trainings'])) {
                    if (!empty($data['lab_access']['room1']) || !empty($data['lab_access']['building3']) || !empty($data['lab_access']['building2']) || !empty($data['lab_access']['building1']) || !empty($data['lab_access']['room4']) || !empty($data['lab_access']['room3']) || !empty($data['lab_access']['room2'])) {
                        if ((!empty($data['lab_access']['room4']) && empty($data['lab_justification']['room4_justification'])) || (!empty($data['lab_access']['room3']) && empty($data['lab_justification']['room3_justification'])) || (!empty($data['lab_access']['room2']) && empty($data['lab_justification']['room2_justification'])) || (!empty($data['lab_access']['room1']) && empty($data['lab_justification']['room1_justification']))) {
                            $_SESSION['request_alert'] = 'You pick a lab but do not give the justification!';
                        } else {
                            if ((empty($data['lab_access']['room4']) && !empty($data['lab_justification']['room4_justification'])) || (empty($data['lab_access']['room3']) && !empty($data['lab_justification']['room3_justification'])) || (empty($data['lab_access']['room2']) && !empty($data['lab_justification']['room2_justification'])) || (empty($data['lab_access']['room1']) && !empty($data['lab_justification']['room1_justification']))) {
                                $_SESSION['request_alert'] = 'You give the justification but do not choose the lab!';
                            } else {
                                $data['checked_credentials'] = true;
                            }
                        }
                    } else {
                        $_SESSION['request_alert'] = 'You do not pick any rooms/buildings!';
                    }
                } else {
                    $_SESSION['request_alert'] = 'Invalid trainings!';
                }
            } else {
                $_SESSION['request_alert'] = 'Invalid bsid!';
            }
        } else {
            $_SESSION['request_alert'] = 'Invalid bid!';
        }
    }                                
    

    // Setting status
    if ($data['checked_credentials'] &&  ($is_manager || (empty($data['lab_access']['room4']) && empty($data['lab_access']['room3']) && empty($data['lab_access']['room2']) && empty($data['lab_access']['room1'])))) {
        $status = 5;

    } elseif ($data['checked_credentials']) {
        // Normal submit
        $status = 2;

        // Checking if user is manager
        if (!$is_manager) {

            $autodelegation = $db->prepare('SELECT delegated_id, in_use FROM autodelegation WHERE eid=:eid');
            $autodelegation->bindValue(':eid', $_SESSION["data"]['manager_id'], PDO::PARAM_STR);
            $autodelegation->execute();

            if ($autodelegation->rowCount() == 1) {
                $lm_delegation = $autodelegation->fetch();
                if ($lm_delegation['in_use']) {
                    $auto_update = $db->prepare('UPDATE access SET delegated_manager_id=:delegated_manager_id WHERE eid=:eid');
                    $auto_update->bindValue(':delegated_manager_id', $lm_delegation['delegated_id'], PDO::PARAM_STR);
                    $auto_update->bindValue(':eid', $_SESSION["data"]['eid'], PDO::PARAM_STR);
                    $auto_update->execute();
                }
            }
            $delegated_manager = $db->prepare('SELECT email, name  FROM access WHERE eid = (SELECT delegated_manager_id FROM access WHERE eid=:user_eid)');
            $delegated_manager->bindValue(':user_eid', $_SESSION["data"]['eid'], PDO::PARAM_STR);
            $delegated_manager->execute();
        }
    } else {
        // Just save
        $status = 1;
    }

    if ($req['status'] != 0) {
        $request_user_add = $db->prepare("UPDATE requests SET status=:status, bid=:bid, bsid=:bsid, training_attendance=:training_attendance,ohs=:ohs,medical=:medical,building3=:building3,building2=:building2,building1=:building1,room4=:room4,room3=:room3,room2=:room2,room1=:room1,room3_justification=:room3_justification,room2_justification=:room2_justification,room1_justification=:room1_justification,room4_justification=:room4_justification, request_date=:request_date WHERE eid =:eid");
    } else {
        $request_user_add = $db->prepare("INSERT INTO requests(status, eid, bid, bsid, training_attendance, ohs, medical, building3, building2, building1, room4, room3, room2, room1, room3_justification, room2_justification, room1_justification, room4_justification, request_date) VALUES (:status, :eid, :bid, :bsid, :training_attendance, :ohs, :medical, :building3, :building2, :building1, :room4, :room3, :room2, :room1, :room3_justification, :room2_justification, :room1_justification, :room4_justification, :request_date)");
    }
    $request_user_add->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);
    $request_user_add->bindValue(':bid', $data['bid'], PDO::PARAM_STR);
    $request_user_add->bindValue(':bsid', $data['bsid'], PDO::PARAM_STR);
    $request_user_add->bindValue(':request_date', date('Y-m-d'), PDO::PARAM_STR);

    $request_user_add->bindValue(':status', $status, PDO::PARAM_INT);
    $request_user_add->bindValue(':training_attendance', $data['checkbox']['training_attendance'], PDO::PARAM_BOOL);
    $request_user_add->bindValue(':ohs', $data['checkbox']['ohs'], PDO::PARAM_BOOL);
    $request_user_add->bindValue(':medical', $data['checkbox']['medical'], PDO::PARAM_BOOL);

    $request_user_add->bindValue(':building3', $data['lab_access']['building3'], PDO::PARAM_BOOL);
    $request_user_add->bindValue(':building2', $data['lab_access']['building2'], PDO::PARAM_BOOL);
    $request_user_add->bindValue(':building1', $data['lab_access']['building1'], PDO::PARAM_BOOL);
    $request_user_add->bindValue(':room4', $data['lab_access']['room4'], PDO::PARAM_BOOL);
    $request_user_add->bindValue(':room3', $data['lab_access']['room3'], PDO::PARAM_BOOL);
    $request_user_add->bindValue(':room2', $data['lab_access']['room2'], PDO::PARAM_BOOL);
    $request_user_add->bindValue(':room1', $data['lab_access']['room1'], PDO::PARAM_BOOL);


    $request_user_add->bindValue(':room3_justification', $data['lab_justification']['room3_justification'], PDO::PARAM_STR);
    $request_user_add->bindValue(':room2_justification', $data['lab_justification']['room2_justification'], PDO::PARAM_STR);
    $request_user_add->bindValue(':room1_justification', $data['lab_justification']['room1_justification']);
    $request_user_add->bindValue(':room4_justification', $data['lab_justification']['room4_justification'], PDO::PARAM_STR);

    $request_user_add->execute();
    if ($data['checked_credentials']) {
        header("Location: /");
    } else {
        if (isset($_POST['save'])) {
            $_SESSION['request_alert'] = 'Save complited';
        }
        header("Location: /request");
    }
}
