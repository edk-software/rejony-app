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
namespace Cantiga\CoreBundle\Api\Controller;

/**
 * If this interface is implemented in a controller, the CRUD actions will use
 * the following methods to perform redirects, instead of default ones. The
 * methods must return <tt>Response</tt> object.
 * 
 * @author Tomasz Jędrzejewski
 */
interface RedirectHandlingInterface {
	public function onError($message);
	public function onSuccess($message);
	public function toIndexPage();
}
