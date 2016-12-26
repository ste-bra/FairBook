{*
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
*}
<div id="reservationUpdated" class="reservationResponseMessage">
	{if $RequiresApproval && !$UserJoinedWaitingList && !$UserEditedWaitingList}
		{html_image src="dialog-warning.png" id="imgApprovalWarning"}
	{else}
		{html_image src="dialog-success.png" id="imgReservationSuccess"}
	{/if}
	
	{if $UserJoinedWaitingList}
		<div class="createdMessage">{translate key=ReservationCreated}</div>
		<div class="waitingList">{translate key=JoinedWaitingList}</div>
	{else}
		<div class="createdMessage">{translate key=ReservationUpdated}</div>
		{if $UserEditedWaitingList}
			<div class="waitingList">{translate key=IsOnWaitingList}</div>
		{/if}
	{/if}

	<div class="dates">
		{foreach from=$Instances item=instance name=date_list}
			<span class="date">{format_date date=$instance->StartDate() timezone=$Timezone}{if !$smarty.foreach.date_list.last}, {/if}</span>
		{/foreach}
	</div>

	<div class="resources">
		{translate key=Resources}:
		{foreach from=$Resources item=resource name=resource_list}
			<span class="resource">{$resource->GetName()}{if !$smarty.foreach.resource_list.last}, {/if}</span>
		{/foreach}
	</div>

	{if $RequiresApproval && !$UserJoinedWaitingList && !$UserEditedWaitingList}
		<div class="approvalMessage">{translate key=ReservationRequiresApproval}</div>
	{/if}
    <div class="referenceNumber">{translate key=YourReferenceNumber args=$ReferenceNumber}</div>

	<input type="button" id="btnSaveSuccessful" value="{translate key='Close'}" class="button" />

</div>