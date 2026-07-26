LetterConfigurator – fortlaufende Entwicklungsdokumentation

Diese Datei bündelt die bisherigen README-Dokumentationen des Moduls. Sie wird ab M4-S.5.5 bei jedem Ausbauschritt fortgeführt.


==============================================================================
README-M3.10.txt
==============================================================================

LetterConfigurator M3.10 – Modulare Preisgrundlagen

- Flächenpreis in EUR/m²
- Kalkulationsfläche ausschließlich aus Kundengesamtbreite × Kundengesamthöhe
- Maßeingabe in mm, Umrechnung in m²
- Konturpreis in EUR/mm
- Preis pro Zeichen
- Festbetrag
- Grundpreis, Mindestpreis, Einrichtkosten und Verschnitt
- Preisbausteine können gemeinsam in einem Profil verwendet werden
- tatsächliche SVG-Fläche ist nicht Bestandteil der Verkaufspreisberechnung
- bestehende Altprofile werden beim Bearbeiten kompatibel übernommen
- keine Datenbankmigration erforderlich


==============================================================================
README-M3.11.txt
==============================================================================

LetterConfigurator M3.11 – Fertigungsarten und Stärkenbereiche
===============================================================

Neu:
- Grundpreis aus Preisprofilen entfernt.
- Fertigungsart je Stärkenprofil: Konturgeschnitten oder 3D-Druck.
- Materialstärke als Bereich von/bis (0,1 bis 200 mm).
- Bestehende Einzelstärken werden automatisch als min=max übernommen.
- Überschneidungsprüfung berücksichtigt Fertigungsart, Stärkenbereich und Farben.
- Preisprofil-Auswahl zeigt Fertigungsart und Stärkenbereich.

Hinweis:
Die eigentliche 3D-Druck-Kalkulation (Volumen, Materialverbrauch, Maschinenzeit,
Rüstkosten usw.) folgt als separater Meilenstein. Konturpreise bleiben von der
3D-Druck-Kalkulation getrennt.


==============================================================================
README-M3.12.2.txt
==============================================================================

LetterConfigurator M3.12.2

- Tabellenreihenfolge bei Farben und Materialstärken angepasst.
- Preisaufschläge aus Farben und Materialstärken entfernt.
- Preisberechnung bleibt zentral in den Preisprofilen.
- Bestehende Datenbankspalten bleiben aus Kompatibilitätsgründen erhalten und werden beim Speichern auf 0 gesetzt.
- Keine Datenbankmigration erforderlich.


==============================================================================
README-M3.12.3.txt
==============================================================================

LetterConfigurator M3.12.3

- Tabellen Farben und Materialstärken beginnen mit Sortierung, danach Material.
- Preisprofile folgen dem einheitlichen Tabellenstandard und besitzen eine Sortierreihenfolge.
- Konturpreis kann direkt eingegeben oder aus Schnittgeschwindigkeit, Durchgängen und Maschinenkosten berechnet werden.
- Verwendete Formel: (Maschinenkosten / 3600) * Schnittgeschwindigkeit * Anzahl Durchgänge.
- Maschinendaten werden im Preisprofil gespeichert.
- Keine Datenbankmigration erforderlich; neue Werte liegen kompatibel in configuration_json.


==============================================================================
README-M3.12.4.txt
==============================================================================

LetterConfigurator M3.12.4
==============================

- Korrigiert die Position des eigenen Admin-Hauptmenüs.
- Der Buchstaben-Konfigurator verwendet jetzt Sortierwert 49.
- Damit steht er nach Artikel (Sortierwert 40) und vor Inhalte/Layout (Sortierwert 50).
- Keine Datenbankänderung.

Nach dem Überschreiben alle Gambio-Caches leeren und im Admin neu anmelden.


==============================================================================
README-M3.12.5.txt
==============================================================================

LetterConfigurator M3.12.5

- Preisprofil-Formular in eine stabile Zweispaltenstruktur aufgeteilt.
- Linke Seite: Grunddaten, Farben, Materialstärken und Status.
- Rechte Seite: sämtliche Preisbausteine.
- Konturpreis kompakt in die rechte Preisspalte integriert.
- Wahl zwischen direkter Eingabe und automatischer Berechnung.
- Bei automatischer Berechnung werden die Zusatzfelder eingeblendet.
- Der Konturpreis aktualisiert sich bei jeder Eingabe ohne zusätzlichen Button.
- Formel: (Maschinenkosten / 3600) * Schnittgeschwindigkeit * Anzahl Durchgänge.
- Responsive Darstellung: unter 1100 px werden die Bereiche untereinander angezeigt.


==============================================================================
README-M3.12.6.txt
==============================================================================

LetterConfigurator M3.12.6
============================

Änderungen:
- Erklärende Hilfetexte unter den Preisfeldern ergänzt.
- Einheitliche Feldbezeichnungen eingeführt:
  - Zeichenpreis (€/Zeichen)
  - Grundpreis (€)
  - Mindestpreis (€)
  - Rüstkosten (€)
  - Verschnitt (%)
- Keine Datenbankänderung erforderlich.

Installation:
Moduldateien überschreiben und anschließend alle Gambio-Caches leeren.


==============================================================================
README-M3.12.txt
==============================================================================

LetterConfigurator M3.12

- Eigener Hauptmenüpunkt mit Sortierung zwischen Artikel und Inhalte
- Fertigungsarten-Verwaltung mit Konturgeschnitten, 3D-Druck und Sonstige
- Konturgeschnitten: ein sichtbares Feld für Materialstärke
- 3D-Druck/Sonstige: Stärke von/bis
- Preisprofile sind einer Fertigungsart zugeordnet
- Materialstärken in Preisprofilen werden nach Material und Fertigungsart gefiltert
- Technischer Modulordner bleibt LetterConfigurator; sichtbare Umbenennung folgt mit M4


==============================================================================
README-M3.13.1.txt
==============================================================================

LetterConfigurator – M3.13.1
============================

Neu:
- Menüpunkt „Produkte“
- eigene Tabelle oli_lc_product_templates
- Produktvorlagen anlegen, bearbeiten, aktivieren/deaktivieren und löschen
- Liste: Sortierung | Produkt | Beschreibung | Status | Aktionen

