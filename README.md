<p align="center">
  <img src="https://cdn.ocsinventory-ng.org/common/banners/banner660px.png" height=300 width=660 alt="Banner">
</p>

<h1 align="center">OCS Inventory UnixAgent</h1>
<p align="center">
  <b>Some Links:</b><br>
  <a href="http://ask.ocsinventory-ng.org">Ask question</a> |
  <a href="#COMMING_SOON_STAY_CONNECTED">Installation</a> |
  <a href="http://www.ocsinventory-ng.org/?utm_source=github-ocs">Website</a> |
  <a href="https://www.factorfx.com/?utm_source=github-ocs">Support</a>
</p>

<p align='justify'>
  Ocsinventory-Agent is an agent for ocsinventory NG. It supports Linux,
  Solaris and AIX. *BSD support is in progress.
</p>




<h2 align="center">Prerequisites</h2>
- Perl 5.8 minimum
  ####The following modules are needed:
    - Digest::MD5
    - XML::Simple
    - Net::IP optional, it is only needed to compute the network information
    - LWP
    - Mac::SysProfile 0.0.5 : this module is need on MacOSX to collect the device informations.
    - To get SSL communications working (for packages deployment or HTTPS communications to OCS server), you need these modules:
      - Crypt::SSLeay if you use LWP prior to version 6
      - LWP::Protocol::https if you use LWP version 6 or more
    - Net::CUPS is used to detect the printer
    - Net::SNMP to scan network devices using SNMP
    - To enhance SNMP feature with custom networks scans, you need these modules:
      - Net::Netmask
      - Net::Ping or Nmap::Parser
    - Data::UUID is used to create a unique id for every machine
    - Parse::EDID is used to inventory monitor and will replace monitor-edid from Mandriva.
      
  ####The following commands are needed:
    - dmidecode on Linux and *BSD (i386, amd64, ia64) => dmidecode is required to read the BIOS stats.
    - lspci on Linux and *BSD (pciutils package) => lspci is required to list PCI devices.
    - sneep on Solaris/sparc, you must install sneep and record the Serial Number with it (download it from http://www.sun.com/download/products.xml?id=4304155a)
    - To get the serial number of the screen you will need one of these tools:
      - monitor-edid from Mandriva is needed to fetch the monitor. A package is available in Fedora repository. information http://wiki.mandriva.com/en/Tools/monitor-edid
      - get-edid from the read-edid package
    - ipmitool if you want to collect information about IPMI
    - Nmap (v3.90 or superior) to scan network devices for Ipdiscover
    
  ####The following PERL modules are optional:
    - Proc::Daemon Daemon mode
    - Proc::PID::File brings the pid file support if Proc::Daemon is installed
    - nvidia::ml brings you some informations on Nvidia Graphic Cards such as memory size, cpu speed, bios version and driver version.
    - Compress::Zlib

  ####The following module is needed if you plan to prepare a tarball or install directly from the Bazaar devel branch. (See SOURCES below.):
    - Module::Install

<h2 align="center">Build / Install</h2>

Once the archive is unpacked, use these commands:

```
perl Makefile.PL
make
make install
```
If you want to turn off the interactive post install script, just do (instead of perl Makefile.PL)
```
PERL_AUTOINSTALL=1 perl Makefile.PL
```

You can also run the agent from the tarball directory. In this case, use the `--devlib` flag to load the library from the local directory.


You need to launch the agent with root privilege. For debugging you can try to launch it with the `-l` flag:

Ex: `ocsinventory-agent -l /tmp --debug`

It's also possible to run directly from the tarball directory:

`sudo ./ocsinventory-agent --devlib --server http://foo/ocsinventory`

<h2 align="center">Note</h2>

Solaris:
  - Sun Studio seems to be needed to build the dependency.
  - The generated Makefile needs gmake to be exectuted
  - The default installation will install the binary in /usr/perl5/5.XXXXX/bin, set you $PATH variable according to that.

Crontab:
  - If you use crontab to launch the agent you'll probably have to redefine the PATH. For example, just add something like: `PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin` At the beginning of the cron file.

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

The memconf script is maintained by Tom Schmidt
http://www.4schmidts.com/memconf.html
Copyright © 1996-2016 Tom Schmidt

memconf is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

Ocsinventory::Agent::Backend::Virtualization::Vmsystem uses code from imvirt:

Authors:
  Thomas Liske <liske@ibh.de>

Copyright Holder:
  2008 (C) IBH IT-Service GmbH [http://www.ibh.de/]

License: This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
