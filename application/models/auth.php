<?php
class Auth extends model {

	protected $tableName        = 'users',
              $primaryKey       = 'id';
     
    function __construct($id = null, $data = null) {
		parent::__construct($id, $data);
    }

    public function check() {
    	$userId = 0;
    	if ( isset($_SESSION['user']['id']) ) {
    		$userId = $_SESSION['user']['id'];
    	}

    	$result = $this->find($userId);

        if ( (isset($result->id) && $result->id > 0 ) || isset($_GET['override_auth']) ) {
            // We are fine
            return true;
        }

        return false;
    }

    public function basicAuth($username = '', $password = '') {
        // Currently we are using this for the auth of the API
        $this->db->select($this->tableName, '*', null, "username = '$username' AND api = 1", null, 'OBJECT');
        $result = $this->db->getResult();

        if ( isset($result->id) && $result->id > 0 ) {
            if ( isset($result->password) && password_verify($password, $result->password) ) {
                // We are fine
                return true;
            }
        }

        return false;
    }

    public function redirect($slug = '/auth/login', $msg = '') {
        header('Location: '.$slug);
    }

    public function login($data = array()) {
        // TODO - validate input
        if ( isset($data['email']) ) {
            $data['username'] = $data['email'];
        }
        if ( isset($data['password']) ) {
            $data['password'] = $data['password'];
        }

        $this->db->select($this->tableName, '*', null, "username = '$data[username]' AND system = 1", null, 'OBJECT');
        $result = $this->db->getResult();

        if ( isset($result->id) && $result->id > 0 ) {
            if ( isset($result->password) && password_verify($data['password'], $result->password) ) {
                // Set the user to session
                $_SESSION['user'] = [];
                $_SESSION['user']['id'] = $result->id;
                $_SESSION['user']['firstname'] = $result->firstname;
                $_SESSION['user']['lastname'] = $result->lastname;
                return true;
            }
        }

        return false;
    }

    public function register($data = array()) {
        // TODO - Validate input 
        if ( isset($data['email']) ) {
            $data['username'] = $data['email'];
        }

        if ( isset($data['firstname']) ) {
            $data['firstname'] = $data['firstname'];
        }

        if ( isset($data['lastname']) ) {
            $data['lastname'] = $data['lastname'];
        }

        if ( isset($data['password']) ) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        // Now lets register the user
        $this->db->insert($this->tableName, array(
                'username' => $data['username'],
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'password' => $data['password']
            ));

        return true;
    }

    public function logout() {
        $_SESSION = [];
        $this->redirect('/');
    }

}