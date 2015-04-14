#!/usr/bin/perl

#
# 1c1af39446bd7e5fb28937b42fb220ea887d26ad, v7 (xcart_4_7_0), 2015-02-02 17:04:59, ccash.pl, aim
#

BEGIN
{
# !!! IMPORTANT !!!
# Path to mck-cgi dir. For example: /mystore/merchantid/mck-cgi
$path2mck = "/path-to-mck/login/mck-cgi";
push(@INC,$path2mck);
}

# MCK function
use CCMckLib3_2 qw(InitConfig);
use CCMckDirectLib3_2 qw(SendCC2_1Server doDirectPayment);
use CCMerchantTest qw($ConfigFile);
use CCMerchantCustom qw(GenerateOrderId);

exit if (!($#ARGV+1)); 

foreach $item (@ARGV)
{
	$key="";
	$val="";
	($key,$val)=split('=',$item,2);
	if ($key ne "")
	{
		$val=~ s/\"//; 
		$args{$key}=$val;
	}
}

$status= &InitConfig ($ConfigFile);
if($status)
	{ print "2,,Unable to initialize configuration";exit;}

%result = &SendCC2_1Server ('mauthcapture', %args);

if (($result{'MStatus'} ne "success") && ($result {'MStatus'} ne "success-duplicate"))
	{ print "2,,".$result{'MErrMsg'}." (ErrCode: ".$result{'MErrCode'}.")"; }
else
	{ print "1,".$result{'avs-code'}.",AuthCode: ".$result{'auth-code'}."; ActionCode: ".$result{'action-code'}."; RefCode: ".$result{'ref-code'};}