Bewusste Abgrenzung:
- Noch keine Material-, Farb-, Stärken-, Fertigungsarten- oder Preisprofil-Zuordnung
- Keine Verknüpfung mit Gambio-Shopartikeln
- Keine Änderung an Gambio-Core-Tabellen

Installation:
1. Dateien in das Gambio-Hauptverzeichnis kopieren und überschreiben.
2. Alle Gambio-Caches leeren.
3. Aus dem Admin abmelden und wieder anmelden.
4. Buchstaben-Konfigurator > Produkte öffnen.

Die neue Tabelle wird beim ersten Öffnen der Produktverwaltung automatisch angelegt.


==============================================================================
README-M3.13.2.1.txt
==============================================================================

LetterConfigurator M3.13.2.1

Fixes:
- Mehr Abstand zwischen den Material-Auswahlkarten in Produktvorlagen.
- Veraltete Preisaufschlag-Validierung bei Farben entfernt.
- Veraltete Preisaufschlag-Validierung bei Materialstärken entfernt.
- Bestehende DB-Felder price_surcharge bleiben kompatibel und werden mit 0 gespeichert.


==============================================================================
README-M3.13.2.txt
==============================================================================

LetterConfigurator M3.13.2
==========================

Neu:
- Produktvorlagen können mehreren Materialien zugeordnet werden.
- Neue Tabelle oli_lc_product_template_materials.
- Materialzuordnungen werden beim Bearbeiten geladen und beim Speichern aktualisiert.
- Produktübersicht zeigt die zugeordneten Materialien.
- Formular ist als Kartenstruktur für weitere Ausbaustufen vorbereitet.
- Beim Löschen einer Produktvorlage werden ihre Materialzuordnungen entfernt.

Installation:
1. Dateien in das Gambio-Hauptverzeichnis kopieren und vorhandene Dateien überschreiben.
2. Alle Gambio-Caches leeren.
3. Admin neu laden und Produkte öffnen.
4. Produktvorlage anlegen oder bearbeiten und Materialien zuweisen.

Keine Gambio-Core-Tabellen werden verändert.


==============================================================================
README-M3.13.3.1.txt
==============================================================================

LetterConfigurator M3.13.3.1

- Alle sichtbaren Preisfelder mit 2 Nachkommastellen
- Konturpreis benutzerfreundlich in EUR pro Meter
- Interne Speicherung weiterhin in EUR pro Millimeter
- Preisbereiche: Materialkosten, Fertigungskosten, Zusatzkosten
- Alle Preise netto zzgl. USt.


==============================================================================
README-M3.13.3.2.txt
==============================================================================

LetterConfigurator M3.13.3.2

- Verschnitt im Preisprofil ohne Nachkommastellen
- Maschinenkosten im Preisprofil mit zwei Nachkommastellen
- Maschinenstunden in der Fertigungsarten-Tabelle mit zwei Nachkommastellen
- Nettohinweis in den Tabellenüberschriften für Maschinenstunde und Preisbausteine
- Wiederholte Nettohinweise in den Tabellenzeilen entfernt


==============================================================================
README-M3.13.3.3.txt
==============================================================================

M3.13.3.3 – Materialstärken-Darstellung und Konturpreis-Automatik

- Materialstärken werden ohne überflüssige Null-Nachkommastellen angezeigt.
- Tatsächliche Dezimalwerte bleiben sichtbar.
- Die automatische Konturpreisberechnung wird beim Aktivieren sofort ausgeführt.
- Maschinenkosten werden vor der Berechnung aus der Fertigungsart vorausgefüllt, sofern kein eigener Wert vorhanden ist.
- Änderungen an Fertigungsart, Maschinenkosten, Schnittgeschwindigkeit und Durchgängen aktualisieren den Konturpreis.
- Bei aktiver Automatik ist der Konturpreis schreibgeschützt.


==============================================================================
README-M3.13.3.txt
==============================================================================

LetterConfigurator M3.13.3

- Fertigungsarten vollständig anlegen, bearbeiten, aktivieren/deaktivieren und löschen
- Stärkeangabe: Einzelwert oder Von-bis-Bereich
- Preisengine: Kontur, 3D-Druck oder Allgemein
- Maschinenbezeichnung und Maschinenkosten pro Stunde
- Maschinenkosten werden im Preisprofil je Fertigungsart vorausgefüllt und bleiben überschreibbar
- Standardarten: CNC-Fräsen, Laserschneiden, Plotten, 3D-Druck, Sonstige
- Alle Preisangaben als netto zzgl. USt. gekennzeichnet
- Preisprofile speichern price_tax_mode=net_excluding_vat


==============================================================================
README-M3.14.1.txt
==============================================================================

LetterConfigurator M3.14.1
==========================

Neu:
- Produktvorlagen können mehreren aktiven Fertigungsarten zugeordnet werden.
- Neue Relationstabelle oli_lc_product_template_production_methods.
- Bereits gespeicherte Zuordnungen werden beim Bearbeiten markiert.
- Beim Speichern werden die Zuordnungen synchronisiert.
- Beim Löschen einer Produktvorlage werden die Zuordnungen entfernt.
- Produktübersicht mit zusätzlicher Spalte Fertigungsarten.

Installation:
1. Inhalt des ZIP-Archivs in das Gambio-Hauptverzeichnis kopieren.
2. Vorhandene Dateien überschreiben.
3. Gambio-Caches vollständig leeren.
4. Admin neu laden und Buchstaben-Konfigurator > Produkte öffnen.

Die neue Relationstabelle wird beim ersten Öffnen der Produktverwaltung automatisch angelegt.


==============================================================================
README-M3.14.2.1.txt
==============================================================================

LetterConfigurator M3.14.2.1

- Farbauswahl in Produktvorlagen konsequent nach aktuell gewählten Materialien gefiltert
- Nicht passende Farbkarten werden vollständig ausgeblendet
- Nicht sichtbare Farb-Checkboxen werden deaktiviert und abgewählt
- Filter aktualisiert sich sofort bei Änderung der Materialauswahl


==============================================================================
README-M3.14.2.txt
==============================================================================

LetterConfigurator M3.14.2

