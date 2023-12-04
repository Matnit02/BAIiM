<?php

class Router
{
    private $handlers = [];
    private $notFoundHandler;
    private $storageHandler;
    private $user;
    private $db;

    private const METHOD_POST = 'POST';
    private const METHOD_GET = 'GET';

    private $callBackSetToNull = false;

    function appendData($user, $db)
    {
        $this->user = $user;
        $this->db = $db;
    }


    public function get(string $path, $policies, $handler): void
    {
        $this->addHandler(self::METHOD_GET, $policies, $path, $handler);
    }

    public function post(string $path, $policies, $handler): void
    {
        $this->addHandler(self::METHOD_POST, $policies, $path, $handler);
    }

    public function addStorageHandler($path, $files): void
    {
        $this->storageHandler = [
            'path' => $path,
            'files' => $files,
        ];
    }

    public function addNotFoundHandler($handler): void
    {
        $this->notFoundHandler = $handler;
    }

    private function addHandler(string $method, $policies, string $path, $handler): void
    {
        $this->handlers[$method . $path] = [
            'path' => $path,
            'policies' => $policies,
            'method' => $method,
            'handler' => $handler,
        ];
    }

    private function checkPolicies($params): bool
    {

        // W przyszłości to naprawie, ale jak na razie za mało mi płacą 😎
        // $user_eid = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_ENCODED);
        $user_eid = $_GET['user'];
        $debug = isset($_GET['debug']) ? 1 :0;

        if (!empty($user_eid)) {
            // ulepszyć w przyszłości - Maciek 2k20 - programista 40k 
            if ((ctype_digit($user_eid ) && strlen($user_eid)<=8) || strlen($user_eid)>64) {
                if (in_array('checkManAccess', $params)){
                    $select_query = $this->db->prepare(
                        'SELECT access.name FROM requests INNER JOIN access ON requests.eid = access.eid WHERE status=2 
                        AND ((access.eid="'.$user_eid.'" AND access.manager_id=:eid) OR (access.delegated_manager_id =:eid_s AND access.eid="'.$user_eid.'"))'
                    );
                    $select_query->bindValue(':eid_s', $_SESSION['data']['eid'], PDO::PARAM_STR);
                    $select_query->bindValue(':eid', $_SESSION['data']['eid'], PDO::PARAM_STR);
                    $select_query->execute();

                    if ($debug && strlen($user_eid)>64){
                        echo "POPRAWNIE PRZEPROWADZONO ATAK SQL INJECTION!!!";
                        var_dump($select_query);
                        $result = $select_query->fetch(PDO::FETCH_ASSOC);
                        var_dump($result);
                    }
                    
                    
                    return false;

                    if ($select_query->rowCount() == 1) {
                        return False;
                    }
                }

                if (in_array('checkLabAccess', $params)){
                    $select_query = $this->db->prepare('SELECT eid FROM requests WHERE eid=:user_eid AND status=5');
                    $select_query->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                    $select_query->execute();
                    if ($select_query->rowCount() == 1) {
                        return False;
                    }
                }
            
                if (in_array('checkActiveAccess', $params)){
                    $select_query = $this->db->prepare('SELECT eid FROM active_requests WHERE eid=:user_eid AND status=2');
                    $select_query->bindValue(':user_eid', $user_eid, PDO::PARAM_STR);
                    $select_query->execute();

                    if ($select_query->rowCount() == 1) {
                        return False;
                    }
                }
            }
        }
        return True;
    }

    public function run()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI']);
        $requestPath = $requestUri['path'];
        $method = $_SERVER['REQUEST_METHOD'];

        $callback = null;
        $parameters = [];

        if(strpos($requestPath,  $this->storageHandler['path']) !== false){
            $file_link = str_replace('/storage/', '', $requestPath);

            foreach($this->storageHandler['files'] as $file) {

                if ($file_link === $file['link']){
                    header('Content-type: '. $file['filename']);
                    return readfile($file['path'] . $file['filename']);
                }
            }
        } else {
            foreach ($this->handlers as $handler) {
                if ($handler['path'] === $requestPath && $method === $handler['method']){
                    $callback = $handler['handler'];

                    if(!empty($handler['policies'])) {
                        $this->callBackSetToNull = $this->checkPolicies($handler['policies']);
                    }
                }
            }
        }

        if (!$callback || $this->callBackSetToNull){
            if (!empty($this->notFoundHandler)) {
                $callback = $this->notFoundHandler;
            }
        }

        call_user_func_array($callback, [
            array_merge($_GET, $_POST),
            $this->db,
            $this->user['lab'],
            $this->user['req'],
            $this->user['areq'],
            $this->user['apat'],
        ]);
    }
}
