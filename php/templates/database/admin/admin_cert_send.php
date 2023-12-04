<?php
if (isset($_POST['accept']) || isset($_POST['save']) || isset($_POST['deny'])) {

    $admin_comment = filter_input(INPUT_POST, 'admin_comment', FILTER_SANITIZE_STRING);

    require_once $_SERVER["DOCUMENT_ROOT"] . '/modules/UserData.php';
    $user = new UserData(['eid' => $usr['user']], $db);
    $access = $user->getaccess();

    if (isset($_POST['accept'])) {
        $update_query = $db->prepare("UPDATE active_requests SET status=1, admin_comment=:admin_comment WHERE eid =:user_eid");

    } elseif (isset($_POST['save'])) {
        // labadmin click save
        $_SESSION['review_labadmin_alert'] = 'Save complited';

        $update_query = $db->prepare("UPDATE active_requests SET admin_comment=:admin_comment WHERE eid =:user_eid");
    } elseif (isset($_POST['deny'])) {
        // labadmin deny certificate
        $update_query = $db->prepare("UPDATE active_requests SET status=3, admin_comment=:admin_comment, upload_cer_time=:upload_cer_time WHERE eid =:user_eid");
        if (ctype_digit($usr['days'])) {
            $days = filter_input(INPUT_POST, 'days', FILTER_SANITIZE_STRING);
        } else {
            $days = 15;
        }
        $update_query->bindValue(':upload_cer_time', date('Y-m-d', (strtotime('+' . $days . ' days', strtotime(date('Y-m-d'))))), PDO::PARAM_STR);
    }

    $update_query->bindValue(':user_eid', $usr['user'], PDO::PARAM_STR);
    $update_query->bindValue(':admin_comment', $admin_comment, PDO::PARAM_STR);
    $update_query->execute();

    if (isset($_POST['save'])) {
        header("Location:  /labadmin/review/cert?user=" . $usr['user']);
    } else {
        header('Location: /labadmin/review');
    }
    exit();
}
header("Location: /");
exit();
