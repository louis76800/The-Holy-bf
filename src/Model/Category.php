<?php
namespace src\Model;
use PDO;
class Category
{
    //variables

    private int $Id;
    private string $Name;
    private string $Description;

    //propriétés
    //TODO : vérifier getters et setters
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->Id;
    }

    /**
     * @param int $Id
     */
    public function setId(int $Id): void
    {
        $this->Id = $Id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName(string $Name): void
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription(string $Description): void
    {
        $this->Description = $Description;
    }

    // Méthodes


    //Tests
    public function initCategory(\PDO $bdd)
    {
        try{
            $sql = "SELECT CAT_ID, CAT_NAME, CAT_DESCRIPTION FROM t_categorie WHERE CAT_ID = ?";

            $query = $bdd->prepare($sql);
            $query->execute([$this->getId()]);
            $result = $query->fetch(\PDO::FETCH_ASSOC);

            return $result ;
            die();

        }catch (\Exception $e){
            return $e->getMessage();
        }

    }

    /**
     * Récupère toutes les catégories
     * @param \PDO $bdd
     * @return object
     */
    public function sqlGetAll(\PDO $bdd)
    {
        $sql = "SELECT * FROM t_categorie ORDER BY CAT_NAME ASC";
        $query = $bdd->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_OBJ); //TODO à changer vers Fetch_class ?
    }

    /**
     * Récupère tous les id de la table catégorie
     * @param \PDO $bdd
     * @return array
     */
    public function sqlGetListId(\PDO $bdd)
    {
        $sql = "SELECT CAT_ID FROM t_categorie";
        $query = $bdd->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * Met à jour une catégorie : nom et description
     * @param \PDO $bdd
     */
    public function  sqlUpdateCategory(\PDO $bdd)
    {
//TODO :  function d'update des catégories et mise à jour via article
        // avec accès only admin
        $sql = "UPDATE `t_categorie` SET `CAT_DESCRIPTION` = :Description, `CAT_NAME` = :Name WHERE CAT_ID = :Id";
        $query = $bdd->prepare($sql);
        $query->bindValue(':Name',$this->getName(), \PDO::PARAM_STR);
        $query->bindValue(':Description', $this->getDescription(), \PDO::PARAM_STR);
        $query->bindValue(':Id', $this->getId(), \PDO::PARAM_INT);

        if(!$query->execute()) {
            throw new \Exception(("Une erreur est survenue lors de l'insertion la nouvelle catégorie"));
        }
        $this->Id = $bdd->lastInsertId();
        return $this;

    }

    /**
     * @param \PDO $bdd
     * @return int
     * @throws \Exception
     */
    public function sqlAdd(\PDO $bdd)
    {
        //var_dump($this->getName(), "object en cours");
        $sql = "INSERT INTO `t_categorie`( `CAT_NAME`, `CAT_DESCRIPTION`) VALUES(:catName, :catDescription)";

        $query = $bdd->prepare($sql);
        $query->bindValue(':catName',$this->getName(), \PDO::PARAM_STR);
        $query->bindValue(':catDescription', $this->getDescription(), \PDO::PARAM_STR);

        if(!$query->execute()) {
            throw new \Exception(("Une erreur est survenue lors de l'insertion la nouvelle catégorie"));
        }
        $this->Id = $bdd->lastInsertId();
        return $this->Id;

    }

    public function sqlGetArticlesFromCategory(\PDO $bdd, int $id)
    {
        $sql = "SELECT ART_TITLE, ART_DATEADD, ART_URL FROM `t_article` WHERE ART_CATEGORIE =  
                 ( SELECT CAT_NAME FROM `t_categorie` WHERE CAT_ID = ?)";
        $query = $bdd->prepare($sql);
        $query->execute([$this->getId()]);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Retourne une catégories par son id
     * @author Tony
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function sqlFindById(int $id)
    {
        // Connexion à la bdd
        $bdd = BDD::getInstance();

        // Récupère la catégiorie par son id
        $sql = "SELECT * FROM `t_categorie` WHERE `CAT_ID` = :id";
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        if (!$query->execute()){
            throw new \Exception(("Une erreur est survenue lors de la récupération d'une catégorie"));
        }
        return $query->fetch(\PDO::FETCH_ASSOC);
    }
}
