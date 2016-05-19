package Ocsinventory::Agent::Backend::OS::MacOS::Packages;

use strict;
use warnings;

sub check {
    my $params = shift;

    return unless can_load("Mac::SysProfile");
    # Do not run an package inventory if there is the --nosoftware parameter
    return if ($params->{config}->{nosoftware});

    1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $profile = Mac::SysProfile->new();
    my $data = $profile->gettype('SPApplicationsDataType'); # might need to check version of darwin

    return unless($data && ref($data) eq 'ARRAY');

    # for each app, normalize the information, then add it to the inventory stack
    foreach my $app (@$data){
        #my $a = $apps->{$app};
        my $kind = $app->{'runtime_environment'} ? $app->{'runtime_environment'} : 'UNKNOWN';
        my $comments = '['.$kind.']';
        $common->addSoftware({
            'NAME'        => $app->{'_name'},
            'VERSION'     => $app->{'version'} || 'unknown',
            'COMMENTS'    => $comments,
            'PUBLISHER'   => $app->{'info'} || 'unknown',
			'INSTALLDATE' => $app->{'lastModified'},
        });
    }
}

1;
