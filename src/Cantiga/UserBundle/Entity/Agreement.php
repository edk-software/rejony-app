<?php

namespace Cantiga\UserBundle\Entity;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Agreement implements IdentifiableInterface
{
	private $id;
	private $projectId;
	private $title;
	private $content;
    private $url;
    private $summary;
	private $signatures;
	private $createdAt;
	private $createdBy;
	private $updatedAt;
	private $updatedBy;

	public function __construct()
	{
		$this->createdAt = time();
        $this->signatures = new ArrayCollection();
	}

	public function getId()
	{
		return $this->id;
	}

    public function getProjectId()
    {
        return $this->projectId;
    }

    public function setProjectId($projectId) : self
    {
        $this->projectId = $projectId;

        return $this;
    }

	public function getTitle()
	{
		return $this->title;
	}

	public function setTitle($title) : self
	{
		$this->title = $title;

		return $this;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function setContent($content) : self
	{
		$this->content = $content;

		return $this;
	}

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url) : self
    {
        $this->url = $url;

        return $this;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function setSummary($summary) : self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getSignatures() : ArrayCollection
    {
        return $this->signatures;
    }

    public function setSignatures(ArrayCollection $signatures) : self
    {
        $this->signatures = $signatures;

        return $this;
    }

    public function addSignature(AgreementSignature $signature) : self
    {
        if (!$this->signatures->contains($signature)) {
            $this->signatures->add($signature);
        }

        return $this;
    }

    public function removeSignature(AgreementSignature $signature) : self
    {
        if ($this->signatures->contains($signature)) {
            $this->signatures->remove($signature);
        }

        return $this;
    }

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function setCreatedAt($createdAt) : self
	{
		$this->createdAt = $createdAt;

		return $this;
	}

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy($createdBy) : self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt($updatedAt) : self
	{
		$this->updatedAt = $updatedAt;

		return $this;
	}

	public function getUpdatedBy()
	{
		return $this->updatedBy;
	}

	public function setUpdatedBy($updatedBy) : self
	{
		$this->updatedBy = $updatedBy;

		return $this;
	}

    public function canRemove() : bool
    {
        return true;
    }
}
