# Mail Server Setup Guide

Complete guide for setting up Postfix (SMTP) and Dovecot (IMAP/POP3) with MySQL authentication for the mail hosting platform.

## Prerequisites

- Ubuntu 20.04 or later
- MySQL/MariaDB installed
- SSL certificate (Let's Encrypt recommended)
- Root/sudo access

## 1. Install Required Packages

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Postfix and Dovecot
sudo apt install -y postfix postfix-mysql dovecot-core dovecot-imapd dovecot-pop3d dovecot-lmtpd dovecot-mysql

# Install additional tools
sudo apt install -y opendkim opendkim-tools
```

## 2. Postfix Configuration (SMTP)

### Main Configuration (/etc/postfix/main.cf)

```
# Basic Settings
myhostname = mail.yourdomain.com
mydomain = yourdomain.com
myorigin = $mydomain
inet_interfaces = all
inet_protocols = all

# Virtual Mailbox Settings
virtual_mailbox_domains = mysql:/etc/postfix/mysql-virtual-mailbox-domains.cf
virtual_mailbox_maps = mysql:/etc/postfix/mysql-virtual-mailbox-maps.cf
virtual_alias_maps = mysql:/etc/postfix/mysql-virtual-alias-maps.cf
virtual_transport = lmtp:unix:private/dovecot-lmtp

# TLS Settings
smtp_tls_security_level = may
smtp_tls_CAfile = /etc/ssl/certs/ca-certificates.crt
smtpd_tls_security_level = may
smtpd_tls_cert_file = /etc/letsencrypt/live/mail.yourdomain.com/fullchain.pem
smtpd_tls_key_file = /etc/letsencrypt/live/mail.yourdomain.com/privkey.pem
smtpd_tls_auth_only = yes
smtpd_tls_loglevel = 1
smtpd_tls_session_cache_database = btree:${data_directory}/smtpd_scache

# SASL Authentication
smtpd_sasl_type = dovecot
smtpd_sasl_path = private/auth
smtpd_sasl_auth_enable = yes
broken_sasl_auth_clients = yes

# Restrictions
smtpd_recipient_restrictions =
    permit_mynetworks,
    permit_sasl_authenticated,
    reject_unauth_destination,
    reject_invalid_hostname,
    reject_unknown_recipient_domain

smtpd_sender_restrictions =
    permit_mynetworks,
    permit_sasl_authenticated,
    reject_unknown_sender_domain,
    reject_sender_login_mismatch

# Rate Limiting
smtpd_client_connection_count_limit = 10
smtpd_client_message_rate_limit = 100
smtpd_client_recipient_rate_limit = 200

# DKIM
milter_default_action = accept
milter_protocol = 6
smtpd_milters = inet:127.0.0.1:8891
non_smtpd_milters = $smtpd_milters
```

### Master Configuration (/etc/postfix/master.cf)

Add/uncomment these lines:

```
submission inet n       -       y       -       -       smtpd
  -o syslog_name=postfix/submission
  -o smtpd_tls_security_level=encrypt
  -o smtpd_sasl_auth_enable=yes
  -o smtpd_reject_unlisted_recipient=no
  -o smtpd_recipient_restrictions=permit_sasl_authenticated,reject
  -o milter_macro_daemon_name=ORIGINATING

smtps     inet  n       -       y       -       -       smtpd
  -o syslog_name=postfix/smtps
  -o smtpd_tls_wrappermode=yes
  -o smtpd_sasl_auth_enable=yes
```

### MySQL Virtual Mailbox Domains (/etc/postfix/mysql-virtual-mailbox-domains.cf)

```
user = mail_user
password = your_database_password
hosts = 127.0.0.1
dbname = your_database
query = SELECT 1 FROM mail_domains WHERE domain_name = '%s' AND is_verified = 1 AND deleted_at IS NULL
```

### MySQL Virtual Mailbox Maps (/etc/postfix/mysql-virtual-mailbox-maps.cf)

```
user = mail_user
password = your_database_password
hosts = 127.0.0.1
dbname = your_database
query = SELECT 1 FROM mail_mailboxes WHERE email = '%s' AND is_active = 1 AND deleted_at IS NULL
```

### MySQL Virtual Alias Maps (/etc/postfix/mysql-virtual-alias-maps.cf)

```
user = mail_user
password = your_database_password
hosts = 127.0.0.1
dbname = your_database
query = SELECT destination_email FROM mail_aliases WHERE alias_email = '%s' AND is_active = 1
```

## 3. Dovecot Configuration (IMAP/POP3)

### Main Configuration (/etc/dovecot/dovecot.conf)

```
protocols = imap pop3 lmtp
listen = *
```

### Mail Location (/etc/dovecot/conf.d/10-mail.conf)

```
mail_location = maildir:/var/mail/vhosts/%d/%n
mail_privileged_group = mail

namespace inbox {
  inbox = yes
  
  mailbox Drafts {
    special_use = \Drafts
    auto = subscribe
  }
  mailbox Sent {
    special_use = \Sent
    auto = subscribe
  }
  mailbox Trash {
    special_use = \Trash
    auto = subscribe
  }
  mailbox Spam {
    special_use = \Junk
    auto = subscribe
  }
}
```

### Authentication (/etc/dovecot/conf.d/10-auth.conf)

```
disable_plaintext_auth = yes
auth_mechanisms = plain login

# MySQL authentication
!include auth-sql.conf.ext
```

### SQL Authentication (/etc/dovecot/conf.d/auth-sql.conf.ext)

```
passdb {
  driver = sql
  args = /etc/dovecot/dovecot-sql.conf.ext
}

userdb {
  driver = sql
  args = /etc/dovecot/dovecot-sql.conf.ext
}
```

### SQL Configuration (/etc/dovecot/dovecot-sql.conf.ext)

```
driver = mysql
connect = host=127.0.0.1 dbname=your_database user=mail_user password=your_database_password

default_pass_scheme = SHA512-CRYPT

password_query = \
  SELECT email as user, password_hash as password \
  FROM mail_mailboxes \
  WHERE email = '%u' AND is_active = 1 AND deleted_at IS NULL

user_query = \
  SELECT \
    email as user, \
    'maildir:/var/mail/vhosts/%d/%n' as mail, \
    5000 AS uid, 5000 AS gid, \
    CONCAT('*:storage=', CAST(storage_quota AS CHAR), 'M') AS quota_rule \
  FROM mail_mailboxes \
  WHERE email = '%u' AND is_active = 1 AND deleted_at IS NULL
```

### SSL Configuration (/etc/dovecot/conf.d/10-ssl.conf)

```
ssl = required
ssl_cert = </etc/letsencrypt/live/mail.yourdomain.com/fullchain.pem
ssl_key = </etc/letsencrypt/live/mail.yourdomain.com/privkey.pem
ssl_min_protocol = TLSv1.2
ssl_cipher_list = ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256
ssl_prefer_server_ciphers = yes
```

### LMTP Configuration (/etc/dovecot/conf.d/10-master.conf)

```
service lmtp {
  unix_listener /var/spool/postfix/private/dovecot-lmtp {
    mode = 0600
    user = postfix
    group = postfix
  }
}

service auth {
  unix_listener /var/spool/postfix/private/auth {
    mode = 0666
    user = postfix
    group = postfix
  }
  unix_listener auth-userdb {
    mode = 0600
    user = vmail
  }
}
```

## 4. DKIM Configuration

### Install and Configure OpenDKIM

```bash
# Create directory
sudo mkdir -p /etc/opendkim/keys

# Generate DKIM keys (automated via platform UI)
# Keys are generated per domain and stored in mail_dns_records table

# OpenDKIM configuration (/etc/opendkim.conf)
Mode                    sv
Canonicalization        relaxed/simple
Socket                  inet:8891@localhost
PidFile                 /var/run/opendkim/opendkim.pid
KeyTable                /etc/opendkim/KeyTable
SigningTable            /etc/opendkim/SigningTable
ExternalIgnoreList      /etc/opendkim/TrustedHosts
InternalHosts           /etc/opendkim/TrustedHosts
```

## 5. Firewall Rules

```bash
# Allow mail ports
sudo ufw allow 25/tcp    # SMTP
sudo ufw allow 587/tcp   # Submission
sudo ufw allow 465/tcp   # SMTPS
sudo ufw allow 993/tcp   # IMAPS
sudo ufw allow 995/tcp   # POP3S
```

## 6. Create Mail User and Directories

```bash
# Create vmail user
sudo groupadd -g 5000 vmail
sudo useradd -g vmail -u 5000 vmail -d /var/mail

# Create mail directories
sudo mkdir -p /var/mail/vhosts
sudo chown -R vmail:vmail /var/mail
sudo chmod -R 770 /var/mail
```

## 7. Start Services

```bash
# Enable and start services
sudo systemctl enable postfix dovecot opendkim
sudo systemctl restart postfix dovecot opendkim

# Check status
sudo systemctl status postfix
sudo systemctl status dovecot
sudo systemctl status opendkim
```

## 8. DNS Configuration

Add these DNS records for each domain:

### MX Record
```
Type: MX
Name: @
Value: mail.yourdomain.com
Priority: 10
```

### SPF Record
```
Type: TXT
Name: @
Value: v=spf1 mx a ip4:YOUR_SERVER_IP ~all
```

### DKIM Record (generated automatically)
```
Type: TXT
Name: mail._domainkey
Value: v=DKIM1; k=rsa; p=YOUR_PUBLIC_KEY
```

### DMARC Record
```
Type: TXT
Name: _dmarc
Value: v=DMARC1; p=quarantine; rua=mailto:postmaster@yourdomain.com
```

### PTR Record (Reverse DNS)
Contact your hosting provider to set PTR record pointing to mail.yourdomain.com

## 9. Testing

```bash
# Test Postfix
echo "Test email" | mail -s "Test" test@example.com

# Test SASL authentication
testsaslauthd -u user@example.com -p password

# Check logs
tail -f /var/log/mail.log
```

## 10. Security Hardening

```bash
# Install Fail2ban
sudo apt install fail2ban

# Configure Fail2ban for Postfix and Dovecot
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Edit /etc/fail2ban/jail.local and enable:
[postfix]
enabled = true

[dovecot]
enabled = true

sudo systemctl restart fail2ban
```

## 11. Monitoring

Monitor your mail server using:
- Maillog analyzer: pflogsumm
- Server monitoring: Nagios, Zabbix
- Mail queue: `mailq` command
- Dovecot stats: `doveadm stats`

## Troubleshooting

### Check Postfix Configuration
```bash
postconf -n
postfix check
```

### Check Dovecot Configuration
```bash
doveconf -n
doveadm auth test user@example.com password
```

### Test MySQL Queries
```bash
postmap -q user@example.com mysql:/etc/postfix/mysql-virtual-mailbox-maps.cf
```

### Check Logs
```bash
tail -f /var/log/mail.log
tail -f /var/log/mail.err
journalctl -u postfix -f
journalctl -u dovecot -f
```

## Production Checklist

- [ ] SSL certificates installed and valid
- [ ] DNS records properly configured
- [ ] PTR record set for reverse DNS
- [ ] DKIM keys generated and published
- [ ] Firewall rules configured
- [ ] Fail2ban enabled and configured
- [ ] Backup strategy implemented
- [ ] Monitoring tools set up
- [ ] Log rotation configured
- [ ] Queue processor running as systemd service

## Support

For issues and questions, refer to:
- Postfix documentation: http://www.postfix.org/documentation.html
- Dovecot documentation: https://doc.dovecot.org/
- Platform documentation: /projects/mail/README.md
