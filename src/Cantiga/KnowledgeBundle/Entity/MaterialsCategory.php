<?php

namespace Cantiga\KnowledgeBundle\Entity;

use Cantiga\KnowledgeBundle\Entity\MaterialsFile as File;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Materials category
 */
class MaterialsCategory implements IdentifiableInterface
{
    /** @var int */
    private $id;

    /** @var string */
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

    public function getFiles() : ArrayCollection
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
