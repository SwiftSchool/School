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

class Auth extends Controller {

    public function __construct($options = array()) {
        parent::__construct($options);

        $headers = getallheaders();
        if (isset($headers["X-Access-Token"])) {
            $type = strtolower($headers["X-App"]);
            $meta = Meta::first(array("property = ?" => "user", "meta_key = ?" => $type."-app", "meta_value = ?" => $headers["X-Access-Token"]), array("property_id"));
            if ($meta) {
                $this->_appLogin($meta, $type);
            }
        }
    }

    /**
     * Login the user if the request if from App
     */
    protected function _appLogin($meta, $type) {
        $session = Registry::get("session");
        $user = User::first(array("id = ?" => $meta->property_id));
        $this->setUser($user);
        
        switch ($type) {
            case 'student':
                $scholar = Scholar::first(array("user_id = ?" => $user->id));
                $session->set("scholar", $scholar);
                $organization = Organization::first(array("id = ?" => $scholar->organization_id));
                break;
            
            case 'teacher':
                $educator = Educator::first(array("user_id = ?" => $user->id));
                $session->set("educator", $educator);
                $organization = Organization::first(array("id = ?" => $educator->organization_id));
                break;
        }
        $session->set("organization", $organization);
    }
    
    public function login() {
        $this->setSEO(array("title" => "Login"));
        $view = $this->getActionView();
        $return = $this->_checkLogin();

        $data = array();

        if (isset($return["error"])) {
            $data["error"] = $return["error"];
        } elseif (isset($return["success"])) {
            $data = $return;
        }
        $view->set($data);
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

            if ($user->admin) {
                self::redirect("/admin");
            }

            $headers = getallheaders();
            $scholar = Scholar::first(array("user_id = ?" => $user->id));
            if ($scholar) {
                $session->set('scholar', $scholar);
                
                $organization = Organization::first(array("id = ?" => $scholar->organization_id));
                $session->set('organization', $organization);
                if (isset($headers["X-Student-App"])) {
                    $meta = $this->_meta($user, "student");
                    return array("success" => true, "meta" => $meta, "scholar" => $scholar);
                } else {
                    self::redirect("/student");
                }
            }

            $organization = Organization::first(array("user_id = ?" => $user->id));
            if ($organization) {
                $session->set('organization', $organization);
                self::redirect("/school");
            }

            $educator = Educator::first(array("user_id = ?" => $user->id));
            if ($educator) {
                $session->set('educator', $educator);
                
                $organization = Organization::first(array("id = ?" => $educator->organization_id));
                $session->set('organization', $organization);
                if (isset($headers["X-Teacher-App"])) {
                    $meta = $this->_meta($user, "teacher");
                    return array("success" => true, "meta" => $meta, "educator" => $educator);
                } else {
                    self::redirect("/teacher");
                }
            }

            return array("error" => "Something went wrong please try again later");
        } else {
            return array("error" => "Invalid Request");
        }   
    }

    /**
     * Creates new Users with unique username
     * @return null
     */
    protected function _createUser($opts) {
        $name = $opts["name"];
        $email = $opts["email"];
        $phone = $opts["phone"];

        $last = \User::first(array(), array("id"), "created", "desc");
        $id = (int) $last->id + 1;
        $prefix = strtolower(array_shift(explode(" ", $this->organization->name)));
        
        if (Markup::checkValue($email)) {
            $found = \User::first(array("email = ?" => $email), array("id"));
            if ($found) {
                throw new \Exception("Email already exists");
            }
        }
        if (Markup::checkValue($phone)) {
            $found = \User::first(array("phone = ?" => $phone), array("id"));
            if ($found) {
                throw new \Exception("Phone number already exists");
            }
        }
        if (empty(Markup::checkValue($name))) {
            return NULL;
            // throw new \Exception("Please provide a name");
        }
        $user = new \User(array(
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "username" => $prefix. "_" .$id,
            "password" => Markup::encrypt("password")
        ));
        $user->save();

        return $user;
    }

    protected function reArray(&$array) {
        $file_ary = array();
        $file_keys = array_keys($array);
        $file_count = count($array[$file_keys[0]]);
        
        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $array[$key][$i];
            }
        }

        return $file_ary;
    }

    /**
     * The method checks whether a file has been uploaded. If it has, the method attempts to move the file to a permanent location.
     * @param string $name
     * @param array $opts
     *
     * @return string|boolean Returns the file name on moving the file successfully else return false
     */
    protected function _upload($name, $opts = array()) {
        $type = isset($opts["type"]) ? $opts["type"] : "images";
        if (isset($_FILES[$name])) {
            $file = $_FILES[$name];
            
            /*** Create Directory if not present ***/
            $path = APP_PATH . "/public/assets/uploads/{$type}";
            exec("mkdir -p $path");
            $path .= "/";

            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            if (empty($extension)) {
                return false;
            }
            /*** Check mime type before moving ***/
            if (isset($opts["mimes"])) {
                if (!preg_match("/^{$opts['mimes']}$/", $extension)) {
                    return false;
                }
            }
            $filename = Markup::uniqueString() . ".{$extension}";
            if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
                return $filename;
            } else {
                return FALSE;
            }
        } else {
            return false;
        }
    }

    /**
     * Sets the meta for app login
     */
    private function _meta($user, $app) {
        $meta = Meta::first(array("property = ?" => "user", "property_id = ?" => $user->id, "meta_key = ?" => $app."-app"));
        if (!$meta) {
            $meta = new Meta(array(
                "property" => "user",
                "property_id" => $user->id,
                "meta_key" => $app. "-app",
                "meta_value" => Markup::uniqueString(44)
            ));
            $meta->save();
        }
        return $meta;
    }
}
