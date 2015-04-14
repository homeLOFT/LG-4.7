#!/usr/bin/perl
#
# 1c1af39446bd7e5fb28937b42fb220ea887d26ad, v17 (xcart_4_7_0), 2015-02-02 17:04:59, netssleay.pl, aim
#

require Net::SSLeay;
Net::SSLeay->import ( qw(sslcat));
$Net::SSLeay::slowly = 5; # Add sleep so broken servers can keep up

if ($#ARGV < 1) {
 	print <<EOF;
 Usage: $0 host port use_tls [cert [keycert]] < requestfile
EOF
	exit;
}

($host, $port, $use_tls, $cert, $kcert) = @ARGV;

if ($use_tls == '1') {
	# http://search.cpan.org/~mikem/Net-SSLeay-1.66/lib/Net/SSLeay.pod#KNOWN_BUGS_AND_CAVEATS
	$Net::SSLeay::ssl_version = 10;
}

$request = "";
while(<STDIN>) {
	$request .= $_;
}

($reply) = sslcat($host, $port, $request, $cert, $kcert);
print $reply;

# tested revision: 1.1; 1.4.9; 1.9.7; 1.5.0; 1.9.8; 1.5.1; 1.9.9.release
