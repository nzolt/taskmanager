<?php

namespace Fluid;

/**
 * Class Fluid
 * @package Fluid
 */
class Fluid extends Db
{

    /**
     * @param $args array
     * @return bool
     */
    function save($args)
    {
        $connection = $this->getConnection();
        $sql = "INSERT INTO `tasks` (`name`, `description`, `active`, `created_at`) VALUES (:name, :description, :active, :created_at)";


        //Prepare our statement.
        $statement = $connection->prepare($sql);

        $now = new \DateTime('NOW');

        //Bind our values to our parameters (we called them :make and :model).
        $statement->bindValue(':name', $args['name']);
        $statement->bindValue(':description', $args['description']);
        $statement->bindValue(':active', $args['active']);
        $statement->bindValue(':created_at', $now->format('Y-m-d H:i:s'));

        //Execute the statement and insert our values.
        try {
            $inserted = $statement->execute();
            $this->logger->addInfo('Task saved: ', ['name' => $args['name']]);
            return true;
        } catch (\PDOException $e) {
            $this->logger->addError('Task with sane name already exists', ['code' => $e->getCode(), 'Message' => $e->getMessage()]);
            return false;
        }

    }

    /**
     * @param $args array
     * @return bool
     */
    public function listTasks()
    {
        $connection = $this->getConnection();
        $sql = "SELECT `id`, `name`, `description`, `active`, `created_at`, `updated_at` FROM `tasks` WHERE `active` = 1 ORDER BY `updated_at` DESC";


        //Prepare our statement.
        $statement = $connection->prepare($sql);

        //Execute the statement and return values.
        try {
            $statement->execute();
            return $statement->fetchAll();
        } catch (\PDOException $e) {
            $this->logger->addError('The list can\'t be retrived', ['code' => $e->getCode(), 'Message' => $e->getMessage()]);
            return [];
        }

    }

    /**
     * @param $args array
     * @return bool
     */
    public function getTask($taskId)
    {
        $connection = $this->getConnection();
        $sql = "SELECT `id`, `name`, `description`, `active`, `created_at`, `updated_at` FROM `tasks` WHERE `id` = :taskId LIMIT 1";

        //Prepare our statement.
        $statement = $connection->prepare($sql);

        $statement->bindParam(':taskId', $taskId, \PDO::PARAM_INT);

        //Execute the statement and return values.
        try {
            $statement->execute();//var_dump($statement->queryString);
            return $statement->fetch();
        } catch (\PDOException $e) {
            $this->logger->addError('The list can\'t be retrived', ['code' => $e->getCode(), 'Message' => $e->getMessage()]);
            return [];
        }

    }

    /**
     * @param $args array
     * @return bool
     */
    public function deleteTask($taskId)
    {
        $connection = $this->getConnection();
        $sql = "DELETE FROM `tasks` WHERE `id` = :taskId";

        $statement = $connection->prepare($sql);

        $statement->bindParam(':taskId', $taskId, \PDO::PARAM_INT);

        //Execute the statement and return values.
        try {
            $inserted = $statement->execute();
            return $inserted;
        } catch (\PDOException $e) {
            $this->logger->addError('The task can\'t be deleted', ['code' => $e->getCode(), 'Message' => $e->getMessage()]);
            return [];
        }

    }
    /**
     * @param $args array
     * @return bool
     */
    function update($taskId, $args)
    {
        $connection = $this->getConnection();
        $sql = "UPDATE `tasks` SET `name`=:name,`description`=:description,`active`=:active WHERE `id` = :taskId";

        //Prepare our statement.
        $statement = $connection->prepare($sql);

        $now = new \DateTime('NOW');

        //Bind our values to our parameters (we called them :make and :model).
        $statement->bindValue(':name', $args['name']);
        $statement->bindValue(':description', $args['description']);
        $statement->bindValue(':active', $args['active']);
        $statement->bindParam(':taskId', $taskId, \PDO::PARAM_INT);

        //Execute the statement and insert our values.
        try {
            $inserted = $statement->execute();
            $this->logger->addInfo('Task saved: ', ['name' => $args['name']]);
            return true;
        } catch (\PDOException $e) {
            $this->logger->addError('Task with sane name already exists', ['code' => $e->getCode(), 'Message' => $e->getMessage()]);
            return false;
        }

    }
}