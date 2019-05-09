<?php

namespace Cantiga\UserBundle\Entity;

use Cantiga\CoreBundle\Validator\Constraints as CantigaAssert;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class Agreement implements IdentifiableInterface
{
    /** @var int */
	private $id;

    /** @var int|null */
	private $projectId;

    /**
     * @var string
     * @Assert\Length(
     *     max = 255
     * )
     */
	private $title;

    /**
     * @var string
     * @CantigaAssert\HtmlString(
     *     allowableTags = { "<b>", "<p>", "<br>", "<u>", "<i>", "<a>", "<ul>", "<ol>", "<li>", "<strong>", "<span>" },
     * )
     */
	private $content;

    /**
     * @var string
     * @Assert\Length(
     *     max = 255
     * )
     */
    private $url;

    /**
     * @var string
     * @CantigaAssert\HtmlString(
     *     allowableTags = { "<b>", "<p>", "<br>", "<u>", "<i>", "<a>", "<ul>", "<ol>", "<li>", "<strong>", "<span>" },
     * )
     */
    private $summary;

    /** @var AgreementSignature[] */
	private $signatures;
	private $createdAt;
	private $createdBy;
	private $updatedAt;
	private $updatedBy;

	public function __construct()
	{
        $this->initializeCollections();
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

    public function initializeCollections()
    {
        // @HACK: used to initialize collections when Doctrine finishes loading an object
        if (!($this->signatures instanceof Collection)) {
            $this->signatures = new ArrayCollection();
        }
    }
}
