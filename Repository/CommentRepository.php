<?php
/**
 * Created by iKNSA.
 * User: Khalid Sookia <khalidsookia@gmail.com>
 * Date: 21/01/2018
 * Time: 15:26
 */


namespace PhpLight\CommentBundle\Repository;


use BNPPARIBAS\DataAnalyticsProjects\Repository\UserRepository;
use PhpLight\CommentBundle\Entity\Comment;
use PhpLight\Framework\Components\Config;
use PhpLight\Framework\Components\DB\DB;

class CommentRepository
{
    public function create(Comment $comment)
    {
        $db = (new DB())->connect();

        $query = $db->prepare("INSERT INTO `comment` (`parentTable`, `parent`, `comment`, `createdAt`, `createdBy`)
                VALUES (:parentTable, :parent, :comment, :createdAt, :createdBy)");

        $query->bindValue(':parentTable', $comment->getParentTable());
        $query->bindValue(':parent', $comment->getParent());
        $query->bindValue(':comment', $comment->getComment());
        $query->bindValue(':createdAt', $comment->getCreatedAt()->format("Y-m-d H:i:s"));
        $query->bindValue(':createdBy', $comment->getCreatedBy()->getUid());

        if ($query->execute()) {
            $comment->setId($db->lastInsertId());

            return $comment;
        } else {
            dump($db->errorInfo());
            return false;
        }
    }

    public function get(array $filter)
    {
        if (!isset($filter["column"]) && !isset($filter["id"])) {
            die("You need to specify the column to use and the value you are looking for");
        }

        $sql = "SELECT * FROM `comment` WHERE TRUE AND ";
        $orderName = " `createdAt` ";
        $orderBy = " DESC ";

        if (isset($filter["orderName"])) {
            $orderName = "`" . $filter["orderName"] . "`";
            unset($filter["orderName"]);
        }

        if (isset($filter["orderBy"])) {
            $orderBy = "`" . $filter["orderBy"] . "`";
            unset($filter["orderBy"]);
        }

        $sql .= " `parentTable`='" . $filter['parent'] . "' AND `parent`='" . $filter["id"] . "' ";
        unset($filter["parent"], $filter["id"]);

        if (!empty($filter)) {
            $sql .= " AND ";
            foreach ($filter as $item => $value) {
                $sql .= " `$item`='$value' ";

                if ($value !== end($filter)) {
                    $sql .= " AND ";
                }
            }
        }

        $sql = $sql . "ORDER BY " . $orderName . $orderBy;

        $db = (new DB())->connect();

        $coms = $db->query($sql)->fetchAll($db::FETCH_ASSOC);

        $comments = [];
        foreach ($coms as $comment) {
            $userRepositoryNamespace = (new Config())->getConfig()["user_repository"];

            /** @var UserRepository $userRepository */
            $userRepository = new $userRepositoryNamespace;

            $comment["createdBy"] = $userRepository->findDataByUid($comment["createdBy"]);

            $comments[] = $comment;
        }

        return $comments;
    }

    public function count(array $filter=[])
    {
        $db = (new DB())->connect();

        $sql = "SELECT count(*) FROM `comment` WHERE TRUE ";

        if (!empty($filter)) {
            $sql .= " AND ";

            foreach ($filter as $item => $value) {
                $sql .= "`$item`='$value'";

                if ($value !== end($filter)) {
                    $sql .= " AND ";
                }
            }
        }

        return $db->query($sql)->fetch($db::FETCH_ASSOC);
    }

    public function delete($commentId)
    {
        $db = (new DB())->connect();

        return $db->exec("DELETE FROM `comment` WHERE `id`=" . (int) $commentId);
    }
}
