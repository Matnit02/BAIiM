<?php

if (isset($_POST['logic'])) {
    switch ($_POST['logic']){
        case 'get':
            $email_pattern_select = $db->prepare('SELECT id, pattern_name, admin_email, reception_email, manager_email, user_email, description FROM email_patterns');
            $email_pattern_select->execute();
            
            foreach ($email_pattern_select as $row) {
                if ($apat['email_pattern'] == $row['id']) {
                    echo '<option value="' . $row['id'] . '" data-pattern_name="' . $row['pattern_name'] . '" data-admin-email="' . $row['admin_email'] . '" data-reception-email="' . $row['reception_email'] . '" data-manager_email="' . $row['manager_email'] . '" data-user_email="' . $row['user_email'] . '" data-description="' . $row['description'] . '" selected>' . $row['pattern_name'] . '</option>';
                } else {
                    echo '<option value="' . $row['id'] . '" data-pattern_name="' . $row['pattern_name'] . '" data-admin-email="' . $row['admin_email'] . '" data-reception-email="' . $row['reception_email'] . '" data-manager_email="' . $row['manager_email'] . '" data-user_email="' . $row['user_email'] . '" data-description="' . $row['description'] . '" >' . $row['pattern_name'] . '</option>';
                }
            }
            break;
        case 'save':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
            $data = [
                'pattern_name' => filter_input(INPUT_POST, 'data-pattern_name', FILTER_SANITIZE_STRING),
                'admin_email' => filter_input(INPUT_POST, 'data-admin-email', FILTER_SANITIZE_STRING),
                'reception_email' => filter_input(INPUT_POST, 'data-reception-email', FILTER_SANITIZE_STRING),
                'description' => filter_input(INPUT_POST, 'data-description', FILTER_SANITIZE_STRING),
                'manager_email' => filter_input(INPUT_POST, 'data-manager_email', FILTER_SANITIZE_STRING),
                'user_email' => filter_input(INPUT_POST, 'data-user_email', FILTER_SANITIZE_STRING),
            ];
    
            if (empty($data['pattern_name']) || empty($data['admin_email']) || empty($data['reception_email'])) {

            } else {
                $email_pattern_select = $db->prepare('SELECT id FROM email_patterns WHERE id=:id');
                $email_pattern_select->bindValue(':id', $id, PDO::PARAM_STR);
                $email_pattern_select->execute();
    
                $recursiveIdSearch = true;
    
                if ($email_pattern_select->rowCount() == 1) {
                    $email_pattern_add = $db->prepare('UPDATE email_patterns SET pattern_name=:pattern_name, admin_email=:admin_email, reception_email=:reception_email, manager_email=:manager_email, user_email=:user_email, description=:description WHERE id=:id');
                    $email_pattern_add->bindValue(':id', $id, PDO::PARAM_INT);
    
                    $recursiveIdSearch = false;
                    echo $id;
                    
                } else {
                    $email_pattern_add = $db->prepare('INSERT INTO email_patterns( pattern_name, admin_email, reception_email, manager_email, user_email, description) VALUES (:pattern_name, :admin_email, :reception_email, :manager_email, :user_email, :description)');
                }
                $email_pattern_add->bindValue(':pattern_name', $data['pattern_name'], PDO::PARAM_STR);
                $email_pattern_add->bindValue(':admin_email', $data['admin_email'], PDO::PARAM_STR);
                $email_pattern_add->bindValue(':reception_email', $data['reception_email'], PDO::PARAM_STR);
                $email_pattern_add->bindValue(':user_email', $data['user_email'], PDO::PARAM_STR);
                $email_pattern_add->bindValue(':manager_email', $data['manager_email'], PDO::PARAM_STR);
                $email_pattern_add->bindValue(':description', $data['description'], PDO::PARAM_STR);
                $email_pattern_add->execute();
    
                if($recursiveIdSearch){
                    $email_pattern_select = $db->prepare('SELECT id FROM email_patterns WHERE pattern_name=:pattern_name');
                    $email_pattern_select->bindValue(':pattern_name', $data['pattern_name'], PDO::PARAM_STR);
                    $email_pattern_select->execute()
                    ;
                    if($email_pattern_select->rowCount()==1){
                        echo $email_pattern_select->fetch()['id'];
                    }
                }
            }
            break;
        
        case 'remove':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
            if (!empty($id)){
                if($apat['email_pattern'] != $id){
                    $email_pattern_delete = $db->prepare('DELETE FROM email_patterns WHERE id=:id');
                    $email_pattern_delete->bindValue(':id', $id, PDO::PARAM_INT);
                    $email_pattern_delete->execute();

                    $active_pattern_update = $db->prepare('UPDATE active_patterns SET email_pattern=1 WHERE email_pattern=:id');
                    $active_pattern_update->bindValue(':id', $id, PDO::PARAM_INT);
                    $active_pattern_update->execute();
                }
            }
            break;
    }
}