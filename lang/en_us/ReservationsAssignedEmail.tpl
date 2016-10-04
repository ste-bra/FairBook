{*
Copyright 2013-2015 Nick Korbel

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
Dear {$FullName},
<br/>
<br/>
The following Reservation{if $ReservationCount > 1}s have {else} has {/if}been assigned to you:<br/>
<ul>
{for $i=0 to $ReservationCount - 1}
	<li>
		Reservation Details:
		<br/>
		Start: {formatdate date=$StartDates[$i] key=reservation_email}<br/>
		End: {formatdate date=$EndDates[$i] key=reservation_email}<br/>
		Resource: {$ResourceNames[$i]}<br/>
		Title: {$Titles[$i]}<br/>
		Description: {$Descriptions[$i]|nl2br}
		<br/>
		<a href="{$ScriptUrl}/{$ReservationUrls[$i]}">View this reservation</a> |
		<a href="{$ScriptUrl}/{$ICalUrls[$i]}">Add to Calendar</a>
		<br/>
		<br/>
	</li>
{/for}
</ul><br/>
<a href="{$ScriptUrl}">Log in to Booked Scheduler</a>