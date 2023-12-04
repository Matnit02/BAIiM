<?php

$eid_string = filter_input(INPUT_POST, 'eid_string', FILTER_SANITIZE_STRING);

if (isset($_POST['submit'])){


    
    switch ($_POST['submit']){
        case 'lockingUser':
            $user_eid = filter_input(INPUT_POST, 'user_eid', FILTER_SANITIZE_STRING);
            
            $query = $db->prepare('SELECT name, status, manager_comment FROM access INNER JOIN requests ON access.eid=requests.eid WHERE access.eid=:user_eid AND (delegated_manager_id=:delegated_manager_id OR manager_id=:manager_id)');
            $query->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
            $query->bindValue(':manager_id', $_SESSION['data']['eid'], PDO::PARAM_STR);
            $query->bindValue(':delegated_manager_id', $_SESSION['data']['eid'], PDO::PARAM_STR);
            $query->execute();

            if($query->rowCount()==1){
                $data = $query->fetch();
                $status = null;
                if($data['status'] == 3) {
                    $status = 1;
                    echo 'unlock';

                } else if ($data['status'] != 3){
                    echo 'lock';
                    $status = 3;
                } else {
                    $status = $data['status'];
                    
                }
                $query = $db->prepare('UPDATE requests SET status=:status WHERE eid=:user_eid ');
                $query->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                $query->bindValue(':status', $status, PDO::PARAM_STR);
                $query->execute();
            }
            break;
        case 'startAutoDelegation':
            $query = $db->prepare('SELECT eid FROM lm_autodelegation WHERE eid=:eid');
            $query->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);
            $query->execute();
            
            if($query->rowCount()==1){
                $query = $db->prepare('UPDATE lm_autodelegation SET in_use=1, delegated_id=:delegated_id WHERE eid=:eid');
            } else {
                $query = $db->prepare('INSERT INTO lm_autodelegation (eid, delegated_id, in_use) VALUES(:eid, :delegated_id, 1)');
            }

            $select = $db->prepare('SELECT name, rights FROM access WHERE eid=:eid');
            $select->bindValue(':eid', filter_input(INPUT_POST, 'delegated_manager_id', FILTER_SANITIZE_STRING), PDO::PARAM_STR);
            $select->execute();
            
            if ($select->rowCount()==1){
                $user = $select->fetch();
                if(preg_match('(m)', $user['rights'])){
                    $query->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);
                    $query->bindValue(':delegated_id', filter_input(INPUT_POST, 'delegated_manager_id', FILTER_SANITIZE_STRING), PDO::PARAM_STR);
                    $query->execute();
                    echo $user['name'];
                }
            }
            break;

        case 'stopAutoDelegation':
            $query = $db->prepare('UPDATE lm_autodelegation SET in_use=0 WHERE eid=:eid');
            $query->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);
            $query->execute();
            break;   
        case 'saveAutoDelegation':
            $select = $db->prepare('SELECT name, rights FROM access WHERE eid=:eid');
            $select->bindValue(':eid', filter_input(INPUT_POST, 'delegated_manager_id', FILTER_SANITIZE_STRING), PDO::PARAM_STR);
            $select->execute();
            if ($select->rowCount()==1){
                $user = $select->fetch();
                if(preg_match('(m)', $user['rights'])){
                    $query = $db->prepare('UPDATE lm_autodelegation SET delegated_id=:delegated_id WHERE eid=:eid');
                    $query->bindValue(':delegated_id', filter_input(INPUT_POST, 'delegated_manager_id', FILTER_SANITIZE_STRING), PDO::PARAM_STR);
                    $query->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);
                    $query->execute();
                    echo $user['name'];
                }
            }
            break;   
    }
}

