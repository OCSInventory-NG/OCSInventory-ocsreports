<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
echo "<LINK REL='StyleSheet' TYPE='text/css' HREF='css/graphic.css'>\n";

function percent_bar($status)
{
if (!is_numeric($status)) { return $status; }
if (($status<0) or ($status>100)) { return $status; }
return "<div class='percent_bar'><!--".str_pad($status, 3, "0", STR_PAD_LEFT)."-->
<div class='percent_status' style='width:".$status."px;'>&nbsp;</div>
<div class='percent_text'>".$status."%</div>
</div>";
}

?>