# Network Setup Guide - Glassify-CI

This guide will help you:
1. Run the application on localhost
2. Access it from another laptop on the same network

---

## 📋 Prerequisites

- XAMPP 7.4 installed and running
- Both laptops connected to the same Wi-Fi/network
- Windows Firewall configured (instructions below)

---

## 🏠 Step 1: Running on Localhost

### 1.1 Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Start **Apache** (click "Start" button)
3. Start **MySQL** (click "Start" button)
4. Both should show green "Running" status

### 1.2 Access the Application
Open your browser and go to:
```
http://localhost/Glassify-CI/
```

If you see the application, localhost setup is complete! ✅

---

## 🌐 Step 2: Access from Another Laptop

### 2.1 Find Your Computer's IP Address

**Method 1: Using Command Prompt**
1. Press `Win + R`, type `cmd`, press Enter
2. Type: `ipconfig` and press Enter
3. Look for **IPv4 Address** under your active network adapter (usually "Wireless LAN adapter Wi-Fi" or "Ethernet adapter")
4. Note down the IP address (e.g., `192.168.1.100`)

**Method 2: Using PowerShell**
1. Press `Win + X`, select "Windows PowerShell"
2. Type: `ipconfig | findstr IPv4`
3. Note down the IP address

### 2.2 Configure XAMPP Apache for Network Access

1. Open XAMPP Control Panel
2. Click **Config** button next to Apache
3. Select **httpd.conf**
4. Find the line that says:
   ```
   Listen 80
   ```
5. Make sure it's set to listen on all interfaces (should already be `Listen 80`)
6. Find the section:
   ```
   <Directory />
       Options FollowSymLinks
       AllowOverride None
       Require all denied
   </Directory>
   ```
7. Change `Require all denied` to `Require all granted` (⚠️ **Security Note:** Only for local network use!)
8. Find:
   ```
   <Directory "C:/xampp/htdocs">
       Options Indexes FollowSymLinks
       AllowOverride None
       Require all granted
   </Directory>
   ```
9. Make sure it says `Require all granted`
10. Save the file and restart Apache

### 2.3 Configure Windows Firewall

1. Press `Win + R`, type `wf.msc`, press Enter
2. Click **Inbound Rules** → **New Rule**
3. Select **Port** → Next
4. Select **TCP**, enter port **80** → Next
5. Select **Allow the connection** → Next
6. Check all profiles (Domain, Private, Public) → Next
7. Name it "XAMPP Apache" → Finish

**Alternative Quick Method:**
1. Open **Windows Defender Firewall**
2. Click **Allow an app or feature through Windows Firewall**
3. Find **Apache HTTP Server** and check both **Private** and **Public**
4. If not listed, click **Change Settings** → **Allow another app** → Browse to `C:\xampp\apache\bin\httpd.exe`

### 2.4 Update Application Configuration

The `config.php` file has been updated to automatically detect your IP address. However, if you need to manually set it:

1. Open `application/config/config.php`
2. The base_url is now set to auto-detect, but you can manually set it to:
   ```php
   $config['base_url'] = 'http://YOUR_IP_ADDRESS/Glassify-CI/';
   ```
   Replace `YOUR_IP_ADDRESS` with the IP you found in Step 2.1

### 2.5 Access from Another Laptop

On the other laptop:
1. Make sure it's connected to the same Wi-Fi/network
2. Open a web browser
3. Go to:
   ```
   http://YOUR_IP_ADDRESS/Glassify-CI/
   ```
   Replace `YOUR_IP_ADDRESS` with the IP from Step 2.1

---

## 🔧 Troubleshooting

### Can't Access from Other Laptop?

1. **Check Firewall:**
   - Temporarily disable Windows Firewall to test
   - If it works, re-enable and add the rule properly

2. **Check IP Address:**
   - Your IP might change if you reconnect to Wi-Fi
   - Run `ipconfig` again to get the current IP

3. **Check Apache is Running:**
   - Verify Apache shows "Running" in XAMPP Control Panel

4. **Check Network:**
   - Both laptops must be on the same network
   - Try pinging: On the other laptop, open cmd and type `ping YOUR_IP_ADDRESS`

5. **Check Port 80:**
   - Make sure no other application is using port 80
   - In XAMPP, check if Apache shows any errors

### Database Connection Issues

If you can access the site but get database errors:
- Make sure MySQL is running in XAMPP
- Check `application/config/database.php` has correct credentials
- The database should be accessible from localhost (same machine)

---

## 🔒 Security Notes

⚠️ **Important Security Warnings:**

1. **Local Network Only:** This setup allows access from your local network only. Do NOT expose this to the internet.

2. **Firewall:** Always keep Windows Firewall enabled and properly configured.

3. **Production Use:** For production deployment, use proper security measures:
   - HTTPS/SSL certificates
   - Strong database passwords
   - Proper user authentication
   - Regular security updates

4. **XAMPP Default Settings:** XAMPP is configured for development, not production. Never use these settings on a public server.

---

## 📝 Quick Reference

**Localhost URL:**
```
http://localhost/Glassify-CI/
```

**Network URL (from other devices):**
```
http://YOUR_IP_ADDRESS/Glassify-CI/
```

**Find Your IP:**
```cmd
ipconfig
```

**Test Connection (from other laptop):**
```cmd
ping YOUR_IP_ADDRESS
```

---

## ✅ Checklist

- [ ] XAMPP Apache and MySQL are running
- [ ] Application works on localhost
- [ ] Found your computer's IP address
- [ ] Configured Apache httpd.conf for network access
- [ ] Configured Windows Firewall
- [ ] Updated config.php (if needed)
- [ ] Tested access from another laptop

---

**Need Help?** Check the troubleshooting section above or verify all steps are completed.
