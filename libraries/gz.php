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

function gz_uncompress($src, $dst) {
    if (file_exists($src)) {
        $src_handle = gzopen($src, "r9");
        if (!file_exists($dst)) {
            $dst_handle = fopen($dst, "w");
            while (!feof($src_handle)) {
                $chunk = gzread($src_handle, 4096);
                fwrite($dst_handle, $chunk);
            }
            gzclose($src_handle);
            fclose($dst_handle);
        } else {
            echo "dst does not exists";
        }
    } else {
        echo "src does not exists";
    }
}

function gz_compress($src, $dst) {
    if (file_exists($src)) {
        $src_handle = fopen($src, "r");
        if (!file_exists($dst)) {
            $dst_handle = gzopen($dst, "w9");
            while (!feof($src_handle)) {
                $chunk = fread($src_handle, 4096);
                gzwrite($dst_handle, $chunk);
            }
            fclose($src_handle);
            gzclose($dst_handle);
        } else {
            echo "dst does not exists";
        }
    } else {
        echo "src does not exists";
    }
}

?>