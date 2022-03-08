<?php

namespace src\Model;

use Twig\Node\Expression\VariadicExpression;

/**
 * Class Topic
 * @package src\Model
 */
class Topic
{
    private int $Topic_Author_Id;
    private \DateTime $Topic_Date;
    private string $Topic_Description;
    private string $Topic_ID;
    private string $Topic_Title="";
    private string $Topic_Url;
    private string $User_Id;
    private array $CategoriesId;

    /**
     * @return int
     */
    public function getTopicAuthorId()
    {
        return $this->Topic_Author_Id;
    }

    /**
     * @param int $Topic_Author_Id
     * @return User
     */
    public function setTopicAuthorId($Topic_Author_Id)
    {
        $this->Topic_Author_Id = $Topic_Author_Id;
        return $this;
    }

    /**
     * @return int
     */
    public function getTopicDate()
    {
        return $this->Topic_Date;
    }

    /**
     * @param int $Topic_Date
     * @return User
     */
    public function setTopicDate($Topic_Date)
    {
        $this->Topic_Date = $Topic_Date;
        return $this;
    }

    /**
     * @return string
     */
    public function getTopicDescription()
    {
        return $this->Topic_Description;
    }

    /**
     * @param string $Topic_Description
     * @return User
     */
    public function setTopicDescription($Topic_Description)
    {
        $this->Topic_Description = $Topic_Description;
        return $this;
    }

    /**
     * @return string
     */
    public function getTopicID()
    {
        return $this->Topic_ID;
    }

    /**
     * @param string $Topic_ID
     * @return User
     */
    public function setTopicID($Topic_ID)
    {
        $this->Topic_ID = $Topic_ID;
        return $this;
    }

    /**
     * @return string
     */
    public function getTopicTitle()
    {
       if(!empty($this->Topic_Title))
       {
           return $this->Topic_Title;
       }
       else{
           $this->Topic_Title="";
       }
    }

    /**
     * @param string $Topic_Title
     * @return User
     */
    public function setTopicTitle($Topic_Title)
    {
        $this->Topic_Title = $Topic_Title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTopicUrl()
    {
        if (empty($this->Topic_Url)) {
            $this->Topic_Url = "";
        }
        return $this->Topic_Url;
    }

    /**
     * @param string $Topic_Url
     * @return User
     */
    public function setTopicUrl(string $Topic_Url) : bool
    {
        $Topic_Url = strip_tags($Topic_Url);
        if (!empty($Topic_Url)) {
            $this->Topic_Url = $Topic_Url;
            return true;
        } else {
            $this->Topic_Url = "";
            $_SESSION['ERROR'] = ['message' => 'Erreur Slug'];
            return false;
        }
    }


    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->User_Id;
    }

    /**
     * @param string $User_Id
     * @return User
     */
    public function setUserId($User_Id)
    {
        $this->User_Id = $User_Id;
        return $this;
    }

    public function getTitle(): string
    {
        if (empty($this->Title)) {
            $this->Title = "";
        }
        return $this->Title;
    }

    /**
     * @param string $Title
     * @return bool
     */
    public function setTitle(string $Title): bool
    {
        $Title = strip_tags($Title);
        if (!empty($Title)) {
            $this->Title = $Title;
            return true;
        } else {
            $this->Title = "";
            $_SESSION['ERROR'] = ['message' => 'Erreur Titre'];
            return false;
        }
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        if (empty($this->Topic_Description)) {
            $this->Topic_Description = "";
        }
        return $this->Topic_Description;
    }

    /**
     * @param string $Topic_Description
     */
    public function setContent(string $Content): bool
    {
        $Content = htmlspecialchars($Content);
        if (!empty($Content)) {
            $this->Topic_Description = $Content;
            return true;
        } else {
            $this->Topic_Description = "";
            $_SESSION['ERROR'] = ['message' => 'Erreur Content'];
            return false;
        }
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        if (empty($this->Slug)) {
            $this->Slug = "";
        }
        return $this->Slug;
    }

    /**
     * @param string $Slug
     */
    public function setSlug(string $Slug): bool
    {
        $Slug = strip_tags($Slug);
        if (!empty($Slug)) {
            $this->Slug = $Slug;
            return true;
        } else {
            $this->Slug = "";
            $_SESSION['ERROR'] = ['message' => 'Erreur Slug'];
            return false;
        }
    }
    public function getCategoriesId(): array
    {
        if (empty($this->CategoriesId)) {
            $this->CategoriesId = [];
        }
        return $this->CategoriesId;
    }

