<?php
require_once '../../config.php';
require_once '../../model/Post.php';
class PostC{
    private function tryRepairSchemaIfMissing(Exception $e, $table = 'posts') {
        $msg = $e->getMessage();
        // Detect missing table errors
        if (stripos($msg, "SQLSTATE[42S02]") !== false || stripos($msg, "doesn't exist") !== false) {
            // Only attempt automatic repair on local/dev environments
            $server = $_SERVER['SERVER_NAME'] ?? null;
            $isLocal = (php_sapi_name() === 'cli') || in_array($server, ['localhost', '127.0.0.1']);
            $repairScript = __DIR__ . '/../ensure_schema.php';
            if ($isLocal && file_exists($repairScript)) {
                // Run the schema repair script once (suppress output)
                ob_start();
                include $repairScript;
                ob_end_clean();
                error_log("PostC: attempted schema repair for table '$table'");
                return true;
            }
        }
        return false;
    }
    

    public function addPost(Post $post) {
    $db = config::getConnexion();
    try {
        $req = $db->prepare('INSERT INTO posts (id_user, author, message, time, image, status) VALUES (:id_user, :author, :message, :time, :image, :status)');
        $req->execute([
            'id_user' => $post->getId_User(),
            'author' => $post->getAuthor(),
            'message' => $post->getMessagePost(),
            'time' => $post->getTime(),
            'image' => $post->getImage(),
            'status' => $post->getStatus(),
        ]);
    } catch (Exception $e) {
        // Try repair on missing table and retry once
        if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
            try {
                $req = $db->prepare('INSERT INTO posts (id_user, author, message, time, image, status) VALUES (:id_user, :author, :message, :time, :image, :status)');
                $req->execute([
                    'id_user' => $post->getId_User(),
                    'author' => $post->getAuthor(),
                    'message' => $post->getMessagePost(),
                    'time' => $post->getTime(),
                    'image' => $post->getImage(),
                    'status' => $post->getStatus(),
                ]);
                return;
            } catch (Exception $e2) {
                echo 'Erreur: '.$e2->getMessage();
                return;
            }
        }

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
            // Try to repair schema automatically on local environments and retry once
            if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
                try {
                    $req = $db->query('\n    SELECT * FROM posts ');
                    $posts = $req->fetchAll();
                    return $posts;
                } catch (Exception $e2) {
                    echo 'Erreur: '.$e2->getMessage();
                    return [];
                }
            }

            echo 'Erreur: '.$e->getMessage();
            return [];
        }
    }
    public function listPostUser($id_User) {
    $db = config::getConnexion();
    
    try {
        // Use prepare() instead of query() for parameter binding
        $req = $db->prepare('SELECT * FROM posts WHERE id_user = :id');
        
        // Bind the parameter (important for security and correctness)
        $req->bindParam(':id', $id_User, PDO::PARAM_INT);
        
        // Execute the prepared statement
        $req->execute();
        
        // Fetch all results as associative array
        $posts = $req->fetchAll(PDO::FETCH_ASSOC);
        
        return $posts;
        
    } catch (Exception $e) {
        // If table missing, try repair and retry once
        if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
            try {
                $req = $db->prepare('SELECT * FROM posts WHERE id_user = :id');
                $req->bindParam(':id', $id_User, PDO::PARAM_INT);
                $req->execute();
                $posts = $req->fetchAll(PDO::FETCH_ASSOC);
                return $posts;
            } catch (Exception $e2) {
                error_log('Error in listPostUser retry: ' . $e2->getMessage());
                return [];
            }
        }

        // Log error instead of echoing (better practice)
        error_log('Error in listPostUser: ' . $e->getMessage());
        
        // Return empty array on error
        return [];
    }
}
    
    public function listPostProuver(){
        $db = config::getConnexion();

        try {
           $req = $db->query("
    SELECT * FROM posts WHERE status='approved' ");

            $posts =  $req->fetchAll();
           return $posts;
        } catch (Exception $e) {
            if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
                try {
                    $req = $db->query("\n    SELECT * FROM posts WHERE status='approved' ");
                    $posts = $req->fetchAll();
                    return $posts;
                } catch (Exception $e2) {
                    echo 'Erreur: '.$e2->getMessage();
                    return [];
                }
            }

            echo 'Erreur: '.$e->getMessage();
            return [];
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
        if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
            try {
                $req = $db->prepare('\n    UPDATE posts\n    SET author = :a, message = :m, time = :t , image = :im\n    WHERE id = :id\n    ');

                $req->execute([
                    'a' => $post->getAuthor(),
                    'm' => $post->getMessagePost(),
                    't' => $post->getTime(),
                    'im' => $post->getImage(),
                    'id' => $id
                ]);
            } catch (Exception $e2) {
                echo 'Erreur: '.$e2->getMessage();
            }
            return;
        }

        echo 'Erreur: '.$e->getMessage();
    }
}



    public function deletePost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('\n           DELETE FROM posts WHERE id = :id\n');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
                try {
                    $req = $db->prepare('\n           DELETE FROM posts WHERE id = :id\n');

                    $req->execute([
                        'id' => $id
                    ]);
                } catch (Exception $e2) {
                    echo 'Erreur: '.$e2->getMessage();
                }
                return;
            }

            echo 'Erreur: '.$e->getMessage();
        }
    }
    public function deleteUserPost($id_User){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('\n           DELETE FROM posts WHERE id_user = :id\n');

            $req->execute([
                'id' => $id_User
            ]);
        } catch (Exception $e) {
            if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
                try {
                    $req = $db->prepare('\n           DELETE FROM posts WHERE id_user = :id\n');

                    $req->execute([
                        'id' => $id_User
                    ]);
                } catch (Exception $e2) {
                    echo 'Erreur: '.$e2->getMessage();
                }
                return;
            }

            echo 'Erreur: '.$e->getMessage();
        }
    }
    
    public function ProuverPost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare("
           UPDATE posts 
           SET status='approved' WHERE id = :id
");

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
                try {
                    $req = $db->prepare("
           UPDATE posts 
           SET status='approved' WHERE id = :id
");

                    $req->execute([
                        'id' => $id
                    ]);
                } catch (Exception $e2) {
                    echo 'Erreur: '.$e2->getMessage();
                }
                return;
            }

            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function BlockPost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare("
           UPDATE posts 
           SET status='blocked' WHERE id = :id
");

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            if ($this->tryRepairSchemaIfMissing($e, 'posts')) {
                try {
                    $req = $db->prepare("
           UPDATE posts 
           SET status='blocked' WHERE id = :id
");

                    $req->execute([
                        'id' => $id
                    ]);
                } catch (Exception $e2) {
                    echo 'Erreur: '.$e2->getMessage();
                }
                return;
            }

            echo 'Erreur: '.$e->getMessage();
        }
    }

}
