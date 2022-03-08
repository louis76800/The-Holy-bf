<?php

namespace src\Model;

/**
 * Class Article
 * @author Tony
 * @package src\Model
 */
class Article implements \JsonSerializable
{
    private int $Id;
    private int $AuthorId;
    private string $Title;
    private string $Content;
    private string $Slug;
    private \DateTime $DateCreate;

    private array $CategoriesId;
    private array $MediasId;

    private Media $Pictures;
    private Category $categories;

    /**
     * Implémentation Interface JsonSerialize API
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "date" => $this->getDateCreate()->format('Y-m-d'),
            "title" => $this->getTitle(),
            "content" => $this->getContent(),
            "author" => $this->getAuthorId()
        ];
    }

    /**
     * @return Media
     */
    public function getPictures(): Media
    {
        return $this->Pictures;
    }

    /**
     * @param Media $Pictures
     * @return Article
     */
    public function setPictures(Media $Pictures): Article
    {
        $this->Pictures = $Pictures;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->Id;
    }

    /**
     * @param $Id
     * @return $this
     */
    public function setId(int $Id)
    {
        $this->Id = $Id;
        return $this;
    }

    /**
     * @return int
     */
    public function getAuthorId(): int
    {
        if (empty($this->AuthorId)) {
            $this->AuthorId = 0;
        }
        return $this->AuthorId;
    }

    /**
     * @param int $AuthorId
     * @return mixed
     */
    public function setAuthorId(int $AuthorId)
    {
        if (is_int($AuthorId) && $AuthorId > 0) {
            $this->AuthorId = $AuthorId;
            return $this;
        } else {
            $this->AuthorId = 0;
            $_SESSION['ERROR'] = ['message' => 'Erreur Auteur : vous devez être connecté'];
            return false;
        }
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        if (empty($this->Title)) {
            $this->Title = "";
        }
        return $this->Title;
    }

