<?php

namespace WIO\EdkBundle\Entity;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use WIO\EdkBundle\Entity\EdkFaqCategory as Category;

/**
 * EDK FAQ question
 */
class EdkFaqQuestion implements IdentifiableInterface
{
    const LEVEL_ALL = 0;
    const LEVEL_AREA = 1;
    const LEVEL_GROUP = 2;
    const LEVEL_PROJECT = 3;

    /** @var int */
    private $id;

    /** @var string */
    private $topic;

    /** @var string */
    private $answer;

    /** @var Category */
    private $category;

    /** @var int */
    private $level = 0;

    public function __toString() : string
    {
        return $this->getTopic();
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id) : self
    {
        $this->id = $id;

        return $this;
    }

    public function getTopic() : string
    {
        return $this->topic;
    }

    public function setTopic(string $topic) : self
    {
        $this->topic = $topic;
        
        return $this;
    }

    public function getAnswer() : string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer) : self
    {
        $this->answer = $answer;
        
        return $this;
    }

    public function getCategory() : Category
    {
        return $this->category;
    }

    public function setCategory(Category $category) : self
    {
        $this->category = $category;
        
        return $this;
    }

    public function getLevel() : int
    {
        return $this->level;
    }

    public function setLevel(int $level) : self
    {
        $this->level = $level;
        
        return $this;
    }
}