    /**
     * @param array $CategoriesIdPost
     * @param array $categoriesIdBdd
     * @return bool
     */
    public function setCategoriesId(array $CategoriesIdPost, array $categoriesIdBdd): bool
    {
        if (!empty($CategoriesIdPost)) {
            // On vérifie que ce sont des catégories existantes
            foreach ($CategoriesIdPost as $categoryPost) {
                if (!in_array($categoryPost, $categoriesIdBdd)) {
                    // On garde les bonnes categories cochées dans la vue Twig getter
                    $this->CategoriesId = $CategoriesIdPost;
                    $_SESSION['ERROR'] = ['message' => 'Erreur Catégories id'];
                    return false;
                }
            }
            $this->CategoriesId = $CategoriesIdPost;
            return true;

        } else {
            $_SESSION['ERROR'] = ['message' => 'Erreur Catégories'];
            return false;
        }
    }
    public function setDateCreate(\DateTime $DateCreate): bool
    {
        if ($DateCreate != null) {
            $this->Topic_Date = $DateCreate;
            return true;
        } else {
            $this->Topic_Date = null;
            $_SESSION['ERROR'] = ['message' => 'Erreur Date'];
            return false;
        }
    }
    /**
     * @return string
     */
    public function getDateCreate(): \DateTime
    {
        return $this->Topic_Date;
    }