    /**
     * @param string $Title
     * @return mixed
     */
    public function setTitle(string $Title)
    {
        $Title = strip_tags($Title);
        if (!empty($Title)) {
            $this->Title = $Title;
            return $this;
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
        if (empty($this->Content)) {
            $this->Content = "";
        }
        return $this->Content;
    }

    /**
     * @param string $Content
     */
    public function setContent(string $Content)
    {
        $Content = htmlspecialchars($Content);
        if (!empty($Content)) {
            $this->Content = $Content;
            return $this;
        } else {
            $this->Content = "";
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

    /**
     * @return string
     */
    public function getDateCreate(): \DateTime
    {
        return $this->DateCreate;
    }

    /**
     * @param \DateTime $DateCreate
     */
    public function setDateCreate(\DateTime $DateCreate)
    {
        if ($DateCreate != null) {
            $this->DateCreate = $DateCreate;
            return $this;
        } else {
            $this->DateCreate = null;
            $_SESSION['ERROR'] = ['message' => 'Erreur Date'];
            return false;
        }
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function getMediasId(): array
    {
        return $this->MediasId;
    }

    /**
     * @param array $MediasId
     * @return Article
     */
    public function setMediasId(array $MediasId): Article
    {
        // On vérifie que l'on reçoit un tableau d'entier
        if (!empty($MediasId)) {
            foreach ($MediasId as $key => $value) {
                if (!is_numeric($value)) {
                    $_SESSION['ERROR'] = ['message' => 'Erreur Image'];
                    $this->MediasId = [];
                } else {
                    $this->MediasId = $MediasId;
                }
            }
        } else {
            $this->MediasId = [];
        }

        return $this;
    }

    /**
     * Retourne l'ensemble des articles du blog (ORDER BY ID DESC)
     * @param \PDO $bdd
     * @return Article
     * @author Tony
     */
    public function sqlGetAll(\PDO $bdd)
    {
        /* SELECT * FROM t_article AS A LEFT JOIN t_user AS U ON A.USER_ID = U.USER_ID LEFT JOIN associate AS J ON A.ART_ID = J.ART_ID LEFT JOIN t_categorie AS C ON J.CAT_ID = C.CAT_ID GROUP BY A.ART_ID ORDER BY A.ART_DATEADD DESC */

        $requete = $bdd->prepare("SELECT * FROM t_article ORDER BY `ART_ID` DESC");
        $requete->execute();
        return $requete->fetchAll(\PDO::FETCH_CLASS, "src\Model\Article");
    }

    /**
     * Retourne sous forme d'objet une liste d'articles pour un id catégorie donné
     * @author Tony
     * @param int $categorieId
     * @return mixed
     * @throws \Exception
     */
    public function sqlFindByCategory(int $categorieId)
    {
        $bdd = BDD::getInstance();

        $query = $bdd->prepare("SELECT * FROM t_article AS A
                    LEFT JOIN associate AS J ON A.ART_ID = J.ART_ID
                    WHERE J.CAT_ID = :id
                    ORDER BY A.ART_ID DESC");
        $query->bindValue(':id', $categorieId, \PDO::PARAM_INT);
        if ($query->execute()){
            return $query->fetchAll(\PDO::FETCH_CLASS, "src\Model\Article");
        }
    }

    /**
     * Récupère les articles admin paginés avec ou sans recherche
     * @param $firsResult
     * @param $perPage
     * @return mixed
     * @author Tony
     */
    public function sqlGetList($firstResult, $perPage, $search = [])
    {

        $bdd = BDD::getInstance();
        $sql = "SELECT * FROM `t_article` ";
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
                $sql .= " `ART_BODY` LIKE :word$i";
                $i++;
            }
        }

        $sql .= " ORDER BY `ART_ID` DESC LIMIT :first, :perPage";
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
        //var_dump($query);die();
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Récupère un article du blog dans la bdd
     * @param \PDO $bdd
     * @param int $id
     * @return object
     * @author Tony
     */
    public function sqlGetOne(\PDO $bdd, int $id)
    {
        $sql = "SELECT * FROM t_article WHERE ART_ID = :id";
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, 'src\Model\Article');
        return $query->fetch();
    }

    /**
     * Retourne les catégories d'un article
     * @param \PDO $bdd
     * @param int $id
     * @return array
     * @author Tony
     */
    public function sqlGetCategories(\PDO $bdd, int $id): array
    {
        $sql = "SELECT `CAT_ID` FROM `associate` WHERE `ART_ID` = :id";
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Retourne les médias d'un article
     * @param \PDO $bdd
     * @param int $id
     * @return array
     * @author Tony
     */
    public function sqlGetMedias(\PDO $bdd, int $id): array
    {
        $sql = "SELECT `MED_ID` FROM `contain_article` WHERE `ART_ID` = :id";
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Insère un article et sa jointure catégorie
     * @param \PDO $bdd
     * @return int
     * @throws \Exception
     * @author Tony
     */
    public function sqlAdd(\PDO $bdd): int
    {
        $sql = $sql = "INSERT INTO `t_article` (`USER_ID`, `ART_TITLE`, `ART_DATEADD`, `ART_URL`, `ART_BODY` )
                VALUES (:AuthorId, :Title, :DateCreate, :Slug, :Content )";
        $query = $bdd->prepare($sql);
        $query->bindValue(':AuthorId', $this->AuthorId, \PDO::PARAM_INT);
        $query->bindValue(':Title', $this->Title, \PDO::PARAM_STR);
        $query->bindValue(':DateCreate', $this->DateCreate->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $query->bindValue(':Slug', $this->Slug, \PDO::PARAM_STR);
        $query->bindValue(':Content', $this->Content, \PDO::PARAM_STR);
        if (!$query->execute()) {
            throw new \Exception("Une erreur est survenue lors de l'insertion de l'article");
        }
        $this->Id = $bdd->lastInsertId();
        foreach ($this->CategoriesId as $categoryId) {
            $sql = "INSERT INTO `associate` (`ART_ID`, `CAT_ID`) VALUES ($this->Id, :CatId)";
            $query = $bdd->prepare($sql);
            $query->bindValue(':CatId', $categoryId, \PDO::PARAM_INT);
            if (!$query->execute()) {
                throw new \Exception("Une erreur est survenue lors de l'insertion de l'article");
            }
        }
        return $this->Id;
    }

    /**
     * Complète la table jointure contain_article
     * @param \PDO $bdd
     * @param $articleId
     * @param $mediaId
     * @throws \Exception
     * @author Tony
     */
    public function sqlAddMediaJoin(\PDO $bdd, $articleId, $mediaId)
    {
        $query = $bdd->prepare("INSERT INTO `contain_article` 
            (`ART_ID`, `MED_ID`) VALUES (:ArtId, :MediaId)");
        if (!$query->execute([
            'ArtId' => $articleId,
            'MediaId' => $mediaId
        ])) {
            throw new \Exception("Une erreur est survenue lors de l'insertion de l'article");
        }
    }

    /**
     * Supprime un article du blog
     * @param \PDO $bdd
     * @param int $id
     * @return int
     * @author Tony
     */
    public function sqlDelete(\PDO $bdd, int $id)
    {
        try {
            // TABLE JOINTURE associate
            $sql = 'DELETE FROM `associate` WHERE `ART_ID` = :id';
            $query = $bdd->prepare($sql);
            $query->bindValue(':id', $id, \PDO::PARAM_INT);
            $query->execute();
            // TABLE JOINTURE contain_article
            $sql = 'DELETE FROM `contain_article` WHERE `ART_ID` = :id';
            $query = $bdd->prepare($sql);
            $query->bindValue(':id', $id, \PDO::PARAM_INT);
            $query->execute();
            // TABLE t_article
            $sql = 'DELETE FROM `t_article` WHERE `ART_ID` = :id';
            $query = $bdd->prepare($sql);
            $query->bindValue(':id', $id, \PDO::PARAM_INT);
            $query->execute();

            if ($query->rowCount() === 0) {
                throw new \Exception("L'article id : $id n'existe pas");
            }
        } catch (\Exception $e) {
            $_SESSION['ERROR'] = ['message' => $e->getMessage()];
        }
    }

    /**
     * Mise à jour d'un article (catégories et médias)
     * @return $this
     * @throws \Exception
     * @author Tony
     */
    public function sqlUpdate() :Article
    {
        //var_dump($this);

        // Connexion bdd
        $bdd = BDD::getInstance();

        // MAJ table t_article
        $sql = 'UPDATE `t_article` SET 
                       `ART_TITLE` = :title, 
                       `ART_URL` =   :slug,
                       `ART_BODY` =  :content WHERE `ART_ID` = :id';

        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $this->getId(), \PDO::PARAM_INT);
        $query->bindValue(':title', $this->getTitle(), \PDO::PARAM_STR);
        $query->bindValue(':slug', $this->getSlug(), \PDO::PARAM_STR);
        $query->bindValue(':content', $this->getContent(), \PDO::PARAM_STR);
        if (!$query->execute()) {
            throw new \Exception("Une erreur est survenue lors de l'insertion de l'article");
        }

        // MAJ table associate (categorie)
        if (!empty($this->getCategoriesId())){

            // On supprime les categories de l'article
            $query = $bdd->prepare('DELETE FROM `associate` WHERE `ART_ID` = :id');
            $query->bindValue(':id', $this->getId(), \PDO::PARAM_INT);
            if (!$query->execute()) {
                throw new \Exception("Une erreur est survenue lors de la suppression des catégories de l'article");
            }

            // On insère les nouvelles catégories postées
            foreach ($this->getCategoriesId() as $catId) {
                $query = $bdd->prepare("INSERT INTO `associate` (`ART_ID`, `CAT_ID`) VALUES (:ArtId, :CatId)");
                $query->bindValue(':ArtId', $this->getId(), \PDO::PARAM_INT);
                $query->bindValue(':CatId', $catId, \PDO::PARAM_INT);
                if (!$query->execute()) {
                    throw new \Exception("Une erreur est survenue lors de l'insertion des catégories de l'article");
                }
            }
        }

        // MAJ table contain_article (supression des id img postéé)
        if (!empty($this->getMediasId())){
            foreach ($this->getMediasId() as $medId){
                $query = $bdd->prepare('DELETE FROM `contain_article` WHERE `ART_ID` = :ArtId AND `MED_ID` = :MedId');
                $query->bindValue(':ArtId', $this->getId(), \PDO::PARAM_INT);
                $query->bindValue(':MedId', $medId, \PDO::PARAM_INT);
                if (!$query->execute()) {
                    throw new \Exception("Une erreur est survenue lors de la suppression des id médias de l'article");
                }
            }
        }

        return $this;
    }

    /**
     * Compte le nombre total d'articles ou en fonction d'une recherche en bdd
     * @return int
     * @author Tony
     */
    public function sqlCount($search = []): int
    {
        $bdd = BDD::getInstance();
        // On détermine le nombre total d'articles
        $sql = 'SELECT COUNT(*) AS nb_articles FROM `t_article`';
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
                $sql .= " `ART_BODY` LIKE :word$i";
                $i++;
            }
        }
        $query = $bdd->prepare($sql);
        if (!empty($search))
        {
            $i = 0;
            foreach ($search as $word) {
                $query->bindValue(':word'.$i, '%'.$word.'%', \PDO::PARAM_STR);
                $i++;
            }
        }
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_NUM);
        return $result[0];
    }
}
