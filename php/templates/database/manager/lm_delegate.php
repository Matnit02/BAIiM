<?php

$eid_string = filter_input(INPUT_POST, 'eid_string', FILTER_SANITIZE_STRING);

if (!empty($eid_string)){
    if(isset($_POST['submit'])){
        $eid_list =  explode(';', $eid_string);
        
        switch ($_POST['submit']){
            case 'dismiss':
                $deleagted_by = [];
                $error = false;
                $dissmis_happend = false;
                foreach ($eid_list as $user_eid) { 
                    $access_users_select = $db->prepare('SELECT name, manager_id FROM access WHERE eid=:user_eid AND delegated_manager_id=:manager_id');
                    $access_users_select->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                    $access_users_select->bindValue(':manager_id', $_SESSION['data']['eid'], PDO::PARAM_STR);
                    $access_users_select->execute();
        
                    if ($access_users_select->rowCount()==1){
                        $access_users_update = $db->prepare('UPDATE access SET delegated_manager_id=NULL WHERE eid=:user_eid AND delegated_manager_id=:manager_id');
                        $access_users_update->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                        $access_users_update->bindValue(':manager_id', $_SESSION['data']['eid'], PDO::PARAM_STR);
                        $access_users_update->execute();
        
                        $user = $access_users_select->fetch();
                        $access_manager = $db->prepare('SELECT name, email FROM access WHERE eid=:manager_id');
                        $access_manager->bindValue(':manager_id', $user['manager_id'], PDO::PARAM_STR);
                        $access_manager->execute();

                    } else {
                        $error = true;
                        $deleagted_by[] = $user_eid;
                    }
                }
                if ($error){
                    echo 'You can not dismiss your employess: ' . implode(', ' , $deleagted_by);
                }
                else {
                    echo 'You successfully dissmis users!';
                }
                break;
            case 'delgate':
                $delegated_manager_id = filter_input(INPUT_POST, 'delegated_manager_id', FILTER_SANITIZE_STRING);
                if($delegated_manager_id != $_SESSION['data']['eid']){

                    $access_delegated_select = $db->prepare('SELECT rights, name, email FROM access WHERE eid=:eid');
                    $access_delegated_select->bindValue(':eid', $delegated_manager_id, PDO::PARAM_STR);
                    $access_delegated_select->execute();

                    if($access_delegated_select->rowCount()==1 || empty($delegated_manager_id)){
                        $deleg_manager = $access_delegated_select->fetch();
                        if (preg_match( '(m)', $deleg_manager['rights']) || empty($delegated_manager_id)){
                            $deleagted_to_you = [];
                            $error = false;
                            $delegation_happend = false;
                            foreach ($eid_list as $user_eid) { 
                                $access_users_select = $db->prepare('SELECT eid, delegated_manager_id FROM access WHERE eid=:user_eid AND manager_id=:manager_id');
                                $access_users_select->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                                $access_users_select->bindValue(':manager_id', $_SESSION['data']['eid'], PDO::PARAM_STR);
                                $access_users_select->execute();

                                if ($access_users_select->rowCount()==1){
                                    if($delegated_manager_id != $access_users_select->fetch()['delegated_manager_id']){
                                        $access_users_update = $db->prepare('UPDATE access SET delegated_manager_id=:delegated_manager_id WHERE eid=:user_eid AND manager_id=:manager_id');
                                        $access_users_update->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                                        $access_users_update->bindValue(':manager_id', $_SESSION['data']['eid'], PDO::PARAM_STR);
                                        $access_users_update->bindValue(':delegated_manager_id', $delegated_manager_id, PDO::PARAM_STR);
                                        $access_users_update->execute();
                                        $delegation_happend = true;
                                    }
                                } else {
                                    $error = true;
                                    $deleagted_to_you[] = $user_eid;
                                }
                                
                            }


                            if ($error){
                                echo 'You can not delegate users delegated to you: ' . implode(', ' , $deleagted_to_you);
                            } else {
                                echo 'You successfully delegated users!';
                            }
                        }
                    }
                }
                break;

            case 'undelegate':
                $error = false;
                foreach ($eid_list as $user_eid) { 
                    $access_users_select = $db->prepare('SELECT eid, delegated_manager_id FROM access WHERE eid=:user_eid AND manager_id=:manager_id');
                    $access_users_select->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                    $access_users_select->bindValue(':manager_id', $_SESSION['data']['eid'], PDO::PARAM_STR);
                    $access_users_select->execute();

                    if ($access_users_select->rowCount()==1){
                        $access_users_update = $db->prepare('UPDATE access SET delegated_manager_id=NULL WHERE eid=:user_eid AND manager_id=:manager_id');
                        $access_users_update->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                        $access_users_update->bindValue(':manager_id', $_SESSION['data']['eid'], PDO::PARAM_STR);
                        $access_users_update->execute();
                        $delegation_happend = true;
                    } else {
                        $error = true;
                        $deleagted_to_you[] = $user_eid;
                    }
                }
                            

                if ($error){
                    echo 'You can not delegate users delegated to you: ' . implode(', ' , $deleagted_to_you);
                } else {
                    echo 'You successfully undelegated users!';
                }
                break;
        }
    }
}

