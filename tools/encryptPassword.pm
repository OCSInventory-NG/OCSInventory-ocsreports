#!/usr/bin/perl -w

# Requirements
use MIME::Base64;

# (1) quit unless we have the correct number of command-line args
$num_args = $#ARGV + 1;
if ($num_args != 1) {
    print "\nUsage: perl encryptPassword.pm clearTextPassword \n";
    exit;
}

my $hash = encode_base64($ARGV[0]);

print "Password's hash is : \n";
print "$hash \n";
print "You can now put it in ocsinventory-agent.cfg \n";
