<?php
class Post {
    private $author;
    private $message;
    private $time;
    private $image; // New property for image path

    public function __construct($author, $message, $time, $image = null) {
        $this->author = $author;
        $this->message = $message;
        $this->time = $time;
        $this->image = $image;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function getMessagePost() {
        return $this->message;
    }

    public function setMessagePost($message) {
        $this->message = $message;
    }

    public function getTime() {
        return $this->time;
    }

    public function setTime($time) {
        $this->time = $time;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage($image) {
        $this->image = $image;
    }
}

?>
