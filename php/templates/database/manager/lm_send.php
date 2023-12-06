<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . '/modules/UserData.php';
    $user = new UserData( ['eid' => $usr['user']], $db);

    $access = $user->getaccess();

    if (isset($_POST['accept']) || isset($_POST['save']) || isset($_POST['deny']) || isset($_POST['block']) || isset($_POST['ACCEPT'])) {

        $manager_comment = filter_input(INPUT_POST, 'manager_comment', FILTER_SANITIZE_STRING);



        if (isset($_POST['accept'])) {
            $update_query = $db->prepare("UPDATE requests SET status=5, accept_manager_name='".$_SESSION['data']['name']."', manager_comment=:manager_comment WHERE eid =:user_eid");

        } elseif (isset($_POST['ACCEPT'])) {
            $update_query = $db->prepare("UPDATE requests SET status=5, accept_manager_name='DONE ACCEPT', manager_comment=:manager_comment WHERE eid =:user_eid");

        }elseif (isset($_POST['save'])) {
            $_SESSION['review_alert'] = 'Save complited';

            $update_query = $db->prepare("UPDATE requests SET status=2, manager_comment=:manager_comment WHERE eid =:user_eid");
        } elseif (isset($_POST['deny'])) {
            $update_query = $db->prepare("UPDATE requests SET status=4, manager_comment=:manager_comment WHERE eid =:user_eid");

        } elseif (isset($_POST['block'])) {
            $update_query = $db->prepare("UPDATE requests SET status=3, manager_comment=:manager_comment WHERE eid =:user_eid");
        }

        $update_query->bindValue(':user_eid', $usr['user'], PDO::PARAM_STR);
        $update_query->bindValue(':manager_comment', $manager_comment, PDO::PARAM_STR);
        $update_query->execute();
        if (isset($_POST['save'])) {
            header("Location: /manager/review/request?user=" . $usr['user']);
        } else {
            header('Location: /manager/review');
        }
        exit();
    }
    header("Location: /");
    exit();
