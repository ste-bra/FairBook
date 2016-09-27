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
{extends file="Reservation/edit.tpl"}

{block name="dates"}
<div class="dateSection">
<li>
	<label>{translate key='BeginDate'}</label> {formatdate date=$StartDate}
	<input type="hidden" id="BeginDate" class="dateinput" value="{formatdate date=$StartDate}"/>
	<input type="hidden" id="formattedBeginDate" {formname key=BEGIN_DATE} value="{formatdate date=$StartDate key=system}"/>
	{foreach from=$StartPeriods item=period}
		{if $period eq $SelectedStart}
			{$period->Label()} <br/>
			<input type="hidden" id="BeginPeriod" {formname key=BEGIN_PERIOD} value="{$period->Begin()}"/>
		{/if}
	{/foreach}
</li>
<li>
	<label>{translate key='EndDate'}</label> {formatdate date=$EndDate}
	<input type="hidden" id="EndDate" class="dateinput" value="{formatdate date=$EndDate}"/>
	<input type="hidden" id="formattedEndDate" {formname key=END_DATE} value="{formatdate date=$EndDate key=system}"/>
	{foreach from=$EndPeriods item=period}
		{if $period eq $SelectedEnd}
			{$period->LabelEnd()} <br/>
			<input type="hidden" id="EndPeriod" {formname key=END_PERIOD} value="{$period->End()}"/>
		{/if}
	{/foreach}
</li>
</div>
{/block}

{block name="waitingList"}
	<div class="waitingList">
		<div class="priority">Priority
			<input type="Radio" name="WaitingListPriority" id="priorityHigh" value="3" {if $WaitingListPriority == WaitingListPriority::HIGH}checked{/if}/><label for="priorityHigh">High</label>
			<input type="Radio" name="WaitingListPriority" id="priorityNormal" value="2" {if !$IAmOnWaitingList || $WaitingListPriority == WaitingListPriority::NORMAL}checked{/if}/><label for="priorityNormal">Normal</label>
			<input type="Radio" name="WaitingListPriority" id="priorityLow" value="1" {if $WaitingListPriority == WaitingListPriority::LOW}checked{/if}/><label for="priorityLow">Low</label>
		</div>
		{if $IAmOnWaitingList}
			<span class="onWaitingListInfo">{translate key=IsOnWaitingList}</span>
			<input type="hidden" {formname key=IS_ON_WAITINGLIST} value="1"/><br/>
		{else}
			<span class="notOnWaitingListInfo">{translate key=IsNotOnWaitingList}</span>
			<input type="hidden" {formname key=IS_ON_WAITINGLIST} value="0"/><br/>
		{/if}
		<span>{translate key=NumberOfPeopleOnWaitingList args=$WaitingList|count}</span><br/>
	</div>
{/block}

{block name=deleteButtons}
	{if $CanDelete}
		{if $IsRecurring}
			<a href="#" class="delete prompt">
				{html_image src="cross-button.png"}
				{translate key='LeaveWaitingList'}
			</a>
		{else}
			<a href="#" class="delete save">
				{html_image src="cross-button.png"}
				{translate key='LeaveWaitingList'}
			</a>
		{/if}
	{/if}

	<a style='margin-left:10px;' href="{$Path}export/{Pages::CALENDAR_EXPORT}?{QueryStringKeys::REFERENCE_NUMBER}={$ReferenceNumber}">
		{html_image src="calendar-plus.png"}
		{translate key=AddToOutlook}</a>

{/block}

{block name=submitButtons}
	{if $IsRecurring}
		<button type="button" class="button update prompt">
			<img src="img/tick-circle.png" />
			{translate key='JoinWaitingList'}
		</button>
		<div class="updateButtons" style="display:none;" title="{translate key=ApplyUpdatesTo}">
			<div style="text-align: center;line-height:50px;">
				<button type="button" class="button save btnUpdateThisInstance">
					{html_image src="disk-black.png"}
					{translate key='ThisInstance'}
				</button>
				<button type="button" class="button save btnUpdateAllInstances">
					{html_image src="disks-black.png"}
					{translate key='AllInstances'}
				</button>
				<button type="button" class="button save btnUpdateFutureInstances">
					{html_image src="disk-arrow.png"}
					{translate key='FutureInstances'}
				</button>
				<button type="button" class="button">
					{html_image src="slash.png"}
					{translate key='Cancel'}
				</button>
			</div>
		</div>
	{else}
		<button type="button" class="button save update btnCreate">
			<img src="img/disk-black.png" />
			{if $IAmOnWaitingList}
				{translate key='Update'}
			{else}
				{translate key='JoinWaitingList'}
			{/if}
		</button>
	{/if}
	<button type="button" class="button btnPrint">
		<img src="img/printer.png" />
		{translate key='Print'}
	</button>
{/block}