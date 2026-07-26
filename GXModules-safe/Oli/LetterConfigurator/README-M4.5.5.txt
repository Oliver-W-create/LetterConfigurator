LetterConfigurator M4.5.5 – Warenwert-Berechnung

- Konfigurierte Artikel werden direkt in shoppingCart::calculate() mit dem serverseitig validierten Konfiguratorpreis berechnet.
- Nicht konfigurierte Artikel verwenden weiterhin unverändert die native Gambio-Preisberechnung.
- Warenwert, Steuerbasis und Versand-/Order-Total-Module bleiben getrennt und kompatibel.
- Keine Core- und Datenbankänderungen.
