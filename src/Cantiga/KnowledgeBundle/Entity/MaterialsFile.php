<?php

namespace Cantiga\KnowledgeBundle\Entity;

use Cantiga\KnowledgeBundle\Entity\MaterialsCategory as Category;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;

/**
 * Materials file
 */
class MaterialsFile implements LevelAwareInterface, IdentifiableInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string */
    private $path;

    /** @var Category */
    private $category;

    /** @var int */
    private $level = 0;

    public function __toString() : string
    {
        return $this->getName();
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

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        
        return $this;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setDescription(string $description) : self
    {
        $this->description = $description;

        return $this;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function setPath(string $path) : self
    {
        $this->path = $path;
        
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
