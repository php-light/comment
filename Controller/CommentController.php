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

        return new JsonResponse([
            "success" => true,
            "comment" => (new CommentRepository())->create($comment)
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
}