- Produktvorlagen: Farbmodus Alle oder Auswahl
- Farben werden nach zugewiesenen Materialien gefiltert
- Neue Relationstabelle oli_lc_product_template_colors
- Übersicht zeigt erlaubte Farben
- Bestehende Produktvorlagen verwenden standardmäßig alle Farben


==============================================================================
README-M3.14.3.txt
==============================================================================

LetterConfigurator M3.14.3

- Materialstärken pro Produktvorlage: alle passenden oder gezielt ausgewählte.
- Filterung nach ausgewählten Materialien und Fertigungsarten.
- Serverseitige Absicherung der gültigen Zuordnungen.
- Neue Relationstabelle oli_lc_product_template_thicknesses.
- Übersichtsspalte Materialstärken.
- Mehr Abstand zwischen Farbfeld und Farbname.


==============================================================================
README-M3.14.4.txt
==============================================================================

LetterConfigurator M3.14.4 – Preisprofil-Zuordnung

- Genau ein Preisprofil pro Produktvorlage
- Auswahl wird nach gewählten Materialien und Fertigungsarten gefiltert
- Nur aktive, passende Preisprofile stehen zur Verfügung
- Serverseitige Validierung verhindert unzulässige Zuordnungen
- Preisprofil wird in der Produktübersicht angezeigt
- Neue optionale Spalte oli_lc_product_templates.price_profile_id wird automatisch angelegt

Installation: Dateien überschreiben, Gambio-Caches leeren, Produkte öffnen und testen.


==============================================================================
README-M3.14.5.1.txt
==============================================================================

M3.14.5.1 – Admin-Layout-Fix

- Konfigurationen-Seite in das reguläre Gambio-Adminlayout eingebunden
- Modul-CSS und JavaScript mit korrekten relativen Pfaden geladen
- Vollbreite Darstellung und einheitlicher Seitenkopf ergänzt
- Keine Datenbank- oder Funktionsänderungen


==============================================================================
README-M3.14.5.txt
==============================================================================

LetterConfigurator M3.14.5 – Gambio-Artikelzuordnung

Neu:
- Menüpunkt Konfigurationen
- Gambio-Artikel mit genau einer Produktvorlage verknüpfen
- Zuordnungen anlegen, bearbeiten, aktivieren/deaktivieren und löschen
- Eigene Tabelle oli_lc_product_assignments
- Keine Änderung an Gambio-Coretabellen
- Schutz vor Löschen verwendeter Produktvorlagen

Installation:
1. ZIP im Gambio-Hauptverzeichnis entpacken.
2. Dateien überschreiben.
3. Alle Gambio-Caches leeren.
4. Admin neu anmelden.
5. Buchstaben-Konfigurator > Konfigurationen öffnen.


==============================================================================
README-M3.4.txt
==============================================================================

LETTERCONFIGURATOR M3.4 – FULL-WIDTH-ADMIN-LAYOUT
================================================

Basis: erfolgreich getestetes M3.3.

Änderungen:
- Materialtabelle belegt eine eigene Zeile über die volle verfügbare Adminbreite.
- Materialformular belegt darunter ebenfalls eine eigene Vollbreiten-Zeile.
- Die bisherige 2:1-Grid-Aufteilung wurde vollständig entfernt.
- Container, Grid, Karten und Tabellen besitzen keine eigene Maximalbreite.
- Bestehende Klasse lc-grid--split wird aus Kompatibilitätsgründen ebenfalls einspaltig dargestellt.
- Cache-Buster und Meilensteinanzeige auf M3.4 aktualisiert.

Unverändert:
- Datenbankschema
- Material-CRUD
- Module-Center-Installation
- Admin-Menü
- Frontend (weiterhin nicht enthalten)

Installation:
1. M3.4 über M3.3 entpacken und Dateien überschreiben.
2. Alle Gambio-Caches leeren.
3. Admin neu laden; bei Bedarf ab- und wieder anmelden.
4. Buchstaben-Konfigurator > Materialien öffnen.

Test:
- Tabelle nutzt die vollständige Inhaltsbreite.
- Formular erscheint unterhalb der Tabelle und ebenfalls vollbreit.
- Suche, Sortierung, Speichern und Aktivieren/Deaktivieren funktionieren weiterhin.


==============================================================================
README-M3.5.1.txt
==============================================================================

LetterConfigurator M3.5.1 - Full Width Dashboard Fix
====================================================

Aenderungen gegenueber M3.5:
- Gambio-Klasse breakpoint-small entfernt (diese begrenzt Inhalte auf 675 px)
- Dashboard und Materialverwaltung nutzen die volle Content-Breite
- Statistik-Karten verteilen sich ueber die komplette Zeile
- Hauptbereich/Schnellzugriff im Verhaeltnis 3:1
- Responsive Umbrueche bleiben erhalten

Keine Datenbankaenderungen.
Keine Neuinstallation erforderlich.
Nach dem Ueberschreiben alle Gambio-Caches leeren.


==============================================================================
README-M3.6.1.txt
==============================================================================

LetterConfigurator M3.6.1 – Farbwert-Validierungsfix

- Behebt die fehlerhafte HTML5-Pattern-Ausgabe im Smarty-Template.
- Farbwerte im Format #RRGGBB können wieder geändert und gespeichert werden.
- Keine Datenbankänderungen.


==============================================================================
README-M3.6.2.txt
==============================================================================

LetterConfigurator M3.6.2

Fix:
- Sichtbare Farbvorschau in der Farbtabelle
- Feste Vorschaugroesse mit Rahmen, damit auch Weiss erkennbar ist
- Hexwert wird direkt neben der Vorschau angezeigt
- Keine Datenbankaenderung


==============================================================================
README-M3.6.txt
==============================================================================

LetterConfigurator M3.6 – Farbverwaltung

Neu:
- Admin-Menüpunkt Farben
- Farben je Material anzeigen, anlegen und bearbeiten
- Farbwert als #RRGGBB mit Farbauswahl
- optionaler Preisaufschlag
- Sortierung und Aktivstatus
- Suche und clientseitige Sortierung
- Dashboard-Verknüpfung

Keine Datenbankänderung und keine Neuinstallation erforderlich.
Basis: bestätigte M3.5.1-Version.


