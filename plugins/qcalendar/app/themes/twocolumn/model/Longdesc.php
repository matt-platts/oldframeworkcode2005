<?php
/*
   Copyright 2009 Bernard Peh

   This file is part of PHP Quick Calendar.

   PHP Quick Calendar is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   PHP Quick Calendar is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with PHP Quick Calendar.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(QCALENDAR_SYS_PATH.'/QCalendarLongdesc.php');

// model for longdesc

class LongdescTwocolumn extends QCalendarLongDesc {

	function LongdescTwocolumn($view, $theme) {
		parent::QCalendarLongDesc($view, $theme);
	}
}
?>
