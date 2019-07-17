# Symcon Kostal Modul
Modul um Kostal Wechselrichter abzufragen.
Unterstützte Modelle:
- Piko 5.5
- Piko 8.3
- Piko 12

Für die Modelle Piko 5.5 und 8.3 sind Teile aus [hermanthegerman2's Modul](https://github.com/hermanthegerman2/KostalPiko) benutzt worden.


### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)

### 1. Funktionsumfang

* Datenabfrage eines Wechselrichters im einstellbaren Intervall.

### 2. Voraussetzungen

- IP-Symcon ab Version 5.x
- Vielleicht auch frühere Versionen, leider kann ich das nicht testen.

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/emtek-at/Kostal-Symcon`  


### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Kostal'-Modul unter dem Hersteller 'Kostal' aufgeführt.

__Konfigurationsseite__:

Name       | Beschreibung
---------- | ---------------------------------
Model           | Das Wechselrichtermodel
Host/IP         | DNS oder IP Adresse des Wechselrichters
Benutzername    | Benutzername für das Webinterface
Passwort        | Passwort für das Webinterface
getStatus Intervall Sek. | Aktualisierungs Intervall



### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch je nach Model angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.
Es werden lediglich die Werte aus dem Webinterface abgerufen und konvertiert aber nicht auf Plausibilität geprüft.

Name               | Typ       | Beschreibung
------------------ | --------- | ----------------
Status              | String   | Status des Wechselrichters
AC Leistung Aktuell | Float    | Aktuelle Wechselstrom Leistung in Watt
Gesamtertrag        | Float    | Gesamtertrag seit Installation in kWh
Tagesertrag         | Float    | Tagesertrag in kWh
L1...L3 Leistung    | Float    | Je nach Model Leistung der Phase in Watt
L1...L3 Spannung    | Float    | Je nach Model Spannung der Phase in Volt
String 1...3 Spannung   | Float | Je nach Model String Spannung in Volt
String 1...3 Strom      | Float | Je nach Model String Strom in Ampere

Es werden keine zusätzlichen Profile benötigt.
