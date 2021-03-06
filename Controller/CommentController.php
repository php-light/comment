<?php
/**
 * Created by iKNSA.
 * User: Khalid Sookia <khalidsookia@gmail.com>
 * Date: 21/01/2018
 * Time: 15:25
 */


namespace PhpLight\CommentBundle\Controller;


use PhpLight\CommentBundle\Entity\Comment;
use PhpLight\CommentBundle\Repository\CommentRepository;
use PhpLight\Framework\Components\Config;
use PhpLight\Framework\Components\Model;
use PhpLight\Framework\Controller\Controller;
use PhpLight\Http\Request\Request;
use PhpLight\Http\Response\JsonResponse;

class CommentController extends Controller
{
    public function createAction(Request $request, array $comment=[])
    {
        try {
            $comment = empty($comment) ? new Comment($request->getPost()["comment"]) : new Comment($comment);
        } catch (\Exception $exception) {
            die($exception);
        }

        $comment->setParent($request->getGet()["id"]);
        $comment->setParentTable($request->getGet()["parent"]);
        $comment->setCreatedBy($request->getUser());

        $newComment = (new CommentRepository())->create($comment);

        $configs = (new Config())->getConfig();
        if (isset($configs["phplight_comment"])) {
            if (isset($configs["phplight_comment"]["listener"])) {
                if (!isset($configs["phplight_comment"]["listener"]["create_success"]["class"])) {
                    dump("You should specify an object");die;
                }
                if (!isset($configs["phplight_comment"]["listener"]["create_success"]["method"])) {
                    dump("You should specify a method");die;
                }
                if (!is_object(new $configs["phplight_comment"]["listener"]["create_success"]["class"])) {
                    dump("The listener does not exist in the namespace");die;
                }
                if (!method_exists($configs["phplight_comment"]["listener"]["create_success"]["class"], $configs["phplight_comment"]["listener"]["create_success"]["method"])) {
                    dump("The method does not exist in the class");die;
                }

                $listener = new $configs["phplight_comment"]["listener"]["create_success"]["class"];
                $method = $configs["phplight_comment"]["listener"]["create_success"]["method"];
                $listener::$method($request, $newComment);
            }
        }

        return new JsonResponse([
            "success" => true,
            "comment" => $newComment->toArray()
        ]);
    }

    public function getAction(Request $request)
    {
        unset($request->getGet()["route"]);
        $filter = $request->getGet();
        unset($filter["route"]);

        if (isset($request->getPost()["query"])) {
            array_merge($filter, $request->getPost()["query"]);
        }

        return new JsonResponse([
            "success" => true,
            "comments" => (new CommentRepository())->get($filter)
        ]);
    }

    public function countAction(Request $request)
    {
        $filter = array_merge($request->getGet(), $request->getGet());

        unset($filter["route"]);

        return new JsonResponse([
            "success" => true,
            "count" => (new CommentRepository())->count($filter)
        ]);
    }

    public function deleteAction(Request $request)
    {
        if ($request->getMethod() !== $request::REQUEST_METHOD_POST) {
            return new JsonResponse([
                "message" => "This method is not allowed"
            ]);
        }

        return new JsonResponse([
            "success" => (new CommentRepository())->delete($request->getPost()["commentId"])
        ]);
    }

    public function editAction(Request $request)
    {
        if ($request->getMethod() !== $request::REQUEST_METHOD_POST) {
            return new JsonResponse([
                "message" => "This method is not allowed"
            ]);
        }

        return new JsonResponse([
            "success" => (new CommentRepository())->edit($request->getPost()["commentId"], $request->getPost()["updatedComment"])
        ]);
    }
}
