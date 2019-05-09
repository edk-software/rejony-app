<?php

namespace Cantiga\KnowledgeBundle\Entity;

use Cantiga\CoreBundle\Entity\EntityInterface;
use Cantiga\KnowledgeBundle\Entity\FaqQuestion as Question;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FAQ category
 */
class FaqCategory implements EntityInterface
{
    /** @var int */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $name;

    /** @var ArrayCollection */
    private $questions;

    /** @var array */
    private $questionsByLevel = [];

    public function __construct()
    {
        $this->initializeCollections();
    }

    public function __toString() : string
    {
        return (string) $this->getName();
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

    public function getName() //: ?string
    {
        return $this->name;
    }

    public function setName($name) : self
    {
        $this->name = $name;
        
        return $this;
    }

    public function getQuestions() : ArrayCollection
    {
        return $this->questions;
    }

    public function getQuestionsByLevel(int $level, bool $refresh = true) : ArrayCollection
    {
        if ($refresh || !array_key_exists($level, $this->questionsByLevel)) {
            $this->questionsByLevel[$level] = new ArrayCollection();
            foreach ($this->questions as $question) {
                if ($question->getLevel() <= $level) {
                    $this->questionsByLevel[$level]->add($question);
                }
            }
        }

        return $this->questionsByLevel[$level];
    }

    public function addQuestion(Question $question) : self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
        }
        
        return $this;
    }

    public function removeQuestion(Question $question) : self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
        }
        
        return $this;
    }

    public function setQuestions(ArrayCollection $questions) : self
    {
        $this->questions = $questions;
        
        return $this;
    }

    public function initializeCollections()
    {
        // @HACK: used to initialize collections when Doctrine finishes loading an object
        if (!($this->questions instanceof Collection)) {
            $this->questions = new ArrayCollection();
        }
    }
}
