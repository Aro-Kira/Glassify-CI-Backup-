# Safe vs Risky Changes in config.php

## ✅ **SAFE Changes (Won't Break Functions)**

### 1. **base_url** - Currently Dynamic (SAFE)
The current auto-detection change is **SAFE** and actually improves functionality:
```php
// Current (SAFE - auto-detects localhost or network IP)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$config['base_url'] = $protocol . '://' . $host . '/Glassify-CI/';

// OR Static (also SAFE - but only works for one scenario)
$config['base_url'] = 'http://localhost/Glassify-CI/';
```
**Why Safe:** CodeIgniter uses this for generating URLs. Both static and dynamic work fine.

### 2. **Language** - SAFE
```php
$config['language'] = 'english';  // Can change to other languages if you have language files
```

### 3. **Charset** - SAFE
```php
$config['charset'] = 'UTF-8';  // Standard, don't change unless needed
```

### 4. **Log Settings** - SAFE
```php
$config['log_threshold'] = 4;  // Can change to 0-4 (0 = no logging, 4 = all)
$config['log_path'] = '';     // Can specify custom path
```

### 5. **Cookie Settings** - SAFE (for local development)
```php
$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = FALSE;  // Set TRUE only if using HTTPS
$config['cookie_httponly'] = FALSE;
$config['cookie_samesite'] = 'Lax';
```

### 6. **Time Reference** - SAFE
```php
$config['time_reference'] = 'local';  // Can change to timezone like 'Asia/Manila'
```

### 7. **Output Compression** - SAFE (usually leave FALSE)
```php
$config['compress_output'] = FALSE;  // Can enable if server supports it
```

---

## ⚠️ **RISKY Changes (May Break Functions)**

### 1. **index_page** - RISKY if changed incorrectly
```php
$config['index_page'] = '';  // ✅ CORRECT - empty for clean URLs
// DON'T change to 'index.php' unless you have URL rewrite issues
```

### 2. **uri_protocol** - RISKY
```php
$config['uri_protocol'] = 'REQUEST_URI';  // ✅ CORRECT - don't change unless URLs break
// Changing this can break routing
```

### 3. **enable_query_strings** - RISKY
```php
$config['enable_query_strings'] = FALSE;  // ✅ CORRECT - don't change
// Changing to TRUE will break all your URLs
```

### 4. **permitted_uri_chars** - RISKY
```php
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';  // Don't change unless you know what you're doing
// Removing characters can break URLs with those characters
```

### 5. **Session Settings** - RISKY
```php
$config['sess_driver'] = 'files';  // Don't change unless you set up database/redis
$config['sess_save_path'] = APPPATH . 'writable/session';  // ✅ CORRECT - don't change
$config['sess_match_ip'] = FALSE;  // Changing to TRUE can break sessions on network access
$config['sess_expiration'] = 7200;  // Can adjust, but be careful
```

### 6. **Database Settings** - VERY RISKY
**Don't touch these in config.php** - they're in `database.php`:
- Changing database settings incorrectly will break the entire application

### 7. **CSRF Protection** - RISKY if enabled incorrectly
```php
$config['csrf_protection'] = FALSE;  // Currently disabled
// If you enable this, you need to add CSRF tokens to all forms
```

---

## 🔄 **Reverting base_url to Static (If Needed)**

If you want to revert to the original static localhost URL:

```php
// Replace lines 26-29 with:
$config['base_url'] = 'http://localhost/Glassify-CI/';
```

**However, the dynamic version is BETTER** because:
- ✅ Works on localhost automatically
- ✅ Works on network access automatically
- ✅ No manual changes needed when switching between localhost and network
- ✅ Same functionality, just smarter

---

## 📋 **Summary**

### ✅ **Safe to Change:**
- `base_url` (static or dynamic - both work)
- `language`
- `charset`
- `log_threshold`
- `log_path`
- `cookie_*` settings (for local dev)
- `time_reference`
- `compress_output`

### ⚠️ **Don't Change (Unless You Know What You're Doing):**
- `index_page`
- `uri_protocol`
- `enable_query_strings`
- `permitted_uri_chars`
- `sess_*` settings (especially `sess_save_path` and `sess_match_ip`)
- Database settings (in database.php)

---

## 🎯 **Current Status**

Your current `config.php` is **SAFE** and **OPTIMIZED**:
- ✅ Dynamic base_url works for both localhost and network
- ✅ All other settings are at safe defaults
- ✅ No breaking changes

**Recommendation:** Keep the current dynamic base_url setup. It's the best of both worlds!
