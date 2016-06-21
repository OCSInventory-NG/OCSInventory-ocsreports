package Ocsinventory::Agent::Backend::AccessLog;

sub run {
    my $params = shift;
    my $common = $params->{common};
  
    my ($YEAR, $MONTH , $DAY, $HOUR, $MIN, $SEC) = (localtime (time))[5,4,3,2,1,0];
    my $date=sprintf "%02d-%02d-%02d %02d:%02d:%02d", ($YEAR+1900), ($MONTH+1), $DAY, $HOUR, $MIN, $SEC;
  
    $common->setAccessLog ({
        USERID => 'N/A',
        LOGDATE => $date
    });
}

1;
