M4.5.7 – Gambio Order-Subtotal Fix

- Behebt den Warenwert in der rechten Gambio-Summenbox.
- Die Gambio-Klasse order berechnet konfigurierbare Artikel nicht mehr erneut aus dem Stammpreis 0,00 EUR.
- Stattdessen wird der bereits serverseitig validierte Konfiguratorpreis aus dem Warenkorb übernommen.
- Warenwert, Steuerbasis und Ausgangswert der Gesamtsumme werden neu aufgebaut.
- Versandkosten und nachfolgende Order-Total-Module bleiben erhalten.
- Bereits gespeicherte Bestellungen werden nicht nachträglich verändert.
