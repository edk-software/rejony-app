<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\CoreBundle\Services;

class MapService
{
	private $secretKey;
	private $olBaseUrl;
	private $olPopupBaseUrl;

	public function __construct($secretKey)
	{
		$this->secretKey = $secretKey;
		$this->olBaseUrl = 'https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1';
		$this->olPopupBaseUrl = 'https://unpkg.com/ol-popup@4.0.0';
	}

	public function getGmJsUrl()
	{
		return 'https://maps.googleapis.com/maps/api/js?key='.$this->secretKey.'&v=3.exp';
	}

	public function getOlCssUrl()
    {
		return $this->olBaseUrl . '/css/ol.css';
	}

	public function getOlJsUrl()
	{
		return $this->olBaseUrl . '/build/ol.js';
	}

	public function getOlPopupJsUrl()
	{
		return $this->olPopupBaseUrl;
	}

	public function getOlPopupCssUrl()
	{
		return $this->olPopupBaseUrl . '/src/ol-popup.css';
	}
}
