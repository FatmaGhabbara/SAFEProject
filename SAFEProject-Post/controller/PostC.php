<?php
require_once '../../config.php';
require_once '../../Model/Post.php';
class PostC{
    

    public function addPost(Post $post) {
    $db = config::getConnexion();
    try {
        $req = $db->prepare('INSERT INTO posts (author, message, time, image) VALUES (:author, :message, :time, :image)');
        $req->execute([
            'author' => $post->getAuthor(),
            'message' => $post->getMessagePost(),
            'time' => $post->getTime(),
            'image' => $post->getImage()
        ]);
    } catch (Exception $e) {
        echo 'Erreur: '.$e->getMessage();
    }
    }


    public function listPost(){
        $db = config::getConnexion();

        try {
           $req = $db->query('
    SELECT * FROM posts ');

            $posts =  $req->fetchAll();
           return $posts;
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }


    public function modifyPost($post, $id) {
    $db = config::getConnexion();
     try {
        $req = $db->prepare('
    UPDATE posts
    SET author = :a, message = :m, time = :t , image = :im
    WHERE id = :id
    ');

        $req->execute([
            'a' => $post->getAuthor(),
            'm' => $post->getMessagePost(),
            't' => $post->getTime(),
            'im' => $post->getImage(),
            'id' => $id
        ]);
    } catch (Exception $e) {
        echo 'Erreur: '.$e->getMessage();
    }
}



    public function deletePost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM posts WHERE id = :id
');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

}
