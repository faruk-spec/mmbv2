# Chrome/Puppeteer Setup Guide for WhatsApp Bridge

## The Problem

The WhatsApp bridge server uses Puppeteer (headless Chrome) to connect to WhatsApp Web. If you see this error:

```
Error: Failed to launch the browser process
libatk-1.0.so.0: cannot open shared object file: No such file or directory
```

This means Chrome is missing required system libraries.

## Quick Fix (Automatic)

### Step 1: Run the Installation Script

We've provided a script that automatically installs all required libraries:

```bash
cd projects/whatsapp/whatsapp-bridge
sudo ./install-chrome-deps.sh
```

**Note**: This requires root/sudo access. If you don't have it, see "Manual Installation" below.

### Step 2: Install Node Packages

```bash
npm install
```

### Step 3: Start the Bridge

```bash
node server.js
```

### Step 4: Verify

```bash
cd ..
./test-integration.sh
```

You should see:
```
✓ Endpoint responding
Response: {"success":true,"qr":"data:image/png;base64..."}
```

## Manual Installation

If you can't run the automatic script, here's how to install manually:

### Ubuntu/Debian

```bash
sudo apt-get update
sudo apt-get install -y \
    ca-certificates \
    fonts-liberation \
    libasound2 \
    libatk-bridge2.0-0 \
    libatk1.0-0 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libgbm1 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libpango-1.0-0 \
    libx11-6 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxrandr2 \
    libxrender1 \
    wget
```

### CentOS/RHEL/Fedora

```bash
sudo yum install -y \
    alsa-lib \
    atk \
    cairo \
    cups-libs \
    gtk3 \
    libX11 \
    libXcomposite \
    libXcursor \
    libXdamage \
    libXext \
    libXfixes \
    libXrandr \
    libXrender \
    nspr \
    nss \
    pango
```

## Alternative: Use System Chrome

If you have Chrome/Chromium already installed, tell Puppeteer to use it:

### Option 1: Environment Variable

```bash
export PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
export PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome
# or
export PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser
```

Then run:
```bash
npm install
node server.js
```

### Option 2: Modify server.js

Edit `server.js` and update the puppeteer configuration:

```javascript
const client = new Client({
    authStrategy: new LocalAuth({ clientId: sessionId }),
    puppeteer: { 
        headless: true,
        executablePath: '/usr/bin/google-chrome', // or /usr/bin/chromium-browser
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu'
        ]
    }
});
```

## Testing

After installing dependencies, test if Chrome works:

```bash
cd projects/whatsapp/whatsapp-bridge
node -e "const puppeteer = require('puppeteer'); puppeteer.launch({headless: true, args: ['--no-sandbox']}).then(browser => { console.log('✅ Chrome launched successfully!'); browser.close(); }).catch(err => console.error('❌ Failed:', err.message));"
```

Expected output:
```
✅ Chrome launched successfully!
```

## Troubleshooting

### Error: Cannot find Chrome binary

**Solution**: Install Chrome or set `PUPPETEER_EXECUTABLE_PATH`

```bash
# Ubuntu/Debian
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo dpkg -i google-chrome-stable_current_amd64.deb
sudo apt-get install -f

# CentOS/RHEL
sudo yum install -y https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm
```

### Error: libgbm.so.1 not found

**Solution**: Install GBM library

```bash
# Ubuntu/Debian
sudo apt-get install -y libgbm1

# CentOS/RHEL
sudo yum install -y mesa-libgbm
```

### Error: Running as root without --no-sandbox

**Solution**: Don't run as root, or add --no-sandbox flag (already included)

```bash
# Run as non-root user
su - youruser
cd /path/to/whatsapp-bridge
node server.js
```

### Still Not Working?

1. Check Puppeteer troubleshooting: https://pptr.dev/troubleshooting
2. Try running with more verbose logging:
   ```bash
   DEBUG=puppeteer:* node server.js
   ```
3. Check error logs in bridge server console
4. Verify Node.js version: `node --version` (need 18+)

## Docker Alternative

If you have issues with system dependencies, consider using Docker:

```dockerfile
FROM node:18

# Install Chrome dependencies
RUN apt-get update && apt-get install -y \
    ca-certificates \
    fonts-liberation \
    libasound2 \
    libatk-bridge2.0-0 \
    libatk1.0-0 \
    libgbm1 \
    libgtk-3-0 \
    libnss3 \
    libxss1 \
    wget

# Set working directory
WORKDIR /app

# Copy files
COPY package*.json ./
RUN npm install

COPY server.js ./

# Expose port
EXPOSE 3000

# Start server
CMD ["node", "server.js"]
```

Build and run:
```bash
docker build -t whatsapp-bridge .
docker run -p 3000:3000 -v $(pwd)/.wwebjs_auth:/app/.wwebjs_auth whatsapp-bridge
```

## Success Indicators

When everything is working, you should see:

1. **Bridge starts without errors:**
   ```
   WhatsApp Bridge running on http://127.0.0.1:3000
   ```

2. **QR generation works:**
   ```
   QR Code generated for session abc123
   ```

3. **Test passes:**
   ```
   ✓ Endpoint responding
   Response: {"success":true,"qr":"data:image/png;base64..."}
   ```

4. **In browser: Real WhatsApp QR codes appear** (not placeholders)

## Need More Help?

- Check `TROUBLESHOOTING.md` for general issues
- Review Puppeteer docs: https://pptr.dev
- Ask in issues with your error logs
