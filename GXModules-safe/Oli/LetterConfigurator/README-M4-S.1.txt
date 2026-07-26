LetterConfigurator M4-S.1 – Architekturgrundlage und Modulskelett
===================================================================

Ziel
---
M4-S.1 schafft die technische Grundlage für den produktiven Schnell-Konfigurator,
ohne bestehende Shop-, Geometrie- oder Preisfunktionen zu verändern.

Neu
---
- zentrale Modul-, Schema- und Konfigurationsversionen
- unveränderliche, Gambio-unabhängige Laufzeitkonfiguration
- minimale Logging-Schnittstelle für wiederverwendbare Services
- NullLogger als sichere Standardimplementierung
- vervollständigte Installation der bereits verwendeten Modultabellen
- additive Schema-Aktualisierung für ältere Modulinstallationen
- vollständigeres Entfernen eigener Tabellen bei der Deinstallation

Neue Dateien
------------
- Shop/Classes/Core/OliLetterConfiguratorModuleInfo.inc.php
- Shop/Classes/Core/Configuration/OliLetterConfiguratorModuleConfiguration.inc.php
- Shop/Classes/Core/Logging/OliLetterConfiguratorLoggerInterface.inc.php
- Shop/Classes/Core/Logging/OliLetterConfiguratorNullLogger.inc.php

Geänderte Datei
---------------
- Admin/Classes/OliLetterConfiguratorModuleCenterModule.inc.php

Architekturregeln
-----------------
- keine Änderungen an Gambio-Core-Dateien
- keine Geschäftslogik in Templates
- Core-Klassen bleiben unabhängig von Warenkorb, Bestellung und Datenbank
- Gambio-spezifische Anbindungen werden später über Adapter umgesetzt
- bestehende Geometry-Klassen bleiben unverändert

Bewusste Grenzen
----------------
- noch kein neuer Konfigurationszustand
- noch keine neue Produktzuordnung
- keine Änderung der Artikeldetailseite
- keine neue Preisberechnung
- keine Warenkorb- oder Bestelländerung
- kein konkreter Dateilogger; bis zur späteren Anbindung wird NullLogger verwendet

Review
------
Zu prüfen sind Installation, erneute Installation über einem älteren Schema,
Deinstallation und die PHP-Syntax aller neuen bzw. geänderten Dateien.