==============================================================================
README-M3.7.1.txt
==============================================================================

LetterConfigurator – Meilenstein M3.7.1
======================================

Neu:
- Mehrfachauswahl von Farben je Materialstärken-Preisgruppe
- Option „Für alle Farben“
- neue Zuordnungstabelle oli_lc_thickness_colors
- automatische Übernahme bestehender Einzelzuordnungen
- Überschneidungsprüfung beim Speichern und Aktivieren
- Anzeige aller zugeordneten Farben in der Übersicht

Installation:
1. ZIP im Gambio-Hauptverzeichnis entpacken und bestehende Dateien überschreiben.
2. Alle Gambio-Caches leeren.
3. Buchstaben-Konfigurator > Materialstärken öffnen.

Die Zuordnungstabelle wird beim ersten Öffnen der Materialstärken-Seite automatisch erstellt.
Eine Neuinstallation des Moduls ist nicht erforderlich.


==============================================================================
README-M3.7.2.txt
==============================================================================

LetterConfigurator M3.7.2 – Materialbezogene Farbauswahl und Abstände

Änderungen:
- In der Materialstärkenverwaltung werden ausschließlich Farben des aktuell gewählten Materials angezeigt.
- Verdeckte Farbkarten werden trotz Flex-Layout zuverlässig ausgeblendet.
- Mehr Abstand zwischen Farbfeld und Farbname.
- Mehr Abstand zwischen Farbname und technischem Code.
- Keine Datenbankänderungen.


==============================================================================
README-M3.7.txt
==============================================================================

Oli LetterConfigurator – M3.7 Materialstärken

Basis: M3.6.2

Neu:
- Verwaltung von Materialstärken
- Zuordnung zu Material und optional Farbe
- Preisaufschlag, Sortierung, Aktiv/Inaktiv
- Dashboard und Admin-Menü erweitert
- Farbtabelle zeigt nur noch die Farbvorschau; der Hexwert bleibt beim Namen

Keine Datenbankmigration erforderlich. Die Tabelle oli_lc_thicknesses besteht seit M2.2.


==============================================================================
README-M3.8.txt
==============================================================================

LetterConfigurator M3.8 – Preisprofile

Neu:
- Preisprofile anlegen, bearbeiten, aktivieren und deaktivieren
- Zuordnung zu Material sowie optional mehreren Farben und Materialstärken
- Berechnungsarten: cm², Zeichen, cm, Festpreis, kombiniert
- Grundpreis, Einheitspreis, Mindestpreis, Einrichtkosten und Verschnitt
- Speicherung im vorhandenen configuration_json; keine Schemaänderung
- Dashboard und Adminmenü erweitert

Installation:
ZIP im Shop-Hauptverzeichnis entpacken, bestehende Dateien überschreiben und Gambio-Caches leeren.
Keine Neuinstallation erforderlich.


==============================================================================
README-M4-S.1.txt
==============================================================================

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


==============================================================================
README-M4-S.2.txt
==============================================================================

M4-S.2 – Kompaktes Konfigurationsmodell
========================================

Ziel
----
Ein gemeinsames, versioniertes Datenmodell für den produktiven Schnell-Konfigurator.
Es ist unabhängig von Gambio, Benutzeroberfläche, Geometrie- und Preisservice.

Neue Klassen
------------
Shop/Classes/Core/Configuration/OliLetterConfiguratorState.inc.php
Shop/Classes/Core/Configuration/OliLetterConfiguratorValidationResult.inc.php

Enthaltene Daten
----------------
- Schema-Version und Konfigurator-Typ
- Gambio-Produkt-ID
- Text, Textmodus, Ausrichtung und Zeilenabstand
- Schrift-ID und lesbarer Snapshot-Name
- Material, Materialstärke und Farbe als IDs plus Snapshot-Namen
- Eingabemodus Höhe oder maximale Breite
- angeforderter Wert in Millimetern
- Geometrie-Snapshot
- Preis-Snapshot

Funktionen
----------
- Erzeugung eines leeren Zustands
- Erzeugung aus Array oder JSON
- unveränderliche Änderungen über with()
- Normalisierung von Datentypen und Zeilenumbrüchen
- Grundvalidierung mit strukturierten Fehler- und Warncodes
- JSON-Serialisierung
- stabiler SHA-256-Konfigurationshash

Bewusste Abgrenzung
-------------------
- keine Datenbanktabellen
- keine Gambio-Integration
- keine UI-Anbindung
- keine Geometrie- oder Preisberechnung
- keine Logo-, SVG-Upload- oder Mehrproduktmodelle

Version
-------
Modulversion: 4-S.2.0
Schema-Version: 4-S.2
Konfigurationsschema: 1


==============================================================================
README-M4-S.3.1.txt
==============================================================================

M4-S.3.1 – Hotfix bestehende Artikelzuordnungen

- ergänzt configurator_type additiv bei bestehenden Tabellen
- verhindert SQL-Fehler beim Öffnen vorhandener Zuordnungen
- Shop-Repository bleibt auch vor der Schema-Ergänzung kompatibel
- keine Änderungen an bestehenden Zuordnungsdaten


==============================================================================
README-M4-S.3.txt
==============================================================================

M4-S.3 – Produktzuordnung
=========================

Ziel
----
Vorhandene Gambio-Artikelzuordnungen werden zur verbindlichen Aktivierungsbasis
des Schnell-Konfigurators. Normale Artikel bleiben unverändert.

Umgesetzt
---------
- Unveränderliches ProductAssignment-Objekt.
- Zentrales Repository zum Auflösen aktiver Artikelzuordnungen.
- Prüfung, ob ein Artikel ein aktives Konfigurator-Produkt ist.
- Konfigurator-Typ "contour_text" in der modulinternen Zuordnung.
- Additive Schema-Aktualisierung bestehender Installationen.
- Modulversion 4-S.3.0 / Schema-Version 4-S.3.

Wiederverwendung
----------------
Die bereits vorhandene Adminverwaltung für Artikelzuordnungen und die Tabelle
oli_lc_product_assignments bleiben erhalten. Es entsteht keine zweite
Artikelzuordnung und keine Änderung an Gambio-Coretabellen.

