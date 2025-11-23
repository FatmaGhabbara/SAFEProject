<?php

class Respond

{
  private $id_Com;
  private $author;
  private $message;
  private $time;

public function __construct($id_Com, $author, $message, $time) {
    $this->id_Com = $id_Com;
    $this->author = $author;
    $this->message = $message;
    $this->time = $time;
}

  public function getId_Com()
  {
    return $this->id_Com;
  }
  public function setId_Com($id_Com)
  {
    $this->id_Com = $id_Com;
  }

  public function getAuthor()
  {
    return $this->author;
  }

  public function setAuthor($author)
  {
    $this->author = $author;
  }

  public function getMessageRes()
  {
    return $this->message;
  }

  public function setMessageRes($message)
  {
    $this->message = $message;
  }

  public function getTime()
  {
    return $this->time;
  }

  public function setTime($time)
  {
   $this->time = $time;
  }
}