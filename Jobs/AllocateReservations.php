<?php
/**
Copyright 2013-2014 Stephen Oliver, Nick Korbel

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

//////////////////
/* Cron Example //
//////////////////

This script must be executed every minute for to enable Reservation Reminders functionality

* * * * * php -f /home/mydomain/public_html/booked/Jobs/AllocateReservations.php
* * * * * /path/to/php -f /home/mydomain/public_html/booked/Jobs/AllocateReservations.php

*/

define('ROOT_DIR', dirname(__FILE__) . '/../');
require_once(ROOT_DIR . 'Domain/Access/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Authorization/namespace.php');
require_once(ROOT_DIR . 'lib/Email/namespace.php');
require_once(ROOT_DIR . 'Jobs/JobCop.php');

Log::Debug('Running AllocateReservations.php');

JobCop::EnsureCommandLine();

try
{
	$allocator = new ReservationsAllocator();
	$allocator->Execute();
} 
catch (Exception $ex)
{
	Log::Error('Error running AllocateReservations.php: %s', $ex);
}

Log::Debug('Finished running AllocateReservations.php');




class ReservationsAllocator
{
	const MINUTES_TO_SUBSTRACT = 40320; // 4 weeks

	private $today;
	private $userRepository;
	private $resourceRepository;
	private $reservationViewRepository;
	private $reservationRepository;
	private $scheduler;
	private $schedulerSession;

	public function __construct()
	{
		$this->today = Date::Now()->GetDate();
		$this->userRepository = new UserRepository();
		$this->resourceRepository = new ResourceRepository();
		$this->reservationViewRepository = new ReservationViewRepository();
		$this->reservationRepository = new ReservationRepository();

		$this->scheduler = $this->userRepository->GetScheduler();
		$authentication = new Authentication(new AuthorizationService($this->userRepository), $this->userRepository);
		$this->schedulerSession = $authentication->Login($this->scheduler->Username(), new WebServiceLoginContext());
	}

	public function Execute()
	{
		$start = microtime(true);
		$reservationAllocations = array();
		
		$sortedReservations = $this->GetReservationsByResourcesByDay();
		$allPriorities = $this->GetAllPriorities($sortedReservations);

		foreach ($sortedReservations as $reservations)
		{
			$userPreferences = $this->GetUserPreferences($reservations);
			$priorities = array_intersect_key($allPriorities, $userPreferences);

			//ToDo Quota-Check each user for current resource 

			$reservationAllocations += $this->DetermineReservationAllocations($reservations, $userPreferences, $priorities);
			$this->AllocateReservations($reservations, $reservationAllocations);
		}

		$this->NotifyUsers($sortedReservations);
		$this->LogResults($sortedReservations);
		
		$end = microtime(true);
		echo $end - $start;
	}


	/// HELPER FUNCTIONS

	/**
	 * @return array|ExistingReservationSeries[][]
	 */
	private function GetReservationsByResourcesByDay()
	{
		$sortedReservations = array();
		$schedulerId = $this->scheduler->Id();
		$resources = $this->GetResourcesWithWaitingList();

		foreach ($resources as $resource)
		{
			$reservationsByDay = array();
			$endDate = $this->today->ApplyDifference($resource->GetMinNotice()->Interval());
			$reservations = $this->reservationViewRepository->GetReservationList($this->today, $endDate, $schedulerId, null, null, $resource->GetId());

			// sort reservations of $resource by day
			foreach ($reservations as $reservation)
			{
				$day = (int)$this->today->GetDifference($reservation->StartDate)->Days();
				//replace ReservationItemView with ExistingReservationSeries at one go
				$reservationsByDay[$day][$reservation->SeriesId] = $this->reservationRepository->LoadByReferenceNumber($reservation->GetReferenceNumber());
			}

			$sortedReservations = array_merge($sortedReservations, $reservationsByDay);
		}

		return $sortedReservations;
	}

	/**
	 * @return array|BookableResource[]
	 */
	private function GetResourcesWithWaitingList()
	{
		$resources = $this->resourceRepository->GetResourceList();

		foreach ($resources as $key => $resource)
		{
			if (!$resource->GetHasWaitingList())
			{
				unset($resources[$key]);
				continue;
			}

			if (!$resource->HasMinNotice())
			{
				Log::Error('Can\'t allocate reservations that use resource "'.$resource->GetName().'": No MinNotice time set.');
				unset($resources[$key]);
				continue;
			}
		}

		return $resources;
	}

