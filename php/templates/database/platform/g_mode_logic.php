<?php

if (isset($_POST['g_mode'])) {

    $query = $db->prepare('SELECT bind_varible, in_use FROM g_user_bindings WHERE eid=:eid');
    $query->bindValue(':eid', $_SESSION['g_user_eid'], PDO::PARAM_STR);
    $query->execute();
    $old_data = $query->fetch();

    if ($_POST['g_mode'] == 'stop'){

        $select_old = $db->prepare('SELECT rights, eid FROM access WHERE eid=:b_var OR login=:b_var1 OR email=:b_var2');
        $select_old->bindValue(':b_var', $old_data['bind_varible'], PDO::PARAM_STR);
        $select_old->bindValue(':b_var1', $old_data['bind_varible'], PDO::PARAM_STR);
        $select_old->bindValue(':b_var2', $old_data['bind_varible'], PDO::PARAM_STR);
        $select_old->execute();

        if($select_old->rowCount()==1){
            $user = $select_old->fetch();

            $update = $db->prepare('UPDATE g_user_bindings SET in_use=0 WHERE eid=:eid');
            $update->bindValue(':eid', $_SESSION['g_user_eid'], PDO::PARAM_STR);
            $update->execute();

            $rm_b = $db->prepare('UPDATE access SET rights="'.str_replace('b', '', $user['rights']).'" WHERE eid=:eid');
            $rm_b->bindValue(':eid', $user['eid'], PDO::PARAM_STR);
            $rm_b->execute();
        }

    } else {

        $new_var = filter_input(INPUT_POST, 'bind_varible', FILTER_SANITIZE_STRING);

        if (strlen($new_var) > 20 ){
            header('Location: ./../../manage');
            exit();
        }
        
        $select_new = $db->prepare('SELECT rights, eid FROM access WHERE eid=:b_var OR login=:b_var1 OR email=:b_var2');
        $select_new->bindValue(':b_var', $new_var, PDO::PARAM_STR);
        $select_new->bindValue(':b_var1', $new_var, PDO::PARAM_STR);
        $select_new->bindValue(':b_var2', $new_var, PDO::PARAM_STR);
        $select_new->execute();

        if($select_new->rowCount()==1){
            if($query->rowCount() == 1){
                $g_binding_add = $db->prepare('UPDATE g_user_bindings SET bind_varible=:b_var, in_use=:in_use WHERE eid=:eid');
            } else if ($query->rowCount() == 0){
                $g_binding_add = $db->prepare('INSERT INTO g_user_bindings(eid, bind_varible, in_use) VALUES (:eid, :b_var, :in_use)');
            }
            $g_binding_add->bindValue(':eid', $_SESSION['g_user_eid'], PDO::PARAM_STR);
            $g_binding_add->bindValue(':b_var', $new_var, PDO::PARAM_STR);

            if($_POST['g_mode'] == 'save'){
                $g_binding_add->bindValue(':in_use', 0, PDO::PARAM_STR);
                $_SESSION['platform_manage_alert'] = 'Save complited!';
                
            } else if ($_POST['g_mode'] == 'start'){
                $user = $select_new->fetch();
                if(!preg_match('(b)', $user['rights'])){
                    $g_binding_add->bindValue(':in_use', 1, PDO::PARAM_STR);
                    $add_b = $db->prepare('UPDATE access SET rights="b'.$user['rights'].'" WHERE eid=:eid');
                    $add_b->bindValue(':eid', $user['eid'], PDO::PARAM_STR);
                    $add_b->execute();
                } else {
                    $g_binding_add->bindValue(':in_use', 0, PDO::PARAM_STR);
                    $_SESSION['platform_manage_alert'] = 'User GMODE session is already used by someone else!';
                }
            }
            $g_binding_add->execute();
        } else {
            $_SESSION['platform_manage_alert'] = 'No user found for this phrase. Check if user with this eid / email / login is in database.';
        }
    }
}
header('Location: ./../../manage');