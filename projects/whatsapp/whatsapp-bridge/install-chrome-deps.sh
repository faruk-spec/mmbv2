#!/bin/bash

# WhatsApp Bridge - Chrome/Puppeteer Dependencies Installation Script
# This script installs required system libraries for Chrome/Puppeteer to run

echo "=================================================="
echo "WhatsApp Bridge - Chrome Dependencies Installer"
echo "=================================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "⚠️  This script requires root/sudo privileges"
    echo "Please run: sudo ./install-chrome-deps.sh"
    exit 1
fi

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    VER=$VERSION_ID
else
    echo "❌ Cannot detect OS. Please install dependencies manually."
    exit 1
fi

echo "Detected OS: $OS $VER"
echo ""

# Install dependencies based on OS
case "$OS" in
    ubuntu|debian)
        echo "📦 Installing Chrome dependencies for Ubuntu/Debian..."
        apt-get update
        apt-get install -y \
            ca-certificates \
            fonts-liberation \
            libasound2 \
            libatk-bridge2.0-0 \
            libatk1.0-0 \
            libc6 \
            libcairo2 \
            libcups2 \
            libdbus-1-3 \
            libexpat1 \
            libfontconfig1 \
            libgbm1 \
            libgcc1 \
            libglib2.0-0 \
            libgtk-3-0 \
            libnspr4 \
            libnss3 \
            libpango-1.0-0 \
            libpangocairo-1.0-0 \
            libstdc++6 \
            libx11-6 \
            libx11-xcb1 \
            libxcb1 \
            libxcomposite1 \
            libxcursor1 \
            libxdamage1 \
            libxext6 \
            libxfixes3 \
            libxi6 \
            libxrandr2 \
            libxrender1 \
            libxss1 \
            libxtst6 \
            lsb-release \
            wget \
            xdg-utils
        
        if [ $? -eq 0 ]; then
            echo "✅ Dependencies installed successfully!"
        else
            echo "❌ Failed to install some dependencies"
            exit 1
        fi
        ;;
        
    centos|rhel|fedora)
        echo "📦 Installing Chrome dependencies for CentOS/RHEL/Fedora..."
        yum install -y \
            alsa-lib \
            atk \
            cairo \
            cups-libs \
            dbus-glib \
            expat \
            fontconfig \
            GConf2 \
            glib2 \
            gtk3 \
            libX11 \
            libXcomposite \
            libXcursor \
            libXdamage \
            libXext \
            libXfixes \
            libXi \
            libXrandr \
            libXrender \
            libXScrnSaver \
            libXtst \
            nspr \
            nss \
            pango \
            xorg-x11-fonts-Type1 \
            xorg-x11-fonts-100dpi \
            xorg-x11-fonts-75dpi
        
        if [ $? -eq 0 ]; then
            echo "✅ Dependencies installed successfully!"
        else
            echo "❌ Failed to install some dependencies"
            exit 1
        fi
        ;;
        
    *)
        echo "❌ Unsupported OS: $OS"
        echo ""
        echo "Please install Chrome/Chromium dependencies manually:"
        echo "https://pptr.dev/troubleshooting#chrome-headless-doesnt-launch-on-unix"
        exit 1
        ;;
esac

echo ""
echo "=================================================="
echo "✅ Installation Complete!"
echo "=================================================="
echo ""
echo "Next steps:"
echo "1. Install npm packages: npm install"
echo "2. Start bridge server: node server.js"
echo "3. Test the bridge: cd .. && ./test-integration.sh"
echo ""
