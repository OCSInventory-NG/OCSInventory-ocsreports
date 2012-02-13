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
#include "ipdiscover.h"

/* We get IP address, netmask, index number and mac address of the adapter */
void get_iface_infos( packet *ppacket, int *index, char *iface, struct sockaddr_in *ipaddr, struct sockaddr_in *netmask){
  int tmpsock;
  struct ifreq  ifr;
  if( ( tmpsock = socket( AF_INET, SOCK_STREAM, 0 ) ) < 0 ){
    perror("Socket creation problem"); 
		exit(1); 
  }
  /* Initialize */
  memset( &ifr, 0x0, sizeof(struct ifreq));
  /* We put into the ifreq struct the name of adapter */
  strncpy(ifr.ifr_name, iface, IF_NAMESIZE-1);
  
  /* IP */
  if(ioctl(tmpsock, SIOCGIFADDR, &ifr)< 0){
    close(tmpsock); 
    perror("Cannot get the ip address"); 
		exit(1); 
  }
  memcpy( &ipaddr->sin_addr, &( (struct sockaddr_in *) &ifr.ifr_addr )->sin_addr, 4 );
  memcpy( ppacket->arphdr.ar_sip, &( (struct sockaddr_in *) &ifr.ifr_addr )->sin_addr, 4 );
  /*******************/
  
  /* SNM */
   if(ioctl(tmpsock, SIOCGIFNETMASK, &ifr)< 0){
    close(tmpsock); 
    perror("Cannot get the net submask"); 
		exit(1); 
  }
  memcpy(&netmask->sin_addr, &((struct sockaddr_in *) &ifr.ifr_netmask)->sin_addr, 4);
  /*******************/
  
  /* MAC */
  if(ioctl(tmpsock, SIOCGIFHWADDR, &ifr)< 0){
    close(tmpsock); 
    perror("Cannot get the mac address"); 
		exit(1); 
  }
	memcpy( ppacket->ethhdr.h_source, (unsigned char *)&ifr.ifr_hwaddr.sa_data, ETH_ALEN );
	memcpy( ppacket->arphdr.ar_sha, (unsigned char *)&ifr.ifr_hwaddr.sa_data, ETH_ALEN ); 
  /*******************/
  
  /* INDEX */
  if(ioctl(tmpsock, SIOCGIFINDEX, &ifr)< 0){
    close(tmpsock); 
    perror("Cannot get the interface index");
		exit(1);
  }
  *index = ifr.ifr_ifindex;
  /*******************/
  close(tmpsock);
} 

void data_init( struct sockaddr_in *ipaddr, struct sockaddr_in *netmask, packet **ppacket, struct sockaddr_ll *sll, int index ){
	memset(ipaddr, 0x00, sizeof(struct sockaddr_in));
	memset(netmask, 0x00, sizeof(struct sockaddr_in));
	/* Arp structure */
	*ppacket = malloc( sizeof( packet ) );
	/* Tie to adapter */
	sll->sll_family = AF_PACKET;
	sll->sll_protocol = htons(ETH_P_ARP);
	/* Building the packet */
	memset( (*ppacket)->ethhdr.h_dest, 0xFF, 6 );
	(*ppacket)->ethhdr.h_proto = htons(0x806);
	
	/* arp header */
	(*ppacket)->arphdr.arp_hrdad = htons(ARPHRD_ETHER);
	(*ppacket)->arphdr.arp_prot = htons(ETH_P_IP);
	(*ppacket)->arphdr.arp_halen = ETH_ALEN;
	(*ppacket)->arphdr.arp_prlen = 4;
	(*ppacket)->arphdr.arp_opcode = htons(ARPOP_REQUEST);
	memset( (*ppacket)->arphdr.ar_tha, 0x0,ETH_ALEN );
}

void print_xml(struct in_addr *ipsrc, packet *ppacket_r, struct hostent* name){
	printf("<H><I>%s</I><M>%02x:%02x:%02x:%02x:%02x:%02x</M><N>%s</N></H>\n",inet_ntoa(*ipsrc), *ppacket_r->arphdr.ar_sha,ppacket_r->arphdr.ar_sha[1],ppacket_r->arphdr.ar_sha[2], 
				 ppacket_r->arphdr.ar_sha[3],ppacket_r->arphdr.ar_sha[4],ppacket_r->arphdr.ar_sha[5], name?name->h_name:"-");
}

