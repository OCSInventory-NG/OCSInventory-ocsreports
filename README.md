<p align="center">
  <img src="https://cdn.ocsinventory-ng.org/common/banners/banner660px.png" height=300 width=660 alt="Banner">
</p>

[![Build Status](https://travis-ci.com/OCSInventory-NG/OCSInventory-ocsreports.svg?branch=master)](https://travis-ci.com/OCSInventory-NG/OCSInventory-ocsreports)

<h1 align="center">OCS Inventory</h1>
<p align="center">
  <b>Some Links:</b><br>
  <a href="http://ask.ocsinventory-ng.org">Ask question</a> |
  <a href="#COMMING_SOON_STAY_CONNECTED">Installation</a> |
  <a href="https://www.ocsinventory-ng.org/?utm_source=github-ocs">Website</a> |
  <a href="https://www.ocsinventory-ng.org/en/#ocs-pro-en">OCS Professional</a>
</p>

<p align='justify'>
OCS (Open Computers and Software Inventory Next Generation) is an assets management and deployment solution.
Since 2001, OCS Inventory NG has been looking for making software and hardware more powerful.
OCS Inventory NG asks its agents to know the software and hardware composition of every computer or server.
</p>




<h2 align="center">Assets management</h2>
<p align='justify'>
Since 2001, OCS Inventory NG has been looking for making software and hardware more powerful. OCS Inventory NG asks its agents to know the software and hardware composition of every computer or server. OCS Inventory also ask to discover network’s elements which can’t receive an agent. Since the version 2.0, OCS Inventory NG take in charge the SNMP scans functionality.
This functionality’s main goal is to complete the data retrieved from the IP Discover scan. These SNMP scans will allow you to add a lot more informations from your network devices : printers, scanner, routers, computer without agents, …
</p>

<h2 align="center">Deployment</h2>
<p align='justify'>
OCS Inventory NG includes the packet deployment functionality to be sure that all of the softwares environments which are on the network are the same. From the central management server, you can send the packets which will be downloaded with HTTP/HTTPS and launched by the agent on client’s computer. The OCS deployment is configured to make the packets less impactable on the network. OCS is used as a deployment tool on IT stock of more 100 000 devices.
</p>
<br />

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Add your changes: `git add folder/file1.php`
4. Commit your changes: `git commit -m 'Add some feature'`
5. Push to the branch: `git push origin my-new-feature`
6. Submit a pull request !

## License

OCS Inventory is GPLv2 licensed

## Dev notes :
  - Minimal PHP Version : 5.3.7 (password_compat requierement)
  - Min IE version : 8 ([bootstrap](http://getbootstrap.com/getting-started/#support))
  - Targeted others browser version : latest ([bootstrap](http://getbootstrap.com/getting-started/#support))

## Libraries
  - [PHP : password_compat - v11 Aug 2015] (https://github.com/ircmaxell/password_compat) Backport of password_* functions shipped with PHP 5.5
  - [PHP : phpcas - v1.3.4] (https://github.com/Jasig/phpCAS) PHP Authentication library that allows authenticate users via a Central Authentication Service (CAS) server
  - [PHP : tc-lib-barcode - v1.4.2] (https://github.com/tecnickcom/tc-lib-barcode) QR Code Generation
  - [PHP : tc-lib-color - v1.5.1] (https://github.com/tecnickcom/tc-lib-color) tc-lib-barcode dependency

  - [Web interface : bootstrap - v3.3.7] (https://github.com/twbs/bootstrap) HTML/CSS/JS framework for responsive design

  - [JavaScript : jquery - v2.2.4] (https://github.com/jquery/jquery) jQuery JavaScript Library
  - [JavaScript : jquery-migrate-1 - v1.4.1] (https://github.com/jquery/jquery-migrate) APIs and features removed from jQuery core
  - [JavaScript : jquery-fileupload - v5.40.1] (https://github.com/blueimp/jQuery-File-Upload) jQuery plugin for uploading files
  - [JavaScript : jquery-iframe-transport - v1.8.2] (https://github.com/blueimp/jQuery-File-Upload) jquery-fileupload dependency
  - [JavaScript : jquery-ui-widget - v1.10.4] (https://github.com/jquery/jquery-ui) jquery-fileupload dependency
  - [JavaScript : Datatables - v1.10.2] (https://github.com/DataTables/DataTables) Tables plug-in for jQuery
  - [JavaScript : Datatables-bootstrap - v???] (https://github.com/DataTables/DataTables) datatables dependency for bootstrap integration

  - [JavaScript : elycharts - v2.1.4] (https://github.com/voidlabs/elycharts) Charting library
  - [JavaScript : raphael - v1.5.2] (https://github.com/DmitryBaranovskiy/raphael/) elycharts dependency
