<?php

if (isset($_POST['logic'])) {
    switch ($_POST['logic']){
        case 'get':
            $patterns_select = $db->prepare('SELECT id, name, email_pattern, description FROM active_patterns');
            $patterns_select->execute();
        
            foreach ($patterns_select as $row) {
                if ($apat['id'] == $row['id']) {
                    echo '<option value="' . $row['id'] . '" data-name="' . $row['name'] . '" data-email_pattern="' . $row['email_pattern'] . '" data-description="' . $row['description'] . '" selected>' . $row['name'] . '</option>';
                } else {
                    echo '<option value="' . $row['id'] . '" data-name="' . $row['name'] . '" data-email_pattern="' . $row['email_pattern'] . '" data-description="' . $row['description'] . '">' . $row['name'] . '</option>';
                }
            }
            break;
        case 'use':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
            if(!empty($id)){
                
                if($apat['id'] != $id){
                    $setting_pattern_udate = $db->prepare('UPDATE active_patterns SET revision_num=:revision_num WHERE id=:id');
                    $setting_pattern_udate->bindValue(':id', $id, PDO::PARAM_INT);
                    $setting_pattern_udate->bindValue(':revision_num', $apat['revision_num'] + 1, PDO::PARAM_INT);
                    $setting_pattern_udate->execute();
                }
            }
            break;
        case 'save':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
            if ($id != 1) {
                $data = [
                    'name' => filter_input(INPUT_POST, 'data-name', FILTER_SANITIZE_STRING),
                    'email_pattern' => filter_input(INPUT_POST, 'data-email_pattern', FILTER_SANITIZE_STRING),
                    'description' => filter_input(INPUT_POST, 'data-description', FILTER_SANITIZE_STRING),
                ];
        
                if (empty($data['name']) || empty($data['email_pattern'])) {
                } else {
                    $setting_pattern_select = $db->prepare('SELECT id FROM active_patterns WHERE id=:id');
                    $setting_pattern_select->bindValue(':id', $id, PDO::PARAM_STR);
                    $setting_pattern_select->execute();
        
                    if ($setting_pattern_select->rowCount() == 1) {
                        $setting_pattern_add = $db->prepare('UPDATE active_patterns SET name=:name, email_pattern=:email_pattern, description=:description WHERE id=:id');
                        $setting_pattern_add->bindValue(':id', $id, PDO::PARAM_INT);
                    } else {
                        $setting_pattern_add = $db->prepare('INSERT INTO active_patterns(name, email_pattern, description) VALUES ( :name, :email_pattern, :description)');
                    }
                    $setting_pattern_add->bindValue(':name', $data['name'], PDO::PARAM_STR);
                    $setting_pattern_add->bindValue(':email_pattern', $data['email_pattern'], PDO::PARAM_INT);
                    $setting_pattern_add->bindValue(':description', $data['description'], PDO::PARAM_STR);
                    $setting_pattern_add->execute();
                }
            }
            break;
        case 'remove':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
            if (!empty($id)){
                if ($id != 1) {
                    $setting_pattern_delete = $db->prepare('DELETE FROM active_patterns WHERE id=:id');
                    $setting_pattern_delete->bindValue(':id', $id, PDO::PARAM_INT);
                    $setting_pattern_delete->execute();
                }
            }
    }
}