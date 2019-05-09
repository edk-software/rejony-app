<?php

namespace Cantiga\KnowledgeBundle\Entity;

use Cantiga\CoreBundle\Entity\EntityInterface;
use Cantiga\KnowledgeBundle\Entity\MaterialsFile as File;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Materials category
 */
class MaterialsCategory implements EntityInterface
{
    /** @var int */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $name;

    /** @var ArrayCollection */
    private $files;

    /** @var array */
    private $filesByLevel = [];

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

    public function getFiles() : Collection
    {
        return $this->files;
    }

    public function getFilesByLevel(int $level, bool $refresh = true) : ArrayCollection
    {
        if ($refresh || !array_key_exists($level, $this->filesByLevel)) {
            $this->filesByLevel[$level] = new ArrayCollection();
            foreach ($this->files as $file) {
                if ($file->getLevel() <= $level) {
                    $this->filesByLevel[$level]->add($file);
                }
            }
        }

        return $this->filesByLevel[$level];
    }

    public function addFile(File $file) : self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
        }
        
        return $this;
    }

    public function removeFile(File $file) : self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }
        
        return $this;
    }

    public function setFiles(ArrayCollection $files) : self
    {
        $this->files = $files;
        
        return $this;
    }

    public function initializeCollections()
    {
        // @HACK: used to initialize collections when Doctrine finishes loading an object
        if (!($this->files instanceof Collection)) {
            $this->files = new ArrayCollection();
        }
    }
}
