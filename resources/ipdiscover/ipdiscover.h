/*
###############################################################################
##OCSInventory Version NG Beta
##Copyleft Pascal DANEK 2006
##Web : http://ocsinventory.sourceforge.net
##
##This code is open source and may be copied and modified as long as the source
##code is always made freely available.
##Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################
*/

#include <sys/socket.h>
#include <linux/if_ether.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <unistd.h>
#include <linux/if_packet.h>
#include <ctype.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <linux/sockios.h>
#include <sys/ioctl.h>
#include <net/if.h>
#include <fcntl.h>
#include <time.h>
#include <netdb.h>

#ifndef ARPHRD_ETHER
#define ARPHRD_ETHER 1
#endif
#ifndef ARPOP_REQUEST
#define ARPOP_REQUEST 1
#endif
#ifndef ARPOP_REPLY
#define ARPOP_REPLY 2
#endif

#define VERSION 5
#define NAME_RES_LATENCY 1000000
#define REQUEST_LATENCY_DEFAULT 100 /* ms */

/* Trame ARP */
struct arphdr{
  unsigned short arp_hrdad; 
	unsigned short arp_prot;
	unsigned char arp_halen;
	unsigned char arp_prlen;
	unsigned short arp_opcode;
    
	unsigned char ar_sha[ETH_ALEN];
	unsigned char ar_sip[4];
	unsigned char ar_tha[ETH_ALEN];
	unsigned char ar_tip[4];
};
  
/* Ethernet header*/
struct Packet{
	struct ethhdr ethhdr;
	struct arphdr arphdr;
};
typedef struct Packet packet;
