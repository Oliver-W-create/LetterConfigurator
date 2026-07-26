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
