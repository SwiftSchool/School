<?php
/**
 * The Users Controller
 *
 * @author Hemant Mann
 */
use Shared\Controller as Controller;

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
    public function _secure() {
        $user = $this->getUser();
        if (!$user) {
            header("Location: /login");
            exit();
        }
    }
    
    public function setUser($user) {
        $session = Registry::get("session");
        if ($user) {
            $session->set("user", $user->id);
        } else {
            $session->erase("user");
        }
        $this->_user = $user;
        return $this;
    }
}
