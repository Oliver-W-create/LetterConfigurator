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
