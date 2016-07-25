<?php
/**
Copyright 2011-2015 Nick Korbel

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

class CurrentUserOnWaitingListExcludedRule implements IReservationValidationRule
{
	/**
	 * @var IReservationValidationRule
	 */
	private $rule;

	/**
	 * @var UserSession
	 */
	private $userSession;

	public function __construct(IReservationValidationRule $baseRule, UserSession $userSession)
	{
		$this->rule = $baseRule;
		$this->userSession = $userSession;
	}

	public function Validate($reservationSeries)
	{
		if ($reservationSeries->AddedToWaitingList() !== null && $reservationSeries->AddedToWaitingList()->UserId() == $this->userSession->UserId)
		{
			return new ReservationRuleResult();
		}

		if ($reservationSeries->IsUserOnWaitingList($this->userSession))
			{
				return new ReservationRuleResult();
			}

		return $this->rule->Validate($reservationSeries);
	}
}