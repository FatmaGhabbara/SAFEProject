<?php
require_once '../../config.php';
include '../../Model/Comment.php';
class CommentC{
    public function addComment($comment){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
    INSERT INTO comments (author, message, time)
    VALUES (:a, :m, :t)');

            $req->execute([
                'a' => $comment->getAuthor(),
                'm' => $comment->getMessageCom(),
                't' => $comment->getTime()
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }


    public function listComment(){
        $db = config::getConnexion();

        try {
           $req = $db->query('
    SELECT * FROM comments ');

            $comments =  $req->fetchAll();
           return $comments;
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function modifyComment($comment,$id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
    UPDATE comments
    SET author = :a, message = :m, time = :t 
    WHERE id = :id
    ');

            $req->execute([
                'a' => $comment->getAuthor(),
                'm' => $comment->getMessageCom(),
                't' => $comment->getTime(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }


    public function deleteComment($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM comments WHERE id = :id
');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

}
