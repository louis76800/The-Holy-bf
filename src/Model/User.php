<?php

namespace src\Model;

use PDO;

class User
{
    private int $USER_ID;
    private int $ROLE_ID;
    private string $USER_MAIL;
    private string $USER_NICKNAME;
    private string $USER_PASSWORD;

    /**
     * Get the value of USER_ID
     */
    public function getUSER_ID()
    {
        return $this->USER_ID;
    }

    /**
     * Set the value of USER_ID
     *
     * @return  self
     */
    public function setUSER_ID(?int $USER_ID)
    {
        $this->USER_ID = $USER_ID;

        return $this;
    }

    /**
     * Get the value of ROLE_ID
     */
    public function getROLE_ID()
    {
        return $this->ROLE_ID;
    }

    /**
     * Set the value of ROLE_ID
     *
     * @return  self
     */
    public function setROLE_ID($ROLE_ID)
    {
        $this->ROLE_ID = $ROLE_ID;

        return $this;
    }

    /**
     * Get the value of USER_MAIL
     */
    public function getUSER_MAIL()
    {
        return $this->USER_MAIL;
    }

    /**
     * Set the value of USER_MAIL
     *
     * @return  self
     */
    public function setUSER_MAIL($USER_MAIL)
    {
        $this->USER_MAIL = $USER_MAIL;

        return $this;
    }

    /**
     * Get the value of USER_NICKNAME
     */
    public function getUSER_NICKNAME()
    {
        return $this->USER_NICKNAME;
    }

    /**
     * Set the value of USER_NICKNAME
     *
     * @return  self
     */
    public function setUSER_NICKNAME($USER_NICKNAME)
    {
        $this->USER_NICKNAME = $USER_NICKNAME;

        return $this;
    }

    /**
     * Get the value of USER_PASSWORD
     */
    public function getUSER_PASSWORD()
    {
        return $this->USER_PASSWORD;
    }

    /**
     * Set the value of USER_PASSWORD
     *
     * @return  self
     */
    public function setUSER_PASSWORD($USER_PASSWORD)
    {
        $this->USER_PASSWORD = $USER_PASSWORD;

        return $this;
    }

