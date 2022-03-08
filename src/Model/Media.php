<?php

namespace src\Model;

/**
 * Class Media
 * @package src\Model
 * @author Tony
 */
class Media
{
    private int $Id;
    private string $Name;
    private string $Format;
    private string $Path;
    private string $Alt;
    private int $Size;
    private string $Pathtmp;

    private const ALLOWFORMAT = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png'
    ];
    private const MAXSIZE = 1024*1024;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->Id;
    }

    /**
     * @param int $Id
     * @return Media
     */
    public function setId(int $Id): Media
    {
        $this->Id = $Id;
        return $this;
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
     * @return Media
     */
    public function setName(string $Name): Media
    {
        $this->Name = $Name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->Format;
    }

    /**
     * @param string $Format
     * @return Media
     */
    public function setFormat(string $Format): Media
    {
        $this->Format = $Format;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->Path;
    }

    /**
     * @param string $Path
     * @return Media
     */
    public function setPath(string $Path): Media
    {
        $this->Path = $Path;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlt(): string
    {
        return $this->Alt;
    }

    /**
     * @param string $Alt
     * @return Media
     */
    public function setAlt(string $Alt): Media
    {
        $this->Alt = $Alt;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathtmp(): string
    {
        return $this->Pathtmp;
    }

    /**
     * @param string $Pathtmp
     * @return Media
     */
    public function setPathtmp(string $Pathtmp): Media
    {
        $this->Pathtmp = $Pathtmp;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->Size;
    }

    /**
     * @param int $Size
     * @return Media
     */
    public function setSize(int $Size): Media
    {
        $this->Size = $Size;
        return $this;
    }


    /**
     * Extrait l'extension d'un nom de fichier
     * @param string $filename
     * @return string
     * @author Tony
     */
    public function extractExtension(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return $extension;
    }

    /**
     * Permet de vérifier la corrélation entre l'extension et le type mime d'un fichier
     * @param string $filename
     * @param string $filetype
     * @return bool
     * @throws \InvalidArgumentException
     * @author Tony
     */
    public function checkFormat(string $filename, string $filetype): bool
    {
        $extension = $this->extractExtension($filename);
        if (!array_key_exists($extension, self::ALLOWFORMAT) || !in_array($filetype, self::ALLOWFORMAT)){
           throw new \InvalidArgumentException("Le format du fichier $filename est incorrect");
           return false;
        }
        return true;
    }

    /**
     * Vérifie la taille acceptée d'un fichier
     * @param int $filesize
     * @param string $filename
     * @return bool
     * @throws \Exception
     * @author Tony
     */
    public function checkSize(string $filename, int $filesize): bool{
        if ($filesize > self::MAXSIZE){
            throw new \Exception("Le fichier $filename est trop volumineux");
            return false;
        }
        return true;
    }

    /**
     * Renomme un fichier avec un nom unique
     * @param string $filename
     * @return string
     * @author Tony
     */
    public function rename(string $filename): string{
        $uniqname = md5(uniqid());
        $extension = $this->extractExtension($filename);
        $newname = $uniqname.'.'.$extension;
        return $newname;
    }

    /**
     * insère un media
     * @param \PDO $bdd
     * @return int
     * @throws \Exception
     * @author Tony
     */
    public function SqlAdd(\PDO $bdd){
        $sql = "INSERT INTO `t_media` (`MED_NAME`, `MED_FORMAT`, `MED_PATH`, `MED_ALT`)
                VALUES (:Name, :Format, :Path, :Alt )";

        $query = $bdd->prepare($sql);
        $query->bindValue(':Name', $this->Name, \PDO::PARAM_STR);
        $query->bindValue(':Format', $this->Format, \PDO::PARAM_STR);
        $query->bindValue(':Path', $this->Path, \PDO::PARAM_STR);
        $query->bindValue(':Alt', $this->Alt, \PDO::PARAM_STR);
        if (!$query->execute()){
            throw new \Exception("Une erreur est survenue lors de l'insertion du fichier");
        }
        return $bdd->lastInsertId();
    }

    /**
     * Retourne un media
     * @param \PDO $bdd
     * @param $Id
     * @return array
     * @author Tony
     */
    public function SqlGetOne(\PDO $bdd, $id)
    {
        $sql = "SELECT * FROM `t_media` WHERE `MED_ID` = :id";
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * Retourne un media hydraté sous forme d'objet
     * @param \PDO $bdd
     * @param $Id
     * @return Media
     * @author Tony
     */
    public function SqlGetOneObject(\PDO $bdd, $id): Media
    {
        $sql = "SELECT * FROM `t_media` WHERE `MED_ID` = :id";
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, 'src\Model\Media');
        return $query->fetch();
    }
}