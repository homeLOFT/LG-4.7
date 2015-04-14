#!/bin/sh

#
# 1c1af39446bd7e5fb28937b42fb220ea887d26ad, v3 (xcart_4_7_0), 2015-02-02 17:04:59, newsletter.sh, aim
#
# Newsletter mailling script
#

#
# To make this script use sendmail instead of mail
# comment out the line with mail_prog definition
#
#mail_prog="mail"
sendmail_prog="sendmail"

if [ "x$REPLYTO" != "x" ]; then
	sendmail_prog="$sendmail_prog -bm -t -i -f $REPLYTO"
fi

#
# Get mail list
#
if [ "${1}" != "" ] 
then 
	mail_list=`cat "${1}"`
fi

#
# Get mail subject
#
if [ "${2}" != "" ] 
then 
	mail_subj=`cat "${2}"`
fi

#
# Get mail body
#
mail_body="${3}"

#
# Get mail "From"
#
if [ "${4}" != "" ] 
then 
	mail_from="${4}"
fi

#
# Get charset
#
if [ "${5}" != "" ] 
then 
	mail_addheader="${5}"
fi

#
# Send mail to all in maillist
#
for target in $mail_list
	do
	if [ x$mail_prog != x ]; then
		(sed "s/###EMAIL###/$target/g" < $mail_body) | $mail_prog -s "$mail_subj" "$target"
	else
		(echo -e "To: $target\nFrom: $mail_from\nSubject: $mail_subj\n$mail_addheader"; sed "s/###EMAIL###/$target/g" < $mail_body) | $sendmail_prog $target
	fi
done

#
# Delete files
#
rm "$1" "$2" "$3"