Nicht Bestandteil
------------------
- Keine neue Artikeldetailseite.
- Keine sichtbaren Eingabefelder.
- Keine Änderung an Geometrie, Preis, Warenkorb oder Bestellung.


==============================================================================
README-M4-S.4.1.txt
==============================================================================

M4-S.4.1 – Farbabhängige Materialstärken

Korrektur:
- Materialstärken werden im Frontend zusätzlich nach der gewählten Farbe gefiltert.
- Stärken ohne explizite Farbzuordnung gelten weiterhin für alle Farben des Materials.
- Ungültige Kombinationen aus Farbe und Stärke werden serverseitig beim Warenkorbaufbau abgewiesen.
- Bestehende Material-, Verfahrens- und Produktvorlagenfilter bleiben unverändert.


==============================================================================
README-M4-S.4.2.txt
==============================================================================

M4-S.4.2 – Korrektur bestehender Farb-/Stärkenzuordnungen

- Berücksichtigt neben oli_lc_thickness_colors auch das ältere Feld oli_lc_thicknesses.color_id.
- Eine fehlende Relation wird nur dann als „alle Farben“ behandelt, wenn auch kein älterer Farbwert gesetzt ist.
- Die gleiche Prüfung erfolgt serverseitig beim Warenkorbaufbau.
- Bestehende Daten müssen nicht migriert oder neu gespeichert werden.


==============================================================================
README-M4-S.4.3.txt
==============================================================================

M4-S.4.3 – Korrektur der Auswahlreihenfolge

Die abhängigen Stammdaten werden jetzt in der fachlich korrekten Reihenfolge geladen:

Material -> Fertigungsart -> Materialstärke -> Farbe

Nach Auswahl einer Materialstärke werden nur die Farben angeboten, die dieser Stärke zugeordnet sind.
Stärken ohne Farbbeschränkung erlauben weiterhin alle aktiven Farben des gewählten Materials.
Die serverseitige Prüfung aus M4-S.4.2 bleibt unverändert aktiv.


==============================================================================
README-M4-S.4.txt
==============================================================================

M4-S.4 – Stammdaten-Adapter
============================

Ziel
----
Die bereits vorhandenen Stammdaten für Materialien, Produktionsverfahren,
Materialstärken, materialabhängige Farben und Schriften werden über eine
gemeinsame, nur lesende Repository-Schnittstelle bereitgestellt.

Umsetzung
---------
- OliLetterConfiguratorCatalogRepositoryInterface definiert den gemeinsamen Zugriff.
- OliLetterConfiguratorCatalogRepository verwendet die vorhandenen Tabellen und
  Produktvorlagen-Zuordnungen; es entsteht keine zweite Datenhaltung.
- Nur aktive und der Produktvorlage zugeordnete Datensätze werden geliefert.
- Farben bleiben materialabhängig.
- Ausgewählte Farben und Stärken einer Produktvorlage werden berücksichtigt.
- Nur aktive Schriften mit bestätigter Lizenz werden bereitgestellt.
- Die Artikeldetailseiten-Integration verwendet das Repository anstelle eigener SQL-Blöcke.

Nicht Bestandteil
-----------------
- neue Verwaltungsoberflächen
- Änderungen an Stammdaten
- sichtbare Änderungen im Shop
- Schrift-Auswahlfeld im Frontend (folgt in einem nächsten Schritt)

Version
-------
Modulversion: 4-S.4.0
Datenbankschema: unverändert 4-S.3


==============================================================================
README-M4-S.5.1.txt
==============================================================================

M4-S.5.1 – Hotfix globale Artikeldetailseite

Ursache:
Die Moduldatei Shop/Themes/All/product_info_template_standard.html überschrieb die aktuelle Gambio-Standardvorlage für alle Artikel. In der eingesetzten GX5-Version führte dies beim Rendern des Herstellers zu: Call to undefined method Manufacturer::getAddress().

Korrektur:
- Globalen Override product_info_template_standard.html entfernt.
- Normale Artikel verwenden wieder unverändert die aktuelle Gambio-Core-/Theme-Vorlage.
- Konfigurator-Produkte verwenden weiterhin product_info_template_letter_configurator.html und das Partial letter_configurator.html.
- Modulversion auf 4-S.5.1 erhöht.

Hinweis:
Beim Kopieren per FTP muss die alte Datei auf dem Server ausdrücklich gelöscht werden:
GXModules/Oli/LetterConfigurator/Shop/Themes/All/product_info_template_standard.html
Ein reines Überschreiben des ZIP-Inhalts löscht sie nicht automatisch.


==============================================================================
README-M4-S.5.2.txt
==============================================================================

M4-S.5.2 – Liveansicht im Produktbildbereich

Änderungen:
- Die Liveansicht ersetzt bei Konfigurator-Artikeln vollständig das Artikelbild.
- Die doppelte Vorschau innerhalb der rechten Konfiguratorspalte wurde entfernt.
- Linke Spalte: große, sticky Liveansicht mit Maß- und Materialzusammenfassung.
- Rechte Spalte: Produktinformationen, Konfiguratorfelder, Preis und Warenkorb.
- Normale Artikel bleiben unverändert.
- Responsive Darstellung für Tablet und Smartphone.

Noch nicht enthalten:
- echte SVG-Textdarstellung
- dynamische Maßberechnung
- Live-Aktualisierung der Zusammenfassung


==============================================================================
README-M4-S.5.3.txt
==============================================================================

M4-S.5.3 – Ersteindruck und Textausrichtung

Umgesetzt:
- Zweizeiliger Starttext „Dein / 3D-Text“ im Texteingabefeld.
- Derselbe Starttext erscheint unmittelbar in der großen Liveansicht.
- Beim ersten Fokus wird der Beispieltext vollständig markiert und kann direkt überschrieben werden.
- Grafische Auswahl für linksbündige, zentrierte und rechtsbündige Textausrichtung.
- Zentrierte Ausrichtung ist voreingestellt.
- Text und Ausrichtung aktualisieren die vorbereitete Liveansicht unmittelbar.
- Gewählte Ausrichtung wird als Formularwert oli_lc_text_alignment übertragen.
- Vorschaufläche wurde mit dezenter Grundplatte und Schatten aufgewertet.