void create_socket(int *sd, struct sockaddr_ll *sll, int index){
	/* Socket creation */
	*sd = socket( PF_PACKET, SOCK_RAW, htons( ETH_P_ARP ) );
	
	/* Put the iface index in sockaddr_ll structure to bind */
	sll->sll_ifindex = index;
	
	if( sd < 0 ){
		perror("Socket creation problem");
		exit(1);
	}
	if( fcntl( *sd, F_SETFL, O_NONBLOCK ) == -1 ){
		perror("Cannot set socket mode to O_NONBLOCK");
		exit(1);
	} 
	/* Bind */
	if( bind( *sd, (struct sockaddr*)sll, sizeof(*sll) ) == -1 ){
		perror("Bind error");
		exit(1);
	}
}

void validate_iface( struct sockaddr_in *ipaddr, struct sockaddr_in *netmask, packet *ppacket ){
	char *error_str;
	
	if( ntohl(netmask->sin_addr.s_addr) < 0xFFFF0000){
		snprintf(error_str, 100, "Invalid netmask -> too large (%s). Stop\n", inet_ntoa(netmask->sin_addr));
		perror( error_str );
		exit(1);
	}
}

void scan_init( unsigned long *unet, unsigned long *uhost, struct sockaddr_in *ipaddr, struct sockaddr_in *netmask, struct in_addr *ipsrc, packet **ppacket_r ){
	/* Netid */
  *unet = ntohl(ipaddr->sin_addr.s_addr) & ntohl(netmask->sin_addr.s_addr);
	/* Supposed number of hosts */
	*uhost = ~( ntohl(netmask->sin_addr.s_addr) );
	memset(ipsrc, 0, sizeof(struct in_addr));
	*ppacket_r = malloc( sizeof( packet ) );
}

int main(int argc, char ** argv){
	/* Declarations */
	/* full packet (tx and rx) */
	packet *ppacket, *ppacket_r;
	/* Socket descriptor, nic index */
	int sd    = 0;
	int flag  = 0;
	int index = 0;
	/* ip data */
  unsigned long unet,uhost,ipdst,tip;
  /* The name of the interface is given in parameter to the binary */
  char * iface;
	/* detected device's FQDN */
  struct hostent* name;
	/* ip level sockaddr */
  struct sockaddr_in ipaddr, netmask;
	/* Lowlevel sockaddr */
  struct sockaddr_ll sll = {0x0};
	/* source ip to put in packet */
  struct in_addr ipsrc;
	int request_latency = REQUEST_LATENCY_DEFAULT;
	
  int p=0;
  
	/* Take at least one argument */
  if(argc<2){
		printf("IPDISCOVER binary ver. %d \nUsage : ipdiscover [iface name] [latency in ms]\n", VERSION);
		exit(0);
  }else{
		iface = argv[1];
		if( argc==3 )
			request_latency = atoi( argv[2] );
  }
	/* Initialize data */
	data_init( &ipaddr, &netmask, &ppacket, &sll, index );
  /* Reading nic parameters */
	get_iface_infos( ppacket, &index, iface, &ipaddr, &netmask );
	/* Check iface settings */
	validate_iface( &ipaddr, &netmask, ppacket );
	/* Creating socket */
	create_socket( &sd, &sll, index );
	/* Initialize packet target ip, potential hosts number... */
	scan_init( &unet, &uhost, &ipaddr, &netmask, &ipsrc, &ppacket_r );
	
  /* We are looking for all the possible connected host */
	for(ipdst=1;ipdst<uhost;ipdst++){
		tip = htonl(ipdst+unet);
		memcpy( ppacket->arphdr.ar_tip, &tip, 4 );
		
		/* Sending the packet */
		if( write( sd, ppacket, sizeof(packet) ) < 0 ){
			perror("Transmission error");
			exit(1);
		}
		
		flag = 0;
		usleep( request_latency * 1000 );
		
		while( flag>=0 ){
			memset( ppacket_r, 0, sizeof( packet ) );
			flag = read( sd, ppacket_r, sizeof( packet ) );
			
			if( flag>0 )
				memcpy(&ipsrc, ppacket_r->arphdr.ar_sip, sizeof(struct in_addr));
			
			if(ntohs(ppacket_r->arphdr.arp_opcode) == 2){
				char * c;
				if(p==0)
					printf("<IPDISCOVER>\n");
				p++;
				name = gethostbyaddr(&ipsrc, sizeof(struct in_addr), AF_INET);	
				if(name){
					while((c=strchr(name->h_name,'<')) || (c=strchr(name->h_name,'>'))){
						strncpy(c,"x",sizeof(c));
					}
				}
				usleep( NAME_RES_LATENCY );
				print_xml( &ipsrc, ppacket_r, name );
			}
		}
	}
if(p)
  printf("</IPDISCOVER>\n");
/* That's all */
  exit(0);
}

