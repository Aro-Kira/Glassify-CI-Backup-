# OSPF Lab - Complete Beginner's Guide

## STEP-BY-STEP INSTRUCTIONS

---

## PART 1: Physical Connections (In Packet Tracer)

### Connect the Routers:

1. **Connect R1 to R2:**
   - Click on R1 → Click on Serial DCE cable
   - Connect it to R1's Serial0/1/0 interface
   - Then connect the other end to R2's Serial0/0/0 interface

2. **Connect R2 to R3:**
   - Click on R2 → Click on Serial DCE cable
   - Connect it to R2's Serial0/1/0 interface
   - Then connect the other end to R3's Serial0/1/0 interface

3. **Connect PC1 to R1:**
   - Use Copper Straight-Through cable
   - Connect PC1's FastEthernet0 to R1's GigabitEthernet0/0

4. **Connect PC2 to R2:**
   - Use Copper Straight-Through cable
   - Connect PC2's FastEthernet0 to R2's GigabitEthernet0/0

5. **Connect PC3 to R3:**
   - Use Copper Straight-Through cable
   - Connect PC3's FastEthernet0 to R3's GigabitEthernet0/0

---

## PART 2: Configure the PCs

### PC1 Configuration:
1. Click on PC1
2. Go to Desktop tab → IP Configuration
3. Set:
   - IP Address: `192.168.1.2`
   - Subnet Mask: `255.255.255.0`
   - Default Gateway: `192.168.1.1`
4. Close the window

### PC2 Configuration:
1. Click on PC2
2. Go to Desktop tab → IP Configuration
3. Set:
   - IP Address: `192.168.3.2`
   - Subnet Mask: `255.255.255.0`
   - Default Gateway: `192.168.3.1`
4. Close the window

### PC3 Configuration:
1. Click on PC3
2. Go to Desktop tab → IP Configuration
3. Set:
   - IP Address: `192.168.5.2`
   - Subnet Mask: `255.255.255.0`
   - Default Gateway: `192.168.5.1`
4. Close the window

---

## PART 3: Configure Router 1 (R1)

1. Click on Router 1
2. Go to the CLI tab (Command Line Interface)
3. You'll see a prompt like: `Router>`
4. Type these commands EXACTLY as shown (press Enter after each line):

```
enable
```

You should see: `Router#`

```
configure terminal
```

You should see: `Router(config)#`

```
hostname R1
```

Now you should see: `R1(config)#`

```
interface GigabitEthernet0/0
```

You should see: `R1(config-if)#`

```
ip address 192.168.1.1 255.255.255.0
```

```
no shutdown
```

```
exit
```

You're back to: `R1(config)#`

```
interface Serial0/1/0
```

You should see: `R1(config-if)#`

```
ip address 192.168.2.1 255.255.255.0
```

```
clock rate 128000
```

```
no shutdown
```

```
exit
```

Back to: `R1(config)#`

```
router ospf 100
```

You should see: `R1(config-router)#`

```
network 192.168.1.0 0.0.0.255 area 0
```

```
network 192.168.2.0 0.0.0.255 area 0
```

```
exit
```

```
exit
```

Now you're at: `R1#`

```
write memory
```

Wait for "Building configuration..." then "OK"

**R1 is done!** You can minimize it or leave it open.

---

## PART 4: Configure Router 2 (R2)

1. Click on Router 2
2. Go to the CLI tab
3. Type these commands (press Enter after each):

```
enable
```

```
configure terminal
```

```
hostname R2
```

```
interface Serial0/0/0
```

```
ip address 192.168.2.2 255.255.255.0
```

```
no shutdown
```

```
exit
```

```
interface GigabitEthernet0/0
```

```
ip address 192.168.3.1 255.255.255.0
```

```
no shutdown
```

```
exit
```

```
interface Serial0/1/0
```

```
ip address 192.168.4.1 255.255.255.0
```

```
clock rate 128000
```

```
no shutdown
```

```
exit
```

```
router ospf 100
```

```
network 192.168.2.0 0.0.0.255 area 0
```

```
network 192.168.3.0 0.0.0.255 area 0
```

```
network 192.168.4.0 0.0.0.255 area 0
```

```
exit
```

```
exit
```

```
write memory
```

Wait for it to save.

**R2 is done!**

---

## PART 5: Configure Router 3 (R3)

1. Click on Router 3
2. Go to the CLI tab
3. Type these commands (press Enter after each):

```
enable
```

```
configure terminal
```

```
hostname R3
```

```
interface Serial0/1/0
```

```
ip address 192.168.4.2 255.255.255.0
```

```
no shutdown
```

```
exit
```

```
interface GigabitEthernet0/0
```

```
ip address 192.168.5.1 255.255.255.0
```

