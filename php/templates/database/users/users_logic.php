<?php


// Checking if user click buttons
if (isset($_POST['logic'])) {
    if($_POST['logic'] == 'revoke_access'){
        // Mail object



        $user_eid = filter_input(INPUT_POST, 'user_eid', FILTER_SANITIZE_STRING);
        $select_user_info = $db->prepare('SELECT access.eid, name, bid, bsid FROM access INNER JOIN active_requests ON active_requests.eid = access.eid WHERE access.eid=:user_eid');
        $select_user_info->bindValue(':user_eid', $user_eid, PDO::PARAM_INT);
        $select_user_info->execute();

        if ($select_user_info->rowCount() != 1) {
            return 0;
        }

        $access = $select_user_info->fetch();

        $active_update = $db->prepare('DELETE FROM active_requests WHERE eid=:user_eid');
        $active_update->bindValue(':user_eid', $user_eid, PDO::PARAM_INT);
        $active_update->execute();

        $access_update = $db->prepare("UPDATE access SET delegated_manager_id=NULL WHERE eid =:eid");
        $access_update->bindValue(':eid', $user_eid, PDO::PARAM_STR);
        $access_update->execute();

        $request_update = $db->prepare("UPDATE requests SET status=1, accept_lm_name=NULL WHERE eid =:eid");
        $request_update->bindValue(':eid', $user_eid, PDO::PARAM_STR);
        $request_update->execute();
    }
}