	/**
	 * @param array|ExistingReservationSeries[][] $sortedReservations
	 * @return array|int[]
	 */
	private function GetAllPriorities($sortedReservations)
	{
		$reversedPriorities = array();
		$priorities = array();
		$startDate = $this->today->SubtractMinutes(self::MINUTES_TO_SUBSTRACT);

		foreach ($sortedReservations as $reservations)
		{
			foreach ($reservations as $reservation)
			{
				$waitingList = $reservation->GetWaitingList();

				foreach ($waitingList as $entry)
				{
					if (isset($reversedPriorities[$entry->UserId()]))
					{
						continue;
					}
					
					$previousReservations = $this->reservationViewRepository->GetReservationList($startDate, $this->today, $entry->UserId);
					$reversedPriorities[$entry->UserId()] = count($previousReservations);

				}
			}
		}

		if (!empty($reversedPriorities))
		{
			$maxPriority = max($reversedPriorities) + 1;
		}

		foreach ($reversedPriorities as $userId => $reversedPriority)
		{
			$priorities[$userId] = $maxPriority - $reversedPriority;
		}

		return $priorities;
	}

	/**
	 * @param array|ExistingReservationSeries[] $reservations
	 * @return array|int[][]
	 */
	private function GetUserPreferences($reservations)
	{
		$userPreferences = array();

		foreach ($reservations as $reservation)
		{
			$waitingList = $reservation->GetWaitingList();

			foreach ($waitingList as $entry)
			{
				$userPreferences[$entry->UserId()][$reservation->SeriesId()] = (int)$entry->Priority();
			}
		}

		return $userPreferences;
	}

	/**
	 * @param array|ExistingReservationSeries[] $reservations
	 * @param array|string[][] $userPreferences
	 * @param array|int[] $priorities
	 * @return array|int[]
	 */
	private function DetermineReservationAllocations($reservations, $userPreferences, $priorities)
	{
		$reservationAllocations = array();
		
		while (count($reservations) > 0)
		{
			$reservationAllocations += $this->DetermineUserAllocations($reservations, $userPreferences, $priorities);
			
			foreach ($reservationAllocations as $seriesId => $value)
			{
				unset($reservations[$seriesId]);
			}
		}

		return $reservationAllocations;
	}
	/**
	 * @param array|ExistingReservationSeries[] $reservations
	 * @param array|string[][] $userPreferences
	 * @param array|int[] $priorities
	 * @return array|int[]
	 */
	private function DetermineUserAllocations($reservations, $userPreferences, $priorities)
	{
		$reservationAllocations = array();
		
		// termination condition
		if (empty($priorities) || empty($reservations))
		{
			return $reservationAllocations;
		}
		
		$userIdsWithHighestPriority = array_keys($priorities, max($priorities));
		// if multiple users got the highest priority, pick one randomly
		$userIdToAllocate = $userIdsWithHighestPriority[rand(0, count($userIdsWithHighestPriority) - 1)];
		$remainingPriorities = $priorities;
		unset($remainingPriorities[$userIdToAllocate]);

		do 
		{
			$remainingReservations = $reservations;
			$tempReservationAllocations = array();
			$preferredReservationSlot = $this->GetFreeReservationSlot($reservations, $userPreferences, $userIdToAllocate);
			
			if ($preferredReservationSlot !== null)
			{
				unset($remainingReservations[$preferredReservationSlot]);
				$tempReservationAllocations[$preferredReservationSlot] = $userIdToAllocate;
			}
			
			$tempReservationAllocations += $this->DetermineUserAllocations($remainingReservations, $userPreferences, $remainingPriorities);
			$reservationAllocations = $this->MaxPrioritySum($reservationAllocations, $tempReservationAllocations, $priorities);
		}
		while ($preferredReservationSlot !== null);

		return $reservationAllocations;
	}

