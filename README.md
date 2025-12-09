# 📦 Zabbix Module – Fetch Host Configuration

A Zabbix frontend module that allows administrators to **view, extract, and export host configuration data** directly from the Zabbix Web UI.  
This module makes it easy to audit, document, back up, or compare host configurations without writing Zabbix API scripts.

---

## ✨ Features

✔ **Fetch configuration for any host**  
✔ **Export host configuration in multiple formats:**  
- **CSV** – ideal for spreadsheets, auditing, and comparisons  
- **HTML** – readable reports for documentation  
- **JSON** – perfect for automation, backups, and DevOps workflows  

✔ **Integrates directly into the Zabbix frontend**  
✔ **No external tools required**  
✔ **Lightweight and simple to deploy**

---

## 📋 What Information Can Be Exported?

The module retrieves key host configuration items, including:

- Host name & visible name  
- Status (enabled/disabled)  
- Host groups  
- Linked templates  
- Interfaces (Agent / SNMP / IPMI / JMX)  
- Host macros  
- Tags  
- Inventory data  
- Proxy assignment  
- Description  
- Other host metadata

This makes it useful for audits, compliance checks, debugging, and migration.

---

## 🚀 Installation

1. **Clone or download** this repository:
   ```bash
   git clone https://github.com/saumyajit/zabbix_module_fetch_host_configuration