Noch nicht Bestandteil:
- Umwandlung der Schrift in echte SVG-Pfade.
- Maßstäbliche Geometrie- und Größenberechnung.


==============================================================================
README-M4-S.5.4.txt
==============================================================================

M4-S.5.4 – Vorschaugröße und mobile Texteingabe

Änderungen:
- Darstellung des Beispieltexts in der Liveansicht verkleinert.
- Der vorausgefüllte Mustertext wird bei der ersten Berührung bzw. beim ersten Fokus sofort aus dem Textfeld entfernt.
- Kein Doppelklick und kein manuelles Markieren mehr erforderlich.
- Texteingaben werden zusätzlich über beforeinput, compositionend, keyup, change und paste verarbeitet.
- Aktualisierung der Liveansicht erfolgt über requestAnimationFrame und ist damit robuster in iPhone/iOS Safari.
- Bestehende Ausrichtungssteuerung bleibt unverändert.

Test:
1. Textfeld einmal anklicken oder auf dem iPhone antippen.
2. Mustertext muss sofort aus dem Eingabefeld verschwinden.
3. Text eingeben; die Liveansicht muss während der Eingabe reagieren.
4. Eingabe per Einfügen und mit iPhone-Tastatur prüfen.
5. Vorschaugröße auf Desktop und Smartphone kontrollieren.


==============================================================================
README-M4-S.5.txt
==============================================================================

M4-S.5 – Grundlayout der Schnell-Konfigurator-Artikeldetailseite

Umgesetzt:
- responsive Konfigurator-Arbeitsfläche mit Eingabe- und Vorschaubereich
- gegliederte Schritte für Text/Schrift, Material/Ausführung und Größe
- vorhandene Materialabhängigkeiten bleiben unverändert nutzbar
- aktive Schriften werden als Auswahl vorbereitet
- mehrzeilige Texteingabe vorbereitet
- SVG-Livevorschau als klar abgegrenzter Platzhalter vorbereitet
- vorhandene SVG-Datei-Upload-Oberfläche aus dem Schnell-Konfigurator entfernt
- bestehende Preis- und Warenkorblogik technisch unverändert belassen

Nicht Bestandteil dieses Schritts:
- Schrift-/Text-Geometrie
- Umschaltung Höhe oder maximale Gesamtbreite
- echte SVG-Livevorschau
- neue Preisberechnung


==============================================================================
README-M4.1.1.txt
==============================================================================

LetterConfigurator M4.1.1

Fix:
- CSS block in the frontend Smarty template is wrapped with {literal}.
- Prevents Smarty from parsing CSS braces as template syntax.
- No database or functional changes.


==============================================================================
README-M4.1.2.txt
==============================================================================

LetterConfigurator M4.1.2
==========================

Änderung
--------
Der Frontend-Konfigurator wird jetzt innerhalb des Formulars der rechten
Gambio-Kaufbox ausgegeben. Er nutzt die Gambio-Klasse "form-control" und
bleibt vollständig innerhalb der Breite der Kaufbox.

Unverändert
-----------
- Datenquelle und Artikelzuordnung
- Auswahlwerte der Produktvorlage
- keine Preisberechnung
- keine Warenkorb-Übergabe
- keine Datenbankänderung

Installation
------------
Den Ordner GXModules aus diesem Paket in das Gambio-Hauptverzeichnis kopieren
und vorhandene Dateien überschreiben. Danach alle Gambio- und Theme-Caches
leeren.


==============================================================================
README-M4.1.txt
==============================================================================

LetterConfigurator M4.1

- Admin-Menüpunkt "Konfigurationen" in "Artikelzuordnungen" umbenannt.
- Frontend-Testansicht auf aktiv zugeordneten Gambio-Artikeln.
- Lädt erlaubte Materialien, Fertigungsarten, Farben und Materialstärken aus der Produktvorlage.
- Noch keine dynamischen Abhängigkeiten, Preisberechnung oder Warenkorb-Übergabe.
- Keine Änderungen an Gambio-Coretabellen.


==============================================================================
README-M4.2.txt
==============================================================================

LetterConfigurator M4.2 – Dynamische Abhängigkeiten

- Material und Fertigungsart filtern sich über vorhandene Materialstärken-Kombinationen gegenseitig.
- Farben werden nach dem gewählten Material gefiltert.
- Materialstärken werden nach Material und Fertigungsart gefiltert.
- Nicht verfügbare bisherige Auswahlen werden automatisch zurückgesetzt.
- Farbe bleibt bis zur Materialauswahl deaktiviert.
- Materialstärke bleibt bis zur Auswahl von Material und Fertigungsart deaktiviert.
- Noch keine Preisberechnung und keine Warenkorb-Übergabe.


==============================================================================
README-M4.3.1.txt
==============================================================================

LetterConfigurator M4.3.1

Korrekturen:
- Automatische Konturpreisberechnung je Meter korrigiert.
- Formel: ((Maschinenkosten / 3600) / Schnittgeschwindigkeit * Anzahl Durchgaenge) * 1000.
- Maschinenkosten werden im Preisprofil mit genau zwei Dezimalstellen angezeigt und vorausgefuellt.
- Keine Datenbankaenderungen.


==============================================================================
README-M4.3.txt
==============================================================================

LetterConfigurator M4.3 – Live-Preisberechnung

- Preisprofil der zugeordneten Produktvorlage wird im Frontend geladen.
- Materialpreis: Kundenbreite × Kundenhöhe × €/m² inklusive Verschnitt.
- Konturpreis: Rechteckumfang × internem €/mm-Preis.
- Zeichenpreis zählt Zeichen ohne Leerzeichen.
- Grundpreis, Rüstkosten und Mindestpreis werden berücksichtigt.
- Anzeige von Netto- und Bruttopreis mit dem Steuersatz des Gambio-Artikels.
- Berechnung erfolgt nur für die Material-/Fertigungsart-Kombination des Preisprofils.
- Noch keine Speicherung oder Warenkorb-Übergabe.


==============================================================================
README-M4.4.1.txt
==============================================================================

M4.4.1 – Warenkorbsumme und einheitliche Bruttopreisanzeige