	/**
	 * @param array|ExistingReservationSeries[] $reservations
	 * @param array|string[][] $userPreferences
	 * @param int $userIdToAllocate
	 * @return null|int
	 */
	private function GetFreeReservationSlot($reservations, &$userPreferences, $userIdToAllocate)
	{
		do
		{
			if (empty($userPreferences[$userIdToAllocate]))
			{
				return null;
			}

			$preferredReservationSlot = array_search(max($userPreferences[$userIdToAllocate]), $userPreferences[$userIdToAllocate]);
			unset($userPreferences[$userIdToAllocate][$preferredReservationSlot]);			
		}
		while (!array_key_exists($preferredReservationSlot, $reservations));

		return $preferredReservationSlot;
	}

	/**
	 * @param array|int[] $reservationAllocations1
	 * @param array|int[] $reservationAllocations2
	 * @param array|int[] $priorities
	 * @return array|int[]
	 */
	private function MaxPrioritySum($reservationAllocations1, $reservationAllocations2, $priorities)
	{
		$sum1 = 0;
		$sum2 = 0;

		foreach ($reservationAllocations1 as $userId)
		{
			$sum1 += $priorities[$userId];
		}

		foreach ($reservationAllocations2 as $userId)
		{
			$sum2 += $priorities[$userId];
		}

		return $sum1 >= $sum2 ? $reservationAllocations1 : $reservationAllocations2;
	}

	/**
	 * @param array|ExistingReservationSeries[] &$reservations
	 */
	private function AllocateReservations(&$reservations, $reservationAllocations)
	{
		foreach ($reservations as $reservation)
		{
			if (!array_key_exists($reservation->SeriesId(), $reservationAllocations))
			{
				Log::Error("Can't allocate reservation ".$reservation->SeriesId().": No allocation determined");
				continue;
			}

			$entry = $reservation->GetWaitingListEntry($reservationAllocations[$reservation->SeriesId()]);

			if ($entry === null)
			{
				Log::Error("Can't allocate reservation ".$reservation->SeriesId().": Inconsistencies in waiting list and determined allocations");
				continue;
			}

			$reservation->Update($entry->UserId(), $reservation->Resource(), $entry->Title(), $entry->Description(), $this->schedulerSession);
			$reservation->SetStatusId(ReservationStatus::Created);
			$reservation->RemoveRedundantAttachments($entry->UserId());

			if (count($reservation->Instances()) > 1)
			{
				$reservation->ApplyChangesTo(SeriesUpdateScope::ThisInstance);
			}

			$this->reservationRepository->Update($reservation);
		}
	}

	/**
	 * @param array|ExistingReservationSeries[][] $sortedReservations
	 */
	private function NotifyUsers($sortedReservations)
	{
		$reservationsByUserId = array();

		// sort reservations by user
		foreach ($sortedReservations as $reservations)
		{
			foreach ($reservations as $reservation)
			{
				$reservationsByUserId[$reservation->UserId()][$reservation->SeriesId()] = $reservation;
			}
		}

		foreach ($reservationsByUserId as $reservations)
		{
			ServiceLocator::GetEmailService()->Send(new ReservationsAssignedEmail($notice));
		}
	}

	/**
	 * @param array|ExistingReservationSeries[][] $sortedReservations
	 */
	private function LogResults($sortedReservations)
	{
		if (empty($sortedReservations))
		{
			return;
		}
		
		$resultString = "The following reservations have been allocated:";

		foreach ($sortedReservations as $reservations)
		{
			if (!empty($reservations))
			{
				$resultString .= "\n".current($reservations)->Resource()->GetName()."  ".current($reservations)->CurrentInstance()->StartDate()->Format('Y-m-d')."\n";
	
				foreach ($reservations as $r)
				{
					$resultString .= "   ".$r->CurrentInstance()->StartDate()->Format('H:i')."-".$r->CurrentInstance()->EndDate()->Format('H:i');
					$resultString .= " assigned to ".$this->userRepository->LoadById($r->UserId())->FullName()." (User Id: ".$r->UserId().")";
					
					$wl = $r->GetWaitingList();
					$resultString .= " (Waiting List: ";
	
					foreach ($wl as $entry)
					{
						$resultString .= $entry->UserId();
						if ($entry !== end($wl))
						{
							$resultString .= ", ";
						}
					}
					$resultString .= ")\n";
				}
			}
		}
		Log::Debug($resultString);
	}
}