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
    
    /**
     * @protected
     */
    public function changeLayout() {
        $which = strtolower(get_class($this));
        $check = substr($which, -1);
        if ($check == "s") {
            $which = substr($which, 0, -1);
        }
        $this->defaultLayout = "layouts/$which";
        $this->setLayout();
    }

    public function login() {
        $this->willRenderLayoutView = false;
        $view = $this->getActionView();
        $return = $this->_checkLogin();

        if ($return && isset($return["error"])) {
            $view->set("error", $return["error"]);
        }
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

            $scholar = Scholar::first(array("user_id = ?" => $user->id));
            if ($scholar) {
                $session->set('scholar', $scholar);
                
                $organization = Organization::first(array("id = ?" => $scholar->organization_id));
                $session->set('organization', $organization);
                self::redirect("/student");
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
                self::redirect("/teacher");
            }

            return array("error" => "Something went wrong please try later");

        } else {
            return null;
        }
        
    }

    protected function _saveUser($opts) {
        $name = RequestMethods::post("name");
        $email = RequestMethods::post("email");
        $phone = RequestMethods::post("phone");

        if ($opts["type"] == "scholar") {
            $dob = RequestMethods::post("dob");
            $address = RequestMethods::post("address");
            $parentName = RequestMethods::post("parent");
            $relation = RequestMethods::post("relation");
            $parentPhone = RequestMethods::post("parent_phone");
            $classroom = RequestMethods::post("classroom");
        }

        $last = \User::first(array(), array("id", "created"), "created", "desc");
        $id = $last->id;
        $prefix = strtolower(array_shift(explode(" ", $this->school->name)));
        foreach ($name as $key => $value) {
            if (Markup::checkValue($email["key"])) {
                $found = \User::first(array("email = ?" => $email["key"]));
                if ($found) {
                    return array("error" => "Email already exists for ". $name[$key]);
                }
            }
            $user = new \User(array(
                "name" => $value,
                "email" => $email[$key],
                "phone" => $phone[$key],
                "username" => $prefix. "_" .(++$id),
                "password" => Markup::encrypt("password"),
                "type" => $opts["type"]
            ));
            $user->save();

            if ($opts["type"] == "teacher") {
                $teacher = new \Educator(array(
                    "user_id" => $user->id,
                    "organization_id" => $this->school->id
                ));
                $teacher->save();
            } elseif ($opts["type"] == "scholar") {
                $parent = new \StudentParent(array(
                    "relation" => $relation[$key],
                    "phone" => $parentPhone[$key],
                    "name" => $parentName[$key]
                ));
                $parent->save();

                $student = new \Student(array(
                    "dob" => $dob[$key],
                    "parent_id" => $parent->id,
                    "address" => $address[$key],
                    "organization_id" => $this->school->id,
                    "roll_no" => "",
                    "user_id" => $user->id
                ));
                $student->save();

                $enrollment = new \Enrollment(array(
                    "scholar_id" => $student->id,
                    "classroom_id" => $classroom[$key]
                ));
                $enrollment->save();
            }
            
        }
    }
}
