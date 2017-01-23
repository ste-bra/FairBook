<?php
/**
Copyright 2016-2017 Stefan Braun

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
require_once(ROOT_DIR . 'Domain/Values/WaitingListPriority.php');

class ReservationWaitingListEntry
{
	/**
	 * @var string
	 */
	private $userId;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string
	 */
	private $priority;

	/**
	 * @return string
	 */
	public function UserId()
	{
		return $this->userId;
	}

	/**
	 * @return string
	 */
	public function Title()
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function Description()
	{
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function Priority()
	{
		return $this->priority;
	}

	public function __construct($userId, $title, $description, $priority)
	{
		$this->userId = $userId;
		$this->title = $title;
		$this->description = $description;
		$this->priority = $priority;
	}
}

?>