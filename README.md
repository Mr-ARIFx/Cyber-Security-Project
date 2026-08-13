
# 🔐 Cyber Security Project — ACMECA PKI & Secure Web Infrastructure

<p align="center">
  <img src="docs/images/project-banner.png" alt="Cyber Security Project Banner" width="900">
</p>

<p align="center">
  <b>Certificate Authority • PKI • DNS • HTTPS/TLS • Web Application • IDS • Network Security</b>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Ubuntu-24.04-orange?logo=ubuntu" alt="Ubuntu">
  <img src="https://img.shields.io/badge/Apache-2.4-red?logo=apache" alt="Apache">
  <img src="https://img.shields.io/badge/OpenSSL-3.x-green?logo=openssl" alt="OpenSSL">
  <img src="https://img.shields.io/badge/BIND9-DNS-blue" alt="BIND9">
  <img src="https://img.shields.io/badge/Suricata-IDS-orange" alt="Suricata">
  <img src="https://img.shields.io/badge/Wireshark-Network%20Analysis-blue?logo=wireshark" alt="Wireshark">
  <img src="https://img.shields.io/badge/Nmap-Network%20Scanning-blue" alt="Nmap">
</p>

---

## 📌 Table of Contents

- [Project Overview](#-project-overview)
- [Project Objectives](#-project-objectives)
- [System Architecture](#-system-architecture)
- [Project Components](#-project-components)
- [Technology Stack](#-technology-stack)
- [Repository Structure](#-repository-structure)
- [Public Key Infrastructure](#-public-key-infrastructure)
- [Certificate Authority Hierarchy](#-certificate-authority-hierarchy)
- [Certificate Lifecycle](#-certificate-lifecycle)
- [DNS Configuration](#-dns-configuration)
- [HTTPS and TLS](#-https-and-tls)
- [Certificate Validation](#-certificate-validation)
- [Certificate Revocation](#-certificate-revocation)
- [Web Application](#-web-application)
- [Network Security](#-network-security)
- [Suricata IDS](#-suricata-ids)
- [Wireshark Network Analysis](#-wireshark-network-analysis)
- [Nmap Network Scanning](#-nmap-network-scanning)
- [Security Testing](#-security-testing)
- [Installation and Setup](#-installation-and-setup)
- [Configuration](#-configuration)
- [Running the Project](#-running-the-project)
- [Demonstration Workflow](#-demonstration-workflow)
- [Important Commands](#-important-commands)
- [Security Considerations](#-security-considerations)
- [Limitations](#-limitations)
- [Future Improvements](#-future-improvements)
- [Academic Context](#-academic-context)
- [Authors](#-authors)
- [License](#-license)

---

# 🔐 Project Overview

This project implements a complete educational cybersecurity environment combining a **Public Key Infrastructure (PKI)**, **Certificate Authority (CA)**, **DNS infrastructure**, **HTTPS/TLS-secured web services**, a **Certificate Authority web application**, and **network intrusion detection and analysis**.

The system demonstrates how digital certificates are created, requested, issued, validated, deployed, and revoked in a controlled environment.

The project also demonstrates defensive network-security concepts including:

- Network service discovery
- Port scanning
- TLS traffic analysis
- Network packet capture
- SYN-based traffic detection
- Intrusion Detection System (IDS) monitoring
- Custom Suricata rules
- Security log analysis
- Certificate inspection and verification
- Certificate revocation

The entire environment is designed as a controlled laboratory system using multiple virtual machines.

---

# 🎯 Project Objectives

The primary objectives of this project are:

1. Build a functional Certificate Authority infrastructure.
2. Implement a hierarchical PKI architecture.
3. Generate and manage RSA private keys.
4. Generate Certificate Signing Requests (CSRs).
5. Issue digital certificates.
6. Support different certificate validation levels.
7. Configure DNS using BIND9.
8. Configure Apache HTTP and HTTPS services.
9. Implement TLS 1.2 and TLS 1.3.
10. Validate certificate chains.
11. Demonstrate certificate revocation.
12. Develop a web-based certificate application.
13. Configure Suricata as an IDS.
14. Create and test custom IDS rules.
15. Capture network traffic using Wireshark.
16. Perform controlled network reconnaissance using Nmap.
17. Analyze network security events and logs.

---

# 🏗️ System Architecture

The project uses a virtualized client-server environment.

```text
                         ┌─────────────────────────┐
                         │       Client VM         │
                         │                         │
                         │  Browser                │
                         │  curl                   │
                         │  dig                    │
                         │  OpenSSL                │
                         │  Wireshark              │
                         │  Nmap                   │
                         └────────────┬────────────┘
                                      │
                         Host-Only Network
                                      │
                                      ▼
                         ┌─────────────────────────┐
                         │       Server VM         │
                         │                         │
                         │  BIND9 DNS              │
                         │  Apache2                │
                         │  HTTPS/TLS              │
                         │  ACMECA Web Application │
                         │  OpenSSL PKI            │
                         │  Suricata IDS           │
                         └────────────┬────────────┘
                                      │
                                      ▼
                         ┌─────────────────────────┐
                         │       PKI Hierarchy     │
                         │                         │
                         │       Root CA           │
                         │          │              │
                         │     ┌────┼────┐         │
                         │     ▼    ▼    ▼         │
                         │    DV   OV   EV         │
                         │    CA   CA   CA         │
                         └─────────────────────────┘
````

---

# 🧩 Project Components

The project consists of several major components.

## 1. Public Key Infrastructure

The PKI is responsible for:

* Private key generation
* CSR generation
* Certificate issuance
* Certificate validation
* Certificate chain construction
* Certificate revocation

---

## 2. Certificate Authority

The project contains:

* Root CA
* DV Subordinate CA
* OV Subordinate CA
* EV Subordinate CA

These CAs are used to issue certificates for different validation levels.

---

## 3. DNS Server

BIND9 provides authoritative DNS resolution for the project domains.

Example:

```text
acmeca.com
www.acmeca.com
ns1.acmeca.com
```

During the viva, the system can also be configured with a newly assigned random domain.

---

## 4. Web Application

The CA web application provides functionality for:

* User registration
* User login
* Certificate request submission
* Certificate request management
* Administrative approval
* Certificate generation
* Certificate download
* Request status tracking

---

## 5. Apache Web Server

Apache provides:

```text
HTTP
HTTPS
TLS 1.2
TLS 1.3
Virtual Hosts
HTTP → HTTPS redirection
```

---

## 6. Intrusion Detection

Suricata monitors network traffic and generates alerts for suspicious activity.

The project also includes custom IDS rules for controlled laboratory testing.

---

# 🛠️ Technology Stack

| Technology   | Purpose                |
| ------------ | ---------------------- |
| Ubuntu 24.04 | Operating system       |
| VirtualBox   | Virtualization         |
| Apache2      | Web server             |
| BIND9        | DNS server             |
| OpenSSL      | PKI and TLS            |
| PHP          | Web application        |
| SQLite       | Application database   |
| Suricata     | Intrusion Detection    |
| Wireshark    | Packet analysis        |
| Nmap         | Network reconnaissance |
| Git          | Version control        |
| GitHub       | Source-code hosting    |

---

# 📁 Repository Structure

```text
Cyber-Security-Project/
│
├── CA-Web-Application/
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── login.php
│   │   └── logout.php
│   │
│   ├── includes/
│   │   ├── auth.php
│   │   ├── db.php
│   │   ├── functions.php
│   │   ├── index.php
│   │   └── navbar.php
│   │
│   ├── css/
│   │   └── style.css
│   │
│   ├── apply.php
│   ├── config.php
│   ├── contact.php
│   ├── create_admin.php
│   ├── download.php
│   ├── generate.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   ├── request_detail.php
│   └── status.php
│
├── PKI/
│   │
│   ├── root-ca/
│   │   └── openssl.cnf
│   │
│   ├── dv-ca/
│   │   ├── openssl.cnf
│   │   └── dv-ca-ext.cnf
│   │
│   ├── ov-ca/
│   │   ├── openssl.cnf
│   │   ├── ov-ca-ext.cnf
│   │   └── apply-ext.cnf
│   │
│   ├── ev-ca/
│   │   ├── openssl.cnf
│   │   └── ev-ca-ext.cnf
│   │
│   └── server/
│       └── server-ext.cnf
│
├── docs/
│   │
│   ├── images/
│   │   ├── project-banner.png
│   │   ├── architecture.png
│   │   ├── pki-hierarchy.png
│   │   ├── dns-architecture.png
│   │   ├── tls-handshake.png
│   │   ├── suricata-alert.png
│   │   └── wireshark-capture.png
│   │
│   └── screenshots/
│
├── .gitignore
├── LICENSE
└── README.md
```

> Private keys, passwords, databases containing sensitive information, generated certificates, logs, and other sensitive runtime files should not be committed to the repository.

---

# 🔑 Public Key Infrastructure

The PKI is based on asymmetric cryptography.

A private key is kept secret by the certificate owner while the corresponding public key can be distributed through a certificate.

The general certificate workflow is:

```text
Private Key
     │
     ▼
   CSR
     │
     ▼
Certificate Authority
     │
     ▼
Digital Certificate
     │
     ▼
Apache HTTPS
```

---

# 🌳 Certificate Authority Hierarchy

The project follows a hierarchical CA model.

```text
                         Root CA
                           │
             ┌─────────────┼─────────────┐
             │             │             │
             ▼             ▼             ▼
           DV CA          OV CA          EV CA
             │             │             │
             └─────────────┼─────────────┘
                           │
                           ▼
                    Server Certificate
```

The Root CA provides trust to the subordinate CAs.

The subordinate CAs issue end-entity certificates.

---

# 📜 Certificate Lifecycle

The certificate lifecycle implemented in the project is:

```text
Generate Private Key
        │
        ▼
Generate CSR
        │
        ▼
Submit Certificate Request
        │
        ▼
Administrative Approval
        │
        ▼
Certificate Issuance
        │
        ▼
Deploy Certificate
        │
        ▼
Configure HTTPS
        │
        ▼
Verify Certificate
        │
        ▼
Renew / Revoke
```

---

# 🧾 Certificate Signing Request

A CSR contains information required by the CA to issue a certificate.

Important fields include:

```text
Subject
Common Name
Organization
Organizational Unit
Country
Public Key
Subject Alternative Name
Signature
```

Example:

```text
CN = example.com

SAN:
DNS:example.com
DNS:www.example.com
```

The CSR is signed using the corresponding private key.

---

# 🌐 DNS Configuration

BIND9 is used as the authoritative DNS server.

Example zone:

```text
example.com
```

Example records:

```text
example.com       A     192.168.56.101
www.example.com   A     192.168.56.101
ns1.example.com   A     192.168.56.101
```

DNS allows the client to resolve the domain name to the server's IP address.

---

# 🔒 HTTPS and TLS

Apache is configured to provide secure HTTPS communication.

The HTTPS virtual host contains:

```text
ServerName
ServerAlias
DocumentRoot
SSLEngine
SSLCertificateFile
SSLCertificateKeyFile
SSLProtocol
```

HTTP requests are redirected to HTTPS.

```text
HTTP
 │
 ▼
301 Redirect
 │
 ▼
HTTPS
 │
 ▼
TLS-secured communication
```

---

# 🔐 TLS Versions

The server supports:

```text
TLS 1.2
TLS 1.3
```

Older insecure protocol versions are disabled.

The TLS connection provides:

* Confidentiality
* Integrity
* Server authentication

---

# 🔎 Certificate Inspection

Certificates can be inspected using OpenSSL.

Example:

```bash
openssl x509 \
-in certificate.pem \
-noout \
-subject \
-issuer \
-dates \
-ext subjectAltName
```

Important information:

```text
Subject
Issuer
Validity Period
Subject Alternative Name
```

---

# ✅ Certificate Verification

Certificate verification confirms that the certificate chains to a trusted CA.

Example:

```bash
openssl verify \
-CAfile root-ca.cert.pem \
-untrusted subca.cert.pem \
server.cert.pem
```

Expected result:

```text
server.cert.pem: OK
```

---

# 🔗 Certificate Chain

A normal certificate chain looks like:

```text
Server Certificate
       │
       ▼
Subordinate CA
       │
       ▼
Root CA
```

The client trusts the Root CA.

Therefore:

```text
Root CA
   ↓
Trusted SubCA
   ↓
Valid Server Certificate
   ↓
Trusted HTTPS Connection
```

---

# 🚫 Certificate Revocation

The project also demonstrates certificate revocation.

A revoked certificate should no longer be considered trustworthy.

The basic concept is:

```text
Valid Certificate
       │
       ▼
Revocation Event
       │
       ▼
Certificate becomes revoked
       │
       ▼
CRL / Revocation checking
```

Certificate revocation is an important part of PKI security because a certificate may need to be invalidated before its normal expiration date.

---

# 🌐 Web Application

The CA web application is implemented using PHP.

## User Features

Users can:

* Create an account
* Log in
* Submit certificate requests
* View request status
* View request details
* Download issued certificates
* Log out

## Administrator Features

Administrators can:

* Log in
* View certificate requests
* Review requests
* Approve requests
* Generate certificates
* Manage certificate issuance

---

# 🗄️ Application Database

The application uses SQLite for data storage.

The database stores application-related information such as:

```text
Users
Certificate Requests
Request Status
Certificate Metadata
```

Passwords are not intended to be stored as plaintext.

Password authentication uses password hashing.

---

# 🛡️ Security Controls

The project incorporates several security controls.

## Authentication

Protected application pages require authentication.

## Authorization

Administrative functionality is separated from normal user functionality.

## Password Hashing

Passwords are stored using hashes rather than plaintext values.

## HTTPS

Sensitive communication is protected using TLS.

## Input Handling

User-supplied information is handled through server-side application logic.

## Private Key Protection

Private keys should remain on the server and should never be uploaded to GitHub.

---

# 🛡️ Suricata IDS

Suricata is used as an Intrusion Detection System.

Its purpose is to inspect network traffic and generate security alerts when traffic matches configured rules.

The IDS workflow is:

```text
Network Traffic
      │
      ▼
  Suricata
      │
      ▼
Rule Matching
      │
      ▼
Security Alert
      │
      ▼
Suricata Logs
```

---

# 📢 Custom IDS Rules

A custom Suricata rule can be used to detect a specific traffic pattern.

Example rule structure:

```text
alert tcp any any -> any 3000
(
    msg:"Custom SYN Detection";
    flags:S;
    sid:1000001;
    rev:1;
)
```

The exact rule should be adapted to the service and network configuration used in the laboratory environment.

---

# 📂 Suricata Logs

Important Suricata files include:

```text
/var/log/suricata/fast.log
/var/log/suricata/eve.json
```

The `fast.log` file provides human-readable alerts.

The `eve.json` file provides structured event information.

---

# 🦈 Wireshark Network Analysis

Wireshark is used to capture and analyze network packets.

The project uses Wireshark to demonstrate:

* DNS traffic
* HTTP traffic
* HTTPS traffic
* TCP connections
* TLS handshakes
* SYN packets
* Network scanning activity
* IDS-related traffic

---

# 🔍 Useful Wireshark Filters

## TCP SYN packets

```text
tcp.flags.syn == 1 && tcp.flags.ack == 0
```

## DNS traffic

```text
dns
```

## HTTP traffic

```text
http
```

## HTTPS/TLS traffic

```text
tls
```

## TCP traffic to a specific port

```text
tcp.port == 3000
```

## Traffic from a specific IP

```text
ip.addr == 192.168.56.101
```

---

# 🛰️ Nmap Network Scanning

Nmap is used for controlled network reconnaissance.

The purpose is to identify:

* Open ports
* Running services
* Network exposure
* Service versions

Example:

```bash
nmap 192.168.56.101
```

Service detection:

```bash
nmap -sV 192.168.56.101
```

Specific port scan:

```bash
nmap -p 22,53,80,443 192.168.56.101
```

All Nmap testing in this project should be performed only against the project's own laboratory machines.

---

# 🧪 Security Testing

The project includes controlled security testing in the isolated virtual environment.

Testing includes:

```text
DNS Testing
      │
      ├── Domain Resolution
      ├── Reverse DNS
      └── DNS Record Verification

TLS Testing
      │
      ├── Certificate Inspection
      ├── Certificate Chain Verification
      ├── TLS 1.2
      └── TLS 1.3

Network Testing
      │
      ├── Nmap
      ├── Wireshark
      └── TCP Analysis

IDS Testing
      │
      ├── Suricata
      ├── Custom Rules
      └── Alert Analysis
```

All security testing is performed in a controlled VM environment.

---

# 💻 Installation and Setup

## Requirements

Recommended environment:

```text
Ubuntu 24.04
VirtualBox
Apache2
BIND9
OpenSSL
PHP
SQLite
Suricata
Wireshark
Nmap
Git
```

---

# 📥 Clone the Repository

```bash
git clone https://github.com/Mr-ARIFx/Cyber-Security-Project.git
```

Enter the project:

```bash
cd Cyber-Security-Project
```

---

# 🌐 Install Required Packages

Example Ubuntu packages:

```bash
sudo apt update
sudo apt install apache2 bind9 openssl php sqlite3 suricata wireshark nmap git -y
```

---

# ⚙️ Configure BIND9

The DNS configuration should contain the required project zones.

After configuration:

```bash
sudo named-checkconf
```

Check the zone:

```bash
sudo named-checkzone example.com /etc/bind/db.example.com
```

Reload BIND:

```bash
sudo systemctl reload bind9
```

---

# 🌍 Configure Apache

Enable SSL:

```bash
sudo a2enmod ssl
```

Enable required virtual hosts:

```bash
sudo a2ensite example.com.conf
```

Check Apache configuration:

```bash
sudo apache2ctl configtest
```

Expected:

```text
Syntax OK
```

Reload:

```bash
sudo systemctl reload apache2
```

---

# 🔐 Configure HTTPS

An HTTPS virtual host should contain:

```apache
<VirtualHost *:443>

    ServerName example.com
    ServerAlias www.example.com

    DocumentRoot /var/www/acmeca/public

    SSLEngine on

    SSLCertificateFile /path/to/certificate.pem
    SSLCertificateKeyFile /path/to/private-key.pem

    SSLProtocol -all +TLSv1.2 +TLSv1.3

</VirtualHost>
```

---

# ▶️ Running the Project

After configuring the environment:

### Start Apache

```bash
sudo systemctl start apache2
```

### Start BIND9

```bash
sudo systemctl start bind9
```

### Start Suricata

```bash
sudo systemctl start suricata
```

Check service status:

```bash
systemctl is-active apache2
systemctl is-active bind9
systemctl is-active suricata
```

---

# 🧪 Demonstration Workflow

The complete demonstration can follow this sequence:

```text
1. Obtain assigned domain
          ↓
2. Configure DNS
          ↓
3. Verify DNS resolution
          ↓
4. Generate private key
          ↓
5. Generate CSR
          ↓
6. Submit CSR
          ↓
7. Issue certificate
          ↓
8. Inspect certificate
          ↓
9. Verify certificate chain
          ↓
10. Configure Apache HTTPS
          ↓
11. Test HTTPS
          ↓
12. Demonstrate TLS
          ↓
13. Demonstrate certificate revocation
          ↓
14. Demonstrate Suricata IDS
          ↓
15. Demonstrate Wireshark
          ↓
16. Demonstrate Nmap
```

---

# 🧰 Important Commands

## DNS

```bash
dig example.com
```

```bash
dig www.example.com
```

```bash
dig @192.168.56.101 example.com
```

```bash
dig -x 192.168.56.101
```

---

## BIND9

```bash
sudo named-checkconf
```

```bash
sudo named-checkzone example.com /etc/bind/db.example.com
```

```bash
sudo systemctl reload bind9
```

---

## OpenSSL Private Key

```bash
openssl genpkey \
-algorithm RSA \
-pkeyopt rsa_keygen_bits:2048 \
-out server.key.pem
```

---

## CSR

```bash
openssl req \
-new \
-sha256 \
-key server.key.pem \
-out server.csr.pem
```

---

## CSR Verification

```bash
openssl req \
-in server.csr.pem \
-noout \
-verify \
-subject
```

---

## CSR Inspection

```bash
openssl req \
-in server.csr.pem \
-noout \
-text
```

---

## Certificate Inspection

```bash
openssl x509 \
-in certificate.pem \
-noout \
-subject \
-issuer \
-dates
```

---

## SAN Inspection

```bash
openssl x509 \
-in certificate.pem \
-noout \
-ext subjectAltName
```

---

## Certificate Verification

```bash
openssl verify \
-CAfile root-ca.cert.pem \
-untrusted subca.cert.pem \
certificate.pem
```

Expected:

```text
certificate.pem: OK
```

---

## TLS Testing

```bash
openssl s_client \
-connect example.com:443 \
-servername example.com
```

TLS 1.2:

```bash
openssl s_client \
-connect example.com:443 \
-servername example.com \
-tls1_2
```

TLS 1.3:

```bash
openssl s_client \
-connect example.com:443 \
-servername example.com \
-tls1_3
```

---

## HTTP → HTTPS

```bash
curl -I http://example.com
```

Expected:

```text
HTTP/1.1 301 Moved Permanently
Location: https://example.com/
```

---

## HTTPS

```bash
curl -I https://example.com
```

---

## Apache

```bash
sudo apache2ctl configtest
```

```bash
sudo apache2ctl -S
```

```bash
sudo systemctl reload apache2
```

---

## Suricata

Configuration test:

```bash
sudo suricata -T -c /etc/suricata/suricata.yaml
```

View alerts:

```bash
sudo tail -f /var/log/suricata/fast.log
```

View structured events:

```bash
sudo tail -f /var/log/suricata/eve.json
```

---

## Wireshark

Useful display filters:

```text
dns
```

```text
tls
```

```text
http
```

```text
tcp.flags.syn == 1 && tcp.flags.ack == 0
```

```text
ip.addr == 192.168.56.101
```

---

## Nmap

Basic scan:

```bash
nmap 192.168.56.101
```

Service detection:

```bash
nmap -sV 192.168.56.101
```

Selected ports:

```bash
nmap -p 22,53,80,443 192.168.56.101
```

---

# 🔍 Security Verification Checklist

Before demonstration, verify:

* [ ] DNS resolves correctly
* [ ] Reverse DNS works where configured
* [ ] Apache is running
* [ ] HTTPS is enabled
* [ ] HTTP redirects to HTTPS
* [ ] Certificate contains correct CN
* [ ] Certificate contains correct SAN
* [ ] Certificate issuer is correct
* [ ] Certificate chain validates
* [ ] TLS 1.2 works
* [ ] TLS 1.3 works
* [ ] Suricata is running
* [ ] Custom IDS rule is loaded
* [ ] Suricata logs are accessible
* [ ] Wireshark can capture traffic
* [ ] Nmap can identify exposed services

---

# 🔐 Security Considerations

The following information must **never** be committed to a public GitHub repository:

```text
Private keys
Passwords
CA passphrases
Database files containing sensitive information
User credentials
Session information
Generated private certificates
TLS private keys
Application secrets
```

The repository should contain configuration templates and source code rather than operational secrets.

---

# 🚫 Sensitive Files

Example `.gitignore` entries:

```gitignore
# Private keys
*.key
*.key.pem
private/
**/private/

# Generated certificates
*.cert.pem
*.crt
*.pem

# CSR files
*.csr
*.csr.pem

# Password / secret files
passphrase.txt
.env
.env.*

# Runtime data
*.db
*.sqlite
*.sqlite3

# Logs
*.log
logs/

# Application runtime storage
CA-Web-Application/storage/
```

> Adjust these rules if a particular certificate or public CA file is intentionally required for the academic demonstration.

---

# 📊 Security Model

The project demonstrates multiple layers of security.

```text
                 ┌───────────────────────┐
                 │       User            │
                 └───────────┬───────────┘
                             │
                             ▼
                 ┌───────────────────────┐
                 │   Web Application     │
                 │ Authentication        │
                 │ Authorization         │
                 └───────────┬───────────┘
                             │
                             ▼
                 ┌───────────────────────┐
                 │       HTTPS           │
                 │       TLS             │
                 └───────────┬───────────┘
                             │
                             ▼
                 ┌───────────────────────┐
                 │       Apache          │
                 └───────────┬───────────┘
                             │
              ┌──────────────┴──────────────┐
              ▼                             ▼
       ┌─────────────┐               ┌─────────────┐
       │    PKI      │               │  Suricata   │
       │ Certificates│               │     IDS     │
       └─────────────┘               └─────────────┘
              │                             │
              ▼                             ▼
       Certificate Trust              Threat Detection
```

---

# 🧠 Key Concepts Demonstrated

This project provides practical experience with:

### Public Key Infrastructure

Understanding:

```text
Root CA
Subordinate CA
CSR
Digital Certificate
Certificate Chain
Certificate Revocation
```

### Cryptography

Understanding:

```text
Private Key
Public Key
RSA
Digital Signature
Certificate
```

### Network Security

Understanding:

```text
TCP
DNS
HTTP
HTTPS
TLS
Network Scanning
Packet Capture
IDS
```

### Web Security

Understanding:

```text
Authentication
Authorization
Password Hashing
HTTPS
TLS
Certificate Validation
```

---

# ⚠️ Limitations

This project is primarily designed for an educational laboratory environment.

Therefore:

* It is not intended to replace a production CA.
* Production-grade HSM integration is not implemented.
* Production certificate lifecycle automation is limited.
* The network is isolated inside a virtualized environment.
* Security policies are simplified for educational demonstration.
* Some components are intentionally implemented for demonstration rather than large-scale deployment.

---

# 🚀 Future Improvements

Possible future improvements include:

* Hardware Security Module (HSM) integration
* Automated certificate renewal
* OCSP responder
* OCSP stapling
* Automated CRL distribution
* Certificate Transparency logging
* Certificate Policy OIDs
* Stronger authentication
* Login rate limiting
* Temporary account lockout
* SSH public-key authentication
* Automated security monitoring
* Centralized logging
* SIEM integration
* Browser-side private-key generation
* Improved certificate request workflow
* Automated PKI deployment

---

# 📚 Academic Demonstration Topics

The project can be demonstrated through the following sections:

## Part 1 — DNS

Demonstrate:

```text
Domain
↓
DNS
↓
Server IP
```

---

## Part 2 — PKI

Demonstrate:

```text
Private Key
↓
CSR
↓
CA
↓
Certificate
```

---

## Part 3 — Certificate

Explain:

```text
Subject
Issuer
CN
SAN
Validity
Serial Number
Public Key
Signature
```

---

## Part 4 — HTTPS

Demonstrate:

```text
HTTP
↓
301 Redirect
↓
HTTPS
↓
TLS
```

---

## Part 5 — Certificate Validation

Demonstrate:

```text
Certificate
↓
SubCA
↓
Root CA
↓
Verification
```

---

## Part 6 — IDS

Demonstrate:

```text
Network Traffic
↓
Suricata
↓
Rule
↓
Alert
↓
Log
```

---

## Part 7 — Packet Analysis

Demonstrate Wireshark:

```text
DNS
TCP
TLS
SYN
HTTP
```

---

## Part 8 — Network Reconnaissance

Demonstrate Nmap:

```text
Host
↓
Open Ports
↓
Services
↓
Security Analysis
```

# 🏁 Project Outcome

The completed project demonstrates an integrated cybersecurity infrastructure combining:

```text
                 ┌──────────────────┐
                 │   Web Application│
                 └────────┬─────────┘
                          │
                          ▼
                 ┌──────────────────┐
                 │      HTTPS       │
                 │       TLS        │
                 └────────┬─────────┘
                          │
            ┌─────────────┼─────────────┐
            │             │             │
            ▼             ▼             ▼
         DNS/BIND        PKI         Suricata
            │             │             │
            │             │             │
            └─────────────┼─────────────┘
                          │
                          ▼
                    Network Security
                          │
                ┌─────────┴─────────┐
                ▼                   ▼
            Wireshark              Nmap
```

The project therefore demonstrates the complete interaction between **identity, certificates, DNS, secure web communication, and network security monitoring** within a controlled cybersecurity laboratory.

---

# 👨‍💻 Authors

**Md. Ariful Islam**

GitHub:

`https://github.com/Mr-ARIFx`

Repository:

`https://github.com/Mr-ARIFx/Cyber-Security-Project`

---

# 📄 License

This project is developed for **academic and educational purposes**.

See the [`LICENSE`](LICENSE) file for the complete license terms.

---

# ⚠️ Disclaimer

This project is intended strictly for educational and authorized security testing.

All network scanning, traffic generation, IDS testing, packet capture, and security experiments should be performed only on systems and networks for which the user has explicit authorization.

Do not use the techniques demonstrated in this project against unauthorized systems.

---

<p align="center">

<b>🔐 Cyber Security Project — ACMECA PKI & Secure Web Infrastructure</b>

<br>

Built for academic cybersecurity demonstration and practical learning.

</p>
```