- Warenkorb wird nach dem Anhängen der Konfiguration erneut berechnet.
- Zwischensumme, Gesamtsumme und Steuer verwenden dadurch den Konfigurationspreis.
- Auf der Produktseite wird der Bruttopreis als Hauptpreis angezeigt.
- Der Nettopreis bleibt ergänzend darunter sichtbar.
- Keine Datenbankänderung.


==============================================================================
README-M4.4.2.txt
==============================================================================

LetterConfigurator M4.4.2

- Korrigiert die Warenkorb-Zusammenfassung auf der rechten Seite.
- show_total() summiert die bereits serverseitig validierten Konfigurationspreise.
- Positionspreis und rechte Zwischensumme verwenden damit dieselbe Preisquelle.
- Keine Datenbankänderungen.


==============================================================================
README-M4.4.txt
==============================================================================

M4.4 – Warenkorb-Übergabe

- Konfiguration wird beim Absenden serverseitig validiert.
- Preis wird serverseitig aus dem aktiven Preisprofil neu berechnet.
- Konfigurationspreis ersetzt den Artikelpreis im Warenkorb.
- Text, Maße, Material, Fertigungsart, Farbe und Stärke werden im Warenkorb angezeigt.
- Keine Core-Dateien und keine Core-Tabellen geändert.

Testhinweis: In diesem kleinen Meilenstein kann pro identischer Artikel-/Attributkombination eine Konfiguration im Warenkorb liegen. Mehrere unterschiedliche Konfigurationen desselben Artikels werden in einem späteren Schritt getrennt behandelt.


==============================================================================
README-M4.5.1.txt
==============================================================================

LetterConfigurator M4.5.1 – Warenkorb-Button wiederhergestellt
Gambio 26.07.0

Korrektur:
- Es wird nur noch der Gambio-Block für die eigentliche Preisausgabe ersetzt.
- Der übergeordnete price-calc-container bleibt vollständig erhalten.
- Mengenfeld, Mengeneinheit und „In den Warenkorb“-Button werden wieder nativ von Gambio ausgegeben.
- Die dynamische Anzeige „Preis nach Konfiguration“ / Bruttopreis / Nettopreis bleibt bestehen.
- Keine Datenbankänderungen.

Installation:
1. Inhalt des ZIP-Archivs in das Gambio-Hauptverzeichnis kopieren und vorhandene Dateien überschreiben.
2. Modul-, Seiten- und Theme-Caches vollständig leeren.
3. Artikelseite neu laden und vollständig konfigurieren.


==============================================================================
README-M4.5.2.txt
==============================================================================

LetterConfigurator M4.5.2

- Preisaufschlüsselung unterhalb des Frontend-Konfigurators ausgeblendet.
- Der konfigurierte Hauptpreis an der regulären Gambio-Preisposition bleibt sichtbar.
- Warenkorb-Gesamtsumme wird aus den tatsächlich angezeigten Positionspreisen und Mengen neu gebildet.
- Rechte Warenkorb-Zusammenfassung erhält denselben Wert für Zwischen- und Gesamtsumme.
- Keine Datenbankänderungen.


==============================================================================
README-M4.5.3.txt
==============================================================================

LetterConfigurator M4.5.3

Fix:
- Der berechnete Bruttopreis verwendet keine nativen Gambio-Preisklassen mehr.
- Gambio kann den Konfiguratorpreis deshalb nicht mehr nachträglich mit 0,00 EUR überschreiben.
- Netto- und Bruttopreis bleiben nach der Live-Berechnung stabil sichtbar.
- Keine Datenbankänderungen.


==============================================================================
README-M4.5.4.txt
==============================================================================

M4.5.4 – Nettoanzeige entfernt und Gambio-Summenlogik wiederhergestellt

- Artikeldetailseite zeigt nur noch den Bruttopreis.
- Eigene show_total()-Überschreibung entfernt.
- Keine Ersetzung von subtotal/total in der Warenkorb-View mehr.
- Der konfigurierte Warenwert wird weiterhin in shoppingCart::calculate() korrigiert.
- Versandkosten, Steuern, Gutscheine und weitere Gambio-Order-Total-Module bleiben Bestandteil der Gesamtsumme.


==============================================================================
README-M4.5.5.txt
==============================================================================

LetterConfigurator M4.5.5 – Warenwert-Berechnung

- Konfigurierte Artikel werden direkt in shoppingCart::calculate() mit dem serverseitig validierten Konfiguratorpreis berechnet.
- Nicht konfigurierte Artikel verwenden weiterhin unverändert die native Gambio-Preisberechnung.
- Warenwert, Steuerbasis und Versand-/Order-Total-Module bleiben getrennt und kompatibel.
- Keine Core- und Datenbankänderungen.


==============================================================================
README-M4.5.6.txt
==============================================================================

M4.5.6 – Warenwert der Gambio-Summenbox

- show_total() summiert die finalen Positionspreise aus get_products().
- Konfiguratorpreise werden dadurch im Warenwert berücksichtigt.
- Versandkosten, Steuern, Rabatte und Gesamtsumme bleiben bei den nativen Gambio Order-Total-Modulen.
- Keine Datenbankänderung.


==============================================================================
README-M4.5.7.txt
==============================================================================

M4.5.7 – Gambio Order-Subtotal Fix

- Behebt den Warenwert in der rechten Gambio-Summenbox.
- Die Gambio-Klasse order berechnet konfigurierbare Artikel nicht mehr erneut aus dem Stammpreis 0,00 EUR.
- Stattdessen wird der bereits serverseitig validierte Konfiguratorpreis aus dem Warenkorb übernommen.
- Warenwert, Steuerbasis und Ausgangswert der Gesamtsumme werden neu aufgebaut.
- Versandkosten und nachfolgende Order-Total-Module bleiben erhalten.
- Bereits gespeicherte Bestellungen werden nicht nachträglich verändert.


==============================================================================
README-M4.5.txt
==============================================================================

M4.5 – Konfiguratorpreis als regulärer Artikelpreis