```
no shutdown
```

```
exit
```

```
router ospf 100
```

```
network 192.168.4.0 0.0.0.255 area 0
```

```
network 192.168.5.0 0.0.0.255 area 0
```

```
exit
```

```
exit
```

```
write memory
```

Wait for it to save.

**R3 is done!**

---

## PART 6: Wait 30-60 Seconds

**IMPORTANT:** After configuring all routers, wait 30-60 seconds for OSPF to exchange routing information and build neighbor relationships.

---

## PART 7: Verification - Check OSPF Neighbors

### On Router 1:
1. Click on Router 1
2. In the CLI, type:

```
show ip ospf neighbor
```

**What you should see:**
- R2 listed as a neighbor on Serial0/1/0
- State should be "FULL" or "FULL/ -"

### On Router 2:
1. Click on Router 2
2. In the CLI, type:

```
show ip ospf neighbor
```

**What you should see:**
- R1 listed on Serial0/0/0
- R3 listed on Serial0/1/0
- Both should be "FULL" state

### On Router 3:
1. Click on Router 3
2. In the CLI, type:

```
show ip ospf neighbor
```

**What you should see:**
- R2 listed as a neighbor on Serial0/1/0
- State should be "FULL"

---

## PART 8: Verification - Check Routing Tables

### On Router 1:
In Router 1's CLI, type:

```
show ip route
```

**What you should see:**
- Routes marked with "O" (that's the letter O for OSPF) for:
  - 192.168.3.0/24 (PC2's network)
  - 192.168.4.0/24 (link between R2 and R3)
  - 192.168.5.0/24 (PC3's network)

### On Router 2:
In Router 2's CLI, type:

```
show ip route
```

**What you should see:**
- Routes marked with "O" for:
  - 192.168.1.0/24 (PC1's network)
  - 192.168.5.0/24 (PC3's network)

### On Router 3:
In Router 3's CLI, type:

```
show ip route
```

**What you should see:**
- Routes marked with "O" for:
  - 192.168.1.0/24 (PC1's network)
  - 192.168.2.0/24 (link between R1 and R2)
  - 192.168.3.0/24 (PC2's network)

---

## PART 9: Test Connectivity - Ping from PCs

### Test from PC1:

1. Click on PC1
2. Go to Desktop tab → Command Prompt
3. Type:

```
ping 192.168.3.2
```

Press Enter. You should see: "Reply from 192.168.3.2..." - Success!

4. Type:

```
ping 192.168.5.2
```

Press Enter. You should see: "Reply from 192.168.5.2..." - Success!

### Test from PC2:

1. Click on PC2
2. Go to Desktop tab → Command Prompt
3. Type:

```
ping 192.168.1.2
```

Press Enter. Success!

4. Type:

```
ping 192.168.5.2
```

Press Enter. Success!

### Test from PC3:

1. Click on PC3
2. Go to Desktop tab → Command Prompt
3. Type:

```
ping 192.168.1.2
```

Press Enter. Success!

4. Type:

```
ping 192.168.3.2
```

Press Enter. Success!

---

## SUCCESS! ✅

If all pings are successful and you see OSPF neighbors, you're done!

---

## Common Mistakes to Avoid:

1. **Forgetting `no shutdown`** - Interfaces won't work without this
2. **Wrong clock rate** - Only on Serial0/1/0 of R1 and R2 (the DCE side)
3. **Typos in IP addresses** - Double-check each one
4. **Wrong wildcard mask** - Always use `0.0.0.255` for networks like 192.168.1.0
5. **Not waiting** - OSPF needs time to form neighbors (30-60 seconds)
6. **Wrong default gateway on PCs** - Must match the router's IP on that network

---

## Quick Reference: What Each Command Does

- `enable` - Gives you admin privileges
- `configure terminal` - Enters configuration mode
- `hostname R1` - Changes router name to R1
- `interface GigabitEthernet0/0` - Opens that interface for configuration
- `ip address 192.168.1.1 255.255.255.0` - Sets the IP address
- `no shutdown` - Turns the interface ON (interfaces are off by default)
- `clock rate 128000` - Sets speed on serial interface (only on DCE side)
- `router ospf 100` - Starts OSPF process number 100
- `network 192.168.1.0 0.0.0.255 area 0` - Tells OSPF to advertise this network
- `exit` - Goes back one level
- `write memory` - Saves your configuration

---

## If Something Doesn't Work:

1. Check that all cables are connected (they should show green dots)
2. Verify all IP addresses are correct
3. Make sure you typed `no shutdown` on every interface
4. Wait longer (OSPF can take up to 60 seconds)
5. Double-check PC default gateways match router IPs
6. Look for error messages in the CLI - they usually tell you what's wrong

Good luck! 🚀