    /**
     ** Read users on BDD
     *  
     */
    public function User_list(\PDO $bdd)
    {
        try {
            $requete = $bdd->prepare("SELECT * FROM t_user");
            $requete->execute();
            return $requete->fetchAll(\PDO::FETCH_CLASS, "src\Model\User");
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     ** Get a User by ID 
     */
    public function get_user(int $id)
    {
        $bdd = BDD::getInstance();

        try {
            $requete = $bdd->prepare("SELECT * FROM t_user WHERE USER_ID=:USER_ID");
            $requete->execute([
                "USER_ID" => $id
            ]);
            $result =  $requete->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /**
     ** Fonction de verification du mail en bdd
     *
     */
    public function verif_email(User $user)
    {
        $bdd = BDD::getInstance();

        $mail = $user->getUSER_MAIL();
        try {
            $requete = $bdd->prepare("SELECT USER_MAIL FROM t_user WHERE USER_MAIL = '$mail';");
            /*  var_dump($requete);
            die; */
            $requete->execute([
                "USER_MAIL" => $mail,
            ]);
            $result = $requete->fetchAll(PDO::FETCH_ASSOC);
            if ($result != null) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     ** Fonction de verification du pseudo en bdd
     *
     */
    public function verif_username(User $user)
    {
        $bdd = BDD::getInstance();

        $username = $user->getUSER_NICKNAME();
        try {
            $requete = $bdd->prepare("SELECT USER_NICKNAME FROM t_user WHERE USER_NICKNAME = '$username';");
            $requete->execute([
                "USER_MAIL" => $username,
            ]);
            $result = $requete->fetchAll(PDO::FETCH_ASSOC);
            if ($result != null) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     ** Add a User in BDD
     * 
     */
    public function User_register(User $user)
    {
        $bdd = BDD::getInstance();
        if ($user->verif_email($user) == true && $user->verif_username($user) == true) {
            try {
                $hashedpass = password_hash($user->getUSER_PASSWORD(), PASSWORD_DEFAULT);

                $requete = $bdd->prepare("INSERT INTO t_user(ROLE_ID, USER_MAIL, USER_NICKNAME, USER_PASSWORD) VALUES(:ROLE_ID, :USER_MAIL, :USER_NICKNAME, :USER_PASSWORD)");
                $req = $requete->execute([
                    "ROLE_ID" => 2,
                    "USER_MAIL" => $user->getUSER_MAIL(),
                    "USER_NICKNAME" => $user->getUSER_NICKNAME(),
                    "USER_PASSWORD" => $hashedpass
                ]);
                return true;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } else {
            if ($user->verif_email($user) == false) {
                $e = "Le mail existe deja";
                return $e;
            }
            if ($user->verif_username($user) == false) {
                $e = "Le pseudo existe deja";
                return $e;
            }
            return false;
        }
    }


    /**
     ** Login function to verify credentials on BDD
     * 
     */
    public function User_login(User $user)
    {
        try {
            $bdd = BDD::getInstance();
            $mail = $user->getUSER_MAIL();
            $pass = $user->getUSER_PASSWORD();
            $requete = $bdd->prepare("SELECT * FROM t_user WHERE USER_MAIL=:USER_MAIL");
            $requete->execute([
                "USER_MAIL" => $mail,
            ]);
            $result = $requete->fetch(PDO::FETCH_ASSOC);
            if ($requete->rowCount() > 0) {
                if (password_verify($pass, $result['USER_PASSWORD'])) {
                    $_SESSION["user"] = [
                        "Id" => $result['USER_ID'],
                        "Username" => $result['USER_NICKNAME'],
                        "Email" => $result['USER_MAIL'],
                        "Role_ID" => $result["ROLE_ID"],
                        "Role" => $user->Get_role(
                            $result['ROLE_ID']
                        )
                    ];
                    return $_SESSION;
                } else {
                    $err = "Le mot de passe est incorrect";
                    return $err;
                }
            } else {
                $err = "Aucun utilisateur trouvé pour le mail " . $mail;
                return $err;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     ** Function to get the user's role name 
     */
    public function Get_role(int $roleid)
    {
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare("SELECT ROLE_NAME FROM t_role WHERE ROLE_ID=:ROLE_ID");
        $requete->execute([
            "ROLE_ID" => $roleid
        ]);
        $res = $requete->fetch(PDO::FETCH_ASSOC);
        return $res["ROLE_NAME"];
    }

    /**
     ** Function to check if a user is logged in
     * 
     */
    public function is_logged_in()
    {
        if (isset($_SESSION['user'])) {
            return true;
        }
    }

    /**
     ** Function to check if a user is admin
     * 
     */
    public static function is_admin()
    {

        if (!isset($_SESSION['user']) || (int) $_SESSION['user']['Role_ID'] != 1) {

            header("Location:/user/login");
            exit();
        }
    }

    public function User_edit($id)
    {
        $bdd = BDD::getInstance();

        try {
            $requete = $bdd->prepare('UPDATE t_user SET ROLE_ID = :role, USER_MAIL = :email, USER_NICKNAME = :pseudo WHERE USER_ID = :id');
            $role = $this->getROLE_ID();
            $email = $this->getUSER_MAIL();
            $name = $this->getUSER_NICKNAME();
            $requete->bindValue(':role', $role, \PDO::PARAM_INT);
            $requete->bindValue(':email', $email, \PDO::PARAM_STR);
            $requete->bindValue(':pseudo', $name, \PDO::PARAM_STR);
            $requete->bindValue(':id', $id, \PDO::PARAM_INT);
            $requete->execute();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function User_delete($id)
    {
        $bdd = BDD::getInstance();

        $requete = $bdd->prepare('UPDATE `t_user` SET USER_NICKNAME = :name, USER_MAIL = :email, USER_PASSWORD = :passwd, ROLE_ID = :role WHERE USER_ID = :id');
        $name = "Anonymous";
        $email = "anonymous@holy-bf.fr";
        $passwd = "";
        $role = 0;
        $requete->bindValue(':name', $name, \PDO::PARAM_STR);
        $requete->bindValue(':email', $email, \PDO::PARAM_STR);
        $requete->bindValue(':passwd', $passwd, \PDO::PARAM_STR);
        $requete->bindValue(':role', $role, \PDO::PARAM_INT);
        $requete->bindValue(':id', $id, \PDO::PARAM_INT);
        $requete->execute();
        return true;
    }
}
