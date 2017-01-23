{*
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
*}
{extends file="Reservation/create.tpl"}

{block name="waitingList"}
	<div class="waitingList">
		<div class="priority">Priority
			<input type="Radio" name="WaitingListPriority" id="priorityHigh" value="3"/><label for="priorityHigh">High</label>
			<input type="Radio" name="WaitingListPriority" id="priorityNormal" value="2" checked/><label for="priorityNormal">Normal</label>
			<input type="Radio" name="WaitingListPriority" id="priorityLow" value="1"/><label for="priorityLow">Low</label>
		</div>
		<span>{translate key=NumberOfPeopleOnWaitingList args="0"}</span><br/>
	</div>
{/block}