<?php

namespace src\Model;


class Message
{

    private int $mess_id;//=mess_id
    private int $topic_id;//=mess_id
    private int $user_id;//=mess_id
    private string $mess_body;//=mess_id
    private \datetime $mess_date;//=mess_id
    /**
     * @return int
     */
    public function getMessId(): int
    {
        return $this->mess_id;
    }

    /**
     * @param int $mess_id
     * @return Message
     */
    public function setMessId(int $mess_id): Message
    {
        $this->mess_id = $mess_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getTopicId(): int
    {
        return $this->topic_id;
    }

    /**
     * @param int $topic_id
     * @return Message
     */
    public function setTopicId(int $topic_id): Message
    {
        $this->topic_id = $topic_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return Message
     */
    public function setUserId(int $user_id): Message
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessBody(): string
    {
        return $this->mess_body;
    }

    /**
     * @param string $mess_body
     * @return Message
     */
    public function setMessBody(string $mess_body): Message
    {
        $this->mess_body = strip_tags($mess_body);
        return $this;
    }

    /**
     * @return \datetime
     */
    public function getMessDate(): \datetime
    {
        return $this->mess_date;
    }

    /**
     * @param \datetime $mess_date
     * @return Message
     */
    public function setMessDate(\datetime $mess_date): Message
    {
        $this->mess_date = $mess_date;
        return $this;
    }




    public function MessagesTopic(\PDO $bdd, int $id)
    {
        $sql = "SELECT USER_NICKNAME,t_message.USER_ID,MESS_DATE,MESS_BODY,MESS_ID FROM t_message 
                LEFT JOIN t_user ON t_message.USER_ID=t_user.USER_ID
                LEFT JOIN t_topic ON t_message.TOPIC_ID = t_topic.TOPIC_ID
                WHERE  t_topic.TOPIC_ID = ".$id."              
                ORDER BY MESS_ID asc
                ";
        $query = $bdd->prepare($sql);
        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_OBJ);
        return $result;
    }


        public function MessageAdd(\PDO $bdd): MESSAGE{

            $sql = "INSERT INTO `t_message` (`USER_ID`,`TOPIC_ID`, `MESS_BODY`, `MESS_DATE` )
                VALUES (:AuthorId, :Topicid, :MessBody, :MessDate )";

            $query = $bdd->prepare($sql);
            $query->bindValue(':AuthorId', $this->getUserId(), \PDO::PARAM_INT);
            $query->bindValue(':Topicid',$this->getTopicId() , \PDO::PARAM_INT);

            $query->bindValue(':MessBody', $this->getMessBody(), \PDO::PARAM_STR);
            $query->bindValue(':MessDate', $this->getMessDate()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);

              if (!$query->execute()) {
               throw new \Exception("Une erreur est survenue lors de l'envoie du message");
            }

            return $this;

        }

        public function MessageDel($bdd, int $id){
            $sql = 'DELETE FROM `t_message` WHERE `MESS_ID` = :id ';
            $query = $bdd->prepare($sql);
            $query->bindValue(':id', $id, \PDO::PARAM_INT);
            $query->execute();
        }

}
?>