- Bei konfigurierbaren Artikeln wird der normale Gambio-Preisbereich ersetzt.
- Vor vollständiger Auswahl erscheint "Preis nach Konfiguration".
- Nach gültiger Konfiguration steht dort der berechnete Bruttopreis.
- Der Nettopreis wird darunter ergänzend angezeigt.
- Nicht konfigurierbare Artikel verwenden unverändert den normalen Gambio-Preisblock.


==============================================================================
README-M4.6.1.txt
==============================================================================

LetterConfigurator M4.6.1 – Preisblock-Feintuning

- „Preis nach Konfiguration“ und der dynamische Bruttopreis sind in der Kaufbox rechtsbündig.
- Der native Gambio-Hinweis zu Umsatzsteuer und Versand wird wieder direkt unter dem Preis ausgegeben.
- Preisberechnung, Validierung und Warenkorb-Logik aus M4.6 bleiben unverändert.
- Keine Datenbankänderungen erforderlich.


==============================================================================
README-M4.6.txt
==============================================================================

LetterConfigurator M4.6 – Stabilisierung des Frontend-Konfigurators

Neu:
- Verbindliche Live-Validierung aller Pflichtfelder
- Deutliche Fehlermarkierungen direkt am jeweiligen Feld
- Warenkorb-Button bleibt deaktiviert, bis die Konfiguration vollständig gültig ist
- Eindeutige Einzeloptionen werden automatisch ausgewählt
- Text: 1 bis 255 Zeichen
- Breite und Höhe: jeweils 1 bis 100.000 mm
- Identische Grenzprüfung zusätzlich serverseitig
- Serverseitige Warenkorbfehler werden auf der Artikelseite angezeigt

Unverändert aus M4.5.7:
- Preisberechnung
- Konfiguratorpreis im Warenkorb
- Warenwert, Versand, Steuer und Gesamtsumme

Keine Datenbankänderung.


==============================================================================
README-M5.1.txt
==============================================================================

LetterConfigurator M5.1 – SVG-Grundlage
========================================

Neu:
- optionales SVG-Uploadfeld auf der Artikeldetailseite
- lokale Vorschau direkt im Browser
- Prüfung auf Dateiendung, MIME-Typ und maximal 2 MB
- XML-/SVG-Parsing vor der Vorschau
- Entfernung aktiver oder externer Inhalte aus der Vorschau
- keine Änderung an Preis-, Warenkorb- oder Summenlogik

Bewusste Grenze dieses Meilensteins:
Die SVG-Datei dient nur der Vorschau. Sie wird noch nicht auf den Server hochgeladen,
nicht im Warenkorb gespeichert und noch nicht für Kontur- oder Flächenberechnungen verwendet.


==============================================================================
README-M5.2.txt
==============================================================================

LetterConfigurator M5.2 – SVG-Analyse
=====================================

Neu:
- Analysebox unter der lokalen SVG-Vorschau
- Dateigröße, Breite, Höhe und ViewBox
- Anzahl von Pfaden, Gruppen und geometrischen Grundelementen
- Erkennung von Transformationen
- offene und geschlossene Konturen
- gesamte Konturlänge
- angenäherte Fläche geschlossener Konturen
- Bounding Box
- Warnungen bei fehlender ViewBox, offenen Konturen, negativen Koordinaten und nicht direkt auswertbaren Elementen

Wichtig:
- Die Analyse läuft in diesem Meilenstein ausschließlich lokal im Browser.
- Die SVG wird noch nicht gespeichert oder an den Warenkorb übertragen.
- Die Preisberechnung bleibt unverändert und verwendet weiterhin Kundenbreite und Kundenhöhe.
- Bei komplexen Pfaden ist die Flächenangabe eine numerische Näherung. Text, Bilder, Masken und use-Elemente müssen für verlässliche Produktionsdaten in Pfade umgewandelt werden.

Installation:
Den Ordner GXModules aus dem ZIP in das Gambio-Hauptverzeichnis kopieren und anschließend alle Gambio- und Theme-Caches leeren.


==============================================================================
README-M5.3.1.txt
==============================================================================

LetterConfigurator M5.3.1 – serverseitige Geometrie-Architektur
===============================================================

Neu:
- eigenständiger GeometryService für serverseitige SVG-Analysen
- sicherer XML-/SVG-Parser ohne Netzwerk- und Entity-Zugriffe
- unveränderliches Ergebnisobjekt und eigene Domain-Exception
- Analyzer für Dokumentmaße, ViewBox, Elemente und geometrische Grundformen
- explizite Kennzeichnung unvollständiger Geometrie bei Pfaden und Transformationen

Bewusste Grenze dieses Meilensteins:
- keine Anbindung an den bestehenden SVG-Datei-Input
- kein Upload und keine Speicherung
- keine Änderung an Frontend, Warenkorb, Datenbank oder Preisberechnung
- Pfade und transformierte Geometrien benötigen vor einer produktiven Nutzung
  noch eine dedizierte Path-/Transform-Engine


==============================================================================
M4-S.5.5 – Mobile Liveansicht und fortlaufende README
==============================================================================

Umgesetzt:
- zuverlässige Synchronisierung der Texteingabe auf iPhone/Safari durch eine wertbasierte Überwachung während der Eingabe
- zusätzliche Behandlung von Kompositionsereignissen mobiler Bildschirmtastaturen
- kompakte, sticky Liveansicht auf Smartphone und Tablet
- lange einzelne Textzeilen werden nicht sichtbar umgebrochen
- automatische Anpassung der Vorschau-Schriftgröße an verfügbare Breite und Höhe
- alle bisherigen README-*.txt wurden in diese fortlaufende README.txt zusammengeführt
- Modulversion 4-S.5.5


M4-S.5.6 – Stabile Texteingabe und vollständige Vorschau
========================================================

- Liveansicht wird dauerhaft mit dem tatsächlichen Textfeldwert synchronisiert.
- Zusätzliche ereignisbasierte Synchronisierung im Capture-Modus für mobile Browser.
- Automatische Schriftgrößenberechnung anhand der real verfügbaren Vorschaufläche.
- Lange einzeilige Texte werden ohne sichtbaren Umbruch vollständig eingepasst.
- Mehrzeilige Texte werden zugleich nach Breite und Höhe skaliert.
- Modulversion 4-S.5.6.
