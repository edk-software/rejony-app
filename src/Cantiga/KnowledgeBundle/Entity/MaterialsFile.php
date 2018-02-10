<?php

namespace Cantiga\KnowledgeBundle\Entity;

use Cantiga\KnowledgeBundle\Entity\MaterialsCategory as Category;
use Cantiga\KnowledgeBundle\Validator\Constraints as CantigaAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Materials file
 */
class MaterialsFile implements EntityInterface, LevelAwareInterface
{
    /** @var int */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups = { "add", "edit" }
     * )
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups = { "add", "edit" }
     * )
     * @CantigaAssert\HtmlString(
     *     allowableTags = { "<b>", "<p>", "<br>", "<u>", "<i>", "<a>", "<ul>", "<ol>", "<li>", "<strong>", "<span>" },
     *     groups = { "add", "edit" }
     * )
     */
    private $description;

    /**
     * @var string
     * @Assert\File(
     *     groups = { "add" },
     *     mimeTypes = { "application/pdf", "application/zip", "text/rtf" }
     * )
     * @Assert\NotBlank(
     *     groups = { "add", "edit" }
     * )
     */
    private $path;

    /** @var Category */
    private $category;

    /**
     * @var int
     * @Assert\GreaterThanOrEqual(
     *     groups = { "add", "edit" },
     *     value = 0
     * )
     */
    private $level = 0;

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

    public function getDescription() //: ?string
    {
        return $this->description;
    }

    public function setDescription($description) : self
    {
        $this->description = $description;

        return $this;
    }

    public function getPath() //: ?string
    {
        return $this->path;
    }

    public function setPath($path) : self
    {
        $this->path = $path;
        
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
