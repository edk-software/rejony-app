<?php

namespace Cantiga\KnowledgeBundle\Entity;

use Cantiga\KnowledgeBundle\Entity\FaqCategory as Category;
use Cantiga\KnowledgeBundle\Validator\Constraints as CantigaAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FAQ question
 */
class FaqQuestion implements EntityInterface, LevelAwareInterface
{
    /** @var int */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $topic;

    /**
     * @var string
     * @Assert\NotBlank
     * @CantigaAssert\HtmlString(
     *     allowableTags = { "<b>", "<p>", "<br>", "<u>", "<i>", "<a>", "<ul>", "<ol>", "<li>", "<strong>", "<span>" }
     * )
     */
    private $answer;

    /** @var Category */
    private $category;

    /**
     * @var int
     * @Assert\GreaterThanOrEqual(0)
     */
    private $level = 0;

    public function __toString() : string
    {
        return (string) $this->getTopic();
    }

    public function getId() //: ?int
    {
        return $this->id;
    }

    public function setId($id) : self
    {
        $this->id = $id;

        return $this;
    }

    public function getTopic() //: ?string
    {
        return $this->topic;
    }

    public function setTopic($topic) : self
    {
        $this->topic = $topic;
        
        return $this;
    }

    public function getAnswer() //: ?string
    {
        return $this->answer;
    }

    public function setAnswer($answer) : self
    {
        $this->answer = $answer;
        
        return $this;
    }

    public function getCategory() //: ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category) : self
    {
        $this->category = $category;
        
        return $this;
    }

    public function getLevel() //: ?int
    {
        return $this->level;
    }

    public function setLevel($level) : self
    {
        $this->level = $level;
        
        return $this;
    }
}
