<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class UserData {
    function __construct($data, $db)
    {
        $this->db = $db;
        $this->data = $data;
    }

    /*
    // -=-=-=-=-=-=-=-
    // INITIAL TEST
    // -=-=-=-=-=-=-=-
    // ------------->
    */ 
    public function init()
    {
        $this->access = $this->getaccess();

        if ($this->access){
            $newUser = false;
            
        } else {
            $newUser = true;
        }
        
        $rights = $this->checkManager($newUser);


        if (preg_match('(g)', $rights)){
            $this->g_bind = $this->getGmodeBind();
            if($this->g_bind){
                if ($this->g_bind['in_use'] == 1){
                    // bind base on eid
                    $bind = $this->db->prepare('SELECT eid, manager_id, login, name, email, status1, status2, rights, training_date1 FROM access WHERE eid=:eid OR email=:email OR login=:login');
                    $bind->bindValue(':eid', $this->g_bind['bind_varible'], PDO::PARAM_STR);
                    $bind->bindValue(':email', $this->g_bind['bind_varible'], PDO::PARAM_STR);
                    $bind->bindValue(':login', $this->g_bind['bind_varible'], PDO::PARAM_STR);
                    $bind->execute();
                    if($bind->rowCount() == 1){
                        $data = $bind->fetch();
                        $_SESSION['g_mode'] = true;

                        $_SESSION["data"] = [
                            'name' => $data['name'],
                            'eid' =>  $data['eid'],
                            'login' => $data['login'],
                            'email' => $data['email'],
                            'manager_name' => '',
                            'manager_id' => $data['manager_id'],
                        ];
                        $this->data = $_SESSION["data"];
                        return 1;
                    }
                }
            }
        }
        $_SESSION['g_mode'] = false;
        return 0;
    }

    private function getGmodeBind()
    {
        $query = $this->db->prepare('SELECT bind_varible, in_use FROM g_user_bindings WHERE eid=:eid');
        $query->bindValue(':eid', $_SESSION['g_user_eid'], PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() == 1){
            return $query->fetch();
        } else {
            return 0;
        }
    }
    
    private function checkManager($newUser) : string
    {
        $is_manager = 1;

        if ($newUser){
            if($is_manager)
                return 'm';
            else
                return ''; 
        } else {
            if (!$is_manager && preg_match('(m)', $this->access['rights'])){
                return str_replace('m', '', $this->access['rights']);
            } else if (!preg_match('(m)',  $this->access['rights']) && $is_manager) {
                return 'm' . $this->access['rights'];
            } else {
                return $this->access['rights'];
            }
        }
    }

    /*
    // -=-=-=-=-=-=-=-
    // <-------------
    // 
    // -=-=-=-=-=-=-=-
    // GET USER DATA
    // -=-=-=-=-=-=-=-
    // ------------->
    */
    public function getaccess()
    {
        $query = $this->db->prepare('SELECT name, eid, login, email, training_date1, training_date2, status1, status2, rights, (SELECT name FROM access WHERE eid=A.manager_id) as manager_name FROM access A WHERE eid=:eid');
        $query->bindValue(':eid', $this->data['eid'], PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() == 1){
            $data = $query->fetch();
            $data['renew_time'] = $this->getRenewTime();
            $data['status1_valid'] = $this->getValidationDate($data['training_date1']);
            return $data;
        } else {
            return 0;
        }
    }

    public function getActiveRequest()
    {
        $query = $this->db->prepare('SELECT status, bid, bsid, building3, building2, building1, room4, room3, room2, room1, room3_justification, room2_justification, room1_justification, room4_justification, admin_comment, upload_cer_time FROM active_requests WHERE eid=:eid');
        $query->bindValue(':eid', $this->data['eid'], PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() == 1)
            return $query->fetch();
        else
            return [
                'status' => 0,
                'bid' => null,
                'bsid' => null,
                'building3' => 0,
                'building2' => 0,
                'building1' => 0,
                'room4' => 0,
                'room3' => 0,
                'room2' => 0,
                'room1' => 0,
                'room3_justification' => null,
                'room2_justification' => null,
                'room1_justification' => null,
                'room4_justification' => null,
                'admin_comment' => null,
                'upload_cer_time' => null,
            ];
    }

    public function getRequest()
    {
        $query = $this->db->prepare('SELECT status, bid, accept_manager_name, bsid, training_attendance, ohs, medical, building3, building2, building1, room4, room3, room2, room1, room3_justification, room2_justification, room1_justification, room4_justification, admin_comment, manager_comment FROM requests WHERE eid=:eid');
        $query->bindValue(':eid', $this->data['eid'], PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() == 1)
            return $query->fetch();
        else
            return [
                'status' => 0,
                'bid'=> null,
                'bsid' => null,
                'training_attendance'=> 0,
                'ohs' => 0,
                'medical' => 0,
                'building3' => 0,
                'building2' => 0,
                'building1' => 0,
                'room4' => 0,
                'room3' => 0,
                'room2' => 0,
                'room1' => 0,
                'room3_justification' => null,
                'room2_justification' => null,
                'room1_justification' => null,
                'room4_justification' => null,
                'admin_comment' => null,
                'manager_comment' => null,
                'accept_manager_name' => null,
            ];

    }
    private function getRenewTime()
    {
        return date('Y-m-d', (strtotime('+6 months', strtotime(date('Y-m-d')))));
    }
    private function getValidationDate($training_date1)
    {
        if (!empty($training_date1));
            return date('Y-m-d', (strtotime('+2 years',  strtotime($training_date1))));
    }

    /*
    // -=-=-=-=-=-=-=-
    // <-------------
    // 
    // -=-=-=-=-=-=-=-
    // GET PLATFORM DATA
    // -=-=-=-=-=-=-=-
    // ------------->
    */ 
    private function getActivePattern(){
        $query = $this->db->prepare('SELECT id, name, email_pattern, description, revision_num FROM active_patterns ORDER BY revision_num DESC LIMIT 1');
        $query->execute();
        return $query->fetch();
    }

    public function fetchData(){
        return [
            'lab' => $this->getaccess(),
            'req' => $this->getRequest(),
            'areq' => $this->getActiveRequest(),
            'apat' => $this->getActivePattern(),
        ];
    }

}
