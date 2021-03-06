#!/usr/bin/env bash
set -e # Quit script on error
$CLIC application:variable:set "$CLIC_APPNAME" mysql/database --description="Name of the database" --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" mysql/host --description="Hostname of the database" --if-not-global-exists --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" mysql/user --description="Username to connect to the database" --if-not-global-exists --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" mysql/password --description="Password of the database user"  --if-not-global-exists --default-existing-value
mail_transport=""
while [[ "$mail_transport" != "mail" && "$mail_transport" != "smtp" && "$mail_transport" != "sendmail" && "$mail_transport" != "gmail" ]]; do
    $CLIC application:variable:set "$CLIC_APPNAME" mail/transport --description="Type of mail transport" --if-not-global-exists --default-existing-value --default=mail
    mail_transport="$($CLIC application:variable:get "$CLIC_APPNAME" mail/transport)"
done;

if [[ "$mail_transport" != "mail" ]]; then
    $CLIC application:variable:set "$CLIC_APPNAME" mail/host --description="Hostname of the mail handler" --if-not-global-exists --default-existing-value
    $CLIC application:variable:set "$CLIC_APPNAME" mail/user --description="Username to connect to the mailhandler" --if-not-global-exists --default-existing-value
    $CLIC application:variable:set "$CLIC_APPNAME" mail/password --description="Password of the mail user" --if-not-global-exists --default-existing-value
    $CLIC application:variable:set "$CLIC_APPNAME" mail/encryption --description="Encryption type for mail [ssl|tls]" --if-not-global-exists --default-existing-value
fi;

$CLIC application:variable:set "$CLIC_APPNAME" mail/sender --description="Sender address of mails"  --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" app/configured 1

cat > app/config/parameters.yml <<EOL
parameters:
    database_driver:   pdo_mysql
    database_host:     $($CLIC application:variable:get "$CLIC_APPNAME" mysql/host --filter=json_encode)
    database_port:     ~
    database_name:     $($CLIC application:variable:get "$CLIC_APPNAME" mysql/database --filter=json_encode)
    database_user:     $($CLIC application:variable:get "$CLIC_APPNAME" mysql/user --filter=json_encode)
    database_password: $($CLIC application:variable:get "$CLIC_APPNAME" mysql/password --filter=json_encode)
    database_path:     ~

    mailer_transport:  $($CLIC application:variable:get "$CLIC_APPNAME" mail/transport --filter=json_encode)
    mailer_host:       $(if [[ "$mail_transport" != "mail" ]]; then $CLIC application:variable:get "$CLIC_APPNAME" mail/host --filter=json_encode; else echo '~'; fi)
    mailer_user:       $(if [[ "$mail_transport" != "mail" ]]; then $CLIC application:variable:get "$CLIC_APPNAME" mail/user --filter=json_encode; else echo '~'; fi)
    mailer_password:   $(if [[ "$mail_transport" != "mail" ]]; then $CLIC application:variable:get "$CLIC_APPNAME" mail/password --filter=json_encode; else echo '~'; fi)
    mailer_encryption: $(if [[ "$mail_transport" != "mail" ]]; then $CLIC application:variable:get "$CLIC_APPNAME" mail/encryption --filter=json_encode; else echo '~'; fi)
    mailer_sender:     $($CLIC application:variable:get "$CLIC_APPNAME" mail/sender --filter=json_encode)

    locale:            en
    secret:            $(pwgen -s 100)
EOL
