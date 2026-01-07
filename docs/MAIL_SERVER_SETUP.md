# Mail Server Setup Guide

## Overview
This guide will help you set up and configure the mail hosting server for MMB v2 platform.

## Prerequisites
- Ubuntu 20.04+ or similar Linux distribution
- Root or sudo access
- Domain name with DNS access
- SSL certificate (Let's Encrypt recommended)

## 1. Database Setup

### Run Migrations
```bash
# Navigate to project directory
cd /www/wwwroot/your-domain.com

# Run the currency column migration
mysql -u your_user -p your_database < projects/mail/migrations/add_currency_column.sql
```

### Verify Tables
Ensure these tables exist:
- `mail_subscription_plans`
- `mail_subscribers`
- `mail_subscriptions`
- `mail_domains`
- `mail_mailboxes`
- `mail_aliases`
- `mail_plan_features`
- `mail_system_settings`
- `mail_admin_actions`
- `mail_logs`

## 2. Install Mail Server Software

### Option A: Postfix + Dovecot (Recommended)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Postfix (SMTP server)
sudo apt install postfix postfix-mysql -y

# Install Dovecot (IMAP/POP3 server)
sudo apt install dovecot-core dovecot-imapd dovecot-pop3d dovecot-lmtpd dovecot-mysql -y

# Install additional tools
sudo apt install opendkim opendkim-tools postfix-policyd-spf-python -y
```

## 3. Configure Postfix

### Main Configuration (/etc/postfix/main.cf)

```conf
# Basic Settings
myhostname = mail.yourdomain.com
mydomain = yourdomain.com
myorigin = $mydomain
inet_interfaces = all
inet_protocols = all

# MySQL Virtual Mailbox Settings
virtual_mailbox_domains = mysql:/etc/postfix/mysql-virtual-mailbox-domains.cf
virtual_mailbox_maps = mysql:/etc/postfix/mysql-virtual-mailbox-maps.cf
virtual_alias_maps = mysql:/etc/postfix/mysql-virtual-alias-maps.cf
virtual_transport = lmtp:unix:private/dovecot-lmtp

# TLS Settings
smtpd_tls_cert_file = /etc/letsencrypt/live/mail.yourdomain.com/fullchain.pem
smtpd_tls_key_file = /etc/letsencrypt/live/mail.yourdomain.com/privkey.pem
smtpd_use_tls = yes
smtpd_tls_security_level = may
smtp_tls_security_level = may

# SASL Authentication
smtpd_sasl_type = dovecot
smtpd_sasl_path = private/auth
smtpd_sasl_auth_enable = yes
```

### MySQL Configuration Files

Create `/etc/postfix/mysql-virtual-mailbox-domains.cf`:
```conf
user = your_db_user
password = your_db_password
hosts = localhost
dbname = your_database
query = SELECT domain_name FROM mail_domains WHERE domain_name='%s' AND is_active=1
```

Create `/etc/postfix/mysql-virtual-mailbox-maps.cf`:
```conf
user = your_db_user
password = your_db_password
hosts = localhost
dbname = your_database
query = SELECT CONCAT(local_part, '@', d.domain_name) FROM mail_mailboxes m JOIN mail_domains d ON m.domain_id = d.id WHERE CONCAT(local_part, '@', d.domain_name)='%s' AND m.is_active=1
```

## 4. DNS Configuration

### Required DNS Records

```dns
; MX Record (Mail Exchange)
@               IN  MX  10  mail.yourdomain.com.

; A Record for mail server
mail            IN  A       YOUR_SERVER_IP

; SPF Record (Sender Policy Framework)
@               IN  TXT     "v=spf1 mx a ip4:YOUR_SERVER_IP ~all"

; DKIM Record (DomainKeys Identified Mail)
default._domainkey  IN  TXT  "v=DKIM1; k=rsa; p=YOUR_PUBLIC_KEY"

; DMARC Record
_dmarc          IN  TXT     "v=DMARC1; p=quarantine; rua=mailto:postmaster@yourdomain.com"
```

## 5. SSL/TLS Setup with Let's Encrypt

```bash
# Install certbot
sudo apt install certbot -y

# Get certificate
sudo certbot certonly --standalone -d mail.yourdomain.com

# Auto-renewal
echo "0 3 * * * certbot renew --quiet && systemctl reload postfix dovecot" | sudo crontab -
```

## 6. Security Hardening

### Configure Firewall

```bash
# Allow mail ports
sudo ufw allow 25/tcp   # SMTP
sudo ufw allow 587/tcp  # Submission
sudo ufw allow 993/tcp  # IMAPS
sudo ufw enable
```

## 7. Start Services

```bash
# Start and enable
sudo systemctl start postfix dovecot
sudo systemctl enable postfix dovecot

# Check status
sudo systemctl status postfix dovecot
```

## 8. Platform Configuration

### Update System Settings

Navigate to: `/admin/projects/mail/settings`

Configure:
- SMTP Host: `mail.yourdomain.com`
- SMTP Port: `587`
- IMAP Host: `mail.yourdomain.com`
- IMAP Port: `993`

### Create Plans

Navigate to: `/admin/projects/mail/plans`

1. Edit or create plans
2. Set currency (run migration first)
3. Configure features

## Troubleshooting

```bash
# Check Postfix
sudo postfix check
sudo tail -f /var/log/mail.log

# Test SMTP
telnet localhost 25

# Test IMAP
openssl s_client -connect localhost:993
```

## Support

For issues:
- Check logs: `/admin/projects/mail/logs`
- Review: `/admin/projects/mail/abuse`
