# How to Fix the WhatsApp QR Code (SSL Error)

> **Problem:** `curl https://web.whatsapp.com` shows `SSL error` on your VPS.  
> **Result:** WhatsApp QR codes never appear — they time out every time.  
> **Cause:** Your VPS provider (or its upstream network) is blocking/intercepting  
> the HTTPS connection to WhatsApp's servers. This is common on cheap Chinese  
> VPS providers, some Indonesian hosts, and certain shared hosting environments.

---

## Step 1 — Confirm the problem

Run this on your server:

```bash
curl https://web.whatsapp.com
```

If you see **`SSL error`** or **`SSL_R_UNKNOWN_PROTOCOL`**, your server cannot
reach WhatsApp directly.  You must fix this before the QR code will work.

Also run the bridge's built-in connectivity test:

```bash
curl http://127.0.0.1:3000/api/connectivity-test
```

If `"tls": { "success": false }`, you have the SSL problem.

---

## Step 2 — Choose one of the three fixes

### ✅ Option A — Buy a cheap SOCKS5 proxy (easiest, ~$2/month)

1. Go to **https://www.webshare.io** (or any SOCKS5 proxy provider)
2. Create a free or paid account
3. Download your proxy list — you will get details like:
   ```
   Host:     123.45.67.89
   Port:     1080
   Username: abcdef
   Password: ghijkl
   ```
4. Go to **Step 3** below

---

### ✅ Option B — Use a second VPS as a SOCKS5 proxy (free if you have one)

If you have another VPS (DigitalOcean, Vultr, Hetzner, etc.) that CAN reach
WhatsApp, you can use SSH as a SOCKS5 proxy.

**On your local machine or the working VPS**, run:

```bash
ssh -D 1080 -N -f user@WORKING_VPS_IP
```

This creates a SOCKS5 proxy on `127.0.0.1:1080` that tunnels through the
working VPS.

Then set:
```
WHATSAPP_PROXY_URL=socks5://127.0.0.1:1080
```

---

### ✅ Option C — Move to a VPS that can reach WhatsApp

Providers known to work: **DigitalOcean (Singapore/US), Vultr, Hetzner, Linode**.

Migrate your server to one of these providers.  This permanently fixes the
problem for all traffic, not just WhatsApp.

---

## Step 3 — Configure the bridge to use the proxy

**3a. Create the configuration file**

```bash
# Run this on your server, in the WhatsApp project folder
cd /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge

cp .env.example .env
nano .env
```

**3b. Inside the .env file, find the line:**

```
# WHATSAPP_PROXY_URL=
```

**Remove the `#` and fill in your proxy details:**

```bash
# For a SOCKS5 proxy with username/password:
WHATSAPP_PROXY_URL=socks5://USERNAME:PASSWORD@HOST:PORT

# For a SOCKS5 proxy WITHOUT username/password:
WHATSAPP_PROXY_URL=socks5://HOST:PORT

# For an HTTP proxy:
WHATSAPP_PROXY_URL=http://USERNAME:PASSWORD@HOST:PORT
```

**Real example** (replace with your values):
```bash
WHATSAPP_PROXY_URL=socks5://alice:mysecret@123.45.67.89:1080
```

Save the file: press `Ctrl+X`, then `Y`, then `Enter`.

---

## Step 4 — Restart the bridge

```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
bash restart-bridge.sh
```

The script will:
1. Load your `.env` file automatically
2. Start the bridge with the proxy
3. Run a connectivity test and tell you if WhatsApp is now reachable

**Expected output when working:**
```
5. Testing WhatsApp connectivity...
   ✓ Server can reach WhatsApp — TLS handshake succeeded.
```

---

## Step 5 — Test the QR code

Go to your WhatsApp panel in the browser and click **"New Session"** or
**"Scan QR"**.  The QR code should appear within 5–10 seconds.

---

## Troubleshooting

| Symptom | Cause | Fix |
|---------|-------|-----|
| Still getting SSL error after setting proxy | Wrong proxy URL format | Double-check `socks5://` prefix and credentials |
| Proxy connects but QR times out | Proxy is slow or blocks WhatsApp | Try a different proxy server |
| `ECONNREFUSED` error | Proxy host/port is wrong | Check the host IP and port number |
| QR appears but scan fails | Different issue — auth problem | Open a new issue |

**View live bridge logs:**
```bash
tail -f /www/wwwroot/mmbtech.online/projects/whatsapp/whatsapp-bridge/bridge-server.log
```

**Run connectivity test manually:**
```bash
curl -s http://127.0.0.1:3000/api/connectivity-test | python3 -m json.tool
```
