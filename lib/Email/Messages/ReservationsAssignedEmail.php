<?php
/**
Copyright 2016-2017 Stefan Braun

This file is part of Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'lib/Email/namespace.php');
require_once(ROOT_DIR . 'Pages/Pages.php');

class ReservationsAssignedEmail extends EmailMessage
{
	/**
	 * @var ExisitingReservationSeries[] $reservations
	 */
	private $reservations;

	/**
	 * @var User $owner
	 */
	private $owner;

	/**
	 * @var User $scheduler
	 */
	private $scheduler;

	public function __construct($reservations, User $owner, User $scheduler)
	{
		$this->reservations = $reservations;
		$this->owner = $owner;
		$this->scheduler = $scheduler;
		parent::__construct($owner->Language());
	}

	/**
	 * @return EmailAddress
	 */
	public function To()
	{
		return new EmailAddress($this->owner->EmailAddress(), $this->owner->FullName());
	}

	/**
	 * @return EmailAddress
	 */
	public function From()
	{  
		return new EmailAddress($this->scheduler->EmailAddress(), $this->scheduler->FullName());
	}

	/**
	 * @return string
	 */
	public function Body()
	{
		foreach ($this->reservations as $reservation)
		{
			$startDates[] = $reservation->CurrentInstance()->StartDate()->ToTimezone($this->owner->Timezone());
			$endDates[] = $reservation->CurrentInstance()->EndDate()->ToTimezone($this->owner->Timezone());
			$resourceNames[] = $reservation->Resource()->GetName();
			$titles[] = $reservation->Title();
			$descriptions[] = $reservation->Description();
			$reservationUrls[] = sprintf("%s?%s=%s", Pages::RESERVATION, QueryStringKeys::REFERENCE_NUMBER, $reservation->CurrentInstance()->ReferenceNumber());
			$iCalUrls[] = sprintf("export/%s?%s=%s", Pages::CALENDAR_EXPORT, QueryStringKeys::REFERENCE_NUMBER, $reservation->CurrentInstance()->ReferenceNumber());
		}

		$this->Set('StartDates', $startDates);
		$this->Set('EndDates', $endDates);
		$this->Set('ResourceNames', $resourceNames);
		$this->Set('Titles', $titles);
		$this->Set('Descriptions', $descriptions);
		$this->Set('ReservationUrls', $reservationUrls);
		$this->Set('ICalUrls', $iCalUrls);
		$this->Set('ReservationCount', count($this->reservations));
		$this->Set('FullName', $this->owner->FullName());
		return $this->FetchTemplate($this->GetTemplateName());
	}

	/**
	 * @return string
	 */
	public function Subject()
	{
		return $this->Translate('ReservationsAssignedSubject');
	}

	protected function GetTemplateName()
	{
		return 'ReservationsAssignedEmail.tpl';
	}
}

?>