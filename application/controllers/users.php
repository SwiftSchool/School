<?php
/**
 * The Users Controller
 *
 * @author Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Shared\Markup as Markup;
use Framework\Registry as Registry;

class Users extends Controller {
    /**
     * @protected
     */
    public function _admin() {
        if (!$this->user->admin) {
            self::redirect("/404");
        }
    }

    /**
     * @protected
     */
    public function _session() {
        if ($this->user && $this->user->type != 'central') {
            self::redirect("/". $this->user->type ."s/");
        } elseif ($this->user->type == 'central') {
            self::redirect("/central");
        }
    }

    /**
     * @protected
     */
    public function _secure() {
        $user = $this->getUser();
        if (!$user) {
            header("Location: /login");
            exit();
        }
    }
    
    protected function setUser($user) {
        $session = Registry::get("session");
        if ($user) {
            $session->set("user", $user->id);
        } else {
            $session->erase("user");
        }
        $this->_user = $user;
        return $this;
    }

    /**
     * @protected
     */
    public function changeLayout() {
        $which = $this->user->type;
        $this->defaultLayout = "layouts/$which";
        $this->setLayout();
    }

    /**
     * @before _session
     */
    public function login() {
        $this->willRenderLayoutView = false;
        $view = $this->getActionView();
        $return = $this->_checkLogin();

        if ($return && isset($return["error"])) {
            $view->set("error", $return["error"]);
        }
    }

    public function logout() {
        $which = strtolower(get_class($this));

        switch ($which) {
            case 'students':
            case 'teachers':
                $location = "/$which/login";
                $func = "set".ucfirst($this->user->type);   
                $this->$func(false);     
                break;
            
            default:
                $location = '/';
                break;
        }

        $this->setUser(false);
        self::redirect($location);
    }

    protected function _checkLogin() {
        if (RequestMethods::post("action") == "logmein") {
            $username = RequestMethods::post("username");
            $password = RequestMethods::post("password");
            $user = User::first(array("username = ?" => $username, "live = ?" => true));

            if (!$user) {
                return array("error" => "Invalid username/password");
            }

            if (!Markup::checkHash($password, $user->password)) {
                return array("error" => "Invalid username/password");
            }
            $session = Registry::get("session");
            $this->setUser($user);

            $model = ucfirst(strtolower($user->type));
            if ($model != 'Central') {
                $person = $model::first(array("user_id = ?" => $user->id));
                if ($person->school_id) {
                    $school = School::first(array("id = ?" => $person->school_id));
                    $session->set('school', $school);
                }
                $func = "set".$model;
                $this->$func($person);
                self::redirect("/". $user->type."s/dashboard");
            } else {
                self::redirect("/central");
            }
        } else {
            return null;
        }
        
    }
}
