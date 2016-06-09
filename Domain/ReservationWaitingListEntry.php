<?php
/**
Copyright 2011-2015 Nick Korbel

This file is part of Booked Scheduler.

Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Booked Scheduler is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'lib/Common/namespace.php');
require_once(ROOT_DIR . 'Domain/ReservationAttachment.php');

class ReservationWaitingListEntry
{
	/**
	 * @var int
	 */
	protected $userId;

	/**
	 * @return int
	 */
	public function UserId()
	{
		return $this->userId;
	}

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @return string
	 */
	public function Title()
	{
		return $this->title;
	}

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @return string
	 */
	public function Description()
	{
		return $this->description;
	}

	public function __construct($userId, $title, $description)
	{
		$this->userId = $userId;
		$this->title = $title;
		$this->description = $description;
	}
}

?>