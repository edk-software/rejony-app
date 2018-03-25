<?php

namespace WIO\EdkBundle\Entity;

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;

class EdkFeedback implements IdentifiableInterface
{
	private $id;
	private $route;
	private $content;
	private $createdAt;

	public function __construct()
	{
		$this->createdAt = time();
	}

	public function getId()
	{
		return $this->id;
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function setRoute(EdkRoute $route) : self
	{
		$this->route = $route;

		return $this;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function setContent(string $content) : self
	{
		$this->content = $content;

		return $this;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function setCreatedAt(int $createdAt) : self
	{
		$this->createdAt = $createdAt;

		return $this;
	}
}
