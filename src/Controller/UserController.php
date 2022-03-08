<?php

namespace src\Controller;


use src\Model\User;
use src\Model\BDD;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class UserController extends AbstractController
{


    public function index()
    {
        USER::is_admin();
        $user = new User();
        $userList = $user->User_list(BDD::getInstance());
        return $this->twig->render("User/index.html.twig", [
            "userList" => $userList,
        ]);
    }
    public function register()
    {
        $user = new User();
        if (isset($_POST['submit'])) {
            if (empty($_POST['mail'])) {
                $err = "Le champ mail ne doit pas être vide !";
            } elseif (!isset($_POST['mail'])) {
                $err = "Le champ mail ne doit pas être vide !";
            }
            if ($_POST['password_first'] !== $_POST['password_confirm']) {
                $err = "Les mots de passe ne correspondent pas";
            } else {
                if (strip_tags($_POST['mail']) && !empty($_POST['mail'])) {
                    $user->setUSER_MAIL($_POST['mail']);
                } else {
                    $err = "Le mail est vide ou incorrect";
                }
                if (strip_tags($_POST['username']) && !empty($_POST['username'])) {
                    $user->setUSER_NICKNAME($_POST['username']);
                } else {
                    $err = "Le nom d'utilisateur est vide ou incorrect";
                }
                if (strip_tags($_POST['password_first']) && !empty($_POST['password_first'])) {
                    $user->setUSER_PASSWORD($_POST['password_first']);
                } else {
                    $err = "Le mot de passe est vide ou incorrect";
                }
                $user->setROLE_ID(2); // user role normal (2) pour le forum, les rôles admin (1) sont en dur en bdd
                try {
                    $req = $user->User_register($user);
                    if ($req == "Le mail existe deja") {
                        $err = $req;
                    } elseif ($req == "Le pseudo existe deja") {
                        $err = $req;
                    } else {
                        header("Location:/User/login");
                        exit;
                    }
                } catch (Exception $e) {
                    $err = $e;
                }
            }
            return $this->twig->render("User/register.html.twig", [
                "error" => $err
            ]);
        } else {
            return $this->twig->render("User/register.html.twig");
        }
    }
    public function login()
    {
        $error = "";
        $user = new User;
        if (isset($_POST['submit'])) {

            $user->setUSER_MAIL($_POST['email']);
            $user->setUSER_PASSWORD($_POST["password"]);
            $req = $user->User_login($user);
            if ($req) {
                if (isset($_SESSION) && !empty($_SESSION)) {

                    // FIX BUG
                    // Si role admin on redirige vers l'administration du site sinon page d'accueil
                    if (isset($_SESSION['user']) && $_SESSION['user']['Role_ID'] == 1) {
                        header("Location: /admin");
                        exit();
                    } else {
                        header("Location: /");
                        $error = "Acces interdit";
                        exit();
                    }
                } else {
                    $error = "Le mot de passe ou le mail est incorrect, veuillez recommencer";
                }
            } else {
                $error = "Une erreur est survenue : " . $req;
            }
        }
        return $this->twig->render("User/login.html.twig", [
            'session' => $_SESSION,
            'error' => $error
        ]);
    }
    public function edit($id)
    {
        $user = new User;

        $u = $user->get_user($id[0]);
        $id = $u['USER_ID'];


        if (isset($_POST["submit"])) {

            if (isset($_POST['email']) && !empty($_POST['email'])) {
                $email = $_POST["email"];
                $user->setUSER_MAIL($email);
            }
            if (isset($_POST['nickname']) && !empty($_POST['nickname'])) {
                $nickname = $_POST["nickname"];
                $user->setUSER_NICKNAME($nickname);
            }
            if (isset($_POST['role']) && !empty($_POST['role'])) {
                $role = $_POST['role'];
                $user->setROLE_ID($role);
            }


            try {
                $req = $user->User_edit($id);


                if ($req) {
                    header("Location:/user/index");
                    exit();
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            return $this->twig->render("User/edit.html.twig", [
                "u" => $u,
                "username" => $u["USER_NICKNAME"],
                "mail" => $u["USER_MAIL"]
            ]);
        }
    }





    public function show($id)
    {
        $user = new User;
        $user->setUSER_ID($id["0"]);
        $userRow = $user->get_user($id["0"]);
        return $this->twig->render("User/show.html.twig", [
            "user" => $userRow
        ]);
    }
    public function sendPassword($email)
    {

        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'localhost';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            /* $mail->Username   = 'antonindalkolmo';                     //SMTP username
            $mail->Password   = 'antonindalkolmo_91a18780b592d81ad3f1e8208e89971a';  */                              //SMTP password
            $mail->Port       = 25;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            //Recipients
            $mail->setFrom('dev@yopmail.com', 'Mailer');
            $mail->addAddress('antonin.dalkolmo@gmail.com', 'Antonin Dalkolmo');     //Add a recipient


            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = "Test Dev ";
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function deleteuser($id)
    {

        $user = new User;
        $user->setUSER_ID($id["0"]);
        $user = $user->User_delete($user->getUSER_ID());
        header("Location:/user/index");
    }

    /**
     * Déconnecte un utilisateur
     * @author Tony
     */
    public function logout(): void
    {

        // Instance model User
        $user = new User();

        // Si l'utilisateur est connecté on vide la SESSION user
        if ($user->is_logged_in()) {
            unset($_SESSION['user']);
        }

        // Redirection page login
        header("Location:/user/login");
        exit();
    }
}