    /**
     * @param \PDO $bdd
     * @return
     * Retourne l'ensemble des topics du forum (avec auteur et catégorie(s))
     */
    public function sqlGetAll(\PDO $bdd)
    {
        $requete = $bdd->prepare("SELECT  `TOPIC_TITLE`,`t_topic`.`TOPIC_ID`,LEFT(`TOPIC_DESCRIPTION`,50) AS TOPIC_DESCRIPTION,`TOPIC_DATE`,`USER_NICKNAME`
                                        FROM `t_topic`
                                       inner join t_user on t_topic.user_id=t_user.user_id
                                        left JOIN `post` ON `post`.`TOPIC_ID`=`t_topic`.`TOPIC_ID`
                                        left JOIN `t_categorie` ON `t_categorie`.`CAT_ID`=`post`.`CAT_ID`
                                        group by `t_topic`.`TOPIC_ID` desc ");
        $requete->execute();


        return $requete->fetchAll(\PDO::FETCH_CLASS, "src\Model\Topic");

    }
    /**
     * Récupère les topics admin paginés avec ou sans recherche
     * @param $firsResult
     * @param $perPage
     * @return mixed
     */
    public function sqlGetList($firstResult, $perPage, $search = [])
    {

        $bdd = BDD::getInstance();
        $sql = "SELECT * FROM t_topic inner join t_user on t_topic.user_id=t_user.user_id";
        if (!empty($search))
        {
            $i = 0;
            foreach ($search as $word){
                if ($i == 0){

                    $sql .= " WHERE ";
                }
                else{
                    $sql .= " AND ";
                }
                $sql .= " `TOPIC_DESCRIPTION`  LIKE :word$i";
                $i++;

           }
        }

        $sql .= " ORDER BY `TOPIC_ID` DESC LIMIT :first, :perPage";
        $query = $bdd->prepare($sql);
        $query->bindValue(':first', $firstResult, \PDO::PARAM_INT);
        $query->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        if (!empty($search))
        {
            $i = 0;
            foreach ($search as $word) {
                $query->bindValue(':word'.$i, '%'.$word.'%', \PDO::PARAM_STR);
                $i++;
            }
        }



        $query->execute();

        return $query->fetchAll(\PDO::FETCH_OBJ);

    }


    public function sqlGetOne(\PDO $bdd,int $id)
    {
        $sql = "SELECT `TOPIC_TITLE`,`TOPIC_URL`,`t_topic`.`TOPIC_ID`,`TOPIC_DESCRIPTION`,`TOPIC_DATE`,`USER_NICKNAME`
                FROM `t_topic`
                INNER JOIN `t_user` ON `t_topic`.`USER_ID`=`t_user`.`USER_ID`
                INNER JOIN `post` ON `post`.`TOPIC_ID`=`t_topic`.`TOPIC_ID`
                INNER JOIN `t_categorie` ON `t_categorie`.`CAT_ID`=`post`.`CAT_ID`
                where `t_topic`.`TOPIC_ID`= " . $id . " ";
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();


        $result = $query->fetch(\PDO::FETCH_OBJ);
        return $result;
    }



/**
 * Retourne les catégories d'un topic
 * @param \PDO $bdd
 * @param int $id
 * @return array
 */
public function sqlGetCategories(\PDO $bdd, int $id)
{
    $sql = "SELECT `CAT_ID` FROM `post` WHERE `TOPIC_ID` = :id";
    $query = $bdd->prepare($sql);
    $query->bindValue(':id', $id, \PDO::PARAM_INT);
    $query->execute();
    return $query->fetchAll(\PDO::FETCH_COLUMN);
}

/**
 * Retourne les médias d'un topic
 * @param \PDO $bdd
 * @param int $id
 * @return array
 */

public function sqlAdd(\PDO $bdd): int
{
    $sql = $sql = "INSERT INTO `t_topic` (`USER_ID`, `TOPIC_TITLE`, `TOPIC_DATE`, `TOPIC_URL`, `TOPIC_DESCRIPTION` )
                VALUES (:AuthorId, :Title, :DateCreate, :Slug, :Content )";

    $query = $bdd->prepare($sql);
 $query->bindValue(':AuthorId', $this->Topic_Author_Id, \PDO::PARAM_INT);
    $query->bindValue(':Title',$this->getTitle() , \PDO::PARAM_STR);

    $query->bindValue(':DateCreate', $this->Topic_Date->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
    $query->bindValue(':Slug', $this->getSlug(), \PDO::PARAM_STR);
    $query->bindValue(':Content', $this->Topic_Description, \PDO::PARAM_STR);
    if (!$query->execute()) {
        throw new \Exception("Une erreur est survenue lors de l'insertion du topic");
    }
    $this->Topic_ID = $bdd->lastInsertId();
    foreach ($this->CategoriesId as $categoryId) {
        $sql = "INSERT INTO `post` (`TOPIC_ID`, `CAT_ID`) VALUES ($this->Topic_ID, :CatId)";
        $query = $bdd->prepare($sql);
        $query->bindValue(':CatId', $categoryId, \PDO::PARAM_INT);
        if (!$query->execute()) {
            throw new \Exception("Une erreur est survenue lors de l'insertion du topic");
        }
    }
    return $this->Topic_ID;

}
    public function sqlUpdate() :Topic
    {

        // Connexion bdd
        $bdd = BDD::getInstance();

        // MAJ table t_topic
        $sql = 'UPDATE `t_topic` SET 
                       `TOPIC_TITLE` = :title, 
                       `TOPIC_URL` =   :slug,
                       `TOPIC_DESCRIPTION` =  :content WHERE `TOPIC_ID` = :id';

        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $this->getTopicID(), \PDO::PARAM_INT);
        $query->bindValue(':title', $this->getTopicTitle(), \PDO::PARAM_STR);
        $query->bindValue(':slug', $this->getTopicUrl(), \PDO::PARAM_STR);
        $query->bindValue(':content', $this->getTopicDescription(), \PDO::PARAM_STR);

        if (!$query->execute()) {
            throw new \Exception("Une erreur est survenue lors de l'insertion du topic");
        }

        // MAJ table post (categorie)
        if (!empty($this->getCategoriesId())){

            // On supprime les categories du topic
            $query = $bdd->prepare('DELETE FROM `post` WHERE `TOPIC_ID` = :id');
            $query->bindValue(':id', $this->getTopicID(), \PDO::PARAM_INT);
            if (!$query->execute()) {
               throw new \Exception("Une erreur est survenue lors de la suppression des catégories du topic");
            }
            // On insère les nouvelles catégories postées
            foreach ($this->getCategoriesId() as $catId) {
                $query = $bdd->prepare("INSERT INTO `post` (`TOPIC_ID`, `CAT_ID`) VALUES (:id, :CatId)");
                $query->bindValue(':id', $this->getTopicID(), \PDO::PARAM_INT);
                $query->bindValue(':CatId', $catId, \PDO::PARAM_INT);
                if (!$query->execute()) {
                    throw new \Exception("Une erreur est survenue lors de l'insertion des catégories du topic");
                }
            }
        }



        return $this;
    }

    /**
     * Compte le nombre total de topic ou en fonction d'une recherche en bdd
     * @return int
     */

/**
 * Supprime un Topic du forum
 * @param \PDO $bdd
 * @param int $id
 * @return int
 */
public function sqlDelete(\PDO $bdd, int $id)
{
    try {
        $sql = 'DELETE FROM `t_message` WHERE `TOPIC_ID` = :id';
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        // TABLE JOINTURE t_message
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        // TABLE JOINTURE associate

        $sql = 'DELETE FROM `post` WHERE `TOPIC_ID` = :id';
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        // TABLE JOINTURE post
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        // TABLE t_topic
        $sql = 'DELETE FROM `t_topic` WHERE `TOPIC_ID` = :id';
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() === 0) {
            throw new \Exception("Le topic id : $id n'existe pas");
        }
    } catch (\Exception $e) {
        $_SESSION['ERROR'] = ['message' => $e->getMessage()];
    }
}


    public function sqlCount($search = []): int
    {
        $bdd = BDD::getInstance();
        // On détermine le nombre total de topics
        $sql = 'SELECT COUNT(*) AS nb_topics FROM `t_topic`';

        if (!empty($search))
        {
            $i = 0;
            foreach ($search as $word) {
                if ($i == 0){
                    $sql .= " WHERE ";
                }
                else{
                    $sql .= " AND ";
                }
                $sql .= " `TOPIC_DESCRIPTION` LIKE :word$i";
                $i++;
            }
        }

        $query = $bdd->prepare($sql);

        if (!empty($search))
        {
            $i = 0;
            foreach ($search as $word)
            {
                $query->bindValue(':word'.$i,'%'.$word.'%', \PDO::PARAM_STR);

                $i++;

            }
        }
        $query->execute();

        $result = $query->fetch(\PDO::FETCH_NUM);

        return $result[0];

    }

}
